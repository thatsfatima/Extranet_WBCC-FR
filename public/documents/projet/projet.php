<?php
header('Access-Control-Allow-Origin: *');

require_once "../../../app/libraries/Database.php";
require_once "../../../app/libraries/Utils.php";
require_once "../../../app/libraries/Model.php";
require_once "../../../app/models/Section.php";
require_once "projetPdf.php";
require_once "word.php";

$idProjet = $_GET['idProjet'];
$idImmeuble = $_GET['idImmeuble'];
$type = $_GET['doc'];
$typeRapport = isset($_GET['typeRapport']) ? $_GET['typeRapport'] : '';
$class = ($_GET['doc'] == 'pdf') ? 'ProjetPdf' : 'Word';
require_once lcfirst($class) . '.php';
$sectionModel = new Section();
$projet = findItemByColumn("wbcc_projet", "idProjet", $idProjet);
$immeuble =  findItemByColumn("wbcc_immeuble", "idImmeuble", $idImmeuble);
$sommaire =  findItemByColumn("wbcc_sommaire", "idProjetF", $idProjet);
$sections =  $sectionModel->getSectionsParentBySommaire($sommaire->idSommaire);
$documents = $sectionModel->getDocumentsBySommaire($sommaire->idSommaire);

$fileName = "PROJET_$projet->idProjet";
$config = [
    'mode' => 'P',
    'format' => 'A4',
    'default_font_size' => 12,
    'debug' => true,
    'enable_links' => true,
];

$projetDoc = new $class($fileName, $projet, $immeuble, $sections, $config, $documents, $typeRapport);

$document = $projetDoc->document();