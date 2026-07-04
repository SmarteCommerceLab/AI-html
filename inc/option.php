<?php /*
* creazione delle costanti di registrazione
* OPTION GROUP
* OPTION DEFAULT
* OPTION REGEX
*/ ?>
<?php /* CONSTANT - PREFISSO 	*/
$PRX = 'AIHL';
?>
<?php /* CONSTANT - OPTION - GROUP					*/
define('AIHL'.'_'.'OPTION', array(
	array('option_group' => AIHL_OPTION_BASE.'_'.'general'   ,'option_base' => 'general','option_text' => 'General'			),
	array('option_group' => AIHL_OPTION_BASE.'_'.'reset'	 ,'option_base' => 'reset'	,'option_text' => 'Reset'			),
));
?>
<?php /* CONSTANT - OPTION - DEFAULT				*/
define('AIHL'.'_'.'OPTION_DEFAULT', array(
	AIHL_OPTION_BASE.'_'.'general'  => array(),
	AIHL_OPTION_BASE.'_'.'reset'	=> array(),
));
?>
<?php /* CONSTANT - OPTION - SMART_SITE				*/
define('SMART_SITE_OPTION', array(
	'SMART_SITE_OPTION_THEME_BASE' => AIHL_THEME_BASE,
	'SMART_SITE_OPTION_THEME_NAME' => AIHL_THEME_NAME,
));
#var_dump(SMART_SITE_OPTION['SMART_SITE_OPTION_THEME_BASE']);
?>