<?php /*
* ver:2024-0429-1556
*/ ?>
<?php add_action('init', function(){if(is_customize_preview()){if(aihtml_is_plugin_active('smart-customizer-frameworks/smart-customizer-frameworks.php')){?>
<?php
add_action('customize_register',function($wp_customize){
	// -------------------------------------------------------------------------------------------------------------/ Panel - Smart Lite Core
	$wp_customize->add_panel(AIHL_THEME_BASE.'_personalize_panel',array(
		'title'			=> AIHL_THEME_NAME,
		'description'	=> '<strong>Imposta i settaggi per il tema '.AIHL_THEME_NAME.'</strong>',
		'priority' 		=> 30,
	));
});
?>
<?php }}});?>
