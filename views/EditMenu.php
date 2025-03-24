<?php
require_once '../models/Menus.php';
require_once '../controllers/Menu.php';
require_once '../models/Connexion.php';
require_once '../auth_check.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/styles/bootstrap5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Modifier un Menu</title>
</head>
<body>
    <?php require_once 'header.php'; ?>
    <section class="container mt-5">
        <h1 class="text-primary mb-4 text-center">Modifier un Menu</h1>
        <form action="" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light shadow-sm" style="max-width: 600px; margin: auto;">
            <?php
            if (isset($_GET['idplat'])) {
                $menuid = $_GET['idplat'];
                $con = Connexion::getConnexion();

                $query = "SELECT * FROM menu WHERE IDPLAT=:menuid";
                $statement = $con->prepare($query);
                $data = [
                    ':menuid' => $menuid
                ];
                $statement->execute($data);

                $result = $statement->fetch(PDO::FETCH_OBJ);

                if ($result) {
                    $nomplat = $result->NOMPLAT;
                    $pu = $result->PU;
                    $image = $result->IMAGE;
                } else {
                    echo "<div class='alert alert-danger'>Menu introuvable</div>";
                    exit();
                }
            }

            if (isset($_POST['nomplat']) && isset($_POST['pu'])) {
                try {
                    $menu = new Menus();
                    $menu->setNomplat($_POST['nomplat']);
                    $menu->setPu($_POST['pu']);
                    $menu->setIdplat($menuid);

                    // Gestion de l'image
                    if (!empty($_FILES['image']['tmp_name'])) {
                        $imageData = file_get_contents($_FILES['image']['tmp_name']);
                        $menu->setImage($imageData);
                    } else {
                        $menu->setImage($image); // Conserver l'image actuelle
                    }

                    // Appel de la méthode updateMenu
                    $menu->updateMenu();

                    echo "<div class='alert alert-success mt-3'>Menu modifié avec succès</div>";
                    header('Location: MenuPage.php');
                    exit();
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger mt-3'>Erreur : " . $e->getMessage() . "</div>";
                }
            }
            ?>
            <div class="mb-3">
                <label for="nomplat" class="form-label">Nom du plat</label>
                <input type="text" class="form-control" id="nomplat" name="nomplat" value="<?php echo htmlspecialchars($nomplat, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="pu" class="form-label">Prix du plat</label>
                <input type="number" class="form-control" id="pu" name="pu" value="<?php echo htmlspecialchars($pu, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image actuelle</label>
                <?php if (!empty($image)): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($image); ?>" alt="Image actuelle" class="img-thumbnail mb-2" style="width: 150px; height: 150px;">
                <?php else: ?>
                    <p class="text-muted">Aucune image disponible</p>
                <?php endif; ?>
                <label for="image" class="form-label">Télécharger une nouvelle image (facultatif)</label>
                <input type="file" class="form-control" id="image" name="image">
            </div>
            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">Modifier</button>
                <button type="button" class="btn btn-danger" onclick="window.location.href='MenuPage.php'">Annuler</button>
            </div>
        </form>
    </section>
    <?php require_once 'footer.php';?>
    <script src="css/styles/bootstrap5.3.2/js/bootstrap.min.js"></script>
</body>
</html>