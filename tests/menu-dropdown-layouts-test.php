<?php

$walker = file_get_contents(__DIR__ . '/../inc/theme/menu-walker.php');
$fields = file_get_contents(__DIR__ . '/../inc/theme/menu-fields.php');
$css = file_get_contents(__DIR__ . '/../resource/css/ai-html.css');
$json = file_get_contents(__DIR__ . '/../inc/theme/menu-json.php');

if ($walker === false || $fields === false || $css === false || $json === false) {
	fwrite(STDERR, "Impossibile leggere i file menu AI-HTML.\n");
	exit(1);
}

$required_fragments = array(
	$walker => array(
		"'directory'",
		"'panel'",
		"'aihl-rich-density-' . \$density",
		"'aihl-rich-count-' . max(0, (int) \$this->current_rich_child_count)",
		"\$mode === 'dropdown'",
		"'aihl-menu-dropdown-parent'",
	),
	$fields => array(
		'value="dropdown"',
		'value="directory"',
		'value="panel"',
	),
	$css => array(
		'.aihl-rich-layout-directory',
		'.aihl-rich-layout-panel',
		'.aihl-rich-density-compact',
		'.aihl-menu-dropdown-parent',
		'.aihl-menu-rich-parent:not(.aihl-menu-dropdown-parent)',
		'grid-template-columns: repeat(5, minmax(9rem, 1fr))',
	),
	$json => array(
		'simple, dropdown oppure rich',
		'directory, panel',
	),
);

foreach ($required_fragments as $content => $fragments) {
	foreach ($fragments as $fragment) {
		if (strpos($content, $fragment) === false) {
			fwrite(STDERR, "Frammento dropdown menu mancante: {$fragment}\n");
			exit(1);
		}
	}
}

echo "OK AI-HTML menu dropdown: layout directory e panel verificati\n";
