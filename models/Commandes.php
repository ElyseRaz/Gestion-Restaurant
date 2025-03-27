<?php
require_once 'Connexion.php';

class Commandes extends Connexion
{
    //recuperation des variables
    private $idcom;
    private $idtable;
    private $nomcli;
    private $datecom;
    private $typecom;
    public $error;

    //constructeur
    public function __construct()
    {
        $this->idcom = "";
        $this->idtable = "";
        $this->nomcli = "";
        $this->datecom = "";
        $this->typecom = "Sur table";
        $this->error = "";
    }

    //getters
    public function getIdcom()
    {
        return $this->idcom;
    }

    public function getIdtable()
    {
        return $this->idtable;
    }

    public function getNomcli()
    {
        return $this->nomcli;
    }

    public function getDatecom()
    {
        return $this->datecom;
    }

    public function getTypecom()
    {
        return $this->typecom;
    }

    public function getCommande()
    {
        // Add logic to retrieve the command details based on $this->idcom
        // Example:
        if (!isset($this->idcom)) {
            throw new Exception("ID Commande is not set.");
        }
        // Simulate fetching data (replace with actual database logic)
        $this->idtable = 1; // Example table ID
        $this->idcom = $this->idcom; // Keep the same ID
    }

    public function getNomClient()
    {
        return $this->nomcli;
    }

    public function getTypeCommande()
    {
        return $this->typecom;
    }

    public function getDateCommande()
    {
        return $this->datecom;
    }

    public function getError()
    {
        return $this->error;
    }

    //setters

    public function setIdcom($idcom)
    {
        $this->idcom = $idcom;
    }

    public function setIdtable($idtable)
    {
        $this->idtable = $idtable;
    }

    public function setNomcli($nomcli)
    {
        $this->nomcli = $nomcli;
    }

    public function setDatecom($datecom)
    {
        $this->datecom = $datecom;
    }

    public function setTypecom($typecom)
    {
        $this->typecom = $typecom;
    }

    //methode pour ajouter une ou plusieurs commandes

    public function addCommande()
    {
        $con = $this->getConnexion();
        $sql = "INSERT INTO commande (IDCOM, IDTABLE, NOMCLI, DATECOM, TYPECOM) VALUES (?, ?, ?, ?, ?)";
        $req = $con->prepare($sql);

        $req->bindParam(1, $this->idcom, PDO::PARAM_STR);
        $req->bindParam(2, $this->idtable, PDO::PARAM_STR);
        $req->bindParam(3, $this->nomcli, PDO::PARAM_STR);
        $req->bindParam(4, $this->datecom, PDO::PARAM_STR);
        $req->bindParam(5, $this->typecom, PDO::PARAM_STR);
        return $req->execute();
    }

    //methode pour modifier une commande

    public function updateCommande($data)
    {
        try {
            $con = $this->getConnexion();
                    
            // Récupérer l'ancienne table si elle existe
            $oldData = $this->getCommandById($data['IDCOM']);
            
            // Si le type passe à "Emporté", on libère la table
            if ($data['TYPECOM'] === 'Emporté') {
                $data['IDTABLE'] = null;
            }

            $sql = "UPDATE commande 
                    SET NOMCLI = :nomcli,
                        DATECOM = :datecom,
                        TYPECOM = :typecom,
                        IDTABLE = :idtable
                    WHERE IDCOM = :idcom";

            $stmt = $con->prepare($sql);
            
            $result = $stmt->execute([
                ':idcom' => $data['IDCOM'],
                ':nomcli' => $data['NOMCLI'],
                ':datecom' => $data['DATECOM'],
                ':typecom' => $data['TYPECOM'],
                ':idtable' => $data['IDTABLE']
            ]);

            if (!$result) {
                error_log("Erreur SQL updateCommande: " . print_r($stmt->errorInfo(), true));
                return false;
            }

            // Libérer l'ancienne table si nécessaire
            if ($oldData && $oldData['IDTABLE'] && ($data['TYPECOM'] === 'Emporté' || $data['IDTABLE'] != $oldData['IDTABLE'])) {
                $this->libererTable($oldData['IDTABLE']);
            }

            return true;
        } catch (PDOException $e) {
            error_log("Exception updateCommande: " . $e->getMessage());
            return false;
        }
    }

    // Nouvelle méthode pour libérer une table
    private function libererTable($idTable)
    {
        try {
            $con = $this->getConnexion();
            $sql = "UPDATE tables SET DISPONIBLE = 1 WHERE NUMTABLE = :idtable";
            $stmt = $con->prepare($sql);
            return $stmt->execute([':idtable' => $idTable]);
        } catch (PDOException $e) {
            error_log("Erreur libération table: " . $e->getMessage());
            return false;
        }
    }

    //methode pour supprimer une commande

    public function deleteCommande()
    {
        $con = $this->getConnexion();
        $req = $con->prepare("DELETE FROM commande WHERE IDCOM=?");
        $req->execute(array($this->idcom));
    }

    //methode pour lister les commandes

    public function listCommandes($limit, $offset)
    {
        try {
            $con = $this->getConnexion();
            $sql = "SELECT * FROM commande ORDER BY IDCOM ASC LIMIT :limit OFFSET :offset";
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function countCommandes()
    {
        try {
            $con = $this->getConnexion();
            $sql = "SELECT COUNT(*) as total FROM commande";
            $stmt = $con->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function generateCommandId()
    {
        $con = $this->getConnexion();
        $req = $con->prepare("SELECT IDCOM FROM commande ORDER BY IDCOM DESC LIMIT 1");
        $req->execute();
        $lastId = $req->fetch(PDO::FETCH_ASSOC);

        if ($lastId && isset($lastId['IDCOM'])) { // Vérifiez que 'IDCOM' existe
            // Extraire le numéro de l'ID (ex: "C0001" -> 1)
            $num = (int)substr($lastId['IDCOM'], 1);
            // Incrémenter le numéro et formater avec des zéros
            $newId = 'C' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        } else {
            // Si aucune commande n'existe, commencer par "C0001"
            $newId = 'C0001';
        }

        // Vérifiez si l'ID généré existe déjà
        $checkReq = $con->prepare("SELECT COUNT(*) FROM commande WHERE IDCOM = ?");
        $checkReq->execute([$newId]);
        $exists = $checkReq->fetchColumn();

        if ($exists > 0) {
            // Si l'ID existe déjà, incrémentez à nouveau
            $num++;
            $newId = 'C' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        }

        return $newId;
    }

    public function addCommand($idcommande, $idtable, $nomcli, $datecom, $typecom)
    {
        $con = $this->getConnexion();
        $req = $con->prepare("INSERT INTO commande   (idcommande, idtable, nomcli, datecom, typecom) VALUES (?, ?, ?, ?, ?)");
        $req->execute(array($idcommande, $idtable, $nomcli, $datecom, $typecom));
    }

    public function getAllCommands()
    {
        $con = $this->getConnexion();
        $query = "SELECT * FROM commande ORDER BY IDCOM DESC"; // Assurez-vous que la requête est correcte
        $result = $con->query($query);

        if ($result) {
            $commands = $result->fetchAll(PDO::FETCH_ASSOC);
            return $commands;
        } else {
            // Débogage : Affichez l'erreur SQL
            echo "Erreur SQL : " . $con->error;
            return [];
        }
    }

    public function getCommandById($idcom)
    {
        $con = $this->getConnexion();
        $req = $con->prepare("SELECT * FROM commande WHERE IDCOM = ?");
        $req->execute(array($idcom));
        return $req->fetch();
    }

    // public function countCommandes()
    // {
    //     $con = $this->getConnexion();
    //     $query = "SELECT COUNT(*) as total FROM commande";
    //     $stmt = $con->prepare($query);
    //     $stmt->execute();
    //     $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //     return $result['total'];
    // }

    public function searchClients($searchTerm, $limit = 10, $offset = 0) {
        try {
            $con = $this->getConnexion();
            $searchTerm = "%$searchTerm%";
            $query = "SELECT * FROM commande WHERE NOMCLI LIKE :searchTerm ORDER BY IDCOM ASC LIMIT :limit OFFSET :offset";
            $stmt = $con->prepare($query);
            $stmt->bindValue(':searchTerm', $searchTerm, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur recherche clients: " . $e->getMessage());
            return [];
        }
    }

    public function countSearchResults($searchTerm) {
        try {
            $con = $this->getConnexion();
            $searchTerm = "%$searchTerm%";
            $query = "SELECT COUNT(*) FROM commande WHERE NOMCLI LIKE :searchTerm";
            $stmt = $con->prepare($query);
            $stmt->bindValue(':searchTerm', $searchTerm, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erreur comptage résultats: " . $e->getMessage());
            return 0;
        }
    }

    public function searchByDate($date) {
        try {
            $con = $this->getConnexion();
            $sql = "SELECT * FROM commande WHERE DATE(DATECOM) = :date";
            $stmt = $con->prepare($sql);
            $stmt->execute(['date' => $date]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur recherche par date: " . $e->getMessage());
            return [];
        }
    }

    public function searchByDateRange($startDate, $endDate) {
        try {
            $con = $this->getConnexion();
            $sql = "SELECT * FROM commande WHERE DATE(DATECOM) BETWEEN :start_date AND :end_date";
            $stmt = $con->prepare($sql);
            $stmt->execute([
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur recherche par plage de dates: " . $e->getMessage());
            return [];
        }
    }
}
?>




