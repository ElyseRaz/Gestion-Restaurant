<?php
require_once '../models/Tables.php';
require_once '../auth_check.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $tables = new Tables();
    $results = $tables->searchTables($search);
    echo json_encode($results, JSON_THROW_ON_ERROR);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la recherche']);
}
