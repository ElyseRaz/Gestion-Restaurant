<?php 
    require_once '../models/Commande.php';
    require_once '../controllers/View.php';
    define('URL', 'localhost/php');

    class Commande {
        private $views;

        public function __construct() {
            $this->views = new Views();
        }

        public function index($idcommande) {
            $commandes = new Commandes();
            $data = $commandes->getCommande($idcommande);
            $this->views->render('commande/index', $data);
        }

        public function add() {
            $this->views->render('commande/add');
        }

        public function store() {
            $commandes = new Commandes();
            $commandes->addCommande($_POST);
            header('location: ' . URL . 'commande');
        }

        public function edit($id) {
            $commandes = new Commandes();
            $data = $commandes->getCommande($id);
            $this->views->render('commande/edit', $data);
        }

        public function update() {
            $commandes = new Commandes();
            $commandes->updateCommande($_POST);
            header('location: ' . URL . 'commande');
        }

        public function delete($id) {
            $commandes = new Commandes();
            $commandes->deleteCommande($id);
            header('location: ' . URL . 'commande');
        }
    }
?>