<?php
/**
 * AI-HTML Theme - Options JSON Admin
 *
 * Pagina admin con editor JSON per le opzioni tema (header, footer, contatti, CTA).
 * Stesso pattern dell'editor JSON widget di Smart Builder Site:
 * l'utente (o un AI) puo incollare un JSON e salvarlo, con validazione whitelist.
 */
if (!defined('ABSPATH')) {
	exit;
}

/* Menu page registration moved to inc/admin/admin-hub.php (v1.2.0) */

add_action('admin_post_aihl_options_json_save', function () {
	if (!current_user_can('edit_theme_options')) {
		wp_die(esc_html__('Permessi insufficienti.', AIHL_TEXT_DOMAIN));
	}
	check_admin_referer('aihl_options_json_save');

	$json_input = isset($_POST['aihl_options_json']) ? wp_unslash((string) $_POST['aihl_options_json']) : '';
	$result = aihl_apply_options_json($json_input);

	$args = array('page' => 'aihl-options-json');
	if (is_wp_error($result)) {
		set_transient('aihl_options_json_error', $result->get_error_message(), 60);
		$args['notice'] = 'error';
	} else {
		$args['notice'] = 'saved';
		$args['applied'] = (int) $result['applied_count'];
		if (!empty($result['rejected'])) {
			set_transient('aihl_options_json_rejected', array_values($result['rejected']), 60);
			$args['rejected'] = count($result['rejected']);
		}
	}
	wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
	exit;
});

/**
 * Applica un JSON di opzioni tema con validazione whitelist.
 * Riusa la whitelist dell'API AI per coerenza.
 */
function aihl_apply_options_json(string $json_input) {
	$data = json_decode($json_input, true);
	if (!is_array($data)) {
		return new WP_Error('invalid_json', __('JSON non valido.', AIHL_TEXT_DOMAIN));
	}

	// Accetta sia { "options": {...} } sia il JSON piatto { campo: valore }
	$options = isset($data['options']) && is_array($data['options']) ? $data['options'] : $data;

	if (!function_exists('aihl_ai_options_whitelist') || !function_exists('aihl_ai_sanitize_option_value')) {
		return new WP_Error('unavailable', __('Whitelist opzioni non disponibile.', AIHL_TEXT_DOMAIN));
	}

	$whitelist = aihl_ai_options_whitelist();
	$current = get_option(AIHL_OPTION_BASE . '_general', array());
	if (!is_array($current)) {
		$current = array();
	}

	$applied = 0;
	$rejected = array();
	foreach ($options as $field => $value) {
		$field = sanitize_key((string) $field);
		if (!isset($whitelist[$field])) {
			$rejected[] = $field;
			continue;
		}
		$clean = aihl_ai_sanitize_option_value($value, $whitelist[$field]);
		if (null === $clean) {
			$rejected[] = $field;
			continue;
		}
		$current[$field] = $clean;
		$applied++;
	}

	update_option(AIHL_OPTION_BASE . '_general', $current, false);

	return array('applied_count' => $applied, 'rejected' => $rejected);
}

/**
 * Genera il JSON corrente delle opzioni tema (per export/precompilazione editor).
 */
function aihl_export_options_json(): string {
	$whitelist = function_exists('aihl_ai_options_whitelist') ? aihl_ai_options_whitelist() : array();
	$options = array();
	foreach (array_keys($whitelist) as $field) {
		$options[$field] = aihtml_option_value($field, '');
	}
	$payload = array(
		'format'  => 'aihl-options-json',
		'version' => 1,
		'theme'   => AIHL_THEME_NAME,
		'options' => $options,
	);
	return (string) wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function aihl_render_options_json_page() {
	if (!current_user_can('edit_theme_options')) {
		return;
	}

	$notice = isset($_GET['notice']) ? sanitize_key((string) $_GET['notice']) : '';
	$current_json = aihl_export_options_json();
	$whitelist = function_exists('aihl_ai_options_whitelist') ? aihl_ai_options_whitelist() : array();

	// Raggruppa la whitelist per gruppo per la guida
	$groups = array();
	foreach ($whitelist as $field => $def) {
		$groups[$def['group']][] = array('field' => $field, 'def' => $def);
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e('Configurazione JSON', AIHL_TEXT_DOMAIN); ?></h1>
		<div class="aihl-oj-explainer" role="note">
			<strong><?php esc_html_e('Questa pagina modifica la configurazione sorgente.', AIHL_TEXT_DOMAIN); ?></strong>
			<span><?php esc_html_e('Qui puoi editare e salvare le opzioni governate del tema: header, footer, contatti e CTA. Dopo il salvataggio WordPress rigenera automaticamente il Manifest JSON live.', AIHL_TEXT_DOMAIN); ?></span>
			<a class="button" href="<?php echo esc_url(admin_url('admin.php?page=aihl-manifest-json')); ?>"><?php esc_html_e('Vedi Manifest JSON', AIHL_TEXT_DOMAIN); ?></a>
		</div>

		<?php if ($notice === 'saved') : ?>
			<div class="notice notice-success is-dismissible"><p><?php
				printf(esc_html__('Opzioni salvate. Campi applicati: %d', AIHL_TEXT_DOMAIN), absint($_GET['applied'] ?? 0));
			?></p></div>
			<?php
			$rejected = get_transient('aihl_options_json_rejected');
			delete_transient('aihl_options_json_rejected');
			if (is_array($rejected) && !empty($rejected)) :
			?>
				<div class="notice notice-warning"><p><?php
					printf(
						esc_html__('Campi ignorati (%1$d): %2$s', AIHL_TEXT_DOMAIN),
						count($rejected),
						esc_html(implode(', ', $rejected))
					);
				?></p></div>
			<?php endif; ?>
		<?php elseif ($notice === 'error') :
			$err = get_transient('aihl_options_json_error');
			delete_transient('aihl_options_json_error');
			?>
			<div class="notice notice-error"><p><?php echo esc_html($err ?: __('Errore.', AIHL_TEXT_DOMAIN)); ?></p></div>
		<?php endif; ?>

		<style>
			.aihl-oj-explainer{display:grid;grid-template-columns:minmax(220px,auto) minmax(320px,1fr) auto;align-items:center;gap:12px;margin:12px 0 20px;padding:14px 16px;border-left:4px solid #00a32a;background:#edfaef;color:#1d2327}.aihl-oj-explainer span{font-size:14px;line-height:1.5}
			.aihl-oj-grid{display:grid;grid-template-columns:minmax(0,1fr);gap:20px;width:100%;margin-top:16px}
			.aihl-oj-editor{width:100%;min-height:68vh;font-family:Consolas,Monaco,monospace;font-size:14px;line-height:1.55;border:1px solid #c3c4c7;border-radius:4px;padding:18px;background:#111827;color:#dbeafe;box-sizing:border-box;resize:vertical}
			.aihl-oj-help{margin:0}
			.aihl-oj-help table{width:100%}
			.aihl-oj-help code{font-size:12px;background:#f0f6fc;color:#135e96;padding:2px 6px}
			.aihl-oj-field-groups{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px}
			.aihl-oj-field-group{border:1px solid #dcdcde;border-radius:4px;padding:14px;background:#fff}
			.aihl-oj-actions{position:sticky;bottom:0;display:flex;flex-wrap:wrap;gap:8px;margin:0;padding:12px 0;background:#fff;border-top:1px solid #dcdcde}
			@media(max-width:900px){.aihl-oj-explainer{grid-template-columns:1fr;align-items:start}}
		</style>

		<div class="aihl-oj-grid">
			<div class="postbox" style="padding:16px;">
				<h2><?php esc_html_e('Editor JSON opzioni', AIHL_TEXT_DOMAIN); ?></h2>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<input type="hidden" name="action" value="aihl_options_json_save">
					<?php wp_nonce_field('aihl_options_json_save'); ?>
					<textarea class="aihl-oj-editor" name="aihl_options_json" id="aihl-options-json"><?php echo esc_textarea($current_json); ?></textarea>
					<p class="aihl-oj-actions">
						<button type="submit" class="button button-primary"><?php esc_html_e('Salva opzioni', AIHL_TEXT_DOMAIN); ?></button>
						<button type="button" class="button" id="aihl-oj-format"><?php esc_html_e('Formatta JSON', AIHL_TEXT_DOMAIN); ?></button>
						<button type="button" class="button" id="aihl-oj-reset"><?php esc_html_e('Ripristina JSON corrente', AIHL_TEXT_DOMAIN); ?></button>
					</p>
				</form>
			</div>

			<div class="postbox aihl-oj-help" style="padding:16px;">
				<h2><?php esc_html_e('Campi disponibili', AIHL_TEXT_DOMAIN); ?></h2>
				<p><?php esc_html_e('Solo questi campi vengono accettati. Gli altri vengono ignorati.', AIHL_TEXT_DOMAIN); ?></p>
				<div class="aihl-oj-field-groups">
				<?php foreach ($groups as $group_name => $fields) : ?>
					<section class="aihl-oj-field-group"><h3 style="text-transform:capitalize;margin:0 0 8px;"><?php echo esc_html($group_name); ?></h3>
					<table class="widefat striped">
						<tbody>
						<?php foreach ($fields as $row) :
							$def = $row['def'];
							$hint = $def['type'];
							if ($def['type'] === 'enum' && !empty($def['values'])) {
								$hint = implode(' | ', $def['values']);
							} elseif ($def['type'] === 'int') {
								$hint = 'int ' . ($def['min'] ?? '') . '-' . ($def['max'] ?? '');
							}
							?>
							<tr>
								<td><code><?php echo esc_html($row['field']); ?></code></td>
								<td style="font-size:12px;color:#646970;"><?php echo esc_html($hint); ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table></section>
				<?php endforeach; ?>
				</div>

				<h3><?php esc_html_e('Endpoint API', AIHL_TEXT_DOMAIN); ?></h3>
				<p style="font-size:12px;">
					<code>GET/POST <?php echo esc_url(rest_url('aihtml/v1/ai/options')); ?></code><br>
					<code>GET <?php echo esc_url(rest_url('aihtml/v1/ai/options/schema')); ?></code>
				</p>
			</div>
		</div>

		<script>
		(function(){
			var editor = document.getElementById('aihl-options-json');
			var original = <?php echo wp_json_encode($current_json); ?>;
			document.getElementById('aihl-oj-format').addEventListener('click', function(){
				try { editor.value = JSON.stringify(JSON.parse(editor.value), null, 2); }
				catch(e){ alert('JSON non valido: ' + e.message); }
			});
			document.getElementById('aihl-oj-reset').addEventListener('click', function(){
				editor.value = original;
			});
		})();
		</script>
	</div>
	<?php
}
