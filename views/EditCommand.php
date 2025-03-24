<?php 
require_once '../models/Commandes.php';
require_once '../models/Commandedetail.php';
require_once '../models/Menus.php';
require_once '../models/Tables.php';
require_once '../auth_check.php';

// Ajout du traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Initialisation
        $commande = new Commandes();
        $commandedetail = new Commandedetail();
        $transaction_ok = true;
        
        // Validation initiale
        if (empty($_POST)) {
            throw new Exception("Aucune donnée n'a été soumise");
        }

        // Validation de base de la commande
        $idcom = isset($_POST['idcom']) ? htmlspecialchars(trim($_POST['idcom'])) : null;
        $nomClient = isset($_POST['nomClient']) ? htmlspecialchars(trim($_POST['nomClient'])) : null;
        $dateCommande = isset($_POST['dateCommande']) ? htmlspecialchars(trim($_POST['dateCommande'])) : null;
        $typeCommande = isset($_POST['typeCommande']) ? htmlspecialchars(trim($_POST['typeCommande'])) : null;
        $idTable = filter_input(INPUT_POST, 'idtable', FILTER_VALIDATE_INT);

        if (!$idcom || !$nomClient || !$dateCommande || !$typeCommande) {
            throw new Exception("Données de commande invalides");
        }

        // Préparation des données de la commande
        $dataCommande = [
            'IDCOM' => $idcom,
            'NOMCLI' => $nomClient,
            'DATECOM' => $dateCommande,
            'TYPECOM' => $typeCommande,
            'IDTABLE' => $idTable ?: null
        ];

        // Début de la transaction
        try {
            // Mise à jour de la commande principale
            if (!$commande->updateCommande($dataCommande)) {
                throw new Exception("Échec de la mise à jour de la commande principale");
            }

            // Traitement des détails de commande
            if (!empty($_POST['menus'])) {
                foreach ($_POST['menus'] as $idPlat => $menuData) {
                    if (!isset($menuData['checked'])) {
                        // Suppression si décoché
                        if (!$commandedetail->deleteDetail($idcom, $idPlat)) {
                            throw new Exception("Échec de la suppression du menu #$idPlat");
                        }
                        continue;
                    }

                    $quantite = (int)$menuData['quantity'];
                    if ($quantite <= 0) continue;

                    // Récupération du prix
                    $menuObj = new Menus();
                    $menuInfo = $menuObj->getMenuById($idPlat);
                    if (!$menuInfo) {
                        throw new Exception("Menu #$idPlat non trouvé");
                    }

                    // Vérifier si le prix est dans PU au lieu de PRIX
                    $prix = isset($menuInfo['PU']) ? floatval($menuInfo['PU']) : (isset($menuInfo['PRIX']) ? floatval($menuInfo['PRIX']) : 0);
                    if ($prix <= 0) {
                        throw new Exception("Prix invalide pour le menu #$idPlat");
                    }

                    $detailData = [
                        'IDCOM' => $idcom,
                        'IDPLAT' => $idPlat,
                        'QUANTITE' => $quantite,
                        'PRIXTOTAL' => $quantite * $prix
                    ];

                    // Debug du détail
                    error_log("Détail à ajouter/modifier : " . print_r($detailData, true));

                    // Mise à jour ou ajout du détail
                    $existingDetail = $commandedetail->getDetailByPlatAndCommande($idcom, $idPlat);
                    if ($existingDetail) {
                        if (!$commandedetail->updateCommandeDetail($detailData)) {
                            throw new Exception("Échec de la mise à jour du menu #$idPlat");
                        }
                    } else {
                        // Utiliser les setters avant d'appeler addCommandeDetail
                        $commandedetail->setIdcom($idcom);
                        $commandedetail->setIdplat($idPlat);
                        $commandedetail->setQte($quantite);
                        $commandedetail->setPrixTotal($quantite * $prix);
                        
                        if (!$commandedetail->addCommandedetail()) {
                            throw new Exception("Échec de l'ajout du menu #$idPlat");
                        }
                    }
                }
            }

            // Après la mise à jour réussie, remplacer la redirection existante par :
            $_SESSION['message'] = "Commande mise à jour avec succès";
            header('Location: CommandePage.php'); // Redirection vers la page des commandes
            exit();

        } catch (Exception $e) {
            // En cas d'erreur, on annule tout
            throw new Exception("Erreur lors de la sauvegarde : " . $e->getMessage());
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        error_log("Erreur EditCommand: " . $e->getMessage());
        header('Location: ' . $_SERVER['PHP_SELF'] . '?idcom=' . $idcom . '&error=1');
        exit();
    }
}

// Affichage des messages
if (isset($_GET['success']) && isset($_SESSION['message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
} elseif (isset($_GET['error']) && isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}

// Récupération des données de la commande et des détails associés
$idcom = isset($_GET['idcom']) ? $_GET['idcom'] : null;
$commande = new Commandes();
$commandeData = $commande->getCommandById($idcom);

// Vérification de l'existence de la commande
if (!$commandeData) {
    die("Erreur : Commande non trouvée.");
}

// Récupération et traitement des détails de la commande
$commandedetail = new Commandedetail();
$commandedetail->setIdcom($idcom);
$details = $commandedetail->getCommandedetail();

// Construction du tableau des détails
$detailsArray = [];
if ($details && is_array($details)) {
    foreach ($details as $detail) {
        // Les clés sont déjà en majuscules dans les données reçues
        if (isset($detail['IDPLAT']) && isset($detail['QUANTITE'])) {
            $detailsArray[$detail['IDPLAT']] = [
                'QTE' => $detail['QUANTITE'],
                'PRIX' => $detail['PRIXTOTAL'],
                'IDPLAT' => $detail['IDPLAT']
            ];
        }
    }
}

$menus = (new Menus())->listMenu();
$tables = (new Tables())->getAvailableTables($commandeData['IDTABLE']);
$prixTotal = $commandedetail->getPrixTotal();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Modifier une commande</title>
</head>
<body>
    <?php 
        require_once 'header.php';
    ?>
    <div class="container mb-5">
        <section >
            <h1 class="text-primary mb-3">Modifier une Commande</h1>
            <form method="POST">
                <div class="form-group">
                    <label for="idcom" class="mb-1">ID Commande</label>
                    <input type="text" class="form-control" id="idcom" name="idcom" value="<?php echo htmlspecialchars($commandeData['IDCOM']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="nomClient" class="mb-1">Nom du Client</label>
                    <input type="text" class="form-control" id="nomClient" name="nomClient" value="<?php echo htmlspecialchars($commandeData['NOMCLI']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="dateCommande" class="mb-1">Date de la Commande</label>
                    <input type="datetime-local" class="form-control" id="dateCommande" name="dateCommande" value="<?php echo htmlspecialchars($commandeData['DATECOM']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="typeCommande" class="mb-1">Type de Commande</label>
                    <select class="form-control" id="typeCommande" name="typeCommande" required>
                        <option value="Sur place" <?php echo $commandeData['TYPECOM'] == 'Sur place' ? 'selected' : ''; ?>>Sur place</option>
                        <option value="Emporté" <?php echo $commandeData['TYPECOM'] == 'Emporté' ? 'selected' : ''; ?>>Emporté</option>
                    </select>
                </div>
                <div class="form-group" id="tableGroup">
                    <label for="idtable">Table</label>
                    <select class="form-control" id="idtable" name="idtable" <?php echo $commandeData['TYPECOM'] == 'Emporté' ? 'disabled' : ''; ?>>
                        <option value="" class="mb-1">Sélectionnez une table</option>
                        <?php foreach ($tables as $table): 
                            $isCurrentTable = $commandeData['IDTABLE'] == $table['NUMTABLE'];
                        ?>
                            <option value="<?php echo $table['NUMTABLE']; ?>" 
                                <?php echo $isCurrentTable ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($table['DESIGNATION']); ?>
                                <?php echo $isCurrentTable ? ' (Table actuelle)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="mb-1">Menus</label>
                    <?php if (empty($menus)): ?>
                        <p>Aucun menu disponible.</p>
                    <?php else: ?>
                        <div class="menu-list">
                            <?php foreach ($menus as $menu): 
                                // Garder l'ID dans son type original
                                $menuId = $menu['IDPLAT'];
                                // Debug détaillé
                                
                                
                                $isChecked = array_key_exists($menuId, $detailsArray);
                                $quantity = $isChecked ? $detailsArray[$menuId]['QTE'] : 0;
                            ?>
                                <div class="menu-item">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input menu-checkbox" 
                                            id="menu_<?php echo htmlspecialchars($menuId); ?>" 
                                            name="menus[<?php echo htmlspecialchars($menuId); ?>][checked]" 
                                            data-price="<?php echo htmlspecialchars($menu['PRIX']); ?>"
                                            <?php echo $isChecked ? 'checked' : ''; ?>>
                                        <input type="hidden" 
                                            name="menus[<?php echo htmlspecialchars($menuId); ?>][prix]" 
                                            value="<?php echo htmlspecialchars($menu['PRIX']); ?>">
                                        <label class="form-check-label menu-label" for="menu_<?php echo htmlspecialchars($menuId); ?>">
                                            <?php echo htmlspecialchars($menu['NOMPLAT']); ?> 
                                            (<?php echo number_format($menu['PRIX'], 2); ?> €)
                                        </label>
                                        <div class="quantity-container" style="display: <?php echo $isChecked ? 'flex' : 'none'; ?>">
                                            <label>Qté:</label>
                                            <input type="number" class="form-control quantity-input" 
                                                name="menus[<?php echo htmlspecialchars($menuId); ?>][quantity]" 
                                                value="<?php echo htmlspecialchars($quantity); ?>" 
                                                min="1" 
                                                <?php echo $isChecked ? '' : 'disabled'; ?> 
                                                required>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="prixTotal" class="mb-1">Prix Total</label>
                    <input type="number" step="0.01" class="form-control" id="prixTotal" name="prixTotal" value="<?php echo $prixTotal; ?>" readonly>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">Modifier</button>
                    <button type="button" class="btn btn-danger" onclick="window.location.href='CommandePage.php'">Retour</button>
                </div>
            </form>
        </section>
    </div>
    <?php require_once 'footer.php';?>

    <script>
    $(document).ready(function() {
        function updateTotals() {
            let grandTotal = 0;
            $('.menu-checkbox:checked').each(function() {
                const row = $(this).closest('.menu-item');
                const price = parseFloat($(this).data('price'));
                const quantity = parseInt(row.find('.quantity-input').val()) || 0;
                const lineTotal = price * quantity;
                grandTotal += lineTotal;
            });
            $('#prixTotal').val(grandTotal.toFixed(2));
        }

        $('.menu-checkbox').change(function() {
            const quantityInput = $(this).closest('.menu-item').find('.quantity-input');
            const quantityContainer = $(this).closest('.menu-item').find('.quantity-container');
            
            if (this.checked) {
                quantityInput.prop('disabled', false).val(1);
                quantityContainer.show();
            } else {
                quantityInput.prop('disabled', true).val('');
                quantityContainer.hide();
            }
            updateTotals();
        });

        $('.quantity-input').on('input', function() {
            updateTotals();
        });

        // Calcul initial
        updateTotals();
    });

    document.getElementById('typeCommande').addEventListener('change', function() {
        var tableSelect = document.getElementById('idtable');
        var tableGroup = document.getElementById('tableGroup');
        var previousTable = tableSelect.value;

        if (this.value === 'Emporté') {
            tableSelect.disabled = true;
            tableSelect.value = ''; // Réinitialiser la sélection
            tableGroup.style.opacity = '0.5';
        } else {
            tableSelect.disabled = false;
        }
    });
    </script>
</body>
</html>