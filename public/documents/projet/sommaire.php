<?php
require_once '../../libs/vendor2/autoload.php';
require_once "../../../app/models/Section.php";
require_once "../../../app/models/Lot.php";
require_once "concat.php";

use \Mpdf\Mpdf;

class Sommaire extends Mpdf
{
    public $concat;
    public $fileName;
    public $sectionModel;
    public $lotModel;
    public $sommaire = array();
    public $numeroter = false;
    public $numeroterFooter = false;
    public $_numPageNum = 1;
    public $links = array();
    public $immeuble;
    public $typeRapport;


    public function __construct($config, $fileName, $immeuble, $typeRapport)
    {
        parent::__construct($config);
        $this->sectionModel = new Section();
        $this->lotModel = new Lot();
        $this->concat = new Concat($fileName . '.pdf');
        $this->fileName = $fileName . '.pdf';
        $this->allow_charset_conversion = true;
        $this->charset_in = 'UTF-8';
        $this->immeuble = $immeuble;
        $this->typeRapport = $typeRapport;
    }

    public function AddPage($orientation = '', $condition = '', $resetpagenum = '', $pagenumstyle = '', $suppress = '', $mgl = '', $mgr = '', $mgt = '', $mgb = '', $mgh = '', $mgf = '', $ohname = '', $ehname = '', $ofname = '', $efname = '', $ohvalue = 0, $ehvalue = 0, $ofvalue = 0, $efvalue = 0, $pagesel = '', $newformat = '')
    {
        parent::AddPage($orientation, $condition, $resetpagenum, $pagenumstyle, $suppress, $mgl, $mgr, $mgt, $mgb, $mgh, $mgf, $ohname, $ehname, $ofname, $efname, $ohvalue, $ehvalue, $ofvalue, $efvalue, $pagesel, $newformat);
        if ($this->numeroter)
            $this->_numPageNum++;
    }

    function startPageNums()
    {
        $this->numeroter = true;
        $this->numeroterFooter = true;
    }

    function stopPageNums()
    {
        $this->numeroter = false;
        $this->numeroterFooter = false;
    }

    function numPageNo()
    {
        return $this->_numPageNum;
    }

    function EntrerDonnee($id, $txt, $level = 0, $y = 0)
    {
        $this->sommaire[] = array('id' => $id, 't' => $txt, 'l' => $level, 'p' => $this->numPageNo());
    }

    function returnSommaire()
    {
        return $this->sommaire;
    }

    function insertSommaire($documents, $nb_page = 0, $sommaire = [], $label = 'Sommaire', $location = 2, $labelSize = 20, $entrySize = 10, $tocfont = 'Arial')
    {
        if (sizeof($sommaire) == 0) {
            $sommaire = $this->sommaire;
        }

        $this->stopPageNums();
        $this->AddPage();
        $start = $this->page;
        $this->Bookmark(htmlspecialchars($label), 1);
        $this->SetFont($tocfont, 'B', $labelSize);
        $this->WriteCell(0, 5, 'Sommaire', 0, 1, 'C');
        $this->Ln(10);

        foreach ($sommaire as $t) {
            //Offset
            $level = $t['l'];
            $weight = '';
            if ($level == 0)
                $weight = 'B';
            $str = $t['t'];
            $id = $t['id'];
            $links = str_repeat('.', $this->w / 1.5 + 8);
            $this->links[$id]['url'] = '#' . $id;
            $this->WriteHTML("<a href='#$id' style='width: 1000px; color: white;'> $links </a>");
            $this->SetFont($tocfont, $weight, $entrySize);
            $strsize = $this->GetStringWidth($str);
            if ($strsize + 20 > $this->w - $this->lMargin - $this->rMargin) {
                $str = substr($str, 0, - (strlen($str) - 50)) . '...';
                $strsize = $this->GetStringWidth($str) - 2;
            }
            $this->SetY($this->y - 5);
            if ($level > 0)
                $this->WriteCell($level * 8);
            $this->WriteCell($strsize + 2, $this->FontSize + 2, $str, 0, 0, 'L', false);

            // Remplir avec des points
            $this->SetFont($tocfont, '', $entrySize);
            $PageCellSize = $this->GetStringWidth($t['p']) + 2;
            $w = $this->w - $this->lMargin - $this->rMargin - $PageCellSize - ($level * 8) - ($strsize + 2);
            $nb = $w / $this->GetStringWidth('.');
            if ($nb < 1) {
                $this->Ln(1);
                $nb = - ($nb);
            }
            $dots = str_repeat('.', (int)$nb);
            $this->WriteCell($w, $this->FontSize + 2, $dots, 0, 0, 'R', false);

            // Numero de Page
            $this->WriteCell($PageCellSize, $this->FontSize + 2, utf8_encode($t['p'] + $nb_page), 0, 1, 'R', false);
        }

        $n = $this->page;
        $n_sommaire = $n - $start + 1;
        return $n_sommaire;
    }

    function chargerDocument($documents, $section, $fontSizeTitle = 14, $fontSizeContent = 8)
    {
        $images = [];
        $docs = [];
        if (sizeof($documents) != 0) {
            // $this->AddPage();
            $this->SetFont('Arial', 'B', $fontSizeTitle);
            $this->WriteHTML("<b>-Annexes $section->numeroSection ($section->titreSection)</b>");
            $this->Ln(10);
            $this->SetFont('Arial', '', $fontSizeContent);
            foreach ($documents as $key =>  $document) {
                $extension = pathinfo($document->urlDocument, PATHINFO_EXTENSION);
                if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'pdf'])) {
                    $docs[] = $document;
                } else if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'svg'], true)) {
                    $images[] = $document;
                }
            }
        }

        $n = 0;

        if (!empty($images)) {
            $n = count($images);
        }
        $i = 1;
        $j = $n % 4;
        $k = intval($n / 4) * 4;
        foreach ($images as $image) {
            (($i - 1) % 4 == 0 && $i != 1) ? $this->AddPage() : '';
            $ty = $this->y;
            if ($i > $k) {
                if ($n % 4 == 1) {
                    $w = $this->w - 30;
                    $h = $w / 2 + 70;
                    $x = 16;
                    $y = $this->y + 20;
                }
                if ($n % 4 == 2) {
                    $w = $this->w / 2 - 20;
                    $h = $w;
                    $x = 50;
                    if ($i % 2 == 0) {
                        $this->Ln(10);
                        $y = $this->y;
                    }
                }
                if ($n % 4 == 3) {
                    $x = ($i % 2 == 0) ? $this->w / 2 + 10 : 10;
                    $w = 80;
                    $h = 80;
                }
            } else {
                $w = 80;
                $h = 80;
                $x = ($i % 2 == 0) ? $this->w / 2 + 10 : 10;
            }
            $y = ($i % 2 == 0) ? $y : $this->y;
            $this->Image(URLROOT . "/public/documents/projet/annexe/" . $image->urlDocument, $x, $y, $w, $h);

            $this->Ln(10);
            $this->x = ($i % 2 == 0 && $i <= $k) ? $this->w / 2 + 10 : 10;
            $y = $ty;
            $i++;
            ($i != 1 && $i % 2 != 0) ? $this->Ln(25) : '';
        }

        if (count($docs) != 0) {
            $this->AddPage();
            $this->SetFont('Arial', 'B', $fontSizeTitle);
            $this->WriteHTML("Annexes $section->numeroSection ($section->titreSection)");
            $this->Ln(7);
            $this->SetFont('Arial', '', $fontSizeContent);
            $i = 1;
            foreach ($docs as $document) {
                $this->WriteHTML($i++ . ".    " . "<a href='" . (URLROOT . "/public/documents/$document->urlDossier/" . $document->urlDocument) . "' target=_blank >$document->nomDocument</a>");
            }
        }
    }

    // Fonction pour changer les lots
    function chargerArticles($articles, $section, $titre)
    {
        // $this->AddPage();
        if (sizeof($articles) != 0) {
            $this->SetFont('Arial', 'B', 14);
            // $this->WriteHTML("<p style='text-align: left; font-weight: bold; '>- <u>$titre </u> </p>");
            $i = 1;

            $html = '<table  width="100%" cellPadding="10" style="border-collapse: collapse; border-color: #000000; border-style: solid;" border="1">
                        <thead>
                        <tr bgcolor="#36b92a" nowrap="nowrap">
                            <th width="5%">#</th>
                            <th width="25%">LIBELLE</th>
                            <th  width="10%" nowrap="nowrap">UNITE</th>
                            <th width="12%" nowrap="nowrap">QUANTITE</th>
                            <th  width="10%" nowrap="nowrap">PRIX</th>
                            <th  width="15%">MONTANT</th>
                            <th width="9%" nowrap="nowrap">TAUX REMISE</th>
                            <th width="15%" nowrap="nowrap">MONTANT AR</th>
                            <th  width="10%" nowrap="nowrap">TVA</th>
                            <th width="15%" nowrap="nowrap">MONTANT TTC</th>
                            </tr>
                        </thead>
                        <tbody>';

            $total = 0;
            $totalRemise = 0;
            $totalHT = 0;
            foreach ($articles as $article) {
                $mRemise = (float)$article->montant - ((float)$article->montant * ($article->tauxRemise == '' ? 0 : (float)$article->tauxRemise) / 100);
                $ttc = $mRemise + ($mRemise * ($article->tva == '' ? 0 : (float)$article->tva) / 100);
                $total += $ttc;
                $totalRemise += $mRemise;
                $totalHT += (float)$article->montant;
                $html .= "<tr>
                                <td>$i</td>
                                <td>$article->libelle </td>
                                <td nowrap='nowrap' style='text-align:center'>$article->unite</td>
                                <td nowrap='nowrap' style='text-align:center'>$article->quantite </td>
                                <td nowrap='nowrap' style='text-align:right'>" . number_format(($article->prix != null && $article->prix != "" ? (float)$article->prix : 0), 2, ',', ' ') . " € </td>
                                <td nowrap='nowrap' style='text-align:right'>" . number_format(($article->montant != null && $article->montant != "" ? (float)$article->montant : 0), 2, ',', ' ') . " €</td>
                                <td nowrap='nowrap' style='text-align:center'>$article->tauxRemise</td>
                                <td nowrap='nowrap' style='text-align:right'>" . number_format($mRemise, 2, ',', ' ') . " €</td>
                                <td nowrap='nowrap' style='text-align:center'>$article->tva</td>
                                <td nowrap='nowrap' style='text-align:right'>" . number_format($ttc, 2, ',', ' ') . " €</td>
                            </tr>";
                $i++;
            }
            $html .= "<tr><td>$i</td>
            <th colspan='4'>TOTAL</th>
            <th nowrap='nowrap' style='text-align:right'>" . number_format($totalHT, 2, ',', ' ') . " €</th>
            <th nowrap='nowrap'></th>
            <th nowrap='nowrap' style='text-align:right'>" . number_format($totalRemise, 2, ',', ' ') . " €</th>
            <th nowrap='nowrap'></th>
            <th nowrap='nowrap' style='text-align:right'>" . number_format($total, 2, ',', ' ') . " €</th>
        </tr>";
            $html .= "</tbody></table>";
            $this->WriteHTML($html);
        }
    }

    // Fonction pour changer les lots
    function chargerLot($lots, $section, $titre)
    {
        $this->AddPage();
        if (sizeof($lots) != 0) {
            $this->SetFont('Arial', 'B', 14);
            $this->WriteHTML("<p style='text-align: left; font-weight: bold; '>- <u>$titre </u> </p>");
            $i = 1;
            $showCell = true;
            if ($this->typeRapport == 'rgpd') {
                $showCell = false;
            }
            $html = '<table  width="100%" cellPadding="10" style="border-collapse: collapse; border-color: #000000; border-style: solid;" border="1">
                        <thead>
                        <tr bgcolor="#36b92a" nowrap="nowrap">
                            <td width="5%">#</td>';
            if ($showCell) {
                $html .= '<td width="20%" >Coproprietaire</td>';
            }
            $html .= '
                            <td width="5%" nowrap="nowrap">Lot</td>
                            <td  width="10%">Type</td>
                            <td width="10%" nowrap="nowrap">Lot Principal</td>
                            <td  width="9%">Bât.</td>
                            <td  width="9%">Etage</td>
                            <td width="9%">Porte</td>
                            <td width="9%">Surface</td>
                            <td  width="10%" nowrap="nowrap">Tantième</td>
                            <td width="13%">Solde</td>
                            </tr>
                        </thead>
                        <tbody>';


            foreach ($lots as $lot) {
                if ($lot->siLotPrincipal == "Oui") {
                    $html .= "<tr bgcolor='$lot->couleur'>
                                <td>$i</td>";
                    if ($showCell) {
                        $html .= "<td " . (sizeof($lot->lotSecondaires) != 0 ? "rowspan='2'" : "") . " : ''  >  $lot->proprietaire </td>";
                    }
                    $html .= " 
                                <td nowrap='nowrap'>$lot->lot </td>
                                <td nowrap='nowrap'>" . ($lot->typeLot == "Appartement" ? "Appt." : (strtolower($lot->typeLot) == "logement" ? "Log." : $lot->typeLot)) . "</td>
                                <td nowrap='nowrap'>" . ($lot->siLotPrincipal == "Oui" ? "Principal" : "Secondaire") . " </td>
                                <td>$lot->batiment </td>
                                <td>$lot->etage </td>
                                <td>$lot->codePorte</td>
                                <td>$lot->surface m²</td>
                                <td nowrap='nowrap'>$lot->tantieme </td>" .
                        ($lot->showSolde ? ("<td rowspan='$lot->rowsPanSolde' nowrap='nowrap'>" . number_format(($lot->solde != null && $lot->solde != "" ? (float)$lot->solde : 0), 2, ',', ' ') . " €</td>") : "")

                        . "</tr>";
                    $i++;
                    if (sizeof($lot->lotSecondaires) != 0) {
                        $lot = $lot->lotSecondaires[0];
                        $html .= "<tr bgcolor='$lot->couleur'>
                        <td>$i </td>
                        <td nowrap='nowrap'>$lot->lot </td>
                        <td nowrap='nowrap'>" . ($lot->typeLot == "Appartement" ? "Appt." : (strtolower($lot->typeLot) == "logement" ? "Log." : $lot->typeLot)) . "</td>
                        <td nowrap='nowrap'>" . ($lot->siLotPrincipal == "Oui" ? "Principal" : "Secondaire") . " </td>
                        <td>$lot->batiment </td>
                        <td>$lot->etage </td>
                        <td>$lot->codePorte</td>
                        <td>$lot->surface m²</td>
                        <td nowrap='nowrap'>$lot->tantieme </td>
                        </tr>";
                        $i++;
                    }
                }
            }

            $html .= "</tbody></table>";
            $this->WriteHTML($html);
        }
    }

    function chargerVariables($htmlVariable, $lot)
    {
        $this->AddPage();
        $htmlVariable = preg_replace('/<td>/', '<td style="border: 1px solid black; font-weight: bold;">', $htmlVariable);
        $htmlVariable = preg_replace('/class="text-right"/', 'style="text-align: right; border: 1px solid black; font-weight: bold;"', $htmlVariable);
        $htmlVariable = str_replace(' montant-cell', '', $htmlVariable);
        $htmlVariable = str_replace(' pourcentage-cell', '', $htmlVariable);
        $htmlVariable = str_replace(' cout-m2', '', $htmlVariable);
        $htmlVariable = str_replace(' prix-revente', '', $htmlVariable);
        $htmlVariable = preg_replace('/<td id=/', '<td style="border: 1px solid black; font-weight: bold; text-align: center;" id=', $htmlVariable);
        $htmlVariable = preg_replace('/<td colspan=/', '<td style="font-weight: bold;" colspan=', $htmlVariable);
        $htmlVariable = preg_replace('/colspan="2">TOTAL GÉNÉRAL/', 'style="font-weight: bold; font-size: 20px;" colspan="2">TOTAL GÉNÉRAL', $htmlVariable);
        $htmlVariable = preg_replace('/<td>/', '<td style="border: 1px solid black;">', $htmlVariable);
        $htmlVariable = preg_replace('/<th>/', '<th style="border: 1px solid black;">', $htmlVariable);
        $htmlVariable = preg_replace('/class="text-right"/', 'style="text-align: right; border: 1px solid black;"', $htmlVariable);
        $htmlVariable = preg_replace('/class="table-secondary"/', 'style="background-color: gray; font-weight: bold; border: 1px solid black;"', $htmlVariable);
        $htmlVariable = preg_replace('/class="table-dark text-white"/', 'style="font-size: 20px; text-align: right; background-color: #d1d1d5; border: 1px solid black;"', $htmlVariable);
        $htmlVariable = preg_replace('/class="bg-success text-white"/', 'style="background-color: #d1d1d5;"', $htmlVariable);
        $this->SetFont('Arial', 'B', 14);
        $this->WriteHTML("<p style='text-align: center; font-weight: bold; '> -<u> Prévisionnel avec des données réelles sur le Lot $lot </u> </p>");
        $this->Ln(5);
        $html = "<table style='font-size: 14px; border-collapse: collapse;'> $htmlVariable </table>";
        $this->WriteHTML($html);
    }

    function SectionTitre($niveau, $num, $libelle, $texte = '', $fontSize = 18, $x = 0)
    {
        $str = htmlspecialchars($libelle);
        $strsize = $this->GetStringWidth($str);
        if ($strsize > 80) {
            $str = substr($str, 0, - (strlen($str) - 80)) . '...';
        }

        $id = str_replace('.', ':', $num);
        $txt = $num . '. ' . htmlspecialchars($libelle);
        if ($niveau == 0) {
            $txt = "Chapitre "  . $txt;
            $this->SetTopMargin($this->h / 2 - 30);
            $this->AddPage();
            $this->WriteHTML("<a name='$id'></a>");
            $this->links[$id]['x'] = $this->x;
            $this->links[$id]['y'] = $this->y;
            $this->links[$id]['width'] = $this->w;
            $this->links[$id]['height'] = $this->h;
            $this->SetFont('', 'B', 25);
            $this->EntrerDonnee($id, ($txt), $niveau, $this->y);
            $this->SetTextColor(46, 125, 50);
            $this->MultiCell(0, 15, ($txt), 0, 'C');
            $this->Bookmark(htmlspecialchars('Chapitre ' . $num . ' : ' . $str), $niveau + 1);
            $this->SetTopMargin(20);
            $this->AddPage();
            $this->SetFont('', 'B', 14);
            $this->MultiCell(0, 6, ($txt), 0, 'L');
            $this->Ln(5);
            $this->SetTextColor(0, 0, 0);
        } else {
            $this->x = $x;
            if ($niveau == 1) {
                $this->SetTextColor(141, 110, 99);
            }
            $this->SetFont('', 'B', $fontSize);
            $this->EntrerDonnee($id, mb_convert_encoding($txt, 'UTF-8', 'UTF-8'), $niveau, $this->y);
            $this->MultiCell(0, $fontSize + 2,  mb_convert_encoding($txt, 'UTF-8', 'UTF-8'), 0, 'L');
            $this->SetTextColor(0, 0, 0);
            $this->WriteHTML("<a name='$id'></a>");
            $this->Bookmark(htmlspecialchars($num . '. ' . $str), $niveau + 1);
        }
    }

    function SectionContent($texte = '', $fontSize = 7, $x = 20)
    {
        $this->SetFont('', '', $fontSize);
        $this->x = $x;
        $this->y = ($this->y - 3);
        $this->WriteHTML($texte);
        if ($texte != '') $this->Ln(3);
    }

    function ajouterSection($section, $fontSizeTitle = 14, $fontSizeContent = 8, $xTitle = 0, $xContent = 20)
    {
        $niveau = 0;
        if ($section->numeroSection) {
            $niveau = substr_count($section->numeroSection, '.');
        }
        if ($niveau == 1 && explode('.', $section->numeroSection)[1] != "1") {
            $this->AddPage();
        }
        $this->SectionTitre($niveau, $section->numeroSection,  $section->titreSection, $section->contenuSection, $fontSizeTitle, $xTitle);
        if (trim($section->contenuSection) != "") {
            $this->SectionContent($section->contenuSection, $fontSizeContent, $xContent);
        }

        if ($section->action == 'tousLots') {
            $titre = "Liste des Lots";
            $lots = $this->lotModel->getLotsByImmeuble($this->immeuble->idImmeuble, "", $section->action);
            $this->chargerLot($lots, $section, $titre);
        } else {
            if ($section->action == 'lotAcquerir') {
                $titre = "Liste des Lots à acquerir";
                $lots = $this->lotModel->getLotsBySection($section->idSection, "", $section->action);
                $this->chargerLot($lots, $section, $titre);
            } else {
                if ($section->action == 'lotAssocie') {
                    $lotSections = $this->lotModel->getLotsBySection($section->idSection, "", $section->action);
                    foreach ($lotSections as $lot) {
                        if ($lot->siLotPrincipal == "Oui") {
                            $htmlVariable = $this->lotModel->getHtmlVariable($lot->idSectionF, $lot->idAppartementF)->htmlVariable;
                            $suite = $this->typeRapport == 'rgpd' ? '' : "Coproprietaire : $lot->proprietaire - ";
                            $text = "$lot->lot-$lot->typeLot ($suite Lot N° : $lot->lot - Bât : $lot->batiment/$lot->etage/$lot->codePorte)";
                            $this->chargerVariables($htmlVariable, $text);
                        }
                    }
                } else {
                    $titre = '';
                    $articles = [];
                    if ($section->action == "cctp") {
                        $titre = "TABLEAU D'ARTICLES CCTP";
                        $articles = $this->sectionModel->getArticlesBySection($section->idSection, '', '', '');
                    } else {
                        if ($section->action == "cctpG") {
                            $titre = "TABLEAU D'ARTICLES CCTP GLOBAL";
                            $articles = $this->sectionModel->getArticlesBySection($section->idSection, $this->immeuble->idImmeuble, '', 'global');
                        }
                    }
                    $this->chargerArticles($articles, $section, $titre);
                }
            }
        }

        $documents = $this->sectionModel->getDocuments($section->idSection);
        $this->chargerDocument($documents, $section, $fontSizeTitle, $fontSizeContent);
    }

    // Fonction récursive pour gérer les sections
    function ajouterSectionsRecursives($projet, $sections, $niveau = 0)
    {
        $parametres = [
            0 => [14, 7, 0, 20],
            1 => [11, 7, 8, 20],
            2 => [10, 7, 12, 20],
            3 => [9, 7, 16, 20],
            4 => [8, 7, 20, 24]
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

    function Footer()
    {
        if (!$this->numeroterFooter) {
            return;
        }

        if (!$this->numeroter) {
            $this->numeroterFooter = false;
        }
    }
}