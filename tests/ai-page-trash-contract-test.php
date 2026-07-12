<?php
$source = file_get_contents(__DIR__ . '/../inc/integrations/ai-api.php');
$required = array(
	"/ai/pages/(?P<id>\\d+)",
	"WP_REST_Server::DELETABLE",
	"aihl_ai_rest_trash_page",
	"published_page_protected",
	'wp_trash_post($page_id)',
	"/aihtml/v1/ai/pages/{id}",
);
foreach ($required as $needle) {
	if (false === strpos($source, $needle)) {
		fwrite(STDERR, "Missing AI page trash contract: {$needle}\n");
		exit(1);
	}
}
echo "AI page trash contract OK\n";
