

function toggleVariables(identifier) {
    const container = document.getElementById(`variables-container-${identifier}`);
    if (container) {
        container.style.display = container.style.display === 'none' ? 'block' : 'none';
    }
}

function createResultatObject() {
    return {
        coutOperationnel: {
            details: [],
            total: 0
        },
        coutFinancier: {
            details: [],
            total: 0
        },
        coutDivers: {
            details: [],
            total: 0
        },
        sommeCouts: {
            details: [],
            total: 0
        },
        resultatFinal: {
            details: [],
            total: 0
        }
    };
}

// Créer les instances dont nous avons besoin
let resultatsSimulation = createResultatObject();
let sommeResultats = createResultatObject();


function extraireValeurs(htmlString) {
    let lignes = htmlString.split('</tr>');
    let resultats = [];


    lignes.forEach(ligne => {
        // console.log(ligne);
        // Extraire les valeurs des cellules
        let valeurs = ligne.match(/<td[^>]*>(.*?)<\/td>/g);
        if (valeurs) {
            let resultatLigne = {
                nom: valeurs[1]?.replace(/<[^>]+>/g, '').trim(),
                cout: parseFloat(valeurs[2]?.replace(/<[^>]+>/g, '').trim() || 0),
                pourcentage: parseFloat(valeurs[3]?.replace(/<[^>]+>/g, '').trim() || 0),
                coutM2: parseFloat(valeurs[4]?.replace(/<[^>]+>/g, '').trim() || 0),
                pourcentagePrixRevente: parseFloat(valeurs[5]?.replace(/<[^>]+>/g, '').trim() || 0)
            };
            resultats.push(resultatLigne);
        }
    });

    return resultats;
}

// Fonction pour afficher les résultats cumulés
function afficherSommeResultatsHTML() {
    // Compter le nombre total de lots principaux
    const lotsAcquerir = mockData.sections.find(s => s.action === 'lotAcquerir');
    let totalLotsCount = 0;

    if (lotsAcquerir) {
        $.ajax({
            url: CONFIG.routes.sectionLots.getAllLotsToAcquire,
            method: 'GET',
            data: {
                immeubleId: CONFIG.immeubleId,
                sectionId: lotsAcquerir.idSection,
                aAcquerir: 1
            },
            async: false,
            success: function (response) {
                if (response.success && response.data) {
                    totalLotsCount = response.data.filter(lot => lot.siLotPrincipal === "Oui").length;
                }
            }
        });
    }

    // console.log("Nombre total de lots principaux :", totalLotsCount);

    // Créer le HTML du tableau récapitulatif
    let htmlRecap = `
        <div class="card mb-1">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">RÉCAPITULATIF GLOBAL DES PRÉVISIONNELS</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr class="bg-secondary text-white">
                                <th>Ligne</th>
                                <th>Categorie</th>
                                <th>Coût pour un logement(en euros)</th>
                                <th>% du coût total</th>
                                <th>Coût au m²</th>
                                <th>% du prix de revente</th>
                            </tr>
                        </thead>
                        <tbody>`;


    for (const [categorie, data] of Object.entries(sommeResultats)) {
        if (data.details && data.details.length > 0) {
            let totalCategoryCout = 0;

            // Calculer le total des coûts pour la catégorie
            data.details.forEach((ligne, index) => {
                if (index < data.details.length - 1) {
                    totalCategoryCout += parseFloat(ligne.cout || 0);
                }
            });

            let lineNum = 1;
            let lineCount = 0;

            // Générer les lignes pour cette catégorie
            data.details.forEach((ligne, index) => {
                if (index < data.details.length - 1) {
                    const pourcentageCoutTotal = totalCategoryCout > 0
                        ? (parseFloat(ligne.cout || 0) / totalCategoryCout * 100).toFixed(2)
                        : "0.00";

                    const coutAuM2 = totalLotsCount > 0
                        ? (parseFloat(ligne.coutM2 || 0) / totalLotsCount).toFixed(2)
                        : "0.00";

                    const totalGlobalPrixRevente = Object.values(sommeResultats).reduce((totalPrixRevente, categorie) => {
                        if (categorie.details && categorie.details.length > 0) {
                            const ligneRevente = categorie.details.find(ligne => ligne.nom === 'Prix de revente');
                            if (ligneRevente) {
                                return parseFloat(ligneRevente.cout || 0);
                            }
                        }
                        return totalPrixRevente;
                    }, 0);

                    const pourcentagePrixRevente = totalGlobalPrixRevente > 0
                        ? (parseFloat(ligne.cout || 0) / totalGlobalPrixRevente * 100).toFixed(2)
                        : "0.00";

                    htmlRecap += `
                <tr>
                    <td>${lineNum}</td>
                    <td>${ligne.nom || ''}</td>
                    <td class="text-right">${parseFloat(ligne.cout || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 })} €</td>
                    <td class="text-right">${pourcentageCoutTotal} %</td>
                    <td class="text-right">${coutAuM2} €</td>
                    <td class="text-right">${pourcentagePrixRevente} %</td>
                </tr>`;

                    lineNum++;
                    lineCount++;
                }
            });

            // Calculer le total de la catégorie
            let totalCout;
            let moyennePourcentagePrixRevente;
            let moyenneCoutM2;

            if (categorie === 'resultatFinal') {
                // Pour resultatFinal, on calcule la différence entre le prix de revente et le coût total
                const prixRevente = data.details[1]?.cout || 0;
                const coutTotal = data.details[0]?.cout || 0;
                totalCout = prixRevente - coutTotal;
                // Calculer les moyennes pour resultatFinal
                moyennePourcentagePrixRevente = (totalCout / prixRevente * 100) || 0;
                moyenneCoutM2 = totalLotsCount > 0 ? (totalCout / totalLotsCount) : 0;
            } else {
                // Pour les autres catégories, on garde la somme
                totalCout = data.details.reduce((sum, ligne) => sum + parseFloat(ligne.cout || 0), 0);
                moyennePourcentagePrixRevente = data.details.reduce((sum, ligne) =>
                    sum + parseFloat(ligne.pourcentagePrixRevente || 0), 0) / data.details.length;
                moyenneCoutM2 = totalLotsCount > 0
                    ? data.details.reduce((sum, ligne) => sum + parseFloat(ligne.coutM2 || 0), 0) / totalLotsCount
                    : 0;
            }

            htmlRecap += `
            <tr class="table-secondary">
                <td colspan="2">Total ${categorie}</td>
                <td class="text-right">${totalCout.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} €</td>
                <td class="text-right">100,00 %</td>
                <td class="text-right">${moyenneCoutM2.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} €</td>
                <td class="text-right">${moyennePourcentagePrixRevente.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} %</td>
            </tr>`;
        }
    }


    htmlRecap += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>`;

    // Ajouter le récapitulatif à la fin des simulations
    let recapDiv = document.getElementById('recap-global');
    if (!recapDiv) {
        const allSimulations = document.getElementById('all-simulations');
        if (allSimulations) {
            recapDiv = document.createElement('div');
            recapDiv.id = 'recap-global';
            allSimulations.appendChild(recapDiv);
        }
    }

    if (recapDiv) {
        recapDiv.innerHTML = htmlRecap;
    }
}
//  fonction ajouterALaSomme pour ajuster les calculs
function ajouterALaSomme(resultatsSimulation) {
    // Pour chaque catégorie
    Object.keys(resultatsSimulation).forEach(categorie => {
        const detailsSource = resultatsSimulation[categorie].details;

        // Si première simulation
        if (!sommeResultats[categorie].details.length) {
            sommeResultats[categorie].details = detailsSource.map(ligne => ({
                nom: ligne.nom,
                cout: Number(ligne.cout) || 0,
                pourcentage: Number(ligne.pourcentage) || 0,
                coutM2: Number(ligne.coutM2) || 0,
                pourcentagePrixRevente: Number(ligne.pourcentagePrixRevente) || 0
            }));
        } else {
            // Ajouter aux valeurs existantes
            detailsSource.forEach((ligne, idx) => {
                let somme = sommeResultats[categorie].details[idx];
                // Si c'est la catégorie resultatFinal, la dernière ligne est un calcul de différence
                if (categorie == 'resultatFinal' && idx === detailsSource.length - 1) {
                    console.log("Ceci est la categorie ", categorie);
                    return;
                }

                somme.cout += Number(ligne.cout) || 0;
                somme.coutM2 += Number(ligne.coutM2) || 0;
                somme.pourcentagePrixRevente += Number(ligne.pourcentagePrixRevente) || 0;
            });
        }
        if (categorie === 'resultatFinal') {
            // Pour resultatFinal, on recalcule la différence
            const prixRevente = sommeResultats[categorie].details[1].cout; // Prix de revente
            const coutTotal = sommeResultats[categorie].details[0].cout;   // Coût total
            sommeResultats[categorie].total = prixRevente - coutTotal;

            // Mettre à jour la dernière ligne des détails
            if (sommeResultats[categorie].details.length > 2) {
                sommeResultats[categorie].details[2].cout = sommeResultats[categorie].total;
            }
        } else {
            sommeResultats[categorie].total += Number(resultatsSimulation[categorie].total) || 0;
        }
    });
}
// Utilisation
let tableauxSimulations = []; // Array pour stocker tous les résultats

// Fonction pour générer des lignes de tableau pour une simulation
function generateSimulationRows(variables, categoryNum, lineNumbers, totalLabel) {
    let rows = '';
    let total = 0;
    let currentIndex = 1;

    function getVariableValue(variableId) {
        const variable = variables.find(v => v.idVariableSimulation == variableId);
        return variable ? parseFloat(variable.montant || variable.valeurVariableSimulation || 0) : 0;
    }

    const surface = getVariableValue(9);
    const prixRevente = getVariableValue(10);
    const prixReventeTotal = surface * prixRevente;

    function calculateCost(variable, variables, type = 'cout', calculatedVariables = new Set()) {
        if (calculatedVariables.has(variable.idVariableSimulation)) {
            return getVariableValue(variable.idVariableSimulation);
        }
        calculatedVariables.add(variable.idVariableSimulation);

        function buildContext() {
            const context = {};

            // Valeurs de base
            variables.forEach(v => {
                if (v.libelleVariable) {
                    let value = getVariableValue(v.idVariableSimulation);
                    context[v.libelleVariable] = v.typeValeurSimulation === 'pourcentage' ? value / 100 : value;
                }
            });

            // Calculs de base
            context.surface = getVariableValue(9);
            context.prixRevente = getVariableValue(10);
            context.prixReventeTotal = context.surface * context.prixRevente;
            context.coutTotalPrixAchat = context.prixAchat * context.surface;

            // Calculer les coûts par catégorie
            const coutsParCategorie = {};
            variables.forEach(v => {
                if (v.categorie) {
                    if (!coutsParCategorie[v.categorie]) coutsParCategorie[v.categorie] = 0;
                    if (v.formuleCoutTotal) {
                        try {
                            const cout = evaluateFormula(v.formuleCoutTotal, { ...context });
                            coutsParCategorie[v.categorie] += cout;
                        } catch (error) {
                            coutsParCategorie[v.categorie] += getVariableValue(v.idVariableSimulation);
                        }
                    } else {
                        coutsParCategorie[v.categorie] += getVariableValue(v.idVariableSimulation);
                    }
                }
            });

            // Ajouter les totaux au contexte
            Object.entries(coutsParCategorie).forEach(([cat, total]) => {
                context[`totalCategorie${cat}`] = total;
            });

            // Calculer capital emprunter 
            context.capitalEmprunter = context.totalCategorie1 /
                (1 - ((context.tauxRemunerationObligataire1 + context.tauxRemunerationObligataire2) + context.tauxCommissionBancaire));

            // console.log("CECI EST UN LOG POUR LISTER le tauxRemuneration ", context.tauxRemunerationObligataire1 + context.tauxRemunerationObligataire2);


            // Calculer le coût total de la variable actuelle
            if (variable.formuleCoutTotal) {
                context.coutTotal = evaluateFormula(variable.formuleCoutTotal, context);
            } else {
                context.coutTotal = getVariableValue(variable.idVariableSimulation);
            }



            // Ajouter le total de la catégorie courante
            if (variable.categorie) {
                context.totalCategorie = coutsParCategorie[variable.categorie] || 0;
            }

            return context;
        }

        function evaluateFormula(formula, context) {
            try {
                const parsed = formula.replace(/[*+/-]/g, match => ` ${match} `).trim();
                return new Function(...Object.keys(context), `return ${parsed}`)(...Object.values(context));
            } catch {
                return 0;
            }
        }

        function calculateBaseCout(variable, context) {
            if (!variable.formuleCoutTotal) return getVariableValue(variable.idVariableSimulation);

            try {
                const formula = variable.formuleCoutTotal.replace(/[*+/-]/g, match => ` ${match} `).trim();
                return new Function(...Object.keys(context), `return ${formula}`)(...Object.values(context));
            } catch (error) {
                console.error(`Erreur calcul de base pour ${variable.nomVariableSimulation}:`, error);
                return getVariableValue(variable.idVariableSimulation);
            }
        }

        const context = buildContext();

        if (type === 'pourcentage') {
            if (!variable.formulePourcentageCoutTotal) return 0;
            try {
                const formula = variable.formulePourcentageCoutTotal.replace(/[*+/-]/g, match => ` ${match} `).trim();
                return new Function(...Object.keys(context), `return ${formula}`)(...Object.values(context));
            } catch (error) {
                console.error(`Erreur calcul pourcentage pour ${variable.nomVariableSimulation}:`, error);
                return 0;
            }
        }

        return calculateBaseCout(variable, context);
    }

    function getTotalCategorie(cat) {
        return variables
            .filter(v => v.categorie == cat)
            .reduce((sum, v) => sum + calculateCost(v, variables), 0);
    }

    function getGrandTotal() {
        const totalCat1 = getTotalCategorie(1);
        const totalCat2 = getTotalCategorie(2); // Spécial pour catégorie 2
        const totalCat3 = getTotalCategorie(3);
        return totalCat1 + totalCat2 + totalCat3;
    }

    // Génération des lignes
    lineNumbers.forEach((lineNum, index) => {
        const variablesCategorie = variables.filter(v => v.categorie == categoryNum);
        let variable = variablesCategorie[index];
        let coutTotal = 0;
        let displayName;
        let pourcentageCoutTotal = "0.00";

        if (categoryNum == 4) {
            switch (lineNum) {
                case 1:
                    coutTotal = getTotalCategorie(1);
                    displayName = "Sous-total 1 (Coût opérationnel total)";
                    break;
                case 2:
                    coutTotal = getTotalCategorie(2);
                    displayName = "Sous-total 2 (Coût financier des obligataires)";
                    break;
                case 3:
                    coutTotal = getTotalCategorie(3);
                    displayName = "Sous-total 3 (Commission bancaire)";
                    break;
            }
            const grandTotal = getGrandTotal();
            pourcentageCoutTotal = grandTotal > 0 ? ((coutTotal / grandTotal) * 100).toFixed(2) : "0.00";

        } else if (categoryNum == 5) {
            switch (lineNum) {
                case 1:
                    coutTotal = getGrandTotal();
                    displayName = "Coût Total";
                    break;
                case 2:
                    coutTotal = prixReventeTotal;
                    displayName = "Prix de revente";
                    break;
            }
            pourcentageCoutTotal = prixReventeTotal > 0 ? ((coutTotal / prixReventeTotal) * 100).toFixed(2) : "0.00";

        } else if (categoryNum == 2) {
            variable1 = variables.find(v => v.idVariableSimulation == 7);
            variable2 = variables.find(v => v.idVariableSimulation == 14);
            // console.log('variable1')
            // console.log(variable1)
            // console.log(variable2)


            if (variable1 && variable2) {
                const tauxRemu1 = parseFloat(getVariableValue(7)).toFixed(2);
                const tauxRemu2 = parseFloat(getVariableValue(14)).toFixed(2);

                // Séparation des calculs pour chaque année
                if (index == 0) {
                    coutTotal = calculateCost(variable1, variables);  // Uniquement le coût de variable1
                    displayName = `Rémunération annuelle des obligataires (${tauxRemu1}%) Année 1`;
                    pourcentageCoutTotal = parseFloat(getVariableValue(7)).toFixed(2);
                } else if (index == 1) {
                    coutTotal = calculateCost(variable2, variables);  // Uniquement le coût de variable2
                    displayName = `Rémunération annuelle des obligataires (${tauxRemu2}%) Année 2`;
                    pourcentageCoutTotal = parseFloat(getVariableValue(14)).toFixed(2);
                }
            }
        } else if (categoryNum == 3) {
            variable = variables.find(v => v.idVariableSimulation == 8);
            if (variable) {
                coutTotal = calculateCost(variable, variables);
                const tauxCommission = parseFloat(getVariableValue(8)).toFixed(2);
                displayName = `Commission bancaire (${tauxCommission}%)`;
                pourcentageCoutTotal = parseFloat(getVariableValue(8)).toFixed(2);
            }
        } else if (variable) {
            coutTotal = calculateCost(variable, variables);
            displayName = variable.nomAfficher || variable.nomVariableSimulation;
            pourcentageCoutTotal = calculateCost(variable, variables, 'pourcentage').toFixed(2);
        }

        const coutM2 = surface > 0 ? (coutTotal / surface).toFixed(2) : "0.00";
        const pourcentagePrixRevente = prixReventeTotal > 0 ? ((coutTotal / prixReventeTotal) * 100).toFixed(2) : "0.00";

        rows += generateTableRow(currentIndex, displayName || '-', coutTotal, pourcentageCoutTotal, coutM2, pourcentagePrixRevente);
        if (coutTotal) total += coutTotal;
        currentIndex++;
    });

    // Ligne totale
    const totalCategorie = categoryNum == 5 ? prixReventeTotal - getGrandTotal() : total;
    const coutM2Total = surface > 0 ? (totalCategorie / surface) : 0;
    const pourcentagePrixReventeTotal = prixReventeTotal > 0 ? ((totalCategorie / prixReventeTotal) * 100) : 0;
    const pourcentageTotal = categoryNum == 2 ? (parseFloat(getVariableValue(7)) + parseFloat(getVariableValue(14))).toFixed(2) :
        categoryNum == 5 ? pourcentagePrixReventeTotal.toFixed(2) : "100.00";

    rows += generateTableRow(null, totalLabel, totalCategorie, pourcentageTotal, coutM2Total, pourcentagePrixReventeTotal, true);

    return rows;
}
function generateTableRow(lineNum, name, cout, pourcentage, coutM2, pourcentagePrixRevente, isTotal = false) {
    return `
    <tr ${isTotal ? 'bgcolor=" #aed8b1"' : ''}>
        ${isTotal ? '<td colspan="2">' : `<td>${lineNum}</td><td>`}${name}</td>
        <td class="text-right montant-cell">${parseFloat(cout || 0).toFixed(2)} €</td>
        <td class="text-right pourcentage-cell">${pourcentage}%</td>
        <td class="text-right cout-m2">${parseFloat(coutM2 || 0).toFixed(2)} €</td>
        <td class="text-right prix-revente">${parseFloat(pourcentagePrixRevente || 0).toFixed(2)}%</td>
    </tr>`;
}


function displayArticlesAcquireTable(articles, sectionId) {
    console.log('Affichage des artciles à acquérir pour la section:', sectionId);
    const containerSelector = `#cctp-container-${sectionId}`;
    console.log(articles);
    if (articles != undefined) {
        let total = 0;

        let html = `
        <div class="card">
                <div class="card-header">
                <div class="alert alert-info">
                            <strong>Liste des articles :</strong> ${articles.length}
                            <button onclick="Actions.handleCCTP(${sectionId})" class="btn btn-sm btn-info float-right">Ajouter des articles</button>
                        </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <div class="float-right mt-0">
                        <label class="font-weight-bold">TOTAL</label>
                        <input type="text" readonly id="totalCCTPSection_${sectionId}" class="form-control  font-weight-bold text-primary">
                    </div>
                        <table id="cctpAcquerirTable-${sectionId}" class="mt-2 table table-bordered table-striped">
                            <thead>
                              <tr>
                                            <th>N°Ligne</th>
                                            <th>Libellé</th>
                                            <th>Unité</th>
                                            <th>Quantité</th>
                                            <th>Prix Unitaire</th>
                                            <th>Montant HT</th>
                                            <th>Taux Remise</th>
                                            <th>Montant AR</th>
                                            <th>Taux TVA</th>
                                            <th>Montant TTC</th>
                                            <th>Actions TTC</th>
                                        </tr>
                            </thead>
                             <tbody>`;
        articles.forEach((article, index) => {
            let mRemise = article.montant - (article.montant * (article.tauxRemise == '' ? 0 : Number(article.tauxRemise)) / 100);
            let ttc = mRemise + (mRemise * (article.tva == '' ? 0 : Number(article.tva)) / 100);
            total += ttc;
            html += ` <tr>
                            <td>${index + 1}</td>
                            <td>${article.libelle}</td>
                            <td>${article.unite}</td>
                            <td>${article.quantite}</td>
                            <td>${formatNumber(article.prix.toFixed(2))} €</td>
                            <td>${formatNumber(article.montant.toFixed(2))} €</td>
                            <td>${article.tauxRemise}%</td>
                            <td>${formatNumber(mRemise.toFixed(2))} €</td>
                            <td>${article.tva}%</td>
                            <td>${formatNumber(ttc.toFixed(2))} €</td>
                            <td>
                                <button type="button" onclick="deleteLigne(${article.idTableSection})" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>`;
        });
        html += `</tbody>
    </table>
</div>
</div>
</div>
`;

        $(containerSelector).html(html);
        $(`#totalCCTPSection_${sectionId}`).val(formatNumber(total.toFixed(2)));
        $(`#cctp-section-${sectionId}`).show();
        $(`#cctpGlobal-section-${sectionId}`).hide();
        $(`#lots-section-${sectionId}`).hide();

        // Initialiser DataTables
        $(`#cctpAcquerirTable-${sectionId}`).DataTable({
            pageLength: 20,
            responsive: true,
            "oLanguage": {
                "sZeroRecords": "Aucune donnée !",
                "sProcessing": "En cours...",
                "sLengthMenu": "Nombre d'éléments _MENU_ ",
                "sInfo": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                "sInfoEmpty": "Affichage de 0 à 0 sur 0 entrée",
                "sInfoFiltered": "(filtré à partir de _MAX_ total entrées)",
                "sSearch": "Recherche:",
            },
            "language": {
                "paginate": {
                    "previous": "<<",
                    "next": ">>"
                }
            },
            "aLengthMenu": [[5, 10, 25, 50, 100, 500, -1], [5, 10, 25, 50, 100, 500, "Tous"]],
            "iDisplayLength": 25
        });
    }

}

function formatNumber(nb) {
    return nb.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

function displayCCTPGlobal(sectionId) {
    console.log('Affichage des artciles Global :', sectionId);
    const containerSelector = `#cctpGlobal-container-${sectionId}`;
    let total = 0;
    $.ajax({
        url: URLROOT + '/public/json/cctp.php?action=getLinesCCTPForImmeuble&idImmeuble=' + CONFIG.immeubleId + "&type=global&idProjet=" + $('#idProjet').val(),
        method: 'GET',
        dataType: 'json',
        success: (articles) => {
            console.log("articles cctp G");
            console.log(articles);
            let html = '';
            if (articles != undefined) {
                let html = `
                <div class="card">
                        <div class="card-header">
                           <div class="alert alert-info">
                            <strong>Liste de tous les articles du CCTP :</strong> ${articles.length}
                        </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                               <div class="float-right">
                                    <label class="font-weight-bold">TOTAL</label>
                                    <input type="text" readonly id="totalCCTPGSection_${sectionId}" class="form-control  font-weight-bold text-primary">
                                </div>
                                <table width="100%" id="cctpGlobalTable-${sectionId}" class="mt-2 table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>N°Ligne</th>
                                            <th>Libellé</th>
                                            <th>Unité</th>
                                            <th>Quantité</th>
                                            <th>Prix Unitaire</th>
                                            <th>Montant HT</th>
                                            <th>Taux Remise</th>
                                            <th>Montant AR</th>
                                            <th>Taux TVA</th>
                                            <th>Montant TTC</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

                articles.forEach((article, index) => {
                    let mRemise = article.montant - (article.montant * (article.tauxRemise == '' ? 0 : Number(article.tauxRemise)) / 100);
                    let ttc = mRemise + (mRemise * (article.tva == '' ? 0 : Number(article.tva)) / 100);
                    total += ttc;
                    html += ` <tr>
                                    <td>${index + 1}</td>
                                    <td>${article.libelle}</td>
                                    <td>${article.unite}</td>
                                    <td>${article.quantite}</td>
                                    <td>${formatNumber(article.prix)} €</td>
                                    <td>${formatNumber(article.montant)} €</td>
                                    <td>${article.tauxRemise}%</td>
                                    <td>${formatNumber(mRemise)} €</td>
                                    <td>${article.tva}%</td>
                                    <td>${formatNumber(ttc)} €</td>
                                </tr>`;
                });
                html += `</tbody>
            </table>
        </div>
        </div>
        </div>
        `;

                $(containerSelector).html(html);
                $(`#totalCCTPGSection_${sectionId}`).val(formatNumber(total.toFixed(2)));
                $(`#cctpGlobal-section-${sectionId}`).show();
                $(`#lots-section-${sectionId}`).hide();
                $(`#cctp-section-${sectionId}`).hide();

                $(`#cctpGlobalTable-${sectionId}`).DataTable({
                    pageLength: 20,
                    responsive: true,
                    "oLanguage": {
                        "sZeroRecords": "Aucune donnée !",
                        "sProcessing": "En cours...",
                        "sLengthMenu": "Nombre d'éléments _MENU_ ",
                        "sInfo": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                        "sInfoEmpty": "Affichage de 0 à 0 sur 0 entrée",
                        "sInfoFiltered": "(filtré à partir de _MAX_ total entrées)",
                        "sSearch": "Recherche:",
                    },
                    "language": {
                        "paginate": {
                            "previous": "<<",
                            "next": ">>"
                        }
                    },
                    "aLengthMenu": [[5, 10, 25, 50, 100, 500, -1], [5, 10, 25, 50, 100, 500, "Tous"]],
                    "iDisplayLength": 25
                });
            }


        },
        error: (response) => {
            console.error('Erreur lors du chargement du cctp:', response);
        }
    });

}

function validateArticlesSelection() {
    let articlesC = $('.article-checkbox:checked');
    let articlesCheckIds = [];
    let idSection = $('#sectionIdActuel').val();


    $('.article-checkbox:checked').each(function () {
        // Ajouter la valeur de la checkbox cochée dans le tableau
        articlesCheckIds.push($(this).data('article-id'));
    });

    if (articlesCheckIds.length != 0) {
        console.log({
            articleIds: articlesCheckIds,
            idSection: $('#sectionIdActuel').val(),
            idUtilisateur: $('#idUtilisateur').val(),
            idProjet: $('#idProjet').val()
        });
        $.ajax({
            url: `${CONFIG.URLROOT}/public/json/section.php?action=saveLineForProjet`,
            method: 'POST',
            data: {
                articleIds: articlesCheckIds,
                idSection: $('#sectionIdActuel').val(),
                idUtilisateur: $('#idUtilisateur').val(),
                idProjet: $('#idProjet').val()
            },
            dataType: "json",
            success: function (response) {
                console.log("response");
                console.log(response);
                if (response) {
                    $('#cctpModal').modal('hide');
                    displayArticlesAcquireTable(response, idSection);

                    $(`#cctp-section-${idSection}`).show();
                } else {
                    alert('Erreur lors de la création de l\'importation des articles ');
                }

            },
            error: function (response) {
                console.error('Erreur lors de la création de l\'importation des articles :');
                console.log(response);
            },
            complete: function () {

            }
        });

    } else {
        $('#msgError').text('Veuillez choisir un article');
        $('#errorOperation').modal('show');

    }
}


function deleteLigne(idLigne) {
    $('#idLigneSup').val(idLigne);
    $('#deleteLigneModal').modal('show');
}

$('#confirmDeleteBtnLigne').on('click', function (e) {
    $.ajax({
        url: `${URLROOT}/public/json/section.php?action=deleteLigne&idProjet=${$('#idProjet').val()}`,
        method: 'POST',
        data: { idLigne: $('#idLigneSup').val() },
        success: function (response) {
            try {

                if (response == '1') {
                    $('#deleteLigneModal').modal('hide');
                    loadSectionsFromDatabase();
                    getCCTPGlobal();
                } else {
                    console.error('Erreur:', jsonResponse.error || 'Erreur inconnue');
                    alert(jsonResponse.error || 'Erreur lors de la suppression de la ligne');
                }
            } catch (e) {
                $('#deleteLigneModal').modal('hide');
                loadSectionsFromDatabase();
            }
        },
        error: function (xhr, status, error) {
            console.error('Erreur lors de la suppression de la ligne:', error);
            if (xhr.status == 200) {
                $('#createSectionModal').modal('hide');
                loadSectionsFromDatabase();
                $('input[name="titreSection"]').val('');
            } else {
                alert('Une erreur est survenue lors de la suppression de la ligne');
            }
        },
        complete: function () {
        }
    });
})

function displaySituationCopro(sectionId) {
    // let comptes = getComptes();
    const containerSelector = `#situation-copro-container-${sectionId}`;
    $.ajax({
        url: URLROOT + '/public/json/lot.php?action=getComptesByImmeuble&idImmeuble=' + CONFIG.immeubleId + "&etatCompte=''",
        method: 'GET',
        dataType: 'json',
        success: (response) => {
            console.log("response");
            console.log(response);
            let html = '';
            let comptes = response;
            if (comptes != undefined) {
                let html = `
                    <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Tableau des situations des copropriétaires</h5>
                            </div>
                            <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable-situation-copro-${sectionId}" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>N°Ligne</th>
                                        <th>Copropriétaire</th>
                                        <th>Tantièmes</th>
                                        <th>Charges Courantes</th>
                                        <th>Charges Exceptionnelles Travaux</th>
                                        <th>Fonds ALUR</th>
                                        <th>Avance</th>
                                        <th>Charges Emprunt</th>
                                        <th>Total des Dettes</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                comptes.forEach((article, index) => {
                    html += ` <tr>
                                        <td>${index + 1}</td>
                                        <td>${article.proprietaire}</td>
                                        <td>${article.tantieme || '0'}</td>
                                        <td nowrap="nowrap" `+ (Number((article.soldeChargeCourante).toString().replace(",", ".")) < 0 ? "style='color:red'" : "") + `>${formatNumber(article.soldeChargeCourante)} €</td>
                                        <td nowrap="nowrap" `+ (Number((article.soldeChargeExceptionnelle).toString().replace(",", ".")) < 0 ? "style='color:red'" : "") + `>${formatNumber(article.soldeChargeExceptionnelle)}€</td>
                                        <td nowrap="nowrap" `+ (Number((article.soldeFondTravauxAlur).toString().replace(",", ".")) < 0 ? "style='color:red'" : "") + `>${formatNumber(article.soldeFondTravauxAlur)} €</td>
                                        <td nowrap="nowrap" `+ (Number((article.soldeAvance).toString().replace(",", ".")) < 0 ? "style='color:red'" : "") + `>${formatNumber(article.soldeAvance)} €</td>
                                        <td nowrap="nowrap" `+ (Number((article.soldeChargeEmprunt).toString().replace(",", ".")) < 0 ? "style='color:red'" : "") + `>${formatNumber(article.soldeChargeEmprunt)} €</td>
                                        <td nowrap="nowrap" data-order="${Number(article.solde)}" ` + ((article.solde != null && article.solde != "" && article.solde != "0" && Number((article.solde).toString().replace(",", ".")) < 0) ? "style='color:red'" : "") + `>${formatNumber(article.solde)} €</td>
                                    </tr>`;
                });
                html += `</tbody>
                        </table>
                    </div>
                    </div>
                    </div>
                    `;

                $(containerSelector).html(html);
                $(`#cctpGlobal-section-${sectionId}`).hide();
                $(`#lots-section-${sectionId}`).hide();
                $(`${containerSelector}`).show();

                $(`#datatable-situation-copro-${sectionId}`).DataTable({
                    pageLength: 20,
                    responsive: true,
                    "oLanguage": {
                        "sZeroRecords": "Aucune donnée !",
                        "sProcessing": "En cours...",
                        "sLengthMenu": "Nombre d'éléments _MENU_ ",
                        "sInfo": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                        "sInfoEmpty": "Affichage de 0 à 0 sur 0 entrée",
                        "sInfoFiltered": "(filtré à partir de _MAX_ total entrées)",
                        "sSearch": "Recherche:",
                    },
                    "language": {
                        "paginate": {
                            "previous": "<<",
                            "next": ">>"
                        }
                    },
                    "aLengthMenu": [[5, 10, 25, 50, 100, 500, -1], [5, 10, 25, 50, 100, 500, "Tous"]],
                    "iDisplayLength": 25
                });

            }
        },
        error: (response) => {
            console.error('Erreur lors du chargement des comptes:');
            console.error(response);
        }
    });
}


const Actions = {
    // Configuration des actions disponibles
    actionsList: [
        { value: 'tousLots', label: 'Tous les lots' },
        { value: 'lotAcquerir', label: 'Lots à acquerir', unique: true },
        { value: 'lotAssocie', label: 'Tableau Previsionnel', unique: true },
        { value: 'tpg', label: 'Tableau Previsionnel Global' },
        { value: 'cctp', label: 'CCTP' },
        { value: 'cctpG', label: 'CCTP GLOBAL' },
        { value: 'situationCopro', label: 'Situation des Copros' },
        { value: 'bibliotheque', label: 'Bibliothèque Textuelle' }
    ],
    // DEBUTE AJOUT


    // Génère le HTML du select avec vérification des actions uniques
    generateActionSelect(sectionId) {
        const section = mockData.sections.find(s => s.idSection == sectionId);
        const currentAction = section?.action || '';

        // Vérifier les actions uniques déjà utilisées dans d'autres sections
        const usedUniqueActions = new Set();
        mockData.sections.forEach(s => {
            if (s.idSection != sectionId && s.action) {
                const actionConfig = this.actionsList.find(a => a.value === s.action);
                if (actionConfig && actionConfig.unique) {
                    usedUniqueActions.add(s.action);
                }
            }
        });

        // Filtrer les options disponibles
        const availableOptions = this.actionsList.filter(action => {
            // Si l'action est unique et déjà utilisée ailleurs
            if (action.unique && usedUniqueActions.has(action.value)) {
                // Ne l'afficher que si c'est l'action actuelle de cette section
                return currentAction === action.value;
            }
            return true;
        });

        const options = availableOptions.map(action => {
            const isSelected = currentAction === action.value;
            return `
                    <option value="${action.value}" ${isSelected ? 'selected' : ''}>
                        ${action.label}
                    </option>
                `;
        }).join('');

        return `
                <select class="form-select form-select-sm" 
                        style="width: auto;" 
                        onchange="Actions.handleAction(this.value, ${sectionId})">
                    <option value="" ${!currentAction ? 'selected' : ''}>Actions</option>
                    ${options}
                </select>
            `;
    },


    // Gère les actions du select
    handleAction(actionType, sectionId) {
        if (!actionType) {
            console.log('Pas de type d\'action');
            return;
        }

        const section = mockData.sections.find(s => s.idSection == sectionId);
        if (!section) {
            console.log('Section non trouvée:', sectionId);
            return;
        }

        // Si l'action est unique, vérifier qu'elle n'est pas déjà utilisée
        const actionConfig = this.actionsList.find(a => a.value === actionType);
        if (actionConfig && actionConfig.unique) {
            const isUsedElsewhere = mockData.sections.some(s =>
                s.idSection != sectionId && s.action === actionType
            );
            if (isUsedElsewhere) {
                this.displayError('Cette action ne peut être utilisée qu\'une seule fois dans le projet');
                return;
            }
        }

        // Mettre à jour l'action et gérer le comportement spécifique
        switch (actionType) {
            case 'tousLots':
                this.handleTousLots(sectionId);
                break;
            case 'lotAcquerir':
                this.handleLotsAcquerir(sectionId);
                break;
            case 'lotAssocie':
                this.handleLotAssocie(sectionId);
                break;
            case 'tpg':
                this.handleTPG(sectionId);
                break;
            case 'cctp':
                this.handleCCTP(sectionId);
                break;
            case 'cctpG':
                this.handleCCTPG(sectionId);
                break;
            case 'situationCopro':
                this.handleSituationCopro(sectionId);
                break;
            case 'bibliotheque':
                this.handleBibilotheque(sectionId);
                break;
        }

        section.action = actionType;
        this.refreshAllSelects();
    },


    handleTPG(sectionId) {
        console.log('Action: Tableau Prévisionnel Globale pour la section', sectionId);

        // Mettre à jour l'action dans la base de données
        this.updateSectionAction(sectionId, 'tpg')
            .then(() => {
                // FORCER la suppression de l'ancien HTML avant de générer un nouveau TPG
                this.clearTPGHtml(CONFIG.sommaireId)
                    .then(() => {
                        // Toujours générer un nouveau TPG
                        this.generateNewTPG(sectionId);
                    })
                    .catch(error => {
                        console.error('Erreur lors de la suppression de l\'ancien HTML:', error);
                        // Générer quand même le nouveau TPG
                        this.generateNewTPG(sectionId);
                    });
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour de l\'action:', error);
                this.displayError('Erreur lors de la mise à jour de l\'action');
            });
    },

    // Nouvelle méthode pour effacer l'ancien HTML du TPG
    clearTPGHtml(sommaireId) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: CONFIG.routes.sommaire.clearHtmlTPG, // Nouvelle route à ajouter
                method: 'POST',
                data: {
                    sommaireId: sommaireId
                },
                success: (response) => {
                    if (response.success) {
                        resolve(response);
                    } else {
                        reject(response.error);
                    }
                },
                error: (xhr, status, error) => {
                    reject(error);
                }
            });
        });
    },


    displayTPG(sectionId, html) {
        const containerSelector = `#lots-container-${sectionId}`;
        // Créer la structure de la carte
        const cardHtml = `
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">TABLEAU PRÉVISIONNEL GLOBALE</h5>
                </div>
                <div class="card-body">
                    <div id="recap-global">${html}</div>
                </div>
            </div>
        `;

        // Mettre à jour le conteneur
        $(containerSelector).empty().html(cardHtml);
    },

    generateNewTPG(sectionId) {
        console.log('Génération d\'un nouveau TPG pour la section', sectionId);

        // Créer le conteneur via displayTPG avec un message de chargement
        this.displayTPG(sectionId, '<div class="text-center">Chargement en cours...</div>');

        // Réinitialiser TOTALEMENT les résultats précédents
        sommeResultats = createResultatObject();
        tableauxSimulations = []; // Vider le tableau des simulations précédentes

        // Chercher la section avec lotAcquerir DYNAMIQUEMENT
        const lotAcquerirSection = mockData.sections.find(s => s.action === 'lotAcquerir');
        if (!lotAcquerirSection) {
            this.showErrorMessage('Aucune section avec les lots à acquérir n\'a été trouvée');
            return;
        }

        // Vérifier la section avec lotAssocie
        const lotAssocieSection = mockData.sections.find(s => s.action === 'lotAssocie');
        if (!lotAssocieSection) {
            this.showErrorMessage('Veuillez d\'abord créer un tableau prévisionnel');
            return;
        }

        // Récupérer les lots à acquérir DIRECTEMENT depuis le serveur
        $.ajax({
            url: CONFIG.routes.sectionLots.getAllLotsToAcquire,
            method: 'GET',
            data: {
                immeubleId: CONFIG.immeubleId,
                sectionId: lotAcquerirSection.idSection,
                aAcquerir: 1
            },
            success: (response) => {
                if (!response.success || !response.data || response.data.length === 0) {
                    this.showErrorMessage('Aucun lot principal à acquérir trouvé');
                    return;
                }

                // Filtrer uniquement les lots principaux
                const lots = response.data.filter(lot => lot.siLotPrincipal === "Oui");

                if (lots.length === 0) {
                    this.showErrorMessage('Aucun lot principal à acquérir trouvé');
                    return;
                }

                // Récupérer les variables pour CHAQUE lot
                Promise.all(lots.map(lot =>
                    $.ajax({
                        url: CONFIG.routes.variableSimulation.getForSection,
                        method: 'GET',
                        data: {
                            sectionId: lotAssocieSection.idSection,
                            lotId: lot.idApp
                        }
                    })
                )).then(responses => {
                    responses.forEach(response => {
                        if (response.success && response.data) {
                            // Réinitialiser resultatsSimulation à chaque itération
                            resultatsSimulation = createResultatObject();
                            const variables = response.data;
                            this.simulerEtAjouterResultats(variables);
                        }
                    });

                    // Générer le HTML global
                    afficherSommeResultatsHTML();

                    // Récupérer le HTML généré et l'afficher
                    const htmlRecap = document.getElementById('recap-global').innerHTML;

                    // IMPORTANT : Sauvegarder un nouvel HTML, écrasant l'ancien
                    this.saveTPGHtml(htmlRecap);

                    // Mettre à jour l'affichage
                    this.displayTPG(sectionId, htmlRecap);
                }).catch(error => {
                    console.error('Erreur lors de la récupération des variables:', error);
                    this.showErrorMessage('Erreur lors de la récupération des variables de simulation');
                });
            },
            error: (xhr, status, error) => {
                console.error('Erreur lors du chargement des lots:', error);
                this.showErrorMessage('Erreur lors du chargement des lots à acquérir');
            }
        });
    },

    // fonction pour sauvegarder le HTML du TPG
    saveTPGHtml(html) {
        $.ajax({
            url: CONFIG.routes.sommaire.updateHtmlTPG,
            method: 'POST',
            data: {
                sommaireId: CONFIG.sommaireId,
                html: html
            },
            success: (response) => {
                if (response.success) {
                    console.log('TPG sauvegardé avec succès');
                } else {
                    console.error('Erreur lors de la sauvegarde du TPG');
                    this.showErrorMessage('Erreur lors de la sauvegarde du tableau prévisionnel');
                }
            },
            error: (xhr, status, error) => {
                console.error('Erreur lors de la sauvegarde du TPG:', error);
                this.showErrorMessage('Erreur lors de la sauvegarde du tableau prévisionnel');
            }
        });
    },

    // méthode pour simuler et ajouter les résultats
    simulerEtAjouterResultats(variables) {
        // Regrouper les variables par catégorie
        const groupedVariables = variables.reduce((acc, variable) => {
            const cat = variable.categorie || 'Autre';
            if (!acc[cat]) acc[cat] = [];
            acc[cat].push(variable);
            return acc;
        }, {});

        // Calculer les résultats pour chaque catégorie
        resultatsSimulation.coutOperationnel.details = extraireValeurs(
            generateSimulationRows(variables, 1, groupedVariables['1']?.map(v => v.idVariableSimulation) || [], "Total Coût opérationnel")
        );
        resultatsSimulation.coutOperationnel.total = resultatsSimulation.coutOperationnel.details[resultatsSimulation.coutOperationnel.details.length - 1].cout;

        resultatsSimulation.coutFinancier.details = extraireValeurs(
            generateSimulationRows(variables, 2, [7, 8], "Total Coût financier des obligataires sur 24 Mois")
        );
        resultatsSimulation.coutFinancier.total = resultatsSimulation.coutFinancier.details[resultatsSimulation.coutFinancier.details.length - 1].cout;

        resultatsSimulation.coutDivers.details = extraireValeurs(
            generateSimulationRows(variables, 3, [9], "Total Coût divers (Commission totale)")
        );
        resultatsSimulation.coutDivers.total = resultatsSimulation.coutDivers.details[resultatsSimulation.coutDivers.details.length - 1].cout;

        resultatsSimulation.sommeCouts.details = extraireValeurs(
            generateSimulationRows(variables, 4, [1, 2, 3], "Somme des coûts")
        );
        resultatsSimulation.sommeCouts.total = resultatsSimulation.sommeCouts.details[resultatsSimulation.sommeCouts.details.length - 1].cout;

        resultatsSimulation.resultatFinal.details = extraireValeurs(
            generateSimulationRows(variables, 5, [1, 2], "Résultat final")
        );
        resultatsSimulation.resultatFinal.total = resultatsSimulation.resultatFinal.details[resultatsSimulation.resultatFinal.details.length - 1].cout;

        // Ajouter les résultats à la somme
        ajouterALaSomme(resultatsSimulation);
    },

    // méthode pour gérer l'action lotAssocie
    handleLotAssocie(sectionId) {
        console.log('Action: Lot associé pour la section', sectionId);
        this.updateSectionAction(sectionId, 'lotAssocie')
            .then(() => {
                // Charger les lots (qui chargera les variables) après la mise à jour réussie
                this.loadLots(sectionId);
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour de l\'action:', error);
                this.displayError('Erreur lors de la mise à jour de l\'action');
            });
    },


    // méthode pour rafraîchir tous les selects
    refreshAllSelects() {
        mockData.sections.forEach(section => {
            const selectContainer = document.querySelector(`#select-container-${section.idSection}`);
            if (selectContainer) {
                selectContainer.innerHTML = this.generateActionSelect(section.idSection);
            }
        });
    },

    // Fin AJOUT
    handleBibilotheque(sectionId) {
        console.log('Action: Bibliotheque Textuelle', sectionId);
        activeSectionId = sectionId;
        this.updateSectionAction(sectionId, '')
            .then(() => {
                // Charger les lots après la mise à jour réussie
                $("#modalBibilotheque").modal('show')
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour de l\'action:', error);
                this.displayError('Erreur lors de la mise à jour de l\'action');
            });
        //SHOW BIBILOTHEQUE

    },


    handleTousLots(sectionId) {
        console.log('Action: Tous les lots pour la section', sectionId);
        // Mettre à jour l'action dans la base de données
        this.updateSectionAction(sectionId, 'tousLots')
            .then(() => {
                // Charger les lots après la mise à jour réussie
                this.loadLots(sectionId, 'tous');
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour de l\'action:', error);
                this.displayError('Erreur lors de la mise à jour de l\'action');
            });
    },

    handleLotsAcquerir(sectionId) {
        // console.log('Gestion lots à acquérir pour section:', sectionId);

        // Charger d'abord tous les lots pour le modal
        $.ajax({
            url: URLROOT + '/public/json/lot.php?action=getLotAcquerir&idImmeuble=' + CONFIG.immeubleId + "&idSommaire=" + CONFIG.sommaire.idSommaire,
            method: 'GET',
            dataType: 'json',
            success: (response) => {
                console.log(response);
                {
                    // Afficher le modal avec tous les lots disponibles
                    this.displayLotsAcquerir(response, sectionId);
                }
            },
            error: (console) => {
                console.error('Erreur lors du chargement des lots:');
                console.error(console);
                this.displayError('Erreur lors du chargement des lots');
            }
        });
    },

    //CCTP
    handleCCTP(sectionId) {
        console.log('Action: CCTP pour la section', sectionId);
        console.log('immeuble: ', CONFIG.immeubleId);
        $('#cctpModal').modal('show');
        $('#sectionIdActuel').val(sectionId);
        console.log('Action: CCTP pour la section Global', sectionId);
        this.updateSectionAction(sectionId, 'cctp')
            .then(() => {
                // Charger les lots après la mise à jour réussie
                this.loadModalArticle();
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour de l\'action:', error);
                this.displayError('Erreur lors de la mise à jour de l\'action');
            });
    },

    handleCCTPG(sectionId) {
        console.log('Action: CCTP pour la section Global', sectionId);
        this.updateSectionAction(sectionId, 'cctpG')
            .then(() => {
                // Charger les lots après la mise à jour réussie
                displayCCTPGlobal(sectionId);
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour de l\'action:', error);
                this.displayError('Erreur lors de la mise à jour de l\'action');
            });
    },

    handleSituationCopro(sectionId) {
        console.log('Action: Situation Copro', sectionId);
        this.updateSectionAction(sectionId, 'situationCopro')
            .then(() => {
                // Charger les lots après la mise à jour réussie
                displaySituationCopro(sectionId);
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour de l\'action:', error);
                this.displayError('Erreur lors de la mise à jour de l\'action');
            });
    },

    loadModalArticle() {
        $.ajax({
            url: URLROOT + '/public/json/cctp.php?action=getLinesCCTPForImmeuble&type=&idImmeuble=' + CONFIG.immeubleId + "&idProjet=" + $('#idProjet').val(),
            method: 'GET',
            dataType: 'json',
            success: (response) => {
                console.log("response");
                console.log(response);
                let html = '';
                if (response) {
                    response.forEach((article, index) => {
                        let mRemise = article.montant - (article.montant * (article.tauxRemise == '' ? 0 : Number(article.tauxRemise)) / 100);
                        let ttc = mRemise + (mRemise * (article.tva == '' ? 0 : Number(article.tva)) / 100);
                        html += ` <tr>
                                                    <td>
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input article-checkbox"
                                                                id="article-${article.idTableSection}" data-article-id="${article.idTableSection}" data-article-numero="<?= $article->numeroTable ?>">
                                                            <label class="custom-control-label" for="article-${article.idTableSection}">
                                                                ${index + 1}
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>${article.libelle}</td>
                                                    <td>${article.unite}</td>
                                                    <td>${article.quantite}</td>
                                                    <td>${formatNumber(article.prix)}</td>
                                                    <td>${formatNumber(article.montant)}</td>
                                                    <td>${article.tauxRemise}</td>
                                                    <td>${formatNumber(mRemise)}</td>
                                                    <td>${article.tva}</td>
                                                    <td>${formatNumber(ttc)}</td>
                                                </tr> `;
                    });

                }
                $('#cctpTableBody').html(html);
                //Afficher le modal avec tous les cctp disponibles

            },
            error: (response) => {
                console.error('Erreur lors du chargement du cctp:', response);
            }
        });

    },

    // méthode pour la mise à jour de l'action
    updateSectionAction(sectionId, action) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: CONFIG.routes.section.updateAction,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    sectionId: sectionId,
                    action: action,
                    immeubleId: CONFIG.immeubleId,
                    projetId: CONFIG.projetId
                }),
                success: function (response) {
                    console.log(response)
                    if (response.success) {
                        resolve(response);
                    } else {
                        reject(response.error);
                    }
                },
                error: function (response) {
                    console.log(response);
                }
            });
        });
    },

    // methode pour charger les lots
    loadLots(sectionId, type) {
        // Récupérer l'action de la section courante
        const section = mockData.sections.find(s => s.idSection == sectionId);
        const currentAction = section?.action || '';

        // Si l'action est lotAssocie, charger le tableau de simulation
        if (currentAction == 'lotAssocie') {
            console.log('Chargement des variables de simulation pour lotAssocie');

            // D'abord, chercher la section avec lotAcquerir dans toutes les sections
            const lotAcquerirSection = mockData.sections.find(s => s.action === 'lotAcquerir');

            if (!lotAcquerirSection) {
                this.showErrorMessage('Aucune section avec l\'action lotAcquerir trouvée');
                return;
            }

            const existingSimulations = document.getElementById('all-simulations');
            if (existingSimulations && existingSimulations.children.length > 0) {
                console.log('Les simulations existent déjà, pas de recalcul nécessaire');
                return;
            }

            // Maintenant, récupérer les lots à acquérir de cette section
            $.ajax({
                url: CONFIG.routes.sectionLots.getAllLotsToAcquire,
                method: 'GET',
                data: {
                    immeubleId: CONFIG.immeubleId,
                    sectionId: lotAcquerirSection.idSection,
                    aAcquerir: 1
                },
                // Dans la partie success du ajax call dans loadLots
                success: async (lotsResponse) => {
                    if (lotsResponse.success && lotsResponse.data && lotsResponse.data.length > 0) {
                        const lots = lotsResponse.data;
                        console.log('Lots à acquérir trouvés:', lots);

                        sommeResultats = createResultatObject();
                        resultatsSimulation = createResultatObject();

                        // Créer un conteneur pour tous les tableaux de simulation
                        const containerSelector = `#lots-container-${sectionId}`;
                        $(containerSelector).empty().append(`
                        <div class="alert alert-info">
                            <strong>Nombre total de lots à acquérir :</strong> ${lots.length}
                            <button hidden type="button" 
                                    class="btn btn-primary add-variable-btn" 
                                    data-section-id="${sectionId}"
                                    data-lot-ids='${JSON.stringify(lots.map(lot => lot.idApp))}'>
                                <i class="fas fa-plus"></i> Ajouter une variable
                            </button>
                        </div>
                        <div id="all-simulations"></div>
                    `);

                        // Initialiser le compteur de lots traités
                        let lotCounter = 1;
                        let totalLotsTraites = 0;
                        const lotsATraiter = lots.filter(l => l.siLotPrincipal == "Oui").length;
                        for (const lot of lots) {
                            if (lot.siLotPrincipal == "Oui") {
                                try {
                                    const response = await $.ajax({
                                        url: CONFIG.routes.variableSimulation.getForSection,
                                        method: 'GET',
                                        data: {
                                            sectionId: sectionId,
                                            lotId: lot.idApp
                                        }
                                    });
                                    if (response.success && response.data) {
                                        const lotContainerId = `lot-simulation-${lot.idApp}`;
                                        $('#all-simulations').append(`
                                            <div class="card mb-1">
                                                <div class="card-header bg-primary text-white row p-1" id="heading-${lot.idApp}">
                                                    <button class="btn col-md-9 text-white text-left"  data-toggle="collapse" data-target="#${lotContainerId}" aria-expanded="false" aria-controls="${lotContainerId}">
                                                         <h6 class="card-title ">
                                                        <i class="fas fa-chevron-right"></i> <i class="fa fa-chevron-down pull-right"></i> (${lotCounter}) PREVISIONNEL AVEC LES DONNEES REELLES <br> Lot ${lot.lot} - Type: ${lot.typeLot} <br>
                                                        <small>
                                                            Propriétaire: ${lot.proprietaire || ''}<br>
                                                            Bâtiment: ${lot.batiment || ''}, Étage: ${lot.etage || ''},
                                                            Surface: ${lot.surface || ''} m²
                                                        </small>
                                                    </h6>
                                                    </button>
                                                   
                                                </div>
                                                <div class="card-body collapse" id="${lotContainerId}" aria-labelledby="heading-${lot.idApp}" data-parent="#all-simulations">
                                                </div>
                                            </div>
                                        `);

                                        // console.log("CECI est le log des variables de simulation ", response.data);

                                        this.displayVariableSimulation(response.data, sectionId, lot.idApp, lotContainerId);
                                        lotCounter++;
                                        totalLotsTraites++;

                                        // Si c'est le dernier lot, afficher le récapitulatif global
                                        if (totalLotsTraites == lotsATraiter) {

                                            afficherSommeResultatsHTML();
                                        }
                                    }
                                } catch (error) {
                                    console.error(`Erreur pour le lot ${lot.lot}:`, error);
                                    this.showErrorMessage(`Erreur lors du chargement des données pour le lot ${lot.lot}`);
                                    continue;
                                }
                            }
                        }
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Erreur lors du chargement des lots:', error);
                    this.showErrorMessage('Erreur lors du chargement des lots à acquérir');
                }
            });
            return;
        }

        if (currentAction == 'tpg') {
            console.log('=== Chargement TPG ===');
            // Créer d'abord le conteneur
            const containerSelector = `#lots-container-${sectionId}`;
            $(containerSelector).empty().append(`
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">TABLEAU PRÉVISIONNEL GLOBALE</h5>
                    </div>
                    <div class="card-body">
                        <div id="recap-global">
                            <div class="text-center">Chargement du tableau...</div>
                        </div>
                    </div>
                </div>
            `);

            // TOUJOURS générer un nouveau TPG, sans tenir compte de l'HTML sauvegardé
            this.generateNewTPG(sectionId);
            return;
        }


        // Le reste du code pour les autres actions reste inchangé
        let ajaxUrl, ajaxData;

        if (currentAction == 'tousLots') {
            ajaxUrl = CONFIG.routes.sectionLots.getAllLots;
            ajaxData = {
                immeubleId: CONFIG.immeubleId,
                sectionId: sectionId,
                type: 'tous'
            };
        } else if (currentAction == 'lotAcquerir') {
            console.log('Chargement des lots à acquérir');
            ajaxUrl = CONFIG.routes.sectionLots.getAllLotsToAcquire;
            ajaxData = {
                immeubleId: CONFIG.immeubleId,
                sectionId: sectionId,
                aAcquerir: 1
            };
        } else {
            console.error('Action non reconnue:', currentAction);
            return;
        }

        $.ajax({
            url: ajaxUrl,
            method: 'GET',
            dataType: 'json',
            data: ajaxData,
            success: function (response) {
                if (response.success && response.data && response.data.length > 0) {
                    if (currentAction == 'lotAcquerir') {
                        this.displayLotsAcquireTable(response.data, sectionId);
                    } else {
                        this.displayLots(response.data, sectionId);
                    }
                }
            }.bind(this),
            error: function (response) {
                console.error('Erreur Ajax:', response);
                this.showErrorMessage('Erreur lors du chargement des lots');
            }.bind(this)
        });
    },

    // Nouvelle méthode pour afficher les erreurs sans utiliser de modal
    showErrorMessage(message) {
        const alertElement = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        // Trouver ou créer le conteneur d'erreurs
        let errorContainer = document.querySelector('#error-messages');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.id = 'error-messages';
            document.querySelector('main').prepend(errorContainer);
        }

        // Ajouter le message d'erreur
        $(errorContainer).append(alertElement);

        // Auto-supprimer après 5 secondes
        setTimeout(() => {
            $('.alert').fadeOut('slow', function () {
                $(this).remove();
            });
        }, 5000);
    },

    displayLots(lots, sectionId) {
        console.log('Affichage des lots pour la section:', sectionId);
        const containerSelector = `#lots-container-${sectionId}`;

        const html = `
         <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Liste de tous les lots</h5>
                </div>
                <div class="card-body">
            <div class="table-responsive">
                <table id="lotsTable-${sectionId}" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>N°Ligne</th>
                            <th>Copropriétaire</th>
                            <th>Type Lot</th>
                            <th>Lot Principal</th>
                            <th>Batiment</th>
                            <th>N° Lot</th>
                            <th>Etage</th>
                            <th>Porte</th>
                            <th>Surface</th>
                            <th>Tantième</th>
                             <th>Logement</th>
                            <th>Si insalubre</th>
                            <th>Solde</th>
                        </tr>
                    </thead>
                </table>
            </div>
            </div>
            </div>
        `;

        $(containerSelector).html(html);
        $(`#lots-section-${sectionId}`).show();

        // Initialiser DataTables
        $(`#lotsTable-${sectionId}`).DataTable({
            data: lots,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
            },
            columns: [
                { data: 'index', defaultContent: '' },
                { data: 'proprietaire', defaultContent: '' },
                {
                    data: 'typeLot', defaultContent: ''
                },
                {
                    data: 'siLotPrincipal', defaultContent: ''
                },
                {
                    data: 'batiment', defaultContent: ''
                },
                {
                    data: 'lot', defaultContent: ''
                },
                {
                    data: 'etage', defaultContent: ''
                }, {
                    data: 'codePorte', defaultContent: ''
                },
                {
                    data: 'surface', defaultContent: ''
                },
                {
                    data: 'tantieme', defaultContent: ''
                },
                {
                    data: 'logementVacant', defaultContent: ''
                },
                {
                    data: 'siInsalubre', defaultContent: ''
                },
                {
                    data: 'solde', defaultContent: ''
                },
                // {
                //     data: 'idApp',
                //     render: function (data) {
                //         return `
                //             <div class="btn-group">
                //                 <button class="btn btn-sm btn-primary" title="Éditer" onclick="Actions.editLot(${data})">
                //                     <i class="fas fa-edit"></i>
                //                 </button>
                //                 <button class="btn btn-sm btn-info" title="Détails" onclick="Actions.viewLotDetails(${data})">
                //                     <i class="fas fa-eye"></i>
                //                 </button>
                //             </div>
                //         `;
                //     }
                // }
            ],
            pageLength: 20,
            responsive: true
        });
    },

    displayVariableSimulation(variables, sectionId, lotId, containerId) {
        // console.log('Affichage des variables pour la section:', sectionId, 'lot:', lotId);
        // console.log('Variables à afficher:', variables);

        // Utiliser le containerId fourni ou le conteneur par défaut
        const containerSelector = containerId ? `#${containerId}` : `#lots-container-${sectionId}`;

        // Regrouper les variables par catégorie
        let numberLineVar = 1
        const groupedVariables = variables.reduce((acc, variable) => {
            const cat = variable.categorie || 'Autre';
            variable.numLigne = Number(numberLineVar);
            if (!acc[cat]) acc[cat] = [];
            acc[cat].push(variable);
            numberLineVar++;
            return acc;
        }, {});

        resultatsSimulation.coutOperationnel.details = extraireValeurs(
            generateSimulationRows(variables, 1, groupedVariables['1']?.map(v => v.idVariableSimulation) || [], "Total Coût opérationnel")
        );
        resultatsSimulation.coutOperationnel.total = resultatsSimulation.coutOperationnel.details[resultatsSimulation.coutOperationnel.details.length - 1].cout;

        resultatsSimulation.coutFinancier.details = extraireValeurs(
            generateSimulationRows(variables, 2, [7, 8], "Total Coût financier des obligataires sur 24 Mois")
        );
        resultatsSimulation.coutFinancier.total = resultatsSimulation.coutFinancier.details[resultatsSimulation.coutFinancier.details.length - 1].cout;

        resultatsSimulation.coutDivers.details = extraireValeurs(
            generateSimulationRows(variables, 3, [9], "Total Coût divers (Commission totale)")
        );
        resultatsSimulation.coutDivers.total = resultatsSimulation.coutDivers.details[resultatsSimulation.coutDivers.details.length - 1].cout;

        resultatsSimulation.sommeCouts.details = extraireValeurs(
            generateSimulationRows(variables, 4, [1, 2, 3], "Somme des coûts")
        );
        resultatsSimulation.sommeCouts.total = resultatsSimulation.sommeCouts.details[resultatsSimulation.sommeCouts.details.length - 1].cout;

        resultatsSimulation.resultatFinal.details = extraireValeurs(
            generateSimulationRows(variables, 5, [1, 2], "Résultat final")
        );
        resultatsSimulation.resultatFinal.total = resultatsSimulation.resultatFinal.details[resultatsSimulation.resultatFinal.details.length - 1].cout;

        // Afficher les résultats dans la console de manière structurée
        // console.log('=== RÉSULTATS DE LA SIMULATION ===');
        // console.log('Coût opérationnel:', resultatsSimulation.coutOperationnel);
        // console.log('Coût financier:', resultatsSimulation.coutFinancier);
        // console.log('Coût divers:', resultatsSimulation.coutDivers);
        // console.log('Somme des coûts:', resultatsSimulation.sommeCouts);
        // console.log('Résultat final:', resultatsSimulation.resultatFinal);

        const html = `
            <div class="row">
                <button class="btn btn-primary btn-sm col-md-3 ml-5" onclick="toggleVariables('${sectionId}-${lotId}')">
                                                        <i class="fas fa-table"></i> Afficher/Masquer les variables
                </button>
            </div>
            <div class="row">
                <!-- Tableau des variables -->
                <div class="col-12 mb-4" id="variables-container-${sectionId}-${lotId}" 
                     data-lot-id="${lotId}" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Tableau des variables</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="variableTable-${sectionId}-${lotId}" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>N°Ligne</th>
                                            <th>Variable</th>
                                            <th>Type</th>
                                            <th>Valeur</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${Object.entries(groupedVariables).map(([categorie, vars]) =>
            vars.map((variable) =>
                `
                                                <tr>
                                                    <td>${Number(variable.numLigne)}</td>
                                                    <td>${variable.nomVariableSimulation}</td>
                                                    <td>${variable.typeValeurSimulation == 'montant' ? '€' : '%'}</td>
                                                    <td>
                                                        <input type="number" onblur=""
                                                            class="form-control form-control-sm variable-value" 
                                                            value="${variable.montant || variable.valeurVariableSimulation || '0'}"
                                                            data-variable-id="${variable.idVariableSimulation}"
                                                            step="0.01">
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary save-variable" 
                                                                data-variable-id="${variable.idVariableSimulation}">
                                                            <i class="fas fa-save"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            `).join('')
        ).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
    
                <!-- Tableau de simulation -->
                <div class="col-12" >
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="simulationTable-${sectionId}-${lotId}" class="table table-bordered">
                                    <thead >
                                        <tr class="text-white" bgcolor=" #36b92a;">
                                            <th>Ligne</th>
                                            <th>Catégorie</th>
                                            <th>Coût pour un logement(en euros)</th>
                                            <th>% du coût total</th>
                                            <th>Coût au m²</th>
                                            <th>% du prix de revente</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                      ${generateSimulationRows(variables, 1, groupedVariables['1']?.map(v => v.idVariableSimulation) || [], "Total Coût opérationnel")}
                                        ${generateSimulationRows(variables, 2, [7, 8], "Total Coût financier des obligataires sur 24 Mois")}
                                        ${generateSimulationRows(variables, 3, [9], "Total Coût divers (Commission totale)")}
                                        ${generateSimulationRows(variables, 4, [1, 2, 3], "Somme des coûts")}
                                        ${generateSimulationRows(variables, 5, [1, 2], "Résultat final")}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        tableauxSimulations.push(resultatsSimulation);
        ajouterALaSomme(resultatsSimulation);



        // Mettre à jour le conteneur
        $(containerSelector).empty().html(html);

        // Initialiser DataTable avec un ID unique
        const tableId = `variableTable-${sectionId}-${lotId}`;
        $(`#${tableId}`).DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
            },
            pageLength: 15,
            responsive: true,
        });

        const htmlTab = $(`#simulationTable-${sectionId}-${lotId}`).html();
        // console.log('HTML à afficher : ', htmlTab);
        $.ajax({
            url: CONFIG.routes.variableSimulation.htmlProjet,
            method: 'POST',
            data: {
                lotId: lotId,
                sectionId: sectionId,
                html: htmlTab
            },
            success: function (response) {
                // console.log('Raw Response:');
                // console.log(response);
                // try {
                //     const jsonResponse = JSON.parse(response);
                //     if (jsonResponse.success) {
                //         // console.log('Chargement HTML :', jsonResponse.data);
                //     } else {
                //         console.error('Erreur lors du chargement du HTML :', jsonResponse.error);
                //     }
                // } catch (e) {
                //     console.error('Erreur de parsing JSON:', e);
                // }
            },
            error: function (response) {
                console.error('Erreur Ajax:');
                console.log(response)
            }
        });

        // function saveVariableValue(this) {
        //     const btn = this;
        //     const row = btn.closest('tr');
        //     const variableId = btn.data('variable-id');
        //     const montant = row.find('.variable-value').val();
        //     console.log("v " + variableId)
        //     console.log("m " + montant)
        //     console.log("l " + lotId)
        //     console.log("s " + sectionId)

        //     btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        //     $.ajax({
        //         url: CONFIG.routes.variableSimulation.saveValue,
        //         method: 'POST',
        //         contentType: 'application/json',
        //         data: JSON.stringify({
        //             sectionId: sectionId,
        //             variableId: variableId,
        //             montant: montant,
        //             lotId: lotId
        //         }),
        //         success: function (response) {
        //             if (response.success) {
        //                 btn.removeClass('btn-primary').addClass('btn-success')
        //                     .html('<i class="fas fa-check"></i>');

        //                 // Récupérer les variables mises à jour pour ce lot
        //                 $.ajax({
        //                     url: CONFIG.routes.variableSimulation.getForSection,
        //                     method: 'GET',
        //                     data: {
        //                         sectionId: sectionId,
        //                         lotId: lotId
        //                     },
        //                     success: function (variablesResponse) {
        //                         if (variablesResponse.success && variablesResponse.data) {
        //                             const variables = variablesResponse.data;
        //                             //reload table
        //                             const simulationTableBody = $(`#simulationTable-${sectionId}-${lotId} tbody`);

        //                             const lotContainerId = `lot-simulation-${lotId}`;


        //                             // // Grouper les variables par catégorie
        //                             // const categorizedVars = variables.reduce((acc, v) => {
        //                             //     if (v.categorie) {
        //                             //         acc[v.categorie] = acc[v.categorie] || [];
        //                             //         acc[v.categorie].push(v);
        //                             //     }
        //                             //     return acc;
        //                             // }, {});

        //                             // // Générer les lignes


        //                             simulationTableBody.html(`
        //                             ${generateSimulationRows(variables, 1, groupedVariables['1']?.map(v => v.idVariableSimulation) || [], "Total Coût opérationnel")}
        //                             ${generateSimulationRows(variables, 2, [7, 8], "Total Coût financier des obligataires sur 24 Mois")}
        //                             ${generateSimulationRows(variables, 3, [9], "Total Coût divers (Commission totale)")}
        //                             ${generateSimulationRows(variables, 4, [1, 2, 3], "Somme des coûts")}
        //                             ${generateSimulationRows(variables, 5, [1, 2], "Résultat final")}
        //                             `);
        //                             afficherSommeResultatsHTML();
        //                             // ${generateSimulationRows(variables, 1, categorizedVars[1]?.map(v => v.idVariableSimulation) || [], "Total Coût opérationnel")}
        //                             // ${generateSimulationRows(variables, 2, categorizedVars[2]?.map(v => v.idVariableSimulation) || [], "Total Coût financier des obligataires sur 24 Mois")}
        //                             // ${generateSimulationRows(variables, 3, categorizedVars[3]?.map(v => v.idVariableSimulation) || [], "Total Coût divers (Commission totale)")}
        //                             // ${generateSimulationRows(variables, 4, Object.keys(categorizedVars).slice(0, 3).map(cat => cat), "Somme des coûts")}
        //                             // ${generateSimulationRows(variables, 5, [1, 2], "Résultat final")}
        //                             // Mise à jour du bouton
        //                             setTimeout(() => {
        //                                 btn.removeClass('btn-success')
        //                                     .addClass('btn-primary')
        //                                     .html('<i class="fas fa-save"></i>')
        //                                     .prop('disabled', false);
        //                             }, 1000);


        //                         }
        //                     },
        //                     error: function (xhr, status, error) {
        //                         console.error('Erreur lors du rechargement des variables:', error);
        //                         btn.removeClass('btn-success').addClass('btn-danger')
        //                             .html('<i class="fas fa-times"></i>')
        //                             .prop('disabled', false);
        //                     }
        //                 });
        //             }
        //         },
        //         error: function (xhr, status, error) {
        //             console.error('Erreur lors de la sauvegarde:', error);
        //             btn.removeClass('btn-primary').addClass('btn-danger')
        //                 .html('<i class="fas fa-times"></i>')
        //                 .prop('disabled', false);
        //         }
        //     });

        // }
        // Gestionnaire d'événements pour la sauvegarde avec IDs uniques
        $(`#${tableId}`).on('click', '.save-variable', function () {
            const btn = $(this);
            const row = btn.closest('tr');
            const variableId = btn.data('variable-id');
            const montant = row.find('.variable-value').val();
            console.log("v " + variableId)
            console.log("m " + montant)
            console.log("l " + lotId)
            console.log("s " + sectionId)

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: CONFIG.routes.variableSimulation.saveValue,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    sectionId: sectionId,
                    variableId: variableId,
                    montant: montant,
                    lotId: lotId
                }),
                success: function (response) {
                    if (response.success) {
                        btn.removeClass('btn-primary').addClass('btn-success')
                            .html('<i class="fas fa-check"></i>');

                        // Récupérer les variables mises à jour pour ce lot
                        $.ajax({
                            url: CONFIG.routes.variableSimulation.getForSection,
                            method: 'GET',
                            data: {
                                sectionId: sectionId,
                                lotId: lotId
                            },
                            success: function (variablesResponse) {
                                if (variablesResponse.success && variablesResponse.data) {
                                    const variables = variablesResponse.data;
                                    //reload table
                                    const simulationTableBody = $(`#simulationTable-${sectionId}-${lotId} tbody`);

                                    const lotContainerId = `lot-simulation-${lotId}`;


                                    // // Grouper les variables par catégorie
                                    // const categorizedVars = variables.reduce((acc, v) => {
                                    //     if (v.categorie) {
                                    //         acc[v.categorie] = acc[v.categorie] || [];
                                    //         acc[v.categorie].push(v);
                                    //     }
                                    //     return acc;
                                    // }, {});

                                    // // Générer les lignes


                                    simulationTableBody.html(`
                                    ${generateSimulationRows(variables, 1, groupedVariables['1']?.map(v => v.idVariableSimulation) || [], "Total Coût opérationnel")}
                                    ${generateSimulationRows(variables, 2, [7, 8], "Total Coût financier des obligataires sur 24 Mois")}
                                    ${generateSimulationRows(variables, 3, [9], "Total Coût divers (Commission totale)")}
                                    ${generateSimulationRows(variables, 4, [1, 2, 3], "Somme des coûts")}
                                    ${generateSimulationRows(variables, 5, [1, 2], "Résultat final")}
                                    `);
                                    afficherSommeResultatsHTML();
                                    // ${generateSimulationRows(variables, 1, categorizedVars[1]?.map(v => v.idVariableSimulation) || [], "Total Coût opérationnel")}
                                    // ${generateSimulationRows(variables, 2, categorizedVars[2]?.map(v => v.idVariableSimulation) || [], "Total Coût financier des obligataires sur 24 Mois")}
                                    // ${generateSimulationRows(variables, 3, categorizedVars[3]?.map(v => v.idVariableSimulation) || [], "Total Coût divers (Commission totale)")}
                                    // ${generateSimulationRows(variables, 4, Object.keys(categorizedVars).slice(0, 3).map(cat => cat), "Somme des coûts")}
                                    // ${generateSimulationRows(variables, 5, [1, 2], "Résultat final")}
                                    // Mise à jour du bouton
                                    setTimeout(() => {
                                        btn.removeClass('btn-success')
                                            .addClass('btn-primary')
                                            .html('<i class="fas fa-save"></i>')
                                            .prop('disabled', false);
                                    }, 1000);


                                }
                            },
                            error: function (xhr, status, error) {
                                console.error('Erreur lors du rechargement des variables:', error);
                                btn.removeClass('btn-success').addClass('btn-danger')
                                    .html('<i class="fas fa-times"></i>')
                                    .prop('disabled', false);
                            }
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Erreur lors de la sauvegarde:', error);
                    btn.removeClass('btn-primary').addClass('btn-danger')
                        .html('<i class="fas fa-times"></i>')
                        .prop('disabled', false);
                }
            });
        });
    },

    displayNoLots() {
        const message = `
            <div class="modal fade" id="lotsModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Liste des lots</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-center">Aucun lot trouvé pour cette section.</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#lotsModal').remove();
        $('body').append(message);
        new bootstrap.Modal($('#lotsModal')).show();
    },

    displayError(errorMessage) {
        const error = `
            <div class="modal fade" id="errorModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Erreur</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-danger">${errorMessage}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#errorModal').remove();
        $('body').append(error);
        new bootstrap.Modal($('#errorModal')).show();
    },


    // displayLotsAcquireTable(lots, sectionId) {
    //     console.log('Affichage du tableau des lots à acquérir pour la section:', sectionId);
    //     console.log(lots);
    //     const containerSelector = `#lots-container-${sectionId}`;

    //     const html = `
    //     <div class="card">
    //             <div class="card-header">
    //                 <h5 class="card-title mb-0">Tableau des lots à acquérir</h5>
    //             </div>
    //             <div class="card-body">
    //         <div class="table-responsive">
    //             <table id="lotsAcquerirTable-${sectionId}" class="table table-bordered table-striped">
    //                 <thead>
    //                     <tr>
    //                         <th>N° Ligne</th>
    //                         <th>Copropriétaire</th>
    //                         <th>Type Lot</th>
    //                         <th>Lot Princiapal</th>
    //                         <th>Batiment</th>
    //                         <th>N° Lot</th>
    //                         <th>Etage</th>
    //                         <th>Porte</th>
    //                         <th>Surface</th>
    //                         <th>Tantiéme</th>
    //                         <th>Solde</th>
    //                     </tr>
    //                 </thead>
    //             </table>
    //         </div>
    //         </div>
    //         </div>
    //     `;

    //     $(containerSelector).html(html);
    //     $(`#lots-section-${sectionId}`).show();

    //     // Initialiser DataTables
    //     $(`#lotsAcquerirTable-${sectionId}`).DataTable({
    //         data: lots,
    //         language: {
    //             url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
    //         },
    //         columns: [
    //             { data: 'index', defaultContent: '' },
    //             { data: 'proprietaire', defaultContent: '' },
    //             {
    //                 data: 'typeLot', defaultContent: ''
    //             },
    //             {
    //                 data: 'siLotPrincipal', defaultContent: ''
    //             },
    //             {
    //                 data: 'batiment', defaultContent: ''
    //             },
    //             {
    //                 data: 'lot', defaultContent: ''
    //             },
    //             {
    //                 data: 'etage', defaultContent: ''
    //             }, {
    //                 data: 'codePorte', defaultContent: ''
    //             }, {
    //                 data: 'surface', defaultContent: ''
    //             },
    //             {
    //                 data: 'tantieme', defaultContent: ''
    //             },
    //             {
    //                 data: 'solde', defaultContent: ''
    //             },
    //             // {
    //             //     data: 'idApp',
    //             //     render: function (data, type, row) {
    //             //         return `
    //             //             <div class="btn-group">
    //             //                 <button class="btn btn-sm btn-warning" title="Supprimer des lots à acquérir" onclick="Actions.removeLotAcquerir(${data}, ${row.idSection})">
    //             //                     <i class="fas fa-trash"></i>
    //             //                 </button>
    //             //             </div>
    //             //         `;
    //             //     }
    //             // }
    //         ],
    //         pageLength: 10,
    //         responsive: true
    //     });
    // },


    displayLotsAcquireTable(lots, sectionId) {
        // console.log('Affichage du tableau des lots à acquérir pour la section:', sectionId);
        // console.log(lots.map(lot => lot.idApp));
        // console.log(lots);
        const containerSelector = `#lots-container-${sectionId}`;

        const html = `
        <div class="card">
                <div class="card-header">
                    <div class="alert alert-info">
                            <strong>Liste des lots à acquérir :</strong> ${lots.length}
                             <button onclick="Actions.handleLotsAcquerir(${sectionId})" class="btn btn-sm btn-info float-right mt-0">Ajouter des lots</button>
                        </div>
                </div>
                <div class="card-body">
            <div class="table-responsive">
                <table id="lotsAcquerirTable-${sectionId}" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>N° Ligne</th>
                            <th>Copropriétaire</th>
                            <th>Type Lot</th>
                            <th>Lot Principal</th>
                            <th>Batiment</th>
                            <th>N° Lot</th>
                            <th>Etage</th>
                            <th>Porte</th>
                            <th>Surface</th>
                            <th>Tantiéme</th>
                             <th>Logement</th>
                            <th>Si insalubre</th>
                            <th>Solde</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            </div>
            </div>
        `;

        $(containerSelector).html(html);
        $(`#lots-section-${sectionId}`).show();
        $(`#cctpGlobal-section-${sectionId}`).hide();


        // Initialiser DataTables
        $(`#lotsAcquerirTable-${sectionId}`).DataTable({
            data: lots,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
            },
            columns: [
                { data: 'index', defaultContent: '' },
                { data: 'proprietaire', defaultContent: '' },
                {
                    data: 'typeLot', defaultContent: ''
                },
                {
                    data: 'siLotPrincipal',
                    render: function (data, type, row) {
                        // Utiliser l'ID de section stocké
                        let typeL = data == "Oui" ? "Principal" : "Secondaire";
                        return `${typeL}
                            `;
                    }
                },
                {
                    data: 'batiment', defaultContent: ''
                },
                {
                    data: 'lot', defaultContent: ''
                },
                {
                    data: 'etage', defaultContent: ''
                }, {
                    data: 'codePorte', defaultContent: ''
                }, {
                    data: 'surface', defaultContent: ''
                },
                {
                    data: 'tantieme', defaultContent: ''
                },
                {
                    data: 'logementVacant', defaultContent: ''
                },
                {
                    data: 'siInsalubre', defaultContent: ''
                },
                {
                    data: 'solde', defaultContent: ''
                },
                {
                    data: 'idApp',
                    render: function (data, type, row) {
                        // Utiliser l'ID de section stocké
                        const currentSectionId = sectionId; // On utilise le sectionId passé à displayLotsAcquireTable
                        return `
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-danger" title="Supprimer des lots à acquérir" 
                                            onclick="Actions.removeLotAcquerir(${data}, ${currentSectionId})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            `;
                    }
                }
            ],
            pageLength: 10,
            responsive: true
        });
    },


    displayLotsAcquerir(lots, sectionId) {
        console.log('Affichage des lots à acquérir pour la section:', sectionId);

        // Variable pour stocker les sélections persistantes
        const selectedLotsMap = new Map();

        // Détruire la table existante si elle existe
        if ($.fn.DataTable.isDataTable('#lotsAcquerirTable')) {
            $('#lotsAcquerirTable').DataTable().destroy();
        }

        const modalContent = `
        <div class="modal fade" id="lotsAcquerirModal" tabindex="-1" aria-labelledby="lotsAcquerirModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="lotsAcquerirModalLabel">Sélection des lots à acquérir</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="actionBarContainer" class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button class="btn btn-sm btn-outline-secondary" id="selectAllLotsAcquerir">
                                        <i class="fas fa-check-square mr-1"></i>Tout sélectionner
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" id="deselectAllLotsAcquerir">
                                        <i class="fas fa-square mr-1"></i>Tout désélectionner
                                    </button>
                                </div>
                                <div>
                                    <span class="badge badge-info" id="selectedLotsCount">0 lot(s) sélectionné(s)</span>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="lotsAcquerirTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>N°Ligne</th>
                                        <th>Copropriétaire</th>
                                        <th>Type Lot</th>
                                        <th>Lot principal</th>
                                        <th>Batiment</th>
                                        <th>N° Lot</th>
                                        <th>Etage</th>
                                        <th>Porte</th>
                                        <th>Surface</th>
                                        <th>Tantieme</th>
                                         <th>Logement</th>
                            <th>Si insalubre</th>
                                        <th>Solde</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${lots.filter((l) => l.siLotPrincipal == "Oui").map((lot, index) => `
                                        <tr>
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input lot-acquerir-checkbox" 
                                                           id="lot-${lot.idApp}" data-id="${lot.idApp}" data-numero="${lot.numeroApp}">
                                                    <label class="custom-control-label" for="lot-${lot.idApp}">
                                                        ${index}
                                                    </label>
                                                </div>
                                            </td>
                                            <td>${lot.proprietaire || '-'}</td>
                                            <td>${lot.typeLot || '-'}</td>
                                            <td>${lot.siLotPrincipal || '-'}</td>
                                            <td>${lot.batiment || '-'}</td>
                                            <td>${lot.lot || '-'}</td>
                                            <td>${lot.etage || '-'}</td>
                                            <td>${lot.codePorte || '-'}</td>
                                            <td>${lot.surface || '-'}</td>
                                            <td>${lot.tantieme || '-'}</td>
                                            <td>${lot.logementVacant || '-'}</td>
                                            <td>${lot.siInsalubre || '-'}</td>
                                            <td>${lot.solde || '-'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" id="validateLotsSelection">
                            Valider la sélection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

        // Supprimer le modal existant et ajouter le nouveau
        $('#lotsAcquerirModal').remove();
        $('body').append(modalContent);

        // Initialiser DataTable avec la gestion des événements de page
        const dataTable = $('#lotsAcquerirTable').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
            },
            order: [[0, 'asc']],
            pageLength: 10,
            responsive: true,
            drawCallback: function () {
                // Restaurer les sélections après chaque redessinage
                $('.lot-acquerir-checkbox').each(function () {
                    const lotId = $(this).data('id');
                    if (selectedLotsMap.has(lotId)) {
                        $(this).prop('checked', true);
                    }
                });
                updateSelectedLots();
            }
        });

        // Créer l'instance du modal
        const modalElement = document.getElementById('lotsAcquerirModal');
        const modal = new bootstrap.Modal(modalElement, {
            keyboard: true,
            backdrop: 'static'
        });

        // Fonction pour mettre à jour le nombre de lots sélectionnés
        function updateSelectedLots() {
            const selectedCount = selectedLotsMap.size;
            $('#selectedLotsCount').text(`${selectedCount} lot(s) sélectionné(s)`);
        }

        // Gestionnaires d'événements
        $('#selectAllLotsAcquerir').on('click', function () {
            lots.forEach(lot => {
                selectedLotsMap.set(lot.idApp, lot.numeroApp);
            });
            $('.lot-acquerir-checkbox').prop('checked', true);
            updateSelectedLots();
        });

        $('#deselectAllLotsAcquerir').on('click', function () {
            selectedLotsMap.clear();
            $('.lot-acquerir-checkbox').prop('checked', false);
            updateSelectedLots();
        });

        // Utiliser délégation pour les événements de changement de checkbox
        $(document).on('change', '.lot-acquerir-checkbox', function () {
            const lotId = $(this).data('id');
            const lotNumero = $(this).data('numero');

            if ($(this).is(':checked')) {
                selectedLotsMap.set(lotId, lotNumero);
            } else {
                selectedLotsMap.delete(lotId);
            }

            updateSelectedLots();
        });

        $('#validateLotsSelection').on('click', function () {
            const selectedLots = Array.from(selectedLotsMap.entries()).map(([id, numero]) => ({
                id: id,
                numero: numero
            }));

            if (selectedLots.length === 0) {
                alert('Veuillez sélectionner au moins un lot');
                return;
            }

            $.ajax({
                url: CONFIG.routes.sectionLots.saveSectionLotsToAcquire,
                method: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    sectionId: sectionId,
                    lots: selectedLots
                }),
                success: (response) => {
                    console.log('Réponse du serveur (succès):', response);
                    modal.hide();

                    // Après la sauvegarde, recharger et afficher les lots
                    $.ajax({
                        url: CONFIG.routes.sectionLots.getAllLotsToAcquire,
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            immeubleId: CONFIG.immeubleId,
                            sectionId: sectionId,
                            aAcquerir: 1
                        },
                        success: (response) => {
                            if (response.success && response.data) {
                                Actions.displayLotsAcquireTable(response.data, sectionId);
                            }
                        },
                        error: (xhr, status, error) => {
                            console.error('Erreur lors du chargement des lots:', error);
                        }
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Erreur de sauvegarde:', error);
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        alert('Erreur : ' + (errorResponse.error || 'Erreur inconnue'));
                    } catch (e) {
                        alert('Erreur de communication avec le serveur: ' + xhr.responseText);
                    }
                }
            });
        });

        // Afficher le modal
        modal.show();
    },


    removeLotAcquerir(lotId, sectionId) {
        // Déboguer les paramètres reçus
        console.log("Tentative de suppression - lotId:", lotId, "sectionId:", sectionId);

        if (!confirm('Êtes-vous sûr de vouloir retirer ce lot de la liste des lots à acquérir ?')) {
            return;
        }

        // Vérifier que la route existe
        if (!CONFIG.routes.sectionLots || !CONFIG.routes.sectionLots.removeLotToAcquire) {
            console.error("La route removeLotToAcquire n'est pas définie");
            this.showErrorMessage("Erreur de configuration");
            return;
        }

        // Log de la route
        console.log("Route de suppression:", CONFIG.routes.sectionLots.removeLotToAcquire);

        $.ajax({
            url: CONFIG.routes.sectionLots.removeLotToAcquire,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                sectionId: sectionId,
                lotId: lotId
            }),
            success: (response) => {
                console.log("Réponse du serveur:", response);
                if (response.success) {
                    // Recharger la liste des lots
                    this.loadLots(sectionId);
                    // Message de succès
                    this.showErrorMessage('Lot supprimé avec succès');
                } else {
                    this.showErrorMessage(response.error || 'Erreur lors de la suppression');
                }
            },
            error: (reponse) => {
                console.error('Erreur lors de la suppression:',);
                console.error(reponse);
            }
        });
    },
    // Pour gérer la sélection/désélection de tous les lots à acquérir
    toggleAllLotsAcquerir() {
        const checked = $('#selectAllLotsAcquerir').prop('checked');
        $('.lot-acquerir-checkbox').prop('checked', checked);
    },

    // Pour sauvegarder la sélection des lots à acquérir
    saveLotsAcquerirSelection(sectionId) {

        const selectedLots = [];
        // Récupérer les IDs des lots sélectionnés par l'utilisateur
        $('.lot-acquerir-checkbox:checked').each(function () {
            selectedLots.push($(this).data('lot-id'));
        });

        console.log('Lots à acquérir sélectionnés:', selectedLots);

        // Fermer le modal
        $('#lotsAcquerirModal').modal('hide');



    },
};



// Exposer l'objet Actions globalement
window.Actions = Actions;

window.toggleVariables = toggleVariables;