<?php
$root = dirname(__DIR__);
$home = file_get_contents($root . '/home.php');
$header = file_get_contents($root . '/header.php');
$css = file_get_contents($root . '/resource/css/ai-html.css');

$checks = array(
	'blog index h1' => strpos($home, '<h1 class="display-5 fw-bold mb-2">') !== false,
	'search input labels' => substr_count($header, 'aria-label="<?php esc_attr_e(\'Cerca nel sito\'') >= 5,
	'search button label' => strpos($header, "esc_attr_e('Avvia la ricerca'") !== false,
	'mobile menu wrapping' => strpos($css, 'overflow-wrap: anywhere;') !== false,
);

foreach ($checks as $label => $passed) {
	if (!$passed) {
		fwrite(STDERR, "Missing frontend accessibility contract: {$label}\n");
		exit(1);
	}
}

echo "OK AI-HTML frontend accessibility contract\n";
