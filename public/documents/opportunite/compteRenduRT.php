<?php
require_once "../../../app/config/config.php";
require_once "../../../app/libraries/Utils.php";
require_once "../../../app/libraries/Database.php";
require_once './compteRenduPdf.php';

$idOp  = $_GET['idOP'];

$db = new Database();
//RT
$db->query("SELECT * FROM wbcc_releve_technique WHERE idOpportunityF=$idOp LIMIT 1");
$data['rt'] = $db->single();
//RV
$db->query("SELECT * FROM wbcc_rendez_vous WHERE idOpportunityF=$idOp LIMIT 1");
$data['rv'] = $db->single();
// RF
$db->query("SELECT * FROM wbcc_recherche_fuite WHERE idOpportunityF=$idOp LIMIT 1");
$data['rf'] = $db->single();
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
$db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF  AND idOpportunityF=" . $data['o']->idOpportunity . " ORDER BY idOpportunityApp DESC  LIMIT 1");
$data['app'] = $db->single();

$data['adresse'] = ($data['immeuble']) ? $data['immeuble']->adresse : (($data['app']) ? $data['app']->adresse  : "");
$data['cp'] = ($data['immeuble']) ? $data['immeuble']->codePostal  : (($data['app']) ?  $data['app']->codePostal  : "");
$data['ville'] = ($data['immeuble']) ? $data['immeuble']->ville : (($data['app']) ?  $data['app']->ville : "");

//CONTACT
$db->query("SELECT * FROM wbcc_contact  WHERE  numeroContact=:numeroContact LIMIT 1");
$db->bind("numeroContact", $data['o']->guidContactClient, null);
$data['contact'] = $db->single();
if ($data['contact'] == false) {
    $db->query("SELECT * FROM wbcc_contact  WHERE  fullName=:fullName LIMIT 1");
    $db->bind("fullName", $data['o']->contactClient, null);
    $data['contact'] = $db->single();
}
if ($data['o']->typeSinistre == "Partie commune exclusive" && $data['o']->source != null && $data['o']->source != "") {
    $db->query("SELECT * FROM wbcc_contact  WHERE  numeroContact=" . $data['o']->source . " LIMIT 1");
    $data['contact'] = $db->single();
}

//COMPAGNIE
$data['guidComp'] = $data['o']->typeSinistre == "Partie commune exclusive" ? $data['o']->guidComMRI : $data['o']->guidComMRH;
$db->query("SELECT * FROM wbcc_company, wbcc_company_opportunity  WHERE idCompany=idCompanyF AND idOpportunityF= " . $data['o']->idOpportunity . " AND category LIKE '%ASSURANCE%'  LIMIT 1");
$data['cie'] = $db->single();

$data['titre'] = "" . strtoupper($data['adresse'] . " du "  . date('d/m/Y'));
$tabPieces = [];
if ($data['rt']) {
    $db->query("SELECT  * FROM wbcc_rt_piece WHERE idRTF= " . $data['rt']->idRT . "");
    $tabPieces = $db->resultSet();
}

$data['estLocataire'] = false;
$data['estProprietaire'] = false;
$data['estPersMorale'] = false;

$data['do'] = "";
if (str_contains(strtolower($data['o']->typeDO), "particulier")) {
    $db->query("SELECT * FROM wbcc_contact WHERE numeroContact = :guidDO LIMIT 1");
    $db->bind('guidDO', $data['o']->guidDO, null);
    $data['do'] = $db->single();
} else {
    $db->query("SELECT * FROM wbcc_contact WHERE numeroContact = :guidDO LIMIT 1");
    $db->bind('guidDO', $data['o']->guidContactClient, null);
    $data['do'] = $db->single();
}
if ($data['do'] && (str_contains(strtolower($data['do']->statutContact), "proprietaire"))) {
    $data['estProprietaire'] = true;
} else {
    $data['estLocataire'] = true;
}
//APP
if ($data['app']  && (str_contains(strtolower($data['app']->typeProprietaire), "moral"))) {
    $data['estPersMorale'] = true;
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

$data['numPolice'] = $data['o']->typeSinistre == "Partie commune exclusive" ? $data['o']->policeMRI : $data['o']->policeMRH;
$data['numSinistre'] = $data['o']->typeSinistre == "Partie commune exclusive" ? $data['o']->sinistreMRI : $data['o']->sinistreMRH;
$data['dateSinistre'] = "";
$data['dateDebutContrat'] = "";
$data['dateFinContrat'] = "";

if ($data['immeuble']) {
    if ($data['o']->typeSinistre == "Partie commune exclusive") {
        $data['numPolice'] = ($data['numPolice'] == null || $data['numPolice'] == "") ? $data['immeuble']->numPolice : $data['numPolice'];
    }
    if ($data['immeuble']->dateEffetContrat != null && $data['immeuble']->dateEffetContrat != "" && strpos("/", $data['immeuble']->dateEffetContrat)) {
        $dateNew = date_parse_from_format("d/m/Y H:i", $data['immeuble']->dateEffetContrat);
        if ($dateNew) {
            $data['dateDebutContrat'] = $dateNew['year'] . "-" . str_pad(($dateNew['month']), 2, '0', STR_PAD_LEFT) . "-" .  str_pad(($dateNew['day']), 2, '0', STR_PAD_LEFT);
        }
    } else {
        $data['dateDebutContrat'] = $data['immeuble']->dateEffetContrat;
    }
    if ($data['immeuble']->dateEcheanceContrat != null && $data['immeuble']->dateEcheanceContrat != "" && strpos("/", $data['immeuble']->dateEcheanceContrat)) {
        $dateNew = date_parse_from_format("d/m/Y H:i", $data['immeuble']->dateEcheanceContrat);
        if ($dateNew) {
            $data['dateFinContrat'] = $dateNew['year'] . "-" . str_pad(($dateNew['month']), 2, '0', STR_PAD_LEFT) . "-" .  str_pad(($dateNew['day']), 2, '0', STR_PAD_LEFT);
        }
    } else {
        $data['dateFinContrat'] = $data['immeuble']->dateEcheanceContrat;
    }
}

if ($data['app']) {
    if ($data['o']->typeSinistre == "Partie privative exclusive") {
        $data['numPolice'] = ($data['numPolice'] == null || $data['numPolice'] == "") ? $data['app']->numPoliceOccupant : $data['numPolice'];
    }
    if ($data['app']->dateEffetOccupant != null && $data['app']->dateEffetOccupant != "" && strpos("/", $data['app']->dateEffetOccupant)) {
        $dateNew = date_parse_from_format("d/m/Y H:i", $data['app']->dateEffetContratOccupant);
        if ($dateNew) {
            $data['dateDebutContrat'] = $dateNew['year'] . "-" . str_pad(($dateNew['month']), 2, '0', STR_PAD_LEFT) . "-" .  str_pad(($dateNew['day']), 2, '0', STR_PAD_LEFT);
        }
    } else {
        $data['dateDebutContrat'] = $data['app']->dateEffetOccupant;
    }
    if ($data['app']->dateEcheanceOccupant != null && $data['app']->dateEcheanceOccupant != "" && strpos("/", $data['app']->dateEcheanceOccupant)) {
        $dateNew = date_parse_from_format("d/m/Y H:i", $data['immeuble']->dateEcheanceOccupant);
        if ($dateNew) {
            $data['dateFinContrat'] = $dateNew['year'] . "-" . str_pad(($dateNew['month']), 2, '0', STR_PAD_LEFT) . "-" .  str_pad(($dateNew['day']), 2, '0', STR_PAD_LEFT);
        }
    } else {
        $data['dateFinContrat'] = $data['app']->dateEcheanceOccupant;
    }
}

$db->query("SELECT * FROM `wbcc_sommaire` WHERE idRTF=" . $data['rt']->idRT);
$idSommaire = $db->single()->idSommaire;
for ($i=1; $i < 8; $i++) { 
    $db->query("SELECT * FROM `wbcc_section` WHERE numeroSection='4-$i' AND idSommaireF=$idSommaire");
    $data['deroulementSeance'][$i] = $db->single();
}

$db->query("SELECT * FROM `wbcc_section` WHERE idSommaireF=$idSommaire");
$data['sections'] = $db->resultSet();

$dataSections = [];
foreach ($data['sections'] as $value) {
    $dataSections[$value->numeroSection] = $value;
}
$data['sections'] = $dataSections;

$data['tabPieces'] = $tabPieces;

$data['dateSinistre'] =  ($data['rt'] ? ($data['rt']->date != null && $data['rt']->date != "" && trim($data['rt']->date) != "" ? $data['rt']->date : ($data['rt']->dateConstat != null && $data['rt']->dateConstat != ""  && trim($data['rt']->dateConstat) != "" ? $data['rt']->dateConstat : ($data['rt']->anneeSurvenance != null && $data['rt']->anneeSurvenance != ""  && trim($data['rt']->anneeSurvenance) != "" ? $data['rt']->anneeSurvenance : ""))) : "");
if ($data['dateSinistre'] != "" && substr($data['dateSinistre'], 4, 1) == '-') {
    $date = new DateTime($data['dateSinistre']);
    $data['dateSinistre'] = $date->format('d/m/Y');
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
    'margin_top' => 25,
    'margin_right' => 12,
    'margin_left' => 12,
    'margin_bottom' => 33,
    'margin_header' => 0,
];

$sommaire = new CompteRenduPdf($config, $data);
$sommaireCompteRendu = $sommaire->returnSommaire();
$nb_sommaire_page = $sommaire->returnSommairePage($sommaireCompteRendu);

$pdf = new CompteRenduPdf($config, $data, $sommaireCompteRendu, $nb_sommaire_page);

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
        "numPolice" => $data['numPolice'],
        "numSinistre" => $data['numSinistre'],
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
        "numPolice" => $data['numPolice'],
        "numSinistre" => $data['numSinistre'],
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
    "numPolice" => $data['numPolice'],
    "numSinistre" => $data['numSinistre'],
    'format' => 'json'
);
$query = http_build_query($query_array);
$file = file_get_contents($url . '?' . $query);
$file = str_replace('"', "", $file);

echo json_encode($file);
// $pdf->Output($file, 'I');

// echo json_encode($file);