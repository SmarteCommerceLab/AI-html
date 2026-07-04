<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('aihl_require_files')) {
	function aihl_require_files($relative_paths) {
		if (!is_array($relative_paths)) {
			return;
		}

		foreach ($relative_paths as $relative_path) {
			$relative_path = is_string($relative_path) ? trim($relative_path) : '';
			if ($relative_path === '') {
				continue;
			}

			$full_path = trailingslashit(get_template_directory()) . ltrim($relative_path, '/\\');
			if (file_exists($full_path)) {
				require_once $full_path;
			}
		}
	}
}
