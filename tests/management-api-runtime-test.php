<?php
declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');
define('AIHL_TEXT_DOMAIN', 'ai_html');
define('AIHL_OPTION_BASE', 'ai_html');
define('AIHL_VERSION', '1.12.0');
define('AIHL_UPDATE_ENDPOINT', 'https://repository.example.test/theme.json');

$GLOBALS['management_options'] = array(
	'blogname' => 'Old name',
	'blogdescription' => 'Old description',
	'blog_public' => 1,
	'show_on_front' => 'posts',
	'page_on_front' => 0,
	'page_for_posts' => 0,
	'permalink_structure' => '',
	AIHL_OPTION_BASE . '_general' => array(),
);
$GLOBALS['management_slots'] = array(
	'header-main' => array('id' => 'header-main', 'hook' => 'header_full', 'active' => true),
);

class WP_Error {
	public function __construct(public string $code, public string $message, public array $data = array()) {}
}
class WP_REST_Request {
	public function __construct(private array $body = array(), private array $params = array()) {}
	public function get_json_params(): array { return $this->body; }
	public function get_param(string $name) { return $this->params[$name] ?? $this->body[$name] ?? null; }
}
class WP_REST_Server {
	public const READABLE = 'GET';
	public const EDITABLE = 'PUT';
	public const CREATABLE = 'POST';
}
function add_action($hook, $callback): void {}
function register_rest_route($namespace, $route, $args): void {}
function __($value, $domain = null): string { return (string) $value; }
function get_option($key, $default = false) { return $GLOBALS['management_options'][$key] ?? $default; }
function update_option($key, $value, $autoload = null): bool { $GLOBALS['management_options'][$key] = $value; return true; }
function sanitize_text_field($value): string { return trim(strip_tags((string) $value)); }
function sanitize_key($value): string { return preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $value)) ?? ''; }
function absint($value): int { return abs((int) $value); }
function get_post_type($id): string { return in_array((int) $id, array(10, 20), true) ? 'page' : 'post'; }
function flush_rewrite_rules($hard = true): void {}
function rest_ensure_response($value) { return $value; }
function rest_url($path = ''): string { return 'https://example.test/wp-json/' . ltrim($path, '/'); }
function aihl_code_slots_get($id) { return $GLOBALS['management_slots'][$id] ?? null; }
function aihl_get_structure_render_mode($area): string {
	$options = get_option(AIHL_OPTION_BASE . '_general', array());
	return (string) ($options[$area . '_render_mode'] ?? 'native');
}
function aihl_get_canvas_override_slot($area): ?array {
	$options = get_option(AIHL_OPTION_BASE . '_general', array());
	$id = (string) ($options[$area . '_canvas_slot_id'] ?? '');
	return $id !== '' ? aihl_code_slots_get($id) : null;
}

require dirname(__DIR__) . '/inc/theme-option-registry.php';
require dirname(__DIR__) . '/inc/integrations/management-api.php';

function runtime_assert(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

$matrix = aihl_sbm_option_compliance_matrix();
runtime_assert(71 === count($matrix), 'matrice compliance non copre tutte le opzioni');
runtime_assert(
	0 === count(array_filter($matrix, static fn(array $option): bool => 'unclassified' === $option['classification'])),
	'matrice compliance contiene opzioni non classificate'
);
runtime_assert('visual' === $matrix['footer_columns_count']['classification'], 'layout colonne footer non classificato come visuale');
runtime_assert('content' === $matrix['footer_cta_title']['classification'], 'testo CTA footer non classificato come contenuto');

$site = aihl_api_update_site_settings(new WP_REST_Request(array(
	'blogname' => ' New <b>name</b> ',
	'blog_public' => false,
	'show_on_front' => 'page',
	'page_on_front' => 10,
	'page_for_posts' => 20,
	'permalink_structure' => '/%postname%/',
)));
runtime_assert(!($site instanceof WP_Error), 'aggiornamento impostazioni sito');
runtime_assert($site['settings']['blogname'] === 'New name', 'nome sito sanitizzato');
runtime_assert($site['settings']['blog_public'] === false, 'visibilita sito aggiornata');
runtime_assert($site['settings']['page_on_front'] === 10, 'front page aggiornata');

$invalid = aihl_api_update_site_settings(new WP_REST_Request(array('permalink_structure' => '%postname%')));
runtime_assert($invalid instanceof WP_Error && $invalid->code === 'aihl_invalid_permalink', 'permalink non valido rifiutato');

$canvas = aihl_api_update_canvas(new WP_REST_Request(array(
	'header' => array('mode' => 'canvas', 'slot_id' => 'header-main'),
	'footer' => array('mode' => 'native', 'slot_id' => ''),
)));
runtime_assert(!($canvas instanceof WP_Error), 'aggiornamento Canvas');
runtime_assert($canvas['canvas']['header']['mode'] === 'canvas', 'modalita Canvas salvata');
runtime_assert($canvas['canvas']['header']['resolved_slot_id'] === 'header-main', 'slot Canvas risolto');

$invalid_canvas = aihl_api_update_canvas(new WP_REST_Request(array(
	'footer' => array('mode' => 'canvas', 'slot_id' => 'header-main'),
)));
runtime_assert($invalid_canvas instanceof WP_Error && $invalid_canvas->code === 'aihl_invalid_canvas_slot', 'slot area errata rifiutato');

echo "AI-HTML management API runtime OK\n";
