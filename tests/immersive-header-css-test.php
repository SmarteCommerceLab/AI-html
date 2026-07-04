<?php

$css = file_get_contents(__DIR__ . '/../resource/css/ai-html.css');

if ($css === false) {
	fwrite(STDERR, "Impossibile leggere il CSS AI-HTML.\n");
	exit(1);
}

$required_fragments = array(
	'.aihl-header-nav.aihl-header-overlay,',
	'body.aihl-has-fullscreen-hero .aihl-header-nav:not(.aihl-overlay-mode-never),',
	'background: transparent !important;',
	'-webkit-backdrop-filter: none;',
	'backdrop-filter: none;',
	'.aihl-header-nav.aihl-header-overlay.is-scrolled',
	'background: rgba(var(--bs-dark-rgb, 33, 37, 41), .92) !important;',
	'body.aihl-has-fullscreen-hero .aihl-header-nav:not(.is-scrolled) .navbar-nav .nav-link',
	'text-shadow: 0 1px 14px rgba(0, 0, 0, .42);',
	'.aihl-header-nav.aihl-sticky-style-gradient-fade:not(.is-scrolled)',
	'.aihl-header-nav.aihl-sticky-style-gradient-fade.is-scrolled',
);

foreach ($required_fragments as $fragment) {
	if (strpos($css, $fragment) === false) {
		fwrite(STDERR, "Regola header immersivo mancante: {$fragment}\n");
		exit(1);
	}
}

$forbidden_fragments = array(
	'.aihl-header-nav.aihl-sticky-style-gradient-fade:not(.is-scrolled) {' . "\n" . '    background: linear-gradient(',
	"\n" . '.aihl-header-nav.aihl-header-structure-dualbar.is-scrolled,',
);

foreach ($forbidden_fragments as $fragment) {
	if (strpos($css, $fragment) !== false) {
		fwrite(STDERR, "Header immersivo contiene ancora una barra/gradiente visibile: {$fragment}\n");
		exit(1);
	}
}

echo "OK AI-HTML immersive header: apertura trasparente e superficie solo dopo scroll\n";
