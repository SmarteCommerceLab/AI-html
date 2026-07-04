<?php
/* Register Menu */
add_action('init',function(){register_nav_menus( array(
	'topic'				=> __('Topic', AIHL_TEXT_DOMAIN),
	'utili'				=> __('Utili', AIHL_TEXT_DOMAIN),

	'naviga'			=> __('Naviga', AIHL_TEXT_DOMAIN),
	'footer'			=> __('Footer', AIHL_TEXT_DOMAIN),

	'topic_left'		=> __('Topic Left (Mega Centered)', AIHL_TEXT_DOMAIN),
	'topic_right'		=> __('Topic Right (Mega Centered)', AIHL_TEXT_DOMAIN),
	'footer_col_1'		=> __('Footer Colonna 1', AIHL_TEXT_DOMAIN),
	'footer_col_2'		=> __('Footer Colonna 2', AIHL_TEXT_DOMAIN),
	'footer_col_3'		=> __('Footer Colonna 3', AIHL_TEXT_DOMAIN),
	'footer_col_4'		=> __('Footer Colonna 4', AIHL_TEXT_DOMAIN),
));});