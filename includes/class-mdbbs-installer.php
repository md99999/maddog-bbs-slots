<?php
if (!defined('ABSPATH')) exit;

class MDBBS_Installer {

    public static function activate() {
        // Scores and the jackpot need 64-bit integers: on 32-bit PHP they stop at 2,147,483,647.
        if (PHP_INT_SIZE < 8) {
            deactivate_plugins(plugin_basename(MDBBS_FILE));
            wp_die(esc_html(MDBBS_GAME_NAME) . ' needs a 64-bit build of PHP; this server runs a 32-bit one.', 'Plugin not activated', ['back_link' => true]);
        }
        // A site coming from WP BBS Slots: move its tables and options across before anything is
        // created. If that fails, stop here: activating would start an empty game beside the old one.
        if (!MDBBS_Migration::maybe_run()) {
            deactivate_plugins(plugin_basename(MDBBS_FILE));
            $why = MDBBS_Migration::last_error() ?: 'Another request is moving it right now; try again in a few minutes.';
            wp_die(esc_html(MDBBS_GAME_NAME . ' could not take over the game data from ' . MDBBS_Migration::LEGACY_NAME . '. '
                . $why . ' Nothing was changed and ' . MDBBS_Migration::LEGACY_NAME . ' is still running.'),
                'Plugin not activated', ['back_link' => true]);
        }
        self::upgrade_from(get_option('mdbbs_db_version'));
        if (get_option(MDBBS_Settings::OPTION) === false) {
            update_option(MDBBS_Settings::OPTION, MDBBS_Settings::defaults());
        }
        MDBBS_Maintenance::schedule();
    }

    public static function deactivate() {
        MDBBS_Maintenance::unschedule();
    }

    public static function maybe_upgrade() {
        // Another page load is moving the old plugin's data right now; leave the tables to it.
        if (!MDBBS_Migration::maybe_run()) return;
        $from = get_option('mdbbs_db_version');
        if ($from !== MDBBS_DB_VERSION) self::upgrade_from($from);
    }

    /** Brings the tables up to date from schema version $from (false on a new install). */
    private static function upgrade_from($from) {
        self::install_schema();
        self::seed_state();
        if ($from === '1') self::upgrade_ranks();
    }

    /**
     * Version 2 keeps ranks for good, earned by best-ever score or days played. Earlier versions
     * did not count days, so anyone who has played starts at one day, and everyone gets the rank
     * their best-ever score has already earned.
     */
    private static function upgrade_ranks() {
        global $wpdb;
        $t = MDBBS_DB::t('players');
        $wpdb->query("UPDATE $t SET days_played = 1 WHERE days_played = 0 AND total_spins > 0");
        foreach ($wpdb->get_results("SELECT id, bankroll, peak_bankroll, days_played, rank_level FROM $t") as $row) {
            $level = MDBBS_Game::rank_for(max((int) $row->peak_bankroll, (int) $row->bankroll), (int) $row->days_played);
            if ($level > (int) $row->rank_level) {
                $wpdb->query($wpdb->prepare("UPDATE $t SET rank_level = %d WHERE id = %d", $level, $row->id));
            }
        }
    }

    /** Runs the schema in sql/schema.php through dbDelta, substituting the table prefix. */
    public static function install_schema() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $sql = (string) include MDBBS_PATH . 'sql/schema.php';
        $sql = str_replace(
            ['{prefix}', '{charset_collate}'],
            [$wpdb->prefix, $wpdb->get_charset_collate()],
            $sql
        );
        dbDelta($sql);
        update_option('mdbbs_db_version', MDBBS_DB_VERSION);
    }

    /** Creates the progressive jackpot at its starting value, if it does not exist yet. */
    public static function seed_state() {
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            'INSERT IGNORE INTO ' . MDBBS_DB::t('state') . ' (state_key, state_value, updated_at) VALUES (%s, %d, %s)',
            'jackpot', MDBBS_Settings::get('jackpot_seed'), current_time('mysql')
        ));
    }

    public static function drop_schema() {
        global $wpdb;
        foreach (MDBBS_DB::TABLES as $table) {
            $wpdb->query('DROP TABLE IF EXISTS ' . MDBBS_DB::t($table));
        }
    }
}
