<?php

class Lot extends Model
{
    // Methode qui retourne soit les lots d'un immeuble soit ceux d'une section
    public function getLotsCB($idSection)
    {
        $this->db->query("SELECT * FROM wbcc_section WHERE idSection = $idSection");
        $section = $this->db->single();
        if ($section && $section->action == 'tousLots') {
            $this->db->query("SELECT * FROM wbcc_sommaire WHERE idSommaire = $section->idSommaireF");
            $sommaire = $this->db->single();

            $this->db->query("SELECT * FROM wbcc_projet WHERE idProjet = $sommaire->idProjetF");
            $projet = $this->db->single();
            
            return $this->getLotsCBByImmeuble($projet->idImmeubleCB);
        }
        else if ($section && $section->action == 'lotAcquerir') {
            return $this->getLotsCBBySection($idSection);
        }
        else if ($section && $section->action == 'lotAssocie') {
            $allLotsAssocies = $this->getLotsCBSection($idSection);
            return $allLotsAssocies;
        }
        return null;
    }

    public function getLotsCBByImmeuble($idImmeuble)
    {
        $this->db->query("SELECT * FROM wbcc_lot_cb WHERE idImmeubleF= $idImmeuble");
        return $this->db->resultSet();
    }

    public function getLotsCBBySection($idSection, $aAcquerir = "")
    {
        $sql = "";
        if ($aAcquerir !== "") {
            // Utiliser des quotes pour les valeurs non numériques
            $sql = " AND sl.aAcquerir = '$aAcquerir'";
        }

        $query = "SELECT l.* FROM wbcc_section_lot_cb sl 
                  JOIN wbcc_lot_cb l ON l.idApp = sl.idLotF 
                  WHERE sl.idSectionF = $idSection $sql";

        error_log("Requête SQL : $query");

        $this->db->query($query);
        $results = $this->db->resultSet();

        error_log("Nombre de résultats : " . count($results));

        return $results;
    }
    
    public function getLotsCBSection($idSection, $aAcquerir = "")
    {
        $sql = "";
        if ($aAcquerir !== "") {
            // Utiliser des quotes pour les valeurs non numériques
            $sql = " AND sl.aAcquerir = '$aAcquerir'";
        }

        $query = "SELECT * FROM wbcc_section_lot_cb sl 
                  JOIN wbcc_lot_cb l ON l.idApp = sl.idLotF 
                  WHERE sl.idSectionF = $idSection $sql";

        error_log("Requête SQL : $query");

        $this->db->query($query);
        $results = $this->db->resultSet();

        error_log("Nombre de résultats : " . count($results));

        return $results;
    }

    public function saveHtmlVariable($idLot, $idSection, $html) {
        $this->db->query("SELECT * FROM wbcc_section_lot_cb WHERE idSectionF = :idSection AND idLotF = :idLot");
        $this->db->bind(':idSection', $idSection);
        $this->db->bind(':idLot', $idLot);
        $res = $this->db->single();

        if ($res) {
            $this->db->query("UPDATE wbcc_section_lot_cb SET htmlVariable = :html WHERE idSectionF = :idSection AND idLotF = :idLot");
        } else {
            $this->db->query("INSERT INTO wbcc_section_lot_cb (idSectionF, idLotF, aAcquerir, htmlVariable) VALUES (:idSection, :idLot, 1, :html)");
        }
        $this->db->bind(':idSection', $idSection);
        $this->db->bind(':idLot', $idLot);
        $this->db->bind(':html', $html);
        return $this->db->execute();
    }

    public function getHtmlVariable($idSection, $idLot) {
        $this->db->query("SELECT * FROM wbcc_section_lot_cb WHERE idSectionF = :idSection AND idLotF = :idLot");
        $this->db->bind(':idSection', $idSection);
        $this->db->bind(':idLot', $idLot);
        $res = $this->db->single();
        return $res;
    }

    public function deleteSectionLotsToAcquire($idSection)
    {
        $this->db->query("DELETE FROM wbcc_section_lot_cb WHERE idSectionF = :idSection");
        $this->db->bind(':idSection', $idSection);
        return $this->db->execute();
    }

    public function insertSectionLotToAcquire($idSection, $idLot)
    {
        $this->db->query("
        INSERT INTO wbcc_section_lot_cb 
        (idSectionF, idLotF, aAcquerir) 
        VALUES (:idSection, :idLot, 1)
    ");
        $this->db->bind(':idSection', $idSection);
        $this->db->bind(':idLot', $idLot);
        return $this->db->execute();
    }

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


    public function  findAppConByLotCon($idContact, $idLot)
    {
        $this->db->query("SELECT * FROM  wbcc_appartement_contact
        WHERE idAppartementF = $idLot
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
        $idLot,
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
            WHERE idApp = $idLot");
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

    public function insertContactLot($idContact, $idLot)
    {
        $this->db->query("
           INSERT INTO wbcc_appartement_contact
           (idContactF, idAppartementF) 
           VALUES ($idContact,$idLot)");
        $this->db->execute();
    }


    public function insertCompanyLot($idCompany, $idLot)
    {
        $this->db->query("
           INSERT INTO wbcc_company_appartement 
           (idCompanyF, idAppartementF) 
           VALUES ($idCompany,$idLot)");
        $this->db->execute();
    }

    public function insertOpportunityAppartement($idOpp, $idLot)
    {
        $this->db->query("
           INSERT INTO wbcc_opportunity_appartement 
           (idOpportunityF, idAppartementF) 
           VALUES ($idOpp,$idLot)");
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