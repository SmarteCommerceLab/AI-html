<?php
declare(strict_types=1);

define('ABSPATH', __DIR__);
define('AIHL_TEXT_DOMAIN', 'ai_html');

$GLOBALS['deleted_menu_ids'] = array();
$GLOBALS['menu_delete_failure'] = 0;
$GLOBALS['theme_mods'] = array('nav_menu_locations' => array('primary' => 17));

class WP_Error {
	public function __construct(public string $code, public string $message, public array $data = array()) {}
}

function add_action($hook, $callback): void {}

function __($value, $domain = null): string {
	return (string) $value;
}

function wp_get_nav_menus($args = array()): array {
	return array((object) array('term_id' => 17), (object) array('term_id' => 23));
}

function wp_delete_nav_menu($menu_id): bool {
	$GLOBALS['deleted_menu_ids'][] = (int) $menu_id;
	return (int) $menu_id !== $GLOBALS['menu_delete_failure'];
}

function set_theme_mod($name, $value): void {
	$GLOBALS['theme_mods'][$name] = $value;
}

require dirname(__DIR__) . '/inc/theme/menu-json.php';

function assert_true(bool $condition, string $message): void {
	if (!$condition) {
		throw new RuntimeException($message);
	}
}

$result = aihl_import_menu_json_payload('{"format":"aihl-menu-json","menus":[]}', true);

assert_true(is_array($result), 'Empty explicit replacement must succeed.');
assert_true($result === array('menus' => 0, 'items' => 0, 'failed_items' => 0), 'Result must report an empty import.');
assert_true($GLOBALS['deleted_menu_ids'] === array(17, 23), 'All existing menus must be removed.');
assert_true($GLOBALS['theme_mods']['nav_menu_locations'] === array(), 'Menu locations must be cleared.');

$invalid = aihl_import_menu_json_payload('{"menus":[]}', false);
assert_true($invalid instanceof WP_Error, 'Empty additive imports must remain invalid.');

$GLOBALS['deleted_menu_ids'] = array();
$GLOBALS['menu_delete_failure'] = 23;
$GLOBALS['theme_mods']['nav_menu_locations'] = array('primary' => 17);
$failed = aihl_import_menu_json_payload('{"menus":[]}', true);
assert_true($failed instanceof WP_Error && $failed->code === 'menu_delete_failed', 'Delete failures must be reported.');
assert_true($GLOBALS['theme_mods']['nav_menu_locations'] === array('primary' => 17), 'Locations must remain assigned after a delete failure.');

echo "menu-empty-replace-test: ok\n";
