<div class="card">
    <div class="card-header bg-danger font-weight-bold text-white">
        <div class="row">
            <div class="col-md-9">
                <?= $numberBloc ?>. Adresse
            </div>
        </div>
    </div>
    <div class="card-body">
        <input type="text" id="idImmeuble" value="<?= $immeuble ? $immeuble->idImmeuble  : "0" ?>" hidden>
        <input type="text" id="numeroImmeuble" value="<?= $immeuble ? $immeuble->numeroImmeuble  : "" ?>" hidden>
        <input type="text" id="idApp" value="<?= $op->app ? $op->app->idApp : "0" ?>" hidden>
        <input type="text" id="numeroApp" value="<?= $op->app ? $op->app->numeroApp : "" ?>" hidden>
        <input type="text" id="idAppCon" value="<?= $op->appCon  ? $op->appCon->idAppCon : "0" ?>" hidden>
        <div class="col-md-12">
            <div>
                <label for="">Adresse</label>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <input class="form-control" type="text" name="" value="<?= $op->adresse ?>" readonly>
                </div>

            </div>
        </div>
        <div class="col-md-12">
            <div>
                <label for="">Code Postal</label>
            </div>
            <div>
                <input class="form-control" type="text" name="" value="<?= $op->cp ?>" readonly>
            </div>
        </div>
        <div class="col-md-12">
            <div>
                <label for="">Ville</label>
            </div>
            <div>
                <input class="form-control" type="text" name="" value="<?= $op->ville ?>" readonly>
            </div>
        </div>
        <div class="row mt-2" <?= $hiddenApp != "" ? "" : "hidden" ?>>
            <div class="col-md-12">
                <div>
                    <label for="">Localisation</label>
                </div>
                <div>
                    <input class="form-control" type="text" name=""
                        value="<?= $app ? $app->libellePartieCommune : "" ?>">
                </div>
            </div>
            <div class="col-md-12">
                <div>
                    <label for="">Côté</label>
                </div>
                <div>
                    <input class="form-control" type="text" name="" id="cote2" value="<?= $app ? $app->cote : "" ?>">
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <input class="form-control" type="text" name="" id="idAppRV" value="<?= $app ? $app->idApp : "0" ?>" hidden>
            <input class="form-control" type="text" name="" id="numeroAppRV" value="<?= $app ? $app->numeroApp : "" ?>"
                hidden>
            <input class="form-control" type="text" name="" id="idAppConRV"
                value="<?= $op->appCon  ? $op->appCon->idAppCon : "" ?>" hidden>
            <div class="col-md-6">
                <div>
                    <label for="">N° Lot</label>
                </div>
                <div>
                    <input class="form-control" type="text" name="" id="lot2" value="<?= $app ? $app->lot : "" ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div>
                    <label for="">Bâtiment</label>
                </div>
                <div>
                    <input class="form-control" type="text" name="" id="batiment2"
                        value="<?= $app ? $app->batiment : "" ?>">
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <div>
                    <label for="">Etage</label>
                </div>
                <div>
                    <input class="form-control" type="text" name="" id="etage2" value="<?= $app ? $app->etage : "" ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div>
                    <label for="">Porte</label>
                </div>
                <div>
                    <input class="form-control" type="text" name="" id="porte2"
                        value="<?= $app ? $app->codePorte : "" ?>">
                </div>
            </div>
        </div>
    </div>
</div>