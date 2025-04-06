<?php
require_once "../../../app/config/config.php";
require_once "../../../app/libraries/Utils.php";
require_once "../../../app/libraries/Database.php";
require('../../fpdf183/fpdf.php');

$idOp  = $_GET['idOP'];

$db = new Database();
//RT
$db->query("SELECT * FROM wbcc_releve_technique WHERE idOpportunityF=$idOp LIMIT 1");
$rt = $db->single();
//RV
$db->query("SELECT * FROM wbcc_rendez_vous WHERE idOpportunityF=$idOp LIMIT 1");
$rv = $db->single();
//OPPORTUNITY
$db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity = $idOp");
$o = $db->single();
$GLOBALS['opportunity'] = $o;
//GET IMMEUBLE
$db->query("SELECT * FROM wbcc_immeuble, wbcc_opportunity_immeuble  WHERE idImmeuble=idImmeubleF AND idOpportunityF=$o->idOpportunity LIMIT 1");
$immeuble = $db->single();
if ($immeuble == false && $o->immeuble != null && $o->immeuble != "") {
    $db->query("SELECT * FROM wbcc_immeuble  WHERE codeImmeuble='$o->immeuble' LIMIT 1");
    $immeuble = $db->single();
}
//GET APPARTEMENT
$db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF  AND idOpportunityF=$o->idOpportunity ORDER BY idOpportunityApp DESC  LIMIT 1");
$app = $db->single();

$adresse = ($immeuble) ? $immeuble->adresse : (($app) ? $app->adresse  : "");
$cp = ($immeuble) ? $immeuble->codePostal  : (($app) ?  $app->codePostal  : "");
$ville = ($immeuble) ? $immeuble->ville : (($app) ?  $app->ville : "");

//CONTACT
$db->query("SELECT * FROM wbcc_contact  WHERE  numeroContact='$o->guidContactClient' LIMIT 1");
$contact = $db->single();
if ($contact == false) {
    $db->query("SELECT * FROM wbcc_contact  WHERE  fullName=:fullName LIMIT 1");
    $db->bind("fullName", $o->contactClient, null);
    $contact = $db->single();
}
if ($o->typeSinistre == "Partie commune exclusive" && $o->source != null && $o->source != "") {
    $db->query("SELECT * FROM wbcc_contact  WHERE  numeroContact='$o->source' LIMIT 1");
    $contact = $db->single();
}

//COMPAGNIE
$guidComp = $o->typeSinistre == "Partie commune exclusive" ? $o->guidComMRI : $o->guidComMRH;
$db->query("SELECT * FROM wbcc_company, wbcc_company_opportunity  WHERE idCompany=idCompanyF AND idOpportunityF=$o->idOpportunity AND category LIKE '%ASSURANCE%'  LIMIT 1");
$cie = $db->single();

$titre = "" . strtoupper($adresse . " du "  . date('d/m/Y'));
$tabPieces = [];
if ($rt) {
    $db->query("SELECT  * FROM wbcc_rt_piece WHERE idRTF= $rt->idRT");
    $tabPieces = $db->resultSet();
}

$estLocataire = false;
$estProprietaire = false;
$estPersMorale = false;

$do = "";
if (str_contains(strtolower($o->typeDO), "particulier")) {
    $db->query("SELECT * FROM wbcc_contact WHERE numeroContact = :guidDO LIMIT 1");
    $db->bind('guidDO', $o->guidDO, null);
    $do = $db->single();
} else {
    $db->query("SELECT * FROM wbcc_contact WHERE numeroContact = :guidDO LIMIT 1");
    $db->bind('guidDO', $o->guidContactClient, null);
    $do = $db->single();
}
if ($do && (str_contains(strtolower($do->statutContact), "proprietaire"))) {
    $estProprietaire = true;
} else {
    $estLocataire = true;
}
//APP
if ($app  && (str_contains(strtolower($app->typeProprietaire), "moral"))) {
    $estPersMorale = true;
}

if (sizeof($tabPieces) > 0) {
    //GET SUPPORTS
    foreach ($tabPieces as $key => $piece) {
        $db->query("SELECT  * FROM wbcc_rt_piece_support WHERE idRTPieceF= $piece->idRTPiece ");
        $supports = $db->resultSet();
        $piece->listSupports = $supports;
        //GET REVETEMENTS
        foreach ($supports as $key2 => $support) {
            $db->query("SELECT  * FROM wbcc_rt_revetement WHERE idRTPieceSupportF= $support->idRTPieceSupport ");
            $revetements = $db->resultSet();
            $support->listRevetements = $revetements;

            //GET OUVERTURES
            $db->query("SELECT  * FROM wbcc_rt_ouverture WHERE idRTPieceSupportF= $support->idRTPieceSupport ");
            $ouvertures = $db->resultSet();
            $support->listOuvertures = $ouvertures;
        }
    }
}

//bien
// $db->query("SELECT * FROM `wbcc_bien` WHERE `idRTF` = $rt->idRT");
// $data = $db->resultSet();

$numPolice = $o->typeSinistre == "Partie commune exclusive" ? $o->policeMRI : $o->policeMRH;
$numSinistre = $o->typeSinistre == "Partie commune exclusive" ? $o->sinistreMRI : $o->sinistreMRH;
$dateSinistre = "";
$dateDebutContrat = "";
$dateFinContrat = "";

if ($immeuble) {
    if ($o->typeSinistre == "Partie commune exclusive") {
        $numPolice = ($numPolice == null || $numPolice == "") ? $immeuble->numPolice : $numPolice;
    }
    if ($immeuble->dateEffetContrat != null && $immeuble->dateEffetContrat != "" && strpos("/", $immeuble->dateEffetContrat)) {
        $dateNew = date_parse_from_format("d/m/Y H:i", $immeuble->dateEffetContrat);
        if ($dateNew) {
            $dateDebutContrat = $dateNew['year'] . "-" . str_pad(($dateNew['month']), 2, '0', STR_PAD_LEFT) . "-" .  str_pad(($dateNew['day']), 2, '0', STR_PAD_LEFT);
        }
    } else {
        $dateDebutContrat = $immeuble->dateEffetContrat;
    }
    if ($immeuble->dateEcheanceContrat != null && $immeuble->dateEcheanceContrat != "" && strpos("/", $immeuble->dateEcheanceContrat)) {
        $dateNew = date_parse_from_format("d/m/Y H:i", $immeuble->dateEcheanceContrat);
        if ($dateNew) {
            $dateFinContrat = $dateNew['year'] . "-" . str_pad(($dateNew['month']), 2, '0', STR_PAD_LEFT) . "-" .  str_pad(($dateNew['day']), 2, '0', STR_PAD_LEFT);
        }
    } else {
        $dateFinContrat = $immeuble->dateEcheanceContrat;
    }
}

if ($app) {
    if ($o->typeSinistre == "Partie privative exclusive") {
        $numPolice = ($numPolice == null || $numPolice == "") ? $app->numPoliceOccupant : $numPolice;
    }
    if ($app->dateEffetOccupant != null && $app->dateEffetOccupant != "" && strpos("/", $app->dateEffetOccupant)) {
        $dateNew = date_parse_from_format("d/m/Y H:i", $app->dateEffetContratOccupant);
        if ($dateNew) {
            $dateDebutContrat = $dateNew['year'] . "-" . str_pad(($dateNew['month']), 2, '0', STR_PAD_LEFT) . "-" .  str_pad(($dateNew['day']), 2, '0', STR_PAD_LEFT);
        }
    } else {
        $dateDebutContrat = $app->dateEffetOccupant;
    }
    if ($app->dateEcheanceOccupant != null && $app->dateEcheanceOccupant != "" && strpos("/", $app->dateEcheanceOccupant)) {
        $dateNew = date_parse_from_format("d/m/Y H:i", $immeuble->dateEcheanceOccupant);
        if ($dateNew) {
            $dateFinContrat = $dateNew['year'] . "-" . str_pad(($dateNew['month']), 2, '0', STR_PAD_LEFT) . "-" .  str_pad(($dateNew['day']), 2, '0', STR_PAD_LEFT);
        }
    } else {
        $dateFinContrat = $app->dateEcheanceOccupant;
    }
}

$dateSinistre =  ($rt ? ($rt->date != null && $rt->date != "" && trim($rt->date) != "" ? $rt->date : ($rt->dateConstat != null && $rt->dateConstat != ""  && trim($rt->dateConstat) != "" ? $rt->dateConstat : ($rt->anneeSurvenance != null && $rt->anneeSurvenance != ""  && trim($rt->anneeSurvenance) != "" ? $rt->anneeSurvenance : ""))) : "");
if ($dateSinistre != "" && substr($dateSinistre, 4, 1) == '-') {
    $date = new DateTime($dateSinistre);
    $dateSinistre = $date->format('d/m/Y');
}
//DEVIS
$db->query("SELECT * FROM `wbcc_opportunity_devis` od, wbcc_opportunity, wbcc_devis WHERE idDevisF=idDevis AND idOpportunityF=idOpportunity AND idOpportunityF=$idOp AND od.valide=1");
$devis = $db->single();

class PDF extends FPDF
{
    // En-tête
    function Header()
    {
        if ($this->PageNo() == 1) {
            $this->Image('https://www.extranet.wbcc.fr/public/img/entete.PNG', 0, 0, $this->GetPageWidth(), 30);
            //   
        }
    }

    // Pied de page
    function Footer()
    {
        if ($this->PageNo() != 1) {
            // Positionnement à 1,5 cm du bas
            // $this->SetY(-8);
            // $this->SetFont('Arial', 'I', 8);
            // $this->Cell(0, 10, $this->PageNo() . '/{nb}', 0, 0, 'C');
            // // Début en police normale
            // $this->SetY(-30);
            // $this->SetFont('Arial', '', 10);
            // $this->SetTextColor(192, 0, 0);
            // $this->Cell(0, 5, 'World Business Contact Center - WBCC ASSISTANCE', 0, 0, 'C');

            // $this->SetY(-20);
            // $this->SetFont('Arial', 'BU', 10);
            // $this->SetTextColor(255, 255, 255);
            // $this->SetFillColor(192, 0, 0);
            // $this->Cell(0, 10, 'www.wbcc.fr', 0, 0, 'R', true, 'https://wbcc.fr/');
            // $this->SetY(-20);
            // $this->SetFont('Arial', 'BU', 10);
            // $this->SetTextColor(255, 255, 255);
            // $this->Cell(0, 10, 'gestion@wbcc.fr', 0, 0, 'L', false, 'mailto:gestion@wbcc.fr');

            // $this->SetY(-25);
            // $this->SetFont('Arial', '', 10);
            // $this->SetTextColor(0, 0, 0);
            // $this->Cell(0, 5, iconv('UTF-8', 'windows-1252', '218, Rue de Bellevue 92700 Colombes. Siret : 817 869 167 00012 Tél. 09 800 844 84. Fax. 09 85 84 53 39'), 0, 0, 'C');

            // $this->SetY(-12);
            // $this->SetFont('Arial', 'I', 8);
            // $this->SetTextColor(0, 0, 0);
            // $this->SetFillColor(192, 0, 0);
            // $this->Cell(0, 10, 'Fait le ' . date('d/m/Y'), 0, 0, 'R');
        }
    }
}

function hex2dec($couleur = "#000000")
{
    $R = substr($couleur, 1, 2);
    $rouge = hexdec($R);
    $V = substr($couleur, 3, 2);
    $vert = hexdec($V);
    $B = substr($couleur, 5, 2);
    $bleu = hexdec($B);
    $tbl_couleur = array();
    $tbl_couleur['R'] = $rouge;
    $tbl_couleur['V'] = $vert;
    $tbl_couleur['B'] = $bleu;
    return $tbl_couleur;
}

//conversion pixel -> millimeter at 72 dpi
function px2mm($px)
{
    return $px * 25.4 / 72;
}

function txtentities($html)
{
    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans = array_flip($trans);
    return strtr($html, $trans);
}

class PDF_HTML extends FPDF
{
    //variables of html parser
    protected $B;
    protected $I;
    protected $U;
    protected $HREF;
    protected $fontlist;
    protected $issetfont;
    protected $issetcolor;

    function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        //Call parent constructor
        parent::__construct($orientation, $unit, $size);
        //Initialization
        $this->B = 0;
        $this->I = 0;
        $this->U = 0;
        $this->HREF = '';
        $this->fontlist = array('arial', 'times', 'courier', 'helvetica', 'symbol');
        $this->issetfont = false;
        $this->issetcolor = false;
    }


    function WriteHTML1($html)
    {
        //HTML parser
        $html = strip_tags($html, "<b><u><i><a><img><p><br><strong><em><font><tr><blockquote>"); //supprime tous les tags sauf ceux reconnus
        $html = str_replace("\n", ' ', $html); //remplace retour à la ligne par un espace
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE); //éclate la chaîne avec les balises
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                //Text
                if ($this->HREF)
                    $this->PutLink($this->HREF, $e);
                else
                    $this->Write(5, txtentities($e));
            } else {
                //Tag
                if ($e[0] == '/')
                    $this->CloseTag(strtoupper(substr($e, 1)));
                else {
                    //Extract attributes
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach ($a2 as $v) {
                        if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3))
                            $attr[strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag, $attr);
                }
            }
        }
    }

    function OpenTag($tag, $attr)
    {
        //Opening tag
        switch ($tag) {
            case 'STRONG':
                $this->SetStyle('B', true);
                break;
            case 'EM':
                $this->SetStyle('I', true);
                break;
            case 'B':
            case 'I':
            case 'U':
                $this->SetStyle($tag, true);
                break;
            case 'A':
                $this->HREF = $attr['HREF'];
                break;
            case 'IMG':
                if (isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
                    if (!isset($attr['WIDTH']))
                        $attr['WIDTH'] = 0;
                    if (!isset($attr['HEIGHT']))
                        $attr['HEIGHT'] = 0;
                    $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
                }
                break;
            case 'TR':
            case 'BLOCKQUOTE':
            case 'BR':
                $this->Ln(5);
                break;
            case 'P':
                $this->Ln(10);
                break;
            case 'FONT':
                if (isset($attr['COLOR']) && $attr['COLOR'] != '') {
                    $coul = hex2dec($attr['COLOR']);
                    $this->SetTextColor($coul['R'], $coul['V'], $coul['B']);
                    $this->issetcolor = true;
                }
                if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
                    $this->SetFont(strtolower($attr['FACE']));
                    $this->issetfont = true;
                }
                break;
        }
    }

    function CloseTag($tag)
    {
        //Closing tag
        if ($tag == 'STRONG')
            $tag = 'B';
        if ($tag == 'EM')
            $tag = 'I';
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, false);
        if ($tag == 'A')
            $this->HREF = '';
        if ($tag == 'FONT') {
            if ($this->issetcolor == true) {
                $this->SetTextColor(0);
            }
            if ($this->issetfont) {
                $this->SetFont('arial');
                $this->issetfont = false;
            }
        }
    }

    function SetStyle($tag, $enable)
    {
        //Modify style and select corresponding font
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach (array('B', 'I', 'U') as $s) {
            if ($this->$s > 0)
                $style .= $s;
        }
        $this->SetFont('', $style);
    }

    function PutLink($URL, $txt)
    {
        //Put a hyperlink
        $this->SetTextColor(0, 0, 255);
        $this->SetStyle('U', true);
        $this->Write(5, $txt, $URL);
        $this->SetStyle('U', false);
        $this->SetTextColor(0);
    }

    protected $widths;
    protected $aligns;
    protected $fonts2;

    function SetFonts2($f)
    {
        // Set the array of column widths
        $this->fonts2 = $f;
    }

    function SetWidths($w)
    {
        // Set the array of column widths
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        // Set the array of column alignments
        $this->aligns = $a;
    }

    function Row($data)
    {
        // Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 8 * $nb;
        // Issue a page break first if needed
        $this->CheckPageBreak2($h);
        // Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $f = isset($this->fonts2[$i]) ? $this->fonts2[$i] : "";
            // Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            // Draw the border
            $this->Rect($x, $y, $w, $h);
            // Print the text
            $this->SetFont('Arial', "$f");
            $this->MultiCell($w, 5, $data[$i], 0, $a, false);
            // Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        // Go to the next line
        $this->Ln($h);
    }

    function CheckPageBreak2($h)
    {
        // If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w, $txt)
    {
        // Compute the number of lines a MultiCell of width w will take
        if (!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $cw = $this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
} //end of class


$pdf = new PDF_HTML();
$htmlPDF = new PDF_HTML();

//Numéro de page
$pdf->AliasNbPages();

$pdf->AddPage();

$photoImm = false;
if ($immeuble && $immeuble->photoImmeuble != null && $immeuble->photoImmeuble !=  "" && file_exists("../../documents/immeuble/$immeuble->photoImmeuble")) {
    $photoImm = true;
}

if ($photoImm) {
    $pdf->SetY(35);
} else {
    $pdf->SetY(($pdf->GetPageHeight() / 2) - 20);
}

$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(0, 25, iconv('UTF-8', 'windows-1252', "COMPTE RENDU RELEVES TECHNIQUES"), 1, 0, 'C', true, '');

if ($photoImm) {
    $pdf->SetY(65);
} else {
    $pdf->SetY(($pdf->GetPageHeight() / 2) - 5);
}
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(184, 0, 0);
$pdf->SetX(60);
$pdf->Cell(50, 8, iconv('UTF-8', 'windows-1252', "Nos Références : "), 0, 0, 'L', true, '');
$pdf->SetX(105);
$pdf->Cell(0, 8, iconv('UTF-8', 'windows-1252', $o->name), 0, 0, 'L', true, '');
$pdf->Ln();

if ($photoImm) {
    $pdf->Image(URLROOT  . "/public/documents/immeuble/$immeuble->photoImmeuble", 15, 75, 180, 120);
    $pdf->SetY(200);
}

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'BU', 12);
$pdf->Cell(75, 10, iconv('UTF-8', 'windows-1252', 'N° de Contrat :'), 1, 0, 'J', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(115, 10, iconv('UTF-8', 'windows-1252', "$numPolice"), 1, 0, 'J', true);
$pdf->Ln();

$pdf->SetFont('Arial', 'BU', 12);
$pdf->Cell(75, 10, iconv('UTF-8', 'windows-1252', 'N° de Sinistre:'), 1, 0, 'J', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(115, 10, iconv('UTF-8', 'windows-1252', $numSinistre), 1, 0, 'J', true);
$pdf->Ln();

$pdf->SetFont('Arial', 'BU', 12);
$pdf->Cell(75, 10, iconv('UTF-8', 'windows-1252', 'Nature du Sinistre :'), 1, 0, 'J', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(115, 10, iconv('UTF-8', 'windows-1252', $rt->nature), 1, 0, 'J', true);
$pdf->Ln();

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'BU', 12);
$pdf->Cell(75, 10, iconv('UTF-8', 'windows-1252', 'Lieu du sinistre :'), 1, 0, 'J', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(115, 10, iconv('UTF-8', 'windows-1252', "$adresse $cp $ville"), 1, 0, 'J', true);
$pdf->Ln();

$pdf->SetFont('Arial', 'BU', 12);
$pdf->Cell(75, 10, iconv('UTF-8', 'windows-1252', 'Date du sinistre :'), 1, 0, 'J', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(115, 10, iconv('UTF-8', 'windows-1252', $dateSinistre), 1, 0, 'J', true);
$pdf->Ln();


$pdf->SetFont('Arial', 'BU', 12);
$pdf->Cell(75, 10, iconv('UTF-8', 'windows-1252', 'Date du rendez-vous RT :'), 1, 0, 'J', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(115, 10, iconv('UTF-8', 'windows-1252', date("d/m/Y", strtotime($rv->dateRV))), 1, 0, 'J', true);
$pdf->Ln();


$pdf->AddPage();



$pdf->setY(15);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(184, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell($pdf->GetPageWidth() - 20, 5, "INTRODUCTION ", 0, 3, 'L', true);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252', $rt->introduction), 0, 'J');
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(184, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell($pdf->GetPageWidth() - 20, 5, "FEUILLE DE PRESENCE ", 0, 3, 'L', true);
$pdf->Ln();

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'BU', 12);
$pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252',  "Etaient présents au $adresse $cp $ville le " . date("d/m/Y à h:i", strtotime($rv->dateRV))), 0, 'J');
$pdf->Ln(3);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(75, 15, iconv('UTF-8', 'windows-1252', "Nom de la partie"), 1, 0, 'C', true, '');
$pdf->Cell(20, 15, iconv('UTF-8', 'windows-1252', "Présente"), 1, 0, 'C', true, '');
$pdf->Cell(20, 15, iconv('UTF-8', 'windows-1252', "Absente"), 1, 0, 'C', true, '');
$pdf->Cell(75, 15, iconv('UTF-8', 'windows-1252', "Représentée"), 1, 0, 'C', true, '');
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(75, 15, iconv('UTF-8', 'windows-1252', $contact->civiliteContact . " " . $contact->prenomContact . " " . $contact->nomContact), 1, 0, 'J', true, '');
$pdf->Cell(20, 15, iconv('UTF-8', 'windows-1252', "x"), 1, 0, 'C', true, '');
$pdf->Cell(20, 15, iconv('UTF-8', 'windows-1252', ""), 1, 0, 'C', true, '');
$pdf->Cell(75, 15, iconv('UTF-8', 'windows-1252', ""), 1, 0, 'J', true, '');
$pdf->Ln();


$pdf->SetFont('Arial', '', 12);
$pdf->Cell(75, 15, iconv('UTF-8', 'windows-1252', "WBCC Assistance"), 1, 0, 'J', true, '');
$pdf->Cell(20, 15, iconv('UTF-8', 'windows-1252', "x"), 1, 0, 'C', true, '');
$pdf->Cell(20, 15, iconv('UTF-8', 'windows-1252', ""), 1, 0, 'C', true, '');
$pdf->Cell(75, 15, iconv('UTF-8', 'windows-1252', "Expert : " . $rv->expert), 1, 0, 'J', true, '');
$pdf->Ln();

//Coordonnées des parties
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(184, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell($pdf->GetPageWidth() - 20, 5, "COORDONNEES DES PARTIES", 0, 3, 'L', true);
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->SetAligns(array('C', 'C', 'C', 'C'));

$pdf->SetFonts2(array('B', 'B', 'B', 'B'));

$pdf->SetWidths(array(40, 50, 50, 50));

$pdf->Row(array('NOM', ($cie ? $cie->name : ""), "WBCC Assistance", $contact->civiliteContact . " " . $contact->prenomContact . " " . $contact->nomContact));
$pdf->SetFonts2(array('B', '', '', ''));
$pdf->Row(array('TYPE', ($o->typeSinistre == "Partie commune exclusive" ? 'MRI' : ("MRH")), "Gestionnaire de Sinistres", ($estLocataire ? "Locataire" : "Copropriétaire")));
$pdf->Row(array('ADRESSE', ($cie ? $cie->businessLine1 : ""), "218, Rue de Bellevue",  $adresse));
$pdf->Row(array('CODE POSTAL / VILLE', ($cie ? "$cie->businessPostalCode $cie->businessCity" : ""), "92700 Colombes", "$cp $ville"));
$pdf->Row(array('TELEPHONE', ($cie ? $cie->name : ""), "0980084484", $contact->telContact));
$pdf->Row(array('EMAIL', ($cie ?  $cie->email : ""), "gestion@wbcc.fr", $contact->emailContact));
$pdf->Row(array('NUMERO DE CONTRAT', ($numPolice), "", $numPolice));
$pdf->Row(array('NUMERO DE SINISTRE', ($numSinistre), "$o->name", "$numSinistre"));

// $pdf->Cell(40, 15, iconv('UTF-8', 'windows-1252', "NOM"), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', ($cie ? $cie->name : "")), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', "WBCC Assistance"), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', $contact->civiliteContact . " " . $contact->prenomContact . " " . $contact->nomContact), 1, 0, 'C', true, '');
// $pdf->Ln();

// $pdf->SetX(10);
// $pdf->Cell(40, 15, iconv('UTF-8', 'windows-1252', "TYPE"), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', ($o->typeSinistre == "Partie commune exclusive" ? 'MRI' : ("MRH"))), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', "Gestionnaire de Sinistres"), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', ($estLocataire ? "Locataire" : "Copropriétaire")), 1, 0, 'C', true, '');
// $pdf->Ln();

// $pdf->SetX(10);
// $pdf->Cell(40, 15, iconv('UTF-8', 'windows-1252', "ADRESSE"), 1, 0, 'C', true, '');
// $pdf->SetFont('Arial', '', 10);
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', $cie ? $cie->businessLine1 : ""), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', "218, Rue de Bellevue"), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', $adresse), 1, 0, 'C', true, '');
// $pdf->Ln();

// $pdf->SetX(10);
// $pdf->SetFont('Arial', 'B', 10);
// $pdf->Cell(40, 15, iconv('UTF-8', 'windows-1252', "CODE POSTAL / VILLE"), 1, 0, 'C', true, '');
// $pdf->SetFont('Arial', '', 10);
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252',  $cie ? "$cie->businessPostalCode $cie->businessCity" : ""), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', "92700 Colombes"), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', "$cp $ville"), 1, 0, 'C', true, '');
// $pdf->Ln();

// $pdf->SetX(10);
// $pdf->SetFont('Arial', 'B', 10);
// $pdf->Cell(40, 15, iconv('UTF-8', 'windows-1252', "TELEPHONE"), 1, 0, 'C', true, '');
// $pdf->SetFont('Arial', '', 10);
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252',  $cie ? $cie->businessPhone : ""), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', "0980084484"), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', $contact->telContact), 1, 0, 'C', true, '');
// $pdf->Ln();

// $pdf->SetX(10);
// $pdf->SetFont('Arial', 'B', 10);
// $pdf->Cell(40, 15, iconv('UTF-8', 'windows-1252', "EMAIL"), 1, 0, 'C', true, '');
// $pdf->SetFont('Arial', '', 10);
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', $cie ?  $cie->email : ""), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', "gestion@wbcc.fr"), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252',  $contact->emailContact), 1, 0, 'C', true, '');
// $pdf->Ln();

// $pdf->SetX(10);
// $pdf->SetFont('Arial', 'B', 10);
// $pdf->Cell(40, 15, iconv('UTF-8', 'windows-1252', "NUMERO DE CONTRAT"), 1, 0, 'C', true, '');
// $pdf->SetFont('Arial', '', 10);
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252',  $numPolice), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', ""), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', $numPolice), 1, 0, 'C', true, '');
// $pdf->Ln();

// $pdf->SetX(10);
// $pdf->SetFont('Arial', 'B', 10);
// $pdf->Cell(40, 15, iconv('UTF-8', 'windows-1252', "NUMERO DE SINISTRE"), 1, 0, 'C', true, '');
// $pdf->SetFont('Arial', '', 10);
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', "$numSinistre"), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', "$o->name"), 1, 0, 'C', true, '');
// $pdf->Cell(50, 15, iconv('UTF-8', 'windows-1252', $numSinistre), 1, 0, 'C', true, '');
// $pdf->Ln();


$pdf->AddPage();

$pdf->setY(15);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(184, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell($pdf->GetPageWidth() - 20, 5, "LISTE DES PIECES JOINTES ", 0, 3, 'L', true);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252', "Voir annexe pour :"), 0, 'J');
$pdf->Ln(2);

$pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252', "1-	Photos et métrés pris par WBCC Assistance lors du Rendez-vous Relevés Techniques."), 0, 'J');
$pdf->Ln(2);
$pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252', "2-	Délégation de gestion signée par " . $contact->civiliteContact . " " . $contact->prenomContact . " " . $contact->nomContact . "."), 0, 'J');
$pdf->Ln(2);

if ($devis) {
    $pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252', "3-   Devis n° " . $devis->numeroDevisWBCC . " de l’entreprise $devis->artisan (artisan agréé) pour les travaux de réfection d’un montant de " . ($devis->montantHT) . " € HT soit " . $devis->montantTotal . "€ TTC"), 0, 'J');
    $pdf->Ln(2);
}

$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(184, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell($pdf->GetPageWidth() - 20, 5, "CONTEXTE", 0, 3, 'L', true);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252', $rt->contexte), 0, 'J');
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(184, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell($pdf->GetPageWidth() - 20, 5, "DEROULEMENT DE LA SEANCE", 0, 3, 'L', true);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252', $rt->deroulementSeance), 0, 'J');
$pdf->Ln();

// $pdf->SetFont('Arial', 'B', 14);
// $pdf->SetFillColor(184, 0, 0);
// $pdf->SetTextColor(255, 255, 255);
// $pdf->Cell($pdf->GetPageWidth() - 20, 5, "ACTIONS A MENER PAR WBCC ASSISTANCE", 0, 3, 'L', true);
// $pdf->Ln();

// $pdf->SetFont('Arial', '', 12);
// $pdf->SetTextColor(0, 0, 0);
// $pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252', ""), 0, 'J');
// $pdf->Ln();

// $pdf->SetFont('Arial', 'B', 14);
// $pdf->SetFillColor(184, 0, 0);
// $pdf->SetTextColor(255, 255, 255);
// $pdf->Cell($pdf->GetPageWidth() - 20, 5, "DATE A RETENIR", 0, 3, 'L', true);
// $pdf->Ln();

// $pdf->SetFont('Arial', '', 12);
// $pdf->SetTextColor(0, 0, 0);
// $pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252', ""), 0, 'J');
// $pdf->Ln();

$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(184, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell($pdf->GetPageWidth() - 20, 5, "CONCLUSION", 0, 3, 'L', true);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252', "Nous vous tiendrons au courant au fur et à mesure de l’évolution du dossier. Nous vous conseillons de communiquer directement avec WBCC ASSISTANCE pour plus de réactivité, de gain de temps et un meilleur suivi du dossier."), 0, 'J');
$pdf->Ln(10);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252',  "Tél. : 09 800 844 84"), 0, 'C');
$pdf->Ln(5);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252',  "A COLOMBES, le " . date("d/m/Y")), 0, 'J');
$pdf->Ln(10);


$pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252',  "Pour la société WBCC-ASSISTANCE "), 0, 'J');
$pdf->Ln(5);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252', $rt->auteurCompteRenduRT), 0, 'J');

//PHOTOS RT
$i = 1;
$pdf->AddPage();
// $pdf->SetFont('Arial', 'B', 14);
// $pdf->SetFillColor(184, 0, 0);
// $pdf->SetTextColor(255, 255, 255);
// $pdf->Cell($pdf->GetPageWidth() - 20, 5, "ANNEXE", 0, 3, 'L', true);
// $pdf->Ln(5);

$pdf->SetY(130);
$pdf->SetX(2);
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 5,  iconv('UTF-8', 'windows-1252',  "ANNEXE 1 : Détail Relevés Techniques"), 0, 'C');
$pdf->Ln();

//PIECES
if (sizeof($tabPieces) != 0) {
    foreach ($tabPieces as $key => $piece) {

        $pdf->AddPage();

        $pdf->SetY(130);
        $pdf->SetX(2);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Times', 'B', 20);
        $pdf->Cell(0, 0, iconv('UTF-8', 'windows-1252', strtoupper("PIECES N°" . $key + 1 . " : " . $piece->libellePiece)), 0, 0, 'C', false, '');

        $pdf->AddPage();
        $pdf->SetX(30);
        $pdf->SetY(30);
        $pdf->SetFont('Times', 'B', 14);
        $pdf->SetFillColor(169, 169, 169);
        $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', "Nom de la Pièce N°" . ($key + 1) . " à inspecter : " . $piece->libellePiece), 0, 0, 'L', true);
        $pdf->Image('../../images/quadrillage.jpg', 10, 40, 190, 35);

        //Dimensions 
        $pdf->SetX(30);
        $pdf->SetY(80);
        $pdf->SetFont('Times', 'B', 14);
        $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', "- Dimensions de la pièce"), 0, 0, 'L');
        $pdf->Ln(10);
        $pdf->SetFont('Times', '', 12);
        $pdf->SetFillColor(240, 128, 128);
        $pdf->Cell(47, 10,  iconv('UTF-8', 'windows-1252', " Longueur (m)"), 1, 0, 'L', true);
        $pdf->Cell(47, 10, iconv('UTF-8', 'windows-1252', "Largeur (m)"), 1, 0, 'L', true);
        $pdf->Cell(47, 10,  iconv('UTF-8', 'windows-1252', "Périmétre (m)"), 1, 0, 'L', true);
        $pdf->Cell(49, 10,  iconv('UTF-8', 'windows-1252', "Surface Totale (m2)"), 1, 0, 'L', true);
        $pdf->Ln(10);
        $pdf->Cell(47, 10,  iconv('UTF-8', 'windows-1252', $piece->longueurPiece), 1, 0, 'C');
        $pdf->Cell(47, 10, iconv('UTF-8', 'windows-1252', $piece->largeurPiece), 1, 0, 'C');
        $pdf->Cell(47, 10,  iconv('UTF-8', 'windows-1252', ($piece->longueurPiece != "" && $piece->longueurPiece != null && $piece->largeurPiece != null && $piece->largeurPiece != "" ? round($piece->longueurPiece * $piece->largeurPiece, 2) : "0")), 1, 0, 'C');
        $pdf->Cell(49, 10,  iconv('UTF-8', 'windows-1252', ($piece->longueurPiece != "" && $piece->longueurPiece != null && $piece->largeurPiece != null && $piece->largeurPiece != "" ? round($piece->longueurPiece * $piece->largeurPiece, 2) : "0")), 1, 0, 'C');

        $pdf->Ln(15);
        $pdf->SetFont('Times', '', 12);
        $pdf->SetFillColor(240, 128, 128);
        $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', " Commentaire sur les dimensions "), 1, 0, 'L', true);
        $pdf->Ln(10);
        $pdf->SetFillColor(255, 255, 255);
        // $pdf->WriteHTML(190, 10, 150, 10, iconv('UTF-8', 'windows-1252',  $piece->commentaireMetrePiece), 1, 'B', 'L');
        $pdf->WriteHTML1("<b>bbbbbbb</b>");
        // $pdf->WriteHTML1($piece->commentaireMetrePiece);
        // $pdf->MultiCell(190, 10, iconv('UTF-8', 'windows-1252',  $piece->commentaireMetrePiece), 1, 'B', 'L');

        $libSupport = "";
        $nbMursSinistres = 0;
        $nbMursNonSinistres = 0;
        $libSansMur = "";
        $surfaceMursSinistres = 0;
        $surfaceMursNonSinistres = 0;
        foreach ($piece->listSupports as $key => $support) {
            if (str_contains($support->nomSupport, "MUR")) {
                $sTotal =  ($support->longueurSupport != "" &&  $support->longueurSupport != null && $support->largeurSupport != "" &&  $support->largeurSupport != null ? round(($support->longueurSupport * $support->largeurSupport), 2) : 0);
                //Calcul OUV
                $sOuverture = 0;
                foreach ($support->listOuvertures as $key => $ouverture) {
                    $sOuverture +=  ($ouverture->longueurOuverture != "" &&  $ouverture->longueurOuverture != null && $ouverture->largeurOuverture != "" &&  $ouverture->largeurOuverture != null ? round(($ouverture->longueurOuverture * $ouverture->largeurOuverture), 2) : 0);
                }
                if ($support->estSinistre == "1") {
                    $nbMursSinistres++;
                    if ($support->siDeduire == "1") {
                        $surfaceMursSinistres +=  ($sTotal - $sOuverture);
                    } else {
                        $surfaceMursSinistres +=  ($sTotal);
                    }
                } else {
                    $nbMursNonSinistres++;
                    if ($support->siDeduire == "1") {
                        $surfaceMursNonSinistres +=  ($sTotal - $sOuverture);
                    } else {
                        $surfaceMursNonSinistres +=  ($sTotal);
                    }
                }
            } else {
                $libSupport .= (($libSupport == "") ? $support->nomSupport : (", " . $support->nomSupport));
            }
        }

        if ($nbMursNonSinistres != 0 || $nbMursSinistres != 0) {
            $libSupport = $libSupport == "" ? "" : $libSupport . ", ";
            $libSupport .= ($nbMursSinistres == 1 ? "$nbMursSinistres Mur Sinistré" : "$nbMursSinistres Murs Sinistrés") . " et " .  ($nbMursNonSinistres == 1 ? "$nbMursNonSinistres Mur Non Sinistré" : "$nbMursNonSinistres Murs Non Sinistrés");
        }

        $pdf->Ln(5);
        $pdf->SetFont('Times', 'B', 14);
        $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', "- Liste des supports endommagés"), 0, 0, 'L');
        $pdf->Ln(5);
        $pdf->SetFont('Times', '', 12);
        $pdf->Cell(190, 20, iconv('UTF-8', 'windows-1252', $libSupport), 'B', 0, 'L');

        if ($nbMursNonSinistres != 0 || $nbMursSinistres != 0) {

            $pdf->Ln(20);
            $pdf->SetFont('Times', 'B', 14);
            $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', "- Surface des Murs"), 0, 0, 'L');
            $pdf->Ln(10);
            $pdf->SetFont('Times', '', 12);
            $pdf->SetFillColor(240, 128, 128);
            $pdf->Cell(95, 10,  iconv('UTF-8', 'windows-1252', " Surface Murs Sinistrés (m²)"), 1, 0, 'L', true);
            $pdf->Cell(95, 10, iconv('UTF-8', 'windows-1252', " Surface Murs Non Sinistrés (m²)"), 1, 0, 'L', true);
            $pdf->Ln(10);
            $pdf->Cell(95, 10,  iconv('UTF-8', 'windows-1252', $surfaceMursSinistres), 1, 0, 'C');
            $pdf->Cell(95, 10, iconv('UTF-8', 'windows-1252', $surfaceMursNonSinistres), 1, 0, 'C');
        }

        $pdf->Ln(20);
        $pdf->SetFont('Times', '', 12);
        $pdf->SetFillColor(240, 128, 128);
        $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', " Commentaire sur les supports"), 1, 0, 'L', true);
        $pdf->Ln(10);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell(190, 10, iconv('UTF-8', 'windows-1252',  $piece->commentaireSupport), 1, 'B', 'L');

        $pdf->Ln(10);
        $pdf->SetFont('Times', '', 12);
        $pdf->SetFillColor(240, 128, 128);
        $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', " Commentaire sur "  .  $piece->nomPiece), 1, 0, 'L', true);
        $pdf->Ln(10);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell(190, 10, iconv('UTF-8', 'windows-1252',  $piece->commentairePiece), 1, 'B', 'L');

        $pdf->AddPage();
        //PHOTO PIECE
        if ($piece->photosPiece != "" && $piece->photosPiece != null) {
            $photos = $piece->photosPiece == "" ? [] : explode(";", $piece->photosPiece);
            $comments = $piece->commentsPhotosPiece == "" ? [] : explode("}", $piece->commentsPhotosPiece);
            $j = 0;
            if (sizeof($photos) != 0) {
                for ($j = 0; $j < count($photos);) {
                    $photoPiece = $photos[$j];
                    if ($j % 2 == 0) {
                        $pdf->SetY(27);
                        $pdf->SetTextColor(0, 0, 0);
                        $pdf->SetFont('Times', 'B', 14);
                        $pdf->SetFillColor(169, 169, 169);
                        $pdf->Cell(190, 15,  iconv('UTF-8', 'windows-1252', "Photos : " . $piece->nomPiece), 0, 0, 'L', true);
                    }
                    if ($photoPiece != "" && file_exists($photoPiece)) {
                        $pdf->Rect(9.5, 44.5, 191, 101, "solid");
                        $pdf->Image(URLROOT .  "/public/documents/opportunite/$photoPiece", 10, 45, 190, 100);
                    }
                    $photoPiece = isset($photos[$j + 1]) ? $photos[$j + 1] : "";
                    if ($photoPiece != "" && file_exists($photoPiece)) {

                        $pdf->Rect(9.5, 152, 191, 101, "solid");
                        $pdf->Image(URLROOT .  "/public/documents/opportunite/$photoPiece", 10, 151, 190, 100);
                    }
                    $j += 2;
                    if ($j < sizeof($photos) - 1) {
                        $pdf->AddPage();
                        $pdf->SetY(25 - $pdf->GetPageHeight());
                    }
                }
            }
        } else {
            $pdf->SetY(27);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Times', '', 14);
            $pdf->SetFillColor(169, 169, 169);
            $pdf->Cell(190, 15,  iconv('UTF-8', 'windows-1252',  "Photos : " . $piece->nomPiece), 0, 0, 'L', true);
            $pdf->Ln(70);
            $pdf->SetFont('Times', '', 12);
            $pdf->Cell(190, 20, iconv('UTF-8', 'windows-1252', "Aucune photo n'a été prise pour cette Piece !"), 0, 0, 'C');
        }

        //TRAITEMENT DES SUPPORTS
        foreach ($piece->listSupports as $key => $support) {
            $pdf->AddPage();
            //METRE
            $sTotal =  ($support->longueurSupport != "" &&  $support->longueurSupport != null && $support->largeurSupport != "" &&  $support->largeurSupport != null ? round(($support->longueurSupport * $support->largeurSupport), 2) : 0);
            //Calcul OUV
            $sOuverture = 0;
            $libOuv = "";
            foreach ($support->listOuvertures as $key => $ouverture) {
                $sOuverture +=  ($ouverture->longueurOuverture != "" &&  $ouverture->longueurOuverture != null && $ouverture->largeurOuverture != "" &&  $ouverture->largeurOuverture != null ? round(($ouverture->longueurOuverture * $ouverture->largeurOuverture), 2) : 0);
                $libOuv .= $libOuv == "" ? $ouverture->libelleOuverture : ", " . $ouverture->libelleOuverture;
            }
            $pdf->Ln(20);
            $pdf->SetFont('Times', 'B', 14);
            $pdf->SetFillColor(169, 169, 169);
            $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', " Traitement de " .  $piece->nomPiece . " / " .  $support->nomSupport  . (($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? " / Sinistré" : ""))), 0, 0, 'L', true);

            $pdf->Ln(10);
            $pdf->SetFont('Times', 'B', 14);
            $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', "-  Métrés du support"), 0, 0, 'L');
            $pdf->Ln(10);
            $pdf->SetFont('Times', '', 12);
            $pdf->SetFillColor(240, 128, 128);
            $pdf->Cell(32, 10,  iconv('UTF-8', 'windows-1252', " Longueur (m)"), 1, 0, 'C', true);
            $pdf->Cell(32, 10, iconv('UTF-8', 'windows-1252', ($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? "Largeur" : "Hauteur") . " (m)"), 1, 0, 'C', true);
            $pdf->Cell(32, 10,  iconv('UTF-8', 'windows-1252', "S. Totale \n (m2)"), 1, 0, 'C', true);
            $pdf->Cell(32, 10,  iconv('UTF-8', 'windows-1252', "S. Totale Ouv (m2)"), 1, 0, 'C', true);
            $pdf->Cell(30, 10,  iconv('UTF-8', 'windows-1252', "A Déduire"), 1, 0, 'C', true);
            $pdf->Cell(32, 10,  iconv('UTF-8', 'windows-1252', "S. à traiter (m2)"), 1, 0, 'C', true);
            $pdf->Ln(10);
            $pdf->Cell(32, 10,  iconv('UTF-8', 'windows-1252',  $support->longueurSupport), 1, 0, 'C');
            $pdf->Cell(32, 10, iconv('UTF-8', 'windows-1252',  $support->largeurSupport), 1, 0, 'C');
            $pdf->Cell(32, 10,  iconv('UTF-8', 'windows-1252', $sTotal), 1, 0, 'C');
            $pdf->Cell(32, 10,  iconv('UTF-8', 'windows-1252',  $sOuverture), 1, 0, 'C');
            $pdf->Cell(30, 10,  iconv('UTF-8', 'windows-1252',  $support->siDeduire == "1" ? "OUI" : "NON"), 1, 0, 'C');
            $pdf->Cell(32, 10,  iconv('UTF-8', 'windows-1252', ($support->siDeduire == "1" ? ($sTotal - $sOuverture) : $sTotal)), 1, 0, 'C');

            //Taux d'Humidité
            $pdf->Ln(20);
            $pdf->SetFillColor(240, 128, 128);
            $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', "Taux d'Humidité"), 1, 0, 'L', true);
            $pdf->Ln(10);
            $pdf->MultiCell(190, 10,  iconv('UTF-8', 'windows-1252', ($support->tauxHumidite != null && $support->tauxHumidite != "" ? $support->tauxHumidite . '%' : "Non renseigné")), 1, 'C');

            //COMMENTAIRE METRE SUPPORT
            $pdf->Ln(10);
            $pdf->SetFillColor(240, 128, 128);
            $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', " Commentaire sur les métrés du support"), 1, 0, 'L', true);
            $pdf->Ln(10);
            $pdf->MultiCell(190, 10,  iconv('UTF-8', 'windows-1252',  $support->commentaireMetreSupport), 1, 'C');

            //RESUME REVETEMENT
            $libRev = "";
            foreach ($support->listRevetements as $key => $rev) {
                $libRev .= $libRev == "" ? $rev->libelleRevetement : ", " . $rev->libelleRevetement;
            }
            $pdf->Ln(10);
            $pdf->SetFont('Times', 'B', 14);
            $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', "-  Revêtements"), 0, 0, 'L');
            $pdf->SetFont('Times', '', 12);
            $pdf->Ln(10);
            $pdf->MultiCell(190, 10, iconv('UTF-8', 'windows-1252', $libRev), 'B', 'L');

            //RESUME OUVERTURE
            $pdf->Ln(10);
            $pdf->SetFont('Times', 'B', 14);
            $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', "-  Ouvertures"), 0, 0, 'L');
            $pdf->SetFont('Times', '', 12);
            $pdf->Ln(10);
            $pdf->MultiCell(190, 10, iconv('UTF-8', 'windows-1252', ($libOuv == "" ? "Pas d'ouvertures sur ce support" : $libOuv)), 'B', 'L');

            //COMMENTAIRE SUPPORT
            $pdf->Ln(10);
            $pdf->SetFillColor(240, 128, 128);
            $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', " Commentaire du support"), 1, 0, 'L', true);
            $pdf->Ln(10);
            $pdf->MultiCell(190, 10,  iconv('UTF-8', 'windows-1252',  $support->commentaireSupport), 1, 'C');

            // $pdf->Ln(10);
            // $pdf->SetFillColor(240, 128, 128);
            // $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', " Commentaire revêtement"), 1, 0, 'L', true);
            // $pdf->Ln(10);
            // $pdf->MultiCell(190, 30,  iconv('UTF-8', 'windows-1252',  $support->commentaireSupport), 1, 'C');

            //DETAIL REVETEMENT
            if (sizeof($support->listRevetements) != 0) {
                $pdf->AddPage();
                $pdf->Ln(20);
                $pdf->SetFont('Times', 'B', 14);
                $pdf->SetFillColor(169, 169, 169);
                $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', "Détail des revêtements : " .  $piece->nomPiece . " / " .  $support->nomSupport  . (($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? " / Sinistré" : ""))), 0, 0, 'L', true);
                foreach ($support->listRevetements as $key => $rev) {
                    $totalRev =  ($rev->longueurRevetement != "" &&  $rev->longueurRevetement != null &&  $rev->longueurRevetement != "NULL" && $rev->largeurRevetement != "" &&  $rev->largeurRevetement != null && $rev->largeurRevetement != "NULL" ? round(($rev->longueurRevetement * $rev->largeurRevetement), 2) : 0);
                    $pdf->Ln(10);
                    $pdf->SetFont('Times', 'B', 14);
                    $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', "-  $rev->libelleRevetement"), 0, 0, 'L');
                    $pdf->Ln(10);
                    $pdf->SetFont('Times', '', 12);
                    $pdf->SetFillColor(240, 128, 128);
                    $pdf->Cell(63, 10,  iconv('UTF-8', 'windows-1252', " Longueur (m)"), 1, 0, 'C', true);
                    $pdf->Cell(63, 10, iconv('UTF-8', 'windows-1252', ($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? "Largeur" : "Hauteur") . " (m)"), 1, 0, 'C', true);
                    $pdf->Cell(64, 10,  iconv('UTF-8', 'windows-1252', "S. Totale \n (m2)"), 1, 0, 'C', true);
                    $pdf->Ln(10);
                    $pdf->Cell(63, 10,  iconv('UTF-8', 'windows-1252',  $rev->longueurRevetement), 1, 0, 'C');
                    $pdf->Cell(63, 10, iconv('UTF-8', 'windows-1252',  $rev->largeurRevetement), 1, 0, 'C');
                    $pdf->Cell(64, 10,  iconv('UTF-8', 'windows-1252', $totalRev), 1, 0, 'C');
                    $pdf->Ln(10);
                    $pdf->MultiCell(190, 10,  iconv('UTF-8', 'windows-1252',  $rev->commentaireRevetement), 1, 'C');
                }
            }

            //DETAIL OUVERTURE
            if (sizeof($support->listOuvertures) != 0) {
                $pdf->AddPage();
                $pdf->Ln(20);
                $pdf->SetFont('Times', 'B', 14);
                $pdf->SetFillColor(169, 169, 169);
                $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', "Détail des ouvertures : " .  $piece->nomPiece . " / " .  $support->nomSupport  . (($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? " / Sinistré" : ""))), 0, 0, 'L', true);
                foreach ($support->listOuvertures as $key => $ouverture) {
                    $totalOuv =  ($ouverture->longueurOuverture != "" &&  $ouverture->longueurOuverture != null && $ouverture->largeurOuverture != "" &&  $ouverture->largeurOuverture != null ? round(($ouverture->longueurOuverture * $ouverture->largeurOuverture), 2) : 0);
                    $pdf->Ln(10);
                    $pdf->SetFont('Times', 'B', 14);
                    $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', "-  $ouverture->libelleOuverture"), 0, 0, 'L');
                    $pdf->Ln(10);
                    $pdf->SetFont('Times', '', 12);
                    $pdf->SetFillColor(240, 128, 128);
                    $pdf->Cell(63, 10,  iconv('UTF-8', 'windows-1252', " Largeur (m)"), 1, 0, 'C', true);
                    $pdf->Cell(63, 10, iconv('UTF-8', 'windows-1252', "Hauteur (m)"), 1, 0, 'C', true);
                    $pdf->Cell(64, 10,  iconv('UTF-8', 'windows-1252', "S. Totale \n (m2)"), 1, 0, 'C', true);
                    $pdf->Ln(10);
                    $pdf->Cell(63, 10,  iconv('UTF-8', 'windows-1252',  $ouverture->longueurOuverture), 1, 0, 'C');
                    $pdf->Cell(63, 10, iconv('UTF-8', 'windows-1252',  $ouverture->largeurOuverture), 1, 0, 'C');
                    $pdf->Cell(64, 10,  iconv('UTF-8', 'windows-1252', $totalOuv), 1, 0, 'C');
                    $pdf->Ln(10);
                    $pdf->MultiCell(190, 10,  iconv('UTF-8', 'windows-1252',  $ouverture->commentaireOuverture), 1, 'C');
                }
            }

            //PHOTOS SUPPORT
            if ($support->photosSupport != "" && $support->photosSupport != null) {
                $photos = $support->photosSupport == "" ? [] : explode(";", $support->photosSupport);
                $comments = $support->commentsPhotosSupport == "" ? [] : explode("}", $support->commentsPhotosSupport);
                if (sizeof($photos) != 0) {
                    $pdf->AddPage();
                    for ($j = 0; $j < count($photos);) {
                        $photoPiece = $photos[$j];
                        if ($j % 2 == 0) {
                            $pdf->SetY(27);
                            $pdf->SetTextColor(0, 0, 0);
                            $pdf->SetFont('Times', 'B', 14);
                            $pdf->SetFillColor(169, 169, 169);
                            $pdf->Cell(190, 15,  iconv('UTF-8', 'windows-1252', "Photos : " . $piece->nomPiece . " / " .  $support->nomSupport  . (($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? " / Sinistré" : ""))), 0, 0, 'L', true);
                        }
                        if ($photoPiece != "" && file_exists($photoPiece)) {
                            $pdf->Rect(9.5, 44.5, 191, 101, "solid");
                            $pdf->Image(URLROOT .  "/public/documents/opportunite/$photoPiece", 10, 45, 190, 100);
                        }
                        $photoPiece = isset($photos[$j + 1]) ? $photos[$j + 1] : "";
                        if ($photoPiece != "" && file_exists($photoPiece)) {

                            $pdf->Rect(9.5, 152, 191, 101, "solid");
                            $pdf->Image(URLROOT .  "/public/documents/opportunite/$photoPiece", 10, 151, 190, 100);
                        }
                        $j += 2;
                        if ($j < sizeof($photos) - 1) {
                            $pdf->AddPage();
                            $pdf->SetY(25 - $pdf->GetPageHeight());
                        }
                    }
                }
            } else {
                $pdf->AddPage();
                $pdf->SetY(27);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('Times', '', 14);
                $pdf->SetFillColor(169, 169, 169);
                $pdf->Cell(190, 15,  iconv('UTF-8', 'windows-1252', "Photos : " . $piece->nomPiece . " / " .  $support->nomSupport  . (($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? " / Sinistré" : ""))), 0, 0, 'L', true);
                $pdf->Ln(70);
                $pdf->SetFont('Times', '', 12);
                $pdf->Cell(190, 20, iconv('UTF-8', 'windows-1252', "Aucune photo n'a été prise pour ce support !"), 0, 0, 'C');
            }
        }
    }
    //COMMENTAIRE CIRCONSTANCES
    $pdf->AddPage();
    $pdf->SetX(30);
    $pdf->SetY(30);
    $pdf->SetFont('Times', 'B', 14);
    $pdf->SetFillColor(169, 169, 169);
    $pdf->Cell(190, 10,  iconv('UTF-8', 'windows-1252', "Commentaire et Circonstances du Sinistre"), 1, 0, 'C', true);
    $pdf->Ln(10);
    $pdf->SetFont('Times', '', 12);
    $pdf->MultiCell(190, 10, iconv('UTF-8', 'windows-1252', ($rt ? $rt->precisionComplementaire : "")), 1, 'J',);
}



//SAVE COMPTE RENDU
$nom =  $o->name . "_CompteRenduRT" . ".pdf";
$pdf->Output($nom, 'F');
$nom = str_replace('"', "", $nom);
$file = "$nom";
$file = str_replace('"', "", $file);

//ANNEXE DELEGATION
$rapportDelegation = $o->rapportDelegation;
if ($rapportDelegation != "") {
    $i++;
    $url = URLROOT . "/public/json/concatTwoDocuments.php";
    $query_array = array(
        'idOp' =>  $o->idOpportunity,
        'nomDocument1' => $nom,
        'nomDocument2' => $rapportDelegation,
        'titre' => "Délégation de gestion et de paiement",
        'index' => "$i",
        "numOP" => $o->name,
        "numPolice" => $numPolice,
        "numSinistre" => $numSinistre,
        'format' => 'json'
    );
    $query = http_build_query($query_array);
    $file = file_get_contents($url . '?' . $query);
    $file = str_replace('"', "", $file);
}

//ANNEXE DEVIS

if ($devis) {
    $i++;
    $url = URLROOT . "/public/json/concatTwoDocuments.php";
    $query_array = array(
        'idOp' =>  $o->idOpportunity,
        'nomDocument1' => $nom,
        'nomDocument2' => $devis->devisFile,
        'titre' => "Devis",
        'index' => "$i",
        "numOP" => $o->name,
        "numPolice" => $numPolice,
        "numSinistre" => $numSinistre,
        'format' => 'json'
    );
    $query = http_build_query($query_array);
    $file = file_get_contents($url . '?' . $query);
    $file = str_replace('"', "", $file);
}

//HEADER - FOOTER - filigrane

$url = URLROOT . "/public/json/concatTwoDocuments.php";
$query_array = array(
    'idOp' =>  $o->idOpportunity,
    'nomDocument1' => $nom,
    'nomDocument2' => "x",
    'titre' => "fin",
    'index' => "",
    "numOP" => $o->name,
    "numPolice" => $numPolice,
    "numSinistre" => $numSinistre,
    'format' => 'json'
);
$query = http_build_query($query_array);
$file = file_get_contents($url . '?' . $query);
$file = str_replace('"', "", $file);

echo json_encode($file);
// $pdf->Output($file, 'I');

// echo json_encode($file);