<?php
require_once '../models/Tables.php';

header('Content-Type: application/json');

try {
    $table = new Tables();
    $allTables = $table->listTables(); // Toujours retourner toutes les tables
    
    echo json_encode([
        'success' => true,
        'tables' => $allTables,
        'count' => count($allTables)
    ]);
} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
