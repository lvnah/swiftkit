<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Laisser passer les fichiers statiques existants (Front/*, Back/api.php, etc.)
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Route spéciale /visualdata -> Explorateur BDD Admin
if ($uri === '/visualdata') {
    require __DIR__ . '/Back/visualdata.php';
    exit;
}

// Racine "/" -> Redirection vers Front/Index/
header('Location: /Front/Index/');
exit;
