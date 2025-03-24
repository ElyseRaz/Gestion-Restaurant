<?php
require_once '../models/Commandes.php';
require_once '../models/Commandedetail.php';
require_once '../fpdf/fpdf.php';
require_once '../auth_check.php';

// Fonction pour convertir un montant en lettres
function convertirMontantEnLettres($nombre) {
    // Convertir le montant en lettres
    $f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
    return ucfirst($f->format($nombre));
}

if (isset($_GET['idcom'])) {
    $idcom = $_GET['idcom'];

    // Récupérer les détails de la commande
    $commandeInstance = new Commandes();
    $commande = $commandeInstance->getCommandById($idcom);

    if ($commande) {
        // Initialiser FPDF avec des marges personnalisées
        $pdf = new FPDF();
        $pdf->SetMargins(10, 10, 10); // Marges gauche, haut, droite
        $pdf->AddPage();

        // Ajouter une police UTF-8
        $pdf->SetFont('Arial', '', 12);

        // Nom du restaurant (en haut, centré)
        $pdf->SetFont('Arial', 'B', 16); // Police plus grande et en gras
        $pdf->Cell(0, 10, mb_convert_encoding('CHEZ L\'OR', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $pdf->Ln(5);

        // Date de facture (centrée)
        $pdf->SetFont('Arial', '', 12);
        $date_commande = new DateTime($commande['DATECOM']);
        $pdf->Cell(0, 10, mb_convert_encoding('Date de Facture : ' . $date_commande->format('d/m/Y'), 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $pdf->Ln(5);

        // Code commande (centré)
        $pdf->Cell(0, 10, mb_convert_encoding('Code Commande : ' . $commande['IDCOM'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $pdf->Ln(5);

        // Nom du client (aligné à gauche)
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, mb_convert_encoding('Nom du Client : ' . $commande['NOMCLI'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $pdf->Ln(5);

        // Table ou type de commande (aligné à gauche)
        $tableOrType = $commande['IDTABLE'] ? 'Table : ' . $commande['IDTABLE'] : 'Type : A Emporté';
        $pdf->Cell(0, 10, mb_convert_encoding($tableOrType, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $pdf->Ln(10);

        // Tableau des détails (centré avec un titre)
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, mb_convert_encoding('Votre facture en détail', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $pdf->Ln(5);

        // Largeur totale du tableau
        $tableWidth = 60 + 30 + 30 + 40;
        $startX = ($pdf->GetPageWidth() - $tableWidth) / 2;

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetX($startX);
        $pdf->Cell(60, 10, mb_convert_encoding('Menu', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell(30, 10, mb_convert_encoding('PU (Ar)', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell(30, 10, mb_convert_encoding('Unité', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell(40, 10, mb_convert_encoding('Total (Ar)', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');

        $pdf->SetFont('Arial', '', 12);
        $commandedetailInstance = new Commandedetail();
        $details = $commandedetailInstance->getDetailsByCommandId($idcom);

        $total_general = 0;
        foreach ($details as $detail) {
            $total = $detail['PRIX'] * $detail['QTE'];
            $total_general += $total;

            $pdf->SetX($startX); 
            $pdf->Cell(60, 10, mb_convert_encoding($detail['NOMPLAT'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
            $pdf->Cell(30, 10, mb_convert_encoding($detail['PRIX'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
            $pdf->Cell(30, 10, mb_convert_encoding($detail['QTE'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
            $pdf->Cell(40, 10, mb_convert_encoding($total, 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
        }

        $pdf->Ln(5);

        // Total général (centré)
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetX($startX);
        $pdf->Cell(120, 10, mb_convert_encoding('TOTAL :', 'ISO-8859-1', 'UTF-8'), 0, 0, 'R'); 
        $pdf->Cell(40, 10, mb_convert_encoding($total_general . ' Ariary', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C'); 
        $pdf->Ln(5);
       

        // Montant en lettres (aligné à gauche)
        $montant_en_lettres = convertirMontantEnLettres($total_general);
        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, mb_convert_encoding('Montant en lettres : ' . $montant_en_lettres . ' Ariary', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

        // Place pour la signature du vendeur (à droite)
        $pdf->Ln(20);
        $pdf->SetX($pdf->GetPageWidth() - 70); 
        $pdf->Cell(0, 10, mb_convert_encoding('Signature du vendeur :', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $pdf->Ln(20);
        $pdf->SetX($pdf->GetPageWidth() - 70); 
        $pdf->Cell(0, 10, '__________________', 0, 1, 'L');

        // Afficher le PDF
        $pdf->Output();
    } else {
        echo mb_convert_encoding("Commande introuvable.", 'ISO-8859-1', 'UTF-8');
    }
} else {
    echo mb_convert_encoding("ID de commande manquant.", 'ISO-8859-1', 'UTF-8');
}
?>