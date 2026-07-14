<?php
$source = file_get_contents(__DIR__ . '/../inc/class-aihl-public-theme-updater.php');
foreach (array('theme_action_links_', 'admin_post_aihl_check_updates', 'check_admin_referer', "delete_site_transient('update_themes')", 'wp_update_themes()', 'Controlla aggiornamenti') as $needle) {
	if (false === strpos($source, $needle)) {
		fwrite(STDERR, "Missing manual theme update contract: {$needle}\n");
		exit(1);
	}
}
echo "AI-HTML manual update link contract OK\n";
