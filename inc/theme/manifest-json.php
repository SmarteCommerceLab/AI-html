<?php
/**
 * Full-width live integration manifest inspector.
 */
if (!defined('ABSPATH')) {
	exit;
}

function aihl_render_manifest_json_page(): void {
	if (!current_user_can('edit_theme_options')) {
		return;
	}

	$manifest = function_exists('aihl_get_theme_integration_manifest') ? aihl_get_theme_integration_manifest() : array();
	$json = (string) wp_json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	$endpoint = rest_url('aihtml/v1/ai/integration-manifest');
	?>
	<div class="wrap aihl-mj-wrap">
		<div class="aihl-mj-guide" role="note">
			<div class="aihl-mj-guide-step">
				<span>1</span>
				<div><strong><?php esc_html_e('Configurazione JSON', AIHL_TEXT_DOMAIN); ?></strong><small><?php esc_html_e('Modifica le opzioni del tema.', AIHL_TEXT_DOMAIN); ?></small></div>
			</div>
			<div class="aihl-mj-guide-arrow" aria-hidden="true">→</div>
			<div class="aihl-mj-guide-step is-current">
				<span>2</span>
				<div><strong><?php esc_html_e('Manifest live', AIHL_TEXT_DOMAIN); ?></strong><small><?php esc_html_e('Mostra lo stato generato del sito.', AIHL_TEXT_DOMAIN); ?></small></div>
			</div>
			<p><?php esc_html_e('Il manifest unisce configurazione, menu, risorse e capability dei plugin. Si aggiorna automaticamente e non si modifica in questa pagina.', AIHL_TEXT_DOMAIN); ?></p>
			<a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=aihl-options-json')); ?>"><?php esc_html_e('Modifica configurazione', AIHL_TEXT_DOMAIN); ?></a>
		</div>

		<div class="aihl-mj-heading">
			<div><h2><?php esc_html_e('Manifest live del sito', AIHL_TEXT_DOMAIN); ?></h2><p><?php esc_html_e('Documento operativo generato da WordPress, AI-HTML e plugin attivi.', AIHL_TEXT_DOMAIN); ?></p></div>
		</div>

		<section class="aihl-mj-editor-card">
			<div class="aihl-mj-toolbar"><strong><?php esc_html_e('JSON generato', AIHL_TEXT_DOMAIN); ?></strong><span id="aihl-mj-status"><?php esc_html_e('Sola lettura', AIHL_TEXT_DOMAIN); ?></span><button type="button" class="button" id="aihl-mj-copy"><?php esc_html_e('Copia JSON', AIHL_TEXT_DOMAIN); ?></button></div>
			<pre id="aihl-manifest-json" class="aihl-mj-editor" tabindex="0" aria-label="<?php esc_attr_e('Manifest JSON generato', AIHL_TEXT_DOMAIN); ?>"><?php echo esc_html($json); ?></pre>
		</section>

		<section class="aihl-mj-endpoint">
			<div><strong><?php esc_html_e('Endpoint API protetto', AIHL_TEXT_DOMAIN); ?></strong><code id="aihl-mj-endpoint"><?php echo esc_html($endpoint); ?></code><small><?php esc_html_e('Richiede autenticazione REST e non va aperto direttamente come pagina pubblica.', AIHL_TEXT_DOMAIN); ?></small></div>
			<button type="button" class="button" id="aihl-mj-copy-endpoint"><?php esc_html_e('Copia URL', AIHL_TEXT_DOMAIN); ?></button>
		</section>
	</div>
	<style>
	.aihl-mj-wrap{margin:0}.aihl-mj-guide{display:grid;grid-template-columns:auto 24px auto minmax(280px,1fr) auto;align-items:center;gap:14px;margin:0 0 24px;padding:18px 20px;border:1px solid #c3c4c7;border-left:4px solid #2271b1;background:#fff;color:#1d2327}.aihl-mj-guide-step{display:flex;align-items:center;gap:10px;min-width:190px}.aihl-mj-guide-step>span{display:grid;width:32px;height:32px;place-items:center;border-radius:50%;background:#f0f0f1;color:#50575e;font-weight:700}.aihl-mj-guide-step.is-current>span{background:#2271b1;color:#fff}.aihl-mj-guide-step div{display:grid;gap:2px}.aihl-mj-guide-step small{color:#646970}.aihl-mj-guide-arrow{text-align:center;color:#8c8f94;font-size:20px}.aihl-mj-guide p{margin:0;font-size:14px;line-height:1.5}.aihl-mj-heading{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}.aihl-mj-heading h2{margin:0 0 4px}.aihl-mj-heading p{margin:0;color:#646970}.aihl-mj-editor-card{margin:0 0 18px;overflow:hidden;border:1px solid #c3c4c7;background:#fff}.aihl-mj-toolbar{display:flex;align-items:center;gap:14px;padding:10px 14px;border-bottom:1px solid #c3c4c7;background:#f6f7f7}.aihl-mj-toolbar span{margin-left:auto;color:#646970}.aihl-mj-editor{display:block;width:100%;height:68vh;min-height:520px;margin:0;padding:20px;overflow:auto;border:0;background:#111827!important;color:#f8fafc!important;white-space:pre;tab-size:2;font:15px/1.65 Consolas,Monaco,"Courier New",monospace;box-sizing:border-box}.aihl-mj-editor:focus{outline:3px solid #2271b1;outline-offset:-3px}.aihl-mj-endpoint{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:16px 18px;border:1px solid #c3c4c7;background:#fff}.aihl-mj-endpoint>div{display:grid;min-width:0;gap:6px}.aihl-mj-endpoint code{display:block;overflow:hidden;color:#135e96;text-overflow:ellipsis;white-space:nowrap}.aihl-mj-endpoint small{color:#646970}@media(max-width:1100px){.aihl-mj-guide{grid-template-columns:auto 24px auto}.aihl-mj-guide p{grid-column:1/-1}.aihl-mj-guide>a{grid-column:1/-1;justify-self:start}}@media(max-width:782px){.aihl-mj-guide{grid-template-columns:1fr}.aihl-mj-guide-arrow{display:none}.aihl-mj-editor{height:60vh;min-height:420px}.aihl-mj-endpoint{align-items:stretch;flex-direction:column}}
	</style>
	<script>(function(){var editor=document.getElementById('aihl-manifest-json'),copy=document.getElementById('aihl-mj-copy'),status=document.getElementById('aihl-mj-status'),endpoint=document.getElementById('aihl-mj-endpoint'),copyEndpoint=document.getElementById('aihl-mj-copy-endpoint');if(editor&&copy){copy.addEventListener('click',function(){navigator.clipboard.writeText(editor.textContent||'').then(function(){status.textContent='<?php echo esc_js(__('Copiato', AIHL_TEXT_DOMAIN)); ?>';setTimeout(function(){status.textContent='<?php echo esc_js(__('Sola lettura', AIHL_TEXT_DOMAIN)); ?>';},1600);});});}if(endpoint&&copyEndpoint){copyEndpoint.addEventListener('click',function(){navigator.clipboard.writeText(endpoint.textContent||'').then(function(){copyEndpoint.textContent='<?php echo esc_js(__('URL copiato', AIHL_TEXT_DOMAIN)); ?>';setTimeout(function(){copyEndpoint.textContent='<?php echo esc_js(__('Copia URL', AIHL_TEXT_DOMAIN)); ?>';},1600);});});}})();</script>
	<?php
}
