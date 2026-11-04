<?php
/**
 * Choosing a player name: shown first on the home and play pages to a signed-in user who has none.
 */
if (!defined('ABSPATH')) exit;
?>
<div class="mdbbs-panel mdbbs-name-panel">
    <h2>Choose your player name</h2>
    <?php if (!MDBBS_Settings::get('allow_new_players')) : ?>
        <p>New players are not being accepted at the moment. Please check back later.</p>
    <?php else : ?>
        <p>Your player name is how you appear on the scores, in the Gazette and in the Hall of Fame. It stays with you for good,
            so choose carefully. You start with <strong><?php echo esc_html(MDBBS_Game::fmt(MDBBS_Settings::get('starting_bankroll'))); ?></strong> credits.</p>
        <?php echo MDBBS_UI::form_open('register', 'mdbbs-stack'); ?>
            <label for="mdbbs-player-name">Player name
                <input type="text" id="mdbbs-player-name" name="player_name" required autocomplete="nickname"
                       minlength="<?php echo (int) MDBBS_Player::NAME_MIN; ?>" maxlength="<?php echo (int) MDBBS_Player::NAME_MAX; ?>"
                       pattern="[A-Za-z0-9](?:[A-Za-z0-9 ._\-]*[A-Za-z0-9])?"
                       title="3 to 20 letters, numbers, spaces, dots, dashes or underscores, starting and ending with a letter or number">
            </label>
            <span class="mdbbs-small mdbbs-dim">3 to 20 characters: letters, numbers, spaces, dots, dashes and underscores.</span>
            <button type="submit" class="mdbbs-btn">Start playing</button>
        </form>
    <?php endif; ?>
</div>
