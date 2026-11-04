<?php
/**
 * The score table, shared by the home and play pages.
 */
if (!defined('ABSPATH')) exit;
$sym = function ($key, $n = 3) {
    return str_repeat(MDBBS_UI::symbol($key), $n);
};
?>
<div class="mdbbs-table-wrap">
<table class="mdbbs-table mdbbs-paytable">
    <thead><tr><th>Combination</th><th>Score</th></tr></thead>
    <tbody>
        <tr class="mdbbs-pay-jackpot"><td><?php echo $sym('jackpot'); ?></td><td><strong>Progressive Jackpot</strong></td></tr>
        <tr><td><?php echo $sym('seven'); ?></td><td>500x your wager</td></tr>
        <tr><td><?php echo $sym('diamond'); ?></td><td>250x</td></tr>
        <tr><td><?php echo $sym('bar'); ?></td><td>100x</td></tr>
        <tr><td><?php echo $sym('bell'); ?></td><td>50x</td></tr>
        <tr><td><?php echo $sym('lemon'); ?></td><td>10x</td></tr>
        <tr><td><?php echo $sym('cherry'); ?></td><td>3x</td></tr>
        <tr><td>Any pair on the first two reels</td><td>2x</td></tr>
        <tr><td>A Cherry anywhere (with no other win)</td><td>Bonus Spin</td></tr>
    </tbody>
</table>
</div>
<p class="mdbbs-small mdbbs-dim">Every score also earns a Bonus Spin. A Bonus Spin gives you back the spin you just used; the wager is not returned.</p>
