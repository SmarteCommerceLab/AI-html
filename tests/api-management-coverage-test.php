<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$registry = file_get_contents($root . '/inc/theme-option-registry.php');
$management = file_get_contents($root . '/inc/integrations/management-api.php');
$openapi = file_get_contents($root . '/inc/integrations/ai-api.php');
$author = file_get_contents($root . '/inc/admin/author-profile.php');
$background = file_get_contents($root . '/inc/theme/page-background.php');

function coverage_assert(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

$runtime_fields = array();
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
	if (!$file->isFile() || $file->getExtension() !== 'php' || str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR)) {
		continue;
	}
	$source = file_get_contents($file->getPathname());
	foreach (array('/aihtml_option_value\(\s*[\'"]([^\'"]+)/', '/get_text\(\s*[\'"]([^\'"]+)/') as $pattern) {
		if (preg_match_all($pattern, $source, $matches)) {
			$runtime_fields = array_merge($runtime_fields, $matches[1]);
		}
	}
}
$runtime_fields = array_values(array_unique($runtime_fields));
sort($runtime_fields);
foreach ($runtime_fields as $field) {
	coverage_assert(str_contains($registry, "'" . $field . "'"), "opzione runtime non registrata per API: {$field}");
}

$domains = array(
	'options', 'site_settings', 'pages', 'page_background', 'content_presentation', 'menus', 'canvas',
	'code_slots', 'deploy', 'reset', 'reset_snapshots', 'author_profile', 'dependencies',
	'compliance', 'runtime_components', 'updates', 'integrations', 'api_credentials',
);
foreach ($domains as $domain) {
	coverage_assert(str_contains($management, "'" . $domain . "' => array("), "dominio assente dal catalogo: {$domain}");
}

$routes = array(
	'/aihtml/v1/ai/management',
	'/aihtml/v1/ai/site/settings',
	'/aihtml/v1/ai/canvas',
	'/aihtml/v1/ai/dependencies',
	'/aihtml/v1/ai/compliance',
	'/aihtml/v1/ai/runtime-components/render',
	'/aihtml/v1/ai/update',
	'/aihtml/v1/ai/pages/{id}/background',
	'/aihtml/v1/ai/content/{id}/presentation',
	'/aihtml/v1/ai/page-background/patterns',
	'/aihtml/v1/ai/code-slots',
	'/aihtml/v1/ai/reset/snapshots/{token}',
	'/aihtml/v1/ai/code-slots/{slot_id}',
	'/aihtml/v1/ai/code-slots/{slot_id}/toggle',
	'/aihtml/v1/ai/code-slots/{slot_id}/rollback',
	'/aihtml/v1/ai/code-slots/import',
	'/aihtml/v1/ai/code-slots/export',
	'/aihtml/v1/ai/code-slots/hooks',
	'/aihtml/v1/ai/introspection',
	'/aihtml/v1/ai/capabilities',
);
foreach ($routes as $route) {
	coverage_assert(str_contains($openapi, "'" . $route . "' => array("), "metadati OpenAPI mancanti: {$route}");
}

coverage_assert(substr_count($author, "'permission_callback' => 'aihl_ai_can_") >= 2, 'profilo autore non usa autenticazione AI');
coverage_assert(substr_count($background, 'aihl_ai_can_') >= 4, 'sfondo pagina non usa autenticazione AI');
coverage_assert(str_contains($management, "'upgrade' === \$action"), 'aggiornamento tema non installabile via API');
coverage_assert(!str_contains($management, 'str_starts_with(') && !str_contains($management, 'str_ends_with('), 'management API non compatibile con PHP 7.4');
foreach (array('SiteSettings', 'CanvasRequest', 'RuntimeComponentRequest', 'ThemeUpdateRequest', 'PageBackground', 'ContentPresentation', 'CodeSlot') as $schema) {
	coverage_assert(str_contains($openapi, "'" . $schema . "' => array("), "schema OpenAPI concreto mancante: {$schema}");
}
coverage_assert(str_contains($management, "'managed' => false") && str_contains($management, "'security-bootstrap'"), 'eccezione credenziali non dichiarata');

echo 'AI-HTML API management coverage OK: ' . count($runtime_fields) . " opzioni runtime e " . count($domains) . " domini verificati\n";
