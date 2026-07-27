<?php

$root = dirname(__DIR__);
$resource = file_get_contents($root . '/inc/resource.php');
$related = file_get_contents($root . '/template-parts/related-posts.php');
$bootstrap = file_get_contents($root . '/inc/core/bootstrap.php');
$main_js = file_get_contents($root . '/resource/js/main.js');
$workflow = file_get_contents($root . '/.github/workflows/release.yml');

$assert = static function ($condition, $message) {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
};

$assert(strpos($resource, "wp_script_add_data('ai-html-main', 'strategy', 'defer')") !== false, 'main script must use the WordPress defer strategy');
$assert(strpos($resource, "'defer', true") === false, 'legacy defer metadata must not be used');
$assert(strpos($related, "'orderby' => 'rand'") === false, 'related posts must not use random ordering');
$assert(strpos($related, 'aihl_related_posts_orderby') !== false, 'related post ordering must remain filterable');
$assert(strpos($bootstrap, 'output-cleanup.php') === false, 'global output buffering must not be loaded');
$assert(!file_exists($root . '/inc/output-cleanup.php'), 'obsolete output cleanup file must be removed');
$assert(strpos($main_js, 'requestAnimationFrame') !== false, 'scroll and resize updates must be frame-scheduled');
$assert(file_exists($root . '/resource/css/webfonts/aihl-solid-core.woff2'), 'solid icon subset must exist');
$assert(file_exists($root . '/resource/css/webfonts/aihl-brands-core.woff2'), 'brand icon subset must exist');
$assert(file_exists($root . '/resource/css/fontawesome/regular.min.css'), 'regular icons must register their font');
$assert(strpos($resource, "'regular-6.4.2'") !== false, 'regular icon font must be enqueued');
$assert(strpos($resource, 'aihl_requires_full_icon_font') !== false, 'custom icons must retain a full-font fallback');
$assert(strpos($workflow, "--exclude 'tools/'") !== false, 'release package must exclude build tools');

$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);
foreach ($iterator as $file) {
	if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
		continue;
	}
	$handle = fopen($file->getPathname(), 'rb');
	$prefix = fread($handle, 3);
	fclose($handle);
	$assert($prefix !== "\xEF\xBB\xBF", 'PHP files must not contain a UTF-8 BOM: ' . $file->getPathname());
}

echo "Performance contract tests passed.\n";
