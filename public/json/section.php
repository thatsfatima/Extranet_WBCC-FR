<?php
header('Access-Control-Allow-Origin: *');
require_once "../../app/config/config.php";
require_once "../../app/libraries/Utils.php";
require_once "../../app/libraries/Database.php";
require_once "../../app/libraries/Model.php";
require_once "../../app/models/Section.php";

if (isset($_GET['action'])) {
    $db = new Database();
    $Section = new Section();

    $action = $_GET['action'];


    if ($action == "saveLineForProjet") {
        extract($_POST);
        $ids_str =  implode(", ", $articleIds);
        $db->query("SELECT * FROM wbcc_section_table WHERE idTableSection IN ({$ids_str})");
        $sectionTables = $db->resultSet();
        foreach ($sectionTables as $key => $sectionTable) {
            $db->query("UPDATE wbcc_section_table SET projetIds=concat(projetIds,';$idProjet;') WHERE idTableSection=$sectionTable->idTableSection");
            $db->execute();
            $sectionTable->idLigne = "0";
            $sectionTable->nomImage = "";
            $sectionTable->remise = $sectionTable->tauxRemise;
            $ligne = json_decode(json_encode($sectionTable), true);
            insertLigneTable($idSection, $ligne, [], $sectionTable->idTableSection);
        }
        $db->query("SELECT * FROM wbcc_section_table WHERE idSectionF=$idSection ORDER BY idTableSection");
        echo json_encode($db->resultSet());
    }

    if ($action == "saveSectionUpload") {
        // echo json_encode($_POST);
        // die;
        extract($_POST);
        foreach ($sections as $key => $section) {
            $sectionDB = false;
            if ($section['idSection_parentF'] != '0') {
                $db->query("SELECT * FROM wbcc_section WHERE  numeroSection=:numeroSection AND idDevisF=:idDevis LIMIT 1");
                $db->bind("numeroSection", $section['idSection_parentF'], null);
                $db->bind("idDevis", $devis['idDevis'], null);
                $sectionDB = $db->single();
            }
            $numSection = "SECT_" . $key + 1 . date("dmYhis");
            $db->query("INSERT INTO wbcc_section(numeroSectionBD,titreSection, numeroSection, contenuSection,idDevisF,idSection_parentF) VALUES ('$numSection',:titreSection, :numeroSection, :contenuSection, :idDevisF, :idSection_parentF)");

            $db->bind(':titreSection', $section['titreSection']);
            $db->bind(':numeroSection', $section['numeroSection']  ?? '');
            $db->bind(':contenuSection', $section['contenuSection']) != null ?  $section['contenuSection'] : '';
            $db->bind(':idDevisF', $devis['idDevis']);
            $db->bind(':idSection_parentF', $section['idSection_parentF'] == "0" || $sectionDB == false  ? null : $sectionDB->idSection);
            if ($db->execute()) {
                $db->query("SELECT * FROM wbcc_section WHERE numeroSectionBD='$numSection' LIMIT 1");
                $lastSection = $db->single();
                if (isset($section['tableur']) && sizeof($section['tableur']) != 0 && $lastSection) {
                    foreach ($section['tableur'] as $keyL => $ligne) {
                        if (insertLigneTable($lastSection->idSection, $ligne, [])) {
                        } else {
                            echo json_encode('-1');
                        }
                    }
                }
            }
        }
        echo json_encode("1");
    }

    // Générer PDF action
    if ($action == "saveSection") {
        $postData = json_decode(file_get_contents('php://input'), true);
        extract($postData);
        if (updateSection($sections)) {
            echo json_encode([
                'success' => true,
                'message' => 'Editeur mises à jour avec succès'
            ]);
        }
    }


    if ($action == 'saveLine') {
        extract($_POST);
        if (insertLigneTable($idSectionLigne, $_POST, $_FILES)) {
            echo json_encode("1");
        } else {
            echo json_encode("0");
        }
    }

    if ($action == 'deleteLigne') {
        // $postData = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        $projetId = isset($_GET['idProjet']) ? $_GET['idProjet'] : '0';
        $db->query("SELECT * FROM wbcc_section_table WHERE idTableSection = $idLigne");
        $sectionTable = $db->single();
        if ($sectionTable && $sectionTable->idTableSectionImport != null && $sectionTable->idTableSectionImport != '0') {
            //Update projet Ids
            if ($projetId != '0') {
                $db->query("UPDATE wbcc_section_table SET projetIds=REPLACE(projetIds,';$projetId;',';') WHERE idTableSection = $sectionTable->idTableSectionImport ");
                $db->execute();
            }
        }
        if (deleteLigneTable($idLigne)) {
            echo "1";
        } else {
            echo "0";
        }
    }

    if ($action == 'deleteSection') {
        // $postData = json_decode(file_get_contents('php://input'), true);
        $idSection = $_GET['idSection'];
        $db->query("DELETE FROM wbcc_section WHERE idSection =$idSection OR idSection_parentF=$idSection");
        if ($db->execute()) {
            echo "1";
        } else {
            echo "0";
        }
    }


    if ($action == "getSectionsByDevis") {
        $idDevis = $_GET['idDevis'];
        try {
            $db->query("SELECT * FROM wbcc_section WHERE idDevisF = :idDevis ORDER BY numeroSection");
            $db->bind(':idDevis', $idDevis);
            $result = $db->resultSet();
            foreach ($result as $key => $section) {
                $tableur = getSectionTable($section->idSection);
                $section->tableur = $tableur;
            }
            $tab = [
                'sections' => $result,
                'devisId' => $idDevis,
                'title' => 'Liste des sections'
            ];
            echo json_encode($tab);
        } catch (Exception $e) {
            error_log("Erreur dans getSecionsByDevis: " . $e->getMessage());
            throw $e;
        }
    }



    if ($action == "getSectionsBySommaire") {
        $idSommaire = $_GET['idSommaire'];
        $result = $Section->getSectionsBySommaire($idSommaire);
        echo json_encode($result);
    }

    if ($action == 'addSection') {
        // echo json_encode($_POST);
        extract($_POST);
        $db->query("INSERT INTO wbcc_section 
        (titreSection, numeroSection, 
        idDevisF, idSection_parentF) 
        VALUES 
        (:titreSection, :numeroSection, 
        :idDevisF, :idSection_parentF)");

        // Bind des valeurs 
        $db->bind(':titreSection', $titreSection);
        $db->bind(':numeroSection', $numeroSection);
        $db->bind(':idDevisF', $idDevisF);
        $db->bind(':idSection_parentF', $idSection_parentF != "" ? $idSection_parentF : null, null);
        if ($db->execute()) {
            echo json_encode("1");
        } else {
            echo json_encode("0");
        }
    }

    if ($action == 'getSectionTable') {
        $idSection = $_GET['idSection'];
        try {
            $result = getTableBySection($idSection);
            echo json_encode($result);
        } catch (Exception $e) {
            error_log("Erreur dans getSectionTable: " . $e->getMessage());
            throw $e;
        }
    }
}

function getSectionTable($idSection)
{
    $db = new Database();

    $db->query("SELECT * FROM wbcc_section_table WHERE idSectionF = $idSection ORDER BY idTableSection");
    $result = $db->resultSet();

    return $result;
}

function insertLigneTable($id, $data, $files, $idTableSection = '0')
{
    $db = new Database();

    $line = null;
    $numero = "L$id" . date("_dmYhis") . rand(1000, 100000);

    if ($data['idLigne'] == "0") {
        $db->query("INSERT INTO wbcc_section_table(numeroTable,libelle,prix,unite,quantite,montant,idSectionF,tauxRemise,tva,idTableSectionImport) VALUES ('$numero',:libelle,:prix,:unite,:quantite,:montant,:idSectionF,:remise,:tva,'$idTableSection')");
    } else {
        $db->query("UPDATE wbcc_section_table SET libelle=:libelle,prix=:prix,unite=:unite,quantite=:quantite,montant=:montant,idSectionF=:idSectionF,tauxRemise=:remise,idTableSectionImport='$idTableSection'  WHERE idSectionF=:idSectionF AND idTableSection=:idTable");
        $db->bind(':idTable', $data['idLigne']);
    }

    $db->bind(':libelle', $data['libelle']);
    $db->bind(':prix', $data['prix'] != "" ? $data['prix'] : 0, null);
    $db->bind(':unite', $data['unite'], null);
    $db->bind(':quantite', $data['quantite'] != "" ? $data['quantite'] : 0, null);
    $db->bind(':montant', $data['montant'] != "" ? $data['montant'] : 0, null);
    $db->bind(':remise', $data['remise'], null);
    $db->bind(':tva', $data['tva'], null);
    $db->bind(':idSectionF', $id);

    if ($db->execute()) {
        if ($data['idLigne'] == 0) {
            $line = findByCol("wbcc_section_table", "numeroTable", $numero);
        } else {
            $line = findByCol("wbcc_section_table", "idTableSection", $data['idLigne']);
        }

        if ($data['nomImage'] != "") {
            if (str_starts_with($data['nomImage'], "ligne")) {
                return true;
            } else {
                $nomFichierOriginal = basename($files["fileListLine"]['name']);
                $extension = pathinfo($nomFichierOriginal, PATHINFO_EXTENSION);
                $file = "ligne_$line->idTableSection" . "." . $extension;
                $cheminTemporaire = $files['fileListLine']['tmp_name'];
                $cheminFinal = "../documents/devis/images/" . $file;
                move_uploaded_file($cheminTemporaire, $cheminFinal);
                $db->query("UPDATE wbcc_section_table SET image='$file' WHERE idTableSection=$line->idTableSection");
                return $db->execute();
            }
        } else {
            $db->query("UPDATE wbcc_section_table SET image='' WHERE idTableSection=$line->idTableSection");
            return $db->execute();
        }
    }
}

function deleteLigneTable($id)
{
    $db = new Database();
    try {

        $db->query("DELETE FROM wbcc_section_table WHERE idTableSection=:idTable");
        $db->bind(':idTable', $id);
        return $db->execute();
    } catch (Exception $e) {
        error_log("Erreur dans updateSection: " . $e->getMessage());
        throw $e;
    }
}

function updateTable($tableur, $idSection)
{
    $db = new Database();
    //Delete les lignes supprimées
    $tableSection = getTableBySection($idSection);
    $tableATraiter = [];
    if (sizeof($tableSection) != 0) {
        foreach ($tableSection as $key1 => $tableDB) {
            $tr = false;
            foreach ($tableur as $key => $tab) {
                if ($tableDB->idTableSection == $tab['id']) {
                    $tr = true;
                    break;
                }
            }
            if (!($tr)) {
                $db->query("DELETE FROM wbcc_section_table WHERE idTableSection =:id");
                $db->bind("id", $tableDB->idTableSection, null);
                $db->execute();
            }
        }
    }

    try {

        foreach ($tableur as $key => $ligne) {
            $data = [
                'libelle' => $ligne['libelle'],
                'unite' => $ligne['unite'],
                'prix' => $ligne['prix'],
                'quantite' => $ligne['quantite'],
                'montant' => $ligne['montant'],
                'id' => $ligne['id']
            ];

            if (!(insertLigneTable($idSection, $data, []))) {
                echo json_encode("Échec de la mise à jour pour la section ID: " . $idSection);
                return false;
            }
        }
        return true;
    } catch (Exception $e) {
        echo json_encode("Erreur dans updateMultiple: " . $e->getMessage());
        return false;
    }
}

function getTableBySection($idSection)
{
    $db = new Database();

    $db->query("SELECT * FROM wbcc_section_table WHERE idSectionF = $idSection ORDER BY idTableSection");
    return $db->resultSet();
}


function updateSection($sections)
{
    $db = new Database();
    try {
        foreach ($sections as $section) {

            $query = "UPDATE wbcc_section SET 
                titreSection = :titreSection,
                numeroSection = :numeroSection,
                contenuSection = :contenuSection,
                typeContenu= :typeContenu,
                tva=:tva
                WHERE idSection = :idSection";
            $db->query($query);

            $db->bind(':titreSection', $section['titreSection'], null);
            $db->bind(':numeroSection', $section['numeroSection'], null);
            $db->bind(':contenuSection', $section['contenuSection'], null);
            $db->bind(':typeContenu', $section['typeContenu'], null);
            $db->bind(':idSection', $section['idSection'], null);
            $db->bind(':tva', $section['tva'], null);

            if ($db->execute()) {
                $query = "UPDATE wbcc_section_table SET 
                tva=:tva
                WHERE idSectionF = :idSection";
                $db->query($query);

                $db->bind(':idSection', $section['idSection'], null);
                $db->bind(':tva', $section['tva'], null);
                $db->execute();
            } else {
                echo json_encode("Échec de la mise à jour pour la section ID: " . $section['idSection']);
                return false;
            }
            // if (isset($section['contenuSectionTableur'])) {
            //     updateTable($section['contenuSectionTableur'], $section['idSection']);
            // }
        }
        return true;
    } catch (Exception $e) {
        echo json_encode("Erreur dans updateMultiple: " . $e->getMessage());
        return false;
    }
}

function findByCol($table, $col, $value)
{
    $db = new Database();
    $db->query("SELECT * FROM $table WHERE $col=:value LIMIT 1");
    $db->bind("value", $value, null);
    return $db->single();
}