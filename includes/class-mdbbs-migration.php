<?php
if (!defined('ABSPATH')) exit;

/**
 * Moves a site's game from the old WP BBS Slots plugin (folder wp-bbs-slots, prefix wpbbs_) to
 * Maddog BBS Slots (folder maddog-bbs-slots, prefix mdbbs_), keeping every player, score, record,
 * Gazette item and setting.
 *
 * It runs once, the first time this plugin loads on a site that still has the old plugin's data:
 *   - the old tables are renamed to the new names (a rename keeps every row; nothing is copied).
 *     If any rename fails, those already done are put back and nothing else changes, so the old
 *     plugin carries on exactly as it was;
 *   - the old plugin is deactivated, so the two never run side by side;
 *   - the old options are copied to the new names, then the old ones are deleted;
 *   - the old daily job is unscheduled (the new one is scheduled by the installer);
 *   - the game's pages, menu and block navigation keep their URLs, and only titles that still
 *     read exactly as the old plugin wrote them are changed to the new name.
 *
 * Nothing is overwritten: a table or option that already exists under the new name is left
 * alone, together with its old counterpart, and the run is logged so an admin can sort it out.
 */
class MDBBS_Migration {
    /** The old plugin, as WordPress names it (folder/main file). */
    const LEGACY_PLUGIN = 'wp-bbs-slots/wp-bbs-slots.php';
    const LEGACY_PREFIX = 'wpbbs_';
    const LEGACY_NAME   = 'WP BBS Slots';
    const LEGACY_HOOK   = 'wpbbs_daily_maintenance';

    /** Set once the move has been done (or found unnecessary), so it is never tried again. */
    const DONE_OPTION = 'mdbbs_migrated_from_wpbbs';
    /** Held while a move runs, so two page loads cannot do it at the same time. */
    const LOCK_OPTION = 'mdbbs_migration_lock';
    const LOCK_TTL    = 300;
    /** Why the last attempt failed, for the admin notice; removed once a move succeeds. */
    const ERROR_OPTION = 'mdbbs_migration_error';

    /** Old option => [new option, autoload]. */
    const OPTIONS = [
        'wpbbs_settings'    => ['mdbbs_settings', true],
        'wpbbs_db_version'  => ['mdbbs_db_version', true],
        'wpbbs_page_ids'    => ['mdbbs_page_ids', true],
        'wpbbs_nav_post_id' => ['mdbbs_nav_post_id', false],
        'wpbbs_last_daily'  => ['mdbbs_last_daily', false],
        'wpbbs_daily_log'   => ['mdbbs_daily_log', false],
    ];

    /** Whether there is old data to move. Cheap: both options are autoloaded. */
    public static function needed() {
        if (get_option(self::DONE_OPTION) !== false) return false;
        return get_option('wpbbs_db_version') !== false || get_option('wpbbs_settings') !== false;
    }

    /**
     * Moves the old data if there is any.
     *
     * @return bool false while another page load holds the lock, or when the move failed; the
     *              caller must then leave the database alone, or it would create empty tables
     *              under the new names beside the old ones.
     */
    public static function maybe_run() {
        if (!self::needed()) return true;

        // add_option() inserts a row with a unique name, so only one request can take the lock.
        if (!add_option(self::LOCK_OPTION, time(), '', false)) {
            $since = (int) get_option(self::LOCK_OPTION);
            if ($since > 0 && time() - $since < self::LOCK_TTL) return false;
            // A lock left behind by a request that died part-way; take it over.
            update_option(self::LOCK_OPTION, time(), false);
        }

        try {
            $notes = self::run();
        } finally {
            delete_option(self::LOCK_OPTION);
        }
        if ($notes === false) return false;
        delete_option(self::ERROR_OPTION);

        // The daily job under its new name (a no-op when it is already scheduled).
        MDBBS_Maintenance::schedule();

        $msg = 'Moved the game from ' . self::LEGACY_NAME . ' to ' . MDBBS_GAME_NAME . '.';
        if ($notes) $msg .= ' Needs a look: ' . implode(' ', $notes);
        // The admin log is one of the moved tables; on a site without it the message goes to the PHP log.
        if (self::table_exists(MDBBS_DB::t('admin_log'))) MDBBS_Log::admin('migrate', $msg);
        if ($notes) error_log('[' . MDBBS_GAME_NAME . '] ' . $msg);
        return true;
    }

    /**
     * @return string[]|false problems an admin should know about (empty when all went cleanly),
     *                        or false when the tables could not be moved and nothing was changed.
     */
    private static function run() {
        $notes = self::rename_tables();
        if ($notes === false) return false;

        self::deactivate_legacy_plugin();
        wp_clear_scheduled_hook(self::LEGACY_HOOK);
        delete_transient('wpbbs_git_reachable');

        $notes = array_merge($notes, self::move_options());
        self::retitle();

        update_option(self::DONE_OPTION, current_time('mysql'), true);
        return $notes;
    }

    /** The old plugin would recreate its empty tables on its next page load; switch it off first. */
    private static function deactivate_legacy_plugin() {
        if (!function_exists('is_plugin_active')) require_once ABSPATH . 'wp-admin/includes/plugin.php';
        if (is_plugin_active(self::LEGACY_PLUGIN) || is_plugin_active_for_network(self::LEGACY_PLUGIN)) {
            // Silent: its own deactivation hook only unschedules the job, which is done here anyway,
            // and its code may not survive running against tables that have just been renamed.
            deactivate_plugins(self::LEGACY_PLUGIN, true, is_plugin_active_for_network(self::LEGACY_PLUGIN));
        }
    }

    private static function table_exists($name) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($name))) === $name;
    }

    /** @return string[]|false notes, or false after a failed rename (any done are undone). */
    private static function rename_tables() {
        global $wpdb;
        $notes = [];
        $done = [];
        foreach (MDBBS_DB::TABLES as $table) {
            // Both names come from fixed constants and the site's own table prefix, never from input.
            $old = $wpdb->prefix . self::LEGACY_PREFIX . $table;
            $new = MDBBS_DB::t($table);
            if (!self::table_exists($old)) continue;
            if (self::table_exists($new)) {
                $notes[] = "Both $old and $new exist; $old was left as it is.";
                continue;
            }
            // ALTER TABLE ... RENAME TO rather than RENAME TABLE: the same thing in MySQL and MariaDB,
            // and also understood by the SQLite driver WordPress Playground and some hosts use.
            if ($wpdb->query("ALTER TABLE `$old` RENAME TO `$new`") === false || !self::table_exists($new)) {
                $error = "Could not rename $old to $new: " . ($wpdb->last_error ?: 'unknown database error') . '.';
                foreach (array_reverse($done) as $pair) {
                    $wpdb->query("ALTER TABLE `{$pair[1]}` RENAME TO `{$pair[0]}`");
                }
                update_option(self::ERROR_OPTION, $error, false);
                error_log('[' . MDBBS_GAME_NAME . '] ' . $error . ' Nothing was moved; ' . self::LEGACY_NAME . ' was left as it was.');
                return false;
            }
            $done[] = [$old, $new];
        }
        return $notes;
    }

    /** Why the last attempt to move the old data failed, or '' if it has not. */
    public static function last_error() {
        return self::needed() ? (string) get_option(self::ERROR_OPTION, '') : '';
    }

    private static function move_options() {
        $notes = [];
        $missing = new stdClass();
        foreach (self::OPTIONS as $old => $def) {
            list($new, $autoload) = $def;
            $value = get_option($old, $missing);
            if ($value === $missing) continue;
            if (get_option($new, $missing) !== $missing) {
                $notes[] = "Option $new already existed; $old was kept beside it.";
                continue;
            }
            if (add_option($new, $value, '', $autoload)) {
                delete_option($old);
            } else {
                $notes[] = "Could not copy option $old to $new.";
            }
        }
        return $notes;
    }

    /**
     * Titles of the game's pages, menu and menu items that still carry the old name exactly as
     * the old plugin wrote it. URLs (slugs) are not touched, so links and bookmarks keep working.
     */
    private static function retitle() {
        $old = self::LEGACY_NAME;
        $new = MDBBS_GAME_NAME;

        $ids = get_option('mdbbs_page_ids', []);
        if (is_array($ids)) {
            foreach (MDBBS_UI::PAGES as $key => $def) {
                if (empty($ids[$key])) continue;
                $page = get_post((int) $ids[$key]);
                if (!$page || $page->post_type !== 'page') continue;
                $was = str_replace($new, $old, $def[0]);
                if ($page->post_title === $was) wp_update_post(['ID' => $page->ID, 'post_title' => $def[0]]);
            }
        }

        $menu = wp_get_nav_menu_object($old);
        if ($menu && !wp_get_nav_menu_object($new)) {
            wp_update_term($menu->term_id, 'nav_menu', ['name' => $new]);
            foreach ((array) wp_get_nav_menu_items($menu->term_id) as $item) {
                if ($item && $item->post_title === $old) wp_update_post(['ID' => (int) $item->ID, 'post_title' => $new]);
            }
        }

        $nav_id = (int) get_option('mdbbs_nav_post_id');
        $nav = $nav_id ? get_post($nav_id) : null;
        if ($nav && $nav->post_type === 'wp_navigation') {
            $update = ['ID' => $nav->ID];
            if ($nav->post_title === $old) $update['post_title'] = $new;
            $label_old = '"label":' . wp_json_encode($old);
            if (strpos($nav->post_content, $label_old) !== false) {
                $update['post_content'] = wp_slash(str_replace($label_old, '"label":' . wp_json_encode($new), $nav->post_content));
            }
            if (count($update) > 1) wp_update_post($update);
        }
    }
}
