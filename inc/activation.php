<?php /*
* ver:2024-0429-1407
*/ ?>
<?php if(!defined('WPINC'))die;?>
<?php /* CONSTANT - PREFISSO 	*/
$PRX            = 'AIHL';
$OPTION         = $PRX.'_OPTION';
?>
<?php
/* Register - Class */
class aihl_register_class {
	/*	*/
	public static function register(){
		foreach (AIHL_OPTION as $option_group) {$option = get_option($option_group['option_group']);
			if(false == $option or empty($option) or !isset($option) or ($option) == null){$option_default = AIHL_OPTION_DEFAULT;
				add_option(
					$option_group['option_group'], 
					apply_filters($option_group['option_group'],
					$option_default[$option_group['option_group']]),
					'',
					false
				);
			}
		}
	}
	
	/*	*/
	public static function unistall(){
		// -- Option List Delete
		foreach(AIHL_OPTION as $option_group){delete_option($option_group['option_group']);}
		// -- meta options
		#$_meta_options = array();
		// -- remove meta options
		#global $wpdb;foreach($_meta_options as $meta_key){$wpdb->delete($wpdb->prefix.'postmeta',array('meta_key' => $meta_key));}
	}
	
	/*	*/
	public static function unireset(){
		// -- Option List Delete
		if(self::check_true(AIHL_OPTION_BASE.'_reset_active')){
			foreach(AIHL_OPTION as $option_group){
				if(self::check_true(AIHL_OPTION_BASE.'_reset_'.$option_group['option_base'])){delete_option($option_group['option_group']);}
			}
			// --
			if(self::check_true(AIHL_OPTION_BASE.'_reset_plugin'	)){
				foreach(AIHL_OPTION as $option_group) {delete_option($option_group['option_group']);}
			}
			// -- Option Reset - delete
			foreach(AIHL_OPTION as $option_group) {
				if(isset($option_group['option_reset']) and !empty($option_group['option_reset']) and $option_group['option_reset'] == true){
					delete_option($option_group['option_group']);
				}
			}
			// --
			delete_option(AIHL_OPTION_BASE.'_reset');
			delete_option(SMART_SITE_OPTION);
		}
		// -- Meta Option
		#$_meta_options = array();
		// -- remove meta options
		#global $wpdb;foreach($_meta_options as $meta_key){$wpdb->delete($wpdb->prefix.'postmeta',array('meta_key' => $meta_key));}
	self::register();}
	
	/*	*/
	public static function check($field){
		foreach (AIHL_OPTION as $option_group) {$option = get_option($option_group['option_group']);
			if(isset($option) and !empty($option) and $option !== null){
				if(isset($option[$field]) and !empty($option[$field]) and $option[$field] !== null and $option[$field] !== ''){
					return $option;
				}
			}		
		}
	}
	
	/*	*/
	public static function check_true($field){
		foreach (AIHL_OPTION as $option_group) {$option = get_option($option_group['option_group']);
			if(isset($option) and !empty($option) and $option !== null){
				if(isset($option[$field]) and !empty($option[$field]) and $option[$field] !== null and $option[$field] !== ''){
					if($option[$field] == true){return $option;}
				}
			}		
		}
	}
	
	/*	*/
	public static function get_text($field){
		foreach (AIHL_OPTION as $option_group) {$option = get_option($option_group['option_group']);
			if(isset($option) and !empty($option) and $option !== null){
				if(isset($option[$field]) and !empty($option[$field]) and $option[$field] !== null and $option[$field] !== ''){
					return $option[$field];
				}
			}
		}
	return "";}
	
	/*	*/
	public static function section_add($option_group,$option_section,$option_text){
		add_settings_section($option_group.'_section_'.$option_section,$option_text,$option_group.'_section_'.$option_section.'_template',$option_group);
	}
	
	/* */
	public static function field_add($option_group,$option_section,$option_field,$option_text){
		add_settings_field($option_group.$option_field,$option_text,$option_group.'_'.$option_field.'_template',$option_group,$option_group.'_section_'.$option_section);
	}
	
	/* reset - page - procedure */
	public static function reset_section_add($option_group,$option_section,$option_text){
		add_settings_section(
			$option_group.'_section_'.$option_section,
			$option_text,
			function(){$html = '';
				$html.= '<span class="description">Imposta la richiesta di reset</span>';
				$html.= '<hr>';
			echo $html;},
			$option_group
		);
	}
	
	/*	*/ 
	public static function reset_field_plugin_add($option_group,$option_section,$option_field,$option_text){
		add_settings_field(
			$option_group.$option_field,
			$option_text,
			function(){$html = '';
				$option_group 	= AIHL_OPTION_BASE.'_'.'reset';
				$option_item	= $option_group.'_'.'plugin';
				$option 		= self::check_true($option_item);
				// --
				$html.= '';
				$html.='<input type="checkbox" id="'.$option_item.'" class="mizer admin-checkbox" name="'.$option_group.'['.$option_item.']" value="1" '.checked(1,isset($option[$option_item])?$option[$option_item]:0,false).'/>';
				$html.='<label for="'.$option_item.'" class="mizer admin-checkbox-switch"></label>';
				$html.='<span class="description">&nbsp;Attiva il reset di tutte le impostazioni</span></br>';
				// --
				$html.='</br>';
				$html.='<span class="description">';
				$html.='Attiva il reset del Plug-in senza tener conto delle preferenze per le schede selezionate</br>';
				$html.='';
				$html.='</span>';
				$html.='<hr>';
			echo $html;},
			$option_group,$option_group.'_section_'.$option_section
		);
	}
	
	/*	*/
	public static function reset_field_active_add($option_group,$option_section,$option_field,$option_text){
		add_settings_field(
			$option_group.$option_field,
			$option_text,
			function(){$html = '';
				$option_group 	= AIHL_OPTION_BASE.'_'.'reset';
				$option_item	= $option_group.'_'.'active';
				$option 		= self::check_true($option_item);
				// --
				$html.='';
				$html.='<input type="checkbox" id="'.$option_item.'" class="mizer admin-checkbox" name="'.$option_group.'['.$option_item.']" value="1" '.checked(1,isset($option[$option_item])?$option[$option_item]:0,false).'/>';
				$html.='<label for="'.$option_item.'" class="mizer admin-checkbox-switch"></label>';
				$html.='<span class="description">&nbsp;Attiva il reset</span></br>';
				// --
				$html.='</br>';
				$html.='<span class="description">';
				$html.='Attiva il reset delle schede selezionate</br>';
				$html.='';
				$html.='</span>';
				$html.='<hr>';
			echo $html;},
			$option_group,$option_group.'_section_'.$option_section
		);
	}
	
	/*	*/
	public static function reset_section_custom_add($option_group,$option_section,$option_text){
		add_settings_section(
			$option_group.'_section_'.$option_section,
			$option_text,
			function(){$html = '';
				$html.= '<span class="description">Imposta le schede da resettare</span>';
				$html.= '<hr>';
			echo $html;},
			$option_group
		);
	}
	
	/*	*/
	public static function reset_feild_custom_add($option_group,$option_section,$option_field,$option_text){
		add_settings_field(
			$option_group.$option_field,
			$option_text,
			function($args){$html = '';
				$option_group 	= AIHL_OPTION_BASE.'_'.'reset';
				$option_item	= $option_group.'_'.$args['option_field'];
				$option 		= self::check_true($option_item);
				// --
				$html.= '';
				$html.='<input type="checkbox" id="'.$option_item.'" class="mizer admin-checkbox" name="'.$option_group.'['.$option_item.']" value="1" '.checked(1,isset($option[$option_item])?$option[$option_item]:0,false).'/>';
				$html.='<label for="'.$option_item.'" class="mizer admin-checkbox-switch"></label>';
				$html.='<span class="description">&nbsp;Richiedi Reset della scheda</span></br>';
				// --
				$html.='</br>';
				$html.='<span class="description">';
				$html.='Permette di chiedere il reset della scheda</br>';
				$html.='</span>';
				$html.='<hr>';
			echo $html;},
			$option_group,$option_group.'_section_'.$option_section,
			$args = array('option_field'=>$option_field)
		);
	}
}
?>
<?php
/* Register Hook - Activation */
add_action('after_setup_theme',function(){aihl_register_class::register();});
/* Register Hook - Unistall */
#register_uninstall_hook(SEXM_BASENAME,'smart_sem_unistall');function smart_sem_unistall(){smart_sem_register_class::unistall();}
/* Register Hook - Activation */
#register_activation_hook(SEXM_BASENAME,function(){smart_sem_register_class::register();});
/* Register Hook - Reset */
if((is_admin() or is_customize_preview()) and aihl_register_class::check_true(AIHL_OPTION_BASE.'_reset_active')){aihl_register_class::unireset();}
?>