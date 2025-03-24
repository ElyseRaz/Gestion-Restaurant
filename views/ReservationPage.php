<?php
    require_once '../models/Reserver.php';
    require_once '../models/Tables.php';
    require_once '../auth_check.php';
    
    // Initialisation des objets
    $reserver = new Reserver();
    $tables = new Tables();

    // Configuration de la pagination
    $limit = 10; // Nombre de réservations par page
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $limit;
    
    // Récupération du nombre total de réservations et calcul des pages
    $totalReservations = $reserver->countReservations();
    $totalPages = max(1, ceil($totalReservations / $limit));
    
    // Vérification de la validité de la page
    if ($page > $totalPages) {
        header('Location: ?page=1');
        exit;
    }
    
    // Récupération des réservations pour la page courante
    $reservations = $reserver->getActiveReservationsWithPagination($limit, $offset);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale="1.0">
    <link href="css/styles/bootstrap5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Reservation Page</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php require_once 'header.php';?>
<!--Tableau qui affiche les réservations avec un statut expiré ou en cours-->
    <section class="mx-5">
        <h1 class="text-secondary mb-3">Liste des réservations</h1>
        <div class="d-flex justify-content-end align-items-center gap-3 mb-3">
            <div class="input-group" style="width: 300px;">
                <input type="text" class="form-control" id="search" name="search" placeholder="Recherche des Clients">
                <button class="btn btn-success" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </button>
            </div>
            <div>
                <a href="AddReservation.php" class="btn btn-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-plus-lg" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                    </svg>
                    Ajouter une réservation
                </a>
            </div>
        </div>
        <table class="table table-striped table-responsive text-center">
            <thead>
                <tr class="table-dark">
                    <th>Numéro de réservation</th>
                    <th>Date de réservation</th>
                    <th>Nom du client</th>
                    <th>Date et Heure Réservé</th>
                    <th>Numéro de table</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($reservations && is_array($reservations)) {
                    foreach ($reservations as $reservation) {
                        // Définir la classe CSS en fonction du statut
                        $statusClass = '';
                        switch($reservation['STATUT']) {
                            case 'En cours':
                                $statusClass = 'text-success';
                                break;
                            case 'À venir':
                                $statusClass = 'text-primary';
                                break;
                            case 'Expirée':
                                $statusClass = 'text-danger';
                                break;
                        }
                        
                        echo "<tr>
                            <td>".htmlspecialchars($reservation['IDRESERVATION'])."</td>";
                        $dateReservation = !empty($reservation['DATERESERVATION']) ? 
                            date('d-m-Y H:i', strtotime($reservation['DATERESERVATION'])) : 'Non définie';
                        $dateReservee = !empty($reservation['DATERESERVE']) ? 
                            date('d-m-Y H:i', strtotime($reservation['DATERESERVE'])) : 'Non définie';

                        echo "<td>".htmlspecialchars($dateReservation)."</td>";
                        echo "<td>".htmlspecialchars($reservation['NOMCLI'])."</td>";
                        echo "<td>".htmlspecialchars($dateReservee)."</td>";
                        echo "<td>".htmlspecialchars($reservation['NUMTABLE'])."</td>
                            <td class='".$statusClass."'>".htmlspecialchars($reservation['STATUT'])."</td>
                            <td>
                                <a href='EditReservation.php?id=".htmlspecialchars($reservation['IDRESERVATION'])."' class='btn btn-primary btn-sm me-2'>
                                    <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-pencil-fill\" viewBox=\"0 0 16 16\">
                                        <path d=\"M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z\"/>
                                    </svg>
                                    Modifier
                                </a>
                                <a href='#' onclick='confirmDelete(".json_encode($reservation['IDRESERVATION']).")' class='btn btn-danger btn-sm'>
                                    <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-trash\" viewBox=\"0 0 16 16\">
                                        <path d=\"M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z\"/>
                                        <path d=\"M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z\"/>
                                    </svg>
                                    Supprimer
                                </a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>Aucune réservation trouvée</td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        <!-- Ajout de la pagination -->
        <nav aria-label="Navigation des pages">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Précédent</a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Suivant</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </section>
    <?php require_once 'footer.php';?>
    <script src="/views/css/styles/bootstrap5.3.2/js/bootstrap.min.js"></script>
    <script>
        // Fonction de recherche dynamique
        document.getElementById('search').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('table tbody tr');
            
            tableRows.forEach(row => {
                const clientName = row.children[2].textContent.toLowerCase(); // Index 2 correspond à la colonne du nom du client
                if (clientName.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        function confirmDelete(id) {
            if(confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')) {
                window.location.href = 'DeleteReservation.php?id=' + encodeURIComponent(id);
            }
        }
         // Fonction pour mettre à jour la liste des tables
         function updateTableList(tables) {
            const select = document.getElementById('idtable');
            const currentValue = select.value;
            
            select.innerHTML = '<option value="">Sélectionnez une table</option>';
            
            tables.forEach(table => {
                const option = document.createElement('option');
                option.value = table.NUMTABLE;
                const status = table.OCCUPATION == 1 ? ' (Occupée)' : ' (Libre)';
                option.textContent = table.DESIGNATION + status;
                if (table.OCCUPATION == 1) {
                    option.disabled = true;
                }
                select.appendChild(option);
            });
            
            if (currentValue) select.value = currentValue;
        }

        // Fonction pour mettre à jour automatiquement le statut des tables
        function autoUpdateTableStatus() {
            fetch('../ajax/updateTableStatus.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.tables) {
                        updateTableList(data.tables);
                    }
                })
                .catch(error => console.error('Erreur:', error));
        }

        // Vérifier toutes les minutes
        setInterval(autoUpdateTableStatus, 60000);
        
        // Vérifier au chargement de la page
        autoUpdateTableStatus();
    </script>
</body>
</html>