<?php
$source = file_get_contents(__DIR__ . '/../inc/integrations/ai-api.php');
$required = array(
	"isset(\$body['slug']) ? sanitize_title((string) \$body['slug']) : ''",
	"\$page_data['post_name'] = \$slug",
	"'slug'     => (string) get_post_field('post_name', \$page_id)",
);
foreach ($required as $needle) {
	if (false === strpos($source, $needle)) {
		fwrite(STDERR, "Missing AI page create slug contract: {$needle}\n");
		exit(1);
	}
}
echo "AI page create slug contract OK\n";
