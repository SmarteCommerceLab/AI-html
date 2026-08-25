<?php
declare(strict_types=1);

$source = (string) file_get_contents(dirname(__DIR__) . '/inc/theme/manifest-json.php');

function manifest_test_assert(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

manifest_test_assert(str_contains($source, 'function aihl_render_manifest_json_page'), 'La pagina Manifest espone il renderer amministrativo.');
manifest_test_assert(str_contains($source, '<pre id="aihl-manifest-json"'), 'Il JSON usa un visualizzatore leggibile in sola lettura.');
manifest_test_assert(str_contains($source, 'Endpoint API protetto'), 'La UI chiarisce che l endpoint richiede autenticazione REST.');
manifest_test_assert(str_contains($source, 'aihl-mj-copy-endpoint'), 'La UI consente di copiare l URL senza aprirlo direttamente.');
manifest_test_assert(!str_contains($source, 'aihl_manifest_json_store_snapshot'), 'Il versionamento del manifest e stato rimosso.');
manifest_test_assert(!str_contains($source, 'aihl_manifest_restore'), 'Il ripristino del manifest e stato rimosso.');
manifest_test_assert(!str_contains($source, 'aihl_manifest_delete'), 'La cancellazione delle versioni e stata rimossa.');

echo "manifest-json-admin-test: ok\n";
