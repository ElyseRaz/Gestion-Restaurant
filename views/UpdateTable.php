<?php 
require_once '../models/Connexion.php';
require_once '../models/Tables.php';
require_once '../auth_check.php';
require_once '../controllers/Table.php';

$message = '';
$result = null;

// Traitement du formulaire
if (isset($_POST['designation']) && isset($_POST['occupation']) && isset($_GET['idtable'])) {
    try {
        $table = new Tables();
        $table->setDesignation($_POST['designation']);
        $table->setOccupation($_POST['occupation']);
        $table->setIdtable($_GET['idtable']);
        
        if ($table->updateTable($_GET['idtable'])) {
            header('Location: TablePage.php');
            exit();
        } else {
            $message = "<div class='alert alert-danger mt-3'>Erreur lors de la modification de la table</div>";
        }
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger mt-3'>Erreur : " . $e->getMessage() . "</div>";
    }
}

// Récupération des données de la table
if (isset($_GET['idtable'])) {
    $tableid = $_GET['idtable'];
    $con = Connexion::getConnexion();
    $query = "SELECT * FROM tables WHERE NUMTABLE=:tableid";
    $statement = $con->prepare($query);
    $statement->execute([':tableid' => $tableid]);
    $result = $statement->fetch(PDO::FETCH_OBJ);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/styles/bootstrap5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Modifier une Table</title>
</head>
<body>
<?php require_once 'header.php'; ?>
<section class="container mt-5 mb-5">
    <h1 class="text-primary mb-4 text-center">Modifier une Table</h1>
    <form action="" method="POST" class="p-4 border rounded bg-light shadow-sm" style="max-width: 600px; margin: auto;">
        <div class="mb-3">
            <label for="designation" class="form-label">Désignation</label>
            <input type="text" class="form-control" id="designation" value="<?=$result->DESIGNATION ?>" name="designation" placeholder="Désignation de la table" required>
        </div>
        <div class="mb-3">
            <label for="occupation" class="form-label">Occupation</label>
            <select class="form-control" id="occupation" name="occupation">
                <option value="0" <?php if($result->OCCUPATION == 0) echo 'selected'; ?>>Libre</option>
                <option value="1" <?php if($result->OCCUPATION == 1) echo 'selected'; ?>>Occupée</option>
            </select>
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Modifier</button>
            <button type="button" class="btn btn-danger" onclick="window.location.href='TablePage.php'">Annuler</button>
        </div>
        <?php echo $message; ?>
    </form>
</section>
<?php require_once 'footer.php';?>
<script src="css/styles/bootstrap5.3.2/js/bootstrap.min.js"></script>
</body>
</html>