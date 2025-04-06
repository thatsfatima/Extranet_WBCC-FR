<?php
$idRole = $_SESSION["connectedUser"]->role;
$viewAdmin = (($idRole == "1" || $idRole == "2" || $idRole == "8" || $idRole == "9" ||  $_SESSION["connectedUser"]->isAccessAllOP == "1")) ? "" : "hidden";
$viewAdmin2 = (($idRole == "1" || $idRole == "2" || $idRole == "8" || $idRole == "9" || $idRole == 25 ||  $_SESSION["connectedUser"]->isAccessAllOP == "1")) ? "" : "hidden";

?>
<div class="section-title">
    <div class="row">
        <div class="col-md-6">
            <h2><?= $titre ?> </h2>
        </div>
        <div class="col-md-6">
            <div class="float-right row mt-0 mb-3">
                <a type="button" rel="tooltip" title="Ajouter" href="<?= linkto('GestionInterne', 'jourFerie') ?>?site=<?= $site->idSite ?>&annee=<?= $annee ?>"
                    class="btn btn-sm btn-red font-weight-bold ml-1 px-3">
                    <i class="fas fa-plus" style="color: #ffffff"></i>
                    Ajout Ferie
                </a>
                <?php
                    if (count($joursFeries) <= 0)
                    {
                ?>
                <div class="dropdown col-md-7">
                    <button class="btn btn-sm btn-red dropdown-toggle form-control font-weight-bold" type="button" id="anneeReferenceDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Année de référence<i class='fas fas-chevron-down text-white'></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="anneeReferenceDropdown">
                        <?php
                        for ($i = $annee - 1; $i >= $annee - 5; $i--) {
                            echo '<a class="dropdown-item anneeReference" data-value="' . $i . '" >' . $i . '</a>';
                        }
                        ?>
                    </div>
                </div>
                <?php
                    }
                ?>
            </div>
        </div>
    </div>
</div>

<input type='text' id='idAuteur' class='form-control' value='<?= $_SESSION['connectedUser']->idUtilisateur ?>' hidden>
<input type='text' id='auteur' class='form-control' value='<?= $_SESSION['connectedUser']->fullName ?>' hidden>

<div class="card mt-0">
    <!-- FILTRE -->
    <div class="col-md-12">
        <div class="accordion-body mt-1 pt-3 pb-3 pr-3 border rounded" style="box-shadow: none !important;">
            <form method="GET" action="<?= linkTo('GestionInterne', 'indexJourFerie', "") ?>">
                <div class="row" style="width: 100%;  margin: auto;">
                    <div class="<?= "col-md-5"  ?>">
                        <fieldset>
                            <legend
                                class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge mx-0' style="background-color: #c00000;">
                                Site </legend>
                                <div class="card ">
                                    <select id="site" name="site" class="form-control"
                                    data-live-search="true">
                                    <?php
                                        $i = 1;
                                        foreach ($sites as $item) {
                                            ?>
                                            <option value="<?= $item->idSite ?>" <?= $item->idSite == $site->idSite ? "selected" : "" ?>> <?= $item->nomSite ?>
                                        </option>
                                        <?php
                                        }
                                        ?>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    <div class="<?= "col-md-5" ?>">
                        <fieldset>
                            <legend
                            class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge mx-0' style="background-color: #c00000;">
                            ANNEE </legend>
                            <div class="card ">
                                <input type="number" id="annee" name="annee" class="form-control" min="2000" max="<?= date('Y') + 1 ?>" value="<?= htmlspecialchars($annee) ?>">
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-danger col-md-10 mt-5 ml-3 font-weight-bold self-center form-control" style="background-color: #c00000;">
                            <i class="fa" style="color: #ffffff">&#xf021;</i> Charger
                        </button>
                    </div>
                </div>
            </form>
            <input type="text" name="URLROOT" id="URLROOT" value="<?= URLROOT ?>" hidden>
            <input type="text" name="URLROOT_GESTION_WBCC_CB" id="URLROOT_GESTION_WBCC_CB" value="<?= URLROOT_GESTION_WBCC_CB ?>" hidden>
            <input type="text" name="idSiteF" id="idSiteF" value="<?= $site->idSite ?>" hidden>
            <input type="text" name="anneeJourFerie" id="anneeJourFerie" value="<?= $annee ?>" hidden>
        </div>
    </div>
    <div class="modal-content my-3">
        <?php
            if (sizeof($joursFeries) != 0) 
            {
        ?>
                <div class="card-header bg-light text-white">
                    <div class="col-md-12 row">
                        <div class="row <?= sizeof($joursFeries) != 0 ? "col-md-5 offset-11" : "hidden"  ?>">
                            <div id="divBtnExporter" class="mt-1">
                                <button type="button" rel="tooltip"
                                    title="Faire la déclaration de compagnie pour les  selectionnées"
                                    onclick="exporterJourFerie()" class="btn btn-simple col-md-12" id="btnExporter" style="color: #ffffff; background-color: #c00000;">
                                    <i class="fas fa-download" style="color: #ffffff;"></i> Exporter
                                </button>
                            </div>
                        </div>
                        <h2
                            class="col-md-12 font-weight-bold text-danger text-center h4">
                            <?= $sousTitre ?>
                            (<?= sizeof($joursFeries) ?>)
                        </h2>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div class="large-table-fake-top-scroll-container-2">
                            <div>&nbsp;</div>
                        </div>
                        <div class="large-table-container-3">
                            <table class="table table-bordered" id="dataTable16">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Evenement</th>
                                        <th>Payer</th>
                                        <th>Chomer</th>
                                        <th <?= $viewAdmin ?> ><?= ($annee < date('Y')) ? "" : "Actions" ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($joursFeries as $jourFerie) {
                                    ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= date('d/m/Y', strtotime($jourFerie->dateJourFerie)) ?></td>
                                            <td><?= $jourFerie->nomJourFerie ?></td>
                                            <td><?= ($jourFerie->Payer) ? "Payé" : "Non payé" ?></td>
                                            <td><?= ($jourFerie->Chomer) ? "Chomé" : "Non chomé" ?></td>
                                            <td <?= $viewAdmin ?> style="display: flex; flex-direction: row; justify-content: center;">
                                                <button class="btn btn-sm btn-warning btn-simple btn-link mr-1 <?= ($jourFerie->dateJourFerie <= date('Y-m-d')) ? "invisible" : "" ?>"
                                                    >
                                                    <a type="button" title="Modifier Jour Ferie"
                                                        href="<?= linkto('GestionInterne', 'jourFerie', $jourFerie->idJourFerie) ?>">
                                                        <i class="fas fa-edit" style="color: #ffffff"></i>
                                                    </a>
                                                </button>
                                                <button type="button" title="Supprimer Jour Ferie" class="deleteJourFerie btn btn-sm btn-danger btn-simple btn-link <?= ($jourFerie->dateJourFerie <= date('Y-m-d')) ? "invisible" : "" ?>"
                                                    
                                                    data-id="<?= $jourFerie->idJourFerie ?>" data-nom="<?= $jourFerie->nomJourFerie ?>" onclick="deleteJourFerie(this)">
                                                    <i class="fas fa-trash" style="color: #ffffff"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
        <?php
            }
            else {
                echo "<div class='col-md-12 p-5 alert alert-danger mb-0 text-center font-weight-bold'> <h1 class='m-5 p-5 col-md-12'> <i class='fas fa-exclamation-triangle mr-2'></i> Aucun jour ferie pour " . $annee . " à " . $site->nomSite . "</h1> </div>";
            }
        ?>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteJourFerieModal" tabindex="-1" role="dialog" aria-labelledby="deleteJourFerieModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-white border-0">
                <h5 class="modal-title text-danger w-100 text-center" id="deleteJourFerieModalLabel">
                    Supprimer le projet
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <h1 class="font-weight-bold mb-4"><i class='fas fa-exclamation-triangle mr-2'></i></h1>
                <h4>
                    <span id="jourFerieNameToDelete"></span> <?= $annee . " à " . $site->nomSite ?>
                </h4>
                <p>En confirmant, vous supprimerez les informations concernant cet évènement et vous ne pourrez plus les recupérer.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" id="confirmDelete" class="btn btn-danger px-4">
                    Confirmer suppression
                </button>
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
                <h3 id="msgLoading"></h3>
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
                    <h3 id="msgSuccess" class="" style="color:green">SUCCESS !!</h3>
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
                    <h3 id="msgError" class="" style="color:red">ECHEC <i class='fas fa-exclamation-triangle mr-2'></i></h3>
                    <button onclick="" class="btn btn-danger" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= URLROOT ?>/public/assets/js/jourFerie/jourFerie.js"></script>
<script src="<?= URLROOT ?>/public/assets/vendor/jquery/jquery.min.js"></script>