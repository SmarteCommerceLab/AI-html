<?php
$aihl_site_name = get_bloginfo('name');
$aihl_site_tagline = get_bloginfo('description');
$aihl_site_description = aihl_register_class::get_text('sito_descrizione');
$aihl_contact_address = aihl_register_class::get_text('contatti_indirizzo');
$aihl_contact_phone = aihl_register_class::get_text('contatti_telefono');
$aihl_contact_email = aihl_register_class::get_text('contatti_email');
$aihl_mailchimp_id = absint(aihl_register_class::get_text('mailchip_footer'));
$aihl_has_newsletter = $aihl_mailchimp_id > 0 && shortcode_exists('mc4wp_form');
$aihl_has_social_links = function_exists('aihl_get_site_builder_social_links') && !empty(aihl_get_site_builder_social_links());
$aihl_has_footer_menu = has_nav_menu('utili');
$aihl_footer_variant = sanitize_key((string) aihtml_option_value('footer_variant', 'enterprise'));
$aihl_footer_variants = array('enterprise', 'futuristic', 'corporate', 'compact', 'mega-footer', 'minimal', 'cta-footer');
if (!in_array($aihl_footer_variant, $aihl_footer_variants, true)) {
	$aihl_footer_variant = 'enterprise';
}
$aihl_footer_bg_enabled = (bool) aihtml_option_value('footer_background_enable', true);
$aihl_footer_bg_local_image = esc_url_raw((string) aihtml_option_value('footer_background_image', AIHL_DIR_URL . '/resource/img/footer.png'));
$aihl_footer_bg_remote_url = esc_url_raw((string) aihtml_option_value('footer_background_remote_url', ''));
$aihl_footer_bg_image = $aihl_footer_bg_remote_url !== '' ? $aihl_footer_bg_remote_url : $aihl_footer_bg_local_image;
$aihl_footer_bg_opacity = (float) str_replace(',', '.', (string) aihtml_option_value('footer_background_opacity', '0.12'));
$aihl_footer_bg_opacity = max(0, min(1, $aihl_footer_bg_opacity));
$aihl_footer_overlay_opacity = (float) str_replace(',', '.', (string) aihtml_option_value('footer_overlay_opacity', '0'));
$aihl_footer_overlay_opacity = max(0, min(1, $aihl_footer_overlay_opacity));
$aihl_footer_bg_position = (string) aihtml_option_value('footer_background_position', 'center center');
$aihl_footer_bg_size = (string) aihtml_option_value('footer_background_size', 'contain');
$aihl_footer_bg_repeat = (string) aihtml_option_value('footer_background_repeat', 'no-repeat');
$aihl_footer_overlay_tone = (string) aihtml_option_value('footer_overlay_tone', 'body');
$aihl_footer_positions = array('center center', 'center top', 'center bottom', 'left center', 'right center');
$aihl_footer_sizes = array('auto', 'cover', 'contain');
$aihl_footer_repeats = array('no-repeat', 'repeat', 'repeat-x', 'repeat-y');
$aihl_footer_overlay_colors = array(
	'body' => 'var(--bs-body-bg, #ffffff)',
	'primary' => 'var(--bs-primary, #0d6efd)',
	'dark' => 'var(--bs-dark, #212529)',
	'light' => 'var(--bs-light, #f8f9fa)',
);
if (!in_array($aihl_footer_bg_position, $aihl_footer_positions, true)) {
	$aihl_footer_bg_position = 'center center';
}
if (!in_array($aihl_footer_bg_size, $aihl_footer_sizes, true)) {
	$aihl_footer_bg_size = 'contain';
}
if (!in_array($aihl_footer_bg_repeat, $aihl_footer_repeats, true)) {
	$aihl_footer_bg_repeat = 'no-repeat';
}
if (!isset($aihl_footer_overlay_colors[$aihl_footer_overlay_tone])) {
	$aihl_footer_overlay_tone = 'body';
}

$aihl_footer_cta_title = trim((string) aihtml_option_value('footer_cta_title', ''));
$aihl_footer_cta_subtitle = trim((string) aihtml_option_value('footer_cta_subtitle', ''));
$aihl_footer_cta_btn_label = trim((string) aihtml_option_value('footer_cta_btn_label', ''));
$aihl_footer_cta_btn_url = esc_url((string) aihtml_option_value('footer_cta_btn_url', '#'));
$aihl_footer_cta_btn2_label = trim((string) aihtml_option_value('footer_cta_btn2_label', ''));
$aihl_footer_cta_btn2_url = esc_url((string) aihtml_option_value('footer_cta_btn2_url', '#'));

$aihl_footer_columns_count = (int) aihtml_option_value('footer_columns_count', 4);
$aihl_footer_columns_count = max(3, min(5, $aihl_footer_columns_count));

$aihl_footer_style = array(
	'--aihl-footer-bg-opacity:' . $aihl_footer_bg_opacity,
	'--aihl-footer-bg-position:' . $aihl_footer_bg_position,
	'--aihl-footer-bg-size:' . $aihl_footer_bg_size,
	'--aihl-footer-bg-repeat:' . $aihl_footer_bg_repeat,
	'--aihl-footer-overlay-opacity:' . $aihl_footer_overlay_opacity,
	'--aihl-footer-overlay-color:' . $aihl_footer_overlay_colors[$aihl_footer_overlay_tone],
);
if ($aihl_footer_bg_enabled && $aihl_footer_bg_image !== '') {
	$aihl_footer_style[] = '--aihl-footer-bg-image:url("' . esc_url($aihl_footer_bg_image) . '")';
}
$aihl_footer_surface = (
	$aihl_footer_variant === 'futuristic'
	|| ($aihl_footer_bg_enabled && $aihl_footer_bg_image !== '')
) ? 'dark' : 'light';
$aihl_footer_classes = array(
	'aihl-footer',
	'aihl-footer--' . ($aihl_footer_variant === 'cta-footer' ? 'enterprise' : $aihl_footer_variant),
	'aihl-footer-surface-' . $aihl_footer_surface,
);
$aihl_slot_context = array(
	'theme' => 'ai-html',
	'screen' => 'footer',
	'entity_id' => (int) get_queried_object_id(),
);

$aihl_header_structure = (string) aihtml_option_value('header_structure', 'standard');
?>

<?php if (
	function_exists('aihl_should_render_canvas_structure')
		? aihl_should_render_canvas_structure('footer')
		: (function_exists('aihl_code_slot_has_override') && aihl_code_slot_has_override('footer_full'))
) : ?>
	<?php // ── Footer Full Override: l'AI sostituisce l'intero footer nativo ── ?>
	<?php aihl_render_code_slot('footer_full'); ?>
<?php else : ?>
<?php // ── Footer Nativo ── ?>

<?php if ($aihl_footer_variant === 'cta-footer' && ($aihl_footer_cta_title !== '' || $aihl_footer_cta_btn_label !== '')) : ?>
<section class="aihl-footer-hero-cta">
	<div class="container text-center py-5">
		<?php if ($aihl_footer_cta_title !== '') : ?>
			<h2 class="display-6 fw-bold mb-3"><?php echo esc_html($aihl_footer_cta_title); ?></h2>
		<?php endif; ?>
		<?php if ($aihl_footer_cta_subtitle !== '') : ?>
			<p class="lead mb-4 mx-auto" style="max-width:640px"><?php echo esc_html($aihl_footer_cta_subtitle); ?></p>
		<?php endif; ?>
		<div class="d-flex flex-wrap gap-3 justify-content-center">
			<?php if ($aihl_footer_cta_btn_label !== '') : ?>
				<a class="btn btn-primary btn-lg" href="<?php echo esc_url($aihl_footer_cta_btn_url); ?>"><?php echo esc_html($aihl_footer_cta_btn_label); ?></a>
			<?php endif; ?>
			<?php if ($aihl_footer_cta_btn2_label !== '') : ?>
				<a class="btn btn-outline-primary btn-lg" href="<?php echo esc_url($aihl_footer_cta_btn2_url); ?>"><?php echo esc_html($aihl_footer_cta_btn2_label); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ($aihl_footer_variant === 'minimal') : ?>
<!-- Minimal Footer -->
<footer class="<?php echo esc_attr(implode(' ', array('aihl-footer', 'aihl-footer--minimal', 'aihl-footer-surface-' . $aihl_footer_surface, 'border-top'))); ?>" style="<?php echo esc_attr(implode(';', $aihl_footer_style)); ?>" role="contentinfo">
	<?php if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('footer_start'); } ?>
	<div class="container py-3">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div class="aihl-footer-bottom-copy">
				&copy; <?php echo esc_html(date_i18n('Y')); ?> <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html($aihl_site_name); ?></a>
			</div>
			<?php if ($aihl_has_footer_menu) : ?>
			<div class="aihl-footer-inline-menu">
				<?php wp_nav_menu(array(
					'menu_class' => 'aihl-footer-inline-list list-unstyled d-flex flex-wrap gap-3 mb-0',
					'container' => '',
					'depth' => 1,
					'theme_location' => 'utili',
					'fallback_cb' => false,
				)); ?>
			</div>
			<?php endif; ?>
			<?php if ($aihl_has_social_links) : ?>
			<div class="aihl-footer-social d-flex gap-2">
				<?php aihl_render_social_links('btn btn-outline-primary btn-sm btn-social'); ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<?php if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('footer_end'); } ?>
</footer>

<?php elseif ($aihl_footer_variant === 'mega-footer') : ?>
<!-- Mega Footer -->
<footer class="<?php echo esc_attr(implode(' ', array('aihl-footer', 'aihl-footer--mega-footer', 'aihl-footer-surface-' . $aihl_footer_surface, 'footer', 'mt-5', 'border-top'))); ?>" style="<?php echo esc_attr(implode(';', $aihl_footer_style)); ?>" role="contentinfo">
	<?php if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('footer_start'); } ?>
	<div class="container py-5">
		<?php if ($aihl_has_newsletter) : ?>
			<section class="aihl-footer-cta mb-5 p-4 p-lg-5" aria-label="<?php esc_attr_e('Newsletter', AIHL_TEXT_DOMAIN); ?>">
				<div class="row g-4 align-items-center">
					<div class="col-lg-5">
						<p class="aihl-footer-kicker small text-uppercase fw-semibold mb-2"><?php esc_html_e('Newsletter', AIHL_TEXT_DOMAIN); ?></p>
						<h2 class="h3 mb-2"><?php esc_html_e('Ricevi aggiornamenti dal sito', AIHL_TEXT_DOMAIN); ?></h2>
						<p class="mb-0"><?php esc_html_e('Contenuti editoriali, novita e comunicazioni selezionate.', AIHL_TEXT_DOMAIN); ?></p>
					</div>
					<div class="col-lg-7">
						<div class="aihl-footer-form">
							<?php echo do_shortcode('[mc4wp_form id="' . $aihl_mailchimp_id . '"]'); ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<div class="row g-5">
			<div class="col-lg-3 col-md-6">
				<a href="<?php echo esc_url(home_url('/')); ?>" class="aihl-footer-brand d-inline-flex align-items-center mb-3">
					<?php if (!function_exists('aihl_render_site_logo') || !aihl_render_site_logo('footer', 'img-fluid aihl-site-logo aihl-footer-logo')) : ?>
						<span class="h4 mb-0"><?php echo esc_html($aihl_site_name); ?></span>
					<?php endif; ?>
				</a>
				<?php if ($aihl_site_tagline !== '') : ?>
					<p class="fw-semibold mb-2"><?php echo esc_html($aihl_site_tagline); ?></p>
				<?php endif; ?>
				<?php if ($aihl_site_description !== '') : ?>
					<p class="aihl-footer-muted mb-0"><?php echo esc_html($aihl_site_description); ?></p>
				<?php endif; ?>
				<?php if ($aihl_has_social_links) : ?>
					<div class="aihl-footer-social d-flex flex-wrap gap-2 mt-3">
						<?php aihl_render_social_links('btn btn-outline-primary btn-social'); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
			$aihl_mega_cols = $aihl_footer_columns_count - 1;
			$aihl_col_class = $aihl_mega_cols >= 4 ? 'col-lg col-md-6' : 'col-lg-3 col-md-6';
			for ($col = 1; $col <= min($aihl_mega_cols, 4); $col++) :
				$location = 'footer_col_' . $col;
				if (has_nav_menu($location)) :
					$menu_obj = wp_get_nav_menu_object(get_nav_menu_locations()[$location] ?? 0);
					$col_title = ($menu_obj instanceof WP_Term) ? $menu_obj->name : '';
			?>
				<div class="<?php echo esc_attr($aihl_col_class); ?>">
					<?php if ($col_title !== '') : ?>
						<h2 class="h6 text-uppercase fw-semibold mb-3"><?php echo esc_html($col_title); ?></h2>
					<?php endif; ?>
					<?php wp_nav_menu(array(
						'menu_class' => 'aihl-footer-menu list-unstyled mb-0',
						'container' => '',
						'depth' => 1,
						'theme_location' => $location,
						'fallback_cb' => false,
					)); ?>
				</div>
			<?php
				endif;
			endfor;
			?>

			<?php if ($aihl_contact_address !== '' || $aihl_contact_phone !== '' || $aihl_contact_email !== '') : ?>
				<div class="<?php echo esc_attr($aihl_col_class); ?>">
					<h2 class="h6 text-uppercase fw-semibold mb-3"><?php esc_html_e('Contatti', AIHL_TEXT_DOMAIN); ?></h2>
					<ul class="aihl-footer-contact list-unstyled mb-0">
						<?php if ($aihl_contact_address !== '') : ?>
							<li><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><?php echo esc_html($aihl_contact_address); ?></span></li>
						<?php endif; ?>
						<?php if ($aihl_contact_phone !== '') : ?>
							<li><i class="fa-solid fa-phone" aria-hidden="true"></i><a href="<?php echo esc_url('tel:' . preg_replace('/[^0-9\+]/', '', $aihl_contact_phone)); ?>"><?php echo esc_html($aihl_contact_phone); ?></a></li>
						<?php endif; ?>
						<?php if ($aihl_contact_email !== '') : ?>
							<li><i class="fa-solid fa-envelope" aria-hidden="true"></i><a href="<?php echo esc_url('mailto:' . sanitize_email($aihl_contact_email)); ?>"><?php echo esc_html($aihl_contact_email); ?></a></li>
						<?php endif; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="aihl-footer-bottom border-top">
		<div class="container py-3">
			<div class="row g-3 align-items-center">
				<div class="col-md-6 text-center text-md-start">
					&copy; <?php echo esc_html(date_i18n('Y')); ?> <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html($aihl_site_name); ?></a>. <?php esc_html_e('Tutti i diritti riservati.', AIHL_TEXT_DOMAIN); ?>
				</div>
				<div class="col-md-6 text-center text-md-end">
					<?php esc_html_e('Design e sviluppo', AIHL_TEXT_DOMAIN); ?> <a href="https://smartecommerce.it" rel="noopener" target="_blank">Smart eCommerce srls</a>
				</div>
			</div>
		</div>
	</div>
	<?php if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('footer_end'); } ?>
</footer>

<?php else : ?>
<!-- Standard / Enterprise / Futuristic / Corporate / Compact / CTA Footer -->
<footer class="<?php echo esc_attr(implode(' ', array_merge($aihl_footer_classes, array('footer', 'mt-5', 'border-top')))); ?>" style="<?php echo esc_attr(implode(';', $aihl_footer_style)); ?>" role="contentinfo">
	<?php if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('footer_start'); } ?>
	<div class="container py-5">
		<?php if ($aihl_footer_variant !== 'compact' && $aihl_has_newsletter) : ?>
			<section class="aihl-footer-cta mb-5 p-4 p-lg-5" aria-label="<?php esc_attr_e('Newsletter', AIHL_TEXT_DOMAIN); ?>">
				<div class="row g-4 align-items-center">
					<div class="col-lg-5">
						<p class="aihl-footer-kicker small text-uppercase fw-semibold mb-2"><?php esc_html_e('Newsletter', AIHL_TEXT_DOMAIN); ?></p>
						<h2 class="h3 mb-2"><?php esc_html_e('Ricevi aggiornamenti dal sito', AIHL_TEXT_DOMAIN); ?></h2>
						<p class="mb-0"><?php esc_html_e('Contenuti editoriali, novita e comunicazioni selezionate.', AIHL_TEXT_DOMAIN); ?></p>
					</div>
					<div class="col-lg-7">
						<div class="aihl-footer-form">
							<?php echo do_shortcode('[mc4wp_form id="' . $aihl_mailchimp_id . '"]'); ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<div class="row g-5">
			<div class="col-lg-4 col-md-6">
				<a href="<?php echo esc_url(home_url('/')); ?>" class="aihl-footer-brand d-inline-flex align-items-center mb-3">
					<?php if (!function_exists('aihl_render_site_logo') || !aihl_render_site_logo('footer', 'img-fluid aihl-site-logo aihl-footer-logo')) : ?>
						<span class="h4 mb-0"><?php echo esc_html($aihl_site_name); ?></span>
					<?php endif; ?>
				</a>
				<?php if ($aihl_site_tagline !== '') : ?>
					<p class="fw-semibold mb-2"><?php echo esc_html($aihl_site_tagline); ?></p>
				<?php endif; ?>
				<?php if ($aihl_site_description !== '') : ?>
					<p class="aihl-footer-muted mb-0"><?php echo esc_html($aihl_site_description); ?></p>
				<?php endif; ?>
			</div>

			<?php if ($aihl_contact_address !== '' || $aihl_contact_phone !== '' || $aihl_contact_email !== '') : ?>
				<div class="col-lg-3 col-md-6">
					<h2 class="h6 text-uppercase fw-semibold mb-3"><?php esc_html_e('Contatti', AIHL_TEXT_DOMAIN); ?></h2>
					<ul class="aihl-footer-contact list-unstyled mb-0">
						<?php if ($aihl_contact_address !== '') : ?>
							<li><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><?php echo esc_html($aihl_contact_address); ?></span></li>
						<?php endif; ?>
						<?php if ($aihl_contact_phone !== '') : ?>
							<li><i class="fa-solid fa-phone" aria-hidden="true"></i><a href="<?php echo esc_url('tel:' . preg_replace('/[^0-9\+]/', '', $aihl_contact_phone)); ?>"><?php echo esc_html($aihl_contact_phone); ?></a></li>
						<?php endif; ?>
						<?php if ($aihl_contact_email !== '') : ?>
							<li><i class="fa-solid fa-envelope" aria-hidden="true"></i><a href="<?php echo esc_url('mailto:' . sanitize_email($aihl_contact_email)); ?>"><?php echo esc_html($aihl_contact_email); ?></a></li>
						<?php endif; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ($aihl_has_footer_menu) : ?>
				<div class="col-lg-3 col-md-6">
					<h2 class="h6 text-uppercase fw-semibold mb-3"><?php esc_html_e('Link utili', AIHL_TEXT_DOMAIN); ?></h2>
					<?php
					wp_nav_menu(array(
						'menu_class' => 'aihl-footer-menu list-unstyled mb-0',
						'container' => '',
						'depth' => 1,
						'theme_location' => 'utili',
						'fallback_cb' => false,
					));
					?>
				</div>
			<?php endif; ?>

			<?php if ($aihl_has_social_links) : ?>
				<div class="col-lg-2 col-md-6">
					<h2 class="h6 text-uppercase fw-semibold mb-3"><?php esc_html_e('Seguici', AIHL_TEXT_DOMAIN); ?></h2>
					<div class="aihl-footer-social d-flex flex-wrap gap-2">
						<?php aihl_render_social_links('btn btn-outline-primary btn-social'); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($aihl_footer_variant === 'compact' && $aihl_has_newsletter) : ?>
				<div class="col-12">
					<div class="aihl-footer-compact-newsletter">
						<h2 class="h6 text-uppercase fw-semibold mb-3"><?php esc_html_e('Newsletter', AIHL_TEXT_DOMAIN); ?></h2>
						<div class="aihl-footer-form">
							<?php echo do_shortcode('[mc4wp_form id="' . $aihl_mailchimp_id . '"]'); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="aihl-footer-bottom border-top">
		<div class="container py-3">
			<div class="row g-3 align-items-center">
				<div class="col-md-6 text-center text-md-start">
					&copy; <?php echo esc_html(date_i18n('Y')); ?> <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html($aihl_site_name); ?></a>. <?php esc_html_e('Tutti i diritti riservati.', AIHL_TEXT_DOMAIN); ?>
				</div>
				<div class="col-md-6 text-center text-md-end">
					<?php esc_html_e('Design e sviluppo', AIHL_TEXT_DOMAIN); ?> <a href="https://smartecommerce.it" rel="noopener" target="_blank">Smart eCommerce srls</a>
				</div>
			</div>
		</div>
	</div>
	<?php if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('footer_end'); } ?>
</footer>
<?php endif; ?>

<?php endif; // end footer_full override check ?>

<?php if ($aihl_header_structure === 'sidebar') : ?>
</div><!-- .aihl-sidebar-content-wrap -->
<?php endif; ?>

<?php
if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('after_content'); }
do_action('sdc/smart-builder-site/slot/slot.content.after', $aihl_slot_context);
do_action('sdc/smart-builder-site/slot/slot.footer.tools', $aihl_slot_context);
do_action('sdc/smart-site-builder/slot/slot.content.after', $aihl_slot_context);
do_action('sdc/smart-site-builder/slot/slot.footer.tools', $aihl_slot_context);
if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('before_footer'); }
?>
<?php
// Footer template rendering happens between these hooks
if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('after_footer'); }
wp_footer(); ?></body></html>
