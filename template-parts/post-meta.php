<?php
/**
 * Template Part: Post Meta
 *
 * Mostra data, autore, categoria, tempo lettura.
 * $args['style']: 'inline' (default) | 'block' | 'minimal'
 *
 * @since 1.2.0
 */
if (!defined('ABSPATH')) {
	exit;
}

$aihl_meta_style = isset($args['style']) ? $args['style'] : 'inline';
$aihl_meta_show_author = isset($args['show_author']) ? (bool) $args['show_author'] : true;
$aihl_meta_show_date = isset($args['show_date']) ? (bool) $args['show_date'] : true;
$aihl_meta_show_category = isset($args['show_category']) ? (bool) $args['show_category'] : true;
$aihl_meta_show_reading_time = isset($args['show_reading_time']) ? (bool) $args['show_reading_time'] : true;

$aihl_reading_time = '';
if ($aihl_meta_show_reading_time) {
	$aihl_word_count = str_word_count(wp_strip_all_tags(get_the_content()));
	$aihl_minutes = max(1, (int) ceil($aihl_word_count / 200));
	/* translators: %d: minutes */
	$aihl_reading_time = sprintf(esc_html__('%d min lettura', AIHL_TEXT_DOMAIN), $aihl_minutes);
}

$aihl_primary_cat = '';
if ($aihl_meta_show_category) {
	if (function_exists('get_post_primary_category')) {
		$aihl_cat_data = get_post_primary_category(get_the_ID());
		if (!empty($aihl_cat_data['primary_category'])) {
			$aihl_primary_cat_obj = $aihl_cat_data['primary_category'];
		}
	}
	if (empty($aihl_primary_cat_obj)) {
		$aihl_cats = get_the_category();
		$aihl_primary_cat_obj = !empty($aihl_cats) ? $aihl_cats[0] : null;
	}
}
?>

<?php if ($aihl_meta_style === 'block') : ?>
<div class="aihl-post-meta aihl-post-meta-block mb-3">
	<?php if ($aihl_meta_show_category && !empty($aihl_primary_cat_obj)) : ?>
		<a class="badge text-bg-primary text-decoration-none mb-2 d-inline-block" href="<?php echo esc_url(get_category_link($aihl_primary_cat_obj->term_id)); ?>"><?php echo esc_html($aihl_primary_cat_obj->name); ?></a>
	<?php endif; ?>
	<?php if ($aihl_meta_show_date) : ?>
		<div class="text-muted small mb-1">
			<time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j F Y')); ?></time>
		</div>
	<?php endif; ?>
	<?php if ($aihl_meta_show_author) : ?>
		<div class="small">
			<?php esc_html_e('di', AIHL_TEXT_DOMAIN); ?>
			<a class="link fw-semibold" href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>" rel="author"><?php echo esc_html(get_the_author()); ?></a>
		</div>
	<?php endif; ?>
	<?php if ($aihl_reading_time !== '') : ?>
		<span class="text-muted small"><i class="fa-regular fa-clock me-1"></i><?php echo esc_html($aihl_reading_time); ?></span>
	<?php endif; ?>
</div>

<?php elseif ($aihl_meta_style === 'minimal') : ?>
<div class="aihl-post-meta aihl-post-meta-minimal small text-muted">
	<?php if ($aihl_meta_show_date) : ?>
		<time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j F Y')); ?></time>
	<?php endif; ?>
	<?php if ($aihl_reading_time !== '') : ?>
		&middot; <?php echo esc_html($aihl_reading_time); ?>
	<?php endif; ?>
</div>

<?php else : /* inline — default */ ?>
<div class="aihl-post-meta aihl-post-meta-inline d-flex flex-wrap align-items-center gap-2 small text-muted mt-1">
	<?php if ($aihl_meta_show_author) : ?>
		<span><?php esc_html_e('di', AIHL_TEXT_DOMAIN); ?> <a class="link" href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>" rel="author"><?php echo esc_html(get_the_author()); ?></a></span>
	<?php endif; ?>
	<?php if ($aihl_meta_show_date) : ?>
		<span><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j F Y')); ?></time></span>
	<?php endif; ?>
	<?php if ($aihl_meta_show_category && !empty($aihl_primary_cat_obj)) : ?>
		<span><a class="link text-primary" href="<?php echo esc_url(get_category_link($aihl_primary_cat_obj->term_id)); ?>"><?php echo esc_html($aihl_primary_cat_obj->name); ?></a></span>
	<?php endif; ?>
	<?php if ($aihl_reading_time !== '') : ?>
		<span><i class="fa-regular fa-clock me-1"></i><?php echo esc_html($aihl_reading_time); ?></span>
	<?php endif; ?>
</div>
<?php endif; ?>
