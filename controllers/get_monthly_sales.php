<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../models/Connexion.php';

header('Content-Type: application/json');

try {
    $conn = Connexion::getConnexion();
    
    // Vérifier la connexion
    if (!$conn) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    $monthlyData = $conn->query("
        SELECT 
            MONTH(c.DATECOM) as mois,
            SUM(cd.QUANTITE * m.PU) as total
        FROM commande c
        JOIN detail_commande cd ON c.IDCOM = cd.IDCOM
        JOIN menu m ON cd.IDPLAT = m.IDPLAT
        WHERE YEAR(c.DATECOM) = YEAR(CURRENT_DATE)
        GROUP BY MONTH(c.DATECOM)
        ORDER BY mois
    ");
    
    if (!$monthlyData) {
        throw new Exception('Erreur lors de l\'exécution de la requête');
    }
    
    $data = $monthlyData->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'file' => basename(__FILE__),
        'line' => $e->getLine()
    ]);
}
