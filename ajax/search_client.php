<?php
require_once '../models/Reserver.php';

header('Content-Type: application/json');
error_log("Recherche client démarrée");

if (isset($_GET['term'])) {
    $term = $_GET['term'];
    error_log("Terme recherché : " . $term);
    
    $reserver = new Reserver();
    $results = $reserver->searchReservationsByClient($term);
    error_log("Résultats trouvés : " . json_encode($results));
    
    echo json_encode($results);
} else {
    error_log("Aucun terme de recherche fourni");
    echo json_encode([]);
}
