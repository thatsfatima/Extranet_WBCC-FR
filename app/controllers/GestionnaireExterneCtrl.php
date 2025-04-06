<?php
class GestionnaireExterneCtrl extends Controller
{
    public function __construct()
    {
        $this->activityUtilisateur = $this->model('Utilisateur');
        $this->activityModel = $this->model('Activity');
        $this->opportunityModel = $this->model('Opportunity');
        $this->parametreModel = $this->model('Parametres');
        $this->documentModel = $this->model('Document');
        $this->contactModel = $this->model('Contact');
        $this->siteModel = $this->model('Site');
        // $this->personnelModel = $this->model('Personnel');
        // $this->companyModel = $this->model('Company');
        // $this->immeubleModel = $this->model('Immeuble');
        // $this->lotModel = $this->model('Lot');
        // $this->documentModel = $this->model('Document');
        // $this->historiqueModel = $this->model('Historique');
        // $this->noteModel = $this->model('Note');
        // $this->pieceModel = $this->model('Piece');
        // $this->bienModel = $this->model('Bien');
        // $this->devisModel = $this->model('Devis');
        // $this->artisanModel = $this->model('Artisan');
        $this->userAccessModel = $this->model('UserAccess');
    }

    public function tbdOpportunite()
    {
        $data = [];
        $this->view("gestionnaire/opportunite/tbdOpportunite", $data);
    }

    //ETAPE
    public function te($idOp)
    {
        $tousContacts = $this->contactModel->getAllContacts();
        $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
        $op = $this->opportunityModel->findByIdOp($idOp);
        $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
        $interlocuteur = $this->contactModel->getInterlocuteurCie($op->idOpportunity, $guidComp);
        $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
        $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
        $pap = $this->opportunityModel->findPap($idOp);
        $allImmeubles = $this->immeubleModel->getAllImmeubles();
        $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
        $activityDE = $this->activityModel->findActivityByOp(1, $idOp);
        $activityTE = $this->activityModel->findActivityByOp(2, $idOp);
        $activityRvRT = $this->activityModel->findActivityByOp(3, $idOp);
        $documents = $this->documentModel->getByOpportunity($idOp, "interne");
        $cie = $op->cie;

        if ($cie == false && $guidComp != null && $guidComp != "") {
            // $cie = $this->companyModel->findByNumero($guidComp);
            $cie = $this->companyModel->findById($guidComp);
        }

        $rapportDelegation = "";
        if ($activityDE) {
            if ($activityDE->isCleared == "True") {
                $rapportDelegation = $op->rapportDelegation;
                if (!($rapportDelegation != null && $rapportDelegation != "" && strpos(strtolower($op->rapportDelegation), "x") == false && strpos(strtolower($op->rapportDelegation), "delegation")  && strpos(strtolower($op->rapportDelegation), "sign"))) {
                    $documents = $this->documentModel->getByOpportunity($idOp, "interne");
                    if (sizeof($documents) != 0) {
                        foreach ($documents as $key => $doc) {
                            if (strpos(strtolower($doc->nomDocument), "delegation") && strpos(strtolower($doc->nomDocument), "sign")) {
                                $rapportDelegation = $doc->urlDocument;
                                break;
                            }
                        }
                    }
                }
            }
        }

        $derniereDelegation = "";
        if (sizeof($documents) != 0) {
            foreach ($documents as $key => $doc) {
                if (strpos(strtolower($doc->nomDocument), "delegation")) {
                    $derniereDelegation = $doc->urlDocument;
                    break;
                }
            }
        }
        $do = ($op->guidGestionnaireAppImm != null && $op->guidGestionnaireAppImm != "") ? $this->companyModel->findByNumero($op->guidGestionnaireAppImm) : false;
        $prescripteur = ($op->guidGestionnaireAppImm != null && $op->guidGestionnaireAppImm != "") ? $this->companyModel->findByNumero($op->guidGestionnaireAppImm) : false;
        if ($do == false) {
            $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
        }

        $immeuble = $op->immeuble;
        $app = $op->app;
        $rt = $op->rt;
        $piecesEnreg = [];
        if ($rt) {
            $piecesEnreg = $this->pieceModel->getPiecesByRT($rt->idRT);
        }
        $experts = $this->activityUtilisateur->getUserByRole('Expert');
        $experts =  array_merge($experts,  $this->activityUtilisateur->getUserByRole('Commercial'));
        $pieces = $this->pieceModel->getAll();
        $biens = $this->bienModel->getAll();
        $otherOpWSameContact = [];
        if ($op->contact) {
            $otherOpWSameContact = $this->opportunityModel->getOPwithSameContactAndEtape($idOp, $op->contact->idContact, 2);
        }
        $data = [
            "op" => $op,
            "activityTE" => $activityTE,
            "activityDE" => $activityDE,
            "activityRvRT" => $activityRvRT,
            "tousContacts" => $tousContacts,
            "tousCieAssurance" => $tousCieAssurance,
            "interlocuteur" => $interlocuteur,
            "tousContactsCie" => $tousContactsCie,
            "notes" => $notes,
            "pap" => $pap,
            "allImmeubles" => $allImmeubles,
            "allContactOp" => $allContactOp,
            "documents" => $documents,
            "cie" => $cie,
            "immeuble" => $immeuble,
            "app" => $app,
            "rt" => $rt,
            "do" => $do,
            "pieces" => $pieces,
            "piecesEnreg" => $piecesEnreg,
            "biens" => $biens,
            "rapportDelegation" => $rapportDelegation,
            "experts" =>  $experts,
            "derniereDelegation" => $derniereDelegation,
            "prescripteur" => $prescripteur,
            "otherOpWSameContact" => $otherOpWSameContact
        ];
        $this->view("gestionnaireExterne/opportunite/teleExpertise", $data);
    }

    public function de($idOp)
    {
        $tousContacts = $this->contactModel->getAllContacts();
        $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
        $op = $this->opportunityModel->findByIdOp($idOp);
        $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
        $interlocuteur = $this->contactModel->getInterlocuteurCie($op->idOpportunity, $guidComp);
        $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
        $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
        $pap = $this->opportunityModel->findPap($idOp);
        $allImmeubles = $this->immeubleModel->getAllImmeubles();
        $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
        $activityDE = $this->activityModel->findActivityByOp(1, $idOp);
        $activityTE = $this->activityModel->findActivityByOp(2, $idOp);
        $activityRvRT = $this->activityModel->findActivityByOp(3, $idOp);
        $documents = $this->documentModel->getByOpportunity($idOp, "interne");
        $cie = $op->cie;
        if ($cie == false && $guidComp != null && $guidComp != "") {
            // $cie = $this->companyModel->findByNumero($guidComp);
            $cie = $this->companyModel->findById($guidComp);
        }

        $rapportDelegation = "";
        if ($activityDE) {
            if ($activityDE->isCleared == "True") {
                $rapportDelegation = $op->rapportDelegation;
                if (!($rapportDelegation != null && $rapportDelegation != "" && strpos(strtolower($op->rapportDelegation), "x") == false && strpos(strtolower($op->rapportDelegation), "delegation")  && strpos(strtolower($op->rapportDelegation), "sign"))) {
                    $documents = $this->documentModel->getByOpportunity($idOp, "interne");
                    if (sizeof($documents) != 0) {
                        foreach ($documents as $key => $doc) {
                            if (strpos(strtolower($doc->nomDocument), "delegation") && strpos(strtolower($doc->nomDocument), "sign")) {
                                $rapportDelegation = $doc->urlDocument;
                                break;
                            }
                        }
                    }
                }
            }
        }

        $derniereDelegation = "";
        if (sizeof($documents) != 0) {
            foreach ($documents as $key => $doc) {
                if (strpos(strtolower($doc->nomDocument), "delegation")) {
                    $derniereDelegation = $doc->urlDocument;
                    break;
                }
            }
        }
        $do = ($op->guidGestionnaireAppImm != null && $op->guidGestionnaireAppImm != "") ? $this->companyModel->findByNumero($op->guidGestionnaireAppImm) : false;
        $prescripteur = ($op->guidGestionnaireAppImm != null && $op->guidGestionnaireAppImm != "") ? $this->companyModel->findByNumero($op->guidGestionnaireAppImm) : false;
        if ($do == false) {
            $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
        }

        $immeuble = $op->immeuble;
        $app = $op->app;
        $rt = $op->rt;
        $experts = $this->activityUtilisateur->getUserByRole('Expert');
        $experts =  array_merge($experts,  $this->activityUtilisateur->getUserByRole('Commercial'));
        $pieces = $this->pieceModel->getAll();
        $biens = $this->bienModel->getAll();

        $otherOpWSameContact = [];
        if ($op->contact) {
            $otherOpWSameContact = $this->opportunityModel->getOPwithSameContactAndEtape($idOp, $op->contact->idContact, 1);
        }
        $data = [
            "op" => $op,
            "activityTE" => $activityTE,
            "activityDE" => $activityDE,
            "activityRvRT" => $activityRvRT,
            "tousContacts" => $tousContacts,
            "tousCieAssurance" => $tousCieAssurance,
            "interlocuteur" => $interlocuteur,
            "tousContactsCie" => $tousContactsCie,
            "notes" => $notes,
            "pap" => $pap,
            "allImmeubles" => $allImmeubles,
            "allContactOp" => $allContactOp,
            "documents" => $documents,
            "cie" => $cie,
            "immeuble" => $immeuble,
            "app" => $app,
            "rt" => $rt,
            "do" => $do,
            "pieces" => $pieces,
            "biens" => $biens,
            "rapportDelegation" => $rapportDelegation,
            "experts" =>  $experts,
            "derniereDelegation" => $derniereDelegation,
            "prescripteur" => $prescripteur,
            "otherOpWSameContact" => $otherOpWSameContact
        ];
        $this->view("gestionnaireExterne/opportunite/delegation", $data);
    }

    public function rvrt($idOp)
    {
        $tousContacts = $this->contactModel->getAllContacts();
        $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
        $op = $this->opportunityModel->findByIdOp($idOp);
        $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
        $interlocuteur = $this->contactModel->getInterlocuteurCie($op->idOpportunity, $guidComp);
        $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
        $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
        $pap = $this->opportunityModel->findPap($idOp);
        $allImmeubles = $this->immeubleModel->getAllImmeubles();
        $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
        $activityDE = $this->activityModel->findActivityByOp(1, $idOp);
        $activityTE = $this->activityModel->findActivityByOp(2, $idOp);
        $activityRvRT = $this->activityModel->findActivityByOp(3, $idOp);
        $documents = $this->documentModel->getByOpportunity($idOp, "interne");
        $cie = $op->cie;

        if ($cie == false && $guidComp != null && $guidComp != "") {
            // $cie = $this->companyModel->findByNumero($guidComp);
            $cie = $this->companyModel->findById($guidComp);
        }

        $rapportDelegation = "";
        if ($activityDE) {
            if ($activityDE->isCleared == "True") {
                $rapportDelegation = $op->rapportDelegation;
                if (!($rapportDelegation != null && $rapportDelegation != "" && strpos(strtolower($op->rapportDelegation), "x") == false && strpos(strtolower($op->rapportDelegation), "delegation")  && strpos(strtolower($op->rapportDelegation), "sign"))) {
                    $documents = $this->documentModel->getByOpportunity($idOp, "interne");
                    if (sizeof($documents) != 0) {
                        foreach ($documents as $key => $doc) {
                            if (strpos(strtolower($doc->nomDocument), "delegation") && strpos(strtolower($doc->nomDocument), "sign")) {
                                $rapportDelegation = $doc->urlDocument;
                                break;
                            }
                        }
                    }
                }
            }
        }

        $derniereDelegation = "";
        if (sizeof($documents) != 0) {
            foreach ($documents as $key => $doc) {
                if (strpos(strtolower($doc->nomDocument), "delegation")) {
                    $derniereDelegation = $doc->urlDocument;
                    break;
                }
            }
        }
        $do = ($op->guidGestionnaireAppImm != null && $op->guidGestionnaireAppImm != "") ? $this->companyModel->findByNumero($op->guidGestionnaireAppImm) : false;
        $prescripteur = ($op->guidGestionnaireAppImm != null && $op->guidGestionnaireAppImm != "") ? $this->companyModel->findByNumero($op->guidGestionnaireAppImm) : false;
        if ($do == false) {
            $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
        }

        $immeuble = $op->immeuble;
        $app = $op->app;
        $rt = $op->rt;
        $piecesEnreg = [];
        if ($rt) {
            $piecesEnreg = $this->pieceModel->getPiecesByRT($rt->idRT);
        }
        $experts = $this->activityUtilisateur->getUserByRole('Expert');
        $experts =  array_merge($experts,  $this->activityUtilisateur->getUserByRole('Commercial'));
        $pieces = $this->pieceModel->getAll();
        $biens = $this->bienModel->getAll();

        $otherOpWSameContact = [];
        if ($op->contact) {
            $otherOpWSameContact = $this->opportunityModel->getOPwithSameContactAndEtape($idOp, $op->contact->idContact, 3);
        }
        $data = [
            "op" => $op,
            "activityTE" => $activityTE,
            "activityDE" => $activityDE,
            "activityRvRT" => $activityRvRT,
            "tousContacts" => $tousContacts,
            "tousCieAssurance" => $tousCieAssurance,
            "interlocuteur" => $interlocuteur,
            "tousContactsCie" => $tousContactsCie,
            "notes" => $notes,
            "pap" => $pap,
            "allImmeubles" => $allImmeubles,
            "allContactOp" => $allContactOp,
            "documents" => $documents,
            "cie" => $cie,
            "immeuble" => $immeuble,
            "app" => $app,
            "rt" => $rt,
            "do" => $do,
            "pieces" => $pieces,
            "piecesEnreg" => $piecesEnreg,
            "biens" => $biens,
            "rapportDelegation" => $rapportDelegation,
            "experts" =>  $experts,
            "derniereDelegation" => $derniereDelegation,
            "prescripteur" => $prescripteur,
            "otherOpWSameContact" => $otherOpWSameContact
        ];
        $this->view("gestionnaireExterne/opportunite/priseRVRT", $data);
    }


    //OPPORTUNITY
    public function indexOpportunite($lien = "", $typeDeclaration = '')
    {
        $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
        $role = $_SESSION["connectedUser"]->libelleRole;
        if ($role != 'Gestionnaire EXTERNE') {
            if (!($lien == ""  || $lien == "nouveau" || $lien == "ancien")) {
                $this->redirectToMethod("GestionnaireExterne", "indexOpportunite");
            } else {

                $guidUser = "";
                $idUser = "";
                if (($role == "Commercial" &&  $_SESSION["connectedUser"]->isDirecteurCommercial == "0") || $role == "Gestionnaire EXTERNE DOSSIER") {
                    $guidUser =  $_SESSION["connectedUser"]->numeroContact;
                    $idUser = $_SESSION["connectedUser"]->idUtilisateur;
                }

                if ($lien == "" || $lien == 'nouveau') {
                    $titre = "Les nouveaux dossiers présentant des anomalies ";
                    $opportunities = $this->opportunityModel->getOPAnomalies("1", "tous", "nouveau", $guidUser, $idUser, $role);
                    $aFaire = "te";
                }
                if ($lien == 'ancien') {
                    $titre = "Les anciens dossiers présentant des anomalies ";
                    $opportunities = $this->opportunityModel->getOPAnomalies("1", "tous", "ancien", $guidUser, $idUser, $role);
                    $aFaire = "te";
                }

                $data = [
                    "lien" => $lien,
                    "opportunities" => $opportunities,
                    "titre" => $titre,
                    "aFaire" => $aFaire
                ];
                $this->view('gestionnaireExterne/opportunite/' . __FUNCTION__, $data);
            }
        } else {
            $this->redirectToMethod('Home', 'index');
        }
    }
}