<?php

$resource = file_get_contents(__DIR__ . '/../inc/resource.php');
$header = file_get_contents(__DIR__ . '/../header.php');

if ($resource === false || $header === false) {
	fwrite(STDERR, "Impossibile leggere i file del tema.\n");
	exit(1);
}

$resource_checks = array(
	"'aihl-bootstrap-fallback'",
	'bootstrap@5.3.8/dist/css/bootstrap.min.css',
	'bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js',
	"wp_script_is('smart-bootstrap', 'enqueued')",
	"\$main_deps[] = 'aihl-bootstrap-fallback'",
);

foreach ($resource_checks as $fragment) {
	if (strpos($resource, $fragment) === false) {
		fwrite(STDERR, "Fallback Bootstrap incompleto: {$fragment}\n");
		exit(1);
	}
}

if (
	strpos($header, 'navbar-nav aihl-desktop-menu d-none d-lg-flex') === false
	|| strpos($header, 'aihl-mobile-menu list-unstyled d-lg-none') === false
	|| strpos($header, 'new AIHL_Mobile_Nav_Menu_Walker()') === false
) {
	fwrite(STDERR, "Separazione tra menu desktop e mobile incompleta.\n");
	exit(1);
}

if (strpos($header, 'aihl_render_mobile_offcanvas_brand($aihl_mobile_offcanvas_logo_variant)') === false) {
	fwrite(STDERR, "Logo del pannello mobile non renderizzato.\n");
	exit(1);
}

if (
	strpos($header, '$aihl_has_header_override') === false
	|| strpos($header, 'aihl_render_mobile_quick_navigation($aihl_mobile_navigation, $aihl_has_header_override)') === false
) {
	fwrite(STDERR, "La navigazione mobile nativa non viene esclusa con header_full.\n");
	exit(1);
}

if (strpos($resource, 'wp_add_inline_script') !== false) {
	fwrite(STDERR, "La logica frontend è ancora distribuita in JavaScript inline.\n");
	exit(1);
}

echo "OK AI-HTML standalone: fallback Bootstrap e menu primario verificati\n";
