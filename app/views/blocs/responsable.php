<div class="card">
    <div class="card-header bg-danger font-weight-bold text-white">
        <div class="row">
            <div class="col-md-8">
                2. INFOS RESPONSABLE
                <?= $rf ? ($typeResponsable == "voisin" ? "(Voisin)" : ($typeResponsable == "pc" ? "(Partie Commune)" : ($typeResponsable == "chez vous" ? "(Le locataire lui même)" : "(Non défini)"))) : "" ?>
            </div>
            <input type="text" id="idResponsable" value="<?= $responsable ? $responsable->idContact : "" ?>" hidden>
            <input type="text" id="typeResponsable" value="<?= $typeResponsable ?>" hidden>

            <input type="text" id="numeroResponsable" value="<?= $responsable ? $responsable->numeroContact : "" ?>"
                hidden>
            <div class="col-md-4" <?= $rf ? "" : "hidden" ?>>
                <button type="button" <?= ($responsable) ?  "" : "hidden" ?> rel="tooltip"
                    title="Enregistrer les modifications" onclick="addOrupdateContact('update', 'responsable')"
                    class="btn btn-sm btn-dark btn-simple btn-link ">
                    <i class="fa fa-save" style="color: #ffffff"></i>
                </button>

                <button hidden <?= ($responsable) ?  "hidden" : "" ?> type="button" rel="tooltip"
                    title="Ajouter un contact" onclick="showModalContact('ajoutResponsable')"
                    class="btn btn-sm btn-dark btn-simple btn-link ml-2">
                    <i class="fas fa-plus" style="color: #ffffff"></i>
                </button>

                <button hidden type="button" <?= ($responsable) ?  "" : "hidden" ?> rel="tooltip"
                    title="Modifier le contact" onclick="showModalContact('changeResponsable')"
                    class="btn btn-sm btn-secondary btn-simple btn-link ml-2">
                    <i class="fas fa-edit" style="color: #ffffff"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body bg-white" style="background-color:whitesmoke;" <?= $rf ? "" : "hidden" ?>>
        <div class="col-md-12" <?= ($responsable) ?  "" : "hidden" ?>>
            <div class="col-md-12">
                <label>Civilité</label>
                <input class="form-control" type="text" name="" id="civiliteResponsable"
                    value="<?= $responsable ? $responsable->civiliteContact : "" ?>">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label>Prénom</label>
                    <input class="form-control" type="text" name="" id="prenomResponsable"
                        value="<?= $responsable ? $responsable->prenomContact : "" ?>">
                </div>
                <div class="col-md-6">
                    <label>Nom</label>
                    <input class="form-control" type="text" name="" id="nomResponsable"
                        value="<?= $responsable ? $responsable->nomContact : "" ?>">
                </div>
            </div>
            <div class="col-md-12">
                <label>Email</label>
                <input class="form-control" type="text" name="" id="emailResponsable"
                    value="<?= $responsable ? $responsable->emailContact : "" ?>">
            </div>
            <div class="col-md-12 row">
                <label>Téléphone</label>
                <div class="col-md-12">
                    <div class="row ">
                        <input class="form-control col-md-7 col-lg-9 col-sm-6 col-xs-6" type="text" name=""
                            id="telResponsable" value="<?= $responsable ? $responsable->telContact : "" ?>">

                        <button type="button" rel="tooltip" title="Passer un appel"
                            onclick="callContact('<?= $responsable ? $responsable->telContact : '' ?>')"
                            class="btn btn-sm btn-info btn-simple btn-link ml-1 <?= $responsable && $responsable->telContact != "" ?: 'disabled' ?>">
                            <i class="fas fa-phone-alt" style="color: #ffffff"></i>
                        </button>

                        <a type="button" rel="tooltip" title="WhatsApp" target="_blank"
                            class="btn btn-sm btn-success btn-simple <?= $responsable && $responsable->telContact != "" ?: 'disabled' ?>"
                            href="https://api.whatsapp.com/send?phone=<?= $responsable && $responsable->telContact != "" ? "33" .   str_replace(' ', '', $responsable->telContact) : "" ?>">
                            <i class="fab fa-whatsapp" style="color: #ffffff"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <label>Date Naiss</label>
                <input class="form-control" type="date" name="" id="dateNaissanceResponsable" max="<?= $maxDateNaiss ?>"
                    value="<?= $responsable ? ($responsable->dateNaissance != null ? $responsable->dateNaissance : "") : "" ?>">
            </div>
            <div class="col-md-12">
                <label>Statut Contact</label>
                <input readonly class="form-control" type="text" name="" id="statutResponsable"
                    value="<?= $responsable ? $responsable->statutContact : "" ?>">
            </div>
        </div>
        <div class="font-weight-bold text-center text-danger" <?= ($responsable) ?  "hidden" : "" ?>>
            <span class="">Aucun Contact !!</span>
        </div>
    </div>
    <div class="card-body bg-white font-weight-bold text-center text-danger" style="background-color:whitesmoke;"
        <?= $rf ? "hidden" : "" ?>>
        <span class="">Responsable pas encore défini ! Merci de remplir le constat</span>
    </div>
</div>