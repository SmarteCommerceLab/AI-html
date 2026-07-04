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

	/* ── Schema opzioni: descrive all'AI quali campi puo modificare ── */
	register_rest_route('aihtml/v1', '/ai/options/schema', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => 'aihl_ai_can_read',
		'callback'            => 'aihl_ai_rest_options_schema',
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

/* ============================================================================
 * Schema opzioni tema: whitelist dei campi modificabili via API
 * ============================================================================ */

function aihl_ai_options_whitelist(): array {
	return array(
		// Identita e media
		'sito_descrizione'          => array('type' => 'text', 'group' => 'sito'),
		'site_logo_url'             => array('type' => 'url', 'group' => 'media'),
		'site_logo_transparent_url' => array('type' => 'url', 'group' => 'media'),
		'site_logo_light_url'       => array('type' => 'url', 'group' => 'media'),
		'footer_logo_url'           => array('type' => 'url', 'group' => 'media'),
		// Header
		'header_render_mode'      => array('type' => 'enum', 'values' => array('native', 'canvas'), 'group' => 'header'),
		'header_structure'        => array('type' => 'enum', 'values' => array('standard', 'dualbar', 'centered', 'topbar-nav', 'mega-centered', 'sidebar', 'triple-row', 'stacked-centered'), 'group' => 'header'),
		'header_nav_layout'       => array('type' => 'enum', 'values' => array('clean', 'pills', 'underline', 'compact'), 'group' => 'header'),
		'header_nav_text_variant' => array('type' => 'enum', 'values' => array('normal', 'uppercase', 'lowercase', 'italic', 'uppercase-italic', 'lowercase-italic'), 'group' => 'header'),
		'header_nav_font_weight'  => array('type' => 'enum', 'values' => array('300', '400', '500', '600', '700', '800'), 'group' => 'header'),
		'header_nav_letter_spacing' => array('type' => 'float', 'min' => 0, 'max' => 0.2, 'group' => 'header'),
		'header_overlay_mode'      => array('type' => 'enum', 'values' => array('auto', 'always', 'never'), 'group' => 'header'),
		'header_overlay_opacity'   => array('type' => 'float', 'min' => 0, 'max' => 1, 'group' => 'header'),
		'header_overlay_blur'      => array('type' => 'int', 'min' => 0, 'max' => 24, 'group' => 'header'),
		'header_sticky_style'     => array('type' => 'enum', 'values' => array('solid', 'blur', 'transparent', 'gradient-fade'), 'group' => 'header'),
		'header_search_style'     => array('type' => 'enum', 'values' => array('none', 'icon-dropdown', 'icon-fullscreen', 'inline'), 'group' => 'header'),
		'header_topbar_scroll_behavior' => array('type' => 'enum', 'values' => array('scroll-away', 'sticky'), 'group' => 'header'),
		'header_show_logo'        => array('type' => 'bool', 'group' => 'header'),
		'header_show_cta'         => array('type' => 'bool', 'group' => 'header'),
		'menu_dropdown_indicator' => array('type' => 'bool', 'group' => 'header'),
		'header_show_login'       => array('type' => 'bool', 'group' => 'header'),
		'header_cta_label'        => array('type' => 'text', 'group' => 'header'),
		'header_cta_url'          => array('type' => 'url', 'group' => 'header'),
		'header_login_label'      => array('type' => 'text', 'group' => 'header'),
		'header_login_url'        => array('type' => 'url', 'group' => 'header'),
		// Page background defaults
		'page_bg_type'            => array('type' => 'enum', 'values' => array('default', 'color', 'image', 'pattern'), 'group' => 'page_background'),
		'page_bg_color'           => array('type' => 'text', 'group' => 'page_background'),
		'page_bg_image'           => array('type' => 'url', 'group' => 'page_background'),
		'page_bg_image_opacity'   => array('type' => 'float', 'min' => 0, 'max' => 1, 'group' => 'page_background'),
		'page_bg_image_size'      => array('type' => 'enum', 'values' => array('cover', 'contain', 'auto'), 'group' => 'page_background'),
		'page_bg_pattern'         => array('type' => 'enum', 'values' => array('none', 'dots', 'grid', 'diagonal', 'cross'), 'group' => 'page_background'),
		'page_bg_overlay_color'   => array('type' => 'text', 'group' => 'page_background'),
		'page_bg_overlay_opacity' => array('type' => 'float', 'min' => 0, 'max' => 1, 'group' => 'page_background'),
		// Mobile nav
		'mobile_nav_style'        => array('type' => 'enum', 'values' => array('rail', 'bottom-bar', 'none'), 'group' => 'mobile'),
		'mobile_rail_enable'      => array('type' => 'bool', 'group' => 'mobile'),
		'mobile_rail_position'    => array('type' => 'enum', 'values' => array('left', 'right'), 'group' => 'mobile'),
		'mobile_rail_autohide'    => array('type' => 'bool', 'group' => 'mobile'),
		// Articoli
		'article_author_box_style' => array(
			'type' => 'enum',
			'values' => array('simple', 'compact', 'card', 'banner', 'editorial', 'enterprise', 'impact', 'signature', 'none'),
			'group' => 'article',
		),
		// Footer
		'footer_render_mode'       => array('type' => 'enum', 'values' => array('native', 'canvas'), 'group' => 'footer'),
		'footer_variant'          => array('type' => 'enum', 'values' => array('enterprise', 'futuristic', 'corporate', 'compact', 'mega-footer', 'minimal', 'cta-footer'), 'group' => 'footer'),
		'footer_columns_count'    => array('type' => 'int', 'min' => 3, 'max' => 5, 'group' => 'footer'),
		'footer_background_enable' => array('type' => 'bool', 'group' => 'footer'),
		'footer_background_image' => array('type' => 'url', 'group' => 'footer'),
		'footer_background_remote_url' => array('type' => 'url', 'group' => 'footer'),
		'footer_background_opacity' => array('type' => 'float', 'min' => 0, 'max' => 1, 'group' => 'footer'),
		'footer_background_position' => array('type' => 'enum', 'values' => array('center center', 'center top', 'center bottom', 'left center', 'right center'), 'group' => 'footer'),
		'footer_background_size'  => array('type' => 'enum', 'values' => array('auto', 'cover', 'contain'), 'group' => 'footer'),
		'footer_background_repeat' => array('type' => 'enum', 'values' => array('no-repeat', 'repeat', 'repeat-x', 'repeat-y'), 'group' => 'footer'),
		'footer_overlay_opacity'  => array('type' => 'float', 'min' => 0, 'max' => 1, 'group' => 'footer'),
		'footer_overlay_tone'     => array('type' => 'enum', 'values' => array('body', 'primary', 'dark', 'light'), 'group' => 'footer'),
		'footer_cta_title'        => array('type' => 'text', 'group' => 'footer'),
		'footer_cta_subtitle'     => array('type' => 'text', 'group' => 'footer'),
		'footer_cta_btn_label'    => array('type' => 'text', 'group' => 'footer'),
		'footer_cta_btn_url'      => array('type' => 'url', 'group' => 'footer'),
		'footer_cta_btn2_label'   => array('type' => 'text', 'group' => 'footer'),
		'footer_cta_btn2_url'     => array('type' => 'url', 'group' => 'footer'),
		// Contatti
		'contatti_telefono'       => array('type' => 'text', 'group' => 'contatti'),
		'contatti_email'          => array('type' => 'email', 'group' => 'contatti'),
		'contatti_indirizzo'      => array('type' => 'text', 'group' => 'contatti'),
		'contatti_maps'           => array('type' => 'maps_html', 'group' => 'contatti'),
		'contactform_contacts'    => array('type' => 'int', 'min' => 0, 'max' => 999999999, 'group' => 'integrazioni'),
		'mailchip_footer'         => array('type' => 'int', 'min' => 0, 'max' => 999999999, 'group' => 'integrazioni'),
	);
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
	foreach (get_pages(array('post_status' => array('publish', 'draft'))) as $p) {
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

	$status = isset($body['status']) && in_array($body['status'], array('publish', 'draft'), true) ? $body['status'] : 'draft';

	$page_id = wp_insert_post(array(
		'post_type'    => 'page',
		'post_title'   => $title,
		'post_status'  => $status,
		'post_content' => isset($body['content']) ? wp_kses_post((string) $body['content']) : '',
	));

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
		'template' => $template ?: 'default',
		'status'   => $status,
		'url'      => get_permalink($page_id),
		'edit_builder' => ('smart-site-home.php' === $template || 'smart-site-builder.php' === $template || 'smart-site-blog.php' === $template)
			? rest_url('sbs/v1/ai/pages/' . $page_id . '/builder')
			: null,
	));
}
