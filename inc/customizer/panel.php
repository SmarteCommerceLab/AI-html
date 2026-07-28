<?php
/**
 * AI-HTML Customizer hierarchy.
 */
if (!defined('ABSPATH')) {
	exit;
}

function aihl_register_customizer_panels($wp_customize): void {
	if (!class_exists('WP_Customize_Panel')) {
		return;
	}

	if (!class_exists('AIHL_Customize_Nested_Panel')) {
		class AIHL_Customize_Nested_Panel extends WP_Customize_Panel {
			public $panel = '';
			public $type = 'aihl_nested_panel';

			public function json() {
				$data = parent::json();
				$data['panel'] = $this->panel;
				return $data;
			}
		}
	}

	$wp_customize->register_panel_type('AIHL_Customize_Nested_Panel');

	$root = AIHL_THEME_BASE . '_personalize_panel';
	$wp_customize->add_panel($root, array(
		'title'       => AIHL_THEME_NAME,
		'description' => __('Configura struttura, contenuti, identita e integrazioni del tema.', AIHL_TEXT_DOMAIN),
		'priority'    => 30,
	));

	$panels = array(
		'structure' => array(
			'title'       => __('Struttura', AIHL_TEXT_DOMAIN),
			'description' => __('Header, footer e sorgenti AI Canvas.', AIHL_TEXT_DOMAIN),
			'priority'    => 10,
		),
		'content' => array(
			'title'       => __('Contenuti', AIHL_TEXT_DOMAIN),
			'description' => __('Articoli, archivi e componenti editoriali.', AIHL_TEXT_DOMAIN),
			'priority'    => 20,
		),
		'appearance' => array(
			'title'       => __('Identita e stile', AIHL_TEXT_DOMAIN),
			'description' => __('Informazioni del sito e impostazioni visive globali.', AIHL_TEXT_DOMAIN),
			'priority'    => 30,
		),
		'integrations' => array(
			'title'       => __('Integrazioni', AIHL_TEXT_DOMAIN),
			'description' => __('Contatti, moduli e servizi collegati.', AIHL_TEXT_DOMAIN),
			'priority'    => 40,
		),
	);

	foreach ($panels as $slug => $args) {
		$args['panel'] = $root;
		$wp_customize->add_panel(new AIHL_Customize_Nested_Panel(
			$wp_customize,
			AIHL_THEME_BASE . '_' . $slug . '_panel',
			$args
		));
	}
}
add_action('customize_register', 'aihl_register_customizer_panels', 5);

function aihl_enqueue_customizer_hierarchy_assets(): void {
	wp_enqueue_script(
		'aihl-customizer-hierarchy',
		AIHL_DIR_URL . '/resource/js/customizer-hierarchy.js',
		array('customize-controls', 'jquery'),
		AIHL_VERSION,
		true
	);
	wp_enqueue_style(
		'aihl-customizer-hierarchy',
		AIHL_DIR_URL . '/resource/css/customizer-hierarchy.css',
		array(),
		AIHL_VERSION
	);
}
add_action('customize_controls_enqueue_scripts', 'aihl_enqueue_customizer_hierarchy_assets');
