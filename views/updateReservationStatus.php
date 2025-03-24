<?php
require_once '../models/Reserver.php';
require_once '../models/Tables.php';

function updateTableStatuses() {
    $reserver = new Reserver();
    $tables = new Tables();
    $reservations = $reserver->listReservations();
    $now = new DateTime();

    foreach ($reservations as $reservation) {
        $reservationDate = DateTime::createFromFormat('Y-m-d H:i:s', $reservation['DATERESERVE']);
        if ($reservationDate) {
            $minutesDiff = floor(($now->getTimestamp() - $reservationDate->getTimestamp()) / 60);
            
            // Si la date de réservation est aujourd'hui et dans les 30 minutes
            if ($reservationDate->format('Y-m-d') === $now->format('Y-m-d') && abs($minutesDiff) <= 30) {
                $tables->updateTableStatus($reservation['NUMTABLE'], 1);
            } else {
                $tables->updateTableStatus($reservation['NUMTABLE'], 0);
            }
        }
    }
}

updateTableStatuses();
header('Content-Type: application/json');
echo json_encode(['success' => true]);
