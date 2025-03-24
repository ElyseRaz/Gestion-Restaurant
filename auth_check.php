<?php
session_start();
require_once 'config/auth_config.php';

// Define ROOT_PATH if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', '/path/to/root/'); // Replace '/path/to/root/' with the actual root path
}

// Obtenir le nom du fichier courant
$current_page = basename($_SERVER['PHP_SELF']);

// Logging pour déboggage
error_log("Page courante : " . $current_page);
error_log("Est authentifié : " . (isAuthenticated() ? "oui" : "non"));
error_log("Est page publique : " . (in_array($current_page, $public_pages) ? "oui" : "non"));

// Vérifier si la page n'est pas publique et si l'utilisateur n'est pas connecté
if (!in_array($current_page, $public_pages) && !isAuthenticated()) {
    header('Location: ' . ROOT_PATH . 'unauthorized.php');
    exit();
}
