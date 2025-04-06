<style>
.modal-body {
    max-height: 80vh;
    overflow-y: auto;
}

a:hover {
    cursor: pointer;
}
</style>

<!-- les modal pour ajout interlocuteur new -->

<div class="modal fade" id="modalListInterlocuteur" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <input name="oldInter" id="oldInter"
                value="<?= isset($interlocuteur) && $interlocuteur ? $interlocuteur->idContact : "" ?>" hidden>
            <div class="modal-header bg-secondary text-white">
                <h2 class="text-center font-weight-bold">Choisissez un Interlocuteur</h2>
                <button type="" onclick="" id="btnShowModalAddInter" class="btn btn-info">Ajouter</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable1" width="100%" cellspacing="0">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Nom </th>
                                <th>Prenom </th>
                                <th>Email</th>
                                <th>Telephone</th>
                            </tr>
                        </thead>
                        <tbody id="contenuListInterlocuteur">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" type="submit" class="btn btn-danger" data-dismiss="modal">Annuler</a>
                <a href="javascript:void(0)" onclick="" type="submit" class="btn btn-success"
                    id="btnSaveInterCoche">Valider</a>
            </div>
        </div>
    </div>
</div>

<!-- modal ajout nouveau interlocuteur -->
<div class="modal fade" id="modalAddInterlocuteur" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="card">
                <div class="card-header bg-secondary">
                    <div class="row">
                        <div class="col-md-7">
                            <h5 class="mt-2 text-white" id="exampleModalLabel">NOUVEAU CONTACT</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mt-0" id="msform">
                        <div class="modal-body mt-0">
                            <div class="row mt-0">
                                <div class="col-md-12 text-left ">
                                    <div class="card ">
                                        <div class="col-md-12 mx-0">
                                            <!-- progressbar -->
                                            <div class="row register-form mt-0">
                                                <fieldset>
                                                    <legend class="text-center legend font-weight-bold text-uppercase">
                                                        <i class="icofont-info-circle"></i>Carte de visite (1)
                                                    </legend>
                                                    <input type="hidden" name="idContactInterlocuteur"
                                                        id="idContactInterlocuteur" value="0">

                                                    <input type="hidden" name="idOpportunity"
                                                        value="<?= ($op) ? $op->idOpportunity : "" ?>">
                                                    <input type="hidden" name="URLROOT" id="URLROOT"
                                                        value="<?= URLROOT ?>">
                                                    <div class="row">
                                                        <div class="row ">
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Civilité </label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <select name="civiliteInterlocuteur"
                                                                        id="civiliteInterlocuteur" class="form-control">
                                                                        <option value="">-- Choisir --</option>
                                                                        <option value="M">Monsieur</option>
                                                                        <option value="Mme">Madame</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Prénom <small
                                                                            class="text-danger">*</small></label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" name="prenomInterlocuteur"
                                                                        class="form-control" id="prenomInterlocuteur">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Nom<small
                                                                            class="text-danger">*</small></label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" name="nomInterlocuteur"
                                                                        class="form-control" id="nomInterlocuteur">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Ligne Directe<small
                                                                            class="text-danger">*</small></label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="tel" name="tel1Interlocuteur"
                                                                        class="form-control" id="tel1Interlocuteur">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Portable</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="tel" name="tel2Interlocuteur"
                                                                        class="form-control" id="tel2Interlocuteur">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Ligne Standard</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="tel" name="tel3Interlocuteur"
                                                                        class="form-control" id="tel3Interlocuteur">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Email Personnel<small
                                                                            class="text-danger">*</small></label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="email" name="emailInterlocuteur"
                                                                        class="form-control" id="emailInterlocuteur">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Email Collaboratif</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="email"
                                                                        name="emailCollaboratifInterlocuteur"
                                                                        class="form-control"
                                                                        id="emailCollaboratifInterlocuteur">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Statut</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <select name="statutInterlocuteur"
                                                                        id="statutInterlocuteur" class="form-control">
                                                                        <option value="" disabled>-- Choisir --</option>
                                                                        <option value="Salarie" selected>
                                                                            Salarie</option>
                                                                        <option value="Dirigeant" selected>
                                                                            Dirigeant</option>
                                                                        <option value="Locataire" selected>
                                                                            Architecte</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 text-left">
                                    <div class="card ">
                                        <div class="col-md-12 mx-0">
                                            <!-- progressbar -->
                                            <div class="row register-form mt-0">
                                                <fieldset>
                                                    <legend class="text-center legend font-weight-bold text-uppercase">
                                                        <i class="icofont-location-pin"></i>Adresse (2)
                                                    </legend>
                                                    <div class="row">
                                                        <div class="row">
                                                            <div class="col-md-8 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Adresse </label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" name="adresseInterlocuteur"
                                                                        class="form-control" id="adresseInterlocuteur">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Code Postal</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" maxlength="5"
                                                                        name="codePostalInterlocuteur"
                                                                        class="form-control"
                                                                        id="codePostalInterlocuteur">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Ville</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" readonly
                                                                        name="villeInterlocuteur" class="form-control"
                                                                        id="villeInterlocuteur">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Département</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" readonly
                                                                        name="departementInterlocuteur"
                                                                        class="form-control"
                                                                        id="departementInterlocuteur">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Région</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" readonly
                                                                        name="regionInterlocuteur" class="form-control"
                                                                        id="regionInterlocuteur">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Numéro Porte</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" name="porteInterlocuteur"
                                                                        class="form-control" id="porteInterlocuteur">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Numéro Bâtiment</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" name="batimentInterlocuteur"
                                                                        class="form-control" id="batimentInterlocuteur">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Numéro Etage</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" name="etageInterlocuteur"
                                                                        class="form-control" id="etageInterlocuteur">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-danger" type="button" data-dismiss="modal">Annuler</button>
                            <button class="btn btn-success" id="btnAddInter" type="button"
                                onclick="">Enregistrer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- les modal pour les sociétés -->

<div class="modal fade" id="modalSociete" tabindex="-1" aria-labelledby="modalSocieteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalSocieteLabel"></h3>
                <button type="" onclick="showModalAddCompany()" id="" class="btn btn-info">Ajouter</button>
            </div>
            <div class="modal-body" id="contenuModal">

            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" onclick="" class="btn btn-danger" data-dismiss="modal">Annuler</a>
                <a href="javascript:void(0)" onclick="validerSociete()" class="btn btn-success">Valider</a>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONFIRM CLOTURER TÂCHE -->
<div class="modal fade" id="modalConfirmClotureTache" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h3 class="text-danger font-weight-bold">Voulez-vous Clôturer cette tâche ?</h3>
                <div class="col-md-10 offset-1 mt-3">
                    <textarea class="form-control" id="commentaireClotureTache" rows="5"
                        placeholder="commentaire..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <div class="offset-8 col-md-1">
                    <button class="btn btn-warning" data-dismiss="modal">Non</button>
                </div>
                <div class="offset-1 col-md-1">
                    <button class="btn btn-danger" onclick="onConfirmCloturerTache()">Oui</button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- les modal pour les ajout et edit de compagnie -->
<div class="modal fade" id="modalExperts" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <input name="oldIdCabinetExpert" id="oldIdCabinetExpert"
                value="<?= isset($cabinetExpert) && $cabinetExpert ? $cabinetExpert->idCompany : "0" ?>" hidden>
            <input name="actionCabinetExpert" value="" id="actionCabinetExpert" hidden>

            <div class="modal-header bg-secondary text-white">
                <h2 class="text-center font-weight-bold">Veuillez choisir un cabinet d'expert</h2>
                <button type="" onclick="showModalAddCompany()" id="" class="btn btn-info">Ajouter</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
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
                        <tbody>
                            <?php
                            $i = 1;
                            if (isset($cabinetExperts)) {
                                foreach ($cabinetExperts as $expert1) {
                            ?>
                            <tr>
                                <td>
                                    <input type="radio" class="oneselectionCabinetExpert"
                                        name="oneselectionCabinetExpert"
                                        value="<?= $expert1->idCompany ?>;<?= $expert1->numeroCompany ?>">
                                </td>
                                <td><?= $i++ ?></td>
                                <td><?= $expert1->name ?></td>
                                <td><?= $expert1->email ?></td>
                                <td><?= $expert1->businessPhone ?></td>
                                <td><?= $expert1->businessLine1 ?></td>
                            </tr>
                            <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" onclick="" class="btn btn-danger" data-dismiss="modal">Annuler</a>
                <a href="javascript:void(0)" onclick="addOrEditCabinetExpert()" class="btn btn-success">Valider</a>
            </div>
        </div>
    </div>
</div>

<!-- les modal pour les ajout et edit de compagnie -->
<div class="modal fade" id="modalExpertsCompany" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <input name="oldIdExpert" id="oldIdExpert"
                value="<?= isset($axpert) && $expert ? $expert->idContact : "0" ?>" hidden>
            <input name="actionExpert" value="" id="actionExpert" hidden>
            <div class="modal-header bg-secondary text-white">
                <h2 class="text-center font-weight-bold">Veuillez choisir un expert</h2>
                <button type="" onclick="showModalAddContact('expert')" id="" class="btn btn-info">Ajouter</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tabledata" width="100%" cellspacing="0">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Nom Complet</th>
                                <th>Email</th>
                                <th>Telephone</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            if (isset($experts)) {
                                foreach ($experts as $expert1) {
                            ?>
                            <tr>
                                <td>
                                    <input type="radio" class="oneselectionExpert" name="oneselectionExpert"
                                        value="<?= $expert1->idContact ?>">
                                </td>
                                <td><?= $i++ ?></td>
                                <td><?= $expert1->fullName ?></td>
                                <td><?= $expert1->emailContact ?></td>
                                <td><?= $expert1->telContact ?></td>
                                <td><?= $expert1->statutContact ?></td>
                            </tr>
                            <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" onclick="" class="btn btn-danger" data-dismiss="modal">Annuler</a>
                <a href="javascript:void(0)" onclick="addOrEditExpert()" class="btn btn-success">Valider</a>
            </div>
        </div>
    </div>
</div>

<!-- modal Confirmation CHANGE CIE -->
<div class="modal fade" id="confirmChangeCieModal" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-md bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h5 class="text-black font-weight-bold">La modification de la compagnie d'assurance entraine la
                    modification de la délégation de gestion, Voulez-vous la modifier ?</h5>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-md-6">
                        <button class="btn btn-danger" data-dismiss="modal">Non</button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-success"
                            onclick="window.location='<?= linkto('Gestionnaire', 'te', $op->idOpportunity) ?>'">Oui</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- les modal pour ajout interlocuteur -->
<div class="modal fade" id="selectContact" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <input name="oldInter" id="oldInter"
                value="<?= isset($interlocuteur) && $interlocuteur ? $interlocuteur->idContact : "" ?>" hidden>
            <input name="action1" value="" id="action1" hidden>
            <div class="modal-header bg-secondary text-white">
                <h2 class="text-center font-weight-bold">Choisissez un Interlocuteur</h2>
                <button type="" onclick="showModalAddContact('interlocuteur')" id=""
                    class="btn btn-info">Ajouter</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable1" width="100%" cellspacing="0">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Nom </th>
                                <th>Prenom </th>
                                <th>Email</th>
                                <th>Telephone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            if (isset($tousContactsCie))
                                foreach ($tousContactsCie as $cntcie) {
                            ?>
                            <tr>

                                <td>
                                    <input type="checkbox" class="oneselectionInterlocuteur" name="checkContact"
                                        value="<?= $cntcie->idContact ?>">
                                </td>
                                <td><?= $i++ ?></td>
                                <td><?= $cntcie->nomContact ?></td>
                                <td><?= $cntcie->prenomContact ?></td>
                                <td><?= $cntcie->emailContact ?></td>
                                <td><?= $cntcie->telContact ?></td>
                            </tr>
                            <?php  }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" type="submit" class="btn btn-danger" data-dismiss="modal">Annuler</a>
                <a href="javascript:void(0)" onclick="AddOrEditInterlocuteur()" type="submit"
                    class="btn btn-success">Valider</a>
            </div>
        </div>
    </div>
</div>

<!-- les modal pour ajout de destinataires -->
<div class="modal fade" id="selectContactDestinataire" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h2 class="text-center font-weight-bold">Choisissez les destinataires</h2>
                <button type="" onclick="showModalAddContact('contact')" id="" class="btn btn-info">Ajouter</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable2" width="100%" cellspacing="0">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Nom </th>
                                <th>Prenom </th>
                                <th>Email</th>
                                <th>Telephone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            if (isset($tousContacts))
                                foreach ($tousContacts as $cnt) {
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="checkContact" value="<?= $cnt->emailContact ?>">
                                </td>
                                <td><?= $i++ ?></td>
                                <td><?= $cnt->nomContact ?></td>
                                <td><?= $cnt->prenomContact ?></td>
                                <td><?= $cnt->emailContact ?></td>
                                <td><?= $cnt->telContact ?></td>
                            </tr>
                            <?php  }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" type="submit" class="btn btn-danger" data-dismiss="modal">Annuler</a>
                <button type="" onclick="ajoutDestinataire()" class="btn btn-success"
                    data-bs-dismiss="modal">Valider</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="reouvertureOPModal" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h3 class="text-danger font-weight-bold">Voulez-vous reouvrir cette opportunité
                    '<?= $op->name ?>' ?
                </h3>
                <div class="col-md-6 mt-2 offset-3">
                    <div>

                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <div class="offset-8 col-md-1">
                    <button class="btn btn-danger" data-dismiss="modal">Non</button>
                </div>
                <div class="offset-1 col-md-1">
                    <button class="btn btn-success" onclick="onConfirmReouvertureOP()">Oui</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal Cloture NUM SINISTRE -->
<div class="modal fade" id="modalCloseActivityNumSinistre" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h3 class="text-danger font-weight-bold">Voulez-vous confirmer le numèro de sinistre pour
                    '<?= $op->name ?>' ?
                </h3>

            </div>
            <div class="modal-footer">
                <div class="offset-8 col-md-1">
                    <button class="btn btn-danger" data-dismiss="modal">Non</button>
                </div>
                <div class="offset-1 col-md-1">
                    <button class="btn btn-success" onclick="onClickConfirmNumSinistre()">Oui</button>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- modal incident -->
<div class="modal fade" id="modalIncident" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h2 class="text-center font-weight-bold">Déclarer un incident</h2>
            </div>
            <div class="modal-body">
                <form class="form">
                    <div class="form-group">
                        <label for="">Date</label>
                        <input type="text" name="" id="dateIncident" readonly class="form-control"
                            value="<?= date('d-m-Y H:i') ?>">
                    </div>
                    <div class="form-group">
                        <label for="">Auteur</label>
                        <input type="text" name="" id="auteurIncident" readonly class="form-control"
                            value="<?= $_SESSION['connectedUser']->prenomContact . ' ' . $_SESSION['connectedUser']->nomContact ?>">
                    </div>
                    <div class="form-group">
                        <label for="">Raison de l'incident </label>
                        <textarea name="" id="incidentText" cols="30" rows="10" class="form-control"></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <div class="offset-6 col-md-2">
                    <button class="btn btn-danger" data-dismiss="modal">Annuler</button>
                </div>
                <div class="offset-1 col-md-2">
                    <button class="btn btn-success" onclick="declarerIncident()">Valider</button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- modal Confirmation CLOTURE ACTIVITE -->
<div class="modal fade" id="modalConfirmClotureActivity" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-md bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <input name="action" value="" id="typeActivity" hidden>
                <h3 class="text-black font-weight-bold" id="textCloture">Voulez-vous clôturer l'activité ?</h3>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-md-6">
                        <button class="btn btn-danger" data-dismiss="modal">Non</button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-success" onclick="onConfirmClotureActivity()">Oui</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal note -->
<div class="modal fade" id="viewNote" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h2 class="text-center font-weight-bold">Note</h2>
            </div>
            <input type="text" id="idNote" readonly hidden class="form-control" value="">
            <input type="text" id="actionNote" readonly hidden class="form-control" value="">
            <div class="modal-body">
                <form class="form">
                    <div class="form-group">
                        <label for="">Date</label>
                        <input type="text" name="" id="dateNote" readonly class="form-control"
                            value="<?= date('d-m-Y H:i') ?>">
                    </div>
                    <div class="form-group">
                        <label for="">Auteur</label>
                        <input type="text" name="" id="auteurNote" readonly class="form-control"
                            value="<?= $_SESSION['connectedUser']->prenomContact . ' ' . $_SESSION['connectedUser']->nomContact ?>">
                    </div>
                    <div class="form-group">
                        <label for="">Note</label>
                        <textarea name="" id="noteText" cols="30" rows="10" readonly class="form-control"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer" id="actionForNote">
                <button onclick='' class='btn btn-danger' data-dismiss='modal'>Annuler</button>
                <button id="btnSaveNote" onclick="saveNote('note')" class='btn btn-success'>Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- modal question NOTE -->
<div class="modal fade" id="questionPublicationNote" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <input hidden readonly id="publieNote" value="">
            <div class="modal-body text-center">
                <h3 id="msgPublicationNote" class="text-danger font-weight-bold">Voulez-vous Publier cette note ?</h3>
            </div>
            <div class="modal-footer">
                <div class="offset-8 col-md-1">
                    <button class="btn btn-danger" id="nonPublicationNote" data-dismiss="modal"
                        onclick='location.reload()'>Non</button>
                </div>
                <div class="offset-1 col-md-1">
                    <button class="btn btn-success" id="okPublicationNote" onclick="confirmPublierNote()">Oui</button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- modal de confirmation -->
<div>
    <div class="modal fade modal-center" id="successOperation" data-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg bg-white">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <h3 id="msgSuccess" class="" style="color:green">Email envoyé !!</h3>
                    <button onclick="" id="buttonConf" class="btn btn-success" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal ERROR -->
<div>
    <div class="modal fade modal-center" id="errorOperation" data-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg bg-white">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <h3 id="msgError" class="" style="color:red">Email envoyé !!</h3>
                    <button onclick="" class="btn btn-danger" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal de chargement -->
<div class="modal fade" id="loadingModal" data-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-danger" style="width: 5vw; height: 10vh;">
                </div>
                <br><br><br>
                <h3 id="msgLoading">Génération de délégation en cours...</h3>
            </div>
        </div>
    </div>
</div>

<!-- modal Confirmation REPROGRAMMER ACTIVITY -->
<div class="modal fade" id="modalConfirmReprogrammerActivity" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-md bg-white">
        <div class="modal-content">
            <div class="modal-body">
                <h3 class="text-center text-black font-weight-bold" id="textReprogrammer">Voulez-vous clôturer
                    l'activité ?</h3>
                <div class="row">
                    <div class="col-md-8">
                        <div>
                            <label for="">Nouvelle Date</label>
                        </div>
                        <div>
                            <input name="action" value="" id="typeActivity" hidden>
                            <input name="action" value="" id="codeActivity" hidden>
                            <input
                                value="<?= isset($activityRVTravaux) && $activityRVTravaux ? $activityRVTravaux->startTime : '' ?>"
                                id="dateActivityLA" hidden>
                            <input class="form-control" type="date" name="dateNewActivity" id="dateNewActivity"
                                value="">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div>
                            <label for="">Heure</label>
                        </div>
                        <div>
                            <input class="form-control" type="time" name="heureNewActivity" id="heureNewActivity"
                                value="">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div>
                            <label for="">Commentaire</label>
                        </div>
                        <div>
                            <textarea rows="5" class="form-control" id="commentaireNewActivity"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-md-6">
                        <button class="btn btn-danger" data-dismiss="modal">Non</button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-success" onclick="onConfirmProgrammerActivity()">Oui</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- les modal pour ajout du contact sinistre -->
<div class="modal fade" id="selectContactSinistre" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h2 class="text-center font-weight-bold">Choisissez le contact</h2>
                <button type="" onclick="showModalAddContact('contact')" id="" class="btn btn-info">Ajouter</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable3" width="100%" cellspacing="0">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Nom </th>
                                <th>Prenom </th>
                                <th>Email</th>
                                <th>Telephone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            if (isset($tousContacts))
                                foreach ($tousContacts as $cnt) {
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="checkSinitre" class="checkSinitre"
                                        value="<?= $cnt->idContact ?>,<?= $cnt->numeroContact ?>">
                                </td>
                                <td><?= $i++ ?></td>
                                <td><?= $cnt->nomContact ?></td>
                                <td><?= $cnt->prenomContact ?></td>
                                <td><?= $cnt->emailContact ?></td>
                                <td><?= $cnt->telContact ?></td>
                            </tr>
                            <?php  }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="" onclick="" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                <button type="" onclick="" id="buttonConfirmContact" class="btn btn-success">Valider</button>
            </div>
        </div>
    </div>
</div>

<!-- les modal pour ajout de immeuble -->
<div class="modal fade" id="selectImmeuble" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h2 class="text-center font-weight-bold">Choisissez un immeuble</h2>
                <button type="" onclick="showModalAddImmeuble()" id="" class="btn btn-info">Ajouter</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable5" width="100%" cellspacing="0">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Code Immeuble </th>
                                <th>Adresse </th>
                                <th>Code Postal</th>
                                <th>Ville</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            if (isset($allImmeubles))
                                foreach ($allImmeubles as $imm) {
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="oneselectionImmeuble" name="checkImmeuble"
                                        value="<?= $imm->idImmeuble ?>,<?= $imm->numeroImmeuble ?>,<?= $imm->codeWBCC ?>">
                                </td>
                                <td><?= $i++ ?></td>
                                <td><?= $imm->codeWBCC ?></td>
                                <td><?= $imm->adresse ?></td>
                                <td><?= $imm->codePostal ?></td>
                                <td><?= $imm->ville ?></td>
                            </tr>
                            <?php  }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="" onclick="" id="" class="btn btn-success" data-dismiss="modal">Annuler</button>
                <button type="" onclick="AddOrEditImmeuble('editImm')" id="" class="btn btn-success">Valider</button>
            </div>
        </div>
    </div>
</div>


<!-- modal ajout nouveau contact -->
<div class="modal fade" id="modalAddOrEditContact" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="card">
                <div class="card-header bg-secondary">
                    <div class="row">
                        <div class="col-md-7">
                            <h5 class="mt-2 text-white" id="exampleModalLabel">NOUVEAU CONTACT</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mt-0" id="msform">
                        <div class="modal-body mt-0">
                            <div class="row mt-0">
                                <div class="col-md-12 text-left ">
                                    <div class="card ">
                                        <div class="col-md-12 mx-0">
                                            <!-- progressbar -->
                                            <div class="row register-form mt-0">
                                                <fieldset>
                                                    <legend class="text-center legend font-weight-bold text-uppercase">
                                                        <i class="icofont-info-circle"></i>Carte de visite (1)
                                                    </legend>
                                                    <input type="hidden" name="idContact" id="idContactAdd" value="0">

                                                    <input type="hidden" name="idOpportunity"
                                                        value="<?= ($op) ? $op->idOpportunity : "" ?>">
                                                    <input type="hidden" name="URLROOT" id="URLROOT"
                                                        value="<?= URLROOT ?>">
                                                    <div class="row">
                                                        <div class="row ">
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Civilité </label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <select name="civilite" id="civilite"
                                                                        class="form-control">
                                                                        <option value="">-- Choisir --</option>
                                                                        <option value="M">Monsieur</option>
                                                                        <option value="Mme">Madame</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Prénom <small
                                                                            class="text-danger">*</small></label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" name="prenom"
                                                                        class="form-control" id="prenom">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Nom<small
                                                                            class="text-danger">*</small></label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" name="nom" class="form-control"
                                                                        id="nom">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Ligne Directe<small
                                                                            class="text-danger">*</small></label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="tel" name="tel1" class="form-control"
                                                                        id="tel1">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Portable</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="tel" name="tel2" class="form-control"
                                                                        id="tel2">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Ligne Standard</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="tel" name="tel3" class="form-control"
                                                                        id="tel3">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Email Personnel<small
                                                                            class="text-danger">*</small></label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="email" name="email"
                                                                        class="form-control" id="email">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Email Collaboratif</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="email" name="emailCollaboratif"
                                                                        class="form-control" id="emailCollaboratif">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Statut</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <select name="statut" id="statut"
                                                                        class="form-control">
                                                                        <option value="" disabled>-- Choisir --</option>
                                                                        <option value="Salarie" selected>
                                                                            Salarie</option>
                                                                        <option value="Dirigeant" selected>
                                                                            Dirigeant</option>
                                                                        <option value="Locataire" selected>
                                                                            Locataire</option>
                                                                        <option value="Copropriétaire Occupant">
                                                                            Copropriétaire Occupant</option>
                                                                        <option value="Copropriétaire Non Occupant">
                                                                            Copropriétaire Non Occupant</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 text-left">
                                    <div class="card ">
                                        <div class="col-md-12 mx-0">
                                            <!-- progressbar -->
                                            <div class="row register-form mt-0">
                                                <fieldset>
                                                    <legend class="text-center legend font-weight-bold text-uppercase">
                                                        <i class="icofont-location-pin"></i>Adresse (2)
                                                    </legend>
                                                    <div class="row">
                                                        <div class="row">
                                                            <div class="col-md-8 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Adresse </label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" name="adresse1"
                                                                        class="form-control" id="adresse1C">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Code Postal</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" maxlength="5"
                                                                        onchange="changePostalCodeC()" name="codePostal"
                                                                        class="form-control" id="codePostalC">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Ville</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" readonly name="ville"
                                                                        class="form-control" id="villeC">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Département</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" readonly name="departement"
                                                                        class="form-control" id="departementC">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Région</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" readonly name="region"
                                                                        class="form-control" id="regionC">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Numéro Porte</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" name="porte" class="form-control"
                                                                        id="porteC">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Numéro Bâtiment</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" name="batiment"
                                                                        class="form-control" id="batimentC">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="">Numéro Etage</label>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="text" name="etage" class="form-control"
                                                                        id="etageC">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-danger" type="button" data-dismiss="modal">Annuler</button>
                            <button class="btn btn-success" id="engCnt" type="button"
                                onclick="saveContactBD('')">Enregistrer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal Cloture OP -->
<div class="modal fade" id="clotureOPModal" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h3 class="text-danger font-weight-bold">Voulez-vous clôturer cette opportunité '<?= $op->name ?>' ?
                </h3>
                <div class="col-md-6 mt-2 offset-3">
                    <div>
                        <select class="form-control" aria-label="Default select example" name="typeClotureOP"
                            id="typeClotureOP">
                            <option value="Won" selected>Clôturée gagnée</option>
                            <option value="Lost">Clôturée Perdue</option>
                            <option value="Tranche 2">Tranche 2</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-10 offset-1 mt-3">
                    <textarea class="form-control" id="commentaireClotureOP" rows="5"
                        placeholder="commentaire..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <div class="offset-8 col-md-1">
                    <button class="btn btn-danger" data-dismiss="modal">Non</button>
                </div>
                <div class="offset-1 col-md-1">
                    <button class="btn btn-success" onclick="onConfirmCloturerOP()">Oui</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalOthersOp" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h4 class="text-center font-weight-bold">Liste des opportunités en attente de déclaration de sinistre
                    avec la même
                    compagnie d'assurance'<?= isset($op->cie)  && $op->cie ? $op->cie->name : "" ?>'
                    (<?= isset($otherOpWSameCie) ? sizeof($otherOpWSameCie) : '' ?>)</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable4" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Actions</th>
                                <th>#</th>
                                <th>N° Dossier</th>
                                <th>DO</th>
                                <th>Gestionnaire Imm/App</th>
                                <th>Statut</th>
                                <th>Commercial</th>
                                <th>Type de dossier</th>
                                <th>Partie concernée</th>
                                <th>Date d'ouverture</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            if (isset($otherOpWSameCie) && sizeof($otherOpWSameCie) != 0) {
                                foreach ($otherOpWSameCie as $opp) {
                            ?>
                            <tr style="background-color: <?= ($opp->demandeCloture == 1) ? 'lightgray' : '' ?>">
                                <td style="text-align : center">
                                    <a target="_blank" type="button" rel="tooltip"
                                        title="Faire la relance pour prise en charge de devis"
                                        href="<?= linkto('Gestionnaire', 'fd', $opp->idOpportunity) ?>"
                                        class="btn btn-sm btn-info btn-simple btn-link">
                                        <i class="fas fa-folder-open" style="color: #ffffff"></i>
                                    </a>
                                </td>
                                <td><?= $i++ ?></td>
                                <td><?= $opp->name ?></td>
                                <td><?= $opp->contactClient ?></td>
                                <td><?= $opp->nomGestionnaireAppImm ?></td>
                                <td>
                                    <?= ($opp->status == 'Lost' ? 'Clôtué Perdu' : ($opp->status == 'Won' ? 'Clôturé gagné' : ($opp->status == 'Inactive' ? 'Inactif' : 'Ouvert'))) ?>
                                </td>
                                <td><?= $opp->commercial ?></td>
                                <td><?= $opp->type ?></td>
                                <td><?= $opp->typeSinistre ?></td>
                                <td data-sort="<?= strtotime(str_replace('/', '-', $opp->createDate)) ?>">
                                    <?= date('d/m/Y', strtotime(str_replace('/', '-', $opp->createDate))) ?></td>
                            </tr>
                            <?php
                                }
                            }

                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="" onclick="" id="" class="btn btn-success" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalOthersOpContact" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h4 class="text-center font-weight-bold">Liste des opportunités en cours de gestion avec
                    '<?= isset($op->contact) && $op->contact ? $op->contact->fullName : "" ?>'
                    (<?= isset($otherOpWSameContact) ? sizeof($otherOpWSameContact) : 0 ?>)</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable4" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Actions</th>
                                <th>#</th>
                                <th>N° Dossier</th>
                                <th>DO</th>
                                <th>Gestionnaire Imm/App</th>
                                <th>Statut</th>
                                <th>Commercial</th>
                                <th>Type de dossier</th>
                                <th>Partie concernée</th>
                                <th>Date d'ouverture</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            if (isset($otherOpWSameContact) && sizeof($otherOpWSameContact) != 0) {
                                foreach ($otherOpWSameContact as $opp) {
                            ?>
                            <tr style="background-color: <?= ($opp->demandeCloture == 1) ? 'lightgray' : '' ?>">
                                <td style="text-align : center">
                                    <a target="_blank" type="button" rel="tooltip" title="Voir détail"
                                        href="<?= linkto('Gestionnaire', 'dossier', $opp->idOpportunity) ?>"
                                        class="btn btn-sm btn-info btn-simple btn-link">
                                        <i class="fas fa-folder-open" style="color: #ffffff"></i>
                                    </a>
                                </td>
                                <td><?= $i++ ?></td>
                                <td><?= $opp->name ?></td>
                                <td><?= $opp->contactClient ?></td>
                                <td><?= $opp->nomGestionnaireAppImm ?></td>
                                <td>
                                    <?= ($opp->status == 'Lost' ? 'Clôtué Perdu' : ($opp->status == 'Won' ? 'Clôturé gagné' : ($opp->status == 'Inactive' ? 'Inactif' : 'Ouvert'))) ?>
                                </td>
                                <td><?= $opp->commercial ?></td>
                                <td><?= $opp->type ?></td>
                                <td><?= $opp->typeSinistre ?></td>
                                <td data-sort="<?= strtotime(str_replace('/', '-', $opp->createDate)) ?>">
                                    <?= date('d/m/Y', strtotime(str_replace('/', '-', $opp->createDate))) ?></td>
                            </tr>
                            <?php
                                }
                            }

                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="" id="" class="btn btn-danger" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- les modal pour les ajout et edit de compagnie -->
<div class="modal fade" id="selectCompany" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" method="POST" action="" id="formChoiceCie">
            <input name="oldIdCie" id="oldIdCie" value="<?= isset($op->cie) &&  $op->cie ? $op->cie->idCompany : "" ?>"
                hidden>
            <input name="action" value="" id="action" hidden>
            <div class="modal-header bg-secondary text-white">
                <h2 class="text-center font-weight-bold">Choisissez une compagnie</h2>
                <button hidden type="" onclick="showModalAddCompany()" id="" class="btn btn-info">Ajouter</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Nom Compagnie</th>
                                <th>Adresse</th>
                                <th>Email</th>
                                <th>Telephone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            if (isset($tousCieAssurance))
                                foreach ($tousCieAssurance as $cie1) {
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="oneselectionCie" name="oneselectionCie"
                                        value="<?= $cie1->idCompany ?>;<?= $cie1->numeroCompany ?>">
                                </td>
                                <td><?= $i++ ?></td>
                                <td><?= $cie1->name ?></td>
                                <td><?= $cie1->businessLine1 ?></td>
                                <td><?= $cie1->email ?></td>
                                <td><?= $cie1->businessPhone ?></td>
                            </tr>
                            <?php  }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a onclick="" class="btn btn-danger" data-dismiss="modal">Annuler</a>
                <a href="javascript:void(0)" onclick="AddOrEditCie()" class="btn btn-success">Valider</a>
            </div>

        </div>
    </div>
</div>