<?php

$footer = file_get_contents(__DIR__ . '/../footer.php');
$css = file_get_contents(__DIR__ . '/../resource/css/ai-html.css');

if ($footer === false || $css === false) {
	fwrite(STDERR, "Impossibile leggere i componenti footer.\n");
	exit(1);
}

$checks = array(
	'footer' => array(
		$footer,
		array(
			'fa-solid fa-location-dot',
			'fa-solid fa-phone',
			'fa-solid fa-envelope',
		),
	),
	'css' => array(
		$css,
		array(
			'.aihl-footer-menu a::before',
			'border-top: 2px solid currentColor',
		),
	),
);

foreach ($checks as $group => $data) {
	foreach ($data[1] as $fragment) {
		if (strpos($data[0], $fragment) === false) {
			fwrite(STDERR, "Footer {$group} incompleto: {$fragment}\n");
			exit(1);
		}
	}
}

if (
	strpos($footer, 'aihl_render_footer_proof_items') !== false
	|| strpos($footer, 'aihl_get_footer_proof_items') !== false
	|| strpos($css, 'aihl-footer-proof') !== false
	|| strpos($footer, 'Governance e sicurezza') !== false
	|| strpos($css, 'Font Awesome 5 Free') !== false
) {
	fwrite(STDERR, "Il footer contiene ancora proof points, fallback generici o glifi legacy.\n");
	exit(1);
}

echo "OK AI-HTML footer: proof points rimossi, contatti e marker CSS verificati\n";
