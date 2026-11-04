<?php
/**
 * Runs when the plugin is deleted from the Plugins screen.
 *
 * The scheduled job is always removed. The game's tables and options are removed only if
 * "Delete all data" is ticked under Maddog BBS Slots -> Settings; otherwise they are kept so the
 * game can be reinstalled with its players, scores and Hall of Fame intact.
 */
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

wp_clear_scheduled_hook('mdbbs_daily_maintenance');
wp_clear_scheduled_hook('wpbbs_daily_maintenance'); // its name before the rename to Maddog BBS Slots
delete_transient('mdbbs_git_reachable');

$settings = get_option('mdbbs_settings', []);
if (!is_array($settings) || empty($settings['delete_data_on_uninstall'])) return;

global $wpdb;
foreach (['players', 'state', 'jackpots', 'records', 'monthly', 'news', 'admin_log'] as $table) {
    $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'mdbbs_' . $table);
}
foreach (['mdbbs_settings', 'mdbbs_db_version', 'mdbbs_page_ids', 'mdbbs_nav_post_id', 'mdbbs_last_daily', 'mdbbs_daily_log', 'mdbbs_migrated_from_wpbbs', 'mdbbs_migration_lock', 'mdbbs_migration_error'] as $option) {
    delete_option($option);
}
