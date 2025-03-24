<?php 

require_once 'header.php';
require_once '../models/Reserver.php';
require_once '../models/Tables.php';
require_once '../auth_check.php';

if(isset($_GET['id'])){
    $reservid = $_GET['id'];
    $reserver = new Reserver();
    $reserver->setIdreserv($reservid);
    $reserver->deleteReservation($reservid);
    header('Location: ReservationPage.php');
}



?>