<div class="card">
    <div class="card-header bg-danger font-weight-bold text-white">
        <div class="row">
            <div class="col-md-8">
                <?= $numberBloc ?>. INFOS DECLARATION
            </div>
        </div>
    </div>
    <div class="card-body bg-white" style="background-color:whitesmoke;">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <?php
                    if ($op->dateDeclarationCieMail == "" || $op->dateDeclarationCieMail == null) {
                    ?>
                        <span class=" offset-1 col-md-10 text-danger">La première déclaration par mail n'a
                            pas
                            été faite ! </span>
                    <?php

                    } else {
                    ?>
                        <span class=" offset-1 col-md-10 p-2">Date premiére déclaration par mail</span>
                        <input readonly class="form-control offset-1 col-md-10 mt-2" type="text" name=""
                            id="dateDernielMail"
                            value="<?= date('d/m/Y à H:i', strtotime($op->dateDeclarationCieMail))  ?>">
                    <?php
                    }
                    ?>

                </div>
                <div class="row">
                    <?php
                    if ($op->dateDeclarationCie == "" || $op->dateDeclarationCie == null) {
                    ?>
                        <span class=" offset-1 col-md-10 text-danger">La déclaration par téléphone n'a pas
                            été faite ! </span>
                    <?php

                    } else {
                    ?>
                        <span class=" offset-1 col-md-10 p-2">Date dernière déclaration par téléphone</span>
                        <input readonly class="form-control offset-1 col-md-10 mt-2" type="text" name=""
                            id="dateDernielMail"
                            value="<?= ($op->dateDeclarationCie != "" || $op->dateDeclarationCie != null) ? date('d/m/Y à H:i', strtotime($op->dateDeclarationCie)) : ""  ?>">
                    <?php
                    }
                    ?>

                </div>
            </div>
        </div>
        <div class="row mt-1">

            <div class="col-md-12">
                <div class="row">
                    <span class="offset-1 col-md-10">Renseignez le numéro du sinistre</span>
                    <input type="hidden" name="" id="typeSinistre" value="<?= $op->typeSinistre ?>">
                    <input class="form-control offset-1 col-md-10 mt-2" type="text" name="" id="numeroSinistre"
                        value="<?= $numSinistre ?>">
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="offset-4 col-md-4">
                <button type="button" rel="tooltip" title="Ajouter un contact" onclick="EngNumeroSinistre()"
                    class="btn btn-success ml-2">
                    Valider
                </button>
            </div>
        </div>
    </div>
</div>