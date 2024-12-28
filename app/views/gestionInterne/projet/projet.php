<?php
$active = "red";
?>
<!-- ======= Avantages Section ======= -->
<div class="section-title mb-0">
    <h2><button onclick="document.location.href='<?= URLROOT ?>/GestionInterne/indexProjet'"><i
                class="fas fa-fw fa-arrow-left" style="color: #c00000"></i></button> <span><i
                class="fa fa-regular fa-folder-open" style="color: #c00000"></i></span> gestion projet</h2>
</div>

<div class="row mt-0">
    <div class="<?= $projet ? "col-md-6" : "col-md-12" ?> text-left m-0 p-0">
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
                            <div class="col-md-12 mb-1 mt-3">
                                <div class="col-md-12">
                                    <label class="font-weight-bold" for="">Nom du projet</label>
                                </div>
                                <input required type="text"
                                    value="<?= ($projet) ?  "$projet->idProjet" : "0" ?>"
                                    name="idProjet" class="shadow " id="idProjet" hidden>
                                <div class="col-md-12">
                                    <input required type="text"
                                        value="<?= ($projet) ?  "$projet->nomProjet" : "" ?>"
                                        name="nomProjet" class="form-control w-100 rounded outline-none shadow-01 border" id="nomProjet">
                                </div>
                            </div>
                            <div class="col-md-12 mb-1 mt-4">
                                <div class="col-md-12">
                                    <label class="font-weight-bold" for="">Description du projet</label>
                                </div>
                                <div class="col-md-12">
                                    <textarea required type="text"
                                        name="descriptionProjet" class="height-4 w-100 rounded outline-none shadow-01 border" id="descriptionProjet"><?= ($projet) ?  "$projet->descriptionProjet" : "" ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-12 mb-1 mt-4">
                                    <label class="font-weight-bold fs-2" for="">Selectionner un Immeuble</label>
                                <div class="d-flex flex-row p-3 border shadow-02 rounded">
                                    <input class="border form-control bg-light rounded outline-none shadow-sm border <?= $projet ? 'w-50 mr-3' : 'w-75 mr-5' ?>" id="adresse"
                                        value="<?= $projet ? "$immeuble->adresse" : "" ?>" readonly/>
                                    <button class="btn mb-4 saveBtn flex space-x-3 text-white font-weight-bold self-end" onclick="onClickImmeuble()" type="button" rel="tooltip"
                                        title="Ajouter" class="btn btn btn-sm  ml-1 text-white">
                                        Charger une adresse
                                        <i class="fa">&#xf021;</i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2 mb-0 p-0">
                            <div class="col text-center">
                                <input name="valider" class="btn btn btn-md text-white saveBtn mt-4 font-weight-bold px-3" type="submit" value="Enregistrer" />
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

<script src="<?= URLROOT ?>/public/assets/vendor/jquery/jquery.min.js"></script>
<script type="text/javascript">
    const URLROOT = document.getElementById("URLROOT").value;
    var idEmp = 0;
    var actionCritere = "";
    let idCritere = "";
    let idDocument = "";
    let typeDelete = "";
    let idCondition = "";
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
    // function selectRow(row, table) {
    //         // Deselect all rows in both tables
    //     $('#dataTable16 tbody tr').removeClass('selected-row');  // Deselect rows in the first table
    //     $('#dataTable17 tbody tr').removeClass('selected-row');  // Deselect rows in the second table

    //             // Check which table was clicked
    //     if (table === 'immeuble') {
    //         // Reset the idApp field if an Immeuble row is selected
    //         $('#idApp').val(null);
    //     } else if (table === 'lot') {
    //         // Reset the idImmeuble field if a Lot row is selected
    //         $('#idImmeuble').val(null);
    //     }

    //         // Remove the 'selected-row' class from all rows in the selected table
    //         $(row).closest('table').find('tbody tr').removeClass('selected-row');

    //     // // Remove the 'selected-row' class from all rows
    //     // $('#dataTable16 tbody tr').removeClass('selected-row');

    //     // Add the 'selected-row' class to the clicked row
    //     $(row).addClass('selected-row');

    //     // Get the idImmeuble from the clicked row's data-id attribute
    //     var selectedId = $(row).data('id');

    //     // Set the selected ID in the appropriate hidden input
    //     if (table === 'immeuble') {
    //         $('#idImmeuble').val(selectedId);
    //     } else if (table === 'lot') {
    //         $('#idApp').val(selectedId);
    //     }

    //     // Optional: Log the selected ID for debugging
    //     console.log("Selected Immeuble ID: " + selectedId);
    // }
    //CONDITION
    function onClickCondition(id, indexCritere) {
        idCritere = id;
        $("#titreCondition").text(
            "Ajout d'une condition au critere N°" + indexCritere
        );
        $('#modalCondition').modal('show');
    }

    function onChangeCondition() {
        let typeCondition = document.getElementById("typeCondition").value;
        if (typeCondition == "Autre") {
            document.getElementById("divAutreCondition").removeAttribute("hidden");
        } else {
            document.getElementById("divAutreCondition").setAttribute("hidden", "hidden");
        }
    }

    function saveCondition() {
        var idSubvention = document.getElementById('idSubvention').value;
        var typeCondition = document.getElementById('typeCondition').value;
        var operateurCondtion = document.getElementById('operateurCondtion').value;
        var valeurCondition = document.getElementById('valeurCondition').value;
        var autreTypeCondition = document.getElementById('autreTypeCondition').value;
        if (typeCondition != "" && valeurCondition != "" && typeCondition != "" && (typeCondition != "Autre" || (
                typeCondition ==
                "Autre" && autreTypeCondition != ""))) {
            console.log(typeCondition);

            //SAVE
            $.ajax({
                url: '<?= URLROOT . "/public/json/critere.php?action=saveCondition" ?>',
                method: 'POST',
                data: JSON.stringify({
                    idSubvention: idSubvention,
                    idCritere: idCritere,
                    idCondition: '0',
                    idTypeConditionF: typeCondition,
                    autreTypeCondition: autreTypeCondition,
                    valeurCondition: valeurCondition,
                    operateur: operateurCondtion.split(';')[1],
                    signeOperateur: operateurCondtion.split(';')[0],
                    idAuteur: '<?= $_SESSION["nomUser"]->idUtilisateur ?>'
                }),
                dataType: "JSON",
                beforeSend: function() {
                    $('#loadingModal').modal('show');
                },
                success: function(response) {
                    console.log("save critere");
                    console.log(response);
                    if (response == "1") {
                        setTimeout(() => {
                            $('#modalCondition').modal('hide');
                            $('#loadingModal').modal('hide');
                        }, 1000);
                        location.reload();
                    }
                },
                error: function(response) {
                    console.log("ERROR");
                    console.log(response);
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                    }, 1000);
                    $("#msgError").text(
                        "Erreur d'enregistrement !");
                    $('#errorOperation').modal('show');
                },
                complete: function() {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                    }, 1000);
                },
            });

        } else {
            $("#msgError").text(
                "Tous les champs sont obligatoires !");
            $('#errorOperation').modal('show');
        }
    }

    function deleteCondition() {
        $.ajax({
            url: '<?= URLROOT . "/public/json/critere.php?action=deleteConditionCritere" ?>',
            method: 'POST',
            data: JSON.stringify({
                idCondition: idCondition,
                idCritere: idCritere
            }),
            dataType: "JSON",
            beforeSend: function() {
                $('#loadingModal').modal('show');
            },
            success: function(response) {
                // console.log("delete condition");
                // console.log(response);
                if (response == "1") {
                    setTimeout(() => {
                        $('#modalDelete').modal('hide');
                        $('#loadingModal').modal('hide');
                    }, 1000);

                    location.reload();
                }
            },
            error: function(response) {
                console.log("ERROR");
                console.log(response);
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                }, 1000);
                $("#msgError").text(
                    "Erreur de suppression !");
                $('#errorOperation').modal('show');
            },
            complete: function() {
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                }, 1000);
            },
        });
    }



    //critere
    function onClickCritere(id) {
        if (id == 0) {
            actionCritere = "add";
            loadDataCritere(null, true);
        } else {
            actionCritere = "edit"
            $.ajax({
                type: "GET",
                dataType: "JSON",
                url: `${URLROOT}/public/json/critere.php?action=find&id=${id}`,
                success: function(data) {
                    console.log(data);
                    if (data != undefined && data != null && data != "false") {
                        loadDataCritere(data, false);
                    } else {
                        $("#msgError").text(
                            "Impossible de charger les infos, contacter l'administrateur !"
                        );
                        $('#errorOperation').modal('show');
                    }
                },
                error: function(jqXHR, error, errorThrown) {
                    console.log("error");
                    console.log(jqXHR.responseText);
                    $("#msgError").text(
                        "Impossible de charger les infos, contacter l'administrateur !"
                    );
                    $('#errorOperation').modal('show');
                }
            });
        }
    }

    function loadDataCritere(data = null, readOnly = false) {
        if (data != null) {
            document.getElementById("idCritere").value = data['idCritere'];
            document.getElementById("typeCondition").value = data['idTypeConditionF'];
            document.getElementById("operateurCondtion").value = data['signeOperateur'] + ";" + data['operateur'];
            document.getElementById("valeurCondition").value = data['valeur'];
        } else {
            document.getElementById("idCritere").value = "";
            document.getElementById("typeCondition").value = "";
            document.getElementById("operateurCondtion").value = "";
            document.getElementById("valeurCondition").value = "";
        }
    }

    function saveCritere(index) {
        var idSubvention = document.getElementById('idSubvention').value;
        var idCritere = document.getElementById('idCritere' + index).value;
        var valeurCritere = document.getElementById('valeurCritere' + index).value;
        var typeValeurCritere = document.getElementById('typeValeurCritere' + index).value;
        if (valeurCritere != "" && typeValeurCritere != "") {
            //SAVE
            $.ajax({
                url: '<?= URLROOT . "/public/json/critere.php?action=saveCritere" ?>',
                method: 'POST',
                data: JSON.stringify({
                    idSubvention: idSubvention,
                    idCritere: idCritere,
                    valeurCritere: valeurCritere,
                    typeValeurCritere: typeValeurCritere,
                    idAuteur: '<?= $_SESSION["nomUser"]->idUtilisateur ?>'
                }),
                dataType: "JSON",
                beforeSend: function() {
                    $('#loadingModal').modal('show');
                },
                success: function(response) {
                    console.log("save critere");
                    console.log(response);
                    if (response == "1") {
                        setTimeout(() => {
                            $('#modalCondition').modal('hide');
                            $('#loadingModal').modal('hide');
                        }, 1000);
                        location.reload();
                    }
                },
                error: function(response) {
                    console.log("ERROR");
                    console.log(response);
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                    }, 1000);
                    $("#msgError").text(
                        "Erreur d'enregistrement !");
                    $('#errorOperation').modal('show');
                },
                complete: function() {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                    }, 1000);
                },
            });
        } else {
            $("#msgError").text(
                "Tous les champs sont obligatoires !");
            $('#errorOperation').modal('show');
        }
    }

    function deleteCritere() {
        var idSubvention = document.getElementById('idSubvention').value;
        $.ajax({
            url: '<?= URLROOT . "/public/json/critere.php?action=deleteCritereSubvention" ?>',
            method: 'POST',
            data: JSON.stringify({
                idSubvention: idSubvention,
                idCritere: idCritere
            }),
            dataType: "JSON",
            beforeSend: function() {
                $('#loadingModal').modal('show');
            },
            success: function(response) {
                // console.log("delete critere");
                // console.log(response);
                if (response == "1") {
                    setTimeout(() => {
                        $('#modalDelete').modal('hide');
                        $('#loadingModal').modal('hide');
                    }, 1000);

                    location.reload();
                }

            },
            error: function(response) {
                console.log("ERROR");
                console.log(response);
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                }, 1000);
                $("#msgError").text(
                    "Erreur de suppression !");
                $('#errorOperation').modal('show');
            },
            complete: function() {
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                }, 1000);
            },
        });
    }

    //DOCUMENT
    function onClickImmeuble() {
        $('#modalImmeuble').modal('show');
    }

    function saveDocument() {
        var idSubvention = document.getElementById('idSubvention').value;
        var docs = document.getElementsByName('checkDoc');
        let tabDocSub = [];
        for (let index = 0; index < docs.length; index++) {
            if (docs[index].checked) {
                let elt = {
                    "idSubvention": idSubvention,
                    "idDocumentRequis": docs[index].value,
                    "etat": $(".etat" + docs[index].value + ":radio:checked").val()
                }
                tabDocSub.push(elt);
            }
        }

        $.ajax({
            url: '<?= URLROOT . "/public/json/subvention.php?action=saveDocumentRequisSubvention" ?>',
            method: 'POST',
            data: JSON.stringify(tabDocSub),
            dataType: "JSON",
            beforeSend: function() {
                $('#loadingModal').modal('show');
            },
            success: function(response) {
                console.log("save documents");
                console.log(response);
                if (response == "1") {
                    setTimeout(() => {
                        $('#modalDocument').modal('hide');
                        $('#loadingModal').modal('hide');
                    }, 1000);

                    location.reload();
                }
            },
            error: function(response) {
                console.log("ERROR");
                console.log(response);
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                }, 1000);
                $("#msgError").text(
                    "Erreur d'enregistrement !");
                $('#errorOperation').modal('show');
            },
            complete: function() {
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                }, 1000);
            },
        });
    }

    function onClickDelete(id, type, idCrit = "", index = "") {
        typeDelete = type;
        if (typeDelete == 'critere') {
            idCritere = id
            $("#textDelete").text(
                "Voulez-vous supprimer le critére N°" + index + " ?");
        } else {
            if (typeDelete == "document") {
                $("#textDelete").text(
                    "Voulez-vous supprimer ce document ?");
                idDocument = id
            } else {
                if (typeDelete == "condition") {
                    $("#textDelete").text(
                        "Voulez-vous supprimer cette condition du critére N°" + index + " ?");
                    idCondition = id
                    idCritere = idCrit
                }
            }
        }
        $('#modalDelete').modal('show');
    }

    function confirmDelete() {
        console.log(typeDelete)
        if (typeDelete == "critere") {
            deleteCritere()
        } else {
            if (typeDelete == "document") {
                deleteDocument()
            } else {
                if (typeDelete == "condition") {
                    deleteCondition()
                }
            }
        }
    }



    function deleteDocument() {
        var idSubvention = document.getElementById('idSubvention').value;
        $.ajax({
            url: '<?= URLROOT . "/public/json/subvention.php?action=deleteDocumentSubvention" ?>',
            method: 'POST',
            data: JSON.stringify({
                idSubvention: idSubvention,
                idDocumentRequis: idDocument
            }),
            dataType: "JSON",
            beforeSend: function() {
                $('#loadingModal').modal('show');
            },
            success: function(response) {
                // console.log("delete critere");
                // console.log(response);
                if (response == "1") {
                    setTimeout(() => {
                        $('#modalDelete').modal('hide');
                        $('#loadingModal').modal('hide');
                    }, 1000);

                    location.reload();
                }

            },
            error: function(response) {
                console.log("ERROR");
                console.log(response);
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                }, 1000);
                $("#msgError").text(
                    "Erreur de suppression !");
                $('#errorOperation').modal('show');
            },
            complete: function() {
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                }, 1000);
            },
        });
    }
</script>