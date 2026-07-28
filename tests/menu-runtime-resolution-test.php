<?php
declare(strict_types=1);

define('ABSPATH', __DIR__);
define('AIHL_TEXT_DOMAIN', 'ai_html');

$GLOBALS['test_menu_locations'] = array();
$GLOBALS['test_menus'] = array();
$GLOBALS['test_nav_args'] = array();

function add_action($hook, $callback): void {}
function register_nav_menus($locations): void {}
function __($value, $domain = null): string { return (string) $value; }
function apply_filters($hook, $value) { return $value; }
function sanitize_key($value): string { return preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $value)) ?? ''; }
function sanitize_title($value): string {
	$value = strtolower((string) $value);
	$value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
	return trim($value, '-');
}
function sanitize_text_field($value): string { return trim(strip_tags((string) $value)); }
function absint($value): int { return abs((int) $value); }
function get_nav_menu_locations(): array { return $GLOBALS['test_menu_locations']; }
function wp_get_nav_menus($args = array()): array { return $GLOBALS['test_menus']; }
function wp_get_nav_menu_object($menu_id) {
	foreach ($GLOBALS['test_menus'] as $menu) {
		if ((int) $menu->term_id === (int) $menu_id) {
			return $menu;
		}
	}
	return false;
}
function has_nav_menu($location): bool { return !empty($GLOBALS['test_menu_locations'][$location]); }
function wp_nav_menu($args) {
	$GLOBALS['test_nav_args'] = $args;
	return '<ul class="' . $args['menu_class'] . '"><li>Menu</li></ul>';
}

require dirname(__DIR__) . '/inc/theme/menu.php';
require dirname(__DIR__) . '/inc/theme/integration-contract.php';

function menu_test_assert(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

function menu_test_term(int $id, string $name, string $slug, int $count = 3): object {
	return (object) array(
		'term_id' => $id,
		'name'    => $name,
		'slug'    => $slug,
		'count'   => $count,
	);
}

$GLOBALS['test_menus'] = array(
	menu_test_term(11, 'Primary', 'primary'),
	menu_test_term(12, 'Utility', 'utility'),
);
$GLOBALS['test_menu_locations'] = array('topic' => 11, 'naviga' => 12);
$resolved = aihl_resolve_nav_menu('topic');
menu_test_assert($resolved['menu_id'] === 11 && $resolved['source'] === 'assigned', 'Direct assignments must win.');

$GLOBALS['test_menu_locations'] = array('naviga' => 12);
$resolved = aihl_resolve_nav_menu('topic');
menu_test_assert($resolved['menu_id'] === 12 && $resolved['source'] === 'location_alias', 'Header alias must recover a compatible assigned menu.');

$GLOBALS['test_menus'] = array(
	menu_test_term(21, 'Footer links', 'footer-links'),
	menu_test_term(22, 'Menu principale', 'menu-principale'),
);
$GLOBALS['test_menu_locations'] = array();
$footer = aihl_resolve_nav_menu('utili');
$header = aihl_resolve_nav_menu('topic');
menu_test_assert($footer['menu_id'] === 21 && $footer['source'] === 'menu_fallback', 'Footer menu must be recognized by its semantic name.');
menu_test_assert($header['menu_id'] === 22 && $header['source'] === 'menu_fallback', 'Header menu must be recognized by its semantic name.');

$GLOBALS['test_menus'] = array(
	menu_test_term(31, 'Main alpha', 'main-alpha'),
	menu_test_term(32, 'Main beta', 'main-beta'),
);
$ambiguous = aihl_resolve_nav_menu('topic');
menu_test_assert($ambiguous['menu_id'] === 0, 'Equally ranked menus must not be selected arbitrarily.');

$GLOBALS['test_menus'] = array(menu_test_term(41, 'Only menu', 'only-menu'));
$html = aihl_render_dynamic_component('smart-menu', array(
	'location' => 'topic',
	'class'    => 'site-navigation',
	'depth'    => '2',
));
menu_test_assert(str_contains($html, 'site-navigation'), 'Canvas smart-menu must render a safely resolved menu.');
menu_test_assert(($GLOBALS['test_nav_args']['menu'] ?? 0) === 41, 'Unassigned menu fallback must render by menu ID.');
menu_test_assert(!isset($GLOBALS['test_nav_args']['theme_location']), 'Unassigned menu fallback must not use an empty location.');

echo "menu-runtime-resolution-test: ok\n";
