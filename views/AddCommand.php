<?php 
    require_once '../models/Commandes.php';
    require_once '../models/Commandedetail.php';
    require_once '../models/Menus.php';
    require_once '../models/Tables.php';
    require_once '../models/ReservER.php'; // Ajout du modèle Reservation
    require_once '../auth_check.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idcom = isset($_POST['idcom']) ? $_POST['idcom'] : null;
        $typecom = isset($_POST['typecom']) ? $_POST['typecom'] : null;
        $idtable = ($typecom === 'A emporter') ? null : (isset($_POST['idtable']) ? $_POST['idtable'] : null);
        $nomcli = isset($_POST['nomcli']) ? $_POST['nomcli'] : null;
        $datecom = isset($_POST['datecom']) ? $_POST['datecom'] : null;
        $plats = isset($_POST['idplat']) ? $_POST['idplat'] : [];
        $quantites = isset($_POST['quantite']) ? $_POST['quantite'] : [];
        $prixTotal = isset($_POST['totalPrice']) ? $_POST['totalPrice'] : 0;

        if (!$idcom || !$nomcli || !$datecom || !$typecom) {
            $errorMessage = "Erreur : Tous les champs obligatoires doivent être remplis.";
        } else {
            // Vérifier si le client a une réservation active
            $reservation = new Reserver();
            $activeReservation = $reservation->getReservationByClientName($nomcli);
            
            if ($activeReservation) {
                // Mettre à jour le statut de la réservation
                $reservation->setIdreserv($activeReservation['IDRESERVATION']);
                $reservation->updateStatusToExpired();
            }

            $commande = new Commandes();
            $commande->setIdcom($idcom);
            $commande->setIdtable($idtable);
            $commande->setNomcli($nomcli);
            $commande->setDatecom($datecom);
            $commande->setTypecom($typecom);
            $commande->addCommande();

            $commandedetail = new Commandedetail();
            foreach ($plats as $idplat) {
                if (isset($quantites[$idplat])) {
                    $quantite = $quantites[$idplat];
                    $commandedetail->setIdcom($idcom);
                    $commandedetail->setIdplat($idplat);
                    $commandedetail->setQte($quantite);
                    $commandedetail->setPrixTotal($prixTotal);
                    $commandedetail->addCommandedetail();
                }
            }

            // Libérer la table si une table est sélectionnée
            if ($idtable !== null) {
                $table = new Tables();
                $table->setIdtable($idtable);
                $table->freeTable(); // Appeler la méthode pour libérer la table
            }

            header('Location: CommandePage.php');
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une commande</title>
    <script src="../js/client-autocomplete.js"></script>
    <style>
        .suggestion-box {
            display: none;
            position: absolute;
            width: 100%;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .suggestion-box div {
            padding: 8px 12px;
            cursor: pointer;
        }
        .suggestion-box div:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>
<section class="container mt-2 mb-5">
    <h1 class="text-success mb-4 text-center">Ajouter une Commande</h1>
    <?php if (isset($errorMessage)): ?>
        <p class="text-danger"><?php echo $errorMessage; ?></p>
    <?php endif; ?>
    <form action="" method="POST" class="p-4 border rounded bg-light shadow-sm" style="max-width: 600px; margin: auto;">
        <div class="mb-3">
            <label for="idcommande" class="form-label">ID de la commande</label>
            <?php
                $commande = new Commandes();
                $newCommandId = $commande->generateCommandId();
            ?>
            <input type="text" class="form-control" id="idcom" name="idcom" value="<?php echo $newCommandId; ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="idtable" class="form-label">Numero de table</label>
            <select class="form-select" id="idtable" name="idtable" required>
                <option value="">Sélectionnez une table</option>
                <?php
                    $table = new Tables();
                    $tables = $table->listOccupiedTables(); // Utilisation de la méthode pour les tables occupées

                    if (!empty($tables)) {
                        foreach ($tables as $table) {
                            echo "<option value='".$table['NUMTABLE']."'>".$table['DESIGNATION']."</option>";
                        }
                    } else {
                        echo "<option disabled>Aucune table occupée disponible</option>";
                    }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="nomcli" class="form-label">Nom du client</label>
            <input type="text" class="form-control" id="nomcli" name="nomcli" required>
        </div>
        <div class="mb-3">
            <label for="idplat" class="form-label">Plats</label>
            <div id="idplat" class="form-check" style="max-height: 200px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px; border-radius: 5px;">
                <?php
                    $menu = new Menus();
                    $menus = $menu->listMenu();

                    if (!empty($menus)) {
                        foreach ($menus as $menu) {
                            $idplat = $menu['IDPLAT'];
                            $nomplat = $menu['NOMPLAT'];  // Changé de 'NOMMENU' à 'NOMPLAT'
                            $prix = $menu['PRIX'];       // Changé de 'pu' à 'PRIX'

                            echo "<div class='form-check d-flex align-items-center'>
                                    <input class='form-check-input me-2' type='checkbox' name='idplat[]' value='".$idplat."' id='plat".$idplat."' data-pu='".$prix."' onchange='toggleQuantityInput(this, ".$idplat.")'>
                                    <label class='form-check-label me-3' for='plat".$idplat."'>".$nomplat."</label>
                                    <input type='number' class='form-control' name='quantite[".$idplat."]' id='quantite".$idplat."' placeholder='Quantité' style='display: none; width: 80px;' min='1' value='1'>
                                  </div>";
                        }
                    } else {
                        echo "<p>Aucun plat disponible</p>";
                    }
                ?>
            </div>
        </div>
        <div class="mb-3">
            <label for="totalPrice" class="form-label">Prix total</label>
            <input type="text" class="form-control" name="totalPrice" id="totalPrice" value="0" readonly>
        </div>
        <div class="mb-3">
            <label for="datecom" class="form-label">Date de la commande</label>
            <input type="date" class="form-control" id="datecom" name="datecom" required>
        </div>
        <div class="mb-3">
            <label for="typecom" class="form-label">Type de commande</label>
            <select class="form-select" id="typecom" name="typecom" onchange="handleTypeCommandeChange()">
                <option value="Sur place">Sur place</option>
                <option value="Emporté">A emporter</option>
            </select>
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-success">Enregistrer</button>
            <button type="button" class="btn btn-danger" onclick="window.location.href='CommandePage.php'">Annuler</button>
        </div>
    </form>
</section>
<?php require_once 'footer.php'; ?>
<script src="js/index.js"></script> <!-- Assurez-vous que le chemin est correct -->
<script>
    function handleTypeCommandeChange() {
        const typecom = document.getElementById('typecom').value;
        const idtable = document.getElementById('idtable');

        if (typecom === 'Emporté') {
            idtable.disabled = true;
            idtable.value = ""; // Réinitialiser la valeur
        } else {
            idtable.disabled = false;
        }
    }
</script>
</body>
</html>