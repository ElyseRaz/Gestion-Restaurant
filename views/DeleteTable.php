<?php 
//requete de suppression de table

require_once 'header.php';
require_once '../models/Tables.php';
require_once '../controllers/Table.php';
require_once '../models/Connexion.php';
require_once '../auth_check.php';

if(isset($_GET['idtable'])){
    $tableid = $_GET['idtable'];
    $table = new Tables();
    $table->setIdTable($tableid);
    $table->deleteTable($tableid);
    header('Location: TablePage.php');
}


?>