<?php
require_once __DIR__ . '../src/core/Database.php';
require_once __DIR__ . '../src/core/Controller.php';
require_once __DIR__ . '../src/core/Router.php';

$router = new Router();

// Admin routes
$router->add('/admin/login', 'admin', 'Auth', 'login');
$router->add('/admin/logout', 'admin', 'Auth', 'logout');
$router->add('/admin/dashboard', 'admin', 'Admin', 'index');
$router->add('/admin/users', 'admin', 'Admin', 'users');
$router->add('/admin/permissions', 'admin', 'Admin', 'permissions');

// Regular routes
$router->add('/', 'user', 'User', 'index');

$url = isset($_GET['url']) ? trim($_GET['url'], '/') : '/';

try {
    $router->dispatch($url);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}