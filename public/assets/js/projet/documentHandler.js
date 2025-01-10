// Gestionnaire de documents pour les sections
const DocumentHandler = (function () {

    // Variables d'état
    let state = {
        selectedFile: null,
        selectedFiles: [],  // Ajout pour la sélection multiple
        selectedFileType: null, // RYM ou Local
        activeSectionId: null
    };

    // Initialisation
    function init() {
        bindEvents();
    }

    // Liaison des événements
    function bindEvents() {
        // Gestionnaire pour le fichier local
        $('#localFileInput').on('change', handleLocalFileSelection);

        // Gestionnaire pour le changement d'onglet
        $('#ryuFileTab').on('shown.bs.tab', loadRyuFiles);

        // Gestionnaire pour la validation
        $('#validateFileSelection').on('click', handleFileValidation);
    }

    // Ouvre le modal de sélection de fichier
    function openFileModal(sectionId) {
        state.activeSectionId = sectionId;
        resetSelection();
        $('#fileSelectionModal').modal('show');
    }

    // Réinitialise la sélection
    function resetSelection() {
        state.selectedFile = null;
        state.selectedFiles = [];
        state.selectedFileType = null;
        $('#validateFileSelection').prop('disabled', true);
        $('#localFileInput').val('');
        $('.document-checkbox').prop('checked', false);
        $('#selectedCount').text('0 document(s) sélectionné(s)');

        // Réinitialiser DataTable si elle existe
        if ($.fn.DataTable.isDataTable('#documentsTable')) {
            $('#documentsTable').DataTable().clear().draw();
        }

        $('#ryuLoadingSpinner').addClass('d-none');
        $('#ryuEmptyState').addClass('d-none');
    }

    // Charger les fichiers RYM
    function loadRyuFiles() {
        $('#ryuLoadingSpinner').removeClass('d-none');
        $('#ryuEmptyState').addClass('d-none');
        $('#ryuFileResults').empty();

        $.ajax({
            url: CONFIG.routes.sectionDocument.getAllDocuments,
            method: 'GET',
            success: function (response) {
                $('#ryuLoadingSpinner').addClass('d-none');

                if (!response.documents || response.documents.length == 0) {
                    $('#ryuEmptyState').removeClass('d-none');
                    return;
                }
                displayDocuments(response.documents);
            },
            error: function () {
                $('#ryuLoadingSpinner').addClass('d-none');
                $('#ryuEmptyState').removeClass('d-none')
                    .html('<div class="text-danger">Erreur lors du chargement des documents</div>');
            }
        });
    }

    // Afficher les documents dans le tableau
    // function displayDocuments(documents) {
    //     if ($.fn.DataTable.isDataTable('#documentsTable')) {
    //         $('#documentsTable').DataTable().destroy();
    //     }

    //     const html = documents.map(doc => `
    //         <tr>
    //             <td>${doc.titre}</td>
    //             <td><span class="badge badge-secondary">${doc.type.toUpperCase()}</span></td>
    //             <td>${new Date(doc.dateCreation).toLocaleDateString()}</td>
    //             <td>
    //                 <button class="btn btn-sm btn-outline-primary select-rym-file" 
    //                         data-id="${doc.id}" 
    //                         data-name="${doc.titre}">
    //                     <i class="fas fa-check mr-1"></i>Sélectionner
    //                 </button>
    //             </td>
    //         </tr>
    //     `).join('');

    //     $('#ryuFileResults').html(html);

    //     // Initialiser DataTable
    //     const dataTable = $('#documentsTable').DataTable({
    //         language: {
    //             url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
    //         },
    //         order: [[2, 'desc']], // Trier par date décroissante
    //         pageLength: 10,       // Nombre d'éléments par page
    //         responsive: true,
    //         columns: [
    //             { width: "40%" },  // Nom du fichier
    //             { width: "15%" },  // Type
    //             { width: "25%" },  // Date
    //             { width: "20%" }   // Action
    //         ]
    //     });

    //     // Ajouter les gestionnaires d'événements pour la sélection
    //     $('.select-rym-file').on('click', function (e) {
    //         e.preventDefault();
    //         const button = $(this);
    //         selectRYMFile(button.data('id'), button.data('name'));

    //         // Mise à jour visuelle
    //         $('.select-rym-file').removeClass('btn-primary').addClass('btn-outline-primary');
    //         button.removeClass('btn-outline-primary').addClass('btn-primary');

    //         $('tr').removeClass('table-active');
    //         button.closest('tr').addClass('table-active');
    //     });
    // }


    // Afficher les documents dans le tableau apres l'ouverture du modal
    function displayDocuments(documents) {
        if ($.fn.DataTable.isDataTable('#documentsTable')) {
            $('#documentsTable').DataTable().destroy();
        }

        const actionBar = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <button class="btn btn-sm btn-outline-secondary" id="selectAllDocs">
                        <i class="fas fa-check-square mr-1"></i>Tout sélectionner
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="deselectAllDocs">
                        <i class="fas fa-square mr-1"></i>Tout désélectionner
                    </button>
                </div>
                <div>
                    <span class="badge badge-info" id="selectedCount">0 document(s) sélectionné(s)</span>
                </div>
            </div>
        `;

        $('#ryuFileContent .table-responsive').before(actionBar);

        // Ajoutez les gestionnaires d'événements
        $('#selectAllDocs').on('click', function () {
            $('.document-checkbox').prop('checked', true);
            updateSelectedDocuments();
        });

        $('#deselectAllDocs').on('click', function () {
            $('.document-checkbox').prop('checked', false);
            updateSelectedDocuments();
        });

        const html = documents.map(doc => `
            <tr>
                <td>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input document-checkbox" 
                               id="doc-${doc.id}" data-id="${doc.id}" data-name="${doc.titre}">
                        <label class="custom-control-label" for="doc-${doc.id}">
                            ${doc.titre}
                        </label>
                    </div>
                </td>
                <td><span class="badge badge-secondary">${doc.type.toUpperCase()}</span></td>
                <td>${new Date(doc.dateCreation).toLocaleDateString()}</td>
                <td >
                   ${doc.auteur ? doc.auteur : '-'}
                </td>
            </tr>
        `).join('');

        $('#ryuFileResults').html(html);

        // Initialiser DataTable
        const dataTable = $('#documentsTable').DataTable({

            order: [[2, 'desc']],
            pageLength: 10,
            responsive: true,
            columns: [
                { width: "40%" },
                { width: "15%" },
                { width: "25%" },
                { width: "20%" }
            ]
        });

        // Gestionnaire pour les checkboxes
        $('.document-checkbox').on('change', function () {
            updateSelectedDocuments();
        });
    }

    // Gestion de  la sélection multiple
    function updateSelectedDocuments() {
        const selectedDocs = [];
        $('.document-checkbox:checked').each(function () {
            const $checkbox = $(this);
            selectedDocs.push({
                id: $checkbox.data('id'),
                name: $checkbox.data('name')
            });
        });

        state.selectedFiles = selectedDocs;
        const count = selectedDocs.length;

        // Mettre à jour l'interface
        $('#validateFileSelection').prop('disabled', count === 0);
        $('#selectedCount').text(`${count} document(s) sélectionné(s)`);
    }

    // Gestion de la sélection de fichier local
    function handleLocalFileSelection(e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            state.selectedFiles = Array.from(files).map(file => ({
                id: null,
                name: file.name,
                file: file
            }));
            state.selectedFileType = 'local';
            $('#validateFileSelection').prop('disabled', false);
            $('#selectedCount').text(`${state.selectedFiles.length} document(s) sélectionné(s)`);
        } else {
            state.selectedFiles = [];
            state.selectedFileType = null;
            $('#validateFileSelection').prop('disabled', true);
            $('#selectedCount').text('0 document(s) sélectionné(s)');
        }
    }

    // Sélection d'un fichier RYM
    function selectRYMFile(fileId, fileName) {
        state.selectedFile = { id: fileId, name: fileName };
        state.selectedFileType = 'RYM';
        $('#validateFileSelection').prop('disabled', false);
    }

    // Validation de la sélection
    // async function handleFileValidation() {
    //     if (!state.selectedFile || !state.selectedFileType || !state.activeSectionId) {
    //         showError('Veuillez sélectionner un fichier');
    //         return;
    //     }

    //     // Désactiver le bouton pendant le traitement
    //     $('#validateFileSelection')
    //         .prop('disabled', true)
    //         .html('<i class="fas fa-spinner fa-spin"></i> Traitement en cours...');

    //     try {
    //         let result;
    //         if (state.selectedFileType === 'local') {
    //             result = await uploadLocalFile();
    //         } else {
    //             result = await linkRYMFile();
    //         }

    //         if (result.success) {
    //             // Fermer le modal
    //             $('#fileSelectionModal').modal('hide');
    //             showSuccess(result.message);

    //             // Rafraîchir la liste des documents si nécessaire
    //             // Vous pouvez ajouter ici une fonction pour mettre à jour l'affichage
    //             reloadSectionDocuments(state.activeSectionId);
    //         } else {
    //             showError(result.error || 'Une erreur est survenue');
    //         }
    //     } catch (error) {
    //         showError('Une erreur est survenue lors du traitement');
    //         console.error('Error:', error);
    //     } finally {
    //         // Réactiver le bouton
    //         $('#validateFileSelection')
    //             .prop('disabled', false)
    //             .html('Valider la sélection');
    //     }
    // }



    // Validation de la sélection
    async function handleFileValidation() {
        if (!state.selectedFiles || state.selectedFiles.length == 0 || !state.activeSectionId) {
            showError('Veuillez sélectionner au moins un document');
            return;
        }

        $('#validateFileSelection')
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Traitement en cours...');

        try {
            let results = [];
            if (state.selectedFileType === 'local') {
                // Traiter les fichiers locaux
                for (const fileData of state.selectedFiles) {
                    try {
                        const result = await uploadLocalFile(fileData.file);
                        results.push({
                            success: true,
                            message: `Document "${fileData.name}" uploadé avec succès`
                        });
                    } catch (error) {
                        results.push({
                            success: false,
                            error: `Erreur lors de l'upload de "${fileData.name}"`
                        });
                    }
                }
            } else {
                // Traiter les fichiers RYM
                results = await Promise.all(state.selectedFiles.map(file => linkRYMFile(file)));
            }

            const successCount = results.filter(r => r.success).length;
            const failureCount = results.filter(r => !r.success).length;

            if (failureCount == 0) {
                // Fermer le modal et rafraîchir les documents sans afficher de message
                $('#fileSelectionModal').modal('hide');
                reloadSectionDocuments(state.activeSectionId);
            } else {
                // En cas d'erreur, afficher uniquement les erreurs
                if (failureCount > 0) {
                    showError(`Erreur: ${failureCount} document(s) n'ont pas pu être ajoutés`);
                }
                if (successCount > 0) {
                    reloadSectionDocuments(state.activeSectionId);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Une erreur est survenue lors du traitement');
        } finally {
            $('#validateFileSelection')
                .prop('disabled', false)
                .html('Valider la sélection');
        }
    }
    // Upload d'un fichier local
    async function uploadLocalFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('sectionId', state.activeSectionId);

        try {
            const response = await $.ajax({
                url: CONFIG.routes.sectionDocument.uploadDocument,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false
            });

            return response;
        } catch (error) {
            console.error('Upload error:', error);
            throw error;
        }
    }

    // Liaison d'un fichier RYM
    async function linkRYMFile(fileData) {
        try {
            if (!fileData || !fileData.id) {
                throw new Error('Données du fichier invalides');
            }

            const response = await $.ajax({
                url: CONFIG.routes.sectionDocument.linkDocument,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    sectionId: state.activeSectionId,
                    documentId: fileData.id
                })
            });
            return {
                success: true,
                message: `Document "${fileData.name}" ajouté avec succès`
            };
        } catch (error) {
            console.error('Link error:', error);
            return {
                success: false,
                error: `Erreur lors de l'ajout du document "${fileData?.name || 'inconnu'}"`
            };
        }
    }

    // Recharger les documents d'une section
    async function reloadSectionDocuments(sectionId) {
        try {
            const response = await $.ajax({
                url: `${CONFIG.routes.sectionDocument.getDocuments}/${sectionId}`,
                method: 'GET'
            });

            if (response.success) {
                // Mettre à jour l'interface utilisateur avec les nouveaux documents
                updateSectionDocumentsUI(response.documents);
            }
        } catch (error) {
            console.error('Error reloading documents:', error);
        }
    }


    // Mettre à jour l'interface utilisateur des documents
    function updateSectionDocumentsUI(documents) {
        const documentsContainer = $('#section-documents-list');

        if (documents.length > 0) {
            const html = documents.map(doc => `
                <div class="document-item list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-file mr-2"></i>
                        <span>${doc.nom}</span>
                        <small class="text-muted ml-2">${new Date(doc.dateCreation).toLocaleDateString()}</small>
                    </div>
                    <div>
                        <a href="${CONFIG.URLROOT}/projet/annexe/${doc.url}" 
                           class="btn btn-sm btn-outline-primary" 
                           target="_blank">
                            <i class="fas fa-eye mr-1"></i>Visualiser
                        </a>
                    </div>
                </div>
            `).join('');

            documentsContainer.html(`
                <div class="list-group">
                    ${html}
                </div>
            `);
        } else {
            documentsContainer.html(`
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    Aucun document associé à cette section
                </div>
            `);
        }
    }

    // Messages
    function showSuccess(message) {
        // Vous pouvez personnaliser l'affichage des messages de succès
        if (window.Toastify) {
            Toastify({
                text: message,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28a745"
            }).showToast();
        } else {
            alert(message);
        }
    }

    function showError(message) {
        // Vous pouvez personnaliser l'affichage des messages d'erreur
        if (window.Toastify) {
            Toastify({
                text: message,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#dc3545"
            }).showToast();
        } else {
            alert('Erreur: ' + message);
        }
    }

    // API publique
    return {
        init: init,
        openFileModal: openFileModal,
        loadSectionDocuments: loadSectionDocuments
    };
})();


// function loadSectionDocuments(sectionId) {
//     $('#section-documents-list').html(`
//         <div class="text-center py-3">
//             <i class="fas fa-spinner fa-spin fa-2x"></i>
//             <p class="text-muted">Chargement des documents...</p>
//         </div>
//     `);

//     $.ajax({
//         url: CONFIG.routes.sectionDocument.getDocuments + '/' + sectionId,
//         method: 'GET',
//         success: function (response) {
//             if (response.success && response.documents.length > 0) {
//                 const documentsHtml = response.documents.map(doc => `
//                     <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
//                         <div>
//                             <i class="fas fa-file mr-2"></i>
//                             <span class="font-weight-bold">${doc.nom}</span>
//                             <small class="text-muted ml-2">
//                                 ${new Date(doc.dateCreation).toLocaleDateString()}
//                             </small>
//                         </div>
//                         <div>
//                             <a href="${CONFIG.URLROOT}/projet/annexe/${doc.url}" 
//                                class="btn btn-sm btn-outline-primary" 
//                                target="_blank">
//                                 <i class="fas fa-eye mr-1"></i>Visualiser
//                             </a>
//                         </div>
//                     </div>
//                 `).join('');

//                 $('#section-documents-list').html(`
//                     <div class="list-group">
//                         ${documentsHtml}
//                     </div>
//                 `);
//             } else {
//                 $('#section-documents-list').html(`
//                     <div class="alert alert-info">
//                         <i class="fas fa-info-circle mr-2"></i>
//                         Aucun document associé à cette section
//                     </div>
//                 `);
//             }
//         },
//         error: function () {
//             $('#section-documents-list').html(`
//                 <div class="alert alert-danger">
//                     <i class="fas fa-exclamation-triangle mr-2"></i>
//                     Erreur lors du chargement des documents
//                 </div>
//             `);
//         }
//     });
// }


// Initialisation du DataTable pour les documents
let documentsTable;

function loadSectionDocuments(sectionId) {
    console.log("ok " + sectionId)
    // Afficher le loader
    $('#section-documents-list').html(`
        <div class="text-center py-3">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="text-muted">Chargement des documents...</p>
        </div>
    `);

    // Préparer la structure du tableau
    $('#section-documents-list').html(`
        <table id="documents-table" class="table table-hover table-striped w-100">
            <thead class="thead-light">
                <tr>
                    <th>Nom du fichier</th>
                    <th>Type</th>
                    <th>Date de création</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    `);
    // $.ajax({
    //     url: CONFIG.routes.sectionDocument.getDocuments + '/' + sectionId,
    //     type: 'GET',
    //     dataType: 'json',
    //     beforeSend: function () { },
    //     success: function (response) {
    //         console.log("success");
    //         console.log(response);
    //         // $("#titre").text("Historique : " + user.trim());
    //         // $('#tabledata').DataTable({
    //         //     "Processing": true, // for show progress bar
    //         //     "serverSide": false, // for process server side
    //         //     "filter": true, // this is for disable filter (search box)
    //         //     "orderMulti": true, // for disable multiple column at once
    //         //     "bDestroy": true,
    //         //     'iDisplayLength': 100,
    //         //     "data": response,
    //         //     "columns": [{
    //         //             "data": "index"
    //         //         },
    //         //         {
    //         //             "data": "action"
    //         //         },
    //         //         {
    //         //             "data": "dateAction"
    //         //         }
    //         //     ]
    //         // });
    //     },
    //     error: function (response) {
    //         console.log(response);
    //     },
    //     complete: function () {
    //         $("#loadingModal").modal("hide");
    //     },
    // });

    // return;
    // Initialiser DataTables
    documentsTable = $('#documents-table').DataTable({
        ajax: {
            url: CONFIG.routes.sectionDocument.getDocuments + '/' + sectionId,
            dataSrc: function (response) {
                console.log(response.success);
                return response.success && response.documents ? response.documents : [];
            }
        },
        columns: [
            {
                data: 'nom',
                render: function (data) {
                    return `<i class="fas fa-file mr-2"></i>${data}`;
                }
            },
            {
                data: 'url',
                render: function (data) {
                    const extension = data.split('.').pop().toUpperCase();
                    return `<span class="badge badge-secondary">${extension}</span>`;
                }
            },
            {
                data: 'dateCreation',
                render: function (data) {
                    return new Date(data).toLocaleDateString();
                }
            },
            {
                data: 'url',
                render: function (data) {
                    return `
                        <a href="${CONFIG.URLROOT}/projet/annexe/${data}" 
                           class="btn btn-sm btn-outline-danger" 
                           target="_blank">
                            <i class="fas fa-eye mr-1"></i>Visualiser
                        </a>
                    `;
                }
            }
        ],
        responsive: true,
        pageLength: 10,
        ordering: true,
        searching: true,
        lengthChange: true,
        info: true,
        autoWidth: false
    });

    // Gérer les erreurs
    $('#documents-table').on('error.dt', function (e, settings, techNote, message) {
        $('#section-documents-list').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Erreur lors du chargement des documents
            </div>
        `);
    });

    // Gérer l'absence de données
    $('#documents-table').on('xhr.dt', function (e, settings, json) {
        if (!json || !json.success || json.documents.length == 0) {
            $('#section-documents-list').html(`
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    Aucun document associé à cette section
                </div>
            `);
        }
    });
}

// Fonction pour détruire le DataTable (à appeler si nécessaire)
function destroyDocumentsTable() {
    if (documentsTable) {
        documentsTable.destroy();
        documentsTable = null;
    }
}

// Initialisation au chargement du document
$(document).ready(function () {
    DocumentHandler.init();
});