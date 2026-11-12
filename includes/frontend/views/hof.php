<?php
/**
 * The Hall of Fame and the top scores. Readable by anyone, signed in or not.
 *
 *   [mdbbs-hof]                       the full page
 *   [mdbbs-hof limit=10 compact=1]    for a sidebar: the records and the top scores
 *   (The pre-rename [wpbs-hof] and [wpb-hof] work too.)
 *
 * limit: how many top scores to list (20 on the page, 10 when compact, 100 at most).
 *
 * @var object|null $p       the player, when one is signed in
 * @var bool        $compact whether this is the compact widget
 */
if (!defined('ABSPATH')) exit;

$jackpot = MDBBS_Records::biggest_jackpot();
$spin = MDBBS_Records::get(MDBBS_Records::BIGGEST_SPIN);
$streak = MDBBS_Records::get(MDBBS_Records::LONGEST_STREAK);
$month = MDBBS_Records::best_month();
$month_label = function ($ym) {
    $d = date_create_immutable($ym . '-01', wp_timezone());
    return $d ? wp_date('F Y', $d->getTimestamp()) : $ym;
};
$records = [
    'Biggest Jackpot (all time)' => $jackpot
        ? [$jackpot->player_name, MDBBS_Game::fmt($jackpot->amount), mysql2date(get_option('date_format'), $jackpot->won_at)]
        : null,
    'Highest single spin win' => $spin ? [$spin->player_name, MDBBS_Game::fmt($spin->record_value), $spin->detail] : null,
    'Most consecutive wins' => $streak ? [$streak->player_name, MDBBS_Game::fmt($streak->record_value), 'in a row'] : null,
    'Highest monthly bankroll' => $month ? [$month->player_name, MDBBS_Game::fmt($month->peak_bankroll), $month_label($month->month)] : null,
];

if ($compact) :
    $top = MDBBS_Player::top(MDBBS_Shortcodes::limit(10, 100));
    ?>
    <div class="mdbbs-panel mdbbs-hof-compact">
        <img class="mdbbs-logo mdbbs-logo-widget" src="<?php echo esc_url(MDBBS_URL . 'assets/img/maddog-bbs-slots-logo.svg'); ?>"
             width="400" height="400" alt="<?php echo esc_attr(MDBBS_GAME_NAME); ?>!">
        <h3>Hall of Fame</h3>
        <ul class="mdbbs-records">
            <?php foreach ($records as $title => $r) : ?>
                <li class="mdbbs-small"><span class="mdbbs-label"><?php echo esc_html($title); ?></span><br>
                    <?php if ($r) : ?>
                        <?php echo esc_html($r[0]); ?> <span class="mdbbs-gold"><?php echo esc_html($r[1]); ?></span>
                    <?php else : ?>
                        <span class="mdbbs-dim">Not yet claimed</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if ($top) : ?>
            <h4>Top scores</h4>
            <ol class="mdbbs-toplist">
                <?php foreach ($top as $row) : ?>
                    <li class="mdbbs-small"><?php echo esc_html($row->player_name); ?>
                        <span class="mdbbs-dim"><?php echo esc_html(MDBBS_Game::fmt($row->bankroll)); ?></span></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
        <p class="mdbbs-small"><a href="<?php echo esc_url(MDBBS_UI::url('hof')); ?>">The full Hall of Fame</a></p>
    </div>
    <?php
    return;
endif;

$top = MDBBS_Player::top(MDBBS_Shortcodes::limit(20, 100));
$ranks = MDBBS_Records::rank_list();
$winners = MDBBS_Records::jackpots(20);
$leaders = MDBBS_Records::monthly_leaders(12);
?>
<div class="mdbbs-panel mdbbs-hof-banner">
    <img class="mdbbs-logo mdbbs-logo-small" src="<?php echo esc_url(MDBBS_URL . 'assets/img/maddog-bbs-slots-logo.svg'); ?>"
         width="400" height="400" alt="<?php echo esc_attr(MDBBS_GAME_NAME); ?>!">
    <h2>Hall of Fame</h2>
    <div class="mdbbs-table-wrap">
    <table class="mdbbs-table">
        <thead><tr><th>Record</th><th>Player</th><th>Score</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($records as $title => $r) : ?>
            <tr>
                <td><?php echo esc_html($title); ?></td>
                <?php if ($r) : ?>
                    <td><strong><?php echo esc_html($r[0]); ?></strong></td>
                    <td class="mdbbs-gold"><?php echo esc_html($r[1]); ?></td>
                    <td class="mdbbs-dim mdbbs-small"><?php echo esc_html($r[2]); ?></td>
                <?php else : ?>
                    <td class="mdbbs-dim" colspan="3">Not yet claimed</td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="mdbbs-grid">
    <div class="mdbbs-panel">
        <h3>First to reach each rank</h3>
        <div class="mdbbs-table-wrap">
        <table class="mdbbs-table">
            <thead><tr><th>Rank</th><th>Reached with</th><th>First player</th></tr></thead>
            <tbody>
            <?php foreach ($ranks as $r) : ?>
                <tr>
                    <td><?php echo esc_html($r['title']); ?></td>
                    <td class="mdbbs-dim"><?php echo esc_html(sprintf('a %s best score or %d days played', MDBBS_Game::fmt($r['score']), $r['days'])); ?></td>
                    <td><?php echo $r['record']
                        ? esc_html($r['record']->player_name) . ' <span class="mdbbs-dim mdbbs-small">' . esc_html(mysql2date(get_option('date_format'), $r['record']->achieved_at)) . '</span>'
                        : '<span class="mdbbs-dim">Nobody yet</span>'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <div class="mdbbs-panel">
        <h3>Highest bankroll by month</h3>
        <?php if (!$leaders) : ?>
            <p class="mdbbs-dim">No months on the books yet.</p>
        <?php else : ?>
            <div class="mdbbs-table-wrap">
            <table class="mdbbs-table">
                <thead><tr><th>Month</th><th>Player</th><th>Bankroll</th></tr></thead>
                <tbody>
                <?php foreach ($leaders as $row) : ?>
                    <tr>
                        <td><?php echo esc_html($month_label($row->month)); ?></td>
                        <td><?php echo esc_html($row->player_name); ?></td>
                        <td><?php echo esc_html(MDBBS_Game::fmt($row->peak_bankroll)); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="mdbbs-panel">
    <h3>Jackpot winners</h3>
    <?php if (!$winners) : ?>
        <p class="mdbbs-dim">Nobody has hit the progressive yet. It stands at <?php echo esc_html(MDBBS_Game::fmt(MDBBS_Game::jackpot())); ?>.</p>
    <?php else : ?>
        <div class="mdbbs-table-wrap">
        <table class="mdbbs-table">
            <thead><tr><th>When</th><th>Player</th><th>Jackpot</th><th>Wager</th></tr></thead>
            <tbody>
            <?php foreach ($winners as $row) : ?>
                <tr>
                    <td class="mdbbs-dim"><?php echo esc_html(mysql2date(get_option('date_format'), $row->won_at)); ?></td>
                    <td><?php echo esc_html($row->player_name); ?></td>
                    <td class="mdbbs-gold"><?php echo esc_html(MDBBS_Game::fmt($row->amount)); ?></td>
                    <td><?php echo esc_html(MDBBS_Game::fmt($row->bet)); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<div class="mdbbs-panel">
    <h3>Top scores</h3>
    <?php if (!$top) : ?>
        <p class="mdbbs-dim">No scores yet.</p>
    <?php else : ?>
        <div class="mdbbs-table-wrap">
        <table class="mdbbs-table">
            <thead><tr><th>#</th><th>Player</th><th>Rank</th><th>Score</th><th>Best spin</th><th>Best streak</th><th>Jackpots</th></tr></thead>
            <tbody>
            <?php foreach ($top as $i => $row) : ?>
                <tr<?php echo ($p && (int) $p->id === (int) $row->id) ? ' class="mdbbs-current"' : ''; ?>>
                    <td><?php echo (int) $i + 1; ?></td>
                    <td><?php echo esc_html($row->player_name); ?></td>
                    <td class="mdbbs-dim"><?php echo esc_html(MDBBS_Game::player_rank($row)); ?></td>
                    <td><?php echo esc_html(MDBBS_Game::fmt($row->bankroll)); ?></td>
                    <td><?php echo esc_html(MDBBS_Game::fmt($row->biggest_win)); ?></td>
                    <td><?php echo (int) $row->best_streak; ?></td>
                    <td><?php echo (int) $row->jackpots_won; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
    <?php if (!is_user_logged_in()) echo MDBBS_UI::sign_in_buttons(); ?>
</div>
