<?php
class GestionInterneCtrl extends Controller
{
    public function __construct()
    {
        $this->agendaModel = $this->model('Agenda');
        $this->companyModel = $this->model('Company');
        $this->siteModel = $this->model('Site');
        $this->userModel = $this->model('Utilisateur');
        // $this->equipementModel = $this->model('Equipement');
        // $this->pieceModel = $this->model('Piece');
        // $this->contactModel = $this->model('Contact');
        // $this->artisanModel = $this->model('Artisan');
        $this->subventionModel = $this->model('Subvention');
        $this->critereModel = $this->model('Critere');
        $this->parametreModel = $this->model('Parametres');
        // $this->pointageModel = $this->model('Pointage');
        $this->roleModel = $this->model('Roles');
        $this->projetModel = $this->model('Projet');
        $this->immeubleModel = $this->model('Immeuble');
        $this->jourFerieModel = $this->model('JourFerie');
        // $this->congeModel = $this->model('Conge');
    }

    public function index()
    {
        header("location:javascript://history.go(-1)");
    }

    public function getAgendas() {
        $data = [
            "titre" => "Gestion des agendas",
            "role" => $this->roleModel->getAllByEtat()
        ];
        return $data;
    }
    
    //ROLE
    public function indexRole()
    {
        $roles = $this->roleModel->getAllByEtat();
        $data = [
            "roles" => $roles,
            "titre" => "Liste des Rôles"
        ];
        $this->view('gestionInterne/role/indexRole', $data);
    }
    public function role($id = '')
    {
        $role = $this->roleModel->findByID($id);
        $modules = getModules();
        $rolesModules = getModulesByRole($id);

        $data = [
            "role"  => $role,
            "modules" => $modules,
            "rolesModules" =>  $rolesModules
        ];
        $this->view('gestionInterne/role/role', $data);
    }
    //SUBVENTION
    public function indexSubvention()
    {
        $subventions = $this->subventionModel->getSubventions();
        $data = [
            "subventions" => $subventions,
            "titre" => "Liste des subventions"
        ];
        $this->view('gestionInterne/subvention/indexSubvention', $data);
    }


    public function subvention($id = '')
    {
        $subvention = false;
        $criteres = [];
        $documents = [];
        $id = $id == '' ? 0 : $id;
        $allDocuments = [];
        if ($id != 0 && $id != "") {
            $subvention = $this->subventionModel->findSubventionByColumnValue("idSubvention", $id);
            if ($subvention) {
            } else {
                $this->redirectToMethod("GestionInterne", "indexSubvention");
            }
            $criteres = $this->critereModel->getCriteresByIdSubvention($id);
            $documents = $this->subventionModel->getDocumentsRequisByIdSubvention($id);
            $allDocuments = $this->subventionModel->getDocumentsRequisNotInSubvention($id);
        }

        $operateurs = [
            ["signe" => ">", "libelleOperateur" => "Supérieur"],
            ["signe" => ">=", "libelleOperateur" => "Supérieur ou Egal"],
            ["signe" => "<", "libelleOperateur" => "Inférieur"],
            ["signe" => "<=", "libelleOperateur" => "Inférieur ou Egal"],
            ["signe" => "=", "libelleOperateur" => "Egal"]
        ];
        $data = [
            "subvention"  => $subvention,
            "criteres" => $criteres,
            "documents" => $documents,
            "allDocuments" => $allDocuments,
            "operateurs" => $operateurs,
            "typeConditions" => $this->critereModel->getTypeConditions(),
            "organimes" => $this->companyModel->getCompaniesByIdStatut('organisme')
        ];
        $this->view('gestionInterne/subvention/subvention', $data);
    }

    public function saveSubvention()
    {
        extract($_POST);
        $natureT = isset($natureTravaux) ? implode(";", $natureTravaux)   : "";
        $natureA = isset($natureAide) ? implode(";", $natureAide)   : "";
        $subvention = $this->subventionModel->saveSubvention($idSubvention, $titreSubvention, $natureT, $natureA,  $montantSubvention, $taux, $idOrganisme, $_SESSION["connectedUser"]->idUtilisateur);
        if ($subvention) {
            $idSubvention = $subvention->idSubvention;
        }
        $this->redirectToMethod("GestionInterne", "subvention", $idSubvention);
    }

    public function parametrageSubvention()
    {
        $criteres = $this->critereModel->getCriteres();
        $documents = $this->subventionModel->getDocumentsRequis();

        $operateurs = [
            ["signe" => ">", "libelleOperateur" => "Supérieur"],
            ["signe" => "<", "libelleOperateur" => "Inférieur"],
            ["signe" => "=", "libelleOperateur" => "Egal"]
        ];
        $data = [
            "criteres" => $criteres,
            "documents" => $documents,
        ];
        $this->view('gestionInterne/subvention/parametrage', $data);
    }

    //ARTISAN
    public function indexArtisan()
    {
        $artisans = $this->artisanModel->getAllArtisanDevis();
        $data = [
            "artisans" => $artisans,
            "titre" => "Liste des super Artisans"
        ];
        $this->view('gestionInterne/artisan/indexArtisan', $data);
    }

    public function artisan($id = '')
    {
        $artisan = false;
        $artisans = [];
        $codeClientArtisan =  "";
        if ($id != 0 && $id != "") {
            $artisan = $this->artisanModel->findArtisanByID($id);
            if ($artisan) {
                $artisans = $this->companyModel->getCompaniesBySuperArtisan($id);
            } else {
                $this->redirectToMethod("GestionInterne", "indexArtisan");
            }
        } else {
            $param = $this->parametreModel->getParametres();
            $codeClientArtisan =  "CL" . str_pad(($param->numeroClient + 1), 4, '0', STR_PAD_LEFT);
        }

        $data = [
            "artisan"  => $artisan,
            "artisans" => $artisans,
            "codeClientArtisan" => $codeClientArtisan
        ];
        $this->view('gestionInterne/artisan/artisan', $data);
    }

    public function saveArtisanDevis()
    {
        $form = getForm($_POST);
        extract($form);
        $artisan = $this->artisanModel->saveArtisanDevis($idArtisanDevis, $codeClientArtisan, $nomArtisan, $telArtisan, $emailArtisan,  $adresseArtisan, $villeArtisan, $codePostalArtisan, $tauxArtisan, $capital);

        if ($idArtisanDevis == null || $idArtisanDevis == "" || $idArtisanDevis == "0") {
            $param = $this->parametreModel->getParametres();
            $this->parametreModel->updateParametre("numeroClient", ($param->numeroClient + 1));
        }
        if ($artisan) {
            $idArtisanDevis = $artisan->idArtisanDevis;
        }
        $this->redirectToMethod("GestionInterne", "artisan", $idArtisanDevis);
    }

    //EQUIPEMENT
    public function indexEquipement()
    {
        $equipements = $this->equipementModel->getAllEquipements();
        $data = [
            "equipements" => $equipements,
            "titre" => "Liste des équipements"
        ];
        $this->view('gestionInterne/equipement/indexEquipement', $data);
    }

    public function equipement($id = '')
    {
        $equipement = false;
        $piecesEquipements = [];
        $origines = [];
        if ($id != 0 && $id != "") {
            $equipement = $this->equipementModel->findById($id);
            if ($equipement) {
                $piecesEquipements = $this->pieceModel->getPiecesByidEquipement($id);
                $origines = $this->equipementModel->getOriginesByEquipement($id);
            } else {
                $this->redirectToMethod("GestionInterne", "indexEquipement");
            }
        }

        $pieces = $this->pieceModel->getAllWithoutAutre();
        $responsables = $this->equipementModel->getResponsableReparationFuite();

        $data = [
            "equipement"  => $equipement,
            "origines" => $origines,
            "pieces" => $pieces,
            "piecesEquipements" => $piecesEquipements,
            "responsables" => $responsables
        ];
        $this->view('gestionInterne/equipement/equipement', $data);
    }

    //SITE
    public function indexSite()
    {
        $sites = $this->siteModel->getAllSites();
        $data = [
            "sites" => $sites,
            "titre" => "Liste des Sites de WBCC"
        ];
        $this->view('gestionInterne/site/indexSite', $data);
    }

    public function site($id = '')
    {
        $site = false;
        $users = [];
        if ($id != 0 && $id != "") {
            $site = $this->siteModel->findById($id);
            if ($site) {
                $users = $this->userModel->getUsersBySite($id);
            } else {
                $this->redirectToMethod("GestionInterne", "indexSite");
            }
        }

        $data = [
            "site"  => $site,
            "users" => $users
        ];
        $this->view('gestionInterne/site/site', $data);
    }

    //DEBUT NABILA 
    public function indexPersonnel()
    {
        $role = $_SESSION['connectedUser']->role;
        $type = "wbcc";
        $idContact = Role::connectedUser()->idUtilisateur;
        if ($role == 25) {
            $personnels = $this->userModel->getUsersBySite(($_SESSION['connectedUser'])->idSiteF, 1);
        } else {
            $personnels = $this->userModel->getUsersByType($type);
        }
        $roles = $this->roleModel->getRolesByType($type);
        $sites = $this->siteModel->getAllSites();
        $data = [
            "gerepresence" => linkTo('GestionInterne', 'gerepresence'),
            "genererAvertissement" => linkTo('GestionInterne', 'genererAvertissement'),
            "gererPaie" => linkTo('GestionInterne', 'gererPaie'),
            "gererConges" => linkTo('GestionInterne', 'gererConges'),
            "tbdPresence" => linkTo('GestionInterne', 'tbdPresence'),
            "Pointer" => linkTo('GestionInterne', 'Pointer'),
            "DemanderConge" => linkTo('GestionInterne', 'DemanderConge'),
            "avertir" => linkTo('GestionInterne', 'avertir'),
            "dashbord" => linkTo('GestionInterne', 'dashbord'),
            "sites" => $sites,
            "personnels" => $personnels,
            "roles" => $roles,
            "idContact" => $idContact,
            "titre" => 'Liste des utilisateurs'
        ];
        $this->view('gestionInterne/personnel/indexPersonnel', $data);
    }
    public function bilanComparatif()
    {

        $site = "";
        $periode = 'today';
        $date1 = "";
        $date2 = "";
        $startDate = "";
        $endDate = "";
        $previousStartDate = "";
        $previousEndDate = "";
        $idUtilisateur = ''; // For filtering by user
        $selectedEmploye = "";
        $role = $_SESSION['connectedUser']->role;
        $re = getPeriodDates("$periode", []);

        if (isset($_GET)) {
            extract($_GET);
        }

        if ($periode != "all" && $periode != "custom" && $periode != "day" && $periode != "today") {
            $re = getPeriodDates("$periode", []);
            if (sizeof($re) != 0) {
                $date1 = $re['startDate'];
                $date2 = $re['endDate'];
                $startDate = $re['startDate'];
                $endDate = $re['endDate'];
                $previousStartDate = $re['previousStartDate'];
                $previousEndDate = $re['previousEndDate'];
            }
        }

        if ($periode === "today") {
            if (sizeof($re) != 0) {
                $startDate = $re['startDate'];
                $endDate = $re['endDate'];
                $previousStartDate = $re['previousStartDate'];
                $previousEndDate = $re['previousEndDate'];
            }
        }

        $idContact = Role::connectedUser()->idUtilisateur;
        if ($selectedEmploye || ($role != 1 && $role != 2 && $role != 25)) {
            if ($role != 1 && $role != 2 && $role != 25) {
                $selectedEmploye = $this->contactModel->findById($_SESSION['connectedUser']->idContactF)->fullName;
            } else {
                $selectedEmploye = $this->contactModel->findById($idContact);
            }
        }
        $sites = $this->siteModel->getAllSites();
        $contactsList =   $this->userModel->getUsersByType("wbcc", "1");

        if ($role == 25) {
            $contactsList =   $this->userModel->getUsersBySite($_SESSION['connectedUser']->idSiteF, 1);
        }
        if ($idUtilisateur == "") {
            $contacts =   $contactsList;
        } else {
            $contacts = [];
            $contacts[] =  $this->userModel->findUserByIdContact($idUtilisateur);
        }
        $user = false;
        $user = $this->contactModel->findById($idContact);
        $contactById =   $this->userModel->findUserByIdContact($idContact);
        $pointages = null;
        $pointages =  $this->pointageModel->getAllWithFullName($idContact);

        $data = [
            "idUtilisateur" => $idUtilisateur,
            "selectedEmploye" => $selectedEmploye,
            "contacts"  => $contacts,
            "contactsList" => $contactsList,
            "contactById" => $contactById,
            "site" => $site,
            "sites" => $sites,
            "pointages" => $pointages,
            "periode" => $periode,
            "date1" => $date1,
            "date2" => $date2,
            "startDate" => $startDate,
            "endDate" => $endDate,
            'previousStartDate' => $previousStartDate,
            'previousEndDate' => $previousEndDate,
            "user" => $user
        ];
        $this->view("gestionInterne/personnel/bilan", $data);
    }

    public function gerepresence()
    {
        $Motifjustification = '';
        $etat = '';
        $site = '';
        $periode =  'today';
        $dateOne =  ''; // For single date 'A la date du'
        $dateDebut =  ''; // For 'Personnaliser'
        $dateFin = ''; // For 'Personnaliser'
        $matricule =  '';
        $idUtilisateur = ''; // For filtering by user
        $fullName = '';
        $role = $_SESSION['connectedUser']->role;

        if (isset($_GET)) {
            extract($_GET);
        }

        if ($role == 25) {
            $site = $_SESSION['connectedUser']->idSiteF;
        }

        $idUtilisateur == '' ?
            $titre = 'LISTE DES POINTAGES DE TOUS LES UTILISATEURS' :
            $titre = 'LISTE DES POINTAGES';

        $totalMinuteRetard = 0;
        $totalMinuteRetardById = 0;

        $idContact = Role::connectedUser()->idUtilisateur;
        $sites = $this->siteModel->getAllSites();

        $contactById =  $this->userModel->findUserByIdContact($idContact);
        $contacts = [];
        $matricules = [];
        if ($role == 1 || $role == 2) {
            $contacts =   $this->userModel->getUsersByType("wbcc", 1);
            $matricules =  $this->userModel->getUsersByType("wbcc", 1);
            if ($idUtilisateur) {
                $fullName = $this->contactModel->findById($idUtilisateur)->fullName;
                $titre .= ' DE ' . $fullName;
            }
        } else {
            if ($role == 25) {
                $contacts =   $this->userModel->getUsersBySite($_SESSION['connectedUser']->idSiteF, 1);
                $matricules =  $this->userModel->getUsersBySite($_SESSION['connectedUser']->idSiteF, 1);
                if ($idUtilisateur) {
                    $fullName = $this->contactModel->findById($idUtilisateur)->fullName;
                    $titre .= ' DE ' . $fullName;
                }
            } else {
                $idUtilisateur = $_SESSION['connectedUser']->idUtilisateur;
                $fullName = $this->userModel->findUserByIdContact(Role::connectedUser()->idContactF)->fullName;
                $titre .= ' DE ' . $fullName;
            }
        }

        $pointages = null;
        $pointagesById = $this->pointageModel->getFilteredPointageWithidUser($idUtilisateur, $Motifjustification, $etat, $periode, $dateOne, $dateDebut, $dateFin);

        if ($role != 1 && $role != 2 && $role != 25) {
            $titre = 'LISTE DES POINTAGES';
            $fullName = $this->userModel->findUserById($idContact)->fullName;
            $titre .= ' DE ' . $fullName;
        }

        if ($site != "") {
            $siteObj = findItemByColumn("wbcc_site", "idSite", $site);
            $titre .= ' DU SITE DE ' . "'" . $siteObj->nomSite . "'";
        }



        if ($periode != "" && $periode != "2" && $periode != "1" && $periode != "today") {
            $re = getPeriodDates("$periode", []);
            if (sizeof($re) != 0) {
                $dateOne = $re['startDate'];
                $dateDebut = $re['startDate'];
                $dateFin = $re['endDate'];
            }
            $titre .= " du " . date('d/m/Y', strtotime($dateDebut)) . " au " . date('d/m/Y', strtotime($dateFin));
        } else {
            if ($periode == "1") {
                $titre .= " du " . date('d/m/Y', strtotime($dateOne));
            } else {
                if ($periode == "today") {
                    $titre .= " Aujourd'hui";
                } else {
                    if ($periode == "2") {
                        $titre .= " du " . date('d/m/Y', strtotime($dateDebut)) . " au " . date('d/m/Y', strtotime($dateFin));
                    }
                }
            }
        }

        foreach ($pointagesById as $index => $pointage) {
            $totalMinuteRetardById += $pointage->nbMinuteRetard;
        }

        if ($role == 1 || $role == 2) {
            if ($Motifjustification == "" && $etat == "" && $site == "" && $periode == "" && $dateOne == "" && $dateDebut == "" && $dateFin == "" && $matricule == "" && $idUtilisateur == "") {
                $pointages =  $this->pointageModel->getAllWithFullName($idContact);
                foreach ($pointages as $index => $pointage) {
                    $totalMinuteRetard += $pointage->nbMinuteRetard;
                }
            } else {
                $pointages = $this->pointageModel->getFilteredPointage($Motifjustification, $etat, $site, $periode, $dateOne, $dateDebut, $dateFin, $matricule, $idUtilisateur);
                foreach ($pointages as $index => $pointage) {
                    $totalMinuteRetard += $pointage->nbMinuteRetard;
                }
            }
        }

        if ($role == 25) {
            $pointages = $this->pointageModel->getFilteredPointage($Motifjustification, $etat, $site, $periode, $dateOne, $dateDebut, $dateFin, $matricule, $idUtilisateur);
            foreach ($pointages as $index => $pointage) {
                $totalMinuteRetard += $pointage->nbMinuteRetard;
            }
        }
        $data = [
            "idUtilisateur" => $idUtilisateur,
            "titre" => $titre,
            "site" => $site,
            "sites" => $sites,
            "etat" => $etat,
            "Motifjustification" => $Motifjustification,
            "periode" => $periode,
            "dateOne" => $dateOne,
            "dateFin" => $dateFin,
            "dateDebut" => $dateDebut,
            "contacts"  => $contacts,
            "contactById" => $contactById,
            "matricules"  => $matricules,
            "pointages" => $pointages,
            "pointagesById" => $pointagesById,
            "totalMinuteRetard" => $totalMinuteRetard,
            "totalMinuteRetardById" => $totalMinuteRetardById,
        ];
        $this->view("gestionInterne/personnel/pointage", $data);
    }

    public function genererAvertissement()
    {
        $data = [];
        $this->view("gestionInterne/espaceAdmin/avertissement", $data);
    }

    public function gererPaie()
    {
        $data = [];
        $this->view("gestionInterne/espaceAdmin/paie", $data);
    }

    public function gererConges()
    {
        $idContact = Role::connectedUser()->idUtilisateur;
        $contacts =  $this->contactModel->getAllContacts();
        $matricules =  $this->userModel->getAll();
        $conges =  $this->congeModel->getAllWithFullName($idContact);



        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
            $id = intval($_GET['id']);

            $congeDetails = $conge->findById($id);


            header('Content-Type: application/json');
            echo json_encode($congeDetails);
            exit();
        }


        $data = [
            "contacts"  => $contacts,
            "matricules"  => $matricules,
            "conges" =>  $conges

        ];
        $this->view("gestionInterne/espaceAdmin/conge", $data);
    }
    public function tbdPresence()
    {
        $data = [];
        $this->view("gestionInterne/espaceAdmin/tbdPresence", $data);
    }
    public function acceuilAdmin()
    {
        $data = [
            "gerepresence" => linkTo('GestionInterne', 'gerepresence'),
            "genererAvertissement" => linkTo('GestionInterne', 'genererAvertissement'),
            "gererPaie" => linkTo('GestionInterne', 'gererPaie'),
            "gererConges" => linkTo('GestionInterne', 'gererConges'),
            "tbdPresence" => linkTo('GestionInterne', 'tbdPresence')
        ];

        $this->view("gestionInterne/espaceAdmin/acceuilAdmin", $data);
    }


    public function Pointer()
    {

        $idContact = Role::connectedUser()->idUtilisateur;
        $do = $this->pointageModel->getAllWithidUser($idContact);
        $data = [
            "pointages" => $do
        ];
        $this->view("gestionInterne/espaceSalarie/espacePointage", $data);
    }
    public function DemanderConge()
    {
        $idContact = Role::connectedUser()->idContact;
        $conges =  $this->congeModel->getAllWithContactId($idContact);
        $data = [
            "conges" =>  $conges
        ];
        $this->view("gestionInterne/espaceSalarie/espaceConge", $data);
    }
    public function Avertir()
    {
        $data = [];
        $this->view("gestionInterne/espaceSalarie/espaceAvertissement", $data);
    }
    public function dashbord()
    {
        $idContact = Role::connectedUser()->idUtilisateur;
        $contacts =  $this->contactModel->getAllContacts();
        $matricules =  $this->userModel->getAll();
        $pointages =  $this->pointageModel->getAllWithFullName($idContact);
        $data = [
            "contacts"  => $contacts,
            "matricules"  => $matricules,
            "pointages" => $pointages
        ];
        $this->view("gestionInterne/espaceSalarie/tableauDuBord", $data);
    }


    public function acceuilSalarie()
    {
        $data = [
            "Pointer" => linkTo('GestionInterne', 'Pointer'),
            "DemanderConge" => linkTo('GestionInterne', 'DemanderConge'),
            "avertir" => linkTo('GestionInterne', 'avertir'),
            "dashbord" => linkTo('GestionInterne', 'dashbord'),

        ];

        $this->view("gestionInterne/espaceSalarie/acceuilSalarie", $data);
    }



    //FIN NABILA

    public function indexProjet()
    {
        $projets = $this->projetModel->getProjets();
        $data = [
            "projets" => $projets,
            "titre" => "Liste des projets"
        ];
        $this->view('gestionInterne/projet/indexProjet', $data);
    }



    public function projet($id = '')
    {
        // Initialiser les variables
        $projet = null;  // Changer false par null
        $immeubles = $this->immeubleModel->getAllImmeublesCB();
        $sommaire = null;
        $immeuble = null;
        // Vérifier si l'ID est valide
        $id = !empty($id) ? $id : 0;

        if ($id != 0) {
            $projet = $this->projetModel->findProjetByColumnValue("idProjet", $id);
            $immeuble = findItemByColumn("wbcc_immeuble_cb", "idImmeuble", $projet->idImmeubleCB);
            if ($projet) {
                // Charger le sommaire associé au projet
                $sommaireModel = $this->model('Sommaire');
                $sommaire = $sommaireModel->getSommaireByProjet($id);
            } else {
                $_SESSION['error'] = "Projet introuvable";
                $this->redirectToMethod("GestionInterne", "indexProjet");
                return;
            }
        }

        // Préparer les données pour la vue
        $data = [
            "projet" => $projet ?? null,
            "immeubles" => $immeubles,
            "sommaire" => $sommaire,
            "immeuble" => $immeuble,
            "title" => "Gestion du projet"
        ];

        // Vérifier quelle vue doit être chargée
        $view = isset($_GET['view']) && $_GET['view'] === 'sommaire'
            ? 'gestionInterne/projet/sommaire'
            : 'gestionInterne/projet/projet';

        // Afficher la vue
        $this->view($view, $data);
    }

    public function saveProjet()
    {
        // print_r($_POST);
        extract($_POST);
        $idImmeuble = $_POST['idImmeuble'];
        echo "\idProjetCTRL = $idProjet";
        $idUser = Role::connectedUser()->idUtilisateur;

        if (isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $uploadBaseDir = $_SERVER['DOCUMENT_ROOT'] . '/public/documents/immeuble/';
            $uploadFile = $uploadBaseDir . $file['name'];
            if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                $this->immeubleModel->updatePhotoImmeuble($idImmeuble, $file['name']);
            } else {
                echo "Error uploading file.";
            }
        } else {
            echo "No file uploaded.";
        }

        $projet = $this->projetModel->saveProjet($idProjet, $nomProjet, $descriptionProjet, $idImmeuble, $idUser);

        if ($projet) {
            $idProjet = $projet->idProjet;
        }

        $this->redirectToMethod("GestionInterne", "projet", $idProjet);
    }

    /**
     * Supprime un projet par son ID
     * @param int $id l'ID du projet à supprimer
     * @return void
     */
    public function deleteProjetById($id)
    {
        // Suppression du projet
        $this->projetModel->deleteProjetById($id);

        // Redirection vers la liste des projets
        $this->redirectToMethod("GestionInterne", "indexProjet");
    }
    
    // Liste des jours fériés
    public function indexJourFerie()
    {
        $idSiteUser = $_SESSION['connectedUser']->idSite;
        $idUser = $_SESSION['connectedUser']->idUtilisateur;

        $idSite = (isset($_GET['site'])) ? $_GET['site'] : $idSiteUser;
        $annee = (isset($_GET['annee'])) ? $_GET['annee'] : date('Y');

        $joursFeries = $this->jourFerieModel->getAllJoursFeries($idSite, $annee);
        $site = $this->siteModel->findById($idSite);
        $sites = $this->siteModel->getAllSites();
        $data = [
            "joursFeries" => $joursFeries,
            "titre" => "Jours fériés ",
            "sousTitre" => "Liste des jours fériés ". $annee . "_WBCC-" . $site->nomSite,
            "annee" => $annee,
            "site" => $site,
            "sites" => $sites,
        ];
        $this->view('gestionInterne/jourFerie/indexJourFerie', $data);
    }

    public function jourFerie($id = '')
    {
        $idSiteUser = $_SESSION['connectedUser']->idSite;
        $idUser = $_SESSION['connectedUser']->idUtilisateur;

        $idSite = (isset($_GET['site'])) ? $_GET['site'] : $idSiteUser;
        $annee = (isset($_GET['annee'])) ? $_GET['annee'] : date('Y');

        $joursFeries = $this->jourFerieModel->getAllJoursFeries($idSite, $annee);
        $site = $this->siteModel->findById($idSite);
        $sites = $this->siteModel->getAllSites();
        $jourFerie = $this->jourFerieModel->findJourFerieByColumnValue("idJourFerie", $id);
        $data = [
            "jourFerie" => $jourFerie,
            "joursFeries" => $joursFeries,
            "titre" => "Jours fériés",
            "sousTitre" => "Ajout de jours fériés ". $annee . "_WBCC-" . $site->nomSite,
            "annee" => $annee,
            "site" => $site,
            "sites" => $sites
        ];
        $this->view('gestionInterne/jourFerie/jourFerie', $data);
    }

    public function saveJourFerie($idJourFerie = null)
    {
        extract($_POST);
        
        $idSiteUser = $_SESSION['connectedUser']->idSite;
        $idUser = Role::connectedUser()->idUtilisateur;

        $idSite = (isset($_GET['site'])) ? $_GET['site'] : $idSiteUser;
        $annee = (isset($_GET['annee'])) ? $_GET['annee'] : date('Y');

        $joursFeries = $this->jourFerieModel->getAllJoursFeries($idSite, $annee);
        $site = $this->siteModel->findById($idSite);
        $sites = $this->siteModel->getAllSites();
        $jourFerie = $this->jourFerieModel->saveJourFerie($nomJourFerie, $dateJourFerie, $anneeJourFerie, $idSiteF, $payer, $chomer, $idJourFerie);
        $data = [
            "jourFerie" => $jourFerie,
            "titre" => "Jours fériés",
            "sousTitre" => "Ajout de jours fériés ". $annee . "_WBCC-" . $site->nomSite,
            "annee" => $annee,
            "site" => $site,
            "sites" => $sites
        ];
        $this->view('gestionInterne/jourFerie/jourFerie', $data);
    }

    public function ajoutJourFerie() {
        extract($_GET);

        $idSiteUser = $_SESSION['connectedUser']->idSite;
        $idUser = Role::connectedUser()->idUtilisateur;
        $idSite = (isset($_GET['idSite'])) ? $_GET['idSite'] : $idSiteUser;
        $annee = (isset($_GET['annee'])) ? $_GET['annee'] : date('Y');
        $anneeReference = (isset($_GET['anneeReference'])) ? $_GET['anneeReference'] : date('Y');

        $joursFeries = $this->jourFerieModel->getAllJoursFeries($idSite, $anneeReference);
        foreach ($joursFeries as $jourFerie) {
            $date = date("{$annee}-m-d", strtotime($jourFerie->dateJourFerie));
            $this->jourFerieModel->saveJourFerie($jourFerie->nomJourFerie, $date, $annee, $idSite, $jourFerie->Payer, $jourFerie->Chomer, null);
        }
    }

    public function deleteJourFerie($id)
    {
        $this->jourFerieModel->deleteJourFerieById($id);
    }
}