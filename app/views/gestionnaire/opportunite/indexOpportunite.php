<?php
// $hiddenFinal = (isset($etape) && $etape != "") ? "hidden" : "";
$hiddenTache = ($statut == "" || $statut == "enCours" || $statut == "attenteCloture" || $statut == "clotures" || $statut == "tous") ? "hidden" : "";
$active = "red";
$role = $_SESSION["connectedUser"]->role;
$idRole = $_SESSION["connectedUser"]->role;
$login = $_SESSION["connectedUser"]->login;
$link = ($aFaire == 'dc' || $aFaire == "sdc") ? "dc" : $aFaire;

$viewAdmin = (($idRole == "1" || $idRole == "2" || $idRole == "8" || $idRole == "25") && ($statut == "" || $statut == "enCours" || $statut == "attenteCloture" || $statut == "clotures" || $statut == "tous")) ? true : false;
$viewAdmin2 = (($idRole == "1" || $idRole == "2" || $idRole == "8" || $idRole == "9" || $idRole == 25 ||  $_SESSION["connectedUser"]->isAccessAllOP == "1")) ? "" : "hidden";

?>

<input type='text' id='idUtilisateur' class='form-control' value='<?= $_SESSION['connectedUser']->idUtilisateur ?>'
    hidden>
<div class="section-title">
    <div class="row">
        <div class="col-md-6">
            <h2><span><i class="fas fa-fw fa-folder" style="color: #c00000"></i></span> GESTION DES OPPORTUNITES</h2>
        </div>
        <?php
        if ($idRole == "1" || $idRole == "2" || $idRole == "3" || $idRole == "8" || $idRole == "25" || $_SESSION["connectedUser"]->isDirecteurCommercial == "1") { ?>
            <div class="col-md-6">
                <div class="float-right mt-0 mb-3">
                    <a type="button" rel="tooltip" title="Ajouter" href="<?= linkto('Gestionnaire', 'creation') ?>"
                        class="btn btn btn-sm btn-red  ml-1">
                        <i class="fas fa-folder-plus" style="color: #ffffff"></i>
                        Créer un dossier
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<div class="card mt-0">
    <div class="col-md-12">
        <div class="accordion-body pt-3 pb-3 pr-3" style="box-shadow: none !important;">
            <form method="GET" action="<?= linkTo('Gestionnaire', 'indexOpportunite', "") ?>">
                <div class="row" style="width: 100%;  margin: auto;">
                    <div class="<?= "col-md-2"  ?>">
                        <fieldset>
                            <legend
                                class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                TYPE OP </legend>
                            <div class="card ">
                                <select id="typeIntervention" name="typeIntervention" class="form-control"
                                    data-live-search="true">
                                    <option value="0" disabled selected>--Choisir--</option>
                                    <option value="AMO" <?= $typeIntervention == "AMO" ? "selected" : "" ?>>
                                        A.M.O.
                                    </option>
                                    <option value="SINISTRE" <?= $typeIntervention == "SINISTRE" ? "selected" : "" ?>>
                                        Sinistres
                                    </option>
                                    <option value="tous" <?= $typeIntervention == "tous" ? "selected" : "" ?>>
                                        Tous
                                    </option>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    <div class="<?= "col-md-2"  ?>">
                        <fieldset>
                            <legend
                                class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                ETAPE OP </legend>
                            <div class="card ">
                                <select id="statut" name="statut" class="form-control" data-live-search="true">
                                    <option value="" disabled selected>--Choisir--</option>
                                    </option>
                                    <option value="enCours" <?= $statut == "enCours" ? "selected" : "" ?>>En
                                        cours
                                    </option>
                                    <option value="attenteCloture" <?= $statut == "attenteCloture" ? "selected" : "" ?>>
                                        Attente de clôture</option>
                                    <option value="won" <?= $statut == "won" ? "selected" : "" ?>>
                                        Clôturés Gagnés
                                    </option>
                                    <option value="lost" <?= $statut == "lost" ? "selected" : "" ?>>
                                        Clôturés Perdus
                                    </option>
                                    <option value="tous" <?= $statut == "tous" ? "selected" : "" ?>>Tous
                                    </option>
                                    <?php
                                    $i = 0;
                                    foreach ($etapes as $et) {
                                        $i++;
                                    ?>
                                        <option <?= $statut == $et->codeActivity ? "selected" : "" ?>
                                            value="<?= $et->codeActivity ?>">
                                            <?= $i . "- " . $et->libelleActivity ?></option>
                                    <?php
                                    } ?>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    <div class="<?= "col-md-2" ?>">
                        <fieldset>
                            <legend
                                class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                site </legend>
                            <div class="card ">
                                <select id="typeOpSelected" name="site" class="form-control" data-live-search="true">
                                    <option value="0" disabled selected>--Choisir--</option>
                                    <!-- LISTE SITE -->
                                    <?php
                                    $i = 0;
                                    foreach ($sites as $sit) {
                                        $i++;
                                        if ((($idRole == "1" || $idRole == "2"  || $idRole == "9" || $idRole == "8" || $idRole == '4' || $idRole == '5' ||  $_SESSION["connectedUser"]->isAccessAllOP == "1") || (($idRole == "3" || $idRole == "25") && $_SESSION["connectedUser"]->nomSite == $sit->nomSite))) {
                                    ?>
                                            <option <?= $site == $sit->idSite ? "selected" : "" ?> value="<?= $sit->idSite ?>">
                                                <?= $sit->nomSite ?></option>
                                    <?php
                                        }
                                    } ?>
                                    <option value="tous" <?= $site == "tous" ? "selected" : "" ?>
                                        <?= $idRole == "1" || $idRole == "2"  || $idRole == "9" || $idRole == "8" ||  $_SESSION["connectedUser"]->isAccessAllOP == "1" ||  $_SESSION["connectedUser"]->isAccessAllOP == "1"  ?  "" : "hidden" ?>>
                                        Tous
                                    </option>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    <div class="<?= "col-md-2" ?>">
                        <fieldset>
                            <legend
                                class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                <?= "Gestionnaire" ?> </legend>
                            <div class="card">
                                <select id="gestionnaireSelected" name="gestionnaire" class="form-control"
                                    data-live-search="true">
                                    <option value="0" disabled selected>--Choisir--</option>
                                    <option value="tous" <?= $gestionnaire == "tous" ? "selected" : "" ?>
                                        <?= $idRole == "1" || $idRole == "2"  || $idRole == "9" || $idRole == "25" || $idRole == "8" ||  $_SESSION["connectedUser"]->isAccessAllOP == "1" ||  $_SESSION["connectedUser"]->isAccessAllOP == "1"  ?  "" : "hidden" ?>>
                                        Tous
                                    </option>
                                    <?php
                                    $i = 1;
                                    foreach ($gestionnaires as $ges) { {
                                    ?>
                                            <option <?= $gestionnaire == $ges->idUtilisateur ? "selected" : "" ?>
                                                value="<?= $ges->idUtilisateur ?>">
                                                <?= $ges->fullName ?></option>
                                    <?php
                                        }
                                    } ?>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    <div class="<?= "col-md-2"  ?>">
                        <fieldset>
                            <legend
                                class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                Date </legend>
                            <div class="card ">
                                <select name="periode" id="periode" class="form-control" onchange="onChangePeriode()">
                                    <option value="all" <?= $periode == "all" ? "selected" : "" ?>>Tous</option>
                                    <option value="today" <?= $periode == "today" ? "selected" : "" ?>>
                                        Aujourd'hui
                                    </option>
                                    <option value="semaine" <?= $periode == "semaine" ? "selected" : "" ?>>
                                        Semaine
                                        en
                                        cours
                                    </option>
                                    <option value="mois" <?= $periode == "mois" ? "selected" : "" ?>>Mois en
                                        cours
                                    </option>
                                    <option value="trimestre" <?= $periode == "trimestre" ? "selected" : "" ?>>
                                        Trismestre en cours
                                    </option>
                                    <option value="semestre" <?= $periode == "semestre" ? "selected" : "" ?>>
                                        Semestre en
                                        cours
                                    </option>
                                    <option value="annuel" <?= $periode == "annuel" ? "selected" : "" ?>>Année
                                        en
                                        cours
                                    </option>
                                    <option value="day" <?= $periode == "day" ? "selected" : "" ?>>A la date du
                                        :
                                    </option>
                                    <option value="perso" <?= $periode == "perso" ? "selected" : "" ?>>
                                        Personnaliser
                                    </option>
                                </select>
                            </div>
                        </fieldset>
                        <fieldset id="changeperso" <?= $periode == "perso" ||  $periode == "day" ? "" : "hidden" ?>>
                            <legend
                                class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                Personnaliser </legend>
                            <div class="card mt-0">
                                <div class="row mt-0">
                                    <div class="col-md-12" id="date1">
                                        <input type="date" name="date1" id="date1Input" value="<?= $date1 ?>"
                                            class="form-control">
                                    </div>
                                    <div class="col-md-12" id="date2" <?= $periode == "day" ? "hidden" : "" ?>>
                                        Au
                                        <input type="date" name="date2" id="date2Input" value="<?= $date2  ?>"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-md-2">
                        <fieldset>
                            <legend
                                class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                <?= "Commercial" ?> </legend>
                            <div class="card">
                                <select id="commercial" name="commercial" class="form-control" data-live-search="true">
                                    <option value="0" disabled selected>--Choisir--</option>
                                    <option
                                        <?= $idRole == "5" && $_SESSION["connectedUser"]->isDirecteurCommercial == "0" ? "hidden" : "" ?>
                                        value="tous" <?= $commercial == "tous" ? "selected" : "" ?>>
                                        Tous
                                    </option>
                                    <?php
                                    $i = 1;
                                    foreach ($commerciaux as $ges) { {
                                    ?>
                                            <option <?= $commercial == $ges->idUtilisateur ? "selected" : "" ?>
                                                value="<?= $ges->idUtilisateur ?>">
                                                <?= $ges->fullName ?></option>
                                    <?php
                                        }
                                    } ?>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-md-2 offset-5 col-xs-12">
                        <button type="submit" class="btn btn-primary form-control">FILTRER</button>
                    </div>
                </div>
                <input hidden name="demandeSignature" value="<?= $demandeSignature ?>">
                <input hidden name="declarationCie" value="<?= $declarationCie ?>">
            </form>
        </div>
    </div>

    <div class="modal-content mt-3">
        <div class="card-header bg-secondary text-white">
            <div class="row">
                <div
                    class="col-sm-12 <?= $aFaire == 'dc' || $aFaire == "constatDDE" || $aFaire == 'justificatifRF' ? "col-md-9" : "col-md-11" ?>">
                    <h2 class="text-center font-weight-bold"><?= $titre ?> (<?= sizeof($opportunities) ?>)<br> Montant
                        Total : <?= number_format($montantTotal, 2, ',', ' ')  ?> €</h2>
                </div>
                <div class=" col-sm-12 mb-2 <?= $aFaire == 'dc' ? "col-md-2" : "" ?>"
                    <?= $aFaire == 'dc' ? "" : "hidden" ?>>
                    <div class="row float-right">
                        <a class="btn btn-md btn-primary" href="#" id="alertsDropdown4" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-info-circle fa-fw"></i>
                        </a>
                        <!-- Dropdown - Alerts -->
                        <div class=" dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                            aria-labelledby="alertsDropdown4">
                            <div class="card-header bg-primary"></div>
                            <div class="card-body">
                                Veuillez trouver ici les opportunités en attente de déclaration de sinistre
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="col btn btn-primary btn-md ml-1 dropdown-toggle" type="button"
                                id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                ---Faites votre choix---
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" onclick="clickType('declarationCie','')">Tous
                                    les
                                    Opportunités à Déclarer</a>
                                <a class="dropdown-item" onclick="clickType('declarationCie','email')">Les
                                    Opportunités Déclarables par mail</a>
                                <a class="dropdown-item" onclick="clickType('declarationCie','tel')">Les
                                    Opportunités Déclarables par Téléphone</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" col-sm-12 mb-2 <?= $aFaire == 'constatDDE' || $aFaire == 'justificatifRF' ? "col-md-2" : "" ?>"
                    <?= $aFaire == 'constatDDE' || $aFaire == 'justificatifRF' ? "" : "hidden" ?>>
                    <div class="row float-right">
                        <a class="btn btn-md btn-primary" href="#" id="alertsDropdown4" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-info-circle fa-fw"></i>
                        </a>
                        <!-- Dropdown - Alerts -->
                        <div class=" dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                            aria-labelledby="alertsDropdown4">
                            <div class="card-header bg-primary"></div>
                            <div class="card-body">
                                Veuillez trouver ici les filtres pour les demande de signature
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="col btn btn-primary btn-md ml-1 dropdown-toggle" type="button"
                                id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                ---Faites votre choix---
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a <?= $demandeSignature == "" ? "selected" : "" ?> class="dropdown-item"
                                    onclick="clickType('demandeSignature','')">Tous</a>
                                <a class="dropdown-item" onclick="clickType('demandeSignature','0')">En
                                    Attente</a>
                                <a class="dropdown-item" onclick="clickType('demandeSignature','1')">Demandes
                                    Envoyées</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-1" <?= $idRole == "1" || $idRole == "2"   || $idRole == "8" ? "" : "hidden" ?>>
                    <div class="float-right mt-0 mb-0">
                        <a type="button" rel="tooltip" title="Ajouter" onclick='onClickExporter()'
                            class="btn btn btn-sm btn-success">
                            <i class="fas fa-print" style="color: #ffffff"></i>
                            Exporter
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body mt-0 pt-0">
            <div class="table-responsive">
                <div class="row"
                    <?= (($statut == "4" && $declarationCie == "email") || $viewAdmin) && sizeof($opportunities) != 0 ? "" : "hidden" ?>>
                    <div class="col-md-1 text-left float-left">
                        <input onclick="onCheckAll()" type="checkbox" class="form-control float-left" name="allChecked"
                            id="allChecked" value="all">
                    </div>
                    <div id="divBtnDeclarer" hidden>
                        <button type="button" rel="tooltip"
                            title="Faire la déclaration de compagnie pour les OP selectionnées"
                            onclick="onClickEnvoyerDeclaration()" class="btn btn-sm btn-info btn-simple">
                            <i class="fas fa-envelope" style="color: #ffffff"></i> Envoyer les déclarations
                        </button>
                    </div>
                    <div id="divBtnTransfererOP" hidden>
                        <input hidden id="viewAdmin" value="<?= $viewAdmin  ?>" type="text">
                        <button type="button" rel="tooltip"
                            title="Faire la déclaration de compagnie pour les OP selectionnées"
                            onclick="onClickTransfererOP()" class="btn btn-sm btn-info btn-simple" id="btnTransferer">
                            <i class="fas fa-envelope" style="color: #ffffff"></i> Transférer
                        </button>
                    </div>
                </div>
                <div class="large-table-fake-top-scroll-container-3">
                    <div>&nbsp;</div>
                </div>
                <div class="large-table-container-3">
                    <table class="table table-bordered" id="dataTable16" width="100%" cellspacing="0" name="tableOP">
                        <thead>
                            <tr>
                                <th <?= ($statut == "4" && $declarationCie == "email") || $viewAdmin ? "" : "hidden" ?>>
                                </th>
                                <th <?= ($statut == "4" && $declarationCie == "tel") ? "" : "hidden" ?>>Actions</th>
                                <th
                                    <?= ($statut == "4" && ($declarationCie == "email" || $declarationCie == "tel")) ? "hidden" : "" ?>>
                                    Actions</th>
                                <th></th>
                                <th>#</th>
                                <th>N° Dossier</th>
                                <th>Site</th>
                                <th>Gestionnaire</th>
                                <th>Type Sinistre</th>
                                <th>Etape</th>
                                <th>DO</th>
                                <th>N° Sinistre</th>
                                <th>N° Police</th>
                                <th>Assureur</th>
                                <th>Gestionnaire Imm/App</th>
                                <th>Montant Devis</th>
                                <th <?= $hiddenTache == "" ? "hidden" : "" ?>>Statut</th>
                                <th <?= $hiddenTache == "" ? "hidden" : "" ?>>Commercial</th>
                                <th <?= $hiddenTache == "" ? "hidden" : "" ?>>Type de dossier</th>
                                <th <?= $hiddenTache == "" ? "hidden" : "" ?>>Partie concernée</th>
                                <th <?= $hiddenTache == "" ? "hidden" : "" ?>>Date d'ouverture</th>
                                <th <?= $hiddenTache ?>>Planifié Pour</th>
                                <th <?= $hiddenTache ?>>Date Début</th>
                                <th <?= $hiddenTache ?>>Date Fin</th>
                                <th <?= $hiddenTache ?>>En Retard</th>
                                <th <?= $hiddenTache ?>>Nb Jours Retard</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            foreach ($opportunities as $op) {
                            ?>
                                <tr
                                    style="background-color: <?= (($op->etapeOp != null &&  $op->etapeOp != null && str_contains(strtolower($op->etapeOp), "rdv travaux")) ? "lightyellow;" : ($statut == "10" && isset($op->dateSuiviUlterieurDevis)  &&  $op->dateSuiviUlterieurDevis != null && $op->dateSuiviUlterieurDevis != "" ? "lightyellow" : ((($statut == "4" || $statut == "2") &&  $op->teleExpertiseFaite == 0 && $op->delegationSigne == 1 && $op->frtFait == 1) ? "orange;color:white" : (($op->demandeCloture == 1) ? 'lightgray' : ($op->incidentSignale == "1" ? 'indianred;color:white' : '')))))  ?>">
                                    <!-- mail -->
                                    <td style="text-align : center"
                                        <?= (($statut == "4" && $declarationCie == "email") || $viewAdmin) ? "" : "hidden" ?>>
                                        <input onclick="onCheckOne()" type="checkbox" class="oneselection" name="checkOP"
                                            value="<?= $op->idOpportunity . ";" . $op->gestionnaire ?>">
                                    </td>
                                    <td style="text-align : center"
                                        <?= ($statut == "4" && $declarationCie == "tel") ? "" : "hidden" ?>>
                                        <a type="button" rel="tooltip" title="Déclarer par téléphone"
                                            onclick="onClickLine('Gestionnaire', 'dc','<?= $op->idOpportunity ?>', '')"
                                            class="ml-1 btn btn-sm btn-info btn-simple btn-link">
                                            <i class="fas fa-folder-open" style="color: #ffffff"></i>
                                        </a>
                                    </td>
                                    <!-- Fin mail -->
                                    <td style="text-align : center"
                                        <?= (($statut == "4" && ($declarationCie == "email" || $declarationCie == "tel"))) ? "hidden" : "" ?>>
                                        <a type="button" rel="tooltip" title="Detail"
                                            onclick="onClickLine('Gestionnaire', '<?= $link ?>','<?= $op->idOpportunity ?>', '<?= $etapeControle ?>')"
                                            class="btn btn-sm btn-info btn-simple btn-link">
                                            <i class="fas fa-folder-open" style="color: #ffffff"></i>
                                        </a>
                                    </td>
                                    <td style="text-align : center">
                                        <a type="button" rel="tooltip" title="Notes"
                                            onclick="onClickNote('<?= $op->idOpportunity ?>')"
                                            class="btn btn-sm btn-info btn-simple btn-link">
                                            <i class="fas fa-sticky-note" style="color: #ffffff"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <?= $i++ ?>
                                    </td>
                                    <td>
                                        <?= $op->name ?></td>
                                    <td>
                                        <?= isset($op->nomSite) ? $op->nomSite : "" ?></td>
                                    <td>
                                        <?= isset($op->fullName) ? $op->fullName : "" ?></td>
                                    <td>
                                        <?= $op->typeIntervention ?></td>
                                    <td>
                                        <?= $op->etapeOp ?></td>
                                    <td>
                                        <?= $op->contactClient ?></td>
                                    <td>
                                        <?= $op->typeSinistre == "Partie commune exclusive" ? $op->sinistreMRI : $op->sinistreMRH ?>
                                    </td>
                                    <td>
                                        <?= $op->typeSinistre == "Partie commune exclusive" ? $op->policeMRI : $op->policeMRH ?>
                                    </td>
                                    <td>
                                        <?= $op->typeSinistre == "Partie commune exclusive" ? $op->denominationComMRI : $op->denominationComMRH ?>
                                    </td>
                                    <td>
                                        <?= $op->nomGestionnaireAppImm ?></td>
                                    <td
                                        data-order="<?= $op->montantOp != null && $op->montantOp != "" ? (float)$op->montantOp : 0 ?>">
                                        <?= $op->montantOp != null && $op->montantOp != "" ? number_format($op->montantOp, 2, ',', ' ')  : 0  ?>
                                        €</td>

                                    <td <?= $hiddenTache == "" ? "hidden" : "" ?>>
                                        <?= ($op->status == 'Lost' ? 'Clôturé Perdu' : ($op->status == 'Won' ? 'Clôturé Gagné' : ($op->status == 'Inactive' ? 'Inactif' : ($op->demandeCloture == '1' ? "Demande de Clôturer" : "Ouvert")))) ?>
                                    </td>
                                    <td <?= $hiddenTache == "" ? "hidden" : "" ?>>
                                        <?= $op->commercial ?></td>
                                    <td <?= $hiddenTache == "" ? "hidden" : "" ?>>
                                        <?= $op->type ?></td>
                                    <td <?= $hiddenTache == "" ? "hidden" : "" ?>>
                                        <?= $op->typeSinistre ?></td>
                                    <td <?= $hiddenTache == "" ? "hidden" : "" ?>
                                        data-sort="<?= strtotime(str_replace('/', '-', $op->createDateOP)) ?>">
                                        <?= date('d/m/Y', strtotime(str_replace('/', '-', $op->createDateOP))) ?>
                                    </td>
                                    <td <?= $hiddenTache ?>>
                                        <?= isset($op->organizer) ? $op->organizer : "" ?></td>
                                    <td <?= $hiddenTache ?>>
                                        <?= isset($op->startTime) ? date('d/m/Y', strtotime(str_replace('/', '-', $op->startTime)))  : "" ?>
                                    </td>
                                    <td <?= $hiddenTache ?>>
                                        <?= isset($op->endTime) ?  date('d/m/Y', strtotime(str_replace('/', '-', $op->endTime))) : "" ?>
                                    </td>
                                    <td <?= $hiddenTache ?>>
                                        <?= isset($op->endTime) ? (date($op->endTime) < date('Y-m-d') && date_diff(date_create($op->endTime), date_create(date("Y-m-d")))->format("%a") > 0 ? 'Oui' : 'Non') :  "" ?>
                                    </td>
                                    <td <?= $hiddenTache ?> class="text-danger font-weight-bold">
                                        <?= isset($op->endTime) ? (date($op->endTime) < date('Y-m-d') && date_diff(date_create($op->endTime), date_create(date("Y-m-d")))->format("%a") > 0 ? date_diff(date_create($op->endTime), date_create(date("Y-m-d")))->format("%a jours") : '') : "" ?>
                                    </td>
                                </tr>
                            <?php    }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal Confirmation Déclaration -->
<div class="modal fade" id="modalConfirmDeclaration" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h3 class="text-black font-weight-bold">Voulez-vous faire la déclaration de sinistre pour les OP
                    selectionnées ?</h3>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-md-6">
                        <button class="btn btn-danger" data-dismiss="modal">Non</button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-success" onclick="sendDeclaration()">Oui</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal EMAIL -->
<div class="mt-0 modal fade" id="modalEmail" data-backdrop="static" tabindex="-1" style="overflow:scroll">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h2 class="text-center font-weight-bold" id="modalTitle">APERCU DECLARATION DE SINISTRE</h2>
            </div>
            <div class="modal-body">
                <div class='form-group'>
                    <label class='col-md-12' for=''>Destinataires</label>
                    <input type='email' id="emailDestinataire" name='' class='form-control col-md-12' value='' readonly>
                </div>
                <div class='form-group'>
                    <label class='col-md-12' for='' id="libelleCC">CC: Assuré</label>
                    <input type='email' id="emailAssure" name='' class='form-control col-md-12' value='' readonly>
                </div>
                <div class='form-group'>
                    <label for=''>Objet</label>
                    <textarea cols='30' rows='2' id='objet' value='' class='form-control' readonly></textarea>
                </div>
                <div class='form-group'>
                    <label for=''>Message</label>
                    <div style="height:330px;overflow:scroll">
                        <textarea rows='5' name='emailText' id='emailText' rows='40' class='text-justify w-100'
                            style='border: none;outline: none; width:100%;text-align: justify;font-size: 14px;font-family: serif;'>

                            </textarea>

                    </div>

                </div>
                <div class='mb-0 form-group row px-2' id="delegationFile">

                </div>
            </div>
            <div class="modal-footer">
                <div class="offset-6 col-md-2">
                    <button class="btn btn-danger" onclick="annulerMailOne()">Annuler</button>
                </div>
                <div class="offset-1 col-md-2">
                    <button class="btn btn-success" onclick="sendMailOne()">Valider</button>
                </div>
            </div>

        </div>
    </div>
</div>



<!-- les modal pour choisir gestionnaire -->
<div class="modal" id="modalGestionnaires" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h2 class="text-center font-weight-bold">Choisissez un gestionnaire</h2>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable10" width="100%" cellspacing="0">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Nom </th>
                                <th>Prenom </th>
                                <th>Matricule</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            foreach ($gestionnaires as $ges) {
                            ?>
                                <tr>
                                    <td style="text-align : center">
                                        <input onclick="onCheckOne()" type="radio" class="oneselection" name="checkGes"
                                            value="<?= $ges->idUtilisateur ?>">
                                    </td>
                                    <td>
                                        <?= $i++ ?></td>
                                    <td>
                                        <?= $ges->prenomContact ?></td>
                                    <td>
                                        <?= $ges->nomContact ?></td>
                                    <td>
                                        <?= $ges->matricule ?></td>
                                    <td>
                                        <?= $ges->libelleRole ?></td>
                                </tr>
                            <?php    }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" type="submit" class="btn btn-danger" data-dismiss="modal">Annuler</a>
                <a href="javascript:void(0)" onclick="onClickValidTransfert()" type="submit"
                    class="btn btn-success">Valider</a>
            </div>
        </div>
    </div>
</div>

<!-- LIST NOTES -->
<div class="modal" id="modalListeNote" data-backdrop="static" tabindex="-1" data-dismiss="modal">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="card">
                <div class="card-header bg-secondary">
                    <div class="row">
                        <div class="col-md-10">
                            <h4 class="text-center font-weight-bold mt-2 text-white" id="titreNote">Liste des Notes</h4>
                        </div>
                        <div class="col-md-2">
                            <button class="close text-danger" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    </div>


                </div>
                <div class="table-responsive mt-2">
                    <table class="table table-bordered" id="tabledata" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th></th>
                                <th>Note</th>
                                <th>Date</th>
                                <th>Auteur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>#</th>
                                <th></th>
                                <th>Note</th>
                                <th>Date</th>
                                <th>Auteur</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>




<!-- modal Confirmation TRANSFERT -->
<div class="modal fade" id="modalConfirmTransfert" data-backdrop="static">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h3 class="text-black font-weight-bold">Voulez-vous transférer les dossiers selectionnés ?</h3>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-md-6">
                        <button class="btn btn-danger" data-dismiss="modal">Non</button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-success" onclick="transfertOP()">Oui</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include_once dirname(__FILE__) . '/../../blocs/boitesModal.php';
?>

<script type="text/javascript">
    let listOP = [];
    let indexOP = 0;

    function onChangePeriode() {
        if ($("#periode option:selected").val() == "perso" || $("#periode option:selected").val() == "day") {
            $("#changeperso").removeAttr("hidden");
            if ($("#periode option:selected").val() == "perso") {
                $("#date2").removeAttr("hidden");

                $("#date1").removeClass("col-md-12");
                $("#date1").addClass("col-md-12");
            } else {
                $("#date2").attr("hidden", "hidden");
                $("#date1").removeClass("col-md-12");
                $("#date1").addClass("col-md-12");
            }
        } else {
            $("#changeperso").attr("hidden", "hidden");
        }
    }

    function onClickExporter() {
        var table = $('#dataTable16').DataTable();
        // console.log(table.rows({
        //     filter: 'applied'
        // }).data())
        // return
        let value = "<table>";
        //GET HEADER
        var headers = table.columns().header().map(d => d.textContent).toArray();
        value += "<tr>";
        for (var i = 0; i < headers.length; i++) {
            if (i > 3) {
                let cell = headers[i]
                value += "<th>" + cell.toString().trim() + "</th>";
            }
        }
        value += "</tr>";

        //GET ROWS
        var rows = table.rows({
            filter: 'applied'
        }).data();
        for (var i = 0; i < rows.length; i++) {
            let row = rows[i];
            value += "<tr>";
            for (var j = 0; j < headers.length; j++) {
                if (j > 3) {
                    let cell = row[j]
                    value += "<td>" + (cell.display != undefined ? cell.display : cell) + "</td>";
                }
            }
            value += "</tr>";
        }
        value += "</table> ";
        post = {
            htmlTable: value,
            fileName: "opportunites_" + "<?= $statut ?>"
        }
        //CALL FUNCTION TO SAVE
        $.ajax({
            url: `<?= URLROOT ?>/public/json/export/exportXLS.php`,
            type: 'POST',
            data: post,
            dataType: "JSON",
            beforeSend: function() {
                $("#msgLoading").text("Export en cours...");
                $("#loadingModal").modal("show");
            },
            success: function(response) {
                console.log(response)
                setTimeout(() => {
                    $("#loadingModal").modal("hide");
                }, 500);
                $("#msgSuccess").text("Données exportées avec succés !!!");
                $('#successOperation').modal('show');
                document.location.href = `<?= URLROOT ?>/public/json/export/` + response;
            },
            error: function(response) {
                console.log(response);
                setTimeout(() => {
                    $("#loadingModal").modal("hide");
                }, 500);
                $("#msgError").text("Impossible d'exporter les données !!!");
                $('#errorOperation').modal('show');
            },
            complete: function() {},
        });

    }

    $(function() {
        var tableContainer = $(".large-table-container-3");
        var table = $(".large-table-container-3 table");
        var fakeContainer = $(".large-table-fake-top-scroll-container-3");
        var fakeDiv = $(".large-table-fake-top-scroll-container-3 div");

        var tableWidth = table.width() + 150;
        fakeDiv.width(tableWidth);

        fakeContainer.scroll(function() {
            tableContainer.scrollLeft(fakeContainer.scrollLeft());
        });
    })

    function onClickNote(idOP) {
        $.ajax({
            url: `<?= URLROOT ?>/public/json/note.php?action=getByIdOP&idOpportunity=` + idOP,
            type: 'GET',
            dataType: 'json',
            beforeSend: function() {},
            success: function(response) {
                // console.log("success");
                // console.log(response);
                let name = response != undefined && response != null && response.length != 0 ? response[0]
                    .name : ""
                $("#titreNote").text("Liste des Notes : " + name);
                $('#tabledata').DataTable({
                    "Processing": true, // for show progress bar
                    "serverSide": false, // for process server side
                    "filter": true, // this is for disable filter (search box)
                    "orderMulti": true, // for disable multiple column at once
                    "bDestroy": true,
                    'iDisplayLength': 5,
                    "data": response,
                    "columns": [{
                            "data": "index"
                        },
                        {
                            'render': function(data, type, row, meta) {
                                return '<a type="button" rel="tooltip" title="Voir la note" onclick="ShowModalNote(' +
                                    row.idNote +
                                    ', \'edit\')" class="btn btn-sm btn-info btn-simple btn-link"> <i class="fa fa-eye" aria-hidden="true"></i> </a>';
                            }
                        },
                        {
                            'render': function(data, type, row, meta) {
                                return row.plainText != null && row.plainText.length > 50 ? row
                                    .plainText.substring(0, 50) : row.plainText;
                            }
                        },
                        {
                            "data": "dateNote"
                        },
                        {
                            "data": "auteur"
                        }
                    ]
                });

                $('#modalListeNote').modal('show');
            },
            error: function(response) {
                console.log(response);
            },
            complete: function() {

            },
        });
    }

    function clickType(type, value) {
        let newUrl =
            '<?= URLROOT ?>' +
            "/Gestionnaire/indexOpportunite?typeIntervention=<?= $typeIntervention ?>&statut=<?= $statut ?>&site=<?= $site ?>&gestionnaire=<?= $gestionnaire ?>&periode=<?= $periode ?>&date1=<?= $date1 ?>&date2=<?= $date2 ?>&commercial=<?= $commercial ?>";
        if (type == "demandeSignature") {
            newUrl += "&demandeSignature=" + value + "&declarationCie=<?= $declarationCie ?>";
        } else {
            if (type == "declarationCie") {
                newUrl += "&demandeSignature=<?= $demandeSignature ?>" + "&declarationCie=" + value;
            }
        }
        location.href = newUrl;
    }

    function onClickLine(ctrl, lien, idOP, etape) {
        //SEARCH ETAPE
        let lien2 = "<?= URLROOT ?>" + "/" + ctrl + "/" + lien + "/" + idOP;
        if (etape != "") {
            lien2 += "/" + etape;
        }
        // console.log(lien2);
        $.ajax({
            url: `<?= URLROOT ?>/public/json/userAccess.php?action=findByLien&idUser=` +
                `<?= $_SESSION['connectedUser']->idUtilisateur ?>`,
            type: 'POST',
            data: JSON.stringify(lien2),
            dataType: "JSON",
            beforeSend: function() {},
            success: function(response) {
                // console.log("success");
                // console.log(response);
                if (response != null && response != undefined) {
                    if (response == false) {
                        location.href = lien2;
                    } else {
                        $("#msgSuccess").text("Tache en cours de traitement par : " + response.nomUser);
                        $('#successOperation').modal('show');
                    }
                } else {
                    $("#msgError").text("Erreur !!!");
                    $('#errorOperation').modal('show');
                }
            },
            error: function(response) {
                // console.log("Error");
                // console.log(response);
                $("#msgError").text("Erreur !!!");
                $('#errorOperation').modal('show');
            },
            complete: function() {},
        });
    }

    function onCheckAll() {
        var all = document.getElementsByName('allChecked');
        let checked = all[0].checked;
        var one = document.getElementsByName('checkOP');
        one.forEach(element => {
            element.checked = false;
        });
        let btnTransferer = document.getElementById("btnTransferer");
        btnTransferer.innerHTML = "<i class='fas fa-envelope' style='color: #ffffff'></i> Transférer(" + one.length + ")"
        if (checked) {
            //Check ALL
            one.forEach(element => {
                element.checked = true;
            });
            if ($('#viewAdmin').val() == "1") {
                $("#divBtnTransfererOP").removeAttr("hidden");
            } else {
                $("#divBtnDeclarer").removeAttr("hidden");
            }
        } else {
            $("#divBtnDeclarer").attr("hidden", "hidden")
            $("#divBtnTransfererOP").attr("hidden", "hidden")
            one.forEach(element => {
                element.checked = false;
            });
        }
    }

    function onCheckOne() {
        let postMail = {};
        var one = document.getElementsByName('checkOP');
        let isChecked = false;
        let checkAll = true;
        let i = 0;
        for (let index = 0; index < one.length; index++) {
            const element = one[index];
            if (element.checked) {
                i++;
                isChecked = true;
            } else {
                checkAll = false;
            }
        }

        if (isChecked) {
            if ($('#viewAdmin').val() == "1") {
                $("#divBtnTransfererOP").removeAttr("hidden");
            } else {
                $("#divBtnDeclarer").removeAttr("hidden");
            }
        } else {
            $("#divBtnDeclarer").attr("hidden", "hidden")
            $("#divBtnTransfererOP").attr("hidden", "hidden")
        }
        var all = document.getElementsByName('allChecked');
        let btnTransferer = document.getElementById("btnTransferer");
        btnTransferer.innerHTML = "<i class='fas fa-envelope' style='color: #ffffff'></i> Transférer(" + i + ")"
        if (checkAll) {
            all[0].checked = true;
        } else {
            all[0].checked = false;
        }
    }

    /** OP **/
    function onClickTransfererOP() {
        $("#modalGestionnaires").modal('show');
    }

    function onClickValidTransfert() {
        let idGes = $('input[name="checkGes"]:checked').val();
        if (idGes == undefined) {
            //ERROR MESSAGE
            $("#msgError").text("Veuillez selectionner un gestionnaire !");
            $('#errorOperation').modal('show');
        } else {
            //CONFIRMATION
            $("#modalConfirmTransfert").modal("show");
        }
    }

    function transfertOP() {
        let idGes = $('input[name="checkGes"]:checked').val();
        //
        var one = document.getElementsByName('checkOP');
        let nbCheck = 0;
        let tabIdOP = [];
        for (let index = 0; index < one.length; index++) {
            const element = one[index];
            if (element.checked) {
                nbCheck++;
                let idOP = element.value;
                tabIdOP.push(idOP);
            }
        }
        let post = {
            idAuteur: `<?= $_SESSION['connectedUser']->idUtilisateur ?>`,
            auteur: `<?= $_SESSION['connectedUser']->fullName ?>`,
            numeroAuteur: `<?= $_SESSION['connectedUser']->numeroContact ?>`,
            tabIdOP: tabIdOP
        }
        // console.log(tabIdOP);
        $.ajax({
            url: `<?= URLROOT ?>/public/json/opportunity.php?action=tansfertOPToGestionnaire&idGestionnaire=` +
                idGes,
            type: 'POST',
            data: JSON.stringify(post),
            dataType: "JSON",
            beforeSend: function() {
                $("#modalConfirmTransfert").modal("hide");
                $("#msgLoading").text("Transfert en cours...");
                $("#loadingModal").modal("show");
            },
            success: function(response) {
                console.log("success");
                console.log(response);
                $("#modalGestionnaires").modal("hide");
                setTimeout(() => {
                    $("#loadingModal").modal("hide");
                }, 500);
                if (response != null && response != undefined && response != "0") {
                    $("#msgSuccess").text("Transfert effectué avec succés");
                    $('#successOperation').modal('show');
                    location.reload();
                } else {
                    $("#msgError").text("Impossible de transférer les dossiers");
                    $('#errorOperation').modal('show');
                }

            },
            error: function(response) {
                console.log("Error");
                console.log(response);
                setTimeout(() => {
                    $("#loadingModal").modal("hide");
                }, 500);
                $("#msgError").text("Impossible de transférer les dossiers");
                $('#errorOperation').modal('show');
            },
            complete: function() {},
        });
    }
    /** OP **/
    function onClickEnvoyerDeclaration() {
        $("#modalConfirmDeclaration").modal("show");
    }

    function annulerMailOne() {
        if (indexOP == listOP.length - 1) {
            $("#modalEmail").modal("hide");
        } else {
            indexOP++;
            afficheMailOne();
        }

    }

    function sendMailOne() {
        if (postMail.to === null || postMail.to === "") {
            $("#msgError").text("Pas de destinataires: Impossible de faire la déclaration de sinistre OP '" + postMail
                .opName +
                "'!");
            $('#errorOperation').modal('show');
        } else {
            postMail.bodyMessage = tinyMCE.get("emailText").getContent();
            $.ajax({
                url: `<?= URLROOT ?>/public/json/activity.php?action=sendmaildeclarationEmailing`,
                type: 'POST',
                data: JSON.stringify(postMail),
                dataType: "JSON",
                beforeSend: function() {
                    $("#modalEmail").modal("hide");
                    $("#msgLoading").text(
                        "Déclaration de sinistre OP '" + postMail.opName + "' en cours...");
                    $("#loadingModal").modal("show");
                },
                success: function(response2) {
                    console.log("success mail");
                    console.log(response2);
                    setTimeout(() => {
                        $("#loadingModal").modal("hide");
                    }, 500)
                    $("#msgSuccess").text(
                        "Déclaration de sinistre OP '" + postMail.opName + "' effectuée avec succès !");
                    $('#successOperation').modal('show');
                    setTimeout(() => {
                        $('#successOperation').modal('hide');
                    }, 100)
                    if (indexOP == listOP.length - 1) {
                        $("#msgSuccess").text(
                            "Déclaration de sinistre terminée !");
                        $('#successOperation').modal('show');
                        location.reload();
                    } else {
                        indexOP++;
                        afficheMailOne();
                    }
                },
                error: function(response2) {
                    console.log("Error mail");
                    console.log(response2);
                    setTimeout(() => {
                        $("#loadingModal").modal("hide");
                    }, 500);
                    $("#msgError").text("Impossible de faire la déclaration de sinistre OP '" + postMail
                        .opName +
                        "'!");
                    $('#errorOperation').modal('show');
                },
                complete: function() {},
            });
        }

    }


    function afficheMailOne() {
        let i = indexOP;
        let response = listOP[i];
        let op = response.op;
        let dateSinistre = "";
        if (op.dateSinistre != null && op.dateSinistre != "") {
            let date = new Date(op.dateSinistre);
            dateSinistre = String(date.getDate()).padStart(2, '0') + "/" + String(date
                .getMonth() + 1).padStart(2, '0') + "/" + (date.getFullYear());
        }
        let contact = response.contact;
        let derniereDelegation = response.derniereDelegation;
        let rt = response.rt;
        let pieces = response.pieces;
        let emplacement = "";
        let description = "";
        if (pieces.length > 0) {
            pieces.forEach(piece => {
                emplacement = emplacement + piece.libellePiece + ",";
                if (piece.listSupports.length != 0) {
                    description = "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" +
                        piece.libellePiece + "<br>";
                    piece.listSupports.forEach(support => {
                        description +=
                            "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- " +
                            support.libelleSupport;
                        if (support.listRevetements != 0) {
                            description += " (";
                            support.listRevetements.forEach(rev => {
                                description += rev
                                    .libelleRevetement + ",";
                            })
                            description += ")";
                        }
                        description += " <br>";
                    });
                }
            });
        }
        let cause = rt && rt.cause !== null && rt.cause != "" ? rt.cause.replaceAll(';',
            ',') : "";
        description = (description == "" || description == "null") ? (rt ? rt.commentaireSinistre : "") : description;
        description = description === null ? "" : description;
        cause = cause === null ? "" : cause;

        let post = {
            to: response.cie.email,
            cc: response.contact.emailContact,
            nomCC: response.contact.prenomContact + " " + response.contact.nomContact,
            subject: "Déclaration de Sinistre et Confirmation de Mandat de Gestion avec cession de créance - Référence '" +
                op.name + "' // Vos références N° de Police : '" + op.policeMRH + "'" + (op.sinistreMRH != null && op
                    .sinistreMRH != "" ? " - N° de Sinistre : " + op.sinistreMRH : ""),
            body: `
                                       
                                            Madame, Monsieur,<br><br>
                                            Je me permets de vous contacter en tant que représentant de SOS SINISTRE by WBCC ASSISTANCE, mandaté pour la gestion des sinistres pour le compte de nos clients. Nous avons reçu une délégation de gestion dont copie en pièce jointe, de la part de :<br><br>
                                            - <b>${contact.civiliteContact} ${contact.prenomContact} ${contact.nomContact}</b>, <br>
                                            - <b>Locataire</b> de l'appartement situé à l’adresse suivante :<br>                                
                                            - <b>${op.adresse}</b>,<br><br> 
                                                                                        
                                            Et assuré(e) auprés de votre compagnie sous la police référence <b>${op.policeMRH}</b>.<br><br><br>
                                            
                                            Conformément aux principes du mandat définis dans le Code civil français, notamment aux articles 1984 et 1985, notre cabinet, <b>SOS SINISTRE by WBCC ASSISTANCE</b>, a été dûment mandaté par votre assuré(e) pour la gestion de son sinistre.<br><br>
                                            Par ce présent courrier, je vous informe que notre cabinet prend en charge la gestion de ce sinistre dont les informations connues à ce jour par nos services sont les suivantes :<br><br>

                                            - Nature du sinistre : <b>${op.typeIntervention == "" ? "En cours d'instruction" :op.typeIntervention}</b>,<br>
                                            - Date du sinistre : <b>${dateSinistre == "" ? "En cours d'instruction" : dateSinistre}</b>,<br>
                                            - Description précise du sinistre : <b>${description == "" ? "En cours d'instruction" : description}</b><br>
                                            - Cause : <b>${cause == "" ? "En cours d'instruction" :cause}</b><br><br>
                   
                   
                                            Nous avons attribué une référence de dossier à ce sinistre dans nos registres. Il s’agit du Numéro <b>${op.name}</b>.<br><br>
                                            Nous vous prions de bien vouloir rappeler cette référence dans toutes vos correspondances et règlements dans le cadre de la gestion de ce sinistre.<br><br>
                                            Nous vous transmettrons prochainement le devis détaillé pour les réparations nécessaires ainsi que le rapport d'expertise établi par nos experts internes.<br><br>
                                            Conformément à nos obligations, nous assurerons une communication fluide et efficace entre toutes les parties impliquées.<br><br>
                                            Nous vous saurions gré de bien vouloir ouvrir un dossier sinistre et de nous communiquer le numéro de référence associé.<br><br>
                                            Cette information est essentielle pour le suivi et la gestion efficace du dossier.<br><br>

                                            Enfin, nous souhaitons souligner que la délégation de gestion que nous détenons est un acte légitime etconforme aux dispositions législatives en vigueur.<br><br><br>
                                                                                            
                                            Nous rappelons que le refus de coopérer avec notre cabineten vertu de cette délégation constituerait une entrave au processus de gestion de sinistres, et pourrait être sujet à des conséquences légales.<br><br><br>
                                                                                            
                                            Nous restons à votre disposition pour toute information complémentaire et vous remercions par avance pour votre collaboration.<br><br>
                                                                                            
                                            Cordialement,
                                        
                                            <br>             
                            `,
            signature: `
                            <div>
                            <table style="vertical-align: top;">
                                            <tr>
                                                <td
                                                    style="width: 134.45pt; border: none; border-right: solid #9CC2E5 2pt; background: #D9E2F3; padding: 0cm 5.4pt 0cm 5.4pt; height: 4.1pt;">
                                                    <img src="<?= URLROOT . '/public/images/ticket/' . SIGNATURE_EMAIL_LOGO ?>"
                                                        alt="Image"> <br />
                                                    <span
                                                        style="color: red; font-size: 20px; font-family: 'Century Gothic',sans-serif; font-weight: bold;">SUIVEZ-NOUS</span>
                                                    <br /><br />
                                                    <a href='<?= SIGNATURE_EMAIL_TWEETER_LINK ?>'>
                                                        <img src="<?= URLROOT ?>/public/images/ticket/<?= SIGNATURE_EMAIL_TWEETER_ICON ?>"
                                                            alt="Tweeter" />
                                                    </a> &nbsp;&nbsp;
                                                    <a href='<?= SIGNATURE_EMAIL_INSTAGRAM_LINK ?>'>
                                                        <img src="<?= URLROOT ?>/public/images/ticket/<?= SIGNATURE_EMAIL_INSTAGRAM_ICON ?>"
                                                            alt="Instagram" />
                                                    </a> &nbsp;&nbsp;
                                                    <a href='<?= SIGNATURE_EMAIL_LINKEDIN_LINK ?>'>
                                                        <img src="<?= URLROOT ?>/public/images/ticket/<?= SIGNATURE_EMAIL_LINKEDIN_ICON ?>"
                                                            style="height: 40px;" alt="Linked-In" />
                                                    </a>
                                                </td>
                                                <td
                                                    style=" width: 350.5pt; background: #EDEDED; padding: 3px 5.4pt 0cm 5.4pt; height: 4.1pt; font-family: 'Century Gothic',sans-serif; color: black; vertical-align: top;">
                                                    <span style="font-weight: bold;">
                                                        <?= $_SESSION['connectedUser']->civiliteContact . ' ' . $_SESSION['connectedUser']->prenomContact  . ' ' . $_SESSION['connectedUser']->nomContact  ?></span>
                                                    <div style="padding-top: 8px; font-size: 17px;">Service Gestion des
                                                        Sinistres et de travaux TCE</div>
                                                    <table style="width: 100%; margin-top: 5px;">
                                                        <tr>
                                                            <td style="vertical-align: middle;"><img
                                                                    src="<?= URLROOT ?>/public/images/ticket/<?= SIGNATURE_EMAIL_PHONE_ICON ?>"
                                                                    alt="" /><span
                                                                    style="color: red; font-weight: bold;">
                                                                    &nbsp;&nbsp;:</span></td>
                                                            <td style="font-weight: bold; vertical-align: middle;">
                                                                <?= SIGNATURE_EMAIL_TEL ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="vertical-align: middle; height: 10px;"></td>
                                                            <td style="vertical-align: middle;"></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="vertical-align: middle;"><img
                                                                    src="<?= URLROOT ?>/public/images/ticket/<?= SIGNATURE_EMAIL_EMAIL_ICON ?>"
                                                                    alt="" /><span
                                                                    style="color: red; font-weight: bold;">
                                                                    &nbsp;&nbsp;:</span></td>
                                                            <td style="vertical-align: middle;"><a
                                                                    style="text-decoration: none; color: blue;"
                                                                    href='mailto:<?= SIGNATURE_EMAIL_CONTACT ?>'><?= SIGNATURE_EMAIL_CONTACT ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="vertical-align: middle;"><img
                                                                    src="<?= URLROOT ?>/public/images/ticket/<?= SIGNATURE_EMAIL_LOCATION_ICON ?>"
                                                                    alt="" /><span
                                                                    style="color: red; font-weight: bold;">
                                                                    &nbsp;&nbsp;:</span></td>
                                                            <td style="vertical-align: middle;">
                                                                <?= SIGNATURE_EMAIL_ADRESSE_POSTALE ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="vertical-align: middle;"><img
                                                                    src="<?= URLROOT ?>/public/images/ticket/<?= SIGNATURE_EMAIL_WEBSITE_ICON ?>"
                                                                    alt="" /><span
                                                                    style="color: red; font-weight: bold;">
                                                                    &nbsp;&nbsp;:</span></td>
                                                            <td style="vertical-align: middle;"><a
                                                                    style="text-decoration: none; color: blue;"
                                                                    href='<?= SIGNATURE_EMAIL_WEB_SITE ?>'><?= SIGNATURE_EMAIL_WEB_SITE ?></a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        </div>
                            `,
            attachment: "/public/documents/opportunite/" + derniereDelegation
                .urlDocument,
            attachmentName: derniereDelegation.urlDocument,
            idOpportunity: op.idOpportunity,
            opName: op.name,
            idAuteur: `<?= $_SESSION['connectedUser']->idUtilisateur ?>`,
            auteur: `<?= $_SESSION['connectedUser']->fullName ?>`,
            numeroAuteur: `<?= $_SESSION['connectedUser']->numeroContact ?>`,
            bodyMessage: ""
        }
        post.bodyMessage = post.body + "<br>" + post.signature
        postMail = post;

        //SHOW MODAL EMAIL
        $("#modalTitle").text(
            "APERCU DECLARATION DE SINISTRE " + (indexOP + 1) + "/" + listOP.length);
        document.getElementById("emailDestinataire").setAttribute("value", (post.to === null ? "" : post.to));
        document.getElementById("emailAssure").setAttribute("value", (post.cc === null ? "" : post.cc));
        $('#libelleCC').html(
            "CC : Assuré '" + (post.nomCC === null ? "" : post.nomCC) + "'");;
        $('#objet').val(post.subject);
        tinyMCE.get("emailText").setContent(post.bodyMessage);
        $("#delegationFile").html(`
                                <div class='row'>
                                    <div class="offset-1 col-10 rounded p-1 mt-1" style="border: 2px solid black;">
                                        <span class="file-list__name" id="delegation" style="max-width: 90%;">${post.attachmentName}</span>
                                        <a class="btn btn-white" data-uploadid="" target="_blank" href="<?= URLROOT ?>${post.attachment}"><i class="fa fa-eye" aria-hidden="true" style="color:#0cb8e1"></i></a>
                                    </div>
                                </div>
                                `)

        setTimeout(() => {
            $("#modalEmail").modal("show");
        }, 300)
    }

    function sendDeclaration() {
        var one = document.getElementsByName('checkOP');
        let nbCheck = 0;
        let tabIdOP = [];
        for (let index = 0; index < one.length; index++) {
            const element = one[index];
            if (element.checked) {
                nbCheck++;
                let idOP = element.value.split(':')[0];
                tabIdOP.push(idOP);
            }
        }

        if (tabIdOP.length != 0) {
            $.ajax({
                url: `<?= URLROOT ?>/public/json/opportunity.php?action=findOpByIDS`,
                type: 'POST',
                data: JSON.stringify(tabIdOP),
                dataType: "JSON",
                beforeSend: function() {
                    $("#modalConfirmDeclaration").modal("hide");
                    $("#msgLoading").text("Chargement des mails");
                    $("#loadingModal").modal("show");
                },
                success: function(response2) {
                    console.log("success mail");
                    console.log(response2);
                    listOP = response2;
                    setTimeout(() => {
                        $("#loadingModal").modal("hide");
                    }, 500)
                    afficheMailOne();
                },
                error: function(response2) {
                    console.log("Error mail");
                    console.log(response2);
                    setTimeout(() => {
                        $("#loadingModal").modal("hide");
                    }, 500);
                    $("#msgError").text("Impossible de charger les mails");
                    $('#errorOperation').modal('show');
                },
                complete: function() {},
            });
        }
    }

    function onHideSide(param) {
        let val = document.getElementById("btnHide");
        let val1 = document.getElementById("div1");
        let val2 = document.getElementById("div2");

        if (val.innerHTML.includes("less")) {
            val.innerHTML = "<i class='fas fa-greater-than fa-fw'></i>"
            val1.classList.value = "col-md-0"
            val1.hidden = "hidden"
            val2.classList.value = "card col-md-12"

            $('#btnShow').removeAttr("hidden");
        } else {
            val.innerHTML = "<i class='fas fa-less-than fa-fw'></i>"
            $('#div1').removeAttr("hidden");
            val1.classList.value = "col-md-2"
            val2.classList.value = "card col-md-10"
            $('#btnShow').attr("hidden", "hidden");
        }
    }
</script>
<?php
include_once dirname(__FILE__) . '/../../blocs/functionBoiteModal.php';
?>