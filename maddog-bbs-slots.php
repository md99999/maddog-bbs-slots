<?php
/*
Plugin Name: Maddog BBS Slots
Plugin URI: https://github.com/md99999/maddog-bbs-slots
Author: Bill Mantz
Author URI: https://maddogproductions.online/
Description: Maddog BBS Slots: a turn-based progressive slot machine game for WordPress, in the spirit of the old BBS door games. A few spins a day, a bankroll that carries over, a progressive jackpot, a Gazette and a Hall of Fame. Credits have no cash value and are a game score only.
Version: 1.7.2
Requires PHP: 8.0
Requires at least: 7.0
Text Domain: maddog-bbs-slots
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Copyright (C) 2026 Bill Mantz

Maddog BBS Slots is free software: you can redistribute it and/or modify it under the terms of the
GNU General Public License as published by the Free Software Foundation, either version 2 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details. A copy is included in LICENSE.

DISCLAIMER: you install and run this plugin at your own risk, and the author accepts no
responsibility or liability for any loss, damage or compromise arising from its use. Every effort
has been made to write it safely, but new vulnerabilities appear in software of every kind every
day and no website can be guaranteed secure. Credits in this game have no cash value and depict a
game score only; it is not gambling and nothing can be bought, sold or redeemed. See README.md.
Report security problems privately to sysop@maddogproductions.online; see SECURITY.md.
*/
if (!defined('ABSPATH')) exit;

define('MDBBS_GAME_NAME', 'Maddog BBS Slots');
define('MDBBS_GAZETTE_NAME', 'The Maddog BBS Slots Gazette');
define('MDBBS_SOURCE_URL', 'https://github.com/md99999/maddog-bbs-slots');
define('MDBBS_SITE_NAME', 'maddogproductions.online');
define('MDBBS_VERSION', '1.7.2');
define('MDBBS_DB_VERSION', '3');
define('MDBBS_FILE', __FILE__);
define('MDBBS_PATH', plugin_dir_path(__FILE__));
define('MDBBS_URL', plugin_dir_url(__FILE__));

require_once MDBBS_PATH . 'includes/class-mdbbs-core.php';
require_once MDBBS_PATH . 'includes/class-mdbbs-installer.php';
require_once MDBBS_PATH . 'includes/class-mdbbs-migration.php';
require_once MDBBS_PATH . 'includes/class-mdbbs-health.php';
require_once MDBBS_PATH . 'includes/services/class-mdbbs-player.php';
require_once MDBBS_PATH . 'includes/services/class-mdbbs-records.php';
require_once MDBBS_PATH . 'includes/services/class-mdbbs-slots.php';
require_once MDBBS_PATH . 'includes/services/class-mdbbs-maintenance.php';
require_once MDBBS_PATH . 'includes/frontend/class-mdbbs-ui.php';
require_once MDBBS_PATH . 'includes/frontend/class-mdbbs-actions.php';
require_once MDBBS_PATH . 'includes/frontend/class-mdbbs-shortcodes.php';

register_activation_hook(__FILE__, ['MDBBS_Installer', 'activate']);
register_deactivation_hook(__FILE__, ['MDBBS_Installer', 'deactivate']);

add_action('plugins_loaded', ['MDBBS_Installer', 'maybe_upgrade']);
add_action('init', ['MDBBS_Shortcodes', 'register']);
add_action('template_redirect', ['MDBBS_Actions', 'handle']);
add_action('wp_ajax_mdbbs_spin', ['MDBBS_Actions', 'ajax_spin']);
add_action('wp_ajax_mdbbs_jackpot', ['MDBBS_Actions', 'ajax_jackpot']);
add_action('wp_ajax_nopriv_mdbbs_jackpot', ['MDBBS_Actions', 'ajax_jackpot']);
add_action('wp_enqueue_scripts', ['MDBBS_UI', 'enqueue_assets']);
add_action(MDBBS_Maintenance::DAILY_HOOK, ['MDBBS_Maintenance', 'cron']);
// The day turns over at midnight in the site's timezone (Settings -> General); follow it when it changes.
add_action('update_option_timezone_string', ['MDBBS_Maintenance', 'reschedule']);
add_action('update_option_gmt_offset', ['MDBBS_Maintenance', 'reschedule']);

if (is_admin()) {
    require_once MDBBS_PATH . 'admin/class-mdbbs-admin.php';
    MDBBS_Admin::init();
}
