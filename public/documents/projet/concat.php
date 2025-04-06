<?php
header('Access-Control-Allow-Origin: *');
require_once "../../../app/config/config.php";
require_once "../../../app/libraries/Database.php";
require_once "../../../app/libraries/SMTP.php";
require_once "../../../app/libraries/PHPMailer.php";
require_once "../../../app/libraries/Role.php";
require('../../TCPDF/tcpdf.php');
require('../../FPDI-2.3.7/src/autoload.php');

class Concat extends \setasign\Fpdi\Tcpdf\Fpdi
{
    protected $outputFile;
    protected $tplId;

    public function __construct($outputFile)
    {
        $this->outputFile = $outputFile;
    }
    
    public function concatTwo($nomDocument1, $nomDocument2)
    {
        $nomDocument1 = "projet_export/$nomDocument1";
        $nomDocument2 = "../annexe/$nomDocument2";
        $this->getAliasNbPages();
        $pageCount1 = 0;

        if (file_exists($nomDocument1)) {
            $open = false;
            try {
                $pageCount = $this->setSourceFile($nomDocument1);
                $open = true;
            } catch (\Throwable $th) {
                $open = false;
            }
            
            // Ajoutez ceci après la vérification de l'existence du fichier
            if ($pageCount1 <= 0 || $open == false) {
                echo "Le fichier $nomDocument1 ne contient aucune page valide.";
                exit;
            }
            
            if ($open) {
                $pageCount1 = $this->setSourceFile($nomDocument1);
                for ($i = 1; $i <= $pageCount1; $i++) {
                    $pageId = $this->importPage($i, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

                    $this->addPage();
                    $this->useImportedPage($pageId, 0, 0, 211, 297, true);
                }
            }
        }
        else {
            echo "Le fichier $nomDocument1 n'existe pas";
            exit;
        }

        if (file_exists($nomDocument2)) {

            $open = false;
            try {
                $pageCount = $this->setSourceFile($nomDocument2);
                $open = true;
            } catch (\Throwable $th) {
                $open = false;
            }

            // Ajoutez ceci après la vérification de l'existence du fichier pour le deuxième document
            if ($pageCount2 <= 0) {
                echo "Le fichier $nomDocument2 ne contient aucune page valide.";
                exit;
            }

            if ($open) {
                $pageCount2 =  $this->setSourceFile($nomDocument2);
                $this->AddPage();

                $this->SetY(25);
                $this->SetX(2);
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('Helvetica', 'B', 20);
                $this->Text(2, 25, "ANNEXE $index : $titre", 0, false, true, 0, 0, 'C');

                for ($i = 1; $i <= $pageCount2; $i++) {
                    $this->addPage();
                    $pageId = $this->importPage($i, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);
                    $this->useImportedPage($pageId, 20, 30, 170, 230, false);
                }
            }
        }
        else {
            echo "Le fichier $nomDocument2 n'existe pas";
            exit;
        }

        // Ajoutez ceci après la vérification de l'existence du fichier
        if ($pageCount1 <= 0) {
            echo "Le fichier $nomDocument1 ne contient aucune page valide.";
            exit;
        }

        // Ajoutez ceci après la vérification de l'existence du fichier pour le deuxième document
        if ($pageCount2 <= 0) {
            echo "Le fichier $nomDocument2 ne contient aucune page valide.";
            exit;
        }

        $this->Output('file://' . $_SERVER['DOCUMENT_ROOT'] . "/public/projet_export/$nomDocument1", "F");
        echo json_encode($nomDocument1);
    }
}