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

require_once './controllers/TiendaController.php';
require_once './controllers/VentaController.php';
require_once './middlewares/ValidarDatosTienda.php';
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
    $group->post('/alta', \TiendaController::class . ':CargarUno')->add(new ValidarDatosTienda(array("titulo", "tipo", "precio", "stock")), new ValidarDatosTienda(array("titulo", "precio", "tipo", "anioSalida", "formato", "stock")));
    $group->post('/consultar', \TiendaController::class . ':TraerUno')->add(new ValidarDatosTienda(array("titulo", "tipo", "formato")));
});
$app->group('/ventas', function (RouteCollectorProxy $group) 
{
  $group->post('/alta', \VentaController::class . ':CargarUno')->add(new ValidarDatosVenta(array("mail", "titulo", "tipo", "formato", "cantidad")));
  $group->group('/consultar', function (RouteCollectorProxy $group)
  {   
    $group->get('/productos/vendidos', \VentaController::class . ':TraerProductosVendidos')->add(new ValidarDatosVenta(array("fecha")));
    $group->get('/ventas/porUsuario', \VentaController::class . ':TraerUno')->add(new ValidarDatosVenta(array("usuario")));
    $group->get('/ventas/porProducto', \VentaController::class . ':TraerPorTipoDeTienda')->add(new ValidarDatosTienda(array("tipo")));
    $group->get('/productos/entreValores', \VentaController::class . ':TraerPorRangoPrecio')->add(new ValidarDatosTienda(array("precio1", "precio2")));
    $group->get('/ventas/ingresos', \VentaController::class . ':TraerPorAnioSalida');
    $group->get('/productos/masVendido', \TiendaController::class . ':TraerProductoMasVendido');
  });
  $group->put('/modificar', \VentaController::class . ':ModificarUno')->add(new ValidarDatosVenta(array("mail", "titulo", "tipo", "formato", "cantidad")));
});
$app->run();
