<?php
/**
 * Template: archive generico.
 *
 * Usato per tag, date, autore e archivi non categoria.
 *
 * @package AI_HTML
 */

get_header();

$aihl_blog_sidebar = (bool) aihtml_option_value('blog_sidebar', false);
$aihl_archive_description = get_the_archive_description();
?>
<main id="main" class="container site-main aihl-template-page aihl-template-page-archive py-4">
	<?php aihl_render_breadcrumbs(array('class' => 'mb-3')); ?>
	<?php aihl_render_template_hero(array(
		'eyebrow' => __('Archivio', AIHL_TEXT_DOMAIN),
		'title' => wp_strip_all_tags(get_the_archive_title()),
		'description' => $aihl_archive_description !== '' ? $aihl_archive_description : __('Raccolta dei contenuti pubblicati in questa sezione.', AIHL_TEXT_DOMAIN),
		'icon' => 'fa-regular fa-folder-open',
	)); ?>

	<div class="row g-5">
		<section class="<?php echo esc_attr($aihl_blog_sidebar ? 'col-12 col-lg-8' : 'col-12'); ?>" aria-labelledby="aihl-archive-results-title">
			<h2 id="aihl-archive-results-title" class="visually-hidden"><?php esc_html_e('Elenco contenuti archivio', AIHL_TEXT_DOMAIN); ?></h2>
			<?php if (have_posts()) : ?>
				<div class="row g-4">
					<?php while (have_posts()) : the_post(); ?>
						<div class="col-12 col-md-6 col-xl-4">
							<?php get_template_part('template-parts/card-post', null, array(
								'layout' => 'vertical',
								'excerpt_words' => 18,
								'heading_class' => 'h5 mb-2',
							)); ?>
						</div>
					<?php endwhile; ?>
				</div>
				<?php aihl_render_template_pagination(); ?>
			<?php else : ?>
				<?php aihl_render_posts_empty_state(); ?>
			<?php endif; ?>
		</section>

		<?php if ($aihl_blog_sidebar) : ?>
			<aside class="col-12 col-lg-4" aria-label="<?php esc_attr_e('Sidebar archivio', AIHL_TEXT_DOMAIN); ?>">
				<?php if (is_active_sidebar('blog-sidebar')) : ?>
					<?php dynamic_sidebar('blog-sidebar'); ?>
				<?php else : ?>
					<div class="aihl-template-panel">
						<h2 class="h5 mb-3"><?php esc_html_e('Categorie', AIHL_TEXT_DOMAIN); ?></h2>
						<ul class="list-unstyled small mb-0">
							<?php wp_list_categories(array('title_li' => '', 'show_count' => true)); ?>
						</ul>
					</div>
				<?php endif; ?>
			</aside>
		<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>
