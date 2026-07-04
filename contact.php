<?php
/**
 * Template Name: Contact
 *
 * Pagina contatti nativa del tema, compatibile con opzioni AI-HTML e shortcode form.
 *
 * @package AI_HTML
 */

get_header();

if (have_posts()) {
	the_post();
}

$aihl_address_option = aihl_register_class::check('contatti_indirizzo');
$aihl_phone_option = aihl_register_class::check('contatti_telefono');
$aihl_email_option = aihl_register_class::check('contatti_email');
$aihl_map_option = aihl_register_class::check('contatti_maps');
$aihl_form_option = aihl_register_class::check('contactform_contacts');

$aihl_address = isset($aihl_address_option['contatti_indirizzo']) ? trim((string) $aihl_address_option['contatti_indirizzo']) : '';
$aihl_phone = isset($aihl_phone_option['contatti_telefono']) ? trim((string) $aihl_phone_option['contatti_telefono']) : '';
$aihl_email = isset($aihl_email_option['contatti_email']) ? trim((string) $aihl_email_option['contatti_email']) : '';
$aihl_map = isset($aihl_map_option['contatti_maps']) ? trim((string) $aihl_map_option['contatti_maps']) : '';
$aihl_form = isset($aihl_form_option['contactform_contacts']) ? trim((string) $aihl_form_option['contactform_contacts']) : '';
$aihl_phone_href = preg_replace('/[^0-9+]/', '', $aihl_phone);
$aihl_form_shortcode = '';

if ('' !== $aihl_form) {
	if (ctype_digit($aihl_form)) {
		$aihl_form_shortcode = '[contact-form-7 id="' . absint($aihl_form) . '"]';
	} elseif (false !== strpos($aihl_form, '[')) {
		$aihl_form_shortcode = $aihl_form;
	}
}
?>
<main id="main" class="container site-main aihl-template-page aihl-template-page-contact py-4" itemscope itemtype="https://schema.org/ContactPage">
	<?php aihl_render_breadcrumbs(array('class' => 'mb-3')); ?>
	<?php aihl_render_template_hero(array(
		'eyebrow' => __('Contatti', AIHL_TEXT_DOMAIN),
		'title' => get_the_title() ?: __('Contatti', AIHL_TEXT_DOMAIN),
		'description' => __('Usa i riferimenti ufficiali per richieste, appuntamenti e informazioni. I dati sono gestiti dalle impostazioni del tema e possono essere aggiornati via pannello o API.', AIHL_TEXT_DOMAIN),
		'icon' => 'fa-regular fa-address-book',
	)); ?>

	<div class="row g-5 align-items-start">
		<section class="col-12 col-lg-5" aria-labelledby="aihl-contact-details-title">
			<div class="aihl-template-panel h-100">
				<h2 id="aihl-contact-details-title" class="h4 mb-4"><?php esc_html_e('Riferimenti', AIHL_TEXT_DOMAIN); ?></h2>
				<div class="aihl-contact-list">
					<?php if ($aihl_address !== '') : ?>
						<div class="aihl-contact-item">
							<i class="fa-solid fa-location-dot" aria-hidden="true"></i>
							<div>
								<span><?php esc_html_e('Sede', AIHL_TEXT_DOMAIN); ?></span>
								<strong itemprop="address"><?php echo esc_html($aihl_address); ?></strong>
							</div>
						</div>
					<?php endif; ?>

					<?php if ($aihl_phone !== '') : ?>
						<div class="aihl-contact-item">
							<i class="fa-solid fa-phone" aria-hidden="true"></i>
							<div>
								<span><?php esc_html_e('Telefono', AIHL_TEXT_DOMAIN); ?></span>
								<a href="<?php echo esc_url('tel:' . $aihl_phone_href); ?>" itemprop="telephone"><?php echo esc_html($aihl_phone); ?></a>
							</div>
						</div>
					<?php endif; ?>

					<?php if ($aihl_email !== '') : ?>
						<div class="aihl-contact-item">
							<i class="fa-solid fa-envelope" aria-hidden="true"></i>
							<div>
								<span><?php esc_html_e('Email', AIHL_TEXT_DOMAIN); ?></span>
								<a href="<?php echo esc_url('mailto:' . sanitize_email($aihl_email)); ?>" itemprop="email"><?php echo esc_html($aihl_email); ?></a>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<div class="mt-4">
					<h3 class="h6 text-uppercase text-muted mb-3"><?php esc_html_e('Canali social', AIHL_TEXT_DOMAIN); ?></h3>
					<div class="d-flex flex-wrap gap-2">
						<?php aihl_render_social_links('btn btn-outline-primary btn-social'); ?>
					</div>
				</div>
			</div>
		</section>

		<section class="col-12 col-lg-7" aria-labelledby="aihl-contact-form-title">
			<div class="aihl-template-panel h-100">
				<h2 id="aihl-contact-form-title" class="h4 mb-3"><?php esc_html_e('Invia una richiesta', AIHL_TEXT_DOMAIN); ?></h2>
				<p class="text-muted"><?php esc_html_e('Compila il modulo con informazioni essenziali. I dati saranno trattati secondo le policy del sito.', AIHL_TEXT_DOMAIN); ?></p>
				<?php if ($aihl_form_shortcode !== '') : ?>
					<?php echo do_shortcode($aihl_form_shortcode); ?>
				<?php elseif (trim((string) get_the_content()) !== '') : ?>
					<?php the_content(); ?>
				<?php else : ?>
					<p class="text-muted mb-0"><?php esc_html_e('Configura lo shortcode del form nelle opzioni contatti del tema.', AIHL_TEXT_DOMAIN); ?></p>
				<?php endif; ?>
			</div>
		</section>
	</div>

	<?php if ($aihl_map !== '') : ?>
		<section class="aihl-contact-map my-5" aria-label="<?php esc_attr_e('Mappa sede', AIHL_TEXT_DOMAIN); ?>">
			<?php echo aihtml_kses_embed_html($aihl_map); ?>
		</section>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
