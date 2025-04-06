<?php
require_once "../../../app/config/config.php";
require_once "../../../app/libraries/Utils.php";
require_once "../../../app/libraries/Database.php";
require_once '../../libs/vendor2/autoload.php';

use \Mpdf\Mpdf;

class CompteRenduPdf extends MPDF {
    public $immeuble;
    public $o;
    public $rt;
    public $rv;
    public $rf;
    public $deroulementSeance;
    public $tabPieces;
    public $app;
    public $numPolice;
    public $numSinistre;
    public $dateSinistre;
    public $dateDebutContrat;
    public $dateFinContrat;
    public $adresse;
    public $cp;
    public $ville;
    public $devis;
    public $contact;
    public $cie;
    public $do;
    public $sections;
    public $estLocataire;
    public $estProprietaire;
    public $estPersMorale;
    public $sommaire;
    public $nb_sommaire;

    public function __construct($config, $data, $sommaire = [], $nb_sommaire = 0) {
        parent::__construct($config);
        $this->immeuble = $data['immeuble'];
        $this->o = $data['o'];
        $this->rt = $data['rt'];
        $this->rv = $data['rv'];
        $this->rf = $data['rf'];
        $this->deroulementSeance = $data['deroulementSeance'];
        $this->tabPieces = $data['tabPieces'];
        $this->app = $data['app'];
        $this->numPolice = $data['numPolice'];
        $this->numSinistre = $data['numSinistre'];
        $this->dateSinistre = $data['dateSinistre'];
        $this->dateDebutContrat = $data['dateDebutContrat'];
        $this->dateFinContrat = $data['dateFinContrat'];
        $this->adresse = $data['adresse'];
        $this->cp = $data['cp'];
        $this->ville = $data['ville'];
        $this->devis = $data['devis'];
        $this->contact = $data['contact'];
        $this->cie = $data['cie'];
        $this->do = $data['do'];
        $this->sections = $data['sections'];
        $this->estLocataire = $data['estLocataire'];
        $this->estProprietaire = $data['estProprietaire'];
        $this->estPersMorale = $data['estPersMorale'];
        $this->sommaire = $sommaire;
        $this->nb_sommaire = $nb_sommaire;
    }

    public function Header($content = "")
    {
        $this->Image('../../images/LOGO_SOS_SINISTRE.png', 10, 2, $this->w / 5, 25);
        if ($this->PageNo() != 1) {
            $this->SetY(15);
            $this->SetX(135);
            $this->SetTextColor(0, 0, 0);
            $this->SetFont('Times', 'B', 14);
            $this->Cell(0, 0, iconv('UTF-8', 'windows-1252', strtoupper($GLOBALS['opportunity']->name)), 0, 0, 'R', false, '');

            $this->SetY(30);
        }
    }
    
    function Footer()
    {
        $this->SetY(-10);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, $this->page . '/{nb}', 0, 0, 'C');
        // Début en police normale
        $this->SetY(-28);
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(192, 0, 0);
        $this->Cell(0, 5, 'World Business Contact Center - WBCC ASSISTANCE', 0, 0, 'C');

        $this->SetY(-18);
        $this->SetFont('Arial', 'BU', 10);
        $this->SetTextColor(255, 255, 255);
        $this->SetFillColor(192, 0, 0);
        $this->Cell(0, 10, 'www.wbcc.fr', 0, 0, 'R', true, 'https://wbcc.fr/');
        $this->SetY(-18);
        $this->SetFont('Arial', 'BU', 10);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 10, 'gestion@wbcc.fr', 0, 0, 'L', false, 'mailto:gestion@wbcc.fr');

        $this->SetY(-22);
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 5, iconv('UTF-8', 'windows-1252', '218, Rue de Bellevue 92700 Colombes. Siret : 817 869 167 00012 Tél. 09 800 844 84. Fax. 09 85 84 53 39'), 0, 0, 'C');

        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(192, 0, 0);
        $this->Cell(0, 10, 'Fait le ' . date('d/m/Y'), 0, 0, 'R');

    }

    public function EntrerDonnee($id, $txt, $level = 0) {
        $this->sommaire[] = [
            'id' => $id,
            't' => $txt,
            'l' => $level,
            'p' => $this->PageNo()
        ];
    }

    
    public function returnSommairePage($sommaireCompteRendu = []) {
        if (sizeof($sommaireCompteRendu) > 0) {
            $nb = $this->insertSommaire($sommaireCompteRendu);
            return $nb;
        }
        return 0;
    }

    public function returnSommaire() {
        $this->PageDeGarde();
        $this->CorpsAlgo();
        return $this->sommaire;
    }
    
    public function insertSommaire($sommaire, $nb_page_offset = 0, $label = 'Sommaire', $labelSize = 20, $entrySize = 10, $tocFont = 'Arial') {
        $this->AddPage();
        $start = $this->page;
    
        $this->SetFont($tocFont, 'B', $labelSize);
        $this->WriteCell(0, 5, $label, 0, 1, 'C');
        $this->Ln(10);
    
        foreach ($sommaire as $t) {
            $level = $t['l'];
            $weight = 'B';
            $str = $t['t'];
            $id = $t['id'];
    
            $links = str_repeat('.', $this->w / 1.5 + 8);
            $this->links[$id]['url'] = '#' . $id;
            $this->WriteHTML("<a href='#$id' style='width: 1000px; color: white;'> $links </a>");
    
            $this->SetFont($tocFont, $weight, $entrySize);
            $strsize = $this->GetStringWidth($str);
            if ($strsize + 20 > $this->w - $this->lMargin - $this->rMargin) {
                $str = substr($str, 0, -(strlen($str) - 50)) . '...';
                $strsize = $this->GetStringWidth($str) - 2;
            }
    
            $this->SetY($this->y - 5);
            if ($level > 0) {
                $this->WriteCell($level * 4);
            }
            $this->WriteCell($strsize + 2, $this->FontSize + 2, $str, 0, 0, 'L', false);
    
            $this->SetFont($tocFont, '', $entrySize);
            $PageCellSize = $this->GetStringWidth($t['p']) + 2;
            $w = $this->w - $this->lMargin - $this->rMargin - $PageCellSize - ($level * 4) - ($strsize + 2);
            $nb = $w / $this->GetStringWidth('.');
            $dots = str_repeat('.', max(0, (int)$nb));
            $this->WriteCell($w, $this->FontSize + 2, $dots, 0, 0, 'R', false);
    
            $this->SetFont($tocFont, "B", $entrySize);
            $this->WriteCell($PageCellSize, $this->FontSize + 2, utf8_encode($t['p'] + $nb_page_offset), 0, 1, 'R', false);
        }
    
        $n = $this->page;
        $n_sommaire = $n - $start + 1;
    
        return $n_sommaire;
    }

    public function PageDeGarde() {
        
        $this->AddPage();
        $this->Bookmark('Compte Rendu RT ' . $this->o->name, 0);
        $photoImm = false;
        if ($this->immeuble && $this->immeuble->photoImmeuble != null && $this->immeuble->photoImmeuble !=  "" && file_exists('../../documents/immeuble/' . $this->immeuble->photoImmeuble . '')) {
            $photoImm = true;
        }

        if ($photoImm) {
            $this->SetY(25);
        } else {
            $this->SetY(($this->h / 2) - 40);
        }

        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(186, 29, ("COMPTE RENDU RELEVES TECHNIQUES"), 1, 0, 'C', true, '');

        if ($photoImm) {
            $this->SetY(55);
        } else {
            $this->SetY(($this->h / 2) - 22);
        }
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(184, 0, 0);
        $this->SetX(60);
        $this->Cell(50, 8, ("Nos Références : " . $this->o->name), 0, 0, 'L', true, '');
        $this->Ln();

        if ($photoImm) {
            $this->Image(URLROOT  . "/public/documents/immeuble/" . $this->immeuble->photoImmeuble, 20, 75, 160, 120);
            $this->SetY(200);
        }

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', 'BU', 12);
        $this->Cell(73, 10, ('N° de Contrat :'), 1, 0, 'J', true);
        $this->SetFont('Arial', '', 12);
        $this->Cell(113, 10, ("$this->numPolice"), 1, 0, 'J', true);
        $this->Ln();

        $this->SetFont('Arial', 'BU', 12);
        $this->Cell(73, 10, ('N° de Sinistre:'), 1, 0, 'J', true);
        $this->SetFont('Arial', '', 12);
        $this->Cell(113, 10, ($this->numSinistre), 1, 0, 'J', true);
        $this->Ln();

        $this->SetFont('Arial', 'BU', 12);
        $this->Cell(73, 10, ('Nature du Sinistre :'), 1, 0, 'J', true);
        $this->SetFont('Arial', '', 12);
        $this->Cell(113, 10, ($this->rt->nature), 1, 0, 'J', true);
        $this->Ln();

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', 'BU', 12);
        $this->Cell(73, 10, ('Lieu du sinistre :'), 1, 0, 'J', true);
        $this->SetFont('Arial', '', 12);
        $this->Cell(113, 10, ("$this->adresse $this->cp $this->ville"), 1, 0, 'J', true);
        $this->Ln();

        $this->SetFont('Arial', 'BU', 12);
        $this->Cell(73, 10, ('Date du sinistre :'), 1, 0, 'J', true);
        $this->SetFont('Arial', '', 12);
        $this->Cell(113, 10, ($this->dateSinistre), 1, 0, 'J', true);
        $this->Ln();


        $this->SetFont('Arial', 'BU', 12);
        $this->Cell(73, 10, ('Date du rendez-vous RT :'), 1, 0, 'J', true);
        $this->SetFont('Arial', '', 12);
        $this->Cell(113, 10, ($this->rv ? date("d/m/Y", strtotime($this->rv->dateRV)) : ""), 1, 0, 'J', true);
        $this->Ln();
    }

    public function corpsAlgo() {
        $i = 1;
        $this->AddPage();
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(184, 0, 0);
        $this->SetTextColor(255, 255, 255);
        $this->Bookmark('1- Introduction', 1);
        $this->EntrerDonnee($i++, ("1- Introduction"), 1);
        $this->Cell($this->w - 20, 8, "1- INTRODUCTION ", 0, 3, 'L', true);

        $this->SetFont('Arial', '', 12);
        $this->SetTextColor(0, 0, 0);
        $this->WriteHTML($this->rt->introduction);
        $this->Ln();

        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(184, 0, 0);
        $this->SetTextColor(255, 255, 255);
        $this->Bookmark('2-1 Feuille de presence', 1);
        $this->EntrerDonnee($i++, ("2-1 Feuille de presence"), 1);
        $this->Cell($this->w - 20, 8, "2-1 FEUILLE DE PRESENCE ", 0, 3, 'L', true);
        $this->Ln(3);

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', 'BU', 12);
        $this->MultiCell(0, 5,  ("Etaient présents au $this->adresse $this->cp $this->ville le " . ($this->rv ? date("d/m/Y à h:i", strtotime($this->rv->dateRV)) : "")), 0, 'J');
        $this->Ln(3);

        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(65, 15, ("Nom de la partie"), 1, 0, 'C', true, '');
        $this->Cell(25, 15, ("Présente"), 1, 0, 'C', true, '');
        $this->Cell(25, 15, ("Absente"), 1, 0, 'C', true, '');
        $this->Cell(75, 15, ("Représentée"), 1, 0, 'C', true, '');
        $this->Ln();

        $this->SetFont('Arial', '', 12);
        $this->Cell(65, 15, $this->contact ? (" " . $this->contact->civiliteContact . " " . $this->contact->prenomContact . " " . $this->contact->nomContact) : "", 1, 0, 'J', true, '');
        $this->Cell(25, 15, ("x"), 1, 0, 'C', true, '');
        $this->Cell(25, 15, (""), 1, 0, 'C', true, '');
        $this->Cell(75, 15, (""), 1, 0, 'J', true, '');
        $this->Ln();


        $this->SetFont('Arial', '', 12);
        $this->Cell(65, 15, (" WBCC Assistance"), 1, 0, 'J', true, '');
        $this->Cell(25, 15, ("x"), 1, 0, 'C', true, '');
        $this->Cell(25, 15, (""), 1, 0, 'C', true, '');
        $this->Cell(75, 15, $this->rv ? (" Expert : " . $this->rv->expert) : "", 1, 0, 'J', true, '');
        $this->Ln();

        //Coordonnées des parties
        $this->AddPage();
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(184, 0, 0);
        $this->SetTextColor(255, 255, 255);
        $this->Bookmark('2-2 Coordonnées des parties', 1);
        $this->EntrerDonnee($i++, ("2-2 Coordonnées des parties"), 1);
        $this->Cell($this->w - 20, 8, "2-2 COORDONNEES DES PARTIES", 0, 3, 'L', true);
        $this->Ln();

        $this->SetX(10);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 12);
        $aligns = ['center', 'center', 'center', 'center'];
        $headers = ['NOM', 'TYPE', 'ADRESSE', 'CODE POSTAL / VILLE', 'TELEPHONE', 'EMAIL', 'NUMERO DE CONTRAT', 'NUMERO DE SINISTRE'];
        $rows = [
            ['NOM', ($this->cie ? $this->cie->name : ""), "WBCC Assistance", ($this->contact->civiliteContact ?? "") . " " . ($this->contact->prenomContact ?? "") . " " . ($this->contact->nomContact ?? "")],
            ['TYPE', ($this->o->typeSinistre == "Partie commune exclusive" ? 'MRI' : ("MRH")), "Gestionnaire de Sinistres", ($this->estLocataire ? "Locataire" : "Copropriétaire")],
            ['ADRESSE', ($this->cie ? $this->cie->businessLine1 : ""), "218, Rue de Bellevue", $this->adresse],
            ['CODE POSTAL / VILLE', ($this->cie ? ($this->cie->businessPostalCode . " " . $this->cie->businessCity) : ""), "92700 Colombes", "$this->cp $this->ville"],
            ['TELEPHONE', ($this->cie ? $this->cie->businessPhone : ""), "0980084484", ($this->contact->telContact ?? "")],
            ['EMAIL', ($this->cie ?  $this->cie->email : ""), "gestion@wbcc.fr", ($this->contact->emailContact ?? "")],
            ['NUMERO DE CONTRAT', ($this->numPolice), "", $this->numPolice],
            ['NUMERO DE SINISTRE', ($this->numSinistre), $this->o->name, $this->numSinistre],
        ];
        $tableStyle = '
            <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                table, th, td {
                    border: 1px solid black;
                }
                th, td {
                    text-align: center;
                    padding: 8px;
                }
            </style>
        ';
        $html = $tableStyle . '<table>';
        foreach ($rows as $index => $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                $tag = $index === 0 ? 'th' : 'td';
                $align = $aligns[$key] ?? 'center';
                $font = $key == 0 ? "font-weight: bold;" : "";
                $html .= "<$tag style='text-align: $align; $font'>$value</$tag>";
            }
            $html .= '</tr>';
        }
        $html .= '</table>';

        $this->Writehtml($html);
        
        // LISTE DES PIECES JOINTES
        $this->AddPage();
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(184, 0, 0);
        $this->SetTextColor(255, 255, 255);
        $this->Bookmark('2-3 Liste des pièces jointes', 1);
        $this->EntrerDonnee($i++, ("2-3 Liste des pièces jointes"), 1);
        $this->Cell($this->w - 20, 8, "2-3 LISTE DES PIECES JOINTES", 0, 3, 'L', true);
        $html = '<ul>
            <li>Photos et métrés pris par WBCC Assistance lors du Rendez-vous Relevés Techniques.</li>
            <li>Délégation de gestion signée par ' . htmlspecialchars($this->contact ?($this->contact->civiliteContact . ' ' . $this->contact->prenomContact . ' ' . $this->contact->nomContact) : "") . '.</li>';

        if ($this->devis) {
            $html .= '<li>Devis n° ' . htmlspecialchars($this->devis->numeroDevisWBCC) . ' de l’entreprise ' . htmlspecialchars($this->devis->artisan) . ' pour les travaux de réfection d’un montant de ' . htmlspecialchars($this->devis->montantHT) . ' € HT soit ' . htmlspecialchars($this->devis->montantTotal) . ' € TTC.</li>';
        }
        $html .= '</ul>';
        $this->WriteHTML($html);

        // CONTEXTE
        $this->AddPage();
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(184, 0, 0);
        $this->SetTextColor(255, 255, 255);
        $this->Bookmark('3- Contexte', 1);
        $this->EntrerDonnee($i++, ("3- Contexte"), 1);
        $this->Cell($this->w - 20, 8, "3- CONTEXTE", 0, 3, 'L', true);
        $x = $this->x;
        $this->Ln(3);
        $this->WriteHTML($this->rt->contexte);
        $this->Ln(3);

        // DESCRIPTION DU SINISTRE
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(169, 169, 169);
        $this->SetTextColor(255, 255, 255);
        $this->Bookmark('3-1 Description du sinistre', 2);
        $this->EntrerDonnee($i++, ("3-1 Description du sinistre"), 2);
        $this->Cell($this->w - 20, 8, "3-1 DESCRIPTION DU SINISTRE", 0, 3, 'L', true);
        $this->Ln(3);
        $this->WriteHTML($this->rt->descriptionSinistre);
        $this->Ln(5);

        // ORIGINE DU SINISTRE
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(169, 169, 169);
        $this->SetTextColor(255, 255, 255);
        $this->Bookmark('3-2 Origine du sinistre', 2);
        $this->EntrerDonnee($i++, ("3-2 Origine du sinistre"), 2);
        $this->Cell($this->w - 20, 8, "3-2 ORIGINE DU SINISTRE", 0, 3, 'L', true);
        $this->Ln(3);
        $this->WriteHTML($this->rt->origineSinistre);
        $this->Ln(5);

        // INTERVENTIONS INITIALES
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(169, 169, 169);
        $this->SetTextColor(255, 255, 255);
        $this->Bookmark('3-3 Interventions initiales', 2);
        $this->EntrerDonnee($i++, ("3-3 Interventions initiales"), 2);
        $this->Cell($this->w - 20, 8, "3-3 INTERVENTIONS INITIALES", 0, 3, 'L', true);
        $this->Ln(3);
        $this->WriteHTML($this->rt->interventionInitiales);
        $this->Ln(5);

        // DEROULEMENT DE LA SEANCE
        $this->addPage();
        $this->Ln(3);
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(184, 0, 0);
        $this->SetTextColor(255, 255, 255);
        $this->SetX($x);
        $this->Bookmark('4- Déroulement de la séance', 1);
        $this->EntrerDonnee($i++, ("4- Déroulement de la séance"), 1);
        $this->Cell($this->w - 20, 8, "4- DEROULEMENT DE LA SEANCE", 0, 3, 'L', true);
        $this->Ln(3);
        $this->WriteHTML($this->rt->deroulementSeance);
        $this->Ln(5);

        // INSPECTION APPARTEMENT
        $this->Ln(3);
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(169, 169, 169);
        $this->SetTextColor(255, 255, 255);
        $this->SetX($x);
        $this->Bookmark('4-1 Inspection de l\'appartement', 2);
        $this->EntrerDonnee($i++, ("4-1 Inspection de l'appartement"), 2);
        $this->Cell($this->w - 20, 8, "4-1 INSPECTION DE L'APPARTEMENT", 0, 3, 'L', true);
        $this->Ln(3);
        $this->WriteHTML($this->deroulementSeance['1']->contenuSection);
        $this->Ln(5);

        // CONFIRMATION NATURE SINISTRE
        $this->Ln(3);
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(169, 169, 169);
        $this->SetTextColor(255, 255, 255);
        $this->SetX($x);
        $this->Bookmark('4-2 Confirmation de la nature du sinistre', 2);
        $this->EntrerDonnee($i++, ("4-2 Confirmation de la nature du sinistre"), 2);
        $this->Cell($this->w - 20, 8, "4-2 CONFIRMATION DE LA NATURE DU SINISTRE", 0, 3, 'L', true);
        $this->Ln(3);
        $this->WriteHTML($this->deroulementSeance['2']->contenuSection);
        $this->Ln(5);

        // IDENTIFICATION ORIGINE SINISTRE
        $this->Ln(3);
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(169, 169, 169);
        $this->SetTextColor(255, 255, 255);
        $this->SetX($x);
        $this->Bookmark('4-3 Identification origine du sinistre', 2);
        $this->EntrerDonnee($i++, ("4-3 Identification origine du sinistre"), 2);
        $this->Cell($this->w - 20, 8, "4-3 IDENTIFICATION ORIGINE DU SINISTRE", 0, 3, 'L', true);
        $this->Ln(3);
        $this->WriteHTML($this->deroulementSeance['3']->contenuSection);
        $this->Ln(5);

        // IDENTIFICATION RESPONSABILITES
        $this->Ln(3);
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(169, 169, 169);
        $this->SetTextColor(255, 255, 255);
        $this->SetX($x);
        $this->Bookmark('4-4 Identification des responsabilites', 2);
        $this->EntrerDonnee($i++, ("4-4 Identification des responsabilites"), 2);
        $this->Cell($this->w - 20, 8, "4-4 IDENTIFICATION DES RESPONSABILITES", 0, 3, 'L', true);
        $this->Ln(3);
        $this->WriteHTML($this->deroulementSeance['4']->contenuSection);
        $this->Ln(5);

        // DESCRIPTION DES DEGATS
        $this->Ln(3);
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(169, 169, 169);
        $this->SetTextColor(255, 255, 255);
        $this->SetX($x);
        $this->Bookmark('4-5 Description des degats constates', 2);
        $this->EntrerDonnee($i++, ("4-5 Description des degats constates"), 2);
        $this->Cell($this->w - 20, 8, "4-5 DESCRIPTION DES DEGATS CONSTATES", 0, 3, 'L', true);
        $this->Ln(3);
        $this->WriteHTML($this->deroulementSeance['5']->contenuSection);
        $this->Ln(5);

        // EVALUATION DES DOMMAGES
        $this->Ln(3);
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(169, 169, 169);
        $this->SetTextColor(255, 255, 255);
        $this->SetX($x);
        $this->Bookmark('4-6 Evaluation des dommages materiels et structurels', 2);
        $this->EntrerDonnee($i++, ("4-6 Evaluation des dommages materiels et structurels"), 2);
        $this->Cell($this->w - 20, 8, "4-6 EVALUATION DES DOMMAGES MATERIELS ET STRUCTURELS", 0, 3, 'L', true);
        $this->Ln(3);
        $this->WriteHTML($this->deroulementSeance['6']->contenuSection);
        $this->Ln(5);

        // PROPOSITION DE SOLUTIONS
        $this->Ln(3);
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(169, 169, 169);
        $this->SetTextColor(255, 255, 255);
        $this->SetX($x);
        $this->Bookmark('4-7 Propositions de solutions pour la reparation et la prevention', 2);
        $this->EntrerDonnee($i++, ("4-7 Propositions de solutions pour la reparation et la prevention"), 2);
        $this->Cell($this->w - 20, 8, "4-7 PROPOSITIONS DE SOLUTIONS POUR REPARATION ET PREVENTION", 0, 3, 'L', true);
        $this->Ln(3);
        $this->WriteHTML($this->deroulementSeance['7']->contenuSection);
        $this->Ln(5);

        // CONCLUSION
        $this->AddPage();
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(184, 0, 0);
        $this->SetTextColor(255, 255, 255);
        $this->Bookmark('5- Conclusion', 1);
        $this->EntrerDonnee($i++, ("5- Conclusion"), 1);
        $this->Cell($this->w - 20, 8, "5- CONCLUSION", 0, 3, 'L', true);
        $conb = $this->sections['5-']->contenuSection;
        $this->Ln(3);
        $html = '<p>Nous vous tiendrons au courant au fur et à mesure de l’évolution du dossier. Nous vous conseillons de communiquer directement avec WBCC ASSISTANCE pour plus de réactivité, de gain de temps et un meilleur suivi du dossier.</p>';
        $html .= '<p style="text-align: center; font-weight: bold;">Tél. : 09 800 844 84</p>';
        $html .= '<p>A COLOMBES, le ' . date("d/m/Y") . '</p>';
        $html .= '<p>Pour la société WBCC-ASSISTANCE</p>';
        $html .= '<p>' . htmlspecialchars($this->rt->auteurCompteRenduRT) . '</p>';
        $this->WriteHTML($conb);

        // ANNEXE 1 : Photos RT
        $this->AddPage();
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetY(130);
        $this->SetX(2);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Times', 'B', 20);
        $this->Bookmark('Annexe 1 : Detail Releves Techniques', 1);
        $this->EntrerDonnee($i++, ("Annexe 1 : Detail Releves Techniques"), 1);
        $this->Cell(0, 0, (strtoupper("ANNEXE 1 : Detail Releves Techniques")), 0, 0, 'C', false, '');

        //PIECES
        if (sizeof($this->tabPieces) != 0) {
            foreach ($this->tabPieces as $key => $piece) {
                $piecenum = $key + 1;

                $this->AddPage();
                $this->Writehtml("<a name='" . $i . "'></a>");
                $this->SetY(130);
                $this->SetX(2);
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('Times', 'B', 20);
                $this->Bookmark($piecenum.'- pieces n°' . $piecenum . ' : ' . $piece->libellePiece, 2);
                $this->EntrerDonnee($i++, ($piecenum."- PIECES N°" . $piecenum . " : " . $piece->libellePiece), 2);
                $this->Cell(0, 0, (strtoupper("PIECES N°" . $piecenum . " : " . $piece->libellePiece)), 0, 0, 'C', false, '');

                $this->AddPage();
                $this->Writehtml("<a name='" . $i . "'></a>");
                $this->EntrerDonnee($i++, ($piecenum . "-" . $piece->libellePiece), 3);
                $this->SetX(12);
                $this->SetY(23);
                $this->SetFont('Times', 'B', 14);
                $this->SetFillColor(169, 169, 169);
                $this->Bookmark($piecenum."-".$piece->libellePiece, 3);
                $this->Cell(186, 10,  (" Nom de la Pièce N°" . ($piecenum) . " à inspecter : " . $piece->libellePiece), 0, 0, 'L', true);
                $this->Ln(10);
                $this->Image('../../images/quadrillage.jpg', $this->x, $this->y, 186, 50);
                $this->Ln(10);

                //Dimensions 
                $this->Writehtml("<a name='" . $i . "'></a>");
                $this->EntrerDonnee($i++, ($piecenum . "-1 Dimensions de la piece"), 4);
                $this->SetX(30);
                $this->SetY(90);
                $this->SetFont('Times', 'B', 14);
                $this->Bookmark($piecenum . "-1 Dimensions de la pièce", 4);
                $this->Cell(190, 10,  ($piecenum . "-1- Dimensions de la pièce"), 0, 0, 'L');
                $this->Ln(10);
                $this->SetFont('Times', '', 12);
                $this->SetFillColor(240, 128, 128);
                $this->Cell(45, 10,  (" Longueur (m)"), 1, 0, 'L', true);
                $this->Cell(45, 10, (" Largeur (m)"), 1, 0, 'L', true);
                $this->Cell(47, 10,  (" Périmétre (m)"), 1, 0, 'L', true);
                $this->Cell(49, 10,  (" Surface Totale (m2)"), 1, 0, 'L', true);
                $this->Ln(10);
                $this->Cell(45, 10,  ($piece->longueurPiece), 1, 0, 'C');
                $this->Cell(45, 10, ($piece->largeurPiece), 1, 0, 'C');
                $this->Cell(47, 10,  (($piece->longueurPiece != "" && $piece->longueurPiece != null && $piece->largeurPiece != null && $piece->largeurPiece != "" ? round($piece->longueurPiece * $piece->largeurPiece, 2) : "0")), 1, 0, 'C');
                $this->Cell(49, 10,  (($piece->longueurPiece != "" && $piece->longueurPiece != null && $piece->largeurPiece != null && $piece->largeurPiece != "" ? round($piece->longueurPiece * $piece->largeurPiece, 2) : "0")), 1, 0, 'C');

                $this->Ln(15);
                $this->SetFont('Times', '', 12);
                $this->SetFillColor(240, 128, 128);
                $this->Cell(186, 10,  (" Commentaire sur les dimensions "), 1, 0, 'L', true);
                $this->Ln(10);
                $this->SetFillColor(255, 255, 255);
                $this->WriteHTML($piece->commentaireMetrePiece ?? "");
                $this->MultiCell(186, 10, ( $piece->commentaireMetrePiece ?? ""), 1, 'L');

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

                $this->Ln(5);
                $this->Writehtml("<a name='" . $i . "'></a>");
                $this->EntrerDonnee($i++, (($piecenum+1) . "-2 Liste des supports endommagés"), 4);
                $this->SetFont('Times', 'B', 14);
                $this->Bookmark(($piecenum+1) . '-2 Liste des supports endommagés', 4);
                $this->Cell(190, 10,  (($piecenum+1) . "-2- Liste des supports endommagés"), 0, 0, 'L');
                $this->Ln(5);
                $this->SetFont('Times', '', 12);
                $this->Cell(190, 20, ($libSupport), 'B', 0, 'L');

                if ($nbMursNonSinistres != 0 || $nbMursSinistres != 0) {

                    $this->Ln(20);
                    $this->Writehtml("<a name='" . $i . "'></a>");
                    $this->EntrerDonnee($i++, (($piecenum+1) . "-3 Surface des murs"), 4);
                    $this->SetFont('Times', 'B', 14);
                    $this->Bookmark(($piecenum+1) . '-3 Surface des Murs', 4);
                    $this->Cell(190, 10,  (($piecenum+1) . "-3- Surface des Murs"), 0, 0, 'L');
                    $this->Ln(10);
                    $this->SetFont('Times', '', 12);
                    $this->SetFillColor(240, 128, 128);
                    $this->Cell(93, 10,  (" Surface Murs Sinistrés (m²)"), 1, 0, 'L', true);
                    $this->Cell(93, 10, (" Surface Murs Non Sinistrés (m²)"), 1, 0, 'L', true);
                    $this->Ln(10);
                    $this->Cell(93, 10,  ($surfaceMursSinistres), 1, 0, 'C');
                    $this->Cell(93, 10, ($surfaceMursNonSinistres), 1, 0, 'C');
                }

                $this->Ln(20);
                $this->SetFont('Times', '', 12);
                $this->SetFillColor(240, 128, 128);
                $this->Cell(186, 10,  (" Commentaire sur les supports"), 1, 0, 'L', true);
                $this->Ln(10);
                $this->SetFillColor(255, 255, 255);
                $this->writehtml("<table style='border: 1px solid black; padding: 5px; text-align: left;'><tr><td>" . $piece->commentaireSupport . "</td></tr></table>");

                $this->Ln(10);
                $this->SetFont('Times', '', 12);
                $this->SetFillColor(240, 128, 128);
                $this->Cell(186, 10,  (" Commentaire sur "  .  $piece->nomPiece), 1, 0, 'L', true);
                $this->Ln(10);
                $this->SetFillColor(255, 255, 255);
                $this->writehtml("<table style='border: 1px solid black; padding: 5px; text-align: left;'><tr><td>" . $piece->commentairePiece . "</td></tr></table>");

                $this->AddPage();
                $this->Bookmark('2- Photos : ' . $piece->nomPiece, 3);
                $this->EntrerDonnee($i++, ("2- Photos : " . $piece->nomPiece), 3); 
                $this->Writehtml("<a name='" . $i . "'></a>");
                //PHOTO PIECE
                if ($piece->photosPiece != "" && $piece->photosPiece != null) {
                    $photos = $piece->photosPiece == "" ? [] : explode(";", $piece->photosPiece);
                    $comments = $piece->commentsPhotosPiece == "" ? [] : explode("}", $piece->commentsPhotosPiece);
                    $j = 0;

                    if (count($photos) > 0) {
                        while ($j < count($photos)) {
                            if ($j % 2 == 0) {
                                $this->SetY(27);
                                $this->SetTextColor(0, 0, 0);
                                $this->SetFont('Times', 'B', 14);
                                $this->SetFillColor(169, 169, 169);
                                $this->Cell(190, 15, iconv('UTF-8', 'windows-1252', "Photos : " . $piece->nomPiece), 0, 0, 'L', true);
                            }

                            $photoPiece1 = isset($photos[$j]) ? $photos[$j] : "";
                            if ($photoPiece1 != "" && file_exists($photoPiece1)) {
                                $this->Rect(9.5, 44.5, 125, 101, "solid");
                                $this->Image(URLROOT . "/public/documents/opportunite/$photoPiece1", 10, 45, 124, 100);
                                $this->SetFont('Arial', 'U', 12);
                                $this->SetTextColor(0, 0, 0);
                                $this->SetY(46);
                                $this->SetX(140);
                                $this->Cell(0, 0, "Commentaire :", 0, 0, 'J', false, '');
                                $this->SetFont('Arial', '', 11);
                                $this->Rect(140, 50, 60, 95);
                                $this->SetY(51);
                                $this->SetX(141);
                                $this->Writehtml(isset($comments[$j]) ? $comments[$j] :"");
                            }

                            $photoPiece2 = isset($photos[$j + 1]) ? $photos[$j + 1] : "";
                            if ($photoPiece2 != "" && file_exists($photoPiece2)) {
                                $this->Rect(9.5, 150, 125, 101, "solid");
                                $this->Image(URLROOT . "/public/documents/opportunite/$photoPiece2", 10, 151, 124, 100);
                                $this->SetFont('Arial', 'U', 12);
                                $this->SetTextColor(0, 0, 0);
                                $this->SetY(152);
                                $this->SetX(140);
                                $this->Cell(0, 0, "Commentaire :", 0, 0, 'J', false, '');
                                $this->SetFont('Arial', '', 11);
                                $this->Rect(140, 156, 60, 95);
                                $this->SetY(157);
                                $this->SetX(142);
                                $this->Writehtml(isset($comments[$j + 1]) ? $comments[$j + 1] :"");
                            }

                            $j += 2;

                            if ($j < count($photos)) {
                                $this->AddPage();
                            }
                        }
                    }
                } else {
                    $this->SetY(27);
                    $this->SetTextColor(0, 0, 0);
                    $this->SetFont('Times', '', 14);
                    $this->SetFillColor(169, 169, 169);
                    $this->Cell(190, 15,  iconv('UTF-8', 'windows-1252',  "Photos : " . $piece->nomPiece), 0, 0, 'L', true);
                    $this->Ln(70);
                    $this->SetFont('Times', '', 12);
                    $this->Cell(190, 20, iconv('UTF-8', 'windows-1252', "Aucune photo n'a été prise pour cette Piece !"), 0, 0, 'C');
                }

                //TRAITEMENT DES SUPPORTS
                foreach ($piece->listSupports as $key => $support) {
                    $this->AddPage();
                    //METRE
                    $sTotal =  ($support->longueurSupport != "" &&  $support->longueurSupport != null && $support->largeurSupport != "" &&  $support->largeurSupport != null ? round(($support->longueurSupport * $support->largeurSupport), 2) : 0);
                    //Calcul OUV
                    $sOuverture = 0;
                    $libOuv = "";
                    foreach ($support->listOuvertures as $key => $ouverture) {
                        $sOuverture +=  ($ouverture->longueurOuverture != "" &&  $ouverture->longueurOuverture != null && $ouverture->largeurOuverture != "" &&  $ouverture->largeurOuverture != null ? round(($ouverture->longueurOuverture * $ouverture->largeurOuverture), 2) : 0);
                        $libOuv .= $libOuv == "" ? $ouverture->libelleOuverture : ", " . $ouverture->libelleOuverture;
                    }
                    
                    $titreTraitement = $piecenum . "-3- Traitement de " .  $support->nomSupport  . (($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? " / Sinistré" : ""));
                    $this->Writehtml("<a name='" . $i . "'></a>");
                    $this->EntrerDonnee($i++, ($titreTraitement), 3);
                    $this->SetFont('Times', 'B', 14);
                    $this->SetFillColor(169, 169, 169);
                    $this->Bookmark($titreTraitement, 3);
                    $this->Cell(186, 10,  ($titreTraitement), 0, 0, 'L', true);
                    $this->Writehtml("<a name='" . $i . "'></a>");

                    $this->Ln(10);
                    $this->SetFont('Times', 'B', 14);
                    $this->Cell(186, 10,  ("-  Métrés du support"), 0, 0, 'L');
                    $this->Ln(10);
                    $this->SetFont('Times', '', 12);
                    $this->SetFillColor(240, 128, 128);
                    $this->Cell(61, 10,  (" Longueur (m)"), 1, 0, 'C', true);
                    $this->Cell(61, 10, (($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? "Largeur" : "Hauteur") . " (m)"), 1, 0, 'C', true);
                    $this->Cell(63, 10,  ("S. Totale (m2)"), 1, 0, 'C', true);
                    $this->Ln(10);
                    $this->Cell(61, 10,  ( $support->longueurSupport), 1, 0, 'C');
                    $this->Cell(61, 10, ( $support->largeurSupport), 1, 0, 'C');
                    $this->Cell(63, 10,  ($sTotal), 1, 0, 'C');
                    $this->Ln(20);
                    $this->Cell(61, 10,  ("S. Totale Ouv (m2)"), 1, 0, 'C', true);
                    $this->Cell(61, 10,  ("A Déduire"), 1, 0, 'C', true);
                    $this->Cell(63, 10,  ("S. à traiter (m2)"), 1, 0, 'C', true);
                    $this->Ln(10);
                    $this->Cell(61, 10,  ( $sOuverture), 1, 0, 'C');
                    $this->Cell(61, 10,  ( $support->siDeduire == "1" ? "OUI" : "NON"), 1, 0, 'C');
                    $this->Cell(63, 10,  (($support->siDeduire == "1" ? ($sTotal - $sOuverture) : $sTotal)), 1, 0, 'C');

                    //Taux d'Humidité
                    $this->Ln(20);
                    $this->SetFillColor(240, 128, 128);
                    $this->Cell(186, 10,  ("Taux d'Humidité"), 1, 0, 'L', true);
                    $this->Ln(10);
                    $this->MultiCell(186, 10,  (($support->tauxHumidite != null && $support->tauxHumidite != "" ? $support->tauxHumidite . '%' : "Non renseigné")), 1, 'C');

                    //COMMENTAIRE METRE SUPPORT
                    $this->Ln(10);
                    $this->SetFillColor(240, 128, 128);
                    $this->Cell(186, 10,  (" Commentaire sur les métrés du support"), 1, 0, 'L', true);
                    $this->Ln(10);
                    $this->MultiCell(186, 10,  ( $support->commentaireMetreSupport), 1, 'C');

                    //RESUME REVETEMENT
                    $libRev = "";
                    foreach ($support->listRevetements as $key => $rev) {
                        $libRev .= $libRev == "" ? $rev->libelleRevetement : ", " . $rev->libelleRevetement;
                    }
                    $this->Ln(10);
                    $this->SetFont('Times', 'B', 14);
                    $this->Cell(186, 10,  ("-  Revêtements"), 0, 0, 'L');
                    $this->SetFont('Times', '', 12);
                    $this->Ln(10);
                    $this->MultiCell(186, 10, ($libRev), 'B', 'L');

                    //RESUME OUVERTURE
                    $this->Ln(10);
                    $this->SetFont('Times', 'B', 14);
                    $this->Cell(186, 10,  ("-  Ouvertures"), 0, 0, 'L');
                    $this->SetFont('Times', '', 12);
                    $this->Ln(10);
                    $this->MultiCell(186, 10, (($libOuv == "" ? "Pas d'ouvertures sur ce support" : $libOuv)), 'B', 'L');

                    //COMMENTAIRE SUPPORT
                    $this->Ln(10);
                    $this->SetFillColor(240, 128, 128);
                    $this->Cell(186, 10,  (" Commentaire du support"), 1, 0, 'L', true);
                    $this->Ln(10);
                    $this->MultiCell(186, 10,  ( $support->commentaireSupport), 1, 'C');

                    // $this->Ln(10);
                    // $this->SetFillColor(240, 128, 128);
                    // $this->Cell(190, 10,  (" Commentaire revêtement"), 1, 0, 'L', true);
                    // $this->Ln(10);
                    // $this->MultiCell(190, 30,  ( $support->commentaireSupport), 1, 'C');

                    $i = 1;

                    //DETAIL REVETEMENT
                    if (sizeof($support->listRevetements) != 0) {
                        $this->AddPage();
                        $revet = $piecenum . "-3-" . $i++ . "- ";
                        $titreRevetement = $revet . "Détail des revêtements : " .  $support->nomSupport  . (($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? " / Sinistré" : ""));
                        $this->Writehtml("<a name='" . $i . "'></a>");
                        $this->EntrerDonnee($i++, ($titreRevetement), 4);
                        $this->SetFont('Times', 'B', 14);
                        $this->SetFillColor(169, 169, 169);
                        $this->Bookmark($titreRevetement, 4);
                        $this->Cell(190, 10,  ($titreRevetement), 0, 0, 'L', true);
                        $this->Writehtml("<a name='" . $i . "'></a>");
                        foreach ($support->listRevetements as $key => $rev) {
                            $totalRev =  ($rev->longueurRevetement != "" &&  $rev->longueurRevetement != null &&  $rev->longueurRevetement != "NULL" && $rev->largeurRevetement != "" &&  $rev->largeurRevetement != null && $rev->largeurRevetement != "NULL" ? round(($rev->longueurRevetement * $rev->largeurRevetement), 2) : 0);
                            $this->Ln(10);
                            $this->SetFont('Times', 'B', 14);
                            $this->Cell(190, 10,  ("-  $rev->libelleRevetement"), 0, 0, 'L');
                            $this->Ln(10);
                            $this->SetFont('Times', '', 12);
                            $this->SetFillColor(240, 128, 128);
                            $this->Cell(63, 10,  (" Longueur (m)"), 1, 0, 'C', true);
                            $this->Cell(63, 10, (($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? "Largeur" : "Hauteur") . " (m)"), 1, 0, 'C', true);
                            $this->Cell(64, 10,  ("S. Totale \n (m2)"), 1, 0, 'C', true);
                            $this->Cell(63, 10,  ( $rev->longueurRevetement), 1, 0, 'C');
                            $this->Cell(63, 10, ( $rev->largeurRevetement), 1, 0, 'C');
                            $this->Cell(64, 10,  ($totalRev), 1, 0, 'C');
                            $this->Ln(10);
                            $this->MultiCell(190, 10,  ( $rev->commentaireRevetement), 1, 'C');
                        }
                    }

                    //DETAIL OUVERTURE
                    if (sizeof($support->listOuvertures) != 0) {
                        $this->AddPage();
                        $ouvert = $piecenum . "-3-" . $i++ . "- ";
                        $titreOuverture = $ouvert . "Détail des ouvertures : " .  $support->nomSupport  . (($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? " / Sinistré" : ""));
                        $this->Writehtml("<a name='" . $i . "'></a>");
                        $this->EntrerDonnee($i++, ($titreOuverture), 4);
                        $this->SetFont('Times', 'B', 14);
                        $this->SetFillColor(169, 169, 169);
                        $this->Bookmark($titreOuverture, 4);
                        $this->EntrerDonnee($i++, ($titreOuverture), 4);
                        $this->Cell(190, 10,  ($titreOuverture), 0, 0, 'L', true);
                        $this->Writehtml("<a name='" . $i . "'></a>");
                        foreach ($support->listOuvertures as $key => $ouverture) {
                            $totalOuv =  ($ouverture->longueurOuverture != "" &&  $ouverture->longueurOuverture != null && $ouverture->largeurOuverture != "" &&  $ouverture->largeurOuverture != null ? round(($ouverture->longueurOuverture * $ouverture->largeurOuverture), 2) : 0);
                            $this->Ln(10);
                            $this->SetFont('Times', 'B', 14);
                            $this->Cell(190, 10,  ("-  $ouverture->libelleOuverture"), 0, 0, 'L');
                            $this->Ln(10);
                            $this->SetFont('Times', '', 12);
                            $this->SetFillColor(240, 128, 128);
                            $this->Cell(63, 10,  (" Largeur (m)"), 1, 0, 'C', true);
                            $this->Cell(63, 10, ("Hauteur (m)"), 1, 0, 'C', true);
                            $this->Cell(64, 10,  ("S. Totale \n (m2)"), 1, 0, 'C', true);
                            $this->Ln(10);
                            $this->Cell(63, 10,  ( $ouverture->longueurOuverture), 1, 0, 'C');
                            $this->Cell(63, 10, ( $ouverture->largeurOuverture), 1, 0, 'C');
                            $this->Cell(64, 10,  ($totalOuv), 1, 0, 'C');
                            $this->Ln(10);
                            $this->MultiCell(190, 10,  ( $ouverture->commentaireOuverture), 1, 'C');
                        }
                    }

                    $photoi = $piecenum . "-3-" . $i++ . "- ";
                    //PHOTOS SUPPORT
                    $this->AddPage();
                    $this->Writehtml("<a name='" . $i . "'></a>");
                    $this->EntrerDonnee($i++, ($photoi . "Photos : " .  $support->nomSupport  . (($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? " / Sinistré" : ""))), 4);
                    $this->Bookmark($photoi . "Photos : " .  $support->nomSupport  . (($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? " / Sinistré" : "")), 4);
                    if ($support->photosSupport != "" && $support->photosSupport != null) {
                        $photos = $support->photosSupport == "" ? [] : explode(";", $support->photosSupport);
                        $comments = $support->commentsPhotosSupport == "" ? [] : explode("}", $support->commentsPhotosSupport);
                        $j = 0;
                        
                        if (count($photos) > 0) {
                            while ($j < count($photos)) {
                                if ($j % 2 == 0) {
                                    $this->SetY(27);
                                    $this->SetTextColor(0, 0, 0);
                                    $this->SetFont('Times', 'B', 14);
                                    $this->SetFillColor(169, 169, 169);
                                    $this->Cell(190, 15, iconv('UTF-8', 'windows-1252', "Photos : " . $piece->nomPiece), 0, 0, 'L', true);
                                }
    
                                $photoPiece1 = isset($photos[$j]) ? $photos[$j] : "";
                                if ($photoPiece1 != "" && file_exists($photoPiece1)) {
                                    $this->Rect(9.5, 44.5, 125, 101, "solid");
                                    $this->Image(URLROOT . "/public/documents/opportunite/$photoPiece1", 10, 45, 124, 100);
                                    $this->SetFont('Arial', 'U', 12);
                                    $this->SetTextColor(0, 0, 0);
                                    $this->SetY(46);
                                    $this->SetX(140);
                                    $this->Cell(0, 0, "Commentaire :", 0, 0, 'J', false, '');
                                    $this->SetFont('Arial', '', 11);
                                    $this->Rect(140, 50, 60, 95);
                                    $this->SetY(51);
                                    $this->SetX(141);
                                    $this->Writehtml(isset($comments[$j]) ? $comments[$j] :"");
                                }

                                $photoPiece2 = isset($photos[$j + 1]) ? $photos[$j + 1] : "";
                                if ($photoPiece2 != "" && file_exists($photoPiece2)) {
                                    $this->Rect(9.5, 150, 125, 101, "solid");
                                    $this->Image(URLROOT . "/public/documents/opportunite/$photoPiece2", 10, 151, 124, 100);
                                    $this->SetFont('Arial', 'U', 12);
                                    $this->SetTextColor(0, 0, 0);
                                    $this->SetY(152);
                                    $this->SetX(140);
                                    $this->Cell(0, 0, "Commentaire :", 0, 0, 'J', false, '');
                                    $this->SetFont('Arial', '', 11);
                                    $this->Rect(140, 156, 60, 95);
                                    $this->SetY(157);
                                    $this->SetX(142);
                                    $this->Writehtml(isset($comments[$j + 1]) ? $comments[$j + 1] :"");
                                }
    
                                $j += 2;
    
                                if ($j < count($photos)) {
                                    $this->AddPage();
                                }
                            }
                        }
                    } else {
                        $this->SetY(27);
                        $this->SetTextColor(0, 0, 0);
                        $this->SetFont('Times', '', 14);
                        $this->SetFillColor(169, 169, 169);
                        $this->Cell(190, 15,  ($photoi . "Photos : " . $piece->nomPiece . " / " .  $support->nomSupport  . (($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? " / Sinistré" : ""))), 0, 0, 'L', true);
                        $this->Ln(70);
                        $this->SetFont('Times', '', 12);
                        $this->Cell(190, 20, ("Aucune photo n'a été prise pour ce support !"), 0, 0, 'C');
                    }
                    
                    $this->Ln(20);
                }
            }
        }

        //COMMENTAIRE CIRCONSTANCES
        $this->AddPage();
        $this->Writehtml("<a name='" . $i . "'></a>");
        $this->SetFont('Times', 'B', 14);
        $this->SetFillColor(240, 128, 128);
        $this->Bookmark("7- Commentaire et Circonstances du Sinistre", 1);
        $this->EntrerDonnee($i++, ("7- Commentaire et Circonstances du Sinistre"), 1);
        $this->Cell(190, 10,  ("7- Commentaire et Circonstances du Sinistre"), 1, 0, 'C', true);
        $this->Ln(10);
        $this->Cell(0, 0, "", 0, 0, 'L');
        $this->Writehtml((($this->rt ? $this->rt->precisionComplementaire : "")));
    }

    public function document() {
        //Numéro de page
        $this->AliasNbPages();

        $this->PageDeGarde();

        if ($this->sommaire != []) {
            $this->insertSommaire($this->sommaire, $this->nb_sommaire);
        }

        $this->CorpsAlgo();

        //SAVE COMPTE RENDU
        $nom =  $this->o->name . "_CompteRenduRT" . ".pdf";
        $this->Output($nom, 'F');
        $this->Output($nom, 'I');
        return $nom;
    }
}