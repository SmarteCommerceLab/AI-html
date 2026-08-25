<?php
$source = file_get_contents(__DIR__ . '/../inc/admin/admin-hub.php');
$required = array('smart-admin-wrap', 'smart-admin-header', 'smart-admin-shell', 'smart-admin-sidebar', 'smart-admin-nav', 'smart-admin-nav-section', 'smart-admin-main', 'smart-admin-pathbar', 'smart-admin-body', 'smart-admin-page-header', 'smart-admin-content', 'smart-admin-footer');
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
$configuration = strpos($source, "'aihl-options-json' => array('section'");
$manifest = strpos($source, "'aihl-manifest-json' => array('section'");
$menus = strpos($source, "'aihl-menu-json' => array('section'");
$integrations = strpos($source, "'aihl-plugins' => array('section'");
$advanced = strpos($source, "'aihl-code-slots' => array('section'");
$governance = strpos($source, "'aihl-compliance' => array('section'");
if (!($configuration < $manifest && $manifest < $menus && $menus < $integrations && $integrations < $advanced && $advanced < $governance)) {
	fwrite(STDERR, "Admin navigation functional order mismatch\n");
	exit(1);
}
echo "AI-HTML Smart Admin v2 contract OK\n";
