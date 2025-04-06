var sizeSections = false;

$(document).ready(function() {
    initializeTinyMCE();
    showSection();
    
});

// Fonction pour initialiser TinyMCE
function initializeTinyMCE() {
    tinymce.remove();
    tinymce.init({
        selector: 'textarea.tinymce-editor',
        height: 570,
        branding: false,
        statusbar: false,
        paste_as_text: true,
        menubar: false,
        plugins: [
            'lists link image code table'
        ],
        toolbar: 'undo redo | fontselect fontsizeselect | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | table ',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
    });
}

function comToTabByName(params) {
    let tab = [];
    params.forEach(element => {
        let id = element.id;
        let idPiece = $('#' + id).data('idpiece');
        tab.push({
            id: idPiece,
            commentaire: $('#' + id).val()
        });
    });
    return tab;
}

function showSection() {
    var showSection = document.getElementById('showSection');
    if (sections == undefined || sections.length == 0) {
        sections = saveSections();
        sizeSections = false;
    } else if (sections.length < maxIndex && sections.length > 0) {
        saveSections();
        sizeSections = true;
    }
    else {
        sizeSections = true;
    }
    
    showSection.innerHTML = displaySectionsTree(sections);
    const activeSectionNumero = localStorage.getItem('activeSection');
    if (activeSectionNumero) {
        const section = sections.find(s => s.numeroSection == activeSectionNumero);
        if (section) {
            numberSection(activeSectionNumero, false);

            let parent = sections.find(s => s.idSection == section.idSection_parentF);
            while (parent) {
                const parentElement = $(`#section${parent.numeroSection}-`);
                const toggleElement = parentElement.find('.section-toggle i');
                parentElement.children('.subsections').removeClass('collapsed');
                toggleElement.removeClass('fa-chevron-right').addClass('fa-chevron-down');
                const parentSection = sections.find(s => s.idSection == parent.idSection);
                parent = parentSection ? parentSection.idSection_parentF : null;
            }
        }
    }

}

function numberSection(numero, isParent = false) {
    if (!isParent) {
        if ($(`#${numero}`).length > 0) {
            $(`#${numero}`).on('blur', function () {
                saveOneSection(numero);
            });
            tinymce.get(`${numero}`).on('blur', function () {
                saveOneSection(numero);
            });
        }

        localStorage.setItem('activeSection', numero);

        activeSection = numero;
        
        $('.sectionSection').addClass('hidden');
        $(`.section${numero}`).removeClass('hidden');

        $('.section-item').removeClass('active');
        $(`#section-item${numero}-`).addClass('active');

    }
    var activs = $(`#section${numero}-`);
    if (!sizeSections) {
        for (let index = 0; index < maxIndex; index++) {
            if ($(`#section${index}-`).data('numero') == numero) {
                activs = $(`#section${index}-`);
                break;
            }
        }
    }
    if (activs.data('parent') === true) {
        for (let index = 0; index < sections.length; index++) {
            var section = sections[index];
            if (section.numeroSection == numero) {
                sectionNumeroContent = $(`#${section.numero}`);
                if(section.idSection_parentF){
                    var idParent = section.idSection_parentF;
                    var sectionParent = sections.find(s => (s.idSection == idParent));
                    if (sectionParent) {
                        var numeroParent = sectionParent.numeroSection;
                        if (!sizeSections) {
                            for (let index = 0; index < maxIndex; index++) {
                                if ($(`#section${index}-`).data('numero') == numeroParent) {
                                    numeroParent = index;
                                    break;
                                }
                            }
                        }
                    }
                    section = $(`#section${numeroParent}-`);
                    section.removeClass('hidden');
                    numberSection(section.data('numero'), true);
                }
            }
        }
    }
}

function reload() {
    location.reload();
}

function saveSections() {
    var idSommaire = $('#idSommaire').val();
    var sectionsRegister = [];
    
    if (sections == undefined || sections.length === 0) {
        for (let index = 0; index <= maxIndex; index++) {
            var section = $(`#section${index}-`);
            if (section.length > 0) {
                var titre = section.data('titre');
                var numero = section.data('numero');
                var idParent = section.data('idparent');
                var contenu = "";
                if (numero != "" && $(`#${numero}`).length > 0) {
                    contenu = $(`#${numero}`).val();
                }

                if (titre) {
                    sectionsRegister.push({
                        titreSection: titre || "",
                        numeroSection: numero || "",
                        idSection_parentF: idParent || "",
                        contenuSection: contenu || "",
                        idSection : numero || ""
                    });
                } else {
                    console.warn(`Section ${index} contient des données invalides`);
                }
            }
        }
    } else {
        sections.forEach((sectionData, index) => {
            var numero = sectionData.numeroSection;
            var section = $(`#section${numero}-`);
            if (section.length > 0) {
                var titre = section.data('titre');
                var idParent = section.data('idparent');
                var contenu = "";
                if (numero != "" && $(`#${numero}`).length > 0) {
                    var editor = tinymce.get(`${numero}`);
                    if (editor) {
                        contenu = editor.getContent();
                        console.log(contenu);
                    } else {
                        contenu = $(`#${numero}`).val();
                        console.warn(`TinyMCE n'est pas initialisé pour l'élément #${numero}`);
                    }
                }
                
                if (titre) {
                    sectionsRegister.push({
                        titreSection: titre || "",
                        numeroSection: numero || "",
                        idSection_parentF: idParent || "",
                        contenuSection: contenu || "",
                        idSection : numero || ""
                    });
                } else {
                    console.warn(`Section ${index} contient des données invalides`);
                }
            }
        });
    }

    if (sectionsRegister.length === 0) {
        $("#msgError").text("Aucune section à enregistrer.");
        return;
    }
    
    console.log(sectionsRegister);
    $.ajax({
        url: `${CONFIG.routes.section.save}`,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            idSommaire: idSommaire,
            sections: sectionsRegister
        }),
        beforeSend: function () {
            $("#msgLoading").text("Enregistrement en cours...");
            $("#msgError").text("");
        },
        success: function(response) {
            try {
                var data = JSON.parse(response);
                console.log("Résultat de l'enregistrement des sections : ", data);
                if (data.success) {
                    $("#msgLoading").text("Enregistrement réussi !");
                } else {
                    $("#msgError").text(data.error || "Erreur inconnue.");
                }
                return sections;
            } catch (error) {
                console.error("Erreur JSON ou réponse inattendue : ", response);
                $("#msgError").text("Une réponse non valide a été reçue du serveur.");
            }
        },
        error: function(xhr, status, error) {
            console.error("Erreur AJAX :", error);
            console.log("Réponse brute :", xhr.responseText);
            $("#msgError").text("Erreur lors de l'enregistrement : " + error);
            $('#errorOperation').modal('show');
        },
        complete: function () {
            $("#msgLoading").text("");
            
        }
    });

    return sectionsRegister;
}

function saveOneSection(number = activeSection) {
    var idSommaire = $('#idSommaire').val();
    var sectionsRegister = [];
    if (number != "" && $(`#section${number}-`).length > 0) {
        var section = $(`#section${number}-`);
        if (section.length > 0) {
            var titre = section.data('titre');
            var numero = section.data('numero');
            var idParent = section.data('idparent');
            var contenu = "";
            if (numero != "" && $(`#${numero}`).length > 0) {
                var editor = tinymce.get(`${numero}`);
                if (editor) {
                    contenu = editor.getContent();
                    console.log(contenu);
                } else {
                    contenu = $(`#${numero}`).val();
                    console.warn(`TinyMCE n'est pas initialisé pour l'élément #${numero}`);
                }
            }

            if (titre) {
                sectionsRegister.push({
                    titreSection: titre || "",
                    numeroSection: numero || "",
                    idSection_parentF: idParent || "",
                    contenuSection: contenu || ""
                });
            } else {
                console.warn(`Section ${index} contient des données invalides`);
            }
        }
    }
    
    if (sectionsRegister.length === 0) {
        $("#msgError").text("Aucune section à enregistrer.");
        return;
    }
    
    console.log(sectionsRegister);
    $.ajax({
        url: `${CONFIG.routes.section.save}`,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            idSommaire: idSommaire,
            sections: sectionsRegister
        }),
        success: function(response) {
            try {
                var data = JSON.parse(response);
                console.log("Résultat de l'enregistrement des sections : ", data);
                
                if (data.success) {
                    $("#msgLoading").text("Enregistrement réussi !");
                } else {
                    $("#msgError").text(data.error || "Erreur inconnue.");
                }
            } catch (error) {
                console.error("Erreur JSON ou réponse inattendue : ", response);
                $("#msgError").text("Une réponse non valide a été reçue du serveur.");
            }
        },
        error: function(xhr, status, error) {
            console.error("Erreur AJAX :", error);
            console.log("Réponse brute :", xhr.responseText);
            $("#msgError").text("Erreur lors de l'enregistrement : " + error);
            $('#errorOperation').modal('show');
        },
        complete: function () {
            $("#msgLoading").text("");
        }
    });
}

function toggleSubsections(event, element) {
    event.stopPropagation();
    const icon = $(element).find('i');
    icon.toggleClass('fa-chevron-down fa-chevron-right');
    $(element).closest('.section-item').children('.subsections').toggleClass('collapsed');
}

function displaySectionsTree(sections, parentId = null, level = 0) {
    console.log(sections);
    let currentLevelSections = sections.filter(s => {
        if (parentId == null) {
            return !s.idSection_parentF;
        }
        return s.idSection_parentF == parentId;
    });

    // currentLevelSections.sort((a, b) => {
    //     const aNumbers = a.numeroSection.split('.').map(Number);
    //     const bNumbers = b.numeroSection.split('.').map(Number);

    //     for (let i = 0; i < Math.max(aNumbers.length, bNumbers.length); i++) {
    //         const aNum = aNumbers[i] || 0;
    //         const bNum = bNumbers[i] || 0;
    //         if (aNum !== bNum) {
    //             return aNum - bNum;
    //         }
    //     }
    //     return 0;
    // });

    let html = '';

    currentLevelSections.forEach(section => {
        var hasSubsections = sections.some(
            s => ((s.idSection_parentF == section.idSection))
        );

        if (!hasSubsections) {
            hasSubsections = sections.some(
                s => ((s.idSection_parentF == section.numeroSection))
            );
        }

        const activeSection = localStorage.getItem('activeSection');
        let isActiveOrParent = false;

        let currentSection = section;
        while (currentSection) {
            if (currentSection.numeroSection == activeSection) {
                isActiveOrParent = true;
                break;
            }
            currentSection = sections.find(
                s => s.idSection == currentSection.idSection_parentF
            );
        }

        html += `
            <div class="section-item w-100" id="section-item${section.numeroSection}">
                <div class="d-flex align-items-center">
                    ${hasSubsections ? `
                        <div class="section-toggle mr-2" onclick="toggleSubsections(event, this)">
                            <i class="fas fa-chevron-${isActiveOrParent ? 'down' : 'right'}"></i>
                        </div>
                    ` : '<div style="width: 20px;"></div>'}
                    <div class="flex-grow-1" onclick="numberSection('${section.numeroSection}', false)">
                        <span class="mr-2">${section.numeroSection}</span>
                        ${section.titreSection}
                    </div>
                </div>
                ${hasSubsections ? `
                    <div class="subsections ${isActiveOrParent ? '' : 'collapsed'}">
                        ${displaySectionsTree(sections, section.idSection, level + 1)}
                    </div>
                ` : ''}
            </div>
        `;
    });

    if (level === 0) {
        const rootDropZone = `
            <div class="root-drop-zone" data-level="root">
                ${html || '<p class="text-muted text-center">Aucune section trouvée</p>'}
            </div>
        `;

        $('#sections-tree').html(rootDropZone);
    }

    return html;
}

function getSection() {
    $.ajax({
        type : 'POST',
        url : url + 'public/json/releve.php?action=getSections',
        data : {
            idRT : idRT,
            idSommaire : idSommaire
        },
        success: function(response) {
            console.log(response);
            if (response != undefined && response != null) {
                return response.data;
            } else {
                $("#msgError").text(
                    "Impossible de charger les sections"
                );
                $('#errorOperation').modal('show');
            }
        }
    });
}

function saveInfosCompteRenduRT(params) {
    saveSections();

    for (let index = 0; index < sections.length; index++) {
        var section = sections[index];
        let editor = tinymce.get(`${section.numeroSection}`);
        if (editor) {
            let contenu = editor.getContent();
            $(`#${section.numeroSection}`).val(contenu);
        }
    }

    let comsPiece = comToTabByName(document.getElementsByName("commentairePiece"));
    let comsMetrePiece = comToTabByName(document.getElementsByName("commentaireMetrePiece"));
    let comsSupportPiece = comToTabByName(document.getElementsByName("commentaireSupportPiece"));
    let comsSupport = comToTabByName(document.getElementsByName("commentaireSupport"));
    let comsMetreSupport = comToTabByName(document.getElementsByName("commentaireMetreSupport"));
    if (files.length != 0) {
        var file = files[0];
        var form_data = new FormData();
        form_data.append("file", file);
        form_data.append("newName", photoImmeuble);

        var xhttp = new XMLHttpRequest();
        xhttp.open("POST",
            `${url}/public/json/document.php?action=saveFileImmeuble`,
            true);
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var response = this.responseText;
            }
        };

        xhttp.send(form_data);
    }

    $.ajax({
        url: `${url}/public/json/releve.php?action=saveInfosCompteRenduRT`,
        type: 'POST',
        data: {
            idOP: $('#idOP').val(),
            pieces: JSON.stringify(pieces),
            idAuteur: $('#idUtilisateur').val(),
            numeroAuteur: $('#numeroAuteur').val(),
            auteur: $('#auteur').val(),
            opName: $('#nameOP').val(),
            contexte: $('#3-').val(),
            descriptionSinistre: $('#3-1').val(),
            origineSinistre1: $('#3-2').val(),
            interventionsInitiales: $('#3-3').val(),
            introduction: $('#1-').val(),
            conclusion: $('#5-').val(),
            deroulementSeance: $('#4-').val(),
            comsPiece: comsPiece,
            comsMetrePiece: comsMetrePiece,
            comsSupportPiece: comsSupportPiece,
            comsSupport: comsSupport,
            comsMetreSupport: comsMetreSupport,
            isGenerate: params,
            precisionComplementaire: $('#7-').val(),
            photoImmeuble: photoImmeuble,
            idImmeuble: $('#idImmeuble').val()
        },
        beforeSend: function() {
            if (params == 1) {
                $("#modalConfirmRT").modal("hide");
            }
            $("#msgLoading").text(
                "Enregistrement en cours..."
            );
            if (params == 3) {
                $("#msgLoading").text(
                    "Enregistrement et visualisation en cours..."
                );
            }
            $("#loadingModal").modal("show");
        },
        dataType: "JSON",
        success: function(response) {
            console.log(response);
            setTimeout(() => {
                $("#loadingModal").modal("hide");
            }, 1000)
            if (response != undefined && response != null && response == "1") {
                if (params == 3) {
                    popitup(op.name + "_CompteRenduRT.pdf", 'RAPPORT FRT');
                    savePDF();
                } else {
                    $("#msgSuccess").text(
                        "Enregistrement effectué avec succés !"
                    );
                    $('#successOperation').modal(
                        'show');
                    setTimeout(function() {
                        $('#successOperation').modal('hide');
                    }, 500);
                    if (params == 1) {
                        closeActivity('Faire Compte Rendu RT', 16)
                    }
                }

            } else {
                $("#msgError").text(
                    "1Impossible d'enregistrer! Réessayer ou contacter l'administrateur"
                );
                $('#errorOperation').modal('show');
                $("#modalConfirmControlerRT").modal("hide");
            }
        },
        error: function(response) {
            console.log(response);
            setTimeout(() => {
                $("#loadingModal").modal("hide");
            }, 1000)
            $("#loadingModal").modal("hide");
            $('#errorOperation').modal('show');
            $("#modalConfirmControlerRT").modal("hide");
            $("#msgError").text(
                "Impossible d'enregistrer! Réessayer ou contacter l'administrateur"
            );
            $('#errorOperation').modal('show');
        },
        complete: function() {
        },
    });
}

function onClickTerminerRT() {
    $("#textTerminerRT").text(
        "Voulez-vous terminer la rédaction du Compte Rendu RT ?"
    );
    $("#modalConfirmRT").modal("show");
}


function onClickControlerRT(params) {
    valueControleRT = params;
    if (valueControleRT == 0) {
        $("#textControleRT").text(
            "Voulez-vous rejeter le Compte Rendu RT ?"
        );

    } else {
        $("#textControleRT").text(
            "Voulez-vous valider le Compte Rendu RT ?"
        );
    }
    $("#modalConfirmControlerRT").modal("show");
}

function onConfirmControlerRT() {
    let text = valueControleRT == 0 ? "Rejet" : "Valid";
    let com = $('#commentaireControlerRT').val();
    if (com == "" && valueControleRT == 0) {
        $("#msgError").text(
            "Veuillez mettre un commentaire !"
        );
        $('#errorOperation').modal('show');
    } else {
        $.ajax({
            url: `${url}/public/json/activity.php?action=controlerRT`,
            type: 'POST',
            data: {
                idOP: $('#idOP').val(),
                idAuteur: $('#idUtilisateur').val(),
                numeroAuteur: $('#numeroAuteur').val(),
                auteur: $('#auteur').val(),
                opName: $('#nameOP').val(),
                commentaire: com,
                idActivityCRT: activityCRT ? activityCRT.idActivity : "0",
                typeControle: valueControleRT
            },
            beforeSend: function() {
                $("#msgLoading").text(
                    "Traitement en cours..."
                );
                $("#loadingModal").modal("show");
            },
            dataType: "JSON",
            success: function(response) {
                console.log(response);
                setTimeout(() => {
                    $("#loadingModal").modal("hide");
                }, 1000)
                if (response != undefined && response != null && response == "1") {
                    $("#msgSuccess").text(
                        "FRT " + text + "ée !"
                    );
                    $('#successOperation').modal(
                        'show');
                    if (valueControleRT == 0) {
                        location.href = url +
                            "/Gestionnaire/indexOpportunite/controleRT/-tous";
                    } else {
                        location.href = url +
                            "/Gestionnaire/indexOpportunite/faireRT/-tous";
                    }
                } else {
                    $("#msgError").text(
                        "1Impossible de " + text +
                        "er la FRT! Réessayer ou contacter l'administrateur"
                    );
                    $('#errorOperation').modal('show');
                    $("#modalConfirmControlerRT").modal("hide");
                }
            },
            error: function(response) {
                console.log(response);
                setTimeout(() => {
                    $("#loadingModal").modal("hide");
                }, 1000)
                $("#loadingModal").modal("hide");
                $('#errorOperation').modal('show');
                $("#modalConfirmControlerRT").modal("hide");
                $("#msgError").text(
                    "Impossible  de " + text + "er la FRT! Réessayer ou contacter l'administrateur"
                );
                $('#errorOperation').modal('show');
            },
            complete: function() {

            },
        });
    }

}

var codePostal = document.getElementById("codePostalC");
var ville = document.getElementById("villeC");
var adresse = document.getElementById("adresse1C");
let autocomplete;

//AUTOCOMPLETE ADRESSE
function initialize() {
    console.log("ok")
    var input = document.getElementById('adresse1C');
    autocomplete = new google.maps.places.Autocomplete(input, {
        componentRestrictions: {
            country: ["fr"]
        },
        fields: ["address_components", "geometry"],
        types: ["address"],
    });
    console.log(autocomplete)
    autocomplete.addListener("place_changed", fillInAddress)
}

function fillInAddress() {
    // Get the place details from the autocomplete object.
    const place = autocomplete.getPlace();
    console.log("place")
    console.log(place)
    let address1 = "";
    let postcode = "";

    // Get each component of the address from the place details,
    // and then fill-in the corresponding field on the form.
    // place.address_components are google.maps.GeocoderAddressComponent objects
    // which are documented at http://goo.gle/3l5i5Mr
    for (const component of place.address_components) {
        // @ts-ignore remove once typings fixed
        const componentType = component.types[0];

        console.log(component);

        switch (componentType) {
            case "street_number": {
                address1 = `${component.long_name} ${address1}`;
                break;
            }

            case "route": {
                address1 += component.short_name;
                break;
            }

            case "postal_code": {
                postcode = `${component.long_name}${postcode}`;
                break;
            }

            case "postal_code_suffix": {
                postcode = `${postcode}-${component.long_name}`;
                break;
            }
            case "locality":
                document.querySelector("#villeC").value = component.long_name;
                break;
        }
    }

    adresse.value = address1;
    codePostal.value = postcode;
    // ville.value = postcode;
    // After filling the form with address components from the Autocomplete
    // prediction, set cursor focus on the second address line to encourage
    // entry of subpremise information such as apartment, unit, or floor number.
    // address2Field.focus();
}

google.maps.event.addDomListener(window, 'load', initialize);



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
    photoImmeuble = "immeuble_" + $('#idImmeuble').val() + "." + ext;
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