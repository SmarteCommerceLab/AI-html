<?php

$theme_css = file_get_contents(__DIR__ . '/../resource/css/ai-html.css');
$bridge_css = file_get_contents(__DIR__ . '/../resource/css/aihl-bootstrap-bridge.css');

if ($theme_css === false || $bridge_css === false) {
	fwrite(STDERR, "Impossibile leggere i CSS header.\n");
	exit(1);
}

$desktop_required = array(
	'@media (min-width: 992px)',
	'.aihl-header-nav .offcanvas {',
	'position: static',
	'background: transparent !important',
	'.aihl-header-nav .offcanvas-header',
	'display: none !important',
	'.aihl-header-nav .offcanvas-body',
	'overflow: visible',
	'.aihl-header-nav.aihl-header-overlay',
	'body.aihl-has-fullscreen-hero .aihl-header-nav:not(.aihl-overlay-mode-never)',
	'z-index: 1030',
	'body.admin-bar .aihl-header-nav.aihl-header-overlay',
);

foreach ($desktop_required as $fragment) {
	if (strpos($theme_css, $fragment) === false) {
		fwrite(STDERR, "Regola desktop header mancante: {$fragment}\n");
		exit(1);
	}
}

$forbidden_bridge_fragments = array(
	'.aihl-header-nav .offcanvas',
	'.aihl-mobile-rail',
	'position: fixed',
	'position: sticky',
	'@media (max-width: 991.98px)',
);

foreach ($forbidden_bridge_fragments as $fragment) {
	if (strpos($bridge_css, $fragment) !== false) {
		fwrite(STDERR, "Il bridge SBM contiene ancora regole strutturali: {$fragment}\n");
		exit(1);
	}
}

echo "OK AI-HTML desktop header: offcanvas desktop isolato e bridge SBM solo-token\n";
