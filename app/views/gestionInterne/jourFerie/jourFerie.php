<?php
$active = "red";
?>
<!-- ======= Avantages Section ======= -->
<div class="section-title mb-0">
    <div class="row">
        <div class="col-md-6">
            <h2><a href="<?= linkto('GestionInterne', 'indexJourFerie') ?>?site=<?= $site->idSite ?>&annee=<?= $annee ?>"><button><i class="fas fa-fw fa-arrow-left"
                            style="color: #c00000"></i></button></a><?= $titre ?> </h2>
        </div>
    </div>
</div>

<div class="<?= "col-md-12" ?> text-left m-0 p-0">
    <div class="row  mt-0 p-0">
        <fieldset>
            <legend
                class="text-center col-md-12 legend font-weight-bold mb-0 py-3 shadow-02 font-weight-bold text-uppercase">
                <i class="fas  my-1"></i><?= $sousTitre ?>
            </legend>
            <div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="float-right mt-0 mb-3">
                            <a type="button" rel="tooltip" title="Ajouter" href="<?= linkto('GestionInterne', 'jourFerie', null) ?>?site=<?= $site->idSite ?>&annee=<?= $annee ?>"
                                class="btn btn btn-sm btn-red font-weight-bold ml-1 px-3">
                                <i class="fas fa-plus my-2" style="font-size: 120%; color: #ffffff"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <form class="mt-0 p-0" id="msform" method="POST" action="<?= linkTo("GestionInterne", "saveJourFerie") ?>?site=<?= $site->idSite ?>&annee=<?= $annee ?>">
                <div class="col-md-12 px-0 mt-0">
                    <input type="text" name="URLROOT" id="URLROOT" value="<?= URLROOT ?>" hidden>
                    <input type="text" name="URLROOT_GESTION_WBCC_CB" id="URLROOT_GESTION_WBCC_CB" value="<?= URLROOT_GESTION_WBCC_CB ?>" hidden>
                    <input type="hidden" id="idJourFerie" name="idJourFerie"
                        value="<?= $jourFerie ? "$jourFerie->idJourFerie" : '' ?>">
                    <input type="hidden" id="anneeJourFerie" name="anneeJourFerie"
                        value="<?= $jourFerie ? "$jourFerie->anneeJourFerie" : $annee ?>">
                    <input type="hidden" id="idSiteF" name="idSiteF"
                        value="<?= $jourFerie ? $jourFerie->idSiteF : $site->idSite ?>">
                    <input type="hidden" id="adresse1C" name="adresse1C"
                        value="<?= $site->nomSite ?>">
                    <input type='text' id='idUtilisateur' class='form-control'
                        value='<?= $_SESSION['connectedUser']->idUtilisateur ?>' hidden>
                    <input type='text' id='auteur' class='form-control'
                        value='<?= $_SESSION['connectedUser']->fullName ?>' hidden>
                    <input type='text' id='numeroAuteur' class='form-control'
                        value='<?= $_SESSION['connectedUser']->numeroContact ?>' hidden>
                    <input type="hidden" name="URLROOT" id="URLROOT" value="<?= URLROOT ?>">
                    <div class="row col-md-12 text-left mt-0">
                        <div class="col-md-6 mb-3">
                            <div class="col-md-12">
                                <label class="font-weight-bold" for="nomJourFerie">Nom de l'évènement</label>
                            </div>
                            <input required type="text" value="<?= ($jourFerie) ?  "$jourFerie->idJourFerie" : "0" ?>"
                                name="idJourFerie" class="shadow " id="idJourFerie" hidden>
                            <div class="col-md-12">
                                <input required type="text" value="<?= ($jourFerie) ?  "$jourFerie->nomJourFerie" : "" ?>"
                                    name="nomJourFerie" class="form-control rounded outline-none shadow-01 border"
                                    id="nomJourFerie">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="col-md-12">
                                <label class="font-weight-bold" for="dateJourFerie">Date de l'évènement</label>
                            </div>
                            <div class="col-md-12">
                                <input type="date" min="<?= $annee ?>-01-01" max="<?= $annee + 1 ?>-12-31" type="text" name="dateJourFerie" value="<?= ($jourFerie) ?  $jourFerie->dateJourFerie : ($annee . "-01-01") ?>"
                                    class="form-control rounded outline-none shadow-01 border"
                                    id="dateJourFerie" required>
                            </div>
                        </div>
                    </div>
                    <div class="row col-md-12 text-left mt-0">
                        <div class="col-md-6 mb-3">
                            <div class="col-md-12">
                                <label class="font-weight-bold" for="dateJourFerie">Payé :</label>
                            </div>
                            <div class="col-md-12">
                                <select name="payer" id="payer" class="form-control rounded outline-none shadow-01 border">
                                    <option value="<?= ($jourFerie && $jourFerie->Payer == 1) ? '1' : '0' ?>"><?= ($jourFerie && $jourFerie->Payer == 1) ? 'Payé' : 'Non-payé' ?></option>
                                    <option value="<?= ($jourFerie && $jourFerie->Payer == 1) ? '0' : '1' ?>"><?= ($jourFerie && $jourFerie->Payer == 1) ? 'Non-payé' : 'Payé' ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="col-md-12">
                                <label class="font-weight-bold" for="dateJourFerie">Chomé :</label>
                            </div>
                            <div class="col-md-12">
                                <select name="chomer" id="chomer" class="form-control rounded outline-none shadow-01 border">
                                    <option value="<?= ($jourFerie && $jourFerie->Chomer == 1) ? '1' : '0' ?>"><?= ($jourFerie && $jourFerie->Chomer == 1) ? 'Chomé' : 'Non-chomé' ?></option>
                                    <option value="<?= ($jourFerie && $jourFerie->Chomer == 1) ? '0' : '1' ?>"><?= ($jourFerie && $jourFerie->Chomer == 1) ? 'Non-chomé' : 'Chomé' ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-0 p-0">
                        <div class="col text-center">
                            <input name="valider" class="btn btn btn-md text-white saveBtn mt-4 font-weight-bold px-3" id="validerJourFerie" 
                                type="button" value="Enregistrer" />
                        </div>
                    </div>
                </div>
            </form>
        </fieldset>
    </div>
</div>

<!-- modal de chargement -->
<div class="modal fade" id="loadingModal" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-danger" style="width: 5vw; height: 10vh;">
                </div>
                <br><br><br>
                <h3 id="loadingText">Chargement...</h3>
            </div>
        </div>
    </div>
</div>

<!-- modal success -->
<div class="modal fade" id="successOperation" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <button onclick="" id="buttonConfirmContact" class="btn btn-success" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- modal Error -->
<div class="modal fade" id="errorOperation" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h3 id="msgError" class="" style="color:red">Email envoyé !!</h3>
                <button onclick="" id="buttonConfirmContact" class="btn btn-danger" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= URLROOT ?>/public/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= URLROOT ?>/public/assets/js/jourFerie/jourFerie.js"></script>