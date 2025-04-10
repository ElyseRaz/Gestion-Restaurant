<?php
    require_once '../models/Reserver.php';
    require_once '../models/Tables.php';
    require_once '../auth_check.php';
    
    // Initialisation des objets
    $reserver = new Reserver();
    $tables = new Tables();

    // Gestion de la suppression
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        if ($reserver->deleteReservation($id)) {
            header('Location: ReservationPage.php?msg=deleted');
            exit;
        }
    }
    
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                        echo "<td>".htmlspecialchars($reservation['NUMTABLE'])."</td>";
                        echo "<td class='".$statusClass."'>".htmlspecialchars($reservation['STATUT'])."</td>";
                        echo "<td>
                                <a href='EditReservation.php?id=".htmlspecialchars($reservation['IDRESERVATION'])."' class='btn btn-primary btn-sm me-2'>
                                    <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-pencil-fill\" viewBox=\"0 0 16 16\">
                                        <path d=\"M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207z\"/>
                                    </svg>
                                    Modifier
                                </a>
                                <a href='DeleteReservation.php?id=".htmlspecialchars($reservation['IDRESERVATION'])."' class='btn btn-danger btn-sm' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cette réservation ?\");'>
                                    <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-trash\" viewBox=\"0 0 16 16\">
                                        <path d=\"M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z\"/>
                                        <path d=\"M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z\"/>
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
        // Fonction pour charger toutes les réservations avec pagination
        function loadAllReservations(page = 1) {
            fetch(`../ajax/searchReservations.php?page=${page}`)
                .then(response => response.json())
                .then(data => {
                    updateReservationTable(data);
                    updatePagination(data.totalPages, page);
                })
                .catch(error => console.error('Erreur:', error));
        }

        // Fonction pour mettre à jour la pagination
        function updatePagination(totalPages, currentPage) {
            const pagination = document.querySelector('.pagination');
            let paginationHtml = '';

            if (currentPage > 1) {
                paginationHtml += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="loadAllReservations(${currentPage - 1}); return false;">Précédent</a>
                    </li>`;
            }

            for (let i = 1; i <= totalPages; i++) {
                paginationHtml += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="loadAllReservations(${i}); return false;">${i}</a>
                    </li>`;
            }

            if (currentPage < totalPages) {
                paginationHtml += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="loadAllReservations(${currentPage + 1}); return false;">Suivant</a>
                    </li>`;
            }

            pagination.innerHTML = paginationHtml;
        }

        // Fonction pour mettre à jour le tableau des réservations
        function updateReservationTable(data) {
            const tbody = document.querySelector('table tbody');
            tbody.innerHTML = '';
            
            if (data.success && data.reservations.length > 0) {
                data.reservations.forEach(reservation => {
                    let statusClass = '';
                    switch(reservation.STATUT) {
                        case 'En cours': statusClass = 'text-success'; break;
                        case 'À venir': statusClass = 'text-primary'; break;
                        case 'Expirée': statusClass = 'text-danger'; break;
                    }

                    tbody.innerHTML += `
                        <tr>
                            <td>${reservation.IDRESERVATION}</td>
                            <td>${reservation.DATERESERVATION}</td>
                            <td>${reservation.NOMCLI}</td>
                            <td>${reservation.DATERESERVE}</td>
                            <td>${reservation.NUMTABLE}</td>
                            <td class="${statusClass}">${reservation.STATUT}</td>
                            <td>
                                <a href='EditReservation.php?id=${reservation.IDRESERVATION}' class='btn btn-primary btn-sm me-2'>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                                        <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207z"/>
                                    </svg>
                                    Modifier
                                </a>
                                <a href='DeleteReservation.php?id=${reservation.IDRESERVATION}' class='btn btn-danger btn-sm' onclick='return confirm("Êtes-vous sûr de vouloir supprimer cette réservation ?");'>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                    </svg>
                                    Supprimer
                                </a>
                            </td>
                        </tr>`;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">Aucune réservation trouvée</td></tr>';
            }
        }

        // Fonction de recherche dynamique modifiée
        document.getElementById('search').addEventListener('keyup', function() {
            const searchValue = this.value.trim();
            
            if (searchValue.length > 0) {
                fetch(`../ajax/searchReservations.php?search=${encodeURIComponent(searchValue)}`)
                    .then(response => response.json())
                    .then(data => {
                        updateReservationTable(data);
                        // Cacher la pagination pendant la recherche
                        document.querySelector('.pagination').innerHTML = '';
                    })
                    .catch(error => console.error('Erreur:', error));
            } else {
                // Recharger la première page quand la recherche est effacée
                loadAllReservations(1);
            }
        });

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