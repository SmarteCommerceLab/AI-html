<?php
$source = (string) file_get_contents(dirname(__DIR__) . '/inc/theme/page-background.php');

foreach (array(
	"function aihl_page_background_patterns(): array {\n\treturn array(",
	"\$sanitized = array(",
	"unset(\$sanitized['image_opacity'])",
	"unset(\$sanitized['overlay_opacity'])",
	"'normal',",
) as $needle) {
	if (strpos($source, $needle) === false) {
		fwrite(STDERR, "Missing page background contract: {$needle}\n");
		exit(1);
	}
}

echo "AI-HTML page background contract OK\n";
