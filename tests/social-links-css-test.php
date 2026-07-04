<?php

$utilities = file_get_contents(__DIR__ . '/../inc/theme/utilities.php');
$theme_css = file_get_contents(__DIR__ . '/../resource/css/ai-html.css');

if ($utilities === false || $theme_css === false) {
	fwrite(STDERR, "Impossibile leggere i file social del tema.\n");
	exit(1);
}

$utility_checks = array(
	'aihl_get_site_builder_social_links',
	'smart_site_builder_social_link()',
	"aihl-social-link aihl-social-link-' . \$key",
);

foreach ($utility_checks as $fragment) {
	if (strpos($utilities, $fragment) === false) {
		fwrite(STDERR, "Renderer social SBS non allineato al tema: {$fragment}\n");
		exit(1);
	}
}

$css_checks = array(
	'.aihl-header-social-main .aihl-social-link',
	'body.aihl-has-fullscreen-hero .aihl-header-nav:not(.aihl-overlay-mode-never) .aihl-header-social-main .aihl-social-link',
	'.aihl-header-nav.aihl-overlay-mode-always .aihl-header-social-main .aihl-social-link',
	'border: 0 !important',
	'var(--bs-primary, #0d6efd)',
	'var(--sbin-primary-contrast, #fff)',
);

foreach ($css_checks as $fragment) {
	if (strpos($theme_css, $fragment) === false) {
		fwrite(STDERR, "Comportamento CSS social header mancante: {$fragment}\n");
		exit(1);
	}
}

echo "OK AI-HTML social SBS: renderer dati e comportamento header verificati\n";
