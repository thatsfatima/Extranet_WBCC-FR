<?php

class Projet extends Model
{
    public function getProjets()
    {
        $this->db->query("SELECT * FROM wbcc_projet");
        $res = $this->db->resultSet();
        return $res;
    }

    public function findProjetByColumnValue($column, $value)
    {
        $this->db->query("SELECT * FROM wbcc_projet WHERE $column = :value LIMIT 1");
        $this->db->bind("value", $value);
        $res = $this->db->single();
        return $res;
    }

    /**
     * Supprime un projet par son ID
     * @param int $id l'ID du projet à supprimer
     * @return bool true si la suppression a réussi, false sinon
     */
    public function deleteProjetById($id)
    {
        // Suppression d'un projet
        $this->db->query("DELETE FROM wbcc_projet WHERE idProjet = :idProjet");
        $this->db->bind("idProjet", $id);
        return $this->db->execute();
    }

    /**
     * Enregistre un projet dans la base de données
     *
     * @param int|null $idProjet L'identifiant du projet (null pour un nouveau projet)
     * @param string $nomProjet Le nom du projet
     * @param string $descriptionProjet La description du projet
     * @param int $idImmeuble L'identifiant de l'immeuble associé
     * @param int $idUser L'identifiant de l'utilisateur
     * @return mixed L'objet projet si l'enregistrement réussit, false sinon
     */
    public function saveProjet($idProjet, $nomProjet, $descriptionProjet, $idImmeuble, $idUser)
    {
        // Vérifie si un projet avec le même nom existe déjà
        $this->db->query("SELECT COUNT(*) as count FROM wbcc_projet WHERE nomProjet = :nomProjet AND idProjet != :idProjet");
        $this->db->bind("nomProjet", $nomProjet);
        $this->db->bind("idProjet", $idProjet);
        $res = $this->db->single();

        // Si un projet avec le même nom existe, renvoie false
        if ($res->count > 0) {
            return false;
        }

        // Vérifie si l'identifiant d'immeuble est fourni
        if (empty($idImmeuble)) {
            return false;
        }

        // Génère un numéro de projet unique
        $numeroProjet = "PROJ" . date("dmYHis") . $idUser;

        // Mise à jour ou insertion du projet dans la base de données
        if ($idProjet != null && $idProjet != "" && $idProjet != "0") {
            $this->db->query("UPDATE wbcc_projet SET nomProjet=:nomProjet, descriptionProjet=:descriptionProjet, idImmeubleCB=:idImmeubleCB, numeroProjet=:numeroProjet WHERE idProjet=:idProjet");
            $this->db->bind("idProjet", $idProjet);
        } else {
            $this->db->query("INSERT INTO wbcc_projet(nomProjet, descriptionProjet, idImmeubleCB, createDate, numeroProjet) VALUES (:nomProjet, :descriptionProjet, :idImmeubleCB, :createDate, :numeroProjet)");
            $this->db->bind("createDate", date("Y-m-d H:i:s"));
        }

        // Ajout des bindings communs
        $this->db->bind("nomProjet", $nomProjet);
        $this->db->bind("descriptionProjet", $descriptionProjet);
        $this->db->bind("idImmeubleCB", $idImmeuble);
        $this->db->bind("numeroProjet", $numeroProjet);

        // Exécute la requête et retourne le projet enregistré ou false en cas d'échec
        if ($this->db->execute()) {
            if ($idProjet != null && $idProjet != "" && $idProjet != "0") {
                return $this->findProjetByColumnValue("idProjet", $idProjet);
            } else {
                return $this->findProjetByColumnValue("nomProjet", $nomProjet);
            }
        } else {
            return false;
        }
    }
}