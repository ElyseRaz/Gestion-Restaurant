<?php 
    require_once '../auth_check.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/styles/bootstrap5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Menu Page</title>
</head>
<body>
    <?php require_once 'header.php';?>
    <?php 
    require_once '../models/Menus.php';
    $menus = new Menus();
    $searchTerm = '';
    $data = null;

    // Pagination
    $limit = 8; // Nombre de résultats par page
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    $totalMenus = $menus->countMenus();
    $totalPages = ceil($totalMenus / $limit);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
        $searchTerm = $_POST['search'];
        $menus->setNomplat($searchTerm);
        $data = $menus->searchMenu($searchTerm); // Résultats de la recherche
    } else {
        $data = $menus->listMenus($limit, $offset); // Liste paginée
    }
    ?>
    <section class="mx-5">
        <h1 class="text-secondary mb-3">Liste des Menu</h1>
        <div class="d-flex justify-content-end align-items-center gap-3 mb-3">
            <div class="input-group" style="width: 300px;">
                <input type="text" class="form-control" id="search" name="search" placeholder="Recherche des Menus" value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>">
                <button class="btn btn-success" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </button>
            </div>
            <div>
                <a href="AddMenu.php" class="btn btn-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-plus-lg" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                    </svg>
                    Ajouter un Menu
                </a>
            </div>
        </div>
        <table class="table table-striped table-hover table-responsive text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nom</th>
                    <th scope="col">Prix</th>
                    <th scope="col">Image</th>
                    <th scope="col">Action</th>
                </tr>   
            </thead>
            <tbody id="menuTableBody">
                <?php
                if (!empty($data)) { // Vérifie si $data contient des résultats
                    foreach ($data as $menu) { // Parcourt les résultats si $data est un tableau
                        $idplat = isset($menu['IDPLAT']) ? $menu['IDPLAT'] : '';
                        $nomplat = isset($menu['NOMPLAT']) ? $menu['NOMPLAT'] : '';
                        $pu = isset($menu['PU']) ? $menu['PU'] : '';
                        $image = isset($menu['IMAGE']) ? $menu['IMAGE'] : '';
                        $imageSrc = '';
                        if (!empty($menu['IMAGE'])) { // Vérifie si l'image n'est pas NULL ou vide
                            $imageData = base64_encode($menu['IMAGE']); // Convertit les données binaires en base64
                            $imageSrc = 'data:image/jpeg;base64,' . $imageData; // Prépare la source de l'image
                        } else {
                            $imageSrc = 'path/to/default-image.jpg'; // Chemin vers une image par défaut
                        }
                ?>
                    <tr>
                        <td><?php echo $idplat; ?></td>
                        <td><?php echo $nomplat; ?></td>
                        <td><?php echo $pu; ?> Ariary</td>
                        <td><img src="<?php echo $imageSrc; ?>" alt="image" width="50" height="50" class="rounded-circle"></td>
                        <td>
                            <a href="EditMenu.php?idplat=<?php echo $idplat; ?>" class="btn btn-primary btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
  <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/>
</svg> Modifier</a>
                            <a href="#" onclick="confirmDelete(<?php echo $idplat; ?>)" class="btn btn-danger btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
    </svg> Supprimer</a>
                        </td>
                    </tr>
                <?php
                    }
                }
                ?>
            </tbody>
        </table>
        <nav>
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
    <script src="/views/css/styles/bootstrap5.3.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('search').addEventListener('keyup', function() {
            const searchTerm = this.value;
            
            fetch(`searchMenu.php?search=${encodeURIComponent(searchTerm)}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('menuTableBody').innerHTML = data;
                })
                .catch(error => console.error('Error:', error));
        });

        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
        });

        function confirmDelete(idplat) {
            if (confirm("Êtes-vous sûr de vouloir supprimer ce menu ?")) {
                window.location.href = "DeleteMenu.php?idplat=" + idplat;
            }
        }
    </script>
     <?php require_once 'footer.php';?>
</body>
</html>