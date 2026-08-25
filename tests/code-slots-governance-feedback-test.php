<?php

$source = file_get_contents(__DIR__ . '/../inc/admin/code-slots.php');

$contracts = array(
	'function aihl_code_slot_governance_error_message' => 'governance error formatter',
	'aihl_code_slot_governance_error_message($governance_report)' => 'detailed activation error',
	'$saved_governance_blocked' => 'saved slot warning state',
	'aihl-slot-governance-issues' => 'governance issues panel',
	'Correggi il codice usando token semantici' => 'actionable remediation text',
);

foreach ($contracts as $needle => $label) {
	if (false === strpos($source, $needle)) {
		fwrite(STDERR, "Code Slots governance feedback contract failed: missing {$label}.\n");
		exit(1);
	}
}

echo "OK Code Slots governance feedback contract verified\n";
