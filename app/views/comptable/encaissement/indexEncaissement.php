<?php
$idRole = $_SESSION["connectedUser"]->role;
$viewAdmin = (($idRole == "1" || $idRole == "2" || $idRole == "8" || $idRole == "9" ||  $_SESSION["connectedUser"]->isAccessAllOP == "1")) ? "" : "hidden";
$viewAdmin2 = (($idRole == "1" || $idRole == "2" || $idRole == "8" || $idRole == "9" || $idRole == 25 ||  $_SESSION["connectedUser"]->isAccessAllOP == "1")) ? "" : "hidden";

?>
<div class="section-title">
    <div class="row">
        <div class="col-md-6">
            <h2><a onclick="history.back()"><button><i class="fas fa-fw fa-arrow-left"
                            style="color: #c00000"></i></button></a><span> <i class="fa fa-solid fa-euro-sign"
                        style="color:#c00000"></i>
                </span> encaissements</h2>
        </div>

    </div>
</div>

<input type='text' id='idAuteur' class='form-control' value='<?= $_SESSION['connectedUser']->idUtilisateur ?>' hidden>
<input type='text' id='auteur' class='form-control' value='<?= $_SESSION['connectedUser']->fullName ?>' hidden>

<div class="card mt-0">
    <!-- FILTRE -->
    <div class="col-md-12">
        <div class="accordion-body pt-3 pb-3 pr-3" style="box-shadow: none !important;">
            <form method="GET" action="<?= linkTo('Comptable', 'indexEncaissement', "") ?>">
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
                                    <option value="AMO" <?= $typeIntervention == "AMO" ? "selected" : "" ?>>A.M.O.
                                    </option>
                                    <option value="SINISTRE" <?= $typeIntervention == "SINISTRE" ? "selected" : "" ?>>
                                        Sinistres
                                    </option>
                                    <option value="tous" <?= $typeIntervention == "tous" ? "selected" : "" ?>>Tous
                                    </option>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    <div class="<?= "col-md-2"  ?>">
                        <fieldset>
                            <legend
                                class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                CATEGORIE OP </legend>
                            <div class="card ">
                                <select id="statut" name="statut" class="form-control" data-live-search="true">
                                    <option value="0" disabled selected>--Choisir--</option>
                                    </option>
                                    <option value="enCours" <?= $statut == "enCours" ? "selected" : "" ?>>En cours
                                    </option>
                                    <option value="clotures" <?= $statut == "clotures" ? "selected" : "" ?>>Clôturés
                                    </option>
                                    <option value="tous" <?= $statut == "tous" ? "selected" : "" ?>>Tous
                                    </option>
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
                                        if ((($idRole == "1" || $idRole == "2"  || $idRole == "9" || $idRole == "8" ||  $_SESSION["connectedUser"]->isAccessAllOP == "1") || (($idRole == "3" || $idRole == "25") && $_SESSION["connectedUser"]->nomSite == $sit->nomSite))) {
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
                                    data-live-search="true" onchange='onSelectGestionnaire()'>
                                    <option value="0" disabled selected>--Choisir--</option>
                                    <option value="tous" <?= $gestionnaire == "tous" ? "selected" : "" ?>
                                        <?= $idRole == "1" || $idRole == "2"  || $idRole == "9" || $idRole == "8" ||  $_SESSION["connectedUser"]->isAccessAllOP == "1" ||  $_SESSION["connectedUser"]->isAccessAllOP == "1"  ?  "" : "hidden" ?>>
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
                                    <option value="today" <?= $periode == "today" ? "selected" : "" ?>>Aujourd'hui
                                    </option>
                                    <option value="semaine" <?= $periode == "semaine" ? "selected" : "" ?>>Semaine en
                                        cours
                                    </option>
                                    <option value="mois" <?= $periode == "mois" ? "selected" : "" ?>>Mois en cours
                                    </option>
                                    <option value="trimestre" <?= $periode == "trimestre" ? "selected" : "" ?>>
                                        Trismestre en cours
                                    </option>
                                    <option value="semestre" <?= $periode == "semestre" ? "selected" : "" ?>>Semestre en
                                        cours
                                    </option>
                                    <option value="annuel" <?= $periode == "annuel" ? "selected" : "" ?>>Année en cours
                                    </option>
                                    <option value="day" <?= $periode == "day" ? "selected" : "" ?>>A la date du :
                                    </option>
                                    <option value="perso" <?= $periode == "perso" ? "selected" : "" ?>>Personnaliser
                                    </option>
                                </select>
                            </div>
                        </fieldset>
                        <fieldset id="changeperso" <?= $periode == "perso" ||  $periode == "day" ? "" : "hidden" ?>>
                            <legend
                                class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                Personnaliser </legend>
                            <div class="card">
                                <div class="row">
                                    <div class="col-md-6" id="date1">
                                        <input type="date" name="date1" id="date1Input" value="<?= $date1 ?>"
                                            class="form-control">
                                    </div>
                                    <div class="col-md-6" id="date2" <?= $periode == "day" ? "hidden" : "" ?>>
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
                                    <option value="tous" <?= $commercial == "tous" ? "selected" : "" ?>>
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
            </form>
        </div>
    </div>
    <div class="modal-content mt-3">
        <div class="card-header bg-light text-white">
            <div class="row">
                <div <?= sizeof($encaissements) != 0 ? "" : "hidden"  ?>
                    class="row  <?= $viewAdmin != "" ? "" : "col-md-2" ?> " <?= $viewAdmin ?>>
                    <div class="col-md-4 text-left float-left">
                        <input onclick="onCheckAll()" type="checkbox" class="form-control float-left" name="allChecked"
                            id="allChecked" value="all">
                    </div>
                    <div id="divBtnExporter" class="mt-1" hidden>
                        <button type="button" rel="tooltip"
                            title="Faire la déclaration de compagnie pour les  selectionnées"
                            onclick="onClickExporter()" class="btn btn-sm btn-info btn-simple" id="btnExporter">
                            <i class="fas fa-download" style="color: #ffffff"></i> Exporter
                        </button>
                    </div>
                </div>
                <h2
                    class="<?= $viewAdmin != "" || sizeof($encaissements) == 0  ? " col-md-12" : " col-md-10 " ?> font-weight-bold text-danger text-center h4">
                    <?= $titre ?>
                    (<?= sizeof($encaissements) ?>) <br> (Montant Total : <?= number_format($total, 2, ',', ' ')  ?>€)
                </h2>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <div class="large-table-fake-top-scroll-container-3">
                    <div>&nbsp;</div>
                </div>
                <div class="large-table-container-3">
                    <table class="table table-bordered" id="dataTable16">
                        <thead class="bg-light">
                            <tr>
                                <th <?= $viewAdmin ?>></th>
                                <th <?= $viewAdmin ?>>Actions</th>
                                <th>#</th>
                                <th>N° OP</th>
                                <th>Type Dossier</th>
                                <th>Gestionnaire</th>
                                <th>Site</th>
                                <th>Date </th>
                                <th>Montant TTC</th>
                                <th>Taux TVA</th>
                                <th>Montant HT</th>
                                <th <?= $viewAdmin ?>>Taux Commis.</th>
                                <th>Réglé Par </th>
                                <th>Libellé </th>
                                <th>Tireur </th>
                                <th>Donneur d'Ordre</th>
                                <th>Adresse</th>
                                <th <?= $viewAdmin ?>>N° Compte</th>
                                <th <?= $viewAdmin ?>>N° Journal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            foreach ($encaissements as $encaisse) {
                                $i++;
                            ?>
                                <tr>
                                    <td style="text-align : center" <?= $viewAdmin ?>>
                                        <input onclick="onCheckOne()" type="checkbox" class="oneselection" name="check"
                                            value="<?= $encaisse->factureFile ?>">
                                    </td>
                                    <td <?= $viewAdmin ?> style="text-align : center">
                                        <a <?= $viewAdmin ?> title="Modifier Taux"
                                            class="btn btn-sm btn-warning btn-simple btn-link"
                                            onclick="onClickEditEncaissement(<?= $encaisse->idEncaissement ?>)"><i
                                                class="fas fa-edit" style="color: #ffffff"></i></a>

                                        <a <?= $viewAdmin ?> title="Voir la Facture"
                                            class="btn btn-sm btn-danger btn-simple btn-link"
                                            onclick="popitup('<?= $encaisse->factureFile ?>','Facture')"><i
                                                class="fas fa-file" style="color: #ffffff"></i></a>

                                        <a <?= $viewAdmin ?>
                                            href="<?= URLROOT . "/public/documents/ecritureComptable/$encaisse->journalFile" ?>"
                                            title="Bordereau" class="btn btn-sm btn-info btn-simple "><i
                                                class="fas fa-download" style="color: #ffffff"></i></a>
                                        <button title="Journal" hidden class="btn btn-sm btn-primary"
                                            onclick="onClickViewJournal('<?= $encaisse->idEncaissement ?>')"><i
                                                class="fas fa-eye"></i></button>
                                    </td>
                                    <td><?= $i ?></td>
                                    <td><?= $encaisse->nameOPEncaissement ?> </td>
                                    <td><?= $encaisse->type ?> </td>
                                    <td><?= $encaisse->prenomContact . ' ' . $encaisse->nomContact ?> </td>
                                    <td><?= $encaisse->nomSite  ?> </td>
                                    <td data-order="<?= $encaisse->dateEncaissement ?>">
                                        <?= date('d/m/Y', strtotime($encaisse->dateEncaissement)) ?></td>
                                    <td><?= $encaisse->montantEncaissement ?> €</td>
                                    <td><?= $encaisse->tauxEncaissement ?>%</td>
                                    <td><?= $encaisse->montantHTEncaissement ?> €</td>
                                    <td <?= $viewAdmin ?>><?= $encaisse->tauxCommissionnement ?>%</td>
                                    <td><?= $encaisse->typeReglement ?></td>
                                    <td><?= $encaisse->typeEncaissement ?> </td>
                                    <td><?= $encaisse->tireur ?> </td>
                                    <td><?= $encaisse->donneurOrdre ?> </td>
                                    <td><?= $encaisse->immeuble ? $encaisse->immeuble->adresse . " " . $encaisse->immeuble->codePostal . " " . $encaisse->immeuble->ville : "" ?>
                                    </td>
                                    <td <?= $viewAdmin ?>><?= $encaisse->numeroCompteBancaire ?> </td>
                                    <td <?= $viewAdmin ?>><u><a title="Journal"
                                                class="text-underline text-primary font-weight-bold"
                                                href="<?= linkTo('Comptable', 'listEncaissement', $encaisse->idJournal) ?>"><?= $encaisse->numeroJournal ?></a></u>
                                    </td>

                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- modal edit chèque -->
<div class="modal fade" id="modalEditEncaissement" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-header text-white">Modification Encaissement <a href="javascript:void(0)" type="submit"
                class="btn btn-danger" data-dismiss="modal">X</a>
        </div>
        <div class="modal-content">
            <div class="modal-body">
                <div class="row col-md-12">
                    <div class="col-md-4 form-group">
                        <label for="">Montant TTC Encaissé</label>

                        <input hidden type="text" class="form-control" value="" id="idEncaissement">
                        <input readonly type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"
                            class="form-control" value="" id="montantEncaissement">
                    </div>
                    <div class="col-md-4 form-group ">
                        <label for="">Montant HT</label>
                        <input readonly type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"
                            class="form-control" value="" id="montantHTEncaissement">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="">Taux de Commissionnement</label><span class="text-danger"> *</span>
                        <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"
                            class="form-control" value="" id="tauxCommissionnement">
                    </div>
                </div>

                <div class="card-footer" id="divFooter">
                    <div class="row">
                        <div class="col-md-4 offset-4  pagination pagination-sm row text-center">
                            <div class="pull-right page-item  p-0 m-0" id="btnEncaisseTerminer"><a type="button"
                                    class="text-center btn btn-success"
                                    onclick="saveTauxCommissionnement()">Enregistrer</a></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal Confirmation TRANSFERT -->
<div class="modal fade" id="modalConfirmExport" data-backdrop="static">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h3 class="text-black font-weight-bold">Voulez-vous exporter les factures des encaissements selectionnés
                    ?</h3>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-md-6">
                        <button class="btn btn-danger" data-dismiss="modal">Non</button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-success" onclick="exporterFacture()">Oui</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal de chargement -->
<div class="modal fade" id="loadingModal" data-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-danger" style="width: 5vw; height: 10vh;">
                </div>
                <br><br><br>
                <h3 id="msgLoading">Génération de délégation en cours...</h3>
            </div>
        </div>
    </div>
</div>

<!-- modal success -->
<div>
    <div class="modal fade modal-center" id="successOperation" data-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg bg-white">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <h3 id="msgSuccess" class="" style="color:green">Email envoyé !!</h3>
                    <button onclick="" id="buttonConfirmContact" class="btn btn-success"
                        data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal ERROR -->
<div>
    <div class="modal fade modal-center" id="errorOperation" data-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg bg-white">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <h3 id="msgError" class="" style="color:red">Email envoyé !!</h3>
                    <button onclick="" class="btn btn-danger" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- modal View Journal -->
<div>
    <div class="modal fade modal-center" id="modalJournal" tabindex="-1">
        <div class="modal-dialog modal-lg bg-white">
            <div class="modal-content">
                <div class="modal-body ">
                    <div class="card-header">
                        Détail Ecriture Comptable
                    </div>
                    <table class="table table-bordered">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th>Date</th>
                                <th>Journal</th>
                                <th>Description</th>
                                <th>Compte</th>
                                <th>Débit (€)</th>
                                <th>Crédit (€)</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyJournal">

                        </tbody>
                    </table>
                    <button onclick="" id="buttonConfirmContact" class="btn btn-success"
                        data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= URLROOT ?>/public/assets/vendor/jquery/jquery.min.js"></script>
<script type="text/javascript">
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


    function popitup(file, type) {
        let newwindow = window.open(("<?= URLROOT ?>" + '/public/documents/factures/' + file), type,
            'height=900,width=1000');
        if (window.focus) {
            newwindow.focus()
        }
        return false;
    }

    function onClickViewJournal(id) {
        console.log(`<?= URLROOT ?>/public/json/enveloppe.php?action=findEncaissementById&idEncaissement=${id}`);
        $.ajax({
            url: `<?= URLROOT ?>/public/json/enveloppe.php?action=findEncaissementById&idEncaissement=${id}`,
            type: 'GET',
            dataType: "JSON",
            beforeSend: function() {
                $("#msgLoading").text(
                    "Chargement en cours..."
                );
                $("#loadingModal").modal("show");
            },
            success: function(response) {
                console.log("success");
                console.log(response);
                $('#tbodyJournal').html(`<tr>
                                <td>${response.dateEncaissement}</td>
                                <td>BQ</td>
                                <td>${response.nameEncaissement} ${response.typeEncaissement} ${response.tireur} POUR ${response.donneurOrdre}</td>
                                <td>512${response.numeroCompteBancaire.substr(response.numeroCompteBancaire.length - 3, 3)}</td>
                                <td>${response.montantEncaissement}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>${response.dateEncaissement}</td>
                                <td>BQ</td>
                                <td>${response.nameEncaissement} ${response.typeEncaissement} ${response.tireur} POUR ${response.donneurOrdre}</td>
                                <td>4110${response.donneurOrdre.substr(0, 3)}</td>
                                <td></td>
                                <td>${response.montantEncaissement}</td>
                            </tr>
                            <tr>
                                <td>${response.dateEncaissement}</td>
                                <td>OD</td>
                                <td>${response.nameEncaissement} TRANSFERT ${response.typeEncaissement} DE ${response.tireur} VERS ${response.donneurOrdre}</td>
                                <td>4110${response.donneurOrdre.substr(0, 3)}</td>
                                <td>${response.montantEncaissement}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>${response.dateEncaissement}</td>
                                <td>OD</td>
                                <td>${response.nameEncaissement} TRANSFERT ${response.typeEncaissement} DE ${response.donneurOrdre} VERS ${response.artisan} </td>
                                <td>4110${response.artisan.substr(0, 4)}</td>
                                <td></td>
                                <td>${response.montantEncaissement}</td>
                            </tr>
                            <tr>
                                <td>${response.dateEncaissement}</td>
                                <td>VE</td>
                                <td>${response.nameEncaissement} HONORAIRES SUITE ${response.typeEncaissement} ${response.tireur} POUR ${response.donneurOrdre}</td>
                                <td>4110${response.donneurOrdre.substr(0, 3)}</td>
                                <td>${(response.montantEncaissement * 0.35) + (response.montantEncaissement * 0.35 * 0.2)}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>${response.dateEncaissement}</td>
                                <td></td>
                                <td>${response.nameEncaissement} HONORAIRES SUITE ${response.typeEncaissement} ${response.tireur} POUR ${response.donneurOrdre} </td>
                                <td>706000</td>
                                <td></td>
                                <td>${response.montantEncaissement * 0.35}</td>
                            </tr>
                            <tr>
                                <td>${response.dateEncaissement}</td>
                                <td></td>
                                <td>${response.nameEncaissement} HONORAIRES SUITE ${response.typeEncaissement} ${response.tireur} POUR ${response.donneurOrdre} </td>
                                <td>445712</td>
                                <td></td>
                                <td>${response.montantEncaissement * 0.35 * 0.2}</td>
                            </tr>`);
                $("#modalJournal").modal("show");

            },
            error: function(response) {
                $("#loadingModal").modal("hide");
                console.log(response);
                $("#msgError").text(
                    " Réessayer ou contacter l'administrateur"
                );
                $('#errorOperation').modal('show');
            },
            complete: function() {
                setTimeout(function() {
                    $('#loadingModal').modal('hide');
                }, 1000);
            },
        });


    }

    function onClickEditEncaissement(idEncaissement) {

        $.ajax({
            url: `<?= URLROOT ?>/public/json/activityEnveloppe.php?action=findEncaissementByID&idEncaissement=${idEncaissement}`,
            type: 'GET',
            dataType: 'JSON',
            success: function(response) {
                console.log(response);
                $('#idEncaissement').val(response.idEncaissement);
                $('#montantEncaissement').val(response.montantEncaissement);
                $('#montantHTEncaissement').val(response.montantHTEncaissement);
                $('#tauxCommissionnement').val(response.tauxCommissionnement);
                $("#modalEditEncaissement").modal("show");
            },
            error: function(response) {
                console.log(response);
            }
        });

    }

    function saveTauxCommissionnement() {
        let post = {
            idEncaissement: $('#idEncaissement').val(),
            montantEncaissement: $('#montantEncaissement').val(),
            montantHTEncaissement: $('#montantHTEncaissement').val(),
            tauxCommissionnement: $('#tauxCommissionnement').val(),
            idUtilisateur: $('#idUtilisateur').val(),
            numeroAuteur: $('#numeroAuteur').val(),
            auteur: $('#auteur').val()
        }
        $.ajax({
            url: `<?= URLROOT ?>/public/json/activityEnveloppe.php?action=saveTauxCommissionnement`,
            type: 'POST',
            data: JSON.stringify(post),
            dataType: "JSON",
            beforeSend: function() {
                $("#modalEditEncaissement").modal("hide");
                $("#msgLoading").text("Enregistrement en cours...");
                $("#loadingModal").modal("show");
            },
            success: function(response) {
                console.log(response);
                setTimeout(() => {
                    $("#loadingModal").modal("hide");
                }, 500)
                location.reload();

            },
            error: function(response) {
                console.log(response);
            }
        });
    }

    function onCheckAll() {
        var all = document.getElementsByName('allChecked');
        let checked = all[0].checked;
        var one = document.getElementsByName('check');
        one.forEach(element => {
            element.checked = false;
        });
        let btnExporter = document.getElementById("btnExporter");
        btnExporter.innerHTML = "<i class='fas fa-download' style='color: #ffffff'></i> Exporter (" + one.length + ")"
        if (checked) {
            //Check ALL
            one.forEach(element => {
                element.checked = true;
            });
            $("#divBtnExporter").removeAttr("hidden");

        } else {
            $("#divBtnExporter").attr("hidden", "hidden")
            one.forEach(element => {
                element.checked = false;
            });
        }
    }

    function onCheckOne() {
        let postMail = {};
        var one = document.getElementsByName('check');
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
            $("#divBtnExporter").removeAttr("hidden");
        } else {
            $("#divBtnExporter").attr("hidden", "hidden")
        }
        var all = document.getElementsByName('allChecked');
        let btnExporter = document.getElementById("btnExporter");
        btnExporter.innerHTML = "<i class='fas fa-download' style='color: #ffffff'></i> Exporter (" + i + ")"
        if (checkAll) {
            all[0].checked = true;
        } else {
            all[0].checked = false;
        }
    }

    function onClickExporter() {
        $("#modalConfirmExport").modal("show");
    }

    function exporterFacture() {
        var one = document.getElementsByName('check');
        let nbCheck = 0;
        let tabId = [];
        for (let index = 0; index < one.length; index++) {
            const element = one[index];
            if (element.checked) {
                nbCheck++;
                let id = element.value;


                tabId.push(id);
            }
        }
        if (tabId.length == 1) {
            download_file(`<?= URLROOT ?>/public/documents/factures/` + tabId[0], tabId[0]);
            $("#modalConfirmExport").modal("hide");
            var one = document.getElementsByName('check');
            let isChecked = false;
            let checkAll = true;
            let i = 0;
            for (let index = 0; index < one.length; index++) {
                const element = one[index];
                element.checked = false;
            }
            var all = document.getElementsByName('allChecked');
            all[0].checked = false;
            $("#divBtnExporter").attr("hidden", "hidden")
        } else {
            let post = {
                files: tabId,
                url: "factures",
                name: "FacturesImport"
            }
            $.ajax({
                url: `<?= URLROOT ?>/public/json/document.php?action=createZipFile`,
                type: 'POST',
                data: JSON.stringify(post),
                dataType: "JSON",
                beforeSend: function() {
                    $("#modalConfirmExport").modal("hide");
                    $("#msgLoading").text("Génération en cours...");
                    $("#loadingModal").modal("show");
                },
                success: function(response) {
                    console.log(response);
                    download_file(`<?= URLROOT ?>/public/documents/factures/` + response, response);
                    setTimeout(() => {
                        $("#loadingModal").modal("hide");
                        $("#msgSuccess").text(
                            "MAJ effectuée avec succès !"
                        );
                        $('#successOperation').modal('show');
                    }, 500)
                    // location.reload();
                    setTimeout(() => {
                        $("#successOperation").modal("hide");
                    }, 1500);

                    var one = document.getElementsByName('check');
                    let isChecked = false;
                    let checkAll = true;
                    let i = 0;
                    for (let index = 0; index < one.length; index++) {
                        const element = one[index];
                        element.checked = false;
                    }
                    var all = document.getElementsByName('allChecked');
                    all[0].checked = false;
                    $("#divBtnExporter").attr("hidden", "hidden")

                },
                error: function(response) {
                    console.log(response);
                    setTimeout(() => {
                        $("#loadingModal").modal("hide");
                    }, 500)
                    $("#msgError").text(
                        "Impossible d'exporter !"
                    );
                    $('#errorOperation').modal('show');
                }
            });
        }



        // setTimeout(() => {
        //     location.reload();
        // }, 4000);
    }

    function download_file(fileURL, fileName) {
        // for non-IE
        if (!window.ActiveXObject) {
            var save = document.createElement('a');
            save.href = fileURL;
            save.target = '_blank';
            var filename = fileURL.substring(fileURL.lastIndexOf('/') + 1);
            save.download = fileName || filename;
            if (navigator.userAgent.toLowerCase().match(/(ipad|iphone|safari)/) && navigator.userAgent.search("Chrome") <
                0) {
                document.location = save.href;
                // window event not working here
            } else {
                var evt = new MouseEvent('click', {
                    'view': window,
                    'bubbles': true,
                    'cancelable': false
                });
                save.dispatchEvent(evt);
                (window.URL || window.webkitURL).revokeObjectURL(save.href);
            }
        }

        // for IE < 11
        else if (!!window.ActiveXObject && document.execCommand) {
            var _window = window.open(fileURL, '_blank');
            _window.document.close();
            _window.document.execCommand('SaveAs', true, fileName || fileURL)
            _window.close();
        }
    }
</script>