<?php 
    require_once '../auth_check.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/styles/bootstrap5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Page de Commande</title>
</head>
<body>
    <?php require_once 'header.php'; ?>
    <?php
    require_once '../models/Commandes.php';
    $commandeInstance = new Commandes();
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Pagination
    $limit = 10;
    $page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
    $offset = ($page - 1) * $limit;
    
    // Ajout de la gestion des dates dans la partie PHP
    $dateType = isset($_GET['date_type']) ? $_GET['date_type'] : '';
    
    if ($dateType === 'single' && !empty($_GET['specific_date'])) {
        $data = $commandeInstance->searchByDate($_GET['specific_date']);
        $totalCommandes = count($data);
    } elseif ($dateType === 'range' && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $data = $commandeInstance->searchByDateRange($_GET['start_date'], $_GET['end_date']);
        $totalCommandes = count($data);
    } elseif (!empty($searchTerm)) {
        $totalCommandes = $commandeInstance->countSearchResults($searchTerm);
        $data = $commandeInstance->searchClients($searchTerm, $limit, $offset);
    } else {
        $totalCommandes = $commandeInstance->countCommandes();
        $data = $commandeInstance->listCommandes($limit, $offset);
    }
    
    $totalPages = max(1, ceil($totalCommandes / $limit));
    
    // Vérification que la page demandée existe
    if ($page > $totalPages) {
        header('Location: ?page=1');
        exit;
    }
    ?>
    <section class="mx-5">
        <h1 class="text-secondary mb-3">Liste des Commandes</h1>
        <div class="d-flex justify-content-end align-items-center gap-3 mb-3">
            <!-- Date de recherche -->
            <div class="d-flex gap-4 align-items-center">
                <div class="d-flex align-items-center gap-2 flex-nowrap">
                    <div class="form-check d-flex align-items-center mb-0">
                        <input type="checkbox" id="single_date" name="search_type" value="single" class="form-check-input me-2">
                        <label for="single_date" class="form-check-label text-nowrap">Date précise :</label>
                    </div>
                    <input type="date" name="specific_date" class="form-control form-control-sm" id="specific_date">
                </div>

                <div class="d-flex align-items-center gap-2 flex-nowrap">
                    <div class="form-check d-flex align-items-center mb-0">
                        <input type="checkbox" id="date_range" name="search_type" value="range" class="form-check-input me-2">
                        <label for="date_range" class="form-check-label text-nowrap">Plage de dates :</label>
                    </div>
                    <div class="d-flex gap-2 align-items-center flex-nowrap">
                        <label for="start_date" class="form-label mb-0 text-nowrap">Du :</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" id="start_date">
                        <label for="end_date" class="form-label mb-0 text-nowrap">Au :</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" id="end_date">
                    </div>
                </div>
            </div>

            <!-- Recherche existante -->
            <div class="input-group" style="width: 300px;">
                <input type="text" class="form-control" id="search" name="search" placeholder="Recherche des Clients">
                <button class="btn btn-success" type="button">
                    <svg xmlns="http://www.w2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </button>
            </div>

            <!-- Bouton Ajouter -->
            <div>
                <a href="AddCommand.php" class="btn btn-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-plus-lg" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                    </svg>
                    Ajouter une Commande
                </a>
            </div>
        </div>
        <table class="table table-striped table-hover table-responsive text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th scope="col">ID Commande</th>
                    <th scope="col">Nom Client</th>
                    <th scope="col">Date Commande</th>
                    <th scope="col">Type Commande</th>
                    <th scope="col">ID Table</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody id="commandesTableBody">
                <?php
                if (!empty($data)) {
                    foreach ($data as $commande) {
                        $idcom = isset($commande['IDCOM']) ? $commande['IDCOM'] : 'N/A';
                        $nomcli = isset($commande['NOMCLI']) ? $commande['NOMCLI'] : 'N/A';
                        $datecom = isset($commande['DATECOM']) ? date('d-m-Y', strtotime($commande['DATECOM'])) : 'N/A';
                        $typecom = isset($commande['TYPECOM']) ? $commande['TYPECOM'] : 'N/A';
                        $idtable = isset($commande['IDTABLE']) ? $commande['IDTABLE'] : null ;
                ?>
                    <tr>
                        <td><?php echo $idcom; ?></td>
                        <td><?php echo $nomcli; ?></td>
                        <td><?php echo $datecom; ?></td>
                        <td><?php echo $typecom; ?></td>
                        <td><?php echo $idtable; ?></td>
                        <td>
                            <a href="EditCommand.php?idcom=<?php echo $idcom; ?>" class="btn btn-primary btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
  <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/>
</svg> Modifier</a>
                            <a href="DeleteCommand.php?idcom=<?php echo $idcom; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ?');"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
    </svg> Supprimer</a>
                            <a href="GenerateReceipt.php?idcom=<?php echo $idcom; ?>" class="btn btn-success btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf" viewBox="0 0 16 16">
  <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
  <path d="M4.603 14.087a.8.8 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.7 7.7 0 0 1 1.482-.645a20 20 0 0 0 1.062-2.227a7.3 7.3 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a11 11 0 0 0 .98 1.686a5.8 5.8 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.86.86 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.7 5.7 0 0 1-.911-.95a11.7 11.7 0 0 0-1.997.406a11.3 11.3 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.8.8 0 0 1-.58.029m1.379-1.901q-.25.115-.459.238c-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361q.016.032.026.044l.035-.012c.137-.056.355-.235.635-.572a8 8 0 0 0 .45-.606m1.64-1.33a13 13 0 0 1 1.01-.193 12 12 0 0 1-.51-.858 21 21 0 0 1-.5 1.05zm2.446.45q.226.245.435.41c.24.19.407.253.498.256a.1.1 0 0 0 .07-.015.3.3 0 0 0 .094-.125.44.44 0 0 0 .059-.2.1.1 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a4 4 0 0 0-.612-.053zM8.078 7.8a7 7 0 0 0 .2-.828q.046-.282.038-.465a.6.6 0 0 0-.032-.198.5.5 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822q.036.167.09.346z"/>
</svg> Voir le reçu</a>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>Aucune commande disponible.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <nav id="pagination">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $searchTerm ? '&search='.urlencode($searchTerm) : ''; ?>">Précédent</a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $searchTerm ? '&search='.urlencode($searchTerm) : ''; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $searchTerm ? '&search='.urlencode($searchTerm) : ''; ?>">Suivant</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </section>
    <?php require_once 'footer.php'; ?>
    <script src="/views/css/styles/bootstrap5.3.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('search').addEventListener('keyup', function() {
            const searchTerm = this.value;
            
            fetch(`../search_commandes.php?term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('commandesTableBody');
                    
                    if (data && data.length > 0) {
                        let html = '';
                        data.forEach(commande => {
                            const datecom = new Date(commande.DATECOM).toLocaleDateString('fr-FR');
                            html += `
                                <tr>
                                    <td>${commande.IDCOM || 'N/A'}</td>
                                    <td>${commande.NOMCLI || 'N/A'}</td>
                                    <td>${datecom}</td>
                                    <td>${commande.TYPECOM || 'N/A'}</td>
                                    <td>${commande.IDTABLE || ''}</td>
                                    <td>
                                        <a href="EditCommand.php?idcom=${commande.IDCOM}" class="btn btn-primary btn-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                                                <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/>
                                            </svg> Modifier
                                        </a>
                                        <a href="DeleteCommand.php?idcom=${commande.IDCOM}" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ?');">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                            </svg> Supprimer
                                        </a>
                                        <a href="GenerateReceipt.php?idcom=${commande.IDCOM}" class="btn btn-success btn-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf" viewBox="0 0 16 16">
                                                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                                                <path d="M4.603 14.087a.8.8 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.7 7.7 0 0 1 1.482-.645a20 20 0 0 0 1.062-2.227a7.3 7.3 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a11 11 0 0 0 .98 1.686a5.8 5.8 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.86.86 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.7 5.7 0 0 1-.911-.95a11.7 11.7 0 0 0-1.997.406a11.3 11.3 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.8.8 0 0 1-.58.029z"/>
                                            </svg> Voir le reçu
                                        </a>
                                    </td>
                                </tr>
                            `;
                        });
                        tbody.innerHTML = html;
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Aucune commande trouvée.</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('commandesTableBody').innerHTML = 
                        '<tr><td colspan="6" class="text-center">Erreur lors de la recherche</td></tr>';
                });
        });

        // Gestion des checkbox et inputs de date
        document.addEventListener('DOMContentLoaded', function() {
            const singleDateInput = document.getElementById('single_date_input');
            const dateRangeInputs = document.getElementById('date_range_inputs');
            
            // S'assurer que les inputs sont cachés au chargement
            singleDateInput.style.display = 'none';
            dateRangeInputs.style.display = 'none';

            document.getElementById('single_date').addEventListener('change', function() {
                if (this.checked) {
                    singleDateInput.style.display = 'block';
                    document.getElementById('date_range').checked = false;
                    dateRangeInputs.style.display = 'none';
                } else {
                    singleDateInput.style.display = 'none';
                    window.location.href = window.location.pathname;
                }
            });

            document.getElementById('date_range').addEventListener('change', function() {
                if (this.checked) {
                    dateRangeInputs.style.display = 'flex';
                    document.getElementById('single_date').checked = false;
                    singleDateInput.style.display = 'none';
                } else {
                    dateRangeInputs.style.display = 'none';
                    window.location.href = window.location.pathname;
                }
            });
        });

        // Fonction de recherche par date
        function searchByDate() {
            const singleDate = document.getElementById('specific_date').value;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const searchType = document.querySelector('input[name="search_type"]:checked')?.value;

            if (!searchType) {
                window.location.href = window.location.pathname;
                return;
            }

            let url = '../search_date.php?';
            if (searchType === 'single' && singleDate) {
                url += `date_type=single&specific_date=${singleDate}`;
            } else if (searchType === 'range' && startDate && endDate) {
                url += `date_type=range&start_date=${startDate}&end_date=${endDate}`;
            } else {
                return; // Ne rien faire si les dates ne sont pas remplies
            }

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }
                    return response.json();
                })
                .then(data => {
                    const tbody = document.getElementById('commandesTableBody');
                    if (data && data.length > 0) {
                        let html = '';
                        data.forEach(commande => {
                            const datecom = new Date(commande.DATECOM).toLocaleDateString('fr-FR');
                            html += `
                                <tr>
                                    <td>${commande.IDCOM || 'N/A'}</td>
                                    <td>${commande.NOMCLI || 'N/A'}</td>
                                    <td>${datecom}</td>
                                    <td>${commande.TYPECOM || 'N/A'}</td>
                                    <td>${commande.IDTABLE || ''}</td>
                                    <td>
                                        <a href="EditCommand.php?idcom=${commande.IDCOM}" class="btn btn-primary btn-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                                                <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/>
                                            </svg> Modifier
                                        </a>
                                        <a href="DeleteCommand.php?idcom=${commande.IDCOM}" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ?');">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                            </svg> Supprimer
                                        </a>
                                        <a href="GenerateReceipt.php?idcom=${commande.IDCOM}" class="btn btn-success btn-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf" viewBox="0 0 16 16">
                                                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                                                <path d="M4.603 14.087a.8.8 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.7 7.7 0 0 1 1.482-.645a20 20 0 0 0 1.062-2.227a7.3 7.3 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a11 11 0 0 0 .98 1.686a5.8 5.8 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.86.86 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.7 5.7 0 0 1-.911-.95a11.7 11.7 0 0 0-1.997.406a11.3 11.3 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.8.8 0 0 1-.58.029z"/>
                                            </svg> Voir le reçu
                                        </a>
                                    </td>
                                </tr>
                            `;
                        });
                        tbody.innerHTML = html;
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Aucune commande trouvée pour cette période.</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('commandesTableBody').innerHTML = 
                        '<tr><td colspan="6" class="text-center">Erreur lors de la recherche</td></tr>';
                });
        }

        // Ajout des événements pour la recherche automatique lors du changement de date
        ['specific_date', 'start_date', 'end_date'].forEach(id => {
            document.getElementById(id).addEventListener('change', searchByDate);
        });

        // Simplifier la gestion des checkbox
        document.getElementById('single_date').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('date_range').checked = false;
            }
            searchByDate();
        });

        document.getElementById('date_range').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('single_date').checked = false;
            }
            searchByDate();
        });
    </script>

</body>
</html>