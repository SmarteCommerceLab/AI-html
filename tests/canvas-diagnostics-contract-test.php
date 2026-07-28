<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$slots = file_get_contents($root . '/inc/admin/code-slots.php');
$api = file_get_contents($root . '/inc/integrations/management-api.php');
$openapi = file_get_contents($root . '/inc/integrations/ai-api.php');
$admin = file_get_contents($root . '/inc/admin/admin-hub.php');
$customizer = file_get_contents($root . '/inc/customizer/section.php');

function canvas_contract_assert(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

canvas_contract_assert(str_contains($slots, 'function aihl_canvas_health_report'), 'rapporto Canvas runtime mancante');
canvas_contract_assert(str_contains($slots, "'canvas_fallback_native'"), 'fallback nativo non diagnosticato');
canvas_contract_assert(str_contains($slots, "'navigation_unresolved'"), 'menu Canvas non diagnosticato');
canvas_contract_assert(str_contains($api, "'health' => function_exists('aihl_canvas_health_report')"), 'rapporto Canvas assente dalla API');
canvas_contract_assert(str_contains($openapi, "'CanvasHealth' => array("), 'schema OpenAPI della diagnostica Canvas mancante');
canvas_contract_assert(str_contains($admin, "aihl_canvas_health_report(\$area)"), 'rapporto Canvas assente dalla dashboard');
canvas_contract_assert(str_contains($customizer, 'aihl_customizer_canvas_management_description'), 'collegamento editor Canvas assente dal Customizer');

echo "AI-HTML Canvas diagnostics contract OK\n";
