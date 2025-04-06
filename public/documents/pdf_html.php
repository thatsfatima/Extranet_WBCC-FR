<?php
require_once '../../libs/vendor2/autoload.php';

use \Mpdf\Mpdf;

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

class PDF_HTML extends Mpdf
{
    //variables of html parser
    public $B;
    public $I;
    public $U;
    public $HREF;
    public $issetfont;
    public $issetcolor;
    public $align;
    public $counter;
    public $marginLeft;


    function __construct($config)
    {
        //Call parent constructor
        parent::__construct($config);
        //Initialization
        $this->B = 0;
        $this->I = 0;
        $this->U = 0;
        $this->HREF = '';
        $this->issetfont = false;
        $this->issetcolor = false;
        $this->align = 'J';
        $this->counter = 0;
        $this->marginLeft = false;
    }

    // function WriteHTML1($html)
    // {
    //     //HTML parser
    //     $html = strip_tags($html, "<b><u><i><a><img><p><br><strong><em><font><tr><blockquote><div><ul><ol><li><h1><h2><h3><h4><h5><h6><pre>"); //supprime tous les tags sauf ceux reconnus
    //     $html = str_replace("\n", ' ', $html); //remplace retour à la ligne par un espace
    //     $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE); //éclate la chaîne avec les balises
    //     foreach ($a as $i => $e) {
            
    //         if ($this->marginLeft !== false) {
    //             $this->SetLeftMargin($this->marginLeft);
    //         }

    //         if ($i % 2 == 0) {
    //             //Text
    //             if ($this->HREF) {
    //                 $this->PutLink($this->HREF, $e);
    //             }
    //             else {
    //                 $this->MultiCell(0, $this->FontSize + 2, iconv('UTF-8', 'windows-1252', txtentities($e)), 0, $this->align);
    //             }
    //         } else {
    //             //Tag
    //             if ($e[0] == '/') {
    //                 $this->CloseTag(strtoupper(substr($e, 1)));
    //             }
    //             else {
    //                 //Extract attributes
    //                 $a2 = explode(' ', $e);
    //                 $tag = strtoupper(array_shift($a2));
    //                 $attr = array();
                    
    //                 foreach ($a2 as $v) {
    //                     $v2 = explode('=', $v);
    //                     if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3)) {
    //                         if($a3[1] == 'style') {
    //                             // Split the style string into individual properties
    //                             $styles = explode(';', $a3[2]);
    //                             $s = explode('"', $e);
    //                             $s[1] = str_replace('px', '', trim($s[1]));
    //                             $arrayStyle = explode(';', $s[1]);
    //                             foreach($arrayStyle as $style) {
    //                                 if(trim($style) !== '') {
    //                                     list($property, $value) = explode(':', trim($style));
    //                                     $attr[strtoupper(trim($property))] = trim($value);
    //                                 }
    //                             }
    //                         }
    //                         else {
    //                             $attr[strtoupper($a3[1])] = $a3[2];
    //                         }
    //                     }
    //                 }
    //                 $this->OpenTag($tag, $attr);
    //             }
    //         }
    //     }
    // }

    // function forAttr($attr) {
    //     foreach ($attr as $key => $value) {
    //         switch ($key) {
    //             case 'TEXT-ALIGN':
    //                 if(trim($value) == 'center') $this->align = 'C';
    //                 else if(trim($value) == 'right') $this->align = 'R';
    //                 else if(trim($value) == 'left') $this->align = 'L';
    //                 else $this->align = 'J';
    //                 break;

    //             case 'PADDING-LEFT':
    //                 if(trim($value) != '') $this->marginLeft = ((int)$value)/4.78;
    //                 else $this->marginLeft = 20;
    //                 break;

    //             default:
    //                 // $this->marginLeft = 20;
    //                 $this->align = 'J';
    //                 break;
    //         }
    //     }
    // }

    // function OpenTag($tag, $attr)
    // {
    //     //Opening tag
    //     switch ($tag) {
    //         case 'P':
    //             $this->marginLeft = 20;
    //             $this->align = 'J';
    //             $this->SetFont('Arial', '', 12);
    //             break;
    //         case 'STRONG':
    //             $this->SetStyle('B', true);
    //             break;
    //         case 'EM':
    //             $this->SetStyle('I', true);
    //             break;
    //         case 'B':
    //         case 'I':
    //         case 'U':
    //             $this->SetStyle($tag, true);
    //             break;
    //         case 'A':
    //             $this->HREF = $attr['HREF'];
    //             break;
    //         case 'IMG':
    //             if (isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
    //                 if (!isset($attr['WIDTH']))
    //                     $attr['WIDTH'] = 0;
    //                 if (!isset($attr['HEIGHT']))
    //                     $attr['HEIGHT'] = 0;
    //                 $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
    //             }
    //             break;
            
    //         case 'TR':
    //         case 'BLOCKQUOTE':
    //         case 'BR':
    //             $this->Ln(3);
    //             break;
    //             break;
    //         case 'DIV':
    //             if (isset($attr['ALIGN']) && strtolower($attr['ALIGN']) == 'center') {
    //                 $this->Ln(5);
    //                 $this->Write(0, 0, '', 0, 0, 'C');
    //                 $this->Ln(5);
    //             }
    //             break;
    //         case 'HR':
    //             $this->Ln(2);
    //             $this->SetLineWidth(2);
    //             $this->Line(5, $this->GetY(), $this->w - 5, $this->GetY());
    //             $this->SetLineWidth(0.4);
    //             $this->Ln(2);
    //             break;
    //         case 'LI':
    //             $this->Write(0, 0, iconv('UTF-8', 'windows-1252', ($this->counter != false) ? $this->counter++ . '. ' : '°'), 0, 0, $this->align);
    //             $this->SetY($this->GetY() - 3);
    //             $this->setX($this->GetX() + 10);
    //             break;
    //         case 'OL':
    //             $this->counter = 1;
    //             $this->setX($this->GetX() + 10);
    //             $this->SetFont('', '', 12);
    //             $this->Write(0, 0, '', 0, 0, $this->align);
    //             break;
    //         case 'UL':
    //             $this->counter = false;
    //             $this->setX($this->GetX() + 10);
    //             $this->SetFont('', '', 12);
    //             $this->Write(0, 0, '', 0, 0, $this->align);
    //             break;
    //         case 'H1':
    //             $this->SetFont('', 'B', 20);
    //             $this->Ln(3);
    //             break;
    //         case 'H2':
    //             $this->SetFont('', 'B', 16);
    //             $this->Ln(3);
    //             break;
    //         case 'H3':
    //             $this->SetFont('', 'B', 14);
    //             $this->Ln(3);
    //             break;
    //         case 'H4':
    //             $this->SetFont('', 'B', 12);
    //             $this->Ln(3);
    //             break;
    //         case 'H5':
    //             $this->SetFont('', 'B', 10);
    //             $this->Ln(3);
    //             break;
    //         case 'H6':
    //             $this->SetFont('', 'B', 8);
    //             $this->Ln(3);
    //             break;
    //         case 'PRE':
    //             $this->Ln(3);
    //             $this->SetFont('Courier', '', 10);
    //             $this->SetTextColor(0, 0, 0);
    //             break;

    //     }
    //     $this->forAttr($attr);
    // }

    // function CloseTag($tag)
    // {
    //     //Closing tag
    //     if ($tag == 'STRONG')
    //         $tag = 'B';
    //     if ($tag == 'EM')
    //         $tag = 'I';
    //     if ($tag == 'B' || $tag == 'I' || $tag == 'U')
    //         $this->SetStyle($tag, false);
    //     if ($tag == 'A')
    //         $this->HREF = '';
    //     if ($tag == 'FONT') {
    //         if ($this->issetcolor == true) {
    //             $this->SetTextColor(0);
    //         }
    //         if ($this->issetfont) {
    //             $this->SetFont('arial');
    //             $this->issetfont = false;
    //         }
    //     }
    // }

    // function SetStyle($tag, $enable)
    // {
    //     //Modify style and select corresponding font
    //     $this->$tag += ($enable ? 1 : -1);
    //     $style = '';
    //     foreach (array('B', 'I', 'U') as $s) {
    //         if ($this->$s > 0)
    //             $style .= $s;
    //     }
    //     $this->SetFont('', $style);
    // }

    // function PutLink($URL, $txt)
    // {
    //     //Put a hyperlink
    //     $this->SetTextColor(0, 0, 255);
    //     $this->SetStyle('U', true);
    //     $this->Write($this->FontSize + 2, iconv('UTF-8', 'windows-1252', $txt), $URL);
    //     $this->SetStyle('U', false);
    //     $this->SetTextColor(0);
    // }

}