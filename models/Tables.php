<?php
    require_once 'Connexion.php';

    class Tables extends Connexion
    {
        //declaration des variables
        private $idtable;
        private $designation;
        private $occupation;

        //constructeur
        public function __construct()
        {
            $this->idtable = "";
            $this->designation = "";
            $this->occupation = 0;
        }

        //getters
        public function getIdtable()
        {
            return $this->idtable;
        }

        public function getDesignation()
        {
            return $this->designation;
        }

        public function getOccupation()
        {
            return $this->occupation;
        }

        //setters
        public function setIdtable($idtable)
        {
            $this->idtable = $idtable;
        }

        public function setDesignation($designation)
        {
            $this->designation = $designation;
        }

        public function setOccupation($occupation)
        {
            $this->occupation = $occupation;
        }

        //methode pour recuperer une table
        public function getTable($idtable)
        {
            $con = $this->getConnexion();
            $req = $con->prepare("SELECT * FROM tables WHERE NUMTABLE=?");
            $req->execute(array($idtable));
            return $req->fetch(PDO::FETCH_ASSOC); // Modifié pour retourner directement les données
        }
        //methode pour ajouter une table
        public function addTable()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("INSERT INTO tables(DESIGNATION,OCCUPATION) VALUES(?,?)");
            $req->bindParam(1,$this->designation);
            $req->bindParam(2,$this->occupation);
            return $req->execute();
            
           
        }

        //methode pour modifier une table
        public function updateTable()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("UPDATE tables SET DESIGNATION=?,OCCUPATION=? WHERE NUMTABLE=?");
            $req->bindParam(1,$this->designation);
            $req->bindParam(2,$this->occupation);
            $req->bindParam(3,$this->idtable);
            return $req->execute();
        }

        //methode pour supprimer une table
        public function deleteTable()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("DELETE FROM tables WHERE NUMTABLE=?");
            $req->execute(array($this->idtable));
        }

        //methode pour lister les tables
        public function listTables()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("SELECT * FROM tables ORDER BY NUMTABLE ASC");
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        }

        //methode pour que une table prise par un client ne peut pas etre prise sauf si l'addition est payee
        public function tableOccupee()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("SELECT * FROM tables WHERE occupation=1");
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        }

        //methode pour que une table prise par un client ne peut pas etre prise sauf si l'addition est payee
        public function tableLibre()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("SELECT * FROM tables WHERE occupation=0");
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        }

        //methode où la table devient libre si la commande est payee

        public function libererTable()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("UPDATE tables SET occupation=0 WHERE idtables=?");
            $req->execute(array($this->idtable));
        }

        //methode pour lister les IDs des tables
        public function listTableIds()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("SELECT NUMTABLE, DESIGNATION FROM tables ORDER BY NUMTABLE ASC");
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        }

        // Méthode pour lister uniquement les tables occupées
        public function listOccupiedTables()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("SELECT NUMTABLE, DESIGNATION FROM tables WHERE OCCUPATION = 1 ORDER BY NUMTABLE ASC");
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        }

        // Méthode pour libérer une table
        public function freeTable()
        {
            $con = $this->getConnexion();
            $sql = "UPDATE tables SET OCCUPATION = 0 WHERE NUMTABLE = ?";
            $req = $con->prepare($sql);
            $req->bindParam(1, $this->idtable, PDO::PARAM_STR);
            $req->execute();
        }

        // Méthode pour obtenir les tables disponibles
        public function getAvailableTables($currentTableId = null) {
            $con = $this->getConnexion();
            $sql = "SELECT * FROM tables 
                    WHERE OCCUPATION = 1 
                    OR NUMTABLE = ?
                    ORDER BY NUMTABLE";
            $stmt = $con->prepare($sql);
            $stmt->execute([$currentTableId]);
            return $stmt->fetchAll();
        }

        // Méthode pour obtenir une table par son ID
        public function getTableById($id) {
            $con = $this->getConnexion();
            $sql = "SELECT * FROM tables WHERE NUMTABLE = ?";
            $stmt =$con->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        }

        // Méthode pour mettre à jour le statut de la table
        public function updateTableStatus($tableNumber, $status) {
            try {
                $con = $this->getConnexion();
                $occupation = ($status == 1) ? 1 : 0;
                
                // Vérifier l'état actuel de la table
                $currentStatus = $this->getTable($tableNumber);
                
                // Ne mettre à jour que si l'état est différent
                if ($currentStatus && $currentStatus['OCCUPATION'] != $occupation) {
                    $sql = "UPDATE tables SET OCCUPATION = :occupation WHERE NUMTABLE = :tableNumber";
                    $stmt = $con->prepare($sql);
                    $stmt->bindParam(':occupation', $occupation, PDO::PARAM_INT);
                    $stmt->bindParam(':tableNumber', $tableNumber, PDO::PARAM_INT);
                    
                    $result = $stmt->execute();
                    error_log("Mise à jour Table " . $tableNumber . " - Nouveau statut: " . $occupation);
                    return $result;
                }
                
                return true; // Pas de changement nécessaire
            } catch(PDOException $e) {
                error_log("Erreur mise à jour table : " . $e->getMessage());
                return false;
            }
        }

        // Méthode pour obtenir les tables disponibles à une heure donnée
        public function getAvailableTableswithDate($datetime) {
            try {
                $conn = $this->getConnexion();
                $query = "SELECT t.* FROM tables t 
                         WHERE t.NUMTABLE NOT IN (
                             SELECT r.IDTABLE FROM reserver r 
                             WHERE :datetime BETWEEN DATE_SUB(r.DATERESERVEE, INTERVAL 30 MINUTE) 
                             AND DATE_ADD(r.DATERESERVEE, INTERVAL 30 MINUTE)
                         )";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':datetime', $datetime);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                echo "Erreur : " . $e->getMessage();
                return [];
            }
        }

        // Méthode simplifiée pour obtenir les tables disponibles à une date et heure donnée
        public function getAvailableTablesForDateTime($datetime = null) {
            try {
                $conn = $this->getConnexion();
                // Retourner toutes les tables, la vérification se fera à la soumission
                return $this->listTables();
            } catch(PDOException $e) {
                error_log("Erreur SQL: " . $e->getMessage());
                return [];
            }
        }

        // Méthode pour lister les tables avec pagination
        public function listTablesWithPagination($limit, $offset) {
            try {
                $con = $this->getConnexion();
                $sql = "SELECT * FROM tables ORDER BY NUMTABLE ASC LIMIT :limit OFFSET :offset";
                $stmt = $con->prepare($sql);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                return false;
            }
        }

        // Méthode pour compter le nombre total de tables
        public function countTables() {
            try {
                $con = $this->getConnexion();
                $sql = "SELECT COUNT(*) as total FROM tables";
                $stmt = $con->query($sql);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return (int)$result['total'];
            } catch(PDOException $e) {
                return 0;
            }
        }

        // Méthode pour rechercher des tables
        public function searchTables($search) {
            try {

                $db = $this->getConnexion();
                $query = "SELECT * FROM tables WHERE DESIGNATION LIKE :search OR NUMTABLE LIKE :search";
                $stmt = $db->prepare($query);
                $searchTerm = "%" . $search . "%";
                $stmt->bindParam(':search', $searchTerm);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                echo "Erreur : " . $e->getMessage();
                return [];
            }
        }
    }

?>