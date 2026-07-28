<?php
declare(strict_types=1);

define('ABSPATH', __DIR__);
define('AIHL_TEXT_DOMAIN', 'ai_html');
define('AIHL_OPTION_BASE', 'ai_html');
define('AIHL_VERSION', '1.13.0');
define('AIHL_UPDATE_ENDPOINT', 'https://repository.example.test/theme.json');
define('SBIN_VERSION', '1.8.4');

class WP_REST_Request {}
class WP_REST_Server {
	public const READABLE = 'GET';
	public const EDITABLE = 'PUT';
	public const CREATABLE = 'POST';
}

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1): void {}
function add_action($hook, $callback, $priority = 10, $accepted_args = 1): void {}
function sanitize_key($value): string {
	return preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $value)) ?? '';
}
function sanitize_html_class($value): string {
	return sanitize_key($value);
}
function get_template_directory(): string {
	return dirname(__DIR__);
}
function trailingslashit($value): string {
	return rtrim((string) $value, '/\\') . DIRECTORY_SEPARATOR;
}
function smart_bootstrap_manager_get_design_governance(): array {
	return array(
		'smart_bootstrap_option_design_mode' => 'governed',
		'smart_bootstrap_option_design_inherit_colors' => true,
		'smart_bootstrap_option_design_inherit_typography' => true,
		'smart_bootstrap_option_design_inherit_spacing' => true,
		'smart_bootstrap_option_design_inherit_radius' => true,
		'smart_bootstrap_option_design_inherit_components' => true,
		'smart_bootstrap_option_design_inherit_motion' => true,
	);
}
function smart_bootstrap_manager_consumer_contract($consumer): array {
	return array(
		'contract_version' => '1.0.0',
		'provider' => 'smart-bootstrap-manager',
		'provider_version' => '1.8.4',
		'consumer' => $consumer,
		'bootstrap' => array(
			'css_handle' => 'smart-bootstrap',
			'js_handle' => 'smart-bootstrap',
			'body_classes' => array(),
		),
		'motion' => array('available' => false),
	);
}

require dirname(__DIR__) . '/inc/theme-option-registry.php';
require dirname(__DIR__) . '/inc/integrations/smart-bootstrap-manager.php';
require dirname(__DIR__) . '/inc/integrations/management-api.php';

function sbm_compliance_runtime_assert(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

sbm_compliance_runtime_assert(aihl_sbm_contract_compatibility_report()['ok'], 'contratto SBM nativo non riconosciuto');
sbm_compliance_runtime_assert(aihl_sbm_bootstrap_ownership_report()['ok'], 'ownership Bootstrap non verificata');
sbm_compliance_runtime_assert(array() === aihl_sbm_static_visual_check(), 'violazioni visuali residue');

$matrix = aihl_sbm_option_compliance_matrix();
sbm_compliance_runtime_assert(71 === count($matrix), 'registro opzioni incompleto');
sbm_compliance_runtime_assert(
	0 === count(array_filter($matrix, static fn(array $option): bool => empty($option['compliant']))),
	'opzioni non conformi o non classificate'
);

echo "AI-HTML SBM compliance runtime OK\n";
