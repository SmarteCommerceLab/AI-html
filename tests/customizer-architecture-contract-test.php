<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$bootstrap = file_get_contents($root . '/inc/core/bootstrap.php');
$panel = file_get_contents($root . '/inc/customizer/panel.php');
$sections = file_get_contents($root . '/inc/customizer/section.php');
$registry = file_get_contents($root . '/inc/theme-option-registry.php');
$api = file_get_contents($root . '/inc/integrations/ai-api.php');

function contract_assert(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

contract_assert(str_contains($panel, "add_action('customize_register'"), 'pannelli registrati direttamente su customize_register');
contract_assert(!str_contains($panel, "add_action('init'"), 'nessun wrapper init per i pannelli');
contract_assert(str_contains($panel, "public \$type = 'aihl_nested_panel'"), 'tipo pannello annidato locale');
contract_assert(str_contains($panel, "\$args['panel'] = \$root"), 'pannelli figli collegati al pannello radice');
contract_assert(str_contains($sections, "_structure_panel"), 'sezioni struttura nel pannello dedicato');
contract_assert(str_contains($sections, "_content_panel"), 'sezioni contenuto nel pannello dedicato');
contract_assert(str_contains($sections, "_appearance_panel"), 'sezioni aspetto nel pannello dedicato');
contract_assert(str_contains($sections, "_integrations_panel"), 'sezioni integrazioni nel pannello dedicato');
contract_assert(!str_contains($sections, "'reset'.'_section'"), 'reset assente dal Customizer');
contract_assert(!str_contains($bootstrap, "inc/customizer/reset.php"), 'reset Customizer non caricato');
contract_assert(str_contains($registry, "'header_canvas_slot_id'"), 'registro include selezione Canvas header');
contract_assert(str_contains($registry, "'footer_canvas_slot_id'"), 'registro include selezione Canvas footer');
contract_assert(str_contains($api, "aihl_theme_option_registry()"), 'API usa il registro canonico');

echo "AI-HTML Customizer hierarchy contract OK\n";
