<?php
/**
 * Template: categoria.
 *
 * Integra Smart Builder Site nella parte alta e mantiene un fallback nativo indicizzabile.
 *
 * @package AI_HTML
 */

get_header();

$aihl_category = get_queried_object();
$aihl_excluded_posts = array();
$aihl_paged = max(1, (int) get_query_var('paged'));
$aihl_subcategories = array();

if ($aihl_category instanceof WP_Term) {
	$aihl_subcategories = get_categories(array(
		'child_of' => $aihl_category->term_id,
		'hide_empty' => true,
		'hierarchical' => 1,
		'depth' => 1,
		'parent' => $aihl_category->term_id,
		'orderby' => 'name',
		'order' => 'ASC',
	));
}
?>
<main id="main" class="container site-main aihl-template-page aihl-template-page-category py-4">
	<?php aihl_render_breadcrumbs(array('class' => 'mb-3')); ?>
	<?php aihl_render_template_hero(array(
		'eyebrow' => __('Categoria', AIHL_TEXT_DOMAIN),
		'title' => $aihl_category instanceof WP_Term ? $aihl_category->name : single_cat_title('', false),
		'description' => $aihl_category instanceof WP_Term ? term_description($aihl_category) : '',
		'icon' => 'fa-regular fa-newspaper',
	)); ?>

	<?php if ($aihl_paged === 1 && $aihl_category instanceof WP_Term && aihtml_is_site_builder_active() && function_exists('bldComposeWidget')) : ?>
		<section class="aihl-category-builder mb-5" aria-label="<?php esc_attr_e('Contenuti in evidenza categoria', AIHL_TEXT_DOMAIN); ?>">
			<?php
			$aihl_primary_widget = new bldComposeWidget(array(
				'item_slug' => $aihl_category->slug,
				'item_tax' => 'category',
				'item_widget' => 'open',
				'item_active' => 'on',
				'esclude_post' => $aihl_excluded_posts,
			));
			$aihl_excluded_posts = is_object($aihl_primary_widget) && isset($aihl_primary_widget->esclude_post) ? (array) $aihl_primary_widget->esclude_post : $aihl_excluded_posts;

			if (!empty($aihl_subcategories)) {
				foreach ($aihl_subcategories as $aihl_subcategory) {
					$aihl_secondary_widget = new bldComposeWidget(array(
						'item_slug' => $aihl_subcategory->slug,
						'item_tax' => 'category',
						'item_widget' => 'gruppoxl',
						'item_active' => 'on',
						'esclude_post' => $aihl_excluded_posts,
					));
					$aihl_excluded_posts = is_object($aihl_secondary_widget) && isset($aihl_secondary_widget->esclude_post) ? (array) $aihl_secondary_widget->esclude_post : $aihl_excluded_posts;
				}
			}
			?>
		</section>
	<?php endif; ?>

	<section aria-labelledby="aihl-category-posts-title">
		<h2 id="aihl-category-posts-title" class="h4 mb-4">
			<?php
			printf(
				esc_html__('Altri contenuti in %s', AIHL_TEXT_DOMAIN),
				esc_html($aihl_category instanceof WP_Term ? $aihl_category->name : single_cat_title('', false))
			);
			?>
		</h2>

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
			<?php aihl_render_load_more($aihl_excluded_posts); ?>
			<?php aihl_render_template_pagination(); ?>
		<?php else : ?>
			<?php aihl_render_posts_empty_state(); ?>
		<?php endif; ?>
	</section>
</main>
<?php get_footer(); ?>
