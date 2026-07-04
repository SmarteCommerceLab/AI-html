<?php
declare(strict_types=1);

define('ABSPATH', __DIR__);
define('AIHL_TEXT_DOMAIN', 'ai_html');
define('AIHL_THEME_NAME', 'AI-HTML');
define('AIHL_OPTION_BASE', 'ai_html');

$GLOBALS['aihl_test_options'] = array();

class WP_Error {
	private string $code;
	private string $message;

	public function __construct(string $code, string $message) {
		$this->code = $code;
		$this->message = $message;
	}

	public function get_error_message(): string {
		return $this->message;
	}
}

class WP_REST_Request {}
class WP_REST_Server {
	public const READABLE = 'GET';
	public const CREATABLE = 'POST';
}

function add_action($hook, $callback): void {}
function register_rest_route($namespace, $route, $args): void {}
function sanitize_key($value): string {
	return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $value)) ?? '';
}
function sanitize_text_field($value): string {
	return trim(strip_tags((string) $value));
}
function sanitize_email($value): string {
	return filter_var((string) $value, FILTER_SANITIZE_EMAIL) ?: '';
}
function is_email($value): bool {
	return false !== filter_var((string) $value, FILTER_VALIDATE_EMAIL);
}
function esc_url_raw($value): string {
	$value = trim((string) $value);
	if ('' === $value || str_starts_with($value, '/')) {
		return $value;
	}
	return false !== filter_var($value, FILTER_VALIDATE_URL) ? $value : '';
}
function wp_kses_post($value): string {
	return strip_tags((string) $value, '<p><a><iframe>');
}
function __($value, $domain = null): string {
	return (string) $value;
}
function wp_json_encode($value, $flags = 0): string {
	return json_encode($value, $flags | JSON_THROW_ON_ERROR);
}
function get_option($key, $default = false) {
	return $GLOBALS['aihl_test_options'][$key] ?? $default;
}
function update_option($key, $value, $autoload = null): bool {
	$GLOBALS['aihl_test_options'][$key] = $value;
	return true;
}
function aihtml_option_value($field, $default = '') {
	$options = get_option(AIHL_OPTION_BASE . '_general', array());
	return is_array($options) && array_key_exists($field, $options) ? $options[$field] : $default;
}
function is_wp_error($value): bool {
	return $value instanceof WP_Error;
}

require dirname(__DIR__) . '/inc/integrations/ai-api.php';
require dirname(__DIR__) . '/inc/theme/options-json.php';

function assert_true(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

$whitelist = aihl_ai_options_whitelist();
$required = array(
	'site_logo_url',
	'site_logo_transparent_url',
	'site_logo_light_url',
	'footer_logo_url',
	'header_render_mode',
	'header_structure',
	'header_overlay_mode',
	'header_nav_letter_spacing',
	'header_topbar_scroll_behavior',
	'mobile_rail_autohide',
	'footer_background_remote_url',
	'footer_cta_btn2_url',
	'contatti_maps',
	'contactform_contacts',
	'mailchip_footer',
	'footer_render_mode',
);

foreach ($required as $field) {
	assert_true(isset($whitelist[$field]), "campo whitelist mancante: {$field}");
}

assert_true('0.2' === aihl_ai_sanitize_option_value(0.8, array('type' => 'float', 'min' => 0, 'max' => 0.2)), 'clamp float');
assert_true(null === aihl_ai_sanitize_option_value('invalid', array('type' => 'enum', 'values' => array('solid'))), 'enum non valido');
assert_true(null === aihl_ai_sanitize_option_value('not-a-url', array('type' => 'url')), 'URL non valido');

$payload = json_encode(array(
	'options' => array(
		'site_logo_url' => 'https://cdn.example.com/logo.png',
		'header_structure' => 'topbar-nav',
		'header_render_mode' => 'canvas',
		'header_nav_letter_spacing' => 0.04,
		'footer_background_opacity' => 0.16,
		'footer_cta_btn2_url' => '/servizi/',
		'campo_inesistente' => 'rifiutato',
	),
), JSON_THROW_ON_ERROR);

$result = aihl_apply_options_json($payload);
assert_true(!is_wp_error($result), 'import opzioni');
assert_true(6 === $result['applied_count'], 'numero campi applicati');
assert_true(array('campo_inesistente') === $result['rejected'], 'campo sconosciuto rifiutato');

$saved = get_option(AIHL_OPTION_BASE . '_general', array());
assert_true('https://cdn.example.com/logo.png' === $saved['site_logo_url'], 'logo URL salvato');
assert_true('topbar-nav' === $saved['header_structure'], 'header salvato');
assert_true('canvas' === $saved['header_render_mode'], 'sorgente header salvata');
assert_true('0.04' === $saved['header_nav_letter_spacing'], 'float serializzato');

$export = json_decode(aihl_export_options_json(), true, 512, JSON_THROW_ON_ERROR);
assert_true('aihl-options-json' === $export['format'], 'formato export');
assert_true(isset($export['options']['footer_logo_url']), 'export include footer logo');
assert_true('https://cdn.example.com/logo.png' === $export['options']['site_logo_url'], 'export conserva logo');

echo 'OK AI-HTML options JSON: ' . count($whitelist) . " campi verificati\n";
