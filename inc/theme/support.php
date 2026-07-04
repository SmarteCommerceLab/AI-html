<?php
/* Setup Theme					*/
add_action('after_setup_theme',function()							{
	add_theme_support('html5', array('gallery','caption'));
	add_theme_support('menus');
	add_theme_support('post-thumbnails');
	add_theme_support('title-tag');
	add_theme_support('custom-logo');
});
/* Widget Areas */
add_action('widgets_init', function() {
	register_sidebar(array(
		'name'          => __('Blog Sidebar', AIHL_TEXT_DOMAIN),
		'id'            => 'blog-sidebar',
		'description'   => __('Widget area per la sidebar del blog e degli archivi.', AIHL_TEXT_DOMAIN),
		'before_widget' => '<div id="%1$s" class="widget %2$s border rounded p-3 mb-4">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="h6 widget-title mb-2">',
		'after_title'   => '</h3>',
	));
});
/* Gravatr - Customize fileds	*/
add_filter('avatar_defaults',function($avatar_defaults) 			{
	$avatar_defaults[get_site_icon_url()] = get_bloginfo('name');
return $avatar_defaults;});
/* Post Sub Title - Display 	*/
function the_sub_title($before = '',$after = '',$display = true) 	{global $post;
	$text = esc_html(get_post_meta($post->ID,'post-sub-title-value',true));
	if(strlen($text) === 0) {return;}
	$text = $before.$text.$after;
	if ($display){echo $text;}else{return $text;}
}
