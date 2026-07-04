<?php /*
* ver:2024-0429-1556
*/ ?>
<?php add_action('init', function(){if(is_customize_preview()){if(aihtml_is_plugin_active('smart-customizer-frameworks/smart-customizer-frameworks.php')){?>
<?php add_action('customize_register',function($wp_customize){
	// -------------------------------------------------------------------------------------------------------------/ Section - Reset
	$wp_customize->add_section(AIHL_THEME_BASE.'_reset_section',array(
		'title' 			=> 'Reset',
		'panel'				=> AIHL_THEME_BASE.'_personalize_panel',
	));
});?>
<?php add_action('customize_register',function($wp_customize){
	// ----------------------------------------------------------------------/ General
	smart_customizer_toggle_add::add($wp_customize,array(
		'id' 			=> AIHL_OPTION_BASE.'_'.'reset['.AIHL_OPTION_BASE.'_'.'reset'.'_'.'general]',
		'section' 		=> AIHL_THEME_BASE.'_reset_section',
		'label' 		=> 'Generale',
		'description' 	=> 'Riporta alle condizioni di default le impostazioni del tema',
		'default' 		=> false,
		'selector' 		=> '',
	));
	smart_customizer_divider_add::render($wp_customize,AIHL_OPTION_BASE.'_'.'reset'.'_'.'general',AIHL_THEME_BASE.'_reset_section');
	// ----------------------------------------------------------------------/ Script
	smart_customizer_toggle_add::add($wp_customize,array(
		'id' 			=> AIHL_OPTION_BASE.'_'.'reset['.AIHL_OPTION_BASE.'_'.'reset'.'_'.'script]',
		'section' 		=> AIHL_THEME_BASE.'_reset_section',
		'label' 		=> 'Script',
		'description' 	=> 'Riporta alle condizioni di default le impostazioni del tema',
		'default' 		=> false,
		'selector' 		=> '',
	));
	smart_customizer_divider_add::render($wp_customize,AIHL_OPTION_BASE.'_'.'reset'.'_'.'script',AIHL_THEME_BASE.'_reset_section');
	// ----------------------------------------------------------------------/ Page
	smart_customizer_toggle_add::add($wp_customize,array(
		'id' 			=> AIHL_OPTION_BASE.'_'.'reset['.AIHL_OPTION_BASE.'_'.'reset'.'_'.'page]',
		'section' 		=> AIHL_THEME_BASE.'_reset_section',
		'label' 		=> 'Page',
		'description' 	=> 'Riporta alle condizioni di default le impostazioni del tema',
		'default' 		=> false,
		'selector' 		=> '',
	));
	smart_customizer_divider_add::render($wp_customize,AIHL_OPTION_BASE.'_'.'reset'.'_'.'page',AIHL_THEME_BASE.'_reset_section');
});?>
<?php /*
* Reset
* ver:2024-0429-1556
*/ ?>
<?php add_action('customize_register',function($wp_customize){
	// ----------------------------------------------------------------------/ Plugin - Active
	smart_customizer_toggle_add::add($wp_customize,array(
		'id' 			=> AIHL_OPTION_BASE.'_'.'reset['.AIHL_OPTION_BASE.'_'.'reset'.'_'.'plugin]',
		'section' 		=> AIHL_THEME_BASE.'_reset_section',
		'label' 		=> 'Plugin',
		'description' 	=> 'Riporta alle condizioni di default il plug-in',
		'default' 		=> false,
		'selector' 		=> AIHL_OPTION_BASE.'_'.'reset'.'_'.'plugin',
	));
	smart_customizer_divider_add::render($wp_customize,AIHL_OPTION_BASE.'_'.'reset'.'_'.'plugin',AIHL_THEME_BASE.'_reset_section');
	// ----------------------------------------------------------------------/ Reset - Active
	smart_customizer_toggle_add::add($wp_customize,array(
		'id' 			=> AIHL_OPTION_BASE.'_'.'reset['.AIHL_OPTION_BASE.'_'.'reset'.'_'.'active]',
		'section' 		=> AIHL_THEME_BASE.'_reset_section',
		'label' 		=> 'Reset',
		'description' 	=> 'Attiva il reset delle schede selezionate',
		'default' 		=> false,
		'selector' 		=> AIHL_OPTION_BASE.'_'.'reset'.'_'.'active',
	));
	smart_customizer_divider_add::render($wp_customize,AIHL_OPTION_BASE.'_'.'reset'.'_'.'active',AIHL_THEME_BASE.'_reset_section');
});?>
<?php }}});?>
