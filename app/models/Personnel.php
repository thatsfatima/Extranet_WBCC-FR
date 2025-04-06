<?php

class Personnel extends Model
{
    public function findById($id)
    {
        $this->db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur  WHERE idContact=idContactF AND idUtilisateur = $id LIMIT 1");
        return $this->db->single();
    }

    public function findContactById($id)
    {
        $this->db->query("SELECT * FROM wbcc_contact  WHERE idContact = $id LIMIT 1");
        return $this->db->single();
    }

    public function  getContactByCompany($id)
    {

        $this->db->query("SELECT * FROM wbcc_contact c, wbcc_contact_company cc 
        WHERE c.idContact = cc.idContactF 
        AND cc.idCompanyF = $id AND etatContact=1");
        return $this->db->resultSet();
    }

    //Les contacts de tous les lots de la société
    public function  getContactByCompanyAndLot($id)
    {
        $this->db->query("SELECT * FROM wbcc_contact c, wbcc_company_appartement ca, wbcc_appartement_contact ac 
        WHERE c.idContact = ac.idContactF 
        AND ca.idCompanyF = $id AND ca.idAppartementF=ac.idAppartementF AND  etatContact=1");
        return $this->db->resultSet();
    }

    public function  getPersonnelByCompany($id)
    {

        $this->db->query("SELECT * FROM wbcc_contact c, wbcc_contact_company cc 
        WHERE c.idContact = cc.idContactF 
        AND cc.idCompanyF = $id AND etatContact=1 AND isPersonnel=1");
        return $this->db->resultSet();
    }

    public function  getContactByOpportunity($id)
    {

        $this->db->query("SELECT * FROM wbcc_contact, wbcc_contact_opportunity
        WHERE idContact = idContactF 
        AND idOpportunityF =:id AND etatContact=1");

        $this->db->bind("id", $id, null);
        return $this->db->resultSet();
    }

    public function addContact(
        $numero,
        $civilite,
        $nom,
        $prenom,
        $tel1,
        $tel2,
        $tel3,
        $email,
        $emailC,
        $adresse1,
        $adresse2,
        $cp,
        $ville,
        $dpt,
        $region,
        $porte,
        $batiment,
        $etage,
        $titre,
        $service,
        $role,
        $isPersonnel,
        $companyName = ''
    ) {
        $fullName = $prenom . " " . $nom;
        $this->db->query("
           INSERT INTO wbcc_contact 
           (numeroContact, civiliteContact, prenomContact, nomContact, fullName, telContact, 
           mobilePhone,faxPhone, emailContact, emailCollaboratif, adresseContact, businessLine2, codePostalContact, 
           villeContact, departement, businessState, codePorte, batiment, etage, jobTitle, 
           service, statutContact, editDate, createDate, isPersonnel, companyName, category) 
           VALUES (:numero,:civilite, :prenom, :nom,:fullName,:tel1, :tel2, :tel3, :email, :emailC,  :adresse1, 
           :adresse2, :cp, :ville, :dpt, :region, :porte, :batiment, :etage, :titre, :service, :role, :editDate, :createDate, :isPersonnel, :companyName, :role)");

        $this->db->bind("numero", $numero, null);
        $this->db->bind("civilite", $civilite, null);
        $this->db->bind("prenom", $prenom, null);
        $this->db->bind("nom", $nom, null);
        $this->db->bind("fullName", $fullName, null);
        $this->db->bind("tel1", $tel1, null);
        $this->db->bind("tel2", $tel2, null);
        $this->db->bind("tel3", $tel3, null);
        $this->db->bind("email", $email, null);
        $this->db->bind("emailC", $emailC, null);
        $this->db->bind("adresse1", $adresse1, null);
        $this->db->bind("adresse2", $adresse2, null);
        $this->db->bind("cp", $cp, null);
        $this->db->bind("ville", $ville, null);
        $this->db->bind("dpt", $dpt, null);
        $this->db->bind("region", $region, null);
        $this->db->bind("porte", $porte, null);
        $this->db->bind("batiment", $batiment, null);
        $this->db->bind("etage", $etage, null);
        $this->db->bind("titre", $titre, null);
        $this->db->bind("service", $service, null);
        $this->db->bind("role", $role, null);
        $this->db->bind("editDate", date("Y-m-d h:i:s"), null);
        $this->db->bind("createDate", date("Y-m-d h:i:s"), null);
        $this->db->bind("isPersonnel", $isPersonnel, null);
        $this->db->bind("companyName", $companyName, null);

        if ($this->db->execute()) {
            $contact = $this->findByNumero($numero);
            return $contact->idContact;
        } else {
            return "0";
        }
    }

    public function updateContact(
        $id,
        $civilite,
        $nom,
        $prenom,
        $tel1,
        $tel2,
        $tel3,
        $email,
        $emailC,
        $adresse1,
        $adresse2,
        $cp,
        $ville,
        $dpt,
        $region,
        $porte,
        $batiment,
        $etage,
        $titre,
        $service,
        $role,
        $companyName = ''
    ) {
        $fullName = $prenom . " " . $nom;
        if (isset($role)) {
            $this->db->query("UPDATE wbcc_contact SET civiliteContact = :civilite, prenomContact= :prenom,nomContact=:nom, fullName = :fullName, telContact = :tel1, mobilePhone= :tel2, faxPhone= :tel3, emailContact= :email, emailCollaboratif= :emailC, adresseContact = :adresse1, businessLine2 = :adresse2, codePostalContact = :cp, villeContact= :ville, departement= :dpt, businessState = :region, codePorte= :porte, batiment= :batiment, etage = :etage, jobTitle = :titre, service= :service, statutContact= :role, editDate= :editDate, companyName =:companyName , category = :role WHERE idContact = :id");

            $this->db->bind("role", $role, null);
        } else {
            $this->db->query("UPDATE wbcc_contact SET civiliteContact = :civilite, prenomContact= :prenom,nomContact=:nom, fullName = :fullName, telContact = :tel1, mobilePhone= :tel2, faxPhone= :tel3, emailContact= :email, emailCollaboratif= :emailC, adresseContact = :adresse1, businessLine2 = :adresse2, codePostalContact = :cp, villeContact= :ville, departement= :dpt, businessState = :region, codePorte= :porte, batiment= :batiment, etage = :etage, jobTitle = :titre, service= :service,  editDate= :editDate, companyName =:companyName, category = :role WHERE idContact = :id");
        }


        $this->db->bind("civilite", $civilite, null);
        $this->db->bind("prenom", $prenom, null);
        $this->db->bind("nom", $nom, null);
        $this->db->bind("fullName", $fullName, null);
        $this->db->bind("tel1", $tel1, null);
        $this->db->bind("tel2", $tel2, null);
        $this->db->bind("tel3", $tel3, null);
        $this->db->bind("email", $email, null);
        $this->db->bind("emailC", $emailC, null);
        $this->db->bind("adresse1", $adresse1, null);
        $this->db->bind("adresse2", $adresse2, null);
        $this->db->bind("cp", $cp, null);
        $this->db->bind("ville", $ville, null);
        $this->db->bind("dpt", $dpt, null);
        $this->db->bind("region", $region, null);
        $this->db->bind("porte", $porte, null);
        $this->db->bind("batiment", $batiment, null);
        $this->db->bind("etage", $etage, null);
        $this->db->bind("titre", $titre, null);
        $this->db->bind("service", $service, null);
        $this->db->bind("editDate", date("Y-m-d h:i:s"), null);
        $this->db->bind("id", $id, null);
        $this->db->bind("companyName", $companyName, null);
        $this->db->execute();
    }

    public function updateContactUser(
        $id,
        $civilite,
        $nom,
        $prenom,
        $tel1,
        $email,
        $statut = ''
    ) {
        $fullName = $prenom . " " . $nom;

        $this->db->query("UPDATE wbcc_contact SET civiliteContact = :civilite, prenomContact= :prenom,nomContact=:nom, fullName = :fullName, telContact = :tel1,  emailContact= :email,  editDate= :editDate, statutContact=:statutContact WHERE idContact = :id");


        $this->db->bind("civilite", $civilite, null);
        $this->db->bind("prenom", $prenom, null);
        $this->db->bind("nom", $nom, null);
        $this->db->bind("fullName", $fullName, null);
        $this->db->bind("tel1", $tel1, null);
        $this->db->bind("email", $email, null);
        $this->db->bind("editDate", date("Y-m-d h:i:s"), null);
        $this->db->bind("statutContact", $statut, null);

        $this->db->bind("id", $id, null);
        $this->db->execute();
    }


    public function deleteContact($id)
    {
        $this->db->query("
            UPDATE wbcc_contact SET etatContact = 0
            WHERE idContact = :id");
        $this->db->bind("id", $id, null);
        $this->db->execute();
    }

    public function findByNumero($numero)
    {
        $this->db->query("SELECT * FROM wbcc_contact WHERE numeroContact = :numero");
        $this->db->bind("numero", $numero, null);
        if ($this->db->single()) {
            return $this->db->single();
        } else {
            return null;
        }
    }

    public function findByEmail($email)
    {
        $this->db->query("SELECT * FROM wbcc_contact WHERE emailContact = :email AND etatContact=1 LIMIT 1");
        $this->db->bind("email", $email, null);
        return $this->db->single();
    }

    public function insertContactCompany($idContact, $idCompany)
    {
        $this->db->query("
           INSERT INTO wbcc_contact_company 
           (idContactF, idCompanyF) 
           VALUES (:idContact, :idCompany)");
        $this->db->bind("idContact", $idContact, null);
        $this->db->bind("idCompany", $idCompany, null);
        $this->db->execute();
    }

    public function changeUserState($id, $oldState)
    {
        $oldState = ($oldState == 1) ? 0 : 1;
        $this->db->query("
            UPDATE wbcc_contact
            SET isUser = :oldState
            WHERE idContact = :id
        ");
        $this->db->bind("id", $id, null);
        $this->db->bind("oldState", $oldState, null);
        return $this->db->execute();
    }

    public function insertContactOpportunity($idContact, $idOp)
    {
        $this->db->query("
           INSERT INTO wbcc_contact_opportunity
           (idContactF, idOpportunityF) 
           VALUES (:idContact,:idOp)");
        $this->db->bind("idContact", $idContact, null);
        $this->db->bind("idOp", $idOp, null);
        $this->db->execute();
    }

    public function deleteContactToOpportunity($id, $idOp)
    {
        $this->db->query("
            DELETE FROM wbcc_contact_opportunity
            WHERE idContactF =:id AND idOpportunityF =:idOp");

        $this->db->bind("idOp", $idOp, null);
        $this->db->bind("id", $id, null);
        $this->db->execute();
    }
}
