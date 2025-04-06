<?php
// $hidden = (isset($etape) && $etape != "") ? "" : "hidden";
// $hiddenFinal = (isset($etape) && $etape != "") ? "hidden" : "";
$active = "red";
?>

<!-- ======= Avantages Section ======= -->

<div class="section-title">
    <div class="row">
        <div class="col-md-6">
            <h2 class="uppercase"><span>

                    <i class="fa fa-regular fa-folder-open" style="color: #c00000"></i>
                </span> <?= $titre ?> </h2>
        </div>
        <div class="col-md-6">
            <div class="float-right mt-0 mb-3">
                <a type="button" rel="tooltip" title="agenda" href="<?= linkto('GestionInterne', 'agenda') ?>"
                    class="btn btn btn-sm btn-red  ml-1">
                    <i class="fas fa-plus" style="color: #ffffff"></i>
                    Voir mon agenda
                </a>
                <a hidden type="button" rel="tooltip" title="Paramétrer"
                    href="<?= linkto('GestionInterne', 'parametrageSubvention') ?>"
                    class="btn btn btn-sm btn-red  ml-1">
                    <i class="fas fa-cog" style="color: #ffffff"></i>
                    Paramétrage
                </a>
            </div>
        </div>
    </div>

</div>

<!-- DataTales Example -->
<div class="card shadow mb-4 col-md-12 ">
    <input type="hidden" name="URLROOT" id="URLROOT" value="<?= URLROOT ?>">
    <div class="card-header bg-secondary text-white">
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-center font-weight-bold" id="titre"> Mon agenda
                </h2>
            </div>
        </div>
    </div>
</div>


<!-- Modal de suppression -->
<div class="modal fade" id="deleteProjectModal" tabindex="-1" role="dialog" aria-labelledby="deleteProjectModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-white border-0">
                <h5 class="modal-title text-danger w-100 text-center" id="deleteProjectModalLabel">
                    Supprimer le projet <span id="projectNumberToDelete"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <h4 class="font-weight-bold mb-4">ATTENTION !</h4>
                <p>Cette action est irréversible. En confirmant, toutes les données associées à ce projet seront
                    définitivement supprimées et ne pourront pas être récupérées.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" id="confirmDelete" class="btn btn-danger px-4">
                    Confirmer suppression
                </button>
            </div>
        </div>
    </div>
</div>
