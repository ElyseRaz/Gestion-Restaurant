<?php 
require_once 'header.php';
require_once '../models/Menus.php';
require_once '../models/Commandedetail.php';
require_once '../models/Commandes.php';
require_once '../models/Connexion.php';
require_once '../auth_check.php';

if (isset($_GET['idcom'])){
    $idcom = $_GET['idcom'];
    $commande = new Commandes();
    $commande->setIdcom($idcom);
    $detail = new Commandedetail();
    $detail->setIdcom($idcom);
    $detail->deleteCommandedetail($idcom);
    $commande->deleteCommande($idcom);
    header('Location: CommandePage.php');
}




?>