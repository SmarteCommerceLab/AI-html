<?php
/**
 * Template: risultati ricerca.
 *
 * Search è noindex/follow dal layer SEO; il markup resta utile per utenti e crawler interni.
 *
 * @package AI_HTML
 */

get_header();

$aihl_query = trim((string) get_search_query());
$aihl_found_posts = isset($wp_query->found_posts) ? (int) $wp_query->found_posts : 0;
$aihl_title = $aihl_query !== ''
	? sprintf(__('Risultati per “%s”', AIHL_TEXT_DOMAIN), $aihl_query)
	: __('Cerca nel sito', AIHL_TEXT_DOMAIN);
$aihl_description = $aihl_found_posts > 0
	? sprintf(_n('%s contenuto trovato. Raffina la ricerca se necessario.', '%s contenuti trovati. Raffina la ricerca se necessario.', $aihl_found_posts, AIHL_TEXT_DOMAIN), number_format_i18n($aihl_found_posts))
	: __('Non abbiamo trovato risultati pertinenti. Prova con termini più specifici.', AIHL_TEXT_DOMAIN);
?>
<main id="main" class="container site-main aihl-template-page aihl-template-page-search py-4">
	<?php aihl_render_breadcrumbs(array('class' => 'mb-3')); ?>
	<?php aihl_render_template_hero(array(
		'eyebrow' => __('Ricerca', AIHL_TEXT_DOMAIN),
		'title' => $aihl_title,
		'description' => $aihl_description,
		'search' => true,
	)); ?>

	<?php if (have_posts()) : ?>
		<section aria-labelledby="aihl-search-results-title">
			<h2 id="aihl-search-results-title" class="h4 mb-4"><?php esc_html_e('Risultati indicizzati', AIHL_TEXT_DOMAIN); ?></h2>
			<div class="row g-4">
				<?php while (have_posts()) : the_post(); ?>
					<?php if (in_array(get_post_type(), array('post', 'page'), true)) : ?>
						<div class="col-12 col-md-6 col-xl-4">
							<?php get_template_part('template-parts/card-post', null, array(
								'layout' => 'vertical',
								'excerpt_words' => 20,
								'heading_class' => 'h5 mb-2',
							)); ?>
						</div>
					<?php endif; ?>
				<?php endwhile; ?>
			</div>
			<?php aihl_render_template_pagination(); ?>
		</section>
	<?php else : ?>
		<?php aihl_render_posts_empty_state(
			__('Nessun risultato trovato', AIHL_TEXT_DOMAIN),
			__('Usa parole chiave diverse oppure consulta le sezioni principali del sito.', AIHL_TEXT_DOMAIN)
		); ?>
	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
</main>
<?php get_footer(); ?>
