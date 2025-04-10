<?php
require_once '../models/Reserver.php';
require_once '../auth_check.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $reserver = new Reserver();
    $reserver->setIdreserv($id);
    
    if ($reserver->deleteReservation($id)) {
        header('Location: ReservationPage.php');
        exit;
    } else {
        header('Location: ReservationPage.php?msg=error');
        exit;
    }
} else {
    header('Location: ReservationPage.php');
    exit;
}
?>