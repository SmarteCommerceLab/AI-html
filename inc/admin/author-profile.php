<?php
/**
 * Per-user author presentation owned by AI-HTML.
 */

if (!defined('ABSPATH')) {
	exit;
}

function aihl_author_style_presets(): array {
	return array(
		'inherit'    => __('Usa stile globale del sito', AIHL_TEXT_DOMAIN),
		'simple'     => __('Simple - firma testuale', AIHL_TEXT_DOMAIN),
		'compact'    => __('Compact - profilo in linea', AIHL_TEXT_DOMAIN),
		'card'       => __('Card - bio e statistiche', AIHL_TEXT_DOMAIN),
		'banner'     => __('Banner - composizione centrata', AIHL_TEXT_DOMAIN),
		'editorial'  => __('Editorial - firma magazine', AIHL_TEXT_DOMAIN),
		'enterprise' => __('Enterprise - profilo professionale', AIHL_TEXT_DOMAIN),
		'impact'     => __('Impact - alto contrasto', AIHL_TEXT_DOMAIN),
		'signature'  => __('Signature - minimale premium', AIHL_TEXT_DOMAIN),
		'none'       => __('Non mostrare il box autore', AIHL_TEXT_DOMAIN),
	);
}

function aihl_sanitize_author_style($value): string {
	$value = sanitize_key((string) $value);
	return array_key_exists($value, aihl_author_style_presets()) ? $value : 'inherit';
}

function aihl_get_author_box_style(int $user_id): string {
	$personal = aihl_sanitize_author_style(get_user_meta($user_id, 'aihl_author_box_style', true));
	if ('inherit' !== $personal) {
		return $personal;
	}

	$global = sanitize_key((string) aihtml_option_value('article_author_box_style', 'enterprise'));
	$allowed = aihl_author_style_presets();
	return isset($allowed[$global]) && 'inherit' !== $global ? $global : 'enterprise';
}

function aihl_register_author_style_meta(): void {
	register_meta('user', 'aihl_author_box_style', array(
		'type'              => 'string',
		'single'            => true,
		'default'           => 'inherit',
		'sanitize_callback' => 'aihl_sanitize_author_style',
		'auth_callback'     => static function ($allowed, $meta_key, $object_id) {
			return get_current_user_id() === (int) $object_id || current_user_can('edit_user', (int) $object_id);
		},
		'show_in_rest'      => array(
			'schema' => array(
				'type' => 'string',
				'enum' => array_keys(aihl_author_style_presets()),
			),
		),
	));
}
add_action('init', 'aihl_register_author_style_meta');

function aihl_author_profile_field(WP_User $user): void {
	if (!current_user_can('edit_user', $user->ID)) {
		return;
	}
	$current = aihl_sanitize_author_style(get_user_meta($user->ID, 'aihl_author_box_style', true));
	?>
	<h2><?php esc_html_e('AI-HTML - Stile autore', AIHL_TEXT_DOMAIN); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="aihl-author-box-style"><?php esc_html_e('Formato box autore', AIHL_TEXT_DOMAIN); ?></label></th>
			<td>
				<select id="aihl-author-box-style" name="aihl_author_box_style">
					<?php foreach (aihl_author_style_presets() as $value => $label) : ?>
						<option value="<?php echo esc_attr($value); ?>" <?php selected($current, $value); ?>><?php echo esc_html($label); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e('La scelta personale ha precedenza sul preset globale AI-HTML.', AIHL_TEXT_DOMAIN); ?></p>
			</td>
		</tr>
	</table>
	<?php
}
add_action('show_user_profile', 'aihl_author_profile_field');
add_action('edit_user_profile', 'aihl_author_profile_field');

function aihl_save_author_profile_field(int $user_id): void {
	if (!current_user_can('edit_user', $user_id) || !isset($_POST['aihl_author_box_style'])) {
		return;
	}
	update_user_meta($user_id, 'aihl_author_box_style', aihl_sanitize_author_style(wp_unslash($_POST['aihl_author_box_style'])));
}
add_action('personal_options_update', 'aihl_save_author_profile_field');
add_action('edit_user_profile_update', 'aihl_save_author_profile_field');

function aihl_register_author_profile_menu(): void {
	if (current_user_can('edit_theme_options')) {
		add_submenu_page(
			'aihl-dashboard',
			__('Profilo autore', AIHL_TEXT_DOMAIN),
			__('Profilo autore', AIHL_TEXT_DOMAIN),
			'read',
			'aihl-author-profile',
			'aihl_render_author_profile_page'
		);
		return;
	}

	add_menu_page(
		__('AI-HTML - Profilo autore', AIHL_TEXT_DOMAIN),
		__('AI-HTML Profilo', AIHL_TEXT_DOMAIN),
		'read',
		'aihl-author-profile',
		'aihl_render_author_profile_page',
		'dashicons-id-alt',
		59
	);
}
add_action('admin_menu', 'aihl_register_author_profile_menu', 20);

function aihl_render_author_profile_page(): void {
	if (!current_user_can('read')) {
		wp_die(esc_html__('Non disponi dei permessi necessari.', AIHL_TEXT_DOMAIN));
	}
	$user_id = get_current_user_id();
	$notice = '';
	if ('POST' === ($_SERVER['REQUEST_METHOD'] ?? '') && isset($_POST['aihl_author_profile_nonce'])) {
		check_admin_referer('aihl_save_author_profile', 'aihl_author_profile_nonce');
		$style = aihl_sanitize_author_style(wp_unslash($_POST['aihl_author_box_style'] ?? 'inherit'));
		update_user_meta($user_id, 'aihl_author_box_style', $style);
		$notice = __('Preferenza autore aggiornata.', AIHL_TEXT_DOMAIN);
	}
	$current = aihl_sanitize_author_style(get_user_meta($user_id, 'aihl_author_box_style', true));
	?>
	<div class="wrap aihl-author-profile-admin">
		<h1><?php esc_html_e('Il tuo stile autore', AIHL_TEXT_DOMAIN); ?></h1>
		<p><?php esc_html_e('Scegli come presentare firma, biografia e autorevolezza nei tuoi articoli.', AIHL_TEXT_DOMAIN); ?></p>
		<?php if ($notice) : ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($notice); ?></p></div><?php endif; ?>
		<form method="post">
			<?php wp_nonce_field('aihl_save_author_profile', 'aihl_author_profile_nonce'); ?>
			<div class="aihl-author-preset-grid">
				<?php foreach (aihl_author_style_presets() as $value => $label) : ?>
					<label class="aihl-author-preset-card">
						<input type="radio" name="aihl_author_box_style" value="<?php echo esc_attr($value); ?>" <?php checked($current, $value); ?>>
						<strong><?php echo esc_html($label); ?></strong>
						<span><?php echo esc_html(sprintf(__('Preset: %s', AIHL_TEXT_DOMAIN), $value)); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
			<?php submit_button(__('Salva stile autore', AIHL_TEXT_DOMAIN)); ?>
		</form>
	</div>
	<style>
		.aihl-author-profile-admin{max-width:1100px}.aihl-author-preset-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin:28px 0}.aihl-author-preset-card{display:grid;grid-template-columns:auto 1fr;gap:6px 12px;align-items:start;padding:20px;border:1px solid #dcdcde;border-radius:12px;background:#fff;cursor:pointer}.aihl-author-preset-card:has(input:checked){border-color:#3858e9;box-shadow:0 0 0 2px #3858e9}.aihl-author-preset-card input{grid-row:1/3;margin-top:3px}.aihl-author-preset-card span{color:#646970}
	</style>
	<?php
}

function aihl_register_author_profile_rest(): void {
	register_rest_route('aihtml/v1', '/ai/author-profile', array(
		array(
			'methods'             => WP_REST_Server::READABLE,
			'permission_callback' => static function () { return is_user_logged_in(); },
			'callback'            => static function () {
				$user_id = get_current_user_id();
				return rest_ensure_response(array(
					'user_id'   => $user_id,
					'selected'  => aihl_sanitize_author_style(get_user_meta($user_id, 'aihl_author_box_style', true)),
					'resolved'  => aihl_get_author_box_style($user_id),
					'presets'   => aihl_author_style_presets(),
				));
			},
		),
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'permission_callback' => static function () { return is_user_logged_in(); },
			'callback'            => static function (WP_REST_Request $request) {
				$user_id = get_current_user_id();
				$style = aihl_sanitize_author_style($request->get_param('style'));
				update_user_meta($user_id, 'aihl_author_box_style', $style);
				return rest_ensure_response(array('updated' => true, 'selected' => $style, 'resolved' => aihl_get_author_box_style($user_id)));
			},
		),
	));
}
add_action('rest_api_init', 'aihl_register_author_profile_rest');
