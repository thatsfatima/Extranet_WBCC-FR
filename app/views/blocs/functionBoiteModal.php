<script>
    let typeDestinataire = ""
    let typeContact = ""

    // Fonction pour la modale selon la category de société
    function changerContenuModal(type) {
        $('#modalSociete').modal('show');
        const contenu = document.getElementById('contenuModal');
        document.getElementById('modalSocieteLabel').innerText = "Liste des Sociétés " + type;
        let listeHTML = ``;
        $.ajax({
            type: "POST",
            url: `<?= URLROOT ?>/public/json/company.php?action=listeByCategory&source=modalCompany`,
            data: {
                category: type
            },
            dataType: "JSON",
            success: function(data) {
                listeHTML = `  <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable7" width="100%" cellspacing="0">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Nom Compagnie</th>
                                <th>Email</th>
                                <th>Telephone</th>
                                <th>Adresse</th>
                            </tr>
                        </thead>
                        <tbody>`;
                data.forEach((societe, i) => {
                    let soc = JSON.stringify(societe);
                    listeHTML += ` <tr>
                                        <td>
                                            <input societe='${soc}' type="radio" class="checkSociete"
                                                name="checkSociete"
                                                value="${societe.idCompany}">
                                        </td>
                                        <td>${i+1}</td>
                                        <td>${societe.name}</td>
                                        <td>${societe.email}</td>
                                        <td>${societe.businessPhone}</td>
                                        <td>${societe.businessLine1}</td>
                                    </tr>`;
                });
                listeHTML += ` </tbody>
                    </table>
                </div>`;
                contenu.innerHTML = listeHTML;
            },
            error: function(jqXHR, error, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });


    }

    function validerSociete() {
        let societe = JSON.parse($('.checkSociete:checked').attr('societe'));
        console.log(societe.email);
        $('#emailArchitecte').val(societe.email);
        $('#modalSociete').modal('hide');
        console.log({
            company: societe,
            op: <?= json_encode($op) ?>
        });
        if (societe.idCompany != undefined) {
            $.ajax({
                url: `<?= URLROOT ?>/public/json/company.php?action=linkCompanyArchi`,
                type: 'POST',
                data: {
                    company: societe,
                    op: <?= json_encode($op) ?>
                },
                dataType: "JSON",
                success: function(response) {
                    console.log("response");
                    // console.log(response);
                    location.reload();
                },
                error: function(response) {
                    $("#msgError").text("Error !!!");
                    $('#errorOperation').modal('show');
                    console.log(response);
                },
                complete: function() {

                },
            });
        } else {
            $("#msgError").text("Veuillez choisir une société !");
            $('#errorOperation').modal('show');
        }
    }


    function showModalInterlocuteur(idCompany, type) {
        $("#modalListInterlocuteur").modal("show");
        $('#btnShowModalAddInter').attr('onclick', `showModalAddInterlocuteur(${idCompany},'${type}')`);
        $('#btnAddInter').attr('onclick', `saveInterlocuteur(${idCompany}),'${type}'`);
        $('#btnSaveInterCoche').attr('onclick', `saveInterlocuteur(${idCompany},'${type}')`);


        const contenu = document.getElementById('contenuListInterlocuteur');
        let listeHTML = ``;
        $.ajax({
            type: "GET",
            url: `<?= URLROOT ?>/public/json/contact.php?action=listeByidCompany&idCompany=${idCompany}`,
            dataType: "JSON",
            success: function(data) {
                listeHTML = ``;
                data.forEach((contact, i) => {
                    let cnt = JSON.stringify(contact);
                    listeHTML += `  <tr>
                                        <td>
                                            <input type="radio" class="checkInterlocuteur" name="checkInterlocuteur"
                                                value='${cnt}'>
                                        </td>
                                        <td>${i+1}</td>
                                        <td>${contact.nomContact }</td>
                                        <td>${contact.prenomContact }</td>
                                        <td>${contact.emailContact }</td>
                                        <td>${contact.telContact }</td>
                                        </tr>`;
                });

                contenu.innerHTML = listeHTML;
            },
            error: function(jqXHR, error, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
        // if (action == "edit") {
        //     $("#action1").val("edit");
        // } else {
        //     $("#action1").val("add");
        // }
    }

    function saveInterlocuteur(idCompany, type1) {
        console.log("uid ");
        console.log(type1);
        let inter = {};
        if ($(".checkInterlocuteur:checked").val() == undefined) {
            inter = {
                civilite: $('#civiliteInterlocuteur').val(),
                prenom: $('#prenomInterlocuteur').val(),
                nom: $('#nomInterlocuteur').val(),
                tel1: $('#tel1Interlocuteur').val(),
                tel2: $('#tel2Interlocuteur').val(),
                tel3: $('#tel3Interlocuteur').val(),
                email: $('#emailInterlocuteur').val(),
                statut: $('#statutInterlocuteur').val(),
                adresse1: $('#adresseInterlocuteur').val(),
                codePostal: $('#codePostalInterlocuteur').val(),
                ville: $('#villeInterlocuteur').val(),
                departement: $('#departementInterlocuteur').val(),
                region: $('#regionInterlocuteur').val(),
                porte: $('#porteInterlocuteur').val(),
                batiment: $('#batimentInterlocuteur').val(),
                etage: $('#etageInterlocuteur').val(),
                idOP: $('#idOP').val(),
                categoryCompany: type1
            };
        } else {
            let ct = JSON.parse($(".checkInterlocuteur:checked").val());
            inter = {
                civilite: ct.civiliteContact,
                prenom: ct.prenomContact,
                nom: ct.nomContact,
                tel1: ct.telContact,
                tel2: ct.telContact,
                tel3: ct.telContact,
                email: ct.emailContact,
                statut: ct.statutContact,
                adresse1: ct.adresseContact,
                codePostal: ct.codePostalContact,
                ville: ct.villeContact,
                departement: ct.departement,
                region: ct.businessState,
                porte: ct.codePorte,
                batiment: ct.batiment,
                etage: ct.etage,
                idOP: $('#idOP').val(),
                categoryCompany: type1
            };
        }

        $.ajax({
            type: "POST",
            url: `<?= URLROOT ?>/public/json/contact.php?action=saveInterlocuteur&idCompany=${idCompany}`,
            data: inter,
            success: function(data) {
                console.log("data");
                console.log(data);
                $("#modalAddInterlocuteur").modal("hide");
                $("#modalListInterlocuteur").modal("hide");
                // location.reload();
            },
            error: function(jqXHR, error, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }


    function showModalAddInterlocuteur(idCompany, type) {
        $("#modalAddInterlocuteur").modal("show");
    }


    function showModalContact(action, type = '') {
        typeDestinataire = type;
        if (action == "ajoutDestinataire") {
            $('#selectContactDestinataire').modal('show');
        }
        if (action == "ajoutSinistre") {
            $('#selectContactSinistre').modal('show');
            $('#buttonConfirmContact').attr("onclick", "addOrupdateContact('new')")
        }
        if (action == "changeSinistre") {
            $('#selectContactSinistre').modal('show');
            $('#buttonConfirmContact').attr("onclick", "addOrupdateContact('change')")
        }


        if (action == "ajoutResponsable") {
            typeContact = "responsable"
            $('#selectContactSinistre').modal('show');
            $('#buttonConfirmContact').attr("onclick", "addOrupdateContact('new', 'responsable')")
        }
        if (action == "changeResponsable") {
            typeContact = "responsable"
            $('#selectContactSinistre').modal('show');
            $('#buttonConfirmContact').attr("onclick", "addOrupdateContact('change', 'responsable')")
        }

    }

    function ajoutDestinataire() {
        var inputCheck = $(".checkContact:checkbox:checked");
        var newvalue = "";
        if (typeDestinataire == 'LA') {
            newvalue = $("#emailDestinaireLA").val();
        } else {
            newvalue = $("#emailDestinaire").val();
        }

        Array.prototype.forEach.call(inputCheck, function(el) {
            if (el.value != "" && !(newvalue.includes(el.value))) {
                newvalue += el.value + ";"
            }

        });
        if (typeDestinataire == 'LA') {
            $("#emailDestinaireLA").attr("value", newvalue);
        } else {
            $("#emailDestinaire").attr("value", newvalue);
        }

        $('.checkContact').prop('checked', false);
        $('#selectContactDestinataire').modal('hide');
    }

    function removeDestinataire(params = '') {
        var adresse = "";
        if (params == "LA") {
            adresse = $("#emailDestinaireLA").val().split(";");
        } else {
            adresse = $("#emailDestinaire").val().split(";");
        }

        let adr = "";
        if (adresse.length - 1 > 0) {
            for (let index = 0; index < adresse.length - 2; index++) {
                adr = adr + adresse[index] + ";";
            }
        }
        if (params == "LA") {
            $('#emailDestinaireLA').attr("value", adr);
        } else {
            $('#emailDestinaire').attr("value", adr);
        }

    }

    function saveInfosCie() {
        $.ajax({
            url: `<?= URLROOT ?>/public/json/company.php?action=saveInfosTelMailCie`,
            type: 'POST',
            data: {
                id: $('#oldIdCie').val(),
                tel: $('#telCie').val(),
                email: $('#emailCie').val()
            },
            dataType: "JSON",
            success: function(response) {
                console.log(response);
                location.reload();
            },
            error: function(response) {
                $("#msgError").text("Error !!!");
                $('#errorOperation').modal('show');
                console.log(response);
            },
            complete: function() {

            },
        });
    }

    function AddOrEditCie() {
        $.ajax({
            url: `<?= URLROOT ?>/public/json/opportunity.php?action=AddOrEditCie`,
            type: 'POST',
            data: {
                idOp: $('#idOP').val(),
                numeroOP: $('#numeroOP').val(),
                oldIdCie: $('#oldIdCie').val(),
                action: $('#action').val(),
                checkCie: $('.oneselectionCie:checkbox:checked').val(),
                type: $('#typeDelegationChecked').val(),
                police: $('#numPolice').val(),
            },
            dataType: "JSON",
            success: function(response) {
                console.log("success");

                // console.log(response);
                $('#idCie').attr("value", response.idCompany);
                $('#numeroCie').attr("value", response.numeroCompany);
                $('#nomCie').attr("value", response.name);
                $('#adresseCie').attr("value", response.businessLine1);
                $('#villeCie').attr("value", response.businessCity);
                $('#codePostalCie').attr("value", response.businessPostalCode);
                $('#telCie').attr("value", response.businessPhone);
                $('#emailCie').attr("value", response.email);
                $("#selectCompany").modal("hide");
                $("#btnAddCie").attr("onclick", "showModalCie('edit')");
                $("#iconeAddCie").attr("class", "fas fa-edit");
                // divInfosCie
                $('#divInfosCie').removeAttr("hidden");
                $('#divInfosPasCie').attr("hidden", "hidden");
                $('#selectCompany').modal('hide');

            },
            error: function(response) {
                console.log(response);
                $('#selectCompany').modal('hide');
                $("#msgError").text(
                    "Erreur de choisir une compagnie"
                );
                $('#errorOperation').modal('show');
            },
            complete: function() {

            },
        });
    }

    function showModalCie(action) {
        if (action == "edit") {
            $("#action").attr("value", "edit");
        } else {
            $("#action").attr("value", "add");
        }
        $("#selectCompany").modal("show");
    }

    function addOrEditCabinetExpert() {
        let id = $('.oneselectionCabinetExpert:checked').val();
        console.log(id);

        if (id != "" && id != undefined) {
            $.ajax({
                url: `<?= URLROOT ?>/public/json/opportunity.php?action=addOrEditCabinetExpert`,
                type: 'POST',
                data: {
                    idOp: $('#idOP').val(),
                    numeroOP: $('#numeroOP').val(),
                    oldIdCie: $('#oldIdCabinetExpert').val(),
                    action: $('#actionCabinetExpert').val(),
                    checkCie: $('.oneselectionCabinetExpert:checked').val()
                },
                dataType: "JSON",
                success: function(response) {
                    console.log(response);
                    $("#modalExperts").modal("hide");
                    if (response != undefined && response != null && response != false) {

                        $(`#idExpert`).val(response.idCompany);
                        $(`#nomExpert`).val(response.name);
                        $(`#telExpert`).val(response.businessPhone);
                        $(`#emailExpert`).val(response.email);
                        $(`#adresseExpert`).val(response.businessLine1);
                        $(`#cpExpert`).val(response.businessPostalCode);
                        $(`#villeExpert`).val(response.businessCity);

                        $(`#nomExpert`).removeAttr("readonly");
                        $(`#telExpert`).removeAttr("readonly");
                        $(`#emailExpert`).removeAttr("readonly");
                        $(`#adresseExpert`).removeAttr("readonly");
                        $(`#cpExpert`).removeAttr("readonly");
                        $(`#villeExpert`).removeAttr("readonly");

                        $("#questionsExpertCompany").removeAttr("hidden");
                    }
                },
                error: function(response) {
                    console.log(response);
                    $("#msgError").text("Erreur lors du choix !");
                    $('#errorOperation').modal('show');
                },
                complete: function() {},
            });
        } else {
            $("#msgError").text("Veuillez choisir un cabinet d'expertise !");
            $('#errorOperation').modal('show');
        }
    }

    function onClickDocument(idDoc, idOp, nom) {
        $('#listIntervenantModal').modal('hide');
        document.getElementById("textBloquageDoc").innerHTML = `Voulez-vous supprimer ce document <b>'${nom}'</b> de cette opportunité ?</br>
                        Cette action est irreversible !`
        document.getElementById("idOpDoc").value = idOp;
        document.getElementById("idDoc").value = idDoc;
        document.getElementById("nomFichier").value = nom;
    }

    function showModalQuestionPublication() {
        $("#viewNote").modal("hide");
        $("#questionPublicationNote").modal("show");
    }

    function ShowModalNote(idNote, action) {
        console.log(idNote);
        console.log(action);
        $("#idNote").attr("value", idNote);
        $("#actionNote").attr("value", action);
        if (idNote == "0") {
            tinymce.init({
                readOnly: false,
                content_readonly: false
            });
            $("#auteurNote").val(
                '<?= $_SESSION['connectedUser']->prenomContact . ' ' . $_SESSION['connectedUser']->nomContact ?>');
            $("#dateNote").val('<?= date('d-m-Y H:i') ?>');
            tinyMCE.get("noteText").setContent("");
            tinymce.activeEditor.getBody().setAttribute('contenteditable', true);
            $("#btnSaveNote").removeAttr('hidden');
            $("#viewNote").modal("show");
        } else {
            tinymce.init({
                readOnly: true,
                content_readonly: true
            });
            $.ajax({
                url: `<?= URLROOT ?>/public/json/note.php?action=find&id=` + idNote,
                type: 'POST',
                dataType: "JSON",
                success: function(response) {
                    console.log(response)
                    $("#auteurNote").val(response.auteur);
                    $("#dateNote").val(response.dateNote);
                    tinyMCE.get("noteText").setContent(response.noteText);

                    //NOTE NON MODIFIABLE APRES 24h
                    const date1 = new Date(response.createDate);
                    const date2 = new Date();
                    const diffTime = Math.abs(date2 - date1);
                    // const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                    if (response.idUtilisateurF != $('#idUtilisateur').val() || response.isAutomatique == 1 ||
                        diffTime > 172800000) {
                        tinymce.activeEditor.getBody().setAttribute('contenteditable', false);
                        //HIDE BTN ENREG
                        $("#btnSaveNote").attr('hidden', "hidden");
                    } else {
                        tinymce.init({
                            readOnly: false,
                            content_readonly: false
                        });
                        tinymce.activeEditor.getBody().setAttribute('contenteditable', true);
                        $("#btnSaveNote").removeAttr('hidden');
                    }
                    $("#viewNote").modal("show");
                },
                error: function(response) {
                    console.log(response);
                    $("#msgError").text("Impossible d'afficher une note !!!");
                    $('#errorOperation').modal('show');
                },
                complete: function() {},
            });
        }
    }

    function saveNote(type) {
        let auteur = "";
        let noteText = "";
        let plainText = "";
        let date = "";
        let action = $('#actionNote').val();
        let idNote = $('#idNote').val();
        if (type == "incident") {
            action = "add";
            idNote = "0";
            auteur = $('#auteurIncident').val();
            noteText = $('#incidentText').val();
            plainText = $('#incidentText').val();
        } else {
            auteur = action == 'add' ? $('#auteur').val() : $("#auteurNote").val();
            noteText = tinyMCE.get("noteText").getContent();
            plainText = tinyMCE.get("noteText").getContent({
                format: 'text'
            })
        }

        $.ajax({
            url: `<?= URLROOT ?>/public/json/note.php?action=saveNote&typeEnreg=interne`,
            type: 'POST',
            data: {
                idNote: idNote,
                idContact: $('#idContact').val(),
                numeroContact: $('#numeroContact').val(),
                idOpportunity: $('#idOP').val(),
                numeroOpportunity: $('#numeroOP').val(),
                auteur: auteur,
                noteText: noteText,
                plainText: plainText,
                idGestionnaire: "0",
                nomUser: auteur,
                idUser: $("#idUtilisateur").val(),
                typeEnreg: "interne",
                nomSyndic: ""
            },
            dataType: "JSON",
            success: function(response) {
                console.log(response);
                $("#viewNote").modal("hide");

                if (response != undefined && response != null && response != "0") {
                    $("#msgSuccess").text("Note enregistrée avec succés !!!");
                    $('#successOperation').modal('show');
                    if (action == "add" && type != "incident") {
                        setTimeout(() => {
                            $('#successOperation').modal('hide');
                        }, 1000);
                        onClickPublierNote("1", response);
                    } else {
                        location.reload();
                    }
                } else {
                    $("#msgError").text("Impossible d'ajouter une note !!!");
                    $('#errorOperation').modal('show');
                }

            },
            error: function(response) {
                console.log(response);
                $("#viewNote").modal("hide");
                $("#msgError").text("Impossible d'ajouter une note !!!");
                $('#errorOperation').modal('show');
            },
            complete: function() {

            },
        });
    }

    function onClickPublierNote(publie, idNote) {
        //CONFIRM PUBLICATION NOTE 
        document.getElementById("publieNote").setAttribute("value", publie);
        document.getElementById("idNote").setAttribute("value", idNote);
        let txt = "";
        if (publie == "0") {
            txt = "Voulez-vous dépublier cette note ?";
        } else {
            txt = "Voulez-vous publier cette note ?";
        }
        $("#msgPublicationNote").text(txt);
        $("#questionPublicationNote").modal("show");
    }

    function confirmPublierNote() {
        let publie = $('#publieNote').val();
        let idNote = $('#idNote').val();
        console.log(publie);
        console.log(idNote);
        $.ajax({
            url: `<?= URLROOT ?>/public/json/note.php?action=publieNote`,
            type: 'POST',
            dataType: "JSON",
            data: {
                idNote: idNote,
                publie: publie
            },
            success: function(response) {
                console.log(response)
                $("#questionPublicationNote").modal("hide");
                $("#msgSuccess").text(
                    `${publie == 0 ? "Vous venez de dépublier cette note !" : "Vous venez de publier cette note !" }`
                );
                $('#successOperation').modal('show');
                location.reload();
            },
            error: function(response) {
                $("#questionPublicationNote").modal("hide");
                console.log(response);
                $("#msgError").text(
                    `${publie == 0 ? "Impossible de dépublier cette note !" : "Impossible de publier cette note !" }`
                );
                $('#errorOperation').modal('show');
            },
            complete: function() {

            },
        });
    }

    function annulerPublierNote() {
        $("#questionPublicationNote").modal("hide");
        location.reload();
    }

    function onModalIncident() {
        $("#modalIncident").modal("show");
    }

    function declarerIncident() {
        $.ajax({
            url: `<?= URLROOT ?>/public/json/opportunity.php?action=declarerIncident`,
            type: 'POST',
            data: {
                idOp: $('#idOP').val(),
                auteurIncident: $('#auteurIncident').val(),
                incidentText: $('#incidentText').val(),
                dateIncident: $('#dateIncident').val(),
                idAuteurIncident: $('#idUtilisateur').val(),
                opName: $('#nameOP').val(),
            },
            dataType: "JSON",
            beforeSend: function() {
                $("#modalIncident").modal("hide");
                $("#msgLoading").text("Signalement Incident en cours...");
                $('#loadingModal').modal('show');
            },
            success: function(response) {
                console.log(response);
                $('#loadingModal').modal('hide');
                $("#msgSuccess").text("Incident déclaré !");
                $('#successOperation').modal('show');
                saveNote('incident');

                // $('#loadingModal').modal('hide');
                // $("#msgSuccess").text("Incident déclaré !");
                // $('#successOperation').modal('show');
                // location.reload();
            },
            error: function(response) {
                $("#msgError").text("Error !!!");
                $('#errorOperation').modal('show');
                console.log(response);
            },
            complete: function() {

            },
        });
    }

    function AddOrEditInterlocuteur() {

        $.ajax({
            url: `<?= URLROOT ?>/public/json/opportunity.php?action=AddOrEditInterlocuteur`,
            type: 'POST',
            data: {
                idOp: $('#idOP').val(),
                idContact: $('.oneselectionInterlocuteur:checkbox:checked').val(),
                oldInter: $('#oldInter').val(),
                action: $('#action1').val(),
            },

            dataType: "JSON",
            success: function(response) {
                console.log("success");
                console.log(response);
                $("#selectCompany").modal("hide");
                location.reload();
            },
            error: function(response) {
                console.log(response);
                $("#msgError").text(
                    "Erreur de choisir un interlocuteur"
                );
                $('#errorOperation').modal('show');
            },
            complete: function() {

            },
        });
    }

    function addOrupdateContact(action, type = '') {

        let nomContact = "";
        let civiliteContact = "";
        let prenomContact = "";
        let idContact = "";
        let contactnew = action == "update" ? "" : $('.checkSinitre:checkbox:checked').val();
        let telContact = "";
        let emailContact = "";
        let numeroContactnew = "";
        let dateNaissanceContact = "";
        let statutContact = "";
        let typeResponsable = "";
        console.log(contactnew);
        if (type == "sinistre" || type == "" || type == "reload") {
            nomContact = $('#nomContact').val()
            civiliteContact = $('#civiliteContact').val()
            prenomContact = $('#prenomContact').val()
            idContact = $('#idContact').val()
            telContact = $('#telContact').val()
            emailContact = $('#emailContact').val()
            numeroContactnew = $('#numeroContact').val()
            dateNaissanceContact = $('#dateNaissanceContact').val()
            statutContact = $("#statutContact").val()
        } else {
            if (type == "responsable") {
                nomContact = $('#nomResponsable').val()
                civiliteContact = $('#civiliteResponsable').val()
                prenomContact = $('#prenomResponsable').val()
                idContact = $('#idResponsable').val()
                telContact = $('#telResponsable').val()
                emailContact = $('#emailResponsable').val()
                numeroContactnew = $('#numeroResponsable').val()
                dateNaissanceContact = $('#dateNaissanceResponsable').val()
                statutContact = ($('#statutResponsable').val() != "" ? $('#statutResponsable').val() : ($(
                    '#typeResponsable').val() == "pc" ? "PERSONNEL" : "LOCATAIRE"))
                typeResponsable = $('#typeResponsable').val()
            }
        }

        $.ajax({
            url: `<?= URLROOT ?>/public/json/opportunity.php?action=AddOrUpdateContact`,
            type: 'POST',
            data: {
                idOp: $('#idOP').val(),
                numeroOP: $('#numeroOP').val(),
                nomContact: nomContact,
                civiliteContact: civiliteContact,
                prenomContact: prenomContact,
                idContact: idContact,
                contactnew: contactnew,
                telContact: telContact,
                emailContact: emailContact,
                numeroContactnew: numeroContactnew,
                dateNaissanceContact: dateNaissanceContact,
                statutContact: statutContact,
                typeDO: $('#typeDO').val(),
                action: action,
                typeOP: $('#typeSinistre').val(),
                idCompany: $('#idDO').val(),
                typeContact: type,
                typeResponsable: typeResponsable
            },
            dataType: "JSON",
            success: function(response) {
                console.log('response res');
                console.log(response);
                setTimeout(() => {
                    $('#selectContactSinistre').modal('hide');
                }, 1000)
                if (type == "responsable") {
                    if (typeResponsable == "voisin" || typeResponsable == "pc") {
                        //CHARGER RESPONSABLE
                        if (response != null && response != true && response != undefined) {
                            $('#nomRes').attr("value", response.fullName);
                            $('#statutRes').attr("value", response.statutContact);
                            $('#idResponsable').attr("value", response.idContact);
                            $('#prenomResponsable').attr("value", response.prenomContact);
                            $('#nomResponsable').attr("value", response.nomContact);
                            $('#emailResponsable').attr("value", response.emailContact);
                            $('#telResponsable').attr("value", response.telContact);
                            $('#statutResponsable').attr("value", response.statutContact);
                            $('#civiliteResponsable').attr("value", response.civiliteContact);
                            $('#dateNaissanceResponsable').attr("value", response.dateNaissance);
                        }
                    }
                } else {
                    if (type == "reload") {
                        location.reload();
                    }
                }
            },
            error: function(response) {

                $("#msgError").text("Error !!!");
                $('#errorOperation').modal('show');
                console.log(response);
            },
            complete: function() {

            },
        });
    }

    function AddOrEditImmeuble(action) {
        $.ajax({
            url: `<?= URLROOT ?>/public/json/opportunity.php?action=UpadateImmLotOp`,
            type: 'POST',
            data: {
                idOp: $('#idOP').val(),
                numeroOP: $('#numeroOP').val(),
                immeublenew: $(".oneselectionImmeuble:checkbox:checked").val(),
                etage: $('#etage').val(),
                porte: $('#porte').val(),
                batiment: $('#batiment').val(),
                lot: $('#lot').val(),
                idImmeuble: $('#idImmeuble').val(),
                numeroImmeuble: $('#numeroImmeuble').val(),
                idApp: $('#idApp').val(),
                adresse: $('#adresseImm').val(),
                codePostal: $('#cP').val(),
                ville: $('#ville').val(),
                libellePartieCommune: $('#libellePartieCommune').val(),
                cote: $('#cote').val(),
                typeLot: $('#typeLot').val(),
                action: action
            },
            dataType: "JSON",
            success: function(response) {
                // console.log("response");
                // console.log(response);
                $('#selectImmeuble').modal('hide');
                if (response != undefined && response != null && response != {}) {
                    $('#adresseImm').attr("value", response.adresse);
                    $('#cP').attr("value", response.codePostal);
                    $('#ville').attr("value", response.ville);
                    $('#adresseImm2').attr("value", response.adresse);
                    $('#cP2').attr("value", response.codePostal);
                    $('#ville2').attr("value", response.ville);
                }

                // location.reload();
            },
            error: function(response) {
                $("#msgError").text("Error !!!");
                $('#errorOperation').modal('show');

                console.log(response);
            },
            complete: function() {

            },
        });
    }

    function notificationNouvelleNote() {
        var inputCheck = $(".checkContactNote:checkbox:checked");
        var emails = "";

        Array.prototype.forEach.call(inputCheck, function(el) {
            // Do stuff here
            if (el.value != "" && !(emails.includes(el.value))) {
                emails += el.value + ";"
            }

        });
        // console.log(emails);
        $.ajax({
            url: `<?= URLROOT ?>/public/json/note.php?action=notificationNouvelleNote`,
            type: 'POST',
            data: {
                auteur: $('#auteurNote').val(),
                opName: $('#nameOP').val(),
                contacts: emails,
                details: tinyMCE.get("noteText").getContent({
                    format: 'text'
                })
            },
            dataType: "JSON",
            beforeSend: function() {
                $("#selectContactNote").modal("hide");
                $('#loadingModal').modal('show');
            },
            success: function(response) {
                console.log(response);
                $('#loadingModal').modal('hide');
                $("#msgSuccess").text("Notification envoyée avec succès !");
                $('#successOperation').modal('show');
                setTimeout(function() {
                    $('#successOperation').modal('hide');
                    location.reload();
                }, 1000);
            },
            error: function(response) {
                $('#loadingModal').modal('hide');
                console.log(response);
                $("#msgError").text("Impossible de Notifier !");
                $('#errorOperation').modal('show');
            },
            complete: function() {

            },
        });
    }

    function showModalAddContact(type = '') {
        type = type == '' ? typeContactCie : type;
        if (type == "interlocuteur") {
            $("#engCnt").attr("onclick", `saveContactBD('${$('#oldIdCie').val()}')`);
        } else {
            $("#engCnt").attr("onclick", `saveContactBD('0')`);
        }
        $("#modalAddOrEditContact").modal("show");
    }

    function saveContactBD(idCompany) {
        $.ajax({
            type: "POST",
            url: `<?= URLROOT ?>/public/json/contact.php?action=saveContact&idCompany=${idCompany}`,
            data: {
                civilite: $('#civilite').val(),
                prenom: $('#prenom').val(),
                nom: $('#nom').val(),
                tel1: $('#tel1').val(),
                tel2: $('#tel2').val(),
                tel3: $('#tel3').val(),
                tel3: $('#tel3').val(),
                email: $('#email').val(),
                statut: $('#statut').val(),
                adresse1: $('#adresse1C').val(),
                codePostal: $('#codePostalC').val(),
                ville: $('#villeC').val(),
                departement: $('#departementC').val(),
                region: $('#regionC').val(),
                porte: $('#porteC').val(),
                batiment: $('#batimentC').val(),
                etage: $('#etageC').val(),
            },
            success: function(data) {
                $("#modalAddOrEditContact").modal("hide");
                location.reload();
            },
            error: function(jqXHR, error, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }

    function showModalContactNote() {
        $("#questionPublicationNote").modal("hide");
        $("#selectContactNote").modal("show");
    }

    function showModalImmeuble() {
        $("#selectImmeuble").modal("show");
    }

    function onClickCloturerOP() {
        $("#clotureOPModal").modal("show");
    }

    function onConfirmCloturerOP() {
        let statut = $('#typeClotureOP').val();
        let idOP = $('#idOP').val();
        let idUser = $('#idUtilisateur').val();
        $.ajax({
            url: `<?= URLROOT ?>/public/json/opportunity.php?action=closeOP&id=` + idOP + '&statut=' + statut +
                '&idUser=' + idUser,
            type: 'POST',
            dataType: "JSON",
            data: {
                commentaire: $('#commentaireClotureOP').val()
            },
            beforeSend: function() {
                $("#clotureOPModal").modal("hide");
                $("#msgLoading").text("Clôture dossier en cours ...");
                $("#loadingModal").modal("show");
            },
            success: function(response) {
                console.log(response);
                $("#loadingModal").modal("hide");
                if (response != undefined && response != "" && response == "1") {
                    $("#msgSuccess").text("Dossier clôturé avec succés !");
                    $('#successOperation').modal('show');
                    history.back();

                    // document.location.href = "<?= URLROOT . '/Gestionnaire/te/' ?>" + idOP;
                } else {
                    $("#msgError").text(
                        "Dossier non clôturé, Veuillez réessayer ou contacter l'administrateur !");
                    $('#errorOperation').modal('show');
                }
            },
            error: function(response) {
                console.log(response);
                $("#loadingModal").modal("hide");
                $("#msgError").text("Veuillez réessayer ou contacter l'administrateur !");
                $('#errorOperation').modal('show');
            },
            complete: function() {
                setTimeout(() => {
                    $("#loadingModal").modal("hide");
                }, 500);
            },
        });

    }

    function onClickReouvertureOP() {
        $("#reouvertureOPModal").modal("show");
    }

    function onConfirmReouvertureOP() {
        let statut = 'Open';
        let idOP = $('#idOP').val();
        let idUser = $('#idUtilisateur').val();
        $.ajax({
            url: `<?= URLROOT ?>/public/json/opportunity.php?action=OpenOP&id=` + idOP + '&statut=' + statut +
                '&idUser=' + idUser,
            type: 'GET',
            dataType: "JSON",
            beforeSend: function() {
                $("#reouvertureOPModal").modal("hide");
                $("#msgLoading").text("Réouverture dossier en cours ...");
                $("#loadingModal").modal("show");
            },
            success: function(response) {
                // console.log(response);
                $("#loadingModal").modal("hide");
                if (response != undefined && response != "" && response == "1") {
                    $("#msgSuccess").text("Dossier réouvert avec succés !");
                    $('#successOperation').modal('show');
                    location.reload();
                } else {
                    $("#msgError").text(
                        "Dossier non réouvert, Veuillez réessayer ou contacter l'administrateur !");
                    $('#errorOperation').modal('show');
                }
            },
            error: function(response) {
                console.log(response);
                $("#loadingModal").modal("hide");
                $("#msgError").text("Veuillez réessayer ou contacter l'administrateur !");
                $('#errorOperation').modal('show');
            },
            complete: function() {
                setTimeout(() => {
                    $("#loadingModal").modal("hide");
                }, 500);
            },
        });

    }

    function changeValueAdr() {
        $('#etage').attr("value", $('#etage2').val());
        $('#porte').attr("value", $('#porte2').val());
        $('#lot').attr("value", $('#lot2').val());
        $('#batiment').attr("value", $('#batiment2').val());
        $('#libellePartieCommune').attr("value", $('#libellePartieCommune2').val());
        $('#cote').attr("value", $('#cote2').val());
    }

    function onClickReprogrammer(typeActivity, codeActivity) {
        var date = new Date();
        date.setDate(date.getDate() + 1);
        let year = new Intl.DateTimeFormat('en', {
            year: 'numeric'
        }).format(date);
        let month = new Intl.DateTimeFormat('en', {
            month: '2-digit'
        }).format(date);
        let day = new Intl.DateTimeFormat('en', {
            day: '2-digit'
        }).format(date);
        let hour = new Intl.DateTimeFormat('fr', {
            hour: '2-digit'
        }).format(date);
        let min = new Intl.DateTimeFormat('en', {
            minute: '2-digit'
        }).format(date);
        hour = hour.split(' ')[0];
        $('#dateNewActivity').attr("value", `${year}-${month}-${day}`);
        $('#heureNewActivity').attr("value", `${hour}:${min}`);
        $('#typeActivity').attr("value", typeActivity);
        $('#codeActivity').attr("value", codeActivity);
        $("#textReprogrammer").text("Voulez-vous reprogrammer l'activité '" + typeActivity + "' ?");
        $("#modalConfirmReprogrammerActivity").modal("show");
    }

    function onConfirmProgrammerActivity() {
        let type = $('#typeActivity').val();
        let code = $('#codeActivity').val();
        let date = $('#dateNewActivity').val();
        let heure = $('#heureNewActivity').val();
        $("#modalConfirmReprogrammerActivity").modal("hide");
        //REPROGRAMMER ACTIVITY
        $.ajax({
            url: `<?= URLROOT ?>/public/json/activity.php?action=reprogrammerActivitybyOPAndRegarding`,
            type: 'POST',
            data: {
                idOp: $('#idOP').val(),
                opName: $('#nameOP').val(),
                date: date,
                heure: heure,
                regarding: type,
                codeActivity: code,
                idAuteur: $('#idUtilisateur').val(),
                numeroAuteur: $('#numeroAuteur').val(),
                auteur: $('#auteur').val(),
                commentaire: $('#commentaireNewActivity').val()
            },
            dataType: "JSON",
            beforeSend: function() {
                $("#msgLoading").text("Reprogrammation Activité '" + type + "' en cours...");
                $("#loadingModal").modal("show");
            },
            success: function(response) {
                $("#loadingModal").modal("hide");
                console.log("success");
                console.log(response);
                $("#msgSuccess").text("Activité '" + type + "' reprogramée avec succés !");
                $('#successOperation').modal('show');
                location.reload();
                return false;
            },
            error: function(response) {
                console.log(response);
                $("#msgError").text(
                    "Impossible de reprogrammer l'activité! Réessayer ou contacter l'administrateur !");
                $('#errorOperation').modal('show');
            },
            complete: function() {},
        });

    }

    function showModalOthersOp() {
        actionOther = "enCours";
        $("#modalOthersOpContact").modal("show");
    }

    function onChangeSiret() {
        let siret = document.getElementById("siretSociete").value;
        $.ajax({
            type: "GET",
            url: `<?= URLROOT ?>/public/json/company.php?action=findBySiret&numero=${siret}`,
            dataType: "JSON",
            success: function(data) {
                if (data != "null") {
                    document.getElementById("idSociete").value = data['idCompany'];
                    document.getElementById("nomSociete").value = data['name'];
                    document.getElementById("enseigneSociete").value = data['enseigne'];
                    document.getElementById("telephoneSociete").value = data['businessPhone'];
                    document.getElementById("emailSociete").value = data['email'];
                    document.getElementById("siteWebSociete").value = data['webaddress'];
                    document.getElementById("categorieSociete").value = data['category'];
                    document.getElementById("regionSociete").value = data['region'];
                    document.getElementById("adresseSociete").value = data['businessLine1'];
                    document.getElementById("codePostalSociete").value = data['businessPostalCode'];
                    document.getElementById("villeSociete").value = data['businessCity'];
                    document.getElementById("departementSociete").value = data['businessState'];
                    document.getElementById("rcsSociete").value = data['numeroRCS'];
                    document.getElementById("villeRcsSociete").value = data['villeRCS'];
                    document.getElementById("siretSociete").value = data['numeroSiret'];
                    document.getElementById("codeNafSociete").value = data['siccode'];
                    document.getElementById("effectifSociete").value = data['numEmployees'];
                    document.getElementById("activiteSociete").value = data['industry'];

                    document.getElementById("idSociete").setAttribute("readonly", "");
                    document.getElementById("nomSociete").setAttribute("readonly", "");
                    document.getElementById("enseigneSociete").setAttribute("readonly", "");
                    document.getElementById("telephoneSociete").setAttribute("readonly", "");
                    document.getElementById("emailSociete").setAttribute("readonly", "");
                    document.getElementById("siteWebSociete").setAttribute("readonly", "");
                    document.getElementById("categorieSociete").setAttribute("readonly", "");
                    document.getElementById("regionSociete").setAttribute("readonly", "");
                    document.getElementById("adresseSociete").setAttribute("readonly", "");
                    document.getElementById("codePostalSociete").setAttribute("readonly", "");
                    document.getElementById("villeSociete").setAttribute("readonly", "");
                    document.getElementById("departementSociete").setAttribute("readonly", "");
                    document.getElementById("rcsSociete").setAttribute("readonly", "");
                    document.getElementById("villeRcsSociete").setAttribute("readonly", "");
                    document.getElementById("codeNafSociete").setAttribute("readonly", "");
                    document.getElementById("effectifSociete").setAttribute("readonly", "");
                    document.getElementById("activiteSociete").setAttribute("readonly", "");

                } else {
                    document.getElementById("idSociete").value = "";
                    document.getElementById("nomSociete").value = "";
                    document.getElementById("enseigneSociete").value = "";
                    document.getElementById("telephoneSociete").value = "";
                    document.getElementById("emailSociete").value = "";
                    document.getElementById("siteWebSociete").value = "";
                    document.getElementById("categorieSociete").value = "";
                    document.getElementById("regionSociete").value = "";
                    document.getElementById("adresseSociete").value = "";
                    document.getElementById("codePostalSociete").value = "";
                    document.getElementById("villeSociete").value = "";
                    document.getElementById("departementSociete").value = "";
                    document.getElementById("rcsSociete").value = "";
                    document.getElementById("villeRcsSociete").value = "";
                    document.getElementById("codeNafSociete").value = "";
                    document.getElementById("effectifSociete").value = "";
                    document.getElementById("activiteSociete").value = "";

                    document.getElementById("idSociete").removeAttribute("readonly");
                    document.getElementById("nomSociete").removeAttribute("readonly");
                    document.getElementById("enseigneSociete").removeAttribute("readonly");
                    document.getElementById("telephoneSociete").removeAttribute("readonly");
                    document.getElementById("emailSociete").removeAttribute("readonly");
                    document.getElementById("siteWebSociete").removeAttribute("readonly");
                    document.getElementById("categorieSociete").removeAttribute("readonly");
                    document.getElementById("adresseSociete").removeAttribute("readonly");
                    document.getElementById("codePostalSociete").removeAttribute("readonly");
                    document.getElementById("rcsSociete").removeAttribute("readonly");
                    document.getElementById("villeRcsSociete").removeAttribute("readonly");
                    document.getElementById("codeNafSociete").removeAttribute("readonly");
                    document.getElementById("effectifSociete").removeAttribute("readonly");
                    document.getElementById("activiteSociete").removeAttribute("readonly");
                }
            },
            error: function(jqXHR, error, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }

    function onChangeSiretM() {
        let siret = document.getElementById("siretSocieteM");
        $.ajax({
            type: "GET",
            url: `<?= URLROOT ?>/public/json/company.php?action=findBySiret&numero=${siret}`,
            dataType: "JSON",
            success: function(data) {

                document.getElementById("idSocieteM").value = data['idCompany'];
                document.getElementById("nomSocieteM").value = data['name'];
                document.getElementById("enseigneSocieteM").value = data['enseigne'];
                document.getElementById("telephoneSocieteM").value = data['businessPhone'];
                document.getElementById("emailSocieteM").value = data['email'];
                document.getElementById("siteWebSocieteM").value = data['webaddress'];
                document.getElementById("categorieSocieteM").value = data['category'];
                document.getElementById("regionSocieteM").value = data['region'];
                document.getElementById("adresseSocieteM").value = data['businessLine1'];
                document.getElementById("codePostalSocieteM").value = data['businessPostalCode'];
                document.getElementById("villeSocieteM").value = data['businessCity'];
                document.getElementById("departementSocieteM").value = data['businessState'];
                document.getElementById("rcsSocieteM").value = data['numeroRCS'];
                document.getElementById("villeRcsSocieteM").value = data['villeRCS'];
                document.getElementById("siretSocieteM").value = data['numeroSiret'];
                document.getElementById("codeNafSocieteM").value = data['siccode'];
                document.getElementById("effectifSocieteM").value = data['numEmployees'];
                document.getElementById("activiteSocieteM").value = data['industry'];
            },
            error: function(jqXHR, error, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }

    function onChangeAdresseImmeuble() {
        var adresse = document.getElementById("adresse1Immeuble").value.trim();
        if (adresse != "") {
            $.ajax({
                type: "GET",
                url: `<?= URLROOT ?>/public/json/immeuble.php?action=findByAdresse&adresse=${adresse}`,
                dataType: "JSON",
                success: function(data) {
                    if (data !== "0") {
                        $("#msgError").text("Cet immeuble existe déjà !");
                        $('#errorOperation').modal('show');

                        document.getElementById("adresse1Immeuble").value = "";
                        document.getElementById("adresse1Immeuble").focus();
                    }
                },
                error: function(jqXHR, error, errorThrown) {
                    console.log(jqXHR.responseText);
                }
            });
        } else {
            document.getElementById("adresse1Immeuble").value = "";
            document.getElementById("adresse1Immeuble").focus();
        }

    }

    function changePostalCodeImmeuble() {
        var code = document.getElementById("codePostalImmeuble").value;
        //console.log(code);

        if (code.length === 5) {
            readTextFile(`<?= URLROOT ?>/public/json/codePostal.json`, function(text) {
                var data = JSON.parse(text);
                var test = false;
                data.forEach(function(val) {
                    if (val[2] === Number(code)) {
                        test = true;
                        document.getElementById("villeImmeuble").value = val[9];
                        document.getElementById("departementImmeuble").value = val[12];
                        document.getElementById("regionImmeuble").value = val[14];
                        //console.log(val[9],val[12],val[14]);
                    }
                });
                if (!test) {
                    alert("Ce code postal n'existe Pas");

                }
            });
        } else {
            document.getElementById("codePostalImmeuble").value = "";
            document.getElementById("villeImmeuble").value = "";
            document.getElementById("departementImmeuble").value = "";
            document.getElementById("regionImmeuble").value = "";
            alert("Code postal invalide !");
        }

    }

    function onClickOtherOp(idOP) {
        if (actionOther == "enCours") {
            window.open("<?= URLROOT . '/Gestionnaire/te/' ?>" + idOP);
        } else {
            document.location.href = "<?= URLROOT . '/Gestionnaire/te/' ?>" + idOP;
        }
    }

    function showModalOthersOpForRelance() {
        actionOther = "enCours";
        $("#modalOthersOpForRelance").modal("show");
    }

    function showModalAddCompany() {
        $("#modalAddSociete").modal("show");
    }

    function showModalAddImmeuble() {
        $("#modalAddImmeuble").modal("show");
    }

    function closeActivity(type, code, idOP = "", nameOP = "", siClose = "") {
        let modeSign = $('#modeSignature').val();
        $.ajax({
            url: `<?= URLROOT ?>/public/json/activity.php?action=closeActivitybyOPAndRegarding`,
            type: 'POST',
            data: {
                idOp: idOP == "" ? $('#idOP').val() : idOP,
                opName: nameOP == "" ? $('#nameOP').val() : nameOP,
                regarding: type,
                codeActivity: code,
                idAuteur: $('#idUtilisateur').val(),
                numeroAuteur: $('#numeroAuteur').val(),
                auteur: $('#auteur').val(),
                idContact: $('#idContact').val(),
                dateRvRT: $('#dateRV').val() + " " + $('#heureRV').val(),
                idAuteurRV: $('#idExpertRV').val()
            },
            dataType: "JSON",
            beforeSend: function() {
                // $("#msgLoading").text("Clôture Activité '" + type + "' en cours...");
                // $("#loadingModal").modal("show");
            },
            success: function(response) {
                if (type == 'Faire signer la délégation de gestion') {
                    setTimeout(function() {
                        $('#loadingModal').modal('hide');
                    }, 1000);
                    $("#msgSuccess").text("Activité '" + type + "' clôturée avec succés !");
                    $('#successOperation').modal('show');
                    setTimeout(function() {
                        $('#successOperation').modal('hide');
                    }, 1000);
                    //HIDE MODAL
                    $('#divAssistGlobal').attr("hidden", "hidden");
                    location.reload();
                }

                if (siClose == "") {
                    if (response == "1") {
                        $("#loadingModal").modal("hide");
                        $("#msgSuccess").text("Activité '" + type + "' clôturée avec succés !");
                        $('#successOperation').modal('show');
                        setTimeout(function() {
                            $('#successOperation').modal('hide');
                        }, 1000);
                    } else {
                        $("#loadingModal").modal("hide");
                        $("#msgSuccess").text("Activité déjà clôturéé !");
                        $('#successOperation').modal('show');
                        setTimeout(function() {
                            $('#successOperation').modal('hide');
                        }, 1000);
                    }

                    if ((code == 1 || code == 2 || code == 3) && idOP == "") {
                        location.reload();
                    } else {
                        history.back();
                    }
                }
            },
            error: function(response) {
                $("#loadingModal").modal("hide");
                console.log(response);
                $("#msgError").text(
                    "Impossible de clôturer l'activité! Réessayer ou contacter l'administrateur");
                $('#errorOperation').modal('show');
            },
            complete: function() {
                $("#loadingModal").modal("hide");
            },
        });
    }

    function showModalContactCie(action) {
        $("#selectContact").modal("show");
        if (action == "edit") {
            $("#action1").val("edit");
        } else {
            $("#action1").val("add");
        }
    }

    function showModalExpertCompany() {
        let idExpert = $('#idExpert').val();
        if (idExpert != "" && idExpert != "0") {
            $.ajax({
                url: `<?= URLROOT ?>/public/json/contact.php?action=listeByidCompany&idCompany=` + idExpert,
                type: 'GET',
                dataType: "JSON",
                beforeSend: function() {
                    $("#msgLoading").text("Chargement...");
                    // $("#loadingModal").modal("show");
                },
                success: function(response) {
                    setTimeout(() => {
                        $("#loadingModal").modal("hide");
                    }, 500);
                    // console.log(response);
                    $('#tabledata').DataTable({
                        "Processing": true, // for show progress bar
                        "serverSide": false, // for process server side
                        "filter": true, // this is for disable filter (search box)
                        "orderMulti": true, // for disable multiple column at once
                        "bDestroy": true,
                        'iDisplayLength': 10,
                        "data": response,
                        "columns": [{
                                'render': function(data, type, row, meta) {
                                    return '<input type="radio" class="oneselectionExpert" name="oneselectionExpert" value="' +
                                        row.idContact + '">';
                                }
                            },
                            {
                                "data": "index"
                            },
                            {
                                "data": "fullName"
                            },
                            {
                                "data": "emailContact"
                            },
                            {
                                "data": "telContact"
                            },
                            {
                                "data": "statutContact"
                            }
                        ]
                    });
                    $('#modalExpertsCompany').modal('show');
                    if (response !== undefined && response !== null && response.length !==
                        0) {
                        // $("#msgSuccess").text("Dossier clôturé avec succés !");
                        // $('#successOperation').modal('show');
                    } else {
                        // $("#msgError").text(
                        //     "1-Impossible de charger les contacts !");
                        // $('#errorOperation').modal('show');
                    }
                },
                error: function(response) {
                    setTimeout(() => {
                        $("#loadingModal").modal("hide");
                    }, 500);
                    // console.log(response);
                    $("#msgError").text("Impossible de charger les contacts !");
                    $('#errorOperation').modal('show');
                },
                complete: function() {},
            });

        } else {
            $("#msgError").text(
                "Veuillez choisir le cadinet d'expertise d'abord !");
            $('#errorOperation').modal('show');
        }
    }

    function addOrEditExpert() {
        let id = $('.oneselectionExpert:checked').val();
        if (id != "" && id != undefined) {
            $.ajax({
                url: `<?= URLROOT ?>/public/json/contact.php?action=find&id=` + id,
                type: 'GET',

                dataType: "JSON",
                success: function(response) {
                    // console.log(response);
                    $("#modalExpertsCompany").modal("hide");
                    if (response != undefined && response != null && response != false) {
                        $(`#idExpertCompany`).val(response.idContact);
                        $(`#nomExpertCompany`).val(response.nomContact);
                        $(`#prenomExpertCompany`).val(response.prenomContact);
                        $(`#telExpertCompany`).val(response.telContact);
                        $(`#emailExpertCompany`).val(response.emailContact);
                        $(`#nomExpertCompany`).removeAttr("readonly");
                        $(`#prenomExpertCompany`).removeAttr("readonly");
                        $(`#telExpertCompany`).removeAttr("readonly");
                        $(`#emailExpertCompany`).removeAttr("readonly");
                    }
                },
                error: function(response) {
                    console.log(response);
                    $("#msgError").text("Erreur lors du choix !");
                    $('#errorOperation').modal('show');
                },
                complete: function() {},
            });
        } else {
            $("#msgError").text("Veuillez choisir un expert !");
            $('#errorOperation').modal('show');
        }
    }

    function onClickChangeCie() {
        $('#confirmChangeCieModal').modal('show');
    }

    function AddOrEditDO() {
        $.ajax({
            url: `<?= URLROOT ?>/public/json/company.php?action=AddOrEditDO`,
            type: 'POST',
            data: {
                idOp: $('#idOP').val(),
                numeroOP: $('#numeroOP').val(),
                oldIdDO: $('#oldIdDO').val(),
                action: $('#action').val(),
                checkDO: $('.oneselection:radio:checked').val(),
                idImmeuble: $('#idImmeuble').val()
            },

            dataType: "JSON",
            success: function(response) {
                console.log(response);
                $("#selectCompany").modal("hide");
                location.reload();
            },
            error: function(response) {
                console.log(response);
                // alert("ok");
            },
            complete: function() {

            },
        });
    }

    let idActivityForClose = "";
    let codeActivityForClose = "";
    let idOPForClose = "";

    function onClickCloturerTache(idActivity, codeActivity, idOP) {
        idActivityForClose = idActivity;
        codeActivityForClose = codeActivity;
        idOPForClose = idOP;
        $('#modalConfirmClotureTache').modal('show');
    }

    function onClickFaireTache(idActivity, codeActivity, idOP) {
        $.ajax({
            url: `<?= URLROOT ?>/public/json/activity.php?action=findActivityDBByCodeActivity&codeActivity=` +
                codeActivity,
            type: 'GET',
            dataType: "JSON",
            beforeSend: function() {
                $("#modalIncident").modal("hide");
                $("#msgLoading").text("Chargement en cours...");
                $('#loadingModal').modal('show');
            },
            success: function(response) {
                if (response != undefined && response != null) {
                    if (response != "0") {
                        if (response.lien != null && response.lien != "") {
                            let lien2 = "<?= URLROOT ?>" + "/Gestionnaire/" + response.lien + "/" + idOP;
                            //TEST IF TACHE EN COURS
                            $.ajax({
                                url: `<?= URLROOT ?>/public/json/userAccess.php?action=findByLien&idUser=` +
                                    `<?= $_SESSION['connectedUser']->idUtilisateur ?>`,
                                type: 'POST',
                                data: JSON.stringify(lien2),
                                dataType: "JSON",
                                beforeSend: function() {},
                                success: function(response2) {
                                    // console.log("success");
                                    // console.log(response);
                                    $("#loadingModal").modal("hide");
                                    setTimeout(() => {
                                        $("#loadingModal").modal("hide");
                                    }, 700);
                                    if (response2 != null && response2 != undefined) {
                                        if (response2 == false) {
                                            setTimeout(() => {
                                                window.open(lien2);
                                            }, 1000);
                                        } else {
                                            $("#msgSuccess").text(
                                                "Tache en cours de traitement par : " +
                                                response2.nomUser);
                                            $('#successOperation').modal('show');
                                        }
                                    } else {
                                        $("#msgError").text("Erreur !!!");
                                        $('#errorOperation').modal('show');
                                    }
                                },
                                error: function(response) {
                                    setTimeout(() => {
                                        $("#loadingModal").modal("hide");
                                    }, 700);
                                    // console.log("Error");
                                    // console.log(response);
                                    $("#msgError").text("Erreur !!!");
                                    $('#errorOperation').modal('show');
                                }
                            });

                        } else {
                            setTimeout(() => {
                                $("#loadingModal").modal("hide");
                            }, 1000);
                            $("#msgError").text("Tâche non disponible !");
                            $('#errorOperation').modal('show');
                        }
                    } else {
                        setTimeout(() => {
                            $("#loadingModal").modal("hide");
                        }, 100);
                        $("#msgError").text("Tâche non disponible !");
                        $('#errorOperation').modal('show');
                    }
                } else {
                    setTimeout(() => {
                        $("#loadingModal").modal("hide");
                    }, 1000);
                    $("#msgError").text("Impossible de contacter le site distant !");
                    $('#errorOperation').modal('show');
                }
            },
            error: function(response) {
                setTimeout(() => {
                    $("#loadingModal").modal("hide");
                }, 1000);
                console.log(response);
                $("#msgError").text("Impossible de contacter le site distant !");
                $('#errorOperation').modal('show');
            },
            complete: function() {

            },
        });
    }

    function onConfirmCloturerTache(index = "") {

        let commentaireClotureTache = $('#commentaireClotureTache').val();
        if (commentaireClotureTache.trim() == "") {
            $("#msgError").text(
                "Veuillez expliquer ...");
            $('#errorOperation').modal('show');
        } else {
            let idActivity = idActivityForClose;
            let codeActivity = codeActivityForClose;
            let idOP = idOPForClose;
            $.ajax({
                url: `<?= URLROOT ?>/public/json/activity.php?action=findActivityDBByCodeActivity&codeActivity=` +
                    codeActivity,
                type: 'GET',
                dataType: "JSON",
                beforeSend: function() {
                    $("#modalConfirmClotureTache").modal("hide");
                    $("#msgLoading").text("Traitement en cours...");
                    $('#loadingModal').modal('show');
                },
                success: function(response) {
                    if (response != undefined && response != null) {
                        if (response != "0") {
                            // if (response.lien != null && response.lien != "")
                            {
                                let lien2 = "<?= URLROOT ?>" + "/Gestionnaire/" + response.lien + "/" + idOP;
                                //TEST IF TACHE EN COURS
                                $.ajax({
                                    url: `<?= URLROOT ?>/public/json/userAccess.php?action=findByLien&idUser=` +
                                        `<?= $_SESSION['connectedUser']->idUtilisateur ?>`,
                                    type: 'POST',
                                    data: JSON.stringify(lien2),
                                    dataType: "JSON",
                                    beforeSend: function() {},
                                    success: function(response2) {
                                        // console.log("success");
                                        // console.log(response);
                                        $("#loadingModal").modal("hide");
                                        setTimeout(() => {
                                            $("#loadingModal").modal("hide");
                                        }, 500);
                                        if (response2 != null && response2 != undefined) {
                                            if (response2 == false) {
                                                //CLOSE ACTIVITY
                                                closeActivity(response.libelleActivity +
                                                    " sans traitement", codeActivity, "", "",
                                                    index)

                                            } else {
                                                if (index == "") {
                                                    $("#msgSuccess").text(
                                                        "Tâche en cours de traitement par : " +
                                                        response2.nomUser);
                                                    $('#successOperation').modal('show');
                                                }
                                            }
                                        } else {
                                            $("#msgError").text("Erreur !!!");
                                            $('#errorOperation').modal('show');
                                        }
                                    },
                                    error: function(response) {
                                        setTimeout(() => {
                                            $("#loadingModal").modal("hide");
                                        }, 200);
                                        // console.log("Error");
                                        // console.log(response);
                                        $("#msgError").text("Erreur !!!");
                                        $('#errorOperation').modal('show');
                                    }
                                });

                            }
                            //  else {
                            //     if (index == "") {
                            //         setTimeout(() => {
                            //             $("#loadingModal").modal("hide");
                            //         }, 500);
                            //         $("#msgError").text("Tâche non disponible !");
                            //         $('#errorOperation').modal('show');
                            //     }
                            // }
                        } else {
                            if (index == "") {
                                setTimeout(() => {
                                    $("#loadingModal").modal("hide");
                                }, 500);
                                $("#msgError").text("Tâche non disponible !");
                                $('#errorOperation').modal('show');
                            }
                        }
                    } else {
                        setTimeout(() => {
                            $("#loadingModal").modal("hide");
                        }, 500);
                        $("#msgError").text("Impossible de contacter le site distant !");
                        $('#errorOperation').modal('show');
                    }
                },
                error: function(response) {
                    setTimeout(() => {
                        $("#loadingModal").modal("hide");
                    }, 500);
                    console.log(response);
                    $("#msgError").text("Impossible de contacter le site distant !");
                    $('#errorOperation').modal('show');
                },
                complete: function() {

                },
            });
        }
    }

    function onClickDeleteNote(idNote, idOp) {
        document.getElementById("textDeleteNote").innerHTML = `Voulez-vous supprimer la note de cette opportunité ?</br>
                        Cette action est irreversible !`
        document.getElementById("idNote2").value = idNote;
        document.getElementById("idOpNote").value = idOp;
    }

    function cloturerPlusieursTache() {
        if ($('#commentaireClotureAllTache').val() == "") {
            $("#msgError").text(
                "Veuillez expliquer ...");
            $('#errorOperation').modal('show');
        } else {
            var one = document.getElementsByName('check');
            let nbCheck = 0;
            let tabId = [];
            for (let index = 0; index < one.length; index++) {
                const element = one[index];
                if (element.checked) {
                    nbCheck++;
                    let val = element.value.split(';');
                    idActivityForClose = val[0];
                    codeActivityForClose = val[1];
                    idOPForClose = val[2];
                    $('#commentaireClotureTache').val($('#commentaireClotureAllTache').val());
                    onConfirmCloturerTache(index == one.length - 1 ? "" : index);
                }
            }

        }

    }
</script>