<?php

class ComptableCtrl extends Controller
{
    public function __construct()
    {

        if (isset($_SESSION["connectedUser"]) && $_SESSION["connectedUser"]->isInterne != '1') {
            $this->redirectToMethod('Home', 'index');
        }

        $this->roleModel = $this->model('Roles');
        $this->utilisateurModel = $this->model('Utilisateur');
        $this->activityModel = $this->model('Activity');
        $this->opportunityModel = $this->model('Opportunity');
        $this->parametreModel = $this->model('Parametres');
        $this->documentModel = $this->model('Document');
        $this->contactModel = $this->model('Contact');
        $this->personnelModel = $this->model('Personnel');
        $this->companyModel = $this->model('Company');
        $this->immeubleModel = $this->model('Immeuble');
        $this->lotModel = $this->model('Lot');
        $this->documentModel = $this->model('Document');
        $this->historiqueModel = $this->model('Historique');
        $this->noteModel = $this->model('Note');
        $this->pieceModel = $this->model('Piece');
        $this->bienModel = $this->model('Bien');
        $this->rvModel = $this->model('RendezVous');
        $this->devisModel = $this->model('Devis');
        $this->artisanModel = $this->model('Artisan');
        $this->userAccessModel = $this->model('UserAccess');
        $this->enveloppeModel = $this->model('Enveloppe');
        $this->siteModel = $this->model('Site');
    }

    public function indexJournal($type = 'tous', $lien = '')
    {
        $journaux = $this->enveloppeModel->getAllJournaux();
        $titre = "Liste des Journaux";

        $data = [
            "titre" => $titre,
            "journaux" => $journaux,
        ];
        $this->view('comptable/' . __FUNCTION__, $data);
    }

    public function listEncaissement($idJournal)
    {
        $encaissements = $this->enveloppeModel->getEncaissementsByIdJournal($idJournal);
        $journal = $this->enveloppeModel->getJournalByIdJournal($idJournal);
        $titre = "Ecritures Comptables des Encaissements";

        $data = [
            "titre" => $titre,
            "encaissements" => $encaissements,
            "journal" => $journal
        ];
        $this->view('comptable/' . __FUNCTION__, $data);
    }

    public function indexEncaissement()
    {
        $role = $_SESSION['connectedUser']->role;
        $idSite = $_SESSION['connectedUser']->idSite;
        $idUser = $_SESSION['connectedUser']->idUtilisateur;
        $statut = "tous";
        $site = ($role == 3 || $role == 25) ? "$idSite" : "tous";
        $gestionnaire = ($role == 1 || $role == 2 || $role == 9 || $role == 8) ?    "tous" : $idUser;
        $typeIntervention = "tous";
        $commercial = "tous";
        $periode = "all";
        $date1 = "";
        $date2 = "";
        if (isset($_GET)) {
            extract($_GET);
        }

        $titre = "LISTE DES ENCAISSEMENTS ";
        if ($typeIntervention != "tous") {
            $titre .= "$typeIntervention";
        }
        $titre .= " EFFECTUES PAR ";
        //GESTIONNAIRE
        if ($gestionnaire != "tous") {
            $contact = $this->utilisateurModel->findUserById($gestionnaire);
            $titre .= "$contact->fullName POUR ";
        }
        //SITE
        if ($site == "tous") {
            $titre .= " WBCC ";
        } else {
            $siteObj = $this->siteModel->findById($site);
            $titre .= " LE SITE DE '$siteObj->nomSite'";
        }
        //COMMERCIAL
        if ($commercial != "tous") {
            $contact = $this->utilisateurModel->findUserById($commercial);
            $titre .= " AVEC LE COMMERCIAL $contact->fullName";
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

        $encaissements = $this->enveloppeModel->getAllEncaissementsOP($statut, $site, $gestionnaire, $typeIntervention, $commercial, $periode, $date1, $date2);
        $total = 0;
        foreach ($encaissements as $key => $value) {
            $total += $value->montantEncaissement;
        }

        $sites = $this->siteModel->getAllSites();
        $gestionnaires = [];
        if ($role == 1 || $role == 2 || $role == 25 || $role == 9 || $role == 8) {
            $gestionnaires = $this->utilisateurModel->getUserByidsRoles(3, 25, "gestionnaire", ($site == "tous"  ? "" : $site));
        } else {
            $gestionnaires[] =  $_SESSION['connectedUser'];
        }
        $commerciaux = $this->utilisateurModel->getUserByidsRoles(5, "", "commercial", "");
        $data = [
            "sites" => $sites,
            "titre" => $titre,
            "encaissements" => $encaissements,
            "gestionnaires" => $gestionnaires,
            "total" => $total,
            "statut" => "$statut",
            "site" => $site,
            "gestionnaire" => $gestionnaire,
            "periode" => "$periode",
            "date1" => "$date1",
            "date2" => "$date2",
            "idUser" => $idUser,
            "typeIntervention" => $typeIntervention,
            "commercial" => $commercial,
            "commerciaux" => $commerciaux
        ];
        $this->view('comptable/' . __FUNCTION__, $data);
    }



    public function indexTache()
    {
        $idContact = Role::connectedUser()->idUtilisateur;
        $taches = $this->activityModel->getTacheByUser($idContact);
        $users =  $this->utilisateurModel->getUsersByType('wbcc');
        $data = [
            "taches" => $taches,
            "titre" => "Liste des Tâches : " . Role::connectedUser()->fullName,
            "users" => $users
        ];
        $this->view('comptable/' . __FUNCTION__, $data);
    }

    public function encaissementCheque($idCheque)
    {

        $cheque = $this->enveloppeModel->findChequeById($idCheque);
        $banques = $this->parametreModel->getListeDeroulante("wbcc_banque");
        $notes = [];

        $data = [
            "cheque" => $cheque,
            "notes" => $notes,
            "banques" => $banques
        ];
        $this->view('comptable/' . __FUNCTION__, $data);
    }

    public function indexCheque($type = 'tous', $lien = '')
    {
        $cheques = $this->enveloppeModel->getAllCheques();
        $titre = "Tous les Chèques";
        $comptes = $this->enveloppeModel->getCompteBancaire();
        $link = '';

        if ($type == 'encaisse') {
            $titre = "Liste des Chèques Encaissés";
            $cheques = array_filter($cheques, function ($env) {
                return $env->etatCheque == 1;
            });
        } else {
            if ($type == 'attente') {
                $titre = "Liste des Chèques en Attente";
                $cheques = array_filter($cheques, function ($env) {
                    return $env->etatCheque == 0;
                });
            }
        }

        $data = [
            "titre" => $titre,
            "type" => $type,
            "cheques" => $cheques,
            "comptes" => $comptes
        ];
        $this->view('comptable/' . __FUNCTION__, $data);
    }

    public function indexEnveloppe($type = 'tous', $lien = '')
    {
        $enveloppes = $this->enveloppeModel->getAllEnveloppes();
        $enveloppeEnCours = $this->enveloppeModel->findEnveloppeOuvert();
        $titre = "Toutes les Enveloppes";
        $link = '';
        if ($lien == 'me') {
            $enveloppes = array_filter($enveloppes, function ($env) {
                return $env->idAuteurCreation == $_SESSION['connectedUser']->idUtilisateur;
            });
        }
        if ($type == 'cloture') {
            $titre = "Liste des Enveloppes Clôturées";
            $enveloppes = array_filter($enveloppes, function ($env) {
                return $env->etatEnveloppe == 1;
            });
        } else {
            if ($type == 'imprime') {
                $titre = "Liste des Enveloppes Imprimées";
                $enveloppes = array_filter($enveloppes, function ($env) {
                    return $env->etatImpression == 1;
                });
            } else {
                if ($type == 'depotPoste') {
                    $titre = "Liste des Enveloppes en attente de Dépôt à la Poste";
                    $enveloppes = array_filter($enveloppes, function ($env) {
                        return $env->etatImpression == 1 && $env->depotPoste == 0;
                    });
                } else {
                    if ($type == 'depotAccuse') {
                        $titre = "Liste des Enveloppes en Accusé de Réception";
                        $enveloppes = array_filter($enveloppes, function ($env) {
                            return $env->depotPoste == 1 && $env->accuseReception == 0;
                        });
                    } else {
                        if ($type == 'attenteImpression') {
                            $titre = "Liste des Enveloppes en Attente d'Impression";
                            $enveloppes = array_filter($enveloppes, function ($env) {
                                return $env->etatEnveloppe == 1 && $env->etatImpression == 0;
                            });
                        } else {
                            if ($type == 'tous') {
                                $type = 'detail';
                                $titre = "Liste de toutes les Enveloppes";
                            }
                        }
                    }
                }
            }
        }
        $comptables = $this->utilisateurModel->getUserByidsRoles(9, "", "");
        $etapes = [];
        $etapes[] = ["nom" => "attenteImpression", "libelle" => "1- En attente d'impression"];
        $etapes[] = ["nom" => "depotPoste", "libelle" => "2- En attente de Justificatif de dépôt"];
        $etapes[] = ["nom" => "depotAccuse", "libelle" => "3- En attente d'Accusé de Réception"];
        $data = [
            "titre" => $titre,
            "type" => $type,
            "enveloppes" => $enveloppes,
            "enveloppeEnCours" => $enveloppeEnCours,
            "comptables" => $comptables,
            "etapes" => $etapes
        ];
        $this->view('comptable/' . __FUNCTION__, $data);
    }

    function detail($idEnveloppe)
    {
        $enveloppe = $this->enveloppeModel->findEnveloppeById($idEnveloppe);
        $enveloppeEnCours = $this->enveloppeModel->findEnveloppeOuvert();
        $notes = $this->noteModel->getNoteByEnveloppe($idEnveloppe);
        $documents = $this->documentModel->getByEnveloppe($idEnveloppe);
        $banques = $this->parametreModel->getListeDeroulante("wbcc_banque");
        $comptes = $this->enveloppeModel->getCompteBancaire();

        $data = [
            "enveloppe" => $enveloppe,
            "enveloppeEnCours" => $enveloppeEnCours,
            "banques" => $banques,
            "notes" => $notes,
            "documents" => $documents,
            "comptes" => $comptes
        ];
        $this->view('comptable/' . __FUNCTION__, $data);
    }

    function depotPoste($idEnveloppe)
    {
        $enveloppe = $this->enveloppeModel->findEnveloppeById($idEnveloppe);
        $data = [
            "enveloppe" => $enveloppe
        ];
        $this->view('comptable/' . __FUNCTION__, $data);
    }



    function depotAccuse($idEnveloppe)
    {
        $enveloppe = $this->enveloppeModel->findEnveloppeById($idEnveloppe);
        $data = [
            "enveloppe" => $enveloppe
        ];
        $this->view('comptable/' . __FUNCTION__, $data);
    }


    public function detailEnveloppe($idEnveloppe)
    {
        $enveloppe = $this->enveloppeModel->findEnveloppeById($idEnveloppe);
        $data = [
            "enveloppe" => $enveloppe
        ];
        $this->view('comptable/' . __FUNCTION__, $data);
    }

    public function reglement($idOp)
    {

        $isOpen = false;
        $nextLien = URLROOT . "/Comptable/reglement/$idOp";

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
            $enveloppe = $this->enveloppeModel->findEnveloppeOuvert();
            $tousContacts = $this->contactModel->getAllContacts();
            $tousCieAssurance = $this->companyModel->getCompaniesByIdStatut("ASSURANCE");
            $cabinetExperts = $this->companyModel->getCompaniesByIdStatut("EXPERT");
            $op = $this->opportunityModel->findByIdOp($idOp);
            $immeuble = $op->immeuble;
            $guidComp = $op->typeSinistre == "Partie commune exclusive" ? $op->guidComMRI : $op->guidComMRH;
            $cie = false;
            if ($guidComp != null && $guidComp != "") {
                $cie = $this->companyModel->findByNumero($guidComp);
            }
            if (!$cie &&  $op->typeSinistre == "Partie commune exclusive" && $op->type != "A.M.O.2") {
                if ($immeuble &&  $immeuble->idCompagnieAssurance != null && $immeuble->idCompagnieAssurance != "") {
                    $cie = $this->companyModel->findById($immeuble->idCompagnieAssurance);
                }
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
            $activityEncaisseImmediat = $this->activityModel->findActivityByOp(21, $idOp);

            $do = ($op->guidGestionnaireAppImm != null && $op->guidGestionnaireAppImm != "") ? $this->companyModel->findByNumero($op->guidGestionnaireAppImm) : false;
            $prescripteur = ($op->guidGestionnaireAppImm != null && $op->guidGestionnaireAppImm != "") ? $this->companyModel->findByNumero($op->guidGestionnaireAppImm) : false;
            if ($do == false) {
                $do = ($op->guidDO != null && $op->guidDO != "" && strtolower($op->typeDO) != "particuliers") ? $this->companyModel->findByNumero($op->guidDO) : false;
            }

            $app = $op->app;
            $rdv = $this->rvModel->getRVByIdOp($idOp);
            $rt = $op->rt;
            $devis = $this->devisModel->getOpDevis($idOp);
            $artisan = false;
            if ($devis) {
                $artisan = $this->artisanModel->findArtisanByID($devis->idArtisanF);
            }
            $listArtisanDevis = $this->artisanModel->getAllArtisanDevis();
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");
            $encaissements = $this->opportunityModel->getEncaissementsByType($idOp);
            $cheques = $this->opportunityModel->getChequeByOP($idOp);
            $banques = $this->parametreModel->getListeDeroulante("wbcc_banque");
            $comptes = $this->enveloppeModel->getCompteBancaire();
            $data = [
                "enveloppe" => $enveloppe,
                "op" => $op,
                "banques" => $banques,
                "comptes" => $comptes,
                "encaissements" => $encaissements,
                "cheques" => $cheques,
                "tousContacts" => $tousContacts,
                "activityEncaisseImmediat" => $activityEncaisseImmediat,
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
                "cabinetExperts" => $cabinetExperts,
                "cabinetExpert" => $cabinetExpert,
                "artisan" => $artisan
            ];
            $this->view('comptable/encaissement', $data);
        }
    }

    public function listeReglement($type = '')
    {
        $titre = "Toutes les opportunités en attente de réglement";
        $opportunities = [];
        $opportunities = $this->opportunityModel->getOpByStatutAndActivity("Open", "envoiDevis");
        $opportunities = array_filter($opportunities, function ($op) {
            return $op->devisRegle == 0;
        });

        // if ($type == "fse") {
        //     $titre = "Toutes les opportunités en attente de réglement de la franchise";
        //     $opportunities = $this->opportunityModel->getOpTache("26", "", "");
        // }
        // if ($type == "relance") {
        //     $titre = "Toutes les opportunités relancées en attente de réglement";
        //     $opportunities = array_filter($opportunities, function ($op) {
        //         return $op->relanceCiePaiementImmediat == 1 || $op->relanceCiePaiementDiffere == 1;
        //     });
        // }
        // if ($type == "nonRelance") {
        //     $titre = "Toutes les opportunités non relancées en attente de réglement";
        //     $opportunities = array_filter($opportunities, function ($op) {
        //         return  $op->relanceCiePaiementImmediat == 0 && $op->relanceCiePaiementDiffere == 0;
        //     });
        // }

        if (isset($_SESSION['isConnected']) && $_SESSION['isConnected']) {
            $idContact = Role::connectedUser()->idContact;
            $data = [
                "type" => $type,
                "opportunities" => $opportunities,
                "titre" => $titre
            ];
            $this->view('comptable/' . __FUNCTION__, $data);
        } else {
            $this->redirectToMethod('Home', 'connexion');
        }
    }

    public function comptabilite()
    {
        if (isset($_SESSION['isConnected']) && $_SESSION['isConnected']) {
            $idContact = Role::connectedUser()->idContact;
            $this->view('comptable/' . __FUNCTION__);
        } else {
            $this->redirectToMethod('Home', 'connexion');
        }
    }
}
