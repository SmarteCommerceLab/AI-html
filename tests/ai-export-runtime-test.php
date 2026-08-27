<?php
define('ABSPATH', __DIR__ . '/');
define('AIHL_VERSION', 'test');
define('AIHL_TEXT_DOMAIN', 'ai_html');
define('SBIN_VERSION', 'test');
define('SBS_VERSION', 'test');

function add_action($hook, $callback) {}
function apply_filters($hook, $value) { return $value; }
function get_bloginfo($field) {
	$values = array('name' => 'Test Site', 'language' => 'it-IT');
	return $values[$field] ?? '';
}
function home_url($path = '') { return 'https://example.test' . $path; }
function aihl_get_theme_integration_manifest() {
	return array(
		'resources' => array('menus' => array('main' => array('assigned' => true))),
		'api_key' => 'must-not-leak',
		'nested' => array('private_token' => 'must-not-leak'),
	);
}
function aihl_sbm_consumer_contract() {
	return array(
		'consumer' => 'ai-html',
		'authorization_token' => 'must-not-leak',
		'css_variables' => array('required' => array('--bs-primary', '--sbin-grid-gap')),
		'design_governance' => array(
			'options' => array('smart_bootstrap_option_design_mode' => 'governed'),
			'semantic_tokens' => array('--canvas-text' => 'var(--bs-body-color)'),
		),
	);
}
function sbs_get_widget_registry() {
	return array('canvas' => array('label' => 'AI Canvas'));
}
function aihl_code_slots_hooks() {
	return array('header_full' => 'Header completo', 'footer_full' => 'Footer completo');
}

require dirname(__DIR__) . '/inc/theme/ai-export.php';

function ai_export_assert($condition, $message) {
	if (!$condition) {
		fwrite(STDERR, $message . PHP_EOL);
		exit(1);
	}
}

$payload = aihl_ai_export_payload();
$json = json_encode($payload);

ai_export_assert('smart-ecommerce-ai-context' === $payload['format'], 'Formato export mancante.');
ai_export_assert(1 === $payload['format_version'], 'Versione schema export errata.');
ai_export_assert(true === $payload['read_only'], 'Export non dichiarato read-only.');
ai_export_assert(isset($payload['contracts']['ai_html_manifest']['resources']['menus']), 'Manifest AI-HTML assente.');
ai_export_assert(isset($payload['contracts']['sbm_consumer_contract']['consumer']), 'Contratto SBM assente.');
ai_export_assert(isset($payload['contracts']['sbs_widget_registry']['canvas']), 'Registry SBS assente.');
ai_export_assert(isset($payload['contracts']['code_slot_hooks']['header_full']), 'Hook Code Slots assenti.');
ai_export_assert(false === strpos($json, 'must-not-leak'), 'L export contiene un segreto.');
ai_export_assert(false === strpos($json, 'api_key'), 'La chiave sensibile non e stata rimossa.');
ai_export_assert(false === strpos($json, 'private_token'), 'Il token sensibile non e stato rimosso.');
ai_export_assert(isset($payload['knowledge_entry_points']['chat_classic']), 'Ingresso KB chat classica assente.');
ai_export_assert(isset($payload['knowledge_entry_points']['smart_ai_studio']), 'Ingresso KB Studio assente.');
ai_export_assert(isset($payload['knowledge_entry_points']['standalone']), 'Ingresso KB standalone assente.');
ai_export_assert(isset($payload['knowledge_entry_points']['prompt_library']), 'Ingresso KB prompt assente.');
ai_export_assert('1.6.2' === $payload['knowledge_pack']['version'], 'Versione Knowledge Pack assente.');
ai_export_assert(5 === count($payload['required_knowledge']), 'Documenti KB obbligatori incompleti.');
ai_export_assert(!empty($payload['knowledge_snapshot']), 'Snapshot KB incorporato assente.');
ai_export_assert('governed' === $payload['contracts']['sbm_authoring_contract']['global_mode'], 'Modalita SBM authoring assente.');
ai_export_assert(isset($payload['contracts']['sbm_authoring_contract']['semantic_tokens']['--canvas-text']), 'Token semantici SBM assenti.');
ai_export_assert(in_array('--sbin-grid-gap', $payload['contracts']['sbm_authoring_contract']['required_tokens'], true), 'Token richiesti SBM assenti.');
ai_export_assert(13 === count($payload['prompt_templates']), 'Catalogo prompt incompleto.');
ai_export_assert('start_session' === $payload['prompt_templates'][0]['id'], 'Primo prompt di contesto assente.');
ai_export_assert(false !== strpos($payload['prompt_templates'][0]['prompt'], 'non generare codice'), 'Il primo prompt non separa comprensione ed esecuzione.');
ai_export_assert(false !== strpos($payload['prompt_templates'][2]['prompt'], 'header_full'), 'Prompt header non dichiara lo slot atteso.');
foreach ($payload['prompt_templates'] as $template) {
	ai_export_assert(false !== strpos($template['prompt'], 'required_knowledge'), 'Un prompt non richiede la consultazione KB: ' . $template['id']);
	if ('start_session' !== $template['id']) {
		ai_export_assert(false !== strpos($template['prompt'], 'contracts.sbm_consumer_contract.design_governance'), 'Un prompt operativo non richiede il contratto SBM: ' . $template['id']);
	}
}

echo "AI export runtime contract OK\n";
