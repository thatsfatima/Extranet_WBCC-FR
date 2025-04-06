<?php
header('Access-Control-Allow-Origin: *');

require_once "../../app/config/config.php";
require_once "../../app/libraries/Utils.php";
require_once "../../app/libraries/Database.php";

if (isset($_GET['action'])) {
    $db = new Database();

    $action = $_GET['action'];

    // Générer PDF action
    if ($action == "saveDocument") {
        // $doc peut être égal 'pdf' ou 'docx'
        extract($_POST);
        if (isset($idProjet) && isset($idImmeuble)) {
            $typeRapport = isset($typeRapport) ? $typeRapport : '';
            $file = file_get_contents(URLROOT . "/public/documents/projet/projet.php?idProjet=$idProjet&idImmeuble=$idImmeuble&doc=$doc&typeRapport=$typeRapport");
            $file = str_replace('"', "", $file);
            if ($file != "") {
                if (str_contains($file, "docx")) {
                    $file = "PROJET_$idProjet.docx";
                } else {
                    $file = "Global_PROJET_$idProjet.pdf";
                }
                echo ($file);
            } else {
                echo json_encode("0");
            }
        } else {
            echo json_encode("1");
        }
    } else {
        echo json_encode("0");
    }
}