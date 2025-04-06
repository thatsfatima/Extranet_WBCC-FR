<script>
    const CONFIG = {
    routes: {
        section: {
            save: '<?= linkTo('GestionOP', 'saveSections') ?>'
        }
    }
};
</script>

<?php
include_once dirname(__FILE__) . '/../../blocs/recupDonnees.php';
$introduction = ($rt && $rt->introduction != null && $rt->introduction != "") ? $rt->introduction : "";
$contexte = ($rt && $rt->contexte != null && $rt->contexte != "") ? $rt->contexte : "";
$deroulementSeance = ($rt && $rt->deroulementSeance != null && $rt->deroulementSeance != "") ? $rt->deroulementSeance : "";
if ($rf) {
    $origineSinistre = "L'origine du sinistre a été identifiée comme une fuite provenant " . $rf->origineFuiteEquipement;

    if (str_contains($rf->origineFuiteSinistre, "Partie Commune")) {
        $origineSinistre .= " située dans les " . $rf->origineFuiteSinistre;
    } else {
        $origineSinistre .= " de l'appartement ";
        if ($voisin) {
            $origineSinistre .= "occupé par " . $voisin->fullName 
                . ", le voisin, causant des dégâts significatifs dans l'appartement de " 
                . $op->contactClient;
        } else {
            $origineSinistre .= "de " . $op->contactClient;
        }
    }
}
if ($introduction == "") {
    $introduction = "Nous avons été mandatés par $nomDO aux fins de gérer en son nom et pour son compte le sinistre intervenu à l’adresse citée ci-dessus.<br><br>Nous vous prions de trouver ci-dessous notre Compte Rendu relatif au Rendez-Vous Relevés Techniques qui s’est tenu le $dateRVRT à $heureDebutRVRT H sur les lieux du sinistre.
    ";
}
$conclusion = ($rt && $rt->conclusion != null && $rt->conclusion != "") ? $rt->conclusion : "";
if ($conclusion == "") {
    $conclusion = "En conclusion, ce rapport de releve technique a permis de documenter de manière détaillée le sinistre de " . $rt->nature . " survenu dans l'appartement de Monsieur/Madame " . $op->contactClient . ", situé au " . $adresse . ". Les observations et les relevés techniques effectués par notre expert ont été soigneusement analysés et ont servi de base pour l'évaluation des dommages.<br><br>"
    . $origineSinistre . ". 
    Les réparations nécessaires ont été chiffrées par notre artisan partenaire, France Travaux, sur la base de notre bordereau de prix mis à jour chaque année.<br><br>
    Les solutions de réparation proposées incluent la validation de notre évaluation, l'obtention d'une lettre d'acceptation de la compagnie d'assurance, et la clarification des modalités de règlement des indemnités. Nous avons également recommandé des mesures de prévention pour éviter que des incidents similaires ne se reproduisent à l'avenir.<br><br>
    Nous sollicitons la collaboration rapide et efficace de la compagnie d'assurance pour valider notre évaluation, formaliser l'accord via une lettre d'acceptation, et procéder au règlement des indemnités selon les modalités convenues. Cette démarche permettra de lancer rapidement les travaux de réparation, assurant ainsi que Monsieur/Madame [Nom du client] puisse retrouver un logement décent et salubre dans les plus brefs délais.<br><br>
    Nous restons à votre disposition pour toute information complémentaire et pour coordonner les prochaines étapes avec les différentes parties impliquées. SOS Sinistre WBCC Assistance s'engage à assurer une gestion efficace et proactive du sinistre, tout en mettant en œuvre des actions préventives pour garantir la sécurité et le bien-être des occupants.";
}
if ($contexte == "") {
}
$contexte =  "$nomDO est $titreDO de l’appartement situé au $adresse $cp à $ville au $etage étage à la porte $codePorte.<br><br>L’appartement subit des $origineFuite depuis le $dateSinistre.";

$descriptionSinistre = ($rt && $rt->descriptionSinistre != null && $rt->descriptionSinistre != "") ? $rt->descriptionSinistre : "";
if ($descriptionSinistre == "") {
}
$descriptionSinistre = "Le présent rapport concerne un sinistre de $natureSinistre survenu dans l'appartement de Monsieur/Madame " . $op->contactClient . ", situé au $adresse $cp à $ville, locataire de " . $cie->name . ". Le sinistre a été signalé le $dateSinistre, et le rendez-vous relevés techniques s’est tenu le $dateRVRT";
$origineSinistre1 = ($rt && $rt->origineSinistre != null && $rt->origineSinistre != "") ? $rt->origineSinistre : "";
if ($origineSinistre1 == "") {
    $origineSinistre1 = $origineSinistre . ".";
}
$interventionsInitiales = ($rt && $rt->interventionInitiales != null && $rt->interventionInitiales != "") ? $rt->interventionInitiales : "";
if ($interventionsInitiales == "") {
    $interventionsInitiales = "";
}
$description =  "<ol>";
if (sizeof($pieces) != 0) {
    foreach ($pieces as $keyP => $piece) {
        $description .= "<li>     " . ucfirst(str_replace('_', ' ', $piece->libellePiece)) . " : 
        <ul>";
        if (sizeof($piece->listSupports) != 0) {
            foreach ($piece->listSupports as $key2 => $support) {
                $description .= "<li>            * " . ucfirst(strtolower(str_replace('_', ' ', $support->libelleSupport))) . " en ";
                if (sizeof($support->listRevetements) != 0) {
                    foreach ($support->listRevetements as $key3 => $rev) {
                        $description .= strtolower($rev->libelleRevetement) . ",";
                    }
                }
                $description .= "</li>";
            }
        }
        $description .= "</ul></li>";
    }
    $description .= "</ol>";
}

if ($deroulementSeance == "") {
    $deroulementSeance = "Le $dateRVRT, le rendez-vous relevés techniques s’est tenu au $adresse $cp à $ville.<br><br>Sur place, l’expert de WBCC Assistance (SOS Sinistre) a fait le constat. <br><br> Les étapes suivantes détaillent le déroulement de la séance.";
}
setlocale(LC_TIME, 'fr_FR.UTF-8');
$dateInspec = ($rt->dateConstat != "" && $rt->dateConstat != null) ? $rt->dateConstat : $rdv->dateRV;
$inspectionAppartement = "L'inspection de l'appartement de Monsieur/Madame " . $op->contactClient . " a débuté à " . ($rt->heure ?? $rdv->heureDebut) . " le " . strftime("%A %d %B %Y", strtotime($dateInspec)) . ". L'expert, " . $rdv->expert . ", a procédé à une évaluation visuelle et technique des lieux afin de constater l'étendue des dommages causés par le sinistre.</br>
Observations principales :</br>" . $description . "</br>Remarques supplémentaires : L'inspection a permis de constater que...";
$elementsConfirmation = "<ul><li><strong>Source : </strong> Une fuite a été clairement identifiée comme provenant ";

if ($rf) {
    $sourceFuite = $rf->origineFuiteEquipement;

    if (str_contains($rf->origineFuiteSinistre, "Partie Commune")) {
        $sourceFuite .= " " . "située dans les " . $rf->origineFuiteSinistre;

    } else {
        $sourceFuite .= ", de l'appartement ";
        if ($voisin) {
            $sourceFuite .= "occupé par " . $voisin->fullName 
                . ", le voisin, causant des dégâts significatifs dans l'appartement de " 
                . $op->contactClient . ".";
        } else {
            $sourceFuite .= "de " . $op->contactClient . ".";
        }
    }
}

$elementsConfirmation .= $sourceFuite . "</li>"
. "<li><strong>Type de dommages :</strong> Les dommages constates (" . $rt->listDommages . ") sont typiques des degats causes par " . $rt->cause . "."
. "</li></ul>";
$confirmationNSinistre = "Suite à l'inspection de l'appartement, la nature du sinistre a été confirmée comme étant des $rt->nature. Les éléments suivants ont été déterminants pour cette confirmation :</br>$elementsConfirmation";

$hypotheses = "<strong>Hypothese 1 : Recherche de fuite realisee par un artisan mandate par WBCC Assistance</strong> </br>
<ul><li><strong>Source : </strong> Cette fuite provenait " . $sourceFuite . "</li><br><br>
<li><strong>Cause technique : </strong></li><br><br>
<li><strong>Confirmation technique : </strong> Un artisan mandaté par WBCC Assistance a réalisé un test ...</li></ul>";
$hypotheses .= "<strong>Hypothese 2 : Recherche de fuite realisee par un artisan mandate par le bailleur social</strong> </br>
<ul><li><strong>Source : </strong> Cette fuite provenait " . $sourceFuite . "</li><br><br>
<li><strong>Cause technique : </strong></li><br><br>
<li><strong>Confirmation technique : </strong> Le bailleur social " . $cie->name . " a realise la recherche de fuite et a fourni un rapport confirmant que ...</li></ul>";
$hypotheses .= "<strong>Hypothese 3 : Recherche de fuite realisee par le sinistre lui-meme</strong> </br>
<ul><li><strong>Source : </strong> Cette fuite provenait " . $sourceFuite . "</li><br><br>
<li><strong>Cause technique : </strong></li><br><br>
<li><strong>Confirmation technique : </strong> Monsieur/Madame " . $op->contactClient . " a lui-même réalisé la recherche de fuite et a identifié que ...</li></ul>";
$identificationOSnistre = "L'origine du sinistre a été clairement identifiée lors de l'inspection de l'appartement. Les éléments suivants ont permis de déterminer la cause précise (" . $rt->precisionDegat . ") :<br> $hypotheses";
$responsabilites = "<li>Responsabilité : La responsabilité incombe directement au " . (str_contains($rf->origineFuiteSinistre, "Partie Commune") ? "bailleur social" : ("locataire de l'appartement " . ($rf->chezVoisin && str_contains($rf->origineFuiteSinistre, "voisin")) ? ("du voisin : " . $voisin->fullName . ",") : ("de " . $op->contactClient))) . " pour la défectuosité " . $rf->origineFuiteEquipement . ". </li>";
$identificationResponsabilites = "L'identification des responsabilités dans le cadre du sinistre de " . $rt->nature . " survenu dans l'appartement de Monsieur/Madame " . $op->contactClient . " repose sur les éléments suivants, conformément à la convention IRSI (Indemnisation et Recours des Sinistres Immeuble).
Responsabilité directe :</br>"
. "<ul><li>";
if (str_contains($rf->origineFuiteSinistre, "Partie Commune")) {
    $identificationResponsabilites .= "Bailleur Social : La fuite provenant " . $sourceFuite . " implique directement le bailleur social " . $cie->name . " . La defectuosite " . $rf->origineFuiteEquipement . " est attribuee à une maintenance insuffisante, relevant de la responsabilite du bailleur social.";
} else {
    $identificationResponsabilites .= "Locataire de l'appartement " . (($rf->chezVoisin && str_contains($rf->origineFuiteSinistre, "voisin")) ? "du voisin : " : " : ") . " La fuite provenant " . $sourceFuite . " implique directement le locataire de cet appartement, relevant de la responsabilite du locataire." 
. "La defectuosite " . $rf->origineFuiteEquipement . " est attribuee à une maintenance insuffisante, relevant de la responsabilite du locataire.";
}
$identificationResponsabilites .= "</li></ul>";
$identificationResponsabilites .= "<br><br>Application de la convention IRSI : La convention IRSI est applicable pour les sinistres de dégâts des eaux survenus dans des immeubles en copropriété ou en gestion par un bailleur social. Elle permet de déterminer les responsabilités et les recours en fonction du montant des dommages. Selon cette convention, les sinistres sont classés en différentes tranches : 
<br><br>
<strong>Hypothèse 1 : Sinistre en tranche 1 (dommages inférieurs à 1600€ HT)</strong>
<ul><li><strong>Montant estimé des dommages HT :</strong> 1200 € HT</li>"
. $responsabilites
. "<li>Recours : Conformément à la convention IRSI, les dommages inférieurs à 1600€ HT sont gérés directement par l'assureur du sinistré. L'assureur de Monsieur/Madame " . $op->contactClient . " prendra en charge les réparations sans recours contre l'assureur du " . (str_contains($rf->origineFuiteSinistre, "Partie Commune") ? "bailleur social" : ("du locataire " . ($rf->chezVoisin && str_contains($rf->origineFuiteSinistre, "voisin")) ? ("voisin, " . $voisin->fullName . ",") : "")) . "</li></ul>"
. "<strong>Hypothèse 2 : Sinistre en tranche 2 (dommages compris entre 1600€ HT et 5000€ HT)</strong>
<li><strong>Montant estimé des dommages HT :</strong> 3500 € HT</li>"
. $responsabilites
. "<li>Recours : Pour les dommages compris entre 1 600€ HT et 5 000€ HT, l'assureur de Monsieur/Madame " . $op->contactClient . " indemnisera le sinistré et pourra exercer un recours contre l'assureur du " . (str_contains($rf->origineFuiteSinistre, "Partie Commune") ? "bailleur social" : ("du locataire " . ($rf->chezVoisin && str_contains($rf->origineFuiteSinistre, "voisin")) ? ("voisin, " . $voisin->fullName . ",") : "")) . "pour récupérer les frais engagés. </li></ul>
<strong>Hypothèse 3 : Sinistre en tranche 3 (dommages supérieurs à 5000€ HT)</strong>
<li><strong>Montant estimé des dommages HT :</strong> 7800 € HT</li>"
. $responsabilites
. "<li>Recours : Pour les dommages supérieurs à 5 000€ HT, une expertise complémentaire peut être requise pour valider les montants des dommages. L'assureur de Monsieur/Madame " . $op->contactClient . " indemnisera le sinistré et pourra exercer un recours contre l'assureur du " . (str_contains($rf->origineFuiteSinistre, "Partie Commune") ? "bailleur social" : ("du locataire " . ($rf->chezVoisin && str_contains($rf->origineFuiteSinistre, "voisin")) ? ("voisin, " . $voisin->fullName . ",") : "")) . ". La coordination entre les assureurs des différentes parties sera essentielle pour la prise en charge des réparations et des indemnités. </li></ul>
Documentation et communication : <ul>
<li>Rapport de recherche de fuite : La documentation de la recherche de fuite, qu'elle soit réalisée par un artisan mandaté par WBCC Assistance, le bailleur social, ou Monsieur/Madame " . $op->contactClient . ", doit être jointe pour appuyer les conclusions sur l'origine et la responsabilité de la fuite. </li>
<li>Rapport d'expertise : Ce rapport d'expertise, détaillant les observations et les conclusions, doit être transmis à toutes les parties impliquées (locataire " . (!str_contains($rf->origineFuiteSinistre, "Partie Commune" && $rf->chezVoisin && str_contains($rf->origineFuiteSinistre, "voisin")) ? "voisin" : "") . ", bailleur, assureur) pour faciliter la prise en charge et la résolution du sinistre. </li></ul>
Conclusion : La convention IRSI facilite la gestion des sinistres en établissant des procédures claires pour la détermination des responsabilités et des recours. Dans les trois hypothèses de sinistre mentionnées, la responsabilité principale incombe au " . (str_contains($rf->origineFuiteSinistre, "Partie Commune") ? "bailleur social" : ("" . ($rf->chezVoisin && str_contains($rf->origineFuiteSinistre, "voisin")) ? ("voisin, " . $voisin->fullName) : "proprietaire, " . $op->contactClient)) . ", en raison de la défectuosité " . $rf->origineFuiteEquipement . ". Les recours et la prise en charge des réparations seront adaptés en fonction de la tranche de sinistre.";
$descriptionDegats = "Lors de l'inspection de l'appartement, les dégâts suivants ont été constatés : </br>" . $description;
$evaluationDommages = "L'évaluation des dommages matériels et structurels dans l'appartement a été réalisée en tenant compte des observations détaillées lors de l'inspection et des relevés techniques effectués par notre expert.</br>
Suite à notre visite technique et aux relevés effectués sur site, nous procédons actuellement à la rédaction du rapport technique détaillé qui permettra d'établir le chiffrage précis des réparations nécessaires selon notre bordereau de prix en vigueur.</br><br>
<strong> Détails de l'évaluation : </strong>
<ul><li>Numéro Devis : " . ($devis ? $devis->numeroDevis : "") . "</li>
<li>Montant Total TTC : " . ($devis ? $devis->montantTotal :  "") . " €</li>
<li>Référence de dossier SOS SINISTRE : " . $op->name . "</li>
<li>Nature du sinistre : " . $rt->nature . "</li>
<li>Locataire : " . $op->contactClient . "</li>
<li>Police d'assurance : " . ($op->typeSinistre == 'Partie commune exclusive' ? $op->policeMRI : $op->policeMRH) . "</li>
<li>Numéro de sinistre : " . ($op->typeSinistre == 'Partie commune exclusive' ? $op->sinistreMRI : $op->sinistreMRH) . "</li></ul>
</br>" . $description;
$solutions = "<ol><li><strong>Solutions de reparation :</strong><br>
<ul>
<li><strong>Validation de l'evaluation :</strong><br>
Nous demandons à la compagnie d'assurance de valider notre évaluation des dommages telle que présentée dans ce rapport et dans le devis détaillé fourni par notre artisan partenaire France Travaux.
</li>
<li><strong>Lettre d'acceptation :</strong><br>
Si la compagnie d'assurance est d'accord avec notre évaluation, nous demandons de bien vouloir nous envoyer une lettre d'acceptation afin de formaliser l'accord. Cela nous permettra d'engager les travaux sans attendre l'encaissement effectif des fonds, assurant ainsi que l'assuré retrouve rapidement un logement décent et salubre.
</li>
<li><strong>Reglement des indemnites :</strong><br>
La compagnie d'assurance doit nous indiquer comment elle souhaite procéder au règlement des indemnités :
<ul><li>Paiement en une seule fois : Si la compagnie préfère régler la totalité en une seule fois, nous demandons de nous indiquer la date prévue pour ce règlement.</li>
<li>Paiement avant ou après travaux : La compagnie peut choisir de payer avant le début des travaux ou sur facture acquittée après leur achèvement.</li>
<li>Paiement en deux fois (immédiat et différé) : Si la compagnie opte pour un paiement en deux fois, nous demandons de procéder sans délai au règlement de l'immédiat pour nous permettre de commencer les travaux chez leur assuré, notre client commun.</li></ul>
</li>
</ul>
</li>
<li><strong>Solutions de prevention :</strong><br>
<ul>
<li><strong>Inspection reguliere :</strong><br>
Effectuer des inspections régulières des installations sanitaires, en particulier les joints d'étanchéité des baignoires, douches et autres équipements susceptibles de provoquer des infiltrations d'eau.
</li>
<li><strong>Maintenance proactive :</strong><br>
Remplacer les joints usés ou endommagés avant qu'ils ne causent des fuites. Cette maintenance proactive est essentielle pour prévenir des sinistres futurs.
</li>
<li><strong>Sensibilisation des occupants :</strong><br>
Sensibiliser les occupants sur l'importance de signaler immédiatement toute fuite ou signe d'humidité. Une intervention rapide peut souvent prévenir des dommages plus importants.
</li>
<li><strong>Amelioration des infrastructures :</strong><br>
Envisager des améliorations des infrastructures sanitaires, comme l'installation de systèmes de détection de fuite d'eau et de robinets d'arrêt automatiques.
</li>
</ul>
Ces conseils de prévention montrent que SOS Sinistre WBCC Assistance est engagé non seulement dans la gestion des sinistres, mais aussi dans la prévention de ceux-ci, contribuant ainsi à la sécurité et au bien-être des occupants.
</li></ol>
";
$pelliculesRT = $rt && $rt->documentFRT  != "" && $rt->documentFRT != "null" ?  explode(";", $rt->documentFRT) : [];
$photoImm = "déposer le fichier ici...";
if ($immeuble && $immeuble->photoImmeuble != null && $immeuble->photoImmeuble != "") {
    $photoImm = "<img id='photoImm' src='" . (URLROOT . "/public/documents/immeuble/$immeuble->photoImmeuble") . "' height='100%' width='100%' alt='IMAGE IMMEUBLE'>";
}

?>

<!-- modal CONFIRM COMPTE RT -->
<div class="modal fade" id="modalConfirmRT" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h3 class="text-dark font-weight-bold" id="textTerminerRT">
                </h3>
            </div>
            <div class="modal-footer">
                <div class="offset-8 col-md-1">
                    <button class="btn btn-danger" data-dismiss="modal">Non</button>
                </div>
                <div class="offset-1 col-md-1">
                    <button class="btn btn-success" onclick="saveInfosCompteRenduRT(1)">Oui</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal CONFIRM REJET FRT -->
<div class="modal fade" id="modalConfirmControlerRT" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h3 class="text-dark font-weight-bold" id="textControleRT">Voulez-vous confirmer le rejet de cette FRT
                    ?
                </h3>
                <div>
                    <div>
                        <textarea rows="5" class="form-control" id="commentaireControlerRT"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="offset-8 col-md-1">
                    <button class="btn btn-danger" data-dismiss="modal">Non</button>
                </div>
                <div class="offset-1 col-md-1">
                    <button class="btn btn-success" onclick="onConfirmControlerRT()">Oui</button>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
include_once dirname(__FILE__) . '/../../blocs/boitesModal.php';
?>

<?php
include_once dirname(__FILE__) . '/../../blocs/hiddenInput.php';
?>
<?php
include_once dirname(__FILE__) . '/../../blocs/titleBack.php';
?>

<input type="hidden" name="idOP" id="idOP" value="<?= $op->idOpportunity ?> ">
<input type="hidden" name="idRT" id="idRT" value="<?= $rt->idRT ?> ">
<input type="hidden" name="idSommaire" id="idSommaire" value="<?= $sommaire->idSommaire ?>">
<input type="hidden" name="sections" id="sections" value="<?= $sections ?> ">

<div class="row mb-2" <?= $checkedMRI == "checked" ? "" : "hidden" ?>>
    <?php
    $numberBloc = 0;
    include_once dirname(__FILE__) . '/../../blocs/donneurOrdre.php'; ?>
</div>
<div class="row mb-2">
    <div class="col">
        <?php
        $numberBloc = 1;
        include_once dirname(__FILE__) . '/../../blocs/contact.php';
        ?>
    </div>

    <div class="col ">
        <?php
        $numberBloc++;
        include_once dirname(__FILE__) . '/../../blocs/rapportTE.php';
        ?>
    </div>

    <div class="col ">
        <?php
        $numberBloc++;
        include_once dirname(__FILE__) . '/../../blocs/rapportFRT.php';
        ?>
    </div>
    <div class="col ">
        <?php
        $numberBloc++;
        include_once dirname(__FILE__) . '/../../blocs/opportunite.php';
        ?>
    </div>
</div>

<div class="row mb-2 mx-0">
    <div class="col-md-3">
        <?php
        $numberBloc++;
        include_once dirname(__FILE__) . '/../../blocs/imm_app.php';
        ?>
    </div>

    <div class="col-md-6">
        <?php
        $numberBloc++;
        include_once dirname(__FILE__) . '/../../blocs/documents.php';
        ?>
    </div>

    <div class="col-md-3">
        <div class="mb-3">
            <?php
            $numberBloc++;
            include_once dirname(__FILE__) . '/../../blocs/assurance.php'; ?>
        </div>
        <div class="mb-3">
            <?php
            $numberBloc++;
            include_once dirname(__FILE__) . '/../../blocs/notes.php'; ?>
        </div>
    </div>
</div>

<div class="row my-2 mx-0">
    <div class="col-md-12">
        <?php
            include_once dirname(__FILE__) . '/redactionCompteRenduRT.php';
        ?>
    </div>
</div>

<script src="<?= URLROOT ?>/public/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= URLROOT ?>/public/assets/js/tiny.js"></script>

<script type="text/javascript">
    var url = "<?= URLROOT ?>";
    var idRT = $("#idRT").val();
    var idSommaire = $("#idSommaire").val();
    var sections = <?= json_encode($sections) ?>;
    var files = [];
    var i = 1;
    var fileName = "";
    var Nbpage = 1;
    var nbrMnt = 1;

    var docs = [];
    var files = [];
    var photoImmeuble = "";
    let activeSection = null;

    let valueControleRT = 0;
    var activityCRT = <?= json_encode($activityCRT) ?>;
    
    var pieces = <?= json_encode($pieces) ?>;
    var maxIndex = <?= json_encode($i); ?>;
    var op = <?= json_encode($op) ?>;

</script>
<script src="<?= URLROOT ?>/public/assets/js/opportunite/compteRendu.js"></script>
<?php
include_once dirname(__FILE__) . '/../../blocs/functionBoiteModal.php';
?>
