<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Page Background System
 *
 * Per-page background configuration: color, image, pattern, decorative element.
 * Defaults from Customizer, per-page override via post meta, REST API configurable.
 */

// ── Meta box registration ──

add_action('add_meta_boxes', function () {
	$post_types = apply_filters('aihl_page_background_post_types', array('page'));
	foreach ($post_types as $pt) {
		add_meta_box(
			'aihl_page_background',
			__('Sfondo Pagina', AIHL_TEXT_DOMAIN),
			'aihl_page_background_metabox_render',
			$pt,
			'side',
			'default'
		);
	}
});

function aihl_page_background_metabox_render($post) {
	wp_nonce_field('aihl_page_bg_nonce', '_aihl_page_bg_nonce');
	$meta = aihl_get_page_background_meta($post->ID);
	$bg_types = array(
		'default' => __('Default (da Customizer)', AIHL_TEXT_DOMAIN),
		'color'   => __('Colore', AIHL_TEXT_DOMAIN),
		'image'   => __('Immagine', AIHL_TEXT_DOMAIN),
		'pattern' => __('Pattern', AIHL_TEXT_DOMAIN),
	);
	$patterns = aihl_page_background_patterns();
	?>
	<p>
		<label for="aihl_bg_type"><strong><?php esc_html_e('Tipo sfondo', AIHL_TEXT_DOMAIN); ?></strong></label><br>
		<select id="aihl_bg_type" name="aihl_bg[type]" style="width:100%">
			<?php foreach ($bg_types as $val => $label) : ?>
				<option value="<?php echo esc_attr($val); ?>" <?php selected($meta['type'], $val); ?>><?php echo esc_html($label); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="aihl_bg_color"><strong><?php esc_html_e('Colore sfondo', AIHL_TEXT_DOMAIN); ?></strong></label><br>
		<input type="text" id="aihl_bg_color" name="aihl_bg[color]" value="<?php echo esc_attr($meta['color']); ?>" class="widefat" placeholder="#f0f0f0">
	</p>
	<p>
		<label for="aihl_bg_image"><strong><?php esc_html_e('Immagine sfondo URL', AIHL_TEXT_DOMAIN); ?></strong></label><br>
		<input type="url" id="aihl_bg_image" name="aihl_bg[image]" value="<?php echo esc_url($meta['image']); ?>" class="widefat" placeholder="https://...">
	</p>
	<p>
		<label for="aihl_bg_image_opacity"><strong><?php esc_html_e('Opacità immagine (0-1)', AIHL_TEXT_DOMAIN); ?></strong></label><br>
		<input type="number" id="aihl_bg_image_opacity" name="aihl_bg[image_opacity]" value="<?php echo esc_attr($meta['image_opacity']); ?>" min="0" max="1" step="0.05" class="widefat">
	</p>
	<p>
		<label for="aihl_bg_image_size"><strong><?php esc_html_e('Dimensione immagine', AIHL_TEXT_DOMAIN); ?></strong></label><br>
		<select id="aihl_bg_image_size" name="aihl_bg[image_size]" style="width:100%">
			<?php foreach (array('cover' => 'Cover', 'contain' => 'Contain', 'auto' => 'Auto') as $v => $l) : ?>
				<option value="<?php echo esc_attr($v); ?>" <?php selected($meta['image_size'], $v); ?>><?php echo esc_html($l); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="aihl_bg_pattern"><strong><?php esc_html_e('Pattern', AIHL_TEXT_DOMAIN); ?></strong></label><br>
		<select id="aihl_bg_pattern" name="aihl_bg[pattern]" style="width:100%">
			<?php foreach ($patterns as $val => $label) : ?>
				<option value="<?php echo esc_attr($val); ?>" <?php selected($meta['pattern'], $val); ?>><?php echo esc_html($label); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="aihl_bg_overlay_color"><strong><?php esc_html_e('Overlay colore', AIHL_TEXT_DOMAIN); ?></strong></label><br>
		<input type="text" id="aihl_bg_overlay_color" name="aihl_bg[overlay_color]" value="<?php echo esc_attr($meta['overlay_color']); ?>" class="widefat" placeholder="#1a3a5c">
	</p>
	<p>
		<label for="aihl_bg_overlay_opacity"><strong><?php esc_html_e('Overlay opacità (0-1)', AIHL_TEXT_DOMAIN); ?></strong></label><br>
		<input type="number" id="aihl_bg_overlay_opacity" name="aihl_bg[overlay_opacity]" value="<?php echo esc_attr($meta['overlay_opacity']); ?>" min="0" max="1" step="0.05" class="widefat">
	</p>
	<?php
}

add_action('save_post', function ($post_id) {
	if (!isset($_POST['_aihl_page_bg_nonce']) || !wp_verify_nonce($_POST['_aihl_page_bg_nonce'], 'aihl_page_bg_nonce')) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}
	$raw = isset($_POST['aihl_bg']) && is_array($_POST['aihl_bg']) ? $_POST['aihl_bg'] : array();
	$sanitized = aihl_sanitize_page_background($raw);

	if ($sanitized['type'] === 'default' && $sanitized['pattern'] === 'none' && $sanitized['overlay_color'] === '') {
		delete_post_meta($post_id, '_aihl_page_background');
	} else {
		update_post_meta($post_id, '_aihl_page_background', $sanitized);
	}
});

// ── Data helpers ──

function aihl_page_background_patterns(): array {
	return array(
		'none'     => __('Nessuno', AIHL_TEXT_DOMAIN),
		'dots'     => __('Puntini (dot grid)', AIHL_TEXT_DOMAIN),
		'grid'     => __('Griglia', AIHL_TEXT_DOMAIN),
		'diagonal' => __('Linee diagonali', AIHL_TEXT_DOMAIN),
		'cross'    => __('Croce (cross-hatch)', AIHL_TEXT_DOMAIN),
	);
}

function aihl_page_background_defaults(): array {
	return array(
		'type'            => (string) aihtml_option_value('page_bg_type', 'default'),
		'color'           => (string) aihtml_option_value('page_bg_color', ''),
		'image'           => (string) aihtml_option_value('page_bg_image', ''),
		'image_opacity'   => (float)  aihtml_option_value('page_bg_image_opacity', 1),
		'image_size'      => (string) aihtml_option_value('page_bg_image_size', 'cover'),
		'pattern'         => (string) aihtml_option_value('page_bg_pattern', 'none'),
		'overlay_color'   => (string) aihtml_option_value('page_bg_overlay_color', ''),
		'overlay_opacity' => (float)  aihtml_option_value('page_bg_overlay_opacity', 0.18),
	);
}

function aihl_get_page_background_meta(int $post_id): array {
	$defaults = aihl_page_background_defaults();
	$meta = get_post_meta($post_id, '_aihl_page_background', true);
	if (!is_array($meta)) {
		return $defaults;
	}
	return wp_parse_args($meta, $defaults);
}

function aihl_sanitize_page_background(array $input): array {
	$valid_types = array('default', 'color', 'image', 'pattern');
	$valid_patterns = array_keys(aihl_page_background_patterns());
	$valid_sizes = array('cover', 'contain', 'auto');

	return array(
		'type'            => in_array($input['type'] ?? '', $valid_types, true) ? $input['type'] : 'default',
		'color'           => sanitize_hex_color($input['color'] ?? '') ?: '',
		'image'           => esc_url_raw($input['image'] ?? ''),
		'image_opacity'   => max(0, min(1, (float) ($input['image_opacity'] ?? 1))),
		'image_size'      => in_array($input['image_size'] ?? '', $valid_sizes, true) ? $input['image_size'] : 'cover',
		'pattern'         => in_array($input['pattern'] ?? '', $valid_patterns, true) ? $input['pattern'] : 'none',
		'overlay_color'   => sanitize_hex_color($input['overlay_color'] ?? '') ?: '',
		'overlay_opacity' => max(0, min(1, (float) ($input['overlay_opacity'] ?? 0.18))),
	);
}

// ── Frontend rendering ──

function aihl_page_background_open_wrapper(): void {
	if (!is_singular()) {
		return;
	}
	$post_id = get_queried_object_id();
	if ($post_id <= 0) {
		return;
	}

	$bg = aihl_get_page_background_meta($post_id);
	if ($bg['type'] === 'default' && $bg['pattern'] === 'none' && $bg['overlay_color'] === '') {
		return;
	}

	$resolved = aihl_resolve_page_background($bg);
	if ($resolved['type'] === 'default' && $resolved['pattern'] === 'none') {
		return;
	}

	$wrapper_style = '';
	$classes = array('aihl-page-bg-wrap');

	if ($resolved['type'] === 'color' && $resolved['color'] !== '') {
		$wrapper_style .= 'background-color:' . esc_attr($resolved['color']) . ';';
		$classes[] = 'aihl-page-bg-color';
	}
	if ($resolved['type'] === 'image' && $resolved['image'] !== '') {
		$wrapper_style .= 'background-image:url(' . esc_url($resolved['image']) . ');';
		$wrapper_style .= 'background-size:' . esc_attr($resolved['image_size']) . ';';
		$wrapper_style .= 'background-position:center center;background-repeat:no-repeat;';
		if ((float) $resolved['image_opacity'] < 1) {
			$wrapper_style .= '--aihl-bg-image-opacity:' . esc_attr($resolved['image_opacity']) . ';';
			$classes[] = 'aihl-page-bg-image-fade';
		}
		$classes[] = 'aihl-page-bg-image';
	}

	if ($resolved['pattern'] !== 'none') {
		$classes[] = 'aihl-page-bg-pattern-' . sanitize_html_class($resolved['pattern']);
	}

	$has_overlay = $resolved['overlay_color'] !== '' && (float) $resolved['overlay_opacity'] > 0;
	if ($has_overlay) {
		$classes[] = 'aihl-page-bg-has-overlay';
	}

	echo '<div class="' . esc_attr(implode(' ', $classes)) . '"';
	if ($wrapper_style !== '') {
		echo ' style="' . esc_attr($wrapper_style) . '"';
	}
	echo '>';

	if ($has_overlay) {
		$oc = $resolved['overlay_color'];
		$oo = $resolved['overlay_opacity'];
		echo '<div class="aihl-page-bg-overlay" style="background-color:' . esc_attr($oc) . ';opacity:' . esc_attr($oo) . '"></div>';
	}
}

function aihl_page_background_close_wrapper(): void {
	if (!is_singular()) {
		return;
	}
	$post_id = get_queried_object_id();
	if ($post_id <= 0) {
		return;
	}
	$bg = aihl_get_page_background_meta($post_id);
	if ($bg['type'] === 'default' && $bg['pattern'] === 'none' && $bg['overlay_color'] === '') {
		return;
	}
	$resolved = aihl_resolve_page_background($bg);
	if ($resolved['type'] === 'default' && $resolved['pattern'] === 'none') {
		return;
	}
	echo '</div><!-- .aihl-page-bg-wrap -->';
}

function aihl_resolve_page_background(array $bg): array {
	if ($bg['type'] === 'default') {
		$defaults = aihl_page_background_defaults();
		if ($defaults['type'] !== 'default') {
			$bg = wp_parse_args(
				array_filter($bg, function ($v) { return $v !== '' && $v !== 'default' && $v !== 'none'; }),
				$defaults
			);
		}
	}
	return $bg;
}

// ── Hook into template rendering ──

add_action('aihl_before_main_content', 'aihl_page_background_open_wrapper');
add_action('aihl_after_main_content', 'aihl_page_background_close_wrapper');

// ── REST API: per-page background ──

add_action('rest_api_init', function () {
	register_rest_route('aihtml/v1/ai', '/pages/(?P<id>\d+)/background', array(
		array(
			'methods'             => 'GET',
			'callback'            => function (WP_REST_Request $request) {
				$post_id = (int) $request['id'];
				if (!get_post($post_id)) {
					return new WP_Error('not_found', 'Page not found', array('status' => 404));
				}
				return new WP_REST_Response(aihl_get_page_background_meta($post_id));
			},
			'permission_callback' => function () {
				return function_exists('smart_ai_can_read') ? smart_ai_can_read() : current_user_can('manage_options');
			},
		),
		array(
			'methods'             => 'PUT',
			'callback'            => function (WP_REST_Request $request) {
				$post_id = (int) $request['id'];
				if (!get_post($post_id)) {
					return new WP_Error('not_found', 'Page not found', array('status' => 404));
				}
				$input = $request->get_json_params();
				if (!is_array($input)) {
					return new WP_Error('bad_request', 'JSON body required', array('status' => 400));
				}
				$sanitized = aihl_sanitize_page_background($input);
				update_post_meta($post_id, '_aihl_page_background', $sanitized);
				return new WP_REST_Response(array('updated' => true, 'background' => $sanitized));
			},
			'permission_callback' => function () {
				return function_exists('smart_ai_can_write') ? smart_ai_can_write() : current_user_can('manage_options');
			},
		),
		array(
			'methods'             => 'DELETE',
			'callback'            => function (WP_REST_Request $request) {
				$post_id = (int) $request['id'];
				delete_post_meta($post_id, '_aihl_page_background');
				return new WP_REST_Response(array('deleted' => true));
			},
			'permission_callback' => function () {
				return function_exists('smart_ai_can_write') ? smart_ai_can_write() : current_user_can('manage_options');
			},
		),
	));

	register_rest_route('aihtml/v1/ai', '/page-background/patterns', array(
		'methods'             => 'GET',
		'callback'            => function () {
			return new WP_REST_Response(aihl_page_background_patterns());
		},
		'permission_callback' => function () {
			return function_exists('smart_ai_can_read') ? smart_ai_can_read() : current_user_can('manage_options');
		},
	));
});
