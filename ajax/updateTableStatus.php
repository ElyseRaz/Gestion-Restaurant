<?php
require_once '../models/Tables.php';
require_once '../models/Reserver.php';

// Vérifier et mettre à jour le statut des tables
function checkAndUpdateTables() {
    $reserver = new Reserver();
    $tables = new Tables();
    
    $reservations = $reserver->listReservations();
    $now = new DateTime();
    
    foreach ($reservations as $reservation) {
        $reservationDate = new DateTime($reservation['DATERESERVATION']);
        
        // Si l'heure actuelle correspond à l'heure de réservation
        if ($now->format('Y-m-d H:i') === $reservationDate->format('Y-m-d H:i')) {
            $tables->updateTableStatus($reservation['NUMTABLE'], 1);
        }
        
        // Si la réservation est passée (plus de 2 heures)
        $timeLimit = clone $reservationDate;
        $timeLimit->modify('+2 hours');
        if ($now > $timeLimit) {
            $tables->updateTableStatus($reservation['NUMTABLE'], 0);
        }
    }
    
    // Récupérer la liste mise à jour des tables
    $updatedTables = $tables->listTables();
    
    echo json_encode([
        'success' => true,
        'tables' => $updatedTables
    ]);
}

checkAndUpdateTables();
