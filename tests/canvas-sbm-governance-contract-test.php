<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$slots = file_get_contents($root . '/inc/admin/code-slots.php');
$management = file_get_contents($root . '/inc/integrations/management-api.php');
$openapi = file_get_contents($root . '/inc/integrations/ai-api.php');

function canvas_sbm_assert(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

canvas_sbm_assert(is_string($slots) && is_string($management) && is_string($openapi), 'sorgenti Canvas non leggibili');
canvas_sbm_assert(str_contains($slots, "'design_mode'   => \$design_mode"), 'design_mode non persistito negli slot');
canvas_sbm_assert(str_contains($slots, 'function aihl_code_slot_governance_report'), 'validatore governance slot assente');
canvas_sbm_assert(str_contains($slots, "'design_mode_missing'"), 'design_mode mancante non diagnosticato');
canvas_sbm_assert(str_contains($slots, "'design_mode_exceeds_global_policy'"), 'downgrade della governance SBM globale non bloccato');
canvas_sbm_assert(str_contains($slots, "'sbm_namespace_override'"), 'override namespace SBM non diagnosticato');
canvas_sbm_assert(str_contains($slots, "'governed_raw_visual_value'"), 'valori visuali raw non diagnosticati');
canvas_sbm_assert(str_contains($slots, "'motion_runtime_override'"), 'runtime motion autonomo non diagnosticato');
canvas_sbm_assert(str_contains($slots, "if (!\$governance_report['valid'])"), 'slot non conforme non escluso dal rendering');
canvas_sbm_assert(str_contains($slots, 'class="sbs-ai-canvas aihl-ai-canvas'), 'wrapper semantico Canvas SBM assente');
canvas_sbm_assert(str_contains($management, "'aihl_canvas_governance_failed'"), 'selezione API di Canvas non conforme non bloccata');
canvas_sbm_assert(str_contains($openapi, "'design_mode_declared' => array('type' => 'boolean')"), 'OpenAPI Canvas non espone la dichiarazione design mode');
canvas_sbm_assert(str_contains($openapi, "'global_design_mode' => array("), 'OpenAPI Canvas non espone la modalita SBM globale');

echo "AI-HTML Canvas SBM governance contract OK\n";
