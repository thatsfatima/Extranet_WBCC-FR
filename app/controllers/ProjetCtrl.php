<?php
class ProjetCtrl extends Controller
{
    public function __construct()
    {
        $this->projetModel = $this->model('Projet');
        $this->immeubleModel = $this->model('Immeuble');
        $this->lotModel = $this->model('Lot');
    }

    public function indexProjet()
    {
        $projets = $this->projetModel->getProjets();
        $data = [
            "projets" => $projets,
            "titre" => "Liste des projets"
        ];
        $this->view('projet/indexProjet', $data);
    }

    public function projet($id = '')
    {
        // Initialiser les variables
        $projet = null;  // Changer false par null
        $immeubles = $this->immeubleModel->getAllImmeubles("cb", "codeImmeuble", "ASC", 1);
        $sommaire = null;
        $immeuble = null;
        // Vérifier si l'ID est valide
        $id = !empty($id) ? $id : 0;

        $bibliotheques = [];
        if ($id != 0) {
            $projet = $this->projetModel->findProjetByColumnValue("idProjet", $id);
            $immeuble = findItemByColumn("wbcc_immeuble", "idImmeuble", $projet->idImmeubleF);
            if ($projet) {
                // Charger le sommaire associé au projet
                $sommaireModel = $this->model('Sommaire');
                $sommaire = $sommaireModel->getSommaireByProjet($id);
            } else {
                $_SESSION['error'] = "Projet introuvable";
                $this->redirectToMethod("Projet", "indexProjet");
                return;
            }
            $comptes = $this->lotModel->getComptesByImmeuble($immeuble->idImmeuble);
            $totalImpaye = 0;
            $listCoproAvecDettes = "";
            foreach ($comptes as $key => $compte) {
                if ($compte->soldeChargeCourante < 0) {
                    $totalImpaye += $compte->soldeChargeCourante;
                }

                if ($compte->solde < 0) {
                    $listCoproAvecDettes .= "<b>$compte->proprietaire</b> : Dettes cummulées de <b>" . ($compte->solde * -1) . " €</b> (dont $compte->soldeChargeCourante de charges, $compte->soldeChargeExceptionnelle pour les travaux  )<br>";
                }
            }
            $totalImpaye = $totalImpaye * -1;
            $budgetMoyen = 50000;
            $tauxImpaye = round(($totalImpaye / $budgetMoyen * 100), 2);



            $bibliotheques = [
                ["id" => "0", "titre" => "Taux d'impayés et vacance", "contenu" => "Le taux d’impayés au sein de la copropriété s’élève à <b>$tauxImpaye % du budget annuel</b>, une situation qui résulte de difficultés financières rencontrées par plusieurs copropriétaires et d’un manque de gestion proactive dans le passé. La vacance des logements, renforcée par les arrêtés préfectoraux, a accentué les problèmes économiques. Cependant, il est également important de souligner que certains copropriétaires, souvent majoritaires, adoptent des comportements inacceptables en refusant de payer leurs charges ou en bloquant les décisions nécessaires pour la réhabilitation de l’immeuble. Ces situations aggravent la fragilité financière de la copropriété. <br><br>Parmi ces opropriétaires, on note précisement :<br> $listCoproAvecDettes
                <br><br>Ces montants témoignent de la lourdeur de la dette collective, qui freine les actions de réhabilitation, malgré l’existence de devis et de solutions votées. <br><br> Consciente de ces enjeux, RYM Partners a élaboré une stratégie efficace pour résoudre ce problème structurel. L’approche consiste à acquérir les lots des copropriétaires débiteurs insolvables, leur offrant ainsi une alternative à des procédures judiciaires longues et coûteuses. Cette démarche garantit une reprise en main de la copropriété tout en permettant aux copropriétaires en difficulté de trouver une solution juste et adaptée. Par cette action, RYM Partners s’assure de la viabilité financière du projet de réhabilitation et de son exécution dans des conditions optimales."]
            ];
        }

        // Préparer les données pour la vue
        $data = [
            "projet" => $projet ?? null,
            "immeubles" => $immeubles,
            "sommaire" => $sommaire,
            "immeuble" => $immeuble,
            "title" => "Gestion du projet",
            "bibliotheques" => $bibliotheques
        ];

        // Vérifier quelle vue doit être chargée
        $view = isset($_GET['view']) && $_GET['view'] === 'sommaire'
            ? 'projet/sommaire'
            : 'projet/projet';

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
            $uploadBaseDir = URLDOCUMENT . 'immeuble/';
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

        $this->redirectToMethod("Projet", "projet", $idProjet);
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
        $this->redirectToMethod("Projet", "indexProjet");
    }
}