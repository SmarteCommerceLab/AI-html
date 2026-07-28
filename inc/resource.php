<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('aihl_enqueue_style_if_exists')) {
	function aihl_enqueue_style_if_exists($handle, $relative_path, $deps = array(), $ver = null, $media = 'all') {
		$file_path = AIHL_DIR_PATH . '/' . ltrim($relative_path, '/');
		if (!file_exists($file_path)) {
			return false;
		}
		wp_enqueue_style($handle, AIHL_DIR_URL . '/' . ltrim($relative_path, '/'), $deps, $ver, $media);
		return true;
	}
}

if (!function_exists('aihl_enqueue_script_if_exists')) {
	function aihl_enqueue_script_if_exists($handle, $relative_path, $deps = array(), $ver = null, $in_footer = true) {
		$file_path = AIHL_DIR_PATH . '/' . ltrim($relative_path, '/');
		if (!file_exists($file_path)) {
			return false;
		}
		wp_enqueue_script($handle, AIHL_DIR_URL . '/' . ltrim($relative_path, '/'), $deps, $ver, $in_footer);
		return true;
	}
}

if (!function_exists('aihl_queried_post_contains')) {
	function aihl_queried_post_contains($needle) {
		if (!is_singular()) {
			return false;
		}

		$post = get_post();
		if (!$post instanceof WP_Post) {
			return false;
		}

		return strpos((string) $post->post_content, $needle) !== false;
	}
}

if (!function_exists('aihl_is_template')) {
	function aihl_is_template($templates) {
		$templates = (array) $templates;
		$current = get_page_template_slug(get_queried_object_id());
		return $current !== '' && in_array($current, $templates, true);
	}
}

if (!function_exists('aihl_should_load_animation_assets')) {
	function aihl_should_load_animation_assets() {
		if (function_exists('aihl_is_bootstrap_manager_active') && aihl_is_bootstrap_manager_active()) {
			return false;
		}
		return aihl_is_template(array('about.php', 'contact.php')) || aihl_queried_post_contains(' wow ') || aihl_queried_post_contains('wow ');
	}
}

if (!function_exists('aihl_should_load_owl_assets')) {
	function aihl_should_load_owl_assets() {
		if (function_exists('aihl_is_bootstrap_manager_active') && aihl_is_bootstrap_manager_active()) {
			return false;
		}
		return aihl_queried_post_contains('testimonial-carousel') || aihl_queried_post_contains('owl-carousel');
	}
}

if (!function_exists('aihl_should_load_brand_icons')) {
	function aihl_should_load_brand_icons() {
		if (is_single() || aihl_is_template(array('about.php', 'contact.php'))) {
			return true;
		}

		if (function_exists('aihl_get_site_builder_social_links')) {
			return !empty(aihl_get_site_builder_social_links());
		}

		return aihl_queried_post_contains('fab ') || aihl_queried_post_contains('fa-brands');
	}
}

if (!function_exists('aihl_core_icon_names')) {
	function aihl_core_icon_names() {
		return array(
			'address-book', 'arrow-down', 'arrow-right', 'bars', 'book', 'book-open',
			'boxes-stacked', 'briefcase', 'broom', 'building', 'bullhorn', 'cart-shopping',
			'chart-line', 'chevron-down', 'chevron-left', 'chevron-right', 'circle-check',
			'clock', 'code', 'cube', 'cubes', 'database', 'envelope', 'facebook-f',
			'folder-open', 'graduation-cap', 'headset', 'house', 'industry', 'info-circle',
			'instagram', 'key', 'layer-group', 'linkedin-in', 'location-dot', 'newspaper',
			'palette', 'paper-plane', 'pen-nib', 'phone', 'robot', 'rotate-left', 'search',
			'share-nodes', 'shoe-prints', 'signal', 'sliders', 'triangle-exclamation',
			'trophy', 'twitter', 'user', 'users', 'video', 'whatsapp', 'youtube',
		);
	}
}

if (!function_exists('aihl_collect_configured_icon_classes')) {
	function aihl_collect_configured_icon_classes() {
		$sources = array(
			get_option(AIHL_OPTION_BASE . '_general', array()),
		);

		$post = is_singular() ? get_post() : null;
		if ($post instanceof WP_Post) {
			$sources[] = $post->post_content;
		}

		$menu_ids = array_values(array_unique(array_filter(array_map('absint', get_nav_menu_locations()))));
		foreach ($menu_ids as $menu_id) {
			$items = wp_get_nav_menu_items($menu_id, array('update_post_term_cache' => false));
			foreach ((array) $items as $item) {
				$sources[] = get_post_meta($item->ID, '_aihl_menu_icon', true);
			}
		}

		$serialized = wp_json_encode($sources);
		if (!is_string($serialized) || !preg_match_all('/\bfa-([a-z0-9-]+)\b/i', $serialized, $matches)) {
			return array();
		}

		return array_values(array_unique(array_map('strtolower', $matches[1])));
	}
}

if (!function_exists('aihl_requires_full_icon_font')) {
	function aihl_requires_full_icon_font() {
		static $requires_full = null;
		if (null !== $requires_full) {
			return $requires_full;
		}

		$utility_classes = array(
			'brands', 'classic', 'regular', 'sharp', 'solid', 'spin', 'pulse', 'border',
			'pull-left', 'pull-right', 'fw', 'ul', 'li', 'inverse', 'stack', 'stack-1x',
			'stack-2x', 'xs', 'sm', 'lg', 'xl', '2xl', '1x', '2x', '3x', '4x', '5x',
			'6x', '7x', '8x', '9x', '10x',
		);
		$allowed = array_merge(aihl_core_icon_names(), $utility_classes);
		$unknown = array_diff(aihl_collect_configured_icon_classes(), $allowed);
		$requires_full = (bool) apply_filters('aihl_use_full_font_awesome', !empty($unknown), $unknown);

		return $requires_full;
	}
}

add_action('wp_enqueue_scripts', function() {
	if (is_admin()) {
		return;
	}

	$bootstrap_handle = wp_style_is('smart-bootstrap', 'enqueued') || wp_style_is('smart-bootstrap', 'registered')
		? 'smart-bootstrap'
		: null;

	if (!$bootstrap_handle) {
		wp_enqueue_style(
			'aihl-bootstrap-fallback',
			'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css',
			array(),
			'5.3.8'
		);
		$bootstrap_handle = 'aihl-bootstrap-fallback';
	}

	$theme_css_deps = $bootstrap_handle ? array($bootstrap_handle) : array();

	aihl_enqueue_style_if_exists('ai-html-theme', 'resource/css/ai-html.css', $theme_css_deps, AIHL_UNICODE);
	aihl_enqueue_style_if_exists('aihl-menu-walker', 'resource/css/aihl-menu-walker.css', array('ai-html-theme'), AIHL_UNICODE);

	if (aihl_should_load_animation_assets()) {
		aihl_enqueue_style_if_exists('ai-html-animate', 'lib/animate/animate.min.css', array('ai-html-theme'), AIHL_UNICODE);
	}

	if (aihl_should_load_owl_assets()) {
		aihl_enqueue_style_if_exists('ai-html-owlcarousel', 'lib/owlcarousel/assets/owl.carousel.min.css', array('ai-html-theme'), AIHL_UNICODE);
	}

	aihl_enqueue_style_if_exists('font-awesome-6.4.2', 'resource/css/fontawesome/fontawesome.min.css', array(), AIHL_UNICODE);
	$full_icon_font = aihl_requires_full_icon_font();
	$solid_path = $full_icon_font ? 'resource/css/fontawesome/solid.min.css' : 'resource/css/fontawesome/aihl-solid-core.min.css';
	aihl_enqueue_style_if_exists('solid-6.4.2', $solid_path, array('font-awesome-6.4.2'), AIHL_UNICODE);
	aihl_enqueue_style_if_exists('regular-6.4.2', 'resource/css/fontawesome/regular.min.css', array('font-awesome-6.4.2'), AIHL_UNICODE);
	if (aihl_should_load_brand_icons()) {
		$brands_path = $full_icon_font ? 'resource/css/fontawesome/brands.min.css' : 'resource/css/fontawesome/aihl-brands-core.min.css';
		aihl_enqueue_style_if_exists('brands-6.4.2', $brands_path, array('font-awesome-6.4.2'), AIHL_UNICODE);
	}
}, 99);

add_action('wp_enqueue_scripts', function() {
	if (is_admin()) {
		return;
	}

	$main_deps = array();
	$bootstrap_script_handle = null;
	foreach (array('smart-bootstrap', 'smart-bootstrap-bundle', 'bootstrap') as $candidate_handle) {
		if (wp_script_is($candidate_handle, 'enqueued') || wp_script_is($candidate_handle, 'registered')) {
			$bootstrap_script_handle = $candidate_handle;
			break;
		}
	}

	if ($bootstrap_script_handle) {
		$main_deps[] = $bootstrap_script_handle;
	} else {
		wp_enqueue_script(
			'aihl-bootstrap-fallback',
			'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js',
			array(),
			'5.3.8',
			true
		);
		$main_deps[] = 'aihl-bootstrap-fallback';
	}

	if (aihl_should_load_animation_assets()) {
		if (aihl_enqueue_script_if_exists('ai-html-wow', 'lib/wow/wow.min.js', array(), AIHL_UNICODE, true)) {
			$main_deps[] = 'ai-html-wow';
		}
	}

	if (aihl_should_load_owl_assets()) {
		if (aihl_enqueue_script_if_exists('ai-html-owl-carousel', 'lib/owlcarousel/owl.carousel.min.js', array('jquery'), AIHL_UNICODE, true)) {
			$main_deps[] = 'jquery';
			$main_deps[] = 'ai-html-owl-carousel';
		}
	}

	if (aihl_enqueue_script_if_exists('ai-html-main', 'resource/js/main.js', array_values(array_unique($main_deps)), AIHL_UNICODE, true)) {
		wp_script_add_data('ai-html-main', 'strategy', 'defer');
	}
}, 100);

add_action('wp_head', function() {
	echo '<!-- This site is using AI-HTML Theme v' . esc_html(AIHL_VERSION) . ' -->';
}, 1);
