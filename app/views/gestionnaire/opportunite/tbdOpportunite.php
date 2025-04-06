<?php
$roleUser = $_SESSION["connectedUser"]->idRole;
$hiddenFiltreContact = ($roleUser == "1" || $roleUser == "1" || $roleUser == "8" || $roleUser == "25") ? "" : "hidden";
$idUser = $_SESSION["connectedUser"]->idUtilisateur;


$hiddenStat = ($_SESSION["connectedUser"]->libelleRole != "Gestionnaire EXTERNE" && $_SESSION["connectedUser"]->role != "27") ? "" : "hidden";

/* var_dump($_SESSION["connectedUser"]->idUtilisateur);
die */
?>

<input type="hidden" name="" id="idUser" value=<?= $hiddenFiltreContact == "" ? "" : $idUser ?>>

<div class="section-title">
    <div class="row">
        <div class="col-md-6">
            <h2><span><i class="fas fa-fw fa-folder" style="color: #c00000"></i></span> Tableau de bord des opportunités
            </h2>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4" <?= $hiddenFiltreContact  ?>>
        <fieldset>
            <legend class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                Gestionnaires </legend>
            <div class="card">
                <select name="" id="gestSelection" class="form-control" onchange="getStatsTab()">
                </select>
            </div>
        </fieldset>
    </div>
    <div class="col-md-4">
        <fieldset>
            <legend class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                Periode </legend>
            <div class="card ">
                <select name="" id="periode" class="form-control" onchange="getStatsTab()">
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
                            onchange="getStatsTab()">
                    </div>
                    <div class="col-md-6" id="date2">
                        <input type="date" name="date2" id="date2Input" max="<?= date("Y-m-d") ?>" class="form-control"
                            onchange="getStatsTab()">
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</div>



<div class="row mt-2">
    <div class="col-md-12">

        <div class="card">
            <div class="modal-content">
                <div class="card-header bg-secondary text-white">
                    <div class="row">

                        <div class="col-md-12">
                            <h2 class="text-center font-weight-bold"> Nombre de Délégation Signé, déclaration , télé
                                -Expertise et prise de RV par gestionnaire</h2>
                        </div>

                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive col-md-12 offset-0">
                        <table class="table table-bordered" id="tabledata" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Gestionnaire</th>
                                    <th>Nb Délég. Sign.</th>
                                    <th>NB Télé-expertise</th>
                                    <th>NB RDV RT pris</th>
                                    <th <?= $hiddenStat ?>>NB Déclaration CIE par Tel</th>
                                    <th <?= $hiddenStat ?>>NB Déclaration CIE par mail</th>
                                    <th <?= $hiddenStat ?>>NB Rel. Num. Sin.</th>
                                    <th <?= $hiddenStat ?>>NB FRT faits</th>
                                    <th <?= $hiddenStat ?>>NB FRT rejettés</th>
                                    <th <?= $hiddenStat ?>>NB Controle FRT (E1) validés</th>
                                    <th <?= $hiddenStat ?>>NB Controle FRT (E2) validés</th>
                                    <th <?= $hiddenStat ?>>NB Controle FRT (E3) validés</th>
                                    <th <?= $hiddenStat ?>>NB Devis faits</th>
                                    <th <?= $hiddenStat ?>>NB Devis envoyés</th>
                                    <th <?= $hiddenStat ?>>NB Relance prise en charge</th>
                                    <th <?= $hiddenStat ?>>NB Rapports faits</th>
                                    <th <?= $hiddenStat ?>>NB Controle RT fais</th>
                                    <th>Gestionnaire</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tableStat">


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<div class="row" id="chartsGen">

    <div class="col-md-12 mt-5  p-2">
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    1. Nombre de Délégation Signé
                </span>
                <span id="totalDE" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('DE', 'te')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart">
        </div>
    </div>

    <div class="col-md-12 mt-5  p-2">
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    2. Nombre de télé-expertise realisé
                </span>
                <span id="totalTE" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('TE', 'te')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart2">
        </div>
    </div>

    <div class="col-md-12 mt-5 p-2">
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    3. Nombre de prise de RDV RT pris
                </span>
                <span id="totalRV" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('RV', 'te')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart3">
        </div>
    </div>

    <div class="col-md-12 mt-5 p-2" <?= $hiddenStat ?>>
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    4-1. Nombre de déclaration compagnie realisé par mail
                </span>
                <span id="totalDCM" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('DCM', 'dc')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart4">
        </div>
    </div>

    <div class="col-md-12 mt-5  p-2" <?= $hiddenStat ?>>
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    4-2. Nombre de déclaration compagnie realisé par téléphone
                </span>
                <span id="totalDC" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('DC', 'dc')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart1">
        </div>
    </div>

    <div class="col-md-12 mt-5 p-2" <?= $hiddenStat ?>>
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    5. Nombre de relance pour numèro de sinistre
                </span>
                <span id="totalSDC" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('SDC', 'dc')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart8">
        </div>
    </div>

    <div class="col-md-12 mt-5 p-2" <?= $hiddenStat ?>>
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    6. Nombre de FRT faits
                </span>
                <span id="totalFRT" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('FRT', 'frt')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart9">
        </div>
    </div>

    <div class="col-md-12 mt-5 p-2" <?= $hiddenStat ?>>
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    7. Nombre de FRT Rejettés
                </span>
                <span id="totalFRTRe" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('FRTRe', 'frt')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart15">
        </div>
    </div>

    <div class="col-md-12 mt-5 p-2" <?= $hiddenStat ?>>
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    8-1. Nombre de controle FRT (E1) validés
                </span>
                <span id="totalCFRT1" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('CFRT1', 'fd')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart10">
        </div>
    </div>

    <div class="col-md-12 mt-5 p-2" <?= $hiddenStat ?>>
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    8-2. Nombre de controle FRT (E2) validés
                </span>
                <span id="totalCFRT2" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('CFRT2', 'fd')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart11">
        </div>
    </div>

    <div class="col-md-12 mt-5 p-2" <?= $hiddenStat ?>>
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    8-3. Nombre de controle FRT (E3) validés
                </span>
                <span id="totalCFRT3" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('CFRT3', 'fd')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart12">
        </div>
    </div>

    <div class="col-md-12 mt-5 p-2" <?= $hiddenStat ?>>
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    9. Nombre de devis fais
                </span>
                <span id="totalFD" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('FD', 'fd')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart5">
        </div>
    </div>

    <div class="col-md-12 mt-5 p-2" <?= $hiddenStat ?>>
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    10. Nombre de devis envoyés
                </span>
                <span id="totalED" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('ED', 'fd')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart6">
        </div>
    </div>

    <div class="col-md-12 mt-5 p-2" <?= $hiddenStat ?>>
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    11. Nombre relance prise en charge fais
                </span>
                <span id="totalPC" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('PR', 'fd')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart7">
        </div>
    </div>

    <div class="col-md-12 mt-5 p-2" <?= $hiddenStat ?>>
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    12. Nombre de rapport RT fais
                </span>
                <span id="totalRRT" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('RRT', 'rt')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart13">
        </div>
    </div>

    <div class="col-md-12 mt-5 p-2" <?= $hiddenStat ?>>
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <span class="col-md-10">
                    13. Nombre controle RT fais
                </span>
                <span id="totalCRT" class="col-md-1"> Total</span>
                <button class="col-md-1 btn btn-secondary" onclick="ListOP('CRT', 'rt')">Liste</button>
            </div>
        </div>
        <div class="card chart" id="chart14">
        </div>
    </div>


</div>

<style>
    .chart {
        height: 50vh;
    }
</style>


<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js">
</script>
<script src="<?= URLROOT ?>/assets/vendor/jquery/jquery.min.js" crossorigin="anonymous"></script>
<script>
    var tablistGest = [];
    var colors = ["#330000", "#660000", "#990000", "#CC0000", "#FF0000", "#003300", "#993300", "#339900", "#FFFF00",
        "#CC0099", "#660066",
        "#66FF33", "#666633", "#009999", "#660099", "#FFCC99", "#330099", "#FF00FF", "#00FF99", "#FF99FF", "#00FFFF",
        "#FF6666", "#993366", "#996666", "#9999CC", "#FFFF99", "#99CC00", "#FF0099", "#000099", "#330033", "#996600",
        "#FF6633", "#CC00FF", "#006600", "#FF5733", "#DDEC91", "#87897C", "#B9B9B9", "#5C4A9B", "#479C78", "#00FF1B",
        "#0064FF", "#B600FF", "#FF0080", "#AF8EFB", "#C4FB8E", "#FAA7BA", "#A7EBFA", "#A73D7F", "#FF1B52", "#7A5D64",
        "#000000"
    ];

    listGestionnaires();

    var role = `<?= $roleUser ?>`;
    var isAdmin = `<?= $_SESSION["connectedUser"]->isAdmin ?>`;

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


    function listGestionnaires() {

        $.ajax({
            // async: false,
            type: "GET",
            url: `<?= URLROOT ?>/public/json/utilisateur.php?action=getGestionnaires&idUser=<?= $idUser ?>&roleUser=<?= $roleUser ?>`,
            dataType: "JSON",
            cache: false,
            success: function(data) {
                // console.log(data);
                //let role = `<?= $roleUser ?>`;
                var html = "";
                if (role == "1" || role == "2" || role == "8" || role == "25") {
                    html = "<option value=''> Tous </option>"
                }
                data.forEach(element => {
                    var obj = {
                        idUtilisateur: element.idUtilisateur,
                        fullName: element.fullName
                    }
                    tablistGest.push(obj);
                    html +=
                        `<option value = '${element.idUtilisateur}' > ${element.fullName} </option> `;
                });
                // console.log(tablistGest);
                $("#gestSelection").append(html);
                getStatsTab();
            },
            error: function(reponse) {
                console.log(reponse);
            }
        });

    }

    function getStatsTab() {


        if ($('#date1Input').val() != "") {
            if ($('#date2Input').val() == "") {
                $('#date2Input').val($('#date1Input').val());
            }
            $('#date2Input').attr("min", $('#date1Input').val());
        }

        $.ajax({
            // async: false,
            url: `<?= URLROOT ?>/public/json/opportunity.php?action=getNbrOP`,
            dataType: "JSON",
            type: 'POST',
            cache: false,
            data: {
                tabUsers: tablistGest,
                user: $("#gestSelection").val(),
                periode: $("#periode").val(),
                date1: $("#date1Input").val(),
                date2: $("#date2Input").val(),
                fullName: $("#gestSelection option:selected").text(),

            },
            success: function(data) {
                // console.log(data);

                var html = "";


                var xValues = [];
                var yValues = [];
                var barColors = [];

                var xValues1 = [];
                var yValues1 = [];

                var xValues2 = [];
                var yValues2 = [];

                var xValues3 = [];
                var yValues3 = [];

                var xValues4 = [];
                var yValues4 = [];

                var xValues5 = [];
                var yValues5 = [];

                var xValues6 = [];
                var yValues6 = [];

                var xValues7 = [];
                var yValues7 = [];

                var xValues8 = [];
                var yValues8 = [];

                var xValues9 = [];
                var yValues9 = [];

                var xValues10 = [];
                var yValues10 = [];

                var xValues11 = [];
                var yValues11 = [];

                var xValues12 = [];
                var yValues12 = [];

                var xValues13 = [];
                var yValues13 = [];

                var xValues14 = [];
                var yValues14 = [];

                var xValues15 = [];
                var yValues15 = [];

                var columns = [];

                var totalDE = 0;
                var totalDC = 0;
                var totalTE = 0
                var totalRV = 0;
                var totalDCM = 0;
                var totalFD = 0;
                var totalED = 0;
                var totalPC = 0;
                var totalSDC = 0;
                var totalFRT = 0;
                var totalFRTRe = 0;
                var totalCFRT1 = 0;
                var totalCFRT2 = 0;
                var totalCFRT3 = 0;
                var totalRRT = 0;
                var totalCRT = 0;

                for (let index = 0; index < data["data"].length; index++) {
                    const element = data["data"][index];

                    xValues.push(element["gestionnaire"] + `(${element["nbrDE"]})`);
                    barColors.push(colors[index]);
                    yValues.push(parseInt(element["nbrDE"]));
                    totalDE += parseInt(element["nbrDE"]);

                    xValues1.push(element["gestionnaire"] + `(${element["nbrDC"]})`);
                    yValues1.push(parseInt(element["nbrDC"]));
                    totalDC += parseInt(element["nbrDC"]);

                    xValues2.push(element["gestionnaire"] + `(${element["nbrTE"]})`);
                    yValues2.push(parseInt(element["nbrTE"]));
                    totalTE += parseInt(element["nbrTE"]);

                    xValues3.push(element["gestionnaire"] + `(${element["nbrRV"]})`);
                    yValues3.push(parseInt(element["nbrRV"]));
                    totalRV += parseInt(element["nbrRV"]);

                    xValues4.push(element["gestionnaire"] + `(${element["nbrDCM"]})`);
                    yValues4.push(parseInt(element["nbrDCM"]));
                    totalDCM += parseInt(element["nbrDCM"]);

                    xValues5.push(element["gestionnaire"] + `(${element["nbrFD"]})`);
                    yValues5.push(parseInt(element["nbrFD"]));
                    totalFD += parseInt(element["nbrFD"]);

                    xValues6.push(element["gestionnaire"] + `(${element["nbrED"]})`);
                    yValues6.push(parseInt(element["nbrED"]));
                    totalED += parseInt(element["nbrED"]);

                    xValues7.push(element["gestionnaire"] + `(${element["nbrPC"]})`);
                    yValues7.push(parseInt(element["nbrPC"]));
                    totalPC += parseInt(element["nbrPC"]);

                    xValues8.push(element["gestionnaire"] + `(${element["nbrSDC"]})`);
                    yValues8.push(parseInt(element["nbrSDC"]));
                    totalSDC += parseInt(element["nbrSDC"]);

                    xValues9.push(element["gestionnaire"] + `(${element["nbrFRT"]})`);
                    yValues9.push(parseInt(element["nbrFRT"]));
                    totalFRT += parseInt(element["nbrFRT"]);

                    xValues10.push(element["gestionnaire"] + `(${element["nbrCFRT1"]})`);
                    yValues10.push(parseInt(element["nbrCFRT1"]));
                    totalCFRT1 += parseInt(element["nbrCFRT1"]);

                    xValues11.push(element["gestionnaire"] + `(${element["nbrCFRT2"]})`);
                    yValues11.push(parseInt(element["nbrCFRT2"]));
                    totalCFRT2 += parseInt(element["nbrCFRT2"]);

                    xValues12.push(element["gestionnaire"] + `(${element["nbrCFRT3"]})`);
                    yValues12.push(parseInt(element["nbrCFRT3"]));
                    totalCFRT3 += parseInt(element["nbrCFRT3"]);

                    xValues13.push(element["gestionnaire"] + `(${element["nbrRRT"]})`);
                    yValues13.push(parseInt(element["nbrRRT"]));
                    totalRRT += parseInt(element["nbrRRT"]);

                    xValues14.push(element["gestionnaire"] + `(${element["nbrCRT"]})`);
                    yValues14.push(parseInt(element["nbrCRT"]));
                    totalCRT += parseInt(element["nbrCRT"]);

                    xValues15.push(element["gestionnaire"] + `(${element["nbrFRTRe"]})`);
                    yValues15.push(parseInt(element["nbrFRTRe"]));
                    totalFRTRe += parseInt(element["nbrFRTRe"]);

                }

                console.log(yValues15);

                $("#totalDE").text(`Total : ${totalDE}`);
                $("#totalDC").text(`Total : ${totalDC}`);
                $("#totalTE").text(`Total : ${totalTE}`);
                $("#totalRV").text(`Total : ${totalRV}`);
                $("#totalDCM").text(`Total : ${totalDCM}`);
                $("#totalFD").text(`Total : ${totalFD}`);
                $("#totalED").text(`Total : ${totalED}`);
                $("#totalPC").text(`Total : ${totalPC}`);
                $("#totalSDC").text(`Total : ${totalSDC}`);
                $("#totalFRT").text(`Total : ${totalFRT}`);
                $("#totalCFRT1").text(`Total : ${totalCFRT1}`);
                $("#totalCFRT2").text(`Total : ${totalCFRT2}`);
                $("#totalCFRT3").text(`Total : ${totalCFRT3}`);
                $("#totalRRT").text(`Total : ${totalRRT}`);
                $("#totalCRT").text(`Total : ${totalCRT}`);
                $("#totalFRTRe").text(`Total : ${totalFRTRe}`);


                $('#tabledata').DataTable({
                    "Processing": true, // for show progress bar
                    "serverSide": false, // for process server side
                    "filter": true, // this is for disable filter (search box)
                    "orderMulti": true, // for disable multiple column at once
                    "bDestroy": true,
                    'iDisplayLength': 100,
                    "data": data["data"],
                    "columns": [{
                            "data": "index"
                        },
                        {
                            "data": "gestionnaire"
                        },
                        {
                            "data": "nbrDE"
                        },
                        {
                            "data": "nbrTE"
                        },
                        {
                            "data": "nbrRV"
                        },
                        {
                            "data": "nbrDC",
                            visible: role != "Gestionnaire EXTERNE" ? true : false,
                        },
                        {
                            "data": "nbrDCM",
                            visible: role != "Gestionnaire EXTERNE" ? true : false,
                        },
                        {
                            "data": "nbrSDC",
                            visible: role != "Gestionnaire EXTERNE" ? true : false,
                        },
                        {
                            "data": "nbrFRT",
                            visible: role != "Gestionnaire EXTERNE" ? true : false,
                        },
                        {
                            "data": "nbrFRTRe",
                            visible: role != "Gestionnaire EXTERNE" ? true : false,
                        },
                        {
                            "data": "nbrCFRT1",
                            visible: role != "Gestionnaire EXTERNE" ? true : false,
                        },
                        {
                            "data": "nbrCFRT2",
                            visible: role != "Gestionnaire EXTERNE" ? true : false,
                        },
                        {
                            "data": "nbrCFRT3",
                            visible: role != "Gestionnaire EXTERNE" ? true : false,
                        },
                        {
                            "data": "nbrFD",
                            visible: role != "Gestionnaire EXTERNE" ? true : false,
                        },
                        {
                            "data": "nbrED",
                            visible: role != "Gestionnaire EXTERNE" ? true : false,
                        },
                        {
                            "data": "nbrPC",
                            visible: role != "Gestionnaire EXTERNE" ? true : false,
                        },
                        {
                            "data": "nbrRRT",
                            visible: role != "Gestionnaire EXTERNE" ? true : false,
                        },
                        {
                            "data": "nbrCRT",
                            visible: role != "Gestionnaire EXTERNE" ? true : false,
                        },
                        {
                            "data": "gestionnaire"
                        },
                        {
                            "data": "index"
                        },

                    ]
                });



                if ($("#gestSelection").val() != "") {
                    xValues.push("Total" + `(${data["data"][0]["allDE"]})`);
                    yValues.push(parseInt(data["data"][0]["allDE"]) - parseInt(data["data"][0]["nbrDE"]));

                    xValues1.push("Total" + `(${data["data"][0]["allDC"]})`);
                    yValues1.push(parseInt(data["data"][0]["allDC"]) - parseInt(data["data"][0]["nbrDC"]));

                    xValues2.push("Total" + `(${data["data"][0]["allTE"]})`);
                    yValues2.push(parseInt(data["data"][0]["allTE"]) - parseInt(data["data"][0]["nbrTE"]));

                    xValues3.push("Total" + `(${data["data"][0]["allRV"]})`);
                    yValues3.push(parseInt(data["data"][0]["allRV"]) - parseInt(data["data"][0]["nbrRV"]));

                    xValues4.push("Total" + `(${data["data"][0]["allDCM"]})`);
                    yValues4.push(parseInt(data["data"][0]["allDCM"]) - parseInt(data["data"][0]["nbrDCM"]));

                    xValues5.push("Total" + `(${data["data"][0]["allFD"]})`);
                    yValues5.push(parseInt(data["data"][0]["allFD"]) - parseInt(data["data"][0]["nbrDCM"]));

                    xValues6.push("Total" + `(${data["data"][0]["allED"]})`);
                    yValues6.push(parseInt(data["data"][0]["allED"]) - parseInt(data["data"][0]["nbrDCM"]));

                    xValues7.push("Total" + `(${data["data"][0]["allPC"]})`);
                    yValues7.push(parseInt(data["data"][0]["allPC"]) - parseInt(data["data"][0]["nbrDCM"]));

                    xValues8.push("Total" + `(${data["data"][0]["allSDC"]})`);
                    yValues8.push(parseInt(data["data"][0]["allSDC"]) - parseInt(data["data"][0]["nbrSDC"]));

                    xValues9.push("Total" + `(${data["data"][0]["allFRT"]})`);
                    yValues9.push(parseInt(data["data"][0]["allFRT"]) - parseInt(data["data"][0]["nbrFRT"]));

                    xValues10.push("Total" + `(${data["data"][0]["allCFRT1"]})`);
                    yValues10.push(parseInt(data["data"][0]["allCFRT1"]) - parseInt(data["data"][0][
                        "nbrCFRT1"
                    ]));

                    xValues11.push("Total" + `(${data["data"][0]["allCFRT2"]})`);
                    yValues11.push(parseInt(data["data"][0]["allCFRT2"]) - parseInt(data["data"][0][
                        "nbrCFRT2"
                    ]));

                    xValues12.push("Total" + `(${data["data"][0]["allCFRT3"]})`);
                    yValues12.push(parseInt(data["data"][0]["allCFRT3"]) - parseInt(data["data"][0][
                        "nbrCFRT3"
                    ]));

                    xValues13.push("Total" + `(${data["data"][0]["allRRT"]})`);
                    yValues13.push(parseInt(data["data"][0]["allRRT"]) - parseInt(data["data"][0]["nbrRRT"]));

                    xValues14.push("Total" + `(${data["data"][0]["allCRT"]})`);
                    yValues14.push(parseInt(data["data"][0]["allCRT"]) - parseInt(data["data"][0]["nbrCRT"]));

                    xValues15.push("Total" + `(${data["data"][0]["allFRTRe"]})`);
                    yValues15.push(parseInt(data["data"][0]["allFRTRe"]) - parseInt(data["data"][0][
                        "nbrFRTRe"
                    ]));
                }



                if (checkIfthereIsValue(yValues)) {
                    $("#chart").empty();
                    $("#chart").append(`
                        <canvas id="myChart" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart", {
                        type: "pie",
                        data: {
                            labels: xValues,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues
                            }]
                        },

                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }

                    });
                } else {
                    $("#chart").empty();
                    $("#chart").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }


                if (checkIfthereIsValue(yValues1)) {
                    // console.log(checkIfthereIsValue(xValues1));
                    // console.log("j'affiche");
                    $("#chart1").empty();
                    $("#chart1").append(`
                        <canvas id="myChart1" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart1", {
                        type: "pie",
                        data: {
                            labels: xValues1,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues1
                            }],
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart1").empty();
                    $("#chart1").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }


                if (checkIfthereIsValue(yValues2)) {
                    $("#chart2").empty();
                    $("#chart2").append(`
                        <canvas id="myChart2" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart2", {
                        type: "pie",
                        data: {
                            labels: xValues2,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues2
                            }]
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart2").empty();
                    $("#chart2").append(`
                        <div style="style="width:100%;height:100%" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }


                if (checkIfthereIsValue(yValues3)) {
                    $("#chart3").empty();
                    $("#chart3").append(`
                        <canvas id="myChart3" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart3", {
                        type: "pie",
                        data: {
                            labels: xValues3,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues3
                            }]
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart3").empty();
                    $("#chart3").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }

                if (checkIfthereIsValue(yValues4)) {
                    $("#chart4").empty();
                    $("#chart4").append(`
                        <canvas id="myChart4" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart4", {
                        type: "pie",
                        data: {
                            labels: xValues4,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues4
                            }]
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart4").empty();
                    $("#chart4").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }

                if (checkIfthereIsValue(yValues5)) {
                    $("#chart5").empty();
                    $("#chart5").append(`
                        <canvas id="myChart5" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart5", {
                        type: "pie",
                        data: {
                            labels: xValues5,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues5
                            }]
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart5").empty();
                    $("#chart5").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }

                if (checkIfthereIsValue(yValues15)) {
                    $("#chart15").empty();
                    $("#chart15").append(`
                        <canvas id="myChart15" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart15", {
                        type: "pie",
                        data: {
                            labels: xValues15,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues15
                            }]
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart15").empty();
                    $("#chart15").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }

                if (checkIfthereIsValue(yValues6)) {
                    $("#chart6").empty();
                    $("#chart6").append(`
                        <canvas id="myChart6" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart6", {
                        type: "pie",
                        data: {
                            labels: xValues6,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues6
                            }]
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart6").empty();
                    $("#chart6").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }

                if (checkIfthereIsValue(yValues7)) {
                    $("#chart7").empty();
                    $("#chart7").append(`
                        <canvas id="myChart7" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart7", {
                        type: "pie",
                        data: {
                            labels: xValues7,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues7
                            }]
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart7").empty();
                    $("#chart7").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }

                if (checkIfthereIsValue(yValues9)) {
                    $("#chart9").empty();
                    $("#chart9").append(`
                        <canvas id="myChart9" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart9", {
                        type: "pie",
                        data: {
                            labels: xValues9,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues9
                            }]
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart9").empty();
                    $("#chart9").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }

                if (checkIfthereIsValue(yValues10)) {
                    $("#chart10").empty();
                    $("#chart10").append(`
                        <canvas id="myChart10" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart10", {
                        type: "pie",
                        data: {
                            labels: xValues10,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues10
                            }]
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart10").empty();
                    $("#chart10").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }

                if (checkIfthereIsValue(yValues11)) {
                    $("#chart11").empty();
                    $("#chart11").append(`
                        <canvas id="myChart11" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart11", {
                        type: "pie",
                        data: {
                            labels: xValues11,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues11
                            }]
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart11").empty();
                    $("#chart11").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }

                if (checkIfthereIsValue(yValues12)) {
                    $("#chart12").empty();
                    $("#chart12").append(`
                        <canvas id="myChart12" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart12", {
                        type: "pie",
                        data: {
                            labels: xValues12,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues12
                            }]
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart12").empty();
                    $("#chart12").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }

                if (checkIfthereIsValue(yValues8)) {
                    $("#chart8").empty();
                    $("#chart8").append(`
                        <canvas id="myChart8" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart8", {
                        type: "pie",
                        data: {
                            labels: xValues8,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues8
                            }]
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart8").empty();
                    $("#chart8").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }

                if (checkIfthereIsValue(yValues13)) {
                    $("#chart13").empty();
                    $("#chart13").append(`
                        <canvas id="myChart13" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart13", {
                        type: "pie",
                        data: {
                            labels: xValues13,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues13
                            }]
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart13").empty();
                    $("#chart13").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }

                if (checkIfthereIsValue(yValues14)) {
                    $("#chart14").empty();
                    $("#chart14").append(`
                        <canvas id="myChart14" style="width:100%;height:100%"></canvas>
                        `);
                    new Chart("myChart14", {
                        type: "pie",
                        data: {
                            labels: xValues14,
                            datasets: [{
                                backgroundColor: barColors,
                                data: yValues14
                            }]
                        },
                        options: {
                            aspectRatio: 2,
                            legend: {
                                display: true,
                                position: 'left',
                                align: "end",
                            }

                        }
                    });
                } else {
                    $("#chart14").empty();
                    $("#chart14").append(`
                        <div style="height:30vh;" class="text-center pt-5">Aucune donnée disponible ! </div>
                        `);
                }

            },
            error: function(reponse) {
                console.log(reponse);
            }
        });

    }



    
    function checkIfthereIsValue(array) {
        var result = false;
        for (var i = 0; i < array.length; i++) {
            if (array[i] != 0) {
                result = true;
                break;
            }
        }

        return result;
    }

    function ListOP(lien, typeDeclaration) {

        var precison =
            `${$("#periode").val()};${$("#date1Input").val()};${$("#date2Input").val()};${$("#gestSelection").val()}`

        window.open(`<?= URLROOT ?>/Gestionnaire/listOP/${lien}/${typeDeclaration};${precison}`);

    }
</script>