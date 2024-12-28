<?php

class GestionInterneCtrl extends Controller
{
    public function __construct()
    {
        $this->companyModel = $this->model('Company');
        $this->siteModel = $this->model('Site');
        $this->userModel = $this->model('Utilisateur');
        // $this->equipementModel = $this->model('Equipement');
        // $this->pieceModel = $this->model('Piece');
        $this->contactModel = $this->model('Contact');
        // $this->artisanModel = $this->model('Artisan');
        $this->subventionModel = $this->model('Subvention');
        $this->critereModel = $this->model('Critere');
        $this->parametreModel = $this->model('Parametres');
        $this->projetModel = $this->model('Projet');
        $this->immeubleModel = $this->model('Immeuble');
        // $this->pointageModel = $this->model('Pointage');
        // $this->roleModel = $this->model('Roles');
        // $this->congeModel = $this->model('Conge');
    }

    public function index()
    {
        header("location:javascript://history.go(-1)");
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
    public function gerepresence()
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
        $this->view("gestionInterne/espaceAdmin/pointage", $data);
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

  //debut jawher

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
        $projet = false;
        $nom = "";
        $description = "";
        $immeubles= [];
        $lots = [];

        $id = $id == '' ? 0 : $id;
        $allDocuments = [];
        $immeuble = null;
        // Tous les immeubles
        $immeubles = $this->immeubleModel->getAllImmeubles();
        if ($id != 0 && $id != "") {
            $projet = $this->projetModel->findProjetByColumnValue("idProjet", $id);
            $immeuble = $this->immeubleModel->findImmeubleById($projet->idImmeuble);
            if ($projet) {
                $nom =$projet ->nomProjet;
                $description =$projet ->descriptionProjet;
            } else {
                $this->redirectToMethod("GestionInterne", "indexProjet");
            }
        }
        $data = [
            "projet"  => $projet,
            "immeuble" => $immeuble,
            "immeubles" => $immeubles
        ];
        $this->view('gestionInterne/projet/projet', $data);
  }

  public function saveProjet()
  {
    // print_r($_POST);
    extract($_POST);
    $idImmeuble = $_POST['idImmeuble'];  // Get the selected Immeuble ID
    echo "\idProjetCTRL = $idProjet";
    $idUser = Role::connectedUser()->idUtilisateur;

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
}