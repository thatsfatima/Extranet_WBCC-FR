<?php

class Document extends Model
{
    private $table = "wbcc_document";


    public function  getByEnveloppe($id)
    {
        $this->db->query("SELECT * FROM wbcc_document as doc, wbcc_enveloppe_document
        WHERE idDocument = idDocumentF AND idEnveloppeF = $id  ORDER BY doc.createDate DESC");

        return $this->db->resultSet();
    }

    public function save($numero, $nomFichier, $fichier, $auteur = '', $idUser = null, $guidUser = '', $urlDossier = "opportunite")
    {
        $connectedUser = Role::connectedUser();
        $idUser = $idUser ?: $connectedUser->idUtilisateur;
        $auteur = $auteur ?: ($connectedUser->nomContact . ' ' . $connectedUser->prenomContact);

        // error_log("Document save data: " . json_encode([
        //     'numero' => $numero,
        //     'nomFichier' => $nomFichier,
        //     'fichier' => $fichier,
        //     'auteur' => $auteur,
        //     'idUser' => $idUser,
        //     'guidUser' => $guidUser,
        //     'connectedUser' => $connectedUser,
        //     'date' => date('Y-m-d H:i:s')
        // ]));

        $extension = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));

        ini_set('upload_max_filesize', '1000M');
        ini_set('post_max_size', '1000M');
        ini_set('max_execution_time', 300);
        ini_set('max_input_time', 300);

        $date = date('Y-m-d H:i:s');
        $this->db->query("INSERT INTO wbcc_document(numeroDocument, nomDocument, urlDocument, urlDossier, createDate, idUtilisateurF, guidUser, source, auteur, publie, etatDocument) 
            VALUES(:numero, :nomFichier, :fichier, :urlDossier, :date, :idUser, :guidUser, 'EXTRA', :auteur, 0, 1)");

        $this->db->bind("numero", $numero);
        $this->db->bind("nomFichier", $nomFichier);
        $this->db->bind("fichier", $fichier);
        $this->db->bind("date", $date);
        $this->db->bind("idUser", $idUser);
        $this->db->bind("guidUser", $guidUser);
        $this->db->bind("auteur", $auteur);
        $this->db->bind("urlDossier", $urlDossier);

        if ($this->db->execute()) {
            $doc = $this->findByNumero($numero);
            return $doc->idDocument;
        }
        return "0";
    }

    public function addOpportunityDocument($idOp, $idDoc, $numOp, $numDoc)
    {
        $this->db->query("
           INSERT INTO wbcc_opportunity_document 
           (idOpportunityF, idDocumentF, numeroOpportunityF, numeroDocumentF ) 
           VALUES ($idOp,$idDoc, '$numOp', '$numDoc')");
        $this->db->execute();
    }

    public function  getByOpportunity($id, $type = "")
    {
        if ($type == "") {
            $this->db->query("SELECT * FROM wbcc_document as doc, wbcc_opportunity_document, wbcc_contact, wbcc_utilisateur 
            WHERE idDocument = idDocumentF AND idOpportunityF = $id AND publie=1 AND idContact = idContactF AND idUtilisateurF=idUtilisateur  ORDER BY doc.createDate DESC ");
        } else {
            $this->db->query("SELECT * FROM wbcc_document as doc, wbcc_opportunity_document
            WHERE idDocument = idDocumentF AND idOpportunityF = $id  ORDER BY doc.createDate DESC");
        }

        return $this->db->resultSet();
    }

    public function findByNumero($numero)
    {
        $this->db->query("SELECT * FROM wbcc_document WHERE numeroDocument = '$numero'");
        if ($this->db->single()) {
            return $this->db->single();
        } else {
            return null;
        }
    }


    public function getAllDocuments($search = null)
    {
        $sql = "SELECT * FROM {$this->table} 
            WHERE etatDocument = 1 
            AND source = 'EXTRA'";

        if ($search) {
            $sql .= " AND (nomDocument LIKE :search 
                 OR numeroDocument LIKE :search 
                 OR auteur LIKE :search)";
        }

        $sql .= " ORDER BY createDate DESC";

        $this->db->query($sql);

        if ($search) {
            $searchTerm = "%$search%";
            $this->db->bind("search", $searchTerm, null);
        }
        $this->db->resultSet();
        $datas = [];
        return $datas;
    }

    public function getAllDocumentsByIdImmeuble($nomTable, $idImmeuble = null)
    {
        $this->db->query("SELECT * FROM $nomTable WHERE idImmeuble = :id");
        $this->db->bind("id", $idImmeuble, null);
        $imm = $this->db->single();
        //GET OP BY IMMEUBLE
        $datas = [];

        if ($imm) {
            $sql = "SELECT d.* FROM wbcc_immeuble i, wbcc_opportunity_immeuble oi, wbcc_opportunity o, wbcc_opportunity_document od, wbcc_document d WHERE i.idImmeuble = oi.idImmeubleF AND oi.idOpportunityF = o.idOpportunity AND o.idOpportunity = od.idOpportunityF AND od.idDocumentF = d.idDocument AND ((i.adresse LIKE :adresse1 OR i.adresse LIKE :adresse2)  AND i.codePostal=:cp ) ";
            $this->db->query($sql);
            $this->db->bind("adresse1", "%$imm->adresse%", null);
            $this->db->bind("adresse2", "%$imm->nomImmeubleSyndic%", null);
            $this->db->bind("cp", $imm->codePostal, null);
            $datas = $this->db->resultSet();
        }

        return $datas;
    }

    public function findByName($name)
    {
        $this->db->query("SELECT * FROM wbcc_document WHERE urlDocument = :name");
        $this->db->bind("name", $name, null);
        if ($this->db->single()) {
            return $this->db->single();
        } else {
            return null;
        }
    }

    public function deleteDocument($id)
    {
        $this->db->query("UPDATE wbcc_document SET etatDocument=0 WHERE idDocument = $id");
        if ($this->db->execute()) {
            return 1;
        } else {
            return null;
        }
    }
}