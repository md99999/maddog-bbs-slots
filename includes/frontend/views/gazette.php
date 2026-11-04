<?php
/**
 * The Gazette: where the progressive stands, who played today, the top scores and the news.
 * Readable by anyone, signed in or not.
 *
 *   [mdbbs-gazette]                       the full page
 *   [mdbbs-gazette limit=10 compact=1]    for a sidebar: the progressive and the top scores
 *
 * limit: on the full page, how many news items (40 by default, 200 at most);
 *        when compact, how many top scores (10 by default, 50 at most).
 *
 * @var object|null $p       the player, when one is signed in
 * @var bool        $compact whether this is the compact widget
 */
if (!defined('ABSPATH')) exit;

$jackpot = MDBBS_Game::jackpot();

if ($compact) :
    $top = MDBBS_Player::top(MDBBS_Shortcodes::limit(10, 50));
    ?>
    <div class="mdbbs-panel mdbbs-gazette-compact">
        <h3><?php echo esc_html(MDBBS_GAZETTE_NAME); ?></h3>
        <p class="mdbbs-small"><span class="mdbbs-label">Progressive Jackpot</span><br>
            <strong class="mdbbs-gold"><?php echo esc_html(MDBBS_Game::fmt($jackpot)); ?></strong></p>
        <?php if (!$top) : ?>
            <p class="mdbbs-small mdbbs-dim">No scores yet. Be the first to play!</p>
        <?php else : ?>
            <ol class="mdbbs-toplist">
                <?php foreach ($top as $row) : ?>
                    <li class="mdbbs-small"><?php echo esc_html($row->player_name); ?>
                        <span class="mdbbs-dim"><?php echo esc_html(MDBBS_Game::fmt($row->bankroll)); ?></span></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
        <p class="mdbbs-small"><a href="<?php echo esc_url(MDBBS_UI::url('gazette')); ?>">All the news</a>
            &middot; <a href="<?php echo esc_url(MDBBS_UI::url('play')); ?>">Play</a></p>
    </div>
    <?php
    return;
endif;

$limit = MDBBS_Shortcodes::limit(40, 200);
global $wpdb;
$news = $wpdb->get_results($wpdb->prepare(
    'SELECT * FROM ' . MDBBS_DB::t('news') . ' ORDER BY created_at DESC, id DESC LIMIT %d', $limit
));
$today = MDBBS_Player::played_today(50);
$top = MDBBS_Player::top(20);
$labels = [
    'new_player' => 'New player', 'jackpot' => 'JACKPOT', 'big_win' => 'Big win', 'record' => 'Record',
    'rank' => 'Rank', 'bailout' => 'Bailout', 'topup' => 'New day', 'season' => 'New season', 'promotion' => 'Promotion',
    'day_start' => 'At the machine', 'day_end' => 'Day done',
];
?>
<div class="mdbbs-panel mdbbs-masthead">
    <h2><?php echo esc_html(MDBBS_GAZETTE_NAME); ?></h2>
    <p class="mdbbs-dim mdbbs-small"><?php echo esc_html(wp_date(get_option('date_format'))); ?></p>
    <p class="mdbbs-jackpot-label">The progressive stands at</p>
    <p class="mdbbs-jackpot-small"><?php echo esc_html(MDBBS_Game::fmt($jackpot)); ?></p>
</div>

<div class="mdbbs-grid">
    <div class="mdbbs-panel">
        <h3>Who played today</h3>
        <?php if (!$today) : ?>
            <p class="mdbbs-dim">Nobody has pulled the handle yet today.</p>
        <?php else : ?>
            <div class="mdbbs-table-wrap">
            <table class="mdbbs-table">
                <thead><tr><th>Player</th><th>Spins</th><th>Won today</th><th>Score</th></tr></thead>
                <tbody>
                <?php foreach ($today as $row) : ?>
                    <tr<?php echo ($p && (int) $p->id === (int) $row->id) ? ' class="mdbbs-current"' : ''; ?>>
                        <td><?php echo esc_html($row->player_name); ?></td>
                        <td><?php echo (int) $row->today_spins; ?></td>
                        <td><?php echo esc_html(MDBBS_Game::fmt($row->today_won)); ?></td>
                        <td><?php echo esc_html(MDBBS_Game::fmt($row->bankroll)); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>
    <div class="mdbbs-panel">
        <h3>Top 20 scores</h3>
        <?php if (!$top) : ?>
            <p class="mdbbs-dim">No scores yet.</p>
        <?php else : ?>
            <div class="mdbbs-table-wrap">
            <table class="mdbbs-table">
                <thead><tr><th>#</th><th>Player</th><th>Rank</th><th>Score</th></tr></thead>
                <tbody>
                <?php foreach ($top as $i => $row) : ?>
                    <tr<?php echo ($p && (int) $p->id === (int) $row->id) ? ' class="mdbbs-current"' : ''; ?>>
                        <td><?php echo (int) $i + 1; ?></td>
                        <td><?php echo esc_html($row->player_name); ?></td>
                        <td class="mdbbs-dim"><?php echo esc_html(MDBBS_Game::player_rank($row)); ?></td>
                        <td><?php echo esc_html(MDBBS_Game::fmt($row->bankroll)); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="mdbbs-panel">
    <h3>The news</h3>
    <?php if (!$news) : ?>
        <p class="mdbbs-dim">The Gazette has nothing to report yet.</p>
    <?php else : ?>
        <div class="mdbbs-table-wrap">
        <table class="mdbbs-table">
            <thead><tr><th>When</th><th>Dispatch</th><th>Kind</th></tr></thead>
            <tbody>
            <?php foreach ($news as $n) : ?>
                <tr<?php echo $n->event_type === 'jackpot' ? ' class="mdbbs-news-jackpot"' : ''; ?>>
                    <td class="mdbbs-dim mdbbs-nowrap"><?php echo esc_html(MDBBS_UI::time_ago($n->created_at)); ?></td>
                    <td><?php echo esc_html($n->message); ?></td>
                    <td class="mdbbs-dim mdbbs-small"><?php echo esc_html($labels[$n->event_type] ?? ucfirst(str_replace('_', ' ', $n->event_type))); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
    <p class="mdbbs-small mdbbs-dim">News is kept for <?php echo (int) MDBBS_Settings::get('news_retention_days'); ?> days.</p>
    <?php if (!is_user_logged_in()) echo MDBBS_UI::sign_in_buttons(); ?>
</div>
