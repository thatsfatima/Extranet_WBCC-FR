<div class="card">
    <div class="card-header bg-danger font-weight-bold text-white">
        <div class="row">
            <input type="text" id="idContact" value="<?= $op->contact ? $op->contact->idContact : "" ?>" hidden>
            <input type="text" id="numeroContact" value="<?= $op->contact ? $op->contact->numeroContact : "" ?>" hidden>
            <div class="col col-md-9 col-lg-9 col-sm-6 col-xs-6 float-left">
                <?= $numberBloc ?>. INFOS CONTACT
            </div>
            <div class="col col-md-3 col-lg-3 col-sm-6 col-xs-6 row">
                <button <?= $hiddenContact ?> type="button" rel="tooltip" title="Enregistrer les modifications"
                    onclick="addOrupdateContact('update', 'reload')" class="btn btn-sm btn-dark btn-simple btn-link ">
                    <i class="fa fa-save" style="color: #ffffff"></i>
                </button>

                <button <?= $hiddenNContact ?> type="button" rel="tooltip" title="Ajouter un contact"
                    onclick="showModalContact('ajoutSinistre')" class="btn btn-sm btn-dark btn-simple btn-link">
                    <i class="fas fa-plus" style="color: #ffffff"></i>
                </button>


                <button <?= $hiddenContact ?> type="button" rel="tooltip" title="Modifier le contact"
                    onclick="showModalContact('changeSinistre')" class="btn btn-sm btn-secondary btn-simple btn-link">
                    <i class="fas fa-edit" style="color: #ffffff"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body bg-white" style="background-color:whitesmoke;">
        <div class="row" <?= $hiddenContact ?>>
            <div class="col-md-12">
                <label>Civilité</label>
                <input class="form-control" type="text" name="" id="civiliteContact"
                    value="<?= $op->contact ? $op->contact->civiliteContact : "" ?>">
            </div>
            <div class="col-md-12 mb-1">

                <label>Prénom</label>
                <input class="form-control" type="text" name="" id="prenomContact"
                    value="<?= $op->contact ? $op->contact->prenomContact : "" ?>">
            </div>
            <div class=" col-md-12 mb-1">
                <label>Nom</label>
                <input class="form-control" type="text" name="" id="nomContact"
                    value="<?= $op->contact ? $op->contact->nomContact : "" ?>">
            </div>

            <div class="col-md-12 mb-1">
                <label>Email</label>
                <input class="form-control" type="text" name="" id="emailContact"
                    value="<?= $op->contact ? $op->contact->emailContact : "" ?>">
            </div>
            <div class="col-md-12 mb-1 mx-0">
                <label>Tél</label>
                <div class="row mx-0 col-md-12">
                    <input class="form-control col-md-7 col-lg-9 col-sm-6 col-xs-6 mx-0" type="text" name=""
                        id="telContact" value="<?= $op->contact ? $op->contact->telContact : "" ?>">
                    <div class="col-md-5 col-lg-3 col-sm-6 col-xs-6">
                        <button type="button" rel="tooltip" title="Passer un appel"
                            onclick="callContact('<?= $op->contact ? $op->contact->telContact : '' ?>')"
                            class="btn btn-sm btn-info btn-simple btn-link <?= $op->contact && $op->contact->telContact != "" ?: 'disabled' ?>">
                            <i class="fas fa-phone-alt" style="color: #ffffff"></i>
                        </button>

                        <a type="button" rel="tooltip" title="WhatsApp" target="_blank"
                            class="btn btn-sm btn-success btn-simple <?= $op->contact && $op->contact->telContact != "" ?: 'disabled' ?>"
                            href="https://api.whatsapp.com/send?phone=<?= $op->contact && $op->contact->telContact != "" ? "33" .   str_replace(' ', '', $op->contact->telContact) : "" ?>">
                            <i class="fab fa-whatsapp" style="color: #ffffff"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <label>Statut Contact</label>
                <input class="form-control" type="text" name="" id="statutContact"
                    value="<?= $op->contact ? $op->contact->statutContact : "" ?>">
            </div>
            <div class="col-md-12">
                <label>Date Naiss</label>
                <input class="form-control" type="date" name="" id="dateNaissanceContact"
                    value="<?= $op->contact ? $op->contact->dateNaissance : "" ?>">
            </div>
        </div>
        <div class="font-weight-bold text-center text-danger" <?= $hiddenNContact ?>>
            <span class="">Aucun Contact !!</span>
        </div>
    </div>
</div>