<div class="card">
    <div class="card-header bg-danger font-weight-bold text-white">
        <div class="row container">
            <div class="col-6 col-md-7 col-sm-6 col-lg-8 float-left">
                <?= $numberBloc ?>. INFOS PAP
            </div>
            <div class="col-6 col-md-5 col-sm-6 col-lg-4 " <?= $hiddenPap ?>>
                <a style="box-shadow: 2px 2px 5px 2px #C00000" class="col-md-12 btn btn-dark"
                    onclick=" window.open('<?= URLROOT . '/public/documents/pap/rapportTerrain/' . ($pap ? $pap->rapportFile : '') ?>')">
                    Voir le PAP</a>
            </div>
        </div>
    </div>
    <div class="card-body" style="background-color:whitesmoke;">
        <div <?= $pap ? "" : "hidden" ?>>
            <div class="row">
                <div class="col-md-6">
                    <div>
                        <label for="">Date Visite <small class="text-danger">*</small></label>
                    </div>
                    <div>
                        <input type="text" readonly class="form-control"
                            value="<?= ($pap) ? date('d/m/Y H:i', strtotime($pap->dateVisite))  : "" ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div>
                        <label for="">Commercial</label>
                    </div>
                    <div>
                        <input type="text" readonly class="form-control" value="<?= ($pap) ? $pap->auteur  : ""  ?>">
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <div>
                        <label for="">Nature Sinistre</label>
                    </div>
                    <div>
                        <input type="text" readonly class="form-control"
                            value="<?= ($op) ? $op->typeIntervention  : ""  ?>">
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <div>
                        <label for="">Origine <small class="text-danger">*</small></label>
                    </div>
                    <div>
                        <textarea rows="5" readonly class="form-control"
                            rows="1"><?= ($rt) ? $rt->precisionComplementaire  : "" ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-1 p-2 font-weight-bold text-center text-danger" <?= $pap ? "hidden" : "" ?>>
            <span class="text-center">PAS DE VISITE PAP</span>
        </div>
    </div>
</div>