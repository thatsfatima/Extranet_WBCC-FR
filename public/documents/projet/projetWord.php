<?php
require_once "../../../app/config/config.php";
require_once "../../../app/libraries/Utils.php";
require_once "../../fpdf183/fpdf.php";
require_once "../../../app/models/Section.php";
require_once "sommaire.php";
require_once "projet.php";
require_once '../word_html.php';

class ProjetWord extends Word_html
{
    public $sectionModel;
    protected $projet;
    protected $immeuble;
    protected $sections;
    protected $numPage = 1;
    protected $sommaire = array();

    public function __construct($fileName, $projet, $immeuble, $sections)
    {
        parent::__construct($fileName);
        $this->sectionModel = new Section();
        $this->projet = $projet;
        $this->immeuble = $immeuble;
        $this->sections = $sections;
    }

    public function Footer()
    {
        $this->render("<footer style='position: absolute; bottom: 2px; padding: 10px; margin-top: 30px; width: 1000px; height: 5%; display: flex; flex-direction: row; justify-content: space-between; align-items: center;'><span>" . $this->projet->nomProjet . "</span><strong>" . $this->numPage++ . "</strong></footer>");
    }

    public function SectionTitre($section, $texte = '', $fontSize = 18, $x = 0)
    {
        $fontSize = $fontSize . '%';
        $x = $x . '%';
        $niveau = 0;
        if ($section->numeroSection) {
            $niveau = substr_count($section->numeroSection, '.');
        }

        if ($niveau == 0) {
            $this->AddPage();
            $this->render("<div style='width: 1000px; height: 50vh; display: flex; justify-content: center; align-items: center;'><span style='font-size: $fontSize; font-weight: bold;'>Chapitre $section->numeroSection : $section->titreSection</span></div>");
            $this->EntrerDonnee($section);
            $this->AddPage();
        } else {
            if ($niveau == 1 && $texte != '') {
                $this->AddPage();
            }
            $this->render("<span style='font-size: $fontSize; padding-left: $x; font-weight: bold;'>$section->numeroSection : $section->titreSection</span>");
            $this->EntrerDonnee($section);
        }
    }

    public function SectionContent($texte = '', $fontSize = 14, $x = 18)
    {
        $fontSize = $fontSize . '%';
        $x = $x . '%';
        $this->render("<div style='font-size: $fontSize; padding-left: $x;'> $texte </div>");
    }

    public function ajouterSection($section, $fontSizeTitle = 16, $fontSizeContent = 11, $xTitle = 0, $xContent = 18)
    {
        $this->SectionTitre($section, $section->contenuSection, $fontSizeTitle, $xTitle);
        $this->SectionContent($section->contenuSection, $fontSizeContent, $xContent);
    }

    public function ajouterSectionsRecursives($projet, $sections, $niveau = 0)
    {
        $parametres = [
            0 => [160, 110, 000, 220],
            1 => [130, 110, 100, 220],
            2 => [120, 100, 140, 220],
            3 => [110, 100, 180, 220],
            4 => [110, 100, 220, 260]
        ];

        foreach ($sections as $section) {
            $params = $parametres[min($niveau, 4)];
            $this->ajouterSection($section, ...$params);
            $sous_sections = $this->sectionModel->getSectionsByParent($section->idSection);
            if (!empty($sous_sections)) {
                $this->ajouterSectionsRecursives($projet, $sous_sections, $niveau + 1);
            }
        }
    }

    public function EntrerDonnee($section)
    {
        $level = substr_count($section->numeroSection, '.');
        // Entreer les données dans le sommaire
        $this->sommaire[$section->numeroSection] = array('t' => $section->titreSection, 'l' => $level, 'p' => $this->numPage);
    }

    public function insertSommaire($location = 2, $label = 'Sommaire', $labelSize = '200%', $entrySize = '100%', $tocfont = 'Arial')
    {
        // $this->AddPage();
        $this->render("<div style='width: 1000px; font-size: $labelSize; font-weight: bold; font-family: $tocfont; text-align: center; margin-bottom: 20%;'><span>$label</span></div>");

        $start = $this->numPage;
        $this->render("<table style='width: 600px; margin: 10px 0px;'><tbody id='sommaire'>");
        foreach ($this->sommaire as $key => $t) {
            $level = $t['l'];
            $this->render("<tr style='display: flex; justify-content: space-between; align-items: center; margin-left: " . ($level * 2) . "%;'>");
            $this->render("<td id='p1" . $key . "' style='text-align: left; text-wrap: wrap; font-size: $entrySize; " . ($level == 0 ? "font-weight: bold;" : "") . "'>" . $t['t'] . "</td>");
            $this->render("<td id='p2" . $key . "' style='text-align: center; font-size: $entrySize; width: 300px; display: flex; align-items: center; border-bottom: 2px dotted black;'></td>");
            $this->render("<td id='p3" . $key . "' style='text-align: right; font-size: $entrySize;'></td>");
            $this->render("</tr>");
        }
        $this->render("</tbody></table>");
    }

    public function pageDeGarde()
    {
        $this->Header();
        if (empty($this->projet) || empty($this->immeuble)) {
            $this->render("<div>Erreur : Les données du projet ou de l'immeuble sont manquantes.</div>");
            return;
        }
        $marginTop = ($this->immeuble->photoImmeuble != null && $this->immeuble->photoImmeuble != "" && file_exists("../../documents/immeuble/" . $this->immeuble->photoImmeuble)) ? "100px" : "300px";
        $this->render("<div style='width: 1000px; height: 500px; margin: $marginTop 100px; display: flex; flex-direction: column; justify-content: center; align-items: center;'>");
        $this->render("<div style='text-align: center;'><h1 style='font-size: 240%; font-weight: bold; font-family: Arial;'>PROJET : " . $this->projet->nomProjet . "</h1></div>");
        $this->render("<div style='text-align: center;'><h2 style='font-size: 180%; font-family: Arial;'>" . $this->immeuble->adresse . ", " . $this->immeuble->codePostal . ", " . $this->immeuble->ville . "</h2></div>");

        if ($this->immeuble && $this->immeuble->photoImmeuble != null && $this->immeuble->photoImmeuble != "" && file_exists("../../documents/immeuble/" . $this->immeuble->photoImmeuble)) {
            $this->render("<div style='width: 1000px; display: flex; justify-content: center; align-items: center;'><img src='" . URLROOT . "/public/documents/immeuble/" . $this->immeuble->photoImmeuble . "' style='width: 1000px; height: auto; max-height: 400%;'></div>");
        }
        $this->render("</div>");
    }

    function document()
    {
        $this->startOfDoc();
        // $this->pageDeGarde();
        $this->insertSommaire();
        $this->ajouterSectionsRecursives($this->projet, $this->sections);
        $script = "
        let sommaire = " . json_encode($this->sommaire) . ";
            const tbody = document.getElementById('sommaire');
            sommaire.forEach((t, key) => {
            const level = t.l;
            const entrySize = '14px';
            const tr = document.createElement('tr');
            tr.style.display = 'flex';
            tr.style.justifyContent = 'space-between';
            tr.style.alignItems = 'center';
            tr.style.marginLeft = `t.l*2%`;

            const td1 = document.createElement('td');
            td1.style.textAlign = 'left';
            td1.style.whiteSpace = 'wrap';
            td1.style.fontSize = entrySize;
            td1.style.fontWeight = level === 0 ? 'bold' : 'normal';
            td1.textContent = t.t;

            const td2 = document.createElement('td');
            td2.style.textAlign = 'center';
            td2.style.fontSize = entrySize;
            td2.style.width = '300px';
            td2.style.display = 'flex';
            td2.style.alignItems = 'center';
            td2.style.borderBottom = '2px dotted black';

            const td3 = document.createElement('td');
            td3.style.textAlign = 'right';
            td3.style.fontSize = entrySize;

            tr.appendChild(td1);
            tr.appendChild(td2);
            tr.appendChild(td3);

            tbody.appendChild(tr);
        });
        ";
        echo "</section>";
        echo "<script>$script</script>";
        $this->endOfDoc($script);
    }
}
