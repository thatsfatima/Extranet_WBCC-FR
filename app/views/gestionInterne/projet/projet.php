<?php
$active = "red";
?>
<!-- ======= Avantages Section ======= -->
<div class="section-title mb-0">
    <div class="row">
        <div class="col-md-6">
        <h2><button onclick="document.location.href='<?= URLROOT ?>/GestionInterne/indexProjet'"><i
        class="fas fa-fw fa-arrow-left" style="color: #c00000"></i></button> <span><i
        class="fa fa-regular fa-folder-open" style="color: #c00000"></i></span> gestion projet</h2>
        </div>
        <div class="col-md-6 <?= ($projet) ? '' : 'hidden' ?>">
            <div class="float-right mt-0 mb-3">
                <a type="button" rel="tooltip" title="Exporter"
                    class="btn btn btn-sm btn-red  ml-1" onclick="onclickExporter()">
                    <i class="fas fa-print" style="color: #ffffff"></i>
                    Exporter
                </a>
                <a type="button" rel="tooltip" title="PDF"
                    href="<?= linkto('GestionInterne', 'parametrageSubvention') ?>"
                    class="btn btn btn-sm btn-red  ml-1">
                    <i class="fas fa-file-pdf" style="color: #ffffff"></i>
                    Pdf
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-0">
    <div class="<?=  "col-md-12" ?> text-left m-0 p-0">
        <div class="row  mt-0 p-0">
            <fieldset>
                <legend class=" text-center col-md-12 legend font-weight-bold mb-4 pt-2 shadow-02 font-weight-bold text-uppercase"><i
                        class="icofont-info-circle my-1"></i>1-Projet</legend>
                <form class="mt-0 p-0" id="msform" method="POST"
                    action="<?= linkTo("GestionInterne", "saveProjet") ?>">
                    <div class="col-md-12 px-0 mt-0">

                        <input type="hidden" id="idImmeuble" name="idImmeuble" value="<?= $projet ? "$projet->idImmeuble" : '' ?>">

                        <input type='text' id='idUtilisateur' class='form-control'
                            value='<?= $_SESSION['connectedUser']->idUtilisateur ?>' hidden>
                        <input type='text' id='auteur' class='form-control'
                            value='<?= $_SESSION['connectedUser']->fullName ?>' hidden>
                        <input type='text' id='numeroAuteur' class='form-control'
                            value='<?= $_SESSION['connectedUser']->numeroContact ?>' hidden>
                        <input type="hidden" name="URLROOT" id="URLROOT" value="<?= URLROOT ?>">
                        <div class="row text-left mt-0">
                            <div class="row w-100">
                                <div class="col-md-6 mb-1 mt-3">
                                    <div class="col-md-12">
                                        <label class="font-weight-bold" for="">Nom du projet</label>
                                    </div>
                                    <input required type="text"
                                        value="<?= ($projet) ?  "$projet->idProjet" : "0" ?>"
                                        name="idProjet" class="shadow " id="idProjet" hidden>
                                    <div class="col-md-12">
                                        <input required type="text"
                                            value="<?= ($projet) ?  "$projet->nomProjet" : "" ?>"
                                            name="nomProjet" class="form-control rounded outline-none shadow-01 border" id="nomProjet">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-1 mt-3">
                                    <div class="col-md-12">
                                        <label class="font-weight-bold fs-2" for="">Selectionner un Immeuble</label>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="d-flex flex-row px-3 rounded">
                                            <input class="form-control rounded outline-none shadow-01 border w-75 mr-3 bg-white" id="adresse"
                                                value="<?= $projet ? "$immeuble->adresse" : "" ?>" readonly/>
                                            <button class="btn saveBtn flex space-x-3 text-white font-weight-bold w-50 px-0"  type="button" rel="tooltip"
                                                title="Ajouter" class="btn btn btn-sm  ml-1 text-white" onclick="onClickImmeuble()" >
                                                Charger 
                                                <i class="fa">&#xf021;</i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="w-100 my-5 pr-5 height-4">
                                <div class="col-md-12">
                                    <label class="font-weight-bold" for="">Description du projet</label>
                                </div>
                                <div class="col-md-12 h-100">
                                    <textarea required type="text"
                                        name="descriptionProjet" class="h-100 w-100 rounded outline-none shadow-01 border" id="descriptionProjet"><?= ($projet) ?  "$projet->descriptionProjet" : "" ?></textarea>
                                </div>
                            </div>
                            
                        </div>
                        <div class="row mt-2 mb-0 p-0">
                            <div class="col text-center">
                                <input name="valider" class="btn btn btn-md text-white saveBtn mt-4 font-weight-bold px-3" type="submit" value="Enregistrer" onclick="onclickExporter()"/>
                            </div>
                        </div>
                    </div>
                </form>
            </fieldset>
        </div>
    </div>
</div>

<!-- Modal Immeuble -->
<div class="modal fade" id="modalImmeuble" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header legen border-top-0">
                <h5 class="modal-title font-weight-bold text-white">Liste des immeubles</h5>
                <button class="close text-white btn" type="button" data-dismiss="modal" aria-label="Close"
                    style='font-size:28px; color:white; border: none !important; padding: 0px;'>
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="flex p-3 table-responsive table-1 border shadow-02 rounded">
                    <table class="table table-bordered p-3" id="dataTable16" cellspacing="0">
                        <thead>
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Code de l'immeuble</th>
                                <th>Type de l'immeuble</th>
                                <th>Adresse</th>
                                <th>Code postal</th>
                                <th>Ville</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $i = 0;
                                foreach ($immeubles as $immeuble) {
                                    $i++;
                            ?>
                            <tr class="p-0 m-0 " onclick="selectImmeuble(this)" data-id="<?= $immeuble->idImmeuble ?>" data-adresse="<?= $immeuble->adresse ?>">
                                <td>
                                    <i class="<?= ($projet && $projet->idImmeuble == $immeuble->idImmeuble) ? 'fas fa-check-square text-primary' : 'fa fa-square text-white border border-1' ?> "></i>
                                </td>
                                <td><?= $i ?></td>
                                <td><?= $immeuble->codeImmeuble ?></td>
                                <td><?= $immeuble->typeImmeuble ?></td>
                                <td><?= $immeuble->adresse ?></td>
                                <td><?= $immeuble->codePostal ?></td>
                                <td><?= $immeuble->ville ?></td>
                            </tr>
                            <?php    }
                            ?>
                        </tbody>                
                    </table>
                </div>
            </div>
            <div class="modal-footer" <?= sizeof($immeubles) != 0 ? "" : " hidden" ?>>
                <input type="hidden" name="idImmeubleSelected" id="idImmeubleSelected"
                    value="">
                <input type="hidden" name="adresseImmeubleSelected" id="adresseImmeubleSelected"
                    value="">
                <button type="button" onclick="saveImmeuble()" class="btn btn btn-md text-white saveBtn mt-4 font-weight-bold px-3 hidden" id="buttonSaveImmeuble">Valider</button>
            </div>
        </div>
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
                <h3>Chargement...</h3>
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

<!-- modal d'export -->
<div class="modal fade" id="exporterModal" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h3>Exporter</h3>
                <div class="spinner-border text-danger" style="width: 5vw; height: 10vh;">
                </div>
                <br><br><br>
            </div>
        </div>
    </div>
</div>

<script src="<?= URLROOT ?>/public/assets/vendor/jquery/jquery.min.js"></script>
<script type="text/javascript">
    const URLROOT = document.getElementById("URLROOT").value;
    var idEmp = 0;
    var actionCritere = "";
    let idCritere = "";
    let idDocument = "";
    let typeDelete = "";
    let idCondition = "";

    function onclickExporter() {
        var idProjet = document.getElementById("idProjet").value;
        var idImmeuble = document.getElementById("idImmeuble").value;
        var idAuteur = document.getElementById("idUtilisateur").value;
        var auteur = document.getElementById("auteur").value;
        var numeroAuteur = document.getElementById("numeroAuteur").value;
        $.ajax({
            method : "POST",
            url: URLROOT + "/public/json/projet.php?action=saveDocumentPDF",
            data: {
                idProjet: idProjet,
                idImmeuble: idImmeuble,
            },
            beforeSend: function() {
                $('#exporterModal').modal('show');
            },
            success: function(response) {
                console.log("exporter pdf");
                console.log(response);
                if (response == "1") {
                    setTimeout(() => {
                        $('#exporterModal').modal('hide');
                    }, 1000);
                    location.reload();
                }
            },
            error: function(response) {
                console.log("ERROR");
                console.log(response);
                setTimeout(() => {
                    $('#exporterModal').modal('hide');
                }, 1000);
                $("#msgError").text(
                    "Erreur d'enregistrement !");
                $('#errorOperation').modal('show');
            },
            complete: function() {
                setTimeout(() => {
                    $('#exporterModal').modal('hide');
                }, 1000);
            },
        });
    }

    function onClickImmeuble() {
        $('#modalImmeuble').modal('show');
    }

    function saveImmeuble() {
        var idImmeuble = document.getElementById('idImmeubleSelected').value;
        var adresse = document.getElementById('adresseImmeubleSelected').value;
        $('#modalImmeuble').modal('hide');
        $('#adresse').val(adresse);
        $('#idImmeuble').val(idImmeuble);
    }
    function selectImmeuble(row) {
        // Deselect Immeuble
        $('#dataTable16 tbody tr td i').removeClass('fas fa-check-square text-primary');
        $('#dataTable16 tbody tr td i').addClass('fa fa-square text-white border border-1');

        // Deselect the selected immeuble
        $(row).closest('table').find('tbody tr td i').removeClass('fas fa-check-square text-primary');
        $('#dataTable16 tbody tr td i').addClass('fa fa-square text-white border border-1');
        //Select immeuble
        $(row).find('td i').addClass('fas fa-check-square text-primary');
        $(row).find('td i').removeClass('fa fa-square text-white border border-1');

        $('#buttonSaveImmeuble').removeClass('hidden');

        var selectedId = $(row).data('id');
        var selectedAdresse = $(row).data('adresse');

        // Set the selected ID in the appropriate hidden input
        $('#idImmeubleSelected').val(selectedId);
        $('#adresseImmeubleSelected').val(selectedAdresse);

        // Optional: Log the selected ID for debugging
        console.log("Selected Immeuble ID: " + selectedId);
        console.log("Selected Immeuble adresse: " + selectedAdresse);
    }

</script>