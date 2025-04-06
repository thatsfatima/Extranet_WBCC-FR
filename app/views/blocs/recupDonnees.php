<?php
mb_internal_encoding('UTF-8');
$role = $_SESSION["connectedUser"]->role;
$viewAdmin = $role == "1" || $role == "2" || $role == "8" || $_SESSION["connectedUser"]->isAdmin == 1 ? "" : "hidden";
$readOnly =  $op != null ? "readonly" : '';
$hidden =  $op != null ? "" : "hidden";
$hiddenOP = ($_SESSION["connectedUser"]->libelleRole == "Gestionnaire EXTERNE") ? "hidden" : "";
$hiddenCommercial = ($role == '5' or $_SESSION["connectedUser"]->isCommercial == "1" or $_SESSION["connectedUser"]->isDirecteurCommercial == "1")  ? "hidden" : "";

$op->cie = ($cie) ? $cie : $op->cie;
$readonlyExpertCompany = isset($cabinetExpert) &&  $cabinetExpert ? "" : "readonly";
$whatsappInter = ($interlocuteur) ? (($interlocuteur->whatsapp != null && $interlocuteur->whatsapp != "" && strlen($interlocuteur->whatsapp) < 12) ? $interlocuteur->whatsapp : str_replace(' ', '', $interlocuteur->telContact)) : "";
$whatsappInter = $whatsappInter != "" ? (strlen($whatsappInter) > 9 ? "33" . substr($whatsappInter, strlen($whatsappInter) - 9, 9) : "33$whatsappInter") : "";

$hiddenTravaux = isset($activityDureeTravaux) && ($activityDureeTravaux) && $activityDureeTravaux->isCleared == "True"  ? true :  false;
$hiddenRVTravaux = isset($activityTravaux) && ($activityRVTravaux) && $activityRVTravaux->isCleared == "True"  ? true :  false;
$adresse = $interlocuteur ?  ($interlocuteur->emailContact != "" ? $interlocuteur->emailContact . ";" : "") : " ";
$adresse .= $op->cie ? (($op->cie->email != "" && !strpos($adresse, $op->cie->email)) ? $op->cie->email . ";" : "") : "";

$hiddenApp = $op->typeSinistre == "Partie commune exclusive" ? "hidden" : "";

$hiddenContact =  $op->contact ? "" : "hidden";
$hiddenNContact =  $op->contact ? "hidden" : "";

$hiddenPap = ($pap) ? "" : "hidden";


$hiddenComp =  $op->cie ? "" : "hidden";
$hiddenNComp =  $op->cie ? "hidden" : "";

$hiddenInter =  $interlocuteur ? "" : "hidden";
$hiddenNInter =  $interlocuteur ? "hidden" : "";

$resultActivityDE = isset($activityDE) && ($activityDE) ? $activityDE->isCleared :  "False";
$resultActivityTE = isset($activityTE) && ($activityTE) ? $activityTE->isCleared : "False";
$resultActivityRvRT = isset($activityRvRT) && ($activityRvRT) ? $activityRvRT->isCleared :  "False";

//INFO ASSUREUR
$checkedMRI = $op->typeSinistre == "Partie commune exclusive" ? "checked" : "";
$checkedMRH = $op->typeSinistre == "Partie commune exclusive" ? "" : "checked";
$checkedPNO = "";

$numPolice = "";
$numSinistre = "";
$dateSinistre = "";
$dateDebutContrat = "";
$dateFinContrat = "";

if ($checkedMRI == "checked") {
    $numPolice = $op->policeMRI;
    $numSinistre = $op->sinistreMRI;
    if ($immeuble) {
        $numPolice = ($numPolice == null || $numPolice == "") ? $immeuble->numPolice : $numPolice;
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
} else {
    $numPolice = $op->policeMRH;
    $numSinistre = $op->sinistreMRH;
    if ($app) {
        $numPolice = ($numPolice == null || $numPolice == "") ? $app->numPoliceOccupant : $numPolice;
        $dateDebutContrat = $app->dateEffetOccupant;
        $dateFinContrat = $app->dateEcheanceOccupant;
    }
}


$dateSinistre =  ($rt ? ($rt->date != null && $rt->date != "" && trim($rt->date) != "" ? $rt->date : ($rt->dateConstat != null && $rt->dateConstat != ""  && trim($rt->dateConstat) != "" ? $rt->dateConstat : ($rt->anneeSurvenance != null && $rt->anneeSurvenance != ""  && trim($rt->anneeSurvenance) != "" ? $rt->anneeSurvenance : ""))) : "");
$dateSinistre2 = $dateSinistre;
if ($dateSinistre != "" && substr($dateSinistre, 4, 1) == '-') {
    $date = new DateTime($dateSinistre);
    $dateSinistre = $date->format('d/m/Y');
}

$dateNaissance =  $op->contact ? ($op->contact->dateNaissance != null && $op->contact->dateNaissance != "" ? $op->contact->dateNaissance : "") : "";
$maxDateNaiss = date('Y-m-d', strtotime('18 years ago'));

$dateRVRT = "";
if ($op->rv) {
    $dateRVRTClass = new DateTime($op->rv->dateRV);
    $dateRVRT = $dateRVRTClass->format('Y-m-d');
}

$objet = "Déclaration de Sinistre et Confirmation de Mandat de Gestion avec cession de créance - Réference " . $op->name;

$commentaireRV = "";
if ($op->contact) {
    $commentaireRV .= "Nom : " . $op->contact->civiliteContact . " " . $op->contact->prenomContact . " " . $op->contact->nomContact;
    $commentaireRV .= "\nTel : " . $op->contact->telContact;
    $commentaireRV .= "\nEmail : " . $op->contact->emailContact;
}

if ($rt) {
    $commentaireRV .= "\nCommentaire : " . $rt->precisionComplementaire;
}

$adresse = $interlocuteur ?  ($interlocuteur->emailContact != "" ? $interlocuteur->emailContact . ";" : "") : " ";
$adresse .= $op->cie ? (($op->cie->email != "" && !strpos($adresse, $op->cie->email)) ? $op->cie->email . ";" : "") : "";


//TEST DELEGATION SIGNE
$delegationComplete =  ($dateSinistre != "" && $dateNaissance != "" && $numPolice != "" &&  $dateDebutContrat != "" && $dateFinContrat != "" && ($cie && $cie->name != "")) ? true : false;
$dateRVRT = "";
$heureDebutRVRT = "";
if ($op->rv) {
    $dateRVRTClass = new DateTime($op->rv->dateRV);
    $dateRVRT = $dateRVRTClass->format('d/m/Y');
    $heureDebutRVRT = $op->rv->heureDebut;
}


$hiddenBailleur = (
    ($prescripteur && (str_contains(strtolower($prescripteur->category), "bailleur social") != false || str_contains(strtolower($prescripteur->sousCategorieDO), "bailleur social") != false)) ||
    ($do && (str_contains(strtolower($do->sousCategorieDO), "bailleur social") || str_contains(strtolower($do->category), "bailleur social")))) ? "" : "hidden";
$objet = "Déclaration de Sinistre et Confirmation de Mandat de Gestion avec cession de créance - Réference " . $op->name;
//TELE EXPERTSISE
$listDommages =  ($op->rt) ?  explode(";", $op->rt->listDommages) : [];
$causes = ($op->rt && $op->rt->cause != null) ?  explode(";", $op->rt->cause) : [];
$precisions = ($op->rt && $op->rt->precisionDegat != null && $op->rt->precisionDegat != "") ? explode("}", $op->rt->precisionDegat) : [];
$precisionA = [];
$precisionE = [];
$precisionD = [];
$precisionI = [];
$autreCause = "";
$autrePrecisionA = "";
$autrePrecisionE = "";
$autrePrecisionD = "";
$autrePrecisionI = "";
$i = 0;

foreach ($causes as $key =>  $c) {
    $tab1 = isset($precisions[$key]) ?  explode(";",  $precisions[$key]) : [];
    if ($c == "Fuite") {
        $precisionA = $tab1;
        $i++;
        foreach ($precisionA as $p) {
            if ($p != "Le canalisation d'alimentation fuyarde : Oui" && $p != "Le canalisation d'alimentation fuyarde : Non" && $p != "Le canalisation d'alimentation fuyarde : Je ne sais pas" && $p != "Le canalisation d'évacuation fuyarde : Oui" && $p != "Le canalisation d'évacuation fuyarde : Non" && $p != "Le canalisation d'évacuation fuyarde : Je ne sais pas") {
                $autrePrecisionA = $p;
            }
        }
    } else if ($c == "Engorgement") {

        $precisionE = $tab1;
        $i++;
        foreach ($precisionE as $p) {
            if ($p != "L'engorgement provient d'une canalisation privative" && $p != "L'engorgement provient d'une canalisation commune") {
                $autrePrecisionE = $p;
            }
        }
    } else  if ($c == "Débordement") {
        $precisionD = $tab1;
        $i++;
        foreach ($precisionD as $p) {
            if ($p != "Toilettes bouchées" && $p != "Lave-vaisselle" && $p != "Lave-Linge" && $p != "Chauffe-Eau" && $p != "Ballon d'eau chaude") {
                $autrePrecisionD = $p;
            }
        }
    } else if ($c == "Infiltration") {

        $precisionI = $tab1;
        $i++;
        foreach ($precisionI as $p) {
            if ($p != "Infiltrations liées à la toiture et à la charpente" && $p != "Infiltrations par les murs extérieurs ou façade" && $p != "Infiltrations autour des fenêtres et des portes-fenêtres : Joints d'étanchéité" && $p != "Infiltrations autour des fenêtres et des portes-fenêtres : Cadres de fenêtres ou de portes-fenêtres" && $p != "Infiltrations d'eau à travers les terrasses ou balcons" && $p != "Infiltrations dans les sanitaires et les pièces d\'eau : Joints de baignoire, de douche ou de lavabo défectueux" && $p != "Infiltrations dans les sanitaires et les pièces d'eau : Joints de carrelage usés" && $p != "Infiltrations liées à la plomberie : Équipements sanitaires endommagés (toilettes, bidet, etc.)" && $p != "Infiltrations autour des fenêtres et des portes-fenêtres : Robinetterie fuyarde" && $p != "Infiltrations dues à la condensation : Mauvaise ventilation entraînant de la condensation sur les surfaces froides (Moisissure)" && $p != "Infiltrations dues à la condensation : Isolation insuffisante permettant la formation de points de rosée (Moisissure)") {
                $autrePrecisionI = $p;
            }
        }
    } else {

        $autreCause = $c;
    }
}

//chargement dommage
$libelleDommageMateriel = ($op->rt) ?  explode(";", $op->rt->libelleDommageMateriel) : [];
$idAuteurPAP = $pap ? $pap->idUserF : "";
$faireDelegation = $op->delegationSigne == "1" || $op->origine != "Extranet-PAP B2C" || $_SESSION["connectedUser"]->idUtilisateur == $idAuteurPAP || ($_SESSION["connectedUser"]->idUtilisateur != $idAuteurPAP && ($op->origine == "Extranet-PAP B2C" && date_diff(date_create($op->createDate), date_create(date("Y-m-d")))->format("%a")  > 2)) ? "1" : "0";


//DECLARATION

$hiddendecla = isset($rapportDelegation) && ($rapportDelegation == "" || $rapportDelegation == null) ? "hidden" : "";
$hiddenNdecla = isset($rapportDelegation) &&  ($rapportDelegation == "" || $rapportDelegation == null) ? "" : "hidden";


//INFOS MAIL
$dateSinistre =  ($rt ? ($rt->date != null && $rt->date != "" && trim($rt->date) != "" ? $rt->date : ($rt->dateConstat != null && $rt->dateConstat != ""  && trim($rt->dateConstat) != "" ? $rt->dateConstat : ($rt->anneeSurvenance != null && $rt->anneeSurvenance != ""  && trim($rt->anneeSurvenance) != "" ? $rt->anneeSurvenance : ""))) : "");
if ($dateSinistre != "" && substr($dateSinistre, 4, 1) == '-') {
    $date = new DateTime($dateSinistre);
    $dateSinistre = $date->format('d/m/Y');
}

$adresseOP = $op->adresse;
$infosLot = " l'appartement ";
$typeIntervention = $op->typeIntervention;
$emplacement =  "";
$description =  "";
$circonstances = "";
if (isset($pieces) && sizeof($pieces) != 0) {
    foreach ($pieces as $key => $piece) {
        $description = $description . $piece->libellePiece . ",";
        // if (sizeof($piece->listSupports) != 0) {
        //     $description = "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $piece->libellePiece . "<br>";
        //     foreach ($piece->listSupports as $key2 => $support) {
        //         $description .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - $support->libelleSupport";
        //         if (sizeof($support->listRevetements) != 0) {
        //             $description .= " (";
        //             foreach ($support->listRevetements as $key => $rev) {
        //                 $description .= "$rev->libelleRevetement" . ",";
        //             }
        //             $description .= ")";
        //         }
        //         $description .= " <br>";
        //     }
        // }
    }
}
$circonstances =  ($rt && $rt->precisionComplementaire != null && $rt->precisionComplementaire != "") ? $rt->precisionComplementaire : "";
$origine = $rt ? str_replace(';', ',', $rt->cause) : "";
$description = ($description == "" || $description == null) ? ($rt ? $rt->commentaireSinistre : "")  : $description;
$description = ($description == "" || $description == null) ? ($rt ? $rt->libellePieces : "")  : $description;
// $description = str_replace(",)", ")", $description);
// $description2 = $description != "" ?  str_replace("<br>", "\n", $description) : $description;
$hiddenTE = (($origine == "" || $description == "" || $dateSinistre == "")) ? "" : "hidden";

//COMPTE RENDU
$natureSinistre = ($rt) ? strtolower($rt->nature) : "";
$nomDO = ($op->contact ? $op->contact->civiliteContact . " " . $op->contact->fullName : "");
$titreDO = $op->contact ? strtolower($op->contact->statutContact) : "";
$adresse = ($immeuble) ? $immeuble->adresse : "";
$cp = ($immeuble) ? $immeuble->codePostal : "";
$ville = ($immeuble) ? $immeuble->ville : "";
$etage = ($app) ? $app->etage : "";
$codePorte = ($app) ? $app->codePorte : "";
$origineFuite = ($rt) ? $rt->cause : "";
