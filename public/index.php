<?php 

#Se carga todo lo que esta en el espacio de trabajo del vendor
#autoload, en este caso app/

ini_set('display_errors', 1);
ini_set('display_starup_errors', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';

session_start();

$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

use Illuminate\Database\Capsule\Manager as Capsule;
use Aura\Router\RouterContainer;

//Creamos la instancia del ORM manager.
$capsule = new Capsule;

//Configuramos la conexión.
$capsule->addConnection([
    'driver'    => getenv('DB_DRIVER'),
    'host'      => getenv('DB_HOST'),
    'database'  => getenv('DB_NAME'),
    'username'  => getenv('DB_USER'),
    'password'  => getenv('DB_PASS'),
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

//Son capturadas las request en las variables superglobales.
$request = Zend\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

//Creamos la instancia del contenedor de rutas.
$routerContainer = new RouterContainer();
//Creamos un mapa de rutas
$map = $routerContainer->getMap();
//Index
$map->get('index', '/SGE/', [
    'controller' => 'App\Controllers\IndexController',
    'action' => 'indexAction'
    ]);
//Login
$map->get('sign-in', '/SGE/sign-in', [
    'controller' => 'App\Controllers\AuthController',
    'action' => 'getLoginAction'
    ]);
$map->post('login', '/SGE/login', [
    'controller' => 'App\Controllers\AuthController',
    'action' => 'getLoginAction'
    ]);
//Logout
$map->get('logout', '/SGE/logout', [
    'controller' => 'App\Controllers\AuthController',
    'action' => 'getLogoutAction'
    ]);
//Students
$map->get('addStudent', '/SGE/addStudent', [
    'controller' => 'App\Controllers\StudentController',
    'action' => 'getAddStudentAction'
    ]);
$map->post('registerStudent', '/SGE/registerStudent', [
    'controller' => 'App\Controllers\StudentController',
    'action' => 'getAddStudentAction'
    ]);
$map->get('panelStudent', '/SGE/panelStudent', [
    'controller' => 'App\Controllers\StudentController',
    'action' => 'getPanelStudentAction'
    ]);
$map->get('profileStudent', '/SGE/profileStudent', [
    'controller' => 'App\Controllers\StudentController',
    'action' => 'getProfileStudentAction'
    ]);
$map->get('editProfileStudent', '/SGE/editProfileStudent', [
    'controller' => 'App\Controllers\StudentController',
    'action' => 'getEditProfileStudentAction'
    ]);
$map->post('updateProfileStudent', '/SGE/updateProfileStudent', [
    'controller' => 'App\Controllers\StudentController',
    'action' => 'getUpdateProfileStudentAction'
    ]);
$map->get('studentsList', '/SGE/studentsList', [
    'controller' => 'App\Controllers\StudentController',
    'action' => 'getAllStudentsAction'
    ]);

//Admin
$map->get('panelAdmin', '/SGE/panelAdmin', [
    'controller' => 'App\Controllers\AdminController',
    'action' => 'getPanelAdminAction'
    ]);

//Creamos el validador de rutas
$matcher = $routerContainer->getMatcher();
//Obtenermos la ruta según la petición
$route = $matcher->match($request);

//Validamos que la ruta sea correcta y emitimos la respuesta correspondiente.
if (!$route){
    echo "Ruta no encontrada.";
}
else{
    //El manejador de la ruta contiene la información básica de la misma
    $handlerData = $route->handler;
    $controllerName = new $handlerData['controller'];
    $actionName = $handlerData['action'];
    $needsAuth = $handlerData['auth'] ?? false; //Se comprueba si el usuario necesita autenticarse

    $sessionUserId = $_SESSION['userId'] ?? null;
    $responseMessage = null;
    if ($needsAuth && !$sessionUserId) {

        $controllerName = 'App\Controller\AuthController';
        $actionName = 'getLogout' ;
    }

    $controller = new $controllerName;
    $response = $controller->$actionName($request);

    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            
                header(sprintf('%s: %s', $name, $value), false);
        }
    }
    http_response_code($response->getStatusCode());
    echo $response->getBody();
}