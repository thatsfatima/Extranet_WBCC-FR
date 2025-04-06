<?php
$active = "red";
?>

<!-- modal Confirm RGPD -->
<div class="modal fade" id="modalConfirmRGPD">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">Quel type de rapport voulez-vous afficher ?</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-check form-check-inline">
                    <input class="form-check-input typeRapport" type="radio" name="typeRapport" id="typeRapportR"
                        value="rgpd">
                    <label class="form-check-label" for="typeRapportR">RGPD </label>
                </div>
                <div class="form-check form-check-inline">
                    <input checked class="form-check-input typeRapport" type="radio" name="typeRapport"
                        id="typeRapportN" value="normal">
                    <label class="form-check-label" for="typeRapportN"> Normal </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="onClickConfirmRGPD()">Valider</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal confirmation de suppression article -->
<div class="modal fade" id="deleteLigneModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation de la suppression</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="idLigneSup">
                <p>Êtes-vous sûr de vouloir supprimer cette ligne ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtnLigne">Supprimer</button>
            </div>
        </div>
    </div>
</div>
<!-- ======= Modal Liste Articles CCTP ======= -->
<div class="modal fade" id="cctpModal" tabindex="-1" aria-labelledby="cctpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cctpModalLabel">Sélection des articles à acquérir</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="actionBarContainer" class="mb-3">
                    <!-- <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" id="selectAllcctp">
                                <i class="fas fa-check-square mr-1"></i>Tout sélectionner
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="deselectAllcctp">
                                <i class="fas fa-square mr-1"></i>Tout désélectionner
                            </button>
                        </div>
                        <div>
                            <span class="badge badge-info" id="selectedArticlesCount">0 article(s) sélectionné(s)</span>
                        </div>
                    </div> -->
                </div>
                <div class="table-responsive">
                    <input type="hidden" name="sectionIdActuel" id="sectionIdActuel">
                    <table id="dataTableArticles" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>N° Ligne</th>
                                <th>LIBELLE</th>
                                <th>UNITE</th>
                                <th>QUANTITE</th>
                                <th>PRIX</th>
                                <th>MONTANT</th>
                                <th>TAUX REMISE</th>
                                <th>MONTANT AR</th>
                                <th>TVA</th>
                                <th>MONTANT TTC</th>
                            </tr>
                        </thead>
                        <tbody id="cctpTableBody">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="validateArticlesSelection"
                    onclick="validateArticlesSelection()">Valider la sélection</button>
            </div>
        </div>
    </div>
</div>

<!-- ======= Avantages Section ======= -->
<div class="section-title mb-0">
    <div class="row">
        <div class="col-md-6">
            <h2><button onclick="document.location.href='<?= URLROOT ?>/Projet/indexProjet'"><i
                        class="fas fa-fw fa-arrow-left" style="color: #c00000"></i></button> <span><i
                        class="fa fa-regular fa-folder-open" style="color: #c00000"></i></span> gestion projet</h2>
        </div>
        <div class="col-md-6 <?= ($projet) ? 'hidden' : 'hidden' ?>">
            <div class="float-right mt-0 mb-3">
                <a type="button" rel="tooltip" title="Exporter" class="btn btn btn-sm btn-red  ml-1"
                    onclick="onclickExporter()">
                    <i class="fas fa-print" style="color: #ffffff"></i>
                    Exporter
                </a>
                <a type="button" rel="tooltip" title="PDF" href="<?= linkto('Projet', 'parametrageSubvention') ?>"
                    class="btn btn btn-sm btn-red  ml-1">
                    <i class="fas fa-file-pdf" style="color: #ffffff"></i>
                    Pdf
                </a>
            </div>
        </div>
    </div>
</div>

<div class="<?= "col-md-12" ?> text-left m-0 p-0">
    <div class="row  mt-0 p-0">
        <fieldset>
            <legend
                class=" text-center col-md-12 legend font-weight-bold mb-4 pt-2 shadow-02 font-weight-bold text-uppercase">
                <i class="icofont-info-circle my-1"></i>1-Projet
            </legend>
            <form class="mt-0 p-0" id="msform" method="POST" enctype="multipart/form-data"
                action="<?= linkTo("Projet", "saveProjet") ?>">
                <div class="col-md-12 px-0 mt-0">
                    <input type="hidden" id="idImmeuble" name="idImmeuble"
                        value="<?= $projet ? "$projet->idImmeubleF" : '' ?>">
                    <input type='text' id='idUtilisateur' class='form-control'
                        value='<?= $_SESSION['connectedUser']->idUtilisateur ?>' hidden>
                    <input type='text' id='auteur' class='form-control'
                        value='<?= $_SESSION['connectedUser']->fullName ?>' hidden>
                    <input type='text' id='numeroAuteur' class='form-control'
                        value='<?= $_SESSION['connectedUser']->numeroContact ?>' hidden>
                    <input type="hidden" name="URLROOT" id="URLROOT" value="<?= URLROOT ?>">
                    <div class="row text-left mt-0">
                        <div class="col-md-7 w-100">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="font-weight-bold" for="">Nom du projet</label>
                                </div>
                                <input required type="text" value="<?= ($projet) ?  "$projet->idProjet" : "0" ?>"
                                    name="idProjet" class="shadow " id="idProjet" hidden>
                                <div class="col-md-12">
                                    <input required type="text" value="<?= ($projet) ?  "$projet->nomProjet" : "" ?>"
                                        name="nomProjet" class="form-control rounded outline-none shadow-01 border"
                                        id="nomProjet">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="font-weight-bold fs-2" for="">Selectionner un Immeuble</label>
                                </div>
                                <div class="col-md-12">
                                    <div class="d-flex flex-row rounded space-x-3">
                                        <input
                                            class="form-control rounded outline-none shadow-01 border w-75 mr-1 bg-white"
                                            id="adresse"
                                            value="<?= $projet != null  && $immeuble != null ? "$immeuble->adresse" : "" ?>"
                                            readonly />
                                        <button class="btn saveBtn flex space-x-3 text-white font-weight-bold w-25 px-0"
                                            onclick="onClickImmeuble()" type="button" rel="tooltip" title="Ajouter"
                                            class="btn btn btn-sm  ml-1 text-white">
                                            Charger
                                            <i class="fa">&#xf021;</i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="font-weight-bold" for="">Description du projet</label>
                                </div>
                                <div class="col-md-12 height-4">
                                    <textarea required type="text" name="descriptionProjet"
                                        class="h-100 w-100 rounded outline-none shadow-01 border"
                                        id="descriptionProjet"><?= ($projet) ?  "$projet->descriptionProjet" : "" ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 w-100" <?= $immeuble != null ? "" : "hidden" ?>>
                            <!-- IMAGE IMMEUBLE -->
                            <div class="col-md-12 mb-4 px-1">
                                <label class="font-weight-bold">Photo Immeuble :
                                    <?= $immeuble != null ? $immeuble->adresse : "" ?> <a class="btn btn-danger fs-5"
                                        target="_blank"
                                        href='https://www.google.com/maps/place/<?= $immeuble != null ? $immeuble->adresse : "" ?>'><i
                                            class="fas fa-street-view"></i></a></label>
                                <div class='form-group row w-100'>
                                    <div style=" width: 100%;
                                       
                                        height: 300px;
                                        position:absolute;
                                        /* top:100px; */
                                        margin : 20px 0px 20px 0px;
                                        border: 2px dashed rgba(0,0,0,.3);
                                        border-radius: 20px;
                                        font-family: Arial;
                                        text-align: center;
                                        position: relative;
                                        line-height: 180px;
                                        font-size: 20px;
                                        color: rgba(0,0,0,.3);" id="dropContainer"
                                        style="border:1px solid black;height:100px;" class="row height-4">
                                        <?= $immeuble != null &&  $immeuble->photoImmeuble ?
                                            "<img src='" . URLROOT . "/public/documents/immeuble/" . $immeuble->photoImmeuble . "' alt='Photo Immeuble' class='w-100 h-100' >" :
                                            "<span class='text-muted text-center px-3'>Deposer ou choisir une image </span>" ?>
                                    </div>
                                    <div class="row space-x-1 ml-1" style="width: 120%;">
                                        <input type='file' id="file" class='col-md-10 form-control' name="file"
                                            accept='.jpg, .png, .jpeg, .JPG, .PNG, .JPEG'>
                                        <button type="button" rel="tooltip" title="Effacer la pièce jointe"
                                            onclick="deleteFileImage()"
                                            class="flex align-items-center justify-content-center col-md-1 btn btn-danger ">
                                            <i class="fas fa-trash" style="font-size: 100%;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-0 p-0">
                        <div class="col text-center">
                            <input name="valider" class="btn btn btn-md text-white saveBtn mt-4 font-weight-bold px-3"
                                type="submit" value="Enregistrer" />
                        </div>
                    </div>
                </div>
            </form>
        </fieldset>
    </div>
</div>

<!-- Modal Immeuble -->
<div class="modal fade" id="modalBibilotheque" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white border-top-0">
                <h5 class="modal-title font-weight-bold text-white">Liste des Textes Personnalisés</h5>
                <button class="close text-white btn" type="button" data-dismiss="modal" aria-label="Close"
                    style='font-size:28px; color:white; border: none !important; padding: 0px;'>
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="flex p-3 table-responsive table-1 border shadow-02 rounded">
                    <table class="table table-bordered p-3" id="dataTable11" cellspacing="0">
                        <thead>
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Titre</th>
                                <th>Contenu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            foreach ($bibliotheques as $bibli) {
                                $i++;
                            ?>
                            <tr class="p-0 m-0 " onclick="selectContenuSection(this)" data-id="<?= $bibli['id'] ?>"
                                data-title="<?= $bibli['titre'] ?>" data-contain="<?= $bibli['contenu'] ?>">
                                <td>
                                    <i class="<?= 'fa fa-circle text-white border border-1' ?> "></i>
                                </td>
                                <td><?= $i ?></td>
                                <td><?= $bibli['titre'] ?></td>
                                <td><?= $bibli['contenu'] ?></td>
                            </tr>
                            <?php    }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" <?= sizeof($bibliotheques) != 0 ? "" : " hidden" ?>>
                <input type="hidden" name="titreSectionSelected" id="titreSectionSelected" value="">
                <input type="hidden" name="contenuSectionSelected" id="contenuSectionSelected" value="">
                <button type="button" onclick="saveContenuSection()"
                    class="btn btn btn-md text-white saveBtn mt-4 font-weight-bold px-3 hidden"
                    id="buttonSaveContenuSection">Valider</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Immeuble -->
<div class="modal fade" id="modalImmeuble" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white border-top-0">
                <h5 class="modal-title font-weight-bold text-white">Liste des immeubles</h5>
                <button class="close text-white btn" type="button" data-dismiss="modal" aria-label="Close"
                    style='font-size:28px; color:white; border: none !important; padding: 0px;'>
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="flex p-3 table-responsive table-1 border shadow-02 rounded">
                    <table class="table table-bordered p-3" id="dataTable11" cellspacing="0">
                        <thead>
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Code de l'immeuble</th>
                                <th>Nom de l'immeuble</th>
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
                            <tr class="p-0 m-0 " onclick="selectImmeuble(this)" data-id="<?= $immeuble->idImmeuble ?>"
                                data-adresse="<?= $immeuble->adresse ?>" data-photo="<?= $immeuble->photoImmeuble ?>">
                                <td>
                                    <i
                                        class="<?= ($projet && $projet->idImmeubleF == $immeuble->idImmeuble) ? 'fas fa-check-square text-primary' : 'fa fa-square text-white border border-1' ?> "></i>
                                </td>
                                <td><?= $i ?></td>
                                <td><?= $immeuble->codeImmeuble ?></td>
                                <td><?= $immeuble->nomImmeubleSyndic ?></td>
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
                <input type="hidden" name="idImmeubleSelected" id="idImmeubleSelected" value="">
                <input type="hidden" name="adresseImmeubleSelected" id="adresseImmeubleSelected" value="">
                <input type="hidden" name="photoImmeubleSelected" id="photoImmeubleSelected" value="">
                <button type="button" onclick="saveImmeuble()"
                    class="btn btn btn-md text-white saveBtn mt-4 font-weight-bold px-3 hidden"
                    id="buttonSaveImmeuble">Valider</button>
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
                <h3 id="loadingText">Chargement...</h3>
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

<?php
// Fiche de sommaire
require_once APPROOT . '/views/projet/sommaire.php';
?>

<script src="<?= URLROOT ?>/public/assets/vendor/jquery/jquery.min.js"></script>
<script type="text/javascript">
const URLROOT = document.getElementById("URLROOT").value;

$('#file').on('change', function() {
    for (const file of $(this).get(0).files) {
        onChangeImage(file);
    }
});

dropContainer.ondragover = dropContainer.ondragenter = function(evt) {
    evt.preventDefault();
};

dropContainer.ondrop = function(evt) {
    let file1 = evt.dataTransfer.files[0];
    let ext = file1.name.split('.')[file1.name.split('.').length - 1];
    if ((ext.toLowerCase() != "png") && (ext.toLowerCase() != "jng") && (ext.toLowerCase() != "jpeg")) {
        $("#msgError").text(
            "Veuillez choisir une image !");
        $('#errorOperation').modal('show');
    } else {
        file.files = evt.dataTransfer.files;
        const dT = new DataTransfer();
        dT.items.add(evt.dataTransfer.files[0]);
        file.files = dT.files;
        onChangeImage(file1);
        evt.preventDefault();
    }
};

function deleteFileImage() {
    $('#file').val("");
    files = [];
    docs = [];
    photoImmeuble = "";
    $('#dropContainer').html("Déposer le fichier ici...");
}

function onChangeImage(file1) {
    files = [];
    docs = [];
    let ext = file1.name.split('.')[file1.name.split('.').length - 1];
    photoImmeuble = $('#file').val();
    files.push(file1);
    docs.push({
        numeroDocument: "",
        idOp: $('#idOP').val(),
        nomDocument: photoImmeuble,
        urlDocument: photoImmeuble,
        commentaire: "",
        createDate: "",
        guidHistory: "",
        typeFichier: file1["name"].split(".")[1],
        size: "",
        guidUser: $("#numeroAuteur").val(),
        auteur: $('#auteur').val(),
        source: "EXTRA",
        publie: "0",
        personneANotifier: "",
        opName: $('#nameOP').val()
    })

    var f = new FileReader();
    f.readAsDataURL(file.files[0]);
    f.onloadend = function(event) {
        const path = event.target.result;
        //SET ICONE PNG
        $('#dropContainer').html("");
        var elem = document.createElement("img");
        elem.setAttribute("src", path);
        elem.setAttribute("height", "100%");
        elem.setAttribute("width", "100%");
        elem.setAttribute("alt", "IMAGE IMMEUBLE");
        document.getElementById("dropContainer").appendChild(elem);
    }
}

function saveImmeuble() {
    var idImmeuble = document.getElementById('idImmeubleSelected').value;
    var adresse = document.getElementById('adresseImmeubleSelected').value;
    var photoImmeuble = document.getElementById('photoImmeubleSelected').value;
    $('#modalImmeuble').modal('hide');
    $('#adresse').val(adresse);
    $('#idImmeuble').val(idImmeuble);
    $('#file').val(photoImmeuble);
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
    var selectedPhoto = $(row).data('photo');

    // Set the selected ID in the appropriate hidden input
    $('#idImmeubleSelected').val(selectedId);
    $('#adresseImmeubleSelected').val(selectedAdresse);
    $('#photoImmeubleSelected').val(selectedPhoto);

    // Optional: Log the selected ID for debugging
    console.log("Selected Immeuble ID: " + selectedId);
    console.log("Selected Immeuble adresse: " + selectedAdresse);
    console.log("Selected Immeuble photo: " + selectedPhoto);
}

function onClickImmeuble() {
    $('#modalImmeuble').modal('show');
}
//BIBLIOTHEQUE
function selectContenuSection(row) {
    // Deselect Immeuble
    $('#dataTable16 tbody tr td i').removeClass('fas fa-check-square text-primary');
    $('#dataTable16 tbody tr td i').addClass('fa fa-square text-white border border-1');

    // Deselect the selected immeuble
    $(row).closest('table').find('tbody tr td i').removeClass('fas fa-check-square text-primary');
    $('#dataTable16 tbody tr td i').addClass('fa fa-square text-white border border-1');
    //Select immeuble
    $(row).find('td i').addClass('fas fa-check-square text-primary');
    $(row).find('td i').removeClass('fa fa-square text-white border border-1');

    $('#buttonSaveContenuSection').removeClass('hidden');

    var selectedId = $(row).data('id');
    var selectedTitle = $(row).data('title');
    var selectedContenu = $(row).data('contain');

    // Set the selected ID in the appropriate hidden input
    $('#contenuSectionSelected').val(selectedContenu);
    $('#titreSectionSelected').val(selectedTitle);

    // Optional: Log the selected ID for debugging
    console.log(selectedContenu);

}

function saveContenuSection() {
    var contenuSectionSelected = document.getElementById('contenuSectionSelected').value;
    var selectedTitle = document.getElementById('titreSectionSelected').value;
    console.log(activeSectionId);
    tinymce.get(`section-content-${activeSectionId}`).setContent(contenuSectionSelected);
    $("#modalBibilotheque").modal('hide')
    saveSection(activeSectionId)
}

function onClickPdf() {
    $('#modalConfirmRGPD').modal('show');
}

function onClickConfirmRGPD() {
    onclickExporter('pdf', $('.typeRapport:checked').val());
}


function onclickExporter(type, rgpd = '') {
    console.log(rgpd);
    var idProjet = document.getElementById("idProjet").value;
    var idImmeuble = document.getElementById("idImmeuble").value;
    var idAuteur = document.getElementById("idUtilisateur").value;
    var auteur = document.getElementById("auteur").value;
    var numeroAuteur = document.getElementById("numeroAuteur").value;
    $.ajax({
        method: "POST",
        url: URLROOT + "/public/json/projet.php?action=saveDocument",
        data: {
            idProjet: idProjet,
            idImmeuble: idImmeuble,
            doc: type,
            typeRapport: rgpd
        },
        beforeSend: function() {
            $('#loadingModal').modal('show');
        },
        success: function(response) {
            console.log("exporter pdf");
            console.log(response);
            setTimeout(() => {
                $('#loadingModal').modal('hide');
                $('#modalConfirmRGPD').modal('hide');
            }, 1000);
            if (response != '"0"') {
                if (type == "pdf") {
                    popitup(response, "PROJET_RYM_INVEST", "projet/projet_export");
                } else {
                    //document.location.href = `<?= URLROOT ?>/public/documents/projet/projet_export/` +
                    //response;
                }
            } else {
                $("#msgError").text(
                    "impossible de générer le document !");
                $('#errorOperation').modal('show');
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
</script>