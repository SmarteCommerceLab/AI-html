<?php
declare(strict_types=1);

define('ABSPATH', __DIR__);
define('AIHL_TEXT_DOMAIN', 'ai_html');
define('AIHL_OPTION_BASE', 'ai_html');

$GLOBALS['aihl_migration_options'] = array();
$GLOBALS['aihl_migration_design_mode'] = 'governed';

function add_action($hook, $callback, $priority = 10, $accepted_args = 1): void {}
function add_filter($hook, $callback, $priority = 10, $accepted_args = 1): void {}
function __($value, $domain = null): string { return (string) $value; }
function sanitize_key($value): string {
	return preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $value)) ?? '';
}
function current_time($type): string { return '2026-07-28 12:00:00'; }
function get_option($key, $default = false) {
	return $GLOBALS['aihl_migration_options'][$key] ?? $default;
}
function update_option($key, $value, $autoload = null): bool {
	$GLOBALS['aihl_migration_options'][$key] = $value;
	return true;
}
function esc_attr($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function aihl_sbm_design_mode(): string {
	return $GLOBALS['aihl_migration_design_mode'];
}
function aihl_sbm_constrain_design_mode(string $mode): array {
	return array(
		'requested' => $mode,
		'global' => $GLOBALS['aihl_migration_design_mode'],
		'effective' => $mode,
		'allowed' => true,
	);
}

require dirname(__DIR__) . '/inc/admin/code-slots.php';

function migration_assert(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

$legacy_compliant = array(
	'id' => 'legacy-compliant',
	'hook' => 'header_full',
	'type' => 'mixed',
	'code' => '<header><smart-menu location="topic"></smart-menu></header>',
	'css' => '.site-header{color:var(--bs-body-color);padding:var(--bs-gutter-x)}',
	'js' => '',
	'context' => 'global',
	'priority' => 10,
	'active' => true,
	'label' => 'Legacy compliant',
	'version' => 4,
	'created' => '2026-01-01 00:00:00',
	'updated' => '2026-06-01 00:00:00',
);
$legacy_raw = array(
	'id' => 'legacy-raw',
	'hook' => 'footer_full',
	'type' => 'mixed',
	'code' => '<footer>Legacy footer</footer>',
	'css' => '.site-footer{color:#fff;padding:20px}',
	'js' => '',
	'context' => 'global',
	'priority' => 10,
	'active' => true,
	'label' => 'Legacy raw',
	'version' => 2,
	'created' => '2026-01-01 00:00:00',
	'updated' => '2026-06-01 00:00:00',
);
$legacy_global_css = array(
	'id' => 'legacy-global-css',
	'hook' => 'global_css',
	'type' => 'css',
	'code' => '.sx-footer{background:#111;color:#fff;padding:40px}',
	'css' => '',
	'js' => '',
	'context' => 'global',
	'priority' => 10,
	'active' => true,
	'label' => 'Legacy global CSS',
	'version' => 2,
	'created' => '2026-01-01 00:00:00',
	'updated' => '2026-06-01 00:00:00',
);
$GLOBALS['aihl_migration_options'][AIHL_CODE_SLOTS_OPTION] = array(
	'legacy-compliant' => $legacy_compliant,
	'legacy-raw' => $legacy_raw,
	'legacy-global-css' => $legacy_global_css,
);

$report = aihl_migrate_legacy_code_slot_governance();
$slots = get_option(AIHL_CODE_SLOTS_OPTION, array());

migration_assert($report['completed'], 'migrazione non completata');
migration_assert(3 === $report['migrated_count'], 'numero slot migrati errato');
migration_assert(1 === $report['deactivated_count'], 'slot non conforme non sospeso');
migration_assert('governed' === $slots['legacy-compliant']['design_mode'], 'design mode non assegnato');
migration_assert($slots['legacy-compliant']['active'], 'slot conforme disattivato');
migration_assert(!$slots['legacy-raw']['active'], 'slot raw ancora attivo');
migration_assert($slots['legacy-global-css']['active'], 'CSS globale legacy disattivato');
migration_assert($legacy_raw['code'] === $slots['legacy-raw']['code'], 'markup legacy modificato');
migration_assert($legacy_raw['css'] === $slots['legacy-raw']['css'], 'CSS legacy modificato');
migration_assert($legacy_raw['version'] === $slots['legacy-raw']['version'], 'versione slot modificata');
migration_assert($legacy_raw['updated'] === $slots['legacy-raw']['updated'], 'timestamp slot modificato');

ob_start();
aihl_render_code_slot('global_css');
$rendered_css = (string) ob_get_clean();
migration_assert(false !== strpos($rendered_css, '.sx-footer'), 'CSS globale legacy non renderizzato');
migration_assert(false !== strpos($rendered_css, 'data-aihl-slot="legacy-global-css"'), 'CSS globale legacy senza marker slot');

$first_state = $slots;
$second_report = aihl_migrate_legacy_code_slot_governance();
migration_assert($report === $second_report, 'report non idempotente');
migration_assert($first_state === get_option(AIHL_CODE_SLOTS_OPTION, array()), 'seconda migrazione ha riscritto gli slot');

$broken_slots = get_option(AIHL_CODE_SLOTS_OPTION, array());
$broken_slots['legacy-global-css']['active'] = false;
update_option(AIHL_CODE_SLOTS_OPTION, $broken_slots, false);
$broken_report = $report;
$broken_report['deactivated_count'] = 2;
$broken_report['deactivated_slot_ids'][] = 'legacy-global-css';
update_option(AIHL_CODE_SLOTS_GOVERNANCE_MIGRATION_OPTION, $broken_report, false);

$repair = aihl_repair_non_canvas_slot_governance();
$repaired_slots = get_option(AIHL_CODE_SLOTS_OPTION, array());
migration_assert(1 === $repair['restored_count'], 'slot non-Canvas sospeso non ripristinato');
migration_assert($repaired_slots['legacy-global-css']['active'], 'CSS globale non riattivato dalla riparazione 1.13.2');
migration_assert(!$repaired_slots['legacy-raw']['active'], 'override Canvas non conforme riattivato per errore');
migration_assert($repair === aihl_repair_non_canvas_slot_governance(), 'riparazione 1.13.2 non idempotente');

echo "AI-HTML legacy Canvas governance migration OK\n";
