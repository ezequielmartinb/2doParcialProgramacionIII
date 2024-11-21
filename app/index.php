<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
// require_once './middlewares/Logger.php';

require_once './controllers/ProductoController.php';
require_once './controllers/VentaController.php';
require_once './middlewares/ValidarDatosProducto.php';
require_once './middlewares/ValidarDatosVenta.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes
$app->group('/tienda', function (RouteCollectorProxy $group) 
{
    $group->post('/alta', \ProductoController::class . ':CargarUno')->add(new ValidarDatosProducto(array("titulo", "tipo", "precio", "stock")), new ValidarDatosProducto(array("titulo", "precio", "tipo", "anioSalida", "formato", "stock")));
    $group->post('/consultar', \ProductoController::class . ':TraerUno')->add(new ValidarDatosProducto(array("titulo", "tipo", "formato")));
});
$app->group('/ventas', function (RouteCollectorProxy $group) 
{
    $group->post('/alta', \VentaController::class . ':CargarUno')->add(new ValidarDatosVenta(array("mail", "titulo", "tipo", "formato", "stock")));
});

$app->run();
