<?php 
require_once '../models/Reserver.php';
require_once '../models/Tables.php';
require_once '../auth_check.php';
require_once '../models/Connexion.php';

// Initialiser l'instance de Reservations
$reservationInstance = new Reserver();
$reserver = null;

// Vérifier si l'ID est passé dans l'URL (soit via GET pour l'affichage initial, soit via POST pour la modification)
$idreservation = isset($_GET['id']) ? $_GET['id'] : (isset($_GET['idreserv']) ? $_GET['idreserv'] : null);

if ($idreservation) {
    // Récupérer les données de la réservation
    $reserver = $reservationInstance->getReservationById($idreservation);
}

// Traitement du formulaire POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idreserv = $idreservation;  // Utiliser l'ID récupéré plus haut
    $datereservation = $_POST['datereserv'];
    $nomcli = $_POST['nomcli'];
    $datereservee = $_POST['datereserve'];
    $idtable = $_POST['idtable'];
    $status = $_POST['status'];

    $reserverCheck = new Reserver(); // Créer une nouvelle instance pour la vérification
    // Vérifier si la table est déjà réservée à cette heure
    $conn = $reserverCheck->getConnexion();
    $checkQuery = "SELECT COUNT(*) as count 
                 FROM reserver 
                 WHERE NUMTABLE = :idtable 
                 AND DATE(DATERESERVE) = DATE(:datereserve)
                 AND IDRESERVATION != :idreserv
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
    $stmt->execute([
        'idtable' => $idtable,
        'datereserve' => $datereservee,
        'idreserv' => $idreserv
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        $errorMessage = "Cette table est déjà réservée dans cet intervalle horaire (±15 minutes).";
    } else {
        $reserver = new Reserver();
        $reserver->setIdreserv($idreserv);
        $reserver->setDatereservation($datereservation);
        $reserver->setNomcli($nomcli);
        $reserver->setDatereservee($datereservee);
        $reserver->setIdtable($idtable);
        $reserver->setStatus($status);

        $update = $reserver->updateReservation();
        if ($update) {
            header('Location: ReservationPage.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/styles/bootstrap5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Modifier une reservation</title>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
             <h1 class="mb-4 text-center text-primary">Modifier une réservation</h1>
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <?php if (isset($errorMessage)): ?>
                            <div class="alert alert-danger">
                                <?php echo $errorMessage; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!$reserver): ?>
                            <div class="alert alert-danger">Réservation non trouvée.</div>
                        <?php else: ?>
                            <form action="EditReservation.php?idreserv=<?php echo $reserver->getIdreserv(); ?>" method="POST">
                                <div class="mb-3">
                                    <label for="idreserv" class="form-label">Numéro de réservation</label>
                                    <input type="text" class="form-control" id="idreserv" name="idreserv" value="<?php echo $reserver->getIdreserv(); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="datereserv" class="form-label">Date et heure de réservation</label>
                                    <input type="datetime-local" class="form-control" id="datereserv" name="datereserv" 
                                           value="<?php echo !empty($reserver->getDatereservation()) ? date('Y-m-d\TH:i', strtotime($reserver->getDatereservation())) : ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="nomcli" class="form-label">Nom du client</label>
                                    <input type="text" class="form-control" id="nomcli" name="nomcli" value="<?php echo $reserver->getNomcli(); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="datereserve" class="form-label">Date et heure réservé</label>
                                    <input type="datetime-local" class="form-control" id="datereserve" name="datereserve" 
                                           value="<?php echo !empty($reserver->getDatereservee()) ? date('Y-m-d\TH:i', strtotime($reserver->getDatereservee())) : ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="idtable" class="form-label">Table actuelle : <?php echo $reserver->getIdtable(); ?></label>
                                    <select class="form-select" id="idtable" name="idtable">
                                        <option value="">Sélectionnez une nouvelle table</option>
                                        <?php
                                        $table = new Tables();
                                        $tables = $table->listTables();
                                        foreach ($tables as $table) {
                                            $selected = $table['NUMTABLE'] === $reserver->getIdtable() ? 'selected' : '';
                                            echo "<option value='{$table['NUMTABLE']}' $selected>Table {$table['NUMTABLE']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Statut</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="À Venir" <?php echo ($reserver->getStatus() === 'À Venir') ? 'selected' : ''; ?>>À Venir</option>
                                        <option value="En cours" <?php echo ($reserver->getStatus() === 'En cours') ? 'selected' : ''; ?>>En cours</option>
                                        <option value="Expiré" <?php echo ($reserver->getStatus() === 'Expiré') ? 'selected' : ''; ?>>Expiré</option>
                                    </select>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary">Modifier</button>
                                    <button type="button" onclick="window.location='ReservationPage.php'" class="btn btn-danger">Annuler</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once 'footer.php';?>
</body>
</html>