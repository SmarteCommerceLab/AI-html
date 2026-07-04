<?php
/**
 * Template Part: Related Posts
 *
 * Articoli correlati per categoria primaria. Max 3 post.
 * Da usare in single.php dopo il contenuto.
 *
 * @since 1.2.0
 */
if (!defined('ABSPATH')) {
	exit;
}

$aihl_related_count = isset($args['count']) ? (int) $args['count'] : 3;
$aihl_related_current = get_the_ID();
$aihl_related_cats = wp_get_post_categories($aihl_related_current, array('fields' => 'ids'));

if (empty($aihl_related_cats)) {
	return;
}

$aihl_related_query = new WP_Query(array(
	'category__in'        => $aihl_related_cats,
	'post__not_in'        => array($aihl_related_current),
	'posts_per_page'      => $aihl_related_count,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
	'orderby'             => 'rand',
));

if (!$aihl_related_query->have_posts()) {
	wp_reset_postdata();
	return;
}
?>
<section class="aihl-related-posts mt-4 pt-4 border-top">
	<h3 class="h5 mb-3"><?php esc_html_e('Articoli correlati', AIHL_TEXT_DOMAIN); ?></h3>
	<div class="row g-3">
		<?php while ($aihl_related_query->have_posts()) : $aihl_related_query->the_post(); ?>
			<div class="col-12 col-md-4">
				<?php get_template_part('template-parts/card-post', null, array(
					'layout'         => 'vertical',
					'excerpt_words'  => 12,
					'heading_tag'    => 'h4',
					'heading_class'  => 'h6 mb-1',
				)); ?>
			</div>
		<?php endwhile; ?>
	</div>
</section>
<?php
wp_reset_postdata();
