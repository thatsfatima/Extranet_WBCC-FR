<?php


class Section extends Model
{
    private $table = "wbcc_section";

    public $idSection;
    public $titreSection;
    public $numeroSection;
    public $contenuSection;
    public $idSommaireF;
    public $idSection_parentF;
    public $contenuSectionTableur;
    public $typeContenu;

    public function getArticlesBySection($idSection, $idImmeuble, $idProjet, $type = '')
    {
        $articles = [];
        if ($type == "global") {
            $articles = $this->getLinesCCTPForImmeuble($idImmeuble, '', 'global');
        } else {
            $this->db->query("SELECT * FROM wbcc_section_table WHERE idSectionF = $idSection ORDER BY idTableSection");
            $articles = $this->db->resultSet();
        }

        return $articles;
    }

    public function getLinesCCTPForImmeuble($idImmeuble, $idProjet, $type = '')
    {
        $datas = [];
        //GET OP FOR AMO FOR IMMEUBLE
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_devis d, wbcc_opportunity_devis od WHERE o.idImmeuble=$idImmeuble AND o.type='A.M.O.' AND d.idDevis=od.idDevisF AND o.idOpportunity = od.idOpportunityF AND d.typeDevis='CCTP' AND od.valide=1 LIMIT 1; ");
        $opDevis =  $this->db->single();
        // echo json_encode("SELECT * FROM wbcc_opportunity o, wbcc_devis d, wbcc_opportunity_devis od WHERE o.idImmeuble=$idImmeuble AND o.type='A.M.O.' AND d.idDevis=od.idDevisF AND o.idOpportunity = od.idOpportunityF AND d.typeDevis='CCTP' AND od.valide=1 LIMIT 1; ");
        // die;
        if ($opDevis) {
            //GET sections
            $sections = $this->getSectionsByDevis($opDevis->idDevis);

            if (sizeof($sections) != 0) {
                $req = '';
                if ($type == "global") {
                    $req = "";
                } else {
                    $req = " AND projetIds NOT LIKE '%;$idProjet;%'";
                }
                foreach ($sections as $key => $sect) {
                    $this->db->query("SELECT * FROM wbcc_section_table WHERE idSectionF = $sect->idSection $req ORDER BY idTableSection");
                    $articles = $this->db->resultSet();
                    if (sizeof($articles) != 0) {
                        $datas = array_merge($datas, $articles);
                    }
                }
            }
        }
        return $datas;
    }


    public function getSectionsByDevis($idDevis)
    {
        $this->db->query("SELECT * FROM wbcc_section WHERE idDevisF=$idDevis");
        $result = $this->db->resultSet();
        return $result;
    }

    public function create($data)
    {
        // Vérifiez que les données requises sont présentes
        if (empty($data['titreSection']) || empty($data['idSommaireF'])) {
            return false;
        }

        // Utilisez des valeurs par défaut si certaines données ne sont pas fournies
        $data['contenuSection'] = $data['contenuSection'] ?? '';
        $data['idSection_parentF'] = $data['idSection_parentF'] ?? null;

        // Création de la requête SQL
        $this->db->query("INSERT INTO {$this->table} 
                         (titreSection, numeroSection, contenuSection, 
                         idSommaireF, idSection_parentF) 
                         VALUES 
                         (:titreSection, :numeroSection, :contenuSection, 
                         :idSommaireF, :idSection_parentF)");

        // Bind des valeurs 
        $this->db->bind(':titreSection', $data['titreSection']);
        $this->db->bind(':numeroSection', $data['numeroSection']);
        $this->db->bind(':contenuSection', $data['contenuSection']);
        $this->db->bind(':idSommaireF', $data['idSommaireF']);
        $this->db->bind(':idSection_parentF', $data['idSection_parentF']);

        // Exécution de la requête
        return $this->db->execute();
    }


    // Execute the query
    public function findSommaireById($sommaireId)
    {
        $this->db->query("SELECT * FROM wbcc_sommaire WHERE idSommaire = :idSommaire");
        $this->db->bind(':idSommaire', $sommaireId);
        return $this->db->single();
    }





    public function countSubSections($parentId)
    {
        $this->db->query("SELECT COUNT(*) as count 
                      FROM {$this->table} 
                      WHERE idSection_parentF = :parentId");
        $this->db->bind(':parentId', $parentId);
        $result = $this->db->single();
        return $result ? $result->count : 0;
    }

    public function getLastSectionNumber($sommaireId, $parentId = null)
    {
        $query = "SELECT MAX(CAST(numeroSection AS UNSIGNED)) as last_number 
              FROM {$this->table} 
              WHERE idSommaireF = :sommaireId";

        // Si un parent est spécifié, cherchez les sous-sections de ce parent
        if ($parentId !== null) {
            $query .= " AND idSection_parentF = :parentId";
        } else {
            // Sinon, cherchez les sections principales
            $query .= " AND (idSection_parentF IS NULL OR idSection_parentF = 0)";
        }

        $this->db->query($query);
        $this->db->bind(':sommaireId', $sommaireId);

        if ($parentId !== null) {
            $this->db->bind(':parentId', $parentId);
        }

        $result = $this->db->single();

        // Retourne 0 si aucune section n'existe encore
        return $result ? (int)$result->last_number : 0;
    }

    public function updateSection($id, $data)
    {
        try {
            $query = "UPDATE {$this->table} SET 
                      titreSection = :titreSection,
                      numeroSection = :numeroSection,
                      typeContenu = :typeContenu";

            // On ne met à jour que le contenu correspondant au type actif
            if ($data['typeContenu'] === 'tableur') {
                $query .= ", contenuSectionTableur = :contenuSectionTableur";
            } else {
                $query .= ", contenuSection = :contenuSection";
            }

            $query .= " WHERE idSection = :idSection";

            $this->db->query($query);

            $this->db->bind(':titreSection', $data['titreSection']);
            $this->db->bind(':numeroSection', $data['numeroSection']);
            $this->db->bind(':typeContenu', $data['typeContenu']);
            $this->db->bind(':idSection', $id);

            // Bind uniquement le champ correspondant au type actif
            if ($data['typeContenu'] === 'tableur') {
                $this->db->bind(':contenuSectionTableur', $data['contenuSectionTableur']);
            } else {
                $this->db->bind(':contenuSection', $data['contenuSection']);
            }

            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Erreur dans updateSection: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteWithChildren($id)
    {
        try {
            error_log("Début de deleteWithChildren pour l'ID: " . $id);

            // D'abord supprimer tous les enfants
            $this->db->query("SELECT idSection FROM {$this->table} WHERE idSection_parentF = :parentId");
            $this->db->bind(':parentId', $id);
            $children = $this->db->resultSet();

            // Supprimer récursivement chaque enfant
            foreach ($children as $child) {
                $this->deleteWithChildren($child->idSection);
            }

            // Enfin, supprimer la section elle-même
            $this->db->query("DELETE FROM {$this->table} WHERE idSection = :id");
            $this->db->bind(':id', $id);
            $result = $this->db->execute();

            error_log("Suppression réussie pour l'ID: " . $id);
            return $result;
        } catch (Exception $e) {
            error_log("Erreur dans deleteWithChildren: " . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $this->db->query("DELETE FROM {$this->table} WHERE idSection = :id");
            $this->db->bind(':id', $id);
            $result = $this->db->execute();

            if (!$result) {
                error_log("Échec de la suppression pour l'ID: " . $id);
            }

            return $result;
        } catch (Exception $e) {
            error_log("Erreur dans delete: " . $e->getMessage());
            throw $e;
        }
    }

    public function getSectionWithParent($id)
    {
        return $this->select("s.*, sp.titreSection as parentTitle")
            ->join("s, wbcc_section sp", "sp")
            ->where("s.idSection_parentF = sp.idSection")
            ->and("s.idSection = $id")
            ->doQuery();
    }

    public function updateMultiple($sections)
    {
        try {
            foreach ($sections as $section) {
                error_log("Mise à jour de la section ID: " . $section['idSection']);

                $data = [
                    'titreSection' => $section['titreSection'],
                    'numeroSection' => $section['numeroSection'],
                    'typeContenu' => $section['typeContenu']
                ];

                // N'inclure que le contenu correspondant au type actif
                if ($section['typeContenu'] === 'tableur') {
                    $data['contenuSectionTableur'] = $section['contenuSectionTableur'];
                } else {
                    $data['contenuSection'] = $section['contenuSection'];
                }

                if (!$this->updateSection($section['idSection'], $data)) {
                    error_log("Échec de la mise à jour pour la section ID: " . $section['idSection']);
                    return false;
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("Erreur dans updateMultiple: " . $e->getMessage());
            return false;
        }
    }
    
    public function getSectionBySommaireForRT($idSommaire)
    {
        try {
            $this->db->query("SELECT * FROM wbcc_section WHERE idSommaireF = :idSommaire ORDER BY CAST((REPLACE(numeroSection,'.', '')) AS int )");
            $this->db->bind(':idSommaire', $idSommaire);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Erreur dans getSectionBySommaireForRT: " . $e->getMessage());
            throw $e;
        }
    }

    public function getSectionsBySommaire($sommaireId)
    {
        try {
            $this->db->query("SELECT * FROM {$this->table} WHERE idSommaireF = :sommaireId ORDER BY CAST((REPLACE(numeroSection,'.', '')) AS int )");
            $this->db->bind(':sommaireId', $sommaireId);
            $result = $this->db->resultSet();
            $sections = [];
            foreach ($result as $key => $section) {
                $section->articles = [];
                $this->db->query("SELECT * FROM wbcc_section_table WHERE idSectionF = $section->idSection ORDER BY idTableSection");
                $articles = $this->db->resultSet();
                $section->articles = $articles;
                $sections[] = $section;
            }
            return $sections;
        } catch (Exception $e) {
            error_log("Erreur dans getSectionsBySommaire: " . $e->getMessage());
            throw $e;
        }
    }

    public function getSectionsForRT($idSommaire, $numeroSection, $titreSection, $contenuSection = "", $numeroParent = "")
    {
        try {
            $this->db->query("SELECT * FROM wbcc_section WHERE idSommaireF = :idSommaire AND numeroSection LIKE :numeroSection LIMIT 1");
            $this->db->bind(':idSommaire', $idSommaire);
            $this->db->bind(':numeroSection', $numeroSection);
            $section = $this->db->single();

            $idParent = "";
            if ($numeroParent && $numeroParent != "") {
                $this->db->query("SELECT * FROM wbcc_section WHERE idSommaireF = :idSommaire AND numeroSection LIKE :numeroParent LIMIT 1");
                $this->db->bind(':idSommaire', $idSommaire);
                $this->db->bind(':numeroParent', $numeroParent);
                $idParent = $this->db->single()->idSection ?? "";
            }

            if ($section) {
                $this->db->query("UPDATE wbcc_section SET numeroSection = :numeroSection, titreSection = :titreSection, contenuSection = :contenuSection, idSection_parentF = :idSectionParent WHERE idSection = :idSection");
                $this->db->bind(':idSection', $section->idSection);
                $this->db->bind(':numeroSection', $numeroSection);
                $this->db->bind(':titreSection', $titreSection);
                $this->db->bind(':contenuSection', $contenuSection);
                $this->db->bind(':idSectionParent', $idParent);
                return $this->db->execute();
            } else {
                $this->db->query("INSERT INTO wbcc_section (idSommaireF, numeroSection, titreSection, contenuSection, idSection_parentF) VALUES (:idSommaire, :numeroSection, :titreSection, :contenuSection, :idSectionParent)");
                $this->db->bind(':idSommaire', $idSommaire);
                $this->db->bind(':numeroSection', $numeroSection);
                $this->db->bind(':titreSection', $titreSection);
                $this->db->bind(':contenuSection', $contenuSection);
                $this->db->bind(':idSectionParent', $idParent);
                return $this->db->execute();
            }
        } catch (Exception $e) {
            error_log("Erreur dans getSectionsForRT: " . $e->getMessage());
            throw $e;
        }
    }

    public function getChildSections($parentId)
    {
        return $this->select()
            ->where("idSection_parentF = $parentId")
            ->doQuery();
    }

    public function linkDocument($sectionId, $documentId)
    {
        try {
            // Logs détaillés
            error_log("Début de linkDocument");
            error_log("Section ID: $sectionId");
            error_log("Document ID: $documentId");

            // Vérification complète du document
            $this->db->query("SELECT * FROM wbcc_document WHERE idDocument = :documentId");
            $this->db->bind(':documentId', $documentId);
            $document = $this->db->single();

            // Log du résultat de recherche du document
            if (!$document) {
                error_log("ERREUR : Document introuvable");
                error_log("Requête : SELECT * FROM wbcc_document WHERE idDocument = $documentId");
                return false;
            }

            // Log des détails du document
            error_log("Détails du document : " . json_encode($document));

            // Vérification de la section
            $this->db->query("SELECT * FROM wbcc_section WHERE idSection = :sectionId");
            $this->db->bind(':sectionId', $sectionId);
            $section = $this->db->single();

            // Log du résultat de recherche de la section
            if (!$section) {
                error_log("ERREUR : Section introuvable");
                error_log("Requête : SELECT * FROM wbcc_section WHERE idSection = $sectionId");
                return false;
            }

            // Log des détails de la section
            error_log("Détails de la section : " . json_encode($section));

            // Vérification de l'existence du lien
            $this->db->query("SELECT COUNT(*) as count FROM wbcc_section_document 
                              WHERE idSectionF = :sectionId AND idDocumentF = :documentId");
            $this->db->bind(':sectionId', $sectionId);
            $this->db->bind(':documentId', $documentId);
            $linkExists = $this->db->single();

            // Log de l'existence du lien
            error_log("Nombre de liens existants : " . $linkExists->count);

            // Si le lien existe déjà, retourner true
            if ($linkExists->count > 0) {
                error_log("Lien déjà existant");
                return true;
            }

            // Insertion du lien
            $this->db->query("INSERT INTO wbcc_section_document 
                              (idSectionF, idDocumentF, numeroDocument) 
                              VALUES (:sectionId, :documentId, :numeroDocument)");

            $this->db->bind(':sectionId', $sectionId);
            $this->db->bind(':documentId', $documentId);
            $this->db->bind(':numeroDocument', $document->numeroDocument);

            $result = $this->db->execute();

            // Log du résultat de l'insertion
            if ($result) {
                error_log("Liaison réussie");
                return true;
            } else {
                error_log("ERREUR : Échec de l'insertion du lien");
                return false;
            }
        } catch (Exception $e) {
            // Log de l'exception
            error_log("EXCEPTION FATALE : " . $e->getMessage());
            error_log("Trace : " . $e->getTraceAsString());
            return false;
        }
    }

    // 
    public function getDocuments($sectionId)
    {
        $this->db->query("SELECT d.* 
                      FROM wbcc_document d
                      JOIN wbcc_section_document sd ON d.idDocument = sd.idDocumentF
                      WHERE sd.idSectionF = :sectionId 
                      AND d.etatDocument = 1
                      ORDER BY d.createDate DESC");

        $this->db->bind(':sectionId', $sectionId);

        return $this->db->resultSet();
    }

    public function getSectionsParentBySommaire($sommaireId)
    {
        try {
            // $this->db->query("SELECT * FROM {$this->table} WHERE idSommaireF = :sommaireId AND idSection_ParentF IS NULL ORDER BY numeroSection");
            $this->db->query("SELECT * FROM {$this->table} WHERE idSommaireF = :sommaireId AND idSection_ParentF IS NULL ORDER BY CAST((REPLACE(numeroSection,'.', '')) AS int ) ");
            $this->db->bind(':sommaireId', $sommaireId);
            $result = $this->db->resultSet();
            return $result;
        } catch (Exception $e) {
            error_log("Erreur dans getSectionsBySommaire: " . $e->getMessage());
            throw $e;
        }
    }

    public function getSectionsByParent($idSection)
    {
        try {
            $this->db->query("SELECT * FROM {$this->table} WHERE idSection_parentF = :idSection ORDER BY CAST((REPLACE(numeroSection,'.', '')) AS INT) ");
            $this->db->bind(':idSection', $idSection);

            $result = $this->db->resultSet();
            return $result;
        } catch (Exception $e) {
            error_log("Erreur dans getSectionsBySommaire: " . $e->getMessage());
            throw $e;
        }
    }

    public function unlinkDocument($sectionId, $documentId)
    {
        try {
            $this->db->query("DELETE FROM wbcc_section_document 
                          WHERE idSectionF = :sectionId 
                          AND idDocumentF = :documentId");

            $this->db->bind(':sectionId', $sectionId);
            $this->db->bind(':documentId', $documentId);

            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Erreur dans unlinkDocument: " . $e->getMessage());
            return false;
        }
    }

    public function getDocumentsBySommaire($sommaireId)
    {
        try {
            $this->db->query("SELECT d.* FROM wbcc_document d JOIN wbcc_section_document sd ON d.idDocument = sd.idDocumentF JOIN wbcc_section s ON sd.idSectionF = s.idSection WHERE s.idSommaireF = :sommaireId");
            $this->db->bind(':sommaireId', $sommaireId);
            $result = $this->db->resultSet();
            return $result;
        } catch (Exception $e) {
            error_log("Erreur dans getDocumentsBySommaire: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateMultipleSections($sections)
    {
        try {
            foreach ($sections as $section) {
                $query = "UPDATE {$this->table} SET 
                          idSection_parentF = :parentId,
                          numeroSection = :numeroSection
                          WHERE idSection = :idSection";

                $this->db->query($query);

                // Bind des paramètres
                $this->db->bind(':parentId', $section['idSection_parentF']);
                $this->db->bind(':numeroSection', $section['numeroSection']);
                $this->db->bind(':idSection', $section['idSection']);

                if (!$this->db->execute()) {
                    error_log("Échec de la mise à jour pour la section ID: " . $section['idSection']);
                    return false;
                }
            }

            return true;
        } catch (Exception $e) {
            error_log("Erreur dans updateMultipleSections: " . $e->getMessage());
            return false;
        }
    }

    public function updateSectionAction($sectionId, $action, $contenuSection)
    {
        try {

            $this->db->query("UPDATE {$this->table} 
                             SET action = :action,
                                 contenuSection = :contenuSection 
                             WHERE idSection = :sectionId");

            $this->db->bind(':action', $action);
            $this->db->bind(':contenuSection', $contenuSection);
            $this->db->bind(':sectionId', $sectionId);

            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Erreur dans updateSectionAction: " . $e->getMessage());
            return false;
        }
    }
}