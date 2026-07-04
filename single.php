<?php
/**
 * Template: articolo singolo.
 *
 * Layout editoriale con microdata Article, metadati leggibili e sezioni riusabili.
 *
 * @package AI_HTML
 */

get_header();
do_action('aihl_before_main_content');

if (have_posts()) :
	while (have_posts()) :
		the_post();
		$aihl_show_related = (bool) aihtml_option_value('article_related', false);
		$aihl_author_id = (int) get_the_author_meta('ID');
		$aihl_author_box_style = function_exists('aihl_get_author_box_style')
			? aihl_get_author_box_style($aihl_author_id)
			: aihtml_option_value('article_author_box_style', 'card');
		?>
		<main id="main" class="container site-main aihl-template-page aihl-template-page-single py-4">
			<article id="post-<?php the_ID(); ?>" <?php post_class('aihl-single-article'); ?> itemscope itemtype="https://schema.org/Article">
				<?php aihl_render_breadcrumbs(array('class' => 'mb-4')); ?>

				<header class="aihl-single-header mb-5">
					<?php
					$aihl_primary_category = get_post_primary_category(get_the_ID());
					if (!empty($aihl_primary_category['primary_category']) && $aihl_primary_category['primary_category'] instanceof WP_Term) :
						$aihl_category = $aihl_primary_category['primary_category'];
						?>
						<a class="aihl-template-eyebrow text-uppercase fw-semibold mb-2 d-inline-block" href="<?php echo esc_url(get_category_link($aihl_category)); ?>">
							<?php echo esc_html($aihl_category->name); ?>
						</a>
					<?php endif; ?>

					<?php the_title('<h1 class="entry-title display-5 fw-bold mb-3" itemprop="headline">', '</h1>'); ?>
					<?php the_sub_title('<p class="lead text-muted mb-4">', '</p>'); ?>

					<div class="aihl-single-meta d-flex flex-wrap align-items-center gap-3 text-muted">
						<div class="d-flex align-items-center gap-2" itemprop="author" itemscope itemtype="https://schema.org/Person">
							<?php echo get_avatar($aihl_author_id, 48, '', '', array('class' => 'rounded-circle')); ?>
							<a class="link text-body fw-semibold" href="<?php echo esc_url(get_author_posts_url($aihl_author_id)); ?>" rel="author" itemprop="url">
								<span itemprop="name"><?php echo esc_html(get_the_author()); ?></span>
							</a>
						</div>
						<time datetime="<?php echo esc_attr(get_the_date('c')); ?>" itemprop="datePublished">
							<?php echo esc_html(get_the_date('j F Y')); ?>
						</time>
						<meta itemprop="dateModified" content="<?php echo esc_attr(get_the_modified_date('c')); ?>">
					</div>
				</header>

				<?php if (has_post_thumbnail()) : ?>
					<figure class="aihl-single-featured figure w-100 mb-5" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
						<?php the_post_thumbnail('large', array(
							'decoding' => 'async',
							'fetchpriority' => 'high',
							'class' => 'img-fluid w-100 rounded-3',
							'alt' => get_the_title(),
						)); ?>
						<meta itemprop="url" content="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>">
						<?php if (get_the_post_thumbnail_caption() !== '') : ?>
							<figcaption class="figure-caption mt-2"><?php echo esc_html(get_the_post_thumbnail_caption()); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>

				<div class="aihl-single-layout">
					<aside class="aihl-single-share" aria-label="<?php esc_attr_e('Condivisione articolo', AIHL_TEXT_DOMAIN); ?>">
						<span class="aihl-single-share-label"><?php esc_html_e('Condividi', AIHL_TEXT_DOMAIN); ?></span>
						<?php get_template_part('template-parts/share-buttons', null, array('style' => 'responsive')); ?>
					</aside>

					<div class="aihl-single-body">
						<div class="entry-content aihl-single-content" itemprop="articleBody">
							<?php the_content(); ?>
						</div>

						<?php
						wp_link_pages(array(
							'before' => '<nav class="page-links my-4" aria-label="' . esc_attr__('Pagine articolo', AIHL_TEXT_DOMAIN) . '">',
							'after' => '</nav>',
						));
						?>

						<?php $aihl_tags = get_the_tags(); ?>
						<?php if (!empty($aihl_tags)) : ?>
							<footer class="aihl-single-tags border-top border-bottom py-3 my-5">
								<h2 class="h6 mb-3"><?php esc_html_e('Argomenti trattati', AIHL_TEXT_DOMAIN); ?></h2>
								<ul class="list-inline mb-0">
									<?php foreach ($aihl_tags as $aihl_tag) : ?>
										<li class="list-inline-item mb-2">
											<a class="btn btn-sm btn-outline-primary rounded-pill" href="<?php echo esc_url(get_tag_link($aihl_tag->term_id)); ?>">
												<?php echo esc_html($aihl_tag->name); ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</footer>
						<?php endif; ?>

						<?php if ($aihl_author_box_style !== 'none') : ?>
						<section class="aihl-single-author mb-5" aria-label="<?php esc_attr_e('Autore', AIHL_TEXT_DOMAIN); ?>">
							<?php get_template_part('template-parts/author-box', null, array(
								'author_id'  => $aihl_author_id,
								'avatar_size' => 80,
								'style'      => $aihl_author_box_style,
							)); ?>
						</section>
						<?php endif; ?>

						<?php if ($aihl_show_related) : ?>
							<?php get_template_part('template-parts/related-posts', null, array('count' => 3)); ?>
						<?php endif; ?>
					</div>
				</div>
			</article>
		</main>
		<?php
	endwhile;
	wp_reset_postdata();
endif;

do_action('aihl_after_main_content');
get_footer();
