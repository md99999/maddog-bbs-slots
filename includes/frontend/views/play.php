<?php
/**
 * The machine. The form works on its own (a POST that reloads the page with the result); with
 * JavaScript, assets/js/maddog-bbs-slots.js takes it over, spins the reels and asks the server for
 * the result without leaving the page.
 *
 * @var object $p the player
 */
if (!defined('ABSPATH')) exit;

// A player who ran low before the bailout rules applied (or whose bankroll an admin changed) gets it now.
if (MDBBS_Slots::maybe_bailout($p)) {
    $p = MDBBS_Player::fresh($p->id);
    MDBBS_UI::flash('warning', 'Your bankroll was low, so the house has given you today\'s one-time bailout.');
    echo MDBBS_UI::render_flashes();
}

$bankroll = (int) $p->bankroll;
$spins = (int) $p->spins_left;
$bet = MDBBS_Player::default_bet($p);
$can_spin = $spins > 0 && $bankroll >= MDBBS_Game::min_bet();
$start = ['seven', 'jackpot', 'seven'];
if (!$can_spin) {
    $status = $spins <= 0
        ? 'No spins left today. Your spins come back tomorrow (site time).'
        : 'You are out of credits until tomorrow\'s top-up.';
} else {
    $status = 'Choose your wager and pull the handle.';
}
?>
<div class="mdbbs-panel mdbbs-machine" data-mdbbs-machine>
    <div class="mdbbs-jackpot-box">
        <p class="mdbbs-jackpot-label">Progressive Jackpot</p>
        <h1 class="mdbbs-jackpot" data-mdbbs="jackpot"><?php echo esc_html(MDBBS_Game::fmt(MDBBS_Game::jackpot())); ?></h1>
    </div>

    <p class="mdbbs-score">
        <span class="mdbbs-label">Your score</span>
        <strong data-mdbbs="bankroll"><?php echo esc_html(MDBBS_Game::fmt($bankroll)); ?></strong>
        <span class="mdbbs-dim">credits</span>
        <span class="mdbbs-sep">&middot;</span>
        <span class="mdbbs-label">Spins left</span> <strong data-mdbbs="spins"><?php echo (int) $spins; ?></strong>
    </p>

    <div class="mdbbs-reels" aria-label="Reels">
        <?php foreach ($start as $i => $key) : ?>
            <div class="mdbbs-reel" data-mdbbs-reel="<?php echo (int) $i; ?>"><?php echo MDBBS_UI::symbol($key); ?></div>
        <?php endforeach; ?>
    </div>

    <?php echo MDBBS_UI::form_open('spin', 'mdbbs-controls', 'data-mdbbs="form"'); ?>
        <label for="mdbbs-bet">Wager</label>
        <select id="mdbbs-bet" name="bet" data-mdbbs="bet">
            <?php foreach (MDBBS_Game::BETS as $amount) : ?>
                <option value="<?php echo (int) $amount; ?>" <?php selected($amount, $bet); ?> <?php disabled($amount > $bankroll); ?>>
                    <?php echo esc_html(MDBBS_Game::fmt($amount)); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="mdbbs-btn mdbbs-pull" data-mdbbs="pull" <?php disabled(!$can_spin); ?>>Pull</button>
    </form>
    <p class="mdbbs-small mdbbs-dim mdbbs-keys">Press <kbd>Enter</kbd> or the space bar to pull. Your wager stays set until you change it.</p>

    <div class="mdbbs-result" data-mdbbs="status" role="status" aria-live="polite"><?php echo esc_html($status); ?></div>
    <div class="mdbbs-extra" data-mdbbs="extra" aria-live="polite"></div>
</div>

<details class="mdbbs-panel mdbbs-help">
    <summary>Score table</summary>
    <?php include MDBBS_PATH . 'includes/frontend/views/_paytable.php'; ?>
</details>

<?php include MDBBS_PATH . 'includes/frontend/views/_notice.php'; ?>
