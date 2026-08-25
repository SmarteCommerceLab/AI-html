<?php
declare(strict_types=1);

define('ABSPATH', __DIR__);
define('AIHL_TEXT_DOMAIN', 'ai_html');
define('AIHL_OPTION_BASE', 'aihl');
define('AIHL_VERSION', 'test');

$GLOBALS['manifest_test_options'] = array();
$GLOBALS['manifest_test_clock'] = 1700000000;

function add_action($hook, $callback): void {}
function get_option($name, $default = false) { return $GLOBALS['manifest_test_options'][$name] ?? $default; }
function update_option($name, $value, $autoload = null): bool { $GLOBALS['manifest_test_options'][$name] = $value; return true; }
function sanitize_text_field($value): string { return trim(strip_tags((string) $value)); }
function sanitize_key($value): string { return preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $value)) ?? ''; }
function __($value, $domain = null): string { return (string) $value; }
function get_current_user_id(): int { return 7; }
function wp_json_encode($value, $flags = 0): string { return (string) json_encode($value, $flags); }
function wp_generate_uuid4(): string { static $counter = 0; $counter++; return sprintf('00000000-0000-4000-8000-%012d', $counter); }
function aihl_get_theme_integration_manifest(): array { return array('site' => array('name' => 'Test')); }

require dirname(__DIR__) . '/inc/theme/manifest-json.php';

function manifest_test_assert(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

$snapshot = aihl_manifest_json_store_snapshot('Prima versione');
manifest_test_assert($snapshot['label'] === 'Prima versione', 'Lo snapshot conserva una label sanificata.');
manifest_test_assert(isset($snapshot['manifest']['site']['name']), 'Lo snapshot contiene il manifest derivato.');
manifest_test_assert(aihl_manifest_json_find_version($snapshot['id']) !== null, 'La versione e recuperabile per ID.');

for ($index = 0; $index < 24; $index++) {
	aihl_manifest_json_store_snapshot('Versione ' . $index);
}
manifest_test_assert(count(aihl_manifest_json_versions()) === 20, 'La cronologia mantiene al massimo 20 versioni.');
manifest_test_assert(count(array_unique(array_column(aihl_manifest_json_versions(), 'id'))) === 20, 'Ogni versione possiede un ID univoco.');

echo "manifest-json-admin-test: ok\n";
