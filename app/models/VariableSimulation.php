<?php

class variableSimulation extends Model
{
    private $table = "wbcc_variable_simulation";

    // Récupérer toutes les variables de simulation
    public function getAllVariables()
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE aSaisir = 1 ORDER BY categorie, idVariableSimulation");
        return $this->db->resultSet();
    }

    public function getVariableValuesForSection($sectionId, $lotId)
    {
        // Vérifier si la section et le lot spécifique ont des variables dans la table d'association
        $this->db->query("SELECT COUNT(*) as count FROM wbcc_appartement_variable_simulation 
                          WHERE idSectionF = :sectionId AND idAppartementF = :lotId");
        $this->db->bind("sectionId", $sectionId);
        $this->db->bind("lotId", $lotId);
        $hasAssociations = $this->db->single()->count > 0;

        if (!$hasAssociations) {
            // Récupérer toutes les variables à associer
            $this->db->query("SELECT * FROM {$this->table} WHERE aSaisir = 1 ORDER BY categorie, idVariableSimulation");
            $variables = $this->db->resultSet();

            // Insérer les associations pour ce lot et cette section
            foreach ($variables as $variable) {
                $this->saveVariableValue($sectionId, $lotId, $variable->idVariableSimulation, $variable->valeurVariableSimulation);
            }
        }

        // Retourner les variables associées pour ce lot et cette section
        $this->db->query("
            SELECT 
                vs.*,
                lvs.montant,
                lvs.idAppartementF as lotId
            FROM wbcc_appartement_variable_simulation lvs
            JOIN {$this->table} vs ON vs.idVariableSimulation = lvs.idVariableSimulationF
            WHERE lvs.idSectionF = :sectionId 
            AND lvs.idAppartementF = :lotId
            AND vs.aSaisir = 1
            ORDER BY vs.categorie, vs.idVariableSimulation
        ");

        $this->db->bind("sectionId", $sectionId);
        $this->db->bind("lotId", $lotId);
        return $this->db->resultSet();
    }

    // Sauvegarder une valeur pour une section
    public function saveVariableValue($sectionId, $lotId, $variableId, $montant)
    {
        // Vérifier si une entrée existe déjà pour ce lot spécifique
        $this->db->query("SELECT idTableauVariable FROM wbcc_appartement_variable_simulation 
        WHERE idSectionF = :sectionId 
        AND idAppartementF = :lotId
        AND idVariableSimulationF = :variableId");

        $this->db->bind("sectionId", $sectionId);
        $this->db->bind("lotId", $lotId);
        $this->db->bind("variableId", $variableId);
        $existing = $this->db->single();

        if ($existing) {
            // Mise à jour pour ce lot spécifique
            $this->db->query("UPDATE wbcc_appartement_variable_simulation 
            SET montant = :montant 
            WHERE idTableauVariable = :id");

            $this->db->bind("montant", $montant);
            $this->db->bind("id", $existing->idTableauVariable);
        } else {
            // Pour une nouvelle entrée, récupérer d'abord valeurVariableSimulation
            $this->db->query("SELECT * FROM {$this->table} 
            WHERE idVariableSimulation = :variableId");
            $this->db->bind("variableId", $variableId);
            $variable = $this->db->single();

            // Utiliser valeurVariableSimulation comme montant initial si disponible
            $initialMontant = $variable ? $variable->valeurVariableSimulation : $montant;

            //RECUPERER SURFACE EXISTANT
            if ($variable->libelleVariable == "surface") {
                $this->db->query("SELECT * FROM wbcc_appartement
                WHERE idApp = :idLot");
                $this->db->bind("idLot", $lotId);
                $lot = $this->db->single();
                $initialMontant = $lot && $lot->surface != null && $lot->surface != "" ? $lot->surface : 0;
            }

            // Nouvelle entrée pour ce lot
            $this->db->query("INSERT INTO wbcc_appartement_variable_simulation 
            (idSectionF, idAppartementF, idVariableSimulationF, montant) 
            VALUES (:sectionId, :lotId, :variableId, :montant)");

            $this->db->bind("sectionId", $sectionId);
            $this->db->bind("lotId", $lotId);
            $this->db->bind("variableId", $variableId);
            $this->db->bind("montant", $initialMontant);
        }

        return $this->db->execute();
    }


    public function createVariableAndAssociation($sectionId, $lotId, $montant, $nom, $categorie, $type, $formuleCoutTotal = null)
    {
        // Générer un libellé en fonction du type
        $libelleVariable = strtolower($nom);

        // Définir la formule de coût total si pas de catégorie, pas de formule par défaut
        if ($categorie === null) {
            $formuleCoutTotal = null;
        } else {
            $formuleCoutTotal = $formuleCoutTotal ?: ($type === 'montant' ? $libelleVariable . '*surface' : $libelleVariable . '*capitalEmprunter');
        }

        // Vérifier si la variable existe déjà
        $this->db->query("SELECT idVariableSimulation FROM {$this->table} 
            WHERE nomVariableSimulation = :nom 
            AND ((:categorie IS NULL AND categorie IS NULL) OR categorie = :categorie)
            LIMIT 1");

        $this->db->bind("nom", $nom);
        $this->db->bind("categorie", $categorie);

        $existingVariable = $this->db->single();
        $variableId = null;

        if ($existingVariable) {
            // Si la variable existe, utiliser son ID
            $variableId = $existingVariable->idVariableSimulation;
        } else {
            // Préparer les formules en fonction de la catégorie
            $formules = $categorie === null ? [
                'formuleCoutTotal' => null,
                'formulePourcentageCoutTotal' => null,
                'formuleCoutIndividuel' => null,
                'formulePourcentageCoutIndividuel' => null
            ] : [
                'formuleCoutTotal' => $formuleCoutTotal,
                'formulePourcentageCoutTotal' => 'coutTotal/totalCategorie*100',
                'formuleCoutIndividuel' => 'coutTotal/surface',
                'formulePourcentageCoutIndividuel' => 'coutTotal/prixReventeTotal*100'
            ];

            // Si la variable n'existe pas, l'insérer
            $this->db->query("INSERT INTO {$this->table} 
                (nomVariableSimulation, 
                libelleVariable,
                nomAfficher,
                typeValeurSimulation, 
                valeurVariableSimulation,
                formuleCoutTotal,
                formulePourcentageCoutTotal,
                formuleCoutIndividuel,
                formulePourcentageCoutIndividuel,
                categorie, 
                aSaisir) 
                VALUES 
                (:nom,
                :libelleVariable,
                :nomAfficher,
                :type,
                :montant,
                :formuleCoutTotal,
                :formulePourcentageCoutTotal,
                :formuleCoutIndividuel,
                :formulePourcentageCoutIndividuel,
                :categorie,
                1)");

            $this->db->bind("nom", $nom);
            $this->db->bind("libelleVariable", $libelleVariable);
            $this->db->bind("nomAfficher", $nom);
            $this->db->bind("type", $type);
            $this->db->bind("montant", $montant);
            $this->db->bind("formuleCoutTotal", $formules['formuleCoutTotal']);
            $this->db->bind("formulePourcentageCoutTotal", $formules['formulePourcentageCoutTotal']);
            $this->db->bind("formuleCoutIndividuel", $formules['formuleCoutIndividuel']);
            $this->db->bind("formulePourcentageCoutIndividuel", $formules['formulePourcentageCoutIndividuel']);
            $this->db->bind("categorie", $categorie);

            if (!$this->db->execute()) {
                return false;
            }

            // Récupérer l'ID de la variable créée
            $this->db->query("SELECT idVariableSimulation FROM {$this->table} 
                WHERE nomVariableSimulation = :nom 
                AND ((:categorie IS NULL AND categorie IS NULL) OR categorie = :categorie)
                ORDER BY idVariableSimulation DESC LIMIT 1");

            $this->db->bind("nom", $nom);
            $this->db->bind("categorie", $categorie);

            $result = $this->db->single();
            if (!$result) {
                return false;
            }

            $variableId = $result->idVariableSimulation;
        }

        // Insérer dans la table d'association
        $this->db->query("INSERT INTO wbcc_appartement_variable_simulation 
            (idSectionF, idAppartementF, idVariableSimulationF, montant) 
            VALUES (:sectionId, :lotId, :variableId, :montant)");

        $this->db->bind("sectionId", $sectionId);
        $this->db->bind("lotId", $lotId);
        $this->db->bind("variableId", $variableId);
        $this->db->bind("montant", $montant);

        return $this->db->execute();
    }

    public function getAllLibellesVariables()
    {
        // Sélectionner uniquement les champs nécessaires pour les libellés
        $this->db->query("
        SELECT 
            idVariableSimulation,
            nomVariableSimulation,
            libelleVariable,
            typeValeurSimulation,
            categorie
        FROM {$this->table}
        WHERE libelleVariable IS NOT NULL
          AND aSaisir = 1
        ORDER BY categorie, idVariableSimulation
    ");
        return $this->db->resultSet();
    }
}