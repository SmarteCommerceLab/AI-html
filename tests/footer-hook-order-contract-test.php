<?php
declare(strict_types=1);

$source = file_get_contents(dirname(__DIR__) . '/footer.php');
$afterContent = strpos($source, "aihl_render_code_slot('after_content')");
$beforeFooter = strpos($source, "aihl_render_code_slot('before_footer')");
$footerOverride = strpos($source, "aihl_should_render_canvas_structure('footer')");
$afterFooter = strpos($source, "aihl_render_code_slot('after_footer')");

if ($afterContent === false || $beforeFooter === false || $footerOverride === false || $afterFooter === false) {
	fwrite(STDERR, "FAIL: hook footer obbligatorio assente\n");
	exit(1);
}
if (!($afterContent < $beforeFooter && $beforeFooter < $footerOverride && $footerOverride < $afterFooter)) {
	fwrite(STDERR, "FAIL: ordine hook contenuto/footer non conforme\n");
	exit(1);
}

echo "AI-HTML footer hook order contract OK\n";
