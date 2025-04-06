// Variables globales
let currentSommaireId = null;
let mockData = {
    titreSommaire: null,
    sections: []
};
let sommaires = [];
let activeSection = null;

let selectedFile = null;
let selectedFileType = null; // 'local' ou 'RYM'
let activeSectionId = null;
// Initialisation au chargement du document
$(document).ready(function () {
    if (CONFIG.hasSommaire) {
        loadSectionsFromDatabase();
    }
});

// Chargement des sections depuis la base de données
function loadSectionsFromDatabase() {
    if (!CONFIG.sommaire) {
        console.log('Sommaire is null');
        return;
    }

    $.ajax({
        url: CONFIG.routes.section.getSectionsBySommaire,
        method: 'GET',
        data: {
            idSommaire: CONFIG.sommaire.idSommaire
        },
        dataType: 'json',
        success: function (response) {
            if (response.sections) {
                // Mettre à jour les numéros avant l'affichage
                mockData.sections = updateAllSectionNumbers(response.sections);

                mockData.titreSommaire = response.titreSommaire;
                displaySectionsTree(mockData.sections);

                // Restaurer la section active si elle existe
                const activeSectionId = localStorage.getItem('activeSection');
                if (activeSectionId) {
                    const section = mockData.sections.find(s => s.idSection == activeSectionId);
                    if (section) {
                        showSection(activeSectionId);

                        // Si la section a des parents, ouvrir les sections parentes
                        let parentId = section.idSection_parentF;
                        while (parentId) {
                            const parentElement = $(`.section-item[data-section-id="${parentId}"]`);
                            const toggleElement = parentElement.find('.section-toggle i');
                            parentElement.children('.subsections').removeClass('collapsed');
                            toggleElement.removeClass('fa-chevron-right').addClass('fa-chevron-down');

                            const parentSection = mockData.sections.find(s => s.idSection == parentId);
                            parentId = parentSection ? parentSection.idSection_parentF : null;
                        }
                    }
                }
            } else {
                $('#sections-tree').html('<p class="text-muted text-center">Aucune section trouvée</p>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Erreur lors du chargement des sections:', error);
            $('#sections-tree').html(
                '<div class="alert alert-danger">Erreur lors du chargement des sections</div>'
            );
        }
    });
}



// Affichage des sections dans le menu déroulant
function displaySectionsTree(sections, parentId = null, level = 0) {
    // Filtrer les sections du niveau courant
    let currentLevelSections = sections.filter(s => {
        if (parentId == null) {
            return !s.idSection_parentF;
        }
        return s.idSection_parentF == parentId;
    });

    // Trier les sections par leur numéro
    currentLevelSections.sort((a, b) => {
        const aNumbers = a.numeroSection.split('.').map(Number);
        const bNumbers = b.numeroSection.split('.').map(Number);

        // Comparer chaque niveau de numérotation
        for (let i = 0; i < Math.max(aNumbers.length, bNumbers.length); i++) {
            const aNum = aNumbers[i] || 0;
            const bNum = bNumbers[i] || 0;
            if (aNum != bNum) {
                return aNum - bNum;
            }
        }
        return 0;
    });

    let html = '';

    currentLevelSections.forEach((section, index) => {
        const hasSubsections = sections.some(s => s.idSection_parentF == section.idSection);

        html += `
            <div class="section-item" data-section-id="${section.idSection}">
                <div class="d-flex align-items-center">
                    ${hasSubsections ? `
                        <div class="section-toggle mr-2" onclick="toggleSubsections(event, this)">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    ` : '<div style="width: 20px;"></div>'}
                    <div class="flex-grow-1" onclick="showSection(${section.idSection})">
                        <span class="mr-2">${section.numeroSection}</span>
                        ${section.titreSection}
                    </div>
                    <div class="section-actions">
                        <button class="btn btn-sm btn-link" onclick="event.stopPropagation(); addSubSection(${section.idSection})" title="Ajouter une sous-section">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-link text-danger" onclick="event.stopPropagation(); deleteSection(${section.idSection})" title="Supprimer la section">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                ${hasSubsections ? `
                    <div class="subsections">
                        ${displaySectionsTree(sections, section.idSection, level + 1)}
                    </div>
                ` : ''}
            </div>
        `;
    });

    // Si on est au niveau racine (level 0), initialiser le DOM et le drag & drop
    if (level == 0) {
        // Entourer l'arborescence avec une zone de drop pour le niveau racine
        const rootDropZone = `
            <div class="root-drop-zone" data-level="root">
                ${html || '<p class="text-muted text-center">Aucune section trouvée</p>'}
            </div>
        `;

        $('#sections-tree').html(rootDropZone);
        // makeItemsDraggable();
        // initializeDragAndDrop();
    }

    return html;
}
// Fonction pour afficher ou masquer les sous-sections
function toggleSubsections(event, element) {
    event.stopPropagation();
    const icon = $(element).find('i');
    icon.toggleClass('fa-chevron-down fa-chevron-right');
    $(element).closest('.section-item').children('.subsections').toggleClass('collapsed');
}


function triggerFileUpload(sectionId) {
    // Délègue la gestion au DocumentHandler
    DocumentHandler.openFileModal(sectionId);
}


// Affichage d'une section
function showSection(sectionId) {
    const section = mockData.sections.find(s => s.idSection == sectionId);
    if (!section) return;

    // Sauvegarder l'ID de la section active
    localStorage.setItem('activeSection', sectionId);

    // Mise à jour de l'UI
    $('.section-item').removeClass('active');
    $(`.section-item[data-section-id="${sectionId}"]`).addClass('active');

    // Charger les documents de la section immédiatement
    DocumentHandler.loadSectionDocuments(sectionId);

    const html = `
        <div class="section-content-area">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4>${section.numeroSection} - ${section.titreSection}</h4>
                </div>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-secondary" onclick="triggerFileUpload(${section.idSection})">
                        <i class="fas fa-upload"></i> Document
                    </button>
                    <button class="btn btn-sm btn-outline-primary" onclick="addSubSection(${section.idSection})">
                        <i class="fas fa-plus"></i> Sous-section
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteSection(${section.idSection})">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="saveSection(${section.idSection})">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label>Titre de la section</label>
                <input type="text" 
                       class="form-control section-title-input" 
                       id="section-title-${section.idSection}" 
                       value="${section.titreSection}"
                       data-section-id="${section.idSection}">
            </div>

            <div class="form-group">
                <label>Contenu de la section</label>
                <textarea class="form-control tinymce-editor" 
                          id="section-content-${section.idSection}"
                          data-section-id="${section.idSection}"
                          rows="3">${section.contenuSection || ''}</textarea>
            </div>
        </div>
    `;

    $('#section-content').html(html);
    initializeTinyMCE();

    // Ajouter les écouteurs d'événements pour la sauvegarde automatique
    $(`#section-title-${section.idSection}`).on('blur', function () {
        saveSection(section.idSection, true); // true indique une sauvegarde automatique
    });

    // Pour TinyMCE, on écoute l'événement de changement
    tinymce.get(`section-content-${section.idSection}`).on('blur', function () {
        saveSection(section.idSection, true); // true indique une sauvegarde automatique
    });
}




// Gestion du formulaire de création de section
$('#createSectionForm').on('submit', function (e) {
    e.preventDefault();

    const formData = {
        titreSection: $('input[name="titreSection"]').val().trim(),
        idSommaireF: $('input[name="idSommaireF"]').val(),
        idSection_parentF: $('#parentSectionId').val() || null,
        numeroSection: calculateSectionNumber($('#parentSectionId').val())
    };

    if (!formData.titreSection) {
        alert('Veuillez entrer un titre pour la section');
        return;
    }

    const saveButton = $(this).find('button[type="submit"]');
    const originalText = saveButton.html();
    saveButton.html('<i class="fas fa-spinner fa-spin"></i> Création...').prop('disabled', true);

    $.ajax({
        url: CONFIG.routes.section.add,
        method: 'POST',
        data: formData,
        success: function (response) {
            try {
                const jsonResponse = typeof response == 'string' ? JSON.parse(response) : response;
                if (jsonResponse.success || response == '') {
                    $('#createSectionModal').modal('hide');
                    loadSectionsFromDatabase();
                    $('input[name="titreSection"]').val('');
                } else {
                    console.error('Erreur:', jsonResponse.error || 'Erreur inconnue');
                    alert(jsonResponse.error || 'Erreur lors de la création de la section');
                }
            } catch (e) {
                $('#createSectionModal').modal('hide');
                loadSectionsFromDatabase();
                $('input[name="titreSection"]').val('');
            }
        },
        error: function (xhr, status, error) {
            console.error('Erreur lors de la création de la section:', error);
            if (xhr.status == 200) {
                $('#createSectionModal').modal('hide');
                loadSectionsFromDatabase();
                $('input[name="titreSection"]').val('');
            } else {
                alert('Une erreur est survenue lors de la création de la section');
            }
        },
        complete: function () {
            saveButton.html(originalText).prop('disabled', false);
        }
    });
});

// Fonctions de gestion des sections
/**
 * Affiche la modal de création d'un sommaire
 *
 * @param {Event} e
 */
function showCreateSommaireModal(e) {
    e.preventDefault();
    $('input[name="titreSommaire"]').val('');
    $('#createSommaireModal').modal('show');
}


/**
 * Ouvre la modal de création d'une section principale
 *
 * @see #createSectionModal
 */
function addMainSection() {
    $('#parentSectionId').val('');
    calculateSectionNumber(null);

    $('#createSectionModal').modal('show');
}


// Ouvre la modal de creation d'une sous-section
function addSubSection(parentId) {
    $('#parentSectionId').val(parentId);
    calculateSectionNumber(parentId);
    $('#createSectionModal').modal('show');
}


// Calcul du numero de section
function calculateSectionNumber(parentId) {
    const sections = mockData.sections;
    let numeroSection;

    if (!parentId) {
        // Pour une section principale
        const mainSections = sections.filter(s => !s.idSection_parentF);
        numeroSection = (mainSections.length + 1).toString();

    } else {
        // Pour une sous-section
        const parentSection = sections.find(s => parseInt(s.idSection) == parseInt(parentId));
        console.log(" parentSection", parentSection);
        const subSections = sections.filter(s => parseInt(s.idSection_parentF) == parseInt(parentId));
        numeroSection = `${parentSection.numeroSection}.${subSections.length + 1}`;
    }

    $('#numeroSection').val(numeroSection);
    return numeroSection;
}


// Selection d'une section
function selectSection(sectionId) {
    const section = mockData.sections.find(s => s.idSection == sectionId);
    if (!section) return;

    // Mise à jour de l'UI
    $('.section-item').removeClass('active');
    $(`.section-item[data-section-id="${sectionId}"]`).addClass('active');


    // Affichage du contenu
    const contentHtml = `
        <div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>${section.numeroSection} - ${section.titreSection}</h4>
                <button class="btn btn-primary" onclick="saveSection(${sectionId})">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
            <div class="form-group">
                <label>Titre de la section</label>
                <input type="text" class="form-control" 
                       id="section-title-${sectionId}" 
                       value="${section.titreSection}">
            </div>
            <div class="form-group">
                <label>Contenu de la section</label>
                <textarea class="form-control tinymce-editor" 
                          id="section-content-${sectionId}">${section.contenuSection || ''}</textarea>
            </div>
        </div>
    `;

    $('#section-content').html(contentHtml);

    // Initialisation de TinyMCE
    tinymce.remove();
    initializeTinyMCE();
}


// Sauvegarde d'une section
function saveSection(sectionId, isAutoSave = false) {
    const section = {
        idSection: sectionId,
        titreSection: $(`#section-title-${sectionId}`).val().trim(),
        contenuSection: tinymce.get(`section-content-${sectionId}`).getContent(),
        numeroSection: mockData.sections.find(s => s.idSection == sectionId).numeroSection
    };

    $.ajax({
        url: CONFIG.routes.section.updateMultiple,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            sections: [section]
        }),
        success: function (response) {
            if (response.success) {
                // Mettre à jour les données locales
                const sectionIndex = mockData.sections.findIndex(s => s.idSection == sectionId);
                if (sectionIndex != -1) {
                    mockData.sections[sectionIndex] = { ...mockData.sections[sectionIndex], ...section };
                }

                // Rafraîchir l'arborescence
                displaySectionsTree(mockData.sections);

                // Afficher la confirmation seulement si ce n'est pas une sauvegarde automatique
                if (!isAutoSave) {
                    $('#saveConfirmationModal').modal('show');
                    setTimeout(function () {
                        $('#saveConfirmationModal').modal('hide');
                    }, 800);
                }
            } else {
                alert('Erreur lors de la sauvegarde: ' + (response.error || 'Erreur inconnue'));
            }
        },
        error: function (xhr, status, error) {
            alert('Erreur lors de la sauvegarde: ' + error);
        }
    });
}


// Affichage des sections
function displaySections(sections, parentId = null, level = 0) {
    function getNextNumber(parentSection) {
        const siblings = sections.filter(s => {
            if (parentSection) {
                return s.idSection_parentF == parentSection.idSection;
            }
            return !s.idSection_parentF;
        });

        return siblings.length > 0
            ? Math.max(...siblings.map(s => {
                const numbers = s.numeroSection.split('.');
                return parseInt(numbers[numbers.length - 1]);
            })) + 1
            : 1;
    }

    function buildSectionNumber(parentSection, currentIndex) {
        if (!parentSection) {
            return currentIndex.toString();
        }
        return `${parentSection.numeroSection}.${currentIndex}`;
    }

    const currentLevelSections = sections
        .filter(s => {
            if (parentId == null) {
                return !s.idSection_parentF;
            }
            return s.idSection_parentF == parentId;
        })
        .sort((a, b) => {
            const aNumbers = a.numeroSection.split('.').map(Number);
            const bNumbers = b.numeroSection.split('.').map(Number);

            for (let i = 0; i < Math.max(aNumbers.length, bNumbers.length); i++) {
                if (aNumbers[i] != bNumbers[i]) {
                    return aNumbers[i] - bNumbers[i];
                }
            }
            return 0;
        });

    let html = '';
    const padding = level * 20;

    currentLevelSections.forEach((section) => {
        const parentSection = parentId ? sections.find(s => s.idSection == parentId) : null;
        const hasSubsections = sections.some(s => s.idSection_parentF == section.idSection);

        html += `
            <div class="section-item" data-section-id="${section.idSection}" 
                 style="padding-left: ${padding}px;" 
                 onclick="selectSection(${section.idSection})">
                <div class="d-flex align-items-center">
                    ${hasSubsections ? `
                        <div class="section-toggle mr-2" onclick="toggleSection(event, this)" 
                             title="Plier/Déplier">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    ` : `<div class="mr-4"></div>`}
                    <div class="section-number mr-2">${section.numeroSection}</div>
                    <div class="section-title flex-grow-1">${section.titreSection}</div>
                    <div class="section-actions">
                       
                        <button class="btn btn-sm btn-link" 
                                onclick="event.stopPropagation(); addSubSection(${section.idSection})"
                                title="Ajouter une sous-section">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-link text-danger" 
                                onclick="event.stopPropagation(); deleteSection(${section.idSection})"
                                title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                ${hasSubsections ? `
                    <div class="subsections">
                        ${displaySections(sections, section.idSection, level + 1)}
                    </div>
                ` : ''}
            </div>
        `;
    });

    if (level == 0) {
        $('#sections-list').html(`
            <div class="sections-container">
                ${html || '<p class="text-muted text-center p-3">Aucune section</p>'}
            </div>
        `);
    }

    return html;
}

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


// Fonction pour enregistrer toutes les modifications
// function saveAllSections() {
//     const updatedSections = [];
//     const processedIds = new Set();

//     $('.section-title-input, .section-content-input').each(function () {
//         const sectionId = parseInt($(this).data('section-id'));

//         if (!processedIds.has(sectionId)) {
//             const section = mockData.sections.find(s => s.idSection == sectionId);

//             if (section) {
//                 const titleInput = $(`.section-title-input[data-section-id="${sectionId}"]`);
//                 const contentEditor = tinymce.get($(`.section-content-input[data-section-id="${sectionId}"]`).attr('id'));
//                 const content = contentEditor ? contentEditor.getContent() : '';

//                 updatedSections.push({
//                     idSection: sectionId,
//                     titreSection: titleInput.val().trim(),
//                     contenuSection: content,
//                     numeroSection: section.numeroSection
//                 });

//                 processedIds.add(sectionId);
//             }
//         }
//     });

//     console.log('Données à envoyer:', updatedSections);

//     const saveButton = $('.btn-primary:contains("Enregistrer")');
//     const originalText = saveButton.html();
//     saveButton.html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...');
//     saveButton.prop('disabled', true);

//     $.ajax({
//         url: CONFIG.routes.section.updateMultiple,
//         method: 'POST',
//         contentType: 'application/json',
//         data: JSON.stringify({
//             sections: updatedSections
//         }),
//         success: function (response) {
//             console.log('Réponse du serveur:', response);
//             if (response.success) {
//                 $('#saveConfirmationModal').modal('show');
//                 loadSectionsFromDatabase();
//             } else {
//                 $('#errorModal').find('.modal-body').text(response.error || 'Erreur inconnue');
//                 $('#errorModal').modal('show');
//             }
//         },
//         error: function (xhr, status, error) {s
//             console.error('Erreur AJAX:', {
//                 status: status,
//                 error: error,
//                 response: xhr.responseText
//             });
//             $('#errorModal').find('.modal-body').text('Erreur lors de l\'enregistrement des modifications: ' + error);
//             $('#errorModal').modal('show');
//         },
//         complete: function () {
//             saveButton.html(originalText);
//             saveButton.prop('disabled', false);
//         }
//     });
// }


// Fonction pour supprimer une section
function deleteSection(sectionId) {
    console.log('Tentative de suppression de la section:', sectionId);
    console.log('État actuel des sections:', mockData.sections);

    // Si la section actuellement active est celle à supprimer, on la supprime
    if (sectionId == localStorage.getItem('activeSection')) {
        console.log('Suppression de la section active du localStorage');
        localStorage.removeItem('activeSection');
    }

    $('#deleteSectionModal')
        .data('sectionId', sectionId)
        .modal('show');
}

// Fonction pour confirmer la suppression
$('#confirmDeleteBtn').click(function () {
    const sectionId = $('#deleteSectionModal').data('sectionId');

    // Logs avant la suppression
    console.log('== DÉBUT SUPPRESSION ==');
    console.log('Tentative de suppression section ID:', sectionId);
    console.log('Section à supprimer:', mockData.sections.find(s => s.idSection == sectionId));
    console.log('URL qui sera appelée:', CONFIG.routes.section.delete + '/' + sectionId);
    console.log('État actuel mockData:', JSON.stringify(mockData.sections, null, 2));

    $('#deleteSectionModal').modal('hide');

    $.ajax({
        url: CONFIG.routes.section.delete + '/' + sectionId,
        method: 'POST',
        dataType: 'json',
        beforeSend: function () {
            console.log('== ENVOI REQUÊTE ==');
            console.log('Envoi requête DELETE pour section:', sectionId);
        },
        success: function (response) {
            console.log('== RÉPONSE SERVEUR ==');
            console.log('Réponse brute du serveur:', response);
            console.log('Type de la réponse:', typeof response);
            console.log('URL appelée:', CONFIG.routes.section.delete + '/' + sectionId);

            if (!response || response.success) {
                console.log('== SUPPRESSION LOCALE ==');

                function removeSection(sections, id) {
                    console.log('Début removeSection pour ID:', id);
                    const subsToRemove = sections.filter(s => s.idSection_parentF == id);
                    console.log('Sous-sections trouvées:', subsToRemove);

                    subsToRemove.forEach(sub => {
                        console.log('Traitement sous-section:', sub.idSection);
                        removeSection(sections, sub.idSection);
                    });

                    const index = sections.findIndex(s => s.idSection == id);
                    console.log('Index trouvé pour la section:', index);

                    if (index != -1) {
                        console.log('Suppression de la section à l\'index:', index);
                        sections.splice(index, 1);
                    }
                }

                removeSection(mockData.sections, sectionId);
                console.log('État mockData après removeSection:', mockData.sections);

                // Mise à jour des numéros
                console.log('Mise à jour des numéros...');
                mockData.sections = updateAllSectionNumbers(mockData.sections);
                console.log('Nouveaux numéros:', mockData.sections);

                // Mise à jour de l'affichage
                console.log('Mise à jour de l\'affichage...');
                displaySectionsTree(mockData.sections);

                // Nettoyage UI si nécessaire
                if ($('#section-content').find(`[data-section-id="${sectionId}"]`).length) {
                    console.log('Nettoyage du contenu dans l\'UI');
                    $('#section-content').empty();
                }

                console.log('== FIN SUPPRESSION RÉUSSIE ==');
            } else {
                console.log('== ERREUR SUPPRESSION ==');
                console.error('Détails de l\'erreur:', {
                    response: response,
                    error: response.error,
                    sectionId: sectionId,
                    success: response.success
                });
                $('#errorModal').find('.modal-body').text(response.error || 'Une erreur est survenue lors de la suppression');
                $('#errorModal').modal('show');
            }
        },
        error: function (xhr, status, error) {
            console.log('== ERREUR AJAX ==');
            console.error('Détails erreur AJAX:', {
                xhr: xhr,
                status: status,
                error: error,
                responseText: xhr.responseText,
                readyState: xhr.readyState,
                statusText: xhr.statusText,
                url: CONFIG.routes.section.delete + '/' + sectionId
            });

            let errorMessage = 'Une erreur est survenue lors de la suppression';
            if (xhr.responseText) {
                try {
                    console.log('Tentative de parse de la réponse erreur');
                    const response = JSON.parse(xhr.responseText);
                    console.log('Réponse erreur parsée:', response);
                    if (response.error) errorMessage = response.error;
                } catch (e) {
                    console.error("Erreur parsing JSON réponse:", e);
                }
            }
            $('#errorModal').find('.modal-body').text(errorMessage);
            $('#errorModal').modal('show');
        }
    });
});

// Fonction pour recalculer tous les numéros de sections
function updateAllSectionNumbers(sections) {
    // Fonction récursive pour mettre à jour les numéros
    function updateNumbers(parentId) {
        // Récupérer toutes les sections du même niveau
        const levelSections = sections.filter(s =>
            (!parentId && !s.idSection_parentF) ||
            (s.idSection_parentF == parentId)
        );

        // Mettre à jour les numéros pour ce niveau
        levelSections.forEach((section, index) => {
            if (!parentId) {
                // Sections principales
                section.numeroSection = (index + 1).toString();
            } else {
                // Sous-sections
                const parentSection = sections.find(s => s.idSection == parentId);
                section.numeroSection = `${parentSection.numeroSection}.${index + 1}`;
            }

            // Récursivement mettre à jour les sous-sections
            updateNumbers(section.idSection);
        });
    }

    // Commencer par le niveau racine
    updateNumbers(null);
    return sections;
}

// Fonction pour mettre à jour les numéros des sous-sections
function updateSubSectionNumbers(parentSection, allSections) {
    const subSections = allSections.filter(s => s.idSection_parentF == parentSection.idSection);
    subSections.sort((a, b) => {
        const aNumbers = a.numeroSection.split('.').map(Number);
        const bNumbers = b.numeroSection.split('.').map(Number);
        for (let i = 0; i < Math.max(aNumbers.length, bNumbers.length); i++) {
            if (aNumbers[i] != bNumbers[i]) {
                return (aNumbers[i] || 0) - (bNumbers[i] || 0);
            }
        }
        return 0;
    });

    subSections.forEach((section, index) => {
        section.numeroSection = `${parentSection.numeroSection}.${index + 1}`;
        // Récursion pour les sous-sections plus profondes
        updateSubSectionNumbers(section, allSections);
    });
}

// Initialisation du drag and drop dans displaySectionsTree
function initializeDragAndDrop() {
    $('.section-item').attr('draggable', true)
        .on('dragstart', function (e) {
            e.stopPropagation();
            e.originalEvent.dataTransfer.setData('application/x-section-id', $(this).data('section-id'));
            $(this).addClass('dragging');
        })
        .on('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const rect = this.getBoundingClientRect();
            const midY = (rect.bottom + rect.top) / 2;
            const mouseY = e.clientY;
            const threshold = 10; // Zone en pixels pour détecter le drop à l'intérieur

            $(this).removeClass('drag-over-top drag-over-bottom drag-over-inside');

            if (Math.abs(mouseY - midY) <= threshold) {
                // Si on est proche du milieu, c'est un drop à l'intérieur
                $(this).addClass('drag-over-inside');
            } else if (mouseY < midY) {
                // Au-dessus du milieu
                $(this).addClass('drag-over-top');
            } else {
                // En-dessous du milieu
                $(this).addClass('drag-over-bottom');
            }
        })
        .on('dragleave', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over-top drag-over-bottom drag-over-inside');
        })
        .on('drop', async function (e) {
            e.preventDefault();
            e.stopPropagation();

            const draggedId = e.originalEvent.dataTransfer.getData('application/x-section-id');
            const targetId = $(this).data('section-id');

            if (!draggedId || draggedId == targetId) {
                $(this).removeClass('drag-over-top drag-over-bottom drag-over-inside');
                return;
            }

            const rect = this.getBoundingClientRect();
            const midY = (rect.bottom + rect.top) / 2;
            const mouseY = e.clientY;
            const threshold = 10;

            let dropType = 'after';
            if (Math.abs(mouseY - midY) <= threshold) {
                dropType = 'inside';
            } else if (mouseY < midY) {
                dropType = 'before';
            }

            if (await canMoveSection(draggedId, targetId)) {
                await moveSection(draggedId, targetId, dropType);
            }

            $(this).removeClass('drag-over-top drag-over-bottom drag-over-inside');
        });

    // Zone de drop racine pour le niveau principal
    $('.root-drop-zone').on('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-over');
    })
        .on('dragleave', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
        })
        .on('drop', async function (e) {
            e.preventDefault();
            e.stopPropagation();

            const draggedId = e.originalEvent.dataTransfer.getData('application/x-section-id');
            if (draggedId) {
                await moveSection(draggedId, null, 'root');
            }
            $(this).removeClass('drag-over');
        });
}
// Rendre les éléments draggable
function makeItemsDraggable() {
    $('.section-item').attr('draggable', true);
}

// Vérifier si le déplacement est possible
function canMoveSection(draggedId, targetId) {
    // Vérification des IDs
    if (!draggedId || !targetId) {
        console.error('IDs invalides');
        return false;
    }

    // Récupération des sections
    const draggedSection = mockData.sections.find(s => s.idSection == draggedId);
    const targetSection = mockData.sections.find(s => s.idSection == targetId);

    // Vérifications de base
    if (!draggedSection || !targetSection) {
        console.error('Sections non trouvées');
        return false;
    }

    // Éviter de déplacer une section vers elle-même
    if (draggedId == targetId) {
        return false;
    }

    // Éviter de déplacer une section parent vers un de ses enfants
    let currentParent = targetSection;
    while (currentParent) {
        if (currentParent.idSection == draggedId) {
            return false;
        }
        currentParent = mockData.sections.find(s => s.idSection == currentParent.idSection_parentF);
    }

    return true;
}

// Vérifier si une section est parent d'une autre
function isParentOf(potentialParent, child, sections) {
    if (!child || !potentialParent) return false;

    let current = child;
    while (current.idSection_parentF) {
        if (current.idSection_parentF == potentialParent.idSection) {
            return true;
        }
        current = sections.find(s => s.idSection == current.idSection_parentF);
    }
    return false;
}

// Déplacer une section
async function moveSection(draggedId, targetId, dropType) {
    if (!draggedId) {
        console.error('ID de section manquant');
        return;
    }

    const draggedSection = mockData.sections.find(s => s.idSection == draggedId);
    const targetSection = targetId ? mockData.sections.find(s => s.idSection == targetId) : null;

    if (!draggedSection) {
        console.error('Section source non trouvée');
        return;
    }

    // Sauvegarder l'ancien état
    const oldParentId = draggedSection.idSection_parentF;
    const oldNumeroSection = draggedSection.numeroSection;

    try {
        // Gérer les différents types de déplacement
        switch (dropType) {
            case 'inside':
                // Déplacer comme sous-section
                draggedSection.idSection_parentF = targetId;
                break;

            case 'root':
                // Déplacer au niveau racine
                draggedSection.idSection_parentF = null;
                break;

            case 'before':
            case 'after':
                // Conserver le même parent que la cible
                draggedSection.idSection_parentF = targetSection.idSection_parentF;

                // Retirer la section de sa position actuelle
                const sections = mockData.sections.filter(s => s.idSection != draggedId);

                // Trouver l'index d'insertion
                const targetIndex = sections.findIndex(s => s.idSection == targetId);
                const insertIndex = dropType == 'before' ? targetIndex : targetIndex + 1;

                // Insérer à la nouvelle position
                sections.splice(insertIndex, 0, draggedSection);
                mockData.sections = sections;
                break;
        }

        // Mettre à jour les numéros
        mockData.sections = updateAllSectionNumbers(mockData.sections);

        // Préparer les données pour la mise à jour
        const updateData = {
            sections: mockData.sections.map(section => ({
                idSection: section.idSection,
                idSection_parentF: section.idSection_parentF,
                numeroSection: section.numeroSection
            }))
        };

        console.log('Données envoyées au serveur:', updateData);

        const response = await $.ajax({
            url: CONFIG.routes.section.updateSectionOrder,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(updateData)
        });

        if (!response.success) {
            throw new Error(response.error || 'Erreur lors de la mise à jour');
        }

        displaySectionsTree(mockData.sections);
        // initializeDragAndDrop();

    } catch (error) {
        console.error('Erreur lors du déplacement:', error);

        // Restaurer l'état précédent
        draggedSection.idSection_parentF = oldParentId;
        draggedSection.numeroSection = oldNumeroSection;
        mockData.sections = updateAllSectionNumbers(mockData.sections);

        displaySectionsTree(mockData.sections);
        // initializeDragAndDrop();

        alert('Erreur lors du déplacement de la section');
    }
}

// Styles CSS pour le drag and drop
// const styles = `
// .section-item {
//     cursor: move;
//     transition: background-color 0.2s ease;
// }

// .section-item.dragging {
//     opacity: 0.5;
// }

// .section-item.drag-over {
//     background-color: rgba(0, 123, 255, 0.1);
//     border: 1px dashed #007bff;
// }
// `;

// Ajouter les styles au document
const styleSheet = document.createElement('style');
// styleSheet.textContent = styles;
document.head.appendChild(styleSheet);



// Fonction utilitaire pour afficher les notifications
// function showNotification(type, message) {
//     const notifDiv = document.createElement('div');
//     notifDiv.className = `alert alert-${type == 'success' ? 'success' : 'danger'} position-fixed`;
//     notifDiv.style.top = '20px';
//     notifDiv.style.right = '20px';
//     notifDiv.style.zIndex = '9999';
//     notifDiv.innerHTML = message;

//     document.body.appendChild(notifDiv);

//     setTimeout(() => {
//         notifDiv.remove();
//     }, 3000);
// }