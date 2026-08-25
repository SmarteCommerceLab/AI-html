<?php
/**
 * Full-width live manifest inspector and source configuration history.
 */
if (!defined('ABSPATH')) {
	exit;
}

const AIHL_MANIFEST_VERSIONS_OPTION = 'aihl_manifest_json_versions';

function aihl_manifest_json_versions(): array {
	$versions = get_option(AIHL_MANIFEST_VERSIONS_OPTION, array());
	return is_array($versions) ? array_values(array_filter($versions, 'is_array')) : array();
}

function aihl_manifest_json_snapshot(string $label = ''): array {
	$manifest = function_exists('aihl_get_theme_integration_manifest') ? aihl_get_theme_integration_manifest() : array();
	$options = get_option(AIHL_OPTION_BASE . '_general', array());
	$options = is_array($options) ? $options : array();
	$created_at = time();
	$id = sanitize_key(wp_generate_uuid4());
	return array(
		'id' => $id,
		'label' => sanitize_text_field($label ?: __('Snapshot manuale', AIHL_TEXT_DOMAIN)),
		'created_at' => $created_at,
		'user_id' => get_current_user_id(),
		'theme_version' => defined('AIHL_VERSION') ? AIHL_VERSION : '',
		'manifest' => $manifest,
		'options' => $options,
	);
}

function aihl_manifest_json_store_snapshot(string $label = ''): array {
	$versions = aihl_manifest_json_versions();
	array_unshift($versions, aihl_manifest_json_snapshot($label));
	$versions = array_slice($versions, 0, 20);
	update_option(AIHL_MANIFEST_VERSIONS_OPTION, $versions, false);
	return $versions[0];
}

function aihl_manifest_json_find_version(string $id): ?array {
	foreach (aihl_manifest_json_versions() as $version) {
		if (hash_equals((string) ($version['id'] ?? ''), $id)) {
			return $version;
		}
	}
	return null;
}

add_action('admin_post_aihl_manifest_snapshot', function () {
	if (!current_user_can('edit_theme_options')) {
		wp_die(esc_html__('Permessi insufficienti.', AIHL_TEXT_DOMAIN));
	}
	check_admin_referer('aihl_manifest_snapshot');
	aihl_manifest_json_store_snapshot(isset($_POST['snapshot_label']) ? wp_unslash((string) $_POST['snapshot_label']) : '');
	wp_safe_redirect(add_query_arg(array('page' => 'aihl-manifest-json', 'notice' => 'snapshot'), admin_url('admin.php')));
	exit;
});

add_action('admin_post_aihl_manifest_restore', function () {
	if (!current_user_can('edit_theme_options')) {
		wp_die(esc_html__('Permessi insufficienti.', AIHL_TEXT_DOMAIN));
	}
	check_admin_referer('aihl_manifest_restore');
	$id = sanitize_key(isset($_POST['version_id']) ? (string) $_POST['version_id'] : '');
	$version = aihl_manifest_json_find_version($id);
	if (!$version || !isset($version['options']) || !is_array($version['options'])) {
		wp_die(esc_html__('Versione non disponibile.', AIHL_TEXT_DOMAIN));
	}
	aihl_manifest_json_store_snapshot(__('Backup prima del ripristino', AIHL_TEXT_DOMAIN));
	update_option(AIHL_OPTION_BASE . '_general', $version['options'], false);
	wp_safe_redirect(add_query_arg(array('page' => 'aihl-manifest-json', 'notice' => 'restored'), admin_url('admin.php')));
	exit;
});

add_action('admin_post_aihl_manifest_delete', function () {
	if (!current_user_can('edit_theme_options')) {
		wp_die(esc_html__('Permessi insufficienti.', AIHL_TEXT_DOMAIN));
	}
	check_admin_referer('aihl_manifest_delete');
	$id = sanitize_key(isset($_POST['version_id']) ? (string) $_POST['version_id'] : '');
	$versions = array_values(array_filter(aihl_manifest_json_versions(), static fn(array $version): bool => !hash_equals((string) ($version['id'] ?? ''), $id)));
	update_option(AIHL_MANIFEST_VERSIONS_OPTION, $versions, false);
	wp_safe_redirect(add_query_arg(array('page' => 'aihl-manifest-json', 'notice' => 'deleted'), admin_url('admin.php')));
	exit;
});

function aihl_render_manifest_json_page(): void {
	if (!current_user_can('edit_theme_options')) {
		return;
	}
	$manifest = function_exists('aihl_get_theme_integration_manifest') ? aihl_get_theme_integration_manifest() : array();
	$json = (string) wp_json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	$versions = aihl_manifest_json_versions();
	$notice = sanitize_key(isset($_GET['notice']) ? (string) $_GET['notice'] : '');
	$messages = array(
		'snapshot' => __('Versione creata.', AIHL_TEXT_DOMAIN),
		'restored' => __('Configurazione sorgente ripristinata. Il manifest live e stato rigenerato.', AIHL_TEXT_DOMAIN),
		'deleted' => __('Versione eliminata.', AIHL_TEXT_DOMAIN),
	);
	?>
	<div class="wrap aihl-mj-wrap">
		<?php if (isset($messages[$notice])) : ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($messages[$notice]); ?></p></div><?php endif; ?>
		<div class="aihl-mj-explainer" role="note">
			<strong><?php esc_html_e('Questa pagina mostra il risultato, non la sorgente.', AIHL_TEXT_DOMAIN); ?></strong>
			<span><?php esc_html_e('Il Manifest JSON descrive lo stato live del sito combinando configurazione del tema, menu, risorse e capability dei plugin. Per cambiare le opzioni del tema usa Configurazione JSON.', AIHL_TEXT_DOMAIN); ?></span>
			<a class="button" href="<?php echo esc_url(admin_url('admin.php?page=aihl-options-json')); ?>"><?php esc_html_e('Apri Configurazione JSON', AIHL_TEXT_DOMAIN); ?></a>
		</div>
		<div class="aihl-mj-intro">
			<div><h2><?php esc_html_e('Manifest live del sito', AIHL_TEXT_DOMAIN); ?></h2><p><?php esc_html_e('Documento generato da WordPress, AI-HTML e plugin attivi. Non si modifica direttamente: cambia quando cambiano le configurazioni sorgente.', AIHL_TEXT_DOMAIN); ?></p></div>
			<a class="button" href="<?php echo esc_url(rest_url('aihtml/v1/ai/integration-manifest')); ?>" target="_blank" rel="noopener"><?php esc_html_e('Apri endpoint', AIHL_TEXT_DOMAIN); ?></a>
		</div>
		<section class="aihl-mj-card aihl-mj-editor-card">
			<div class="aihl-mj-toolbar"><strong><?php esc_html_e('JSON generato', AIHL_TEXT_DOMAIN); ?></strong><span id="aihl-mj-status"><?php esc_html_e('Sola lettura', AIHL_TEXT_DOMAIN); ?></span><button type="button" class="button" id="aihl-mj-copy"><?php esc_html_e('Copia JSON', AIHL_TEXT_DOMAIN); ?></button></div>
			<pre id="aihl-manifest-json" class="aihl-mj-editor" tabindex="0" aria-label="<?php esc_attr_e('Manifest JSON generato', AIHL_TEXT_DOMAIN); ?>"><?php echo esc_html($json); ?></pre>
		</section>
		<section class="aihl-mj-card">
			<header><div><h2><?php esc_html_e('Crea una versione', AIHL_TEXT_DOMAIN); ?></h2><p><?php esc_html_e('Salva manifest e configurazione sorgente prima di modificare header, footer, menu, contatti o integrazioni.', AIHL_TEXT_DOMAIN); ?></p></div></header>
			<form class="aihl-mj-snapshot-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<input type="hidden" name="action" value="aihl_manifest_snapshot"><?php wp_nonce_field('aihl_manifest_snapshot'); ?>
				<label><span><?php esc_html_e('Nome versione', AIHL_TEXT_DOMAIN); ?></span><input type="text" name="snapshot_label" placeholder="<?php esc_attr_e('Prima della modifica header', AIHL_TEXT_DOMAIN); ?>"></label>
				<button type="submit" class="button button-primary"><?php esc_html_e('Crea versione', AIHL_TEXT_DOMAIN); ?></button>
				<a class="button" href="<?php echo esc_url(admin_url('admin.php?page=aihl-options-json')); ?>"><?php esc_html_e('Modifica configurazione', AIHL_TEXT_DOMAIN); ?></a>
			</form>
		</section>
		<section class="aihl-mj-card">
			<header><div><h2><?php esc_html_e('Cronologia', AIHL_TEXT_DOMAIN); ?></h2><p><?php esc_html_e('Massimo 20 versioni. Il ripristino crea automaticamente un backup preventivo.', AIHL_TEXT_DOMAIN); ?></p></div><span class="aihl-mj-count"><?php echo esc_html((string) count($versions)); ?></span></header>
			<?php if (!$versions) : ?><p class="aihl-mj-empty"><?php esc_html_e('Nessuna versione salvata.', AIHL_TEXT_DOMAIN); ?></p><?php else : ?>
			<div class="aihl-mj-history">
			<?php foreach ($versions as $version) : $id = (string) ($version['id'] ?? ''); ?>
				<article><div><strong><?php echo esc_html((string) ($version['label'] ?? $id)); ?></strong><small><?php echo esc_html(wp_date('d/m/Y H:i', (int) ($version['created_at'] ?? 0))); ?> · v<?php echo esc_html((string) ($version['theme_version'] ?? '')); ?></small></div>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="aihl_manifest_restore"><input type="hidden" name="version_id" value="<?php echo esc_attr($id); ?>"><?php wp_nonce_field('aihl_manifest_restore'); ?><button class="button" type="submit"><?php esc_html_e('Ripristina', AIHL_TEXT_DOMAIN); ?></button></form>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('<?php echo esc_js(__('Eliminare questa versione?', AIHL_TEXT_DOMAIN)); ?>');"><input type="hidden" name="action" value="aihl_manifest_delete"><input type="hidden" name="version_id" value="<?php echo esc_attr($id); ?>"><?php wp_nonce_field('aihl_manifest_delete'); ?><button class="button button-link-delete" type="submit"><?php esc_html_e('Elimina', AIHL_TEXT_DOMAIN); ?></button></form>
				</article>
			<?php endforeach; ?>
			</div><?php endif; ?>
		</section>
	</div>
	<style>
	.aihl-mj-wrap{margin:0}.aihl-mj-explainer{display:grid;grid-template-columns:minmax(220px,auto) minmax(320px,1fr) auto;align-items:center;gap:12px;margin:0 0 20px;padding:14px 16px;border-left:4px solid #2271b1;background:#f0f6fc;color:#1d2327}.aihl-mj-explainer span{font-size:14px;line-height:1.5}.aihl-mj-intro,.aihl-mj-card>header,.aihl-mj-toolbar,.aihl-mj-snapshot-form,.aihl-mj-history article{display:flex;align-items:center;justify-content:space-between;gap:16px}.aihl-mj-intro{margin-bottom:16px}.aihl-mj-intro h2,.aihl-mj-card h2{margin:0 0 4px}.aihl-mj-intro p,.aihl-mj-card header p{margin:0;color:#646970}.aihl-mj-card{margin:0 0 20px;padding:18px;background:#fff;border:1px solid #dcdcde;border-radius:4px}.aihl-mj-editor-card{padding:0;overflow:hidden}.aihl-mj-toolbar{padding:10px 14px;background:#f6f7f7;border-bottom:1px solid #dcdcde}.aihl-mj-toolbar span{margin-left:auto;color:#646970}.aihl-mj-editor{display:block;width:100%;height:68vh;min-height:520px;margin:0;padding:20px;overflow:auto;border:0;border-radius:0;background:#111827!important;color:#f8fafc!important;white-space:pre;tab-size:2;font:15px/1.65 Consolas,Monaco,"Courier New",monospace;box-sizing:border-box}.aihl-mj-editor:focus{outline:3px solid #2271b1;outline-offset:-3px}.aihl-mj-snapshot-form{justify-content:flex-start;margin-top:16px}.aihl-mj-snapshot-form label{display:grid;flex:1 1 420px;max-width:640px;gap:5px}.aihl-mj-snapshot-form label span{font-weight:600}.aihl-mj-snapshot-form input{width:100%}.aihl-mj-count{display:grid;min-width:30px;height:30px;place-items:center;background:#eef6fc;color:#135e96;border-radius:4px;font-weight:700}.aihl-mj-history{margin-top:14px;border-top:1px solid #dcdcde}.aihl-mj-history article{justify-content:flex-start;padding:12px 0;border-bottom:1px solid #dcdcde}.aihl-mj-history article>div{display:grid;flex:1;gap:3px}.aihl-mj-history small,.aihl-mj-empty{color:#646970}@media(max-width:900px){.aihl-mj-explainer{grid-template-columns:1fr;align-items:start}}@media(max-width:782px){.aihl-mj-intro,.aihl-mj-card>header,.aihl-mj-snapshot-form,.aihl-mj-history article{align-items:stretch;flex-direction:column}.aihl-mj-snapshot-form label{flex-basis:auto}.aihl-mj-editor{height:60vh;min-height:420px}}
	</style>
	<script>(function(){var editor=document.getElementById('aihl-manifest-json'),copy=document.getElementById('aihl-mj-copy'),status=document.getElementById('aihl-mj-status');if(!editor||!copy)return;copy.addEventListener('click',function(){navigator.clipboard.writeText(editor.textContent||'').then(function(){status.textContent='<?php echo esc_js(__('Copiato', AIHL_TEXT_DOMAIN)); ?>';setTimeout(function(){status.textContent='<?php echo esc_js(__('Sola lettura', AIHL_TEXT_DOMAIN)); ?>';},1600);});});})();</script>
	<?php
}
