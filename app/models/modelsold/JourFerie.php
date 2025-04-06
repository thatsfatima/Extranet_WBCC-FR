<?php

class JourFerie extends Model
{
    public function getAllJoursFeries($idSite, $annee)
    {
        $this->db->query("SELECT * FROM wbcc_jour_ferie WHERE idSiteF = $idSite AND anneeJourFerie = $annee ORDER BY dateJourFerie ASC");
        $res = $this->db->resultSet();
        return $res;
    }

    public function findJourFerieByColumnValue($column, $value)
    {
        $this->db->query("SELECT * FROM wbcc_jour_ferie WHERE $column = :value LIMIT 1");
        $this->db->bind("value", $value);
        $res = $this->db->single();
        return $res;
    }

    /**
     * Supprime un jour ferie par son ID
     * @param int $id l'ID du jour ferie à supprimer
     * @return bool true si la suppression a réussi, false sinon
     */
    public function deleteJourFerieById($id)
    {
        // Suppression d'un jour ferie
        $this->db->query("DELETE FROM wbcc_jour_ferie WHERE idJourFerie = :idJourFerie");
        $this->db->bind("idJourFerie", $id);
        return $this->db->execute();
    }

    /**
     * Enregistre un jour ferie dans la base de données
     *
     * @param int|null $idJourFerie L'identifiant du jour ferie (null pour un nouveau jour ferie)
     * @param string $nomJourFerie Le nom du jour ferie
     * @param string $dateJourFerie La description du jour ferie
     * @param int $idSiteF L'identifiant de l'immeuble associé
     * @param int $anneeJourFerie L'identifiant de l'utilisateur
     * @return mixed L'objet jour ferie si l'enregistrement réussit, false sinon
     */
    public function saveJourFerie($nomJourFerie, $dateJourFerie, $anneeJourFerie, $idSiteF, $Payer, $Chomer, $idJourFerie = null)
    {
        // Vérifie s'il existe un jour férié avec le même nom dans l'année en cours
        $this->db->query("SELECT COUNT(*) as count FROM wbcc_jour_ferie WHERE anneeJourFerie = :anneeJourFerie AND nomJourFerie = :nomJourFerie AND idSiteF = :idSiteF");
        $this->db->bind("anneeJourFerie", $anneeJourFerie);
        $this->db->bind("idSiteF", $idSiteF);
        $this->db->bind("nomJourFerie", $nomJourFerie);
        $res = $this->db->single();
        // Mise à jour ou insertion du jour ferie dans la base de données
        if ($idJourFerie == null || $idJourFerie == "" || $idJourFerie == "0" || $res->count != 0) {
            $this->db->query("INSERT INTO wbcc_jour_ferie(nomJourFerie, dateJourFerie, anneeJourFerie, idSiteF, Payer, Chomer) VALUES (:nomJourFerie, :dateJourFerie, :anneeJourFerie, :idSiteF, :Payer, :Chomer)");
        } else {
            $this->db->query("UPDATE wbcc_jour_ferie SET nomJourFerie=:nomJourFerie, dateJourFerie=:dateJourFerie, idSiteF=:idSiteF, anneeJourFerie=:anneeJourFerie, Payer=:Payer, Chomer=:Chomer WHERE idJourFerie=:idJourFerie");
            $this->db->bind("idJourFerie", $idJourFerie ?? $res->idJourFerie);
        }

        $this->db->bind("nomJourFerie", $nomJourFerie);
        $this->db->bind("dateJourFerie", $dateJourFerie);
        $this->db->bind("anneeJourFerie", $anneeJourFerie);
        $this->db->bind("idSiteF", $idSiteF);
        $this->db->bind("Payer", $Payer);
        $this->db->bind("Chomer", $Chomer);

        $jourFerie = $this->db->execute();
        // Exécute la requête et retourne le jour ferie enregistré ou false en cas d'échec
        if ($jourFerie) {
            if ($idJourFerie != null && $idJourFerie != "" && $idJourFerie != "0") {
                return $this->findJourFerieByColumnValue("idJourFerie", $idJourFerie);
            } else {
                return $this->findJourFerieByColumnValue("nomJourFerie", $nomJourFerie);
            }
        } else {
            return false;
        }
    }
}