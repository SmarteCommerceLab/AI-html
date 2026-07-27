<?php
declare(strict_types=1);

define('ABSPATH', __DIR__);

function add_action($hook, $callback): void {}

class WP_REST_Request {}
class WP_REST_Server {
	public const READABLE = 'GET';
	public const CREATABLE = 'POST';
	public const EDITABLE = 'POST, PUT, PATCH';
	public const DELETABLE = 'DELETE';
}

require dirname(__DIR__) . '/inc/integrations/ai-api.php';

$route = '/aihtml/v1/ai/pages/(?P<id>\d+)';
$parameters = aihl_ai_openapi_path_parameters(
	$route,
	array('args' => array('id' => array('type' => 'integer', 'minimum' => 1)))
);

if (aihl_ai_openapi_path_from_route($route) !== '/aihtml/v1/ai/pages/{id}') {
	throw new RuntimeException('Dynamic REST route was not converted to an OpenAPI path.');
}
if (
	count($parameters) !== 1
	|| $parameters[0]['name'] !== 'id'
	|| $parameters[0]['in'] !== 'path'
	|| $parameters[0]['required'] !== true
	|| $parameters[0]['schema'] !== array('type' => 'integer', 'minimum' => 1)
) {
	throw new RuntimeException('OpenAPI path parameter schema is invalid.');
}

echo "AI-HTML OpenAPI path parameters OK\n";
