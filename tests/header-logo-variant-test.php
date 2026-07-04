<?php

$header = file_get_contents(__DIR__ . '/../header.php');
$utilities = file_get_contents(__DIR__ . '/../inc/theme/utilities.php');

if ($header === false || $utilities === false) {
	fwrite(STDERR, "Impossibile leggere header.php\n");
	exit(1);
}

$required = array(
	'$aihl_uses_overlay_header = $aihl_has_fullscreen_hero &&',
	'$aihl_dark_surface_logo_variant = function_exists(\'aihl_get_dark_surface_logo_variant\') ? aihl_get_dark_surface_logo_variant() : \'transparent\';',
	'$aihl_header_logo_variant = $aihl_uses_overlay_header ? $aihl_dark_surface_logo_variant : \'default\';',
	'$aihl_mobile_offcanvas_logo_variant = $aihl_dark_surface_logo_variant;',
	'aihl_render_mobile_offcanvas_brand($aihl_mobile_offcanvas_logo_variant)',
	'if ($aihl_uses_overlay_header) {',
);

foreach ($required as $fragment) {
	if (strpos($header, $fragment) === false) {
		fwrite(STDERR, "Logica variante logo incompleta: {$fragment}\n");
		exit(1);
	}
}

if (strpos($header, '|| in_array($aihl_header_sticky_style') !== false) {
	fwrite(STDERR, "Il logo trasparente dipende ancora dallo sticky style invece che dall'overlay reale.\n");
	exit(1);
}

if (
	strpos($utilities, 'function aihl_get_dark_surface_logo_variant') === false
	|| strpos($utilities, 'site_logo_light_url') === false
	|| strpos($utilities, 'site_logo_transparent_url') === false
) {
	fwrite(STDERR, "Helper logo per superfici scure incompleto.\n");
	exit(1);
}

echo "OK AI-HTML header logo: navbar interna default, offcanvas mobile su variante chiara\n";
