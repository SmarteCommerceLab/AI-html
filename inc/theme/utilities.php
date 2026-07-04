<?php
/*
* Safe wrapper for plugin checks in frontend and customizer.
*/
if (!function_exists('aihtml_is_plugin_active')) {
	function aihtml_is_plugin_active($plugin_path){
		$plugin_path = (string) $plugin_path;
		$legacy_site_builder_paths = array(
			'smart-site-builder/smart-site-builder.php',
			'wp-smart-site-builder/wp-smart-site-builder.php',
			'smart-site-builder/wp-smart-site-builder.php',
		);

		if (in_array($plugin_path, $legacy_site_builder_paths, true)) {
			if (defined('SBS_BASENAME') && SBS_BASENAME !== '') {
				$plugin_path = SBS_BASENAME;
			} else {
				$plugin_path = 'smart-builder-site/smart-builder-site.php';
			}
		}

		if (function_exists('is_plugin_active')) {
			return is_plugin_active($plugin_path);
		}

		if (!function_exists('get_option')) {
			return false;
		}

		$active_plugins = (array) get_option('active_plugins', array());
		if (in_array($plugin_path, $active_plugins, true)) {
			return true;
		}

		if (function_exists('is_multisite') && is_multisite()) {
			$network_plugins = (array) get_site_option('active_sitewide_plugins', array());
			return isset($network_plugins[$plugin_path]);
		}

		return false;
	}
}

if (!function_exists('aihtml_is_site_builder_active')) {
	function aihtml_is_site_builder_active(){
		$paths = array(
			'smart-builder-site/smart-builder-site.php',
			'wp-smart-site-builder/wp-smart-site-builder.php',
			'smart-site-builder/smart-site-builder.php',
			'smart-site-builder/wp-smart-site-builder.php',
		);

		foreach ($paths as $path) {
			if (aihtml_is_plugin_active($path)) {
				return true;
			}
		}

		return false;
	}
}

if (!function_exists('aihtml_option_value')) {
	function aihtml_option_value($field, $default = '') {
		$options = get_option(AIHL_OPTION_BASE . '_general', array());
		if (!is_array($options)) {
			return $default;
		}
		if (!array_key_exists($field, $options)) {
			return $default;
		}
		$value = $options[$field];
		return $value === '' || $value === null ? $default : $value;
	}
}

if (!function_exists('aihl_get_site_logo_data')) {
	function aihl_get_site_logo_data($variant = 'default') {
		$variant = sanitize_key((string) $variant);
		$option_map = array(
			'default' => 'site_logo_url',
			'transparent' => 'site_logo_transparent_url',
			'light' => 'site_logo_light_url',
			'footer' => 'footer_logo_url',
		);
		if (!isset($option_map[$variant])) {
			$variant = 'default';
		}

		if (function_exists('smart_site_builder_image_logo')) {
			$logo = smart_site_builder_image_logo($variant);
			if (is_array($logo) && !empty($logo['url'])) {
				return $logo;
			}
		}

		$logo_url = esc_url_raw((string) aihtml_option_value($option_map[$variant], ''));
		if ('' === $logo_url && 'default' !== $variant) {
			$logo_url = esc_url_raw((string) aihtml_option_value('site_logo_url', ''));
		}
		if ('' !== $logo_url) {
			return array(
				'ID' => 0,
				'url' => $logo_url,
				'width' => 0,
				'height' => 0,
				'source' => 'ai-html-url',
				'variant' => $variant,
			);
		}

		if (function_exists('has_custom_logo') && has_custom_logo()) {
			$logo_id = (int) get_theme_mod('custom_logo');
			$image = wp_get_attachment_image_src($logo_id, 'full');
			if (is_array($image) && !empty($image[0])) {
				return array(
					'ID' => $logo_id,
					'url' => (string) $image[0],
					'width' => isset($image[1]) ? (int) $image[1] : 0,
					'height' => isset($image[2]) ? (int) $image[2] : 0,
					'source' => 'wordpress-custom-logo',
					'variant' => $variant,
				);
			}
		}

		return false;
	}
}

if (!function_exists('aihl_get_dark_surface_logo_variant')) {
	function aihl_get_dark_surface_logo_variant() {
		$light_logo = esc_url_raw((string) aihtml_option_value('site_logo_light_url', ''));
		if ($light_logo !== '') {
			return 'light';
		}

		$transparent_logo = esc_url_raw((string) aihtml_option_value('site_logo_transparent_url', ''));
		if ($transparent_logo !== '') {
			return 'transparent';
		}

		return 'default';
	}
}

if (!function_exists('aihl_render_site_logo')) {
	function aihl_render_site_logo($variant = 'default', $class = 'img-fluid aihl-site-logo') {
		$logo = aihl_get_site_logo_data($variant);
		if (!is_array($logo) || empty($logo['url'])) {
			return false;
		}

		$attributes = array(
			'class' => sanitize_text_field((string) $class),
			'src' => esc_url((string) $logo['url']),
			'alt' => esc_attr(get_bloginfo('name')),
			'decoding' => 'async',
		);
		if (!empty($logo['width'])) {
			$attributes['width'] = (string) absint($logo['width']);
		}
		if (!empty($logo['height'])) {
			$attributes['height'] = (string) absint($logo['height']);
		}

		$html = '<img';
		foreach ($attributes as $name => $value) {
			$html .= ' ' . esc_attr($name) . '="' . esc_attr($value) . '"';
		}
		$html .= '>';
		echo $html;
		return true;
	}
}

if (!function_exists('aihl_render_mobile_offcanvas_brand')) {
	function aihl_render_mobile_offcanvas_brand($variant = 'default', $id = 'offcanvasNavbarLabel') {
		?>
		<a
			class="aihl-mobile-menu-brand"
			id="<?php echo esc_attr($id); ?>"
			href="<?php echo esc_url(home_url('/')); ?>"
			aria-label="<?php echo esc_attr(sprintf(__('Homepage di %s', AIHL_TEXT_DOMAIN), get_bloginfo('name'))); ?>"
		>
			<?php if (!aihl_render_site_logo($variant, 'aihl-mobile-menu-logo')) : ?>
				<span class="aihl-mobile-menu-brand-text"><?php bloginfo('name'); ?></span>
			<?php endif; ?>
		</a>
		<?php
	}
}

if (!function_exists('aihl_get_site_builder_social_links')) {
	function aihl_get_site_builder_social_links() {
		if (!aihtml_is_site_builder_active() || !function_exists('smart_site_builder_social_link')) {
			return array();
		}

		$social_links = smart_site_builder_social_link();
		return is_array($social_links) ? $social_links : array();
	}
}

if (!function_exists('aihl_render_social_links')) {
	function aihl_render_social_links($button_class = 'btn btn-outline-primary btn-square me-2') {
		$social_links = aihl_get_site_builder_social_links();
		if (empty($social_links)) {
			return;
		}

		$items = array(
			'facebook'  => array('label' => 'Facebook', 'icon' => 'fab fa-facebook-f'),
			'twitter'   => array('label' => 'Twitter', 'icon' => 'fab fa-twitter'),
			'instagram' => array('label' => 'Instagram', 'icon' => 'fab fa-instagram'),
			'linkedin'  => array('label' => 'LinkedIn', 'icon' => 'fab fa-linkedin-in'),
			'youtube'   => array('label' => 'YouTube', 'icon' => 'fab fa-youtube'),
		);

		foreach ($items as $key => $item) {
			if (empty($social_links[$key])) {
				continue;
			}

			$link_classes = trim($button_class . ' aihl-social-link aihl-social-link-' . $key);

			printf(
				'<a class="%1$s" href="%2$s" aria-label="%3$s" rel="noopener nofollow" target="_blank"><i class="%4$s"></i></a>',
				esc_attr($link_classes),
				esc_url($social_links[$key]),
				esc_attr($item['label']),
				esc_attr($item['icon'])
			);
		}
	}
}

if (!function_exists('aihl_render_breadcrumbs')) {
	function aihl_render_breadcrumbs($args = array()) {
		if (!function_exists('seodots_bootstrap_breadcrumbs')) {
			return;
		}

		seodots_bootstrap_breadcrumbs($args);
	}
}

if (!function_exists('aihl_render_load_more')) {
	function aihl_render_load_more($excluded_posts = array()) {
		if (!function_exists('wp_seodots_nopaging_load_more')) {
			return;
		}

		wp_seodots_nopaging_load_more((array) $excluded_posts);
	}
}

if (!function_exists('aihl_render_template_hero')) {
	function aihl_render_template_hero($args = array()) {
		$args = wp_parse_args($args, array(
			'eyebrow' => '',
			'title' => '',
			'description' => '',
			'class' => '',
			'icon' => '',
			'search' => false,
			'actions' => array(),
		));

		$title = trim((string) $args['title']);
		if ($title === '') {
			return;
		}

		$classes = trim('aihl-template-hero py-5 mb-5 ' . (string) $args['class']);
		?>
		<section class="<?php echo esc_attr($classes); ?>" aria-labelledby="aihl-template-title">
			<div class="row align-items-center g-4">
				<div class="col-12 col-lg-8">
					<?php if ($args['eyebrow'] !== '') : ?>
						<p class="aihl-template-eyebrow text-uppercase fw-semibold mb-2"><?php echo esc_html($args['eyebrow']); ?></p>
					<?php endif; ?>
					<h1 id="aihl-template-title" class="display-5 fw-bold mb-3"><?php echo esc_html($title); ?></h1>
					<?php if ($args['description'] !== '') : ?>
						<div class="aihl-template-description lead text-muted mb-0"><?php echo wp_kses_post((string) $args['description']); ?></div>
					<?php endif; ?>
				</div>
				<div class="col-12 col-lg-4">
					<?php if ($args['search']) : ?>
						<?php aihtml_search_form_html(array('show_divider' => false, 'class' => 'aihl-template-search mb-0')); ?>
					<?php elseif ($args['icon'] !== '') : ?>
						<div class="aihl-template-hero-icon ms-lg-auto" aria-hidden="true">
							<i class="<?php echo esc_attr((string) $args['icon']); ?>"></i>
						</div>
					<?php endif; ?>
					<?php if (!empty($args['actions']) && is_array($args['actions'])) : ?>
						<div class="d-flex flex-wrap gap-2 justify-content-lg-end mt-3">
							<?php foreach ($args['actions'] as $action) :
								if (empty($action['label']) || empty($action['url'])) {
									continue;
								}
								$button_class = !empty($action['class']) ? (string) $action['class'] : 'btn btn-primary';
								?>
								<a class="<?php echo esc_attr($button_class); ?>" href="<?php echo esc_url((string) $action['url']); ?>">
									<?php echo esc_html((string) $action['label']); ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}

if (!function_exists('aihl_render_template_pagination')) {
	function aihl_render_template_pagination($class = 'mt-5 mb-4') {
		echo '<nav class="' . esc_attr((string) $class) . '" aria-label="' . esc_attr__('Navigazione contenuti', AIHL_TEXT_DOMAIN) . '">';
		if (function_exists('wp_bs_pagination')) {
			wp_bs_pagination();
		} else {
			the_posts_pagination(array(
				'mid_size' => 2,
				'prev_text' => __('Precedenti', AIHL_TEXT_DOMAIN),
				'next_text' => __('Successivi', AIHL_TEXT_DOMAIN),
			));
		}
		echo '</nav>';
	}
}

if (!function_exists('aihl_render_posts_empty_state')) {
	function aihl_render_posts_empty_state($title = '', $description = '') {
		$title = $title !== '' ? $title : __('Nessun contenuto disponibile', AIHL_TEXT_DOMAIN);
		$description = $description !== '' ? $description : __('Prova a usare la ricerca o torna alla pagina principale.', AIHL_TEXT_DOMAIN);
		?>
		<section class="aihl-empty-state text-center py-5 my-4">
			<i class="fa-regular fa-folder-open display-5 text-primary mb-3" aria-hidden="true"></i>
			<h2 class="h4"><?php echo esc_html($title); ?></h2>
			<p class="text-muted mb-4"><?php echo esc_html($description); ?></p>
			<?php aihtml_search_form_html(array('show_divider' => false, 'class' => 'aihl-empty-search mx-auto')); ?>
		</section>
		<?php
	}
}

if (!function_exists('aihl_page_has_sbs_fullscreen_hero')) {
	function aihl_page_has_sbs_fullscreen_hero($page_id = null) {
		$page_id = null === $page_id ? (int) get_queried_object_id() : (int) $page_id;
		if ($page_id <= 0 || !is_page($page_id)) {
			return false;
		}

		$page_group = get_option('smart_site_builder_option_page', array());
		if (!is_array($page_group)) {
			return false;
		}

		$payloads = array(
			isset($page_group['sbs_page_' . $page_id . '_builder_json']) ? (string) $page_group['sbs_page_' . $page_id . '_builder_json'] : '',
			isset($page_group['sbs_page_' . $page_id . '_compose_json']) ? (string) $page_group['sbs_page_' . $page_id . '_compose_json'] : '',
		);

		foreach ($payloads as $payload) {
			if ('' === trim($payload)) {
				continue;
			}

			$widgets = json_decode($payload, true);
			if (is_array($widgets)) {
				foreach ($widgets as $widget) {
					if (!is_array($widget)) {
						continue;
					}
					$widget_code = isset($widget['widget']) ? sanitize_key((string) $widget['widget']) : '';
					if (in_array($widget_code, array('widget_4', 'hero_video_fullscreen', 'hero_legacy', 'widget_7', 'hero_legacy_full_visual', 'widget_13', 'hero_magazine_video'), true)) {
						return true;
					}
				}
			}

			if (preg_match('/\"widget\"\\s*:\\s*\"(?:widget_4|hero_video_fullscreen|hero_legacy|widget_7|hero_legacy_full_visual|widget_13|hero_magazine_video)\"/', $payload)) {
				return true;
			}
		}

		return false;
	}
}

if (!is_admin()){
add_filter('body_class', function($classes){
	if (!is_array($classes)) {
		$classes = array();
	}

	if (aihtml_is_site_builder_active()) {
		$classes[] = 'aihl-with-smart-builder-site';
	}

	if (is_page()) {
		$template = get_page_template_slug(get_queried_object_id());
		if (in_array($template, array('smart-site-home.php', 'smart-site-blog.php', 'smart-site-builder.php'), true)) {
			$classes[] = 'aihl-smart-builder-template';
		}

		if (aihl_page_has_sbs_fullscreen_hero()) {
			$classes[] = 'aihl-has-fullscreen-hero';
		}
	}

	return array_values(array_unique($classes));
}, 20);

/*
* 
*/
function aihtml_customizer_separetor_add($wp_customize,$object,$section){
	$wp_customize->add_setting($object,array('sanitize_callback' => 'sanitize_text_field',));
	$wp_customize->add_control(new Mizer_Separator_Control($wp_customize,$object,array('section' => $section)));	
}
/*
* Wrapping Content Article
*/
add_action('wp_enqueue_scripts', function() {
	if (!is_single()) {
		return;
	}

	$option = aihl_register_class::check('article_content_size');
	if ($option) {
		$widht = isset($option['article_content_size']) ? absint($option['article_content_size']) : 1280;
		if ($widht < 640) {
			$widht = 640;
		}
		if ($widht > 1920) {
			$widht = 1920;
		}
	} else {
		$widht = 1280;
	}

	wp_add_inline_style(
		'ai-html-theme',
		'.header-main{max-width:' . $widht . 'px!important}main.container.site-main{max-width:' . $widht . 'px!important}footer .container{max-width:' . $widht . 'px!important}'
	);
}, 101);
/*
* Wrapping Content Article
*/
#add_action('the_content',function($content){return '<div id="single-content" class="entry-content">'.$content.'</div>';});
/*
* Get Menu Info
*/
function aihtml_menu_get_info($location_name){
	$locations = get_nav_menu_locations();
	if (!isset($locations[$location_name])) {
		return false;
	}

	$menu = wp_get_nav_menu_object($locations[$location_name]);
	if(is_object($menu)){
		#echo 'This menu exists!';
		#echo 'This menu has ' . $menu->count . ' menu items.';
		#echo 'This menu ID is ' . $menu->term_id . '.';
		#echo 'This menu Name is ' . $menu->name . '.';
	} else {
		#echo 'A menu with that name doesn\'t exist';
	}
return $menu;}
/*
* Jquery Remove - Completely Remove jQuery From WordPress
*/	
/*if(get_theme_mod('secMagazine_jquery_disable',false)==true){
	add_action('init', 'my_init');function my_init() {if (!is_admin()) {wp_deregister_script('jquery');wp_register_script	('jquery', false);}}
	// == deRegistrazione
	#wp_deregister_script('wp-embed');
	#wp_deregister_script('jquery');
	#wp_deregister_script('jquery-ui-core');		
}*/
/*
*	
* Get Primary Post Category
* https://www.lab21.gr/blog/wordpress-get-primary-category-post
--------------------*/
function get_post_primary_category($post_id, $term='category', $return_all_categories=false){
	$return = array();
	if (class_exists('WPSEO_Primary_Term')){
		// Show Primary category by Yoast if it is enabled & set
		$wpseo_primary_term = new WPSEO_Primary_Term( $term, $post_id );
		$primary_term = get_term($wpseo_primary_term->get_primary_term());

		if (!is_wp_error($primary_term)){
			$return['primary_category'] = $primary_term;
		}
	}
	if (empty($return['primary_category']) || $return_all_categories){
		$categories_list = get_the_terms($post_id, $term);

		if (empty($return['primary_category']) && !empty($categories_list)){
			$return['primary_category'] = $categories_list[0];  //get the first category
		}
		if ($return_all_categories){
			$return['all_categories'] = array();

			if (!empty($categories_list)){
				foreach($categories_list as &$category){
					$return['all_categories'][] = $category->term_id;
				}
			}
		}
	}
	return $return;
}
/*
* Escludere Categorie dalle query di pagine specifiche
* http://www.semanticstone.net/wordpress/snippet/wordpress-customizzare-la-query-taxonomy/
* https://codex.wordpress.org/it:Riferimento_funzioni/query_posts
* https://wordpress.stackexchange.com/questions/167032/exclude-particular-posts-in-archive-php
*/
/*add_action( 'pre_get_posts', function($query){
	if ( is_admin() || ! $query->is_main_query() )
		return;
	if ( $query->is_archive() ) {$query->set( 'category__not_in', array(get_theme_mod('journal_notinclude_category','')) );}
	if ( $query->is_search() ) {$query->set( 'category__not_in', array(get_theme_mod('journal_notinclude_category','')) );}
});*/
/**
*
* Search form html
*/
function aihtml_search_form_html($args = array()){
	$args = wp_parse_args($args, array(
		'class' => '',
		'id' => 'searchform',
		'show_divider' => true,
		'button_label' => __('Cerca', AIHL_TEXT_DOMAIN),
	));

	$form_class = trim('search-form mb-3 d-flex justify-content-center align-items-center border border-1 ' . (string) $args['class']);
	$form_id = sanitize_html_class((string) $args['id']);
	?>
    <form
        class	= "<?php echo esc_attr($form_class); ?>"
        role	= "search"
        method	= "get"
        id		= "<?php echo esc_attr($form_id); ?>"
        action	= "<?php echo esc_url(home_url('/')); ?>"
    >
        <input
            type 				= "search"
            class 				= "form-control border-0 m-2"
            placeholder 		= "<?php esc_attr_e('Cerca nel sito', AIHL_TEXT_DOMAIN); ?>"
            aria-label			= "<?php esc_attr_e('Cerca nel sito', AIHL_TEXT_DOMAIN); ?>"
            aria-describedby	= "basic-addon2"
            value				= "<?php echo esc_attr(get_search_query()); ?>"
            name				= "s"
            id					= "s"
        >
        <button
        	class	= "btn btn-primary m-2"
            type	= "submit"
            aria-label="<?php echo esc_attr((string) $args['button_label']); ?>"
		>
            <i class="fa-solid fa-search fa-xl"></i>
		</button>
    </form> 
    <?php if ($args['show_divider']) : ?>
    	<hr>
    <?php endif; ?>
<?php }

function aihtml_kses_embed_html($html) {
	$allowed = wp_kses_allowed_html('post');
	$allowed['iframe'] = array(
		'src' => true,
		'width' => true,
		'height' => true,
		'style' => true,
		'loading' => true,
		'allow' => true,
		'allowfullscreen' => true,
		'referrerpolicy' => true,
		'aria-label' => true,
		'title' => true,
	);

	return wp_kses($html, $allowed);
}
}
