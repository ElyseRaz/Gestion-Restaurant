<?php
require_once '../auth_check.php';
require_once '../controllers/Table.php';
require_once '../models/Tables.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['designation']) && isset($_POST['occupation'])) {
    try {
        if (empty($_POST['designation'])) {
            throw new Exception("La désignation de la table ne peut pas être vide");
        }

        $table = new Tables();
        $table->setDesignation(trim($_POST['designation']));
        $table->setOccupation($_POST['occupation']);
        
        if ($table->addTable()) {
            header('Location: TablePage.php');
            exit();
        } else {
            $message = "Erreur lors de l'ajout de la table. Veuillez vérifier que la désignation n'existe pas déjà.";
            $messageType = "danger";
        }
    } catch (Exception $e) {
        $message = "Erreur : " . $e->getMessage();
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/styles/bootstrap5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Ajouter une Table</title>
</head>
<body>
    <?php require_once 'header.php'; ?>
    <section class="container mt-5">
        <h1 class="text-success mb-4 text-center">Ajouter une Table</h1>
        <form action="AddTable.php" method="POST" class="p-4 border rounded bg-light shadow-sm" style="max-width: 600px; margin: auto;">
            <div class="mb-3">
                <label for="designation" class="form-label">Désignation</label>
                <input type="text" class="form-control" id="designation" name="designation" placeholder="Désignation de la table" required>
            </div>
            <div class="mb-3">
                <label for="occupation" class="form-label">Occupation</label>
                <select class="form-control" id="occupation" name="occupation">
                    <option value="0">Libre</option>
                    <option value="1">Occupée</option>
                </select>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-success">Ajouter</button>
                <button type="button" class="btn btn-danger" onclick="window.location.href='TablePage.php'">Annuler</button>
            </div>

            <?php 
            if ($message) {
                echo "<div class='alert alert-{$messageType} mt-3'>{$message}</div>";
            }
            ?>
        </form>
    </section>
    <?php require_once 'footer.php';?>
    <script src="css/styles/bootstrap5.3.2/js/bootstrap.min.js"></script>
</body>
</html>