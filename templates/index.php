<?php
$uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

// 1. Laisser passer les vrais fichiers (Back/api.php, assets, etc.)
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

// 2. Table de routage propre -> Dossiers Front/ et Back/
$routes = [
    '/'           => '/Front/Index/index.html',
    '/index'      => '/Front/Index/index.html',
    '/login'      => '/Front/Login/index.html',
    '/admin'      => '/Front/Admin/index.html',
    '/visualdata' => '/Back/visualdata.php',
];

if (isset($routes[$uri]) && file_exists(__DIR__ . $routes[$uri])) {
    $target = __DIR__ . $routes[$uri];
    if (str_ends_with($target, '.php')) {
        require $target;
    } else {
        header('Content-Type: text/html; charset=UTF-8');
        readfile($target);
    }
    exit;
}

http_response_code(404);
echo 'Page introuvable';
