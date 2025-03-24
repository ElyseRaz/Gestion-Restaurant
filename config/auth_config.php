<?php
define('ROOT_PATH', '/php/');

// Pages accessibles sans authentification
$public_pages = ['login.php', 'register.php', 'unauthorized.php'];

function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
