<div class="card">
    <div class="card-header bg-danger font-weight-bold text-white">
        <div class="row">
            <div class="col-md-4">
                3. RAPPORT FRT
            </div>
            <div class="col-md-4"
                <?= ($op->rapportFRT == null || $op->rapportFRT == "" && ($activityCFRT && $activityCFRT->isCleared == "True" && $op->devisFais == "0")) ? "" : "" ?>
                <?= (((isset($activityCFRT) && $activityCFRT && $activityCFRT->isCleared == "False") || $op->controleFRT == "0" || $op->controleFRT2 == "0" || $op->controleFRT3 == "0") ? "hidden" : (((isset($activityFD) && $activityFD && $activityFD->isCleared == "False") || (isset($devis) && !$devis) || $op->devisFais == "0")  ? "" :  "hidden")) ?>>
                <button class="btn btn-warning" onclick="onClickControlerFRT(0, 'fd')">
                    Rejeter FRT</button>
            </div>
            <div class="col-md-4" <?= $op->frtOutlook == "1" ? "" : "hidden" ?>>
                <button class="btn btn-primary">
                    FRT OUTLOOK</button>
            </div>
            <div class="col-md-4" <?= ($op->rapportFRT == null || $op->rapportFRT == "") ? "hidden" : "" ?>>
                <button class="btn btn-secondary" onclick="popitup('<?= $op->rapportFRT ?>', 'RAPPORT FRT')">voir
                    le rapport</button>
            </div>
        </div>
    </div>
    <div class="card-body text-center">
        <embed
            src="<?= ($op->rapportFRT == null || $op->rapportFRT == "") ? "" : URLROOT . '/public/documents/opportunite/' . $op->rapportFRT ?>"
            width="100%" height="520vh" />
    </div>
</div>