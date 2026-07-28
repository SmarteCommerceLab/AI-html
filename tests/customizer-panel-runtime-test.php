<?php
declare(strict_types=1);

define('ABSPATH', __DIR__);
define('AIHL_THEME_BASE', 'ai_html');
define('AIHL_THEME_NAME', 'AI-HTML');
define('AIHL_TEXT_DOMAIN', 'ai_html');

function __($text, $domain = null) {
	return $text;
}

function add_action($hook, $callback, $priority = 10): void {
}

class WP_Customize_Panel {
	public $manager;
	public $id;
	public $title = '';
	public $description = '';
	public $priority = 160;

	public function __construct($manager, $id, array $args = array()) {
		$this->manager = $manager;
		$this->id = $id;
		foreach ($args as $key => $value) {
			$this->{$key} = $value;
		}
	}

	public function json() {
		return array(
			'id'    => $this->id,
			'title' => $this->title,
			'type'  => $this->type ?? 'default',
		);
	}
}

class AIHL_Customize_Manager_Stub {
	public $panel_types = array();
	public $panels = array();

	public function register_panel_type($class): void {
		$this->panel_types[] = $class;
	}

	public function add_panel($panel, array $args = array()): void {
		if (!$panel instanceof WP_Customize_Panel) {
			$panel = new WP_Customize_Panel($this, $panel, $args);
		}
		$this->panels[$panel->id] = $panel;
	}
}

function runtime_assert(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

require dirname(__DIR__) . '/inc/customizer/panel.php';

$manager = new AIHL_Customize_Manager_Stub();
aihl_register_customizer_panels($manager);

$root_id = 'ai_html_personalize_panel';
runtime_assert(count($manager->panels) === 5, 'registrati pannello radice e quattro pannelli figli');
runtime_assert(
	isset($manager->panels[$root_id]) && $manager->panels[$root_id] instanceof AIHL_Customize_Nested_Panel,
	'il pannello radice usa la classe gerarchica'
);
runtime_assert($manager->panels[$root_id]->panel === '', 'il pannello radice non ha un genitore');

foreach (array('structure', 'content', 'appearance', 'integrations') as $slug) {
	$id = 'ai_html_' . $slug . '_panel';
	runtime_assert(isset($manager->panels[$id]), "pannello {$slug} registrato");
	runtime_assert($manager->panels[$id]->panel === $root_id, "pannello {$slug} collegato alla radice");
}

echo "AI-HTML Customizer panel runtime OK\n";
