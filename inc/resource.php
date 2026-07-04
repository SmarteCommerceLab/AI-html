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
		return aihl_is_template(array('about.php', 'contact.php')) || aihl_queried_post_contains(' wow ') || aihl_queried_post_contains('wow ');
	}
}

if (!function_exists('aihl_should_load_owl_assets')) {
	function aihl_should_load_owl_assets() {
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
	aihl_enqueue_style_if_exists('solid-6.4.2', 'resource/css/fontawesome/solid.min.css', array('font-awesome-6.4.2'), AIHL_UNICODE);
	if (aihl_should_load_brand_icons()) {
		aihl_enqueue_style_if_exists('brands-6.4.2', 'resource/css/fontawesome/brands.min.css', array('font-awesome-6.4.2'), AIHL_UNICODE);
	}
}, 99);

add_action('wp_enqueue_scripts', function() {
	if (is_admin()) {
		return;
	}

	$main_deps = array();

	$has_bootstrap_script = wp_script_is('smart-bootstrap', 'enqueued')
		|| wp_script_is('smart-bootstrap', 'registered')
		|| wp_script_is('smart-bootstrap-bundle', 'enqueued')
		|| wp_script_is('smart-bootstrap-bundle', 'registered')
		|| wp_script_is('bootstrap', 'enqueued')
		|| wp_script_is('bootstrap', 'registered');

	if (!$has_bootstrap_script) {
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
		wp_script_add_data('ai-html-main', 'defer', true);
	}
}, 100);

add_action('wp_head', function() {
	echo '<!-- This site is using AI-HTML Theme v' . esc_html(AIHL_VERSION) . ' -->';
}, 1);
