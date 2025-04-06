<div class="card mb-2">
    <div class="card-header bg-danger font-weight-bold text-white">
        <?= $numberBloc ?>. INFOS OPPORTUNITE (<?= $op->name ?>)
    </div>
    <div class="card-body bg-white" style="background-color:whitesmoke;">

        <div class="row mb-1">
            <span class="offset-1 col-md-10 p-2">Date Ouverture</span>
            <span class="offset-1 col-md-10 border border-2 p-2"><?= $op->createDate ?></span>
        </div>

        <div class="row mb-1">
            <span class="offset-1 col-md-10 p-2">Type</span>
            <span
                class="offset-1 col-md-10 border border-2 p-2"><?= $op->type == "Sinistres" ? "Gestion de Sinistres" : "A.M.O." ?></span>
        </div>
        <div class="row mb-1">
            <span class="offset-1 col-md-10 p-2">Partie Concernée</span>
            <span class="offset-1 col-md-10 border border-2 p-2"><?= $op->typeSinistre ?></span>
        </div>
        <div class="row mb-1">
            <span class="offset-1 col-md-10 p-2">Donneur d'ordre</span>
            <span class="offset-1 col-md-10 border border-2 p-2"><?= $op->nomDO ?></span>
        </div>
        <div class="row mb-1">
            <span class="offset-1 col-md-10 p-2">Gest Imm/App</span>
            <span class="offset-1 col-md-10 border border-2 p-2"><?= $op->nomGestionnaireAppImm ?></span>
        </div>
        <div class="row mb-1">
            <span class="offset-1 col-md-10 p-2">Numéro du sinistre</span>
            <span class="offset-1 col-md-10 border border-2 p-2"><?= $numSinistre ?></span>
        </div>


    </div>
</div>