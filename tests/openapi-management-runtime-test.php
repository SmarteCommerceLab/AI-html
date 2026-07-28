<?php
declare(strict_types=1);

define('ABSPATH', __DIR__);
define('AIHL_THEME_NAME', 'AI-HTML');
define('AIHL_VERSION', '1.12.0');
define('AIHL_OPTION_BASE', 'ai_html');

function add_action($hook, $callback): void {}
function rest_url($path = ''): string { return 'https://example.test/wp-json/' . ltrim((string) $path, '/'); }
function untrailingslashit($value): string { return rtrim((string) $value, '/'); }

class WP_REST_Request {}
class WP_REST_Server {
	public const READABLE = 'GET';
	public const CREATABLE = 'POST';
	public const EDITABLE = 'POST, PUT, PATCH';
	public const DELETABLE = 'DELETE';
}
class AIHL_Fake_REST_Server {
	public function get_routes(): array {
		return array(
			'/aihtml/v1/ai/update' => array(
				array('methods' => array('GET' => true)),
				array('methods' => array('POST' => true)),
			),
			'/aihtml/v1/ai/code-slots/(?P<slot_id>[a-z0-9_-]+)' => array(
				array(
					'methods' => array('DELETE' => true),
					'args' => array('slot_id' => array('type' => 'string', 'required' => true)),
				),
			),
			'/aihtml/v1/ai/content/(?P<id>\d+)/presentation' => array(
				array(
					'methods' => array('PUT' => true),
					'args' => array('id' => array('type' => 'integer', 'minimum' => 1, 'required' => true)),
				),
			),
		);
	}
}
function rest_get_server(): AIHL_Fake_REST_Server { return new AIHL_Fake_REST_Server(); }

require dirname(__DIR__) . '/inc/integrations/ai-api.php';

$openapi = aihl_ai_openapi_payload();

if (!isset($openapi['components']['schemas']['CanvasRequest'], $openapi['components']['schemas']['CodeSlot'])) {
	throw new RuntimeException('Concrete management schemas missing.');
}
$update = $openapi['paths']['/aihtml/v1/ai/update'];
if (!isset($update['get'], $update['post'])) {
	throw new RuntimeException('Update methods missing.');
}
$postSecurity = json_encode($update['post']['security']);
$getSecurity = json_encode($update['get']['security']);
if (str_contains($postSecurity, 'smartAiKey') || !str_contains($postSecurity, 'applicationPassword')) {
	throw new RuntimeException('Update write security is invalid.');
}
if (!str_contains($getSecurity, 'smartAiKey')) {
	throw new RuntimeException('Update read security must allow Smart AI keys.');
}
$delete = $openapi['paths']['/aihtml/v1/ai/code-slots/{slot_id}']['delete'];
if (isset($delete['requestBody'])) {
	throw new RuntimeException('DELETE operation must not expose a request body.');
}
$presentation = $openapi['paths']['/aihtml/v1/ai/content/{id}/presentation']['put'];
if (($presentation['requestBody']['content']['application/json']['schema']['$ref'] ?? '') !== '#/components/schemas/ContentPresentation') {
	throw new RuntimeException('Content presentation schema not linked.');
}

echo "AI-HTML OpenAPI management runtime OK\n";
