<?php
require_once __DIR__ . '/../config/database.php';

class Connexion {
    private static $connexion = null;

    public static function getConnexion() {
        if (self::$connexion === null) {
            try {
                self::$connexion = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $e) {
                die('Erreur de connexion : ' . $e->getMessage());
            }
        }
        return self::$connexion;
    }
}
?>