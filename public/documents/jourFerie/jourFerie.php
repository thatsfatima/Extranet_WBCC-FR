<?php
header('Access-Control-Allow-Origin: *');

require_once "../../../app/libraries/Database.php";
require_once "../../../app/libraries/Utils.php";
require_once "../../../app/libraries/Model.php";
require_once '../../libs/vendor2/autoload.php';
require_once "../../../app/models/JourFerie.php";
require_once "../../../app/models/Site.php";

use \Mpdf\Mpdf;

$config = [
    'mode' => 'P',
    'format' => 'A4',
    'default_font_size' => 12,
    'debug' => true,
    'enable_links' => true,
];
$pdf = new Mpdf($config);
$jourFerieModel = new JourFerie();
$siteModel = new Site();

$annee = $_GET['annee'];
$idSiteF = $_GET['site'];
$joursFeries = $jourFerieModel->getAllJoursFeries($idSiteF, $annee);
$site = $siteModel->findById($idSiteF);

$fileName = "JOURS_FERIES_" . $site->nomSite . "_" . $annee . '.pdf';

$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFillColor(255, 0, 0);
$pdf->SetFont('Times', 'B', 20);
$pdf->SetXY(0, 0);
$pdf->WriteCell($pdf->w, 15, "L'EXCELLENCE DE LA RELATION CLIENT", 0, 1, 'C', true);
$pdf->Ln(18);

$pdf->SetFont('Times', 'BU', 16);
$pdf->x = 50;
$pdf->WriteHTML("<div style='text-align: center; font-weight: bold; width: 100%;'> <u> Note d'information </u> </div>");
$pdf->Ln(18);

$pdf->SetFont('Times', 'BU', 12);
$pdf->WriteHTML("<strong> <u> Objet :</u> Liste des jours fériés " . $annee . "_WBCC-" . $site->nomSite ." </strong>");
$pdf->Ln(15);

$pdf->WriteCell(0, 5, "Veuillez trouver ci-après la liste des jours fériés qui seront observés par WBCC-" . $site->nomSite, 0, 1, 'L');
$pdf->Setmargins(10, 10, 10, 10);
$pdf->Ln(5);

$pdf->SetFont('Times', 'B', 14);
$pdf->SetFillColor(192, 192, 192);
$pdf->WriteCell(10, 10, "#", 0, 0, 'C', true);
$pdf->WriteCell(65, 10, "Date", 0, 0, 'C', true);
$pdf->WriteCell(65, 10, "Evènements", 0, 0, 'C', true);
$pdf->WriteCell(55, 10, 'Statut', 0, 0, 'C', true);
$pdf->Ln(15);
setlocale(LC_TIME, 'fr_FR.UTF-8');

$pdf->SetFillColor(255, 255, 255);
$i = 1;
foreach ($joursFeries as $jourFerie) {
    $pdf->SetFont('Times', 'B', 11);
    $date = $jourFerie->dateJourFerie;
    $date = strftime("%A %d %B %Y", strtotime($date));
    $pdf->WriteCell(10, 8, utf8_encode($i), 1, 0, 'C');
    $pdf->SetFont('Times', '', 11);
    $pdf->WriteCell(65, 8, $date, 1, 0, 'C');
    $pdf->WriteCell(65, 8, $jourFerie->nomJourFerie, 1, 0, 'C');
    
    $pdf->WriteCell(55, 8, (($jourFerie->Payer == 1) ? "Payé" : "Non-Payé") . " et " . (($jourFerie->Chomer == 1) ? "Chômé" : "Non-Chômé"), 1, 0, 'C');
    $pdf->Ln(8);
    $i++;
}
$pdf->Setmargins(10, 10, 10, 10);
$pdf->Ln(15);

if ($site->nomSite == "Dakar") {
    $pdf->SetFont('Times', 'B', 12);
    $pdf->MultiCell(0, 5, "*Jours et dates succeptibles de changement en fonction de l'apparition de la lune (Exemple: Korite, Tabaski...),", 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->MultiCell(0, 5, "Pour les fêtes religieuses telles que Magal, Gamou et Pentecôte, les salariés ont la possibilité de prendre deux jours de congés pour s'y rendre. Vous devez faire la demande à votre hiérarchie ou moins 8 jours avant.", 0, 1, 'C');
    $pdf->Ln(15);
}
$pdf->SetFont('Times', 'BU', 14);
$pdf->WriteCell(0, 5, "La Direction", 0, 1, 'R');

$pdf->Output("../../../public/documents/jourFerie/jour_ferie_export/" . $fileName, 'F');
// $pdf->Output($fileName, 'I');
echo $fileName;
?>