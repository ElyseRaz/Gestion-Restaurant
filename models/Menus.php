<?php
    require_once 'Connexion.php';

    class Menus extends Connexion
    {
        private $idplat;
        private $nomplat;
        private $pu;
        private $image;

        public function __construct()
        {
            $this->idplat = "";
            $this->nomplat = "";
            $this->pu = 0;
            $this->image = "";

        }

        //getters
        public function getIdplat()
        {
            return $this->idplat;
        }

        public function getNomplat()
        {
            return $this->nomplat;
        }

        public function getPu()
        {
            return $this->pu;
        }

        public function getImage()
        {
            return $this->image;
        }

        //setters

        public function setIdplat($idplat)
        {
            $this->idplat = $idplat;
        }

        public function setNomplat($nomplat)
        {
            $this->nomplat = $nomplat;
        }

        public function setPu($pu)
        {
            $this->pu = $pu;
        }

        public function setImage($image)
        {
            $this->image = $image;
        }

        //methode pour ajouter un menu

        public function addMenu()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("INSERT INTO menu(nomplat, pu, image) VALUES(?, ?, ?)");
            $req->bindParam(1, $this->nomplat);
            $req->bindParam(2, $this->pu);
            $req->bindParam(3, $this->image);
            return $req->execute();
            
        }

        //methode pour modifier un menu

        public function updateMenu()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("UPDATE menu SET nomplat = ?, pu = ?, image = ? WHERE idplat = ?");
            $req->execute(array($this->nomplat, $this->pu, $this->image, $this->idplat));
        }   

        //methode pour supprimer un menu
        public function deleteMenu()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("DELETE FROM menu WHERE idplat=?");
            $req->execute(array($this->idplat));
        }

        //methode pour lister les menus
        public function listMenus($limit = 10, $offset = 0)
        {
            $con = $this->getConnexion();
            $req = $con->prepare("SELECT * FROM menu LIMIT :limit OFFSET :offset");
            $req->bindValue(':limit', $limit, PDO::PARAM_INT);
            $req->bindValue(':offset', $offset, PDO::PARAM_INT);
            $req->execute();
            $menus = $req->fetchAll(PDO::FETCH_ASSOC);

            // Débogage temporaire
            // var_dump($menus); exit;

            return $menus;
        }

        public function listMenu() {
            $bdd = $this->getConnexion();
            $query = "SELECT IDPLAT, NOMPLAT, PU as PRIX FROM menu";
            $stmt = $bdd->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function countMenus()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("SELECT COUNT(*) as total FROM menu");
            $req->execute();
            return $req->fetch()['total'];
        }

        //methode pour rechercher un menu en utilisant %LIKE%
        public function searchMenu($searchTerm) {
            $query = "SELECT * FROM menu WHERE NOMPLAT LIKE :searchTerm"; // Correction du nom de la table
            $con = $this->getConnexion();
            $stmt = $con->prepare($query);
            $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
            $stmt->execute();
            return $stmt; // Retourne un objet PDOStatement
        }

        //methode pour lister les 10 plats le plus vendus
        public function bestSelling()
        {
            $con = $this->getConnexion();
            $req = $con->prepare("SELECT menu.nomplat,SUM(quantite) as quantite FROM menu,commander WHERE menu.idplat=commander.idplat GROUP BY menu.nomplat ORDER BY quantite DESC LIMIT 10");
            $req->execute();
            return $req->fetchAll();
        }

        public function getMenuById($idPlat) {
            // Assuming you have a database connection $db
            $db = $this->getConnexion();
    
            $query = $db->prepare("SELECT * FROM menu WHERE IDPLAT = :idPlat");
            $query->bindParam(':idPlat', $idPlat, PDO::PARAM_INT);
            $query->execute();
    
            return $query->fetch(PDO::FETCH_ASSOC);
        }
    }


?>