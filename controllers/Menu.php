<?php 
    require_once '../models/Menus.php';
    require_once '../controllers/View.php'; // Include the Views class
    define('URL', 'localhost/php'); // Define the URL constant

    class Menu  {
        private $views;

        public function __construct() {
            $this->views = new Views();
        }

        public function index($idplat) {
            $menus = new Menus();
            $data = $menus->getIdplat($idplat);
            $this->views->render('menu/index', $data);
        }

        public function add() {
            $this->views->render('menu/add');
        }

        public function store() {
            $menus = new Menus();
            $menus->addMenu($_POST);
            header('location: ' . URL . 'menu');
        }

        public function edit($id) {
            $menus = new Menus();
            $data = $menus->getMenuById($id);
            $this->views->render('menu/edit', $data);
        }

        public function update() {
            $menus = new Menus();
            $menus->updateMenu($_POST);
            header('location: ' . URL . 'menu');
        }

        public function delete($id) {
            $menus = new Menus();
            $menus->deleteMenu($id);
            header('location: ' . URL . 'menu');
        }
    }

?>