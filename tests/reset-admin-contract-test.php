<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$activation = file_get_contents($root . '/inc/activation.php');
$reset = file_get_contents($root . '/inc/smart-reset.php');
$hub = file_get_contents($root . '/inc/admin/admin-hub.php');

if (str_contains($activation, 'unireset') || str_contains($activation, 'SMART_SITE_OPTION)')) {
	fwrite(STDERR, "FAIL: reset legacy ancora eseguibile\n");
	exit(1);
}
if (!str_contains($reset, 'aihl_smart_reset_snapshot') || !str_contains($reset, "RESET")) {
	fwrite(STDERR, "FAIL: Smart Reset senza snapshot o conferma\n");
	exit(1);
}
if (!str_contains($hub, 'aihl-smart-reset')) {
	fwrite(STDERR, "FAIL: pagina Reset assente dall'hub admin\n");
	exit(1);
}

echo "AI-HTML admin Smart Reset contract OK\n";
