<?php
if (!defined('ABSPATH')) {
	exit;
}

add_filter('wp_setup_nav_menu_item', function($item) {
	$item->aihl_menu_mode = get_post_meta($item->ID, '_aihl_menu_mode', true);
	$item->aihl_menu_rich_layout = get_post_meta($item->ID, '_aihl_menu_rich_layout', true);
	$item->aihl_menu_icon = get_post_meta($item->ID, '_aihl_menu_icon', true);
	$item->aihl_menu_badge = get_post_meta($item->ID, '_aihl_menu_badge', true);
	$item->aihl_menu_badge_color = get_post_meta($item->ID, '_aihl_menu_badge_color', true);
	$item->aihl_menu_subtitle = get_post_meta($item->ID, '_aihl_menu_subtitle', true);
	$item->aihl_menu_eyebrow = get_post_meta($item->ID, '_aihl_menu_eyebrow', true);
	$item->aihl_menu_image = get_post_meta($item->ID, '_aihl_menu_image', true);
	$item->aihl_menu_image_id = get_post_meta($item->ID, '_aihl_menu_image_id', true);
	$item->aihl_menu_highlight = get_post_meta($item->ID, '_aihl_menu_highlight', true);
	$item->aihl_menu_color = get_post_meta($item->ID, '_aihl_menu_color', true);
	$item->aihl_menu_item_cta = get_post_meta($item->ID, '_aihl_menu_item_cta', true);
	$item->aihl_menu_rich_content = get_post_meta($item->ID, '_aihl_menu_rich_content', true);
	$item->aihl_menu_rich_cta_label = get_post_meta($item->ID, '_aihl_menu_rich_cta_label', true);
	$item->aihl_menu_rich_cta_url = get_post_meta($item->ID, '_aihl_menu_rich_cta_url', true);
	$item->aihl_menu_rich_footer = get_post_meta($item->ID, '_aihl_menu_rich_footer', true);
	return $item;
});

add_action('admin_enqueue_scripts', function($hook) {
	if ('nav-menus.php' !== $hook) {
		return;
	}

	wp_enqueue_media();

	$admin_css = <<<'CSS'
.aihl-menu-fields-section{margin:8px 0 4px;padding:8px 10px;background:#f0f0f1;border-left:3px solid #2271b1;font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#1d2327}
.aihl-menu-fields-section.aihl-section-rich{border-left-color:#d63638}
.aihl-menu-fields-section.aihl-section-visual{border-left-color:#00a32a}
.aihl-mf-color-row{display:flex;align-items:center;gap:8px}
.aihl-mf-color-row input[type="color"]{width:36px;height:28px;padding:0;border:1px solid #8c8f94;border-radius:3px;cursor:pointer}
.aihl-mf-color-row input[type="text"]{flex:1}
CSS;
	wp_add_inline_style('wp-admin', $admin_css);

	$script = <<<'JS'
(function($){
	'use strict';

	function bindMediaPicker() {
		$(document).off('click.aihlMenuImage').on('click.aihlMenuImage', '.aihl-menu-image-select', function(e){
			e.preventDefault();
			var $button = $(this);
			var targetId = $button.attr('data-target');
			if (!targetId) {
				return;
			}
			var $input = $('#' + targetId);
			if (!$input.length) {
				return;
			}

			var frame = wp.media({
				title: 'Seleziona immagine menu',
				button: { text: 'Usa questa immagine' },
				multiple: false,
				library: { type: 'image' }
			});

			frame.on('select', function(){
				var attachment = frame.state().get('selection').first().toJSON();
				if (!attachment || !attachment.url) {
					return;
				}
				var $idInput = $('#' + targetId + '-id');
				var $preview = $('#' + targetId + '-preview');
				$input.val(attachment.url).trigger('change');
				if ($idInput.length && attachment.id) {
					$idInput.val(String(attachment.id)).trigger('change');
				}
				if ($preview.length) {
					$preview.html('<img src="' + attachment.url + '" alt="" style="max-width:100%;height:auto;border-radius:4px;">');
				}
			});

			frame.open();
		});
	}

	function syncColorInputs() {
		$(document).on('input.aihlColor', '.aihl-mf-color-picker', function(){
			$(this).closest('.aihl-mf-color-row').find('.aihl-mf-color-text').val(this.value).trigger('change');
		});
		$(document).on('input.aihlColor', '.aihl-mf-color-text', function(){
			var val = $.trim(this.value);
			if (/^#[0-9a-fA-F]{3,8}$/.test(val)) {
				$(this).closest('.aihl-mf-color-row').find('.aihl-mf-color-picker').val(val);
			}
		});
	}

	$(bindMediaPicker);
	$(syncColorInputs);

	$(document).off('click.aihlMenuImageRemove').on('click.aihlMenuImageRemove', '.aihl-menu-image-remove', function(e){
		e.preventDefault();
		var targetId = $(this).attr('data-target');
		if (!targetId) {
			return;
		}
		$('#' + targetId).val('').trigger('change');
		$('#' + targetId + '-id').val('').trigger('change');
		$('#' + targetId + '-preview').empty();
	});

	$(document).on('menu-item-added', bindMediaPicker);
})(jQuery);
JS;
	wp_add_inline_script('jquery', $script, 'after');
});

add_action('wp_nav_menu_item_custom_fields', function($item_id, $item, $depth) {
	$mode = get_post_meta($item_id, '_aihl_menu_mode', true);
	$rich_layout = get_post_meta($item_id, '_aihl_menu_rich_layout', true);
	$icon = get_post_meta($item_id, '_aihl_menu_icon', true);
	$badge = get_post_meta($item_id, '_aihl_menu_badge', true);
	$badge_color = get_post_meta($item_id, '_aihl_menu_badge_color', true);
	$subtitle = get_post_meta($item_id, '_aihl_menu_subtitle', true);
	$eyebrow = get_post_meta($item_id, '_aihl_menu_eyebrow', true);
	$image = get_post_meta($item_id, '_aihl_menu_image', true);
	$image_id = get_post_meta($item_id, '_aihl_menu_image_id', true);
	$highlight = get_post_meta($item_id, '_aihl_menu_highlight', true);
	$color = get_post_meta($item_id, '_aihl_menu_color', true);
	$item_cta = get_post_meta($item_id, '_aihl_menu_item_cta', true);
	$rich_content = get_post_meta($item_id, '_aihl_menu_rich_content', true);
	?>

	<!-- â•â•â• SEZIONE: Comportamento â•â•â• -->
	<div class="aihl-menu-fields-section"><?php esc_html_e('Comportamento', AIHL_TEXT_DOMAIN); ?></div>

	<p class="description description-wide">
		<label for="edit-menu-item-aihl-menu-mode-<?php echo esc_attr($item_id); ?>">
			<?php esc_html_e('Menu Mode', AIHL_TEXT_DOMAIN); ?><br>
			<select id="edit-menu-item-aihl-menu-mode-<?php echo esc_attr($item_id); ?>" class="widefat code edit-menu-item-custom" name="aihl_menu_mode[<?php echo esc_attr($item_id); ?>]">
				<option value="" <?php selected($mode, ''); ?>><?php esc_html_e('Auto (Rich se ha figli)', AIHL_TEXT_DOMAIN); ?></option>
				<option value="simple" <?php selected($mode, 'simple'); ?>><?php esc_html_e('Simple (dropdown classico)', AIHL_TEXT_DOMAIN); ?></option>
				<option value="dropdown" <?php selected($mode, 'dropdown'); ?>><?php esc_html_e('Advanced Dropdown (directory/panel)', AIHL_TEXT_DOMAIN); ?></option>
				<option value="rich" <?php selected($mode, 'rich'); ?>><?php esc_html_e('Rich / Mega (pannello full-width)', AIHL_TEXT_DOMAIN); ?></option>
			</select>
		</label>
	</p>
	<p class="description description-thin">
		<label for="edit-menu-item-aihl-menu-item-cta-<?php echo esc_attr($item_id); ?>">
			<?php esc_html_e('Stile voce', AIHL_TEXT_DOMAIN); ?><br>
			<select id="edit-menu-item-aihl-menu-item-cta-<?php echo esc_attr($item_id); ?>" class="widefat code edit-menu-item-custom" name="aihl_menu_item_cta[<?php echo esc_attr($item_id); ?>]">
				<option value="" <?php selected($item_cta, ''); ?>><?php esc_html_e('Link normale', AIHL_TEXT_DOMAIN); ?></option>
				<option value="btn-primary" <?php selected($item_cta, 'btn-primary'); ?>><?php esc_html_e('Bottone Primary', AIHL_TEXT_DOMAIN); ?></option>
				<option value="btn-outline-primary" <?php selected($item_cta, 'btn-outline-primary'); ?>><?php esc_html_e('Bottone Outline', AIHL_TEXT_DOMAIN); ?></option>
				<option value="btn-secondary" <?php selected($item_cta, 'btn-secondary'); ?>><?php esc_html_e('Bottone Secondary', AIHL_TEXT_DOMAIN); ?></option>
			</select>
			<span class="description"><?php esc_html_e('Trasforma la voce in un bottone CTA nel menu', AIHL_TEXT_DOMAIN); ?></span>
		</label>
	</p>

	<!-- â•â•â• SEZIONE: Contenuto visivo â•â•â• -->
	<div class="aihl-menu-fields-section aihl-section-visual"><?php esc_html_e('Contenuto visivo', AIHL_TEXT_DOMAIN); ?></div>

	<p class="description description-thin">
		<label for="edit-menu-item-aihl-menu-icon-<?php echo esc_attr($item_id); ?>">
			<?php esc_html_e('Icona (Font Awesome)', AIHL_TEXT_DOMAIN); ?><br>
			<input type="text" id="edit-menu-item-aihl-menu-icon-<?php echo esc_attr($item_id); ?>" class="widefat code edit-menu-item-custom" name="aihl_menu_icon[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($icon); ?>" placeholder="fa-solid fa-briefcase">
			<span class="description"><?php esc_html_e('fa-solid fa-briefcase, fa-solid fa-chart-line, fa-regular fa-circle-check', AIHL_TEXT_DOMAIN); ?></span>
		</label>
	</p>
	<p class="description description-thin">
		<label for="edit-menu-item-aihl-menu-badge-<?php echo esc_attr($item_id); ?>">
			<?php esc_html_e('Badge', AIHL_TEXT_DOMAIN); ?><br>
			<input type="text" id="edit-menu-item-aihl-menu-badge-<?php echo esc_attr($item_id); ?>" class="widefat code edit-menu-item-custom" name="aihl_menu_badge[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($badge); ?>" placeholder="New">
			<span class="description"><?php esc_html_e('New, Pro, Hot, Beta, 2026', AIHL_TEXT_DOMAIN); ?></span>
		</label>
	</p>
	<p class="description description-thin">
		<label>
			<?php esc_html_e('Colore badge', AIHL_TEXT_DOMAIN); ?><br>
			<span class="aihl-mf-color-row">
				<input type="color" class="aihl-mf-color-picker" value="<?php echo esc_attr($badge_color !== '' ? $badge_color : '#0d6efd'); ?>">
				<input type="text" class="widefat code edit-menu-item-custom aihl-mf-color-text" name="aihl_menu_badge_color[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($badge_color); ?>" placeholder="<?php esc_attr_e('Vuoto = primary', AIHL_TEXT_DOMAIN); ?>">
			</span>
		</label>
	</p>
	<p class="description description-wide">
		<label for="edit-menu-item-aihl-menu-eyebrow-<?php echo esc_attr($item_id); ?>">
			<?php esc_html_e('Eyebrow (sopratitolo)', AIHL_TEXT_DOMAIN); ?><br>
			<input type="text" id="edit-menu-item-aihl-menu-eyebrow-<?php echo esc_attr($item_id); ?>" class="widefat code edit-menu-item-custom" name="aihl_menu_eyebrow[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($eyebrow); ?>" placeholder="Solutions, Products, Docs">
		</label>
	</p>
	<p class="description description-wide">
		<label for="edit-menu-item-aihl-menu-subtitle-<?php echo esc_attr($item_id); ?>">
			<?php esc_html_e('Sottotitolo', AIHL_TEXT_DOMAIN); ?><br>
			<input type="text" id="edit-menu-item-aihl-menu-subtitle-<?php echo esc_attr($item_id); ?>" class="widefat code edit-menu-item-custom" name="aihl_menu_subtitle[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($subtitle); ?>" placeholder="<?php esc_attr_e('Descrizione breve 35-70 caratteri', AIHL_TEXT_DOMAIN); ?>">
		</label>
	</p>
	<p class="description description-wide">
		<label for="edit-menu-item-aihl-menu-image-<?php echo esc_attr($item_id); ?>">
			<?php esc_html_e('Immagine', AIHL_TEXT_DOMAIN); ?><br>
			<input type="url" id="edit-menu-item-aihl-menu-image-<?php echo esc_attr($item_id); ?>" class="widefat code edit-menu-item-custom" name="aihl_menu_image[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($image); ?>" placeholder="https://example.com/menu-image.webp">
			<input type="hidden" id="edit-menu-item-aihl-menu-image-<?php echo esc_attr($item_id); ?>-id" name="aihl_menu_image_id[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($image_id); ?>">
		</label>
		<button type="button" class="button button-secondary aihl-menu-image-select mt-2" data-target="edit-menu-item-aihl-menu-image-<?php echo esc_attr($item_id); ?>">
			<?php esc_html_e('Seleziona da Media Library', AIHL_TEXT_DOMAIN); ?>
		</button>
		<button type="button" class="button aihl-menu-image-remove mt-2" data-target="edit-menu-item-aihl-menu-image-<?php echo esc_attr($item_id); ?>">
			<?php esc_html_e('Rimuovi', AIHL_TEXT_DOMAIN); ?>
		</button>
		<div id="edit-menu-item-aihl-menu-image-<?php echo esc_attr($item_id); ?>-preview" class="mt-2">
			<?php if (!empty($image)) : ?>
				<img src="<?php echo esc_url($image); ?>" alt="" style="max-width:100%;height:auto;border-radius:4px;">
			<?php endif; ?>
		</div>
	</p>
	<p class="description description-thin">
		<label>
			<?php esc_html_e('Colore accento voce', AIHL_TEXT_DOMAIN); ?><br>
			<span class="aihl-mf-color-row">
				<input type="color" class="aihl-mf-color-picker" value="<?php echo esc_attr($color !== '' ? $color : '#0d6efd'); ?>">
				<input type="text" class="widefat code edit-menu-item-custom aihl-mf-color-text" name="aihl_menu_color[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($color); ?>" placeholder="<?php esc_attr_e('Vuoto = tema default', AIHL_TEXT_DOMAIN); ?>">
			</span>
			<span class="description"><?php esc_html_e('Colore icona e bordo hover per questa voce', AIHL_TEXT_DOMAIN); ?></span>
		</label>
	</p>
	<p class="description description-thin">
		<label for="edit-menu-item-aihl-menu-highlight-<?php echo esc_attr($item_id); ?>">
			<input type="checkbox" id="edit-menu-item-aihl-menu-highlight-<?php echo esc_attr($item_id); ?>" class="edit-menu-item-custom" name="aihl_menu_highlight[<?php echo esc_attr($item_id); ?>]" value="1" <?php checked($highlight, '1'); ?>>
			<?php esc_html_e('Evidenzia questa voce (sfondo accent)', AIHL_TEXT_DOMAIN); ?>
		</label>
	</p>

	<?php if ((int) $depth === 0) : ?>
	<!-- â•â•â• SEZIONE: Rich Menu (solo livello 0) â•â•â• -->
	<div class="aihl-menu-fields-section aihl-section-rich"><?php esc_html_e('Rich / Mega Menu (livello 0)', AIHL_TEXT_DOMAIN); ?></div>

	<p class="description description-wide">
		<label for="edit-menu-item-aihl-menu-rich-layout-<?php echo esc_attr($item_id); ?>">
			<?php esc_html_e('Layout Rich', AIHL_TEXT_DOMAIN); ?><br>
			<select id="edit-menu-item-aihl-menu-rich-layout-<?php echo esc_attr($item_id); ?>" class="widefat code edit-menu-item-custom" name="aihl_menu_rich_layout[<?php echo esc_attr($item_id); ?>]">
				<option value="split" <?php selected($rich_layout, 'split'); ?>><?php esc_html_e('Split â€” lista link + pannello laterale', AIHL_TEXT_DOMAIN); ?></option>
				<option value="compact" <?php selected($rich_layout, 'compact'); ?>><?php esc_html_e('Compact â€” lista densa + pannello', AIHL_TEXT_DOMAIN); ?></option>
				<option value="columns" <?php selected($rich_layout, 'columns'); ?>><?php esc_html_e('Columns â€” 2 colonne senza pannello', AIHL_TEXT_DOMAIN); ?></option>
				<option value="grid" <?php selected($rich_layout, 'grid'); ?>><?php esc_html_e('Grid â€” card 3 colonne centrate', AIHL_TEXT_DOMAIN); ?></option>
				<option value="tabbed" <?php selected($rich_layout, 'tabbed'); ?>><?php esc_html_e('Tabbed â€” item come tab con bordo', AIHL_TEXT_DOMAIN); ?></option>
				<option value="directory" <?php selected($rich_layout, 'directory'); ?>><?php esc_html_e('Directory - multi-colonna tipo marketplace', AIHL_TEXT_DOMAIN); ?></option>
				<option value="panel" <?php selected($rich_layout, 'panel'); ?>><?php esc_html_e('Panel - dropdown compatto evoluto', AIHL_TEXT_DOMAIN); ?></option>
				<option value="featured" <?php selected($rich_layout, 'featured'); ?>><?php esc_html_e('Featured â€” immagine grande + overlay testo', AIHL_TEXT_DOMAIN); ?></option>
				<option value="showcase" <?php selected($rich_layout, 'showcase'); ?>><?php esc_html_e('Showcase â€” hero card con bg image', AIHL_TEXT_DOMAIN); ?></option>
			</select>
		</label>
	</p>
	<p class="description description-wide">
		<label for="edit-menu-item-aihl-menu-rich-content-<?php echo esc_attr($item_id); ?>">
			<?php esc_html_e('Pannello laterale (HTML)', AIHL_TEXT_DOMAIN); ?><br>
			<textarea id="edit-menu-item-aihl-menu-rich-content-<?php echo esc_attr($item_id); ?>" class="widefat code edit-menu-item-custom" rows="4" name="aihl_menu_rich_content[<?php echo esc_attr($item_id); ?>]" placeholder="<?php esc_attr_e('HTML per pannello laterale (layout Split/Compact)', AIHL_TEXT_DOMAIN); ?>"><?php echo esc_textarea($rich_content); ?></textarea>
		</label>
	</p>
	<?php
	$rich_cta_label = get_post_meta($item_id, '_aihl_menu_rich_cta_label', true);
	$rich_cta_url = get_post_meta($item_id, '_aihl_menu_rich_cta_url', true);
	$rich_footer_html = get_post_meta($item_id, '_aihl_menu_rich_footer', true);
	?>
	<p class="description description-thin">
		<label for="edit-menu-item-aihl-menu-rich-cta-label-<?php echo esc_attr($item_id); ?>">
			<?php esc_html_e('CTA nel mega menu (label)', AIHL_TEXT_DOMAIN); ?><br>
			<input type="text" id="edit-menu-item-aihl-menu-rich-cta-label-<?php echo esc_attr($item_id); ?>" class="widefat code edit-menu-item-custom" name="aihl_menu_rich_cta_label[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($rich_cta_label); ?>" placeholder="Scopri tutte le soluzioni &rarr;">
		</label>
	</p>
	<p class="description description-thin">
		<label for="edit-menu-item-aihl-menu-rich-cta-url-<?php echo esc_attr($item_id); ?>">
			<?php esc_html_e('CTA nel mega menu (URL)', AIHL_TEXT_DOMAIN); ?><br>
			<input type="url" id="edit-menu-item-aihl-menu-rich-cta-url-<?php echo esc_attr($item_id); ?>" class="widefat code edit-menu-item-custom" name="aihl_menu_rich_cta_url[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($rich_cta_url); ?>" placeholder="https://example.com/solutions">
		</label>
	</p>
	<p class="description description-wide">
		<label for="edit-menu-item-aihl-menu-rich-footer-<?php echo esc_attr($item_id); ?>">
			<?php esc_html_e('Footer mega menu (HTML)', AIHL_TEXT_DOMAIN); ?><br>
			<textarea id="edit-menu-item-aihl-menu-rich-footer-<?php echo esc_attr($item_id); ?>" class="widefat code edit-menu-item-custom" rows="2" name="aihl_menu_rich_footer[<?php echo esc_attr($item_id); ?>]" placeholder="<?php esc_attr_e('Barra in fondo al mega menu (promo, link secondari)', AIHL_TEXT_DOMAIN); ?>"><?php echo esc_textarea($rich_footer_html); ?></textarea>
		</label>
	</p>
	<?php endif; ?>
	<?php
}, 10, 3);

add_action('wp_update_nav_menu_item', function($menu_id, $menu_item_db_id) {
	$map = array(
		'_aihl_menu_mode' => 'aihl_menu_mode',
		'_aihl_menu_rich_layout' => 'aihl_menu_rich_layout',
		'_aihl_menu_icon' => 'aihl_menu_icon',
		'_aihl_menu_badge' => 'aihl_menu_badge',
		'_aihl_menu_badge_color' => 'aihl_menu_badge_color',
		'_aihl_menu_subtitle' => 'aihl_menu_subtitle',
		'_aihl_menu_eyebrow' => 'aihl_menu_eyebrow',
		'_aihl_menu_image' => 'aihl_menu_image',
		'_aihl_menu_image_id' => 'aihl_menu_image_id',
		'_aihl_menu_color' => 'aihl_menu_color',
		'_aihl_menu_item_cta' => 'aihl_menu_item_cta',
		'_aihl_menu_rich_content' => 'aihl_menu_rich_content',
		'_aihl_menu_rich_cta_label' => 'aihl_menu_rich_cta_label',
		'_aihl_menu_rich_cta_url' => 'aihl_menu_rich_cta_url',
		'_aihl_menu_rich_footer' => 'aihl_menu_rich_footer',
	);

	foreach ($map as $meta_key => $post_key) {
		$value = '';
		if (isset($_POST[$post_key][$menu_item_db_id])) {
			$value = wp_unslash($_POST[$post_key][$menu_item_db_id]);
		}

		if ('_aihl_menu_rich_content' === $meta_key || '_aihl_menu_rich_footer' === $meta_key) {
			$value = wp_kses_post($value);
		} elseif ('_aihl_menu_image' === $meta_key || '_aihl_menu_rich_cta_url' === $meta_key) {
			$value = esc_url_raw($value);
		} elseif ('_aihl_menu_image_id' === $meta_key) {
			$value = (string) absint($value);
		} elseif ('_aihl_menu_color' === $meta_key || '_aihl_menu_badge_color' === $meta_key) {
			$value = sanitize_hex_color($value);
			if ($value === null) {
				$value = '';
			}
		} else {
			$value = sanitize_text_field($value);
		}

		if ('' === $value) {
			delete_post_meta($menu_item_db_id, $meta_key);
		} else {
			update_post_meta($menu_item_db_id, $meta_key, $value);
		}
	}

	$highlight_value = isset($_POST['aihl_menu_highlight'][$menu_item_db_id]) ? '1' : '';
	if ($highlight_value === '') {
		delete_post_meta($menu_item_db_id, '_aihl_menu_highlight');
	} else {
		update_post_meta($menu_item_db_id, '_aihl_menu_highlight', '1');
	}
}, 10, 2);
