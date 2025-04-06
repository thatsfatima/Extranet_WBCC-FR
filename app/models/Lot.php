<?php

class Lot extends Model
{
    public function getComptesByImmeuble($idImmeuble, $etatCompte = '')
    {
        $data = [];
        $sql = "";
        if ($etatCompte == "debiteur") {
            $sql .= " AND solde < 0 ";
        } else {
            if ($etatCompte == "crediteur") {
                $sql .= " AND solde > 0 ";
            }
        }
        $this->db->query("SELECT * FROM wbcc_appartement  WHERE  idImmeubleF  = $idImmeuble AND etatApp=1 AND siLotPrincipal='Oui' $sql  ORDER BY proprietaire");
        $response = $this->db->resultSet();
        foreach ($response as $key => $value) {
            //SEARCH IF COPRO DEJA TAITE
            $traite = false;
            foreach ($response as $key2 => $value2) {
                if ($key2 < $key && $value2->proprietaire == $value->proprietaire) {
                    $traite = true;
                }
                # code...
            }
            if (!$traite) {
                $lots = $this->getLotsWithSameCopro("wbcc_appartement", "proprietaire", $value->proprietaire);
                $solde = 0;
                $soldeChargeCourante = 0;
                $soldeChargeExceptionnelle = 0;
                $soldeFondTravauxAlur = 0;
                $soldeAvance = 0;
                $soldeChargeEmprunt = 0;
                $tantieme = 0;
                foreach ($lots as $key2 => $lot) {
                    $tantieme += $lot->tantieme != null && $lot->tantieme != "" ? $lot->tantieme : 0;
                    if ($lot->compteIndividuel == "1") {
                        $solde += ($lot->solde != null && $lot->solde != "" && $lot->solde != "0") ? ((float)(str_replace(",", ".", $lot->solde))) : 0;
                        $soldeChargeCourante += $lot->soldeChargeCourante != null && $lot->soldeChargeCourante != "" ? (float)(str_replace(",", ".", $lot->soldeChargeCourante)) : 0;
                        $soldeChargeExceptionnelle += $lot->soldeChargeExceptionnelle != null && $lot->soldeChargeExceptionnelle != "" ? (float)(str_replace(",", ".", $lot->soldeChargeExceptionnelle)) : 0;
                        $soldeFondTravauxAlur += $lot->soldeFondTravauxAlur != null && $lot->soldeFondTravauxAlur != "" ? (float)(str_replace(",", ".", $lot->soldeFondTravauxAlur)) : 0;
                        $soldeAvance += $lot->soldeAvance != null && $lot->soldeAvance != "" ? (float)(str_replace(",", ".", $lot->soldeAvance)) : 0;
                        $soldeChargeEmprunt += $lot->soldeChargeEmprunt != null && $lot->soldeChargeEmprunt != "" ? (float)(str_replace(",", ".", $lot->soldeChargeEmprunt)) : 0;
                    } else {
                        $solde = $solde == 0 ? (float)(str_replace(",", ".", $lot->solde)) : $solde;
                        $soldeChargeCourante = $soldeChargeCourante == 0 ? (float)str_replace(",", ".", $lot->soldeChargeCourante) : $soldeChargeCourante;
                        $soldeChargeExceptionnelle = $soldeChargeExceptionnelle == 0 ? (float)(str_replace(",", ".", $lot->soldeChargeExceptionnelle)) : $soldeChargeExceptionnelle;
                        $soldeFondTravauxAlur = $soldeFondTravauxAlur == 0 ? (float)str_replace(",", ".", $lot->soldeFondTravauxAlur) : $soldeFondTravauxAlur;
                        $soldeAvance = $soldeAvance == 0 ? (float)(str_replace(",", ".", $lot->soldeAvance)) : $soldeAvance;
                        $soldeChargeEmprunt = $soldeChargeEmprunt == 0 ? (float)str_replace(",", ".", $lot->soldeChargeEmprunt) : $soldeChargeEmprunt;
                    }
                }
                $value->index = $key + 1;
                $value->solde = round($solde, 2);
                $value->soldeChargeEmprunt = round($soldeChargeEmprunt, 2);
                $value->soldeAvance = round($soldeAvance, 2);
                $value->soldeFondTravauxAlur = round($soldeFondTravauxAlur, 2);
                $value->soldeChargeExceptionnelle = round($soldeChargeExceptionnelle, 2);
                $value->soldeChargeCourante = round($soldeChargeCourante, 2);
                $value->tantieme = $tantieme;
                $data[] = $value;
            }
        }
        return $data;
    }

    public function removeLotToAcquire($idSection, $idLot)
    {
        try {
            // error_log("Début de la suppression - Section: $idSection, Lot: $idLot");

            // // Trouver la section correcte pour les variables de simulation
            // $querySectionCheck = "SELECT DISTINCT idSectionF FROM wbcc_appartement_variable_simulation 
            //                       WHERE idAppartementF = :idLot";
            // $this->db->query($querySectionCheck);
            // $this->db->bind(':idLot', $idLot);
            // $sectionResults = $this->db->resultSet();

            // $variablesDeleted = true;
            // foreach ($sectionResults as $sectionResult) {
            //     $queryVars = "DELETE FROM wbcc_appartement_variable_simulation 
            //                  WHERE idSectionF = :idSection 
            //                  AND idAppartementF = :idLot";
            //     error_log("Requête variables pour section " . $sectionResult->idSectionF . ": $queryVars");

            //     $this->db->query($queryVars);
            //     $this->db->bind('idSection', $sectionResult->idSectionF, null);
            //     $this->db->bind('idLot', $idLot, null);
            //     $resultVars = $this->db->execute();

            //     $rowCount = $this->db->rowCount();
            //     error_log("Nombre de variables de simulation supprimées pour section " . $sectionResult->idSectionF . ": " . $rowCount);

            //     $variablesDeleted = $variablesDeleted && $resultVars;
            // }

            //SUpprimer variable de simulation
            $queryVars = "DELETE FROM wbcc_appartement_variable_simulation 
                              WHERE idSectionF = :idSection 
                              AND idAppartementF = :idLot";

            $this->db->query($queryVars);
            $this->db->bind('idSection', $idSection, null);
            $this->db->bind('idLot', $idLot, null);
            $resultVars = $this->db->execute();


            // Supprimer l'association lot-section
            $queryLot = "DELETE FROM wbcc_section_appartement 
                         WHERE idSectionF = :idSection 
                         AND idAppartementF = :idLot";

            $this->db->query($queryLot);
            $this->db->bind('idSection', $idSection, null);
            $this->db->bind('idLot', $idLot, null);
            $resultLot = $this->db->execute();

            // $lotRowCount = $this->db->rowCount();
            // error_log("Nombre de lots-sections supprimés: " . $lotRowCount);

            // Retourner true si les suppressions ont réussi
            return $resultLot;
        } catch (Exception $e) {
            error_log('Exception dans removeLotToAcquire: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());
            return false;
        }
    }
    public function saveLot($idApp, $codeLot, $lot, $batiment, $escalier, $codePorte, $niveau, $nomCopro, $typeLot, $surface, $solde, $tantieme, $siLotPrincipal, $siLotFutur, $compteIndividuel, $codeLotPrincipal, $etatLot, $gererLogement, $typeProprietaire, $immeuble, $proprietaire, $occupant)
    {
        $app = false;
        if ($idApp != "" && $idApp != null && $idApp != "0") {
            $app = $this->findById($idApp);
        } else {
            if ($codeLot != null && $codeLot != "") {
                $this->db->query(" SELECT * FROM wbcc_appartement WHERE idImmeubleF=$immeuble->idImmeuble AND codeApp = :lot  LIMIT 1");
                $this->db->bind("lot", $codeLot, null);
                $app = $this->db->single();
            }
            //FIND APP BY LOT AND IMMEUUBLE
            if (!$app) {
                if ($lot != null && $lot != "") {
                    $this->db->query(" SELECT * FROM wbcc_appartement WHERE idImmeubleF=$immeuble->idImmeuble AND lot = :lot  LIMIT 1");
                    $this->db->bind("lot", $lot, null);
                    $app = $this->db->single();
                }
            }
        }
        $numeroLot = "LOT" . date("YmdHis");
        $idOccupant = null;
        $nomOccupant = null;
        if ($app) {
            $idApp = $app->idApp;
            $codeLot = $codeLot != "" && $codeLot != null ? $codeLot : $app->codeApp;
            $nomCopro = $nomCopro != "" && $nomCopro != null ? $nomCopro : $app->proprietaire;
            $typeLot = $typeLot != "" && $typeLot != null ? $typeLot : $app->typeLot;
            $surface = $surface != "" && $surface != null ? $surface : $app->surface;
            $solde = $solde != "" && $solde != null ? $solde : $app->solde;
            $tantieme = $tantieme != "" && $tantieme != null ? $tantieme : $app->tantieme;
            $siLotPrincipal = $siLotPrincipal != "" && $siLotPrincipal != null ? $siLotPrincipal : $app->siLotPrincipal;
            $siLotFutur = $siLotFutur != "" && $siLotFutur != null ? $siLotFutur : $app->siLotFutur;
            $compteIndividuel = $compteIndividuel != "" && $compteIndividuel != null ? $compteIndividuel : $app->compteIndividuel;
            $codeLotPrincipal = $codeLotPrincipal != "" && $codeLotPrincipal != null ? $codeLotPrincipal : $app->codeLotPrincipal;
            $gererLogement = $gererLogement != "" && $gererLogement != null ? $gererLogement : $app->typeOccupation;
            $typeProprietaire = $typeProprietaire != "" && $typeProprietaire != null ? $typeProprietaire : $app->typeProprietaire;
            $idOccupant = $occupant ? $occupant->idContact : $app->idOccupant;
            $nomOccupant = $occupant ? $occupant->fullName : $app->occupant;
            $this->db->query("UPDATE wbcc_appartement SET codeApp=:codeLot  ,batiment=:batiment  ,codePorte=:codePorte  ,proprietaire=:nomCopro  ,idImmeubleF=:idImmeubleF  ,etage=:niveau, escalier=:escalier, lot=:lot , surface=:surface  ,typeLot=:typeLot, tantieme=:tantiemeCorrespondant, siLotPrincipal=:siLotPrincipal, compteIndividuel=:compteIndividuel, solde=:solde, codeLotPrincipal=:codeLotPrincipal, siLotFutur=:siLotFutur, etatApp=:etatApp, idProprietaire=:idProprietaire, idOccupant=:idOccupant,  occupant=:occupant, typeOccupation=:typeOccupation, typeProprietaire=:typeProprietaire  WHERE idApp =" . $app->idApp);
        } else {
            $this->db->query("INSERT INTO wbcc_appartement (numeroApp,codeApp, batiment, codePorte, proprietaire, idImmeubleF, etage, escalier, lot, surface, typeLot, tantieme, siLotPrincipal, compteIndividuel, solde, codeLotPrincipal, siLotFutur, etatApp, idProprietaire, idOccupant, occupant, typeOccupation, typeProprietaire) VALUES 
                        (:numeroApp,:codeLot, :batiment, :codePorte, :nomCopro, :idImmeubleF, :niveau, :escalier , :lot, :surface, :typeLot, :tantiemeCorrespondant, :siLotPrincipal, :compteIndividuel, :solde, :codeLotPrincipal, :siLotFutur, :etatApp, :idProprietaire, :idOccupant, :occupant, :typeOccupation, :typeProprietaire)");
            $this->db->bind("numeroApp", $numeroLot, null);
        }
        $this->db->bind("codeLot", $codeLot, null);
        $this->db->bind("batiment", $batiment, null);
        $this->db->bind("codePorte", $codePorte, null);
        $this->db->bind("nomCopro", $proprietaire ? $proprietaire->fullName : ($nomCopro), null);
        $this->db->bind("niveau", $niveau, null);
        $this->db->bind("escalier", $escalier, null);
        $this->db->bind("lot", $lot, null);
        $this->db->bind("surface", $surface, null);
        $this->db->bind("typeLot", $typeLot, null);
        $this->db->bind("tantiemeCorrespondant", $tantieme, null);
        $this->db->bind("siLotPrincipal", $siLotPrincipal, null);
        $this->db->bind("solde", ($solde != null && $solde != "" ? $solde : 0), null);
        $this->db->bind("compteIndividuel", $compteIndividuel != null && $compteIndividuel != "" ? $compteIndividuel : null, null);
        $this->db->bind("codeLotPrincipal", $codeLotPrincipal, null);
        $this->db->bind("siLotFutur", $siLotFutur != null && $siLotFutur != "" ? $siLotFutur : null, null);
        $this->db->bind("etatApp", $etatLot, null);
        $this->db->bind("typeOccupation", $gererLogement, null);
        $this->db->bind("typeProprietaire", $typeProprietaire, null);
        $this->db->bind("idImmeubleF", $immeuble->idImmeuble, null);
        $this->db->bind("idProprietaire", $proprietaire ? $proprietaire->idContact : null, null);
        $this->db->bind("idOccupant", $idOccupant, null);
        $this->db->bind("occupant", $nomOccupant, null);
        if ($this->db->execute()) {
            if ($idApp != "" && $idApp != null && $idApp != "0") {
                $app = $this->findById($idApp);
            } else {
                $app = $this->findByNumero($numeroLot);
            }

            if ($proprietaire) {
                $this->insertContactLot($proprietaire->idContact, $app->idApp);
            }
            return $app;
        } else {
            return false;
        }
    }

    public function getLotsByImmeuble($idImmeuble, $typeApp = "", $etatLot = "")
    {
        $sql = "";
        if ($typeApp == "cb") {
            $sql .= " AND codeAPP IS NOT NULL  AND codeAPP != '' ";
        }
        if ($etatLot != "") {
            $sql .= " AND etatLot = $etatLot ";
        }
        $this->db->query("SELECT * FROM wbcc_appartement WHERE idImmeubleF= $idImmeuble  AND etatApp = 1 ORDER BY proprietaire ASC, siLotPrincipal DESC");
        $datas = $this->db->resultSet();
        foreach ($datas as $key => $lot) {
            $lot->index = $key + 1;
        }
        $datas = $this->getLotsTypeByImmeuble($datas);
        return $datas;
    }

    public function getLotsRestantByImmeuble($idImmeuble, $typeApp = "", $etatLot = "", $siLotPrincipal = "", $idSommaire)
    {
        $sql = "";
        if ($typeApp == "cb") {
            $sql .= " AND codeAPP IS NOT NULL  AND codeAPP != '' ";
        }
        if ($etatLot != "") {
            $sql .= " AND etatApp = $etatLot ";
        }

        if ($siLotPrincipal != "") {
            $sql .= " AND siLotPrincipal = '$siLotPrincipal' ";
        }
        if ($idSommaire != null && $idSommaire != "") {
            $sql .= " AND idApp NOT IN ( SELECT idAppartementF FROM wbcc_section_appartement sa, wbcc_section s WHERE s.idSection= sa.idSectionF AND s.idSommaireF = $idSommaire  ) ";
        }
        $this->db->query("SELECT * FROM wbcc_appartement WHERE idImmeubleF= $idImmeuble  $sql ORDER BY proprietaire ASC, siLotPrincipal DESC");
        $datas = $this->db->resultSet();
        $response = [];
        foreach ($datas as $key => $lot) {
            $lot->index = $key + 1;
            $response[] = $lot;
        }
        return $response;
    }

    public function getLotsBySection($idSection, $aAcquerir = "", $type = "")
    {
        $sql = "";
        if ($aAcquerir != "") {
            // Utiliser des quotes pour les valeurs non numériques
            $sql = " AND sl.aAcquerir = '$aAcquerir'";
        }

        if ($type == 'lotAssocie') {
            $section = findItemByColumn("wbcc_section", "idSection", $idSection);
            $this->db->query("SELECT * FROM wbcc_section_appartement sl , wbcc_appartement l, wbcc_section s, wbcc_sommaire som WHERE l.idApp = sl.idAppartementF AND sl.idSectionF = s.idSection AND s.idSommaireF = som.idSommaire AND som.idSommaire = $section->idSommaireF GROUP BY l.idApp ORDER BY  proprietaire ASC, siLotPrincipal DESC");
        } else {

            $this->db->query("SELECT l.* FROM wbcc_section_appartement sl JOIN wbcc_appartement l ON l.idApp = sl.idAppartementF WHERE sl.idSectionF = $idSection $sql ORDER BY  proprietaire ASC, siLotPrincipal DESC");
        }

        $results = $this->db->resultSet();
        $results = $this->getLotsTypeByImmeuble($results, $type);
        return $results;
    }

    public function getLotsTypeByImmeuble($lots, $type = "")
    {
        $lotPrec = null;
        $new = false;
        foreach ($lots as $key => $lot) {
            $lotsSec = [];
            if ($lot->siLotPrincipal == "Oui") {
                $lot->rowsPanSolde =  0;
                $lot->showSolde = false;
                if ($lotPrec == null) {
                    $new = true;
                    $lot->rowsPanSolde =  sizeof($this->getLotsWithSameCopro("wbcc_appartement", "proprietaire", $lot->proprietaire, $type));
                    $lot->showSolde = true;
                } else {
                    if ($lotPrec->proprietaire != $lot->proprietaire) {
                        if ($new) {
                            $new  = false;
                            $lot->showSolde = true;
                            $lot->rowsPanSolde =  sizeof($this->getLotsWithSameCopro("wbcc_appartement", "proprietaire", $lot->proprietaire, $type));
                        } else {
                            $new = true;
                            $lot->rowsPanSolde =  sizeof($this->getLotsWithSameCopro("wbcc_appartement", "proprietaire", $lot->proprietaire, $type));
                            $lot->showSolde = true;
                        }
                    }
                }
                //search lot secondaire
                $lots2 = $this->getLotsWithSameCopro("wbcc_appartement", "codeLotPrincipal", $lot->codeApp, $type);
                foreach ($lots2 as $key2 => $lot2) {
                    $lot2->couleur =  $new ? '#e7ffea' : '#aed8b1';
                    $lotsSec[] = $lot2;
                }
                $lotPrec = $lot;
            }
            $lot->couleur =  $new ? '#e7ffea' : '#aed8b1';
            $lot->lotSecondaires = $lotsSec;
        }
        return $lots;
    }

    public function getLotsWithSameCopro($table, $column, $value, $type = "")
    {
        $sql = "";
        if ($type == "lotAcquerir") {
            $sql .= " AND ((siLotPrincipal = 'Non') OR (siLotPrincipal = 'Oui' AND idApp IN (SELECT idAppartementF FROM wbcc_section_appartement sl)))";
        }
        $this->db->query("SELECT * FROM $table WHERE $column = :value  AND etatApp=1 AND (siLotPrincipal = 'Oui' OR (siLotPrincipal = 'Non' AND codeLotPrincipal IS NOT NULL AND codeLotPrincipal != '')) $sql ");
        $this->db->bind("value", $value, null);
        return $this->db->resultSet();
    }
    //
    public function saveHtmlVariable($idApp, $idSection, $html)
    {
        $this->db->query("SELECT * FROM wbcc_section_appartement WHERE  idAppartementF = :idApp LIMIT 1");
        // $this->db->bind(':idSection', $idSection);
        $this->db->bind(':idApp', $idApp);
        $res = $this->db->single();

        if ($res) {
            $this->db->query("UPDATE wbcc_section_appartement SET htmlVariable = :html WHERE idSectionLot = $res->idSectionLot");
        } else {
            $this->db->query("INSERT INTO wbcc_section_appartement (idSectionF, idAppartementF, aAcquerir, htmlVariable) VALUES (:idSection, :idApp, 1, :html)");
            $this->db->bind(':idSection', $idSection);
            $this->db->bind(':idApp', $idApp);
        }

        $this->db->bind('html', $html, null);
        return $this->db->execute();
    }

    public function getHtmlVariable($idSection, $idApp)
    {
        $this->db->query("SELECT * FROM wbcc_section_appartement WHERE  idAppartementF = :idApp");
        // $this->db->bind(':idSection', $idSection);
        $this->db->bind(':idApp', $idApp);
        $res = $this->db->single();
        return $res;
    }

    public function deleteSectionLotsToAcquire($idSection)
    {
        $this->db->query("DELETE FROM wbcc_section_appartement WHERE idSectionF = :idSection");
        $this->db->bind(':idSection', $idSection);
        return $this->db->execute();
    }

    public function insertSectionLotToAcquire($idSection, $idApp)
    {
        $this->db->query("
        INSERT INTO wbcc_section_appartement 
        (idSectionF, idAppartementF, aAcquerir) 
        VALUES (:idSection, :idApp, 1)
    ");
        $this->db->bind(':idSection', $idSection);
        $this->db->bind(':idApp', $idApp);
        return $this->db->execute();
    }

    //FIN MODIF

    public function  getLots()
    {

        $this->db->query("SELECT * FROM wbcc_appartement ORDER BY idApp DESC");
        return $this->db->resultSet();
    }

    public function  findLotByContact($id)
    {

        $this->db->query("SELECT * FROM wbcc_appartement, wbcc_appartement_contact
        WHERE idApp = idAppartementF
        AND idContactF = $id ");
        return $this->db->single();
    }

    public function  findAppConByLotCon($idContact, $idApp)
    {
        $this->db->query("SELECT * FROM  wbcc_appartement_contact
        WHERE idAppartementF = $idApp
        AND idContactF = $idContact ");
        return $this->db->single();
    }

    public function  getLotsByCompany($id, $idChefSecteur = "")
    {
        $sql = "";
        if ($idChefSecteur != "") {
            $sql = " AND idChefSecteur=$idChefSecteur ";
        }
        $this->db->query("SELECT * FROM wbcc_appartement a, wbcc_company_appartement ca , wbcc_immeuble i
        WHERE idApp = idAppartementF AND a.idImmeubleF = i.idImmeuble
        AND idCompanyF = $id $sql");
        return $this->db->resultSet();
    }

    public function getLotsByCopro($idCopro)
    {
        $this->db->query("SELECT * FROM wbcc_appartement, wbcc_appartement_contact WHERE idAppartementF=idApp AND idContactF=$idCopro");
        $data = $this->db->resultSet();

        return $data;
    }

    public function  getLotByOpportunity($id)
    {

        $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement 
        WHERE idApp = idAppartementF
        AND idOpportunityF = $id");
        return $this->db->resultSet();
    }

    public function addLot(
        $numero,
        $nomImmeuble,
        $adresse1,
        $codePostal,
        $ville,
        $departement,
        $region,
        $numeroLot,
        $batiment,
        $etage,
        $porte,
        $surface,
        $nbPiece
    ) {
        $this->db->query("
           INSERT INTO wbcc_appartement 
           (numeroApp, nomImmeubleSyndic, adresse, codePostal, 
           ville, departement, region, lot, batiment, etage, codePorte, 
           surface, nbPiece) 
           VALUES ('$numero','$nomImmeuble', '$adresse1', 
            '$codePostal', '$ville', '$departement', '$region', '$numeroLot', '$batiment',
            '$etage', '$porte', '$surface', '$nbPiece'
           )");
        if ($this->db->execute()) {
            $lot = $this->findByNumero($numero);
            return $lot->idApp;
        } else {
            return "0";
        }
    }

    public function updateLot(
        $idApp,
        $nomImmeuble,
        $adresse1,
        $codePostal,
        $ville,
        $departement,
        $region,
        $numeroLot,
        $batiment,
        $etage,
        $porte,
        $surface,
        $nbPiece
    ) {
        $this->db->query("
            UPDATE wbcc_appartement SET nomImmeubleSyndic = '$nomImmeuble', adresse='$adresse1',
            codePostal = '$codePostal', ville='$ville', departement='$departement',
            region = '$region', lot='$numeroLot', batiment='$batiment', 
            etage = '$etage', codePorte='$porte', surface='$surface', nbPiece = '$nbPiece', editDate=:editDate
            WHERE idApp = $idApp");
        $this->db->bind("editDate", date("Y-m-d H:i:s"), null);
        $this->db->execute();
    }

    public function deleteContact($id)
    {
        $this->db->query("
            UPDATE wbcc_contact SET etatContact = 0
            WHERE idContact = $id");
        $this->db->execute();
    }

    public function findByNumero($numero)
    {
        $this->db->query("SELECT * FROM wbcc_appartement WHERE numeroApp = '$numero'");
        if ($this->db->single()) {
            return $this->db->single();
        } else {
            return null;
        }
    }

    public function findById($id)
    {
        $this->db->query("SELECT * FROM wbcc_appartement WHERE idApp = $id");
        if ($this->db->single()) {
            return $this->db->single();
        } else {
            return null;
        }
    }

    public function insertContactLot($idContact, $idApp)
    {
        $this->db->query("SELECT * FROM wbcc_appartement_contact WHERE idContactF =$idContact AND  idAppartementF= $idApp LIMIT 1");
        $con = $this->db->single();
        if (!$con) {
            $this->db->query("
            INSERT INTO wbcc_appartement_contact
            (idContactF, idAppartementF) 
            VALUES ($idContact,$idApp)");
            $this->db->execute();
        }
    }


    public function insertCompanyLot($idCompany, $idApp)
    {
        $this->db->query("
           INSERT INTO wbcc_company_appartement 
           (idCompanyF, idAppartementF) 
           VALUES ($idCompany,$idApp)");
        $this->db->execute();
    }

    public function insertOpportunityAppartement($idOpp, $idApp)
    {
        $this->db->query("
           INSERT INTO wbcc_opportunity_appartement 
           (idOpportunityF, idAppartementF) 
           VALUES ($idOpp,$idApp)");
        $this->db->execute();
    }

    public function deleteLotToOpportunity($id, $idOp)
    {
        $this->db->query("
            DELETE FROM wbcc_opportunity_appartement
            WHERE idAppartementF =:id AND idOpportunityF =:idOp");

        $this->db->bind("idOp", $idOp, null);
        $this->db->bind("id", $id, null);
        $this->db->execute();
    }
}