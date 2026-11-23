<?php
/**
 * Developer test for MDBBS_Slots::spin(): runs real spins against a MySQL or MariaDB database with a
 * minimal $wpdb stand-in (no WordPress needed), and checks that a spin is saved completely or not at
 * all: a failed payout, progressive update or COMMIT leaves the bankroll, spins, jackpot and Gazette
 * exactly as they were, and a jackpot is never reset without being paid.
 *
 *   php tools/test-spin.php
 *
 * Connection settings come from MDBBS_TEST_DB_HOST, _NAME, _USER and _PASS (defaults: localhost, wp,
 * wp, wp). Use a scratch database: the tables are created under the prefix "mdbbstest_", and any
 * existing tables with that prefix are dropped first. A WordPress site's own tables are never touched.
 * CLI only, and left out of release zips with the rest of tools/.
 */
if (PHP_SAPI !== 'cli') exit("CLI only.\n");
$plugin = dirname(__DIR__) . '/';
define('ABSPATH', '/tmp/');
define('MINUTE_IN_SECONDS', 60);
define('DAY_IN_SECONDS', 86400);

class TestWpdb {
    public $prefix = 'mdbbstest_';
    public $last_error = '';
    public $insert_id = 0;
    public $fail = null;      // regex: the next matching query fails
    private $db;
    function __construct() {
        mysqli_report(MYSQLI_REPORT_OFF);
        $env = function ($k, $d) { $v = getenv('MDBBS_TEST_DB_' . $k); return ($v === false || $v === '') ? $d : $v; };
        $this->db = new mysqli($env('HOST', 'localhost'), $env('USER', 'wp'), $env('PASS', 'wp'), $env('NAME', 'wp'));
        if ($this->db->connect_errno) { fwrite(STDERR, 'Could not connect: ' . $this->db->connect_error . "\n"); exit(2); }
    }
    function prepare($q, ...$args) {
        if (count($args) === 1 && is_array($args[0])) $args = $args[0];
        $i = 0;
        return preg_replace_callback('/%[dsf]/', function ($m) use (&$i, $args) {
            $v = $args[$i++];
            if ($m[0] === '%d') return (string) (int) $v;
            if ($m[0] === '%f') return (string) (float) $v;
            return "'" . $this->db->real_escape_string((string) $v) . "'";
        }, $q);
    }
    function esc_like($s) { return addcslashes($s, '_%\\'); }
    function query($q) {
        if ($this->fail && preg_match($this->fail, $q)) { $this->fail = null; $this->last_error = 'injected failure'; return false; }
        $r = $this->db->query($q);
        if ($r === false) { $this->last_error = $this->db->error; return false; }
        $this->last_error = '';
        if ($r instanceof mysqli_result) { $rows = []; while ($o = $r->fetch_object()) $rows[] = $o; return $rows; }
        $this->insert_id = $this->db->insert_id;
        return $this->db->affected_rows;
    }
    function get_results($q) { $r = $this->query($q); return is_array($r) ? $r : []; }
    function get_row($q) { $r = $this->get_results($q); return $r[0] ?? null; }
    function get_var($q) { $r = $this->get_row($q); if (!$r) return null; $a = array_values((array) $r); return $a[0]; }
    function insert($t, $d) {
        $cols = implode(',', array_keys($d));
        $vals = implode(',', array_map(function ($v) { return $v === null ? 'NULL' : "'" . $this->db->real_escape_string((string) $v) . "'"; }, $d));
        return $this->query("INSERT INTO $t ($cols) VALUES ($vals)");
    }
    function update($t, $d, $w) {
        $set = implode(',', array_map(function ($k, $v) { return "$k = " . ($v === null ? 'NULL' : "'" . $this->db->real_escape_string((string) $v) . "'"); }, array_keys($d), $d));
        $wh = implode(' AND ', array_map(function ($k, $v) { return "$k = '" . $this->db->real_escape_string((string) $v) . "'"; }, array_keys($w), $w));
        return $this->query("UPDATE $t SET $set WHERE $wh");
    }
    function delete($t, $w) {
        $wh = implode(' AND ', array_map(function ($k, $v) { return "$k = '" . $this->db->real_escape_string((string) $v) . "'"; }, array_keys($w), $w));
        return $this->query("DELETE FROM $t WHERE $wh");
    }
    function get_charset_collate() { return 'DEFAULT CHARSET=utf8mb4'; }
}
$GLOBALS['wpdb'] = new TestWpdb();

$OPTIONS = [];
function get_option($k, $d = false) { global $OPTIONS; return array_key_exists($k, $OPTIONS) ? $OPTIONS[$k] : $d; }
function update_option($k, $v, $a = null) { global $OPTIONS; $OPTIONS[$k] = $v; return true; }
function add_option($k, $v, $x = '', $a = null) { global $OPTIONS; if (isset($OPTIONS[$k])) return false; $OPTIONS[$k] = $v; return true; }
function delete_option($k) { global $OPTIONS; unset($OPTIONS[$k]); return true; }
function wp_parse_args($a, $d) { return array_merge($d, $a); }
function current_time($f) { return $f === 'mysql' ? date('Y-m-d H:i:s') : date($f); }
function sanitize_key($k) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower($k)); }
function sanitize_text_field($s) { return trim(strip_tags($s)); }
function number_format_i18n($n) { return number_format($n); }
function get_current_user_id() { return 1; }
function is_user_logged_in() { return true; }
function wp_date($f, $t = null) { return date($f, $t ?? time()); }

require $plugin . 'includes/class-mdbbs-core.php';
require $plugin . 'includes/class-mdbbs-installer.php';
require $plugin . 'includes/class-mdbbs-migration.php';
require $plugin . 'includes/services/class-mdbbs-player.php';
require $plugin . 'includes/services/class-mdbbs-records.php';
require $plugin . 'includes/services/class-mdbbs-slots.php';

global $wpdb;
foreach (MDBBS_DB::TABLES as $t) $wpdb->query('DROP TABLE IF EXISTS ' . MDBBS_DB::t($t));
$sql = str_replace(['{prefix}', '{charset_collate}'], ['mdbbstest_', 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'], (string) include $plugin . 'sql/schema.php');
foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
    if ($wpdb->query($stmt) === false) die("schema: {$wpdb->last_error}\n");
}
MDBBS_Installer::seed_state();

$fails = 0;
function check($cond, $what) { global $fails; echo ($cond ? 'PASS ' : 'FAIL ') . $what . "\n"; if (!$cond) $fails++; }
function state() {
    global $wpdb;
    $p = $wpdb->get_row('SELECT bankroll, spins_left, total_spins, today_spins FROM mdbbstest_mdbbs_players WHERE id = 1');
    return [
        'bankroll' => (int) $p->bankroll, 'spins' => (int) $p->spins_left, 'total' => (int) $p->total_spins,
        'jackpot'  => (int) $wpdb->get_var("SELECT state_value FROM mdbbstest_mdbbs_state WHERE state_key = 'jackpot'"),
        'news'     => (int) $wpdb->get_var('SELECT COUNT(*) FROM mdbbstest_mdbbs_news'),
    ];
}

$id = MDBBS_Player::create(1, 'Tester');
$wpdb->query('UPDATE mdbbstest_mdbbs_players SET spins_left = 1000 WHERE id = 1');
$p = MDBBS_Player::fresh($id);

// 1. A normal spin is saved in full.
$before = state();
$r = MDBBS_Slots::spin($p, 100);
$after = state();
check($after['total'] === $before['total'] + 1, 'normal spin: counted');
check($after['bankroll'] === min(MDBBS_Game::MAX_SCORE, $before['bankroll'] - 100 + $r['win'] + ($r['bailout'] ? 10000 : 0)), 'normal spin: bankroll = before - wager + win');
check($after['spins'] === $before['spins'] - 1 + $r['bonus'], 'normal spin: spins = before - 1 + bonus');
check($r['kind'] === 'jackpot' || $after['jackpot'] === $before['jackpot'] + 5000, 'normal spin: progressive fed');

// 2. The payout fails: everything is rolled back, and the failure is logged.
foreach (['/days_played = days_played \+/' => 'payout', '/SET state_value = state_value \+/' => 'progressive', '/^COMMIT$/' => 'commit'] as $pattern => $label) {
    $before = state();
    $logs = (int) $wpdb->get_var('SELECT COUNT(*) FROM mdbbstest_mdbbs_admin_log');
    $wpdb->fail = $pattern;
    $msg = '';
    try { MDBBS_Slots::spin($p, 500); } catch (MDBBS_Exception $e) { $msg = $e->getMessage(); }
    $after = state();
    check(strpos($msg, 'nothing was charged') !== false, "$label fails: player told nothing was charged");
    check($after === $before, "$label fails: bankroll, spins, jackpot and news unchanged");
    check((int) $wpdb->get_var('SELECT COUNT(*) FROM mdbbstest_mdbbs_admin_log') === $logs + 1, "$label fails: one admin log entry, kept after the rollback");
}

// 3. A refusal meant for the player passes through untouched.
$wpdb->query('UPDATE mdbbstest_mdbbs_players SET spins_left = 0 WHERE id = 1');
$before = state();
$msg = '';
try { MDBBS_Slots::spin($p, 100); } catch (MDBBS_Exception $e) { $msg = $e->getMessage(); }
check(strpos($msg, 'no spins left') !== false && state() === $before, 'no spins left: refused with its own message, nothing changed');
$wpdb->query('UPDATE mdbbstest_mdbbs_players SET spins_left = 1000 WHERE id = 1');

// 4. The jackpot claim pays the true pot and resets it; rolled back, the pot is untouched.
$claim = new ReflectionMethod('MDBBS_Slots', 'claim_jackpot');
$claim->setAccessible(true);
$wpdb->query("UPDATE mdbbstest_mdbbs_state SET state_value = 123456789 WHERE state_key = 'jackpot'");
$wpdb->query('START TRANSACTION');
$wpdb->query("UPDATE mdbbstest_mdbbs_state SET state_value = state_value + 5000 WHERE state_key = 'jackpot'");
$won = $claim->invoke(null);
$wpdb->query('ROLLBACK');
check($won === 123461789, 'jackpot claim: pays the pot including this spin\'s feed');
check(state()['jackpot'] === 123456789, 'jackpot claim rolled back: pot untouched');
$wpdb->query('START TRANSACTION');
$won = $claim->invoke(null);
$wpdb->query('COMMIT');
check($won === 123456789 && state()['jackpot'] === MDBBS_Settings::get('jackpot_seed'), 'jackpot claim committed: paid the pot, reset to the seed');
$wpdb->fail = '/SET state_value = %d|SET state_value = \d+, updated_at/';
$threw = false;
$wpdb->query('START TRANSACTION');
try { $claim->invoke(null); } catch (RuntimeException $e) { $threw = true; }
$wpdb->query('ROLLBACK');
check($threw, 'jackpot reset fails: throws instead of paying out');

// 5. Many spins in a row stay consistent: bankroll never negative, every spin counted.
$before = state();
$n = 300;
for ($i = 0; $i < $n; $i++) { try { MDBBS_Slots::spin($p, 100); } catch (MDBBS_Exception $e) { echo 'refused: ' . $e->getMessage() . "\n"; } }
$after = state();
check($after['total'] === $before['total'] + $n && $after['bankroll'] >= 0, "$n spins: all counted, bankroll never negative");

echo $fails ? "$fails FAILED\n" : "ALL PASSED\n";
exit($fails ? 1 : 0);
