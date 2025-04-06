<?php
require_once "../../../app/config/config.php";
require_once "../../../app/libraries/Utils.php";
require_once "../../../app/libraries/Database.php";
require_once '../../libs/vendor2/autoload.php';

use \Mpdf\Mpdf;

$idOp  = $_GET['idOP'];

$db = new Database();
//RT
$db->query("SELECT * FROM wbcc_releve_technique WHERE idOpportunityF=$idOp LIMIT 1");
$data['rt'] = $db->single();
//RV
$db->query("SELECT * FROM wbcc_rendez_vous WHERE idOpportunityF=$idOp LIMIT 1");
$data['rv'] = $db->single();
//OPPORTUNITY
$db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity = $idOp");
$data['o'] = $db->single();
if ($data['o'] == false) {
    echo "Vous ne disposez pas de ce relevé technique.";
    exit;
}
$GLOBALS['opportunity'] = $data['o'];
//GET IMMEUBLE
$db->query("SELECT * FROM wbcc_immeuble, wbcc_opportunity_immeuble  WHERE idImmeuble=idImmeubleF AND idOpportunityF= :idOpportunityF LIMIT 1");
$db->bind("idOpportunityF", $data['o']->idOpportunity, null);
$data['immeuble'] = $db->single();
if ($data['immeuble'] == false && $data['o']->immeuble != null && $data['o']->immeuble != "") {
    $db->query("SELECT * FROM wbcc_immeuble  WHERE codeImmeuble=:immeuble LIMIT 1");
    $db->bind("immeuble", $data['o']->immeuble, null);
    $data['immeuble'] = $db->single();
}
//GET APPARTEMENT
$db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF  AND idOpportunityF=" . $data['o']->idOpportunity . "ORDER BY idOpportunityApp DESC  LIMIT 1");
$app = $db->single();

$adresse = ($data['immeuble']) ? $data['immeuble']->adresse : (($app) ? $app->adresse  : "");
$cp = ($data['immeuble']) ? $data['immeuble']->codePostal  : (($app) ?  $app->codePostal  : "");
$ville = ($data['immeuble']) ? $data['immeuble']->ville : (($app) ?  $app->ville : "");

//CONTACT
$db->query("SELECT * FROM wbcc_contact  WHERE  numeroContact=" . $data['o']->guidContactClient . " LIMIT 1");
$contact = $db->single();
if ($contact == false) {
    $db->query("SELECT * FROM wbcc_contact  WHERE  fullName=:fullName LIMIT 1");
    $db->bind("fullName", $data['o']->contactClient, null);
    $contact = $db->single();
}
if ($data['o']->typeSinistre == "Partie commune exclusive" && $data['o']->source != null && $data['o']->source != "") {
    $db->query("SELECT * FROM wbcc_contact  WHERE  numeroContact=" . $data['o']->source . " LIMIT 1");
    $contact = $db->single();
}

//COMPAGNIE
$guidComp = $data['o']->typeSinistre == "Partie commune exclusive" ? $data['o']->guidComMRI : $data['o']->guidComMRH;
$db->query("SELECT * FROM wbcc_company, wbcc_company_opportunity  WHERE idCompany=idCompanyF AND idOpportunityF= " . $data['o']->idOpportunity . " AND category LIKE '%ASSURANCE%'  LIMIT 1");
$cie = $db->single();

$titre = "" . strtoupper($adresse . " du "  . date('d/m/Y'));
$tabPieces = [];
if ($data['rt']) {
    $db->query("SELECT  * FROM wbcc_rt_piece WHERE idRTF= " . $data['rt']->idRT . "");
    $tabPieces = $db->resultSet();
}

$estLocataire = false;
$estProprietaire = false;
$estPersMorale = false;

$do = "";
if (str_contains(strtolower($data['o']->typeDO), "particulier")) {
    $db->query("SELECT * FROM wbcc_contact WHERE numeroContact = :guidDO LIMIT 1");
    $db->bind('guidDO', $data['o']->guidDO, null);
    $do = $db->single();
} else {
    $db->query("SELECT * FROM wbcc_contact WHERE numeroContact = :guidDO LIMIT 1");
    $db->bind('guidDO', $data['o']->guidContactClient, null);
    $do = $db->single();
}
if ($do && (str_contains(strtolower($do->statutContact), "proprietaire"))) {
    $estProprietaire = true;
} else {
    $estLocataire = true;
}
//APP
if ($app  && (str_contains(strtolower($app->typeProprietaire), "moral"))) {
    $estPersMorale = true;
}

if (sizeof($tabPieces) > 0) {
    //GET SUPPORTS
    foreach ($tabPieces as $key => $piece) {
        $db->query("SELECT  * FROM wbcc_rt_piece_support WHERE idRTPieceF= $piece->idRTPiece ");
        $supports = $db->resultSet();
        $piece->listSupports = $supports;
        //GET REVETEMENTS
        foreach ($supports as $key2 => $support) {
            $db->query("SELECT  * FROM wbcc_rt_revetement WHERE idRTPieceSupportF= $support->idRTPieceSupport ");
            $revetements = $db->resultSet();
            $support->listRevetements = $revetements;

            //GET OUVERTURES
            $db->query("SELECT  * FROM wbcc_rt_ouverture WHERE idRTPieceSupportF= $support->idRTPieceSupport ");
            $data['ouvertures'] = $db->resultSet();
            $support->listOuvertures = $data['ouvertures'];
        }
    }
}

//bien
// $db->query("SELECT * FROM `wbcc_bien` WHERE `idRTF` = $data['rt']->idRT");
// $data = $db->resultSet();

$numPolice = $data['o']->typeSinistre == "Partie commune exclusive" ? $data['o']->policeMRI : $data['o']->policeMRH;
$numSinistre = $data['o']->typeSinistre == "Partie commune exclusive" ? $data['o']->sinistreMRI : $data['o']->sinistreMRH;
$dateSinistre = "";
$dateDebutContrat = "";
$dateFinContrat = "";

if ($data['immeuble']) {
    if ($data['o']->typeSinistre == "Partie commune exclusive") {
        $numPolice = ($numPolice == null || $numPolice == "") ? $data['immeuble']->numPolice : $numPolice;
    }
    if ($data['immeuble']->dateEffetContrat != null && $data['immeuble']->dateEffetContrat != "" && strpos("/", $data['immeuble']->dateEffetContrat)) {
        $dateNew = date_parse_from_format("d/m/Y H:i", $data['immeuble']->dateEffetContrat);
        if ($dateNew) {
            $dateDebutContrat = $dateNew['year'] . "-" . str_pad(($dateNew['month']), 2, '0', STR_PAD_LEFT) . "-" .  str_pad(($dateNew['day']), 2, '0', STR_PAD_LEFT);
        }
    } else {
        $dateDebutContrat = $data['immeuble']->dateEffetContrat;
    }
    if ($data['immeuble']->dateEcheanceContrat != null && $data['immeuble']->dateEcheanceContrat != "" && strpos("/", $data['immeuble']->dateEcheanceContrat)) {
        $dateNew = date_parse_from_format("d/m/Y H:i", $data['immeuble']->dateEcheanceContrat);
        if ($dateNew) {
            $dateFinContrat = $dateNew['year'] . "-" . str_pad(($dateNew['month']), 2, '0', STR_PAD_LEFT) . "-" .  str_pad(($dateNew['day']), 2, '0', STR_PAD_LEFT);
        }
    } else {
        $dateFinContrat = $data['immeuble']->dateEcheanceContrat;
    }
}

if ($app) {
    if ($data['o']->typeSinistre == "Partie privative exclusive") {
        $numPolice = ($numPolice == null || $numPolice == "") ? $app->numPoliceOccupant : $numPolice;
    }
    if ($app->dateEffetOccupant != null && $app->dateEffetOccupant != "" && strpos("/", $app->dateEffetOccupant)) {
        $dateNew = date_parse_from_format("d/m/Y H:i", $app->dateEffetContratOccupant);
        if ($dateNew) {
            $dateDebutContrat = $dateNew['year'] . "-" . str_pad(($dateNew['month']), 2, '0', STR_PAD_LEFT) . "-" .  str_pad(($dateNew['day']), 2, '0', STR_PAD_LEFT);
        }
    } else {
        $dateDebutContrat = $app->dateEffetOccupant;
    }
    if ($app->dateEcheanceOccupant != null && $app->dateEcheanceOccupant != "" && strpos("/", $app->dateEcheanceOccupant)) {
        $dateNew = date_parse_from_format("d/m/Y H:i", $data['immeuble']->dateEcheanceOccupant);
        if ($dateNew) {
            $dateFinContrat = $dateNew['year'] . "-" . str_pad(($dateNew['month']), 2, '0', STR_PAD_LEFT) . "-" .  str_pad(($dateNew['day']), 2, '0', STR_PAD_LEFT);
        }
    } else {
        $dateFinContrat = $app->dateEcheanceOccupant;
    }
}

$dateSinistre =  ($data['rt'] ? ($data['rt']->date != null && $data['rt']->date != "" && trim($data['rt']->date) != "" ? $data['rt']->date : ($data['rt']->dateConstat != null && $data['rt']->dateConstat != ""  && trim($data['rt']->dateConstat) != "" ? $data['rt']->dateConstat : ($data['rt']->anneeSurvenance != null && $data['rt']->anneeSurvenance != ""  && trim($data['rt']->anneeSurvenance) != "" ? $data['rt']->anneeSurvenance : ""))) : "");
if ($dateSinistre != "" && substr($dateSinistre, 4, 1) == '-') {
    $date = new DateTime($dateSinistre);
    $dateSinistre = $date->format('d/m/Y');
}
//DEVIS
$db->query("SELECT * FROM `wbcc_opportunity_devis` od, wbcc_opportunity, wbcc_devis WHERE idDevisF=idDevis AND idOpportunityF=idOpportunity AND idOpportunityF=$idOp AND od.valide=1");
$data['devis'] = $db->single();

$config = [
    'mode' => 'P',
    'format' => 'A4',
    'default_font_size' => 12,
    'debug' => true,
    'enable_links' => true,
    'margin_top' => 23,
    'margin_right' => 12,
    'margin_left' => 12,
    'margin_bottom' => 21,
    'margin_header' => 0,
];

$pdf = new CompteRendu($config, $data);
$nom = str_replace('"', "", $pdf->document());
$file = "$nom";
$file = str_replace('"', "", $file);

$i = 0;
//ANNEXE DELEGATION
$rapportDelegation = $data['o']->rapportDelegation;
if ($rapportDelegation != "") {
    $i++;
    $url = URLROOT . "/public/json/concatTwoDocuments.php";
    $query_array = array(
        'idOp' =>  $data['o']->idOpportunity,
        'nomDocument1' => $nom,
        'nomDocument2' => $rapportDelegation,
        'titre' => "Délégation de gestion et de paiement",
        'index' => "$i",
        "numOP" => $data['o']->name,
        "numPolice" => $numPolice,
        "numSinistre" => $numSinistre,
        'format' => 'json'
    );
    $query = http_build_query($query_array);
    $file = file_get_contents($url . '?' . $query);
    $file = str_replace('"', "", $file);
}

//ANNEXE DEVIS

if ($data['devis']) {
    $i++;
    $url = URLROOT . "/public/json/concatTwoDocuments.php";
    $query_array = array(
        'idOp' =>  $data['o']->idOpportunity,
        'nomDocument1' => $nom,
        'nomDocument2' => $data['devis']->devisFile,
        'titre' => "Devis",
        'index' => "$i",
        "numOP" => $data['o']->name,
        "numPolice" => $numPolice,
        "numSinistre" => $numSinistre,
        'format' => 'json'
    );
    $query = http_build_query($query_array);
    $file = file_get_contents($url . '?' . $query);
    $file = str_replace('"', "", $file);
}

//HEADER - FOOTER - filigrane

$url = URLROOT . "/public/json/concatTwoDocuments.php";
$query_array = array(
    'idOp' =>  $data['o']->idOpportunity,
    'nomDocument1' => $nom,
    'nomDocument2' => "x",
    'titre' => "fin",
    'index' => "",
    "numOP" => $data['o']->name,
    "numPolice" => $numPolice,
    "numSinistre" => $numSinistre,
    'format' => 'json'
);
$query = http_build_query($query_array);
$file = file_get_contents($url . '?' . $query);
$file = str_replace('"', "", $file);

echo json_encode($file);
// $pdf->Output($file, 'I');

// echo json_encode($file);