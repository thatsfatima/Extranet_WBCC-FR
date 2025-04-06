<?php
class SommaireCtrl extends Controller
{
    private $sommaireModel;

    public function __construct()
    {
        $this->sommaireModel = $this->model('Sommaire');
    }



    public function index()
    {
        $sommaires = $this->sommaireModel->getAll();
        $this->view('sommaires/index', [
            'sommaires' => $sommaires,
            'title' => 'Liste des sommaires'
        ]);
    }

    public function updateHtmlTPG()
    {
        ob_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!isset($_POST['sommaireId']) || !isset($_POST['html'])) {
                    throw new Exception('Paramètres manquants');
                }

                $sommaireId = $_POST['sommaireId'];
                $html = $_POST['html'];

                if ($this->sommaireModel->updateHtmlTPG($sommaireId, $html)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'HTML TPG mis à jour avec succès'
                    ]);
                } else {
                    throw new Exception('Erreur lors de la mise à jour du HTML TPG');
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Méthode non autorisée'
            ]);
        }
        exit();
    }

    public function getHtmlTPG()
    {
        ob_clean();
        header('Content-Type: application/json');

        // Vérifier si 'sommaireId' existe dans la requête
        if (!isset($_GET['sommaireId'])) {
            echo json_encode([
                'success' => false,
                'error' => 'ID du sommaire manquant'
            ]);
            exit();
        }

        $sommaireId = $_GET['sommaireId'];

        // Récupérer l'HTML sans utiliser try-catch
        $html = $this->sommaireModel->getHtmlTPG($sommaireId);

        echo json_encode([
            'success' => true,
            'data' => [
                'html' => $html
            ]
        ]);
        exit();
    }

    public function clearHtmlTPG()
    {
        ob_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!isset($_POST['sommaireId'])) {
                    throw new Exception('ID du sommaire manquant');
                }

                $sommaireId = $_POST['sommaireId'];

                // Mettre htmlTPG à NULL dans la base de données
                if ($this->sommaireModel->clearHtmlTPG($sommaireId)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'HTML TPG effacé avec succès'
                    ]);
                } else {
                    throw new Exception('Erreur lors de l\'effacement du HTML TPG');
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Méthode non autorisée'
            ]);
        }
        exit();
    }

    public function getAll()
    {
        $this->view = false;
        ob_clean();
        header('Content-Type: application/json');

        try {
            $sommaires = $this->sommaireModel->getAllSommairesWithProjects();
            echo json_encode([
                'success' => true,
                'sommaires' => array_map(function ($sommaire) {
                    return [
                        'idSommaire' => $sommaire->idSommaire,
                        'titreSommaire' => $sommaire->titreSommaire,
                        'nomProjet' => $sommaire->nomProjet
                    ];
                }, $sommaires)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }

    public function getAllProjets()
    {
        $projetModel = $this->model('Projet');
        $projets = $projetModel->getAllProjetsWithSommaire();

        // Renvoyer en JSON pour DataTable
        header('Content-Type: application/json');
        echo json_encode($projets);
        exit;
    }

    public function show($id)
    {
        $sommaire = $this->sommaireModel->findBy('idSommaire', $id);
        if ($sommaire) {
            // Récupérer les informations du projet associé
            $sommaireWithProjet = $this->sommaireModel->getSommaireWithProjet($id);
            $this->view('sommaires/show', [
                'sommaire' => $sommaire,
                'sommaireWithProjet' => $sommaireWithProjet,
                'title' => 'Détails du sommaire'
            ]);
        } else {
            $this->redirectToMethod('sommaires');
        }
    }

    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'numeroSommaire' => trim($_POST['numeroSommaire']),
                'titreSommaire' => trim($_POST['titreSommaire']),
                'idProjetF' => trim($_POST['idProjetF'])
            ];

            // Vérifier si c'est une copie d'un sommaire existant
            if (isset($_POST['sommaireType']) && $_POST['sommaireType'] === 'existing' && !empty($_POST['sommaireSource'])) {
                // Copier le sommaire avec ses sections
                $sourceSommaireId = $_POST['sommaireSource'];
                $newSommaireId = $this->sommaireModel->copySommaireWithSections($sourceSommaireId, $data);
                if ($newSommaireId) {
                    $_SESSION['success'] = "Sommaire et ses sections copiés avec succès";
                    $this->redirectToMethod('Projet', 'projet', $data['idProjetF']);
                } else {
                    $_SESSION['error'] = "Erreur lors de la copie du sommaire";
                    $this->redirectToMethod('Projet', 'projet', $data['idProjetF']);
                }
            } else {
                // Création d'un sommaire vide
                if ($this->sommaireModel->create($data)) {
                    $_SESSION['success'] = "Sommaire créé avec succès";
                    $this->redirectToMethod('Projet', 'projet', $data['idProjetF']);
                } else {
                    $_SESSION['error'] = "Erreur lors de la création du sommaire";
                    $this->redirectToMethod('Projet', 'projet', $data['idProjetF']);
                }
            }
        } else {
            $this->redirectToMethod('Projet', 'projet');
        }
    }

    public function edit($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Traitement du formulaire
            $data = [
                'numeroSommaire' => trim($_POST['numeroSommaire']),
                'titreSommaire' => trim($_POST['titreSommaire']),
                'idProjetF' => trim($_POST['idProjetF'])
            ];

            if ($this->sommaireModel->updateSommaire($id, $data)) {
                $this->redirectToMethod('sommaires', 'show', $id);
            } else {
                $this->view('sommaires/edit', [
                    'data' => $data,
                    'error' => 'Une erreur est survenue lors de la modification du sommaire',
                    'title' => 'Modifier le sommaire'
                ]);
            }
        } else {
            // Récupérer les données du sommaire
            $sommaire = $this->sommaireModel->findBy('idSommaire', $id);
            if ($sommaire) {
                $this->view('sommaires/edit', [
                    'sommaire' => $sommaire,
                    'title' => 'Modifier le sommaire'
                ]);
            } else {
                $this->redirectToMethod('sommaires');
            }
        }
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->sommaireModel->delete($id)) {
                $this->redirectToMethod('sommaires');
            } else {
                $this->redirectToMethod('sommaires', 'show', $id);
            }
        } else {
            $this->redirectToMethod('sommaires');
        }
    }
}