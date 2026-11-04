<?php
if (!defined('ABSPATH')) exit;

/**
 * Registers the game shortcodes. Each renders a shared frame (title, status, navigation,
 * notices, footer) around a view from includes/frontend/views. The home page, Gazette and
 * Hall of Fame are public; the Play page needs a signed-in user with a player name.
 *
 * The Gazette and Hall of Fame take limit="10" and compact="1", for a sidebar widget.
 */
class MDBBS_Shortcodes {
    /** Pages readable without signing in. */
    const PUBLIC_PAGES = ['home', 'gazette', 'hof'];

    /** Views that have a compact (widget) form. */
    const COMPACT_PAGES = ['gazette', 'hof'];

    /** Attributes the current shortcode was called with; views read them through att(). */
    private static $atts = [];

    public static function register() {
        foreach (MDBBS_UI::shortcodes() as $tag => $key) {
            add_shortcode($tag, function ($atts) use ($key) {
                return MDBBS_Shortcodes::render($key, is_array($atts) ? $atts : []);
            });
        }
    }

    public static function att($name, $default = '') {
        return isset(self::$atts[$name]) ? self::$atts[$name] : $default;
    }

    /** Whether the current shortcode asked for compact output. */
    public static function compact() {
        $v = strtolower((string) self::att('compact', ''));
        return $v !== '' && !in_array($v, ['0', 'false', 'no', 'off'], true);
    }

    /** The limit attribute, clamped between 1 and $max. */
    public static function limit($default, $max = 100) {
        $n = (int) self::att('limit', $default);
        return max(1, min($max, $n ?: $default));
    }

    public static function render($key, $atts = []) {
        if (!isset(MDBBS_UI::PAGES[$key])) return '';
        self::$atts = $atts;
        // Shortcodes also run in the block editor's previews; keep those cheap.
        if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return '<p>[' . esc_html(MDBBS_UI::PAGES[$key][0]) . ']</p>';
        }
        wp_enqueue_style('maddog-bbs-slots');
        wp_enqueue_script('maddog-bbs-slots');

        $p = is_user_logged_in() ? MDBBS_Player::current() : null;
        // Views are chosen from this fixed list only; nothing from the request names a file.
        $view = MDBBS_PATH . 'includes/frontend/views/' . $key . '.php';

        ob_start();
        if (self::compact() && in_array($key, self::COMPACT_PAGES, true)) {
            // A widget: the panel and nothing else.
            echo '<div class="mdbbs-game mdbbs-page-' . esc_attr($key) . ' mdbbs-compact">';
            $compact = true;
            include $view;
            echo '</div>';
            return ob_get_clean();
        }

        $compact = false;
        echo '<div class="mdbbs-game mdbbs-page-' . esc_attr($key) . '">';
        echo '<div class="mdbbs-title">' . esc_html(MDBBS_GAME_NAME) . '</div>';
        if ($p) echo MDBBS_UI::status_bar($p);
        echo MDBBS_UI::nav($key);
        echo MDBBS_UI::render_flashes();

        if ($key === 'play' && !is_user_logged_in()) {
            echo '<div class="mdbbs-panel"><h2>Sign in to play</h2><p>Sign in to your account to pick a player name and pull the handle.'
                . ' The <a href="' . esc_url(MDBBS_UI::url('home')) . '">home page</a> explains how to play.</p>'
                . MDBBS_UI::sign_in_buttons() . '</div>';
        } elseif ($key === 'play' && !$p) {
            include MDBBS_PATH . 'includes/frontend/views/_name-form.php';
        } else {
            include $view;
        }
        echo MDBBS_UI::footer();
        echo '</div>';
        return ob_get_clean();
    }
}
