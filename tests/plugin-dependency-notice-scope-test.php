<?php

$required_plugins = file_get_contents(__DIR__ . '/../inc/required-plugins.php');
$admin_hub = file_get_contents(__DIR__ . '/../inc/admin/admin-hub.php');

if ($required_plugins === false || $admin_hub === false) {
	fwrite(STDERR, "Impossibile leggere il sistema dipendenze AI-HTML.\n");
	exit(1);
}

$required_fragments = array(
	'aihl_missing_plugins_grouped',
	'aihl_render_plugin_dependency_summary',
	'Plugin richiesti',
	'Plugin consigliati',
	'smart-dependency-summary',
);

foreach ($required_fragments as $fragment) {
	if (strpos($required_plugins . $admin_hub, $fragment) === false) {
		fwrite(STDERR, "Componente riepilogo dipendenze mancante: {$fragment}\n");
		exit(1);
	}
}

if (strpos($required_plugins, "add_action('admin_notices'") !== false) {
	fwrite(STDERR, "Il controllo dipendenze usa ancora un admin notice globale.\n");
	exit(1);
}

$dashboard_call = "aihl_render_plugin_dependency_summary();";
if (substr_count($admin_hub, $dashboard_call) !== 1) {
	fwrite(STDERR, "Il riepilogo dipendenze deve essere renderizzato una sola volta nella Dashboard AI-HTML.\n");
	exit(1);
}

echo "OK AI-HTML plugin dependencies: riepilogo limitato alla Dashboard del tema\n";
