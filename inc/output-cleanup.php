<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('aihl_strip_bom_from_output')) {
	function aihl_strip_bom_from_output($buffer) {
		if (!is_string($buffer) || $buffer === '') {
			return $buffer;
		}

		return str_replace(
			array("\xEF\xBB\xBF", '&#xFEFF;', '&#xfeff;', '&#65279;'),
			'',
			$buffer
		);
	}
}

if (!function_exists('aihl_start_output_cleanup')) {
	function aihl_start_output_cleanup() {
		if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST) || PHP_SAPI === 'cli') {
			return;
		}

		ob_start('aihl_strip_bom_from_output');
	}
}

add_action('template_redirect', 'aihl_start_output_cleanup', 0);
