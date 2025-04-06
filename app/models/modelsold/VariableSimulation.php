<?php

class VariableSimulation extends Model
{
    private $table = "wbcc_variable_simulation";

    // Récupérer toutes les variables de simulation
    public function getAllVariables()
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE aSaisir = 1 ORDER BY categorie, idVariableSimulation");
        return $this->db->resultSet();
    }

    // Récupérer les valeurs pour une section spécifique
    public function getVariableValuesForSection($sectionId, $lotId)
    {
        $this->db->query("
            SELECT 
                vs.*,
                lvs.montant,
                lvs.idLotCBF as lotId
            FROM {$this->table} vs
            LEFT JOIN wbcc_lot_variable_simulation lvs 
                ON vs.idVariableSimulation = lvs.idVariableSimulationF 
                AND lvs.idSectionF = :sectionId
                AND lvs.idLotCBF = :lotId
            WHERE vs.aSaisir = 1
            ORDER BY vs.categorie, vs.idVariableSimulation
        ");

        $this->db->bind("sectionId", $sectionId);
        $this->db->bind("lotId", $lotId);
        return $this->db->resultSet();
    }

    // Sauvegarder une valeur pour une section
    public function saveVariableValue($sectionId, $lotId, $variableId, $montant)
    {
        // Vérifier si une entrée existe déjà
        $this->db->query("SELECT idTableauVariable FROM wbcc_lot_variable_simulation
            WHERE idSectionF = :sectionId 
            AND idVariableSimulationF = :variableId");

        $this->db->bind("sectionId", $sectionId);
        $this->db->bind("variableId", $variableId);
        $existing = $this->db->single();

        if ($existing) {
            // Mise à jour
            $this->db->query("UPDATE wbcc_lot_variable_simulation 
                SET montant = :montant 
                WHERE idTableauVariable = :id");

            $this->db->bind("montant", $montant);
            $this->db->bind("id", $existing->idTableauVariable);
        } else {
            // Nouvelle entrée
            $this->db->query("INSERT INTO wbcc_lot_variable_simulation 
                (idSectionF, idLotCBF, idVariableSimulationF, montant) 
                VALUES (:sectionId, :lotId, :variableId, :montant)");

            $this->db->bind("sectionId", $sectionId);
            $this->db->bind("lotId", $lotId);
            $this->db->bind("variableId", $variableId);
            $this->db->bind("montant", $montant);
        }

        return $this->db->execute();
    }

    public function CalculateTotal($formule, $arrayReplace)
    {
        foreach ($arrayReplace as $key => $value) {
            $formule = str_replace($key, $value, $formule);
        }
        return floatval($formule);
    }
}
