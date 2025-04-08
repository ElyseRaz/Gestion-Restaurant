<?php
require_once '../models/Reserver.php';

header('Content-Type: application/json');

try {
    $reserver = new Reserver();
    $limit = 10; // Nombre d'éléments par page
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    
    if (!isset($_GET['search']) || empty($_GET['search'])) {
        // Retourner les réservations paginées
        $results = $reserver->getActiveReservationsWithPagination($limit, $offset);
        $totalReservations = $reserver->countReservations();
        $totalPages = ceil($totalReservations / $limit);
    } else {
        // Rechercher avec le terme fourni (sans pagination)
        $results = $reserver->searchReservations($_GET['search']);
        $totalPages = 1; // Pas de pagination pour les résultats de recherche
    }

    // Formater les dates pour l'affichage
    foreach ($results as &$reservation) {
        $reservation['DATERESERVATION'] = date('d-m-Y H:i', strtotime($reservation['DATERESERVATION']));
        $reservation['DATERESERVE'] = date('d-m-Y H:i', strtotime($reservation['DATERESERVE']));
    }

    echo json_encode([
        'success' => true,
        'reservations' => $results,
        'currentPage' => $page,
        'totalPages' => $totalPages
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
