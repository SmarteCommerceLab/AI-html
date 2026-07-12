<?php
if (!defined('ABSPATH')) {
	exit;
}

define('AIHL_VERSION', '1.8.9');

/**
 * Move the visually oversized legacy author banner to the enterprise preset.
 */
function aihl_upgrade_author_box_preset(): void {
	$migration_key = 'aihl_author_presets_180_migrated';
	if (get_option($migration_key)) {
		return;
	}

	$option_key = AIHL_OPTION_BASE . '_general';
	$options = get_option($option_key, array());
	$options = is_array($options) ? $options : array();
	if (empty($options['article_author_box_style']) || 'banner' === $options['article_author_box_style']) {
		$options['article_author_box_style'] = 'enterprise';
		update_option($option_key, $options, false);
	}
	update_option($migration_key, 1, false);
}
add_action('after_setup_theme', 'aihl_upgrade_author_box_preset', 30);
define('AIHL_UNICODE', '202607081130');

define('AIHL_TEXT_DOMAIN', 'ai_html');
define('AIHL_THEME_NAME', 'AI-HTML');
define('AIHL_THEME_BASE', 'ai_html');
define('AIHL_OPTION_BASE', 'ai_html_option');
define('AIHL_PRODUCT_SLUG', 'ai-html');
define('AIHL_UPDATE_ENDPOINT', 'https://repository.smartecommerce.it/updates/themes/ai-html.json');

define('AIHL_DIR_PATH', get_template_directory());
define('AIHL_DIR_URL', get_template_directory_uri());

require_once trailingslashit(AIHL_DIR_PATH) . 'inc/core/bootstrap.php';
