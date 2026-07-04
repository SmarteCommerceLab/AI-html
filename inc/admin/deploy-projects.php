<?php
/**
 * AI-HTML Deploy Projects
 *
 * Sistema di deploy one-click per progetti demo e produzione.
 * Orchestrates: opzioni tema -> menu -> pagine -> builder SBS -> homepage.
 *
 * Formato project.json:
 * {
 *   "format": "aihl-project",
 *   "version": 1,
 *   "name": "Nome progetto",
 *   "description": "Descrizione",
 *   "options": { ... },          // opzioni tema (stesso formato theme-options.json)
 *   "menus": { ... },            // menu (stesso formato menus.json)
 *   "pages": [ ... ],            // pagine da creare
 *   "settings": { ... }          // homepage, blog page, ecc.
 * }
 *
 * @since 1.4.0
 */
if (!defined('ABSPATH')) {
	exit;
}

/* ============================================================================
 * 1. Deploy Engine — orchestra l'import di un progetto completo
 * ============================================================================ */

if (!function_exists('aihl_deploy_project')) {
	/**
	 * Esegue il deploy di un progetto completo.
	 *
	 * @param array $project Il progetto decodificato da JSON.
	 * @return array|WP_Error Risultato del deploy con dettagli per ogni step.
	 */
	function aihl_deploy_project(array $project) {
		$log = array(
			'steps'    => array(),
			'warnings' => array(),
			'errors'   => array(),
		);

		// Validazione formato
		if (empty($project['format']) || $project['format'] !== 'aihl-project') {
			return new WP_Error('invalid_format', __('Formato progetto non valido. Atteso "aihl-project".', AIHL_TEXT_DOMAIN));
		}

		$project_name = !empty($project['name']) ? sanitize_text_field($project['name']) : __('Senza nome', AIHL_TEXT_DOMAIN);

		// ── Step 1: Opzioni tema ──
		if (!empty($project['options']) && is_array($project['options'])) {
			$options_result = aihl_deploy_step_options($project['options']);
			$log['steps']['options'] = $options_result;
		} else {
			$log['steps']['options'] = array('skipped' => true, 'reason' => 'Nessuna opzione nel progetto.');
		}

		// ── Step 2: Menu ──
		if (!empty($project['menus']) && is_array($project['menus'])) {
			$menus_result = aihl_deploy_step_menus($project['menus']);
			$log['steps']['menus'] = $menus_result;
		} else {
			$log['steps']['menus'] = array('skipped' => true, 'reason' => 'Nessun menu nel progetto.');
		}

		// ── Step 3: Pagine ──
		$created_pages = array();
		if (!empty($project['pages']) && is_array($project['pages'])) {
			$pages_result = aihl_deploy_step_pages($project['pages']);
			$log['steps']['pages'] = $pages_result;
			$created_pages = $pages_result['created'] ?? array();
		} else {
			$log['steps']['pages'] = array('skipped' => true, 'reason' => 'Nessuna pagina nel progetto.');
		}

		// ── Step 4: Builder SBS (se disponibile) ──
		if (!empty($project['pages']) && is_array($project['pages'])) {
			$builder_result = aihl_deploy_step_builder($project['pages'], $created_pages);
			$log['steps']['builder'] = $builder_result;
		} else {
			$log['steps']['builder'] = array('skipped' => true, 'reason' => 'Nessuna pagina con builder.');
		}

		// ── Step 5: Settings (homepage, blog page) ──
		if (!empty($project['settings']) && is_array($project['settings'])) {
			$settings_result = aihl_deploy_step_settings($project['settings'], $created_pages);
			$log['steps']['settings'] = $settings_result;
		} else {
			$log['steps']['settings'] = array('skipped' => true, 'reason' => 'Nessuna impostazione nel progetto.');
		}

		// Componenti opzionali registrati da AI-HTML o plugin integrati,
		// ad esempio i Code Slots.
		$log['steps'] = apply_filters('aihl_deploy_extra_steps', $log['steps'], $project, $created_pages);

		$log['project_name'] = $project_name;
		$log['success'] = empty($log['errors']);

		return $log;
	}
}

/* ── Step 1: Opzioni tema ── */

if (!function_exists('aihl_deploy_step_options')) {
	function aihl_deploy_step_options(array $options) {
		if (!function_exists('aihl_ai_options_whitelist') || !function_exists('aihl_ai_sanitize_option_value')) {
			return array('error' => 'Funzioni opzioni API non disponibili.');
		}

		$whitelist = aihl_ai_options_whitelist();
		$current = get_option(AIHL_OPTION_BASE . '_general', array());
		if (!is_array($current)) {
			$current = array();
		}

		$applied = 0;
		$rejected = array();

		foreach ($options as $field => $value) {
			$field = sanitize_key($field);
			if (!isset($whitelist[$field])) {
				$rejected[] = $field;
				continue;
			}
			$clean = aihl_ai_sanitize_option_value($value, $whitelist[$field]);
			if (null !== $clean) {
				$current[$field] = $clean;
				$applied++;
			} else {
				$rejected[] = $field;
			}
		}

		update_option(AIHL_OPTION_BASE . '_general', $current, false);

		return array(
			'applied'  => $applied,
			'rejected' => $rejected,
		);
	}
}

/* ── Step 2: Menu ── */

if (!function_exists('aihl_deploy_step_menus')) {
	function aihl_deploy_step_menus(array $menus_data) {
		if (!function_exists('aihl_import_menu_json_payload')) {
			return array('error' => 'Funzioni import menu non disponibili.');
		}

		$json = wp_json_encode($menus_data);
		$result = aihl_import_menu_json_payload($json, true); // replace = true

		if (is_wp_error($result)) {
			return array('error' => $result->get_error_message());
		}

		return array(
			'menus_imported' => $result['menus'] ?? 0,
			'items_imported' => $result['items'] ?? 0,
			'failed_items'   => $result['failed_items'] ?? 0,
		);
	}
}

/* ── Step 3: Pagine ── */

if (!function_exists('aihl_deploy_step_pages')) {
	function aihl_deploy_step_pages(array $pages) {
		$created = array();
		$errors = array();

		foreach ($pages as $page_def) {
			$title = isset($page_def['title']) ? sanitize_text_field($page_def['title']) : '';
			if ('' === $title) {
				$errors[] = __('Pagina senza titolo saltata.', AIHL_TEXT_DOMAIN);
				continue;
			}

			$slug = isset($page_def['slug']) ? sanitize_title($page_def['slug']) : sanitize_title($title);
			$template = isset($page_def['template']) ? sanitize_text_field($page_def['template']) : '';
			$status = isset($page_def['status']) && in_array($page_def['status'], array('publish', 'draft'), true)
				? $page_def['status']
				: 'publish';
			$content = isset($page_def['content']) ? wp_kses_post($page_def['content']) : '';
			$excerpt = isset($page_def['excerpt']) ? sanitize_textarea_field($page_def['excerpt']) : '';

			// Controlla se esiste gia una pagina con questo slug
			$existing = get_page_by_path($slug);
			if ($existing) {
				// Aggiorna la pagina esistente
				wp_update_post(array(
					'ID'           => $existing->ID,
					'post_title'   => $title,
					'post_status'  => $status,
					'post_content' => $content,
					'post_excerpt' => $excerpt,
				));
				$page_id = $existing->ID;
			} else {
				$page_id = wp_insert_post(array(
					'post_type'    => 'page',
					'post_title'   => $title,
					'post_name'    => $slug,
					'post_status'  => $status,
					'post_content' => $content,
					'post_excerpt' => $excerpt,
				));
			}

			if (is_wp_error($page_id) || !$page_id) {
				$errors[] = sprintf(
					/* translators: %s: page title */
					__('Creazione pagina "%s" fallita.', AIHL_TEXT_DOMAIN),
					$title
				);
				continue;
			}

			// Assegna template
			if ('' !== $template && 'default' !== $template) {
				update_post_meta($page_id, '_wp_page_template', $template);
			}

			$created[] = array(
				'page_id'  => (int) $page_id,
				'title'    => $title,
				'slug'     => $slug,
				'template' => $template ?: 'default',
				'is_new'   => !$existing,
			);
		}

		return array(
			'created' => $created,
			'errors'  => $errors,
			'count'   => count($created),
		);
	}
}

/* ── Step 4: Builder SBS ── */

if (!function_exists('aihl_deploy_step_builder')) {
	function aihl_deploy_step_builder(array $pages_def, array $created_pages) {
		$populated = 0;
		$skipped = 0;
		$errors = array();

		// Mappa slug → page_id dai risultati creazione
		$slug_to_id = array();
		foreach ($created_pages as $cp) {
			$slug_to_id[$cp['slug']] = $cp['page_id'];
		}

		foreach ($pages_def as $page_def) {
			if (empty($page_def['builder']) || !is_array($page_def['builder'])) {
				$skipped++;
				continue;
			}

			$slug = isset($page_def['slug']) ? sanitize_title($page_def['slug']) : sanitize_title($page_def['title'] ?? '');
			if (!isset($slug_to_id[$slug])) {
				$errors[] = sprintf(
					/* translators: %s: page slug */
					__('Pagina "%s" non trovata per popolare il builder.', AIHL_TEXT_DOMAIN),
					$slug
				);
				continue;
			}

			$page_id = $slug_to_id[$slug];

			// Prova a usare l'API SBS interna
			if (function_exists('sbs_ai_import_builder_data')) {
				$result = sbs_ai_import_builder_data($page_id, $page_def['builder']);
				if (is_wp_error($result)) {
					$errors[] = $result->get_error_message();
				} else {
					$populated++;
				}
			} elseif (function_exists('smart_site_builder_save_widgets')) {
				// Fallback: salva direttamente nei meta SBS
				$widgets_json = wp_json_encode($page_def['builder']);
				update_post_meta($page_id, '_sbs_builder_data', $widgets_json);
				$populated++;
			} else {
				// SBS non disponibile — salva i dati in un meta temporaneo
				// per import manuale successivo
				update_post_meta($page_id, '_aihl_pending_builder', wp_json_encode($page_def['builder']));
				$errors[] = sprintf(
					/* translators: %s: page title */
					__('SBS non attivo. Dati builder per "%s" salvati come pending.', AIHL_TEXT_DOMAIN),
					$page_def['title'] ?? $slug
				);
			}
		}

		return array(
			'populated' => $populated,
			'skipped'   => $skipped,
			'errors'    => $errors,
		);
	}
}

/* ── Step 5: Settings (homepage, blog page) ── */

if (!function_exists('aihl_deploy_step_settings')) {
	function aihl_deploy_step_settings(array $settings, array $created_pages) {
		$applied = array();

		// Mappa slug → page_id
		$slug_to_id = array();
		foreach ($created_pages as $cp) {
			$slug_to_id[$cp['slug']] = $cp['page_id'];
		}

		// Homepage statica
		if (!empty($settings['show_on_front'])) {
			update_option('show_on_front', 'page');
			$applied[] = 'show_on_front = page';
		}

		if (!empty($settings['page_on_front'])) {
			$front_slug = sanitize_title($settings['page_on_front']);
			$front_id = $slug_to_id[$front_slug] ?? 0;
			if (!$front_id) {
				$page = get_page_by_path($front_slug);
				$front_id = $page ? $page->ID : 0;
			}
			if ($front_id) {
				update_option('show_on_front', 'page');
				update_option('page_on_front', $front_id);
				$applied[] = 'page_on_front = ' . $front_slug . ' (ID ' . $front_id . ')';
			}
		}

		// Pagina blog
		if (!empty($settings['page_for_posts'])) {
			$blog_slug = sanitize_title($settings['page_for_posts']);
			$blog_id = $slug_to_id[$blog_slug] ?? 0;
			if (!$blog_id) {
				$page = get_page_by_path($blog_slug);
				$blog_id = $page ? $page->ID : 0;
			}
			if ($blog_id) {
				update_option('page_for_posts', $blog_id);
				$applied[] = 'page_for_posts = ' . $blog_slug . ' (ID ' . $blog_id . ')';
			}
		}

		// Titolo sito
		if (!empty($settings['blogname'])) {
			update_option('blogname', sanitize_text_field($settings['blogname']));
			$applied[] = 'blogname';
		}

		// Descrizione sito
		if (!empty($settings['blogdescription'])) {
			update_option('blogdescription', sanitize_text_field($settings['blogdescription']));
			$applied[] = 'blogdescription';
		}

		// Permalink structure
		if (!empty($settings['permalink_structure'])) {
			$allowed = array('/%postname%/', '/%year%/%monthnum%/%postname%/', '/%category%/%postname%/');
			$struct = sanitize_text_field($settings['permalink_structure']);
			if (in_array($struct, $allowed, true)) {
				update_option('permalink_structure', $struct);
				flush_rewrite_rules(false);
				$applied[] = 'permalink_structure = ' . $struct;
			}
		}

		return array(
			'applied' => $applied,
			'count'   => count($applied),
		);
	}
}

/* ============================================================================
 * 2. REST API endpoint — POST /aihtml/v1/ai/deploy
 * ============================================================================ */

add_action('rest_api_init', function () {
	register_rest_route('aihtml/v1', '/ai/deploy', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'permission_callback' => function (WP_REST_Request $request) {
			if (function_exists('smart_ai_can_write')) {
				return smart_ai_can_write($request);
			}
			return current_user_can('manage_options');
		},
		'callback'            => function (WP_REST_Request $request) {
			$body = $request->get_json_params();
			if (!is_array($body)) {
				return new WP_Error('invalid_json', 'Payload JSON non valido.', array('status' => 400));
			}
			$result = aihl_deploy_project($body);
			if (is_wp_error($result)) {
				return $result;
			}
			return rest_ensure_response($result);
		},
	));

	// GET deploy/projects — lista progetti demo disponibili
	register_rest_route('aihtml/v1', '/ai/deploy/projects', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => function (WP_REST_Request $request) {
			if (function_exists('smart_ai_can_read')) {
				return smart_ai_can_read($request);
			}
			return current_user_can('manage_options');
		},
		'callback'            => function () {
			$projects = aihl_get_demo_projects();
			return rest_ensure_response(array(
				'count'    => count($projects),
				'projects' => $projects,
			));
		},
	));
});

/* ============================================================================
 * 3. Scanner progetti demo nella cartella demo-projects/
 * ============================================================================ */

if (!function_exists('aihl_get_demo_projects')) {
	function aihl_get_demo_projects() {
		$base = get_template_directory() . '/demo-projects';
		if (!is_dir($base)) {
			return array();
		}

		$projects = array();
		$dirs = glob($base . '/*', GLOB_ONLYDIR);
		if (!is_array($dirs)) {
			return array();
		}

		foreach ($dirs as $dir) {
			$project_file = $dir . '/project.json';
			if (!file_exists($project_file)) {
				// Fallback: cerca file separati (formato legacy)
				$has_options = file_exists($dir . '/theme-options.json');
				$has_menus = file_exists($dir . '/menus.json');
				if (!$has_options && !$has_menus) {
					continue;
				}

				$readme = file_exists($dir . '/README.md') ? file_get_contents($dir . '/README.md') : '';
				$name = basename($dir);
				$title = ucwords(str_replace(array('-', '_'), ' ', $name));

				// Estrai titolo dal README se presente
				if (preg_match('/^#\s+(.+)/m', $readme, $m)) {
					$title = trim($m[1]);
				}

				$projects[] = array(
					'slug'        => $name,
					'name'        => $title,
					'description' => '',
					'format'      => 'legacy',
					'path'        => $dir,
					'has_options' => $has_options,
					'has_menus'   => $has_menus,
					'has_builder' => file_exists($dir . '/sbs-home.json'),
				);
				continue;
			}

			$raw = file_get_contents($project_file);
			$data = json_decode($raw, true);
			if (!is_array($data) || empty($data['format'])) {
				continue;
			}

			$projects[] = array(
				'slug'        => basename($dir),
				'name'        => $data['name'] ?? basename($dir),
				'description' => $data['description'] ?? '',
				'format'      => 'aihl-project',
				'path'        => $dir,
				'has_options' => !empty($data['options']),
				'has_menus'   => !empty($data['menus']),
				'has_pages'   => !empty($data['pages']),
				'has_builder' => false,
			);

			// Check se qualche pagina ha builder data
			if (!empty($data['pages'])) {
				foreach ($data['pages'] as $p) {
					if (!empty($p['builder'])) {
						$projects[count($projects) - 1]['has_builder'] = true;
						break;
					}
				}
			}
		}

		return $projects;
	}
}

/* ============================================================================
 * 4. Deploy da cartella demo (supporta sia project.json che legacy)
 * ============================================================================ */

if (!function_exists('aihl_deploy_demo_project')) {
	function aihl_deploy_demo_project(string $project_slug) {
		$base = get_template_directory() . '/demo-projects/' . sanitize_file_name($project_slug);
		if (!is_dir($base)) {
			return new WP_Error('not_found', __('Progetto demo non trovato.', AIHL_TEXT_DOMAIN));
		}

		// Formato unificato project.json
		$project_file = $base . '/project.json';
		if (file_exists($project_file)) {
			$raw = file_get_contents($project_file);
			$project = json_decode($raw, true);
			if (!is_array($project)) {
				return new WP_Error('invalid_json', __('project.json non valido.', AIHL_TEXT_DOMAIN));
			}
			return aihl_deploy_project($project);
		}

		// Formato legacy: file separati
		$project = array(
			'format'  => 'aihl-project',
			'version' => 1,
			'name'    => ucwords(str_replace(array('-', '_'), ' ', $project_slug)),
		);

		// Opzioni
		if (file_exists($base . '/theme-options.json')) {
			$opts_raw = json_decode(file_get_contents($base . '/theme-options.json'), true);
			if (is_array($opts_raw) && !empty($opts_raw['options'])) {
				$project['options'] = $opts_raw['options'];
			}
		}

		// Menu
		if (file_exists($base . '/menus.json')) {
			$menus_raw = json_decode(file_get_contents($base . '/menus.json'), true);
			if (is_array($menus_raw)) {
				$project['menus'] = $menus_raw;
			}
		}

		// Pagine + Builder (da pages.json o inferite da sbs-*.json)
		if (file_exists($base . '/pages.json')) {
			$pages_raw = json_decode(file_get_contents($base . '/pages.json'), true);
			if (is_array($pages_raw)) {
				$project['pages'] = $pages_raw;
			}
		}

		// Settings
		if (file_exists($base . '/settings.json')) {
			$settings_raw = json_decode(file_get_contents($base . '/settings.json'), true);
			if (is_array($settings_raw)) {
				$project['settings'] = $settings_raw;
			}
		}

		return aihl_deploy_project($project);
	}
}

/* ============================================================================
 * 4b. AI-HTML Reset compatibility
 *
 * Il reset non e' piu' un registry cross-prodotto. Queste funzioni restano solo
 * per la pagina Deploy e chiamano il reset standalone del tema AI-HTML.
 * ============================================================================ */

if (!function_exists('smart_reset_get_registry')) {
	function smart_reset_get_registry() {
		return function_exists('aihl_get_smart_reset_registry') ? aihl_get_smart_reset_registry() : array();
	}
}

if (!function_exists('smart_reset_execute')) {
	function smart_reset_execute(array $component_ids) {
		$registry = smart_reset_get_registry();
		$log = array(
			'results'  => array(),
			'executed' => 0,
			'skipped'  => 0,
			'errors'   => 0,
		);

		if (!function_exists('aihl_smart_reset_execute')) {
			$log['errors'] = 1;
			$log['success'] = false;
			$log['results']['aihl:reset'] = array('status' => 'error', 'detail' => __('Reset AI-HTML non disponibile.', AIHL_TEXT_DOMAIN));
			return $log;
		}

		$response = aihl_smart_reset_execute($component_ids, false);
		foreach (($response['results'] ?? array()) as $id => $result) {
			$log['results'][$id] = $result;
			$status = $result['status'] ?? '';
			if ('missing' === $status || 'skipped' === $status) {
				$log['skipped']++;
			} elseif ('error' === $status) {
				$log['errors']++;
			} else {
				$log['executed']++;
			}
		}
		$log['success'] = (0 === $log['errors']);
		return $log;
	}
}

if (!function_exists('aihl_reset_theme')) {
	function aihl_reset_theme(array $components) {
		$map = array(
			'options'    => 'aihl:theme-options',
			'code_slots' => 'aihl:code-slots',
			'cache'      => 'aihl:runtime-cache',
		);
		$ids = array();
		foreach ($components as $component) {
			if (isset($map[$component])) {
				$ids[] = $map[$component];
			}
		}
		$log = smart_reset_execute($ids);
		$flat = array('success' => $log['success']);
		foreach ($log['results'] as $id => $result) {
			$flat[str_replace('aihl:', '', $id)] = $result;
		}
		return $flat;
	}
}

/* ============================================================================
 * 5. Admin page — Progetti Demo (UI)
 * ============================================================================ */

if (!function_exists('aihl_render_deploy_projects_page')) {
	function aihl_render_deploy_projects_page() {
		$projects = aihl_get_demo_projects();
		$deploy_result = null;
		$deploy_slug = '';

		// Handle POST deploy
		if (isset($_POST['aihl_deploy_project']) && check_admin_referer('aihl_deploy_project_nonce')) {
			$deploy_slug = sanitize_text_field(wp_unslash($_POST['aihl_deploy_project']));
			$deploy_result = aihl_deploy_demo_project($deploy_slug);
		}

		// Handle POST reset
		$reset_result = null;
		if (isset($_POST['aihl_reset_theme']) && check_admin_referer('aihl_deploy_project_nonce')) {
			$registry = smart_reset_get_registry();
			$allowed_ids = array_keys($registry);
			$component_ids = array();
			if (!empty($_POST['aihl_reset_components']) && is_array($_POST['aihl_reset_components'])) {
				$component_ids = array_intersect(
					array_map('sanitize_text_field', wp_unslash($_POST['aihl_reset_components'])),
					$allowed_ids
				);
			}
			if (!empty($component_ids)) {
				$reset_result = smart_reset_execute($component_ids);
			}
		}

		// Handle file upload deploy
		if (isset($_FILES['aihl_project_file']) && check_admin_referer('aihl_deploy_project_nonce')) {
			$file = $_FILES['aihl_project_file'];
			if (!empty($file['tmp_name']) && UPLOAD_ERR_OK === $file['error']) {
				$raw = file_get_contents($file['tmp_name']);
				$data = json_decode($raw, true);
				if (is_array($data)) {
					$deploy_result = aihl_deploy_project($data);
					$deploy_slug = 'upload';
				} else {
					$deploy_result = new WP_Error('invalid_json', __('File JSON non valido.', AIHL_TEXT_DOMAIN));
				}
			}
		}

		?>
		<?php // Reset feedback viene mostrato vicino alla sezione reset, vedi sotto ?>

		<?php if ($deploy_result) : ?>
			<?php if (is_wp_error($deploy_result)) : ?>
				<div class="notice notice-error">
					<p><strong><?php esc_html_e('Errore deploy:', AIHL_TEXT_DOMAIN); ?></strong>
					<?php echo esc_html($deploy_result->get_error_message()); ?></p>
				</div>
			<?php else : ?>
				<div class="notice notice-success">
					<p><strong><?php
						printf(
							/* translators: %s: project name */
							esc_html__('Progetto "%s" deployato con successo!', AIHL_TEXT_DOMAIN),
							esc_html($deploy_result['project_name'] ?? $deploy_slug)
						);
					?></strong></p>
				</div>
				<div class="aihl-deploy-log">
					<h3><?php esc_html_e('Dettaglio deploy', AIHL_TEXT_DOMAIN); ?></h3>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e('Step', AIHL_TEXT_DOMAIN); ?></th>
								<th><?php esc_html_e('Risultato', AIHL_TEXT_DOMAIN); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($deploy_result['steps'] as $step_name => $step_data) : ?>
							<tr>
								<td><strong><?php echo esc_html(ucfirst($step_name)); ?></strong></td>
								<td>
								<?php if (!empty($step_data['skipped'])) : ?>
									<span style="color:#646970;"><?php echo esc_html($step_data['reason']); ?></span>
								<?php elseif (!empty($step_data['error'])) : ?>
									<span style="color:#dc3545;"><?php echo esc_html($step_data['error']); ?></span>
								<?php else : ?>
									<span style="color:#16a34a;">
									<?php
									$parts = array();
									if (isset($step_data['applied'])) {
										$parts[] = is_int($step_data['applied']) ? $step_data['applied'] . ' applicati' : count($step_data['applied']) . ' applicati';
									}
									if (isset($step_data['menus_imported'])) {
										$parts[] = $step_data['menus_imported'] . ' menu, ' . $step_data['items_imported'] . ' voci';
									}
									if (isset($step_data['count'])) {
										$parts[] = $step_data['count'] . ' elementi';
									}
									if (isset($step_data['populated'])) {
										$parts[] = $step_data['populated'] . ' popolati';
									}
									echo esc_html(implode(' | ', $parts) ?: 'OK');
									?>
									</span>
									<?php if (!empty($step_data['rejected'])) : ?>
										<br><small style="color:#92400e;">
											<?php
											$rej = $step_data['rejected'];
											if (is_array($rej)) {
												echo esc_html('Rifiutati: ' . implode(', ', array_slice($rej, 0, 5)));
												if (count($rej) > 5) {
													echo esc_html(' + ' . (count($rej) - 5) . ' altri');
												}
											}
											?>
										</small>
									<?php endif; ?>
									<?php if (!empty($step_data['errors'])) : ?>
										<br><small style="color:#dc3545;">
											<?php echo esc_html(implode('; ', array_slice($step_data['errors'], 0, 3))); ?>
										</small>
									<?php endif; ?>
								<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field('aihl_deploy_project_nonce'); ?>

			<!-- Progetti demo disponibili -->
			<h2 style="margin-top:24px;"><?php esc_html_e('Progetti demo disponibili', AIHL_TEXT_DOMAIN); ?></h2>

			<?php if (empty($projects)) : ?>
				<p class="description"><?php esc_html_e('Nessun progetto demo trovato nella cartella demo-projects/.', AIHL_TEXT_DOMAIN); ?></p>
			<?php else : ?>
				<div class="smart-dash-cards" style="margin-bottom:24px;">
					<?php foreach ($projects as $proj) : ?>
						<div class="aihl-project-card">
							<div class="aihl-project-card-header">
								<strong><?php echo esc_html($proj['name']); ?></strong>
								<span class="aihl-project-format"><?php echo esc_html($proj['format']); ?></span>
							</div>
							<?php if (!empty($proj['description'])) : ?>
								<p class="description"><?php echo esc_html($proj['description']); ?></p>
							<?php endif; ?>
							<div class="aihl-project-badges">
								<?php if ($proj['has_options']) : ?>
									<span class="aihl-project-badge aihl-badge-options">
										<i class="fa-solid fa-sliders"></i> <?php esc_html_e('Opzioni', AIHL_TEXT_DOMAIN); ?>
									</span>
								<?php endif; ?>
								<?php if ($proj['has_menus']) : ?>
									<span class="aihl-project-badge aihl-badge-menus">
										<i class="fa-solid fa-bars"></i> <?php esc_html_e('Menu', AIHL_TEXT_DOMAIN); ?>
									</span>
								<?php endif; ?>
								<?php if (!empty($proj['has_pages'])) : ?>
									<span class="aihl-project-badge aihl-badge-pages">
										<i class="fa-solid fa-file"></i> <?php esc_html_e('Pagine', AIHL_TEXT_DOMAIN); ?>
									</span>
								<?php endif; ?>
								<?php if ($proj['has_builder']) : ?>
									<span class="aihl-project-badge aihl-badge-builder">
										<i class="fa-solid fa-cubes"></i> <?php esc_html_e('Builder', AIHL_TEXT_DOMAIN); ?>
									</span>
								<?php endif; ?>
							</div>
							<button type="submit" name="aihl_deploy_project" value="<?php echo esc_attr($proj['slug']); ?>"
								class="button button-primary" style="margin-top:12px;"
								onclick="return confirm('<?php echo esc_js(__('Deployare il progetto? Le impostazioni attuali verranno sovrascritte.', AIHL_TEXT_DOMAIN)); ?>');">
								<i class="fa-solid fa-rocket"></i> <?php esc_html_e('Deploy', AIHL_TEXT_DOMAIN); ?>
							</button>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- Upload progetto custom -->
			<h2><?php esc_html_e('Import progetto da file', AIHL_TEXT_DOMAIN); ?></h2>
			<p class="description"><?php esc_html_e('Carica un file project.json per deployare un progetto personalizzato.', AIHL_TEXT_DOMAIN); ?></p>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e('File project.json', AIHL_TEXT_DOMAIN); ?></th>
					<td>
						<input type="file" name="aihl_project_file" accept=".json">
						<p class="description"><?php esc_html_e('Formato: aihl-project (project.json unificato).', AIHL_TEXT_DOMAIN); ?></p>
					</td>
				</tr>
			</table>
			<p>
				<button type="submit" class="button button-secondary">
					<i class="fa-solid fa-upload"></i> <?php esc_html_e('Upload e Deploy', AIHL_TEXT_DOMAIN); ?>
				</button>
			</p>
		</form>

		<!-- API Reference -->
		<h2 style="margin-top:32px;"><?php esc_html_e('API Deploy', AIHL_TEXT_DOMAIN); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e('Endpoint', AIHL_TEXT_DOMAIN); ?></th>
				<td>
					<code>POST <?php echo esc_url(rest_url('aihtml/v1/ai/deploy')); ?></code><br>
					<small><?php esc_html_e('Invia un project.json completo per deploy via API.', AIHL_TEXT_DOMAIN); ?></small>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e('Lista progetti', AIHL_TEXT_DOMAIN); ?></th>
				<td>
					<code>GET <?php echo esc_url(rest_url('aihtml/v1/ai/deploy/projects')); ?></code><br>
					<small><?php esc_html_e('Elenca i progetti demo disponibili nel tema.', AIHL_TEXT_DOMAIN); ?></small>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e('Reset tema', AIHL_TEXT_DOMAIN); ?></th>
				<td>
					<code>POST <?php echo esc_url(rest_url('aihtml/v1/ai/reset/execute')); ?></code><br>
					<small><?php esc_html_e('Body: {"components":["aihl:theme-options","aihl:code-slots","aihl:runtime-cache"]}', AIHL_TEXT_DOMAIN); ?></small>
				</td>
			</tr>
		</table>

		<!-- Reset AI-HTML -->
		<div id="aihl-reset-anchor"></div>
		<?php if ($reset_result) :
			$reset_fb_registry = smart_reset_get_registry();
			$has_errors = !$reset_result['success'];
			$notice_icon = $has_errors ? 'fa-solid fa-triangle-exclamation' : 'fa-solid fa-check-circle';
		?>
			<div class="aihl-reset-feedback <?php echo $has_errors ? 'aihl-reset-feedback-error' : 'aihl-reset-feedback-ok'; ?>">
				<div class="aihl-reset-feedback-header">
					<i class="<?php echo esc_attr($notice_icon); ?>"></i>
					<div>
						<strong><?php
							if ($has_errors) {
								esc_html_e('Reset completato con errori', AIHL_TEXT_DOMAIN);
							} else {
								printf(
									esc_html__('Reset completato — %d componenti resettati', AIHL_TEXT_DOMAIN),
									$reset_result['executed']
								);
							}
						?></strong>
						<span class="aihl-reset-feedback-summary">
							<?php
							$parts = array();
							if ($reset_result['executed'] > 0) {
								$parts[] = sprintf(__('%d eseguiti', AIHL_TEXT_DOMAIN), $reset_result['executed']);
							}
							if ($reset_result['skipped'] > 0) {
								$parts[] = sprintf(__('%d saltati', AIHL_TEXT_DOMAIN), $reset_result['skipped']);
							}
							if ($reset_result['errors'] > 0) {
								$parts[] = sprintf(__('%d errori', AIHL_TEXT_DOMAIN), $reset_result['errors']);
							}
							echo esc_html(implode(' · ', $parts));
							?>
						</span>
					</div>
				</div>
				<div class="aihl-reset-feedback-items">
					<?php foreach ($reset_result['results'] as $comp_id => $comp_result) :
						$entry = $reset_fb_registry[$comp_id] ?? null;
						$status = $comp_result['status'] ?? 'unknown';
						$status_icon = 'reset' === $status ? 'fa-solid fa-check' : ('error' === $status ? 'fa-solid fa-xmark' : 'fa-solid fa-minus');
						$status_class = 'reset' === $status ? 'aihl-rfi-ok' : ('error' === $status ? 'aihl-rfi-err' : 'aihl-rfi-skip');
					?>
						<div class="aihl-reset-feedback-item <?php echo esc_attr($status_class); ?>">
							<i class="<?php echo esc_attr($status_icon); ?>"></i>
							<div>
								<strong>
									<?php if ($entry) : ?>
										<span class="aihl-rfi-product"><?php echo esc_html($entry['product']); ?></span>
										<?php echo esc_html($entry['label']); ?>
									<?php else : ?>
										<?php echo esc_html($comp_id); ?>
									<?php endif; ?>
								</strong>
								<span><?php echo esc_html($comp_result['detail'] ?? ''); ?></span>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<script>document.getElementById('aihl-reset-anchor').scrollIntoView({behavior:'smooth',block:'start'});</script>
		<?php endif; ?>
		<?php
		$reset_registry = smart_reset_get_registry();
		// Raggruppa per prodotto
		$by_product = array();
		foreach ($reset_registry as $id => $entry) {
			$by_product[$entry['product']][$id] = $entry;
		}
		?>
		<div class="aihl-reset-section">
			<h2><i class="fa-solid fa-rotate-left"></i> <?php esc_html_e('Reset AI-HTML', AIHL_TEXT_DOMAIN); ?></h2>
			<p class="description"><?php esc_html_e('Seleziona i soli componenti governati dal tema AI-HTML. Gli altri plugin hanno reset autonomi nelle rispettive console.', AIHL_TEXT_DOMAIN); ?></p>

			<!-- Bottone Reset Tutto -->
			<p style="margin:16px 0 8px;">
				<button type="button" class="button button-secondary" id="aihl-reset-select-all"
					onclick="document.querySelectorAll('.aihl-reset-cb').forEach(function(c){c.checked=true;});">
					<i class="fa-solid fa-check-double"></i> <?php esc_html_e('Seleziona tutto', AIHL_TEXT_DOMAIN); ?>
				</button>
				<button type="button" class="button button-secondary" style="margin-left:4px;"
					onclick="document.querySelectorAll('.aihl-reset-cb').forEach(function(c){c.checked=false;});">
					<i class="fa-solid fa-xmark"></i> <?php esc_html_e('Deseleziona tutto', AIHL_TEXT_DOMAIN); ?>
				</button>
			</p>

			<?php foreach ($by_product as $product_name => $components) :
				$first = reset($components);
			?>
				<div class="aihl-reset-product-group">
					<h3 class="aihl-reset-product-title">
						<i class="<?php echo esc_attr($first['product_icon']); ?>"></i>
						<?php echo esc_html($product_name); ?>
						<span class="aihl-reset-product-count"><?php echo count($components); ?></span>
					</h3>
					<div class="aihl-reset-grid">
						<?php foreach ($components as $id => $entry) : ?>
							<label class="aihl-reset-option">
								<input type="checkbox" class="aihl-reset-cb" name="aihl_reset_components[]"
									value="<?php echo esc_attr($id); ?>">
								<span class="aihl-reset-option-body">
									<i class="<?php echo esc_attr($entry['icon']); ?> aihl-reset-icon"></i>
									<strong><?php echo esc_html($entry['label']); ?></strong>
									<span><?php echo esc_html($entry['description']); ?></span>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<div class="aihl-reset-actions">
				<button type="submit" name="aihl_reset_theme" value="1"
					class="button aihl-btn-reset"
					onclick="var c=document.querySelectorAll('.aihl-reset-cb:checked');if(!c.length){alert('<?php echo esc_js(__('Seleziona almeno un componente da resettare.', AIHL_TEXT_DOMAIN)); ?>');return false;}var n=c.length;return confirm('<?php echo esc_js(__('ATTENZIONE: stai per resettare', AIHL_TEXT_DOMAIN)); ?> '+n+' <?php echo esc_js(__('componenti. Questa operazione non e facilmente reversibile. Continuare?', AIHL_TEXT_DOMAIN)); ?>');">
					<i class="fa-solid fa-rotate-left"></i> <?php esc_html_e('Esegui Reset selezionati', AIHL_TEXT_DOMAIN); ?>
				</button>
				<button type="submit" name="aihl_reset_theme" value="1"
					class="button aihl-btn-reset-all"
					onclick="document.querySelectorAll('.aihl-reset-cb').forEach(function(c){c.checked=true;});return confirm('<?php echo esc_js(__('ATTENZIONE: stai per resettare tutti i componenti AI-HTML. Sei sicuro?', AIHL_TEXT_DOMAIN)); ?>');">
					<i class="fa-solid fa-skull-crossbones"></i> <?php esc_html_e('Reset TUTTO', AIHL_TEXT_DOMAIN); ?>
				</button>
			</div>
		</div>
		<?php
	}
}

/* ============================================================================
 * 6. CSS aggiuntivo per la pagina progetti
 * ============================================================================ */

add_action('admin_enqueue_scripts', function ($hook) {
	if (strpos($hook, 'aihl-deploy') === false) {
		return;
	}

	$css = <<<'CSS'
.aihl-project-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px 20px;display:flex;flex-direction:column;gap:8px}
.aihl-project-card-header{display:flex;align-items:center;justify-content:space-between;gap:8px}
.aihl-project-card-header strong{font-size:15px;color:#1d2327}
.aihl-project-format{font-size:10px;color:#646970;background:#f0f0f1;padding:2px 8px;border-radius:10px;text-transform:uppercase;letter-spacing:.04em;font-weight:600}
.aihl-project-badges{display:flex;flex-wrap:wrap;gap:6px}
.aihl-project-badge{display:inline-flex;align-items:center;gap:4px;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:500}
.aihl-badge-options{background:#eff6ff;color:#1e40af}
.aihl-badge-menus{background:#f0fdf4;color:#166534}
.aihl-badge-pages{background:#fefce8;color:#854d0e}
.aihl-badge-builder{background:#fdf2f8;color:#9d174d}
.aihl-deploy-log{margin:16px 0 24px;padding:16px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:8px}
.aihl-deploy-log h3{margin:0 0 12px;font-size:14px}
.aihl-deploy-log table{border-radius:6px;overflow:hidden}
/* ── Reset Section ── */
.aihl-reset-section{margin-top:40px;padding-top:32px;border-top:2px solid #dcdcde}
.aihl-reset-section h2{font-size:17px;font-weight:600;color:#991b1b;margin:0 0 6px;display:flex;align-items:center;gap:8px}
.aihl-reset-product-group{margin-top:20px}
.aihl-reset-product-title{font-size:14px;font-weight:600;color:#1d2327;margin:0 0 10px;display:flex;align-items:center;gap:8px;padding-bottom:8px;border-bottom:1px solid #f0f0f1}
.aihl-reset-product-title i{color:#2271b1;font-size:15px}
.aihl-reset-product-count{font-size:10px;color:#646970;background:#f0f0f1;padding:1px 7px;border-radius:10px;font-weight:600}
.aihl-reset-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px}
.aihl-reset-option{display:flex;align-items:flex-start;gap:10px;padding:14px 16px;background:#fff;border:1px solid #dcdcde;border-radius:8px;cursor:pointer;transition:border-color .15s,background .15s}
.aihl-reset-option:hover{border-color:#dc3545;background:#fef2f2}
.aihl-reset-option input[type=checkbox]{margin-top:2px;flex-shrink:0}
.aihl-reset-option-body{display:flex;flex-direction:column;gap:2px}
.aihl-reset-option-body strong{font-size:13px;color:#1d2327}
.aihl-reset-option-body span{font-size:11px;color:#646970;line-height:1.4}
.aihl-reset-icon{font-size:13px;color:#991b1b;float:right}
.aihl-reset-actions{margin-top:20px;display:flex;gap:10px;align-items:center}
.aihl-btn-reset{background:#dc3545!important;border-color:#dc3545!important;color:#fff!important;border-radius:6px!important}
.aihl-btn-reset:hover{background:#b91c1c!important;border-color:#b91c1c!important}
.aihl-btn-reset-all{background:#1d2327!important;border-color:#1d2327!important;color:#fff!important;border-radius:6px!important}
.aihl-btn-reset-all:hover{background:#000!important;border-color:#000!important}
/* ── Reset Feedback ── */
.aihl-reset-feedback{margin-bottom:24px;border-radius:8px;border:1px solid #dcdcde;overflow:hidden}
.aihl-reset-feedback-ok{border-color:#16a34a}
.aihl-reset-feedback-error{border-color:#dc3545}
.aihl-reset-feedback-header{display:flex;align-items:center;gap:12px;padding:16px 20px}
.aihl-reset-feedback-ok .aihl-reset-feedback-header{background:#f0fdf4;color:#166534}
.aihl-reset-feedback-error .aihl-reset-feedback-header{background:#fef2f2;color:#991b1b}
.aihl-reset-feedback-header i{font-size:20px;flex-shrink:0}
.aihl-reset-feedback-header strong{display:block;font-size:14px}
.aihl-reset-feedback-summary{display:block;font-size:12px;opacity:.8;margin-top:2px}
.aihl-reset-feedback-items{padding:0}
.aihl-reset-feedback-item{display:flex;align-items:flex-start;gap:10px;padding:10px 20px;border-top:1px solid #f0f0f1;font-size:13px}
.aihl-reset-feedback-item i{margin-top:2px;flex-shrink:0;width:16px;text-align:center}
.aihl-reset-feedback-item strong{display:block;font-size:13px}
.aihl-reset-feedback-item span{display:block;font-size:11px;color:#646970;margin-top:1px}
.aihl-rfi-ok i{color:#16a34a}
.aihl-rfi-err i{color:#dc3545}
.aihl-rfi-skip i{color:#646970}
.aihl-rfi-product{font-size:10px;color:#646970;background:#f0f0f1;padding:1px 6px;border-radius:8px;margin-right:4px;font-weight:600;text-transform:uppercase;letter-spacing:.03em}
CSS;

	wp_add_inline_style('wp-admin', $css);
});
