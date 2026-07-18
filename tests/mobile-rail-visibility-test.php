<?php

$theme_css = file_get_contents(__DIR__ . '/../resource/css/ai-html.css');
$bridge_css = file_get_contents(__DIR__ . '/../resource/css/aihl-bootstrap-bridge.css');
$header = file_get_contents(__DIR__ . '/../header.php');
$mobile_navigation = file_get_contents(__DIR__ . '/../inc/theme/mobile-navigation.php');

if ($theme_css === false || $bridge_css === false || $header === false || $mobile_navigation === false) {
	fwrite(STDERR, "Impossibile leggere i file del rail mobile.\n");
	exit(1);
}

$checks = array(
	'theme CSS' => array(
		$theme_css,
		array(
			'.aihl-mobile-rail {',
			'display: flex !important',
			'visibility: visible',
			'.aihl-mobile-rail.aihl-mobile-rail-hidden',
		),
	),
	'header' => array(
		$header,
		array(
			'aihl_get_mobile_navigation_config()',
			'aihl_render_mobile_quick_navigation',
		),
	),
	'mobile navigation' => array(
		$mobile_navigation,
		array(
			"\$style === 'rail'",
			'aihl-mobile-rail aihl-mobile-rail-',
			'aihl-bottom-bar d-md-none',
			'data-bs-target="#offcanvasNavbar"',
		),
	),
);

foreach ($checks as $group => $data) {
	foreach ($data[1] as $fragment) {
		if (strpos($data[0], $fragment) === false) {
			fwrite(STDERR, "Rail mobile {$group} incompleto: {$fragment}\n");
			exit(1);
		}
	}
}

if (strpos($bridge_css, '.aihl-mobile-rail') !== false) {
	fwrite(STDERR, "Il bridge SBM contiene ancora regole strutturali del rail.\n");
	exit(1);
}

foreach (array('@media (max-width: 767.98px)', 'padding-bottom: calc(3.5rem + env(safe-area-inset-bottom, 0px));') as $fragment) {
	if (strpos($theme_css, $fragment) === false) {
		fwrite(STDERR, "Bottom bar responsive incompleta: {$fragment}\n");
		exit(1);
	}
}

echo "OK AI-HTML mobile rail: visibilita indipendente dalla hero verificata\n";
