<?php
session_start();
header('Content-Type: application/json');

$schema = json_decode(file_get_contents(__DIR__ . '/schema.json'), true);
$db = new PDO('sqlite:' . __DIR__ . '/data.db');$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

foreach ($schema['tables'] as $table =>$cols) {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) exit(json_encode(['error' => 'Table invalide']));$colsSql = implode(', ', array_map(fn($c) => "$c TEXT", $cols));$db->exec("CREATE TABLE IF NOT EXISTS $table (id INTEGER PRIMARY KEY AUTOINCREMENT,$colsSql, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
}

if (!empty($schema['auth'])) {$db->exec("CREATE TABLE IF NOT EXISTS _users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password TEXT, role TEXT DEFAULT 'user')");
    if ($db->query("SELECT COUNT(*) FROM _users")->fetchColumn() == 0) {
        $db->prepare("INSERT INTO _users (username, password, role) VALUES (?, ?, 'admin')")
           ->execute(['admin', password_hash('admin', PASSWORD_DEFAULT)]);
    }
}

$action =$_GET['action'] ?? '';
$table  =$_GET['table'] ?? '';
$id     =$_GET['id'] ?? null;
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

if ($action === 'login') {
    $stmt =$db->prepare("SELECT * FROM _users WHERE username = ?");
    $stmt->execute([$body['username'] ?? '']);
    $user =$stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || !password_verify($body['password'] ?? '',$user['password'])) {
        http_response_code(401);
        exit(json_encode(['error' => 'Identifiants invalides']));
    }
    $_SESSION['user'] = ['id' =>$user['id'], 'username' => $user['username'], 'role' =>$user['role']];
    exit(json_encode(['user' => $_SESSION['user']]));
}

if ($action === 'logout') {
    session_destroy();
    exit(json_encode(['ok' => true]));
}

if (!empty($schema['auth']) && empty($_SESSION['user'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Non connecté']));
}

if ($action === 'schema') {
    exit(json_encode([
        'appName' => $schema['appName'],
        'auth'    => $schema['auth'],
        'admin'   => $schema['admin'],
        'tables'  => $schema['tables'],
        'user'    => $_SESSION['user'] ?? null
    ]));
}

if (!isset($schema['tables'][$table])) { http_response_code(404); exit(json_encode(['error' => 'Table inconnue'])); }$cols = $schema['tables'][$table];
$method =$_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if ($id) {$stmt = $db->prepare("SELECT * FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        exit(json_encode($stmt->fetch(PDO::FETCH_ASSOC)));
    }
    exit(json_encode($db->query("SELECT * FROM $table ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC)));
}

if ($method === 'POST') {
    $placeholders = implode(', ', array_fill(0, count($cols), '?'));
    $stmt =$db->prepare("INSERT INTO $table (" . implode(', ', $cols) . ") VALUES ($placeholders)");
    $stmt->execute(array_map(fn($c) =>$body[$c] ?? '',$cols));
    exit(json_encode(['id' => $db->lastInsertId()]));
}

if ($method === 'PUT' &&$id) {
    $sets = implode(', ', array_map(fn($c) => "$c = ?", $cols));
    $stmt =$db->prepare("UPDATE $table SET$sets WHERE id = ?");
    $stmt->execute([...array_map(fn($c) => $body[$c] ?? '', $cols),$id]);
    exit(json_encode(['ok' => true]));
}

if ($method === 'DELETE' && $id) {$db->prepare("DELETE FROM $table WHERE id = ?")->execute([$id]);
    exit(json_encode(['ok' => true]));
}
