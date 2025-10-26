<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', true);

$requestUri = trim($_SERVER['REQUEST_URI'], '/');
$requestUri = strtok($requestUri, '?');
$segments = explode('/', $requestUri);
$method = $_SERVER['REQUEST_METHOD'];

if ($segments[0] === 'api') {
    // API Routes
    header('Content-Type: application/json');
    $resource = $segments[1] ?? null;
    $id = $segments[2] ?? null;
    $controllerName = ucfirst($resource) . 'ApiController';
    $controllerClass = "App\\Controllers\\Api\\" . $controllerName;

    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        if ($method === 'GET' && $id) {
            if ($id == 'login') {
                $controller->login();
            } else {
                $controller->show($id);
            }
        } elseif ($method === 'GET') {
            $controller->index();
        } elseif ($method === 'POST') {
            $controller->store();
        } elseif ($method === 'PUT' || $method === 'PATCH') {

            $controller->update($id);
        } elseif ($method === 'DELETE') {
            $controller->delete($id);
        } else {
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'API endpoint not found']);
    }
} else {
    // Web Routes
    if (in_array('logout', $segments)) {
        $controllerName = 'HomeController';
        $methodName = 'logout';
        $params = [];
    } else {
        $controllerName = !empty($segments[0]) ? ucfirst($segments[0]) . 'Controller' : 'HomeController';
        $methodName = $segments[1] ?? 'index';
        $params = array_slice($segments, 2);
    }
    $controllerClass = "App\\Controllers\\" . $controllerName;

    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();

        if (method_exists($controller, $methodName)) {
            call_user_func_array([$controller, $methodName], $params);
        } else {
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        http_response_code(404);
        echo "Page not found";
    }
}
