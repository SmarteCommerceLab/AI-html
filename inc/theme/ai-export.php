<?php
/**
 * Export a read-only Smart eCommerce context for commercial AI chats.
 */
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('aihl_ai_export_is_sensitive_key')) {
	function aihl_ai_export_is_sensitive_key($key) {
		$key = strtolower((string) $key);
		return (bool) preg_match('/(?:^|_)(?:api_?key|password|passwd|secret|token|nonce|authorization|cookie|session|user_login|user_email)(?:$|_)/', $key);
	}
}

if (!function_exists('aihl_ai_export_redact')) {
	function aihl_ai_export_redact($value) {
		if (!is_array($value)) {
			return $value;
		}

		$clean = array();
		foreach ($value as $key => $item) {
			if (is_string($key) && aihl_ai_export_is_sensitive_key($key)) {
				continue;
			}
			$clean[$key] = aihl_ai_export_redact($item);
		}
		return $clean;
	}
}

if (!function_exists('aihl_ai_export_product_versions')) {
	function aihl_ai_export_product_versions() {
		$products = array(
			'ai-html' => array(
				'name' => 'AI-HTML',
				'active' => true,
				'version' => defined('AIHL_VERSION') ? AIHL_VERSION : '',
			),
			'smart-bootstrap-manager' => array(
				'name' => 'Smart Bootstrap Manager',
				'active' => defined('SBIN_VERSION') || function_exists('smart_bootstrap_manager_consumer_contract'),
				'version' => defined('SBIN_VERSION') ? SBIN_VERSION : '',
			),
			'smart-builder-site' => array(
				'name' => 'Smart Builder Site',
				'active' => defined('SBS_VERSION') || function_exists('sbs_get_widget_registry'),
				'version' => defined('SBS_VERSION') ? SBS_VERSION : '',
			),
		);

		return $products;
	}
}

if (!function_exists('aihl_ai_export_payload')) {
	function aihl_ai_export_payload() {
		$manifest = function_exists('aihl_get_theme_integration_manifest')
			? aihl_get_theme_integration_manifest()
			: array();
		$sbm = function_exists('aihl_sbm_consumer_contract')
			? aihl_sbm_consumer_contract()
			: array();
		$sbs = function_exists('sbs_get_widget_registry')
			? sbs_get_widget_registry()
			: array();
		$hooks = function_exists('aihl_code_slots_hooks')
			? aihl_code_slots_hooks()
			: array();

		$payload = array(
			'format' => 'smart-ecommerce-ai-context',
			'format_version' => 1,
			'generated_at' => gmdate('c'),
			'read_only' => true,
			'purpose' => 'Context for a commercial AI chat. Generate proposals and copyable AI-HTML Code Slots or SBS Canvas artifacts; never assume direct site access.',
			'site' => array(
				'name' => get_bloginfo('name'),
				'url' => home_url('/'),
				'language' => get_bloginfo('language'),
			),
			'products' => aihl_ai_export_product_versions(),
			'contracts' => array(
				'ai_html_manifest' => $manifest,
				'sbm_consumer_contract' => $sbm,
				'sbs_widget_registry' => $sbs,
				'code_slot_hooks' => $hooks,
			),
			'knowledge_entry_points' => array(
				'chat_classic' => array(
					'label' => 'ChatGPT, Claude or Gemini without site access',
					'url' => 'https://kb.smartecommerce.it/guide/ai-commerciali-chat-classica/',
				),
				'smart_ai_studio' => array(
					'label' => 'Smart AI Studio with governed site operations',
					'url' => 'https://kb.smartecommerce.it/guide/attivare-smart-ai-studio/',
				),
				'standalone' => array(
					'label' => 'Standalone workflow without ai.smartecommerce.it',
					'url' => 'https://kb.smartecommerce.it/guide/flusso-ai-standalone/',
				),
				'code_slots' => array(
					'label' => 'AI-HTML Code Slots contract',
					'url' => 'https://kb.smartecommerce.it/api/ai-html-code-slots/',
				),
				'ai_canvas' => array(
					'label' => 'SBS AI Canvas authoring contract',
					'url' => 'https://kb.smartecommerce.it/guide/authoring-ai-canvas/',
				),
			),
			'assistant_instructions' => array(
				'Use this document as the source of truth for the current WordPress site.',
				'Do not ask the user to interpret endpoints, IDs, menu locations, tokens, hooks or widget schemas.',
				'Do not invent resources that are absent from the contracts.',
				'Keep AI-HTML Code Slot code, css and js in separate fields.',
				'Use WordPress runtime components for identity, menus, contacts, social links and configured add-ons.',
				'Respect SBM design governance and validate artifacts before activation.',
				'Return copyable artifacts and short non-technical insertion steps.',
			),
		);

		return apply_filters('aihl_ai_export_payload', aihl_ai_export_redact($payload));
	}
}

add_action('admin_post_aihl_download_ai_context', function () {
	if (!current_user_can('manage_options')) {
		wp_die(esc_html__('Non hai i permessi per esportare il contesto AI.', AIHL_TEXT_DOMAIN));
	}
	check_admin_referer('aihl_download_ai_context');

	$json = wp_json_encode(aihl_ai_export_payload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if (!is_string($json)) {
		wp_die(esc_html__('Impossibile generare il contesto AI.', AIHL_TEXT_DOMAIN));
	}

	$host = (string) wp_parse_url(home_url('/'), PHP_URL_HOST);
	$slug = sanitize_title($host ?: get_bloginfo('name'));
	$filename = ($slug ?: 'wordpress-site') . '-ai-context.json';

	nocache_headers();
	header('Content-Type: application/json; charset=utf-8');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('X-Content-Type-Options: nosniff');
	echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
	exit;
});

if (!function_exists('aihl_render_ai_export_page')) {
	function aihl_render_ai_export_page() {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Non hai i permessi per accedere a questa pagina.', AIHL_TEXT_DOMAIN));
		}

		$products = aihl_ai_export_product_versions();
		$ready = !empty($products['smart-bootstrap-manager']['active']) && !empty($products['smart-builder-site']['active']);
		$download_url = wp_nonce_url(
			admin_url('admin-post.php?action=aihl_download_ai_context'),
			'aihl_download_ai_context'
		);
		$prompt = __('Leggi il file di contesto allegato come fonte autorevole del sito. Progetta il sito richiesto senza inventare risorse WordPress. Restituisci Code Slot AI-HTML e Canvas SBS separati, conformi alla governance SBM, con istruzioni brevi per copiarli nel sito.', AIHL_TEXT_DOMAIN);
		?>
		<div class="aihl-ai-export">
			<section class="aihl-ai-export-hero" aria-labelledby="aihl-ai-export-title">
				<div class="aihl-ai-export-hero-copy">
					<span class="aihl-ai-export-kicker"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i><?php esc_html_e('Chat AI classica', AIHL_TEXT_DOMAIN); ?></span>
					<h2 id="aihl-ai-export-title"><?php esc_html_e('Porta il contesto del sito nella tua AI', AIHL_TEXT_DOMAIN); ?></h2>
					<p><?php esc_html_e('Un solo file raccoglie struttura, menu, risorse, widget e regole di design. Allegalo a ChatGPT, Claude o Gemini e continua a parlare in linguaggio naturale.', AIHL_TEXT_DOMAIN); ?></p>
					<div class="aihl-ai-export-actions">
						<a class="button button-primary aihl-ai-export-primary" href="<?php echo esc_url($download_url); ?>"><i class="fa-solid fa-download" aria-hidden="true"></i><?php esc_html_e('Scarica contesto AI', AIHL_TEXT_DOMAIN); ?></a>
						<button type="button" class="button aihl-ai-copy-prompt" data-copy="<?php echo esc_attr($prompt); ?>"><i class="fa-solid fa-copy" aria-hidden="true"></i><?php esc_html_e('Copia richiesta iniziale', AIHL_TEXT_DOMAIN); ?></button>
					</div>
				</div>
				<div class="aihl-ai-export-status <?php echo $ready ? 'is-ready' : 'needs-attention'; ?>">
					<i class="fa-solid <?php echo $ready ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>" aria-hidden="true"></i>
					<strong><?php echo esc_html($ready ? __('Contesto completo', AIHL_TEXT_DOMAIN) : __('Contesto parziale', AIHL_TEXT_DOMAIN)); ?></strong>
					<span><?php echo esc_html($ready ? __('AI-HTML, SBM e SBS sono disponibili.', AIHL_TEXT_DOMAIN) : __('Il file indichera chiaramente le integrazioni mancanti.', AIHL_TEXT_DOMAIN)); ?></span>
				</div>
			</section>

			<section class="aihl-ai-export-flow" aria-labelledby="aihl-ai-flow-title">
				<div class="aihl-ai-export-section-head"><span><?php esc_html_e('Tre passaggi', AIHL_TEXT_DOMAIN); ?></span><h2 id="aihl-ai-flow-title"><?php esc_html_e('Dal sito alla chat, senza configurazioni tecniche', AIHL_TEXT_DOMAIN); ?></h2></div>
				<div class="aihl-ai-export-steps">
					<article><b>1</b><span class="aihl-ai-step-icon step-export"><i class="fa-solid fa-file-arrow-down" aria-hidden="true"></i></span><h3><?php esc_html_e('Esporta', AIHL_TEXT_DOMAIN); ?></h3><p><?php esc_html_e('Scarica il file aggiornato direttamente da WordPress.', AIHL_TEXT_DOMAIN); ?></p></article>
					<i class="fa-solid fa-arrow-right aihl-ai-step-arrow" aria-hidden="true"></i>
					<article><b>2</b><span class="aihl-ai-step-icon step-chat"><i class="fa-solid fa-message" aria-hidden="true"></i></span><h3><?php esc_html_e('Allega e descrivi', AIHL_TEXT_DOMAIN); ?></h3><p><?php esc_html_e('Allega il file alla chat e racconta il sito che vuoi creare.', AIHL_TEXT_DOMAIN); ?></p></article>
					<i class="fa-solid fa-arrow-right aihl-ai-step-arrow" aria-hidden="true"></i>
					<article><b>3</b><span class="aihl-ai-step-icon step-build"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span><h3><?php esc_html_e('Copia gli artefatti', AIHL_TEXT_DOMAIN); ?></h3><p><?php esc_html_e('Inserisci Code Slot e Canvas nei rispettivi editor e valida.', AIHL_TEXT_DOMAIN); ?></p></article>
				</div>
			</section>

			<section class="aihl-ai-export-details">
				<div><span class="aihl-ai-detail-icon"><i class="fa-solid fa-box-open" aria-hidden="true"></i></span><h2><?php esc_html_e('Cosa contiene', AIHL_TEXT_DOMAIN); ?></h2><p><?php esc_html_e('Manifest AI-HTML, contratto SBM, registry SBS, hook Code Slots, versioni, capability, ingressi KB e istruzioni per la AI.', AIHL_TEXT_DOMAIN); ?></p></div>
				<div><span class="aihl-ai-detail-icon is-safe"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span><h2><?php esc_html_e('Sicuro da allegare', AIHL_TEXT_DOMAIN); ?></h2><p><?php esc_html_e('Il file non include API key, password, token, nonce, cookie, sessioni o account WordPress e non viene salvato nel database.', AIHL_TEXT_DOMAIN); ?></p></div>
			</section>

			<section class="aihl-ai-export-studio">
				<div><span><?php esc_html_e('Vuoi applicazione automatica, preview e rollback?', AIHL_TEXT_DOMAIN); ?></span><h2><?php esc_html_e('Usa Smart AI Studio', AIHL_TEXT_DOMAIN); ?></h2><p><?php esc_html_e('Lo Studio legge direttamente capability e contratti del sito e governa le operazioni. L export serve invece alle chat classiche.', AIHL_TEXT_DOMAIN); ?></p></div>
				<a class="button" href="https://studio.smartecommerce.it" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Apri Smart AI Studio', AIHL_TEXT_DOMAIN); ?><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
			</section>
			<p class="aihl-ai-copy-feedback" role="status" aria-live="polite"></p>
		</div>
		<script>
		(function(){
			var button=document.querySelector('.aihl-ai-copy-prompt');
			var feedback=document.querySelector('.aihl-ai-copy-feedback');
			if(!button||!navigator.clipboard){return;}
			button.addEventListener('click',function(){
				navigator.clipboard.writeText(button.getAttribute('data-copy')||'').then(function(){
					feedback.textContent=<?php echo wp_json_encode(__('Richiesta iniziale copiata.', AIHL_TEXT_DOMAIN)); ?>;
				});
			});
		})();
		</script>
		<?php
	}
}

add_action('admin_enqueue_scripts', function ($hook) {
	if (false === strpos((string) $hook, 'aihl-ai-export')) {
		return;
	}

	$css = <<<'CSS'
.aihl-ai-export{display:flex;flex-direction:column;gap:28px;color:#1d2327}
.aihl-ai-export-hero{display:grid;grid-template-columns:minmax(0,1fr) 230px;gap:28px;align-items:center;padding:32px;border:1px solid #c7d7f5;border-left:5px solid #3157d5;background:#f4f7ff}
.aihl-ai-export-kicker,.aihl-ai-export-section-head>span{display:inline-flex;align-items:center;gap:8px;color:#3157d5;font-size:12px;font-weight:700;text-transform:uppercase}
.aihl-ai-export-hero h2{max-width:720px;margin:10px 0 10px;font-size:30px;line-height:1.15}.aihl-ai-export-hero p{max-width:760px;margin:0;color:#4b5563;font-size:16px;line-height:1.55}
.aihl-ai-export-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:24px}.aihl-ai-export-actions .button{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:44px;padding:0 18px}.aihl-ai-export-actions .aihl-ai-export-primary{min-height:52px;padding:0 24px;font-size:15px;font-weight:700;background:#3157d5;border-color:#3157d5}
.aihl-ai-export-status{display:flex;min-height:150px;flex-direction:column;justify-content:center;padding:22px;border:1px solid #b7e4d5;background:#ecf9f4}.aihl-ai-export-status i{margin-bottom:14px;color:#008a67;font-size:28px}.aihl-ai-export-status strong{font-size:17px}.aihl-ai-export-status span{margin-top:5px;color:#50645d;line-height:1.45}.aihl-ai-export-status.needs-attention{border-color:#f2cf82;background:#fff8e7}.aihl-ai-export-status.needs-attention i{color:#b26a00}
.aihl-ai-export-section-head{margin-bottom:18px}.aihl-ai-export-section-head h2{margin:5px 0 0;font-size:22px}.aihl-ai-export-steps{display:grid;grid-template-columns:minmax(0,1fr) 36px minmax(0,1fr) 36px minmax(0,1fr);align-items:center;border-block:1px solid #dcdcde}.aihl-ai-export-steps article{position:relative;min-height:190px;padding:26px 22px}.aihl-ai-export-steps article>b{position:absolute;top:18px;right:18px;color:#a7aaad;font-size:24px}.aihl-ai-step-icon{display:flex;width:48px;height:48px;align-items:center;justify-content:center;margin-bottom:18px;border-radius:6px;color:#fff;font-size:20px}.step-export{background:#008a67}.step-chat{background:#3157d5}.step-build{background:#c23678}.aihl-ai-export-steps h3{margin:0 0 7px;font-size:18px}.aihl-ai-export-steps p{margin:0;color:#646970;font-size:14px;line-height:1.5}.aihl-ai-step-arrow{color:#a7aaad;text-align:center}
.aihl-ai-export-details{display:grid;grid-template-columns:1fr 1fr;border:1px solid #dcdcde}.aihl-ai-export-details>div{padding:24px}.aihl-ai-export-details>div+div{border-left:1px solid #dcdcde}.aihl-ai-detail-icon{display:flex;width:42px;height:42px;align-items:center;justify-content:center;float:left;margin:0 14px 24px 0;border-radius:6px;background:#e8eefc;color:#3157d5;font-size:18px}.aihl-ai-detail-icon.is-safe{background:#e3f5ed;color:#008a67}.aihl-ai-export-details h2{margin:0 0 7px;font-size:17px}.aihl-ai-export-details p{margin:0;color:#646970;line-height:1.55}
.aihl-ai-export-studio{display:flex;align-items:center;justify-content:space-between;gap:28px;padding:24px 26px;border:1px solid #ead6e2;background:#fff6fb}.aihl-ai-export-studio span{color:#9b2c68;font-size:12px;font-weight:700;text-transform:uppercase}.aihl-ai-export-studio h2{margin:4px 0 6px;font-size:20px}.aihl-ai-export-studio p{margin:0;color:#646970}.aihl-ai-export-studio .button{display:inline-flex;flex-shrink:0;align-items:center;gap:8px}.aihl-ai-copy-feedback{min-height:20px;margin:0;color:#008a67;font-weight:600}
@media(max-width:900px){.aihl-ai-export-hero{grid-template-columns:1fr}.aihl-ai-export-status{min-height:0}.aihl-ai-export-steps{grid-template-columns:1fr}.aihl-ai-step-arrow{display:none}.aihl-ai-export-steps article+article{border-top:1px solid #dcdcde}.aihl-ai-export-details{grid-template-columns:1fr}.aihl-ai-export-details>div+div{border-top:1px solid #dcdcde;border-left:0}.aihl-ai-export-studio{align-items:flex-start;flex-direction:column}.aihl-ai-export-hero h2{font-size:25px}}
CSS;
	wp_add_inline_style('smart-admin-fa', $css);
});
