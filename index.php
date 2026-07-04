<?php
/**
 * Template: fallback principale.
 *
 * Copre query non gestite da template più specifici.
 *
 * @package AI_HTML
 */

get_header();
?>
<main id="main" class="container site-main aihl-template-page aihl-template-page-index py-4">
	<?php aihl_render_breadcrumbs(array('class' => 'mb-3')); ?>
	<?php aihl_render_template_hero(array(
		'eyebrow' => __('Contenuti', AIHL_TEXT_DOMAIN),
		'title' => get_bloginfo('name'),
		'description' => get_bloginfo('description'),
		'icon' => 'fa-regular fa-newspaper',
	)); ?>

	<?php if (have_posts()) : ?>
		<section aria-labelledby="aihl-index-title">
			<h2 id="aihl-index-title" class="visually-hidden"><?php esc_html_e('Elenco contenuti', AIHL_TEXT_DOMAIN); ?></h2>
			<div class="row g-4">
				<?php while (have_posts()) : the_post(); ?>
					<div class="col-12 col-md-6 col-xl-4">
						<?php get_template_part('template-parts/card-post', null, array(
							'layout' => 'vertical',
							'excerpt_words' => 20,
							'heading_class' => 'h5 mb-2',
						)); ?>
					</div>
				<?php endwhile; ?>
			</div>
			<?php aihl_render_template_pagination(); ?>
		</section>
	<?php else : ?>
		<?php aihl_render_posts_empty_state(); ?>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
