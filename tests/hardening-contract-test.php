<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$files = array(
	'functions' => file_get_contents($root . '/functions.php'),
	'auth' => file_get_contents($root . '/inc/integrations/ai-auth-core.php'),
	'updater' => file_get_contents($root . '/inc/class-aihl-public-theme-updater.php'),
	'reset' => file_get_contents($root . '/inc/smart-reset.php'),
	'openapi' => file_get_contents($root . '/inc/integrations/ai-api.php'),
	'admin' => file_get_contents($root . '/inc/admin/admin-hub.php'),
	'subtitle' => file_get_contents($root . '/inc/theme/post-occhiello.php'),
	'resource' => file_get_contents($root . '/inc/resource.php'),
	'workflow' => file_get_contents($root . '/.github/workflows/release.yml'),
);

foreach ($files as $name => $contents) {
	if ($contents === false) {
		throw new RuntimeException("Unable to read {$name}.");
	}
}

$required = array(
	'functions' => array("define('AIHL_UNICODE', AIHL_VERSION)"),
	'updater' => array('if (is_array($cached))', 'is_allowed_download_url', 'verify_package_download', 'hash_equals', 'sha256'),
	'reset' => array('aihl_smart_reset_download', 'aihl_reset_snapshot_', 'Require all denied', 'wp_normalize_path'),
	'openapi' => array('aihl_ai_openapi_path_parameters', "'in' => 'path'", "'required' => true", '$metadata[$path]'),
	'admin' => array('admin_post_aihl_download_openapi', "check_admin_referer('aihl_download_openapi')"),
	'subtitle' => array('aihl_post_subtitle_nonce', 'wp_is_post_autosave', 'sanitize_text_field', 'delete_post_meta'),
	'resource' => array('$bootstrap_script_handle', '$main_deps[] = $bootstrap_script_handle'),
	'workflow' => array("--exclude 'tests/'", "--exclude 'build-slot.php'", 'Validate release package', 'Verify public deployment'),
);

foreach ($required as $file => $needles) {
	foreach ($needles as $needle) {
		if (strpos($files[$file], $needle) === false) {
			throw new RuntimeException("Missing hardening contract in {$file}: {$needle}");
		}
	}
}

if (strpos($files['auth'], "get_param('api_key')") !== false) {
	throw new RuntimeException('API keys must not be accepted from request parameters.');
}
if (strpos($files['reset'], "'url'   => trailingslashit") !== false) {
	throw new RuntimeException('Reset snapshots must not expose a public upload URL.');
}

echo "AI-HTML hardening contract OK\n";
