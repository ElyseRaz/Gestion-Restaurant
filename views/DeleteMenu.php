<?php 
// Requête de suppression de menu

require_once 'header.php';
require_once '../models/Menus.php';
require_once '../controllers/Menu.php';
require_once '../models/Connexion.php'; 
require_once '../auth_check.php';



if (isset($_GET['idplat'])) {
    $idplat = $_GET['idplat'];
    $menu = new Menus(); 
    $menu->setIdplat($idplat);
    $menu->deleteMenu($idplat); 
    header('Location: MenuPage.php');
}

?>