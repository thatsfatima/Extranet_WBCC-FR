<?php
require_once "../../../app/libraries/Database.php";
require_once "../../../app/libraries/Utils.php";
require_once "../../../app/libraries/Model.php";
require_once "../../../app/models/Section.php";
// require_once "projetPdf.php";
// require_once "word.php";

$idProjet = $_GET['idProjet'];
$idImmeuble = $_GET['idImmeuble'];
$type = $_GET['doc'];
$class = ($_GET['doc'] == 'pdf') ? 'ProjetPdf' : 'ProjetWord';
require_once lcfirst($class).'.php';
$sectionModel = new Section();

$projet = findItemByColumn("wbcc_projet", "idProjet", $idProjet);
$immeuble =  findItemByColumn("wbcc_immeuble", "idImmeuble", $idImmeuble);
$sommaire =  findItemByColumn("wbcc_sommaire", "idProjetF", $idProjet);
$sections =  $sectionModel->getSectionsParentBySommaire($sommaire->idSommaire);
$fileName = "PROJET_$projet->idProjet";

// Rediger Projet
$projetDoc = new $class($fileName.'.'.$type, $projet, $immeuble, $sections); 

$document = $projetDoc->document();
var_dump($document);
die;
// $projetPdf->Output('D', $nom);
// header("Content-type: application/vnd.ms-word");  
// header("Content-Disposition: attachment;Filename=".$fileName.".doc");
// header("Pragma: no-cache");  
// header("Expires: 0");
// $nom = str_replace('"', "", $nom);
// echo json_encode($nom);

// $file = "$nom";
// $file = str_replace('"', "", $file);


// $word = "../../../public/documents/projet/projet_export/PROJET_$projet->idProjet.doc";

// $file2 = $projetWord->getWord("../../../public/documents/projet/projet_export/$nom", $word);

// echo json_encode($file2);