<?php
$source = file_get_contents(__DIR__ . '/../inc/integrations/ai-api.php');
$required = array(
	"/ai/pages/(?P<id>\\d+)/status",
	"/ai/site/front-page",
	"/ai/auth/capabilities",
	"aihl_ai_can_publish",
	"smart_ai_can_publish",
	"aihl_ai_rest_update_page_status",
	"aihl_ai_rest_update_front_page",
	"aihl_ai_rest_auth_capabilities",
	"create_status_not_allowed",
	"front_page_not_published",
	"/aihtml/v1/ai/pages/{id}/status",
	"/aihtml/v1/ai/site/front-page",
);
foreach ($required as $needle) {
	if (false === strpos($source, $needle)) {
		fwrite(STDERR, "Missing AI publish contract: {$needle}\n");
		exit(1);
	}
}
echo "AI publish contract OK\n";
