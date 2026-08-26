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
	return array('consumer' => 'ai-html', 'authorization_token' => 'must-not-leak');
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

echo "AI export runtime contract OK\n";
