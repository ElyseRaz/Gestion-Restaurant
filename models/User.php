<?php 
require_once 'Connexion.php';

class User {
    private $id;
    private $username;
    private $password;
    private $email;
    private $db;

    public function __construct($username = null, $password = null, $email = null) {
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
    }

    public function signIn() {
        $conn = Connexion::getConnexion();
        // Hash du mot de passe avant l'insertion
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO user (USERNAME, PASSWORD, EMAIL) VALUES (:username, :password, :email)");
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
    }

    public function login($username, $password) {
        $conn = Connexion::getConnexion();
        $query = $conn->prepare('SELECT IDUSER, USERNAME, PASSWORD FROM user WHERE USERNAME = ?');
        $query->execute([$username]);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Débogage 

            // Si le mot de passe stocké n'est pas hashé, on le hashe
            if (strlen($user['PASSWORD']) < 60) {
                // Le mot de passe n'est pas hashé, on met à jour la base
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateQuery = $conn->prepare('UPDATE user SET PASSWORD = ? WHERE IDUSER = ?');
                $updateQuery->execute([$hashedPassword, $user['IDUSER']]);
                
                // On compare directement les mots de passe non hashés
                return ($password === $user['PASSWORD']) ? $user : false;
            } else {
                // Le mot de passe est déjà hashé, on utilise password_verify
                return password_verify($password, $user['PASSWORD']) ? $user : false;
            }
        }
        return false;
    }

    public static function getUserById($id) {
        $conn = Connexion::getConnexion();
        $stmt = $conn->prepare("SELECT * FROM user WHERE IDUSER = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch();
        return $user;
    }

    public static function signOut(){
        session_start();
        session_destroy();
    }
}
?>