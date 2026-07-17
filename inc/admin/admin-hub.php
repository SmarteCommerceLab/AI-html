<?php
/**
 * AI-HTML Admin Hub
 *
 * Registra il menu top-level "AI-HTML" e raccoglie tutte le sottopagine
 * del tema sotto un unico punto di ingresso con template e stile unificati.
 *
 * Ogni sottopagina continua a usare la propria funzione di render,
 * ma viene wrappata dal template comune aihl_admin_page_template().
 *
 * @since 1.1.2
 */
if (!defined('ABSPATH')) {
	exit;
}

/* ──────────────────────────────────────────────
   1. Registra menu top-level + sottopagine
   ────────────────────────────────────────────── */

add_action('admin_menu', function () {

	$icon_svg = 'data:image/svg+xml;base64,' . base64_encode(
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="black">'
		. '<path d="M10 2a3 3 0 0 0-3 3v1H5a2 2 0 0 0-2 2v7a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V8a2 2 0 0 0-2-2h-2V5a3 3 0 0 0-3-3zM8.5 5a1.5 1.5 0 1 1 3 0v1h-3V5zM7 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zm4 0a1 1 0 1 1 2 0 1 1 0 0 1-2 0z"/>'
		. '</svg>'
	);

	// Dashboard (pagina principale)
	add_menu_page(
		AIHL_THEME_NAME,
		AIHL_THEME_NAME,
		'edit_theme_options',
		'aihl-dashboard',
		'aihl_render_admin_dashboard',
		$icon_svg,
		59
	);

	// Sottopagine — ordine logico
	$subpages = aihl_admin_get_subpages();
	foreach ($subpages as $subpage) {
		add_submenu_page(
			'aihl-dashboard',
			$subpage['page_title'],
			$subpage['menu_title'],
			$subpage['capability'],
			$subpage['slug'],
			$subpage['callback']
		);
	}

	// Rinomina la prima voce da "AI-HTML" a "Dashboard"
	global $submenu;
	if (isset($submenu['aihl-dashboard'])) {
		$submenu['aihl-dashboard'][0][0] = __('Dashboard', AIHL_TEXT_DOMAIN);
	}
}, 5);

/* ──────────────────────────────────────────────
   1b. Cattura notices globali sulle pagine AIHL
       Output buffering cattura i notices WordPress,
       li salva in globale e li ri-renderizza dentro
       il pannello admin hub con stile smart-admin-notice.
   ────────────────────────────────────────────── */

global $aihl_captured_notices;
$aihl_captured_notices = '';

add_action('admin_notices', function () {
	$screen = get_current_screen();
	if (!$screen || strpos($screen->id, 'aihl-') === false) {
		return;
	}
	ob_start();
}, -9999);

add_action('admin_notices', function () {
	global $aihl_captured_notices;
	$screen = get_current_screen();
	if (!$screen || strpos($screen->id, 'aihl-') === false) {
		return;
	}
	$aihl_captured_notices = ob_get_clean();
}, 999999);

// Nascondi anche all_admin_notices (WP 6.4+)
add_action('all_admin_notices', function () {
	$screen = get_current_screen();
	if (!$screen || strpos($screen->id, 'aihl-') === false) {
		return;
	}
	ob_start();
}, -9999);

add_action('all_admin_notices', function () {
	global $aihl_captured_notices;
	$screen = get_current_screen();
	if (!$screen || strpos($screen->id, 'aihl-') === false) {
		return;
	}
	$aihl_captured_notices .= ob_get_clean();
}, 999999);

/* ──────────────────────────────────────────────
   2. Registry sottopagine
   ────────────────────────────────────────────── */

if (!function_exists('aihl_admin_get_subpages')) {
	function aihl_admin_get_subpages() {
		return array(
			array(
				'slug'        => 'aihl-plugins',
				'page_title'  => __('Plugin Dipendenze', AIHL_TEXT_DOMAIN),
				'menu_title'  => __('Plugin', AIHL_TEXT_DOMAIN),
				'capability'  => 'manage_options',
				'callback'    => 'aihl_admin_wrap_plugins',
				'icon'        => 'fa-solid fa-plug',
				'description' => __('Stato dei plugin richiesti e raccomandati.', AIHL_TEXT_DOMAIN),
			),
			array(
				'slug'        => 'aihl-menu-json',
				'page_title'  => __('Menu JSON', AIHL_TEXT_DOMAIN),
				'menu_title'  => __('Menu JSON', AIHL_TEXT_DOMAIN),
				'capability'  => 'edit_theme_options',
				'callback'    => 'aihl_admin_wrap_menu_json',
				'icon'        => 'fa-solid fa-bars',
				'description' => __('Esporta, importa e gestisci menu con JSON.', AIHL_TEXT_DOMAIN),
			),
			array(
				'slug'        => 'aihl-menu-help',
				'page_title'  => __('Rich Menu Guida', AIHL_TEXT_DOMAIN),
				'menu_title'  => __('Rich Menu Guida', AIHL_TEXT_DOMAIN),
				'capability'  => 'edit_theme_options',
				'callback'    => 'aihl_admin_wrap_menu_help',
				'icon'        => 'fa-solid fa-circle-question',
				'description' => __('Guida rapida alla configurazione dei rich mega menu.', AIHL_TEXT_DOMAIN),
			),
			array(
				'slug'        => 'aihl-options-json',
				'page_title'  => __('Opzioni JSON', AIHL_TEXT_DOMAIN),
				'menu_title'  => __('Opzioni JSON', AIHL_TEXT_DOMAIN),
				'capability'  => 'edit_theme_options',
				'callback'    => 'aihl_admin_wrap_options_json',
				'icon'        => 'fa-solid fa-sliders',
				'description' => __('Editor JSON per opzioni tema (header, footer, contatti).', AIHL_TEXT_DOMAIN),
			),
			array(
				'slug'        => 'aihl-smart-reset',
				'page_title'  => __('Smart Reset', AIHL_TEXT_DOMAIN),
				'menu_title'  => __('Smart Reset', AIHL_TEXT_DOMAIN),
				'capability'  => 'manage_options',
				'callback'    => 'aihl_admin_wrap_smart_reset',
				'icon'        => 'fa-solid fa-rotate-left',
				'description' => __('Reset selettivo e autonomo delle sole impostazioni governate dal tema AI-HTML.', AIHL_TEXT_DOMAIN),
			),
			array(
				'slug'        => 'aihl-compliance',
				'page_title'  => __('Compliance 2026', AIHL_TEXT_DOMAIN),
				'menu_title'  => __('Compliance', AIHL_TEXT_DOMAIN),
				'capability'  => 'edit_theme_options',
				'callback'    => 'aihl_admin_wrap_compliance',
				'icon'        => 'fa-solid fa-shield-halved',
				'description' => __('Audit compliance Google/AI 2026.', AIHL_TEXT_DOMAIN),
			),
			array(
				'slug'        => 'aihl-deploy',
				'page_title'  => __('Progetti Demo', AIHL_TEXT_DOMAIN),
				'menu_title'  => __('Progetti Demo', AIHL_TEXT_DOMAIN),
				'capability'  => 'manage_options',
				'callback'    => 'aihl_admin_wrap_deploy',
				'icon'        => 'fa-solid fa-rocket',
				'description' => __('Deploy one-click di progetti demo e produzione.', AIHL_TEXT_DOMAIN),
			),
			array(
				'slug'        => 'aihl-code-slots',
				'page_title'  => __('Code Slots', AIHL_TEXT_DOMAIN),
				'menu_title'  => __('Code Slots', AIHL_TEXT_DOMAIN),
				'capability'  => 'manage_options',
				'callback'    => 'aihl_admin_wrap_code_slots',
				'icon'        => 'fa-solid fa-code',
				'description' => __('Inietta codice HTML/CSS/JS libero nei punti chiave del tema.', AIHL_TEXT_DOMAIN),
			),
			array(
				'slug'        => 'aihl-api-keys',
				'page_title'  => __('API Keys', AIHL_TEXT_DOMAIN),
				'menu_title'  => __('API Keys', AIHL_TEXT_DOMAIN),
				'capability'  => 'manage_options',
				'callback'    => 'aihl_admin_wrap_api_keys',
				'icon'        => 'fa-solid fa-key',
				'description' => __('Gestione chiavi API per agenti AI (Claude, GPT, custom).', AIHL_TEXT_DOMAIN),
			),
			array(
				'slug'        => 'aihl-api-docs',
				'page_title'  => __('Swagger/OpenAPI', AIHL_TEXT_DOMAIN),
				'menu_title'  => __('Swagger', AIHL_TEXT_DOMAIN),
				'capability'  => 'manage_options',
				'callback'    => 'aihl_admin_wrap_api_docs',
				'icon'        => 'fa-solid fa-book-open',
				'description' => __('Consulta la specifica OpenAPI generata automaticamente dalle route REST del tema.', AIHL_TEXT_DOMAIN),
			),
		);
	}
}

/* ──────────────────────────────────────────────
   4. Template wrapper comune
   ────────────────────────────────────────────── */

if (!function_exists('aihl_admin_page_template')) {
	function aihl_admin_page_template($page_title, $page_description, $inner_callback) {
		if (!current_user_can('edit_theme_options')) {
			return;
		}

		$current_slug = isset($_GET['page']) ? sanitize_key((string) $_GET['page']) : '';
		$subpages = aihl_admin_get_subpages();
		$all_pages = array_merge(array(array(
			'slug' => 'aihl-dashboard',
			'menu_title' => __('Dashboard', AIHL_TEXT_DOMAIN),
			'description' => __('Stato del tema e accessi rapidi.', AIHL_TEXT_DOMAIN),
			'icon' => 'fa-solid fa-gauge-high',
		)), $subpages);

		$header_bg = '#1d2327';
		global $_wp_admin_css_colors;
		$scheme = get_user_option( 'admin_color' ) ?: 'fresh';
		if ( ! empty( $_wp_admin_css_colors[ $scheme ]->colors[0] ) ) {
			$header_bg = sanitize_hex_color( $_wp_admin_css_colors[ $scheme ]->colors[0] );
		}
		?>
		<div class="smart-admin-wrap">
			<div class="smart-admin-header" style="background:<?php echo esc_attr( $header_bg ); ?>">
				<div class="smart-admin-header-brand">
					<div class="smart-admin-logo">
						<svg class="smart-admin-logo-img" width="28" height="28" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
							<defs><linearGradient id="se-grad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#e91e8c"/><stop offset="100%" style="stop-color:#ff6ec7"/></linearGradient></defs>
							<rect width="100" height="100" rx="20" fill="url(#se-grad)"/>
							<text x="50" y="68" text-anchor="middle" fill="#fff" font-family="Arial,sans-serif" font-weight="700" font-size="52">Se</text>
						</svg>
						<span><strong><?php echo esc_html(AIHL_THEME_NAME); ?></strong><small><?php esc_html_e('AI-ready theme control panel', AIHL_TEXT_DOMAIN); ?></small></span>
					</div>
					<div class="smart-admin-header-actions"><a href="<?php echo esc_url(admin_url('customize.php')); ?>" class="smart-admin-top-action"><i class="fa-solid fa-palette" aria-hidden="true"></i><?php esc_html_e('Personalizza', AIHL_TEXT_DOMAIN); ?></a><span class="smart-admin-version">v<?php echo esc_html(AIHL_VERSION); ?></span></div>
				</div>
			</div>

			<div class="smart-admin-shell">
				<aside class="smart-admin-sidebar" aria-label="<?php esc_attr_e('Navigazione AI-HTML', AIHL_TEXT_DOMAIN); ?>">
					<div class="smart-admin-sidebar-title"><?php esc_html_e('Pannello', AIHL_TEXT_DOMAIN); ?></div>
					<nav class="smart-admin-nav">
						<?php foreach ($all_pages as $sp) : ?>
							<a href="<?php echo esc_url(admin_url('admin.php?page=' . $sp['slug'])); ?>" class="smart-admin-nav-item<?php echo $current_slug === $sp['slug'] ? ' smart-admin-nav-item-active' : ''; ?>">
								<span class="smart-admin-nav-icon"><i class="<?php echo esc_attr($sp['icon']); ?>" aria-hidden="true"></i></span>
								<span class="smart-admin-nav-copy"><strong><?php echo esc_html($sp['menu_title']); ?></strong><small><?php echo esc_html($sp['description']); ?></small></span>
							</a>
						<?php endforeach; ?>
					</nav>
				</aside>
				<main class="smart-admin-main">
					<div class="smart-admin-pathbar"><a href="<?php echo esc_url(admin_url('admin.php?page=aihl-dashboard')); ?>"><?php echo esc_html(AIHL_THEME_NAME); ?></a><i class="fa-solid fa-chevron-right" aria-hidden="true"></i><span><?php echo esc_html($page_title); ?></span></div>
					<div class="smart-admin-body">
						<div class="smart-admin-page-header"><h1><?php echo esc_html($page_title); ?></h1><?php if ($page_description !== '') : ?><p class="smart-admin-page-desc"><?php echo esc_html($page_description); ?></p><?php endif; ?></div>
						<?php global $aihl_captured_notices; if (!empty($aihl_captured_notices) && trim($aihl_captured_notices) !== '') : ?><div class="smart-admin-notices"><?php echo $aihl_captured_notices; // phpcs:ignore WordPress.Security.EscapeOutput -- WP core notices already escaped ?></div><?php endif; ?>
						<div class="smart-admin-content"><?php call_user_func($inner_callback); ?></div>
					</div>
				</main>
			</div>

			<div class="smart-admin-footer">
				<span><?php echo esc_html(AIHL_THEME_NAME); ?> v<?php echo esc_html(AIHL_VERSION); ?></span>
				<span>&middot;</span>
				<a href="https://smartecommerce.it" target="_blank" rel="noopener">Smart eCommerce srls</a>
			</div>
		</div>
		<?php
	}
}

/* ──────────────────────────────────────────────
   5. Dashboard principale
   ────────────────────────────────────────────── */

if (!function_exists('aihl_render_admin_dashboard')) {
	function aihl_render_admin_dashboard() {
		aihl_admin_page_template(
			__('Dashboard', AIHL_TEXT_DOMAIN),
			__('Panoramica del tema e accesso rapido a tutte le funzionalita.', AIHL_TEXT_DOMAIN),
			'aihl_render_dashboard_content'
		);
	}
}

if (!function_exists('aihl_render_dashboard_content')) {
	function aihl_render_dashboard_content() {
		$subpages = aihl_admin_get_subpages();

		if (function_exists('aihl_render_plugin_dependency_summary')) {
			aihl_render_plugin_dependency_summary();
		}

		// Info cards
		$info = array(
			array('label' => __('Versione', AIHL_TEXT_DOMAIN), 'value' => AIHL_VERSION, 'icon' => 'fa-solid fa-code-branch'),
			array('label' => __('Sorgente Header', AIHL_TEXT_DOMAIN), 'value' => function_exists('aihl_get_structure_render_mode') ? aihl_get_structure_render_mode('header') : 'native', 'icon' => 'fa-solid fa-window-maximize'),
			array('label' => __('Sorgente Footer', AIHL_TEXT_DOMAIN), 'value' => function_exists('aihl_get_structure_render_mode') ? aihl_get_structure_render_mode('footer') : 'native', 'icon' => 'fa-solid fa-window-minimize'),
			array('label' => __('Smart Bootstrap', AIHL_TEXT_DOMAIN), 'value' => (function_exists('aihl_is_bootstrap_manager_active') && aihl_is_bootstrap_manager_active()) ? 'Active' : 'Inactive', 'icon' => 'fa-solid fa-palette'),
			array('label' => __('Smart Builder Site', AIHL_TEXT_DOMAIN), 'value' => aihtml_is_site_builder_active() ? 'Active' : 'Inactive', 'icon' => 'fa-solid fa-cubes'),
			array('label' => __('Search Style', AIHL_TEXT_DOMAIN), 'value' => aihtml_option_value('header_search_style', 'icon-dropdown'), 'icon' => 'fa-solid fa-search'),
		);
		?>
		<div class="smart-dash-stats">
			<?php foreach ($info as $item) : ?>
				<div class="smart-dash-stat-card">
					<div class="smart-dash-stat-icon"><i class="<?php echo esc_attr($item['icon']); ?>"></i></div>
					<div class="smart-dash-stat-text">
						<span class="smart-dash-stat-label"><?php echo esc_html($item['label']); ?></span>
						<span class="smart-dash-stat-value"><?php echo esc_html($item['value']); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<h2 class="smart-dash-section-title"><?php esc_html_e('Strumenti', AIHL_TEXT_DOMAIN); ?></h2>
		<div class="smart-dash-cards">
			<?php foreach ($subpages as $sp) : ?>
				<a class="smart-dash-card" href="<?php echo esc_url(admin_url('admin.php?page=' . $sp['slug'])); ?>">
					<div class="smart-dash-card-icon"><i class="<?php echo esc_attr($sp['icon']); ?>"></i></div>
					<div class="smart-dash-card-body">
						<strong><?php echo esc_html($sp['menu_title']); ?></strong>
						<span><?php echo esc_html($sp['description']); ?></span>
					</div>
				</a>
			<?php endforeach; ?>
			<a class="smart-dash-card" href="<?php echo esc_url(admin_url('customize.php')); ?>">
				<div class="smart-dash-card-icon"><i class="fa-solid fa-palette"></i></div>
				<div class="smart-dash-card-body">
					<strong><?php esc_html_e('Personalizza', AIHL_TEXT_DOMAIN); ?></strong>
					<span><?php esc_html_e('Apri il Customizer WordPress per configurare header, footer e opzioni.', AIHL_TEXT_DOMAIN); ?></span>
				</div>
			</a>
		</div>
		<?php
	}
}

/* ──────────────────────────────────────────────
   6. Wrapper per le sottopagine esistenti
   ────────────────────────────────────────────── */

function aihl_admin_wrap_plugins() {
	aihl_admin_page_template(
		__('Plugin Dipendenze', AIHL_TEXT_DOMAIN),
		__('Stato dei plugin richiesti e raccomandati dal tema.', AIHL_TEXT_DOMAIN),
		function () {
			if (function_exists('aihl_render_plugins_page')) {
				aihl_render_plugins_page();
			}
		}
	);
}

function aihl_admin_wrap_menu_json() {
	aihl_admin_page_template(
		__('Menu JSON', AIHL_TEXT_DOMAIN),
		__('Esporta, importa e gestisci i menu WordPress con tutti i campi AIHL.', AIHL_TEXT_DOMAIN),
		function () {
			if (function_exists('aihl_render_menu_json_page')) {
				aihl_render_menu_json_page();
			}
		}
	);
}

function aihl_admin_wrap_menu_help() {
	aihl_admin_page_template(
		__('Rich Menu Guida', AIHL_TEXT_DOMAIN),
		__('Guida rapida alla configurazione dei rich mega menu.', AIHL_TEXT_DOMAIN),
		function () {
			if (function_exists('aihl_render_rich_menu_help_page')) {
				aihl_render_rich_menu_help_page();
			}
		}
	);
}

function aihl_admin_wrap_options_json() {
	aihl_admin_page_template(
		__('Opzioni JSON', AIHL_TEXT_DOMAIN),
		__('Editor JSON per le opzioni tema.', AIHL_TEXT_DOMAIN),
		function () {
			if (function_exists('aihl_render_options_json_page')) {
				aihl_render_options_json_page();
			}
		}
	);
}

function aihl_admin_wrap_smart_reset() {
	aihl_admin_page_template(
		__('Smart Reset', AIHL_TEXT_DOMAIN),
		__('Reset selettivo del tema con registry locale, dry-run e snapshot preventivo.', AIHL_TEXT_DOMAIN),
		function () {
			if (function_exists('aihl_render_smart_reset_page')) {
				aihl_render_smart_reset_page();
			}
		}
	);
}

function aihl_admin_wrap_compliance() {
	aihl_admin_page_template(
		__('Compliance 2026', AIHL_TEXT_DOMAIN),
		__('Audit compliance Google e AI 2026.', AIHL_TEXT_DOMAIN),
		function () {
			if (function_exists('aihl_render_compliance_2026_page')) {
				aihl_render_compliance_2026_page();
			}
		}
	);
}

function aihl_admin_wrap_deploy() {
	aihl_admin_page_template(
		__('Progetti Demo', AIHL_TEXT_DOMAIN),
		__('Deploy one-click di progetti demo e produzione.', AIHL_TEXT_DOMAIN),
		function () {
			if (function_exists('aihl_render_deploy_projects_page')) {
				aihl_render_deploy_projects_page();
			}
		}
	);
}

function aihl_admin_wrap_code_slots() {
	aihl_admin_page_template(
		__('Code Slots', AIHL_TEXT_DOMAIN),
		__('Inietta codice HTML/CSS/JS libero nei punti chiave del tema via admin, API o JSON.', AIHL_TEXT_DOMAIN),
		function () {
			if (function_exists('aihl_render_code_slots_page')) {
				aihl_render_code_slots_page();
			}
		}
	);
}

function aihl_admin_wrap_api_keys() {
	aihl_admin_page_template(
		__('API Keys', AIHL_TEXT_DOMAIN),
		__('Gestione chiavi API per agenti AI. Una sola chiave autentica tutte le API Smart attive sul sito.', AIHL_TEXT_DOMAIN),
		function () {
			if (function_exists('smart_ai_render_keys_page_content')) {
				smart_ai_render_keys_page_content();
			}
		}
	);
}

/* ──────────────────────────────────────────────
   7. CSS Admin
   ────────────────────────────────────────────── */

function aihl_admin_wrap_api_docs() {
	aihl_admin_page_template(
		__('Swagger/OpenAPI', AIHL_TEXT_DOMAIN),
		__('Documentazione OpenAPI generata dal tema per agenti AI, consumer e strumenti di integrazione.', AIHL_TEXT_DOMAIN),
		'aihl_render_api_docs_content'
	);
}

function aihl_render_api_docs_content() {
	if (!current_user_can('manage_options')) {
		wp_die(esc_html__('Non hai i permessi per accedere a questa pagina.', AIHL_TEXT_DOMAIN));
	}

	$openapi = function_exists('aihl_ai_openapi_payload') ? aihl_ai_openapi_payload() : array();
	$openapi_json = wp_json_encode($openapi, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	$openapi_url = rest_url('aihtml/v1/ai/openapi');
	$schema = isset($openapi['components']['schemas']['AIHLOptionsPayload']['properties']['options']['properties']) && is_array($openapi['components']['schemas']['AIHLOptionsPayload']['properties']['options']['properties'])
		? $openapi['components']['schemas']['AIHLOptionsPayload']['properties']['options']['properties']
		: array();
	$paths = isset($openapi['paths']) && is_array($openapi['paths']) ? $openapi['paths'] : array();
	?>
	<div class="aihl-api-docs-grid">
		<section class="aihl-json-panel aihl-api-docs-summary">
			<h2><?php echo esc_html__('Specifica generata', AIHL_TEXT_DOMAIN); ?></h2>
			<div class="smart-dash-stats">
				<div class="smart-dash-stat-card">
					<div class="smart-dash-stat-icon"><i class="fa-solid fa-code-branch" aria-hidden="true"></i></div>
					<div class="smart-dash-stat-text">
						<span class="smart-dash-stat-label"><?php echo esc_html__('Versione', AIHL_TEXT_DOMAIN); ?></span>
						<span class="smart-dash-stat-value"><?php echo esc_html(AIHL_VERSION); ?></span>
					</div>
				</div>
				<div class="smart-dash-stat-card">
					<div class="smart-dash-stat-icon"><i class="fa-solid fa-route" aria-hidden="true"></i></div>
					<div class="smart-dash-stat-text">
						<span class="smart-dash-stat-label"><?php echo esc_html__('Path REST', AIHL_TEXT_DOMAIN); ?></span>
						<span class="smart-dash-stat-value"><?php echo esc_html((string) count($paths)); ?></span>
					</div>
				</div>
				<div class="smart-dash-stat-card">
					<div class="smart-dash-stat-icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></div>
					<div class="smart-dash-stat-text">
						<span class="smart-dash-stat-label"><?php echo esc_html__('Campi opzione', AIHL_TEXT_DOMAIN); ?></span>
						<span class="smart-dash-stat-value"><?php echo esc_html((string) count($schema)); ?></span>
					</div>
				</div>
			</div>
			<p>
				<a class="button button-primary" href="<?php echo esc_url($openapi_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__('Apri OpenAPI JSON', AIHL_TEXT_DOMAIN); ?></a>
				<a class="button" href="https://editor.swagger.io/" target="_blank" rel="noopener noreferrer"><?php echo esc_html__('Apri Swagger Editor', AIHL_TEXT_DOMAIN); ?></a>
			</p>
			<p class="description"><?php echo esc_html__('La specifica viene generata runtime dalle route REST registrate da WordPress e dalla whitelist opzioni AI-HTML. Non viene salvata come file statico.', AIHL_TEXT_DOMAIN); ?></p>
		</section>

		<section class="aihl-json-panel">
			<h2><?php echo esc_html__('Endpoint rilevati', AIHL_TEXT_DOMAIN); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php echo esc_html__('Metodo', AIHL_TEXT_DOMAIN); ?></th>
						<th><?php echo esc_html__('Path', AIHL_TEXT_DOMAIN); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($paths as $path => $operations) : ?>
						<?php foreach ($operations as $method => $operation) : ?>
							<tr>
								<td><code><?php echo esc_html(strtoupper((string) $method)); ?></code></td>
								<td><code><?php echo esc_html((string) $path); ?></code></td>
							</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
		</section>

		<section class="aihl-json-panel aihl-json-panel-wide">
			<h2><?php echo esc_html__('OpenAPI JSON', AIHL_TEXT_DOMAIN); ?></h2>
			<textarea class="aihl-json-editor aihl-openapi-editor" readonly spellcheck="false"><?php echo esc_textarea((string) $openapi_json); ?></textarea>
		</section>
	</div>
	<?php
}

add_action('admin_enqueue_scripts', function ($hook) {
	if (strpos($hook, 'aihl-') === false) {
		return;
	}

	$css = <<<'CSS'
/* ── AI-HTML Admin Hub ── */
.smart-admin-wrap{margin:20px 20px 40px 2px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif}
.smart-admin-header{overflow:hidden;border:1px solid rgba(0,0,0,.08);border-bottom:0}
.smart-admin-header-brand{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 18px}
.smart-admin-logo{display:flex;align-items:center;gap:8px;color:#fff;font-size:15px;text-decoration:none}
.smart-admin-logo strong{display:block}.smart-admin-logo small{display:block;margin-top:1px;color:rgba(255,255,255,.68);font-size:11px;text-transform:uppercase}
.smart-admin-logo-img{max-height:28px;width:auto;flex-shrink:0}
.smart-admin-header-actions{display:flex;align-items:center;gap:10px}.smart-admin-top-action{display:inline-flex;align-items:center;gap:7px;padding:7px 11px;border:1px solid rgba(255,255,255,.24);border-radius:4px;color:#fff;text-decoration:none;font-size:12.5px;font-weight:600;background:rgba(255,255,255,.08)}.smart-admin-top-action:hover{color:#fff;background:rgba(255,255,255,.14)}.smart-admin-version{font-size:11px;color:rgba(255,255,255,.72);background:rgba(255,255,255,.1);padding:3px 8px;border-radius:4px}
.smart-admin-shell{display:grid;grid-template-columns:260px minmax(0,1fr);background:#f3f5f7;border:1px solid #dcdcde;border-top:0;min-height:520px}.smart-admin-sidebar{background:#fff;border-right:1px solid #dcdcde;padding:16px 0}.smart-admin-sidebar-title{padding:0 18px 10px;color:#646970;font-size:11px;font-weight:700;text-transform:uppercase}.smart-admin-nav{display:flex;flex-direction:column}.smart-admin-nav-item{display:flex;gap:11px;padding:11px 14px 11px 18px;border-left:3px solid transparent;color:#1d2327;text-decoration:none}.smart-admin-nav-item:hover{background:#f6f7f7;color:#135e96}.smart-admin-nav-item-active{background:#eef6fc;border-left-color:#2271b1;color:#135e96}.smart-admin-nav-icon{width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:4px;background:#f0f0f1;color:#646970;flex-shrink:0}.smart-admin-nav-item-active .smart-admin-nav-icon{background:#2271b1;color:#fff}.smart-admin-nav-copy{display:flex;min-width:0;flex-direction:column;gap:2px}.smart-admin-nav-copy strong{font-size:13px}.smart-admin-nav-copy small{color:#646970;font-size:11.5px;line-height:1.35}.smart-admin-main{min-width:0;padding:18px}.smart-admin-pathbar{display:flex;align-items:center;gap:8px;margin:0 0 10px;color:#646970;font-size:12px}.smart-admin-pathbar a{color:#2271b1;text-decoration:none}.smart-admin-pathbar i{font-size:10px;color:#8c8f94}.smart-admin-body{background:#fff;border:1px solid #dcdcde;padding:24px 28px 30px;min-height:400px}
.smart-admin-page-header{margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid #f0f0f1}
.smart-admin-page-header h1{font-size:22px;font-weight:600;margin:0 0 4px;color:#1d2327}
.smart-admin-page-desc{color:#646970;margin:0;font-size:14px}
.smart-admin-content .wrap{margin:0;padding:0}.smart-admin-content .wrap>h1:first-child,.smart-admin-content .wrap>h2:first-child{display:none}

/* ── Notices catturati dentro il pannello ── */
.smart-admin-notices{margin-bottom:20px}
.smart-admin-notices:empty{display:none}
.smart-admin-notices .notice{margin:0 0 12px;border-radius:8px;border-left-width:4px;padding:12px 16px;box-shadow:none;font-size:13px}
.smart-admin-notices .notice p{margin:.4em 0}
.smart-admin-notices .notice-error{background:#fef2f2;border-color:#dc3545;color:#991b1b}
.smart-admin-notices .notice-warning{background:#fffbeb;border-color:#f59e0b;color:#92400e}
.smart-admin-notices .notice-success{background:#f0fdf4;border-color:#16a34a;color:#166534}
.smart-admin-notices .notice-info{background:#eff6ff;border-color:#2271b1;color:#1e40af}
.smart-admin-notices .notice .button{border-radius:6px;font-size:13px;margin-top:4px}
.smart-admin-notices .notice code{background:rgba(0,0,0,.06);padding:2px 6px;border-radius:4px;font-size:12px}
.smart-admin-notices .notice ul{margin:6px 0 6px 20px;list-style:disc}
.smart-dependency-summary{margin:0 0 24px;padding:22px;border:1px solid #dcdcde;border-radius:12px;background:#fff;box-shadow:0 8px 24px rgba(29,35,39,.06)}
.smart-dependency-summary-heading{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:18px}
.smart-dependency-summary-heading h2{margin:3px 0 5px;font-size:20px;line-height:1.25}
.smart-dependency-summary-heading p{margin:0;color:#646970}
.smart-dependency-summary-heading .button{display:inline-flex;align-items:center;gap:6px;flex:0 0 auto}
.smart-dependency-summary-kicker{display:block;color:#646970;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
.smart-dependency-summary-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.smart-dependency-card{padding:16px 18px;border:1px solid #dcdcde;border-left-width:4px;border-radius:9px;background:#f6f7f7}
.smart-dependency-card-required{border-left-color:#d63638}
.smart-dependency-card-recommended{border-left-color:#dba617}
.smart-dependency-card-title{display:flex;align-items:center;gap:8px;margin-bottom:7px}
.smart-dependency-card-required .smart-dependency-card-title .dashicons{color:#d63638}
.smart-dependency-card-recommended .smart-dependency-card-title .dashicons{color:#996800}
.smart-dependency-count{display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;margin-left:auto;padding:0 7px;border-radius:999px;background:#1d2327;color:#fff;font-size:12px;font-weight:700}
.smart-dependency-card p{margin:0 0 12px;color:#50575e}
.smart-dependency-list{display:flex;flex-wrap:wrap;gap:7px;margin:0;padding:0;list-style:none}
.smart-dependency-list li{margin:0;padding:5px 9px;border:1px solid #c3c4c7;border-radius:999px;background:#fff;color:#1d2327;font-size:12px;font-weight:600}
@media (max-width:782px){.smart-dependency-summary-heading{align-items:flex-start;flex-direction:column}.smart-dependency-summary-grid{grid-template-columns:1fr}}

.smart-admin-footer{background:#f6f7f7;border:1px solid #dcdcde;border-top:0;padding:12px 24px;display:flex;gap:8px;align-items:center;font-size:12px;color:#646970}
.smart-admin-footer a{color:#2271b1;text-decoration:none}

/* ── Dashboard ── */
.smart-dash-stats{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:28px}
.smart-dash-stat-card{display:flex;align-items:center;gap:12px;padding:14px 16px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:8px}
.smart-dash-stat-icon{width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:#2271b1;color:#fff;border-radius:8px;font-size:15px;flex-shrink:0}
.smart-dash-stat-label{display:block;font-size:11px;color:#646970;text-transform:uppercase;letter-spacing:.03em;font-weight:600}
.smart-dash-stat-value{display:block;font-size:14px;color:#1d2327;font-weight:600}
.smart-dash-section-title{font-size:16px;font-weight:600;margin:0 0 14px;color:#1d2327}
.smart-dash-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px}
.smart-dash-card{display:flex;align-items:flex-start;gap:14px;padding:16px 18px;background:#fff;border:1px solid #dcdcde;border-radius:8px;text-decoration:none;color:#1d2327;transition:border-color .15s,box-shadow .15s}
.smart-dash-card:hover{border-color:#2271b1;box-shadow:0 2px 8px rgba(34,113,177,.12);color:#1d2327}
.smart-dash-card-icon{width:40px;height:40px;display:flex;align-items:center;justify-content:center;background:rgba(34,113,177,.08);color:#2271b1;border-radius:8px;font-size:17px;flex-shrink:0}
.smart-dash-card-body{display:flex;flex-direction:column;gap:2px}
.smart-dash-card-body strong{font-size:14px}
.smart-dash-card-body span{font-size:12px;color:#646970;line-height:1.4}
.aihl-api-docs-grid{display:grid;grid-template-columns:minmax(0,.75fr) minmax(360px,.25fr);gap:18px}
.aihl-api-docs-summary{grid-column:1/-1}
.aihl-json-panel{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px}
.aihl-json-panel-wide{grid-column:1/-1}
.aihl-json-panel h2{margin-top:0}
.aihl-json-editor{width:100%;min-height:520px;font-family:Consolas,Monaco,monospace;font-size:12px;line-height:1.55;color:#101517;background:#f8fafc;border:1px solid #c3c4c7;border-radius:6px;padding:14px;tab-size:2}
.aihl-openapi-editor{min-height:680px}

@media(max-width:782px){
.smart-admin-header-brand{align-items:flex-start;flex-direction:column}
.smart-admin-header-actions{justify-content:flex-start}
.smart-admin-shell{grid-template-columns:1fr}
.smart-admin-sidebar{border-right:0;border-bottom:1px solid #dcdcde;padding:10px 0}
.smart-admin-nav{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr))}
.smart-admin-nav-item{border-left:0;border-bottom:3px solid transparent;padding:10px 12px}
.smart-admin-nav-item-active{border-bottom-color:#2271b1}
.smart-admin-nav-copy small{display:none}
.smart-admin-main{padding:12px}
.smart-admin-body{padding:16px}
}
@media(max-width:1100px){.aihl-api-docs-grid{grid-template-columns:1fr}.aihl-json-editor{min-height:360px}}
CSS;

	wp_add_inline_style('wp-admin', $css);

	// Font Awesome per le icone della navigazione e delle pagine.
	if (!wp_style_is('font-awesome-6.4.2', 'enqueued') && !wp_style_is('font-awesome-6', 'enqueued')) {
		if (defined('AIHL_DIR_URL')) {
			wp_enqueue_style('smart-admin-fa', AIHL_DIR_URL . '/resource/css/fontawesome/fontawesome.min.css', array(), AIHL_UNICODE);
			wp_enqueue_style('smart-admin-fa-solid', AIHL_DIR_URL . '/resource/css/fontawesome/solid.min.css', array('smart-admin-fa'), AIHL_UNICODE);
		}
	}
});
