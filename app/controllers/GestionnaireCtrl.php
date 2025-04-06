<?php
class GestionnaireCtrl extends Controller
{
    public function __construct()
    {
        if (isset($_SESSION["connectedUser"]) && $_SESSION["connectedUser"]->isInterne != '1') {
            $this->redirectToMethod('Home', 'index');
        }
        $this->utilisateurModel = $this->model('Utilisateur');
        $this->activityModel = $this->model('Activity');
        $this->opportunityModel = $this->model('Opportunity');
        $this->parametreModel = $this->model('Parametres');
        $this->documentModel = $this->model('Document');
        $this->contactModel = $this->model('Contact');
        // $this->personnelModel = $this->model('Personnel');
        $this->companyModel = $this->model('Company');
        $this->immeubleModel = $this->model('Immeuble');
        $this->lotModel = $this->model('Lot');
        $this->documentModel = $this->model('Document');
        // $this->historiqueModel = $this->model('Historique');
        $this->noteModel = $this->model('Note');
        $this->pieceModel = $this->model('Piece');
        // $this->bienModel = $this->model('Bien');
        $this->rvModel = $this->model('RendezVous');
        $this->devisModel = $this->model('Devis');
        // $this->artisanModel = $this->model('Artisan');
        $this->userAccessModel = $this->model('UserAccess');
        $this->siteModel = $this->model('Site');
        // $this->equipementModel = $this->model('Equipement'); // Ajouté par Espoir
    }

    public function index()
    {
        header("location:javascript://history.go(-1)");
    }
    //PRISE RENDEZ-VOUS recherche de fuite
    public function priseRDVRF($idOp)
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/priseRDVRF/$idOp";
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $immeuble = $op->immeuble;
            $app = $op->app;
            $cie = false;
            if ($guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }
            $interlocuteur = false;
            $otherOpWSameCie = [];
            if ($cie) {
                $interlocuteur = $this->contactModel->getInterlocuteurCie($idOp, $cie->idCompany);
                $otherOpWSameCie = $this->opportunityModel->getOPwithSameCie($cie->numeroCompany, "interne", $idOp);
            }
            $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            $pap = $this->opportunityModel->findPap($idOp);
            $allImmeubles = $this->immeubleModel->getAllImmeubles();
            $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
            $activityDDE = $this->activityModel->findActivityByOp(24, $idOp);

            $rf = $op->rf;
            $typeResponsable = ($rf && $rf->origineFuiteSinistre != null) ? (str_contains(strtolower($rf->origineFuiteSinistre), "voisin") ? "voisin" : ((str_contains(strtolower($rf->origineFuiteSinistre), "commune") ? "pc" : ((str_contains(strtolower($rf->origineFuiteSinistre), "vous") ? "chez vous" : ("")))))) : "";
            $responsable = false;

            if ($op->idResponsableContactF != null && $op->idResponsableContactF != "") {
                $responsable = $this->contactModel->findById($op->idResponsableContactF);
            }

            $appResponsable = false;
            $appConResponsable = false;
            if (!$responsable) {
                if ($typeResponsable == "voisin") {
                    if ($rf && $rf->idVoisinF != null && $rf->idVoisinF != "") {
                        $responsable = $this->contactModel->findById($rf->idVoisinF);
                    }
                    $appResponsable = $this->lotModel->findLotByContact($responsable->idContact);
                    if ($appResponsable && $responsable) {
                        $appConResponsable = $this->lotModel->findAppConByLotCon($responsable->idContact, $appResponsable->idApp);
                    }
                }
                if ($typeResponsable == "pc") {
                    if ($immeuble && $immeuble->idGardien != null && $immeuble->idGardien != "" && $immeuble->idGardien != "0") {
                        $responsable = $this->contactModel->findById($immeuble->idGardien);
                    }
                    if (!$responsable) {
                        if ($op->idReferentDo != null && $op->idReferentDo != "" && $op->idReferentDo != "0") {
                            $responsable = $this->contactModel->findById($op->idReferentDo);;
                        }
                    }
                    $appResponsable = $op->app;
                    $appConResponsable = $op->appCon;
                }

                if ($typeResponsable == "chez vous") {
                    $responsable = $op->contact;
                    $appResponsable = $op->app;
                    $appConResponsable = $op->appCon;
                }
            } else {
                if ($typeResponsable == "voisin") {
                    $appResponsable = $this->lotModel->findLotByContact($responsable->idContact);
                    if ($appResponsable && $responsable) {
                        $appConResponsable = $this->lotModel->findAppConByLotCon($responsable->idContact, $appResponsable->idApp);
                    }
                }
                if ($typeResponsable == "pc" || $typeResponsable == "chez vous") {
                    $appResponsable = $op->app;
                    $appConResponsable = $op->appCon;
                }
            }





            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }

            $rdv = $this->rvModel->getRVByIdOp($idOp, 'RFP');
            $rt = $op->rt;

            $devis = $this->devisModel->getOpDevis($idOp);
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");

            $otherOpWSameContact = [];
            if ($op->contact) {
                $otherOpWSameContact = $this->opportunityModel->getOPwithSameContactAndEtape($idOp, $op->contact->idContact, "", 'open');
            }

            $pieces = [];
            if ($rt) {
                $pieces = $this->pieceModel->getPiecesByRT($rt->idRT);
            }
            $docSign = false;
            if ($rf && $rf->documentConstatDDE != null && $rf->documentConstatDDE != "") {
                $docSign = $this->documentModel->findByName($rf->documentConstatDDE);
            }
            $tousDO = $this->companyModel->getDO();
            $data = [
                "op" => $op,
                "tousContacts" => $tousContacts,
                "activityDDE" => $activityDDE,
                "tousCieAssurance" => $tousCieAssurance,
                "interlocuteur" => $interlocuteur,
                "tousContactsCie" => $tousContactsCie,
                "notes" => $notes,
                "pap" => $pap,
                "allImmeubles" => $allImmeubles,
                "allContactOp" => $allContactOp,
                "cie" => $cie,
                "otherOpWSameCie" => $otherOpWSameCie,
                "do" => $do,
                "prescripteur" => $prescripteur,
                "immeuble" => $immeuble,
                "app" => $app,
                "rdv" => $rdv,
                "rt" => $rt,
                "devis" => $devis,
                "documents" => $documents,
                "pieces" => $pieces,
                "otherOpWSameContact" => $otherOpWSameContact,
                "rf" => $rf,
                "typeResponsable" => "$typeResponsable",
                "responsable" => $responsable,
                "docSign" => $docSign,
                "tousDO" => $tousDO,
                "appResponsable" => $appResponsable,
                "appConResponsable" => $appConResponsable
            ];
            $this->view("gestionnaire/opportunite/priseRDVRF", $data);
        } else {
            header("location:javascript://history.go(-1)");
        }
    }

    //SUITE EXPERTISE
    public function suiteExpertise($idOp)
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/constatDDE/$idOp";
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $expertise = $op->expertise;
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $immeuble = $op->immeuble;
            $app = $op->app;
            $cie = false;
            if ($guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }
            $interlocuteur = false;
            $otherOpWSameCie = [];
            if ($cie) {
                $interlocuteur = $this->contactModel->getInterlocuteurCie($idOp, $cie->idCompany);
                $otherOpWSameCie = $this->opportunityModel->getOPwithSameCie($cie->numeroCompany, "interne", $idOp);
            }
            $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            $pap = $this->opportunityModel->findPap($idOp);
            $allImmeubles = $this->immeubleModel->getAllImmeubles();
            $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
            $activityDDE = $this->activityModel->findActivityByOp(24, $idOp);

            $rf = $op->rf;
            $typeResponsable = ($rf && $rf->origineFuiteSinistre != null) ? (str_contains(strtolower($rf->origineFuiteSinistre), "voisin") ? "voisin" : ((str_contains(strtolower($rf->origineFuiteSinistre), "commune") ? "pc" : ((str_contains(strtolower($rf->origineFuiteSinistre), "vous") ? "chez vous" : ("")))))) : "";
            $responsable = false;

            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }

            $rdv = $this->rvModel->getRVByIdOp($idOp);
            $rt = $op->rt;

            $devis = $this->devisModel->getOpDevis($idOp);
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");

            $otherOpWSameContact = [];
            if ($op->contact) {
                $otherOpWSameContact = $this->opportunityModel->getOPwithSameContactAndEtape($idOp, $op->contact->idContact, "", 'open');
            }

            $pieces = [];
            if ($rt) {
                $pieces = $this->pieceModel->getPiecesByRT($rt->idRT);
            }
            $docSign = false;
            if ($rf && $rf->documentConstatDDE != null && $rf->documentConstatDDE != "") {
                $docSign = $this->documentModel->findByName($rf->documentConstatDDE);
            }
            $tousDO = $this->companyModel->getDO();

            $expert = false;
            $cabinetExpert = false;
            if ($op->idCabinetExpertF != null && $op->idCabinetExpertF != "") {
                $cabinetExpert = $this->companyModel->findById($op->idCabinetExpertF);
                if ($op->idExpertCompanyF != null && $op->idExpertCompanyF != "" && $op->idExpertCompanyF != "0") {
                    $expert = $this->contactModel->findById($op->idExpertCompanyF);
                }
            }
            $data = [
                "op" => $op,
                "tousContacts" => $tousContacts,
                "activityDDE" => $activityDDE,
                "tousCieAssurance" => $tousCieAssurance,
                "interlocuteur" => $interlocuteur,
                "tousContactsCie" => $tousContactsCie,
                "notes" => $notes,
                "pap" => $pap,
                "allImmeubles" => $allImmeubles,
                "allContactOp" => $allContactOp,
                "cie" => $cie,
                "otherOpWSameCie" => $otherOpWSameCie,
                "do" => $do,
                "prescripteur" => $prescripteur,
                "immeuble" => $immeuble,
                "app" => $app,
                "rdv" => $rdv,
                "rt" => $rt,
                "devis" => $devis,
                "documents" => $documents,
                "pieces" => $pieces,
                "otherOpWSameContact" => $otherOpWSameContact,
                "rf" => $rf,
                "typeResponsable" => "$typeResponsable",
                "responsable" => $responsable,
                "docSign" => $docSign,
                "tousDO" => $tousDO,
                "expertise" => $expertise,
                "cabinetExpert" => $cabinetExpert,
                "expert" => $expert
            ];
            $this->view("gestionnaire/opportunite/suiteExpertise", $data);
        } else {
            header("location:javascript://history.go(-1)");
        }
    }

    //ENVOI DE DEMANDE DE REPARATION DE FUITE
    public function reparationFuite($idOp)
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/reparationFuite/$idOp";
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $immeuble = $op->immeuble;
            $app = $op->app;
            $cie = false;
            if ($guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }
            $interlocuteur = false;
            $otherOpWSameCie = [];
            if ($cie) {
                $interlocuteur = $this->contactModel->getInterlocuteurCie($idOp, $cie->idCompany);
                $otherOpWSameCie = $this->opportunityModel->getOPwithSameCie($cie->numeroCompany, "interne", $idOp);
            }
            $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            $pap = $this->opportunityModel->findPap($idOp);
            $allImmeubles = $this->immeubleModel->getAllImmeubles();
            $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
            $activityJustificatif = $this->activityModel->findActivityByOp(32, $idOp);

            $rf = $op->rf;
            $typeResponsable = ($rf && $rf->origineFuiteSinistre != null) ? (str_contains(strtolower($rf->origineFuiteSinistre), "voisin") ? "voisin" : ((str_contains(strtolower($rf->origineFuiteSinistre), "commune") ? "pc" : ((str_contains(strtolower($rf->origineFuiteSinistre), "vous") ? "chez vous" : ("")))))) : "";
            $responsable = false;
            if ($typeResponsable == "voisin") {
                if ($rf && $rf->idVoisinF != null && $rf->idVoisinF != "") {
                    $responsable = $this->contactModel->findById($rf->idVoisinF);
                }
                if (!$responsable) {
                    if ($op->idResponsableContactF != null && $op->idResponsableContactF != "") {
                        $responsable = $this->contactModel->findById($op->idResponsableContactF);
                    }
                }
            }
            if ($typeResponsable == "pc") {
                if ($immeuble && $immeuble->idGardien != null && $immeuble->idGardien != "" && $immeuble->idGardien != "0") {
                    $responsable = $this->contactModel->findById($immeuble->idGardien);
                }
                if (!$responsable) {
                    if ($op->idReferentDo != null && $op->idReferentDo != "" && $op->idReferentDo != "0") {
                        $responsable = $this->contactModel->findById($op->idReferentDo);;
                    }
                }
            }

            if ($typeResponsable == "chez vous") {
                $responsable = $op->contact;
            }

            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }

            $rdv = $this->rvModel->getRVByIdOp($idOp);
            $rt = $op->rt;

            $devis = $this->devisModel->getOpDevis($idOp);
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");

            $otherOpWSameContact = [];
            if ($op->contact) {
                $otherOpWSameContact = $this->opportunityModel->getOPwithSameContactAndEtape($idOp, $op->contact->idContact, "", 'open');
            }

            $pieces = [];
            if ($rt) {
                $pieces = $this->pieceModel->getPiecesByRT($rt->idRT);
            }
            $docSign = false;
            if ($rf && $rf->documentJustificatif != null && $rf->documentJustificatif != "") {
                $docSign = $this->documentModel->findByName($rf->documentJustificatif);
            }
            $tousDO = $this->companyModel->getDO();

            $data = [
                "op" => $op,
                "tousContacts" => $tousContacts,
                "activityJustificatif" => $activityJustificatif,
                "tousCieAssurance" => $tousCieAssurance,
                "interlocuteur" => $interlocuteur,
                "tousContactsCie" => $tousContactsCie,
                "notes" => $notes,
                "pap" => $pap,
                "allImmeubles" => $allImmeubles,
                "allContactOp" => $allContactOp,
                "cie" => $cie,
                "otherOpWSameCie" => $otherOpWSameCie,
                "do" => $do,
                "prescripteur" => $prescripteur,
                "immeuble" => $immeuble,
                "app" => $app,
                "rdv" => $rdv,
                "rt" => $rt,
                "devis" => $devis,
                "documents" => $documents,
                "pieces" => $pieces,
                "otherOpWSameContact" => $otherOpWSameContact,
                "rf" => $rf,
                "typeResponsable" => "$typeResponsable",
                "responsable" => $responsable,
                "docSign" => $docSign,
                "tousDO" => $tousDO
            ];
            $this->view("gestionnaire/opportunite/reparationFuite", $data);
        } else {
            header("location:javascript://history.go(-1)");
        }
    }

    //JUSTIFICATIF DE REPARATION DE FUITE
    public function justificatifRF($idOp)
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/justificatifRF/$idOp";
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $immeuble = $op->immeuble;
            $app = $op->app;
            $cie = false;
            if ($guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }
            $interlocuteur = false;
            $otherOpWSameCie = [];
            if ($cie) {
                $interlocuteur = $this->contactModel->getInterlocuteurCie($idOp, $cie->idCompany);
                $otherOpWSameCie = $this->opportunityModel->getOPwithSameCie($cie->numeroCompany, "interne", $idOp);
            }
            $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            $pap = $this->opportunityModel->findPap($idOp);
            $allImmeubles = $this->immeubleModel->getAllImmeubles();
            $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
            $activityJustificatif = $this->activityModel->findActivityByOp(32, $idOp);

            $rf = $op->rf;
            $typeResponsable = ($rf && $rf->origineFuiteSinistre != null) ? (str_contains(strtolower($rf->origineFuiteSinistre), "voisin") ? "voisin" : ((str_contains(strtolower($rf->origineFuiteSinistre), "commune") ? "pc" : ((str_contains(strtolower($rf->origineFuiteSinistre), "vous") ? "chez vous" : ("")))))) : "";
            $responsable = false;

            if ($op->idResponsableContactF != null && $op->idResponsableContactF != "") {
                $responsable = $this->contactModel->findById($op->idResponsableContactF);
            }

            if (!$responsable) {
                if ($typeResponsable == "voisin") {
                    if ($rf && $rf->idVoisinF != null && $rf->idVoisinF != "") {
                        $responsable = $this->contactModel->findById($rf->idVoisinF);
                    }
                }
                if ($typeResponsable == "pc") {
                    if ($immeuble && $immeuble->idGardien != null && $immeuble->idGardien != "" && $immeuble->idGardien != "0") {
                        $responsable = $this->contactModel->findById($immeuble->idGardien);
                    }
                    if (!$responsable) {
                        if ($op->idReferentDo != null && $op->idReferentDo != "" && $op->idReferentDo != "0") {
                            $responsable = $this->contactModel->findById($op->idReferentDo);;
                        }
                    }
                }

                if ($typeResponsable == "chez vous") {
                    $responsable = $op->contact;
                }
            }

            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }

            $rdv = $this->rvModel->getRVByIdOp($idOp);
            $rt = $op->rt;

            $devis = $this->devisModel->getOpDevis($idOp);
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");

            $otherOpWSameContact = [];
            if ($op->contact) {
                $otherOpWSameContact = $this->opportunityModel->getOPwithSameContactAndEtape($idOp, $op->contact->idContact, "", 'open');
            }

            $pieces = [];
            if ($rt) {
                $pieces = $this->pieceModel->getPiecesByRT($rt->idRT);
            }
            $docSign = false;
            if ($rf && $rf->documentJustificatif != null && $rf->documentJustificatif != "") {
                $docSign = $this->documentModel->findByName($rf->documentJustificatif);
            }
            $tousDO = $this->companyModel->getDO();

            $data = [
                "op" => $op,
                "tousContacts" => $tousContacts,
                "activityJustificatif" => $activityJustificatif,
                "tousCieAssurance" => $tousCieAssurance,
                "interlocuteur" => $interlocuteur,
                "tousContactsCie" => $tousContactsCie,
                "notes" => $notes,
                "pap" => $pap,
                "allImmeubles" => $allImmeubles,
                "allContactOp" => $allContactOp,
                "cie" => $cie,
                "otherOpWSameCie" => $otherOpWSameCie,
                "do" => $do,
                "prescripteur" => $prescripteur,
                "immeuble" => $immeuble,
                "app" => $app,
                "rdv" => $rdv,
                "rt" => $rt,
                "devis" => $devis,
                "documents" => $documents,
                "pieces" => $pieces,
                "otherOpWSameContact" => $otherOpWSameContact,
                "rf" => $rf,
                "typeResponsable" => "$typeResponsable",
                "responsable" => $responsable,
                "docSign" => $docSign,
                "tousDO" => $tousDO
            ];
            $this->view("gestionnaire/opportunite/justificatifRF", $data);
        } else {
            header("location:javascript://history.go(-1)");
        }
    }

    //ENVOIN DE JUSTIFICATIF DE REPARATION DE FUITE
    public function envoiJustificatifRF($idOp)
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/envoiJustificatifRF/$idOp";
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $immeuble = $op->immeuble;
            $app = $op->app;
            $cie = false;
            if ($guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }
            $interlocuteur = false;
            $otherOpWSameCie = [];
            if ($cie) {
                $interlocuteur = $this->contactModel->getInterlocuteurCie($idOp, $cie->idCompany);
                $otherOpWSameCie = $this->opportunityModel->getOPwithSameCie($cie->numeroCompany, "interne", $idOp);
            }
            $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            $pap = $this->opportunityModel->findPap($idOp);
            $allImmeubles = $this->immeubleModel->getAllImmeubles();
            $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
            $activityJustificatif = $this->activityModel->findActivityByOp(32, $idOp);

            $rf = $op->rf;
            $typeResponsable = ($rf && $rf->origineFuiteSinistre != null) ? (str_contains(strtolower($rf->origineFuiteSinistre), "voisin") ? "voisin" : ((str_contains(strtolower($rf->origineFuiteSinistre), "commune") ? "pc" : ((str_contains(strtolower($rf->origineFuiteSinistre), "vous") ? "chez vous" : ("")))))) : "";
            $responsable = false;

            if ($op->idResponsableContactF != null && $op->idResponsableContactF != "") {
                $responsable = $this->contactModel->findById($op->idResponsableContactF);
            }

            if (!$responsable) {
                if ($typeResponsable == "voisin") {
                    if ($rf && $rf->idVoisinF != null && $rf->idVoisinF != "") {
                        $responsable = $this->contactModel->findById($rf->idVoisinF);
                    }
                }
                if ($typeResponsable == "pc") {
                    if ($immeuble && $immeuble->idGardien != null && $immeuble->idGardien != "" && $immeuble->idGardien != "0") {
                        $responsable = $this->contactModel->findById($immeuble->idGardien);
                    }
                    if (!$responsable) {
                        if ($op->idReferentDo != null && $op->idReferentDo != "" && $op->idReferentDo != "0") {
                            $responsable = $this->contactModel->findById($op->idReferentDo);;
                        }
                    }
                }

                if ($typeResponsable == "chez vous") {
                    $responsable = $op->contact;
                }
            }

            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }

            $rdv = $this->rvModel->getRVByIdOp($idOp);
            $rt = $op->rt;

            $devis = $this->devisModel->getOpDevis($idOp);
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");

            $otherOpWSameContact = [];
            if ($op->contact) {
                $otherOpWSameContact = $this->opportunityModel->getOPwithSameContactAndEtape($idOp, $op->contact->idContact, "", 'open');
            }

            $pieces = [];
            if ($rt) {
                $pieces = $this->pieceModel->getPiecesByRT($rt->idRT);
            }
            $docSign = false;
            if ($rf && $rf->documentJustificatif != null && $rf->documentJustificatif != "") {
                $docSign = $this->documentModel->findByName($rf->documentJustificatif);
            }
            $tousDO = $this->companyModel->getDO();

            $data = [
                "op" => $op,
                "tousContacts" => $tousContacts,
                "activityJustificatif" => $activityJustificatif,
                "tousCieAssurance" => $tousCieAssurance,
                "interlocuteur" => $interlocuteur,
                "tousContactsCie" => $tousContactsCie,
                "notes" => $notes,
                "pap" => $pap,
                "allImmeubles" => $allImmeubles,
                "allContactOp" => $allContactOp,
                "cie" => $cie,
                "otherOpWSameCie" => $otherOpWSameCie,
                "do" => $do,
                "prescripteur" => $prescripteur,
                "immeuble" => $immeuble,
                "app" => $app,
                "rdv" => $rdv,
                "rt" => $rt,
                "devis" => $devis,
                "documents" => $documents,
                "pieces" => $pieces,
                "otherOpWSameContact" => $otherOpWSameContact,
                "rf" => $rf,
                "typeResponsable" => "$typeResponsable",
                "responsable" => $responsable,
                "docSign" => $docSign,
                "tousDO" => $tousDO
            ];
            $this->view("gestionnaire/opportunite/envoiJustificatifRF", $data);
        } else {
            header("location:javascript://history.go(-1)");
        }
    }

    //CONSTAT DEGATS DES EAUX
    public function constatDDE($idOp)
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/constatDDE/$idOp";
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $immeuble = $op->immeuble;
            $app = $op->app;
            $cie = false;
            if ($guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }
            $interlocuteur = false;
            $otherOpWSameCie = [];
            if ($cie) {
                $interlocuteur = $this->contactModel->getInterlocuteurCie($idOp, $cie->idCompany);
                $otherOpWSameCie = $this->opportunityModel->getOPwithSameCie($cie->numeroCompany, "interne", $idOp);
            }
            $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            $pap = $this->opportunityModel->findPap($idOp);
            $allImmeubles = $this->immeubleModel->getAllImmeubles();
            $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
            $activityDDE = $this->activityModel->findActivityByOp(24, $idOp);

            $rf = $op->rf;
            $typeResponsable = ($rf && $rf->origineFuiteSinistre != null) ? (str_contains(strtolower($rf->origineFuiteSinistre), "voisin") ? "voisin" : ((str_contains(strtolower($rf->origineFuiteSinistre), "commune") ? "pc" : ((str_contains(strtolower($rf->origineFuiteSinistre), "vous") ? "chez vous" : ("")))))) : "";
            $responsable = false;

            if ($op->idResponsableContactF != null && $op->idResponsableContactF != "") {
                $responsable = $this->contactModel->findById($op->idResponsableContactF);
            }

            if (!$responsable) {
                if ($typeResponsable == "voisin") {
                    if ($rf && $rf->idVoisinF != null && $rf->idVoisinF != "") {
                        $responsable = $this->contactModel->findById($rf->idVoisinF);
                    }
                }
                if ($typeResponsable == "pc") {
                    if ($immeuble && $immeuble->idGardien != null && $immeuble->idGardien != "" && $immeuble->idGardien != "0") {
                        $responsable = $this->contactModel->findById($immeuble->idGardien);
                    }
                    if (!$responsable) {
                        if ($op->idReferentDo != null && $op->idReferentDo != "" && $op->idReferentDo != "0") {
                            $responsable = $this->contactModel->findById($op->idReferentDo);;
                        }
                    }
                }

                if ($typeResponsable == "chez vous") {
                    $responsable = $op->contact;
                }
            }


            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }

            $rdv = $this->rvModel->getRVByIdOp($idOp);
            $rt = $op->rt;

            $devis = $this->devisModel->getOpDevis($idOp);
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");

            $otherOpWSameContact = [];
            if ($op->contact) {
                $otherOpWSameContact = $this->opportunityModel->getOPwithSameContactAndEtape($idOp, $op->contact->idContact, "", 'open');
            }

            $pieces = [];
            if ($rt) {
                $pieces = $this->pieceModel->getPiecesByRT($rt->idRT);
            }
            $docSign = false;
            if ($rf && $rf->documentConstatDDE != null && $rf->documentConstatDDE != "") {
                $docSign = $this->documentModel->findByName($rf->documentConstatDDE);
            }
            $tousDO = $this->companyModel->getDO();
            $data = [
                "op" => $op,
                "tousContacts" => $tousContacts,
                "activityDDE" => $activityDDE,
                "tousCieAssurance" => $tousCieAssurance,
                "interlocuteur" => $interlocuteur,
                "tousContactsCie" => $tousContactsCie,
                "notes" => $notes,
                "pap" => $pap,
                "allImmeubles" => $allImmeubles,
                "allContactOp" => $allContactOp,
                "cie" => $cie,
                "otherOpWSameCie" => $otherOpWSameCie,
                "do" => $do,
                "prescripteur" => $prescripteur,
                "immeuble" => $immeuble,
                "app" => $app,
                "rdv" => $rdv,
                "rt" => $rt,
                "devis" => $devis,
                "documents" => $documents,
                "pieces" => $pieces,
                "otherOpWSameContact" => $otherOpWSameContact,
                "rf" => $rf,
                "typeResponsable" => "$typeResponsable",
                "responsable" => $responsable,
                "docSign" => $docSign,
                "tousDO" => $tousDO
            ];
            $this->view("gestionnaire/opportunite/constatDDE", $data);
        } else {
            header("location:javascript://history.go(-1)");
        }
    }

    //ENVOI CONSTAT DEGATS DES EAUX
    public function envoiConstatDDE($idOp)
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/envoiConstatDDE/$idOp";
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $immeuble = $op->immeuble;
            $app = $op->app;
            $cie = false;
            if ($guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }
            $interlocuteur = false;
            $otherOpWSameCie = [];
            if ($cie) {
                $interlocuteur = $this->contactModel->getInterlocuteurCie($idOp, $cie->idCompany);
                $otherOpWSameCie = $this->opportunityModel->getOPwithSameCie($cie->numeroCompany, "interne", $idOp);
            }
            $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            $pap = $this->opportunityModel->findPap($idOp);
            $allImmeubles = $this->immeubleModel->getAllImmeubles();
            $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
            $activityDDE = $this->activityModel->findActivityByOp(24, $idOp);

            $rf = $op->rf;
            $typeResponsable = ($rf && $rf->origineFuiteSinistre != null) ? (str_contains(strtolower($rf->origineFuiteSinistre), "voisin") ? "voisin" : ((str_contains(strtolower($rf->origineFuiteSinistre), "commune") ? "pc" : ((str_contains(strtolower($rf->origineFuiteSinistre), "vous") ? "chez vous" : ("")))))) : "";
            $responsable = false;

            if ($op->idResponsableContactF != null && $op->idResponsableContactF != "") {
                $responsable = $this->contactModel->findById($op->idResponsableContactF);
            }

            if (!$responsable) {
                if ($typeResponsable == "voisin") {
                    if ($rf && $rf->idVoisinF != null && $rf->idVoisinF != "") {
                        $responsable = $this->contactModel->findById($rf->idVoisinF);
                    }
                }
                if ($typeResponsable == "pc") {
                    if ($immeuble && $immeuble->idGardien != null && $immeuble->idGardien != "" && $immeuble->idGardien != "0") {
                        $responsable = $this->contactModel->findById($immeuble->idGardien);
                    }
                    if (!$responsable) {
                        if ($op->idReferentDo != null && $op->idReferentDo != "" && $op->idReferentDo != "0") {
                            $responsable = $this->contactModel->findById($op->idReferentDo);;
                        }
                    }
                }

                if ($typeResponsable == "chez vous") {
                    $responsable = $op->contact;
                }
            }

            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }

            $rdv = $this->rvModel->getRVByIdOp($idOp);
            $rt = $op->rt;

            $devis = $this->devisModel->getOpDevis($idOp);
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");

            $otherOpWSameContact = [];
            if ($op->contact) {
                $otherOpWSameContact = $this->opportunityModel->getOPwithSameContactAndEtape($idOp, $op->contact->idContact, "", 'open');
            }

            $pieces = [];
            if ($rt) {
                $pieces = $this->pieceModel->getPiecesByRT($rt->idRT);
            }
            $docSign = false;
            if ($rf && $rf->documentConstatDDE != null && $rf->documentConstatDDE != "") {
                $docSign = $this->documentModel->findByName($rf->documentConstatDDE);
            }
            $tousDO = $this->companyModel->getDO();
            $data = [
                "op" => $op,
                "tousContacts" => $tousContacts,
                "activityDDE" => $activityDDE,
                "tousCieAssurance" => $tousCieAssurance,
                "interlocuteur" => $interlocuteur,
                "tousContactsCie" => $tousContactsCie,
                "notes" => $notes,
                "pap" => $pap,
                "allImmeubles" => $allImmeubles,
                "allContactOp" => $allContactOp,
                "cie" => $cie,
                "otherOpWSameCie" => $otherOpWSameCie,
                "do" => $do,
                "prescripteur" => $prescripteur,
                "immeuble" => $immeuble,
                "app" => $app,
                "rdv" => $rdv,
                "rt" => $rt,
                "devis" => $devis,
                "documents" => $documents,
                "pieces" => $pieces,
                "otherOpWSameContact" => $otherOpWSameContact,
                "rf" => $rf,
                "typeResponsable" => "$typeResponsable",
                "responsable" => $responsable,
                "docSign" => $docSign,
                "tousDO" => $tousDO
            ];
            $this->view("gestionnaire/opportunite/envoiConstatDDE", $data);
        } else {
            header("location:javascript://history.go(-1)");
        }
    }

    //Lettre Acceptation
    public function la($idOp, $etape = '')
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/la/$idOp";
        if ($etape != "") {
            $nextLien .= "/$etape";
        }
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $cabinetExperts = $this->companyModel->getCompaniesByIdStatut("EXPERT");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $cie = false;
            if ($guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }

            $cabinetExpert = false;
            if ($op->idCabinetExpertF != null && $op->idCabinetExpertF != "") {
                $cabinetExpert = $this->companyModel->findById($op->idCabinetExpertF);
            }

            $interlocuteur = false;
            $otherOpWSameCie = [];
            if ($cie) {
                $interlocuteur = $this->contactModel->getInterlocuteurCie($idOp, $cie->idCompany);
                $otherOpWSameCie = $this->opportunityModel->getOPwithSameCieForPCDevis($cie->numeroCompany, "interne", $idOp, $cie->idCompany, $_SESSION["connectedUser"]->libelleRole, $_SESSION["connectedUser"]->idUtilisateur);
            }
            $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            $pap = $this->opportunityModel->findPap($idOp);
            $allImmeubles = $this->immeubleModel->getAllImmeubles();
            $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
            $activityCFRT = $this->activityModel->findActivityByOp(11, $idOp);
            $activityFD = $this->activityModel->findActivityByOp(7, $idOp);
            $activityCD  = $this->activityModel->findActivityByOp(8, $idOp);
            $activityED = $this->activityModel->findActivityByOp(9, $idOp);
            $activityPC = $this->activityModel->findActivityByOp(10, $idOp);
            $activityLA = $this->activityModel->findActivityByOp(20, $idOp);

            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }
            $immeuble = $op->immeuble;
            $app = $op->app;
            $rdv = $this->rvModel->getRVByIdOp($idOp);
            $rt = $op->rt;
            $devis = $this->devisModel->getOpDevis($idOp);
            $listArtisanDevis = $this->artisanModel->getAllArtisanDevis();
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");
            $data = [
                "op" => $op,
                "tousContacts" => $tousContacts,
                "activityCFRT" => $activityCFRT,
                "activityFD" => $activityFD,
                "activityED" => $activityED,
                "activityPC" => $activityPC,
                "activityCD" => $activityCD,
                "activityLA" => $activityLA,
                "tousCieAssurance" => $tousCieAssurance,
                "interlocuteur" => $interlocuteur,
                "tousContactsCie" => $tousContactsCie,
                "notes" => $notes,
                "pap" => $pap,
                "allImmeubles" => $allImmeubles,
                "allContactOp" => $allContactOp,
                //"rapportDelegation" => $rapportDelegation,
                "cie" => $cie,
                "otherOpWSameCie" => $otherOpWSameCie,
                "do" => $do,
                "prescripteur" => $prescripteur,
                "immeuble" => $immeuble,
                "app" => $app,
                "rdv" => $rdv,
                "rt" => $rt,
                "devis" => $devis,
                "documents" => $documents,
                "listArtisanDevis" => $listArtisanDevis,
                "etapeControle" => $etape,
                "cabinetExperts" => $cabinetExperts,
                "cabinetExpert" => $cabinetExpert
            ];
            $this->view("gestionnaire/opportunite/lettreAcceptation", $data);
        }
    }

    //Définir Franchise
    public function fse($idOp, $etape = '')
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/fse/$idOp";
        if ($etape != "") {
            $nextLien .= "/$etape";
        }
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $cabinetExperts = $this->companyModel->getCompaniesByIdStatut("EXPERT");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $cie = false;
            if ($guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }

            $cabinetExpert = false;
            if ($op->idCabinetExpertF != null && $op->idCabinetExpertF != "") {
                $cabinetExpert = $this->companyModel->findById($op->idCabinetExpertF);
            }

            $interlocuteur = false;
            $otherOpWSameCie = [];
            if ($cie) {
                $interlocuteur = $this->contactModel->getInterlocuteurCie($idOp, $cie->idCompany);
                $otherOpWSameCie = $this->opportunityModel->getOPwithSameCieForPCDevis($cie->numeroCompany, "interne", $idOp, $cie->idCompany, $_SESSION["connectedUser"]->libelleRole, $_SESSION["connectedUser"]->idUtilisateur);
            }
            $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            $pap = $this->opportunityModel->findPap($idOp);
            $allImmeubles = $this->immeubleModel->getAllImmeubles();
            $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
            $activityCFRT = $this->activityModel->findActivityByOp(11, $idOp);

            $activityFranchise = $this->activityModel->findActivityByOp(25, $idOp);

            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }
            $immeuble = $op->immeuble;
            $app = $op->app;
            $rdv = $this->rvModel->getRVByIdOp($idOp);
            $rt = $op->rt;
            $devis = $this->devisModel->getOpDevis($idOp);
            $listArtisanDevis = $this->artisanModel->getAllArtisanDevis();
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");
            $data = [
                "op" => $op,
                "tousContacts" => $tousContacts,
                "activityCFRT" => $activityCFRT,
                "activityFranchise" => $activityFranchise,
                "tousCieAssurance" => $tousCieAssurance,
                "interlocuteur" => $interlocuteur,
                "tousContactsCie" => $tousContactsCie,
                "notes" => $notes,
                "pap" => $pap,
                "allImmeubles" => $allImmeubles,
                "allContactOp" => $allContactOp,
                //"rapportDelegation" => $rapportDelegation,
                "cie" => $cie,
                "otherOpWSameCie" => $otherOpWSameCie,
                "do" => $do,
                "prescripteur" => $prescripteur,
                "immeuble" => $immeuble,
                "app" => $app,
                "rdv" => $rdv,
                "rt" => $rt,
                "devis" => $devis,
                "documents" => $documents,
                "listArtisanDevis" => $listArtisanDevis,
                "etapeControle" => $etape,
                "cabinetExperts" => $cabinetExperts,
                "cabinetExpert" => $cabinetExpert
            ];
            $this->view("gestionnaire/opportunite/franchise", $data);
        }
    }

    //Définir Durée Travaux
    public function rvTr($idOp, $etape = '')
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/rvTr/$idOp";
        if ($etape != "") {
            $nextLien .= "/$etape";
        }
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $cabinetExperts = $this->companyModel->getCompaniesByIdStatut("EXPERT");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $cie = false;
            if ($guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }

            $cabinetExpert = false;
            if ($op->idCabinetExpertF != null && $op->idCabinetExpertF != "") {
                $cabinetExpert = $this->companyModel->findById($op->idCabinetExpertF);
            }

            $interlocuteur = false;
            $otherOpWSameCie = [];
            if ($cie) {
                $interlocuteur = $this->contactModel->getInterlocuteurCie($idOp, $cie->idCompany);
                $otherOpWSameCie = $this->opportunityModel->getOPwithSameCieForPCDevis($cie->numeroCompany, "interne", $idOp, $cie->idCompany, $_SESSION["connectedUser"]->libelleRole, $_SESSION["connectedUser"]->idUtilisateur);
            }
            $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            $pap = $this->opportunityModel->findPap($idOp);
            $allImmeubles = $this->immeubleModel->getAllImmeubles();
            $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
            $activityDureeTravaux = $this->activityModel->findActivityByOp(24, $idOp);

            $activityRVTravaux = $this->activityModel->findActivityByOp(23, $idOp);

            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }
            $immeuble = $op->immeuble;
            $app = $op->app;
            $rdv = $this->rvModel->getRVByIdOp($idOp);
            $rt = $op->rt;
            $devis = $this->devisModel->getOpDevis($idOp);
            $listArtisanDevis = $this->artisanModel->getAllArtisanDevis();
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");
            $artisans =  $this->utilisateurModel->getUserByidsRoles(6, 0, "artisan");
            $chefArtisans = $this->artisanModel->getChefArtisans();
            $data = [
                "op" => $op,
                "tousContacts" => $tousContacts,
                "activityRVTravaux" => $activityRVTravaux,
                "activityDureeTravaux" => $activityDureeTravaux,
                "tousCieAssurance" => $tousCieAssurance,
                "interlocuteur" => $interlocuteur,
                "tousContactsCie" => $tousContactsCie,
                "notes" => $notes,
                "pap" => $pap,
                "allImmeubles" => $allImmeubles,
                "allContactOp" => $allContactOp,
                //"rapportDelegation" => $rapportDelegation,
                "cie" => $cie,
                "otherOpWSameCie" => $otherOpWSameCie,
                "do" => $do,
                "prescripteur" => $prescripteur,
                "immeuble" => $immeuble,
                "app" => $app,
                "rdv" => $rdv,
                "rt" => $rt,
                "devis" => $devis,
                "documents" => $documents,
                "listArtisanDevis" => $listArtisanDevis,
                "etapeControle" => $etape,
                "cabinetExperts" => $cabinetExperts,
                "cabinetExpert" => $cabinetExpert,
                "artisans" => $artisans,
                "chefArtisans" => $chefArtisans
            ];
            $this->view("gestionnaire/opportunite/priseRVTravaux", $data);
        }
    }

    //Appeler Cie pour Encaissement Immédiat
    public function eci($idOp, $etape = '')
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/eci/$idOp";
        if ($etape != "") {
            $nextLien .= "/$etape";
        }
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $cabinetExperts = $this->companyModel->getCompaniesByIdStatut("EXPERT");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $cie = false;
            if ($guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }

            $cabinetExpert = false;
            if ($op->idCabinetExpertF != null && $op->idCabinetExpertF != "") {
                $cabinetExpert = $this->companyModel->findById($op->idCabinetExpertF);
            }

            $interlocuteur = false;
            $otherOpWSameCie = [];
            if ($cie) {
                $interlocuteur = $this->contactModel->getInterlocuteurCie($idOp, $cie->idCompany);
                $otherOpWSameCie = $this->opportunityModel->getOPwithSameCieForPCDevis($cie->numeroCompany, "interne", $idOp, $cie->idCompany, $_SESSION["connectedUser"]->libelleRole, $_SESSION["connectedUser"]->idUtilisateur);
            }
            $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            $pap = $this->opportunityModel->findPap($idOp);
            $allImmeubles = $this->immeubleModel->getAllImmeubles();
            $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
            $activity = false;

            $activity = $this->activityModel->findActivityByOp(22, $idOp);
            if ($activity) {
                $codeActivity = 22;
            } else {
                $activity = $this->activityModel->findActivityByOp(21, $idOp);
                $codeActivity = 21;
            }

            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }

            $immeuble = $op->immeuble;
            $app = $op->app;
            $rdv = $this->rvModel->getRVByIdOp($idOp);
            $rt = $op->rt;
            $devis = $this->devisModel->getOpDevis($idOp);
            $listArtisanDevis = $this->artisanModel->getAllArtisanDevis();
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");
            $encaissements = $this->opportunityModel->getEncaissementsByType($idOp, "Encaissement Devis Immédiat");
            $banques = $this->parametreModel->getListeDeroulante("wbcc_banque");
            $data = [
                "op" => $op,
                "banques" => $banques,
                "encaissements" => $encaissements,
                "tousContacts" => $tousContacts,
                "activity" => $activity,
                "codeActivity" => $codeActivity,
                "tousCieAssurance" => $tousCieAssurance,
                "interlocuteur" => $interlocuteur,
                "tousContactsCie" => $tousContactsCie,
                "notes" => $notes,
                "pap" => $pap,
                "allImmeubles" => $allImmeubles,
                "allContactOp" => $allContactOp,
                //"rapportDelegation" => $rapportDelegation,
                "cie" => $cie,
                "otherOpWSameCie" => $otherOpWSameCie,
                "do" => $do,
                "prescripteur" => $prescripteur,
                "immeuble" => $immeuble,
                "app" => $app,
                "rdv" => $rdv,
                "rt" => $rt,
                "devis" => $devis,
                "documents" => $documents,
                "listArtisanDevis" => $listArtisanDevis,
                "etapeControle" => $etape,
                "cabinetExperts" => $cabinetExperts,
                "cabinetExpert" => $cabinetExpert
            ];
            $this->view("gestionnaire/opportunite/relanceEncaisseImm", $data);
        }
    }

    public function tbdOpportunite()
    {
        $data = [];
        $this->view("gestionnaire/opportunite/tbdOpportunite", $data);
    }
    //ESPOIR
    public function prdvs($idOp)
    {
        $tousContacts = $this->contactModel->getAllContacts();
        $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
        $op = $this->opportunityModel->findByIdOp($idOp);
        $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
        $cie = false;
        if ($cie == false && $guidComp != null && $guidComp != "") {
            $cie = $this->companyModel->findByNumero($guidComp);
        }
        $cie = $op->cie;
        $interlocuteur = false;

        $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
        $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");

        $allImmeubles = $this->immeubleModel->getAllImmeubles();
        $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
        $activity = $this->activityModel->findActivityByOp(4, $idOp);

        $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
        $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
        if ($do == false) {
            // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
        }
        $immeuble = $op->immeuble;
        $app = $op->app;

        $expertCompagnie = $this->opportunityModel->getExpertCompagnie($idOp);
        $rdvExpertiseOp = $this->opportunityModel->getRDVExpertiseOp($idOp);
        $documents = $this->documentModel->getByOpportunity($idOp, "interne");
        $data = [
            "op" => $op,
            "tousContacts" => $tousContacts,
            "activity" => $activity,
            "tousCieAssurance" => $tousCieAssurance,
            "interlocuteur" => $interlocuteur,
            "tousContactsCie" => $tousContactsCie,
            "notes" => $notes,
            "allImmeubles" => $allImmeubles,
            "allContactOp" => $allContactOp,
            "cie" => $cie,
            "do" => $do,
            "prescripteur" => $prescripteur,
            "immeuble" => $immeuble,
            "app" => $app,
            "rdvExpertiseOp" => $rdvExpertiseOp,
            "documents" => $documents,
            'expertCompagnie' => $expertCompagnie
        ];
        $this->view("gestionnaire/opportunite/priseRdvExpertiseSinistre", $data);
    }

    public function prdvExpert($idOp)
    {
        $tousContacts = $this->contactModel->getAllContacts();
        $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
        $op = $this->opportunityModel->findByIdOp($idOp);
        $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
        $cie = false;
        if ($cie == false && $guidComp != null && $guidComp != "") {
            $cie = $this->companyModel->findByNumero($guidComp);
        }
        $cie = $op->cie;

        $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");

        $allImmeubles = $this->immeubleModel->getAllImmeubles();
        $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
        $activity = $this->activityModel->findActivityByOp(4, $idOp);

        $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
        $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
        if ($do == false) {
            // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
        }
        $immeuble = $op->immeuble;
        $app = $op->app;

        $expertCompagnie = $this->opportunityModel->getExpertCompagnie($idOp);
        $tousContactsCie = $expertCompagnie ? $this->contactModel->getPersonnelByCompany($expertCompagnie->idCompany) : array();
        $interlocuteur = $expertCompagnie ? $this->contactModel->getInterlocuteurExpertCie($idOp) : false;
        $documents = $this->documentModel->getByOpportunity($idOp, "interne");

        $data = [
            "op" => $op,
            "tousContacts" => $tousContacts,
            "activity" => $activity,
            "tousCieAssurance" => $tousCieAssurance,
            "interlocuteur" => $interlocuteur,
            "tousContactsCie" => $tousContactsCie,
            "notes" => $notes,
            "allImmeubles" => $allImmeubles,
            "allContactOp" => $allContactOp,
            "cie" => $cie,
            "do" => $do,
            "prescripteur" => $prescripteur,
            "immeuble" => $immeuble,
            "app" => $app,
            "expertCompagnie" => $expertCompagnie,
            "documents" => $documents

        ];
        $this->view("gestionnaire/opportunite/prendreRdvExpertise", $data);
    }
    //ESPOIR

    //ETAPE
    public function rt($idOp)
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/rt/$idOp";
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $cie = false;
            if ($guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }
            $interlocuteur = false;
            $otherOpWSameCie = [];
            if ($cie) {
                $interlocuteur = $this->contactModel->getInterlocuteurCie($idOp, $cie->idCompany);
                $otherOpWSameCie = $this->opportunityModel->getOPwithSameCie($cie->numeroCompany, "interne", $idOp);
            }
            $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            // $pap = $this->opportunityModel->findPap($idOp);
            $pap = false;
            $allImmeubles = $this->immeubleModel->getAllImmeubles();
            $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
            $activityFRT = $this->activityModel->findActivityByOp(16, $idOp);
            $activityCRT = $this->activityModel->findActivityByOp(17, $idOp);


            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }
            $immeuble = $op->immeuble;
            $app = $op->app;
            $rdv = $this->rvModel->getRVByIdOp($idOp);
            $rt = $op->rt;
            $devis = $this->devisModel->getOpDevis($idOp);
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");

            $pieces = [];
            if ($rt) {
                $pieces = $this->pieceModel->getPiecesByRT($rt->idRT);
            }
            $data = [
                "op" => $op,
                "tousContacts" => $tousContacts,
                "activityFRT" => $activityFRT,
                "activityCRT" => $activityCRT,
                "tousCieAssurance" => $tousCieAssurance,
                "interlocuteur" => $interlocuteur,
                "tousContactsCie" => $tousContactsCie,
                "notes" => $notes,
                "pap" => $pap,
                "allImmeubles" => $allImmeubles,
                "allContactOp" => $allContactOp,
                "cie" => $cie,
                "otherOpWSameCie" => $otherOpWSameCie,
                "do" => $do,
                "prescripteur" => $prescripteur,
                "immeuble" => $immeuble,
                "app" => $app,
                "rdv" => $rdv,
                "rt" => $rt,
                "devis" => $devis,
                "documents" => $documents,
                "pieces" => $pieces
            ];
            $this->view("gestionnaire/opportunite/compteRenduRT", $data);
        } else {
            header("location:javascript://history.go(-1)");
        }
    }

    public function frt($idOp)
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/frt/$idOp";
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $idActivity =  $this->activityModel->findActivityByOp('6', $idOp)->idActivity;
            $this->redirectToMethod("Expert", "frt", "$idActivity/$idOp");
        }
    }

    public function fd($idOp, $etape = '')
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/fd/$idOp";
        if ($etape != "") {
            $nextLien .= "/$etape";
        }
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $cabinetExperts = $this->companyModel->getCompaniesByIdStatut("EXPERT");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $cie = false;
            if ($guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }

            $experts = [];
            $expert = false;
            $cabinetExpert = false;
            if ($op->idCabinetExpertF != null && $op->idCabinetExpertF != "") {
                $cabinetExpert = $this->companyModel->findById($op->idCabinetExpertF);
                if ($cabinetExpert) {
                    $experts = $this->contactModel->getPersonnelByCompany($cabinetExpert->idCompany);
                }
                if ($op->idExpertCompanyF != null && $op->idExpertCompanyF != "" && $op->idExpertCompanyF != "0") {
                    $expert = $this->contactModel->findById($op->idExpertCompanyF);
                }
            }

            $interlocuteur = false;
            $otherOpWSameCie = [];
            if ($cie) {
                $interlocuteur = $this->contactModel->getInterlocuteurCie($idOp, $cie->idCompany);
                $otherOpWSameCie = $this->opportunityModel->getOPwithSameCieForPCDevis($cie->numeroCompany, "interne", $idOp, $cie->idCompany, $_SESSION["connectedUser"]->libelleRole, $_SESSION["connectedUser"]->idUtilisateur);
            }
            $interlocuteurArchi = false;
            //ARCHITECTE
            if ($op->architecte) {
                $interlocuteurArchi = $this->contactModel->getInterlocuteurCie($idOp, $op->architecte->idCompany);
            }
            //FIN ARCHI

            $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            $pap = $this->opportunityModel->findPap($idOp);
            $allImmeubles = $this->immeubleModel->getAllImmeubles();
            $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
            $activityCFRT = $this->activityModel->findActivityByOp(11, $idOp);
            $activityFD = $this->activityModel->findActivityByOp(7, $idOp);
            $activityCD  = $this->activityModel->findActivityByOp(8, $idOp);
            $activityED = $this->activityModel->findActivityByOp(9, $idOp);
            $activityPC = $this->activityModel->findActivityByOp(10, $idOp);

            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }
            $immeuble = $op->immeuble;
            $app = $op->app;
            $rdv = $this->rvModel->getRVByIdOp($idOp);
            $rt = $op->rt;
            $devis = $this->devisModel->getOpDevis($idOp);
            $listArtisanDevis = $this->artisanModel->getAllArtisanDevis();
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");
            $data = [
                "op" => $op,
                "tousContacts" => $tousContacts,
                "activityCFRT" => $activityCFRT,
                "activityFD" => $activityFD,
                "activityED" => $activityED,
                "activityPC" => $activityPC,
                "activityCD" => $activityCD,
                "tousCieAssurance" => $tousCieAssurance,
                "interlocuteur" => $interlocuteur,
                "tousContactsCie" => $tousContactsCie,
                "notes" => $notes,
                "pap" => $pap,
                "allImmeubles" => $allImmeubles,
                "allContactOp" => $allContactOp,
                //"rapportDelegation" => $rapportDelegation,
                "cie" => $cie,
                "otherOpWSameCie" => $otherOpWSameCie,
                "do" => $do,
                "prescripteur" => $prescripteur,
                "immeuble" => $immeuble,
                "app" => $app,
                "rdv" => $rdv,
                "rt" => $rt,
                "devis" => $devis,
                "documents" => $documents,
                "listArtisanDevis" => $listArtisanDevis,
                "etapeControle" => $etape,
                "cabinetExperts" => $cabinetExperts,
                "cabinetExpert" => $cabinetExpert,
                "experts" => $experts,
                "expert" => $expert,
                "interlocuteurArchi" => $interlocuteurArchi
            ];
            if ($op->type == 'Sinistres') {
                $this->view("gestionnaire/opportunite/envoiDevis", $data);
            } else {
                if ($op->type == 'A.M.O.') {
                    $this->view("gestionnaire/opportunite/faireDevisAMO", $data);
                }
            }
        }
    }

    public function te($idOp)
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/te/$idOp";
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
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

            // if ($guidComp != null && $guidComp != "") {
            //     // $cie = $this->companyModel->findByNumero($guidComp);
            //     $cie = $this->companyModel->findById($guidComp);
            // }

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
            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }

            $immeuble = $op->immeuble;
            $app = $op->app;
            $rt = $op->rt;
            $experts = $this->utilisateurModel->getUserByRole('Expert');
            $experts =  array_merge($experts,  $this->utilisateurModel->getUserByRole('Commercial'));
            $pieces = $this->pieceModel->getAll();
            $biens = $this->bienModel->getAll();
            $otherOpWSameContact = [];
            if ($op->contact) {
                $otherOpWSameContact = $this->opportunityModel->getOPwithSameContactAndEtape($idOp, $op->contact->idContact, "", 'open');
                $tabSvg = [];
                foreach ($otherOpWSameContact as $key => $value) {
                    $tabSvg[] = $this->opportunityModel->findByIdOp($value->idOpportunity);
                }
                $otherOpWSameContact = $tabSvg;
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
            $this->view("gestionnaire/opportunite/teleExpertise", $data);
        } else {
            header("location:javascript://history.go(-1)");
        }
    }

    public function dc($idOp)
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/fd/$idOp";
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $cie = false;
            if ($cie == false && $guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }
            $cie = $op->cie;
            $interlocuteur = false;

            $role = $_SESSION["connectedUser"]->libelleRole;
            $idUser = $_SESSION["connectedUser"]->idUtilisateur;
            $otherOpWSameCie = [];
            $otherOpWSameCieForRelance = [];
            if ($cie) {
                $interlocuteur = $this->contactModel->getInterlocuteurCie($idOp, $cie->idCompany);
                $otherOpWSameCie = $this->opportunityModel->getOPwithSameCie($cie->numeroCompany, "interne", $idOp,  $cie->idCompany, $role, $idUser);
                $otherOpWSameCieForRelance = $this->opportunityModel->getOPwithSameCieForRelance($cie->numeroCompany, "interne", $idOp, $cie->idCompany, $role,  $idUser);
            }
            $tousContactsCie = $this->contactModel->getContactByGuidCompany($guidComp);
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            $pap = $this->opportunityModel->findPap($idOp);
            $allImmeubles = $this->immeubleModel->getAllImmeubles();
            $allContactOp = $this->contactModel->getContactByOpportunity($idOp);
            $activity = $this->activityModel->findActivityByOp(4, $idOp);
            $activityDE = $this->activityModel->findActivityByOp(1, $idOp);
            $rapportDelegation = "";

            if ($activityDE) {
                if ($activityDE->isCleared == "True") {
                    $rapportDelegation = $op->rapportDelegation;
                    if (!($rapportDelegation != null && $rapportDelegation != "" && strpos(strtolower($op->rapportDelegation), "x") == false && strpos(strtolower($op->rapportDelegation), "delegation")  && strpos(strtolower($op->rapportDelegation), "sign"))) {
                        $documents = $this->documentModel->getByOpportunity($idOp, "interne");
                        if (sizeof($documents) != 0) {
                            foreach ($documents as $key => $doc) {
                                if (strpos(strtolower($doc->nomDocument), "delegation") && strpos(strtolower($doc->nomDocument), "sign") && $doc->isDeleted == "0") {
                                    $rapportDelegation = $doc->urlDocument;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            $do = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            $prescripteur = ($op->idGestionnaireAppImm != null && $op->idGestionnaireAppImm != "") ? $this->companyModel->findById($op->idGestionnaireAppImm) : false;
            if ($do == false) {
                // $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }
            $immeuble = $op->immeuble;
            $app = $op->app;
            $rt = $op->rt;
            $pieces = [];
            if ($rt) {
                $pieces = $this->pieceModel->getPiecesByRT($rt->idRT);
            }
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");
            $data = [
                "op" => $op,
                "tousContacts" => $tousContacts,
                "activity" => $activity,
                "tousCieAssurance" => $tousCieAssurance,
                "interlocuteur" => $interlocuteur,
                "tousContactsCie" => $tousContactsCie,
                "notes" => $notes,
                "pap" => $pap,
                "allImmeubles" => $allImmeubles,
                "allContactOp" => $allContactOp,
                "rapportDelegation" => $rapportDelegation,
                "cie" => $cie,
                "otherOpWSameCie" => $otherOpWSameCie,
                "otherOpWSameCieForRelance" => $otherOpWSameCieForRelance,
                "do" => $do,
                "prescripteur" => $prescripteur,
                "immeuble" => $immeuble,
                "app" => $app,
                "rt" => $rt,
                "pieces" => $pieces,
                "documents" => $documents

            ];
            $this->view("gestionnaire/opportunite/declarationCie", $data);
        } else {
            header("location:javascript://history.go(-1)");
        }
    }

    public function listOP($lien = "", $precision = '')
    {
        $columnDate = "";
        $columType = "";
        $columnAut = "";
        $type = explode(";", $precision)[0];
        $periode = explode(";", $precision)[1];
        $date1 = explode(";", $precision)[2];
        $date2 = explode(";", $precision)[3];
        $user = explode(";", $precision)[4];

        if ($lien == "DE") {

            $titre = "Les opportunités avec la délégation signée";
            $columnDate = "dateSignatureDelegation";
            $columType = "delegationSigne";
            $columnAut = "idAuteurSignatureDelegation";
        } else if ($lien == "DCM") {

            $titre = "Les opportunités déclarées par mail";
            $columnDate = "dateDeclarationCieMail";
            $columType = "declarationCieMail";
            $columnAut = "idAuteurDeclarationCieMail";
        } else if ($lien == "DC") {

            $titre = "Les opportunités déclarées par telehone ";
            $columnDate = "dateDeclarationCie";
            $columType = "declarationCie";
            $columnAut = "idAuteurDeclarationCie";
        } else if ($lien == "PR") {

            $titre = "Les opportunités avec prise en charge effectuée";
            $columnDate = "datePriseEnCharge";
            $columType = "priseEnCharge";
            $columnAut = "idAuteurPriseEnCharge";
        } else if ($lien == "SDC") {

            $titre = "Les opportunités déclarées par telehone ";
            $columnDate = "dateRelanceCieNumSinistre";
            $columType = "relanceCieNumSinistre";
            $columnAut = "idAuteurRelanceCieNumSinistre";
        } else if ($lien == "TE") {

            $titre = "Les opportunités déclarées par telehone ";
            $columnDate = "dateTeleExpertise";
            $columType = "teleExpertiseFaite";
            $columnAut = "idAuteurTeleExpertise";
        } else if ($lien == "ED") {

            $titre = "Les opportunités déclarées par telehone ";
            $columnDate = "dateEnvoiDevis";
            $columType = "envoiDevis";
            $columnAut = "idAuteurEnvoiDevis";
        } else if ($lien == "FD") {

            $titre = "Les opportunités déclarées par telehone ";
            $columnDate = "dateDevisFais";
            $columType = "devisFais";
            $columnAut = "idAuteurDevisFais";
        } else if ($lien == "RV") {

            $titre = "Les opportunités déclarées par telehone ";
            $columnDate = "datePriseRvRT";
            $columType = "priseRvRT";
            $columnAut = "idAuteurPriseRvRT";
        } else if ($lien == "FRT") {

            $titre = "Les opportunités avec FRT fais ";
            $columnDate = "dateFrt";
            $columType = "frtFait";
            $columnAut = "idAuteurFrt";
        } else if ($lien == "FRTRe") {

            $titre = "Les opportunités avec FRT fais ";
            $columnDate = "dateControleFRT";
            $columType = "frtRejet";
            $columnAut = "idAuteurControleFRT";
        } else if ($lien == "CFRT1") {

            $titre = "Les opportunités avec controle FRT etape 1";
            $columnDate = "dateControleFRT";
            $columType = "controleFRT";
            $columnAut = "idAuteurControleFRT";
        } else if ($lien == "CFRT2") {

            $titre = "Les opportunités avec controle FRT etape 2";
            $columnDate = "dateControleFRT2";
            $columType = "controleFRT2";
            $columnAut = "idAuteurControleFRT2";
        } else if ($lien == "CFRT3") {

            $titre = "Les opportunités avec controle FRT etape 3";
            $columnDate = "dateControleFRT3";
            $columType = "controleFRT3";
            $columnAut = "idAuteurControleFRT3";
        } else if ($lien == "RRT") {

            $titre = "Les opportunités avec rapport RT";
            $columnDate = "dateFaireRapportRT";
            $columType = "faireRapportRT";
            $columnAut = "IdAuteurFaireRapportRT";
        } else if ($lien == "CRT") {

            $titre = "Les opportunités avec controle RT fais";
            $columnDate = "dateControleRT";
            $columType = "controleRT";
            $columnAut = "idAuteurControleRT";
        } else {
        }


        $opportunities = $this->opportunityModel->getListeOPFaiteByTypeAuteurPeriode($periode, $columnDate, $columType, $date1, $date2, $user, $columnAut);

        $data = [
            "opportunities" => $opportunities,
            "titre" => $titre,
            "type" => $type,
            "periode" => $periode,
            "date1" => $date1,
            "date2" => $date2
        ];
        $this->view('gestionnaire/opportunite/' . __FUNCTION__, $data);
    }

    //OPPORTUNITY
    public function indexOpportunite()
    {
        $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
        $etapes = $this->activityModel->getActivitiesDB(1,  "priorite",  "ASC");
        $role = $_SESSION['connectedUser']->role;
        $idSite = $_SESSION['connectedUser']->idSite;
        $idUser = $_SESSION['connectedUser']->idUtilisateur;
        $statut = "enCours";
        $site = (($role == 3 && $_SESSION["connectedUser"]->isAccessAllOP != "1") || $role == 25) ? "$idSite" : "tous";
        $gestionnaire = (($role == 3 && $_SESSION["connectedUser"]->isAccessAllOP != "1") || $role == 25)  ?    "$idUser" : "tous";
        $typeIntervention = "tous";
        $commercial = ($role == 5 && $_SESSION["connectedUser"]->isDirecteurCommercial == "0" ?  $idUser : "tous");
        $periode = "all";
        $date1 = "";
        $date2 = "";
        $demandeSignature = "";
        $declarationCie = "";
        // if (!($statut == "" || $statut == "enCours" || $statut == "attenteCloture" || $statut == "clotures" || $statut == "tous" )) 
        // {
        //     $this->redirectToMethod("Gestionnaire", "indexOpportunite");
        // } else 
        {
            $titre = "Les opportunités ";
            $aFaire = "dossier";
            $opportunities = [];
            if (isset($_GET)) {
                extract($_GET);
            }
            $titre = "LISTE DES OP ";
            //TYPE INTERVENTION
            if ($typeIntervention != "tous") {
                $titre .= "'$typeIntervention'";
            }
            //GESTIONNAIRE
            if ($gestionnaire != "tous") {
                $contact = $this->utilisateurModel->findUserById($gestionnaire);
                $titre .= " de $contact->fullName  ";
            }
            //STATUT
            if ($statut == "enCours") {
                $titre .= " en Cours de Gestion ";
            } else {
                if ($statut == "won") {
                    $titre .= " clôturées gagnées ";
                } else {
                    if ($statut == "lost") {
                        $titre .= " clôturées perdues ";
                    } else {
                        if ($statut == "attenteCloture") {
                            $titre .= " en attente de cloture ";
                        } else {
                            if ($statut == "tous") {
                                $titre .= "";
                            } else {
                                //FIND ETAPE BY CODEACTIVITY
                                // $etape = array_filter($etapes, fn($value) => $value->codeActivity == $statut);
                                if ($statut == "4"  && $declarationCie != "") {
                                    $titre .= " déclarables";
                                }
                                foreach ($etapes as $key => $eta) {
                                    if ($eta->codeActivity == $statut) {
                                        $aFaire = $eta->lien;
                                        $titre .= "en attente de '" . $eta->libelleActivity . "' ";
                                    }
                                }
                                if (($statut == "24" || $statut == "32") && $demandeSignature == "0") {
                                    $titre .= " avec demande envoyéé";
                                }
                            }
                        }
                    }
                }
            }
            //SITE
            if ($site == "tous") {
            } else {
                $siteObj = $this->siteModel->findById($site);
                $titre .= " pour le site de '$siteObj->nomSite'";
            }
            //COMMERCIAL
            if ($commercial != "tous") {
                $contact = $this->utilisateurModel->findUserById($commercial);
                $titre .= " par le commercial $contact->fullName";
            }
            //PERIODE
            if ($periode != "all" && $periode != "perso" && $periode != "day" && $periode != "today") {
                $re = getPeriodDates("$periode", []);
                if (sizeof($re) != 0) {
                    $date1 = $re['startDate'];
                    $date2 = $re['endDate'];
                }
                $titre .= " du " . date('d/m/Y', strtotime($date1)) . " au " . date('d/m/Y', strtotime($date2));
            } else {
                if ($periode == "day") {
                    $titre .= " du " . date('d/m/Y', strtotime($date1));
                } else {
                    if ($periode == "today") {
                        $titre .= " Aujourd'hui";
                    } else {
                        if ($periode == "perso") {
                            $titre .= " du " . date('d/m/Y', strtotime($date1)) . " au " . date('d/m/Y', strtotime($date2));
                        }
                    }
                }
            }
            //GET OPPORTUNITES
            $opportunities =  $this->opportunityModel->getOpByFilter($statut, $site, $gestionnaire, $typeIntervention, $commercial, $periode, $date1, $date2, $demandeSignature, $declarationCie);
            $montantTotal = 0;
            foreach ($opportunities as $opportunity) {
                $montantTotal += ($opportunity->montantOp != null && $opportunity->montantOp != "") ? (float) $opportunity->montantOp : 0;
            }

            $sites = $this->siteModel->getAllSites();
            $gestionnaires = [];
            if ($site != "" && $site != "tous") {
                $gestionnaires = $this->utilisateurModel->getUserByidsRoles(3, 25, "gestionnaire", ($site));
            } else {
                $gestionnaires = $this->utilisateurModel->getUserByidsRoles(3, 25, "gestionnaire", "");
            }

            $commerciaux = $this->utilisateurModel->getUserByidsRoles("5", "5", "commercial", "", ($role == 5 && $_SESSION["connectedUser"]->isDirecteurCommercial == "0" ?  $idUser : ""));
            $etapeControle = "";
            $data = [
                "opportunities" => $opportunities,
                "aFaire" => $aFaire,
                "listOpCieMail" => [],
                "etapes" => $etapes,
                "etapeControle" => $etapeControle,
                "sites" => $sites,
                "titre" => $titre,
                "gestionnaires" => $gestionnaires,
                "statut" => "$statut",
                "site" => $site,
                "gestionnaire" => $gestionnaire,
                "periode" => "$periode",
                "date1" => "$date1",
                "date2" => "$date2",
                "idUser" => $idUser,
                "typeIntervention" => $typeIntervention,
                "commercial" => $commercial,
                "commerciaux" => $commerciaux,
                "demandeSignature" => $demandeSignature,
                "declarationCie" => $declarationCie,
                "montantTotal" => $montantTotal
            ];
            $this->view('gestionnaire/opportunite/' . __FUNCTION__, $data);
        }
    }

    public function creation($id = '')
    {
        $op = false;
        $do = false;
        $gestionnaire = false;
        $numProvisoire = "";
        $tel = "+33782004748";
        $documents = [];
        $intervenants = [];
        $contactCompanyNotInOpp = [];
        $immeubles = [];
        $immeubleCompanyNotInOpp = [];
        $lots = [];
        $notes = [];
        $lotCompanyNotInOpp = [];
        $immeublesCompany = [];
        $companies = [];
        $typeInterventions = $this->parametreModel->getListeDeroulante("wbcc_type_intervention");
        $corpsMetiers = $this->parametreModel->getListeDeroulante("wbcc_corps_metier");
        $idContact = Role::connectedUser()->idContact;
        $do = $this->companyModel->findByContact($idContact);
        $adresse = "";

        if ($id == "") {
            $title = "CREATION D'UN DOSSIER";
            //numProvisoire
            $param = $this->parametreModel->getParametres();
            $numero = str_pad(($param->numeroOpProvisoire + 1), 3, '0', STR_PAD_LEFT);
            $numProvisoire = "OP" . date("Y-m-d") . "-X$numero";
            $immeublesCompany = $this->immeubleModel->getAllImmeubles();
            $intervenants = $this->contactModel->getAllContacts();
        } else {

            //get Opportunity by id
            $op = $this->opportunityModel->findOpById($id);
            if ($op) {
                if ($op->guidGestionnaireAppImm != null && $op->guidGestionnaireAppImm != '') {
                    $do = $this->companyModel->findByNumero($op->guidGestionnaireAppImm);
                }
                if ($do) {
                } else {
                    if ($op->nomGestionnaireAppImm != null && $op->nomGestionnaireAppImm != '') {
                        $do = $this->companyModel->findByName($op->nomGestionnaireAppImm);
                    }
                }
            }

            $documents = $this->documentModel->getByOpportunity($id);
            $companies = $this->companyModel->getCompaniesByOpportunity($id, $do->idCompany);
            $intervenants = $this->personnelModel->getContactByCompanyAndLot($do->idCompany);

            // $intervenantsCompany = $this->personnelModel->getContactByCompany($do->idCompany);
            // $contactCompanyNotInOpp = [];
            // $notes = $this->noteModel->getNoteByOpportunity($id);
            // foreach ($intervenantsCompany as $ic) {
            //     $tr = false;
            //     foreach ($intervenants as $i) {
            //         if ($ic->idContact == $i->idContact) {
            //             $tr = true;
            //             break;
            //         }
            //     }
            //     if ($tr == false) {
            //         $contactCompanyNotInOpp[] = $ic;
            //     }
            // }

            $immeubles = $this->immeubleModel->getImmeubleByOpportunity($id);
            $immeublesCompany = $this->immeubleModel->getImmeubleByCompany($do->idCompany);
            $immeubleCompanyNotInOpp = [];

            foreach ($immeublesCompany as $ic) {
                $tr = false;
                foreach ($immeubles as $i) {
                    if ($ic->idImmeuble == $i->idImmeuble) {
                        $tr = true;
                        break;
                    }
                }
                if ($tr == false) {
                    $immeubleCompanyNotInOpp[] = $ic;
                }
            }

            $lots = $this->lotModel->getLotByOpportunity($id);
            $lotsCompany = $this->lotModel->getLotsByCompany($do->idCompany);
            $lotCompanyNotInOpp = [];

            foreach ($lotsCompany as $ic) {
                $tr = false;
                foreach ($lots as $i) {
                    if ($ic->idApp == $i->idApp) {
                        $tr = true;
                        break;
                    }
                }
                if ($tr == false) {
                    $lotCompanyNotInOpp[] = $ic;
                }
            }
            if ($op) {
                //TEST SI OP APPARTIENT AU DO
                $title = "DETAIL DOSSIER ";
                //GET NUM GESTIONNAIRE
                $gestionnaire = $op->numGestionnaire != null && $op->numGestionnaire != "" ? $this->parametreModel->getGestionnaireByCode($op->numGestionnaire) : false;

                if ($gestionnaire) {
                    $tel = $gestionnaire->whatsapp;
                }
                $adresse = (sizeof($immeubles) != 0) ? $immeubles[0]->adresse . " " . $immeubles[0]->codePostal . " " . $immeubles[0]->ville : ((sizeof($lots) != 0) ? $lots[0]->adresse . " " . $lots[0]->codePostal . " " . $lots[0]->ville : "");
            } else {
                return $this->redirectToMethod("Gestionnaire", "index");
            }
        }
        $tousContacts = $this->contactModel->getAllContacts();
        $tousCompanySyndic = $this->companyModel->getCompaniesByIdStatut("SYNDIC");
        $tousCompanyBailleur = $this->companyModel->getCompaniesByIdStatut("Bailleur");
        $tousCompany = $this->companyModel->getCompaniesByIdStatut("");
        $tousCie = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
        $tousCourt = $this->companyModel->getCompaniesByIdStatut("Courtier");
        $tousCommerciaux = $this->utilisateurModel->getUserByRole("Commercial");

        $data = [
            "title" => $title,
            "numProvisoire" => $numProvisoire,
            "op" => $op,
            "gestionnaire" => $gestionnaire,
            "telGestionnaire" => $tel,
            "documents" => $documents,
            "intervenants" => $intervenants,
            "intervenantsCompany" => $contactCompanyNotInOpp,
            "immeubles" => $immeubles,
            "immeublesCompany" => $immeublesCompany,
            "lots" => $lots,
            "lotsCompany" => $lotCompanyNotInOpp,
            "company" => $do,
            "societes" => $companies,
            "do" => $do,
            "notes" => $notes,
            "idContact" => $idContact,
            "adresse" => $adresse,
            "typeInterventions" => $typeInterventions,
            "corpsMetiers" => $corpsMetiers,
            "tousContacts" => $tousContacts,
            "tousCompanySyndic" => $tousCompanySyndic,
            "tousCompany" => $tousCompany,
            "tousCie" => $tousCie,
            "tousCourt" => $tousCourt,
            "tousCompanyBailleur" => $tousCompanyBailleur,
            "tousCommerciaux" => $tousCommerciaux
        ];
        $this->view("gestionnaire/opportunite/creation", $data);
    }

    public function dossier($id = '')
    {
        $op = false;
        $do = false;
        $gestionnaire = false;
        $numProvisoire = "";
        $tel = "+33782004748";
        $documents = [];
        $intervenants = [];
        $contactCompanyNotInOpp = [];
        $immeubles = [];
        $immeubleCompanyNotInOpp = [];
        $lots = [];
        $notes = [];
        $lotCompanyNotInOpp = [];
        $companies = [];
        $typeInterventions = $this->parametreModel->getListeDeroulante("wbcc_type_intervention");
        $corpsMetiers = $this->parametreModel->getListeDeroulante("wbcc_corps_metier");
        $idContact = Role::connectedUser()->idContact;

        $adresse = "";
        $esi = "";
        $activitesFutures = [];
        $activitesPassees = [];
        if ($id == "") {
            $title = "CREATION D'UN DOSSIER";
            //numProvisoire
            $param = $this->parametreModel->getParametres();
            $numero = str_pad(($param->numeroOpProvisoire + 1), 3, '0', STR_PAD_LEFT);
            $numProvisoire = "OP" . date("Y-m-d") . "-X$numero";
        } else {

            //get Opportunity by id
            $op = $this->opportunityModel->findByIdOp($id);
            if ($op) {
                if ($op->guidGestionnaireAppImm != null && $op->guidGestionnaireAppImm != '') {
                    $do = $this->companyModel->findByNumero($op->guidGestionnaireAppImm);
                }
                if ($do) {
                } else {
                    if ($op->nomGestionnaireAppImm != null && $op->nomGestionnaireAppImm != '') {
                        $do = $this->companyModel->findByName($op->nomGestionnaireAppImm);
                    }
                }
            }
            $activitesFutures = $this->opportunityModel->getActivitesFutures($id, "interne");
            $activitesPassees = $this->opportunityModel->getActivitesPassees($id, "interne");
            $documents = $this->documentModel->getByOpportunity($id, "interne");
            $companies = $this->companyModel->getCompaniesByOpportunity($id, 0);
            $intervenants = $this->personnelModel->getContactByOpportunity($id);
            $immeubles = $this->immeubleModel->getImmeubleByOpportunity($id);

            //GET GARDIEN ET CHEF DE SECTEUR
            if (sizeof($immeubles) != 0) {
                $immeuble = $immeubles[0];
                if ($immeuble && $immeuble->idGardien != null && $immeuble->idGardien != "") {
                    $gardien = $this->contactModel->findById($immeuble->idGardien);
                    if ($gardien && !array_filter($intervenants, fn($value) => $value->idContact == $gardien->idContact)) {
                        $intervenants[] = $gardien;
                    }
                }

                if ($immeuble && $immeuble->idChefSecteur != null && $immeuble->idChefSecteur != "") {
                    $gardien = $this->contactModel->findById($immeuble->idChefSecteur);
                    if ($gardien && !array_filter($intervenants, fn($value) => $value->idContact == $gardien->idContact)) {
                        $intervenants[] = $gardien;
                    }
                }
            }
            $intervenantsCompany = $this->contactModel->getAllContacts();
            $contactCompanyNotInOpp = [];
            $notes = $this->noteModel->getNoteByOpportunity($id, "interne");

            foreach ($intervenantsCompany as $ic) {
                $tr = false;
                foreach ($intervenants as $i) {
                    if ($ic->idContact == $i->idContact) {
                        $tr = true;
                        break;
                    }
                }
                if ($tr == false) {
                    $contactCompanyNotInOpp[] = $ic;
                }
            }


            $immeublesCompany = $this->immeubleModel->getAllImmeubles();
            $immeubleCompanyNotInOpp = [];

            foreach ($immeublesCompany as $ic) {
                $tr = false;
                foreach ($immeubles as $i) {
                    if ($ic->idImmeuble == $i->idImmeuble) {
                        $tr = true;
                        break;
                    }
                }
                if ($tr == false) {
                    $immeubleCompanyNotInOpp[] = $ic;
                }
            }

            $lots = $this->lotModel->getLotByOpportunity($id);
            $lotsCompany = $this->lotModel->getLots();
            $otherOpWSameContact = [];
            if ($op->contact) {
                $otherOpWSameContact = $this->opportunityModel->getOPwithSameContactAndEtape(
                    $op->idOpportunity,
                    $op->contact->idContact,
                    ""
                );
            }

            $lotCompanyNotInOpp = [];
            foreach ($lotsCompany as $ic) {
                $tr = false;
                foreach ($lots as $i) {
                    if ($ic->idApp == $i->idApp) {
                        $tr = true;
                        break;
                    }
                }
                if ($tr == false) {
                    $lotCompanyNotInOpp[] = $ic;
                }
            }
            if ($op) {

                //TEST SI OP APPARTIENT AU DO
                $title = "DETAIL DOSSIER ";
                //GET NUM GESTIONNAIRE
                $gestionnaire = $op->gestionnaire != null && $op->gestionnaire != "" ?
                    $this->utilisateurModel->findUserById($op->gestionnaire) : false;

                if ($gestionnaire) {
                    $tel = $gestionnaire->telContact;
                }
                $adresse = (sizeof($immeubles) != 0) ? $immeubles[0]->adresse . " " . $immeubles[0]->codePostal . " " .
                    $immeubles[0]->ville : ((sizeof($lots) != 0) ? $lots[0]->adresse . " " . $lots[0]->codePostal . " " . $lots[0]->ville :
                        "");
                $esi = (sizeof($immeubles) != 0) ? $immeubles[0]->nomImmeubleSyndic : "";
            } else {
                return $this->redirectToMethod("Gestionnaire", "index");
            }
        }

        $superArtisans = $this->artisanModel->getAllArtisanDevis();
        $artisans = $this->companyModel->getCompaniesByIdStatut('Artisan');
        $architectes = $this->companyModel->getCompaniesByIdStatut('Architecte');
        $devis = $this->devisModel->getOpDevis($id);
        $lastSituation = $this->devisModel->findLastSituation($id);

        $data = [
            "title" => $title,
            "numProvisoire" => $numProvisoire,
            "op" => $op,
            "gestionnaire" => $gestionnaire,
            "telGestionnaire" => $tel,
            "documents" => $documents,
            "intervenants" => $intervenants,
            "intervenantsCompany" => $contactCompanyNotInOpp,
            "immeubles" => $immeubles,
            "immeublesCompany" => $immeubleCompanyNotInOpp,
            "lots" => $lots,
            "lotsCompany" => $lotCompanyNotInOpp,
            "company" => $do,
            "societes" => $companies,
            "do" => $do,
            "notes" => $notes,
            "idContact" => $idContact,
            "adresse" => $adresse,
            "esi" => $esi,
            "typeInterventions" => $typeInterventions,
            "corpsMetiers" => $corpsMetiers,
            "activitesFutures" => $activitesFutures,
            "activitesPassees" => $activitesPassees,
            "otherOpWSameContact" => $otherOpWSameContact,
            "superArtisans" => $superArtisans,
            "artisans" => $artisans,
            "architectes" => $architectes,
            "devis" => $devis,
            "lastSituation" => $lastSituation
        ];
        $this->view("gestionnaire/opportunite/dossier", $data);
    }

    public function saveDossier()
    {
        $form = getForm($_POST);
        extract($form);
        $tabDoc = [];

        $pm = isset($partieMitoyenne) ? 1 : 0;
        $to = "gestion@wbcc.fr";
        $idContact = Role::connectedUser()->idContact;
        $do = $this->companyModel->findByContact($idContact);
        $fileNames = [];
        $files = [];

        for ($i = 0; $i < sizeof($_FILES['files']['name']) - 1; $i++) {
            $tmp_name = $_FILES["files"]["tmp_name"][$i];
            // $name = $_FILES["files"]["name"][$i];
            $tab = explode(".", $_FILES['files']['name'][$i]);
            $nomFichier = $name . "_" . date("dmYHis") . "_" . $idContact . "." . $tab[sizeof($tab) - 1];

            if (!file_exists('../public/documents/opportunite/' . $nomFichier)) {
                move_uploaded_file($tmp_name, '../public/documents/opportunite/' . $nomFichier);
                $tabDoc[] = [
                    "nom" => $_POST["nom" . ($i + 1)],
                    "fichier"  => $nomFichier
                ];
                $fileNames[] = $_POST["nom" . ($i + 1)] . "." . $tab[sizeof($tab) - 1];
                $files[] = "/public/documents/opportunite/" . $nomFichier;
            }
        }

        if ($num != "0") {

            $this->opportunityModel->updateOpportunity(
                $num,
                $name,
                "",
                $type,
                $partieConcernee,
                $pm,
                $causeMission,
                $typeIntervention,
                $corpsMetier,
                $commentaireOP,
                $numSinistreMRH,
                $referenceExpert
            );
            foreach ($tabDoc as $key => $doc) {
                $docNumero = date("dmYHis") .  Role::connectedUser()->idUtilisateur . $key;
                $idDoc = $this->documentModel->save($docNumero, $doc['nom'], $doc['fichier'], Role::connectedUser()->fullName, Role::connectedUser()->idUtilisateur, Role::connectedUser()->numeroContact);
                $this->historiqueModel->save("Ajout du Fichier " . $doc['nom']);
                $this->documentModel->addOpportunityDocument($num, $idDoc, $numeroOp, $docNumero);
            }
            //Send Mail Gestion
            if (sizeof($files) != 0) {
                // $body = "Bonjour, <br><br> <b>" . Role::nomComplet() . "</b> de <b>" . $nomSyndic . "</b> vient d'ajouter des documents.<br><br><b>WBCC ASSISTANCE Extranet</b>";
                // Role::mailExtranetWithFiles($to, "$name / Ajout Document extranet", $body, [], $files, $fileNames);
                // $ccs = [];
                // // if ($do && trim($do->email) != "") {
                // //     $ccs[] = $do->email;
                // // }
                // Role::mailExtranetWithFiles(Role::connectedUser()->login, "$name / Ajout Document", "Bonjour " . Role::nomComplet() . ",<br><br> Vous venez d'ajouter des documents.<br><br><b>WBCC ASSISTANCE Extranet</b>", $ccs, $files, $fileNames);
            }

            $this->redirectToMethod("Gestionnaire", "dossier", $num);
        } else {
            $verif = false;
            if ($referenceDO != "") {
                $verif = $this->opportunityModel->findByReferenceDO($referenceDO);
            }

            if (!$verif) {
                $idContact = Role::connectedUser()->idContact;

                $numero = date('dmyhis');
                $id = $this->opportunityModel->addOpportunity(
                    $numero,
                    $name,
                    $referenceDO,
                    $type,
                    $partieConcernee,
                    $pm,
                    $causeMission,
                    $typeIntervention,
                    $corpsMetier,
                    $commentaireOP
                );

                if ($id != "0") {
                    foreach ($tabDoc as $doc) {
                        $docNumero = date("dmYHis");
                        $idDoc = $this->documentModel->save($docNumero, $doc['nom'], $doc['fichier']);
                        $this->historiqueModel->save("Ajout du Fichier " . $doc['nom']);
                        $this->documentModel->addOpportunityDocument($id, $idDoc, $numero, $docNumero);
                    }
                    $pm2 = ($pm == 1) ? "Oui" : "Non";
                    // $this->opportunityModel->insertCompanyOpportunity($do->idCompany, $id);
                    $param = $this->parametreModel->getParametres();
                    //MAIL DEMANDE DE CREATION
                    $numero = str_pad(($param->numeroDemandeValidation + 1), 8, '0', STR_PAD_LEFT);
                    $subject = $name . ": Demande de création du dossier";
                    $body = "WBCC EXTRANET  |  " . date("d/m/Y") . " <br><br> Bonjour,<br><br>Nous demandons la création du dossier :<br>
                    <u>N° Dossier WBCC</u> : <b>" . $name . "</b><br>
                    <u>Référence Donneur d'ordre</u> : <b>" . $referenceDO . "</b><br>
                    <u>Date demande</u> : <b>" . date("d/m/Y") . "</b><br>
                    <u>Type</u> : <b>" . $type . "</b><br>
                    <u>Partie concernée</u> : <b>" . $partieConcernee . "</b><br>
                    <u>Partie Mitoyenne</u> : <b>" . $pm2 . "</b><br>
                    <u>Cause de la mission</u> : <b>" . $causeMission . "</b><br>
                    <u>Type d'intervention</u> : <b>" . $typeIntervention . "</b><br>
                    <u>Corps de metier</u> : <b>" . $corpsMetier . "</b><br>
                    <br><br><br>Email envoyé depuis l'extranet de WBCC par <br>
                    <b> " . Role::nomComplet() . "</b><br>";
                    // if (Role::mailOnServer($to, $subject, $body)) {
                    //     // $this->parametreModel->updateParametre("numeroDemandeValidation", ($param->numeroDemandeValidation + 1));
                    //     $this->parametreModel->updateParametre("numeroOpProvisoire", ($param->numeroOpProvisoire + 1));
                    //     //MAIL RETOUR
                    //     $subject1 = "Demande de création du dossier $name";
                    //     $body1 = "WBCC EXTRANET  |  " . date("d/m/Y") . " <br><br> 
                    //         Bonjour " . Role::nomComplet() . ",<br><br>
                    //         Merci d'avoir soumis une requête auprès de nos services. Nous avons bien pris en compte votre demande et sommes en train de travailler dessus pour vous répondre dans les meilleurs délais.
                    //         <br><br>Voici les détails de votre demande :
                    //         <br><br>
                    //         - Ticket : <b>$name</b> <br>
                    //         - Sujet : <b>$subject</b> <br>
                    //         - Description : <br>$body
                    //        ";
                    //     Role::mailOnServer($_SESSION['connectedUser']->login, $subject1, $body1);
                    // }
                }
                $this->redirectToMethod("Gestionnaire", "dossier", $id);
            } else {
                $this->redirectToMethod("Gestionnaire", "indexOpportunite", 0);
            }
        }
    }

    //ACTIVITY
    public function indexTache($type = 'AFaire')
    {
        $idContact = Role::connectedUser()->idContact;
        // $activities = $this->activityModel->getActivities($type);;
        $activities = [];
        $titre = "Liste des Tâches : Tous ";
        $data = [
            "nomLien" => "espace",
            "activities" => $activities,
            "idContact" => $idContact,
            "titre" => $titre
            // "historiques" => $this->historiqueModel->getHistoriqueByUser( $_SESSION['connectedUser']->idUtilisateur)
        ];
        $this->view('gestionnaire/tache/' . __FUNCTION__, $data);
    }

    public function activity($id = '') {}

    //IMMEUBLE
    public function indexImmeuble()
    {
        $idContact = Role::connectedUser()->idContact;
        $immeubles = $this->immeubleModel->getAllImmeubles();;
        $titre = "Liste des Immeubles";
        $contacts = $this->contactModel->getAllContacts();
        $data = [
            "nomLien" => "espace",
            "immeubles" => $immeubles,
            "idContact" => $idContact,
            "contacts" => $contacts,
            "titre" => $titre
            // "historiques" => $this->historiqueModel->getHistoriqueByUser( $_SESSION['connectedUser']->idUtilisateur)
        ];
        $this->view('gestionnaire/immeuble/' . __FUNCTION__, $data);
    }

    public function immeuble($id = '')
    {
        $immeuble = $this->immeubleModel->findImmeubleById($id);;
        $titre = "Gestion Immeuble";
        $dos = $this->companyModel->getCompaniesByIdStatut("donneur");
        $cies = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
        $courtiers = $this->companyModel->getCompaniesByIdStatut("COURT");
        $contacts = $this->contactModel->getAllContacts();
        $data = [
            "nomLien" => "espace",
            "immeuble" => $immeuble,
            "titre" => $titre,
            "cies" => $cies,
            "courtiers" => $courtiers,
            "dos" => $dos,
            "contacts" => $contacts
        ];
        $this->view('gestionnaire/immeuble/' . __FUNCTION__, $data);
    }

    public function saveImmeubleComplet()
    {
        $form = getForm($_POST);
        extract($form);
        $immeuble = $this->immeubleModel->saveImmeubleComplet(
            $idImmeuble,
            $nomImmeuble,
            $adresse,
            $codePostal,
            ($ville),
            $departement,
            $region,
            $typeImmeuble,
            $nomDO,
            $idDO,
            $nomGardien,
            $idGardien,
            $nomChefSecteur,
            $idChefSecteur,
            $nomCompagnieAssurance,
            $idCompagnieAssurance,
            $nomCourtier,
            $idCourtier,
            $numPolice,
            $dateEffetContrat,
            $dateEcheanceContrat,
            $commentaire,
            '',
            '',
            '',
            '',
            '',
            '',
            $idUser
        );
        $this->redirectToMethod("Gestionnaire", "immeuble", $idImmeuble);
    }

    public function addImmeuble()
    {
        if (sizeof($_POST['idImmeubles']) != 0) {
            foreach ($_POST['idImmeubles'] as $id) {
                $this->immeubleModel->insertOpportunityImmeuble($_POST['idOpportunity'], $id);
            }
        }
        $this->redirectToMethod("Gestionnaire", "dossier", $_POST['idOpportunity']);
    }

    public function deleteImmeuble()
    {
        $form = getForm($_POST);
        extract($form);
        $this->immeubleModel->deleteImmeubleToOpportunity($idImmeuble, $idOp2);
        $this->redirectToMethod("Gestionnaire", "dossier", $idOp2);
    }

    //APPARTEMENT
    public function indexAppartement()
    {
        $idContact = Role::connectedUser()->idContact;
        $lots = $this->lotModel->getLots();
        $titre = "Liste des Appartements";
        $data = [
            "nomLien" => "espace",
            "lots" => $lots,
            "idContact" => $idContact,
            "titre" => $titre
            // "historiques" => $this->historiqueModel->getHistoriqueByUser( $_SESSION['connectedUser']->idUtilisateur)
        ];
        $this->view('gestionnaire/appartement/' . __FUNCTION__, $data);
    }

    public function Appartement($id = '') {}

    public function addLot()
    {
        if (sizeof($_POST['idLots']) != 0) {
            foreach ($_POST['idLots'] as $id) {
                $this->lotModel->insertOpportunityAppartement($_POST['idOpportunity'], $id);
            }
        }

        $this->redirectToMethod("Gestionnaire", "dossier", $_POST['idOpportunity']);
    }

    public function deleteLot()
    {
        $form = getForm($_POST);
        extract($form);
        $this->lotModel->deleteLotToOpportunity($idLot, $idOp2);
        $this->redirectToMethod("Gestionnaire", "dossier", $idOp2);
    }

    //NOTE
    public function deleteNote()
    {
        $form = getForm($_POST);
        extract($form);
        $this->noteModel->deleteNoteToOpportunity($idNote2, $idOpNote);
        $this->redirectToMethod("Gestionnaire", "dossier", $idOpNote);
    }

    //CONTACT
    public function indexContact()
    {
        $idContact = Role::connectedUser()->idContact;
        $contacts = $this->contactModel->getAllContacts();
        $titre = "Liste des Contacts";
        $data = [
            "nomLien" => "espace",
            "contacts" => $contacts,
            "idContact" => $idContact,
            "titre" => $titre
            // "historiques" => $this->historiqueModel->getHistoriqueByUser( $_SESSION['connectedUser']->idUtilisateur)
        ];
        $this->view('gestionnaire/contact/' . __FUNCTION__, $data);
    }

    public function contact($id = '') {}

    public function saveIntervenant()
    {
        $form = getForm($_POST);
        extract($form);

        $numero = date('dmyhis');

        if ($idContact != "0") {
            $this->personnelModel->updateContact(
                $idContact,
                $civilite,
                $nom,
                $prenom,
                $tel1,
                $tel2,
                $tel3,
                $email,
                $emailCollaboratif,
                $adresse1,
                $adresse2,
                $codePostal,
                $ville,
                $departement,
                $region,
                $porte,
                $batiment,
                $etage,
                "",
                "",
                $statut
            );
        } else {

            $id = $this->personnelModel->addContact(
                $numero,
                $civilite,
                $nom,
                $prenom,
                $tel1,
                $tel2,
                $tel3,
                $email,
                $emailCollaboratif,
                $adresse1,
                $adresse2,
                $codePostal,
                $ville,
                $departement,
                $region,
                $porte,
                $batiment,
                $etage,
                "",
                "",
                $statut,
                0,
                ''
            );
            if ($id != "0") {
                $this->personnelModel->insertContactOpportunity($id, $idOpportunity);
                $this->personnelModel->insertContactCompany($id, $idCompany);
            }
        }

        $this->redirectToMethod("Gestionnaire", "dossier", $idOpportunity);
    }

    public function addIntervenant()
    {
        //$form = getForm($_POST);
        if (sizeof($_POST['idIntervenants']) != 0) {
            foreach ($_POST['idIntervenants'] as $id) {
                $this->personnelModel->insertContactOpportunity($id, $_POST['idOpportunity']);
            }
        }
        $this->redirectToMethod("Gestionnaire", "dossier", $_POST['idOpportunity']);
    }

    public function deleteIntervenant()
    {
        $form = getForm($_POST);
        extract($form);
        $this->personnelModel->deleteContactToOpportunity($idContact2, $idOp2);
        $this->redirectToMethod("Gestionnaire", "dossier", $idOp2);
    }

    // SOCIETE
    public function indexSociete()
    {
        $idContact = Role::connectedUser()->idContact;
        $companies = $this->companyModel->getAllCompanies();
        $titre = "Liste des Sociétés";
        $data = [
            "nomLien" => "espace",
            "companies" => $companies,
            "idContact" => $idContact,
            "titre" => $titre
            // "historiques" => $this->historiqueModel->getHistoriqueByUser( $_SESSION['connectedUser']->idUtilisateur)
        ];
        $this->view('gestionnaire/societe/' . __FUNCTION__, $data);
    }

    public function societe($id = '')
    {
        if ($id == 0 || $id == "") {
            $this->redirectToMethod("Gestionnaire", "indexSociete");
        } else {
            $company = $this->companyModel->findById($id);
            $personnels = $this->personnelModel->getPersonnelByCompany($id);
            $data = [
                "company"  => $company,
                "personnels" => $personnels
            ];
            $this->view('gestionnaire/societe/societe', $data);
        }
    }

    public function addSociete()
    {

        if (sizeof($_POST['idSocietes']) != 0) {
            foreach ($_POST['idSocietes'] as $id) {
                $this->companyModel->insertOpportunityCompany($_POST['idOpportunity'], $id);
            }
        }

        $this->redirectToMethod("Gestionnaire", "dossier", $_POST['idOpportunity']);
    }

    public function deleteSociete()
    {
        $form = getForm($_POST);
        extract($form);
        $this->companyModel->deleteCompanyToOpportunity($idSociete, $idOp2);
        $this->redirectToMethod("Gestionnaire", "dossier", $idOp2);
    }

    public function saveSociete($page = '')
    {
        $id = "";
        $form = getForm($_POST);
        extract($form);
        if ($page == "societe" || $page == "dossier") {
            if ($idSociete != "0" && $idSociete != "") {
                $id = $idSociete;
                $this->companyModel->updateCompany(
                    $idSociete,
                    $nomSociete,
                    $enseigneSociete,
                    $telephoneSociete,
                    $emailSociete,
                    $siteWebSociete,
                    $categorieSociete,
                    $adresseSociete,
                    $codePostalSociete,
                    $villeSociete,
                    $departementSociete,
                    $regionSociete,
                    $rcsSociete,
                    $villeRcsSociete,
                    $siretSociete,
                    $codeNafSociete,
                    $activiteSociete,
                    $effectifSociete,
                    ""
                );
            } else {
                $numero = "COM" . date('dmYHis') . $_SESSION['user']->idUtilisateur;
                $id = $this->companyModel->addCompany2(
                    $numero,
                    $nomSociete,
                    $enseigneSociete,
                    $telephoneSociete,
                    $emailSociete,
                    $siteWebSociete,
                    $categorieSociete,
                    $adresseSociete,
                    $codePostalSociete,
                    $departementSociete,
                    $regionSociete,
                    $villeSociete,
                    $paysSociete,
                    $rcsSociete,
                    $villeRcsSociete,
                    $siretSociete,
                    $codeNafSociete,
                    $activiteSociete,
                    $effectifSociete
                );
            }
            if ($page == "societe") {
                $this->redirectToMethod("Gestionnaire", "societe", $id);
            } else {
                if ($page == "dossier") {
                    $this->redirectToMethod("Gestionnaire", "dossier", $idOp);
                }
            }
        } else {
            $this->redirectToMethod("Societe", "index");
        }
    }

    //AUDIT
    public function indexAudit($lien = "penalite", $flitres = '')
    {
        if (!($lien == "" || $lien == "rvrt" || $lien == "devis" || $lien == "declaration" || $lien == "anciennesOp" || $lien == "franchise" || $lien == "penalite")) {
        } else {
            $role = $_SESSION["connectedUser"]->libelleRole;
            $idUser = $_SESSION["connectedUser"]->idUtilisateur;
            $isGestionnaire = $_SESSION["connectedUser"]->isGestionnaire;
            $fTypeOp =  'clotures';
            $fSite =  'tous';
            $fGest =   $idUser;
            $fEtat =  '1';
            if ($flitres != "") {
                $flitreArray = explode('-', $flitres);
                $fTypeOp = isset($flitreArray[0]) ? $flitreArray[0] : 'clotures';
                $fSite = isset($flitreArray[1]) ? $flitreArray[1] : 'tous';
                $fGest = isset($flitreArray[2]) ? $flitreArray[2] :  $idUser;
                $fEtat = isset($flitreArray[3]) ? $flitreArray[3] : '1';
            }

            $gestionnaires = $this->utilisateurModel->getUserByidsRoles(5, 25, "gestionnaire");
            $sites = $this->siteModel->getAllSites();
            $fSiteId = $fSite;
            if ($fSite != "tous" && $fSite != "me") {
                $fSiteId =  $this->siteModel->findByNomSite($fSite)->idSite;
            }

            $opportunities = [];
            $titre = "";
            $link = "";
            if ($lien == "" || $lien == "penalite") {
                $link = "auditPenalite";
                $titre = "Liste des opportunités en attente d'audit de Pénalité";
                $opportunities = $this->opportunityModel->getOpAudit("reglement", $role, $isGestionnaire, $idUser, [$fTypeOp, $fSiteId, $fGest, $fEtat]);
            }
            if ($lien == "rvrt") {
                $link = "auditRvRt";
                $titre = "Liste des opportunités en attente d'audit de RV RT";
                $opportunities = $this->opportunityModel->getOpAudit("rvrt", $role, $isGestionnaire, $idUser, [$fTypeOp, $fSiteId, $fGest, $fEtat]);
            }
            if ($lien == "declaration") {
                $link = "auditDeclaration";
                $titre = "Liste des opportunités en attente d'audit de déclaration de sinistre, de numèro de sinistre et de délégation";
                $opportunities = $this->opportunityModel->getOpAudit("declaration", $role, $isGestionnaire, $idUser, [$fTypeOp, $fSiteId, $fGest, $fEtat]);
            }
            if ($lien == "devis") {
                $link = "auditDevis";
                $titre = "Liste des opportunités en attente d'audit de devis";
                $opportunities = $this->opportunityModel->getOpAudit("devis", $role, $isGestionnaire, $idUser, [$fTypeOp, $fSiteId, $fGest, $fEtat]);
            }
            if ($lien == "anciennesOp") {
                $link = "anciennesOp";
                $titre = "Liste des anciennes opportunités en attente d'audit";
                $opportunities = $this->opportunityModel->getOpAudit("anciennesOP", $role, $isGestionnaire, $idUser, [$fTypeOp, $fSiteId, $fGest, $fEtat]);
            }
            if ($lien == "franchise") {
                $link = "auditFranchise";
                $titre = "Liste des opportunités en attente d'audit de franchise";
                $opportunities = $this->opportunityModel->getOpAudit("franchise", $role, $isGestionnaire, $idUser, [$fTypeOp, $fSiteId, $fGest, $fEtat]);
            }

            $data = [
                "link" => $link,
                "lien" => $lien,
                "opportunities" => $opportunities,
                "titre" => $titre,
                "gestionnaires" => $gestionnaires,
                "sites" => $sites,
                'flitres' => $flitres,
                'fSite' => $fSite,
                'fGest' => $fGest,
                'fEtat' => $fEtat,
                'fTypeOp' => $fTypeOp,
                "idUser" => $idUser
            ];
            $this->view('gestionnaire/audit/' . __FUNCTION__, $data);
        }
    }

    public function audit($typeAudit = "", $idOp = "")
    {
        $isOpen = false;
        $nextLien = URLROOT . "/Gestionnaire/audit/$typeAudit/$idOp";
        $lien = $this->userAccessModel->findByLien($nextLien);
        if ($lien && $lien->idUserF !=  $_SESSION["connectedUser"]->idUtilisateur) {
            $isOpen = true;
            // header("location:javascript://history.go(-1)");
        } else {
            //SAVE LIEN
            $this->userAccessModel->deleteByUser($_SESSION["connectedUser"]->idUtilisateur);
            $this->userAccessModel->addLien($nextLien, $_SESSION["connectedUser"]->idUtilisateur, $_SESSION["connectedUser"]->fullName, date("Y-m-d H:i:s"));
        }
        if (!$isOpen) {
            $op = $this->opportunityModel->findByIdOp($idOp);
            $activityDE = $this->activityModel->findActivityByOp(1, $idOp);
            $activityTE = $this->activityModel->findActivityByOp(2, $idOp);
            $activityRvRT = $this->activityModel->findActivityByOp(3, $idOp);
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");
            $notes = $this->noteModel->getNoteByOpportunity($idOp, "interne");
            $activities = $this->activityModel->getActivitiesByOp($idOp);
            $listArtisanDevis = $this->artisanModel->getAllArtisanDevis();
            $titre = $typeAudit == "auditDeclaration" ? "Audit Déclaration de sinistre - N° de Sinistre et Délégation de gestion" : ($typeAudit == "anciennesOp" ? "AUDIT DES OP" : ($typeAudit == "auditRvRt" ? "AUDIT RDV RT" : ($typeAudit == "auditFranchise" ? "AUDIT FRANCHISE" : ($typeAudit == "auditDevis" ? "AUDIT DEVIS" : "AUDIT PENALITE"))));
            $pieces = $this->pieceModel->getAll();
            $rt = $op->rt;
            $rdv = $this->rvModel->getRVByIdOp($idOp);
            $devis = $this->devisModel->getOpDevis($idOp);

            $allImmeubles = $this->immeubleModel->getAllImmeubles(); // Nouveau ajouté Espoir
            $allAppImmeuble = $this->lotModel->getLotByOpportunity($idOp); // Nouveau ajouté Espoir
            $artisans =  $this->utilisateurModel->getUserByidsRoles(6, 0, "artisan"); // Nouveau ajouté Espoir

            /*******************  Ajouté nouvellement par Espoir ******************/
            $cies = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            /*$gestionnaires = $this->utilisateurModel->getUserByidsRoles(8, 27, "gestionnaire"); 
            $experts = $this->utilisateurModel->getUserByidsRoles(28, 3, "gestionnaire"); */

            $gestionnaires = $this->utilisateurModel->getUsersByType('wbcc');
            $experts = $this->utilisateurModel->getUsersByType('wbcc');


            $equipements = $this->equipementModel->getAllEquipements('libelleEquipement ASC');
            $demanderReparationFuite = $this->activityModel->findActivityByOp(33, $idOp);
            $compteRenduRT = $op->compteRenduRT != NULL && $op->compteRenduRT != '' ? $this->documentModel->findByName($op->compteRenduRT) : false;

            $compteRenduRT = new stdClass();
            if ($op->compteRenduRT == 2) {
                $compteRenduRT->key = 2;
                $compteRenduRT->value = array();
            }
            if ($op->compteRenduRT == NULL && $op->compteRenduRT == '') {
                $compteRenduRT->key = 0;
                $compteRenduRT->value = array();
            } else {
                $compteRenduRT->key = 1;
                $compteRenduRT->value = $this->documentModel->findByName($op->compteRenduRT);
            }

            /******************* fin  Ajouté nouvellement par Espoir ******************/
            $data = [
                "titre" => $titre,
                "notes" => $notes,
                "documents" => $documents,
                "activities" => $activities,
                "typeAudit" => $typeAudit,
                "op" => $op,
                "activityTE" => $activityTE,
                "activityDE" => $activityDE,
                "activityRvRT" => $activityRvRT,
                "immeuble" => $op->immeuble,
                "app" => $op->app,
                "pieces" => $pieces,
                "rt" => $rt,
                "rdv" => $rdv,
                "devis" => $devis,
                "listArtisanDevis" => $listArtisanDevis,
                "cies" => $cies,
                "gestionnaires" => $gestionnaires,
                "experts" => $experts,
                "equipements" => $equipements,
                "demanderReparationFuite" => $demanderReparationFuite,
                "compteRenduRT" => $compteRenduRT,
                "allImmeubles" => $allImmeubles, // Ajouté nouvellement par Espoir
                "allAppImmeuble" => $allAppImmeuble, // Ajouté nouvellement par Espoir
                "artisans" => $artisans, // Ajouté nouvellement par Espoir
            ];
            if ($typeAudit == 'anciennesOp') {
                $this->view("gestionnaire/audit/anciennesOp", $data);
            } else {
                $this->view("gestionnaire/audit/audit", $data);
            }
        } else {
            header("location:javascript://history.go(-1)");
        }
    }

    public function save()
    {
        $form = getForm($_POST);
        extract($form);
        $tabDoc = [];
        echo "<pre>";

        $referenceDO = ($referenceDO == "") ? null : $referenceDO;
        $pm = isset($partieMitoyenne) ? 1 : 0;
        $to = "gestion@wbcc.fr";
        $idContact = Role::connectedUser()->idContact;
        $do = $this->companyModel->findByContact($idContact);
        $fileNames = [];
        $files = [];

        for ($i = 0; $i < sizeof($_FILES['files']['name']) - 1; $i++) {
            $tmp_name = $_FILES["files"]["tmp_name"][$i];
            // $name = $_FILES["files"]["name"][$i];
            $tab = explode(".", $_FILES['files']['name'][$i]);
            $nomFichier = $name . "_" . date("dmYHis") . "_" . $idContact . "." . $tab[sizeof($tab) - 1];

            if (!file_exists('../public/documents/opportunite/' . $nomFichier)) {
                move_uploaded_file($tmp_name, '../public/documents/opportunite/' . $nomFichier);
                $tabDoc[] = [
                    "nom" => $_POST["nom" . ($i + 1)],
                    "fichier"  => $nomFichier
                ];
                $fileNames[] = $_POST["nom" . ($i + 1)] . "." . $tab[sizeof($tab) - 1];
                $files[] = "/public/documents/opportunite/" . $nomFichier;
            }
        }

        if ($num != "0") {
            $this->opportunityModel->updateOpportunity(
                $num,
                $name,
                $referenceDO,
                $type,
                $partieConcernee,
                $pm,
                $causeMission,
                $typeIntervention,
                $corpsMetier,
                $commentaireOP
            );
            foreach ($tabDoc as $doc) {
                $docNumero = date("dmYHis");
                $idDoc = $this->documentModel->save($docNumero, $doc['nom'], $doc['fichier']);
                $this->historiqueModel->save("Ajout du Fichier " . $doc['nom']);
                $this->documentModel->addOpportunityDocument($num, $idDoc, $numeroOp, $docNumero);
            }
            //Send Mail Gestion
            if (sizeof($files) != 0) {
                $body = "Bonjour, <br><br> <b>" . Role::nomComplet() . "</b> de <b>" . $nomSyndic . "</b> vient d'ajouter des documents.<br><br><b>WBCC ASSISTANCE Extranet</b>";
                Role::mailExtranetWithFiles($to, "$name / Ajout Document extranet", $body, [], $files, $fileNames);
                $ccs = [];
                // if ($do && trim($do->email) != "") {
                //     $ccs[] = $do->email;
                // }
                Role::mailExtranetWithFiles(Role::connectedUser()->login, "$name / Ajout Document", "Bonjour " . Role::nomComplet() . ",<br><br> Vous venez d'ajouter des documents.<br><br><b>WBCC ASSISTANCE Extranet</b>", $ccs, $files, $fileNames);
            }

            $this->redirectToMethod("Gestionnaire", "dossier", $num);
        } else {
            $verif = false;
            if ($referenceDO != "") {
                $verif = $this->opportunityModel->findByReferenceDO($referenceDO);
            }

            if (!$verif) {
                $idContact = Role::connectedUser()->idContact;

                $numero = date('dmyhis');
                $id = $this->opportunityModel->addOpportunity(
                    $numero,
                    $name,
                    $referenceDO,
                    $type,
                    $partieConcernee,
                    $pm,
                    $causeMission,
                    $typeIntervention,
                    $corpsMetier,
                    $commentaireOP
                );

                if ($id != "0") {
                    foreach ($tabDoc as $doc) {
                        $docNumero = date("dmYHis");
                        $idDoc = $this->documentModel->save($docNumero, $doc['nom'], $doc['fichier']);
                        $this->historiqueModel->save("Ajout du Fichier " . $doc['nom']);
                        $this->documentModel->addOpportunityDocument($id, $idDoc, $numero, $docNumero);
                    }
                    $pm2 = ($pm == 1) ? "Oui" : "Non";
                    $this->opportunityModel->insertCompanyOpportunity($do->idCompany, $id);
                    $param = $this->parametreModel->getParametres();
                    //MAIL DEMANDE DE CREATION
                    $numero = str_pad(($param->numeroDemandeValidation + 1), 8, '0', STR_PAD_LEFT);
                    $subject = $name . ": Demande de création du dossier";
                    $body = "WBCC EXTRANET  |  " . date("d/m/Y") . " <br><br> Bonjour,<br><br>Nous demandons la création du dossier :<br>
                    <u>N° Dossier WBCC</u> : <b>" . $name . "</b><br>
                    <u>Référence Donneur d'ordre</u> : <b>" . $referenceDO . "</b><br>
                    <u>Date demande</u> : <b>" . date("d/m/Y") . "</b><br>
                    <u>Type</u> : <b>" . $type . "</b><br>
                    <u>Partie concernée</u> : <b>" . $partieConcernee . "</b><br>
                    <u>Partie Mitoyenne</u> : <b>" . $pm2 . "</b><br>
                    <u>Cause de la mission</u> : <b>" . $causeMission . "</b><br>
                    <u>Type d'intervention</u> : <b>" . $typeIntervention . "</b><br>
                    <u>Corps de metier</u> : <b>" . $corpsMetier . "</b><br>
                    <br><br><br>Email envoyé depuis l'extranet de WBCC par <br>
                    <b> " . Role::nomComplet() . "</b><br>
                    <b>" . $do->name . ".</b>";
                    // if (Role::mailOnServer($to, $subject, $body)) {
                    //     // $this->parametreModel->updateParametre("numeroDemandeValidation", ($param->numeroDemandeValidation + 1));
                    //     $this->parametreModel->updateParametre("numeroOpProvisoire", ($param->numeroOpProvisoire + 1));
                    //     //MAIL RETOUR
                    //     $subject1 = "Demande de création du dossier $name";
                    //     $body1 = "WBCC EXTRANET  |  " . date("d/m/Y") . " <br><br> 
                    //         Bonjour " . Role::nomComplet() . ",<br><br>
                    //         Merci d'avoir soumis une requête auprès de nos services. Nous avons bien pris en compte votre demande et sommes en train de travailler dessus pour vous répondre dans les meilleurs délais.
                    //         <br><br>Voici les détails de votre demande :
                    //         <br><br>
                    //         - Ticket : <b>$name</b> <br>
                    //         - Sujet : <b>$subject</b> <br>
                    //         - Description : <br>$body
                    //        ";
                    //     Role::mailOnServer($_SESSION['connectedUser']->login, $subject1, $body1);
                    // }
                }
                $this->redirectToMethod("Gestionnaire", "dossier", $id);
            } else {
                $this->redirectToMethod("Gestionnaire", "index", 0);
            }
        }
    }
}
