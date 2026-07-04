<?php
/**
 * Template Part: Card Post
 *
 * Card articolo riusabile. Supporta 3 layout via $args['layout']:
 *   - 'horizontal' (default) — thumbnail sx + testo dx (category/search)
 *   - 'vertical'             — thumbnail sopra + testo sotto (grid blog)
 *   - 'list'                 — titolo + excerpt senza thumbnail (minimal)
 *
 * @since 1.2.0
 */
if (!defined('ABSPATH')) {
	exit;
}

$aihl_card_layout = isset($args['layout']) ? $args['layout'] : 'horizontal';
$aihl_card_excerpt_words = isset($args['excerpt_words']) ? (int) $args['excerpt_words'] : 18;
$aihl_card_show_thumbnail = isset($args['show_thumbnail']) ? (bool) $args['show_thumbnail'] : true;
$aihl_card_show_excerpt = isset($args['show_excerpt']) ? (bool) $args['show_excerpt'] : true;
$aihl_card_heading_tag = isset($args['heading_tag']) ? $args['heading_tag'] : 'h2';
$aihl_card_heading_class = isset($args['heading_class']) ? $args['heading_class'] : 'h6 mb-2';
?>

<?php if ($aihl_card_layout === 'vertical') : ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('aihl-card-post aihl-card-vertical mb-4'); ?>>
	<?php if ($aihl_card_show_thumbnail && has_post_thumbnail()) : ?>
		<a class="aihl-card-thumb-link d-block mb-3 overflow-hidden rounded" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
			<?php the_post_thumbnail('medium_large', array(
				'class'    => 'img-fluid w-100 aihl-card-thumb',
				'alt'      => get_the_title(),
				'loading'  => 'lazy',
				'decoding' => 'async',
			)); ?>
		</a>
	<?php endif; ?>
	<?php get_template_part('template-parts/post-meta', null, array('style' => 'inline')); ?>
	<<?php echo esc_attr($aihl_card_heading_tag); ?> class="<?php echo esc_attr($aihl_card_heading_class); ?>">
		<a class="link text-body" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
	</<?php echo esc_attr($aihl_card_heading_tag); ?>>
	<?php if ($aihl_card_show_excerpt) : ?>
		<p class="small text-muted mb-0"><?php echo esc_html(wp_trim_words(get_the_excerpt(), $aihl_card_excerpt_words, '...')); ?></p>
	<?php endif; ?>
</article>

<?php elseif ($aihl_card_layout === 'list') : ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('aihl-card-post aihl-card-list mb-4 pb-4 border-bottom'); ?>>
	<<?php echo esc_attr($aihl_card_heading_tag); ?> class="<?php echo esc_attr($aihl_card_heading_class); ?>">
		<a class="link text-body" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
	</<?php echo esc_attr($aihl_card_heading_tag); ?>>
	<?php get_template_part('template-parts/post-meta', null, array('style' => 'inline')); ?>
	<?php if ($aihl_card_show_excerpt) : ?>
		<div class="entry-summary mt-1">
			<p class="small text-muted mb-0"><?php echo esc_html(wp_trim_words(get_the_excerpt(), $aihl_card_excerpt_words, '...')); ?></p>
		</div>
	<?php endif; ?>
</article>

<?php else : /* horizontal — default */ ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('aihl-card-post aihl-card-horizontal mb-3'); ?>>
	<div class="row d-flex align-items-center">
		<?php if ($aihl_card_show_thumbnail && has_post_thumbnail()) : ?>
			<div class="col-4">
				<a class="d-block overflow-hidden rounded" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
					<?php the_post_thumbnail('medium', array(
						'class'    => 'img-fluid mx-auto d-block aihl-card-thumb',
						'alt'      => get_the_title(),
						'loading'  => 'lazy',
						'decoding' => 'async',
					)); ?>
				</a>
			</div>
			<div class="col-8">
		<?php else : ?>
			<div class="col-12">
		<?php endif; ?>
			<<?php echo esc_attr($aihl_card_heading_tag); ?> class="<?php echo esc_attr($aihl_card_heading_class); ?>">
				<a class="link text-body" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
			</<?php echo esc_attr($aihl_card_heading_tag); ?>>
			<?php if ($aihl_card_show_excerpt) : ?>
				<p class="small text-muted mb-0"><?php echo esc_html(wp_trim_words(get_the_excerpt(), $aihl_card_excerpt_words, '...')); ?></p>
			<?php endif; ?>
			<?php get_template_part('template-parts/post-meta', null, array('style' => 'inline')); ?>
		</div>
	</div>
	<hr class="mt-3 mb-0">
</article>
<?php endif; ?>
