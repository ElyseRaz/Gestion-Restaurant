<?php
require_once '../auth_check.php';
require_once '../controllers/Menu.php';
require_once '../models/Menus.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($_POST['nomplat']) || empty($_POST['pu']) || empty($_FILES['image'])) {
            throw new Exception("Tous les champs sont obligatoires");
        }

        if (!is_numeric($_POST['pu']) || $_POST['pu'] <= 0) {
            throw new Exception("Le prix doit être un nombre positif");
        }

        $menu = new Menus();
        $menu->setNomplat(trim($_POST['nomplat']));
        $menu->setPu($_POST['pu']);

        // Validation de l'image
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erreur lors du téléchargement de l'image");
        }

        $imageData = file_get_contents($_FILES['image']['tmp_name']);
        $menu->setImage($imageData);

        if ($menu->addMenu()) {
            header('Location: MenuPage.php');
            exit();
        } else {
            throw new Exception("Erreur lors de l'ajout du menu. Vérifiez que le nom du plat n'existe pas déjà.");
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
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
    <title>Ajouter un Menu</title>
</head>
<body>
    <?php require_once 'header.php';?>
    <section class="container mt-5">
        <h1 class="text-success mb-4 text-center">Ajouter un Menu</h1>
        <form action="" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light shadow-sm" style="max-width: 600px; margin: auto;">
            <div class="mb-3">
                <label for="nomplat" class="form-label">Nom du plat</label>
                <input type="text" class="form-control" id="nomplat" name="nomplat" placeholder="Entrez le nom du plat" required>
            </div>
            <div class="mb-3">
                <label for="pu" class="form-label">Prix du plat</label>
                <input type="number" class="form-control" id="pu" name="pu" placeholder="Entrez le prix du plat" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image du plat</label>
                <input type="file" class="form-control" id="image" name="image" required>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-success">Ajouter</button>
                <button type="button" class="btn btn-danger" onclick="window.location.href='MenuPage.php'">Annuler</button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> mt-3"><?php echo $message; ?></div>
            <?php endif; ?>
        </form>
    </section>
    <?php require_once 'footer.php';?>
    <script src="css/styles/bootstrap5.3.2/js/bootstrap.min.js"></script>
</body>
</html>