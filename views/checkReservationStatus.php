<?php
require_once '../models/Reserver.php';
require_once '../models/Tables.php';

$reserver = new Reserver();
$tables = new Tables();
$reservations = $reserver->listReservations();
$now = new DateTime();
$updates = [];

foreach ($reservations as $reservation) {
    $reservationDate = DateTime::createFromFormat('Y-m-d H:i:s', $reservation['DATERESERVE']);
    
    if ($reservationDate) {
        $timeDiff = $now->getTimestamp() - $reservationDate->getTimestamp();
        
        // Réservation en cours (±30 minutes)
        if (abs($timeDiff) <= 1800) {
            $tables->updateTableStatus($reservation['NUMTABLE'], 1);
            $updates[] = ['table' => $reservation['NUMTABLE'], 'status' => 'occupied'];
        }
        // Réservation expirée
        elseif ($timeDiff > 1800) {
            $tables->updateTableStatus($reservation['NUMTABLE'], 0);
            $updates[] = ['table' => $reservation['NUMTABLE'], 'status' => 'free'];
        }
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'updates' => $updates]);
