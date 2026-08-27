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

if (!function_exists('aihl_ai_export_prompt_templates')) {
	function aihl_ai_export_prompt_templates() {
		$foundation = 'Leggi il file di contesto allegato come fonte autorevole del sito. Prima di rispondere consulta i documenti pubblici indicati in required_knowledge e knowledge_entry_points. Dichiara quali documenti KB hai consultato e la versione del Knowledge Pack; se non puoi aprirli, dichiaralo e usa soltanto il knowledge_snapshot incorporato, senza fingere di averli letti. Non inventare menu, pagine, URL, immagini, widget, token o integrazioni assenti. Usa le risorse dinamiche WordPress e rispetta la governance SBM dichiarata in contracts.sbm_consumer_contract.design_governance. Se la modalita effettiva e governed, usa classi Bootstrap e soltanto i token elencati in semantic_tokens e css_variables.required: non scrivere colori HEX/RGB/HSL, font-family, spacing, radius o scale tipografiche visuali dirette. Non inizializzare Bootstrap, GSAP, ScrollTrigger, WOW o carousel nel codice. Prima di generare codice, fammi solo le domande indispensabili sui dati mancanti. Prima di restituire gli artefatti esegui una verifica dichiarazione per dichiarazione e segnala che il CSS e pronto per Analizza codice. Restituisci una breve sintesi delle scelte e poi gli artefatti separati, completi e pronti da copiare nei rispettivi editor, con una checklist finale di verifica.';

		return array(
			'start_session' => array(
				'id' => 'start_session',
				'title' => 'Avvia e comprendi il sito',
				'description' => 'Primo messaggio obbligatorio in una nuova chat.',
				'icon' => 'fa-graduation-cap',
				'prompt' => 'Leggi integralmente il file smart-ecommerce-ai-context allegato e usalo come fonte autorevole per tutta questa conversazione. Consulta i documenti pubblici indicati in required_knowledge e knowledge_entry_points. Dichiara i documenti KB consultati e la versione del Knowledge Pack; se non puoi aprirli, dichiaralo e usa soltanto il knowledge_snapshot incorporato, senza fingere di averli letti. In questa fase non generare codice e non proporre ancora modifiche. Non chiedermi di interpretare endpoint, ID, menu location, hook, token, widget o capability. Non inventare risorse assenti. Rispondi con sei sezioni: identita e obiettivo; prodotti attivi; risorse disponibili; vincoli AI-HTML, SBS e SBM; operazioni possibili e non applicabili direttamente; informazioni commerciali mancanti. Nell ultima sezione usa un elenco separato da 1 a 5, senza continuare la numerazione delle sezioni. Chiudi chiedendomi quale risultato voglio ottenere. Considera questo riepilogo il contratto operativo della sessione e attendi la mia conferma prima di continuare.',
			),
			'complete_site' => array(
				'id' => 'complete_site',
				'title' => 'Crea un sito completo',
				'description' => 'Architettura, pagine, header, footer e Canvas.',
				'icon' => 'fa-sitemap',
				'prompt' => $foundation . ' Voglio progettare o completare il sito descritto nel contesto. Guidami nella definizione di obiettivo, pubblico, offerta, pagine necessarie, tono e CTA principale. Proponi prima la mappa del sito; dopo la mia conferma genera header e footer AI-HTML e i Canvas SBS delle pagine, mantenendo i contenuti in bozza.',
			),
			'header' => array(
				'id' => 'header',
				'title' => 'Crea o migliora l header',
				'description' => 'Navigazione, top bar, logo, social e CTA.',
				'icon' => 'fa-window-maximize',
				'prompt' => $foundation . ' Crea o migliora l header del sito. Usa logo, top bar, menu WordPress, social, ricerca e CTA soltanto quando risultano disponibili nel manifest. Il menu deve restare dinamico tramite i componenti runtime AI-HTML. Imposta design_mode uguale alla modalita SBM globale esportata. Restituisci lo slot header_full con HTML, CSS e JS separati e indica come provarlo con Analizza codice senza attivarlo subito.',
			),
			'footer' => array(
				'id' => 'footer',
				'title' => 'Crea o migliora il footer',
				'description' => 'Link, contatti, social, note legali e CTA.',
				'icon' => 'fa-table-columns',
				'prompt' => $foundation . ' Crea o migliora il footer del sito usando soltanto identita, menu, contatti, social, pagine e integrazioni presenti nel contesto. Mantieni dinamici i dati gestiti da WordPress. Imposta design_mode uguale alla modalita SBM globale esportata. Restituisci lo slot footer_full con HTML, CSS e JS separati, una verifica per desktop e mobile e la conferma che non contiene valori visuali diretti.',
			),
			'landing_page' => array(
				'id' => 'landing_page',
				'title' => 'Crea una landing page',
				'description' => 'Pagina focalizzata su un offerta e una conversione.',
				'icon' => 'fa-bullseye',
				'prompt' => $foundation . ' Progetta una landing page per questa offerta: [DESCRIVI OFFERTA]. Il pubblico e: [DESCRIVI PUBBLICO]. La conversione principale e: [AZIONE DESIDERATA]. Proponi struttura e messaggi prima del codice; dopo la mia conferma restituisci il Canvas SBS compatibile con il registry disponibile, senza modificare header e footer globali.',
			),
			'redesign' => array(
				'id' => 'redesign',
				'title' => 'Riprogetta una pagina',
				'description' => 'Migliora gerarchia, leggibilita e conversione.',
				'icon' => 'fa-pen-ruler',
				'prompt' => $foundation . ' Analizza la pagina che descrivero o alleghero e proponi un restyling coerente con il sito. Conserva contenuti e funzioni utili, segnala cosa manca e non copiare il sito di riferimento. Dopo la mia conferma restituisci il Canvas SBS aggiornato e un elenco sintetico delle differenze.',
			),
			'diagnosis' => array(
				'id' => 'diagnosis',
				'title' => 'Analizza e correggi',
				'description' => 'Problemi responsive, accessibilita e runtime.',
				'icon' => 'fa-stethoscope',
				'prompt' => $foundation . ' Analizza il problema che descrivero o mostrero con uno screenshot. Distingui errore di contenuto, layout, responsive, accessibilita, runtime WordPress o governance SBM. Proponi prima diagnosi e correzione minima; genera codice soltanto dopo la mia conferma e indica esattamente quale Code Slot o Canvas sostituire.',
			),
			'site_audit' => array(
				'id' => 'site_audit',
				'title' => 'Valuta il sito esistente',
				'description' => 'Priorita, lacune, coerenza e prossimi interventi.',
				'icon' => 'fa-clipboard-check',
				'prompt' => $foundation . ' Valuta lo stato attuale del sito usando il contesto e gli eventuali URL o screenshot che alleghero. Ordina i rilievi per impatto su chiarezza, conversione, mobile, accessibilita, prestazioni e coerenza con i prodotti Smart eCommerce. Non generare codice: restituisci una roadmap breve con priorita, dipendenze e criterio di completamento.',
			),
			'service_page' => array(
				'id' => 'service_page',
				'title' => 'Crea una pagina servizio',
				'description' => 'Problema, soluzione, benefici, processo e CTA.',
				'icon' => 'fa-briefcase',
				'prompt' => $foundation . ' Crea una pagina dedicata al servizio [NOME SERVIZIO] per [PUBBLICO]. Organizzala per chiarire problema, soluzione, benefici, processo, prove, domande frequenti e CTA. Non inventare prezzi, recensioni o certificazioni. Proponi prima outline e contenuti mancanti; dopo la conferma restituisci il Canvas SBS compatibile.',
			),
			'product_catalog' => array(
				'id' => 'product_catalog',
				'title' => 'Progetta catalogo eCommerce',
				'description' => 'Categorie, filtri, schede e percorso di acquisto.',
				'icon' => 'fa-store',
				'prompt' => $foundation . ' Progetta l esperienza catalogo per i prodotti descritti nel contesto o nel file che alleghero. Definisci tassonomia, categorie, filtri, ordinamento, informazioni nelle card e percorso verso la scheda prodotto. Usa WooCommerce o le integrazioni eCommerce soltanto se risultano disponibili. Prima restituisci il modello informativo; dopo la conferma genera i Canvas compatibili necessari.',
			),
			'product_page' => array(
				'id' => 'product_page',
				'title' => 'Crea una scheda prodotto',
				'description' => 'Contenuti, media, fiducia e conversione.',
				'icon' => 'fa-box-open',
				'prompt' => $foundation . ' Progetta una scheda per [NOME PRODOTTO] destinata a [PUBBLICO]. Usa soltanto dati prodotto verificati e segnala quelli mancanti. Organizza media, proposta di valore, varianti, benefici, specifiche, fiducia, spedizione, FAQ e CTA secondo le capability eCommerce disponibili. Proponi prima struttura e campi richiesti; genera il Canvas solo dopo conferma.',
			),
			'magazine_blog' => array(
				'id' => 'magazine_blog',
				'title' => 'Progetta blog o magazine',
				'description' => 'Categorie, formati editoriali e navigazione.',
				'icon' => 'fa-newspaper',
				'prompt' => $foundation . ' Progetta l area editoriale del sito. Definisci categorie, rubriche, formati, pagina archivio, card articolo, pagina articolo e collegamenti verso prodotti o servizi. Riusa categorie, autori e contenuti WordPress presenti; non inventare articoli gia pubblicati. Restituisci prima architettura e piano editoriale iniziale, poi i Canvas richiesti dopo conferma.',
			),
			'content_plan' => array(
				'id' => 'content_plan',
				'title' => 'Crea un piano contenuti',
				'description' => 'Argomenti, formati, calendario e obiettivi.',
				'icon' => 'fa-calendar-days',
				'prompt' => $foundation . ' Crea un piano contenuti per [PERIODO] orientato a [OBIETTIVO]. Parti da pubblico, offerta, categorie e pagine reali del sito. Per ogni contenuto indica intento, formato, titolo di lavoro, CTA, pagina o prodotto collegato e dati necessari. Non generare affermazioni non verificabili. Restituisci il piano prima di scrivere i singoli contenuti.',
			),
		);
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
		$sbm_governance = isset($sbm['design_governance']) && is_array($sbm['design_governance'])
			? $sbm['design_governance']
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
				'sbm_authoring_contract' => array(
					'global_mode' => $sbm_governance['options']['smart_bootstrap_option_design_mode'] ?? (function_exists('aihl_sbm_design_mode') ? aihl_sbm_design_mode() : 'autonomous'),
					'semantic_tokens' => $sbm_governance['semantic_tokens'] ?? array(),
					'required_tokens' => $sbm['css_variables']['required'] ?? array(),
					'forbidden_raw_values' => array('hex/rgb/hsl colors', 'font-family', 'spacing in px/rem/em', 'border-radius', 'font-size', 'line-height', 'letter-spacing'),
				),
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
				'prompt_library' => array(
					'label' => 'Prompt library for commercial AI chats',
					'url' => 'https://kb.smartecommerce.it/prompt-ai/',
				),
			),
			'knowledge_pack' => array(
				'id' => 'ai-html-contracts',
				'version' => '1.6.2',
				'index_url' => 'https://kb.smartecommerce.it/v1/packs.json',
			),
			'required_knowledge' => array(
				'https://kb.smartecommerce.it/prompt-ai/primo-prompt/',
				'https://kb.smartecommerce.it/guide/ai-commerciali-chat-classica/',
				'https://kb.smartecommerce.it/api/ai-html-code-slots/',
				'https://kb.smartecommerce.it/guide/authoring-ai-canvas/',
				'https://kb.smartecommerce.it/api/smart-bootstrap-manager-ai-api/',
			),
			'knowledge_snapshot' => array(
				'Use the exported manifest and registries as the source of truth for this site.',
				'Keep HTML, CSS and JavaScript as separate Canvas artifacts.',
				'Do not enqueue Bootstrap or GSAP: Smart Bootstrap Manager owns shared libraries.',
				'Use WordPress runtime components for menus, identity, contacts and configured integrations.',
				'In governed mode consume semantic --bs-*, --sbin-* and --canvas-* tokens.',
				'In governed mode never emit raw visual colors, fonts, spacing, radius or type-scale values.',
				'Use the exported SBM semantic token catalog; do not invent token names.',
			),
			'prompt_templates' => array_values(aihl_ai_export_prompt_templates()),
			'assistant_instructions' => array(
				'Use this document as the source of truth for the current WordPress site.',
				'Do not ask the user to interpret endpoints, IDs, menu locations, tokens, hooks or widget schemas.',
				'Do not invent resources that are absent from the contracts.',
				'Keep AI-HTML Code Slot code, css and js in separate fields.',
				'Use WordPress runtime components for identity, menus, contacts, social links and configured add-ons.',
				'Respect SBM design governance and validate artifacts before activation.',
				'Set design_mode to the exported global SBM mode unless a stricter mode is explicitly required.',
				'Return copyable artifacts and short non-technical insertion steps.',
			),
		);

		return apply_filters('aihl_ai_export_payload', aihl_ai_export_redact($payload));
	}
}

if (!function_exists('aihl_serve_context_file')) {
	function aihl_serve_context_file() {
	if (!current_user_can('manage_options')) {
		wp_die(esc_html__('Non hai i permessi per esportare il contesto AI.', AIHL_TEXT_DOMAIN));
	}
	check_admin_referer('aihl_context_file');

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
	}
}
add_action('admin_post_aihl_context_file', 'aihl_serve_context_file');

if (!function_exists('aihl_render_ai_export_page')) {
	function aihl_render_ai_export_page() {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Non hai i permessi per accedere a questa pagina.', AIHL_TEXT_DOMAIN));
		}

		$products = aihl_ai_export_product_versions();
		$ready = !empty($products['smart-bootstrap-manager']['active']) && !empty($products['smart-builder-site']['active']);
		$download_url = wp_nonce_url(
			admin_url('admin-post.php?action=aihl_context_file'),
			'aihl_context_file'
		);
		$prompt_templates = aihl_ai_export_prompt_templates();
		$default_prompt = $prompt_templates['start_session']['prompt'];
		?>
		<div class="aihl-ai-export">
			<section class="aihl-ai-export-hero" aria-labelledby="aihl-ai-export-title">
				<div class="aihl-ai-export-hero-copy">
					<span class="aihl-ai-export-kicker"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i><?php esc_html_e('Chat AI classica', AIHL_TEXT_DOMAIN); ?></span>
					<h2 id="aihl-ai-export-title"><?php esc_html_e('Porta il contesto del sito nella tua AI', AIHL_TEXT_DOMAIN); ?></h2>
					<p><?php esc_html_e('Un solo file raccoglie struttura, menu, risorse, widget e regole di design. Allegalo a ChatGPT, Claude o Gemini e continua a parlare in linguaggio naturale.', AIHL_TEXT_DOMAIN); ?></p>
					<div class="aihl-ai-export-actions">
						<a class="button button-primary aihl-ai-export-primary" href="<?php echo esc_url($download_url); ?>"><i class="fa-solid fa-download" aria-hidden="true"></i><?php esc_html_e('Scarica contesto AI', AIHL_TEXT_DOMAIN); ?></a>
						<a class="button" href="#aihl-prompt-library"><i class="fa-solid fa-message" aria-hidden="true"></i><?php esc_html_e('Scegli il prompt', AIHL_TEXT_DOMAIN); ?></a>
					</div>
				</div>
				<div class="aihl-ai-export-status <?php echo $ready ? 'is-ready' : 'needs-attention'; ?>">
					<i class="fa-solid <?php echo $ready ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>" aria-hidden="true"></i>
					<strong><?php echo esc_html($ready ? __('Contesto completo', AIHL_TEXT_DOMAIN) : __('Contesto parziale', AIHL_TEXT_DOMAIN)); ?></strong>
					<span><?php echo esc_html($ready ? __('AI-HTML, SBM e SBS sono disponibili.', AIHL_TEXT_DOMAIN) : __('Il file indichera chiaramente le integrazioni mancanti.', AIHL_TEXT_DOMAIN)); ?></span>
				</div>
			</section>

			<section class="aihl-ai-prompt-info" aria-labelledby="aihl-prompt-info-title">
				<div class="aihl-ai-export-section-head"><span><?php esc_html_e('Informazioni per la richiesta', AIHL_TEXT_DOMAIN); ?></span><h2 id="aihl-prompt-info-title"><?php esc_html_e('Come ottenere un prompt e un risultato migliori', AIHL_TEXT_DOMAIN); ?></h2><p><?php esc_html_e('Il file fornisce i dettagli tecnici. Tu devi aggiungere soltanto le informazioni commerciali e creative che la AI non puo ricavare dal sito.', AIHL_TEXT_DOMAIN); ?></p></div>
				<div class="aihl-ai-prompt-info-grid">
					<div><i class="fa-solid fa-bullseye" aria-hidden="true"></i><strong><?php esc_html_e('Obiettivo', AIHL_TEXT_DOMAIN); ?></strong><span><?php esc_html_e('Cosa deve ottenere la pagina o il sito.', AIHL_TEXT_DOMAIN); ?></span></div>
					<div><i class="fa-solid fa-users" aria-hidden="true"></i><strong><?php esc_html_e('Pubblico', AIHL_TEXT_DOMAIN); ?></strong><span><?php esc_html_e('A chi ti rivolgi e quale problema risolvi.', AIHL_TEXT_DOMAIN); ?></span></div>
					<div><i class="fa-solid fa-box-open" aria-hidden="true"></i><strong><?php esc_html_e('Offerta', AIHL_TEXT_DOMAIN); ?></strong><span><?php esc_html_e('Prodotti, servizi e priorita reali.', AIHL_TEXT_DOMAIN); ?></span></div>
					<div><i class="fa-solid fa-pen-nib" aria-hidden="true"></i><strong><?php esc_html_e('Tono', AIHL_TEXT_DOMAIN); ?></strong><span><?php esc_html_e('Stile del brand, riferimenti e vincoli.', AIHL_TEXT_DOMAIN); ?></span></div>
					<div><i class="fa-solid fa-arrow-pointer" aria-hidden="true"></i><strong><?php esc_html_e('Azione', AIHL_TEXT_DOMAIN); ?></strong><span><?php esc_html_e('Contatto, acquisto, preventivo o altra conversione.', AIHL_TEXT_DOMAIN); ?></span></div>
				</div>
				<p class="aihl-ai-prompt-info-example"><strong><?php esc_html_e('Esempio:', AIHL_TEXT_DOMAIN); ?></strong> <?php esc_html_e('Voglio una pagina servizio per responsabili eCommerce, con tono autorevole e una richiesta demo come azione principale.', AIHL_TEXT_DOMAIN); ?></p>
			</section>

			<section id="aihl-prompt-library" class="aihl-ai-prompt-library" aria-labelledby="aihl-prompt-library-title">
				<div class="aihl-ai-export-section-head"><span><?php esc_html_e('Prompt guidati', AIHL_TEXT_DOMAIN); ?></span><h2 id="aihl-prompt-library-title"><?php esc_html_e('Inizia sempre dal prompt iniziale', AIHL_TEXT_DOMAIN); ?></h2><p><?php esc_html_e('Prima fai comprendere il sito alla AI. Dopo la sua conferma torna qui e scegli il lavoro specifico.', AIHL_TEXT_DOMAIN); ?></p></div>
				<div class="aihl-ai-prompt-grid" role="list">
					<?php foreach ($prompt_templates as $index => $template) : ?>
						<div role="listitem"><button type="button" class="aihl-ai-prompt-choice<?php echo 0 === $index ? ' is-selected' : ''; ?>" data-prompt="<?php echo esc_attr($template['prompt']); ?>" aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>">
							<i class="fa-solid <?php echo esc_attr($template['icon']); ?>" aria-hidden="true"></i><span><strong><?php echo esc_html($template['title']); ?></strong><small><?php echo esc_html($template['description']); ?></small></span>
						</button></div>
					<?php endforeach; ?>
				</div>
				<div class="aihl-ai-prompt-editor">
					<label for="aihl-ai-prompt-text"><?php esc_html_e('Prompt pronto da copiare', AIHL_TEXT_DOMAIN); ?></label>
					<textarea id="aihl-ai-prompt-text" rows="8" readonly><?php echo esc_textarea($default_prompt); ?></textarea>
					<div><button type="button" class="button button-primary aihl-ai-copy-prompt"><i class="fa-solid fa-copy" aria-hidden="true"></i><?php esc_html_e('Copia prompt', AIHL_TEXT_DOMAIN); ?></button><a href="https://kb.smartecommerce.it/prompt-ai/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Vedi tutti i casi nella KB', AIHL_TEXT_DOMAIN); ?></a></div>
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
			var editor=document.getElementById('aihl-ai-prompt-text');
			var choices=document.querySelectorAll('.aihl-ai-prompt-choice');
			var feedback=document.querySelector('.aihl-ai-copy-feedback');
			choices.forEach(function(choice){choice.addEventListener('click',function(){choices.forEach(function(item){item.classList.remove('is-selected');item.setAttribute('aria-pressed','false');});choice.classList.add('is-selected');choice.setAttribute('aria-pressed','true');if(editor){editor.value=choice.getAttribute('data-prompt')||'';}});});
			if(!button||!editor||!navigator.clipboard){return;}
			button.addEventListener('click',function(){
				navigator.clipboard.writeText(editor.value||'').then(function(){
					feedback.textContent=<?php echo wp_json_encode(__('Prompt copiato. Ora allega il file di contesto alla chat.', AIHL_TEXT_DOMAIN)); ?>;
				});
			});
		})();
		</script>
		<?php
	}
}

add_action('admin_enqueue_scripts', function ($hook) {
	if (false === strpos((string) $hook, 'aihl-chat-context')) {
		return;
	}

	$css = <<<'CSS'
.aihl-ai-export{display:flex;flex-direction:column;gap:28px;color:#1d2327}
.aihl-ai-export-hero{display:grid;grid-template-columns:minmax(0,1fr) 230px;gap:28px;align-items:center;padding:32px;border:1px solid #c7d7f5;border-left:5px solid #3157d5;background:#f4f7ff}
.aihl-ai-export-kicker,.aihl-ai-export-section-head>span{display:inline-flex;align-items:center;gap:8px;color:#3157d5;font-size:12px;font-weight:700;text-transform:uppercase}
.aihl-ai-export-hero h2{max-width:720px;margin:10px 0 10px;font-size:30px;line-height:1.15}.aihl-ai-export-hero p{max-width:760px;margin:0;color:#4b5563;font-size:16px;line-height:1.55}
.aihl-ai-export-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:24px}.aihl-ai-export-actions .button{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:44px;padding:0 18px}.aihl-ai-export-actions .aihl-ai-export-primary{min-height:52px;padding:0 24px;font-size:15px;font-weight:700;background:#3157d5;border-color:#3157d5}
.aihl-ai-prompt-info{padding:26px;border:1px solid #dcdcde;background:#fff}.aihl-ai-prompt-info-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));border:1px solid #dcdcde}.aihl-ai-prompt-info-grid>div{display:flex;min-width:0;flex-direction:column;gap:6px;padding:18px;border-right:1px solid #dcdcde}.aihl-ai-prompt-info-grid>div:last-child{border-right:0}.aihl-ai-prompt-info-grid i{color:#3157d5;font-size:18px}.aihl-ai-prompt-info-grid strong{font-size:14px}.aihl-ai-prompt-info-grid span{color:#646970;font-size:13px;line-height:1.45}.aihl-ai-prompt-info-example{margin:14px 0 0;padding:14px 16px;border-left:4px solid #008a67;background:#ecf9f4;line-height:1.5}
.aihl-ai-export-status{display:flex;min-height:150px;flex-direction:column;justify-content:center;padding:22px;border:1px solid #b7e4d5;background:#ecf9f4}.aihl-ai-export-status i{margin-bottom:14px;color:#008a67;font-size:28px}.aihl-ai-export-status strong{font-size:17px}.aihl-ai-export-status span{margin-top:5px;color:#50645d;line-height:1.45}.aihl-ai-export-status.needs-attention{border-color:#f2cf82;background:#fff8e7}.aihl-ai-export-status.needs-attention i{color:#b26a00}
.aihl-ai-export-section-head{margin-bottom:18px}.aihl-ai-export-section-head h2{margin:5px 0 0;font-size:22px}.aihl-ai-export-steps{display:grid;grid-template-columns:minmax(0,1fr) 36px minmax(0,1fr) 36px minmax(0,1fr);align-items:center;border-block:1px solid #dcdcde}.aihl-ai-export-steps article{position:relative;min-height:190px;padding:26px 22px}.aihl-ai-export-steps article>b{position:absolute;top:18px;right:18px;color:#a7aaad;font-size:24px}.aihl-ai-step-icon{display:flex;width:48px;height:48px;align-items:center;justify-content:center;margin-bottom:18px;border-radius:6px;color:#fff;font-size:20px}.step-export{background:#008a67}.step-chat{background:#3157d5}.step-build{background:#c23678}.aihl-ai-export-steps h3{margin:0 0 7px;font-size:18px}.aihl-ai-export-steps p{margin:0;color:#646970;font-size:14px;line-height:1.5}.aihl-ai-step-arrow{color:#a7aaad;text-align:center}
.aihl-ai-export-section-head>p{max-width:780px;margin:8px 0 0;color:#646970;font-size:15px;line-height:1.5}.aihl-ai-prompt-library{padding:26px;border:1px solid #dcdcde;background:#fff}.aihl-ai-prompt-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.aihl-ai-prompt-grid>div{display:flex}.aihl-ai-prompt-choice{display:flex;width:100%;min-height:92px;align-items:flex-start;gap:12px;padding:16px;border:1px solid #c3c4c7;background:#fff;color:#1d2327;text-align:left;cursor:pointer}.aihl-ai-prompt-choice>i{width:22px;margin-top:3px;color:#3157d5;font-size:18px;text-align:center}.aihl-ai-prompt-choice span{display:flex;min-width:0;flex-direction:column;gap:5px}.aihl-ai-prompt-choice strong{font-size:14px}.aihl-ai-prompt-choice small{color:#646970;font-size:13px;line-height:1.4}.aihl-ai-prompt-choice:hover,.aihl-ai-prompt-choice:focus,.aihl-ai-prompt-choice.is-selected{border-color:#3157d5;background:#f2f6ff;box-shadow:inset 3px 0 #3157d5}.aihl-ai-prompt-editor{margin-top:18px;padding:20px;background:#f6f7f7}.aihl-ai-prompt-editor label{display:block;margin-bottom:8px;font-weight:700}.aihl-ai-prompt-editor textarea{box-sizing:border-box;width:100%;min-height:170px;padding:14px;border-color:#8c8f94;background:#fff;color:#1d2327;font-family:inherit;font-size:14px;line-height:1.55;resize:vertical}.aihl-ai-prompt-editor>div{display:flex;align-items:center;gap:16px;margin-top:12px}.aihl-ai-prompt-editor .button{display:inline-flex;align-items:center;gap:8px}
.aihl-ai-export-details{display:grid;grid-template-columns:1fr 1fr;border:1px solid #dcdcde}.aihl-ai-export-details>div{padding:24px}.aihl-ai-export-details>div+div{border-left:1px solid #dcdcde}.aihl-ai-detail-icon{display:flex;width:42px;height:42px;align-items:center;justify-content:center;float:left;margin:0 14px 24px 0;border-radius:6px;background:#e8eefc;color:#3157d5;font-size:18px}.aihl-ai-detail-icon.is-safe{background:#e3f5ed;color:#008a67}.aihl-ai-export-details h2{margin:0 0 7px;font-size:17px}.aihl-ai-export-details p{margin:0;color:#646970;line-height:1.55}
.aihl-ai-export-studio{display:flex;align-items:center;justify-content:space-between;gap:28px;padding:24px 26px;border:1px solid #ead6e2;background:#fff6fb}.aihl-ai-export-studio span{color:#9b2c68;font-size:12px;font-weight:700;text-transform:uppercase}.aihl-ai-export-studio h2{margin:4px 0 6px;font-size:20px}.aihl-ai-export-studio p{margin:0;color:#646970}.aihl-ai-export-studio .button{display:inline-flex;flex-shrink:0;align-items:center;gap:8px}.aihl-ai-copy-feedback{min-height:20px;margin:0;color:#008a67;font-weight:600}
@media(max-width:900px){.aihl-ai-export-hero{grid-template-columns:1fr}.aihl-ai-export-status{min-height:0}.aihl-ai-prompt-info-grid{grid-template-columns:1fr 1fr}.aihl-ai-prompt-info-grid>div{border-bottom:1px solid #dcdcde}.aihl-ai-export-steps{grid-template-columns:1fr}.aihl-ai-step-arrow{display:none}.aihl-ai-export-steps article+article{border-top:1px solid #dcdcde}.aihl-ai-prompt-grid{grid-template-columns:1fr 1fr}.aihl-ai-export-details{grid-template-columns:1fr}.aihl-ai-export-details>div+div{border-top:1px solid #dcdcde;border-left:0}.aihl-ai-export-studio{align-items:flex-start;flex-direction:column}.aihl-ai-export-hero h2{font-size:25px}}@media(max-width:600px){.aihl-ai-prompt-info-grid,.aihl-ai-prompt-grid{grid-template-columns:1fr}.aihl-ai-prompt-editor>div{align-items:flex-start;flex-direction:column}}
CSS;
	wp_add_inline_style('smart-admin-fa', $css);
});
