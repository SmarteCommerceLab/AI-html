<?php
/**
 * Complete management surface for AI-HTML.
 */
if (!defined('ABSPATH')) {
	exit;
}

function aihl_api_management_catalog(): array {
	return array(
		'options' => array('route' => '/aihtml/v1/ai/options', 'methods' => array('GET', 'POST'), 'owner' => 'theme'),
		'site_settings' => array('route' => '/aihtml/v1/ai/site/settings', 'methods' => array('GET', 'PUT'), 'owner' => 'theme'),
		'pages' => array('route' => '/aihtml/v1/ai/pages', 'methods' => array('GET', 'POST', 'PUT', 'DELETE'), 'owner' => 'theme'),
		'page_background' => array('route' => '/aihtml/v1/ai/pages/{id}/background', 'methods' => array('GET', 'PUT', 'DELETE'), 'owner' => 'theme'),
		'content_presentation' => array('route' => '/aihtml/v1/ai/content/{id}/presentation', 'methods' => array('GET', 'PUT'), 'owner' => 'theme'),
		'menus' => array('route' => '/aihtml/v1/ai/menus', 'methods' => array('GET', 'POST'), 'owner' => 'theme'),
		'canvas' => array('route' => '/aihtml/v1/ai/canvas', 'methods' => array('GET', 'PUT'), 'owner' => 'theme'),
		'code_slots' => array('route' => '/aihtml/v1/ai/code-slots', 'methods' => array('GET', 'POST', 'PUT', 'DELETE'), 'owner' => 'theme'),
		'deploy' => array('route' => '/aihtml/v1/ai/deploy', 'methods' => array('POST'), 'owner' => 'theme'),
		'reset' => array('route' => '/aihtml/v1/ai/reset/execute', 'methods' => array('POST'), 'owner' => 'theme'),
		'reset_snapshots' => array('route' => '/aihtml/v1/ai/reset/snapshots/{token}', 'methods' => array('GET'), 'owner' => 'theme'),
		'author_profile' => array('route' => '/aihtml/v1/ai/author-profile', 'methods' => array('GET', 'PUT'), 'owner' => 'theme'),
		'dependencies' => array('route' => '/aihtml/v1/ai/dependencies', 'methods' => array('GET'), 'owner' => 'theme', 'lifecycle_route' => '/wp/v2/plugins'),
		'compliance' => array('route' => '/aihtml/v1/ai/compliance', 'methods' => array('GET'), 'owner' => 'theme'),
		'runtime_components' => array('route' => '/aihtml/v1/ai/runtime-components/render', 'methods' => array('POST'), 'owner' => 'theme'),
		'updates' => array(
			'route' => '/aihtml/v1/ai/update',
			'methods' => array('GET', 'POST'),
			'owner' => 'theme',
			'write_auth' => 'WordPress administrator with update_themes via nonce or Application Password',
		),
		'integrations' => array('route' => '/aihtml/v1/ai/integration-manifest', 'methods' => array('GET'), 'owner' => 'theme'),
		'api_credentials' => array(
			'route' => null,
			'methods' => array(),
			'owner' => 'security-bootstrap',
			'managed' => false,
			'reason' => 'Credential creation and revocation require an authenticated WordPress admin session.',
		),
	);
}

function aihl_api_site_settings_payload(): array {
	return array(
		'blogname' => (string) get_option('blogname', ''),
		'blogdescription' => (string) get_option('blogdescription', ''),
		'blog_public' => (bool) get_option('blog_public', 1),
		'show_on_front' => (string) get_option('show_on_front', 'posts'),
		'page_on_front' => (int) get_option('page_on_front', 0),
		'page_for_posts' => (int) get_option('page_for_posts', 0),
		'permalink_structure' => (string) get_option('permalink_structure', ''),
	);
}

function aihl_api_update_site_settings(WP_REST_Request $request) {
	$body = $request->get_json_params();
	if (!is_array($body)) {
		return new WP_Error('aihl_invalid_json', __('Payload JSON non valido.', AIHL_TEXT_DOMAIN), array('status' => 400));
	}
	$applied = array();
	foreach (array('blogname', 'blogdescription') as $key) {
		if (array_key_exists($key, $body)) {
			$applied[$key] = sanitize_text_field((string) $body[$key]);
			update_option($key, $applied[$key]);
		}
	}
	if (array_key_exists('blog_public', $body)) {
		$applied['blog_public'] = !empty($body['blog_public']) ? 1 : 0;
		update_option('blog_public', $applied['blog_public']);
	}
	if (array_key_exists('show_on_front', $body)) {
		$show = sanitize_key((string) $body['show_on_front']);
		if (!in_array($show, array('posts', 'page'), true)) {
			return new WP_Error('aihl_invalid_front_mode', __('show_on_front non valido.', AIHL_TEXT_DOMAIN), array('status' => 400));
		}
		$applied['show_on_front'] = $show;
		update_option('show_on_front', $show);
	}
	foreach (array('page_on_front', 'page_for_posts') as $key) {
		if (array_key_exists($key, $body)) {
			$page_id = absint($body[$key]);
			if ($page_id > 0 && 'page' !== get_post_type($page_id)) {
				return new WP_Error('aihl_invalid_page', sprintf(__('%s deve indicare una pagina valida.', AIHL_TEXT_DOMAIN), $key), array('status' => 400));
			}
			$applied[$key] = $page_id;
			update_option($key, $page_id);
		}
	}
	if (array_key_exists('permalink_structure', $body)) {
		$structure = trim((string) $body['permalink_structure']);
		if ($structure !== '' && (strpos($structure, '/') !== 0 || substr($structure, -1) !== '/')) {
			return new WP_Error('aihl_invalid_permalink', __('La struttura permalink deve iniziare e terminare con /.', AIHL_TEXT_DOMAIN), array('status' => 400));
		}
		$applied['permalink_structure'] = sanitize_text_field($structure);
		update_option('permalink_structure', $applied['permalink_structure']);
		flush_rewrite_rules(false);
	}
	return rest_ensure_response(array('updated' => true, 'applied' => $applied, 'settings' => aihl_api_site_settings_payload()));
}

function aihl_api_canvas_payload(): array {
	$result = array();
	foreach (array('header', 'footer') as $area) {
		$options = get_option(AIHL_OPTION_BASE . '_general', array());
		$winner = function_exists('aihl_get_canvas_override_slot') ? aihl_get_canvas_override_slot($area) : null;
		$result[$area] = array(
			'mode' => function_exists('aihl_get_structure_render_mode') ? aihl_get_structure_render_mode($area) : 'native',
			'selected_slot_id' => is_array($options) ? (string) ($options[$area . '_canvas_slot_id'] ?? '') : '',
			'resolved_slot_id' => is_array($winner) ? (string) ($winner['id'] ?? '') : '',
			'available' => is_array($winner),
		);
	}
	return $result;
}

function aihl_api_update_canvas(WP_REST_Request $request) {
	$body = $request->get_json_params();
	if (!is_array($body)) {
		return new WP_Error('aihl_invalid_json', __('Payload JSON non valido.', AIHL_TEXT_DOMAIN), array('status' => 400));
	}
	$options = get_option(AIHL_OPTION_BASE . '_general', array());
	$options = is_array($options) ? $options : array();
	foreach (array('header', 'footer') as $area) {
		if (!isset($body[$area]) || !is_array($body[$area])) {
			continue;
		}
		if (array_key_exists('mode', $body[$area])) {
			$options[$area . '_render_mode'] = aihl_sanitize_registered_option($body[$area]['mode'], $area . '_render_mode');
		}
		if (array_key_exists('slot_id', $body[$area])) {
			$slot_id = sanitize_key((string) $body[$area]['slot_id']);
			$slot = $slot_id !== '' ? aihl_code_slots_get($slot_id) : null;
			if ($slot_id !== '' && (!$slot || ($slot['hook'] ?? '') !== $area . '_full')) {
				return new WP_Error('aihl_invalid_canvas_slot', sprintf(__('Slot Canvas %s non valido.', AIHL_TEXT_DOMAIN), $area), array('status' => 400));
			}
			$options[$area . '_canvas_slot_id'] = $slot_id;
		}
	}
	update_option(AIHL_OPTION_BASE . '_general', $options, false);
	return rest_ensure_response(array('updated' => true, 'canvas' => aihl_api_canvas_payload()));
}

function aihl_api_dependency_payload(): array {
	$dependencies = array();
	foreach (aihl_plugin_registry() as $plugin) {
		$dependencies[] = array(
			'name' => (string) $plugin['name'],
			'required' => !empty($plugin['required']),
			'area' => (string) $plugin['area'],
			'paths' => aihl_plugin_paths($plugin),
			'active' => aihl_is_plugin_entry_active($plugin),
		);
	}
	return array('count' => count($dependencies), 'dependencies' => $dependencies, 'lifecycle_api' => rest_url('wp/v2/plugins'));
}

function aihl_api_compliance_payload(): array {
	$checks = aihl_google_compliance_checks();
	$passed = count(array_filter($checks, static fn($check) => !empty($check['ok'])));
	return array(
		'score' => $checks ? (int) round(($passed / count($checks)) * 100) : 0,
		'passed' => $passed,
		'total' => count($checks),
		'checks' => $checks,
	);
}

function aihl_api_update_status(bool $refresh = false): array {
	$cache_key = 'aihl_public_update_' . md5(AIHL_UPDATE_ENDPOINT);
	if ($refresh) {
		delete_site_transient($cache_key);
		delete_site_transient('update_themes');
		wp_update_themes();
	}
	$response = wp_remote_get(AIHL_UPDATE_ENDPOINT, array('timeout' => 15, 'headers' => array('Accept' => 'application/json')));
	$remote = (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response))
		? json_decode(wp_remote_retrieve_body($response), true)
		: array();
	$remote = is_array($remote) ? $remote : array();
	$remote_version = sanitize_text_field((string) ($remote['version'] ?? $remote['new_version'] ?? ''));
	return array(
		'installed_version' => AIHL_VERSION,
		'remote_version' => $remote_version,
		'update_available' => $remote_version !== '' && version_compare($remote_version, AIHL_VERSION, '>'),
		'package' => esc_url_raw((string) ($remote['download_url'] ?? $remote['package'] ?? '')),
		'sha256' => strtolower(sanitize_text_field((string) ($remote['sha256'] ?? $remote['checksum_sha256'] ?? ''))),
		'refreshed' => $refresh,
	);
}

function aihl_api_upgrade_theme() {
	if (!current_user_can('update_themes')) {
		return new WP_Error('aihl_update_forbidden', __('Permesso update_themes richiesto.', AIHL_TEXT_DOMAIN), array('status' => 403));
	}
	wp_update_themes();
	$updates = get_site_transient('update_themes');
	$slug = get_template();
	if (!is_object($updates) || empty($updates->response[$slug])) {
		return rest_ensure_response(array('updated' => false, 'reason' => 'no-update', 'status' => aihl_api_update_status(false)));
	}
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	$skin = new Automatic_Upgrader_Skin();
	$upgrader = new Theme_Upgrader($skin);
	$result = $upgrader->upgrade($slug);
	if (is_wp_error($result)) {
		return $result;
	}
	if (!$result) {
		return new WP_Error('aihl_update_failed', __('Aggiornamento tema non completato.', AIHL_TEXT_DOMAIN), array('status' => 500));
	}
	return rest_ensure_response(array('updated' => true, 'theme' => $slug, 'result' => $result));
}

function aihl_register_management_api_routes(): void {
	register_rest_route('aihtml/v1', '/ai/management', array(
		'methods' => WP_REST_Server::READABLE,
		'permission_callback' => 'aihl_ai_can_read',
		'callback' => static fn() => rest_ensure_response(array('catalog' => aihl_api_management_catalog())),
	));
	register_rest_route('aihtml/v1', '/ai/site/settings', array(
		array('methods' => WP_REST_Server::READABLE, 'permission_callback' => 'aihl_ai_can_read', 'callback' => static fn() => rest_ensure_response(aihl_api_site_settings_payload())),
		array('methods' => WP_REST_Server::EDITABLE, 'permission_callback' => 'aihl_ai_can_publish', 'callback' => 'aihl_api_update_site_settings'),
	));
	register_rest_route('aihtml/v1', '/ai/canvas', array(
		array('methods' => WP_REST_Server::READABLE, 'permission_callback' => 'aihl_ai_can_read', 'callback' => static fn() => rest_ensure_response(aihl_api_canvas_payload())),
		array('methods' => WP_REST_Server::EDITABLE, 'permission_callback' => 'aihl_ai_can_write', 'callback' => 'aihl_api_update_canvas'),
	));
	register_rest_route('aihtml/v1', '/ai/content/(?P<id>\d+)/presentation', array(
		array(
			'methods' => WP_REST_Server::READABLE,
			'permission_callback' => 'aihl_ai_can_read',
			'callback' => static function (WP_REST_Request $request) {
				$post_id = absint($request->get_param('id'));
				if (!$post_id || !get_post($post_id)) {
					return new WP_Error('aihl_content_not_found', __('Contenuto non trovato.', AIHL_TEXT_DOMAIN), array('status' => 404));
				}
				return rest_ensure_response(array(
					'post_id' => $post_id,
					'subtitle' => (string) get_post_meta($post_id, 'post-sub-title-value', true),
				));
			},
			'args' => array('id' => array('type' => 'integer', 'minimum' => 1, 'required' => true)),
		),
		array(
			'methods' => WP_REST_Server::EDITABLE,
			'permission_callback' => 'aihl_ai_can_write',
			'callback' => static function (WP_REST_Request $request) {
				$post_id = absint($request->get_param('id'));
				if (!$post_id || !get_post($post_id)) {
					return new WP_Error('aihl_content_not_found', __('Contenuto non trovato.', AIHL_TEXT_DOMAIN), array('status' => 404));
				}
				$subtitle = sanitize_text_field((string) $request->get_param('subtitle'));
				if ($subtitle === '') {
					delete_post_meta($post_id, 'post-sub-title-value');
				} else {
					update_post_meta($post_id, 'post-sub-title-value', $subtitle);
				}
				return rest_ensure_response(array('updated' => true, 'post_id' => $post_id, 'subtitle' => $subtitle));
			},
			'args' => array('id' => array('type' => 'integer', 'minimum' => 1, 'required' => true)),
		),
	));
	register_rest_route('aihtml/v1', '/ai/dependencies', array(
		'methods' => WP_REST_Server::READABLE, 'permission_callback' => 'aihl_ai_can_read', 'callback' => static fn() => rest_ensure_response(aihl_api_dependency_payload()),
	));
	register_rest_route('aihtml/v1', '/ai/compliance', array(
		'methods' => WP_REST_Server::READABLE, 'permission_callback' => 'aihl_ai_can_read', 'callback' => static fn() => rest_ensure_response(aihl_api_compliance_payload()),
	));
	register_rest_route('aihtml/v1', '/ai/runtime-components/render', array(
		'methods' => WP_REST_Server::CREATABLE,
		'permission_callback' => 'aihl_ai_can_read',
		'callback' => static function (WP_REST_Request $request) {
			$name = sanitize_key((string) $request->get_param('name'));
			$attributes = $request->get_param('attributes');
			$attributes = is_array($attributes) ? $attributes : array();
			$html = aihl_render_dynamic_component($name, $attributes);
			return rest_ensure_response(array('name' => $name, 'html' => $html, 'rendered' => $html !== ''));
		},
	));
	register_rest_route('aihtml/v1', '/ai/update', array(
		array('methods' => WP_REST_Server::READABLE, 'permission_callback' => 'aihl_ai_can_read', 'callback' => static fn() => rest_ensure_response(aihl_api_update_status(false))),
		array(
			'methods' => WP_REST_Server::CREATABLE,
			'permission_callback' => static fn() => current_user_can('update_themes'),
			'callback' => static function (WP_REST_Request $request) {
				$action = sanitize_key((string) ($request->get_param('action') ?: 'check'));
				if ('upgrade' === $action) {
					return aihl_api_upgrade_theme();
				}
				if ('check' !== $action) {
					return new WP_Error('aihl_invalid_update_action', __('Azione aggiornamento non valida.', AIHL_TEXT_DOMAIN), array('status' => 400));
				}
				return rest_ensure_response(aihl_api_update_status(true));
			},
		),
	));
}
add_action('rest_api_init', 'aihl_register_management_api_routes');
