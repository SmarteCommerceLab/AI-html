<?php

$base = __DIR__;
$html = file_get_contents($base . '/header.html');
$css = file_get_contents($base . '/header.css');
$js = file_get_contents($base . '/header.js');

if ($html === false || $css === false || $js === false) {
	fwrite(STDERR, "Impossibile leggere i sorgenti dello slot.\n");
	exit(1);
}

$payload = array(
	'format' => 'aihl-code-slots',
	'version' => 1,
	'count' => 1,
	'slots' => array(
		array(
			'id' => 'legal-enterprise-header',
			'label' => 'Header enterprise Studio Legale Di Caprio',
			'hook' => 'header_full',
			'type' => 'mixed',
			'context' => 'global',
			'priority' => 10,
			'active' => true,
			'author' => 'AI-HTML',
			'code' => $html,
			'css' => $css,
			'js' => $js,
		),
	),
);

$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($json === false) {
	fwrite(STDERR, "Impossibile generare il JSON dello slot.\n");
	exit(1);
}

$target = $base . '/legal-enterprise-header-slot.json';
if (file_put_contents($target, $json . PHP_EOL) === false) {
	fwrite(STDERR, "Impossibile scrivere {$target}.\n");
	exit(1);
}

echo $target . PHP_EOL;
