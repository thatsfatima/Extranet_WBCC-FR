<?php

class SectionCtrl extends Controller
{
    private $sectionModel;
    private  $documentModel;

    public function __construct()
    {
        $this->sectionModel = $this->model('Section');
        $this->documentModel = $this->model('Document');
        $this->lotModel = $this->model('Lot');
    }

    public function index($sommaireId = null)
    {
        if ($sommaireId) {
            $sections = $this->sectionModel->getSectionsBySommaire($sommaireId);
        } else {
            $sections = $this->sectionModel->getAll();
        }

        $this->view('sections/index', [
            'sections' => $sections,
            'sommaireId' => $sommaireId,
            'title' => 'Liste des sections'
        ]);
    }



    public function getSectionsBySommaire()
    {
        echo json_encode("okkk");
        die;
        ob_clean();
        $this->view = false;

        header('Content-Type: application/json');

        if (!isset($_GET['idSommaire'])) {
            echo json_encode(['error' => 'ID Sommaire manquant']);
            exit;
        }

        $idSommaire = $_GET['idSommaire'];


        try {
            // Récupérer les sections
            $sections = $this->sectionModel->getSectionsBySommaire($idSommaire);


            //Récupérer le sommaire
            $sommaireModel = $this->model('Sommaire');
            $sommaire = $sommaireModel->findBy('idSommaire', $idSommaire);

            echo json_encode([
                'success' => true,
                'sections' => $sections ?? [],
                'titreSommaire' => $sommaire ? $sommaire->titreSommaire : ''
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => $e->getMessage(),
                'trace' => debug_backtrace()
            ]);
        }
        exit();
    }


    public function show($id)
    {
        $section = $this->sectionModel->findBy('idSection', $id);
        if ($section) {
            // Récupérer les informations de la section parente si elle existe
            $sectionWithParent = $this->sectionModel->getSectionWithParent($id);
            // Récupérer les sous-sections
            $childSections = $this->sectionModel->getChildSections($id);

            $this->view('sections/show', [
                'section' => $section,
                'sectionWithParent' => $sectionWithParent,
                'childSections' => $childSections,
                'title' => 'Détails de la section'
            ]);
        } else {
            $this->redirectToMethod('sections');
        }
    }


    public function updateMultiple()
    {
        // Désactiver la vue
        $this->view = false;

        // Vider le buffer
        @ob_end_clean();

        // En-tête JSON
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        try {
            // Vérifier la méthode
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Récupérer les données
            $postData = json_decode(file_get_contents('php://input'), true);

            if (!isset($postData['sections']) || !is_array($postData['sections'])) {
                throw new Exception('Format de données invalide');
            }

            // Mettre à jour les sections
            if ($this->sectionModel->updateMultiple($postData['sections'])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Sections mises à jour avec succès'
                ]);
            } else {
                throw new Exception('Erreur lors de la mise à jour des sections');
            }
        } catch (Exception $e) {
            error_log('Erreur dans updateMultiple: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        exit();
    }

    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $data = [
                'titreSection' => trim($_POST['titreSection']),
                'contenuSection' => trim($_POST['contenuSection'] ?? ''),
                'idSommaireF' => trim($_POST['idSommaireF']),
                'idSection_parentF' => !empty($_POST['idSection_parentF']) ? trim($_POST['idSection_parentF']) : null,
                'numeroSection' => trim($_POST['numeroSection'])
            ];

            if ($this->sectionModel->create($data)) {
                $_SESSION['success'] = "Section créée avec succès";
                $this->redirectToMethod('Projet', 'projet', $data['idSommaireF']);
            } else {
                $_SESSION['error'] = "Erreur lors de la création de la section";
                $this->redirectToMethod('Projet', 'projet', $data['idSommaireF']);
            }
        } else {
            $this->redirectToMethod('Projet');
        }
    }

    public function edit($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Traitement du formulaire
            $data = [
                'titreSection' => trim($_POST['titreSection']),
                'numeroSection' => trim($_POST['numeroSection']),
                'contenuSection' => trim($_POST['contenuSection']),
                'idSommaireF' => trim($_POST['idSommaireF']),
                'idSection_parentF' => !empty($_POST['idSection_parentF']) ? trim($_POST['idSection_parentF']) : null
            ];

            if ($this->sectionModel->updateSection($id, $data)) {
                $this->redirectToMethod('sections', 'show', $id);
            } else {
                $this->view('sections/edit', [
                    'data' => $data,
                    'error' => 'Une erreur est survenue lors de la modification de la section',
                    'title' => 'Modifier la section'
                ]);
            }
        } else {
            // Récupérer les données de la section
            $section = $this->sectionModel->findBy('idSection', $id);
            if ($section) {
                $this->view('sections/edit', [
                    'section' => $section,
                    'title' => 'Modifier la section'
                ]);
            } else {
                $this->redirectToMethod('sections');
            }
        }
    }

    public function delete($id)
    {
        error_log('Entrée dans la méthode delete');

        // // Désactiver complètement le rendu de vue
        // $this->view = null;
        // $this->layout = null; // Si vous avez une propriété layout

        // Vider le buffer de sortie
        @ob_end_clean();

        // Forcer les en-têtes
        if (!headers_sent()) {
            header('Content-Type: application/json');
            header('X-PHP-Response-Type: JSON');
        }

        try {
            error_log('ID de la section à supprimer: ' . $id);

            // Tenter la suppression sans vérification préalable
            if ($this->sectionModel->deleteWithChildren($id)) {
                error_log('Suppression réussie');
                $response = json_encode([
                    'success' => true,
                    'message' => 'Section supprimée avec succès'
                ]);
            } else {
                error_log('Échec de la suppression');
                $response = json_encode([
                    'success' => false,
                    'error' => 'Échec de la suppression'
                ]);
            }

            error_log('Réponse préparée: ' . $response);
            echo $response;
        } catch (Exception $e) {
            error_log('Exception lors de la suppression: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de la suppression'
            ]);
        }
        error_log('Fin de la méthode delete');
        exit();
    }


    /**
     * Récupère tous les documents (pour la recherche RYM)
     */
    public function getAllDocumentsByIdImmeuble($idImmeuble)
    {
        $this->view = false;
        ob_clean();
        header('Content-Type: application/json');
        try {
            // Utiliser la nouvelle méthode
            $documents = $this->documentModel->getAllDocumentsByIdImmeuble('wbcc_immeuble', $idImmeuble);
            // Formatter les documents pour l'API
            $formattedDocs = array_map(function ($doc) {
                return [
                    'id' => $doc->idDocument,
                    'titre' => $doc->nomDocument,
                    'type' => pathinfo($doc->urlDocument, PATHINFO_EXTENSION),
                    'dateCreation' => $doc->createDate,
                    'url' => $doc->urlDossier . "/" . $doc->urlDocument,
                    'auteur' => $doc->auteur
                ];
            }, $documents);

            echo json_encode([
                'success' => true,
                'documents' => $formattedDocs
            ]);
        } catch (Exception $e) {
            error_log('Erreur lors de la récupération des documents: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de la récupération des documents : ' . $e->getMessage()
            ]);
        }
        exit();
    }

    public function removeLotToAcquire()
    {
        ob_clean();
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            error_log('Données reçues: ' . print_r($data, true));

            if (!isset($data['sectionId']) || !isset($data['lotId'])) {
                throw new Exception('Données manquantes ou invalides');
            }

            $lotModel = $this->getLotModel();
            $result = $lotModel->removeLotToAcquire($data['sectionId'], $data['lotId']);

            error_log('Résultat de la suppression: ' . ($result ? 'succès' : 'échec'));

            if ($result) {
                // Récupérer la nouvelle liste des lots
                $updatedLots = $lotModel->getLotsByImmeuble($data['sectionId'], "cb", 1);

                echo json_encode([
                    'success' => true,
                    'message' => 'Lot supprimé avec succès',
                    'data' => $updatedLots
                ]);
            } else {
                throw new Exception('Erreur lors de la suppression du lot');
            }
        } catch (Exception $e) {
            error_log('Exception dans removeLotToAcquire: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }
    /**
     * Upload d'un nouveau document
     */
    public function uploadSectionDocument()
    {
        $this->view = false;
        ob_clean();
        header('Content-Type: application/json');

        try {
            // Vérification des données
            if (!isset($_FILES['file']) || !isset($_POST['sectionId'])) {
                throw new Exception('Données manquantes');
            }

            $file = $_FILES['file'];
            $sectionId = $_POST['sectionId'];

            // Vérifications basiques
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Erreur lors du téléchargement');
            }
            // Générer un numéro unique pour le document
            $idUser = Role::connectedUser()->idUtilisateur;

            $numeroDocument = uniqid('DOC_' . $idUser . '_', true);

            // Utiliser URLROOT pour définir le chemin
            $uploadBaseDir = $_SERVER['DOCUMENT_ROOT'] . '/public/documents/projet/annexe/';
            // $uploadBaseDir = $_SERVER['DOCUMENT_ROOT'] . '/Extranet_WBCC-FR/public/documents/projet/annexe/';

            // Créer le répertoire s'il n'existe pas
            if (!is_dir($uploadBaseDir)) {
                mkdir($uploadBaseDir, 0755, true);
            }

            // if ($choix == "local")
            {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $source = $file['tmp_name'];
                $urlDocument = $file['name'];
                error_log('Fichier temporaire : ' . $file['tmp_name']);
            }
            // else {
            //     //RECUP DOCUMEN BY ID document
            //     $doc = false;
            //     $chemin = URLROOT . "/public/documents/" . $doc->urlDossier . "/" . $doc->urlDocument;
            //     $extension = pathinfo($chemin, PATHINFO_EXTENSION);
            //     $source = $chemin;
            //     $urlDocument = $doc->urlDocument;
            // }
            $fileName = $numeroDocument . '.' . $extension;
            $destination = $uploadBaseDir . $fileName;

            // Débogage
            error_log('Chemin de destination complet : ' . $destination);


            // Déplacer le fichier
            if (!move_uploaded_file($source, $destination)) {
                // Log de l'erreur de déplacement
                error_log('Impossible de déplacer le fichier');
                throw new Exception('Erreur lors du déplacement du fichier');
            }

            // Sauvegarder dans la base de données
            $documentId = $this->documentModel->save(
                $numeroDocument,
                $urlDocument,
                $fileName,
                isset($_SESSION['user']) ? $_SESSION['user']->nomUtilisateur : '',
                isset($_SESSION['user']) ? $_SESSION['user']->idUtilisateur : null,
                isset($_SESSION['user']) ? $_SESSION['user']->guidUtilisateur : '',
                "projet/annexe"
            );

            if (!$documentId) {
                throw new Exception('Erreur lors de l\'enregistrement du document');
            }

            // Lier le document à la section
            $this->sectionModel->linkDocument($sectionId, $documentId);

            echo json_encode([
                'success' => true,
                'message' => 'Document uploadé avec succès',
                'document' => [
                    'id' => $documentId,
                    'nom' => $file['name'],
                    'url' => URLROOT . '/public/documents/projet/annexe/' . $fileName
                ]
            ]);
        } catch (Exception $e) {
            error_log('Erreur complète : ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }


    /**
     * Lie un document existant à une section
     */
    public function linkDocumentToSection()
    {
        $this->view = false;
        ob_clean();
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['sectionId']) || !isset($data['documentId'])) {
                error_log('Données manquantes : ' . print_r($data, true));
                throw new Exception('Données manquantes');
            }

            $sectionId = $data['sectionId'];
            $documentId = $data['documentId'];

            error_log("Tentative de liaison - Données reçues : " . print_r($data, true));

            $result = $this->sectionModel->linkDocument($sectionId, $documentId);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Document lié avec succès'
                ]);
            } else {
                error_log("Échec de la liaison du document");
                throw new Exception('Erreur lors de la liaison du document');
            }
        } catch (Exception $e) {
            error_log('Exception dans linkDocumentToSection : ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * Récupère les documents liés à une section
     */
    public function getSectionDocuments($sectionId)
    {
        $this->view = false;
        ob_clean();
        header('Content-Type: application/json');

        $documents = $this->sectionModel->getDocuments($sectionId);
        echo json_encode($documents);
        exit();
    }

    public function deleteDocument()
    {
        $this->view = false;
        ob_clean();
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['sectionId']) || !isset($data['documentId'])) {
                throw new Exception('Données manquantes');
            }

            $sectionId = $data['sectionId'];
            $documentId = $data['documentId'];

            // Supprimer le lien entre la section et le document
            if ($this->sectionModel->unlinkDocument($sectionId, $documentId)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Document supprimé avec succès'
                ]);
            } else {
                throw new Exception('Erreur lors de la suppression du document');
            }
        } catch (Exception $e) {
            error_log('Exception dans deleteDocument : ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }

    public function htmlProjet()
    {
        try {
            extract($_POST);

            if (!isset($html) || (!isset($lotId)) || (!isset($sectionId))) {
                throw new Exception('Données "html" manquantes');
            }
            $lotModel = $this->getLotModel();
            $result = $lotModel->saveHtmlVariable($lotId, $sectionId, $html);

            // header('Content-Type: application/json');
            echo ($result);
        } catch (Exception $e) {
            // error_log('Erreur enregistrement HTML: ' . $e->getMessage());
            echo json_encode(
                $e->getMessage()
            );
        }
    }


    public function updateSectionOrder()
    {
        $this->view = false;
        @ob_end_clean();

        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            $postData = json_decode(file_get_contents('php://input'), true);

            if (!isset($postData['sections']) || !is_array($postData['sections'])) {
                throw new Exception('Format de données invalide');
            }

            if ($this->sectionModel->updateMultipleSections($postData['sections'])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Ordre des sections mis à jour avec succès'
                ]);
            } else {
                throw new Exception('Erreur lors de la mise à jour des sections');
            }
        } catch (Exception $e) {
            error_log('Erreur dans updateSectionOrder: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        exit();
    }

    private function getLotModel()
    {
        if ($this->lotModel === null) {
            $this->lotModel = $this->model('Lot');
        }
        return $this->lotModel;
    }

    public function getAllLots()
    {
        try {
            $this->view = false;
            ob_clean();
            header('Content-Type: application/json');

            // Récupérer l'ID de l'immeuble depuis la requête
            $idImmeuble = $_GET['immeubleId'] ?? null;

            if (!$idImmeuble) {
                throw new Exception('ID Immeuble non fourni');
            }

            $lots = $this->getLotModel()->getLotsByImmeuble($idImmeuble);

            echo json_encode([
                'success' => true,
                'data' => $lots
            ]);
        } catch (Exception $e) {
            error_log('Erreur getAllLots: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }

    public function getAllLotsToAcquire()
    {
        try {
            $this->view = false;
            ob_clean();
            header('Content-Type: application/json');

            $idSection = $_GET['sectionId'] ?? null;
            $idImmeuble = $_GET['immeubleId'] ?? null;
            $aAcquerir = $_GET['aAcquerir'] ?? null;

            error_log("Paramètres reçus - Section: $idSection, Immeuble: $idImmeuble, aAcquerir: $aAcquerir");

            if (!$idSection || !$idImmeuble) {
                throw new Exception('ID Section ou Immeuble manquant');
            }

            // Utiliser le modèle Lot pour récupérer les lots
            $lotModel = $this->getLotModel();
            $lots = $lotModel->getLotsBySection($idSection, $aAcquerir);

            error_log("Nombre de lots trouvés : " . count($lots));

            echo json_encode([
                'success' => true,
                'data' => $lots
            ]);
        } catch (Exception $e) {
            error_log('Erreur getAllLotsToAcquire: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }


    public function saveSectionLotsToAcquire()
    {
        // Configuration des erreurs
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->view = false;
        ob_clean();
        header('Content-Type: application/json');

        try {
            // Récupérer les données JSON
            $rawInput = file_get_contents('php://input');
            error_log('Données brutes reçues: ' . $rawInput);

            $data = json_decode($rawInput, true);

            // Validation des données
            if (!isset($data['sectionId']) || !isset($data['lots']) || !is_array($data['lots'])) {
                error_log('Données invalides reçues: ' . print_r($data, true));
                throw new Exception('Données manquantes ou invalides');
            }

            $sectionId = $data['sectionId'];
            $lots = $data['lots'];

            error_log("Traitement pour la section $sectionId");
            error_log("Nombre de lots à sauvegarder: " . count($lots));

            // Récupérer le modèle de lot
            $lotModel = $this->getLotModel();

            // Supprimer les lots existants
            // $deleteResult = $lotModel->deleteSectionLotsToAcquire($sectionId);
            // error_log("Résultat de la suppression : " . ($deleteResult ? "Succès" : "Échec"));

            // Insérer les nouveaux lots
            $insertSuccessCount = 0;
            foreach ($lots as $lot) {
                $insertResult = $lotModel->insertSectionLotToAcquire($sectionId, $lot['id']);
                if ($insertResult) {
                    $insertSuccessCount++;
                }
            }

            error_log("Lots insérés avec succès : $insertSuccessCount sur " . count($lots));

            // Réponse JSON
            echo json_encode([
                'success' => true,
                'message' => "Lots à acquérir sauvegardés. $insertSuccessCount lots insérés.",
                'details' => [
                    'sectionId' => $sectionId,
                    'totalLots' => count($lots),
                    'successfulInserts' => $insertSuccessCount
                ]
            ]);
        } catch (Exception $e) {
            error_log('Erreur complète : ' . $e->getMessage());
            error_log('Trace : ' . $e->getTraceAsString());

            // Réponse JSON d'erreur
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'rawInput' => $rawInput
            ]);
        }

        exit();
    }

    public function updateAction()
    {
        $this->view = false;
        ob_clean();
        header('Content-Type: application/json');

        try {
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);

            if (!isset($data['sectionId']) || !isset($data['action']) || !isset($data['immeubleId']) || !isset($data['projetId'])) {
                throw new Exception('Données manquantes');
            }

            // Récupérer les informations de l'immeuble
            $immeubleModel = $this->model('Immeuble');
            $immeuble = $immeubleModel->findImmeubleById($data['immeubleId']);
            $adresseComplete = $immeuble ? $immeuble->adresse . ", " . $immeuble->codePostal . " " . $immeuble->ville : "";
            $nomImmeuble = $immeuble ? $immeuble->nomImmeubleSyndic : "";

            // Récupérer tous les lots de l'immeuble avec le projetId
            $lots = $this->getLotModel()->getLotsByImmeuble($data['immeubleId']);
            $totalLots = count($lots);

            // Compteurs pour chaque type
            $logements = 0;
            $annexes = 0;
            $commerces = 0;
            $logementsVacants = 0;

            foreach ($lots as $lot) {
                if ($lot->typeLot == 'Appartement' || $lot->typeLot == 'Chambre') {
                    $logements++;
                    // Compter les logements vacants
                    if ($lot->logementVacant == "Oui") {
                        $logementsVacants++;
                    }
                } elseif ($lot->typeLot == 'Cave' || $lot->typeLot == 'Grenier' || $lot->typeLot == 'Débarras') {
                    $annexes++;
                } elseif ($lot->typeLot == 'Boutique' || $lot->typeLot == 'Local') {
                    $commerces++;
                }
            }

            // Définir le texte pour les logements vacants
            $texteVacant = "";
            if ($logementsVacants > 1) {
                $texteVacant = "et plusieurs logements vacants.";
            } elseif ($logementsVacants == 1) {
                $texteVacant = "et 1 logement vacant.";
            }

            $section = findItemByColumn("wbcc_section", "idSection",  $data['sectionId']);
            $contenuSection =  $section ? $section->contenuSection : "";

            if ($data['action'] == "tousLots") {
                $contenuSection = "La copropriété comprend $totalLots lots, répartis entre $logements logements, $commerces commerce(s), et $annexes espaces communs tels que les caves et greniers. Les lots appartiennent à 19 copropriétaires, avec une forte concentration d'impayés " . $texteVacant;
            } elseif ($data['action'] == "lotAcquerir") {
                $contenuSection = "L'immeuble situé au $nomImmeuble se compose d'un total de $totalLots lots, incluant $logements lots principaux (appartements et chambres) et $annexes lots secondaires (caves et greniers). Pour garantir un redressement efficace, RYM Partners cible les lots principaux tout en prenant en compte leurs lots secondaires associés, qui seront intégrés dans chaque acquisition. Voici le tableau récapitulatif des lots identifiés, avec une colonne indiquant les lots principaux qui sont au cœur des acquisitions prévues :";
            }

            $result = $this->sectionModel->updateSectionAction(
                $data['sectionId'],
                $data['action'],
                $contenuSection
            );

            error_log("Résultat de la mise à jour: " . ($result ? 'succès' : 'échec'));

            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Action mise à jour' : 'Erreur lors de la mise à jour'
            ]);
        } catch (Exception $e) {

            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }

    // Mise à jour de l'action d'une section

    public function getVariableValuesForSection()
    {
        ob_clean();
        header('Content-Type: application/json');

        try {
            // Récupérer l'ID de la section et du lot depuis la requête
            $sectionId = $_GET['sectionId'] ?? null;
            $lotId = $_GET['lotId'] ?? null;

            if (!$sectionId || !$lotId) {
                throw new Exception('ID Section ou ID Lot manquant');
            }

            // Initialiser le modèle de variable simulation
            $variableSimulationModel = $this->model('VariableSimulation');

            // Récupérer les variables avec leurs valeurs pour la section et le lot
            $variables = $variableSimulationModel->getVariableValuesForSection($sectionId, $lotId);

            echo json_encode([
                'success' => true,
                'data' => $variables
            ]);
        } catch (Exception $e) {
            error_log('Erreur getVariableValuesForSection: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }

    public function saveVariableValue()
    {
        ob_clean();
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Vérifier toutes les données nécessaires de base
            if (!isset($data['sectionId']) || !isset($data['lotId']) || !isset($data['montant'])) {
                throw new Exception('Données manquantes pour la sauvegarde');
            }

            // Données pour nouvelle variable
            $isNewVariable = empty($data['variableId']); // Si variableId est 0 ou non défini
            if ($isNewVariable && (!isset($data['nomVariableSimulation']) || !isset($data['typeValeurSimulation']))) {
                throw new Exception('Données manquantes pour la nouvelle variable');
            }

            $variableSimulationModel = $this->model('VariableSimulation');

            if ($isNewVariable) {
                // Gérer le cas où la catégorie est null
                $categorie = array_key_exists('categorie', $data) ? $data['categorie'] : null;
                $formuleCoutTotal = array_key_exists('formuleCoutTotal', $data) ? $data['formuleCoutTotal'] : null;

                // Créer nouvelle variable puis association
                $result = $variableSimulationModel->createVariableAndAssociation(
                    $data['sectionId'],
                    $data['lotId'],
                    $data['montant'],
                    $data['nomVariableSimulation'],
                    $categorie,
                    $data['typeValeurSimulation'],
                    $formuleCoutTotal
                );
            } else {
                // Juste mettre à jour l'association
                $result = $variableSimulationModel->saveVariableValue(
                    $data['sectionId'],
                    $data['lotId'],
                    $data['variableId'],
                    $data['montant']
                );
            }

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Variable sauvegardée avec succès'
                ]);
            } else {
                throw new Exception('Erreur lors de la sauvegarde');
            }
        } catch (Exception $e) {
            error_log('Erreur saveVariableValue: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }

    public function getAllLibellesVariables()
    {
        ob_clean();
        header('Content-Type: application/json');

        try {
            $variableSimulationModel = $this->model('VariableSimulation');
            $libelles = $variableSimulationModel->getAllLibellesVariables();

            echo json_encode([
                'success' => true,
                'data' => $libelles
            ]);
        } catch (Exception $e) {
            error_log('Erreur getAllLibellesVariables: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }


    public function calculateSimulation()
    {
        $this->view = false;
        ob_clean();
        header('Content-Type: application/json');

        try {
            $sectionId = $_GET['sectionId'] ?? null;

            if (!$sectionId) {
                throw new Exception('ID Section manquant');
            }

            // Initialiser le modèle
            $variableSimulationModel = $this->model('VariableSimulation');

            // Récupérer toutes les variables et leurs valeurs
            $variables = $variableSimulationModel->getVariableValuesForSection($sectionId);

            // Créer un tableau associatif pour faciliter l'accès aux valeurs
            $values = [];
            foreach ($variables as $variable) {
                $values[$variable->libelleVariable] =
                    $variable->montant ??
                    $variable->valeurVariableSimulation ??
                    0;
            }

            // Calculer les résultats pour chaque variable qui a une formule
            $results = [];
            foreach ($variables as $variable) {
                if (!empty($variable->formuleCoutTotal)) {
                    // Évaluer la formule en remplaçant les variables par leurs valeurs
                    $formula = $variable->formuleCoutTotal;
                    foreach ($values as $key => $value) {
                        $formula = str_replace($key, $value, $formula);
                    }

                    // Évaluer la formule de manière sécurisée
                    try {
                        $results[$variable->nomVariableSimulation] = eval("return $formula;");
                    } catch (Exception $e) {
                        $results[$variable->nomVariableSimulation] = 'Erreur de calcul';
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'data' => $results
            ]);
        } catch (Exception $e) {
            error_log('Erreur calculateSimulation: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }
}