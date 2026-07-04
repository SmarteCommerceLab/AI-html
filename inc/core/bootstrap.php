<?php
if (!defined('ABSPATH')) {
	exit;
}

require_once trailingslashit(get_template_directory()) . 'inc/core/loader.php';

aihl_require_files(array(
	'inc/option.php',
	'inc/activation.php',
	'inc/output-cleanup.php',
	'inc/resource.php',
	'inc/required-plugins.php',
	'inc/admin/admin-hub.php',
	'inc/admin/author-profile.php',
	'inc/admin/deploy-projects.php',
	'inc/admin/code-slots.php',
	'inc/smart-reset.php',
));

aihl_require_files(array(
	'inc/customizer/panel.php',
	'inc/customizer/section.php',
	'inc/customizer/reset.php',
));

aihl_require_files(array(
	'inc/theme/support.php',
	'inc/theme/menu.php',
	'inc/theme/menu-fields.php',
	'inc/theme/menu-help.php',
	'inc/theme/menu-json.php',
	'inc/theme/menu-walker.php',
	'inc/theme/image-size.php',
	'inc/theme/post-occhiello.php',
	'inc/theme/page-background.php',
	'inc/theme/utilities.php',
	'inc/theme/integration-contract.php',
	'inc/theme/mobile-navigation.php',
	'inc/integrations/smart-bootstrap-manager.php',
	'inc/integrations/google-compliance-2026.php',
	'inc/integrations/seo.php',
	'inc/integrations/ai-auth-core.php',
	'inc/integrations/ai-api.php',
	'inc/theme/options-json.php',
));
