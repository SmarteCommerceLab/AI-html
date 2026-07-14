<?php
/**
 * Template: Blog Index (home.php)
 *
 * Layout configurabile da Customizer: griglia, lista, magazine.
 * Questa template si attiva quando la pagina "Articoli" e impostata in Impostazioni > Lettura.
 *
 * @since 1.2.0
 */
get_header();

$aihl_blog_layout = (string) aihtml_option_value('blog_layout', 'grid');
if (!in_array($aihl_blog_layout, array('grid', 'list', 'magazine'), true)) {
	$aihl_blog_layout = 'grid';
}
$aihl_blog_sidebar = (bool) aihtml_option_value('blog_sidebar', false);
?>
<main id="main" class="container site-main overflow-hidden py-4">
	<header class="mb-4">
		<h1 class="display-5 fw-bold mb-2"><?php echo esc_html(get_bloginfo('name')); ?></h1>
		<?php if (get_bloginfo('description')) : ?>
			<p class="lead text-muted mb-0"><?php echo esc_html(get_bloginfo('description')); ?></p>
		<?php endif; ?>
	</header>

	<?php if ($aihl_blog_layout === 'magazine' && have_posts()) : ?>
		<?php /* Magazine: primo post grande, poi griglia */
		the_post(); ?>
		<article class="aihl-magazine-hero mb-4 pb-4 border-bottom">
			<div class="row g-4 align-items-center">
				<?php if (has_post_thumbnail()) : ?>
					<div class="col-lg-7">
						<a class="d-block overflow-hidden rounded" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
							<?php the_post_thumbnail('large', array(
								'class'    => 'img-fluid w-100',
								'alt'      => get_the_title(),
								'decoding' => 'async',
							)); ?>
						</a>
					</div>
				<?php endif; ?>
				<div class="<?php echo has_post_thumbnail() ? 'col-lg-5' : 'col-12'; ?>">
					<?php get_template_part('template-parts/post-meta', null, array('style' => 'block')); ?>
					<h2 class="h3 mb-2">
						<a class="link text-body" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
					</h2>
					<p class="text-muted mb-3"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 30, '...')); ?></p>
					<a class="btn btn-primary btn-sm" href="<?php the_permalink(); ?>"><?php esc_html_e('Leggi articolo', AIHL_TEXT_DOMAIN); ?></a>
				</div>
			</div>
		</article>
	<?php endif; ?>

	<div class="row">
		<div class="<?php echo $aihl_blog_sidebar ? 'col-lg-8' : 'col-12'; ?>">

			<?php if ($aihl_blog_layout === 'grid' || $aihl_blog_layout === 'magazine') : ?>
				<?php if (have_posts()) : ?>
					<div class="row g-4">
						<?php while (have_posts()) : the_post(); ?>
							<div class="col-12 col-md-6 col-xl-4">
								<?php get_template_part('template-parts/card-post', null, array(
									'layout'        => 'vertical',
									'excerpt_words' => 16,
								)); ?>
							</div>
						<?php endwhile; ?>
					</div>
				<?php else : ?>
					<p><?php esc_html_e('Nessun contenuto disponibile.', AIHL_TEXT_DOMAIN); ?></p>
				<?php endif; ?>

			<?php elseif ($aihl_blog_layout === 'list') : ?>
				<?php if (have_posts()) : ?>
					<?php while (have_posts()) : the_post(); ?>
						<?php get_template_part('template-parts/card-post', null, array(
							'layout'        => 'list',
							'excerpt_words' => 25,
							'heading_tag'   => 'h2',
							'heading_class' => 'h5 mb-1',
						)); ?>
					<?php endwhile; ?>
				<?php else : ?>
					<p><?php esc_html_e('Nessun contenuto disponibile.', AIHL_TEXT_DOMAIN); ?></p>
				<?php endif; ?>
			<?php endif; ?>

			<div class="mt-4 mb-4">
				<?php if (function_exists('wp_bs_pagination')) { wp_bs_pagination(); } else { the_posts_pagination(); } ?>
			</div>
		</div>

		<?php if ($aihl_blog_sidebar) : ?>
			<aside class="col-lg-4">
				<?php if (is_active_sidebar('blog-sidebar')) : ?>
					<?php dynamic_sidebar('blog-sidebar'); ?>
				<?php else : ?>
					<div class="border rounded p-3 mb-4">
						<h3 class="h6 mb-2"><?php esc_html_e('Categorie', AIHL_TEXT_DOMAIN); ?></h3>
						<ul class="list-unstyled small mb-0">
							<?php wp_list_categories(array('title_li' => '', 'show_count' => true)); ?>
						</ul>
					</div>
					<div class="border rounded p-3 mb-4">
						<h3 class="h6 mb-2"><?php esc_html_e('Tag', AIHL_TEXT_DOMAIN); ?></h3>
						<?php wp_tag_cloud(array('smallest' => 12, 'largest' => 16, 'unit' => 'px')); ?>
					</div>
				<?php endif; ?>
			</aside>
		<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>
