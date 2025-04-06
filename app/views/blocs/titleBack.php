<div class="section-title mb-0">
    <h2 class="mb-0"><button onclick="history.back()"><i class="fas fa-fw fa-arrow-left"
                style="color: #c00000"></i></button><span><i class="fas fa-fw fa-folder" style="color: #c00000"></i>
            <?= isset($title) ? $title . "/" : '' ?>
            <?= $op != null ? $op->name : $numProvisoire  ?>
            <?= ($op != null) ? " / " . $adresse . " / " . $op->contactClient . (isset($esi) && $esi != "" && $esi != null ? " / " . $esi : "") : "" ?>
        </span> </h2>
</div>
<div class="pull-right" style="text-align: right;">
    <a <?= isset($otherOpWSameContact) && sizeof($otherOpWSameContact) != 0 ? "" : "hidden" ?>
        style="box-shadow: 2px 2px 5px 2px #C00000" class="btn btn-dark" onclick="showModalOthersOp()">
        Autres OP(<?= isset($otherOpWSameContact) && sizeof($otherOpWSameContact) ?>)</a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <a style="box-shadow: 2px 2px 5px 2px #C00000" class="btn btn-dark" onclick="onModalIncident()">
        Sign. Incident
    </a>
    &nbsp;&nbsp;
    <a <?= $op  && (($op->status == 'Lost' || $op->status == 'Won') ||  $op->demandeCloture == '1') ? "" : "hidden"  ?>
        style="box-shadow: 2px 2px 5px 2px #C00000" class="btn btn-dark"
        onclick="onClickReouvertureOP()"><?= $op->demandeCloture == '1' ? "Annuler Clôture" : "Rouvrir" ?></a>
    &nbsp;&nbsp;
    <a <?= $op  && (($op->status == 'Open' || $op->status == 'Inactive')) ? "" : "hidden"  ?>
        style="box-shadow: 2px 2px 5px 2px #C00000" class="btn btn-dark"
        onclick="onClickCloturerOP()"><?= ($role == "3" || $role == "25" || $role == "9" ?  "Demande Clôture" : "Clôturer OP") ?></a>
    <br><br>
</div>