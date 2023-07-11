<?php
////usar en la terminal en la direccion del programa -> php -S localhost:666 -t app
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

//use Psr\Http\Server\RequestHandlerInterface as - El objeto controlador de solicitudes PSR15 (parámetro).
use Psr\Http\Message\ResponseInterface as Response; //respuesta
use Psr\Http\Message\ServerRequestInterface as Request; //(parámetro).
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;


require_once('C:\xampp\htdocs\Slim\slim-php-deployment\vendor\autoload.php');
require_once('C:\xampp\htdocs\Slim\app\JWT\token.php');
require_once('C:\xampp\htdocs\Slim\app\test\Empleado.php');
require_once('C:\xampp\htdocs\Slim\app\db\database.php');
require_once('C:\xampp\htdocs\Slim\app\test\Mesa.php');
require_once('C:\xampp\htdocs\Slim\app\test\Producto.php');
require_once('C:\xampp\htdocs\Slim\app\test\Orden.php');
require_once('.\test\funciones.php');
require_once('C:\xampp\htdocs\Slim\app\logs\logs.php');
require_once('C:\xampp\htdocs\Slim\app\db\guardar.php');
require_once('C:\xampp\htdocs\Slim\app\test\Mesa.php');


// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes

$app->group('/testo', function (RouteCollectorProxy $group) {
  $group->get('/test', \Control::class . ':testeoASD');//me muestra el estado actual de los comandas
});                                                     //lo use para ir provando las modificaciones

$app->group('/admin', function (RouteCollectorProxy $group) {
  $group->post('/ventas', \Mesa::class . ':traerVentasDesdeFecha');//me muestra el estado actual de los comandas
});   

$app->group('/administrar_staff', function (RouteCollectorProxy $group) {
  $group->post('/cambiar_estado_staff', \Control::class . ':administrarEmpleado');//despedir empleado
  $group->post('/mostrar_seguimiento', \Control::class . ':mostrarAccionesEmpleado');//envio el dni y traigo la tabla login
});

$app->group('/manejo_archivo', function (RouteCollectorProxy $group) {
  $group->get('/descarga_pdf', \Guardar::class . ':descargarLogsPDF');
  $group->get('/carga_logs_csv', \Guardar::class . ':cargarLogsCSV');//cargo archivos de csv a una tabla elegida 
  $group->get('/descarga_logs_csv', \Guardar::class . ':descargarLogsCSV');//descargo los datos de una tabla y
});                                                                       //los guardo en csv

$app->group('/servicio', function (RouteCollectorProxy $group) {
  $group->get('/evaluar_servicio', \Control::class . ':evaluar_servicio');//evalua el servicio mozo,restaurante,
});                                                                //comida, puede dejar comentario

$app->group('/comandas', function (RouteCollectorProxy $group) {
  $group->get('/listar_pendientes', \Control::class . ':mostrarPedidosPendientes');//listo las ordenes a prep
  $group->post('/cambiar_estado_comanda', \Control::class . ':listoParaServir');//cambio el estado de una orden
});

$app->group('/pedido', function (RouteCollectorProxy $group) {
  $group->post('/realizar_pedidos', \Control::class . ':realizarPedido');
  $group->get('/mostrar_estados', \Control::class . ':mostrarPedidosPreparados');//muestra los platos listo//para servir
  $group->post('/entregar_plato', \Control::class . ':entregarPlato');//cambio el estado a entregado
  $group->post('/cliente_comiendo', \Control::class . ':clienteComiendo');//si no tiene pendientes cambio a comiendo
  $group->post('/cliente_pagando', \Control::class . ':clientePagando');//si su estado ya es comiendo pasa a pagado y se calcula el total
  $group->post('/cerrar', \Control::class . ':cerrarComanda');//si su estado es pagado se cierra la cuenta solo admin
});

$app->group('/listar', function (RouteCollectorProxy $group) {
    $group->get('/empleados/{lista}', \Control::class . ':listarCategoria');
    $group->get('/productos/{lista}', \Control::class . ':listarCategoria');
    $group->get('/ordenes/{lista}', \Control::class . ':listarCategoria');
  });


$app->group('/alta', function (RouteCollectorProxy $group) {
    $group->post('/empleado', \Control::class . ':crearUno');
    $group->post('/producto', \Control::class . ':crearUno');
    $group->post('/orden', \Control::class . ':crearUno');
  });

$app->group('/inicio', function (RouteCollectorProxy $group){
    
    $group->post('/login', \Empleado::class . ':login');
  });


$app->run();
?>