<?php

class Devis extends Model
{
    public function findDevisById($idDevis)
    {
        $this->db->query("SELECT * FROM wbcc_devis  WHERE idDevis = $idDevis ");
        return $this->db->single();
    }

    public function getOpDevis($idOP)
    {
        $this->db->query("SELECT * FROM wbcc_devis, wbcc_opportunity_devis, wbcc_utilisateur,  wbcc_contact  WHERE wbcc_opportunity_devis.idDevisF = wbcc_devis.idDevis AND wbcc_utilisateur.idUtilisateur = wbcc_devis.idUserF  AND wbcc_contact.idContact = wbcc_utilisateur.idContactF AND wbcc_opportunity_devis.idOpportunityF = $idOP ");
        return $this->db->single();
    }

    public function getOpDevisValide($idOP)
    {
        $this->db->query("SELECT * FROM wbcc_devis JOIN wbcc_opportunity_devis ON wbcc_opportunity_devis.idDevisF = wbcc_devis.idDevis JOIN wbcc_utilisateur ON wbcc_utilisateur.idUtilisateur = wbcc_devis.idUserF  JOIN wbcc_contact ON wbcc_contact.idContact = wbcc_utilisateur.idContactF WHERE wbcc_opportunity_devis.idOpportunityF = $idOP and wbcc_opportunity_devis.valide = 1");
        return $this->db->single();
    }


    public function findLastSituation($idOP)
    {
        $this->db->query("SELECT * FROM wbcc_situation_devis WHERE idOpportunityF = $idOP  ORDER BY idSituationDevis DESC LIMIT 1");
        return $this->db->single();
    }

    public function saveDevisOP($idOP, $type, $idUser, $idArchi, $idSocieteArchi, $nomArchi, $existCCTP, $existRemise, $typeRemise, $tauxRemise)
    {
        $numero = "Devis_" . date("YmdHis") . "$idOP$idUser";

        $this->db->query("INSERT INTO wbcc_devis(numeroDevis,typeDevis,idUserF, idArchitecteF, idSocieteArchi,architecte,existDevis, existRemise, typeRemise, tauxRemise) VALUES ('$numero','$type',$idUser,$idArchi,$idSocieteArchi,:nomArchi,'$existCCTP', '$existRemise', '$typeRemise', '$tauxRemise')");
        $this->db->bind("nomArchi", $nomArchi, null);

        if ($this->db->execute()) {
            $devis = findItemByColumn("wbcc_devis", "numeroDevis", $numero);
            $this->db->query("INSERT INTO `wbcc_opportunity_devis`(`idOpportunityF`, `idDevisF`) VALUES (:idOpportunityF,:idDevisF)");
            $this->db->bind("idOpportunityF", $idOP, null);
            $this->db->bind("idDevisF", $devis->idDevis, null);
            if ($this->db->execute()) {
                return $devis;
            } else {
                return false;
            }
        }
    }
}