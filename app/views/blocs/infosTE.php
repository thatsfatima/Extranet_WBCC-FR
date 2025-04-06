<div class="card">
    <div class="card-header bg-danger font-weight-bold text-white">
        <div class="row">
            <div class="col-md-9">
                <?= $numberBloc ?>. RESUME TELE-EXPERTISE
            </div>
            <div class="col-md-3">
                <button <?= $hiddenContact ?> type="button" rel="tooltip" title="Enregistrer les modifications"
                    onclick="onClickSaveInfoExpertise()" class="btn btn-sm btn-dark btn-simple btn-link ">
                    <i class="fa fa-save" style="color: #ffffff"></i>
                </button>
                <button <?= ($op->rapportTeleExpertise == "" || $op->rapportTeleExpertise == null) ? "hidden" : "" ?>
                    type="button" rel="tooltip" title="Voir la teleExpertise"
                    onclick="popitup('<?= $op->rapportTeleExpertise ?>', 'RAPPORT TE')"
                    class="btn btn-sm btn-dark btn-simple btn-link ">
                    <i class="fa fa-eye" style="color: #ffffff"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body bg-white">
        <div class="row">
            <div class="col-md-6">
                <div>
                    <label for="">N° Police</label>
                </div>
                <div>
                    <input readonly type="text" class="form-control" value="<?= $numPolice ?>" id="numPolice">
                </div>
            </div>
            <div class="col-md-6">
                <div>
                    <label for="">Date Sinistre</label>
                </div>
                <div>
                    <input readonly type="text" class="form-control" value="<?= $dateSinistre ?>" id="dateSinistre">
                </div>
            </div>
        </div>
        <div class="mt-2">
            <div>
                <label for="">Nature Sinistre</label>
            </div>
            <div>
                <input readonly type="text" class="form-control" value="<?= $typeIntervention  ?>" id="natureSinistre">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <div>
                    <label for="">Origine du Sinistre</label>
                </div>
                <div>
                    <textarea class="form-control" rows="1" id="causeSinistre"><?= $origine ?></textarea>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <div>
                    <label class="mb-0" for="">Commentaire & Circonstances</label>
                    <div class="mt-0">
                        <small>(dites-nous tout ce que vous savez du sinistre) </small>
                    </div>
                </div>
                <div>
                    <textarea class="form-control" rows="4" id="circonstances"><?= $circonstances ?></textarea>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <div>
                    <label for="">Description des dommages</label>
                </div>
                <div>
                    <textarea class="form-control" rows="3" id="descriptionSinistre"><?= $description ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>