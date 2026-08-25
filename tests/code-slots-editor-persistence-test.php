<?php

$source = file_get_contents(__DIR__ . '/../inc/admin/code-slots.php');

$contracts = array(
	'$edit_slot = $save_result;' => 'saved slot is rendered after creation',
	'name="slot_editor_tab"' => 'active editor tab is submitted',
	'data-aihl-editor-tab-input' => 'active editor tab state',
	'section.editor.getValue()' => 'CodeMirror-aware copy',
	'section.editor.setValue(text)' => 'CodeMirror-aware paste',
	'activateTab(<?php echo wp_json_encode($initial_editor_tab); ?>)' => 'saved tab restoration',
);

foreach ($contracts as $needle => $label) {
	if (false === strpos($source, $needle)) {
		fwrite(STDERR, "Code Slots editor persistence contract failed: missing {$label}.\n");
		exit(1);
	}
}

echo "OK Code Slots editor persistence contract verified\n";
