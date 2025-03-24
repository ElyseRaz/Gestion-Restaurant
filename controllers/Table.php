<?php 
    require_once '../models/Tables.php';
    require_once '../controllers/View.php'; // Include the Views class
    define('URL', 'localhost/php'); // Define the URL constant

    class Table  {
        private $views;

        public function __construct() {
            $this->views = new Views();
        }

        public function index($idtable) {
            $tables = new Tables();
            $data = $tables->getTable($idtable);
            $this->views->render('table/index', $data);
        }

        public function add() {
            $this->views->render('table/add');
        }

        public function store() {
            $tables = new Tables();
            $tables->addTable($_POST);
            header('location: ' . URL . 'table');
        }

        public function edit($id) {
            $tables = new Tables();
            $data = $tables->getTable($id);
            $this->views->render('table/edit', $data);
        }

        public function update() {
            $tables = new Tables();
            $tables->updateTable($_POST);
            header('location: ' . URL . 'table');
        }

        public function delete($id) {
            $tables = new Tables();
            $tables->deleteTable($id);
            header('location: ' . URL . 'table');
        }
    }

?>