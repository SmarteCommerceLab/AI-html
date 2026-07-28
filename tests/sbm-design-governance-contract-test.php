<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$bridge = file_get_contents($root . '/inc/integrations/smart-bootstrap-manager.php');
$header = file_get_contents($root . '/header.php');
$footer = file_get_contents($root . '/footer.php');
$utilities = file_get_contents($root . '/inc/theme/utilities.php');
$background = file_get_contents($root . '/inc/theme/page-background.php');
$resource = file_get_contents($root . '/inc/resource.php');
$management = file_get_contents($root . '/inc/integrations/management-api.php');
$css = file_get_contents($root . '/resource/css/ai-html.css');
$bridge_css = file_get_contents($root . '/resource/css/aihl-bootstrap-bridge.css');

function sbm_governance_assert(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

foreach (array($bridge, $header, $footer, $utilities, $background, $resource, $management, $css, $bridge_css) as $source) {
	sbm_governance_assert(is_string($source), 'file contrattuale SBM non leggibile');
}

sbm_governance_assert(str_contains($bridge, 'function aihl_sbm_design_governance'), 'resolver governance assente');
sbm_governance_assert(str_contains($bridge, 'function aihl_sbm_effective_css_value'), 'resolver valori effettivi assente');
sbm_governance_assert(str_contains($bridge, 'function aihl_sbm_constrain_design_mode'), 'vincolo gerarchico delle modalita assente');
sbm_governance_assert(str_contains($header, '--aihl-request-overlay-opacity:'), 'header non conserva il valore richiesto');
sbm_governance_assert(!str_contains($header, '--sbin-overlay-opacity:'), 'header dichiara ancora token SBM');
sbm_governance_assert(str_contains($utilities, 'var(--sbin-container-max-width,1320px)'), 'larghezza articolo non subordinata al container SBM');
sbm_governance_assert(str_contains($background, "aihl_sbm_inherits_design_domain('colors')"), 'background pagina non subordinato ai colori SBM');
sbm_governance_assert(str_contains($footer, '--aihl-request-footer-bg-opacity:'), 'footer non distingue richiesta e valore effettivo');
sbm_governance_assert(str_contains($resource, 'aihl_is_bootstrap_manager_active()'), 'asset motion legacy non condizionati da SBM');
sbm_governance_assert(str_contains($management, 'function aihl_sbm_option_compliance_matrix'), 'matrice opzioni SBM assente');
sbm_governance_assert(str_contains($management, "'option_registry_coverage'"), 'check copertura registro opzioni assente');
sbm_governance_assert(str_contains($management, 'function aihl_sbm_contract_compatibility_report'), 'validazione versione contratto assente');
sbm_governance_assert(str_contains($management, 'function aihl_sbm_bootstrap_ownership_report'), 'verifica ownership Bootstrap assente');
sbm_governance_assert(!str_contains($management, "'bootstrap_ownership', 'ok' => true"), 'ownership Bootstrap ancora hardcoded');

foreach (array($header, $footer, $bridge, $css, $bridge_css) as $source) {
	sbm_governance_assert(!preg_match('/--sbin-[a-z0-9-]+\s*:/i', $source), 'il tema dichiara token nel namespace SBM');
	sbm_governance_assert(!preg_match('/--(?:primary|secondary|light|dark)\s*:/i', $source), 'il tema dichiara alias colore legacy');
}

sbm_governance_assert(!preg_match('/var\(--(?:primary|secondary|light|dark)\b/i', $css), 'CSS frontend usa ancora alias colore legacy');
sbm_governance_assert(!preg_match('/rgba?\(\s*0\s*,\s*0\s*,\s*0/i', $css), 'CSS frontend contiene ancora nero RGB non tokenizzato');
sbm_governance_assert(str_contains($css, 'rgba(var(--bs-body-color-rgb'), 'pattern e ombre non usano i token colore');

echo "AI-HTML SBM design governance contract OK\n";
