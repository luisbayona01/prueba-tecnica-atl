<?php

declare(strict_types=1);

use App\Config\Env;
use App\Utils\ApiException;
use App\Utils\Response;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Carga de variables de entorno.
Env::load(dirname(__DIR__) . '/.env');

// Desarrollo muestra detalles; produccion los oculta por completo.
$isProduction = Env::get('APP_ENV', 'development') === 'production';

if (!$isProduction) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// --- CORS ---
$allowedOrigins = Env::get('APP_CORS_ALLOWED_ORIGINS', '*');
$origins = array_map('trim', explode(',', $allowedOrigins));

if (in_array('*', $origins, true)) {
    header('Access-Control-Allow-Origin: *');
} elseif (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $origins, true)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Vary: Origin');
}

header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Peticiones OPTIONS de CORS preflight.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $router = require dirname(__DIR__) . '/routes/api.php';
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (ApiException $e) {
    Response::error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
} catch (JsonException) {
    Response::error('El cuerpo de la peticion no es un JSON valido.', 400);
} catch (Throwable $e) {
    if (!$isProduction) {
        Response::error('Error interno del servidor: ' . $e->getMessage(), 500);
    }
    Response::error('Error interno del servidor.', 500);
}