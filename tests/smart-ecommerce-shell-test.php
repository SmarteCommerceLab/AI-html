<?php
$root = dirname(__DIR__);
$project = $root . '/demo-projects/smart-ecommerce-shell';
$header = (string) file_get_contents($project . '/header.html');
$footer = (string) file_get_contents($project . '/footer.html');
$css = (string) file_get_contents($project . '/header.css') . (string) file_get_contents($project . '/footer.css');
$js = (string) file_get_contents($project . '/header.js');
$json = json_decode((string) file_get_contents($project . '/smart-ecommerce-shell-slots.json'), true);
foreach (array('<header', '<smart-logo', '<smart-menu', 'aria-expanded', 'aria-controls') as $needle) if (!str_contains($header, $needle)) { fwrite(STDERR, "Header shell incompleto: {$needle}\n"); exit(1); }
foreach (array('<footer', '<smart-logo', '<smart-menu', '<smart-social', '<smart-contact') as $needle) if (!str_contains($footer, $needle)) { fwrite(STDERR, "Footer shell incompleto: {$needle}\n"); exit(1); }
foreach (array('--bs-primary', '--bs-body-bg', '--sbin-radius') as $needle) if (!str_contains($css, $needle)) { fwrite(STDERR, "Token design system mancante: {$needle}\n"); exit(1); }
foreach (array("event.key === 'Escape'", 'aihl:content-ready', 'data-sec-shell-ready') as $needle) if (!str_contains($js, $needle)) { fwrite(STDERR, "Comportamento header mancante: {$needle}\n"); exit(1); }
if (($json['format'] ?? '') !== 'aihl-code-slots' || ($json['count'] ?? 0) !== 2 || count($json['slots'] ?? array()) !== 2) { fwrite(STDERR, "Payload Code Slots non valido.\n"); exit(1); }
echo "Smart eCommerce shell Code Slots test OK\n";
