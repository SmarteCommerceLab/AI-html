<?php

$walker = file_get_contents(__DIR__ . '/../inc/theme/menu-walker.php');
$css = file_get_contents(__DIR__ . '/../resource/css/ai-html.css');
$js = file_get_contents(__DIR__ . '/../resource/js/main.js');

if ($walker === false || $css === false || $js === false) {
	fwrite(STDERR, "Impossibile leggere i file del menu mobile.\n");
	exit(1);
}

$walker_checks = array(
	'class AIHL_Mobile_Nav_Menu_Walker extends Walker_Nav_Menu',
	"'aihl-mobile-submenu-' . \$item_id",
	'aihl-mobile-menu-row',
	'aihl-mobile-menu-link',
	'aihl-mobile-submenu-toggle',
	'aihl-mobile-submenu list-unstyled',
	"\$filtered_classes[] = 'aihl-mobile-menu-link'",
);

$css_checks = array(
	'.aihl-mobile-menu-item.has-children > .aihl-mobile-menu-row',
	'grid-template-columns: minmax(0, 1fr) 48px',
	'.aihl-mobile-menu-item.is-open > .aihl-mobile-submenu',
	'.aihl-mobile-submenu .aihl-mobile-menu-link',
	'.aihl-mobile-search .search-form',
	'#offcanvasNavbar .aihl-mobile-menu-text',
	'visibility: visible !important',
);

$js_checks = array(
	'initMobileMenuAccordion',
	'getDirectSubmenu',
	'getDirectToggle',
	'closeSiblingItems',
	'submenu.hidden = !open',
	'mobileMenu.addEventListener("click"',
);

foreach (
	array(
		'walker' => array($walker, $walker_checks),
		'css' => array($css, $css_checks),
		'js' => array($js, $js_checks),
	) as $group => $data
) {
	foreach ($data[1] as $fragment) {
		if (strpos($data[0], $fragment) === false) {
			fwrite(STDERR, "Accordion {$group} incompleto: {$fragment}\n");
			exit(1);
		}
	}
}

if (strpos($walker, 'aihl-mobile-submenu-toggle d-lg-none') !== false) {
	fwrite(STDERR, "Il walker desktop contiene ancora controlli mobile.\n");
	exit(1);
}

echo "OK AI-HTML mobile submenu: rendering dedicato, accordion e layout verticale verificati\n";
