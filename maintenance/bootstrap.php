<?php
/**
 * Loads WordPress for the CLI maintenance script.
 *
 * Pass the WordPress folder (the one holding wp-load.php) explicitly, as the Maintenance screen
 * shows:
 *   php daily_maintenance.php --wp-path=/path/to/wordpress
 * or set MDBBS_WP_PATH in the cron job's environment.
 *
 * Without either, it falls back to walking up from the path the script was run by, which only
 * finds the right site when the plugin really sits inside it. Through a symlinked plugin folder,
 * PHP's __DIR__ is the link's target, so walking up from there could land on a wp-load.php that
 * has nothing to do with this site; the walk starts from the path as typed instead, and insists
 * on a complete WordPress install (wp-load.php, wp-settings.php and wp-includes/version.php).
 */
if (PHP_SAPI !== 'cli') exit("CLI only.\n");

$mdbbs_wp_path = function () {
    global $argv;
    foreach (array_slice((array) $argv, 1) as $arg) {
        if (strpos($arg, '--wp-path=') === 0) return substr($arg, 10);
    }
    $env = getenv('MDBBS_WP_PATH');
    // WPBBS_WP_PATH is the name from before the rename to Maddog BBS Slots; it still works.
    if ($env === false || $env === '') $env = getenv('WPBBS_WP_PATH');
    return $env !== false ? $env : '';
};

$mdbbs_is_wp = function ($dir) {
    return is_file($dir . '/wp-load.php') && is_file($dir . '/wp-settings.php') && is_file($dir . '/wp-includes/version.php');
};

$dir = '';
$given = $mdbbs_wp_path();
if ($given !== '') {
    $dir = realpath($given);
    if ($dir === false || !$mdbbs_is_wp($dir)) {
        fwrite(STDERR, "No WordPress install found at $given (looked for wp-load.php, wp-settings.php and wp-includes/).\n");
        exit(1);
    }
} else {
    // The script's path as it was typed, not resolved through symlinks.
    $start = isset($argv[0]) && $argv[0] !== '' ? $argv[0] : __FILE__;
    if ($start[0] !== '/' && !preg_match('#^[A-Za-z]:[\\\\/]#', $start)) $start = getcwd() . '/' . $start;
    $walk = dirname($start);
    while ($walk !== dirname($walk) && !$mdbbs_is_wp($walk)) {
        $walk = dirname($walk);
    }
    if ($mdbbs_is_wp($walk)) $dir = $walk;
}

if ($dir === '') {
    fwrite(STDERR, "Could not locate WordPress. Run with --wp-path=/path/to/wordpress (the folder holding wp-load.php).\n");
    exit(1);
}
unset($mdbbs_wp_path, $mdbbs_is_wp, $given, $start, $walk);
require_once $dir . '/wp-load.php';
if (!class_exists('MDBBS_Maintenance')) {
    fwrite(STDERR, "The Maddog BBS Slots plugin is not active.\n");
    exit(1);
}
