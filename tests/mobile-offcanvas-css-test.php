<?php

$css = file_get_contents(__DIR__ . '/../resource/css/ai-html.css');
if ($css === false) {
	fwrite(STDERR, "Impossibile leggere ai-html.css\n");
	exit(1);
}

$required = array(
	'@media (max-width: 991.98px)',
	'-webkit-backdrop-filter: none !important',
	'backdrop-filter: none !important',
	'height: 100dvh',
	'max-height: 100dvh',
	'.aihl-header-nav .offcanvas .aihl-mobile-menu',
	'.aihl-mobile-menu-brand',
	'.aihl-mobile-menu-logo',
	'.aihl-mobile-menu-item',
	'.aihl-mobile-search',
	'.aihl-header-nav .navbar-toggler',
	'.aihl-header-nav .navbar-toggler-icon',
	'linear-gradient(currentColor, currentColor)',
	'--aihl-mobile-panel-bg',
	'min-height: 58px',
);

foreach ($required as $fragment) {
	if (strpos($css, $fragment) === false) {
		fwrite(STDERR, "Regola mobile offcanvas mancante: {$fragment}\n");
		exit(1);
	}
}

echo "OK AI-HTML mobile offcanvas: viewport, blur e contrasto verificati\n";
