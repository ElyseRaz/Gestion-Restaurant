<?php
    ob_start();
    require_once '../models/Tables.php';
    require_once '../models/Reserver.php';
    require_once '../auth_check.php';
    
    $reserver = new Reserver();
    $nextId = $reserver->getLastReservationId();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idreserv = isset($_POST['idreserv']) ? $_POST['idreserv'] : null;
        $datereserv = isset($_POST['datereserv']) ? $_POST['datereserv'] : null;
        $nomcli = isset($_POST['nomcli']) ? $_POST['nomcli'] : null;
        $datereserve = isset($_POST['datereserve']) ? $_POST['datereserve'] : null;
        $idtable = isset($_POST['idtable']) ? $_POST['idtable'] : null;

        if (!$idreserv || !$datereserv || !$nomcli || !$datereserve || !$idtable) {
            $errorMessage = "Erreur : Tous les champs obligatoires doivent être remplis.";
        } else {
            // Vérifier si la table est déjà réservée à cette heure
            $conn = $reserver->getConnexion();
            $checkQuery = "SELECT COUNT(*) as count 
                         FROM reserver 
                         WHERE NUMTABLE = :idtable 
                         AND DATE(DATERESERVE) = DATE(:datereserve)
                         AND STATUT NOT IN ('Expiré', 'Annulé')
                         AND (
                             :datereserve BETWEEN 
                                 DATE_SUB(DATERESERVE, INTERVAL 15 MINUTE) 
                                 AND DATE_ADD(DATERESERVE, INTERVAL 15 MINUTE)
                             OR 
                             DATERESERVE BETWEEN 
                                 DATE_SUB(:datereserve, INTERVAL 15 MINUTE)
                                 AND DATE_ADD(:datereserve, INTERVAL 15 MINUTE)
                         )";
            
            $stmt = $conn->prepare($checkQuery);
            
            // Debug - Afficher les valeurs avant exécution
            error_log("Table ID: " . $idtable);
            error_log("Date réservée: " . $datereserve);
            
            $stmt->execute([
                'idtable' => $idtable,
                'datereserve' => $datereserve
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Résultat de la vérification: " . var_export($result, true));
            
            if ($result['count'] > 0) {
                $errorMessage = "Cette table est déjà réservée dans cet intervalle horaire (±15 minutes).";
            } else {
                // Continuer avec la réservation
                $reserver->setIdreserv($idreserv);
                $reserver->setDatereservation($datereserv);
                $reserver->setNomcli($nomcli);
                $reserver->setDatereservee($datereserve);
                $reserver->setIdtable($idtable);
                
                if ($reserver->addReservation()) {
                    ob_end_clean(); // Nettoyer le buffer avant la redirection
                    header('Location: ReservationPage.php');
                    exit();
                } else {
                    $errorMessage = "Erreur lors de l'ajout de la réservation.";
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une réservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once 'header.php'; ?>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
            <h1 class="mb-4 text-center text-success">Ajouter une réservation</h1>
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <?php if (isset($errorMessage)): ?>
                            <div class="alert alert-danger">
                                <?php echo $errorMessage; ?>
                            </div>
                        <?php endif; ?>
                        <form action="AddReservation.php" method="POST">
                            <div class="mb-3">
                                <label for="idreserv" class="form-label">Numéro de réservation</label>
                                <input type="text" class="form-control" name="idreserv" id="idreserv" 
                                       value="<?php echo $nextId; ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="datereserv" class="form-label">Date et heure de réservation</label>
                                <input type="datetime-local" class="form-control" name="datereserv" id="datereserv">
                            </div>

                            <div class="mb-3">
                                <label for="nomcli" class="form-label">Nom du client</label>
                                <input type="text" class="form-control" name="nomcli" id="nomcli">
                            </div>

                            <div class="mb-3">
                                <label for="datereserve" class="form-label">Date et heure réservé</label>
                                <input type="datetime-local" class="form-control" name="datereserve" id="datereserve">
                            </div>

                            <div class="mb-3">
                                <label for="idtable" class="form-label">Numéro de table</label>
                                <select class="form-select" name="idtable" id="idtable">
                                    <option value="">Sélectionnez une table</option>
                                    <?php
                                        $table = new Tables();
                                        $tables = $table->getAvailableTablesForDateTime();
                                        foreach ($tables as $t) {
                                            echo "<option value='".$t['NUMTABLE']."'>".$t['DESIGNATION']."</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Statut</label>
                                <select class="form-select" name="status" id="status">
                                    <option value="En cours">En cours</option>
                                    <option value="À venir">À venir</option>
                                    <option value="Expirée">Expirée</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-success">Ajouter</button>
                                <button type="button" onclick="window.location.href='ReservationPage.php'" class="btn btn-danger">Annuler</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once 'footer.php';?>
    <script>
        // Fonction pour mettre à jour la liste des tables
        function updateTableList(tables) {
            const select = document.getElementById('idtable');
            select.innerHTML = '<option value="">Sélectionnez une table</option>';
            
            if (tables && tables.length > 0) {
                tables.forEach(table => {
                    const option = document.createElement('option');
                    option.value = table.NUMTABLE;
                    option.textContent = table.DESIGNATION;
                    select.appendChild(option);
                });
            }
        }

        // Exécuter au chargement de la page
        window.addEventListener('load', function() {
            fetch('../ajax/getAvailableTables.php')
                .then(response => response.json())
                .then(data => updateTableList(data.tables));
        });

        // Supprimer l'événement change sur datereserve car nous voulons toujours afficher toutes les tables
        document.getElementById('datereserve').removeEventListener('change', function() {});
    </script>
</body>
</html>