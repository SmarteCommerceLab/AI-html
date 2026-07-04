<?php
declare(strict_types=1);

define('ABSPATH', __DIR__);
define('AIHL_TEXT_DOMAIN', 'ai_html');
define('AIHL_OPTION_BASE', 'ai_html');
define('AIHL_THEME_NAME', 'AI-HTML');
define('AIHL_VERSION', '1.4.0');

$GLOBALS['aihl_test_options'] = array();
$GLOBALS['aihl_test_context'] = array(
	'front_page' => false,
	'page' => '',
	'logged_in' => false,
);

class WP_Error {
	private string $message;

	public function __construct(string $code, string $message, array $data = array()) {
		$this->message = $message;
	}

	public function get_error_message(): string {
		return $this->message;
	}
}

class WP_REST_Request implements ArrayAccess {
	public function offsetExists(mixed $offset): bool { return false; }
	public function offsetGet(mixed $offset): mixed { return null; }
	public function offsetSet(mixed $offset, mixed $value): void {}
	public function offsetUnset(mixed $offset): void {}
	public function get_json_params(): array { return array(); }
}

class WP_REST_Server {
	public const READABLE = 'GET';
	public const CREATABLE = 'POST';
	public const EDITABLE = 'PUT';
	public const DELETABLE = 'DELETE';
}

function add_action($hook, $callback, $priority = 10, $accepted_args = 1): void {}
function add_filter($hook, $callback, $priority = 10, $accepted_args = 1): void {}
function register_rest_route($namespace, $route, $args): void {}
function __($value, $domain = null): string { return (string) $value; }
function sanitize_key($value): string { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $value)) ?? ''; }
function sanitize_text_field($value): string { return trim(strip_tags((string) $value)); }
function wp_generate_password($length = 12, $special_chars = true): string { return str_repeat('x', (int) $length); }
function wp_get_current_user(): object { return (object) array('user_login' => 'tester'); }
function current_time($type): string { return '2026-06-09 12:00:00'; }
function get_option($key, $default = false) { return $GLOBALS['aihl_test_options'][$key] ?? $default; }
function update_option($key, $value, $autoload = null): bool { $GLOBALS['aihl_test_options'][$key] = $value; return true; }
function delete_option($key): bool { unset($GLOBALS['aihl_test_options'][$key]); return true; }
function esc_attr($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function is_wp_error($value): bool { return $value instanceof WP_Error; }
function is_front_page(): bool { return (bool) $GLOBALS['aihl_test_context']['front_page']; }
function is_home(): bool { return false; }
function is_single(): bool { return false; }
function is_archive(): bool { return false; }
function is_search(): bool { return false; }
function is_404(): bool { return false; }
function is_user_logged_in(): bool { return (bool) $GLOBALS['aihl_test_context']['logged_in']; }
function is_page($value): bool { return (string) $GLOBALS['aihl_test_context']['page'] === (string) $value; }
function is_singular($value): bool { return false; }
function is_page_template($value): bool { return false; }
function is_category($value): bool { return false; }
function is_tag($value): bool { return false; }

require dirname(__DIR__) . '/inc/admin/code-slots.php';

function assert_true(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

$hooks = aihl_code_slots_hooks();
assert_true(isset($hooks['header_full']) && !empty($hooks['header_full']['override']), 'header_full deve essere override');

$GLOBALS['aihl_test_context']['page'] = 'landing';
assert_true(aihl_code_slot_context_matches('front_page, page:landing'), 'contesti CSV funzionanti');
assert_true(aihl_code_slot_context_matches('!logged_in'), 'negazione contesto');

$saved = aihl_code_slots_save(array(
	'id' => 'test-header',
	'label' => 'Test Header',
	'hook' => 'header_full',
	'type' => 'mixed',
	'context' => 'global',
	'priority' => 10,
	'active' => true,
	'code' => '<header data-test-slot>Header</header>',
	'css' => '.test{display:block}',
	'js' => 'window.testSlot=true;',
));

assert_true(!is_wp_error($saved), 'salvataggio slot');
assert_true(aihl_code_slot_has_override('header_full'), 'override attivo');
$GLOBALS['aihl_test_options'][AIHL_OPTION_BASE . '_general'] = array(
	'header_render_mode' => 'canvas',
	'footer_render_mode' => 'native',
);
assert_true('canvas' === aihl_get_structure_render_mode('header'), 'modalita header Canvas');
assert_true(aihl_should_render_canvas_structure('header'), 'Canvas header selezionato e disponibile');
assert_true(!aihl_should_render_canvas_structure('footer'), 'footer nativo non usa Canvas');
$GLOBALS['aihl_test_options'][AIHL_OPTION_BASE . '_general']['header_render_mode'] = 'native';
assert_true(!aihl_should_render_canvas_structure('header'), 'header nativo ignora lo slot attivo');

ob_start();
aihl_render_code_slot('header_full');
$output = ob_get_clean();

assert_true(str_contains($output, 'data-aihl-slot="test-header-css"'), 'CSS Mixed renderizzato');
assert_true(str_contains($output, '<header data-test-slot>'), 'HTML Mixed renderizzato');
assert_true(str_contains($output, 'data-aihl-slot="test-header-js"'), 'JS Mixed renderizzato');

echo "OK AI-HTML Code Slots: sorgente nativa/Canvas, override, contesti e Mixed verificati\n";
