<?php

declare(strict_types=1);

require __DIR__ . '/../src/autoload.php';

use App\Controllers\OrderController;
use App\Http\JsonResponse;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$uri    = rtrim($uri, '/') ?: '/';

$controller = new OrderController();

if ($method === 'POST' && $uri === '/api/orders') {
    $rawBody = file_get_contents('php://input') ?: '';
    $payload = json_decode($rawBody, true) ?? $_POST;

    if (!is_array($payload)) {
        JsonResponse::send(400, [
            'status'  => 'error',
            'message' => 'Body request harus berupa JSON yang valid',
        ]);
        return;
    }

    $controller->store($payload);
    return;
}

if ($method === 'GET' && preg_match('#^/api/orders/(\d+)$#', $uri, $matches) === 1) {
    $controller->show((int) $matches[1]);
    return;
}

JsonResponse::send(404, [
    'status'  => 'error',
    'message' => "Route {$method} {$uri} tidak ditemukan",
]);
