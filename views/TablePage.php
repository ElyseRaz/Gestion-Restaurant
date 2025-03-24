
<?php     require_once '../auth_check.php';?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Table</title>
    <link href="css/styles/bootstrap5.3.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!--barre de navigation-->
    <?php
    require_once 'header.php'; ?>
   
    <!-- Section principale contenant la liste des tables -->
    <section class="mx-5">
        <h1 class="text-secondary mb-3">Liste des Tables</h1>
        
        <!-- Barre d'outils avec recherche et bouton d'ajout -->
        <div class="d-flex justify-content-end align-items-center gap-3 mb-3">
            <!-- Barre de recherche -->
            <div class="input-group" style="width: 300px;">
                <input type="text" class="form-control" id="search" name="search" placeholder="Recherche des Tables" aria-label="Search Tables" aria-describedby="basic-addon2">
                <button class="btn btn-success" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </button>
            </div>
            <!-- Bouton pour ajouter une nouvelle table -->
            <div>
                <a href="AddTable.php" class="btn btn-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-plus-lg" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                    </svg>
                    Ajouter une table
                </a>
            </div>
        </div>
        
        <!-- Table listant les données -->
        <table class="table table-striped table-responsive text-center">
            <thead>
                <tr class="table-dark">
                    <th class="col">Numéro Table</th>
                    <th class="col-4">Designation</th>
                    <th class="col-4">Occupation</th>
                    <th class="col-3">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    require_once '../models/Tables.php';
                    
                    // Configuration et initialisation de la pagination
                    $limit = 10; // Nombre de tables par page
                    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                    $offset = ($page - 1) * $limit;
                    
                    // Récupération du nombre total de tables pour la pagination
                    $tables = new Tables();
                    $totalTables = $tables->countTables();
                    $totalPages = max(1, ceil($totalTables / $limit));
                    
                    // Redirection si le numéro de page est invalide
                    if ($page > $totalPages) {
                        header('Location: ?page=1');
                        exit;
                    }
                    
                    // Affichage des données avec gestion de l'état d'occupation
                    $data = $tables->listTablesWithPagination($limit, $offset);
                    
                    foreach($data as $table) {
                        // Vérification des clés
                        $idtable = isset($table['NUMTABLE']) ? $table['NUMTABLE'] : '';
                        $designation = isset($table['DESIGNATION']) ? $table['DESIGNATION'] : '';
                        $occupation = isset($table['OCCUPATION']) ? $table['OCCUPATION'] : '';
                ?>
                <tr>
                    <td><?php echo $idtable; ?></td>
                    <td>
                        <?php 
                    
                        echo $designation;
                    ?>
                    </td>
                    <td><?php if($occupation == 0){
                        echo "Libre";
                    }else if($occupation == 1){
                        echo "Occupée";
                    }
                    ?></td>
                    <td>
                        <a href="UpdateTable.php?idtable=<?php echo htmlentities($table['NUMTABLE'])?>" class="btn btn-primary btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
  <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/>
</svg> Modifier</a>
                        <a href="#" onclick="confirmDelete(<?php echo htmlentities($table['NUMTABLE']); ?>)" class="btn btn-danger btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                            </svg> Supprimer
                        </a>
                    </td>
                </tr>
                <?php
                    }
                ?>
            </tbody>
        </table>
        
        <!-- Composant de pagination -->
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

    <!-- Scripts -->
    <script src="/views/css/styles/bootstrap5.3.2/js/bootstrap.min.js"></script>
    <script>
        // Script de recherche dynamique dans le tableau
        document.getElementById('search').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('table tbody tr');
            
            tableRows.forEach(row => {
                const designation = row.children[1].textContent.toLowerCase(); // Index 1 correspond à la colonne de désignation
                if (designation.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Fonction de confirmation pour la suppression
        function confirmDelete(idtable) {
            if (confirm("Êtes-vous sûr de vouloir supprimer cette table ?")) {
                window.location.href = "DeleteTable.php?idtable=" + idtable;
            }
        }
    </script>
     <?php require_once 'footer.php';?>
</body>
</html>