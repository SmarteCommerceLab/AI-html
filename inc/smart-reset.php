<?php
/**
 * AI-HTML Smart Reset
 *
 * Reset standalone del tema AI-HTML.
 * Non orchestra e non invoca reset di altri plugin.
 */
if (!defined('ABSPATH')) {
	exit;
}

add_action('admin_post_aihl_smart_reset_execute', 'aihl_handle_smart_reset_execute');
add_action('admin_post_aihl_smart_reset_download', 'aihl_handle_smart_reset_download');
add_action('rest_api_init', 'aihl_register_smart_reset_routes');

function aihl_smart_reset_can_manage(): bool {
	return current_user_can('manage_options');
}

function aihl_get_smart_reset_registry(): array {
	$registry = aihl_register_reset_components(array());
	if (!is_array($registry)) {
		return array();
	}

	$normalized = array();
	foreach ($registry as $id => $component) {
		if (!is_array($component)) {
			continue;
		}
		$id = isset($component['id']) ? sanitize_key(str_replace(':', '-', (string) $component['id'])) : sanitize_key(str_replace(':', '-', (string) $id));
		$public_id = isset($component['id']) ? (string) $component['id'] : (string) $id;
		$callback = $component['callback'] ?? null;
		if (!$public_id || !is_callable($callback)) {
			continue;
		}
		$normalized[$public_id] = array(
			'id'           => $public_id,
			'product'      => sanitize_text_field((string) ($component['product'] ?? 'AI-HTML')),
			'product_icon' => sanitize_text_field((string) ($component['product_icon'] ?? 'fa-solid fa-cube')),
			'label'        => sanitize_text_field((string) ($component['label'] ?? $public_id)),
			'description'  => sanitize_text_field((string) ($component['description'] ?? '')),
			'icon'         => sanitize_text_field((string) ($component['icon'] ?? 'fa-solid fa-rotate-left')),
			'callback'     => $callback,
			'danger'       => !empty($component['danger']),
		);
	}

	ksort($normalized);
	return $normalized;
}

function aihl_register_reset_components(array $registry): array {
	$registry['aihl:factory'] = array(
		'product'      => 'AI-HTML',
		'product_icon' => 'fa-solid fa-layer-group',
		'id'           => 'aihl:factory',
		'label'        => __('Impostazioni di fabbrica', AIHL_TEXT_DOMAIN),
		'description'  => __('Ripristina tutte le impostazioni governate dal tema: opzioni, Code Slot e cache runtime.', AIHL_TEXT_DOMAIN),
		'icon'         => 'fa-solid fa-industry',
		'callback'     => 'aihl_reset_exec_factory',
		'danger'       => true,
	);

	$registry['aihl:theme-options'] = array(
		'product'      => 'AI-HTML',
		'product_icon' => 'fa-solid fa-layer-group',
		'id'           => 'aihl:theme-options',
		'label'        => __('Opzioni tema', AIHL_TEXT_DOMAIN),
		'description'  => __('Azzera le opzioni AI-HTML salvate nel gruppo tema e ripristina i default.', AIHL_TEXT_DOMAIN),
		'icon'         => 'fa-solid fa-sliders',
		'callback'     => 'aihl_reset_exec_theme_options',
	);

	$registry['aihl:code-slots'] = array(
		'product'      => 'AI-HTML',
		'product_icon' => 'fa-solid fa-layer-group',
		'id'           => 'aihl:code-slots',
		'label'        => __('Code Slot AI', AIHL_TEXT_DOMAIN),
		'description'  => __('Rimuove gli slot HTML/CSS/JS AI per header, footer e aree tema.', AIHL_TEXT_DOMAIN),
		'icon'         => 'fa-solid fa-code',
		'callback'     => 'aihl_reset_exec_code_slots',
		'danger'       => true,
	);

	$registry['aihl:runtime-cache'] = array(
		'product'      => 'AI-HTML',
		'product_icon' => 'fa-solid fa-layer-group',
		'id'           => 'aihl:runtime-cache',
		'label'        => __('Cache runtime', AIHL_TEXT_DOMAIN),
		'description'  => __('Svuota transient e cache runtime del tema senza toccare contenuti o impostazioni.', AIHL_TEXT_DOMAIN),
		'icon'         => 'fa-solid fa-broom',
		'callback'     => 'aihl_reset_exec_runtime_cache',
	);

	return $registry;
}

function aihl_smart_reset_snapshot_directory(): string {
	$upload = wp_upload_dir();
	return trailingslashit($upload['basedir']) . 'ai-html-reset';
}

function aihl_smart_reset_protect_snapshot_directory(string $dir): void {
	$files = array(
		'index.php' => "<?php\nif (!defined('ABSPATH')) {\n\texit;\n}\n",
		'.htaccess' => "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n",
		'web.config' => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration><system.webServer><authorization><deny users=\"*\" /></authorization></system.webServer></configuration>\n",
	);

	foreach ($files as $name => $contents) {
		$target = trailingslashit($dir) . $name;
		if (!file_exists($target)) {
			file_put_contents($target, $contents, LOCK_EX);
		}
	}
}

function aihl_smart_reset_snapshot(): array {
	global $wpdb;

	$prefixes = array(
		AIHL_OPTION_BASE . '_%',
		'aihl_%',
	);

	$options = array();
	foreach ($prefixes as $like) {
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value, autoload FROM {$wpdb->options} WHERE option_name LIKE %s",
				$like
			),
			ARRAY_A
		);
		foreach ((array) $rows as $row) {
			$options[$row['option_name']] = array(
				'value'    => maybe_unserialize($row['option_value']),
				'autoload' => $row['autoload'],
			);
		}
	}

	$dir = aihl_smart_reset_snapshot_directory();
	if (!is_dir($dir) && !wp_mkdir_p($dir)) {
		return array('error' => __('Impossibile creare la directory privata degli snapshot.', AIHL_TEXT_DOMAIN));
	}
	aihl_smart_reset_protect_snapshot_directory($dir);

	$token = strtolower(wp_generate_password(32, false, false));
	$filename = 'snapshot-' . gmdate('Ymd-His') . '-' . $token . '.json';
	$file = trailingslashit($dir) . $filename;
	$payload = array(
		'created_at' => gmdate('c'),
		'site_url'   => home_url('/'),
		'project'    => 'AI-HTML',
		'theme'      => defined('AIHL_VERSION') ? AIHL_VERSION : '',
		'options'    => $options,
	);
	$json = wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if (!is_string($json) || file_put_contents($file, $json, LOCK_EX) === false) {
		return array('error' => __('Impossibile scrivere lo snapshot preventivo.', AIHL_TEXT_DOMAIN));
	}

	set_transient(
		'aihl_reset_snapshot_' . $token,
		array('file' => $file, 'filename' => $filename),
		15 * MINUTE_IN_SECONDS
	);

	return array(
		'token'    => $token,
		'filename' => $filename,
		'count'    => count($options),
	);
}

function aihl_smart_reset_download_url(array $snapshot): string {
	if (empty($snapshot['token'])) {
		return '';
	}

	$token = sanitize_key((string) $snapshot['token']);
	return wp_nonce_url(
		admin_url('admin-post.php?action=aihl_smart_reset_download&token=' . rawurlencode($token)),
		'aihl_smart_reset_download_' . $token
	);
}

function aihl_handle_smart_reset_download(): void {
	if (!aihl_smart_reset_can_manage()) {
		wp_die(esc_html__('Permessi insufficienti.', AIHL_TEXT_DOMAIN));
	}

	$token = isset($_GET['token']) ? sanitize_key(wp_unslash((string) $_GET['token'])) : '';
	check_admin_referer('aihl_smart_reset_download_' . $token);
	$snapshot = get_transient('aihl_reset_snapshot_' . $token);
	$base = realpath(aihl_smart_reset_snapshot_directory());
	$file = is_array($snapshot) && !empty($snapshot['file']) ? realpath((string) $snapshot['file']) : false;
	$base = $base ? wp_normalize_path($base) : false;
	$file = $file ? wp_normalize_path($file) : false;

	if ($token === '' || !$base || !$file || strpos($file, trailingslashit($base)) !== 0 || !is_readable($file)) {
		wp_die(esc_html__('Snapshot non disponibile o scaduto.', AIHL_TEXT_DOMAIN));
	}

	nocache_headers();
	header('Content-Type: application/json; charset=utf-8');
	header('Content-Disposition: attachment; filename="' . sanitize_file_name((string) $snapshot['filename']) . '"');
	header('Content-Length: ' . (string) filesize($file));
	readfile($file);
	exit;
}

function aihl_smart_reset_execute(array $component_ids, bool $dry_run = false): array {
	$registry = aihl_get_smart_reset_registry();
	$selected = array_values(array_unique(array_filter(array_map('strval', $component_ids))));
	$results = array();

	foreach ($selected as $id) {
		if (!isset($registry[$id])) {
			$results[$id] = array('status' => 'missing', 'detail' => __('Componente non registrato.', AIHL_TEXT_DOMAIN));
			continue;
		}
		if ($dry_run) {
			$results[$id] = array(
				'status' => 'dry-run',
				'detail' => sprintf(__('Verrebbe eseguito: %s.', AIHL_TEXT_DOMAIN), $registry[$id]['label']),
			);
			continue;
		}
		$result = call_user_func($registry[$id]['callback']);
		$results[$id] = is_array($result) ? $result : array('status' => 'reset', 'detail' => (string) $result);
	}

	return array(
		'dry_run' => $dry_run,
		'results' => $results,
	);
}

function aihl_register_smart_reset_routes(): void {
	register_rest_route('aihtml/v1', '/ai/reset/registry', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => 'aihl_ai_can_read',
		'callback'            => function () {
			$registry = aihl_get_smart_reset_registry();
			foreach ($registry as &$component) {
				unset($component['callback']);
			}
			return rest_ensure_response(array('components' => array_values($registry)));
		},
	));

	register_rest_route('aihtml/v1', '/ai/reset/execute', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'permission_callback' => 'aihl_ai_can_write',
		'callback'            => function (WP_REST_Request $request) {
			$ids = $request->get_param('components');
			$dry_run = (bool) $request->get_param('dry_run');
			if (!is_array($ids)) {
				return new WP_Error('aihl_reset_invalid_components', __('components deve essere un array.', AIHL_TEXT_DOMAIN), array('status' => 400));
			}
			$snapshot = $dry_run ? null : aihl_smart_reset_snapshot();
			if (is_array($snapshot) && !empty($snapshot['error'])) {
				return new WP_Error('aihl_reset_snapshot_failed', (string) $snapshot['error'], array('status' => 500));
			}
			$response = aihl_smart_reset_execute($ids, $dry_run);
			$response['snapshot'] = is_array($snapshot)
				? array(
					'token' => $snapshot['token'],
					'filename' => $snapshot['filename'],
					'count' => $snapshot['count'],
					'api' => rest_url('aihtml/v1/ai/reset/snapshots/' . rawurlencode((string) $snapshot['token'])),
				)
				: null;
			return rest_ensure_response($response);
		},
	));

	register_rest_route('aihtml/v1', '/ai/reset/snapshots/(?P<token>[a-z0-9]+)', array(
		'methods' => WP_REST_Server::READABLE,
		'permission_callback' => 'aihl_ai_can_write',
		'callback' => function (WP_REST_Request $request) {
			$token = sanitize_key((string) $request->get_param('token'));
			$snapshot = get_transient('aihl_reset_snapshot_' . $token);
			$base = realpath(aihl_smart_reset_snapshot_directory());
			$file = is_array($snapshot) && !empty($snapshot['file']) ? realpath((string) $snapshot['file']) : false;
			$base = $base ? wp_normalize_path($base) : false;
			$file = $file ? wp_normalize_path($file) : false;
			if ($token === '' || !$base || !$file || strpos($file, trailingslashit($base)) !== 0 || !is_readable($file)) {
				return new WP_Error('aihl_reset_snapshot_missing', __('Snapshot non disponibile o scaduto.', AIHL_TEXT_DOMAIN), array('status' => 404));
			}
			$payload = json_decode((string) file_get_contents($file), true);
			if (!is_array($payload)) {
				return new WP_Error('aihl_reset_snapshot_invalid', __('Snapshot non valido.', AIHL_TEXT_DOMAIN), array('status' => 500));
			}
			return rest_ensure_response($payload);
		},
		'args' => array('token' => array('type' => 'string', 'required' => true)),
	));
}

function aihl_handle_smart_reset_execute(): void {
	if (!aihl_smart_reset_can_manage()) {
		wp_die(esc_html__('Permessi insufficienti.', AIHL_TEXT_DOMAIN));
	}
	check_admin_referer('aihl_smart_reset_execute', 'aihl_smart_reset_nonce');

	$confirm = isset($_POST['aihl_reset_confirm']) ? sanitize_text_field(wp_unslash((string) $_POST['aihl_reset_confirm'])) : '';
	if ($confirm !== 'RESET') {
		wp_safe_redirect(add_query_arg(array('page' => 'aihl-smart-reset', 'reset_message' => 'confirm'), admin_url('admin.php')));
		exit;
	}

	$components = isset($_POST['components']) && is_array($_POST['components'])
		? array_map('sanitize_text_field', wp_unslash($_POST['components']))
		: array();

	$snapshot = aihl_smart_reset_snapshot();
	if (!empty($snapshot['error'])) {
		set_transient('aihl_smart_reset_last_result', array('snapshot_error' => $snapshot['error']), 120);
		wp_safe_redirect(add_query_arg(array('page' => 'aihl-smart-reset', 'reset_message' => 'snapshot-error'), admin_url('admin.php')));
		exit;
	}
	$result = aihl_smart_reset_execute($components, false);
	set_transient('aihl_smart_reset_last_result', array('snapshot' => $snapshot, 'result' => $result), 120);

	wp_safe_redirect(add_query_arg(array('page' => 'aihl-smart-reset', 'reset_message' => 'done'), admin_url('admin.php')));
	exit;
}

function aihl_reset_exec_factory(): array {
	$results = array(
		'aihl:theme-options' => aihl_reset_exec_theme_options(),
		'aihl:code-slots'    => aihl_reset_exec_code_slots(),
		'aihl:runtime-cache' => aihl_reset_exec_runtime_cache(),
	);
	return array(
		'status'   => 'reset',
		'detail'   => __('AI-HTML ripristinato alle impostazioni di fabbrica.', AIHL_TEXT_DOMAIN),
		'children' => $results,
	);
}

function aihl_reset_exec_theme_options(): array {
	$deleted = 0;
	foreach (AIHL_OPTION as $option_group) {
		if (($option_group['option_group'] ?? '') === AIHL_OPTION_BASE . '_reset') {
			continue;
		}
		if (delete_option($option_group['option_group'])) {
			$deleted++;
		}
	}
	if (class_exists('aihl_register_class')) {
		aihl_register_class::register();
	}
	return array('status' => 'reset', 'detail' => sprintf(__('%d gruppi opzioni AI-HTML azzerati.', AIHL_TEXT_DOMAIN), $deleted));
}

function aihl_reset_exec_code_slots(): array {
	$deleted = 0;
	foreach (array('aihl_code_slots', 'aihl_code_slots_revision', 'aihl_code_slots_backup') as $option) {
		if (delete_option($option)) {
			$deleted++;
		}
	}
	return array('status' => 'reset', 'detail' => sprintf(__('%d opzioni Code Slot rimosse.', AIHL_TEXT_DOMAIN), $deleted));
}

function aihl_reset_exec_runtime_cache(): array {
	global $wpdb;
	$deleted = $wpdb->query(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aihl_%' OR option_name LIKE '_transient_timeout_aihl_%'"
	);
	return array('status' => 'reset', 'detail' => sprintf(__('%d transient AI-HTML rimossi.', AIHL_TEXT_DOMAIN), (int) $deleted));
}

function aihl_render_smart_reset_page(): void {
	if (!aihl_smart_reset_can_manage()) {
		return;
	}
	$registry = aihl_get_smart_reset_registry();
	$last = get_transient('aihl_smart_reset_last_result');
	if (isset($_GET['reset_message']) && $_GET['reset_message'] === 'confirm') {
		echo '<div class="notice notice-error"><p>' . esc_html__('Per eseguire il reset devi scrivere RESET nel campo conferma.', AIHL_TEXT_DOMAIN) . '</p></div>';
	}
	if (is_array($last)) {
		delete_transient('aihl_smart_reset_last_result');
		if (!empty($last['snapshot_error'])) {
			echo '<div class="notice notice-error"><p>' . esc_html((string) $last['snapshot_error']) . '</p></div>';
		} else {
		echo '<div class="notice notice-success"><p>' . esc_html__('Reset completato. Backup preventivo creato prima delle modifiche.', AIHL_TEXT_DOMAIN) . '</p>';
		$download_url = !empty($last['snapshot']) ? aihl_smart_reset_download_url((array) $last['snapshot']) : '';
		if ($download_url !== '') {
			echo '<p><a href="' . esc_url($download_url) . '">' . esc_html__('Scarica snapshot JSON', AIHL_TEXT_DOMAIN) . '</a></p>';
		}
		echo '</div>';
		}
	}
	?>
	<div class="aihl-reset-console">
		<div class="aihl-reset-intro"><span class="aihl-reset-intro-icon"><i class="fa-solid fa-shield-halved"></i></span><div><h2><?php esc_html_e('Ripristino selettivo protetto', AIHL_TEXT_DOMAIN); ?></h2><p><?php esc_html_e('Seleziona solo le aree da ripristinare. Prima di ogni operazione viene creato automaticamente uno snapshot JSON.', AIHL_TEXT_DOMAIN); ?></p></div></div>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="aihl_smart_reset_execute">
			<?php wp_nonce_field('aihl_smart_reset_execute', 'aihl_smart_reset_nonce'); ?>
			<div class="aihl-reset-grid">
				<?php foreach ($registry as $component) : ?>
					<label class="aihl-reset-card<?php echo !empty($component['danger']) ? ' is-danger' : ''; ?>">
						<input type="checkbox" name="components[]" value="<?php echo esc_attr($component['id']); ?>">
						<span class="aihl-reset-icon"><i class="<?php echo esc_attr($component['icon']); ?>"></i></span>
						<span class="aihl-reset-copy">
							<strong><?php echo esc_html($component['label']); ?></strong>
							<em><?php echo esc_html($component['product']); ?></em>
							<small><?php echo esc_html($component['description']); ?></small>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
			<div class="aihl-reset-confirm">
				<div><strong><?php esc_html_e('Conferma operazione', AIHL_TEXT_DOMAIN); ?></strong><small><?php esc_html_e('Scrivi RESET per abilitare il pulsante.', AIHL_TEXT_DOMAIN); ?></small></div>
				<input id="aihl-reset-confirm" type="text" name="aihl_reset_confirm" placeholder="RESET" autocomplete="off">
				<button type="submit" id="aihl-reset-submit" class="button button-primary button-large" disabled><?php esc_html_e('Esegui reset selettivo', AIHL_TEXT_DOMAIN); ?></button>
			</div>
		</form>
	</div>
	<style>
		.aihl-reset-console{width:100%}.aihl-reset-intro{display:flex;align-items:center;gap:14px;padding:18px 20px;border:1px solid #b8dfc4;border-left:4px solid #00a32a;background:#f3fbf5}.aihl-reset-intro-icon{display:grid;place-items:center;width:38px;height:38px;background:#dff3e5;color:#008a20;border-radius:4px}.aihl-reset-intro h2{margin:0 0 4px;font-size:18px}.aihl-reset-intro p{margin:0;color:#50575e}.aihl-reset-grid{display:flex;flex-direction:column;gap:0;margin:20px 0;border:1px solid #dcdcde}
		.aihl-reset-card{display:grid;grid-template-columns:auto 40px minmax(0,1fr);gap:14px;align-items:center;border:0;border-bottom:1px solid #dcdcde;background:#fff;padding:16px;cursor:pointer}.aihl-reset-card:last-child{border-bottom:0}
		.aihl-reset-card:hover{border-color:#2271b1}
		.aihl-reset-card.is-danger{border-color:#f0b8bd}
		.aihl-reset-card input{margin:0}
		.aihl-reset-icon{display:inline-flex;width:38px;height:38px;align-items:center;justify-content:center;border-radius:12px;background:#f0f6fc;color:#2271b1;flex:0 0 auto}
		.aihl-reset-copy{display:flex;flex-direction:column;gap:4px}
		.aihl-reset-copy strong{font-size:14px;color:#1d2327}
		.aihl-reset-copy em{font-style:normal;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#646970;font-weight:700}
		.aihl-reset-copy small{font-size:12px;color:#50575e;line-height:1.45}
		.aihl-reset-confirm{display:flex;flex-wrap:wrap;gap:14px;align-items:center;justify-content:flex-end;margin-top:22px;padding:18px;border:1px solid #dcdcde;background:#f6f7f7}.aihl-reset-confirm>div{display:flex;flex-direction:column;margin-right:auto}.aihl-reset-confirm small{color:#646970;margin-top:3px}
		.aihl-reset-confirm input{min-width:220px;min-height:38px}
	</style>
	<script>(function(){var input=document.getElementById('aihl-reset-confirm'),button=document.getElementById('aihl-reset-submit');if(!input||!button)return;input.addEventListener('input',function(){button.disabled=input.value.trim()!=='RESET';});})();</script>
	<?php
}
