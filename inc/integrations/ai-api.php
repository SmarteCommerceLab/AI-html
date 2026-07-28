<?php
/**
 * AI-HTML Theme - AI REST API
 * Namespace: /aihtml/v1/ai/
 *
 * API del TEMA per agenti AI. Gestisce cio che e di competenza del tema:
 * opzioni globali (header, footer, contatti, CTA), menu WordPress, pagine.
 *
 * Autenticazione: riusa il sistema API key di SBS (sbs_ai_rest_can_*).
 * Se SBS non e attivo, fallback su capability manage_options.
 */
if (!defined('ABSPATH')) {
	exit;
}

add_action('rest_api_init', 'aihl_ai_register_rest_routes');

function aihl_ai_register_rest_routes() {

	/* ── Context: info sito dal punto di vista tema ── */
	register_rest_route('aihtml/v1', '/ai/context', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => 'aihl_ai_can_read',
		'callback'            => 'aihl_ai_rest_context',
	));

	/* ── Theme options (header, footer, contatti, CTA) ── */
	register_rest_route('aihtml/v1', '/ai/options', array(
		array(
			'methods'             => WP_REST_Server::READABLE,
			'permission_callback' => 'aihl_ai_can_read',
			'callback'            => 'aihl_ai_rest_get_options',
		),
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => 'aihl_ai_can_write',
			'callback'            => 'aihl_ai_rest_update_options',
		),
	));

	/* ── Menu JSON (delega alle funzioni menu-json del tema) ── */
	register_rest_route('aihtml/v1', '/ai/menus', array(
		array(
			'methods'             => WP_REST_Server::READABLE,
			'permission_callback' => 'aihl_ai_can_read',
			'callback'            => 'aihl_ai_rest_get_menus',
		),
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => 'aihl_ai_can_write',
			'callback'            => 'aihl_ai_rest_import_menus',
		),
	));

	/* ── Pagine WP ── */
	register_rest_route('aihtml/v1', '/ai/pages', array(
		array(
			'methods'             => WP_REST_Server::READABLE,
			'permission_callback' => 'aihl_ai_can_read',
			'callback'            => 'aihl_ai_rest_list_pages',
		),
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => 'aihl_ai_can_write',
			'callback'            => 'aihl_ai_rest_create_page',
		),
	));
	register_rest_route('aihtml/v1', '/ai/pages/(?P<id>\d+)', array(
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'permission_callback' => 'aihl_ai_can_write',
			'callback'            => 'aihl_ai_rest_update_page',
			'args'                => array(
				'id' => array('type' => 'integer', 'minimum' => 1, 'required' => true),
			),
		),
		array(
			'methods'             => WP_REST_Server::DELETABLE,
			'permission_callback' => 'aihl_ai_can_write',
			'callback'            => 'aihl_ai_rest_trash_page',
			'args'                => array(
				'id' => array('type' => 'integer', 'minimum' => 1, 'required' => true),
			),
		),
	));
	register_rest_route('aihtml/v1', '/ai/pages/(?P<id>\d+)/restore', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'permission_callback' => 'aihl_ai_can_write',
		'callback'            => 'aihl_ai_rest_restore_page',
		'args'                => array(
			'id' => array('type' => 'integer', 'minimum' => 1, 'required' => true),
		),
	));
	register_rest_route('aihtml/v1', '/ai/pages/(?P<id>\d+)/status', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'permission_callback' => 'aihl_ai_can_publish',
		'callback'            => 'aihl_ai_rest_update_page_status',
		'args'                => array(
			'id' => array('type' => 'integer', 'minimum' => 1, 'required' => true),
		),
	));

	register_rest_route('aihtml/v1', '/ai/site/front-page', array(
		array(
			'methods'             => WP_REST_Server::READABLE,
			'permission_callback' => 'aihl_ai_can_read',
			'callback'            => 'aihl_ai_rest_get_front_page',
		),
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => 'aihl_ai_can_publish',
			'callback'            => 'aihl_ai_rest_update_front_page',
		),
	));

	register_rest_route('aihtml/v1', '/ai/auth/capabilities', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => 'aihl_ai_can_read',
		'callback'            => 'aihl_ai_rest_auth_capabilities',
	));

	/* ── Schema opzioni: descrive all'AI quali campi puo modificare ── */
	register_rest_route('aihtml/v1', '/ai/options/schema', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => 'aihl_ai_can_read',
		'callback'            => 'aihl_ai_rest_options_schema',
	));

	register_rest_route('aihtml/v1', '/ai/openapi', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => 'aihl_ai_can_read',
		'callback'            => function () {
			return rest_ensure_response(aihl_ai_openapi_payload());
		},
	));

	register_rest_route('aihtml/v1', '/openapi', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => 'aihl_ai_can_read',
		'callback'            => function () {
			return rest_ensure_response(aihl_ai_openapi_payload());
		},
	));

	register_rest_route('aihtml/v1', '/ai/integration-manifest', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => 'aihl_ai_can_read',
		'callback'            => function () {
			return rest_ensure_response(aihl_get_theme_integration_manifest());
		},
	));

	register_rest_route('aihtml/v1', '/ai/addons', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => 'aihl_ai_can_read',
		'callback'            => function () {
			return rest_ensure_response(array('addons' => aihl_get_addon_integrations()));
		},
	));
}

/* ============================================================================
 * Auth — usa il core neutro condiviso smart_ai_* (caricato da ai-auth-core.php).
 * Il tema funziona standalone: il core e incluso nel tema stesso.
 * ============================================================================ */

function aihl_ai_can_read(WP_REST_Request $request): bool {
	if (function_exists('smart_ai_can_read')) {
		return smart_ai_can_read($request);
	}
	return current_user_can('manage_options');
}

function aihl_ai_can_write(WP_REST_Request $request): bool {
	if (function_exists('smart_ai_can_write')) {
		return smart_ai_can_write($request);
	}
	return current_user_can('edit_theme_options');
}

function aihl_ai_can_publish(WP_REST_Request $request): bool {
	if (function_exists('smart_ai_can_publish')) {
		return smart_ai_can_publish($request);
	}
	return current_user_can('publish_pages');
}

function aihl_ai_rest_auth_capabilities(WP_REST_Request $request) {
	return rest_ensure_response(array(
		'read'    => aihl_ai_can_read($request),
		'write'   => aihl_ai_can_write($request),
		'publish' => aihl_ai_can_publish($request),
		'update_themes' => current_user_can('update_themes'),
	));
}

/* ============================================================================
 * Schema opzioni tema: whitelist dei campi modificabili via API
 * ============================================================================ */

if (!function_exists('aihl_theme_option_registry')) {
	require_once dirname(__DIR__) . '/theme-option-registry.php';
}

function aihl_ai_options_whitelist(): array {
	return function_exists('aihl_theme_option_registry') ? aihl_theme_option_registry() : array();
}

function aihl_ai_rest_options_schema() {
	$whitelist = aihl_ai_options_whitelist();
	$schema = array();
	foreach ($whitelist as $field => $def) {
		$schema[$field] = array(
			'type'  => $def['type'],
			'group' => $def['group'],
			'values' => $def['values'] ?? null,
			'min'   => $def['min'] ?? null,
			'max'   => $def['max'] ?? null,
			'current' => aihtml_option_value($field, ''),
		);
	}
	return rest_ensure_response(array(
		'theme'  => AIHL_THEME_NAME,
		'option_key' => AIHL_OPTION_BASE . '_general',
		'fields' => $schema,
	));
}

/* ============================================================================
 * Context
 * ============================================================================ */

function aihl_ai_rest_context() {
	$menus = array();
	foreach (wp_get_nav_menus(array('hide_empty' => false)) as $menu) {
		$menus[] = array(
			'term_id' => (int) $menu->term_id,
			'name'    => $menu->name,
			'slug'    => $menu->slug,
			'count'   => (int) $menu->count,
		);
	}

	$locations = get_registered_nav_menus();
	$assigned  = get_nav_menu_locations();

	return rest_ensure_response(array(
		'theme' => array(
			'name'    => AIHL_THEME_NAME,
			'version' => AIHL_VERSION,
		),
		'site' => array(
			'name'        => get_bloginfo('name'),
			'description' => get_bloginfo('description'),
			'url'         => home_url('/'),
			'language'    => get_bloginfo('language'),
		),
		'menus'             => $menus,
		'menu_locations'    => $locations,
		'assigned_locations' => $assigned,
		'footer_variant'    => aihtml_option_value('footer_variant', 'enterprise'),
		'header_structure'  => aihtml_option_value('header_structure', 'standard'),
		'header_render_mode' => function_exists('aihl_get_structure_render_mode') ? aihl_get_structure_render_mode('header') : 'native',
		'footer_render_mode' => function_exists('aihl_get_structure_render_mode') ? aihl_get_structure_render_mode('footer') : 'native',
		'canvas_structures' => array(
			'header_available' => function_exists('aihl_code_slot_has_override') && aihl_code_slot_has_override('header_full'),
			'footer_available' => function_exists('aihl_code_slot_has_override') && aihl_code_slot_has_override('footer_full'),
		),
		'api' => array(
			'options' => rest_url('aihtml/v1/ai/options'),
			'menus'   => rest_url('aihtml/v1/ai/menus'),
			'pages'   => rest_url('aihtml/v1/ai/pages'),
			'openapi' => rest_url('aihtml/v1/ai/openapi'),
			'integration_manifest' => rest_url('aihtml/v1/ai/integration-manifest'),
			'addons' => rest_url('aihtml/v1/ai/addons'),
		),
		'integration_contract' => function_exists('aihl_get_theme_integration_manifest')
			? aihl_get_theme_integration_manifest()
			: array(),
	));
}

/* ============================================================================
 * Theme options read/write
 * ============================================================================ */

function aihl_ai_rest_get_options() {
	$whitelist = aihl_ai_options_whitelist();
	$values = array();
	foreach (array_keys($whitelist) as $field) {
		$values[$field] = aihtml_option_value($field, '');
	}
	return rest_ensure_response(array(
		'theme'   => AIHL_THEME_NAME,
		'options' => $values,
	));
}

function aihl_ai_rest_update_options(WP_REST_Request $request) {
	$body = $request->get_json_params();
	if (!is_array($body) || empty($body['options']) || !is_array($body['options'])) {
		return new WP_Error('invalid_payload', 'Invia { "options": { campo: valore } }.', array('status' => 400));
	}

	$whitelist = aihl_ai_options_whitelist();
	$current = get_option(AIHL_OPTION_BASE . '_general', array());
	if (!is_array($current)) {
		$current = array();
	}

	$applied = array();
	$rejected = array();

	foreach ($body['options'] as $field => $value) {
		$field = sanitize_key($field);
		if (!isset($whitelist[$field])) {
			$rejected[$field] = 'campo non in whitelist';
			continue;
		}
		$def = $whitelist[$field];
		$clean = aihl_ai_sanitize_option_value($value, $def);
		if (null === $clean) {
			$rejected[$field] = 'valore non valido';
			continue;
		}
		$current[$field] = $clean;
		$applied[$field] = $clean;
	}

	update_option(AIHL_OPTION_BASE . '_general', $current, false);

	return rest_ensure_response(array(
		'saved'    => true,
		'applied'  => $applied,
		'rejected' => $rejected,
	));
}

function aihl_ai_sanitize_option_value($value, array $def) {
	switch ($def['type']) {
		case 'enum':
			$value = sanitize_text_field((string) $value);
			return in_array($value, $def['values'], true) ? $value : null;
		case 'bool':
			return (bool) filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
		case 'int':
			$value = (int) $value;
			if (isset($def['min'])) { $value = max($def['min'], $value); }
			if (isset($def['max'])) { $value = min($def['max'], $value); }
			return (string) $value;
		case 'float':
			$value = is_scalar($value) ? str_replace(',', '.', (string) $value) : '';
			if (!is_numeric($value)) {
				return null;
			}
			$value = (float) $value;
			if (isset($def['min'])) { $value = max((float) $def['min'], $value); }
			if (isset($def['max'])) { $value = min((float) $def['max'], $value); }
			return (string) $value;
		case 'url':
			$raw_url = trim((string) $value);
			$clean_url = esc_url_raw($raw_url);
			return '' === $raw_url || '' !== $clean_url ? $clean_url : null;
		case 'email':
			$email = sanitize_email((string) $value);
			return is_email($email) ? $email : null;
		case 'key':
			return sanitize_key((string) $value);
		case 'textarea':
			return sanitize_textarea_field((string) $value);
		case 'color':
			$color = sanitize_hex_color((string) $value);
			return $color ?: null;
		case 'maps_html':
			if (function_exists('aihtml_kses_embed_html')) {
				return aihtml_kses_embed_html((string) $value);
			}
			return function_exists('aihl_sanitize_maps_embed')
				? aihl_sanitize_maps_embed((string) $value)
				: wp_kses_post((string) $value);
		case 'text':
		default:
			return sanitize_text_field((string) $value);
	}
}

/* ============================================================================
 * Menu JSON — delega alle funzioni del tema (menu-json.php)
 * ============================================================================ */

function aihl_ai_openapi_field_schema(array $field): array {
	$type = (string) ($field['type'] ?? 'text');
	$schema = array('type' => 'string');

	switch ($type) {
		case 'bool':
			$schema = array('type' => 'boolean');
			break;
		case 'int':
			$schema = array('type' => 'integer');
			break;
		case 'float':
			$schema = array('type' => 'number');
			break;
		case 'url':
			$schema = array('type' => 'string', 'format' => 'uri');
			break;
		case 'email':
			$schema = array('type' => 'string', 'format' => 'email');
			break;
		case 'textarea':
		case 'color':
		case 'key':
			$schema = array('type' => 'string');
			break;
		case 'maps_html':
			$schema = array('type' => 'string', 'format' => 'html');
			break;
		case 'enum':
			$schema = array('type' => 'string', 'enum' => array_values((array) ($field['values'] ?? array())));
			break;
	}

	if (isset($field['min'])) {
		$schema['minimum'] = $field['min'];
	}
	if (isset($field['max'])) {
		$schema['maximum'] = $field['max'];
	}
	if (!empty($field['group'])) {
		$schema['x-aihl-group'] = (string) $field['group'];
	}

	return $schema;
}

function aihl_ai_openapi_options_schema(): array {
	$properties = array();
	foreach (aihl_ai_options_whitelist() as $key => $field) {
		$properties[$key] = aihl_ai_openapi_field_schema((array) $field);
	}

	return array(
		'type' => 'object',
		'additionalProperties' => false,
		'properties' => $properties,
	);
}

function aihl_ai_openapi_generic_object_schema(): array {
	return array('type' => 'object', 'additionalProperties' => true);
}

function aihl_ai_openapi_schema_ref(string $name): array {
	return array('$ref' => '#/components/schemas/' . $name);
}

function aihl_ai_openapi_path_from_route(string $route): string {
	return (string) preg_replace('/\(\?P<([a-zA-Z0-9_]+)>[^)]+\)/', '{$1}', $route);
}

function aihl_ai_openapi_path_parameters(string $route, array $handler): array {
	if (!preg_match_all('/\(\?P<([a-zA-Z0-9_]+)>([^)]+)\)/', $route, $matches, PREG_SET_ORDER)) {
		return array();
	}

	$parameters = array();
	$args = isset($handler['args']) && is_array($handler['args']) ? $handler['args'] : array();
	foreach ($matches as $match) {
		$name = (string) $match[1];
		$pattern = (string) $match[2];
		$arg = isset($args[$name]) && is_array($args[$name]) ? $args[$name] : array();
		$type = isset($arg['type']) ? (string) $arg['type'] : (strpos($pattern, '\d') !== false ? 'integer' : 'string');
		$schema = array('type' => in_array($type, array('integer', 'number', 'boolean'), true) ? $type : 'string');
		if (isset($arg['minimum'])) {
			$schema['minimum'] = $arg['minimum'];
		}
		if (!empty($arg['enum']) && is_array($arg['enum'])) {
			$schema['enum'] = array_values($arg['enum']);
		}
		$parameters[] = array(
			'name' => $name,
			'in' => 'path',
			'required' => true,
			'schema' => $schema,
		);
	}

	return $parameters;
}

function aihl_ai_openapi_methods_from_endpoint($methods): array {
	if (is_string($methods)) {
		$methods = array($methods);
	}

	$normalized = array();
	foreach ((array) $methods as $method => $enabled) {
		if (is_string($method) && is_bool($enabled)) {
			if ($enabled) {
				$normalized[] = strtoupper($method);
			}
			continue;
		}
		if (is_string($enabled)) {
			$normalized[] = strtoupper($enabled);
		}
	}

	return array_values(array_intersect(array_unique($normalized), array('GET', 'POST', 'PUT', 'PATCH', 'DELETE')));
}

function aihl_ai_openapi_route_metadata(): array {
	return array(
		'/aihtml/v1/ai/context' => array('summary' => 'Theme AI context', 'tag' => 'AI', 'read_schema' => 'AIContext'),
		'/aihtml/v1/ai/options' => array('summary' => 'Theme AI options', 'tag' => 'Options', 'read_schema' => 'AIHLOptionsPayload', 'write_schema' => 'AIHLOptionsEnvelope'),
		'/aihtml/v1/ai/menus' => array('summary' => 'Theme menu JSON', 'tag' => 'Menus', 'read_schema' => 'MenuPayload', 'write_schema' => 'GenericObject'),
		'/aihtml/v1/ai/pages' => array('summary' => 'Theme pages', 'tag' => 'Pages', 'read_schema' => 'PagesPayload', 'write_schema' => 'PageCreateRequest'),
		'/aihtml/v1/ai/pages/{id}' => array('summary' => 'Update or trash an AI page', 'tag' => 'Pages', 'write_schema' => 'PageUpdateRequest'),
		'/aihtml/v1/ai/pages/{id}/restore' => array('summary' => 'Restore a trashed AI page as a non-published draft', 'tag' => 'Pages', 'write_schema' => 'PageRestoreRequest'),
		'/aihtml/v1/ai/pages/{id}/status' => array('summary' => 'Change page publication status', 'tag' => 'Pages', 'write_schema' => 'PageStatusRequest'),
		'/aihtml/v1/ai/site/front-page' => array('summary' => 'Read or assign the WordPress front page', 'tag' => 'Site', 'read_schema' => 'FrontPagePayload', 'write_schema' => 'FrontPageRequest'),
		'/aihtml/v1/ai/site/settings' => array('summary' => 'Read or update WordPress settings governed by AI-HTML', 'tag' => 'Site', 'read_schema' => 'SiteSettings', 'write_schema' => 'SiteSettings'),
		'/aihtml/v1/ai/management' => array('summary' => 'Complete API management coverage catalog', 'tag' => 'Management', 'read_schema' => 'GenericObject'),
		'/aihtml/v1/ai/canvas' => array('summary' => 'Read or select Canvas sources for header and footer', 'tag' => 'Canvas', 'read_schema' => 'GenericObject', 'write_schema' => 'CanvasRequest'),
		'/aihtml/v1/ai/dependencies' => array('summary' => 'Theme dependency status and lifecycle API discovery', 'tag' => 'Management', 'read_schema' => 'GenericObject'),
		'/aihtml/v1/ai/compliance' => array('summary' => 'Run theme compliance checks', 'tag' => 'Management', 'read_schema' => 'GenericObject'),
		'/aihtml/v1/ai/runtime-components/render' => array('summary' => 'Render a governed runtime component', 'tag' => 'Integration', 'read_schema' => 'GenericObject', 'write_schema' => 'RuntimeComponentRequest'),
		'/aihtml/v1/ai/update' => array(
			'summary' => 'Inspect, refresh or install a theme update',
			'tag' => 'Updates',
			'read_schema' => 'GenericObject',
			'write_schema' => 'ThemeUpdateRequest',
			'write_security' => array(array('wpNonce' => array()), array('applicationPassword' => array())),
		),
		'/aihtml/v1/ai/auth/capabilities' => array('summary' => 'Capabilities granted to the current Smart AI key', 'tag' => 'AI', 'read_schema' => 'AuthCapabilities'),
		'/aihtml/v1/ai/options/schema' => array('summary' => 'Theme options schema', 'tag' => 'Options', 'read_schema' => 'AIHLOptionsSchema'),
		'/aihtml/v1/ai/openapi' => array('summary' => 'OpenAPI document', 'tag' => 'Documentation', 'read_schema' => 'OpenAPI'),
		'/aihtml/v1/openapi' => array('summary' => 'OpenAPI document alias', 'tag' => 'Documentation', 'read_schema' => 'OpenAPI'),
		'/aihtml/v1/ai/integration-manifest' => array('summary' => 'Theme integration manifest', 'tag' => 'Integration', 'read_schema' => 'IntegrationManifest'),
		'/aihtml/v1/ai/addons' => array('summary' => 'Theme addon integrations', 'tag' => 'Integration', 'read_schema' => 'AddonsPayload'),
		'/aihtml/v1/ai/deploy' => array('summary' => 'Deploy AI-HTML project', 'tag' => 'Deploy', 'read_schema' => 'DeployResult', 'write_schema' => 'GenericObject'),
		'/aihtml/v1/ai/deploy/projects' => array('summary' => 'List deploy projects', 'tag' => 'Deploy', 'read_schema' => 'DeployProjects'),
		'/aihtml/v1/ai/reset/registry' => array('summary' => 'Smart Reset registry', 'tag' => 'Reset', 'read_schema' => 'ResetRegistry'),
		'/aihtml/v1/ai/reset/execute' => array('summary' => 'Execute Smart Reset', 'tag' => 'Reset', 'read_schema' => 'ResetResult', 'write_schema' => 'ResetRequest'),
		'/aihtml/v1/ai/reset/snapshots/{token}' => array('summary' => 'Read a private Smart Reset snapshot', 'tag' => 'Reset', 'read_schema' => 'GenericObject'),
		'/aihtml/v1/ai/author-profile' => array('summary' => 'Author profile preferences', 'tag' => 'Authors', 'read_schema' => 'GenericObject', 'write_schema' => 'AuthorProfileRequest'),
		'/aihtml/v1/ai/pages/{id}/background' => array('summary' => 'Read, update or remove per-page background', 'tag' => 'Pages', 'read_schema' => 'PageBackground', 'write_schema' => 'PageBackground'),
		'/aihtml/v1/ai/content/{id}/presentation' => array('summary' => 'Read or update theme presentation metadata for content', 'tag' => 'Pages', 'read_schema' => 'ContentPresentation', 'write_schema' => 'ContentPresentation'),
		'/aihtml/v1/ai/page-background/patterns' => array('summary' => 'List available page background patterns', 'tag' => 'Pages', 'read_schema' => 'GenericObject'),
		'/aihtml/v1/ai/code-slots' => array('summary' => 'List or save AI Code Slots', 'tag' => 'Canvas', 'read_schema' => 'GenericObject', 'write_schema' => 'CodeSlot'),
		'/aihtml/v1/ai/code-slots/{slot_id}' => array('summary' => 'Read, update or delete an AI Code Slot', 'tag' => 'Canvas', 'read_schema' => 'CodeSlot', 'write_schema' => 'CodeSlot'),
		'/aihtml/v1/ai/code-slots/{slot_id}/toggle' => array('summary' => 'Enable or disable an AI Code Slot', 'tag' => 'Canvas', 'read_schema' => 'GenericObject', 'write_schema' => 'GenericObject'),
		'/aihtml/v1/ai/code-slots/{slot_id}/rollback' => array('summary' => 'Rollback an AI Code Slot revision', 'tag' => 'Canvas', 'read_schema' => 'GenericObject', 'write_schema' => 'GenericObject'),
		'/aihtml/v1/ai/code-slots/import' => array('summary' => 'Import AI Code Slots', 'tag' => 'Canvas', 'read_schema' => 'GenericObject', 'write_schema' => 'GenericObject'),
		'/aihtml/v1/ai/code-slots/export' => array('summary' => 'Export AI Code Slots', 'tag' => 'Canvas', 'read_schema' => 'GenericObject'),
		'/aihtml/v1/ai/code-slots/hooks' => array('summary' => 'List AI Code Slot hook points', 'tag' => 'Canvas', 'read_schema' => 'GenericObject'),
		'/aihtml/v1/ai/introspection' => array('summary' => 'Inspect the complete AI-HTML runtime state', 'tag' => 'Management', 'read_schema' => 'GenericObject'),
		'/aihtml/v1/ai/capabilities' => array('summary' => 'AI agent onboarding and capability discovery', 'tag' => 'Management', 'read_schema' => 'GenericObject'),
	);
}

function aihl_ai_openapi_payload(): array {
	$server = rest_get_server();
	$routes = $server ? $server->get_routes() : array();
	$metadata = aihl_ai_openapi_route_metadata();
	$paths = array();

	foreach ($routes as $route => $handlers) {
		if (strpos((string) $route, '/aihtml/v1/') !== 0) {
			continue;
		}
		$path = aihl_ai_openapi_path_from_route((string) $route);
		if (!isset($paths[$path])) {
			$paths[$path] = array();
		}

		foreach ((array) $handlers as $handler) {
			if (empty($handler['methods'])) {
				continue;
			}
			foreach (aihl_ai_openapi_methods_from_endpoint($handler['methods']) as $method) {
				$method_key = strtolower($method);
				$route_meta = $metadata[$path] ?? array();
				$is_write = in_array($method, array('POST', 'PUT', 'PATCH', 'DELETE'), true);
				$response_schema = isset($route_meta['read_schema']) ? (string) $route_meta['read_schema'] : 'GenericObject';
				$request_schema = isset($route_meta['write_schema']) ? (string) $route_meta['write_schema'] : 'GenericObject';
				$operation_id = 'aihl_' . strtolower($method) . '_' . preg_replace('/[^a-z0-9]+/', '_', trim(strtolower($path), '/'));

				$operation = array(
					'tags' => array((string) ($route_meta['tag'] ?? 'AI-HTML')),
					'summary' => (string) ($route_meta['summary'] ?? trim($method . ' ' . $path)),
					'operationId' => trim($operation_id, '_'),
					'responses' => array(
						'200' => array(
							'description' => 'Successful response',
							'content' => array(
								'application/json' => array('schema' => aihl_ai_openapi_schema_ref($response_schema)),
							),
						),
						'401' => array('description' => 'Authentication required'),
						'403' => array('description' => 'Insufficient permissions'),
					),
					'security' => ($is_write && isset($route_meta['write_security']))
						? $route_meta['write_security']
						: array(array('wpNonce' => array()), array('smartAiKey' => array()), array('applicationPassword' => array())),
				);
				$path_parameters = aihl_ai_openapi_path_parameters((string) $route, (array) $handler);
				if (!empty($path_parameters)) {
					$operation['parameters'] = $path_parameters;
				}

				if ($is_write && 'DELETE' !== $method) {
					$operation['requestBody'] = array(
						'required' => in_array($method, array('POST', 'PUT', 'PATCH'), true),
						'content' => array(
							'application/json' => array('schema' => aihl_ai_openapi_schema_ref($request_schema)),
						),
					);
				}

				$paths[$path][$method_key] = $operation;
			}
		}
	}

	ksort($paths);

	return array(
		'openapi' => '3.1.0',
		'info' => array(
			'title' => AIHL_THEME_NAME . ' REST API',
			'description' => 'OpenAPI generated at runtime from WordPress REST routes and the AI-HTML theme option whitelist.',
			'version' => AIHL_VERSION,
		),
		'servers' => array(array('url' => untrailingslashit(rest_url()))),
		'tags' => array(
			array('name' => 'AI'),
			array('name' => 'Options'),
			array('name' => 'Menus'),
			array('name' => 'Pages'),
			array('name' => 'Site'),
			array('name' => 'Management'),
			array('name' => 'Canvas'),
			array('name' => 'Deploy'),
			array('name' => 'Reset'),
			array('name' => 'Updates'),
			array('name' => 'Authors'),
			array('name' => 'Integration'),
			array('name' => 'Documentation'),
		),
		'paths' => $paths,
		'components' => array(
			'securitySchemes' => array(
				'wpNonce' => array('type' => 'apiKey', 'in' => 'header', 'name' => 'X-WP-Nonce'),
				'smartAiKey' => array('type' => 'apiKey', 'in' => 'header', 'name' => defined('SMART_AI_KEY_HEADER') ? SMART_AI_KEY_HEADER : 'X-Smart-AI-Key'),
				'applicationPassword' => array('type' => 'http', 'scheme' => 'basic'),
			),
			'schemas' => array(
				'GenericObject' => aihl_ai_openapi_generic_object_schema(),
				'OpenAPI' => aihl_ai_openapi_generic_object_schema(),
				'AIContext' => aihl_ai_openapi_generic_object_schema(),
				'MenuPayload' => aihl_ai_openapi_generic_object_schema(),
				'PagesPayload' => aihl_ai_openapi_generic_object_schema(),
				'AuthCapabilities' => array(
					'type' => 'object',
					'properties' => array(
						'read' => array('type' => 'boolean'),
						'write' => array('type' => 'boolean'),
						'publish' => array('type' => 'boolean'),
						'update_themes' => array('type' => 'boolean'),
					),
				),
				'DeployResult' => aihl_ai_openapi_generic_object_schema(),
				'DeployProjects' => aihl_ai_openapi_generic_object_schema(),
				'ResetRegistry' => aihl_ai_openapi_generic_object_schema(),
				'ResetResult' => aihl_ai_openapi_generic_object_schema(),
				'IntegrationManifest' => aihl_ai_openapi_generic_object_schema(),
				'AddonsPayload' => aihl_ai_openapi_generic_object_schema(),
				'AIHLOptionsSchema' => array(
					'type' => 'object',
					'properties' => array(
						'theme' => array('type' => 'string'),
						'option_key' => array('type' => 'string'),
						'fields' => aihl_ai_openapi_options_schema(),
					),
				),
				'AIHLOptionsEnvelope' => array(
					'type' => 'object',
					'required' => array('options'),
					'properties' => array('options' => aihl_ai_openapi_options_schema()),
				),
				'AIHLOptionsPayload' => array(
					'type' => 'object',
					'properties' => array(
						'theme' => array('type' => 'string'),
						'options' => aihl_ai_openapi_options_schema(),
					),
				),
				'PageCreateRequest' => array(
					'type' => 'object',
					'required' => array('title'),
					'properties' => array(
						'title' => array('type' => 'string'),
						'content' => array('type' => 'string'),
						'status' => array('type' => 'string', 'enum' => array('draft')),
						'template' => array('type' => 'string'),
					),
				),
				'PageStatusRequest' => array(
					'type' => 'object',
					'required' => array('status'),
					'properties' => array(
						'status' => array('type' => 'string', 'enum' => array('draft', 'pending', 'private', 'publish')),
					),
				),
				'PageUpdateRequest' => array(
					'type' => 'object',
					'minProperties' => 1,
					'additionalProperties' => false,
					'properties' => array(
						'title' => array('type' => 'string'),
						'slug' => array('type' => 'string'),
						'content' => array('type' => 'string'),
						'status' => array('type' => 'string', 'enum' => array('draft', 'pending', 'private', 'publish')),
						'template' => array('type' => 'string'),
					),
				),
				'FrontPageRequest' => array(
					'type' => 'object',
					'required' => array('show_on_front'),
					'properties' => array(
						'show_on_front' => array('type' => 'string', 'enum' => array('posts', 'page')),
						'page_on_front' => array('type' => 'integer', 'minimum' => 0),
					),
				),
				'FrontPagePayload' => aihl_ai_openapi_generic_object_schema(),
				'ResetRequest' => array(
					'type' => 'object',
					'properties' => array(
						'components' => array('type' => 'array', 'items' => array('type' => 'string')),
						'dry_run' => array('type' => 'boolean'),
					),
				),
				'SiteSettings' => array(
					'type' => 'object',
					'additionalProperties' => false,
					'properties' => array(
						'blogname' => array('type' => 'string'),
						'blogdescription' => array('type' => 'string'),
						'blog_public' => array('type' => 'boolean'),
						'show_on_front' => array('type' => 'string', 'enum' => array('posts', 'page')),
						'page_on_front' => array('type' => 'integer', 'minimum' => 0),
						'page_for_posts' => array('type' => 'integer', 'minimum' => 0),
						'permalink_structure' => array('type' => 'string'),
					),
				),
				'CanvasRequest' => array(
					'type' => 'object',
					'additionalProperties' => false,
					'properties' => array(
						'header' => array('$ref' => '#/components/schemas/CanvasArea'),
						'footer' => array('$ref' => '#/components/schemas/CanvasArea'),
					),
				),
				'CanvasArea' => array(
					'type' => 'object',
					'additionalProperties' => false,
					'properties' => array(
						'mode' => array('type' => 'string', 'enum' => array('native', 'canvas')),
						'slot_id' => array('type' => 'string'),
					),
				),
				'RuntimeComponentRequest' => array(
					'type' => 'object',
					'required' => array('name'),
					'additionalProperties' => false,
					'properties' => array(
						'name' => array('type' => 'string', 'enum' => array('smart-logo', 'smart-menu', 'smart-social', 'smart-contact', 'smart-addon')),
						'attributes' => array('type' => 'object', 'additionalProperties' => true),
					),
				),
				'ThemeUpdateRequest' => array(
					'type' => 'object',
					'required' => array('action'),
					'additionalProperties' => false,
					'properties' => array('action' => array('type' => 'string', 'enum' => array('check', 'upgrade'))),
				),
				'AuthorProfileRequest' => array(
					'type' => 'object',
					'required' => array('style'),
					'additionalProperties' => false,
					'properties' => array(
						'user_id' => array('type' => 'integer', 'minimum' => 1),
						'style' => array('type' => 'string', 'enum' => array('simple', 'compact', 'card', 'banner', 'editorial', 'enterprise', 'impact', 'signature', 'none')),
					),
				),
				'PageBackground' => array(
					'type' => 'object',
					'additionalProperties' => false,
					'properties' => array(
						'type' => array('type' => 'string', 'enum' => array('default', 'color', 'image', 'pattern')),
						'color' => array('type' => 'string'),
						'image' => array('type' => 'string', 'format' => 'uri'),
						'image_opacity' => array('type' => 'number', 'minimum' => 0, 'maximum' => 1),
						'image_size' => array('type' => 'string', 'enum' => array('cover', 'contain', 'auto')),
						'pattern' => array('type' => 'string'),
						'overlay_color' => array('type' => 'string'),
						'overlay_opacity' => array('type' => 'number', 'minimum' => 0, 'maximum' => 1),
					),
				),
				'ContentPresentation' => array(
					'type' => 'object',
					'additionalProperties' => false,
					'properties' => array(
						'post_id' => array('type' => 'integer', 'minimum' => 1, 'readOnly' => true),
						'subtitle' => array('type' => 'string'),
					),
				),
				'CodeSlot' => array(
					'type' => 'object',
					'required' => array('hook'),
					'properties' => array(
						'id' => array('type' => 'string'),
						'label' => array('type' => 'string'),
						'hook' => array('type' => 'string'),
						'type' => array('type' => 'string', 'enum' => array('html', 'css', 'js', 'mixed')),
						'code' => array('type' => 'string'),
						'css' => array('type' => 'string'),
						'js' => array('type' => 'string'),
						'context' => array('oneOf' => array(array('type' => 'string'), array('type' => 'array', 'items' => array('type' => 'string')))),
						'priority' => array('type' => 'integer', 'minimum' => 1, 'maximum' => 999),
						'active' => array('type' => 'boolean'),
					),
				),
			),
		),
		'x-aihl-generated' => gmdate('c'),
		'x-aihl-schema-source' => 'aihl_ai_options_whitelist',
	);
}

function aihl_ai_rest_get_menus(WP_REST_Request $request) {
	if (!function_exists('aihl_build_menu_json_payload')) {
		return new WP_Error('unavailable', 'Funzioni menu JSON non disponibili.', array('status' => 500));
	}
	$term_id = absint($request->get_param('menu_term_id'));
	$payload = aihl_build_menu_json_payload($term_id);
	return rest_ensure_response($payload);
}

function aihl_ai_rest_import_menus(WP_REST_Request $request) {
	if (!function_exists('aihl_import_menu_json_payload')) {
		return new WP_Error('unavailable', 'Funzioni import menu non disponibili.', array('status' => 500));
	}
	$body = $request->get_json_params();
	$replace = !empty($body['replace_existing']);

	// Accetta sia { "menus": [...] } diretto sia { "payload": {...} }
	$payload = isset($body['payload']) ? $body['payload'] : $body;
	$json = wp_json_encode($payload);

	$result = aihl_import_menu_json_payload($json, $replace);
	if (is_wp_error($result)) {
		return new WP_Error('import_failed', $result->get_error_message(), array('status' => 400));
	}

	return rest_ensure_response(array(
		'imported' => true,
		'menus'    => $result['menus'],
		'items'    => $result['items'],
		'failed'   => $result['failed_items'] ?? 0,
	));
}

/* ============================================================================
 * Pages
 * ============================================================================ */

function aihl_ai_rest_list_pages() {
	$pages = array();
	foreach (get_pages(array('post_status' => array('publish', 'draft', 'pending', 'private'))) as $p) {
		$pages[] = array(
			'id'       => (int) $p->ID,
			'title'    => $p->post_title,
			'slug'     => $p->post_name,
			'status'   => $p->post_status,
			'template' => get_page_template_slug($p->ID) ?: 'default',
			'url'      => get_permalink($p->ID),
		);
	}
	return rest_ensure_response(array(
		'count' => count($pages),
		'pages' => $pages,
		'available_templates' => array(
			'default'                 => 'Pagina standard del tema',
			'smart-site-home.php'     => 'Home builder (SBS)',
			'smart-site-builder.php'  => 'Pagina builder (SBS)',
			'smart-site-blog.php'     => 'Blog builder + compose (SBS)',
		),
	));
}

function aihl_ai_rest_create_page(WP_REST_Request $request) {
	$body = $request->get_json_params();
	$title = isset($body['title']) ? sanitize_text_field((string) $body['title']) : '';
	if ('' === $title) {
		return new WP_Error('missing_title', 'Il campo title e obbligatorio.', array('status' => 400));
	}

	$template = isset($body['template']) ? sanitize_text_field((string) $body['template']) : '';
	$allowed_templates = array('', 'default', 'smart-site-home.php', 'smart-site-builder.php', 'smart-site-blog.php');
	if (!in_array($template, $allowed_templates, true)) {
		return new WP_Error('invalid_template', 'Template non valido.', array('status' => 400));
	}

	if (isset($body['status']) && 'draft' !== $body['status']) {
		return new WP_Error('create_status_not_allowed', 'Le nuove pagine AI devono essere create come bozze.', array('status' => 422));
	}
	$status = 'draft';
	$slug = isset($body['slug']) ? sanitize_title((string) $body['slug']) : '';

	$page_data = array(
		'post_type'    => 'page',
		'post_title'   => $title,
		'post_status'  => $status,
		'post_content' => isset($body['content']) ? wp_kses_post((string) $body['content']) : '',
	);
	if ('' !== $slug) {
		$page_data['post_name'] = $slug;
	}
	$page_id = wp_insert_post($page_data);

	if (is_wp_error($page_id) || !$page_id) {
		return new WP_Error('create_failed', 'Creazione pagina fallita.', array('status' => 500));
	}

	if ('' !== $template && 'default' !== $template) {
		update_post_meta($page_id, '_wp_page_template', $template);
	}

	return rest_ensure_response(array(
		'created'  => true,
		'page_id'  => (int) $page_id,
		'title'    => $title,
		'slug'     => (string) get_post_field('post_name', $page_id),
		'template' => $template ?: 'default',
		'status'   => $status,
		'url'      => get_permalink($page_id),
		'edit_builder' => ('smart-site-home.php' === $template || 'smart-site-builder.php' === $template || 'smart-site-blog.php' === $template)
			? rest_url('sbs/v1/ai/pages/' . $page_id . '/builder')
			: null,
	));
}

function aihl_ai_rest_update_page(WP_REST_Request $request) {
	$page_id = absint($request->get_param('id'));
	$page = get_post($page_id);
	if (!$page || 'page' !== $page->post_type) {
		return new WP_Error('page_not_found', 'Pagina non trovata.', array('status' => 404));
	}
	if ('trash' === $page->post_status) {
		return new WP_Error('page_trashed', 'Ripristinare la pagina prima di modificarla.', array('status' => 409));
	}

	$body = $request->get_json_params();
	if (!is_array($body)) {
		$body = array();
	}
	$allowed_fields = array('title', 'slug', 'content', 'status', 'template');
	$unknown_fields = array_diff(array_keys($body), $allowed_fields);
	if ($unknown_fields) {
		return new WP_Error('unsupported_page_fields', 'La richiesta contiene campi pagina non supportati.', array('status' => 422));
	}
	if (!$body) {
		return new WP_Error('empty_page_update', 'Specificare almeno un campo da aggiornare.', array('status' => 422));
	}

	$requested_status = array_key_exists('status', $body)
		? sanitize_key((string) $body['status'])
		: (string) $page->post_status;
	if (!in_array($requested_status, array('draft', 'pending', 'private', 'publish'), true)) {
		return new WP_Error('invalid_page_status', 'Stato pagina non valido.', array('status' => 422));
	}
	if (('publish' === $page->post_status || 'publish' === $requested_status) && !aihl_ai_can_publish($request)) {
		return new WP_Error('publish_permission_required', 'La modifica di una pagina pubblicata richiede il permesso publish.', array('status' => 403));
	}

	$allowed_templates = array('', 'default', 'smart-site-home.php', 'smart-site-builder.php', 'smart-site-blog.php');
	$template = array_key_exists('template', $body)
		? sanitize_text_field((string) $body['template'])
		: (get_page_template_slug($page_id) ?: 'default');
	if (!in_array($template, $allowed_templates, true)) {
		return new WP_Error('invalid_template', 'Template non valido.', array('status' => 422));
	}

	$update = array('ID' => $page_id);
	if (array_key_exists('title', $body)) {
		$title = sanitize_text_field((string) $body['title']);
		if ('' === $title) {
			return new WP_Error('missing_title', 'Il titolo della pagina non puo essere vuoto.', array('status' => 422));
		}
		$update['post_title'] = $title;
	}
	if (array_key_exists('slug', $body)) {
		$update['post_name'] = sanitize_title((string) $body['slug']);
	}
	if (array_key_exists('content', $body)) {
		$update['post_content'] = wp_kses_post((string) $body['content']);
	}
	if (array_key_exists('status', $body)) {
		$update['post_status'] = $requested_status;
	}

	$result = wp_update_post($update, true);
	if (is_wp_error($result)) {
		return new WP_Error('page_update_failed', $result->get_error_message(), array('status' => 500));
	}
	if (array_key_exists('template', $body)) {
		if ('' === $template || 'default' === $template) {
			delete_post_meta($page_id, '_wp_page_template');
		} else {
			update_post_meta($page_id, '_wp_page_template', $template);
		}
	}

	return rest_ensure_response(array(
		'updated'  => true,
		'page_id'  => $page_id,
		'title'    => (string) get_the_title($page_id),
		'slug'     => (string) get_post_field('post_name', $page_id),
		'status'   => (string) get_post_status($page_id),
		'template' => get_page_template_slug($page_id) ?: 'default',
		'content'  => (string) get_post_field('post_content', $page_id),
		'url'      => get_permalink($page_id),
	));
}

function aihl_ai_rest_trash_page(WP_REST_Request $request) {
	$page_id = absint($request->get_param('id'));
	$page = get_post($page_id);
	if (!$page || 'page' !== $page->post_type) {
		return new WP_Error('page_not_found', 'Pagina non trovata.', array('status' => 404));
	}
	if (!in_array($page->post_status, array('draft', 'pending', 'private'), true)) {
		return new WP_Error('published_page_protected', 'Le pagine pubblicate non possono essere cestinate dalla AI.', array('status' => 409));
	}
	$trashed = wp_trash_post($page_id);
	if (!$trashed) {
		return new WP_Error('trash_failed', 'Impossibile spostare la pagina nel cestino.', array('status' => 500));
	}
	return rest_ensure_response(array(
		'trashed' => true,
		'page_id' => $page_id,
		'previous_status' => $page->post_status,
	));
}

function aihl_ai_rest_restore_page(WP_REST_Request $request) {
	$page_id = absint($request->get_param('id'));
	$page = get_post($page_id);
	if (!$page || 'page' !== $page->post_type) {
		return new WP_Error('page_not_found', 'Pagina non trovata.', array('status' => 404));
	}
	if ('trash' !== $page->post_status) {
		return new WP_Error('page_not_trashed', 'Solo le pagine nel cestino possono essere ripristinate.', array('status' => 409));
	}
	$body = $request->get_json_params();
	$status = isset($body['status']) ? sanitize_key((string) $body['status']) : 'draft';
	$has_slug = is_array($body) && array_key_exists('slug', $body);
	$slug = $has_slug ? sanitize_title((string) $body['slug']) : '';
	if (!in_array($status, array('draft', 'pending', 'private'), true)) {
		return new WP_Error('restore_status_not_allowed', 'Il ripristino AI non puo pubblicare una pagina.', array('status' => 422));
	}
	$restored = wp_untrash_post($page_id);
	if (!$restored) {
		return new WP_Error('restore_failed', 'Impossibile ripristinare la pagina dal cestino.', array('status' => 500));
	}
	if ('draft' !== $status || $has_slug) {
		$restore_update = array('ID' => $page_id, 'post_status' => $status);
		if ($has_slug) {
			$restore_update['post_name'] = $slug;
		}
		wp_update_post($restore_update);
	}
	return rest_ensure_response(array(
		'restored' => true,
		'page_id'  => $page_id,
		'status'   => $status,
		'slug'     => (string) get_post_field('post_name', $page_id),
	));
}

function aihl_ai_rest_update_page_status(WP_REST_Request $request) {
	$page_id = absint($request->get_param('id'));
	$page = get_post($page_id);
	if (!$page || 'page' !== $page->post_type) {
		return new WP_Error('page_not_found', 'Pagina non trovata.', array('status' => 404));
	}
	if ('trash' === $page->post_status) {
		return new WP_Error('page_trashed', 'Ripristinare la pagina prima di cambiarne lo stato.', array('status' => 409));
	}

	$body = $request->get_json_params();
	$status = isset($body['status']) ? sanitize_key((string) $body['status']) : '';
	if (!in_array($status, array('draft', 'pending', 'private', 'publish'), true)) {
		return new WP_Error('invalid_page_status', 'Stato pagina non valido.', array('status' => 422));
	}

	$previous_status = $page->post_status;
	$result = wp_update_post(array('ID' => $page_id, 'post_status' => $status), true);
	if (is_wp_error($result)) {
		return new WP_Error('status_update_failed', $result->get_error_message(), array('status' => 500));
	}

	return rest_ensure_response(array(
		'updated'         => true,
		'page_id'         => $page_id,
		'previous_status' => $previous_status,
		'status'          => (string) get_post_status($page_id),
		'url'             => get_permalink($page_id),
	));
}

function aihl_ai_rest_get_front_page() {
	return rest_ensure_response(array(
		'show_on_front' => (string) get_option('show_on_front', 'posts'),
		'page_on_front' => (int) get_option('page_on_front', 0),
	));
}

function aihl_ai_rest_update_front_page(WP_REST_Request $request) {
	$body = $request->get_json_params();
	$show_on_front = isset($body['show_on_front']) ? sanitize_key((string) $body['show_on_front']) : '';
	if (!in_array($show_on_front, array('posts', 'page'), true)) {
		return new WP_Error('invalid_show_on_front', 'show_on_front deve essere posts o page.', array('status' => 422));
	}

	$page_on_front = isset($body['page_on_front']) ? absint($body['page_on_front']) : 0;
	if ('page' === $show_on_front) {
		$page = get_post($page_on_front);
		if (!$page || 'page' !== $page->post_type) {
			return new WP_Error('front_page_not_found', 'La pagina iniziale indicata non esiste.', array('status' => 404));
		}
		if ('publish' !== $page->post_status) {
			return new WP_Error('front_page_not_published', 'La pagina iniziale deve essere pubblicata.', array('status' => 409));
		}
	}

	$previous = array(
		'show_on_front' => (string) get_option('show_on_front', 'posts'),
		'page_on_front' => (int) get_option('page_on_front', 0),
	);
	update_option('show_on_front', $show_on_front);
	update_option('page_on_front', 'page' === $show_on_front ? $page_on_front : 0);

	return rest_ensure_response(array(
		'updated'  => true,
		'previous' => $previous,
		'current'  => array(
			'show_on_front' => (string) get_option('show_on_front', 'posts'),
			'page_on_front' => (int) get_option('page_on_front', 0),
		),
	));
}
