<?php
    require_once 'Connexion.php';

    class Reserver extends Connexion
    {
        private $idreserv;
        private $idtable;
        private $datereservation;
        private $datereservee;
        private $nomcli;
        private $status;

        public function __construct()
        {
            $this->idreserv = "";
            $this->idtable = "";
            $this->datereservation = "";
            $this->datereservee = "";
            $this->nomcli = "";
            $this->status = 'À Venir';
        }

        //getters
        public function getIdreserv()
        {
            return $this->idreserv;
        }

        public function getIdtable()
        {
            return $this->idtable;
        }

        public function getDatereservation()
        {
            return $this->datereservation;
        }

        public function getDatereservee()
        {
            return $this->datereservee;
        }

        public function getNomcli()
        {
            return $this->nomcli;
        }

        public function getStatus()
        {
            return $this->status;
        }

        //setters

        public function setIdreserv($idreserv)
        {
            $this->idreserv = $idreserv;
        }

        public function setIdtable($idtable)
        {
            $this->idtable = $idtable;
        }

        public function setDatereservation($datereservation)
        {
            $this->datereservation = $datereservation;
        }

        public function setDatereservee($datereservee)
        {
            $this->datereservee = $datereservee;
        }

        public function setNomcli($nomcli)
        {
            $this->nomcli = $nomcli;
        }

        public function setStatus($status)
        {
            $this->status = $status;
        }

        //methode pour ajouter une reservation

        public function addReservation()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("INSERT INTO reserver(IDRESERVATION,NUMTABLE,DATERESERVATION,DATERESERVE,NOMCLI,STATUT) VALUES(?,?,?,?,?,?)");
            $req->bindParam(1,$this->idreserv);
            $req->bindParam(2,$this->idtable);
            $req->bindParam(3,$this->datereservation);
            $req->bindParam(4,$this->datereservee);
            $req->bindParam(5,$this->nomcli);
            $req->bindParam(6,$this->status);
            return $req->execute();
        }

        //methode pour modifier une reservation
        public function updateReservation()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("UPDATE reserver SET NUMTABLE=?,DATERESERVATION=?,DATERESERVE=?,NOMCLI=?,STATUT=? WHERE IDRESERVATION=?");
            $req->bindParam(1,$this->idtable);
            $req->bindParam(2,$this->datereservation);
            $req->bindParam(3,$this->datereservee);
            $req->bindParam(4,$this->nomcli);
            $req->bindParam(5,$this->status);
            $req->bindParam(6,$this->idreserv);
            return $req->execute();
        }

        //methode pour supprimer une reservation
        public function deleteReservation()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("DELETE FROM reserver WHERE IDRESERVATION=?");
            $req->bindParam(1,$this->idreserv);
            return $req->execute();
        }

        //methode pour lister les reservations
        public function listReservations()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("SELECT * FROM reserver ORDER BY IDRESERVATION DESC");
            $req->execute();
            return $req->fetchAll();
        }
        
        // Méthode pour obtenir le dernier ID de réservation
        public function getLastReservationId()
        {
            try {
                $con = $this->getConnexion();
                $req = $con->prepare("SELECT MAX(CAST(SUBSTRING(IDRESERVATION, 2) AS UNSIGNED)) as lastId FROM reserver");
                $req->execute();
                $result = $req->fetch(PDO::FETCH_ASSOC);
                
                if ($result['lastId'] === null) {
                    return 'R0001'; // Premier ID formaté avec des zéros
                } else {
                    // Format avec 4 chiffres (par exemple: R0001, R0002, etc.)
                    return 'R' . str_pad(($result['lastId'] + 1), 4, '0', STR_PAD_LEFT);
                }
            } catch(PDOException $e) {
                error_log("Erreur lors de la récupération du dernier ID : " . $e->getMessage());
                return 'R0001';
            }
        }

        // Méthode pour récupérer les réservations actives
        public function getActiveReservations()
        {
            try {
                $con = $this->getConnexion();
                $sql = "SELECT r.IDRESERVATION, r.DATERESERVATION, r.NOMCLI, 
                        r.DATERESERVE, r.NUMTABLE, r.STATUT 
                        FROM RESERVER r 
                        ORDER BY r.IDRESERVATION ASC";
                $stmt = $con->prepare($sql);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                error_log("Erreur lors de la récupération des réservations : " . $e->getMessage());
                return [];
            }
        }

        public function getReservationById($idreserv)
        {
            try {
                $bdd = $this->getConnexion();
                $query = "SELECT * FROM reserver WHERE IDRESERVATION = :idreserv";
                $stmt = $bdd->prepare($query);
                $stmt->bindParam(':idreserv', $idreserv, PDO::PARAM_STR);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    // Hydrate l'objet courant avec les données
                    $this->idreserv = $result['IDRESERVATION'];
                    $this->idtable = $result['NUMTABLE'];
                    $this->datereservation = $result['DATERESERVATION'];
                    $this->datereservee = $result['DATERESERVE'];
                    $this->nomcli = $result['NOMCLI'];
                    $this->status = $result['STATUT'];
                    return $this;
                }
                return null;
            } catch(PDOException $e) {
                error_log("Erreur dans getReservationById : " . $e->getMessage());
                return null;
            }
        }

        public function getActiveReservationsWithPagination($limit, $offset) {
            try {
                $con = $this->getConnexion();
                $sql = "SELECT * FROM reserver ORDER BY IDRESERVATION ASC LIMIT :limit OFFSET :offset";
                $stmt = $con->prepare($sql);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                return false;
            }
        }

        public function countReservations() {
            try {
                $con = $this->getConnexion();
                $sql = "SELECT COUNT(*) as total FROM reserver";
                $stmt = $con->query($sql);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return (int)$result['total'];
            } catch(PDOException $e) {
                return 0;
            }
        }
    }

?>