<input type="hidden" name="" value="<?= $user->idUtilisateur ?>" id="idUtilisateur">
<input type="hidden" name="" value="<?= $user->fullName ?>" id="nomUtilisateur">
<input type="hidden" name="" value="<?= $user->role ?>" id="role">
<?php
$role = $_SESSION["connectedUser"]->libelleRole;
$login = $_SESSION["connectedUser"]->login;

$dossier = $role == "Gestionnaire EXTERNE" ? "GestionnaireExterne" : "Gestionnaire";

?>
<!-- modal de chargement -->
<div class="modal fade" id="detailEvent" data-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content"
            style=" font-weight: bold; background-color:#FAFAFA; text-align: center; padding: 3px; border: 3px solid red; color: black; font-size: 13px; padding:5%">
            <div class="modal-body text-center">
                <div style=" font-weight: bold; background-color:#d9dee3; width: 70%; text-align: center; border: 2px solid red; color: black; font-size: 13px; margin: auto; margin-bottom: 15px;height:50px"
                    class="py-3">
                    Details Evenement
                </div><br><br>
                <table class="table table-bordered">
                    <tr class="row">
                        <td class="col-md-4">Titre</td>
                        <td id="titre" class="col-md-8"></td>
                    </tr>
                    <tr class="row">
                        <td class="col-md-4">Type</td>
                        <td id="type" class="col-md-8"></td>
                    </tr>
                    <tr class="row">
                        <td class="col-md-4">Lieu</td>
                        <td id="lieu" class="col-md-8"></td>
                    </tr>
                    <tr class="row" id="rowNumOp">
                        <td class="col-md-4">numéro OP</td>
                        <td id="nameOp" class="col-md-8"></td>
                    </tr>
                    <tr class="row">
                        <td class="col-md-4">Date</td>
                        <td id="date" class="col-md-8"></td>
                    </tr>
                    <tr class="row">
                        <td class="col-md-4">Heure</td>
                        <td id="hd" class="col-md-8"></td>
                    </tr>

                    <tr class="row">
                        <td class="col-md-4">Source</td>
                        <td id="source" class="col-md-8"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-success" id="btnOP">Voir l'opportunité</button>
                <button data-dismiss="modal" class="btn btn-secondary"
                    onclick="$('#detailEvent').modal('hide')">Fermer</button>
            </div>
        </div>

    </div>
</div>

<!-- modal de chargement -->
<div class="modal fade" id="creerRDVPerso" data-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="card bg-white">
                    <div class="card-body">

                        <div class='row mt-5' id="divPageRDV1" hidden>
                            <fieldset>
                                <legend
                                    class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-secondary mx-0'>
                                    <span class="nbrPageRDV"> </span>
                                </legend>

                                <div class="card">

                                    <div class="card-body bg-white">

                                        <hr>

                                        <div
                                            style=" font-weight: bold; background-color:#d9dee3; width: 70%; text-align: center; padding: 3px; border: 2px solid #eb7f15; color: black; fonti-size: 13px; margin: auto; margin-bottom: 15px;">
                                            Mes Disponibilités
                                        </div>

                                        <hr>
                                        <!--    <div class="row mt-2 mb-2 ml-2">
                                            <div class="col-md-12 row">
                                                <div class="col-md-3 row">
                                                    <div class="col-md-3" style="background-color: #d3ff78;">

                                                    </div>
                                                    <div class="col-md-9">
                                                        <span><?= /* $op->rv ? "Même Date & Même Heure" : */ "RV à moins de 30min" ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 row">
                                                    <div class="col-md-3" style="background-color: lightblue;">

                                                    </div>
                                                    <div class="col-md-9">
                                                        <span><?= /* $op->rv ? "Même Date mais Heure différente" : */ "RV  entre 30min et 1H" ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 row">
                                                    <div class="col-md-3" style="background-color: #ffc020;">

                                                    </div>
                                                    <div class="col-md-9">
                                                        <span>Date différente</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 row">
                                                    <div class="col-md-3" style="background-color: #FF4C4C;">

                                                    </div>
                                                    <div class="col-md-9">
                                                        <span>Commercial Sans RDV</span>
                                                    </div>
                                                </div>
                                            </div>

                                        </div> -->
                                        <div id="placeCommerciaux">
                                            <!-- <div style="text-align: center; color: red; font-weight: bold;"> Veuillez définir la date et heures du RDV !</div> -->
                                        </div>

                                        <div>
                                            <div
                                                class="offset-2 col-md-8 pagination pagination-sm row text-center mb-3 mt-1">
                                                <div class="pull-left page-item col-md-6 p-0 m-0">
                                                    <div id="btnPrecedentRDV">
                                                        <a type="button" class="text-center btn"
                                                            style="background-color: grey;color:white"
                                                            onclick="onClickPrecedentRDV()">Dispos Prec. << </a>
                                                    </div>
                                                </div>
                                                <div id="btnSuivantRDV" class="pull-right page-item col-md-6 p-0 m-0"><a
                                                        type="button" class="text-center btn"
                                                        style="background-color: grey;color:white"
                                                        onclick="onClickSuivantRDV()">>>
                                                        Dispos Suiv.</a></div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </fieldset>
                        </div>

                        <div class='row mt-5' id="divPageRDV2" hidden>
                            <fieldset>
                                <legend
                                    class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-secondary mx-0'>
                                    <span class="nbrPageRDV"> </span>
                                </legend>

                                <div class="card">

                                    <div class="card-body bg-white">

                                        <div id="divTabHoraire">
                                            <div class="font-weight-bold">
                                                <span class="text-center text-danger">2. Veuillez selectionner l'heure
                                                    de disponibilité</span>
                                            </div><br>
                                            <div style=" font-weight: bold; background-color:#d9dee3; width: 100%; text-align: center; padding: 3px; border: 2px solid #eb7f15; color: black; fonti-size: 13px; margin: auto; margin-bottom: 15px;"
                                                id="plageHoraire">

                                            </div><br>


                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label for="">Date RDV:</label>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control" id="inputDate" disabled>
                                                </div>
                                            </div>
                                            <hr>

                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label for="">Heure début:</label>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="time" class="form-control" id="inputHD"
                                                        onchange="verifyTime()">
                                                </div>
                                            </div>
                                            <hr>

                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label for="">Heure fin:</label>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="time" class="form-control" id="inputHF"
                                                        onchange="verifyTime()">
                                                </div>
                                            </div>
                                            <hr>

                                        </div>

                                    </div>
                                </div>

                            </fieldset>
                        </div>

                        <div class='row mt-5' id="divPageRDV3" hidden>
                            <fieldset>
                                <legend
                                    class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-secondary mx-0'>
                                    <span class="nbrPageRDV"> </span>
                                </legend>

                                <div class="card">

                                    <div class="card-body bg-white">
                                        <div class="col-md-12">
                                            DETAILS DU RENDEZ-VOUS
                                            <hr>
                                            <input type="hidden" name="" id="listidCntPublierPour">
                                            <div class="row">
                                                <div class="col">
                                                    <input type="radio" name="priorite" value="0" checked
                                                        class="priorite">
                                                    <label for="">Cacher</label>
                                                </div>
                                                <div class="col">
                                                    <input type="radio" name="priorite" value="1" class="priorite">
                                                    <label for="">Publier</label>
                                                </div>
                                                <div class="col">
                                                    <input type="radio" name="priorite" value="2"
                                                        onchange="showModalPublierPour(this)" class="priorite">
                                                    <label for="">Publier pour</label>
                                                </div>
                                            </div>

                                            <div class="row mt-3">
                                                <div class="col-md-3">
                                                    <label for="">Description:</label>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="titreEv" id="titreEv" class="form-control">
                                                </div>
                                            </div>
                                            <hr>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label for="">Adresse </label>
                                                </div>
                                                <div class="col-md-12">
                                                    <div id="hiddenId2" hidden></div>
                                                    <input type="text" name="adresse1C" class="form-control"
                                                        id="adresse1C" onkeyup="completAdress3(this.value, 'contact')">
                                                    <ul id="listAdressCnt">

                                                    </ul>
                                                </div>
                                            </div>
                                            <hr>

                                            <div class="row mt-3">
                                                <div class="col-md-6 mb-1">
                                                    <div class="col-md-12">
                                                        <label for="">Code Postal</label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="text" maxlength="5" name="codePostal"
                                                            class="form-control" id="codePostalC" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-1">
                                                    <div class="col-md-12">
                                                        <label for="">Ville</label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="text" readonly name="ville" class="form-control"
                                                            id="villeC">
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>



                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>


                    </div>
                    <div class="card-footer">
                        <button onclick="$('#modalPRDV').modal('hide')" class="btn btn-danger"
                            data-dismiss="modal">Annuler</button>
                        <button onclick="loadPageRDV('precedent')" class="btn btn-secondary"
                            id="pagePRDV">Precedent</button>
                        <button onclick="loadPageRDV('suivant')" class="btn btn-secondary"
                            id="pageSRDV">Suivant</button>
                        <button onclick="enregistrerEvPerso()" class="btn btn-success" id="pageTRDV">Terminer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal de chargement -->
<div class="modal fade" id="modalContactSalarie" data-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">

                <table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Nom et Prenom</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($personnels as $key => $value) {
                            if ($user->idUtilisateur  != $value->idUtilisateur) {
                        ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="idCntPublierPour" id=""
                                            value="<?= $value->idUtilisateur ?>" class="contactPublier">
                                    </td>
                                    <td>
                                        <?= $value->fullName ?>
                                    </td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button onclick="ChoisirListePublier()" class="btn btn-success">Enregistrer</button>
            </div>
        </div>

    </div>
</div>

<div class="row">
    <div class="col-md-3" <?= $personnels == [] ? 'hidden' : "" ?>>
        <!-- <fieldset>
            <legend class='text-white col-md-12 text-uppercase font-weight-bold text-center py-2 badge bg-dark mx-0'>
                Gestionnaires </legend> -->
        <div class="card">
            <select name="" id="gestSelection" class="form-control" onchange="loadAgenda(this)">
                <option value="<?= $user->idUtilisateur ?>"> Mon agenda </option>
                <?php
                foreach ($personnels as $key => $value) {
                    if ($user->idUtilisateur  != $value->idUtilisateur) {
                ?>
                        <option value="<?= $value->idUtilisateur ?>"> <?= $value->fullName ?> </option>
                <?php
                    }
                }
                ?>
            </select>
        </div>
        <!--  </fieldset> -->
    </div>
    <div class="col-4">
        <button onclick="priseRVPerso()" class="btn btn-info">Creer un Rendez-vous perso</button>
    </div>
    <div class="col-2"></div>
    <div class="col-6">
        <button class="col-3 font-weight-bold btn btn-dark" onclick="clickOutlook()"><i class="far fa-calendar"></i> Outlook</button>
        <button class="col-3 font-weight-bold btn btn-primary" onclick="clickGoogle()"><i class="far fa-calendar-alt"></i> Google</button>
        <div class="col-4 font-weight-bold btn bg-danger" onclick="clickExport()">
            <span class="text-white"><i class="far fa-calendar-alt"></i> Exporter </span>
        </div>
        <div id="export" class="hidden">
            <div class="row">
                <div class="col-md-12" onclick="exportICS()">
                    <span>ICS</span>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12" onclick="exportCSV()">
                    <span>CSV</span>
                </div>
            </div>
        </div>
    </div>
<input type="hidden" id="URLROOT_GESTION_WBCC_CB" value="<?= URLROOT_GESTION_WBCC_CB ?>">
</div>
<div id="calendar" style="margin-top: 20px;"></div>

<style>
    .fc-scrollgrid-sync-inner>a {
        color: black
    }
</style>

<script src="<?= URLROOT ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="https://apis.google.com/js/api.js"></script>

<script>
    var calendarEl = document.getElementById('calendar');
    var calendar;
    var NpageRDV = 1;
    var NbrpageTRDV = 3;
    var URLROOT_GESTION_WBCC_CB = $('#URLROOT_GESTION_WBCC_CB').val();

    document.addEventListener('DOMContentLoaded', function() {
        getEvents($('#idUtilisateur').val());
    });
    
    function getEvents(idUser) {
        var events = [];
        $.ajax({
            url: `<?= URLROOT_GESTION_WBCC_CB ?>/public/json/evenement.php?action=getUserEvent&source=wbcc`,
            type: 'GET',
            data: {
                idUtilisateur: $('#idUtilisateur').val(),
                idUtilisateurEv: idUser,
            },
            dataType: "JSON",
            success: function(response) {
                
                response.forEach(element => {
                    events.push({
                        title: element.typeEvenement == null ? `Event - ${element.source }` : `RV - ${element.type} - ${element.source }`,
                        start: element.dateEvenement + "T" + element.heureDebutEvenement,
                        end: element.dateEvenement + "T" + element.heureFinEvenement,
                        description: (element.titreEvenement == null ?
                                `Event - ${element.source }` : element.titreEvenement) + ";" +
                                element.lieu,
                                // url: element.idOpportunityF,
                                textColor: 'white',
                                color: element.typeEvenement == "Rendez-Vous" ? (element.type ==
                                "Perso" ? '#616161' : (element.type == "EXPERTISE" ? '#D32F2F' :
                                (element.type == "RT" ? '#5D4037' : (element.type ==
                                "Feries" ? '#1DE9B6' : '#FBC02D')))) : (element
                            .typeEvenement == "Reunion" ? (element.type ==
                            "Reunion avant vente" ? '#FF4081' : '#AB47BC') : (element
                            .typeEvenement == "Tâche à faire" ? '#03A9F4' : '#F10A0A')),
                            source: element.source + ";" + element.typeEvenement + ";" + element
                            .dateEvenement + ";" + element.heureDebutEvenement + " - " + element
                            .heureFinEvenement + ";" + element.type + ";" + (element
                            .idOpportunityF == null ? "" : element.idOpportunityF)
                        });
                        
                        console.log(events);
                        
                    });
                    
                    console.log(events);
                    initCalendar(events);
                    
                    
                },
                error: function(response) {
                    alert("ok");
                    console.log(response);
                    
                },
                complete: function() {
                    
                },
            });
    }

    function clickExport() {
        $('#export').toggleClass('hidden');
    }

    function exportToICS(events) {
        let icsContent = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//YourAppName//NONSGML v1.0//EN\n";

        events.forEach(event => {
            icsContent += `
            BEGIN:VEVENT
            UID:${event.id}@yourapp.com
            SUMMARY:${event.title}
            DESCRIPTION:${event.extendedProps.description}
            LOCATION:${event.extendedProps.location || ''}
            DTSTART:${new Date(event.start).toISOString().replace(/[-:]/g, '').replace('.000', '')}
            DTEND:${new Date(event.end).toISOString().replace(/[-:]/g, '').replace('.000', '')}
            END:VEVENT
            `.trim();
        });

        icsContent += "\nEND:VCALENDAR";

        // Télécharger le fichier ICS
        const blob = new Blob([icsContent], { type: "text/calendar" });
        const url = URL.createObjectURL(blob);

        const a = document.createElement("a");
        a.href = url;
        a.download = "events.ics";
        a.click();

        URL.revokeObjectURL(url);
    }

    function exportICS() {
        downloadICS(calendar.getEvents());
    }

    function exportToCSV(events) {
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "ID,Titre,Description,Début,Fin,Localisation\n";

        events.forEach(event => {
            csvContent += `"${event.id}","${event.title}","${event.extendedProps.description}","${event.start}","${event.end}","${event.extendedProps.location || ''}"\n`;
        });

        // Télécharger le fichier CSV
        const encodedUri = encodeURI(csvContent);
        const a = document.createElement("a");
        a.href = encodedUri;
        a.download = "events.csv";
        a.click();
    }

    function exportCSV() {
        downloadCSV(calendar.getEvents());
    }

    function downloadCSV(events) {
        const formData = new FormData();
        formData.append('events', JSON.stringify(events));

        fetch('<?= URLROOT_GESTION_WBCC_CB ?>/public/json/evenement.php?action=exportToCSV&source=wbcc', {
            method: 'POST',
            body: formData
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'events.csv';
            document.body.appendChild(a);
            a.click();
            a.remove();
        })
        .catch(error => console.error('Erreur lors de l\'export CSV :', error));
    }

    function downloadICS(events) {
        const formData = new FormData();
        formData.append('events', JSON.stringify(events));

        fetch('<?= URLROOT_GESTION_WBCC_CB ?>/public/json/evenement.php?action=exportToICS&source=wbcc', {
            method: 'POST',
            body: formData
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'events.ics';
            document.body.appendChild(a);
            a.click();
            a.remove();
        })
        .catch(error => console.error('Erreur lors de l\'export ICS :', error));
    }

    function gapiClientLoad() {
        gapi.load('client:auth2', () => {
            gapi.client.init({
                apiKey: 'AIzaSyDIhVkSvp2tTK-rqKQ_vUl-LAf0b-ISfPw',
                clientId: '143200318289-8q32tasc8vljv36pg49kjgcc5nofu2e7.apps.googleusercontent.com',
                discoveryDocs: ['https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest'],
                scope: 'https://www.googleapis.com/auth/calendar',
            }).then(() => {
                console.log("Google API client loaded!");
            });
        });
    }

    function addEventToGoogleCalendar(event) {
        gapi.auth2.getAuthInstance().signIn().then(() => {
            gapi.client.calendar.events.insert({
                calendarId: 'primary',
                resource: {
                    summary: event.title,
                    description: event.extendedProps.description,
                    start: {
                        dateTime: new Date(event.start).toISOString(),
                        timeZone: 'Europe/Paris',
                    },
                    end: {
                        dateTime: new Date(event.end).toISOString(),
                        timeZone: 'Europe/Paris',
                    },
                },
            }).then(response => {
                alert('Événement ajouté à Google Calendar!');
                console.log(response);
            });
        });
    }

    function loginWithGoogle() {
        const clientId = "<?= GOOGLE_CLIENT_ID ?>";
        const redirectUri = "<?= REDIRECT_URI ?>";
        const scope = "<?= GOOGLE_OAUTH_SCOPE ?>";
        const responseType = 'code';

        const authUrl = `https://accounts.google.com/o/oauth2/auth?client_id=${clientId}&redirect_uri=${encodeURIComponent(redirectUri)}&response_type=${responseType}&scope=${encodeURIComponent(scope)}&access_type=online`;

        window.location.href = authUrl;
    }

    function clickGoogle() {
        window.location.href = "<?= URLROOT_GESTION_WBCC_CB ?>/public/json/evenement.php?action=google-calendar&source=wbcc&user=" + $('#idUtilisateur').val();
    }

    
    function initCalendar(events) {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,' // user can switch between the two
            },
            locale: 'fr',
            dayHeaders: true,
            dayHeaderFormat: {
                weekday: 'short',
                month: 'numeric',
                day: 'numeric',
                omitCommas: true
            },
            buttonText: {
                today: "Aujourd'hui",
                month: 'Mois',
                week: 'Semaine',
                day: 'Jour',
            },
            firstDay: 1,
            fixedWeekCount: true,
            showNonCurrentDates: true,
            //events: events,
            eventClick: function(info) {

                var detail = info.event.extendedProps.source.split(";");
                var desc = info.event.extendedProps.description.split(";");

                $("#titre").text(desc[0]);
                $("#type").text(detail[1] + " - " + detail[4]);
                $("#date").text(detail[2]);
                $("#hd").text(detail[3]);
                $("#source").text(detail[0]);
                $("#lieu").text(desc[1]);

                console.log(detail[5]);


                if (detail[5] != "" && detail[5] != 0) {
                    // if (info.event.url != null && info.event.url != "" && info.event.url != "NULL") {

                    $("#btnOP").removeAttr("hidden");

                    $("#btnOP").attr("onclick", `detailOp(${detail[5]})`);

                    $.ajax({
                        url: `<?= URLROOT ?>/public/json/opportunity.php?action=findOpByIDS&source=Extranet`,
                        method: 'POST',
                        data: {
                            data: [detail[5]],
                        },
                        dataType: 'JSON',
                        success: function(response) {


                            $("#nameOp").text(response[0]["op"].name);


                        },
                        error: function(response) {
                            console.log(response);
                        }
                    });
                } else {
                    $("#btnOP").attr("hidden", "hidden");
                    $("#rowNumOp").attr('hidden', 'hidden');
                }


                $("#detailEvent").modal("show");

                // change the border color just for fun
                info.el.style.borderColor = 'red';
            }

        });
        calendar.addEventSource(events)
        calendar.render();

    }

    function loadAgenda(val) {

        getEvents(val.value);
        // }
    }
    let k = 0;
    let iColor = 0;
    var nbDispo = 0;
    var tab = [];

    function priseRVPerso() {
        $('#creerRDVPerso').modal('show');
        NpageRDV = 1;
        nbDispo = 0;
        loadPageRDV("suvant");
        getDisponiblites();
    }

    function loadPageRDV(params) {

        let verif = true;
        let text = "";

        for (let index = 1; index <= NbrpageTRDV; index++) {
            $('#divPageRDV' + index).attr("hidden", "hidden");
        }

        if (params == 'suivant') {

            if (NpageRDV == 3) {
                editLot()
            }


            NpageRDV++;
        } else if (params == 'precedent') {

            NpageRDV--;
        }


        $('#divPageRDV' + NpageRDV).removeAttr("hidden");

        $(".nbrPageRDV").text(NpageRDV + "/" + NbrpageTRDV);


        if (NpageRDV == 1) {
            $('#pagePRDV').attr("hidden", "hidden");
        } else {
            $('#pagePRDV').removeAttr("hidden");
        }

        if (NpageRDV == NbrpageTRDV) {
            $('#pageSRDV').attr("hidden", "hidden");
            $('#pageTRDV').removeAttr("hidden");

        } else {
            $('#pageSRDV').removeAttr("hidden");
            $('#pageTRDV').attr("hidden", "hidden");
        }
    }

    function onClickPrecedentRDV() {
        // if (Npage != 1) {
        iColor = ((NpageRDV - 1) * 2) - 2;
        //  Npage--;
        k = k - nbDispo - 10;
        afficheBy10InTable();

    }

    function onClickSuivantRDV() {

        afficheBy10InTable();

    }

    function getDisponiblites() {

        $.ajax({
            url: `<?= URLROOT_GESTION_WBCC_CB ?>/public/json/evenement.php?action=getDisponibilites&source=wbcc&origine=extranet&forcage=1`,
            method: 'POST',
            data: {
                adresseRV: "",
                codePostal: "",
                ville: "",
                batiment: "",
                etage: "",
                libelleRV: "",
                dateDebut: "",
                dateFin: "",
                req: `AND idUtilisateur = ${$('#idUtilisateur').val()}`,
            },
            beforeSend: function() {
                document.getElementById('placeListCommerciaux').innerHTML = `<div style="text-align: center;">
                                                            <img src="<?= URLROOT ?>/public/images/loader-image.gif" alt class="rounded-circle" style="width: 100px;" />
                                                            <br><br>
                                                            <p style="color: red; font-weight: bold;">
                                                                Chargement du tableau en cours...
                                                            </p>
                                                        </div>`;
            },
            success: function(response) {


                const result = JSON.parse(response);
                console.log(result.length);
                console.log(result);

                if (result.length == 0) {
                    var html = `<table style="font-weight:bold; font-size:15px; " border ="1" width="100%" cellpadding="10px">
                        <tr class="tr">
                            <td style="background-color : #e74a3b; color: white;" class="tdClass"  align="center"> 
                                Aucune disponibilite ! 
                            </td>
                            </tr>
                    </table>`;
                    document.getElementById('placeListCommerciaux').innerHTML = html;
                } else {
                    tab = result;
                    taille = tab.length;
                    nbPageTotal = Math.ceil(tab.length / 10);
                    //nbPage++; 
                    afficheBy10InTable();
                }

            },
            error: function(response) {
                console.log(response);
            }
        });
    }

    function afficheBy10InTable() {
        var test = 0;
        var kD = k;
        first = k;
        $('#placeListCommerciaux').empty();
        /*   var html =
              `<table style="font-weight:bold; font-size:15px; " id="table" border ="1" width="100%" cellpadding="10px"><tr><th colspan="7">DISPONIBLITES DES EXPERTS- Page${nbPage}/${nbPageTotal}</th></tr>`; */
        var html = "";
        if (tab.length != 0) {
            for (var i = 0; i < 2; i++) {
                html += `<tr class="tr">`;
                for (var j = 0; j < 5; j++) {
                    html +=
                        `<td  class="tdClass p-2"  align="center" id="cel${k}" value="${k}"><div style="background-color : ${tab[k].couleur}" class="p-1 rounded"><br>${tab[k].commercial} <br> ${tab[k].date} <br> ${tab[k].horaire}<br><span hidden="">-${tab[k].idCommercial}-${tab[k].marge}-${tab[k].duree}min -</span></div> </td>`;
                    k++;
                    test++;
                    if (k == taille || test > 10 || k == 50) {
                        if (j == 5)
                            iColor++;
                        break;
                    }
                }

                html += `</tr>`;
                if (k == taille || test > 10 || k == 50) {
                    if (j != 5 && i == 2)
                        iColor++;
                    break;
                }
                iColor++;
            }
        }
        html += `</table>`;
        $('#placeListCommerciaux').append(html);
        nbDispo = k - kD;

        if (k == 10) {
            $('#btnPrecedentRDV').attr("hidden", "hidden");
        } else {
            $('#btnPrecedentRDV').removeAttr("hidden");
        }

        if (k == tab.length) {
            $('#btnSuivantRDV').attr("hidden", "hidden");
        } else {
            $('#btnSuivantRDV').removeAttr("hidden");
        }

        //recuperer la valeur d4une cellule
        $(".tdClass").click(function() {
            $("#INFO_RDV").text("");
            $('#divPriseRvRT').attr("hidden", "hidden");
            $('#expertRV').attr("value", "");
            $('#idExpertRV').attr("value", "0");
            $('#dateRV').attr("value", "");
            $('#heureRV').attr("value", "");
            $(".tr > td").css("box-shadow", "0px 0px 0px 0px lightgray");
            // $(".tr > td").css("background-color", "white");
            // $(this).closest("td").css("background-color", "lightgray");
            $(this).closest("td").css("box-shadow", " 1px 1px 5px 5px  #e74a3b");
            // $(this).closest("td").css("position", "relative");
            // $(this).closest("td").css("z-index", "2");
            var item = $(this).closest("td").html();

            var tabItem = item.split("<br>");
            tabItem.splice(0, 1);
            console.log(tabItem);
            let nomCommercial = tabItem[0];
            let DATE_RV = tabItem[1];
            let HEURE_D = tabItem[2].split("-")[0];
            let HEURE_F = tabItem[2].split("-")[1];
            idCommercialRDV = tabItem[3].split("-")[1];
            let marge = tabItem[3].split("-")[2];
            let DUREE = tabItem[3].split("-")[3];
            // console.log(idCommercialRDV);
            //Nouveau tableau
            heure = Number(HEURE_D.split(":")[0].trim());
            min = Number(HEURE_D.split(":")[1].trim());
            secondHD = (heure * 3600 + min * 60) * 1000;
            heure = Number(HEURE_F.split(":")[0].trim());
            min = HEURE_F.split(":")[1].trim();
            //TEST IF FIN + MARGE
            secondHF = (heure * 3600 + min * 60 + ((marge == "" || marge == null) ? 0 : marge * 60)) * 1000;
            horaires = [];
            for (var i = secondHD; i < secondHF - 6000; i = i + 3600000) {
                j = i + 3600000;
                var time1 = msToTime(i);
                var time2 = msToTime(j);
                if (j <= secondHF) {
                    horaires.push(time1 + "-" + time2);
                }
            }
            nTaille = horaires.length;




            $('#inputDate').val(DATE_RV.replace(" ", "").split(' ')[1]);
            $("#plageHoraire").text(tabItem[2]);

            $("#inputHD").val(`${HEURE_D.replace(" ","")}`);
            $("#inputHF").val(`${HEURE_F.replace(" ","")}`);

            $("#inputHD").attr("onchange", `verifyTime('min','${HEURE_D.replace(" ","")}',this)`);
            $("#inputHF").attr("onchange", `verifyTime('max','${HEURE_F.replace(" ","")}',this)`);

            //afficheNewTable(nomCommercial, DATE_RV, DUREE);
        });
    }

    function verifyTime(type, time, val) {
        if (type == 'min') {
            console.log("verifier min");
            console.log(time);
            console.log(val.value);
            console.log(val.value < time);

            if (val.value < time) {
                val.value = time
            }
        }

        if (type == 'max') {
            console.log("verifier max");
            console.log(time);
            console.log(val.value);
            console.log(val.value > time);
            console.log($("#inputHF").val() < $("#inputHD").val());


            if (val.value > time || $("#inputHF").val() < $("#inputHD").val()) {
                val.value = time
            }
        }
    }

    //Conversion milliseconds en time
    function msToTime(s) {
        var ms = s % 1000;
        s = (s - ms) / 1000;
        var secs = s % 60;
        s = (s - secs) / 60;
        var mins = ((s % 60) >= 10) ? s % 60 : "0" + s % 60;
        var hrs = (((s - mins) / 60) >= 10) ? (s - mins) / 60 : "0" + (s - mins) / 60;

        return hrs + ':' + mins;
    }

    function afficheNewTable(nomCommercial, date, duree) {
        $('#divTabHoraire').empty();
        var html =
            `<div class="font-weight-bold">
                                <span class="text-center text-danger">2. Veuillez selectionner l'heure de disponibilité</span>
                            </div>
        <table style="font-weight:bold; font-size:15px; margin-top : 20px" id="table" border ="1" width="100%" cellpadding="10px"><tr><th colspan="${nTaille}">DISPONIBLITES DE ${nomCommercial} à la date du ${date}</th></tr>`;
        html += `<tr class="ntr" style="background-color: lightgray">`;
        for (var i = 0; i < nTaille; i++) {
            html += `<td class="ntdClass"  align="center" id="cel${i}" value="${i}"> ${horaires[i]} </td>`;
        }
        html += `</tr>`;
        html += `</table>`;
        $('#divTabHoraire').append(html);

        $(".ntdClass").click(function() {
            $(".ntr > td").css("background-color", "lightgray");
            $(this).closest("td").css("background-color", "#e74a3b");
            var item = $(this).closest("td").html();

            console.log(item);
            commercialRDV = nomCommercial;
            dateRDV = date;
            heureDebutRDV = item.split("-")[0];
            heureFinRDV = item.split("-")[1];
            let DUREE = duree;
            let HEURE_RV = item;
            if (idCommercialRDV != "0") {
                $("#INFO_RDV").text("RDV à prendre pour " + commercialRDV + " le " + dateRDV + " de " +
                    heureDebutRDV +
                    " à " + heureFinRDV);
                $('#inputComm').val(commercialRDV);
                //  $('#idComm').val(idCommercialRDV);
                $('#inputDate').val(dateRDV.replace(" ", "").split(' ')[1]);
                $('#inputHD').val(heureDebutRDV.replace(" ", ""));
                $('#inputHF').val(heureFinRDV);
            }

        });
    }

    function removeElements() {
        let items = document.querySelectorAll(".list-items");
        items.forEach((item) => {
            item.remove();
        });
    }

    function displayNames3(value, type) {

        console.log(value);
        removeElements();
        $.ajax({
            url: '<?= URLROOT . '/public/json/ticket/Cron_Email.php' ?>',
            method: 'GET',
            data: {
                action: 'autoCompletPlaceId',
                item: value[1]
            },
            success: function(response2) {

                console.log(response2);

                const obj2 = JSON.parse(response2);
                var adresss = obj2['result']['adr_address'];

                console.log(obj2);

                document.getElementById('hiddenId2').innerHTML = adresss;
                var addr = $(".street-address").html();

                document.getElementById('adresse1C').value = addr == undefined ? value[0] : addr;;

                obj2.result.address_components.forEach(element => {
                    if (element["types"][0] == "postal_code") {
                        document.getElementById('codePostalC').value = element['long_name'];
                    }
                    if (element["types"][0] == "locality") {
                        document.getElementById('villeC').value = element['long_name'];
                    }
                    if (element["types"][0] == "administrative_area_level_1") {
                        document.getElementById('departementC').value = element['long_name'];
                    }
                    if (element["types"][0] == "administrative_area_level_2") {
                        document.getElementById('regionC').value = element['long_name'];
                    }
                });





            }
        });
    }

    function completAdress3(val, type) {
        $.ajax({
            url: '<?= URLROOT . '/public/json/ticket/Cron_Email.php' ?>',
            method: 'GET',
            data: {
                action: 'autoCompletString',
                item: val
            },
            success: function(response) {

                const obj = JSON.parse(response);
                removeElements();
                names = [];
                for (var k = 0; k < obj['predictions'].length; k++) {
                    var name = [obj['predictions'][k]['description'], obj['predictions'][k]['place_id']];
                    names.push(name);
                }


                // console.log(names);
                refreshAutocomplate3(val, type);
            }
        });
    }

    function refreshAutocomplate3(value, type) {
        removeElements();

        for (let i of names) {

            console.log(`displayNames3(["${i[0]}","${i[1]}"], '${type}')`);

            let listItem = document.createElement("li");
            listItem.style.margin = "10px";
            listItem.classList.add("list-items");
            listItem.style.cursor = "pointer";
            listItem.setAttribute("onclick", `displayNames3(["${i[0]}","${i[1]}"], '${type}')`);
            let word = i[0].substr(0, value.length);
            word += i[0].substr(value.length);
            listItem.innerHTML = word;

            if (type == "immeuble") {
                document.querySelector("#listAdressImm").appendChild(listItem);
            } else if (type == "company") {
                document.querySelector("#listAdressComp").appendChild(listItem);
            } else if (type == "contact") {
                document.querySelector("#listAdressCnt").appendChild(listItem);
            }

        }
    }

    function showModalPublierPour(val) {
        if (val.checked) {
            $("#modalContactSalarie").modal("show");
        }
    }

    function ChoisirListePublier() {
        var listidCntPublierPour = [];

        Array.prototype.forEach.call($('.contactPublier:checkbox:checked'), function(el) {
            if (el.value != "") {
                listidCntPublierPour.push(el.value);
            }
        });

        $('#listidCntPublierPour').val(listidCntPublierPour.join(";"));
        $("#modalContactSalarie").modal("hide");
    }

    function enregistrerEvPerso() {
        let dateDeb = document.getElementById('inputDate').value;
        let newHDVal = document.getElementById('inputHD').value;
        let newHFVal = document.getElementById('inputHF').value;
        $.ajax({
            url: `<?= URLROOT_GESTION_WBCC_CB ?>/public/json/evenement.php?action=saveEvAgenda&source=wbcc&origine=extranet`,
            method: 'POST',
            data: {
                idAuteur: $('#idUtilisateur').val(),
                nomAuteur: $('#nomUtilisateur').val(),
                idExpert: $('#idUtilisateur').val(),
                idCntExpert: '0',
                nomExpert: $('#nomUtilisateur').val(),
                nomContact: '',
                adresseRV: $('#adresse1C').val(),
                typeEvenement: "Rendez-Vous",
                typeRV: "Perso",
                dateEvenement: dateDeb.substr(6, 4) + '-' + dateDeb.substr(3, 2) + '-' + dateDeb.substr(0, 2),
                heureDebut: newHDVal,
                heureFin: newHFVal,
                priorite: $(".priorite:checked").val(),
                titreEvenement: $('#titreEv').val(),
                listIdPublier: $('#listIdPublier').val() == undefined ? "" : $('#listIdPublier').val(),
                ancDateEvent: "",
                ancHeureDebut: "",
                ancHeureFin: "",
                idOpportunity: '0',
            },
            beforeSend: function() {

            },
            success: function(response) {

                console.log(response);

                getEvents($('#idUtilisateur').val());
                $("#creerRDVPerso").modal("hide");
            },
            error: function(response) {
                console.log(response);
            }
        });

    }

    function detailOp(idOp) {

        window.open(`<?= URLROOT ?>/Gestionnaire/dossier/${idOp}`);
    }
</script>