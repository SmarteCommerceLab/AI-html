<?php
$source = file_get_contents(__DIR__ . '/../inc/integrations/ai-api.php');
$required = array(
	'WP_REST_Server::EDITABLE',
	'aihl_ai_rest_update_page',
	'unsupported_page_fields',
	'publish_permission_required',
	"'title', 'slug', 'content', 'status', 'template'",
	"'post_content'] = wp_kses_post",
	'PageUpdateRequest',
	'delete_post_meta($page_id, \'_wp_page_template\')',
);
foreach ($required as $needle) {
	if (false === strpos($source, $needle)) {
		fwrite(STDERR, "Missing AI page update contract: {$needle}\n");
		exit(1);
	}
}
echo "AI page update contract OK\n";
