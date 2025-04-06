<?php
require_once "../../../app/config/config.php";
require_once "../../../app/libraries/Utils.php";
require_once "../../fpdf183/fpdf.php";
require_once "sommaire.php";
require_once "projet.php";
require('../../FPDI-2.3.7/src/autoload.php');

use setasign\Fpdi\Fpdi;

class ProjetPdf extends Sommaire
{
    public $fileName;
    protected $sommairePdf;
    protected $projet;
    public $immeuble;
    protected $sections;
    protected $documents;
    public $typeRapport;

    function __construct($fileName, $projet, $immeuble, $sections, $config, $documents, $typeRapport)
    {
        parent::__construct($config, $fileName, $immeuble, $typeRapport);
        $this->sommairePdf = new Sommaire($config, $fileName, $immeuble, $typeRapport);
        $this->projet = $projet;
        $this->immeuble = $immeuble;
        $this->sections = $sections;
        $this->fileName = $fileName;
        $this->documents = $documents;
        $this->typeRapport = $typeRapport;
    }

    public function Header($content = "")
    {
        $this->SetMargins(0, 0, 0, 0);
        $this->setMargins(15, 15, 20, 20);
        parent::Header();
        // $headerHTML = '
        // <div style=" text-align: left; 
        //         margin: 0; padding: -10px 0 0 20px; width: 100%; height: 73px; font-size: 11px; font-weight: bold; line-height: 73px;"> <i> ' . $this->projet->nomProjet . ' </i> </div>';
        // $this->SetHTMLHeader($headerHTML);
        // $this->setMargins(15, 15, 20, 20);
    }

    function PageDeGarde()
    {
        $this->Bookmark(iconv('UTF-8', 'windows-1252', $this->projet->nomProjet), 0);
        $photoImm = false;
        if ($this->immeuble && $this->immeuble->photoImmeuble != null && $this->immeuble->photoImmeuble != "" && file_exists("../../documents/immeuble/" . $this->immeuble->photoImmeuble)) {
            $photoImm = true;
        }

        if ($photoImm) {
            $this->y = (50);
            $this->SetY($this->y);
            $this->Image((URLROOT  . "/public/documents/immeuble/" . $this->immeuble->photoImmeuble), 15, 50, 180, 120);
        } else {
            $this->y = (($this->h / 2) - 50);
        }

        $this->SetFont('', 'B', 18);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->WriteCell(0, 20, "PROJET : " . $this->projet->nomProjet, 0, 0, 'C', true, '');
        $this->Ln(15);
        $this->SetFont('', 'B', 16);
        $this->WriteHTML("<p style='text-align:center'>" . ("Adresse : " . $this->immeuble->adresse . " " . $this->immeuble->codePostal . " " . $this->immeuble->ville) . "</p>");
        //DESCRIPTION

        $this->SetFont('', '', 14);
        $this->WriteHTML("<p style='text-align:justify'>" . ($this->projet->descriptionProjet) . "</p>");
    }

    function Footer()
    {
        parent::Footer();
        // $this->SetY(-8);
        // $this->SetFont('Arial', 'I', 8);
        // $this->Cell(0, 10, $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->SetY(-15);
        $this->SetFont('Arial', 'BI', 8);
        $projectName = iconv('UTF-8', 'windows-1252', $this->projet->nomProjet);
        $this->WriteCell(0, 12, $projectName, 0, 0, 'L');
        $this->WriteCell(0, 7, ($this->page . '/{nb}'), 0, 0, 'R');
    }

    function sommaire()
    {
        $this->sommairePdf->AliasNbPages();

        $this->sommairePdf->setMargins(20, 100, 25, 20);
        // PAGE DE GARDE
        $this->sommairePdf->AddPage();
        $this->sommairePdf->AddPage();

        $this->sommairePdf->startPageNums();

        // CONTENU DOCUMENT PROJET AVEC TOUTES LES SECTIONS
        $this->sommairePdf->ajouterSectionsRecursives($this->projet, $this->sections);

        // ADD SOMMAIRE
        return $this->sommairePdf->insertSommaire($this->documents);
    }

    function addDocument()
    {
        //Numéro de page
        $this->AliasNbPages();

        $this->setMargins(20, 100, 25, 20);
        $nb_page = $this->sommaire();

        // PAGE DE GARDE
        $this->AddPage();
        $this->PageDeGarde();

        // INSERER LE SOMMAIRE
        $this->insertSommaire($this->documents, $nb_page, $this->sommairePdf->returnSommaire());

        // CONTENU DOCUMENT PROJET AVEC TOUTES LES SECTIONS
        $this->ajouterSectionsRecursives($this->projet, $this->sections);

        //SAUVEGARDER LE DOCUMENT
        $this->Output("../../../public/documents/projet/projet_export/$this->fileName.pdf", 'F');
        return "$this->fileName.pdf";
    }

    function document()
    {
        $document = $this->addDocument();

        $pdf = new Fpdi();

        $pdfFiles = [
            ['nomDocument' => 'projet', 'urlDocument' => 'projet_export/' .  $document]
        ];
        foreach ($this->documents as $document) {
            $extension = pathinfo($document->nomDocument, PATHINFO_EXTENSION);
            if ($extension == 'pdf') {
                $pdfFiles[] =
                    [
                        'nomDocument' => $document->nomDocument,
                        'urlDocument' =>  '../' . $document->urlDossier . '/' . $document->urlDocument
                    ];
            }
        }

        foreach ($pdfFiles as $key => $file) {

            $pageCount = 0;
            if (file_exists($file['urlDocument'])) {
                $open = false;
                try {
                    $pageCount = $pdf->setSourceFile($file['urlDocument']);
                    $open = true;
                } catch (\Throwable $th) {
                    $open = false;
                }

                if ($open) {
                    $pageCount = $pdf->setSourceFile($file['urlDocument']);
                    if ($pageCount <= 0 || $open == false) {
                        echo "Le fichier " . $file['nomDocument'] . " ne contient aucune page valide.";
                        exit;
                    }



                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $templateId = $pdf->importPage($pageNo);
                        $size = $pdf->getTemplateSize($templateId);

                        // if ($size['width'] > $size['height']) {
                        //     $pdf->addPage('P', [$size['width'], $size['height']]);
                        //     $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height'], true);
                        // } else {
                        //     $pdf->addPage('P', [$size['width'], $size['height']]);
                        //     $pdf->useTemplate($templateId);
                        // }

                        $pdf->addPage('P', 'A4');
                        $pdf->useTemplate($templateId, 0, 0, 595, 842, true);
                        if ($pageNo == 1 && $file['nomDocument'] != 'projet') {
                            $pdf->SetFont('Arial', 'BU', 30);
                            $pdf->Cell(0, 10, "Document : " . $file['nomDocument'], 0, 0, 'C');
                        }

                        // Vérifie les liens
                        // foreach ($this->links as $link) {
                        //     extract($link);
                        //     // Utiliser les données pour créer un lien
                        //     $pdf->Link($x, $y, $width, $height, $url);
                        // }
                    }
                }
            } else {
                echo "Le fichier " . $file['nomDocument'] . " n'existe pas";
                exit;
            }
        }

        $name =  "Global_" . $this->fileName . ".pdf";
        $pdf->Output('../../../public/documents/projet/projet_export/' . $name, 'F');
        $this->Output($this->fileName . '.pdf', 'F');
        echo $this->fileName . '.pdf';
    }
}