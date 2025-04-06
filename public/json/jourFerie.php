<?php
header('Access-Control-Allow-Origin: *');

require_once "../../app/config/config.php";
require_once "../../app/libraries/Utils.php";
require_once "../../app/libraries/Database.php";

if (isset($_GET['action'])) {
    $db = new Database();

    $action = $_GET['action'];

    // Générer PDF action
    if ($action == "genererPDF") {
        extract($_POST);
        if (isset($idSiteF) && isset($annee)) {
            $file = file_get_contents(URLROOT . "/public/documents/jourFerie/jourFerie.php?site=$idSiteF&annee=$annee");
            $file = str_replace('"', "", $file);
            if ($file != "") {
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