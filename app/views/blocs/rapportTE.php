<div class="card">
    <div class="card-header bg-danger font-weight-bold text-white">
        <div class="row">
            <div class="col-md-8">
                <?= $numberBloc ?>. RAPPORT Tele - Expertise
            </div>
            <div class="col-md-4"
                <?= ($op->rapportTeleExpertise == null || $op->rapportTeleExpertise == "") ? "hidden" : "" ?>>
                <button class="btn btn-secondary"
                    onclick="popitup('<?= $op->rapportTeleExpertise ?>', 'RAPPORT TE')">voir
                    le rapport</button>
            </div>
        </div>
    </div>
    <div class="card-body text-center">
        <embed
            src="<?= ($op->rapportTeleExpertise == null || $op->rapportTeleExpertise == "") ? "" : URLROOT . '/public/documents/opportunite/' . $op->rapportTeleExpertise ?>"
            width="100%" height="520vh" />
    </div>
</div>