<?php
$root = dirname(__DIR__);
$hub = file_get_contents($root . '/inc/admin/admin-hub.php');
$export = file_get_contents($root . '/inc/theme/ai-export.php');
$bootstrap = file_get_contents($root . '/inc/core/bootstrap.php');

function ai_export_admin_assert($condition, $message) {
	if (!$condition) {
		fwrite(STDERR, $message . PHP_EOL);
		exit(1);
	}
}

ai_export_admin_assert(false !== strpos($bootstrap, "'inc/theme/ai-export.php'"), 'Bootstrap export AI assente.');
ai_export_admin_assert(false !== strpos($hub, "'slug'        => 'aihl-chat-context'"), 'Pagina Esporta per AI non registrata.');
ai_export_admin_assert(strpos($hub, "'aihl-chat-context' =>") < strpos($hub, "'aihl-api-keys' =>"), 'Esporta per AI deve precedere Accesso API.');
ai_export_admin_assert(false !== strpos($export, 'admin_post_aihl_context_file'), 'Handler file contesto assente.');
ai_export_admin_assert(false === strpos($hub, "'slug'        => 'aihl-ai-export'"), 'Lo slug pagina filtrabile dal browser non deve essere usato.');
ai_export_admin_assert(false === strpos($export, 'admin-post.php?action=aihl_download_ai_context'), 'L azione filtrabile dal browser non deve essere usata.');
ai_export_admin_assert(false !== strpos($export, 'check_admin_referer'), 'Protezione nonce export assente.');
ai_export_admin_assert(false !== strpos($export, 'aihl_ai_export_redact'), 'Redazione segreti export assente.');
ai_export_admin_assert(false !== strpos($export, 'Scarica contesto AI'), 'Azione primaria export assente.');
ai_export_admin_assert(false !== strpos($export, 'Tre passaggi'), 'Flow istruttivo export assente.');
ai_export_admin_assert(false !== strpos($export, 'Esporta'), 'Passaggio export assente.');
ai_export_admin_assert(false !== strpos($export, 'Allega e descrivi'), 'Passaggio chat assente.');
ai_export_admin_assert(false !== strpos($export, 'Copia gli artefatti'), 'Passaggio applicazione assente.');
ai_export_admin_assert(false !== strpos($export, 'aihl_ai_export_prompt_templates'), 'Catalogo prompt AI assente.');
ai_export_admin_assert(false !== strpos($export, 'Inizia sempre dal punto 1'), 'Sequenza iniziale dei prompt assente.');
ai_export_admin_assert(false !== strpos($export, 'aihl-ai-prompt-text'), 'Anteprima prompt assente.');
ai_export_admin_assert(false !== strpos($export, 'Vedi tutti i casi nella KB'), 'Collegamento libreria prompt KB assente.');

echo "AI export admin contract OK\n";
