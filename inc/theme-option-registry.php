<?php
/**
 * Canonical registry for AI-HTML public theme options.
 */
if (!defined('ABSPATH')) {
	exit;
}

function aihl_theme_option_registry(): array {
	$enum = static function (array $values, string $group, $default = ''): array {
		return compact('values', 'group', 'default') + array('type' => 'enum');
	};
	$field = static function (string $type, string $group, $default = '', array $extra = array()): array {
		return $extra + compact('type', 'group', 'default');
	};

	return array(
		'sito_descrizione' => $field('textarea', 'sito'),
		'site_logo_url' => $field('url', 'media'),
		'site_logo_transparent_url' => $field('url', 'media'),
		'site_logo_light_url' => $field('url', 'media'),
		'footer_logo_url' => $field('url', 'media'),

		'header_render_mode' => $enum(array('native', 'canvas'), 'header', 'native'),
		'header_canvas_slot_id' => $field('key', 'header'),
		'header_structure' => $enum(array('standard', 'dualbar', 'centered', 'topbar-nav', 'mega-centered', 'sidebar', 'triple-row', 'stacked-centered'), 'header', 'standard'),
		'header_nav_layout' => $enum(array('clean', 'pills', 'underline', 'compact'), 'header', 'clean'),
		'header_nav_text_variant' => $enum(array('normal', 'uppercase', 'lowercase', 'italic', 'uppercase-italic', 'lowercase-italic'), 'header', 'normal'),
		'header_nav_font_weight' => $enum(array('300', '400', '500', '600', '700', '800'), 'header', '500'),
		'header_nav_letter_spacing' => $field('float', 'header', 0, array('min' => 0, 'max' => 0.2)),
		'header_overlay_mode' => $enum(array('auto', 'always', 'never'), 'header', 'auto'),
		'header_overlay_opacity' => $field('float', 'header', 0.18, array('min' => 0, 'max' => 1)),
		'header_overlay_blur' => $field('int', 'header', 8, array('min' => 0, 'max' => 24)),
		'header_sticky_style' => $enum(array('solid', 'blur', 'transparent', 'gradient-fade'), 'header', 'solid'),
		'header_search_style' => $enum(array('none', 'icon-dropdown', 'icon-fullscreen', 'inline'), 'header', 'none'),
		'header_topbar_scroll_behavior' => $enum(array('scroll-away', 'sticky'), 'header', 'scroll-away'),
		'header_show_logo' => $field('bool', 'header', true),
		'header_show_cta' => $field('bool', 'header', true),
		'header_show_login' => $field('bool', 'header', true),
		'menu_dropdown_indicator' => $field('bool', 'header', true),
		'header_cta_label' => $field('text', 'header'),
		'header_cta_url' => $field('url', 'header'),
		'header_login_label' => $field('text', 'header'),
		'header_login_url' => $field('url', 'header'),

		'mobile_nav_style' => $enum(array('rail', 'bottom-bar', 'none'), 'mobile', 'rail'),
		'mobile_rail_enable' => $field('bool', 'mobile', true),
		'mobile_rail_position' => $enum(array('left', 'right'), 'mobile', 'right'),
		'mobile_rail_autohide' => $field('bool', 'mobile', false),

		'blog_layout' => $enum(array('grid', 'list', 'magazine'), 'article', 'grid'),
		'blog_sidebar' => $field('bool', 'article', false),
		'article_image_size_control' => $field('bool', 'article', false),
		'article_next_prev' => $field('bool', 'article', false),
		'article_related' => $field('bool', 'article', false),
		'article_related_link' => $field('bool', 'article', false),
		'article_content_size' => $field('int', 'article', 1280, array('min' => 320, 'max' => 2560)),
		'article_author_box_style' => $enum(array('simple', 'compact', 'card', 'banner', 'editorial', 'enterprise', 'impact', 'signature', 'none'), 'article', 'card'),

		'page_bg_type' => $enum(array('default', 'color', 'image', 'pattern'), 'page_background', 'default'),
		'page_bg_color' => $field('color', 'page_background'),
		'page_bg_image' => $field('url', 'page_background'),
		'page_bg_image_opacity' => $field('float', 'page_background', 1, array('min' => 0, 'max' => 1)),
		'page_bg_image_size' => $enum(array('cover', 'contain', 'auto'), 'page_background', 'cover'),
		'page_bg_pattern' => $enum(array('none', 'dots', 'grid', 'diagonal', 'cross'), 'page_background', 'none'),
		'page_bg_overlay_color' => $field('color', 'page_background'),
		'page_bg_overlay_opacity' => $field('float', 'page_background', 0.18, array('min' => 0, 'max' => 1)),

		'footer_render_mode' => $enum(array('native', 'canvas'), 'footer', 'native'),
		'footer_canvas_slot_id' => $field('key', 'footer'),
		'footer_variant' => $enum(array('enterprise', 'futuristic', 'corporate', 'compact', 'mega-footer', 'minimal', 'cta-footer'), 'footer', 'enterprise'),
		'footer_columns_count' => $field('int', 'footer', 4, array('min' => 3, 'max' => 5)),
		'footer_background_enable' => $field('bool', 'footer', true),
		'footer_background_image' => $field('url', 'footer'),
		'footer_background_remote_url' => $field('url', 'footer'),
		'footer_background_opacity' => $field('float', 'footer', 0.12, array('min' => 0, 'max' => 1)),
		'footer_background_position' => $enum(array('center center', 'center top', 'center bottom', 'left center', 'right center'), 'footer', 'center center'),
		'footer_background_size' => $enum(array('auto', 'cover', 'contain'), 'footer', 'contain'),
		'footer_background_repeat' => $enum(array('no-repeat', 'repeat', 'repeat-x', 'repeat-y'), 'footer', 'no-repeat'),
		'footer_overlay_opacity' => $field('float', 'footer', 0, array('min' => 0, 'max' => 1)),
		'footer_overlay_tone' => $enum(array('body', 'primary', 'dark', 'light'), 'footer', 'body'),
		'footer_cta_title' => $field('text', 'footer'),
		'footer_cta_subtitle' => $field('text', 'footer'),
		'footer_cta_btn_label' => $field('text', 'footer'),
		'footer_cta_btn_url' => $field('url', 'footer', '#'),
		'footer_cta_btn2_label' => $field('text', 'footer'),
		'footer_cta_btn2_url' => $field('url', 'footer', '#'),

		'contatti_telefono' => $field('text', 'contatti'),
		'contatti_email' => $field('email', 'contatti'),
		'contatti_indirizzo' => $field('text', 'contatti'),
		'contatti_maps' => $field('maps_html', 'contatti'),
		'contactform_contacts' => $field('int', 'integrazioni', 0, array('min' => 0, 'max' => 999999999)),
		'mailchip_footer' => $field('int', 'integrazioni', 0, array('min' => 0, 'max' => 999999999)),
	);
}

function aihl_sanitize_registered_option($value, string $field) {
	$definition = aihl_theme_option_registry()[$field] ?? null;
	if (!$definition) {
		return null;
	}

	switch ($definition['type']) {
		case 'bool':
			return (bool) $value;
		case 'int':
			return max((int) ($definition['min'] ?? PHP_INT_MIN), min((int) ($definition['max'] ?? PHP_INT_MAX), absint($value)));
		case 'float':
			$value = is_numeric(str_replace(',', '.', (string) $value)) ? (float) str_replace(',', '.', (string) $value) : (float) $definition['default'];
			return max((float) ($definition['min'] ?? -PHP_FLOAT_MAX), min((float) ($definition['max'] ?? PHP_FLOAT_MAX), $value));
		case 'enum':
			$value = sanitize_text_field((string) $value);
			return in_array($value, $definition['values'], true) ? $value : $definition['default'];
		case 'url':
			return esc_url_raw((string) $value);
		case 'email':
			return sanitize_email((string) $value);
		case 'textarea':
			return sanitize_textarea_field((string) $value);
		case 'color':
			return sanitize_hex_color((string) $value) ?: '';
		case 'key':
			return sanitize_key((string) $value);
		case 'maps_html':
			return function_exists('aihl_sanitize_maps_embed') ? aihl_sanitize_maps_embed($value) : wp_kses_post((string) $value);
		default:
			return sanitize_text_field((string) $value);
	}
}
