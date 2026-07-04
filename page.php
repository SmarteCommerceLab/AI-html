<?php
/**
 * Template: pagina standard.
 *
 * Fallback pulito per pagine non gestite da SBS o template dedicati.
 *
 * @package AI_HTML
 */

get_header();
do_action('aihl_before_main_content');
?>
<main id="main" class="container site-main aihl-template-page aihl-template-page-default py-4">
	<?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class('aihl-page-article'); ?> itemscope itemtype="https://schema.org/WebPage">
				<?php aihl_render_breadcrumbs(array('class' => 'mb-3')); ?>
				<header class="aihl-single-header mb-4">
					<?php the_title('<h1 class="entry-title display-5 fw-bold mb-3" itemprop="headline">', '</h1>'); ?>
				</header>
				<div class="entry-content" itemprop="mainContentOfPage">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<?php aihl_render_posts_empty_state(); ?>
	<?php endif; ?>
</main>
<?php
do_action('aihl_after_main_content');
get_footer();
