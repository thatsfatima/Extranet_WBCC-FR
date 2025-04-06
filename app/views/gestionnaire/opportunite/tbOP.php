<?php
$roleUser = $_SESSION["connectedUser"]->idRole;
$idRole = $roleUser;
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
<div class="card mt-0">
    <div class="col-md-12">
        <div class="accordion-body pt-3 pb-3 pr-3" style="box-shadow: none !important;">
            <form method="POST" id="msform" action="<?= linkTo('GestionOP', 'tbdOpportunite') ?>">
                <div class="row" style="width: 100%;  margin: auto;">
                    <div class="<?= "col-md-3"  ?>">
                        <fieldset>
                            <legend
                                class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                ETAPE OP </legend>
                            <div class="card ">
                                <select id="statut" name="statut" class="form-control" data-live-search="true">
                                    <option value="" disabled selected>--Choisir--</option>
                                    </option>
                                    <option value="tous" <?= $statut == "tous" ? "selected" : "" ?>>Tous
                                    </option>
                                    <option value="enCours" <?= $statut == "enCours" ? "selected" : "" ?>>En
                                        cours
                                    </option>
                                    <option value="attenteCloture" <?= $statut == "attenteCloture" ? "selected" : "" ?>>
                                        Attente de clôture</option>
                                    <option value="won" <?= $statut == "won" ? "selected" : "" ?>>
                                        Clôturés Gagnés
                                    </option>
                                    <option value="lost" <?= $statut == "lost" ? "selected" : "" ?>>
                                        Clôturés Perdus
                                    </option>
                                    <?php
                                    $i = 0;
                                    foreach ($act as $acti) {
                                        $i++;
                                    ?>
                                        <option <?= $statut == $acti->codeActivity ? "selected" : "" ?>
                                            value="<?= $acti->codeActivity ?>">
                                            <?= $i . "- " . $acti->libelleActivity ?></option>
                                    <?php
                                    } ?>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    <div class="<?= "col-md-2"  ?>">
                        <fieldset>
                            <legend
                                class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                TYPE OP </legend>
                            <div class="card ">
                                <select id="typeIntervention" name="typeIntervention" class="form-control"
                                    data-live-search="true">
                                    <option value="0" disabled selected>--Choisir--</option>
                                    <option value="tous" <?= $typeIntervention == "tous" ? "selected" : "" ?>>
                                        Tous
                                    </option>
                                    <option value="AMO" <?= $typeIntervention == "AMO" ? "selected" : "" ?>>
                                        A.M.O.
                                    </option>
                                    <option value="SINISTRE" <?= $typeIntervention == "SINISTRE" ? "selected" : "" ?>>
                                        Sinistres
                                    </option>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    <div class="<?= "col-md-2" ?>">
                        <fieldset>
                            <legend
                                class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                site </legend>
                            <div class="card ">
                                <select id="site" name="site" class="form-control">
                                    <option value="0" disabled selected>--Choisir--</option>
                                    <option value="tous" <?= $site == "tous" ? "selected" : "" ?>
                                        <?= $idRole == "1" || $idRole == "2"  || $idRole == "9" || $idRole == "8" ||  $_SESSION["connectedUser"]->isAccessAllOP == "1" ||  $_SESSION["connectedUser"]->isAccessAllOP == "1"  ?  "" : "hidden" ?>>
                                        Tous
                                    </option>
                                    <!-- LISTE SITE -->
                                    <?php
                                    $i = 0;
                                    foreach ($sites as $sit) {
                                        $i++;
                                        if ((($idRole == "1" || $idRole == "2"  || $idRole == "9" || $idRole == "8" || $idRole == '4' || $idRole == '5' ||  $_SESSION["connectedUser"]->isAccessAllOP == "1") || (($idRole == "3" || $idRole == "25") && $_SESSION["connectedUser"]->nomSite == $sit->nomSite))) {
                                    ?>
                                            <option <?= $site == $sit->idSite ? "selected" : "" ?> value="<?= $sit->idSite ?>">
                                                <?= $sit->nomSite ?></option>
                                    <?php
                                        }
                                    } ?>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    <div class="<?= "col-md-2" ?>">
                        <fieldset>
                            <legend
                                class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                <?= "Gestionnaire" ?> </legend>
                            <div class="card">
                                <select id="gestSelection" name="gestionnaire" class="form-control"
                                    data-live-search="true">
                                    <option value="tous" <?= $gestionnaire == "tous" ? "selected" : "" ?>
                                        <?= $idRole == "1" || $idRole == "2"  || $idRole == "9" || $idRole == "25" || $idRole == "8" ||  $_SESSION["connectedUser"]->isAccessAllOP == "1" ||  $_SESSION["connectedUser"]->isAccessAllOP == "1"  ?  "" : "hidden" ?>>
                                        Tous
                                    </option>
                                    <?php
                                    $i = 1;
                                    foreach ($gestionnaires as $ges) { {
                                    ?>
                                            <option <?= $gestionnaire == $ges->idUtilisateur ? "selected" : "" ?>
                                                value="<?= $ges->idUtilisateur ?>">
                                                <?= $ges->fullName ?></option>
                                    <?php
                                        }
                                    } ?>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    <div class="<?= "col-md-3"  ?>">
                        <div class="row">
                            <fieldset>
                                <legend
                                    class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                    Date </legend>
                                <div class="card ">
                                    <select name="periode" id="periode" class="form-control" onchange="onChangePeriode()">
                                        <option value="all" <?= $periode == "all" ? "selected" : "" ?>>Tous</option>
                                        <option value="today" <?= $periode == "today" ? "selected" : "" ?>>
                                            Aujourd'hui
                                        </option>
                                        <option value="semaine" <?= $periode == "semaine" ? "selected" : "" ?>>
                                            Semaine
                                            en
                                            cours
                                        </option>
                                        <option value="mois" <?= $periode == "mois" ? "selected" : "" ?>>Mois en
                                            cours
                                        </option>
                                        <option value="trimestre" <?= $periode == "trimestre" ? "selected" : "" ?>>
                                            Trismestre en cours
                                        </option>
                                        <option value="semestre" <?= $periode == "semestre" ? "selected" : "" ?>>
                                            Semestre en
                                            cours
                                        </option>
                                        <option value="annuel" <?= $periode == "annuel" ? "selected" : "" ?>>Année
                                            en
                                            cours
                                        </option>
                                        <option value="day" <?= $periode == "day" ? "selected" : "" ?>>A la date du
                                            :
                                        </option>
                                        <option value="perso" <?= $periode == "perso" ? "selected" : "" ?>>
                                            Personnaliser
                                        </option>
                                    </select>
                                </div>
                            </fieldset>
                        </div>
                        <div class="row mt-3">
                            <fieldset id="changeperso" <?= $periode == "perso" ||  $periode == "day" ? "" : "hidden" ?>>
                                <legend id="legendPeriode"
                                    class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                                        <?= $periode == "perso" ? "Personnaliser" : "A la date de :" ?>
                                     </legend>
                                <div class="card mt-0">
                                    <div class="row mt-0">
                                        <div class="col-md-12" id="date1">
                                            Du
                                            <input type="date" name="date1" id="date1Input" value="<?= $date1 ?>"
                                                class="form-control">
                                        </div>
                                        <div class="col-md-12" id="date2" <?= $periode == "day" ? "hidden" : "" ?>>
                                            Au
                                            <input type="date" name="date2" id="date2Input" value="<?= $date2  ?>"
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="col-md-2 offset-5 col-xs-12">
                        <button type="submit" class="btn btn-primary form-control">FILTRER</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="mt-5 mb-4 card pt-3 pb-2 bg-danger text-white rounded font-weight-bold shadow-sm">
    <div class="row col-md-12 mx-0">
        <div class="row col-md-3 mx-0">
            <div class="col-md-6 my-auto text-center space-x-1 row mx-0">
                <div class="bg-transparent spinner-grow">
                    <i class="fas fa-envelope" style="font-size: 22px;"></i>
                </div>
                <span> Mails : <span><?= $nbMails['total'] ?></span> </span>
            </div>
            <div class="col-md-6 text-center">
                <div class="row col-md-12 p-1 space-x-1">
                    <i class="fas fa-arrow-up"></i>
                    <span><?= $nbMails['sortant'] ?></span>
                </div>
                <div class="row col-md-12 p-1 space-x-1">
                    <i class="fas fa-arrow-down"></i>
                    <span><?= $nbMails['entrant'] ?></span>
                </div>
            </div>
        </div>
        <div class="row col-md-3 mx-0">
            <div class="col-md-6 my-auto text-center space-x-1 row mx-0">
                <div class="bg-transparent spinner-grow">
                    <i class="fas fa-phone-alt" style="font-size: 22px;"></i>
                </div>
                <span> Appels : <span><?= $nbAppels['total'] ?></span> </span>
            </div>
            <div class="col-md-6 text-center">
                <div class="row col-md-12 p-1 space-x-1">
                    <i class="fas fa-arrow-up"></i>
                    <span><?= $nbAppels['sortant'] ?></span>
                </div>
                <div class="row col-md-12 p-1 space-x-1">
                    <i class="fas fa-arrow-down"></i>
                    <span><?= $nbAppels['entrant'] ?></span>
                </div>
            </div>
        </div>
        <div class="row col-md-4 mx-0">
            <div class="col-md-6 my-auto text-center space-x-1 row mx-0">
                <div class="bg-transparent spinner-grow">
                    <i class="fas fa-comment" style="font-size: 22px;"></i>
                </div>
                <span> Messages : <span><?= $nbMessages['total'] ?></span> </span>
            </div>
            <div class="col-md-6 text-center">
                <div class="row col-md-12 p-1 space-x-1">
                    <i class="fas fa-arrow-up"></i>
                    <span><?= $nbMessages['sortant'] ?></span>
                </div>
                <div class="row col-md-12 p-1 space-x-1">
                    <i class="fas fa-arrow-down"></i>
                    <span><?= $nbMessages['entrant'] ?></span>
                </div>
            </div>
        </div>
        <div class="row col-md-2 mx-0 my-auto space-x-1">
            <div class="bg-transparent spinner-grow">
                <i class="fas fa-tasks" style="font-size: 22px;"></i>
            </div>
            <span> Total : <?= $Total ?> taches </span>
        </div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-12">

        <div class="card">
            <div class="modal-content">
                <div class="card-header bg-secondary text-white">
                    <div class="row">

                        <div class="col-md-12">
                            <h2 class="text-center font-weight-bold"> Nombre des differents opportunites par gestionnaire</h2>
                        </div>

                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive col-md-12 offset-0">
                        <table class="table table-bordered" <?= (empty($tabAllCodesOP)) ? 'hidden' : '' ?> id="tabledata" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Gestionnaire</th>
                                <?php
                                    foreach ($activites as $acti) {
                                ?>
                                        <th>Nb <?= $acti->libelleActivity ?> </th>
                                <?php
                                    }
                                ?>
                                    <th>Total taches</th>
                                    
                                    <th>Gestionnaire</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tableStat">
                                <?php
                                $i = 1;
                                    foreach ($tabGes as $ges) {
                                ?>
                                    <tr>
                                        <td> <?= $i ?> </td>
                                        <td> <?= $ges->fullName ?> </td>
                                        <?php
                                            foreach ($activites as $acti) {
                                        ?>
                                            <td id="<?= $acti->codeActivity ?>_<?= $ges->idUtilisateur ?>"> <?= $ges->codes[$acti->codeActivity] ?> </td>
                                        <?php
                                            }
                                        ?>
                                        <td id="total_<?= $ges->idUtilisateur ?>"> <?= $ges->Total ?> </td>

                                        <td> <?= $ges->fullName ?> </td>
                                        <td> <?= $i++ ?> </td>
                                    </tr>
                                <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                        <div <?= (empty($tabAllCodesOP)) ? 'class="text-center font-weight-bold"' : 'hidden' ?> >
                            Aucun resultat trouv&eacute;
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<div class="row" <?= (empty($tabAllCodesOP)) ? 'hidden' : '' ?> id="chartsGen">
    <?php
        $i = 1;
        foreach ($activites as $activite) {
            if ( $activite->codeActivity < 0 ) {
                $code = $activite->libelleActivity;
            }
            else {
                $code = $activite->codeActivity;
            }
    ?>
            <div class="col-md-12 mt-3 p-2" style="height: <?= ( $tabTotalAllOP[$activite->codeActivity] > 0 ) ? '58vh' : '40vh' ?>;">
                <div class="card-header bg-danger font-weight-bold text-white">
                    <div class="row">
                        <span class="col-md-10">
                            <?= $i++ ?>. Nombre de <?= $activite->libelleActivity ?>
                        </span>
                        <span class="col-md-1"> Total <span id="total_<?= $code ?>"><?= $tabTotalAllOP[$activite->codeActivity] ?></span></span>
                        <button class="col-md-1 btn btn-secondary" <?= (isset($activite->lien) || $activite->codeActivity == 0) ? '' : 'hidden' ?> onclick="ListOP('<?= $code ?>', '<?= $activite->lien ?>')">Liste</button>
                    </div>
                </div>
                <div class="card chart p-2" style="height: 80%;" id="chart_<?= $code ?>">
                    <canvas id="myChart_<?= $code ?>" style="width:100%; height:100%;"></canvas>
                <?php
                if ($tabTotalAllOP[$activite->codeActivity] <= 0) {
                ?>
                    <div style="height: 80%;" class="text-center pt-5">Aucune donnée disponible ! </div>
                <?php
                }
                ?>
                </div>
            </div>
    <?php
        }
    ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<script src="<?= URLROOT ?>/assets/vendor/jquery/jquery.min.js" crossorigin="anonymous"></script>

<script>
    var role = `<?= $roleUser ?>`;
    var isAdmin = `<?= $_SESSION["connectedUser"]->isAdmin ?>`;

    $(document).ready(function() {
        chartjsOP();
    });

    function chartjsOP() {
        <?php
            foreach ($tabAllCodesOP as $code) {
                $chartData = [];
                $labels = [];
                $colors = [];
                
                foreach ($tabGes as $ges) {
                    if (isset($ges->codes[$code])) {
                        $nb = $ges->codes[$code];
                    }
                    else {
                        $nb = 0;
                    }
                        $chartData[] = $nb;
                        $labels[] = $ges->fullName . ' : ' . $nb;
                        $colors[] = $ges->color;
                }

                $chartDataJson = json_encode($chartData);
                $labelsJson = json_encode($labels);
                $colorsJson = json_encode($colors);
                if ( $code == -1 ) {
                    $var ="Mails";
                }
                else if ( $code == -2 ) {
                    $var = "Appels";
                }
                else if ( $code == -3 ) {
                    $var = "Messages";
                }
                else {
                    $var = $code;
                }
            ?>
        var ctx_<?= $var ?> = document.getElementById('myChart_<?= $var ?>').getContext('2d');
            new Chart(ctx_<?= $var ?>, {
            type: 'pie',
            data: {
                labels: <?= $labelsJson ?>,
                datasets: [{
                    data: <?= $chartDataJson ?>,
                    backgroundColor: <?= $colorsJson ?>,
                    hoverOffset: 4,
                }]
            },
            options: {
                aspectRatio: 2,
                responsive: true,
                legend: {
                    display: true,
                    position: 'left',
                    align: "end"
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return data.labels[tooltipItem.index] + ': ' + tooltipItem.yLabel;
                        }
                    }
                },
            elements: {
                arc: {
                    borderWidth: 1,
                    borderColor: '#fff'
                }
            }
            }
        });

        <?php
                if ($tabTotalAllOP[$code] <= 0) {
        ?>

            $doc = document.getElementById('myChart_<?= $var ?>');
            $doc.style.display = "none";

        <?php
              }
            }
        ?>
    }

    function onChangePeriode() {
        if ($("#periode option:selected").val() == "perso" || $("#periode option:selected").val() == "day") {
            $("#changeperso").removeAttr("hidden");
            if ($("#periode option:selected").val() == "perso") {
                $("#date2").removeAttr("hidden");

                $("#legendPeriode").text("Personnaliser");
            } else {
                $("#date2").attr("hidden", "hidden");
                $("#legendPeriode").text("A la date de :");
            }
        } else {
            $("#changeperso").attr("hidden", "hidden");
        }
    }

    function ListOP(code, typeDeclaration) {
        var user = $("#gestSelection").val();
        if (user == 'tous') {
            user = ';<?= $site ?>';
        }
        var precison =
            `${$("#periode").val()};${$("#date1Input").val()};${$("#date2Input").val()};${$("#statut").val()};${user}`

        window.open(`<?= URLROOT ?>/GestionOP/listeOP/${code}/${typeDeclaration};${precison}`);

    }
</script>