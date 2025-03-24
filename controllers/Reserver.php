<?php 
    require_once '../models/Reserver.php';
    require_once '../controllers/View.php';
    define('URL', 'localhost/php');

    class Reservation  {
        private $views;

        public function __construct() {
            $this->views = new Views();
        }

        public function index($idreservation) {
            $reservations = new Reserver();
            $data = $reservations->getReservationById($idreservation);
            $this->views->render('reservation/index', $data);
        }

        public function add() {
            $this->views->render('reservation/add');
        }

        public function store() {
            $reservations = new Reserver();
            $reservations->addReservation($_POST);
            header('location: ' . URL . 'reservation');
        }

        public function edit($id) {
            $reservations = new Reserver();
            $data = $reservations->getReservationById($id);
            $this->views->render('reservation/edit', $data);
        }

        public function update() {
            $reservations = new Reserver();
            $reservations->updateReservation($_POST);
            header('location: ' . URL . 'reservation');
        }

        public function delete($id) {
            $reservations = new Reserver();
            $reservations->deleteReservation($id);
            header('location: ' . URL . 'reservation');
        }
    }
?>