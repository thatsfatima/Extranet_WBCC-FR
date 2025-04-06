$(document).ready(function () {
    let idJourFerie = false;
    let annee = $('#annee').val();
    let idSite = $('#idSiteF').val();

    // Gestion du modal de suppression
    $('.deleteJourFerie').click(function (e) {
        e.preventDefault();
        idJourFerie = $(this).data('id');
        const nomJourFerie = $(this).data('nom');
        console.log(idJourFerie);
        
        $('#deleteJourFerieModal').modal('show');
        $('#jourFerieNameToDelete').text(nomJourFerie);
    });

    // Confirmation de suppression
    $('#confirmDelete').click(function () {
        console.log(idJourFerie);
        
        if (idJourFerie) {
            $.ajax({
                url: $('#URLROOT').val() + '/GestionInterne/deleteJourFerie/' + idJourFerie,
                type: 'POST',
                success: function () {
                    $('#deleteJourFerieModal').modal('hide');
                    $('#successOperation').modal('show');
                    $('#msgSuccess').text("Le jour férié a bien été supprimé.");
                    window.location.href = $('#URLROOT').val() + '/GestionInterne/indexJourFerie?site=' + idSite + '&annee=' + annee;
                },
                error: function () {
                    $('#deleteJourFerieModal').modal('hide');
                    alert("Une erreur est survenue lors de la suppression du jour férié.");
                }
            });
        }
    });

    $('.anneeReference').click(function () {
        $.ajax({
            url: $('#URLROOT').val() + '/GestionInterne/ajoutJourFerie',
            type: 'GET',
            data: {
                anneeReference: $(this).data('value'),
                idSite: idSite,
                annee: annee
            },
            success: function () {
                window.location.href = $('#URLROOT').val() + '/GestionInterne/indexJourFerie?site=' + idSite + '&annee=' + annee;
            },
            error: function () {
                alert("Une erreur est survenue lors du changement d'année.");
            }
        })
        window.location.href = $('#URLROOT').val() + '/GestionInterne/indexJourFerie?site=' + idSite + '&annee=' + annee;
    });
    $("#validerJourFerie").on('click', function(event) {
        event.preventDefault();

        $('#validerJourFerie').prop('disabled', true);
        $('#validerJourFerie').text('En cours...');

        $('#loadingModal').modal('show');

        $('#msform').submit();

        $.ajax({
            url: `${URLROOT_GESTION_WBCC_CB}/public/json/evenement.php?action=saveEvAgenda&source=wbcc&origine=extranet`,
            method: 'POST',
            data: {
                idAuteur: $('#idUtilisateur').val(),
                nomAuteur: $('#auteur').val(),
                idExpert: $('#idUtilisateur').val(),
                idCntExpert: '0',
                nomExpert: $('#auteur').val(),
                nomContact: '',
                adresseRV: $('#adresse1C').val(),
                typeEvenement: "Feries",
                typeRV: "Feries",
                dateEvenement: $('#dateJourFerie').val(),
                heureDebut: '00:00',
                heureFin: '23:59',
                priorite: "1",
                titreEvenement: $('#nomJourFerie').val(),
                listIdPublier: $('#listIdPublier').val() === undefined ? "" : $('#listIdPublier').val(),
                ancDateEvent: "",
                ancHeureDebut: "",
                ancHeureFin: "",
                idOpportunity: '0',
            },
            beforeSend: function() {
                $('#loadingModal').modal('show');
            },
            success: function(response) {
                $('#loadingModal').modal('hide');
                $('#successOperation').modal('show');
                $('#msgSuccess').text("Le jour férié a bien été enregistré.");
            },
            error: function(xhr, status, error) {
                $('#loadingModal').modal('hide');
                $('#errorOperation').modal('show');
                $('#msgError').text("Une erreur est survenue lors de l'enregistrement du jour férié.");
                console.error('Erreur AJAX:', error);
            }
        });
    });
});

const URLROOT = document.getElementById("URLROOT").value;
const URLROOT_GESTION_WBCC_CB = document.getElementById("URLROOT_GESTION_WBCC_CB").value;

function exporterJourFerie() {
    var idSiteF = $('#idSiteF').val();
    var anneeJourFerie = $('#anneeJourFerie').val();
    $.ajax({
        url: URLROOT + "/public/json/jourFerie.php?action=genererPDF",
        method: 'POST',
        data: {
            idSiteF: idSiteF,
            annee: anneeJourFerie
        },
        beforeSend: function() {
            $('#loadingModal').modal('show');
        },
        success: function (response) {
            $('#successOperation').modal('show');
            $('#msgSuccess').text("Le PDF a bien été exporté.");
            setTimeout(() => {
                $('#loadingModal').modal('hide');
            }, 1000);
        },
        error: function (response) {
            alert("Une erreur est survenue lors de l'exportation du PDF. Détails: " + response.responseText);
        }
    });
}