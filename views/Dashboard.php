<?php 
    //inclure tous les fichiers du models
    require_once '../models/Commandes.php';
    require_once '../models/Commandedetail.php';
    require_once '../models/Menus.php';
    require_once '../models/Tables.php'; // Ajout de la classe Tables
    require_once '../models/Reserver.php';
    require_once '../models/Connexion.php';
    require_once '../auth_check.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Dashboard</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .stats-container {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin-bottom: 30px;
            padding: 0 20px;
        }
        .main-container {
            display: flex;
            gap: 20px;
            margin: 20px 30px; /* Ajustement des marges externes */
        }
        .left-section {
            flex: 1;
            max-width: 60%;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .right-section {
            flex: 1;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .top-menu-table {
            width: 100%;
            border-collapse: collapse;
        }
        .top-menu-table th, .top-menu-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .top-menu-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .stat-card {
            position: relative;
            cursor: pointer;
            text-decoration: none;
            background: #0d6efd;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            text-align: center;
            width: 250px; /* Largeur fixe */
            height: 120px; /* Hauteur fixe */
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex: 1; /* Distribution égale de l'espace */
            max-width: 300px; /* Largeur maximale */
            min-width: 200px; /* Largeur minimale */
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.3);
        }
        .stat-card:nth-child(1) { background: #0d6efd; }
        .stat-card:nth-child(2) { background: #dc3545; }
        .stat-card:nth-child(3) { background: #198754; }
        .stat-card:nth-child(4) { background: #6610f2; }

        /* Suppression des styles inutiles */
        .stat-label, .nav-arrow {
            display: none;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: white;
            margin: 5px 0;
        }
        /* Classe spécifique pour le chiffre d'affaires */
        .stat-card:nth-child(4) .stat-number {
            font-size: 1.8em; /* Taille réduite pour mieux s'adapter */
        }
        .stat-title{
            color: rgba(255,255,255,0.8);
            font-size: 0.9em;
            margin: 0;
        }
        /* Nouveau style pour le thead */
        .blue-header {
            background-color: #0d6efd;
        }
        .blue-header th {
            color: white;
            font-weight: bold;
        }
        .card-header {
            margin-bottom: 20px;
        }
        .table-responsive {
            margin-top: 15px;
        }
        .table {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <?php 
        require_once 'header.php';
        
        // Obtenir les statistiques
        $conn = Connexion::getConnexion();
        
        $nbReservations = $conn->query("SELECT COUNT(*) as total FROM reserver")->fetch()['total'];
        $nbCommandes = $conn->query("SELECT COUNT(*) as total FROM commande")->fetch()['total'];
        $nbTables = $conn->query("SELECT COUNT(*) as total FROM tables")->fetch()['total'];
        
        // Calculer le chiffre d'affaires total en utilisant commandedetail
        $totalRevenue = $conn->query("
            SELECT SUM(cd.QUANTITE * m.PU) as total 
            FROM detail_commande cd 
            JOIN menu m ON cd.IDPLAT = m.IDPLAT
        ")->fetch()['total'];

        // Requête pour le top 10 des menus
        $topMenus = $conn->query("
            SELECT m.NOMPLAT, m.PU, SUM(cd.QUANTITE) as total_vendus
            FROM detail_commande cd 
            JOIN menu m ON cd.IDPLAT = m.IDPLAT
            GROUP BY m.IDPLAT, m.NOMPLAT, m.PU
            ORDER BY total_vendus DESC
            LIMIT 10
        ")->fetchAll();

        // Ajouter après les autres requêtes SQL existantes
        $monthlyData = $conn->query("
            SELECT 
                MONTH(c.DATECOM) as mois,
                SUM(cd.QUANTITE * m.PU) as total
            FROM commande c
            JOIN detail_commande cd ON c.IDCOM = cd.IDCOM
            JOIN menu m ON cd.IDPLAT = m.IDPLAT
            WHERE c.DATECOM >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
            GROUP BY MONTH(c.DATECOM)
            ORDER BY c.DATECOM DESC
            LIMIT 6
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Inverser le tableau pour avoir l'ordre chronologique
        $monthlyData = array_reverse($monthlyData);
    ?>
    
    <div class="stats-container">
        <a href="ReservationPage.php" class="stat-card">
            <div class="stat-title">Nombre de Réservations</div>
            <div class="stat-number"><?php echo $nbReservations; ?></div>
        </a>
        <a href="CommandePage.php" class="stat-card">
            <div class="stat-title">Nombre de Commandes</div>
            <div class="stat-number"><?php echo $nbCommandes; ?></div>
        </a>
        <a href="TablePage.php" class="stat-card">
            <div class="stat-title">Nombre de Tables</div>
            <div class="stat-number"><?php echo $nbTables; ?></div>
        </a>
        <a href="Dashboard.php" class="stat-card">
            <div class="stat-title">Chiffre d'Affaires Total</div>
            <div class="stat-number"><?php echo number_format($totalRevenue,0); ?> Ariary</div>
        </a>
    </div>

    <div class="main-container">
        <div class="left-section">
            <div id="error-message" style="color: red; text-align: center;"></div>
            <canvas id="myChart"></canvas>
        </div>
        <div class="right-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-center text-primary">Top 10 des Menus les Plus Vendus</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>Rang</th>
                                    <th>Menu</th>
                                    <th>Prix Unitaire</th>
                                    <th>Quantité Vendue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($topMenus as $menu): ?>
                                <tr>
                                    <td><?php echo array_search($menu, $topMenus) + 1; ?></td>
                                    <td><?php echo $menu['NOMPLAT']; ?></td>
                                    <td><?php echo number_format($menu['PU'], 0); ?> Ar</td>
                                    <td><?php echo $menu['total_vendus']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once 'footer.php';?>
    <script>
        const monthlyData = <?php echo json_encode($monthlyData); ?>;
    </script>
    <script src="./js/chart.js"></script>
</body>
</html>