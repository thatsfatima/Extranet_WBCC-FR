<?php
// $hidden = (isset($etape) && $etape != "") ? "" : "hidden";
// $hiddenFinal = (isset($etape) && $etape != "") ? "hidden" : "";
$active = "red";
$roleUser = $_SESSION["connectedUser"]->libelleRole;
$hiddenFiltreContact = ($roleUser == "Administrateur" || $roleUser == "Manager" ||  $_SESSION["connectedUser"]->isAdmin == "1") ? "" : "hidden";
$idUser = $_SESSION["connectedUser"]->idUtilisateur;


$hiddenStat = ($_SESSION["connectedUser"]->libelleRole != "Gestionnaire EXTERNE" && $_SESSION["connectedUser"]->role != "27") ? "" : "hidden";
?>

<!-- ======= Avantages Section ======= -->

<div class="section-title">
    <h2><span><i class="fas fa-fw fa-user-alt" style="color: #c00000"></i></span> HISTORIQUE </h2>
</div>

<div class="row">
    <div class="col-md-4" <?= $hiddenFiltreContact  ?>>
        <fieldset>
            <legend class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                Utilisateurs </legend>
            <div class="card">
                <select name="" id="user" class="form-control" onchange="getHistorique()">
                    <!-- <option value="">--Selectionner un utilisateur ---</option> -->
                    <?php
                    foreach ($users as $key => $u) {
                        if ($u->etatUser == "1") {
                    ?>
                            <option <?= $u->idUtilisateur == $idUser ? "selected" : "" ?> value="<?= $u->idUtilisateur ?>">
                                <?= $u->fullName ?></option>
                    <?php  }
                    }
                    ?>
                </select>
            </div>
        </fieldset>
    </div>
    <div class="col-md-4" hidden>
        <fieldset>
            <legend class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                Periode </legend>
            <div class="card ">
                <select name="" id="periode" class="form-control" onchange="getHistorique()">
                    <option value="today">Aujourd'hui</option>
                    <option value="semaine">Cette Semaine</option>
                    <option value="mois">Ce mois</option>
                    <option value="trimestre">Ce trimestre</option>
                    <option value="semestre">Ce semestre</option>
                    <option value="annuel">Cette année</option>
                    <option value="jour">A la date du : </option>
                    <option value="perso">Personnaliser </option>
                    <option value="">Tous </option>
                </select>
            </div>
        </fieldset>
    </div>

    <div class="col-md-3" id="changeperso" hidden>
        <fieldset>
            <legend class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                Personnaliser </legend>
            <div class="card">
                <div class="row">
                    <div class="col-md-6" id="date1">
                        <input type="date" name="date1" id="date1Input" max="<?= date("Y-m-d") ?>" class="form-control"
                            onchange="getHistorique()">
                    </div>
                    <div class="col-md-6" id="date2">
                        <input type="date" name="date2" id="date2Input" max="<?= date("Y-m-d") ?>" class="form-control"
                            onchange="getHistorique()">
                    </div>
                </div>
            </div>
        </fieldset>

    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header bg-secondary text-white">
        <div class="row">

            <div class="col-md-12">
                <h2 class="text-center font-weight-bold" id="titre"> <?= $titre ?></h2>
            </div>

        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="tabledata" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Action</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    foreach ($historiques as $key =>  $historique) {

                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $historique->action ?></td>
                            <td><?= $historique->dateAction ?></td>
                        </tr>
                    <?php    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script src="<?= URLROOT ?>/assets/vendor/jquery/jquery.min.js" crossorigin="anonymous"></script>
<script>
    $("#periode").on("change", function() {
        if ($("#periode option:selected").val() == "perso" || $("#periode option:selected").val() == "jour") {
            $("#changeperso").removeAttr("hidden");
            if ($("#periode option:selected").val() == "perso") {
                $("#date2").removeAttr("hidden");

                $("#date1").removeClass("col-md-12");
                $("#date1").addClass("col-md-6");
            } else {
                $("#date2").attr("hidden", "hidden");
                $("#date1").removeClass("col-md-6");
                $("#date1").addClass("col-md-12");
            }
        } else {
            $("#changeperso").attr("hidden", "hidden");
        }
    })


    function getHistorique() {
        let id = $("#user").val();
        let user = $("#user option:selected").text();
        if (id != "") {
            $.ajax({
                url: `<?= URLROOT ?>/public/json/historique.php?action=getHistoriqueByIdUser&id=` + id,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {},
                success: function(response) {
                    console.log("success");
                    // console.log(response);
                    $("#titre").text("Historique : " + user.trim());
                    $('#tabledata').DataTable({
                        "Processing": true, // for show progress bar
                        "serverSide": false, // for process server side
                        "filter": true, // this is for disable filter (search box)
                        "orderMulti": true, // for disable multiple column at once
                        "bDestroy": true,
                        'iDisplayLength': 100,
                        "data": response,
                        "columns": [{
                                "data": "index"
                            },
                            {
                                "data": "action"
                            },
                            {
                                "data": "dateAction"
                            }
                        ]
                    });
                },
                error: function(response) {
                    console.log(response);
                },
                complete: function() {
                    $("#loadingModal").modal("hide");
                },
            });
        }

    }

    getHistorique();
</script>