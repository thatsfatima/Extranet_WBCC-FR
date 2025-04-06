<?php
header('Access-Control-Allow-Origin: *');
require_once "../../app/config/config.php";
require_once "../../app/libraries/Utils.php";
require_once "../../app/libraries/Database.php";
require_once "../../app/libraries/Model.php";
require_once "../../app/models/Lot.php";

if (isset($_GET['action'])) {
    $db = new Database();
    $Lot = new Lot();
    $action = $_GET['action'];

    if ($action == "getLotAcquerir") {
        $idImmeuble = $_GET['idImmeuble'];
        $idSommaire = $_GET['idSommaire'];
        $response = $Lot->getLotsRestantByImmeuble($idImmeuble,  "cb", $etatLot = "1", "Oui", $idSommaire);
        echo json_encode($response);
    }

    if ($action == "find") {
        $tab = [];
        $id = $_GET['id'];
        $sql = "SELECT a.idApp, a.lot, a.batiment as bat, a.escalier as esc, a.etage as eta, a.codePorte as porte, a.digicode as digi, a.interphone as interphone, a.cote as cote, a.libellePartieCommune as libellePartieCommune, c.* FROM wbcc_appartement a LEFT JOIN wbcc_appartement_contact ac ON ac.idAppartementF = a.idApp LEFT JOIN wbcc_contact c ON ac.idContactF = c.idContact WHERE ( a.idApp = $id AND lower(c.statutContact) LIKE '%occupant%' ) OR a.idApp = $id LIMIT 1";

        $db->query($sql);
        $imm = $db->single();
        /* 
        var_dump($imm);
        die(); */
        if (empty($imm)) {
            echo json_encode("0");
        } else {
            echo json_encode($imm);
        }
    }

    if ($action == "findByAdresse") {
        $tab = [];
        $adresse = $_GET['adresse'];
        $db->query("SELECT * FROM wbcc_immeuble WHERE adresse = '$adresse'");
        $imm = $db->single();

        if (empty($imm)) {
            echo json_encode("0");
        } else {
            echo json_encode($imm->idImmeuble);
        }
    }

    if ($action == "getLotByImmeuble") {
        $tab = [];
        $data = [];
        $idImmeuble = $_GET['idImmeuble'];
        $response = $Lot->getLotsByImmeuble($idImmeuble);
        foreach ($response as $key => $value) {
            $value->index = $key + 1;
        }
        echo json_encode($response);
    }

    if ($action == "getComptesByImmeuble") {
        $idImmeuble = $_GET['idImmeuble'];
        $etatCompte = $_GET['etatCompte'];
        $response = $Lot->getComptesByImmeuble($idImmeuble, $etatCompte);
        echo json_encode($response);
    }

    if ($action == "getLotCBByImmeuble") {
        $idImmeuble = $_GET['idImmeuble'];
        $response = $Lot->getLotsByImmeuble($idImmeuble);
        $tab = [];
        foreach ($response as $key => $value) {
            if ($value->siLotPrincipal == "Oui") {
                $subvention = findItemByColumn("wbcc_subvention_questionnaire", "idAppF", $value->idApp);
                $value->visiteEffectuee = $subvention ? 1 : 0;
                $tab[] = $value;
            }
        }
        echo json_encode($tab);
    }
}