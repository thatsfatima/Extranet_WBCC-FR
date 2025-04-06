<?php
class GestionOPCtrl extends Controller
{
    public function __construct()
    {
        if (isset($_SESSION["connectedUser"]) && $_SESSION["connectedUser"]->isInterne != '1') {
            $this->redirectToMethod('Home', 'index');
        }
        $this->activityUtilisateur = $this->model('Utilisateur');
        $this->activityModel = $this->model('Activity');
        $this->opportunityModel = $this->model('Opportunity');
        $this->parametreModel = $this->model('Parametres');
        $this->documentModel = $this->model('Document');
        $this->sommaireModel = $this->model('Sommaire');
        $this->sectionModel = $this->model('Section');
        $this->contactModel = $this->model('Contact');
        $this->siteModel = $this->model('Site');
        $this->utilisateurModel = $this->model('Utilisateur');
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
    }

    public function index()
    {
        header("location:javascript://history.go(-1)");
    }

    public function tbdOpportunite($data = [])
    {
        $idUser = $_SESSION['connectedUser']->idUtilisateur;
        $role = $_SESSION['connectedUser']->role;
        $periode = "all";
        $site = "tous";
        $gestionnaire = "tous";
        $statut = "tous";
        $typeIntervention = "tous";
        $date1 = "";
        $date2 = "";

        if (isset($_POST))
        extract($_POST);
        //PERIODE
        if ($periode != "all" && $periode != "perso" && $periode != "day" && $periode != "today") {
            $re = getPeriodDates("$periode", []);
            if (sizeof($re) != 0) {
                $date1 = $re['startDate'];
                $date2 = $re['endDate'];
            }
        }
        $sites = $this->siteModel->getAllSites();
        $gestionnaires = [];
        $ges = [];
        if ($site != "" && $site != "tous") {
            $gestionnaires = $this->activityUtilisateur->getUserByidsRoles(3, 25, "gestionnaire", ($site));
        } else {
            $gestionnaires = $this->activityUtilisateur->getUserByidsRoles(3, 25, "gestionnaire", "");
        }
        //GESTIONNAIRE
        if ($gestionnaire != "tous") {
            $userOP = $gestionnaire;
            $ges[$gestionnaire] = $this->activityUtilisateur->findUserById($gestionnaire);
        }
        else if ($gestionnaire == "tous") {
            $userOP = "";
            foreach ($gestionnaires as $gest) {
                $ges[$gest->idUtilisateur] = $this->activityUtilisateur->findUserById($gest->idUtilisateur);
            }
        }
        else {
            $userOP = "";
            $ges = [];
        }
        
        $activites = $this->activityModel->getActivitiesDB(1, "codeActivity", "ASC");
        
        $nbAppels = ["total" => 0, "entrant" => 0, "sortant" => 0];
        $nbMessages = ["total" => 0, "entrant" => 0, "sortant" => 0];
        $nbMails["total"] = $this->opportunityModel->getNbMails($gestionnaire, $site, $typeIntervention, $periode, $date1, $date2)->nb;
        $nbMails["entrant"] = $this->opportunityModel->getNbMails($gestionnaire, $site, $typeIntervention, $periode, $date1, $date2, "entrantSortant", "1")->nb;
        $nbMails["sortant"] = $this->opportunityModel->getNbMails($gestionnaire, $site, $typeIntervention, $periode, $date1, $date2, "entrantSortant", "2")->nb;

        $tabAllCodesOP = [-1, -2, -3];
        $tousTotal = 0;
        
        $tabTotals['-1'] = $nbMails["sortant"] ?? 0;
        $tabTotals['-2'] = $nbAppels["sortant"] ?? 0;
        $tabTotals['-3'] = $nbMessages["sortant"] ?? 0;
        
        $mailsSortant = new stdClass();
        $appelsSortant = new stdClass();
        $messagesSortant = new stdClass();
        
        $mailsSortant->codeActivity = -1;
        $appelsSortant->codeActivity = -2;
        $messagesSortant->codeActivity = -3;
        
        $mailsSortant->libelleActivity = "Mails";
        $appelsSortant->libelleActivity = "Appels";
        $messagesSortant->libelleActivity = "Messages";
        
        $activities = [$mailsSortant, $appelsSortant, $messagesSortant];
        // STATUS
        if ($statut != "tous" && $statut != "enCours" && $statut != "attenteCloture" && $statut != "won" && $statut != "lost") {
            $tabAllCodesOP[] = $statut;
            $tabTotals[$statut] = 0;
            $activities[] = $this->activityModel->findByCode($statut);
        }
        else {
            $tabAllCodesOP[] = 0;
            $tabTotals[0] = 0;
            foreach ($activites as $activitie) {
                $tabAllCodesOP[] = $activitie->codeActivity;
                $tabTotals[$activitie->codeActivity] = 0;
                $activities[] = $activitie;
                if ($activitie->codeActivity == 11) {
                    $frtRejetes = new stdClass($activitie);
                    $frtRejetes->codeActivity = 0;
                    $frtRejetes->libelleActivity = "FRT rejetés";
                    $activities[] = $frtRejetes;
                }
            }
        }
        
        foreach ($ges as $key => $value) {
            $i = min(255, rand(0, 255));
            $j = min(255, rand(0, 255));
            $k = min(255, rand(0, 255));
            $ges[$key]->color = "rgb($i,$j,$k)";
            $ges[$key]->Total = 0;
            foreach ($tabAllCodesOP as $code) {
                if ( $code >= 0 ) {
                    $ges[$key]->codes[$code] = $this->opportunityModel->getNbTacheEffectGest($value->idUtilisateur, $periode, $date1, $date2, $code, $statut, $typeIntervention);
                    $tabTotals[$code] = $tabTotals[$code] + $ges[$key]->codes[$code];
                    $ges[$key]->Total = $ges[$key]->Total + $ges[$key]->codes[$code];
                }
                else {
                    $ges[$key]->codes[$code] = 0;
                }
            }
            $tousTotal = $tousTotal + $ges[$key]->Total;
            $ges[$key]->codes[-1] = $ges[$key]->codes[-1] + $this->opportunityModel->getNbMails($ges[$key]->idUtilisateur, $site, $typeIntervention, $periode, $date1, $date2, "entrantSortant", "2")->nb;
            $ges[$key]->codes[-2] = 0;
            $ges[$key]->codes[-3] = 0;
        }
        
        $data = [
            "act" => $activites,
            "activites" => $activities,
            "tabGes" => $ges,
            "tabAllCodesOP" => $tabAllCodesOP,
            "tabTotalAllOP" => $tabTotals,
            "Total" => $tousTotal,
            "nbMails" => $nbMails,
            "nbAppels" => $nbAppels,
            "nbMessages" => $nbMessages,
            "sites" => $sites,
            "gestionnaires" => $gestionnaires,
            "statut" => "$statut",
            "site" => $site,
            "gestionnaire" => $gestionnaire,
            "periode" => "$periode",
            "date1" => "$date1",
            "date2" => "$date2",
            "idUser" => $idUser,
            "typeIntervention" => $typeIntervention
        ];
    
        $this->view("gestionnaire/opportunite/tbOP", $data);
    }

    public function listeOP($code = "", $precision = '')
    {
        $columnDate = "";
        $columType = "";
        $columnAut = "";
        $site = "";
        $type = explode(";", $precision)[0];
        $periode = explode(";", $precision)[1];
        $date1 = explode(";", $precision)[2];
        $date2 = explode(";", $precision)[3];
        $statut = explode(";", $precision)[4];
        $user = explode(";", $precision)[5];
        if (isset($precision[6]))
        $site = explode(";", $precision)[6];

        if ($code != 0) {
            $activity = $this->activityModel->findByCode($code);
            $titre = "Les opportunités avec " . $activity->libelleActivity;
        } else {
            $activity = $this->activityModel->findByCode(11);
            $titre = "Les opportunités avec les FRT rejetes";
        }
        $columnDate = $activity->nomVariableDateOP;
        $columType = $activity->nomVariableOP;
        $columnAut = $activity->nomVariableIdAuteurOP;

        $opportunities = $this->opportunityModel->getListeOPByTypeAuteurPeriode($periode, $columnDate, $columType, $date1, $date2, $user, $site, $columnAut, $code, $statut);

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

    public function saveSections()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->sendErrorResponse('Erreur de décodage JSON : ' . json_last_error_msg(), 400);
        }

        if (empty($data['idSommaire']) || !is_array($data['sections'])) {
            return $this->sendErrorResponse('Données invalides ou manquantes', 400);
        }

        $idSommaire = $data['idSommaire'];
        $sections = $data['sections'];

        file_put_contents('C:/xampp/htdocs/Extranet_WBCC-FR/debug.log', print_r([
            'decoded_data' => $sections,
            'idSommaire' => $idSommaire
        ], true), FILE_APPEND);

        try {
            if (empty($idSommaire) || !is_array($sections)) {
                throw new InvalidArgumentException("Données invalides : idSommaire ou sections incorrects.");
            }

            $tab = [];
            $ignored = [];

            foreach ($sections as $section) {
                if (!isset($section['numeroSection'], $section['titreSection'])) {
                    $ignored[] = $section;
                    continue;
                }

                try {
                    $tab[] = $this->sectionModel->getSectionsForRT(
                        $idSommaire,
                        $section['numeroSection'],
                        $section['titreSection'],
                        $section['contenuSection'],
                        $section['idSection_parentF']
                    );
                } catch (Exception $e) {
                    error_log("Erreur lors du traitement de la section : " . $e->getMessage());
                    $ignored[] = $section;
                }
            }

            $result = [
                'processed' => $tab,
                'ignored' => $ignored
            ];
            
            return $this->sendSuccessResponse('Sections enregistrées avec succès', $result);
        } catch (Exception $e) {
            return $this->sendErrorResponse('Erreur : ' . $e->getMessage(), $e->getCode() ?: 500);
        }
    }

    protected function sendErrorResponse($message, $code = 400)
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }

    protected function sendSuccessResponse($message, $data = [])
    {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

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
            $sommaire = $this->sommaireModel->sommaireForRT($rt);
            $sections = $this->sectionModel->getSectionBySommaireForRT($sommaire->idSommaire);
            if ($sections == null) {
                $sections = [];
            }
            $devis = $this->devisModel->getOpDevis($idOp);
            $documents = $this->documentModel->getByOpportunity($idOp, "interne");
            $rf = $op->rf;
            $voisin = $this->contactModel->findById($rf->idVoisinF);
            $expertise  = $op->expertise;
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
                "rf" => $rf,
                "expertise" => $expertise,
                "voisin" => $voisin,
                "sommaire" => $sommaire,
                "sections" => $sections,
                "devis" => $devis,
                "documents" => $documents,
                "pieces" => $pieces
            ];
            $this->view("gestionnaire/opportunite/compteRenduRT", $data);
        } else {
            header("location:javascript://history.go(-1)");
        }
    }

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
}