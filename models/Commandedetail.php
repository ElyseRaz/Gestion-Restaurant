<?php
    require_once 'Connexion.php';   

    class Commandedetail extends Connexion
    {
        private $idcom;
        private $idplat;
        private $qte;
        private $prixTotal;
        private $lastQuery; // Nouvelle propriété pour stocker la dernière requête

        public function __construct()
        {
            $this->idcom = "";
            $this->idplat = "";
            $this->qte = 1;
            $this->prixTotal = 0;
           
        }

        //getters
        public function getIdcom()
        {
            return $this->idcom;
        }

        public function getIdplat()
        {
            return $this->idplat;
        }

        public function getQte()
        {
            return $this->qte;
        }

        public function getPrixTotal()
        {
            return $this->prixTotal;
        }
        public function getError() {
            return $this->error ?? 'Unknown error';
        }

        //setters

        public function setIdcom($idcom)
        {
            $this->idcom = $idcom;
        }

        public function setIdplat($idplat)
        {
            $this->idplat = $idplat;
        }

        public function setQte($qte)
        {
            $this->qte = $qte;
        }                   

        public function setPrixTotal($prixTotal)
        {
            $this->prixTotal = $prixTotal;
        }

        public function addCommandedetail()
        {
            $con = $this->getConnexion();
            $sql = "INSERT INTO detail_commande (IDCOM, IDPLAT, QUANTITE, PRIXTOTAL) VALUES (?, ?, ?, ?)";
            $req = $con->prepare($sql);

            // Vérifiez que les propriétés ne sont pas des tableaux
            if (is_array($this->idcom) || is_array($this->idplat) || is_array($this->qte) || is_array($this->prixTotal)) {
                throw new Exception("Erreur : Les valeurs passées ne doivent pas être des tableaux.");
            }

            $req->bindParam(1, $this->idcom, PDO::PARAM_STR);
            $req->bindParam(2, $this->idplat, PDO::PARAM_STR);
            $req->bindParam(3, $this->qte, PDO::PARAM_INT);
            $req->bindParam(4, $this->prixTotal, PDO::PARAM_STR);
            return $req->execute();
        }

        public function updateCommandeDetail($data) {
            try {
                $con = $this->getConnexion();
                $sql = "UPDATE detail_commande 
                        SET QUANTITE = :quantite 
                        WHERE IDCOM = :idcom 
                        AND IDPLAT = :idplat";
                
                $stmt = $con->prepare($sql);
                $result = $stmt->execute([
                    ':quantite' => $data['QUANTITE'],
                    ':idcom' => $data['IDCOM'],
                    ':idplat' => $data['IDPLAT']
                ]);

                if (!$result) {
                    error_log("Erreur SQL updateCommandeDetail: " . print_r($stmt->errorInfo(), true));
                    return false;
                }
                return true;
            } catch (PDOException $e) {
                error_log("Exception updateCommandeDetail: " . $e->getMessage());
                return false;
            }
        }

        public function deleteCommandedetail()
        {
            $con = $this->getConnexion();
            $sql = "DELETE FROM detail_commande WHERE IDCOM=:idcom ";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':idcom', $this->idcom, PDO::PARAM_STR);
            $stmt->execute();
        }

        public function getCommandedetail()
        {
            try {
                $sql = "SELECT cd.* 
                        FROM detail_commande cd 
                        WHERE cd.IDCOM = :idcom";
                $stmt = $this->getConnexion()->prepare($sql);
                $stmt->execute(['idcom' => $this->idcom]);
                
                // Debug de la requête
                error_log("SQL: " . $sql . " avec IDCOM = " . $this->idcom);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("Résultat requête : " . print_r($result, true));
                
                return $result;
            } catch (PDOException $e) {
                error_log("Erreur dans getCommandedetail: " . $e->getMessage());
                return [];
            }
        }

        public function listCommandedetails()
        {
            $con = $this->getConnexion();
            $sql = "SELECT * FROM detail_commande WHERE IDCOM=:idcom";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':idcom', $this->idcom, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        public function countCommandedetails()
        {
            $con = $this->getConnexion();
            $sql = "SELECT COUNT(*) as total FROM detail_commande WHERE IDCOM=:idcom";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':idcom', $this->idcom, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch()['total'];
        }

        public function getTotal()
        {
            $con = $this->getConnexion();
            $sql = "SELECT SUM(QUANTITE) as total FROM detail_commande WHERE IDCOM=:idcom";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':idcom', $this->idcom, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch()['total'];
        }

        public function getTotalPrice()
        {
            $con = $this->getConnexion();
            $sql = "SELECT SUM(QUANTITE * PU) as total FROM detail_commande JOIN menu ON detail_commande.IDPLAT = menu.IDPLAT WHERE IDCOM=:idcom";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':idcom', $this->idcom, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch()['total'];
        }

        public function getTotalPriceByPlat()
        {
            $con = $this->getConnexion();
            $sql = "SELECT SUM(QUANTITE * PU) as total FROM detail_commande JOIN menu ON detail_commande.IDPLAT = menu.IDPLAT WHERE IDCOM=:idcom AND detail_commande.IDPLAT=:idplat";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':idcom', $this->idcom, PDO::PARAM_STR);
            $stmt->bindParam(':idplat', $this->idplat, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch()['total'];
        }

        public function getDetailsByCommandId($idcom) {
            $db = $this->getConnexion();
            $stmt = $db->prepare("
                SELECT menu.NOMPLAT, detail_commande.QUANTITE AS QTE, menu.PU AS PRIX
                FROM detail_commande
                JOIN menu ON detail_commande.IDPLAT = menu.IDPLAT
                WHERE detail_commande.IDCOM = :idcom
            ");
            $stmt->bindParam(':idcom', $idcom, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Nouvelle méthode pour récupérer la dernière requête
        public function getLastQuery() {
            return $this->lastQuery;
        }

        public function getDetailByPlatAndCommande($idcom, $idplat) {
            $con = $this->getConnexion();
            $sql = "SELECT * FROM detail_commande 
                    WHERE IDCOM = :idcom 
                    AND IDPLAT = :idplat";
            
            $stmt = $con->prepare($sql);
            $stmt->execute([
                ':idcom' => $idcom,
                ':idplat' => $idplat
            ]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function deleteDetail($idcom, $idplat) {
            $con = $this->getConnexion();
            $sql = "DELETE FROM detail_commande 
                    WHERE IDCOM = :idcom 
                    AND IDPLAT = :idplat";
            
            $stmt = $con->prepare($sql);
            return $stmt->execute([
                ':idcom' => $idcom,
                ':idplat' => $idplat
            ]);
        }

        public function getDetailByIds($idcom, $idplat) {
            try {
                $connexion = $this->getConnexion();
                $sql = "SELECT * FROM detail_commande WHERE IDCOM = :idcom AND IDPLAT = :idplat";
                $stmt = $connexion->prepare($sql);
                $stmt->bindParam(':idcom', $idcom);
                $stmt->bindParam(':idplat', $idplat);
                $stmt->execute();
                
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$result) {
                    error_log("Aucun détail trouvé pour IDCOM=$idcom et IDPLAT=$idplat");
                    return false;
                }
                return $result;
            } catch (PDOException $e) {
                error_log("Erreur dans getDetailByIds: " . $e->getMessage());
                return false;
            }
        }
    }
?>