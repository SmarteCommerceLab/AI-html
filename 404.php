<?php
/**
 * Template: 404.
 *
 * Pagina di errore leggibile, utile e noindex tramite layer SEO del tema.
 *
 * @package AI_HTML
 */

get_header();

$aihl_recent_posts = new WP_Query(array(
	'post_type' => 'post',
	'posts_per_page' => 3,
	'ignore_sticky_posts' => true,
	'no_found_rows' => true,
));
?>
<main id="main" class="container site-main aihl-template-page aihl-template-page-404 py-5">
	<?php aihl_render_template_hero(array(
		'eyebrow' => __('Errore 404', AIHL_TEXT_DOMAIN),
		'title' => __('Pagina non trovata', AIHL_TEXT_DOMAIN),
		'description' => __('La risorsa richiesta non esiste più o è stata spostata. Puoi cercare nel sito, tornare alla home o consultare gli ultimi contenuti pubblicati.', AIHL_TEXT_DOMAIN),
		'icon' => 'fa-solid fa-triangle-exclamation',
		'actions' => array(
			array(
				'label' => __('Torna alla home', AIHL_TEXT_DOMAIN),
				'url' => home_url('/'),
				'class' => 'btn btn-primary rounded-pill px-4',
			),
		),
	)); ?>

	<section class="row g-4 align-items-start" aria-labelledby="aihl-404-search-title">
		<div class="col-12 col-lg-5">
			<div class="aihl-template-panel h-100">
				<h2 id="aihl-404-search-title" class="h4 mb-3"><?php esc_html_e('Cerca nel sito', AIHL_TEXT_DOMAIN); ?></h2>
				<p class="text-muted"><?php esc_html_e('Inserisci una parola chiave o un argomento per trovare la pagina corretta.', AIHL_TEXT_DOMAIN); ?></p>
				<?php aihtml_search_form_html(array('show_divider' => false, 'class' => 'mb-0')); ?>
			</div>
		</div>

		<div class="col-12 col-lg-7">
			<div class="aihl-template-panel h-100">
				<h2 class="h4 mb-3"><?php esc_html_e('Ultimi contenuti', AIHL_TEXT_DOMAIN); ?></h2>
				<?php if ($aihl_recent_posts->have_posts()) : ?>
					<div class="row g-3">
						<?php while ($aihl_recent_posts->have_posts()) : $aihl_recent_posts->the_post(); ?>
							<div class="col-12">
								<?php get_template_part('template-parts/card-post', null, array(
									'layout' => 'list',
									'excerpt_words' => 14,
									'heading_class' => 'h6 mb-1',
								)); ?>
							</div>
						<?php endwhile; ?>
					</div>
				<?php else : ?>
					<p class="text-muted mb-0"><?php esc_html_e('Nessun contenuto recente disponibile.', AIHL_TEXT_DOMAIN); ?></p>
				<?php endif; ?>
				<?php wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
</main>
<?php get_footer(); ?>
