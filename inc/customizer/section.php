<?php /*
* ver:2024-0429-1556
*/ ?>
<?php add_action('init', function(){if(is_customize_preview()){if(aihtml_is_plugin_active('smart-customizer-frameworks/smart-customizer-frameworks.php')){?>
<?php
if (!function_exists('aihl_sanitize_header_overlay_mode')) {
	function aihl_sanitize_header_overlay_mode($value) {
		$value = sanitize_text_field((string) $value);
		return in_array($value, array('auto', 'always', 'never'), true) ? $value : 'auto';
	}
}

if (!function_exists('aihl_sanitize_structure_render_mode')) {
	function aihl_sanitize_structure_render_mode($value) {
		$value = sanitize_key((string) $value);
		return in_array($value, array('native', 'canvas'), true) ? $value : 'native';
	}
}

if (!function_exists('aihl_sanitize_mobile_rail_position')) {
	function aihl_sanitize_mobile_rail_position($value) {
		$value = sanitize_text_field((string) $value);
		return in_array($value, array('left', 'right'), true) ? $value : 'right';
	}
}

if (!function_exists('aihl_sanitize_header_nav_layout')) {
	function aihl_sanitize_header_nav_layout($value) {
		$value = sanitize_key((string) $value);
		$allowed = array('clean', 'pills', 'underline', 'compact');
		return in_array($value, $allowed, true) ? $value : 'clean';
	}
}

if (!function_exists('aihl_sanitize_header_structure')) {
	function aihl_sanitize_header_structure($value) {
		$value = sanitize_key((string) $value);
		$allowed = array('standard', 'dualbar', 'centered', 'topbar-nav', 'mega-centered', 'sidebar', 'triple-row', 'stacked-centered');
		return in_array($value, $allowed, true) ? $value : 'standard';
	}
}

if (!function_exists('aihl_sanitize_header_nav_text_variant')) {
	function aihl_sanitize_header_nav_text_variant($value) {
		$value = sanitize_key((string) $value);
		$allowed = array('normal', 'uppercase', 'lowercase', 'italic', 'uppercase-italic', 'lowercase-italic');
		return in_array($value, $allowed, true) ? $value : 'normal';
	}
}

if (!function_exists('aihl_sanitize_header_nav_font_weight')) {
	function aihl_sanitize_header_nav_font_weight($value) {
		$value = absint($value);
		$allowed = array(300, 400, 500, 600, 700, 800);
		return in_array($value, $allowed, true) ? (string) $value : '500';
	}
}

if (!function_exists('aihl_sanitize_header_nav_letter_spacing')) {
	function aihl_sanitize_header_nav_letter_spacing($value) {
		$value = is_scalar($value) ? str_replace(',', '.', (string) $value) : '0';
		$value = is_numeric($value) ? (float) $value : 0;
		$value = max(0, min(0.2, $value));
		return (string) $value;
	}
}

if (!function_exists('aihl_sanitize_overlay_opacity')) {
	function aihl_sanitize_overlay_opacity($value) {
		$value = is_scalar($value) ? str_replace(',', '.', (string) $value) : '0.18';
		$value = is_numeric($value) ? (float) $value : 0.18;
		$value = max(0, min(1, $value));
		return (string) $value;
	}
}

if (!function_exists('aihl_sanitize_overlay_blur')) {
	function aihl_sanitize_overlay_blur($value) {
		$value = absint($value);
		if ($value > 24) {
			$value = 24;
		}
		return (string) $value;
	}
}

if (!function_exists('aihl_sanitize_unit_interval')) {
	function aihl_sanitize_unit_interval($value) {
		$value = is_scalar($value) ? str_replace(',', '.', (string) $value) : '0';
		$value = is_numeric($value) ? (float) $value : 0;
		$value = max(0, min(1, $value));
		return (string) $value;
	}
}

if (!function_exists('aihl_sanitize_footer_bg_position')) {
	function aihl_sanitize_footer_bg_position($value) {
		$value = sanitize_text_field((string) $value);
		$allowed = array('center center', 'center top', 'center bottom', 'left center', 'right center');
		return in_array($value, $allowed, true) ? $value : 'center center';
	}
}

if (!function_exists('aihl_sanitize_footer_bg_size')) {
	function aihl_sanitize_footer_bg_size($value) {
		$value = sanitize_text_field((string) $value);
		$allowed = array('auto', 'cover', 'contain');
		return in_array($value, $allowed, true) ? $value : 'contain';
	}
}

if (!function_exists('aihl_sanitize_footer_bg_repeat')) {
	function aihl_sanitize_footer_bg_repeat($value) {
		$value = sanitize_text_field((string) $value);
		$allowed = array('no-repeat', 'repeat', 'repeat-x', 'repeat-y');
		return in_array($value, $allowed, true) ? $value : 'no-repeat';
	}
}

if (!function_exists('aihl_sanitize_footer_overlay_tone')) {
	function aihl_sanitize_footer_overlay_tone($value) {
		$value = sanitize_key((string) $value);
		$allowed = array('body', 'primary', 'dark', 'light');
		return in_array($value, $allowed, true) ? $value : 'body';
	}
}

if (!function_exists('aihl_sanitize_footer_variant')) {
	function aihl_sanitize_footer_variant($value) {
		$value = sanitize_key((string) $value);
		$allowed = array('enterprise', 'futuristic', 'corporate', 'compact', 'mega-footer', 'minimal', 'cta-footer');
		return in_array($value, $allowed, true) ? $value : 'enterprise';
	}
}

if (!function_exists('aihl_assign_setting_sanitizer')) {
	function aihl_assign_setting_sanitizer($wp_customize, $setting_id, $callback) {
		$setting = $wp_customize->get_setting($setting_id);
		if ($setting && is_callable($callback)) {
			$setting->sanitize_callback = $callback;
		}
	}
}

if (!function_exists('aihl_sanitize_maps_embed')) {
	function aihl_sanitize_maps_embed($value) {
		$allowed = wp_kses_allowed_html('post');
		$allowed['iframe'] = array(
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'style'           => true,
			'allow'           => true,
			'loading'         => true,
			'referrerpolicy'  => true,
			'allowfullscreen' => true,
			'aria-hidden'     => true,
			'tabindex'        => true,
			'title'           => true,
		);
		return wp_kses((string) $value, $allowed);
	}
}

/* checkbox */
#ai_html_toggle_item_add($wp_customize,'option','setting','section','label','description','default','selector');
function ai_html_toggle_item_add($wp_customize,$option_id,$setting_id,$setting_section,$setting_label,$setting_desc = '',$setting_default = '', $setting_selector = '') 	{
	smart_customizer_toggle_add::add($wp_customize,array(
		'id' 			=> $option_id.'['.$setting_id.']',
		'section' 		=> $setting_section,
		'label' 		=> esc_html__($setting_label,AIHL_TEXT_DOMAIN),
		'description' 	=> esc_html__($setting_desc,AIHL_TEXT_DOMAIN),
		'default' 		=> $setting_default,
		'selector' 		=> $setting_selector,
	));
	smart_customizer_divider_add::render($wp_customize,$setting_id.'_separetor',$setting_section);
}
/* textbox */
#ai_html_textbox_item_add($wp_customize,'option','setting','section','label','description','selector');
function ai_html_textbox_item_add($wp_customize,$option_id,$setting_id,$setting_section,$setting_label,$setting_desc = '',$setting_default = '', $setting_selector = '') 	{
	$wp_customize->add_setting($option_id.'['.$setting_id.']',array(
		'type' 				=> 'option',
		'autoload' 			=> false,
		'capability' 		=> 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'default' 			=> $setting_default,
	));
	$wp_customize->add_control($option_id.'['.$setting_id.']',array(
		'type' 				=> 'text',
		'section' 			=> $setting_section,
		'settings'   		=> $option_id.'['.$setting_id.']',
		'label' 			=> esc_html__($setting_label,AIHL_TEXT_DOMAIN),
		'description' 		=> esc_html__($setting_desc,AIHL_TEXT_DOMAIN),
	));
	$wp_customize->selective_refresh->add_partial($setting_id.'_parzial',array(
		'selector' 				=> $setting_selector,
		'container_inclusive' 	=> false,
		'fallback_refresh' 		=> false
	));
	smart_customizer_divider_add::render($wp_customize,$setting_id.'_separetor',$setting_section);
}

if (!function_exists('aihl_addon_resource_control')) {
	function aihl_addon_resource_control($wp_customize, $setting_id, $section, $label, $post_type) {
		$option_id = AIHL_OPTION_BASE . '_general[' . $setting_id . ']';
		$choices = array('0' => __('Non configurato', AIHL_TEXT_DOMAIN));
		if (post_type_exists($post_type)) {
			foreach (get_posts(array(
				'post_type' => $post_type,
				'post_status' => array('publish', 'draft'),
				'posts_per_page' => 100,
				'orderby' => 'title',
				'order' => 'ASC',
			)) as $resource) {
				$choices[(string) $resource->ID] = sprintf('%s (#%d)', get_the_title($resource), $resource->ID);
			}
		}

		$wp_customize->add_setting($option_id, array(
			'type' => 'option',
			'autoload' => false,
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'absint',
			'default' => 0,
		));
		$wp_customize->add_control($option_id, array(
			'type' => count($choices) > 1 ? 'select' : 'number',
			'section' => $section,
			'settings' => $option_id,
			'label' => $label,
			'description' => count($choices) > 1
				? __('Seleziona una risorsa pubblicata dal relativo add-on.', AIHL_TEXT_DOMAIN)
				: __('Nessuna risorsa rilevata. Inserisci un ID numerico oppure attiva e configura l’add-on.', AIHL_TEXT_DOMAIN),
			'choices' => $choices,
			'input_attrs' => array('min' => 0, 'step' => 1),
		));
		smart_customizer_divider_add::render($wp_customize, $setting_id . '_separator', $section);
	}
}
/* reapeter */
#ai_html_reapeter_item_add($wp_customize,'option','setting','section','label','description');
function ai_html_reapeter_item_add($wp_customize,$option_id,$setting_id,$section_id,$setting_label,$setting_desc = '',$setting_default = '', $setting_selector = '') 	{
	$args = array(
		'id' 			=> $option_id.'['.$setting_id.']',
		'section' 		=> $section_id,
		'label' 		=> esc_html__($setting_label,AIHL_TEXT_DOMAIN),
		'description' 	=> esc_html__($setting_desc,AIHL_TEXT_DOMAIN),
		'default' 		=> $setting_default,
		'selector' 		=> $setting_selector,
	);
	if(isset($setting_noresposive) and !empty($setting_noresposive)){$args = array_merge($wp_data,array('no_responsive'=> $setting_noresposive));}
	smart_customizer_script_add::add	($wp_customize,$args);
	smart_customizer_divider_add::render($wp_customize,$setting_id.'_separetor',$section_id);
}
?>
<?php
// -- Section
add_action( 'customize_register',function($wp_customize) {
	// -------------------------------------------------------------------------------------------------------------/ Sito
	$wp_customize->add_section(AIHL_THEME_BASE.'_'.'sito'.'_section' , array(
		'title'     	=> 'Sito',
		'panel'			=> AIHL_THEME_BASE.'_personalize_panel',
		'priority'   	=> 30,
	));
	// -------------------------------------------------------------------------------------------------------------/ Articoli
	$wp_customize->add_section(AIHL_THEME_BASE.'_'.'article'.'_section' , array(
		'title'     	=> 'Articoli',
		'panel'			=> AIHL_THEME_BASE.'_personalize_panel',
		'priority'   	=> 30,
	));
	// -------------------------------------------------------------------------------------------------------------/ Contatti
	$wp_customize->add_section(AIHL_THEME_BASE.'_'.'contatti'.'_section' , array(
		'title'      	=> 'Contatti',
		'panel'			=> AIHL_THEME_BASE.'_personalize_panel',
		'priority'   	=> 30,
	));
	// -------------------------------------------------------------------------------------------------------------/ Contact Form
	$wp_customize->add_section(AIHL_THEME_BASE.'_'.'contactform'.'_section' , array(
		'title'      	=> 'Contanct Form',
		'panel'			=> AIHL_THEME_BASE.'_personalize_panel',
		'priority'   	=> 30,
	));
	// -------------------------------------------------------------------------------------------------------------/ Mailchip
	$wp_customize->add_section(AIHL_THEME_BASE.'_'.'mailchip'.'_section' , array(
		'title'      	=> 'Mailchimp',
		'panel'			=> AIHL_THEME_BASE.'_personalize_panel',
		'priority'   	=> 30,
	));
	// -------------------------------------------------------------------------------------------------------------/ Header
	$wp_customize->add_section(AIHL_THEME_BASE.'_'.'headerux'.'_section' , array(
		'title'      	=> 'Header',
		'panel'			=> AIHL_THEME_BASE.'_personalize_panel',
		'priority'   	=> 30,
	));
	// -------------------------------------------------------------------------------------------------------------/ Footer
	$wp_customize->add_section(AIHL_THEME_BASE.'_'.'footerux'.'_section' , array(
		'title'      	=> 'Footer',
		'panel'			=> AIHL_THEME_BASE.'_personalize_panel',
		'priority'   	=> 31,
	));
	// -------------------------------------------------------------------------------------------------------------/ Section -  Reset
	$wp_customize->add_section(AIHL_THEME_BASE.'_'.'reset'.'_section' , array(
		'title' 		=> 'Reset',
		'panel'			=> AIHL_THEME_BASE.'_personalize_panel',
		'priority'   	=> 30,
	));
});?>
<?php
// -- Section 	- Sito
add_action('customize_register',function($wp_customize) {
    // ----------------------------------------------------------------------/ descrizione
    $wp_customize->add_setting(AIHL_OPTION_BASE.'_'.'general[sito_descrizione]', array(
        'type' 				=> 'option',
        'autoload' 			=> false,
        'capability' 		=> 'edit_theme_options',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' 			=> '',
    ));
    $wp_customize->add_control(AIHL_OPTION_BASE.'_'.'general[sito_descrizione]', array(
        'type' 				=> 'textarea',
        'settings'   		=> AIHL_OPTION_BASE.'_'.'general[sito_descrizione]',
        'section' 			=> AIHL_THEME_BASE.'_'.'sito'.'_section',
        'label'				=> __( 'Descrizone',AIHL_TEXT_DOMAIN),
        'description' 		=> __( 'Inserisci la descrizione del sito',AIHL_TEXT_DOMAIN),
    ));
    smart_customizer_divider_add::render($wp_customize,AIHL_THEME_BASE.'_'.'sito'.'_'.'sito_descrizione'.'_separetor',AIHL_THEME_BASE.'_'.'sito'.'_section');
});?>
<?php
// -- Section 	- Articoli
add_action('customize_register',function($wp_customize) {
	// ----------------------------------------------------------------------/ Image Size Control
	ai_html_toggle_item_add($wp_customize,AIHL_OPTION_BASE.'_'.'general','article_image_size_control'  ,AIHL_TEXT_DOMAIN.'_'.'article_section','Image size Control','Controllo dimensioni minime Immagine',false);
	// ----------------------------------------------------------------------/ Next-Previous Post
	ai_html_toggle_item_add($wp_customize,AIHL_OPTION_BASE.'_'.'general','article_next_prev'           ,AIHL_TEXT_DOMAIN.'_'.'article_section','Next/Prev Article','Attiva i Post Next e Previous',false,'next_prev_post');
	// ----------------------------------------------------------------------/ Related - Active
	ai_html_toggle_item_add($wp_customize,AIHL_OPTION_BASE.'_'.'general','article_related'             ,AIHL_TEXT_DOMAIN.'_'.'article_section','Articoli correlati','Mostra articoli relativi al contenuto',false,'related');
	// ----------------------------------------------------------------------/ Related Link - Active
	ai_html_toggle_item_add($wp_customize,AIHL_OPTION_BASE.'_'.'general','article_related_link'        ,AIHL_TEXT_DOMAIN.'_'.'article_section','Link correlati','Mostra Link relativi al contenuto',false,'related_link');
	// ----------------------------------------------------------------------/ content-size
	ai_html_textbox_item_add($wp_customize,AIHL_OPTION_BASE.'_'.'general','article_content_size'       ,AIHL_TEXT_DOMAIN.'_'.'article_section','Width(px)','Imposta la larghezza del sito','1280');

	$base_art = AIHL_OPTION_BASE.'_'.'general';
	$section_art = AIHL_TEXT_DOMAIN.'_'.'article_section';

	$wp_customize->add_setting($base_art.'[blog_layout]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_key',
		'default'           => 'grid',
	));
	$wp_customize->add_control($base_art.'[blog_layout]', array(
		'type'        => 'select',
		'section'     => $section_art,
		'settings'    => $base_art.'[blog_layout]',
		'label'       => __('Layout Blog Index', AIHL_TEXT_DOMAIN),
		'description' => __('Layout per la pagina blog (home.php).', AIHL_TEXT_DOMAIN),
		'choices'     => array(
			'grid'     => __('Griglia (card verticali)', AIHL_TEXT_DOMAIN),
			'list'     => __('Lista (titolo + excerpt)', AIHL_TEXT_DOMAIN),
			'magazine' => __('Magazine (hero + griglia)', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'blog_layout_separator',$section_art);

	ai_html_toggle_item_add($wp_customize,$base_art,'blog_sidebar',$section_art,'Blog Sidebar','Mostra sidebar nel blog e negli archivi',false);

	smart_customizer_divider_add::render($wp_customize,'author_box_separator',$section_art);

	$wp_customize->add_setting($base_art.'[article_author_box_style]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_key',
		'default'           => 'card',
	));
	$wp_customize->add_control($base_art.'[article_author_box_style]', array(
		'type'        => 'select',
		'section'     => $section_art,
		'settings'    => $base_art.'[article_author_box_style]',
		'label'       => __('Author Box Style', AIHL_TEXT_DOMAIN),
		'description' => __('Stile del box autore nella pagina articolo.', AIHL_TEXT_DOMAIN),
		'choices'     => array(
			'simple'  => __('Simple (solo dati essenziali)', AIHL_TEXT_DOMAIN),
			'card'    => __('Card (avatar + bio + stats)', AIHL_TEXT_DOMAIN),
			'compact' => __('Compact (avatar inline + nome)', AIHL_TEXT_DOMAIN),
			'banner'  => __('Banner (full-width centrato)', AIHL_TEXT_DOMAIN),
			'editorial' => __('Editorial (firma e biografia)', AIHL_TEXT_DOMAIN),
			'enterprise' => __('Enterprise (profilo professionale)', AIHL_TEXT_DOMAIN),
			'impact' => __('Impact (contrasto e statistiche)', AIHL_TEXT_DOMAIN),
			'signature' => __('Signature (firma minimale premium)', AIHL_TEXT_DOMAIN),
			'none'    => __('Nascosto', AIHL_TEXT_DOMAIN),
		),
	));
});?>
<?php
// -- Section 	- Contatti
add_action('customize_register',function($wp_customize) {
	// ---------------------------------------------------------------------- Indirizzo
	ai_html_textbox_item_add($wp_customize,AIHL_OPTION_BASE.'_'.'general','contatti_indirizzo'         ,AIHL_TEXT_DOMAIN.'_'.'contatti'.'_section','Indirizzo'  ,'Inserisci indirizzo');    
	// ---------------------------------------------------------------------- Telefono
	ai_html_textbox_item_add($wp_customize,AIHL_OPTION_BASE.'_'.'general','contatti_telefono'          ,AIHL_TEXT_DOMAIN.'_'.'contatti'.'_section','Telefono'  ,'Inserisci il contatto telefono generale');
	// ---------------------------------------------------------------------- email
	ai_html_textbox_item_add($wp_customize,AIHL_OPTION_BASE.'_'.'general','contatti_email'             ,AIHL_TEXT_DOMAIN.'_'.'contatti'.'_section','e-Mail'    ,'Iserisci e-mail generale');
    // ----------------------------------------------------------------------/ Google Maps
    $wp_customize->add_setting(AIHL_OPTION_BASE.'_'.'general[contatti_maps]', array(
        'type' 				=> 'option',
        'autoload' 			=> false,
        'capability' 		=> 'edit_theme_options',
        'sanitize_callback' => 'aihl_sanitize_maps_embed',
        'default' 			=> '',
    ));
    $wp_customize->add_control(AIHL_OPTION_BASE.'_'.'general[contatti_maps]', array(
        'type' 				=> 'textarea',
        'settings'   		=> AIHL_OPTION_BASE.'_'.'general[contatti_maps]',
        'section' 			=> AIHL_THEME_BASE.'_'.'contatti'.'_section',
        'label'				=> __( 'Google Maps',AIHL_TEXT_DOMAIN),
        'description' 		=> __( 'Inserisci il codice iFrame di Google Maps',AIHL_TEXT_DOMAIN),
    ));
    smart_customizer_divider_add::render($wp_customize,AIHL_THEME_BASE.'_'.'contatti'.'_'.'contatti_maps'.'_separetor',AIHL_THEME_BASE.'_'.'sito'.'_section');
});?>
<?php
// -- Section 	- Contact Form
add_action('customize_register',function($wp_customize) {
	// ----------------------------------------------------------------------/ Image Size Control
	aihl_addon_resource_control($wp_customize, 'contactform_contacts', AIHL_TEXT_DOMAIN.'_'.'contactform'.'_section', 'Contact Form 7', 'wpcf7_contact_form');
});?>
<?php
// -- Section 	- Mailchip
add_action('customize_register',function($wp_customize) {
	// ----------------------------------------------------------------------/ Image Size Control
	aihl_addon_resource_control($wp_customize, 'mailchip_footer', AIHL_TEXT_DOMAIN.'_'.'mailchip'.'_section', 'Mailchimp', 'mc4wp-form');
});?>
<?php
// -- Section 	- Header UX
add_action('customize_register',function($wp_customize) {
	$section = AIHL_THEME_BASE.'_'.'headerux'.'_section';
	$base = AIHL_OPTION_BASE.'_'.'general';

	$wp_customize->add_setting($base.'[header_render_mode]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'aihl_sanitize_structure_render_mode',
		'default'           => 'native',
	));
	$wp_customize->add_control($base.'[header_render_mode]', array(
		'type'        => 'select',
		'section'     => $section,
		'settings'    => $base.'[header_render_mode]',
		'label'       => __('Sorgente header', AIHL_TEXT_DOMAIN),
		'description' => __('Nativo usa le strutture del tema. AI Canvas usa lo slot attivo header_full e ricade sul nativo se lo slot non e disponibile.', AIHL_TEXT_DOMAIN),
		'choices'     => array(
			'native' => __('Header nativo AI-HTML', AIHL_TEXT_DOMAIN),
			'canvas' => __('Header AI Canvas', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'header_render_mode_separator',$section);

	$wp_customize->add_setting($base.'[header_overlay_mode]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'aihl_sanitize_header_overlay_mode',
		'default'           => 'auto',
	));
	$wp_customize->add_control($base.'[header_overlay_mode]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[header_overlay_mode]',
		'label'    => __('Topbar Overlay Mode', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'auto'   => __('Auto su Hero Fullscreen', AIHL_TEXT_DOMAIN),
			'always' => __('Sempre Overlay', AIHL_TEXT_DOMAIN),
			'never'  => __('Mai Overlay', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'header_overlay_mode_separetor',$section);

	ai_html_toggle_item_add($wp_customize,$base,'header_show_logo',$section,'Mostra Logo nella Navbar','Disattiva per nascondere il logo nella barra di navigazione principale',true);
	ai_html_toggle_item_add($wp_customize,$base,'header_show_cta',$section,'Mostra CTA nella Navbar','Disattiva per nascondere il bottone CTA',true);
	ai_html_toggle_item_add($wp_customize,$base,'header_show_login',$section,'Mostra Login','Disattiva per nascondere il bottone Login',true);

	foreach (array(
		'site_logo_url' => array('Logo principale', 'Logo default del sito. Ha precedenza sul logo SBS e sul logo WordPress.'),
		'site_logo_transparent_url' => array('Logo overlay (trasparente)', 'Variante per header su hero/sfondo scuro. Se vuota usa il logo principale.'),
		'site_logo_light_url' => array('Logo chiaro', 'Variante per fondi scuri (footer, dark mode). Se vuota usa il logo principale.'),
	) as $logo_key => $logo_control) {
		$wp_customize->add_setting($base.'['.$logo_key.']', array(
			'type'              => 'option',
			'autoload'          => false,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'esc_url_raw',
			'default'           => '',
		));
		$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, $base.'_'.$logo_key, array(
			'section'     => $section,
			'settings'    => $base.'['.$logo_key.']',
			'label'       => __($logo_control[0], AIHL_TEXT_DOMAIN),
			'description' => __($logo_control[1], AIHL_TEXT_DOMAIN),
		)));
	}

	$wp_customize->add_setting($base.'[header_structure]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'aihl_sanitize_header_structure',
		'default'           => 'standard',
	));
	$wp_customize->add_control($base.'[header_structure]', array(
		'type'        => 'select',
		'section'     => $section,
		'settings'    => $base.'[header_structure]',
		'label'       => __('Header Structure', AIHL_TEXT_DOMAIN),
		'description' => __('Scegli la struttura generale del menu/header.', AIHL_TEXT_DOMAIN),
		'choices'     => array(
			'standard'     => __('Standard', AIHL_TEXT_DOMAIN),
			'dualbar'      => __('Dual Bar Overlay', AIHL_TEXT_DOMAIN),
			'centered'     => __('Logo centrato', AIHL_TEXT_DOMAIN),
			'topbar-nav'   => __('Topbar + Navbar', AIHL_TEXT_DOMAIN),
			'mega-centered'    => __('Mega Centered (doppio menu)', AIHL_TEXT_DOMAIN),
			'sidebar'          => __('Sidebar verticale', AIHL_TEXT_DOMAIN),
			'triple-row'       => __('Triple Row (utility + brand + nav)', AIHL_TEXT_DOMAIN),
			'stacked-centered' => __('Stacked Centered (logo grande + nav sotto)', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'header_structure_separator',$section);

	$wp_customize->add_setting($base.'[header_nav_layout]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'aihl_sanitize_header_nav_layout',
		'default'           => 'clean',
	));
	$wp_customize->add_control($base.'[header_nav_layout]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[header_nav_layout]',
		'label'    => __('Header Menu Layout', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'clean'     => __('Clean', AIHL_TEXT_DOMAIN),
			'pills'     => __('Pills', AIHL_TEXT_DOMAIN),
			'underline' => __('Underline', AIHL_TEXT_DOMAIN),
			'compact'   => __('Compact', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'header_nav_layout_separator',$section);

	$wp_customize->add_setting($base.'[header_nav_text_variant]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'aihl_sanitize_header_nav_text_variant',
		'default'           => 'normal',
	));
	$wp_customize->add_control($base.'[header_nav_text_variant]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[header_nav_text_variant]',
		'label'    => __('Header Menu Text Variant', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'normal'           => __('Normale', AIHL_TEXT_DOMAIN),
			'uppercase'        => __('Stampatello maiuscolo', AIHL_TEXT_DOMAIN),
			'lowercase'        => __('Minuscolo', AIHL_TEXT_DOMAIN),
			'italic'           => __('Corsivo', AIHL_TEXT_DOMAIN),
			'uppercase-italic' => __('Maiuscolo corsivo', AIHL_TEXT_DOMAIN),
			'lowercase-italic' => __('Minuscolo corsivo', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'header_nav_text_variant_separator',$section);

	$wp_customize->add_setting($base.'[header_nav_font_weight]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'aihl_sanitize_header_nav_font_weight',
		'default'           => '500',
	));
	$wp_customize->add_control($base.'[header_nav_font_weight]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[header_nav_font_weight]',
		'label'    => __('Header Menu Font Weight', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'300' => '300',
			'400' => '400',
			'500' => '500',
			'600' => '600',
			'700' => '700',
			'800' => '800',
		),
	));

	$wp_customize->add_setting($base.'[header_nav_letter_spacing]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'aihl_sanitize_header_nav_letter_spacing',
		'default'           => '0',
	));
	$wp_customize->add_control($base.'[header_nav_letter_spacing]', array(
		'type'        => 'number',
		'section'     => $section,
		'settings'    => $base.'[header_nav_letter_spacing]',
		'label'       => __('Header Menu Letter Spacing (em)', AIHL_TEXT_DOMAIN),
		'description' => __('Range: 0 - 0.2', AIHL_TEXT_DOMAIN),
		'input_attrs' => array(
			'min'  => '0',
			'max'  => '0.2',
			'step' => '0.01',
		),
	));

	$wp_customize->add_setting($base.'[header_cta_label]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => 'Consulenza gratuita',
	));
	$wp_customize->add_control($base.'[header_cta_label]', array(
		'type'     => 'text',
		'section'  => $section,
		'settings' => $base.'[header_cta_label]',
		'label'    => __('Header CTA Label', AIHL_TEXT_DOMAIN),
	));

	$wp_customize->add_setting($base.'[header_cta_url]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'esc_url_raw',
		'default'           => '#',
	));
	$wp_customize->add_control($base.'[header_cta_url]', array(
		'type'     => 'url',
		'section'  => $section,
		'settings' => $base.'[header_cta_url]',
		'label'    => __('Header CTA URL', AIHL_TEXT_DOMAIN),
	));

	$wp_customize->add_setting($base.'[header_login_label]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => 'Login',
	));
	$wp_customize->add_control($base.'[header_login_label]', array(
		'type'     => 'text',
		'section'  => $section,
		'settings' => $base.'[header_login_label]',
		'label'    => __('Header Login Label', AIHL_TEXT_DOMAIN),
	));

	$wp_customize->add_setting($base.'[header_login_url]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'esc_url_raw',
		'default'           => '#',
	));
	$wp_customize->add_control($base.'[header_login_url]', array(
		'type'     => 'url',
		'section'  => $section,
		'settings' => $base.'[header_login_url]',
		'label'    => __('Header Login URL', AIHL_TEXT_DOMAIN),
	));

	ai_html_textbox_item_add($wp_customize,$base,'header_overlay_opacity',$section,'Overlay Opacity (0-1)','Default 0.18','0.18');
	ai_html_textbox_item_add($wp_customize,$base,'header_overlay_blur',$section,'Overlay Blur px','Default 8','8');
	aihl_assign_setting_sanitizer($wp_customize, $base.'[header_overlay_opacity]', 'aihl_sanitize_overlay_opacity');
	aihl_assign_setting_sanitizer($wp_customize, $base.'[header_overlay_blur]', 'aihl_sanitize_overlay_blur');

	ai_html_toggle_item_add($wp_customize,$base,'menu_dropdown_indicator',$section,'Dropdown Indicator','Mostra icona chevron sulle voci con sottomenu',true);
	smart_customizer_divider_add::render($wp_customize,'menu_dropdown_indicator_separator',$section);

	ai_html_toggle_item_add($wp_customize,$base,'mobile_rail_enable',$section,'Mobile Rail','Mostra barra rapida laterale su mobile',true);

	$wp_customize->add_setting($base.'[mobile_rail_position]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'aihl_sanitize_mobile_rail_position',
		'default'           => 'right',
	));
	$wp_customize->add_control($base.'[mobile_rail_position]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[mobile_rail_position]',
		'label'    => __('Mobile Rail Position', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'right' => __('Destra', AIHL_TEXT_DOMAIN),
			'left'  => __('Sinistra', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'mobile_rail_position_separetor',$section);

	ai_html_toggle_item_add($wp_customize,$base,'mobile_rail_autohide',$section,'Mobile Rail Autohide','Nasconde la rail quando non c è scroll',false);

	$wp_customize->add_setting($base.'[header_search_style]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_key',
		'default'           => 'icon-dropdown',
	));
	$wp_customize->add_control($base.'[header_search_style]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[header_search_style]',
		'label'    => __('Search Desktop', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'none'            => __('Disabilitato', AIHL_TEXT_DOMAIN),
			'icon-dropdown'   => __('Icona + Dropdown', AIHL_TEXT_DOMAIN),
			'icon-fullscreen' => __('Icona + Fullscreen', AIHL_TEXT_DOMAIN),
			'inline'          => __('Campo inline', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'header_search_style_separator',$section);

	$wp_customize->add_setting($base.'[header_topbar_scroll_behavior]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_key',
		'default'           => 'scroll-away',
	));
	$wp_customize->add_control($base.'[header_topbar_scroll_behavior]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[header_topbar_scroll_behavior]',
		'label'    => __('Topbar Scroll Behavior', AIHL_TEXT_DOMAIN),
		'description' => __('Per strutture Topbar+Navbar e Dual Bar.', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'scroll-away' => __('Nasconde allo scroll', AIHL_TEXT_DOMAIN),
			'sticky'      => __('Sempre visibile', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'header_topbar_scroll_separator',$section);

	$wp_customize->add_setting($base.'[header_sticky_style]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_key',
		'default'           => 'solid',
	));
	$wp_customize->add_control($base.'[header_sticky_style]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[header_sticky_style]',
		'label'    => __('Sticky Style', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'solid'         => __('Solido', AIHL_TEXT_DOMAIN),
			'blur'          => __('Glassmorphism (blur)', AIHL_TEXT_DOMAIN),
			'transparent'   => __('Trasparente fino a scroll', AIHL_TEXT_DOMAIN),
			'gradient-fade' => __('Gradient fade (nero alto → trasparente)', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'header_sticky_style_separator',$section);

	$wp_customize->add_setting($base.'[mobile_nav_style]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_key',
		'default'           => 'rail',
	));
	$wp_customize->add_control($base.'[mobile_nav_style]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[mobile_nav_style]',
		'label'    => __('Mobile Navigation Style', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'rail'       => __('Floating Rail', AIHL_TEXT_DOMAIN),
			'bottom-bar' => __('Bottom Bar (stile app)', AIHL_TEXT_DOMAIN),
			'none'       => __('Nessuna', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'mobile_nav_style_separator',$section);
});?>
<?php
// -- Section  - Page Background Defaults
add_action('customize_register',function($wp_customize) {
	$section = AIHL_THEME_BASE.'_'.'pagebg'.'_section';
	$base = AIHL_OPTION_BASE.'_'.'general';

	$wp_customize->add_section($section, array(
		'title'    => __('Sfondo Pagina', AIHL_TEXT_DOMAIN),
		'panel'    => AIHL_THEME_BASE.'_panel',
		'priority' => 35,
	));

	$wp_customize->add_setting($base.'[page_bg_type]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_key',
		'default'           => 'default',
	));
	$wp_customize->add_control($base.'[page_bg_type]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[page_bg_type]',
		'label'    => __('Tipo sfondo default', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'default' => __('Nessuno (body standard)', AIHL_TEXT_DOMAIN),
			'color'   => __('Colore', AIHL_TEXT_DOMAIN),
			'image'   => __('Immagine', AIHL_TEXT_DOMAIN),
			'pattern' => __('Pattern', AIHL_TEXT_DOMAIN),
		),
	));

	ai_html_textbox_item_add($wp_customize,$base,'page_bg_color',$section,'Colore sfondo','Hex color default per pagine','');

	ai_html_textbox_item_add($wp_customize,$base,'page_bg_image',$section,'Immagine sfondo URL','URL immagine di sfondo default','');

	ai_html_textbox_item_add($wp_customize,$base,'page_bg_image_opacity',$section,'Opacità immagine (0-1)','Default 1','1');

	$wp_customize->add_setting($base.'[page_bg_pattern]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_key',
		'default'           => 'none',
	));
	$wp_customize->add_control($base.'[page_bg_pattern]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[page_bg_pattern]',
		'label'    => __('Pattern default', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'none'     => __('Nessuno', AIHL_TEXT_DOMAIN),
			'dots'     => __('Puntini (dot grid)', AIHL_TEXT_DOMAIN),
			'grid'     => __('Griglia', AIHL_TEXT_DOMAIN),
			'diagonal' => __('Linee diagonali', AIHL_TEXT_DOMAIN),
			'cross'    => __('Croce', AIHL_TEXT_DOMAIN),
		),
	));

	ai_html_textbox_item_add($wp_customize,$base,'page_bg_overlay_color',$section,'Overlay colore','Hex color overlay','');
	ai_html_textbox_item_add($wp_customize,$base,'page_bg_overlay_opacity',$section,'Overlay opacità (0-1)','Default 0.18','0.18');
});
?>
<?php
// -- Section 	- Footer UX
add_action('customize_register',function($wp_customize) {
	$section = AIHL_THEME_BASE.'_'.'footerux'.'_section';
	$base = AIHL_OPTION_BASE.'_'.'general';

	$wp_customize->add_setting($base.'[footer_render_mode]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'aihl_sanitize_structure_render_mode',
		'default'           => 'native',
	));
	$wp_customize->add_control($base.'[footer_render_mode]', array(
		'type'        => 'select',
		'section'     => $section,
		'settings'    => $base.'[footer_render_mode]',
		'label'       => __('Sorgente footer', AIHL_TEXT_DOMAIN),
		'description' => __('Nativo usa il footer del tema. AI Canvas usa lo slot attivo footer_full e ricade sul nativo se lo slot non e disponibile.', AIHL_TEXT_DOMAIN),
		'choices'     => array(
			'native' => __('Footer nativo AI-HTML', AIHL_TEXT_DOMAIN),
			'canvas' => __('Footer AI Canvas', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'footer_render_mode_separator',$section);

	$wp_customize->add_setting($base.'[footer_variant]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'aihl_sanitize_footer_variant',
		'default'           => 'enterprise',
	));
	$wp_customize->add_control($base.'[footer_variant]', array(
		'type'        => 'select',
		'section'     => $section,
		'settings'    => $base.'[footer_variant]',
		'label'       => __('Layout footer', AIHL_TEXT_DOMAIN),
		'description' => __('Scegli una variante commerciale del footer.', AIHL_TEXT_DOMAIN),
		'choices'     => array(
			'enterprise'   => __('Enterprise', AIHL_TEXT_DOMAIN),
			'futuristic'   => __('Futuristico', AIHL_TEXT_DOMAIN),
			'corporate'    => __('Corporate', AIHL_TEXT_DOMAIN),
			'compact'      => __('Compatto', AIHL_TEXT_DOMAIN),
			'mega-footer'  => __('Mega Footer (multi-colonna)', AIHL_TEXT_DOMAIN),
			'minimal'      => __('Minimal (una riga)', AIHL_TEXT_DOMAIN),
			'cta-footer'   => __('CTA + Footer', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'footer_variant_separetor',$section);

	ai_html_toggle_item_add($wp_customize,$base,'footer_background_enable',$section,'Attiva immagine footer','Mostra immagine decorativa di sfondo nel footer',true);

	$wp_customize->add_setting($base.'[footer_logo_url]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'esc_url_raw',
		'default'           => '',
	));
	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, $base.'_footer_logo_url', array(
		'section'     => $section,
		'settings'    => $base.'[footer_logo_url]',
		'label'       => __('Logo footer', AIHL_TEXT_DOMAIN),
		'description' => __('Opzionale. Se vuoto usa il logo principale.', AIHL_TEXT_DOMAIN),
	)));

	$wp_customize->add_setting($base.'[footer_background_image]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'esc_url_raw',
		'default'           => AIHL_DIR_URL . '/resource/img/footer.png',
	));
	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, $base.'[footer_background_image]', array(
		'section'     => $section,
		'settings'    => $base.'[footer_background_image]',
		'label'       => __('Immagine di sfondo locale', AIHL_TEXT_DOMAIN),
		'description' => __('Seleziona una immagine dalla libreria media.', AIHL_TEXT_DOMAIN),
	)));
	smart_customizer_divider_add::render($wp_customize,'footer_background_image_separetor',$section);

	$wp_customize->add_setting($base.'[footer_background_remote_url]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'esc_url_raw',
		'default'           => '',
	));
	$wp_customize->add_control($base.'[footer_background_remote_url]', array(
		'type'        => 'url',
		'section'     => $section,
		'settings'    => $base.'[footer_background_remote_url]',
		'label'       => __('Immagine remota URL', AIHL_TEXT_DOMAIN),
		'description' => __('Se valorizzato, questo URL ha priorita sull immagine locale.', AIHL_TEXT_DOMAIN),
	));
	smart_customizer_divider_add::render($wp_customize,'footer_background_remote_url_separetor',$section);

	ai_html_textbox_item_add($wp_customize,$base,'footer_background_opacity',$section,'Opacita immagine (0-1)','Default 0.12','0.12');
	aihl_assign_setting_sanitizer($wp_customize, $base.'[footer_background_opacity]', 'aihl_sanitize_unit_interval');

	$wp_customize->add_setting($base.'[footer_background_position]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'aihl_sanitize_footer_bg_position',
		'default'           => 'center center',
	));
	$wp_customize->add_control($base.'[footer_background_position]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[footer_background_position]',
		'label'    => __('Posizione immagine', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'center center' => __('Centro', AIHL_TEXT_DOMAIN),
			'center top'    => __('Alto centro', AIHL_TEXT_DOMAIN),
			'center bottom' => __('Basso centro', AIHL_TEXT_DOMAIN),
			'left center'   => __('Sinistra', AIHL_TEXT_DOMAIN),
			'right center'  => __('Destra', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'footer_background_position_separetor',$section);

	$wp_customize->add_setting($base.'[footer_background_size]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'aihl_sanitize_footer_bg_size',
		'default'           => 'contain',
	));
	$wp_customize->add_control($base.'[footer_background_size]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[footer_background_size]',
		'label'    => __('Dimensione immagine', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'contain' => __('Contenuta', AIHL_TEXT_DOMAIN),
			'cover'   => __('Copertura', AIHL_TEXT_DOMAIN),
			'auto'    => __('Originale', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'footer_background_size_separetor',$section);

	$wp_customize->add_setting($base.'[footer_background_repeat]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'aihl_sanitize_footer_bg_repeat',
		'default'           => 'no-repeat',
	));
	$wp_customize->add_control($base.'[footer_background_repeat]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[footer_background_repeat]',
		'label'    => __('Ripetizione immagine', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'no-repeat' => __('Nessuna', AIHL_TEXT_DOMAIN),
			'repeat'    => __('Ripeti', AIHL_TEXT_DOMAIN),
			'repeat-x'  => __('Orizzontale', AIHL_TEXT_DOMAIN),
			'repeat-y'  => __('Verticale', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'footer_background_repeat_separetor',$section);

	ai_html_textbox_item_add($wp_customize,$base,'footer_overlay_opacity',$section,'Opacita overlay (0-1)','Default 0','0');
	aihl_assign_setting_sanitizer($wp_customize, $base.'[footer_overlay_opacity]', 'aihl_sanitize_unit_interval');

	$wp_customize->add_setting($base.'[footer_overlay_tone]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'aihl_sanitize_footer_overlay_tone',
		'default'           => 'body',
	));
	$wp_customize->add_control($base.'[footer_overlay_tone]', array(
		'type'     => 'select',
		'section'  => $section,
		'settings' => $base.'[footer_overlay_tone]',
		'label'    => __('Colore overlay', AIHL_TEXT_DOMAIN),
		'choices'  => array(
			'body'    => __('Sfondo pagina', AIHL_TEXT_DOMAIN),
			'primary' => __('Primary', AIHL_TEXT_DOMAIN),
			'dark'    => __('Dark', AIHL_TEXT_DOMAIN),
			'light'   => __('Light', AIHL_TEXT_DOMAIN),
		),
	));
	smart_customizer_divider_add::render($wp_customize,'footer_overlay_tone_separetor',$section);

	$wp_customize->add_setting($base.'[footer_columns_count]', array(
		'type'              => 'option',
		'autoload'          => false,
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'absint',
		'default'           => '4',
	));
	$wp_customize->add_control($base.'[footer_columns_count]', array(
		'type'        => 'select',
		'section'     => $section,
		'settings'    => $base.'[footer_columns_count]',
		'label'       => __('Numero colonne (Mega Footer)', AIHL_TEXT_DOMAIN),
		'choices'     => array(
			'3' => '3',
			'4' => '4',
			'5' => '5',
		),
	));
	smart_customizer_divider_add::render($wp_customize,'footer_columns_count_separetor',$section);

	ai_html_textbox_item_add($wp_customize,$base,'footer_cta_title',$section,'CTA Titolo (CTA Footer)','Titolo grande sopra il footer','');
	ai_html_textbox_item_add($wp_customize,$base,'footer_cta_subtitle',$section,'CTA Sottotitolo','Testo di supporto','');
	ai_html_textbox_item_add($wp_customize,$base,'footer_cta_btn_label',$section,'CTA Bottone 1 Label','','');
	ai_html_textbox_item_add($wp_customize,$base,'footer_cta_btn_url',$section,'CTA Bottone 1 URL','','#');
	ai_html_textbox_item_add($wp_customize,$base,'footer_cta_btn2_label',$section,'CTA Bottone 2 Label','','');
	ai_html_textbox_item_add($wp_customize,$base,'footer_cta_btn2_url',$section,'CTA Bottone 2 URL','','#');

});?>
<?php }}});?>
