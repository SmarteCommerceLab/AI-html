<?php
declare(strict_types=1);

define('ABSPATH', __DIR__);
define('SBIN_VERSION', '1.8.4');

$GLOBALS['sbm_runtime_mode'] = 'governed';

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1): void {}
function add_action($hook, $callback, $priority = 10, $accepted_args = 1): void {}
function sanitize_key($value): string {
	return preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $value)) ?? '';
}
function sanitize_html_class($value): string {
	return sanitize_key($value);
}
function smart_bootstrap_manager_get_design_governance(): array {
	return array(
		'smart_bootstrap_option_design_mode' => $GLOBALS['sbm_runtime_mode'],
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

require dirname(__DIR__) . '/inc/integrations/smart-bootstrap-manager.php';

function governance_mode_assert(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

$governed = aihl_sbm_constrain_design_mode('autonomous');
governance_mode_assert(!$governed['allowed'], 'autonomous non bloccato sotto governed');
governance_mode_assert('governed' === $governed['effective'], 'modalita governed non imposta');

$GLOBALS['sbm_runtime_mode'] = 'adaptive';
$adaptive = aihl_sbm_constrain_design_mode('autonomous');
governance_mode_assert(!$adaptive['allowed'], 'autonomous non bloccato sotto adaptive');
governance_mode_assert('adaptive' === $adaptive['effective'], 'modalita adaptive non imposta');
governance_mode_assert(aihl_sbm_constrain_design_mode('governed')['allowed'], 'slot governed non consentito sotto adaptive');

$GLOBALS['sbm_runtime_mode'] = 'autonomous';
governance_mode_assert(aihl_sbm_constrain_design_mode('adaptive')['allowed'], 'slot adaptive non consentito sotto autonomous');

echo "AI-HTML SBM governance mode runtime OK\n";
