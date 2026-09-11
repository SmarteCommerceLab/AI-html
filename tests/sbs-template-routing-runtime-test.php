<?php
// Exercise the theme declaration against the real SBS routing implementation.
$sbs = $argv[1] ?? getenv('SBS_TEST_PATH');
if (!$sbs || !is_file($sbs . '/pages-register.php')) {
    fwrite(STDERR, "Pass the SBS source directory as the first argument.\n");
    exit(1);
}
define('ABSPATH', __DIR__ . '/');
define('AIHL_TEXT_DOMAIN', 'ai-html');
define('SBS_TEXT_DOMAIN', 'smart-builder-site');
define('SBS_VERSION', '1.23.0');
define('SBS_BASENAME', 'smart-builder-site/smart-builder-site.php');
define('SBS_PLUGIN_NAME', 'Smart Builder Site');
define('SBS_OPTION_BASE', 'smart_builder_site');
define('SBS_DIR_URL', 'https://example.test/plugins/smart-builder-site/');
define('SBS_DIR_PATH', rtrim($sbs, '/\\') . '/');
$hooks = $support = array();
$selected = '';
function add_action($hook, $callback, ...$args) { $GLOBALS['hooks'][$hook][] = $callback; }
function add_filter(...$args) {}
function add_theme_support($name, ...$args) { $GLOBALS['support'][$name] = $args; }
function get_theme_support($name) { return $GLOBALS['support'][$name] ?? false; }
function load_theme_textdomain(...$args) {}
function get_template_directory() { return dirname(__DIR__); }
function sanitize_file_name($name) { return basename($name); }
function apply_filters($hook, $value, ...$args) { return $value; }
function esc_html__($text, $domain) { return $text; }
function is_page() { return true; }
function get_the_ID() { return 26; }
function get_page_template_slug($id) { return $GLOBALS['selected']; }
require dirname(__DIR__) . '/inc/theme/support.php';
foreach ($hooks['after_setup_theme'] as $callback) { $callback(); }
require $sbs . '/compatibility.php';
require $sbs . '/pages-register.php';
$registered = sbs_register_page_templates(array('custom.php' => 'Custom'));
foreach (array('smart-site-home.php' => false, 'smart-site-builder.php' => false, 'smart-site-blog.php' => true) as $selected => $compose) {
    if (!isset($registered[$selected]) || !sbs_template_supports_builder($selected)
        || sbs_template_supports_compose($selected) !== $compose
        || sbs_template_include('/theme/page.php') !== SBS_DIR_PATH . 'templates/pages/' . $selected) {
        throw new RuntimeException('Broken capability or routing: ' . $selected);
    }
}
$selected = 'custom.php';
if (!isset($registered['custom.php']) || sbs_template_include('/theme/custom.php') !== '/theme/custom.php') {
    throw new RuntimeException('Unrelated theme template changed.');
}
echo "AI-HTML/SBS template routing runtime OK\n";
