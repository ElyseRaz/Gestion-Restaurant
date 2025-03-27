<?php
require_once __DIR__ . '/models/Commandes.php';

header('Content-Type: application/json');

try {
    $commandeInstance = new Commandes();
    $dateType = $_GET['date_type'] ?? '';
    $result = [];

    if ($dateType === 'single' && !empty($_GET['specific_date'])) {
        $date = $_GET['specific_date'];
        $result = $commandeInstance->searchByDate($date);
    } elseif ($dateType === 'range' && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $startDate = $_GET['start_date'];
        $endDate = $_GET['end_date'];
        $result = $commandeInstance->searchByDateRange($startDate, $endDate);
    }

    if (empty($result)) {
        echo json_encode([]);
    } else {
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log("Erreur dans search_date.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la recherche']);
}
?>
