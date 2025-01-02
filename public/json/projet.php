<?php
header('Access-Control-Allow-Origin: *');
require_once "../../app/config/config.php";
require_once "../../app/libraries/Utils.php";
require_once "../../app/libraries/Database.php";

if (isset($_GET['action'])) {
    $db = new Database();

    $action = $_GET['action'];

    // Générer PDF action
    if ($action == "saveDocumentPDF") {
        extract($_POST);
        if (isset($idProjet) && isset($idImmeuble)) {
            $file = file_get_contents(URLROOT . "/public/documents/projet/projet.php?idProjet=$idProjet&idImmeuble=$idImmeuble");
            $file = str_replace('"', "", $file);
             
            if ($file != "") {
                // //ADD DOCUMENT TO OP
                // $search = findItemByColumn("wbcc_document", "urlDocument", $file);
                // if ($search) {
                //     //UPDATE
                //     $db->query("UPDATE wbcc_document SET nomDocument = :nomDocument, urlDocument = :urlDocument, commentaire = :commentaire,  guidHistory = :guidHistory, typeFichier=:typeFichier, size=:size, guidUser=:guidUser, idUtilisateurF= :idUtilisateurF, auteur=:auteur, source=:source WHERE idDocument = $search->idDocument");
                // } else {
                //     $search = false;
                //     //CREATE
                //     $numeroDoc = 'DOC' . date("dmYHis") . "$idProjet" . 518;
                //     $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");
                //     $db->bind("publie",  "0", null);
                //     $db->bind("numeroDocument", $numeroDoc, null);
                //     $db->bind("createDate",  date("Y-m-d H:i:s"), null);
                // }
                // $db->bind("source", "EXTRA", null);
                // $db->bind("nomDocument", $file, null);
                // $db->bind("urlDocument", $file, null);
                // $db->bind("commentaire", "", null);
                // $db->bind("guidHistory", null, null);
                // $db->bind("typeFichier", "Adobe Acrobat Document", null);
                // $db->bind("size", null, null);
                // $db->bind("guidUser", $numeroAuteur, null);
                // $db->bind("idUtilisateurF", $idAuteur, null);
                // $db->bind("auteur", "$auteur", null);
                // $db->execute();
                echo json_encode($file);
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
