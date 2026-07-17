<?php
$source = file_get_contents(__DIR__ . '/../inc/admin/admin-hub.php');
$required = array('smart-admin-wrap', 'smart-admin-header', 'smart-admin-shell', 'smart-admin-sidebar', 'smart-admin-nav', 'smart-admin-main', 'smart-admin-pathbar', 'smart-admin-body', 'smart-admin-page-header', 'smart-admin-content', 'smart-admin-footer');
foreach ($required as $class) {
	if (false === strpos($source, $class)) {
		fwrite(STDERR, "Missing Smart Admin v2 class: {$class}\n");
		exit(1);
	}
}
if (false !== strpos($source, 'smart-admin-tabs') || false === strpos($source, 'current_user_can')) {
	fwrite(STDERR, "Legacy navigation or capability guard mismatch\n");
	exit(1);
}
echo "AI-HTML Smart Admin v2 contract OK\n";
