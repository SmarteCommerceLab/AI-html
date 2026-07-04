<?php

$theme_css = file_get_contents(__DIR__ . '/../resource/css/ai-html.css');
$bootstrap_bridge = file_get_contents(__DIR__ . '/../inc/integrations/smart-bootstrap-manager.php');

if ($theme_css === false || $bootstrap_bridge === false) {
	fwrite(STDERR, "Impossibile leggere CSS tema o bridge SBM.\n");
	exit(1);
}

$required_theme_fragments = array(
	'--aihl-header-surface-color',
	'.aihl-header-nav.aihl-header-structure-dualbar.aihl-overlay-mode-auto:not(.is-scrolled)',
	'.aihl-header-nav.aihl-sticky-style-gradient-fade:not(.is-scrolled)',
	'.aihl-header-nav.aihl-header-structure-dualbar.aihl-overlay-mode-auto:not(.is-scrolled) .navbar-nav .nav-link',
	'.aihl-header-nav.aihl-sticky-style-gradient-fade:not(.is-scrolled) .navbar-nav .nav-link.show',
	'.aihl-header-nav.aihl-header-structure-dualbar.aihl-overlay-mode-auto:not(.is-scrolled) .aihl-header-social-main .aihl-social-link',
	'color: var(--aihl-header-surface-color, var(--bs-light, #f8f9fa)) !important',
	'body.aihl-has-fullscreen-hero .aihl-header-nav:not(.aihl-overlay-mode-never) .nav-link',
	'border-color: var(--aihl-header-surface-border',
);

foreach ($required_theme_fragments as $fragment) {
	if (strpos($theme_css, $fragment) === false) {
		fwrite(STDERR, "Regola colore contestuale mancante nel tema: {$fragment}\n");
		exit(1);
	}
}

$required_bridge_fragments = array(
	'.aihl-header-nav:not(.aihl-header-overlay):not(.aihl-overlay-mode-always) .navbar-brand',
	'.aihl-footer:not(.aihl-footer-surface-dark) a',
);

foreach ($required_bridge_fragments as $fragment) {
	if (strpos($bootstrap_bridge, $fragment) === false) {
		fwrite(STDERR, "Bridge SBM non contestuale o incompleto: {$fragment}\n");
		exit(1);
	}
}

$forbidden_bridge_fragments = array(
	'.aihl-header-nav .navbar-brand,.aihl-header-nav .navbar-brand .h2{color:var(--aihl-brand-color)!important;}',
	'.aihl-header-nav .navbar-nav>.current-menu-item>.nav-link,.aihl-header-nav .navbar-nav>.current-menu-ancestor>.nav-link{color:var(--aihl-ui-link-hover-color)!important;}',
	'.aihl-footer a{color:var(--aihl-ui-link-color)!important;}',
);

foreach ($forbidden_bridge_fragments as $fragment) {
	if (strpos($bootstrap_bridge, $fragment) !== false) {
		fwrite(STDERR, "Bridge SBM contiene ancora override globale non contestuale: {$fragment}\n");
		exit(1);
	}
}

echo "OK AI-HTML color token context: overlay e footer non ereditano colori globali SBM\n";
