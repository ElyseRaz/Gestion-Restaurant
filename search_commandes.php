<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once 'models/Commandes.php';

try {
    $searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';
    $commandeInstance = new Commandes();

    if (empty($searchTerm)) {
        $result = $commandeInstance->listCommandes(10, 0);
    } else {
        $result = $commandeInstance->searchClients($searchTerm);
    }

    echo json_encode($result ?: []);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la recherche']);
}
