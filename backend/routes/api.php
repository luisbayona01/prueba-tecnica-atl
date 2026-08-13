<?php

declare(strict_types=1);

use App\Controllers\ContactController;
use App\Controllers\PhoneController;
use App\Repositories\ContactRepository;
use App\Repositories\PhoneRepository;
use App\Routes\Router;
use App\Services\ContactService;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$router = new Router();

$contactService = new ContactService(
    new ContactRepository(),
    new PhoneRepository()
);

$contactController = new ContactController($contactService);
$phoneController   = new PhoneController($contactService);

// --- Rutas de contactos ---
$router->get('/api/contactos', static fn (array $params) => $contactController->index());
$router->post('/api/contactos', static fn (array $params) => $contactController->store());
$router->get('/api/contactos/{id}', static fn (array $params) => $contactController->show($params['id']));
$router->delete('/api/contactos/{id}', static fn (array $params) => $contactController->destroy($params['id']));

// --- Rutas de telefonos (recursos anidados) ---
$router->post('/api/contactos/{id}/telefonos', static fn (array $params) => $phoneController->store($params['id']));
$router->delete('/api/contactos/{id}/telefonos/{telefonoId}', static fn (array $params) => $phoneController->destroy($params['id'], $params['telefonoId']));

return $router;
