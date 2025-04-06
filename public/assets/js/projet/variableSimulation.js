const VariableSimulation = {
    // Gestionnaire du clic sur le bouton d'ajout de variable
    handleAddVariable(event) {
        const button = event.target.closest('.add-variable-btn');
        if (!button) return;

        const sectionId = button.dataset.sectionId;
        const lotIdsStr = button.dataset.lotIds;

        try {
            const lotIds = JSON.parse(lotIdsStr);
            console.log("IDs des lots récupérés:", lotIds);

            if (!Array.isArray(lotIds) || !lotIds.length) {
                this.showErrorMessage('Aucun lot trouvé');
                return;
            }

            this.showAddVariableModal(sectionId, lotIds);
        } catch (error) {
            console.error('Erreur lors de la récupération des IDs des lots:', error);
            this.showErrorMessage('Erreur lors de la récupération des lots');
        }
    },


    // Afficher le modal d'ajout de variable
    async showAddVariableModal(sectionId, lotIds) {
        if (!sectionId || !lotIds) {
            this.showErrorMessage('Informations manquantes pour créer le modal');
            return;
        }

        try {
            // Charger les variables disponibles
            const response = await $.ajax({
                url: CONFIG.routes.variableSimulation.getForSection,
                method: 'GET',
                data: {
                    sectionId: sectionId,
                    lotId: lotIds[0] // On utilise le premier lot pour récupérer les variables
                }
            });

            if (!response.success || !response.data) {
                throw new Error('Erreur lors du chargement des variables');
            }

            const variables = response.data;
            const variablesOptions = variables
                .filter(v => v.libelleVariable)
                .map(v => `<option value="${v.libelleVariable}">${v.nomVariableSimulation}</option>`)
                .join('');

            const modalHtml = `
                <div class="modal fade" id="addVariableModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Ajouter une nouvelle variable</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="addVariableForm">
                                    <div class="mb-3">
                                        <label for="nomVariable" class="form-label">Nom de la variable</label>
                                        <input type="text" class="form-control" id="nomVariable" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="libelleVariable" class="form-label">Libellé de la variable</label>
                                        <input type="text" class="form-control" id="libelleVariable">
                                    </div>
                                    <div class="mb-3">
                                        <label for="nomAfficher" class="form-label">Nom à afficher</label>
                                        <input type="text" class="form-control" id="nomAfficher">
                                    </div>
                                    <div class="mb-3">
                                        <label for="categorieVariable" class="form-label">Catégorie (optionnelle)</label>
                                        <select class="form-select" id="categorieVariable">
                                            <option value="">Sélectionner une catégorie</option>
                                            <option value="1">Coût opérationnel</option>
                                            <option value="2">Coût financier</option>
                                            <option value="3">Coût divers</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="typeValeur" class="form-label">Type de valeur</label>
                                        <select class="form-select" id="typeValeur" required>
                                            <option value="montant">Montant (€)</option>
                                            <option value="pourcentage">Pourcentage (%)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="valeurVariable" class="form-label">Valeur</label>
                                        <input type="number" class="form-control" id="valeurVariable" step="0.01" required>
                                    </div>
                                    <div class="mb-3">
                                    <label class="form-label">Formule coût total</label>
                                    <div class="d-flex gap-2 align-items-center flex-wrap">
                                        <select class="form-select" id="formulePart1" style="min-width: 0; flex: 1;">
                                            <option value="">Sélectionner une variable</option>
                                            ${variablesOptions}
                                        </select>
            
                                        <select class="form-select" id="formuleOperateur" style="width: 80px; flex-shrink: 0;">
                                            <option value="*">×</option>
                                            <option value="/">/</option>
                                            <option value="+">+</option>
                                            <option value="-">-</option>
                                        </select>
            
                                        <select class="form-select" id="formulePart2" style="min-width: 0; flex: 1;">
                                            <option value="">Sélectionner une variable</option>
                                            ${variablesOptions}
                                        </select>
                                    </div>
                                    <input type="hidden" id="formuleCoutTotal">
                                </div>
                                    <input type="hidden" id="sectionId" value="${sectionId}">
                                    <input type="hidden" id="lotIds" value='${JSON.stringify(lotIds)}'>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-primary" id="saveVariable">Enregistrer</button>
                            </div>
                        </div>
                    </div>
                </div>`;

            // Supprimer l'ancien modal s'il existe
            $('#addVariableModal').remove();

            // Ajouter le nouveau modal au DOM
            $('body').append(modalHtml);

            // Initialiser le modal Bootstrap
            const modal = new bootstrap.Modal(document.getElementById('addVariableModal'));

            // Ajouter la gestion de la formule
            const updateFormule = () => {
                const part1 = $('#formulePart1').val();
                const operateur = $('#formuleOperateur').val();
                const part2 = $('#formulePart2').val();

                if (part1 && operateur && part2) {
                    const formule = `${part1}${operateur}${part2}`;
                    console.log("Formule  " + formule);
                    $('#formuleCoutTotal').val(formule);
                } else {
                    $('#formuleCoutTotal').val('');
                }
            };

            // Écouter les changements des selects pour la formule
            $('#formulePart1, #formuleOperateur, #formulePart2').on('change', updateFormule);

            // Gérer la sauvegarde de la variable
            $('#saveVariable').on('click', () => {
                if (this.validateForm()) {
                    const formLotIds = JSON.parse($('#lotIds').val());
                    this.saveNewVariable(sectionId, formLotIds);
                    modal.hide();
                }
            });

            // Ajouter un gestionnaire pour la touche Entrée
            $('#addVariableForm').on('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $('#saveVariable').click();
                }
            });

            // Gestionnaire pour fermer le modal
            $('#addVariableModal').on('hidden.bs.modal', () => {
                $('#addVariableModal').remove();
            });

            // Focus sur le premier champ lors de l'ouverture
            $('#addVariableModal').on('shown.bs.modal', () => {
                $('#nomVariable').focus();
            });

            // Afficher le modal
            modal.show();

        } catch (error) {
            console.error('Erreur lors de l\'affichage du modal:', error);
            this.showErrorMessage('Erreur lors de l\'affichage du formulaire');
        }
    },

    // Valider le formulaire
    validateForm() {
        const form = document.getElementById('addVariableForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }
        return true;
    },

    // Sauvegarder la nouvelle variable
    saveNewVariable(sectionId, lotIds) {
        console.log("Début saveNewVariable avec:", { sectionId, lotIds });

        const saveBtn = $('#saveVariable');
        const montant = $('#valeurVariable').val();
        const nomVariable = $('#nomVariable').val();
        // const categorie = $('#categorieVariable').val();
        const typeValeur = $('#typeValeur').val();

        // Vérification des données
        if (!sectionId || !lotIds || !montant || !nomVariable || !typeValeur) {
            console.error("Données manquantes:", {
                sectionId, lotIds, montant, nomVariable, categorie, typeValeur
            });
            this.showErrorMessage('Tous les champs sont requis');
            return;
        }


        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        // Créer un tableau de promesses pour chaque lot

        const savePromises = lotIds.map(lotId => {
            const categorieValue = $('#categorieVariable').val();

            // Si la catégorie est null, toutes les formules seront null
            const formules = !categorieValue ? {
                formuleCoutTotal: null,
                formulePourcentageCoutTotal: null,
                formuleCoutIndividuel: null,
                formulePourcentageCoutIndividuel: null
            } : {
                formuleCoutTotal: $('#formuleCoutTotal').val() || null,
                formulePourcentageCoutTotal: 'coutTotal/totalCategorie*100',
                formuleCoutIndividuel: 'coutTotal/surface',
                formulePourcentageCoutIndividuel: 'coutTotal/prixReventeTotal*100'
            };

            const variableData = {
                sectionId: parseInt(sectionId),
                lotId: parseInt(lotId),
                montant: montant,
                variableId: 0,
                nomVariableSimulation: $('#nomVariable').val(),
                libelleVariable: $('#libelleVariable').val() || null,
                nomAfficher: $('#nomAfficher').val() || null,
                typeValeurSimulation: $('#typeValeur').val(),
                valeurVariableSimulation: montant,
                categorie: categorieValue === '' ? null : parseInt(categorieValue),
                aSaisir: 1,
                ...formules  // Spread les formules dans l'objet
            };

            console.log(`Envoi des données pour le lot ${lotId}:`, variableData);

            return new Promise((resolve, reject) => {
                $.ajax({
                    url: CONFIG.routes.variableSimulation.saveValue,
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(variableData),
                    success: response => {
                        console.log(`Réponse pour le lot ${lotId}:`, response);
                        if (response.success) {
                            resolve(response);
                        } else {
                            console.error(`Erreur serveur pour le lot ${lotId}:`, response);
                            reject(new Error(response.error || 'Erreur de sauvegarde'));
                        }
                    },
                    error: (xhr, status, error) => {
                        console.error(`Erreur réseau pour le lot ${lotId}:`, {
                            status,
                            error,
                            response: xhr.responseText
                        });
                        reject(new Error(error));
                    }
                });
            });
        });
        // Exécuter toutes les sauvegardes en parallèle
        Promise.all(savePromises)
            .then(results => {
                console.log('Toutes les sauvegardes réussies:', results);
                this.showSuccessMessage('Variable ajoutée avec succès pour tous les lots');
                // Recharger les données pour chaque lot
                lotIds.forEach(lotId => {
                    this.reloadLotData(sectionId, lotId);
                });
            })
            .catch(error => {
                console.error('Erreur détaillée lors de la sauvegarde:', error);
                this.showErrorMessage('Erreur lors de la sauvegarde: ' + error.message);
            })
            .finally(() => {
                // Réinitialiser le bouton
                setTimeout(() => {
                    saveBtn.prop('disabled', false)
                        .removeClass('btn-success btn-danger')
                        .addClass('btn-primary')
                        .html('Enregistrer');
                }, 1000);
            });
    },

    // Méthode pour recharger les données d'un lot
    reloadLotData(sectionId, lotId) {
        $.ajax({
            url: CONFIG.routes.variableSimulation.getForSection,
            method: 'GET',
            data: { sectionId, lotId },
            success: (response) => {
                if (response.success && response.data) {
                    const variables = response.data;
                    const simulationTableBody = $(`#simulationTable-${sectionId}-${lotId} tbody`);

                    if (simulationTableBody.length) {
                        const groupedVariables = variables.reduce((acc, v) => {
                            if (v.categorie) {
                                acc[v.categorie] = acc[v.categorie] || [];
                                acc[v.categorie].push(v);
                            }
                            return acc;
                        }, {});

                        simulationTableBody.html(`
                            ${generateSimulationRows(variables, 1, groupedVariables['1']?.map(v => v.idVariableSimulation) || [], "Total Coût opérationnel")}
                            ${generateSimulationRows(variables, 2, [7, 8], "Total Coût financier des obligataires sur 24 Mois")}
                            ${generateSimulationRows(variables, 3, [9], "Total Coût divers (Commission totale)")}
                            ${generateSimulationRows(variables, 4, [1, 2, 3], "Somme des coûts")}
                            ${generateSimulationRows(variables, 5, [1, 2], "Résultat final")}
                        `);
                    }
                    this.refreshVariableTable(sectionId, lotId, variables);
                }
            }
        });
    },

    // Méthode pour rafraîchir le tableau des variables
    refreshVariableTable(sectionId, lotId, variables) {
        const tableBody = $(`#variableTable-${sectionId}-${lotId} tbody`);
        if (tableBody.length) {
            variables.forEach(variable => {
                const input = tableBody.find(`input[data-variable-id="${variable.idVariableSimulation}"]`);
                if (input.length) {
                    input.val(variable.montant || variable.valeurVariableSimulation || '');
                }
            });
        }
    },

    // Afficher un message de succès
    showSuccessMessage(message) {
        const alert = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ${message}
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>`;
        this.showMessage(alert);
    },

    // Afficher un message d'erreur
    showErrorMessage(message) {
        const alert = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        this.showMessage(alert);
    },

    // Afficher un message
    showMessage(alertHtml) {
        const messageContainer = document.getElementById('message-container') ||
            (() => {
                const container = document.createElement('div');
                container.id = 'message-container';
                container.style.position = 'fixed';
                container.style.top = '20px';
                container.style.right = '20px';
                container.style.zIndex = '9999';
                document.body.appendChild(container);
                return container;
            })();

        $(messageContainer).append(alertHtml);
        setTimeout(() => {
            $('.alert').fadeOut('slow', function () {
                $(this).remove();
            });
        }, 5000);
    }
};

// Exporter l'objet VariableSimulation
window.VariableSimulation = VariableSimulation;

//écouteur d'événements une fois que le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    // Utiliser les événements pour gérer les clics sur le bouton d'ajout
    document.body.addEventListener('click', (event) => {
        if (event.target.closest('.add-variable-btn')) {
            VariableSimulation.handleAddVariable(event);
        }
    });
});