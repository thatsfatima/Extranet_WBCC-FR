<?php
require_once "../../../app/config/config.php";
require_once "../../../app/libraries/Database.php";
require_once "../../../app/libraries/Model.php";
require_once "../../../app/models/Projet.php";
require_once "../../../app/models/Immeuble.php";
require_once "pdf.php";

$idProjet = $_GET['idProjet'];
$idImmeuble = $_GET['idImmeuble'];

$projetModel = new Projet();
$immeubleModel = new Immeuble();

$projet = $projetModel->findProjetByColumnValue("idProjet", $idProjet);
$immeuble = $immeubleModel->findImmeubleById($idImmeuble);
$sommaire = $projetModel->findSommaireByIdProjet($idProjet);

$sections = $projetModel->findSectionByIdSommaire($sommaire[0]->idSommaire);

// Rediger Projet
$projetPdf = new ProjetPdf($projet);

//Numéro de page
$projetPdf->AliasNbPages();

// PAGE DE GARDE
$projetPdf->AddPage();
$projetPdf->PageDeGarde($projet, $immeuble);

// CONTENU DOCUMENT PROJET AVEC TOUTES LES SECTIONS
$projetPdf->setMargins(10, 10, 10, 10);
$projetPdf->startPageNums();
$projetPdf->ajouterSectionsRecursives($projet, $sections);

// ADD SOMMAIRE
$projetPdf->insertSommaire();

header('Content-type: application/pdf');

//SAVE COMPTE RENDU
$nom = __DIR__ . "/$projet->nomProjet.pdf";
$projetPdf->Output($nom, 'F');
$nom = str_replace('"', "", $nom);
$file = "$nom";
$file = str_replace('"', "", $file);

echo json_encode($file);
// $pdf->Output($file, 'I');

// echo json_encode($file);
