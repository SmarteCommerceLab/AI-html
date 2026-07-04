<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('aihl_get_meta_description')) {
	function aihl_get_meta_description() {
		if (is_singular()) {
			$post = get_post();
			if ($post instanceof WP_Post) {
				$excerpt = has_excerpt($post) ? $post->post_excerpt : wp_trim_words(wp_strip_all_tags($post->post_content), 28, '...');
				$excerpt = trim((string) $excerpt);
				if ($excerpt !== '') {
					return $excerpt;
				}
			}
		}

		if (is_category() || is_tag() || is_tax()) {
			$term_description = trim(wp_strip_all_tags((string) term_description()));
			if ($term_description !== '') {
				return wp_trim_words($term_description, 28, '...');
			}
		}

		return wp_trim_words((string) get_bloginfo('description'), 28, '...');
	}
}

if (!function_exists('aihl_has_external_seo_plugin')) {
	function aihl_has_external_seo_plugin() {
		return defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION');
	}
}

if (!function_exists('aihl_get_canonical_url')) {
	function aihl_get_canonical_url() {
		if (is_singular()) {
			return get_permalink();
		}

		if (is_category() || is_tag() || is_tax()) {
			$term = get_queried_object();
			if ($term instanceof WP_Term) {
				$link = get_term_link($term);
				return is_wp_error($link) ? '' : $link;
			}
		}

		if (is_home() || is_front_page()) {
			return home_url('/');
		}

		if (is_author()) {
			return get_author_posts_url((int) get_queried_object_id());
		}

		return '';
	}
}

if (!function_exists('aihl_get_featured_image_url')) {
	function aihl_get_featured_image_url($post_id = 0) {
		$post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();
		if ($post_id > 0 && has_post_thumbnail($post_id)) {
			$image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
			if (is_array($image) && !empty($image[0])) {
				return esc_url_raw($image[0]);
			}
		}

		$icon = get_site_icon_url(512);
		return $icon ? esc_url_raw($icon) : '';
	}
}

if (!function_exists('aihl_get_featured_image_object')) {
	function aihl_get_featured_image_object($post_id = 0) {
		$post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();
		if ($post_id <= 0 || !has_post_thumbnail($post_id)) {
			return array();
		}

		$attachment_id = (int) get_post_thumbnail_id($post_id);
		$src = wp_get_attachment_image_src($attachment_id, 'full');
		if (!is_array($src) || empty($src[0])) {
			return array();
		}

		$image = array(
			'@type' => 'ImageObject',
			'url' => esc_url_raw($src[0]),
		);
		if (!empty($src[1])) {
			$image['width'] = (int) $src[1];
		}
		if (!empty($src[2])) {
			$image['height'] = (int) $src[2];
		}

		$alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
		if (is_string($alt) && trim($alt) !== '') {
			$image['caption'] = wp_strip_all_tags($alt);
		}

		return $image;
	}
}

if (!function_exists('aihl_get_logo_url')) {
	function aihl_get_logo_url() {
		if (function_exists('smart_site_builder_image_logo')) {
			$logo_data = smart_site_builder_image_logo();
			if (is_array($logo_data) && !empty($logo_data['url'])) {
				return esc_url_raw($logo_data['url']);
			}
		}

		if (function_exists('get_custom_logo') && has_custom_logo()) {
			$logo_id = (int) get_theme_mod('custom_logo');
			$logo_src = wp_get_attachment_image_src($logo_id, 'full');
			if (is_array($logo_src) && !empty($logo_src[0])) {
				return esc_url_raw($logo_src[0]);
			}
		}

		$icon = get_site_icon_url(512);
		return $icon ? esc_url_raw($icon) : '';
	}
}

if (!function_exists('aihl_get_social_sameas')) {
	function aihl_get_social_sameas() {
		if (!function_exists('aihl_get_site_builder_social_links')) {
			return array();
		}

		$raw = aihl_get_site_builder_social_links();
		if (!is_array($raw) || empty($raw)) {
			return array();
		}

		$same_as = array();
		foreach ($raw as $value) {
			$url = esc_url_raw((string) $value);
			if ($url !== '') {
				$same_as[] = $url;
			}
		}

		return array_values(array_unique($same_as));
	}
}

if (!function_exists('aihl_get_page_title')) {
	function aihl_get_page_title() {
		if (is_singular()) {
			return single_post_title('', false);
		}
		if (is_category() || is_tag() || is_tax()) {
			return single_term_title('', false);
		}
		if (is_search()) {
			return sprintf(__('Risultati ricerca per: %s', AIHL_TEXT_DOMAIN), get_search_query());
		}
		return wp_get_document_title();
	}
}

if (!function_exists('aihl_get_primary_category_chain')) {
	function aihl_get_primary_category_chain($post_id) {
		$post_id = (int) $post_id;
		if ($post_id <= 0) {
			return array();
		}

		$terms = get_the_category($post_id);
		if (!is_array($terms) || empty($terms)) {
			return array();
		}

		$primary = $terms[0];
		if (class_exists('WPSEO_Primary_Term')) {
			$wpseo_primary_term = new WPSEO_Primary_Term('category', $post_id);
			$primary_id = (int) $wpseo_primary_term->get_primary_term();
			if ($primary_id > 0) {
				$term = get_term($primary_id, 'category');
				if ($term instanceof WP_Term && !is_wp_error($term)) {
					$primary = $term;
				}
			}
		}

		$chain = array();
		$ancestors = array_reverse(get_ancestors((int) $primary->term_id, 'category', 'taxonomy'));
		foreach ($ancestors as $ancestor_id) {
			$ancestor = get_term((int) $ancestor_id, 'category');
			if ($ancestor instanceof WP_Term && !is_wp_error($ancestor)) {
				$chain[] = $ancestor;
			}
		}
		$chain[] = $primary;
		return $chain;
	}
}

add_action('wp_head', function() {
	if (aihl_has_external_seo_plugin()) {
		return;
	}

	$description = aihl_get_meta_description();
	if ($description === '') {
		return;
	}
	echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
}, 2);

add_action('wp_head', function() {
	if (aihl_has_external_seo_plugin()) {
		return;
	}

	$canonical = aihl_get_canonical_url();
	if ($canonical !== '') {
		echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
	}

	if (is_search() || is_404() || (is_paged() && !is_singular())) {
		echo '<meta name="robots" content="noindex,follow">' . "\n";
		return;
	}
	echo '<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">' . "\n";
}, 3);

add_action('wp_head', function() {
	if (aihl_has_external_seo_plugin()) {
		return;
	}

	$title = aihl_get_page_title();
	$description = aihl_get_meta_description();
	$url = aihl_get_canonical_url();
	$image = aihl_get_featured_image_url();
	$type = is_singular('post') ? 'article' : 'website';

	if ($url === '') {
		$request = isset($GLOBALS['wp']->request) ? (string) $GLOBALS['wp']->request : '';
		$url = $request !== '' ? home_url('/' . ltrim($request, '/')) : home_url('/');
	}

	echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
	echo '<meta property="og:locale" content="' . esc_attr(str_replace('-', '_', get_locale())) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr($type) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
	if ($image !== '') {
		echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
	if ($image !== '') {
		echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
	}
}, 4);

add_action('wp_head', function() {
	if (aihl_has_external_seo_plugin()) {
		return;
	}

	$site_name = get_bloginfo('name');
	$site_url = home_url('/');
	$logo_url = aihl_get_logo_url();
	$canonical = aihl_get_canonical_url();
	$same_as = aihl_get_social_sameas();

	$data = array(
		'@context' => 'https://schema.org',
		'@graph' => array(
			array(
				'@type' => 'Organization',
				'@id' => trailingslashit($site_url) . '#organization',
				'name' => $site_name,
				'url' => $site_url,
			),
			array(
				'@type' => 'WebSite',
				'@id' => trailingslashit($site_url) . '#website',
				'name' => $site_name,
				'url' => $site_url,
				'publisher' => array(
					'@id' => trailingslashit($site_url) . '#organization',
				),
				'potentialAction' => array(
					'@type' => 'SearchAction',
					'target' => home_url('/?s={search_term_string}'),
					'query-input' => 'required name=search_term_string',
				),
			),
		),
	);

	if ($logo_url !== '') {
		$data['@graph'][0]['logo'] = $logo_url;
	}
	if (!empty($same_as)) {
		$data['@graph'][0]['sameAs'] = $same_as;
	}
	if ($canonical !== '' && !is_search() && !is_404()) {
		$data['@graph'][] = array(
			'@type' => 'WebPage',
			'@id' => trailingslashit($canonical) . '#webpage',
			'url' => $canonical,
			'name' => aihl_get_page_title(),
			'isPartOf' => array(
				'@id' => trailingslashit($site_url) . '#website',
			),
		);
	}

	if (is_singular()) {
		$post = get_post();
		if ($post instanceof WP_Post) {
			$author_id = (int) $post->post_author;
			$author_schema = array(
				'@type' => 'Person',
				'name' => get_the_author_meta('display_name', $author_id),
				'url' => get_author_posts_url($author_id),
			);
			$author_same_as = array();
			foreach (array('facebook', 'twitter', 'instagram', 'youtube', 'linkedin', 'user_url') as $author_social_key) {
				$author_social_url = trim((string) get_the_author_meta($author_social_key, $author_id));
				if ($author_social_url !== '') {
					$author_same_as[] = esc_url_raw($author_social_url);
				}
			}
			$author_same_as = array_values(array_unique(array_filter($author_same_as)));
			if (!empty($author_same_as)) {
				$author_schema['sameAs'] = $author_same_as;
			}

			$article = array(
				'@type' => is_singular('post') ? 'Article' : 'WebPage',
				'@id' => trailingslashit(get_permalink($post)) . '#content',
				'headline' => get_the_title($post),
				'description' => aihl_get_meta_description(),
				'url' => get_permalink($post),
				'datePublished' => get_the_date('c', $post),
				'dateModified' => get_the_modified_date('c', $post),
				'author' => $author_schema,
				'publisher' => array(
					'@id' => trailingslashit($site_url) . '#organization',
				),
				'mainEntityOfPage' => array(
					'@id' => trailingslashit(get_permalink($post)) . '#webpage',
				),
			);

			$image_object = aihl_get_featured_image_object((int) $post->ID);
			if (!empty($image_object)) {
				$article['image'] = $image_object;
			} else {
				$image = aihl_get_featured_image_url((int) $post->ID);
				if ($image !== '') {
					$article['image'] = $image;
				}
			}
			if ($logo_url !== '') {
				$article['publisher'] = array(
					'@type' => 'Organization',
					'@id' => trailingslashit($site_url) . '#organization',
					'name' => $site_name,
					'logo' => array(
						'@type' => 'ImageObject',
						'url' => $logo_url,
					),
				);
			}

			$data['@graph'][] = $article;
		}
	}

	if ($canonical !== '' && !is_search() && !is_404()) {
		$breadcrumb_items = array(
			array(
				'@type' => 'ListItem',
				'position' => 1,
				'name' => __('Home', AIHL_TEXT_DOMAIN),
				'item' => home_url('/'),
			),
		);

		if (is_singular()) {
			$position = 2;
			if (is_singular('post')) {
				$chain = aihl_get_primary_category_chain((int) get_queried_object_id());
				foreach ($chain as $term) {
					$link = get_term_link($term);
					if (is_wp_error($link)) {
						continue;
					}
					$breadcrumb_items[] = array(
						'@type' => 'ListItem',
						'position' => $position++,
						'name' => $term->name,
						'item' => $link,
					);
				}
			}
			$breadcrumb_items[] = array(
				'@type' => 'ListItem',
				'position' => $position,
				'name' => get_the_title(),
				'item' => $canonical,
			);
		} elseif (is_category() || is_tag() || is_tax()) {
			$breadcrumb_items[] = array(
				'@type' => 'ListItem',
				'position' => 2,
				'name' => single_term_title('', false),
				'item' => $canonical,
			);
		}

		if (count($breadcrumb_items) > 1) {
			$data['@graph'][] = array(
				'@type' => 'BreadcrumbList',
				'itemListElement' => $breadcrumb_items,
			);
		}
	}

	echo '<script type="application/ld+json">' . wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}, 20);
