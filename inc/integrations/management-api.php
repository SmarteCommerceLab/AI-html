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
			'health' => function_exists('aihl_canvas_health_report') ? aihl_canvas_health_report($area) : array(),
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
			if ($slot_id !== '' && function_exists('aihl_code_slot_governance_report')) {
				$governance_report = aihl_code_slot_governance_report($slot);
				if (!$governance_report['valid']) {
					return new WP_Error(
						'aihl_canvas_governance_failed',
						sprintf(__('Slot Canvas %s non conforme alla governance SBM.', AIHL_TEXT_DOMAIN), $area),
						array('status' => 409, 'governance' => $governance_report)
					);
				}
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

function aihl_sbm_option_domain_map(): array {
	return array(
		'header_render_mode' => 'components',
		'header_canvas_slot_id' => 'components',
		'header_structure' => 'components',
		'header_nav_layout' => 'components',
		'header_nav_text_variant' => 'typography',
		'header_nav_font_weight' => 'typography',
		'header_nav_letter_spacing' => 'typography',
		'header_overlay_mode' => 'components',
		'header_overlay_opacity' => 'components',
		'header_overlay_blur' => 'components',
		'header_sticky_style' => 'components',
		'header_search_style' => 'components',
		'header_topbar_scroll_behavior' => 'components',
		'header_show_logo' => 'components',
		'header_show_cta' => 'components',
		'header_show_login' => 'components',
		'menu_dropdown_indicator' => 'components',
		'mobile_nav_style' => 'components',
		'mobile_rail_enable' => 'components',
		'mobile_rail_position' => 'components',
		'mobile_rail_autohide' => 'components',
		'blog_layout' => 'spacing',
		'blog_sidebar' => 'spacing',
		'article_image_size_control' => 'components',
		'article_next_prev' => 'components',
		'article_related' => 'components',
		'article_related_link' => 'components',
		'article_content_size' => 'spacing',
		'article_author_box_style' => 'components',
		'page_bg_type' => 'colors',
		'page_bg_color' => 'colors',
		'page_bg_image_opacity' => 'colors',
		'page_bg_image_size' => 'spacing',
		'page_bg_pattern' => 'colors',
		'page_bg_overlay_color' => 'colors',
		'page_bg_overlay_opacity' => 'colors',
		'footer_render_mode' => 'components',
		'footer_canvas_slot_id' => 'components',
		'footer_variant' => 'components',
		'footer_columns_count' => 'spacing',
		'footer_background_enable' => 'components',
		'footer_background_opacity' => 'colors',
		'footer_background_position' => 'spacing',
		'footer_background_size' => 'spacing',
		'footer_background_repeat' => 'components',
		'footer_overlay_opacity' => 'colors',
		'footer_overlay_tone' => 'colors',
	);
}

function aihl_sbm_content_option_names(): array {
	return array(
		'sito_descrizione',
		'site_logo_url',
		'site_logo_transparent_url',
		'site_logo_light_url',
		'footer_logo_url',
		'header_cta_label',
		'header_cta_url',
		'header_login_label',
		'header_login_url',
		'page_bg_image',
		'footer_background_image',
		'footer_background_remote_url',
		'footer_cta_title',
		'footer_cta_subtitle',
		'footer_cta_btn_label',
		'footer_cta_btn_url',
		'footer_cta_btn2_label',
		'footer_cta_btn2_url',
		'contatti_telefono',
		'contatti_email',
		'contatti_indirizzo',
		'contatti_maps',
		'contactform_contacts',
		'mailchip_footer',
	);
}

function aihl_sbm_option_compliance_matrix(): array {
	$registry = function_exists('aihl_theme_option_registry') ? aihl_theme_option_registry() : array();
	$domains = aihl_sbm_option_domain_map();
	$content_options = array_fill_keys(aihl_sbm_content_option_names(), true);
	$mode = function_exists('aihl_sbm_design_mode') ? aihl_sbm_design_mode() : 'autonomous';
	$matrix = array();

	foreach ($registry as $option => $definition) {
		$domain = $domains[$option] ?? '';
		$visual = '' !== $domain;
		$classification = $visual ? 'visual' : (isset($content_options[$option]) ? 'content' : 'unclassified');
		$inherits = $visual && function_exists('aihl_sbm_inherits_design_domain')
			? aihl_sbm_inherits_design_domain($domain)
			: false;
		if ('unclassified' === $classification) {
			$status = 'unclassified';
		} elseif (!$visual) {
			$status = 'compatible-content-structure';
		} elseif ($inherits && 'adaptive' === $mode) {
			$status = 'sbm-adaptive';
		} elseif ($inherits) {
			$status = 'sbm-governed';
		} else {
			$status = 'sbm-authorized-autonomous';
		}

		$matrix[$option] = array(
			'group' => (string) ($definition['group'] ?? ''),
			'type' => (string) ($definition['type'] ?? ''),
			'classification' => $classification,
			'visual_domain' => $domain,
			'requested' => function_exists('aihtml_option_value')
				? aihtml_option_value($option, $definition['default'] ?? '')
				: ($definition['default'] ?? ''),
			'design_mode' => $mode,
			'inherited_from_sbm' => $inherits,
			'status' => $status,
			'compliant' => 'unclassified' !== $classification,
		);
	}

	return $matrix;
}

function aihl_sbm_contract_compatibility_report(): array {
	$contract = function_exists('aihl_sbm_consumer_contract') ? aihl_sbm_consumer_contract() : array();
	$version = is_array($contract) ? (string) ($contract['contract_version'] ?? '') : '';
	$provider = is_array($contract) ? (string) ($contract['provider'] ?? '') : '';
	$provider_version = is_array($contract) ? (string) ($contract['provider_version'] ?? '') : '';
	$contract_major = preg_match('/^(\d+)\./', $version, $matches) ? (int) $matches[1] : 0;
	$provider_callable = function_exists('smart_bootstrap_manager_consumer_contract');
	$version_supported = 1 === $contract_major;
	$provider_supported = '' !== $provider_version && version_compare($provider_version, '1.8.4', '>=');

	return array(
		'ok' => $provider_callable
			&& 'smart-bootstrap-manager' === $provider
			&& $version_supported
			&& $provider_supported,
		'provider_callable' => $provider_callable,
		'provider' => $provider,
		'provider_version' => $provider_version,
		'minimum_provider_version' => '1.8.4',
		'contract_version' => $version,
		'contract_major_supported' => $version_supported,
	);
}

function aihl_sbm_static_visual_check(): array {
	$theme_root = trailingslashit(get_template_directory());
	$files = array(
		'resource/css/ai-html.css',
		'resource/css/aihl-menu-walker.css',
		'resource/css/aihl-bootstrap-bridge.css',
	);
	$violations = array();
	foreach ($files as $relative) {
		$path = $theme_root . $relative;
		$lines = is_readable($path) ? file($path, FILE_IGNORE_NEW_LINES) : array();
		foreach ((array) $lines as $index => $line) {
			$reason = '';
			if (preg_match('/--(?:primary|secondary|light|dark)\s*:/i', $line)) {
				$reason = 'legacy-color-alias';
			} elseif (preg_match('/letter-spacing\s*:\s*([^;]+)/i', $line, $tracking_match)) {
				$tracking = trim((string) $tracking_match[1]);
				if (!preg_match('/^(?:0(?:\.0+)?(?:[a-z%]+)?|inherit|initial|normal|unset|var\()/i', $tracking)) {
					$reason = 'local-letter-spacing';
				}
			} elseif (
				!preg_match('/var\(--(?:bs|sbin|canvas)-/i', $line)
				&& preg_match('/(?:color|background(?:-color)?|border(?:-[a-z-]+)?-color|fill|stroke)\s*:[^;]*(?:#[0-9a-f]{3,8}\b|rgba?\(\s*\d|hsla?\(\s*\d)/i', $line)
			) {
				$reason = 'raw-color';
			}
			if ('' !== $reason) {
				$violations[] = array(
					'file' => $relative,
					'line' => $index + 1,
					'reason' => $reason,
				);
			}
		}
	}
	return $violations;
}

function aihl_sbm_bootstrap_ownership_report(): array {
	$contract = function_exists('aihl_sbm_consumer_contract') ? aihl_sbm_consumer_contract() : array();
	$css_handle = (string) ($contract['bootstrap']['css_handle'] ?? '');
	$js_handle = (string) ($contract['bootstrap']['js_handle'] ?? '');
	$resource_file = trailingslashit(get_template_directory()) . 'inc/resource.php';
	$source = is_readable($resource_file) ? (string) file_get_contents($resource_file) : '';
	$conditional_fallback = false !== strpos($source, "wp_style_is('smart-bootstrap'")
		&& false !== strpos($source, "wp_script_is(\$candidate_handle")
		&& false !== strpos($source, "if (!\$bootstrap_handle)")
		&& false !== strpos($source, "if (\$bootstrap_script_handle)");

	return array(
		'ok' => 'smart-bootstrap' === $css_handle
			&& 'smart-bootstrap' === $js_handle
			&& $conditional_fallback,
		'css_handle' => $css_handle,
		'js_handle' => $js_handle,
		'conditional_fallback' => $conditional_fallback,
	);
}

function aihl_sbm_static_namespace_check(): array {
	$theme_root = trailingslashit(get_template_directory());
	$files = array(
		'header.php',
		'footer.php',
		'inc/integrations/smart-bootstrap-manager.php',
		'resource/css/ai-html.css',
		'resource/css/aihl-bootstrap-bridge.css',
		'resource/css/aihl-menu-walker.css',
	);
	$violations = array();
	foreach ($files as $relative) {
		$path = $theme_root . $relative;
		$content = is_readable($path) ? (string) file_get_contents($path) : '';
		if ($content !== '' && preg_match_all('/--sbin-[a-z0-9-]+\s*:/i', $content, $matches)) {
			$violations[$relative] = array_values(array_unique($matches[0]));
		}
	}
	return $violations;
}

function aihl_sbm_compliance_payload(): array {
	$matrix = aihl_sbm_option_compliance_matrix();
	$namespace_violations = aihl_sbm_static_namespace_check();
	$visual_violations = aihl_sbm_static_visual_check();
	$contract_report = aihl_sbm_contract_compatibility_report();
	$bootstrap_report = aihl_sbm_bootstrap_ownership_report();
	$unclassified_options = array_keys(array_filter($matrix, static function(array $option): bool {
		return 'unclassified' === ($option['classification'] ?? '');
	}));
	$canvas = array(
		'header' => function_exists('aihl_canvas_health_report') ? aihl_canvas_health_report('header') : array(),
		'footer' => function_exists('aihl_canvas_health_report') ? aihl_canvas_health_report('footer') : array(),
	);
	$canvas_ok = true;
	foreach ($canvas as $health) {
		if ('error' === ($health['status'] ?? 'inactive')) {
			$canvas_ok = false;
		}
	}
	$checks = array(
		array('code' => 'provider_active', 'ok' => function_exists('aihl_is_bootstrap_manager_active') && aihl_is_bootstrap_manager_active(), 'details' => 'Smart Bootstrap Manager is the active design provider.'),
		array('code' => 'consumer_contract', 'ok' => !empty($contract_report['ok']), 'details' => 'AI-HTML consumes a supported native SBM runtime contract.'),
		array('code' => 'namespace_ownership', 'ok' => empty($namespace_violations), 'details' => 'AI-HTML does not declare provider-owned --sbin-* tokens.'),
		array('code' => 'visual_token_ownership', 'ok' => empty($visual_violations), 'details' => 'Frontend CSS contains no local color aliases, raw governed colors or local tracking.'),
		array('code' => 'option_registry_coverage', 'ok' => empty($unclassified_options), 'details' => sprintf('%d/%d registered options explicitly classified.', count($matrix) - count($unclassified_options), count($matrix))),
		array('code' => 'canvas_governance', 'ok' => $canvas_ok, 'details' => 'Active Canvas overrides declare a valid design mode and pass token validation.'),
		array('code' => 'motion_ownership', 'ok' => function_exists('aihl_should_load_animation_assets') && !aihl_should_load_animation_assets(), 'details' => 'Theme-owned motion libraries are disabled while SBM is active.'),
		array('code' => 'bootstrap_ownership', 'ok' => !empty($bootstrap_report['ok']), 'details' => 'Theme Bootstrap assets are fallback-only when the SBM handles are unavailable.'),
	);
	$passed = count(array_filter($checks, static fn(array $check): bool => !empty($check['ok'])));

	return array(
		'provider' => array(
			'active' => function_exists('aihl_is_bootstrap_manager_active') && aihl_is_bootstrap_manager_active(),
			'version' => defined('SBIN_VERSION') ? SBIN_VERSION : null,
			'contract_version' => function_exists('aihl_sbm_contract_value') ? aihl_sbm_contract_value(array('contract_version'), '') : '',
		),
		'governance' => function_exists('aihl_sbm_design_governance') ? aihl_sbm_design_governance() : array(),
		'score' => $checks ? (int) round(($passed / count($checks)) * 100) : 0,
		'passed' => $passed,
		'total' => count($checks),
		'checks' => $checks,
		'contract' => $contract_report,
		'bootstrap' => $bootstrap_report,
		'namespace_violations' => $namespace_violations,
		'visual_violations' => $visual_violations,
		'unclassified_options' => $unclassified_options,
		'options_count' => count($matrix),
		'options' => $matrix,
		'canvas' => $canvas,
	);
}

function aihl_api_compliance_payload(): array {
	$google_checks = aihl_google_compliance_checks();
	$google_passed = count(array_filter($google_checks, static fn($check) => !empty($check['ok'])));
	$sbm = aihl_sbm_compliance_payload();
	$passed = $google_passed + (int) $sbm['passed'];
	$total = count($google_checks) + (int) $sbm['total'];
	return array(
		'score' => $total ? (int) round(($passed / $total) * 100) : 0,
		'passed' => $passed,
		'total' => $total,
		'google' => array(
			'score' => $google_checks ? (int) round(($google_passed / count($google_checks)) * 100) : 0,
			'passed' => $google_passed,
			'total' => count($google_checks),
			'checks' => $google_checks,
		),
		'sbm' => $sbm,
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
