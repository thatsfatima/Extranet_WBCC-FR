<div class="card">
    <div class="card-header bg-danger font-weight-bold text-white">
        <div class="row">
            <div class="col-md-9">
                <?= $numberBloc ?>. INFOS ARCHITECTE
            </div>
            <div class="col-md-3">
                <button type="button" rel="tooltip" title="Enregistrer les modifications de la compagnie d'architecte"
                    onclick="saveInfosarchitecte()" class="btn btn-sm btn-dark btn-simple btn-link ">
                    <i class="fa fa-save" style="color: #ffffff"></i>
                </button>
                <button type="button" rel="tooltip" title="Modifier la compagnie d'architecte"
                    onclick="changerContenuModal('ARCHITECTE')" class="btn btn-sm btn-secondary btn-simple btn-link">
                    <i class="fas fa-edit" style="color: #ffffff"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body bg-white" style="background-color:whitesmoke;">
        <div <?= $op->architecte ? '' : 'hidden' ?>>

            <input name="oldIdarchitecte" id="oldIdarchitecte"
                value="<?= $op->architecte ? $op->architecte->idCompany : "" ?>" hidden>
            <div class="row mb-1">
                <span class="col-md-4 p-2">Nom Compagnie</span>
                <span
                    class="col-md-8 border border-2"><?= $op->architecte ? $op->architecte->nameCompany  : "" ?></span>
            </div>

            <div class="row mb-1">
                <span class="col-md-4 p-2" $hiddenComp>Adresse</span>
                <span
                    class="col-md-8 border border-2 p-2"><?= $op->architecte ? $op->architecte->businessLine1 : "" ?></span>
            </div>
            <div class="row mb-1">
                <span class="col-md-4 p-2">Code Postal</span>
                <span
                    class="col-md-8 border border-2"><?= $op->architecte ? $op->architecte->businessPostalCode : "" ?></span>
            </div>
            <div class="row mb-1">
                <span class="col-md-4 p-2">Ville</span>
                <span
                    class="col-md-8 border border-2 p-2"><?= $op->architecte ? $op->architecte->businessCity : "" ?></span>
            </div>

            <div class="row mb-1">
                <span class="col-md-4 p-2">Telephone</span>
                <input readonly class="form-control col-md-5" type="tel" name="" id="telarchitecte"
                    value="<?= $op->architecte ? $op->architecte->businessPhone : "" ?>">
                <div class="col-md-3 row">
                    <button type="button" rel="tooltip" title="Passer un appel"
                        onclick="callContact('<?= $op->architecte ? $op->architecte->businessPhone : '' ?>')"
                        class="mr-1 btn btn-sm btn-info btn-simple btn-link <?= $op->architecte && $op->architecte->businessPhone != "" ?: 'disabled' ?>">
                        <i class="fas fa-phone-alt" style="color: #ffffff"></i>
                    </button>

                    <a type="button" rel="tooltip" title="WhatsApp" target="_blank"
                        class="btn btn-sm btn-success btn-simple <?= $op->architecte && $op->architecte->businessPhone != "" ?: 'disabled' ?>"
                        href="https://api.whatsapp.com/send?phone=<?= $op->architecte && $op->architecte->businessPhone != "" ? "33" .   str_replace(' ', '', $op->architecte->businessPhone) : "" ?>">
                        <i class="fab fa-whatsapp" style="color: #ffffff"></i>
                    </a>
                </div>
            </div>

            <div class="row mb-1">
                <span class="col-md-4 p-2">Email</span>
                <input readonly class="form-control col-md-8" type="mail" name="" id="emailarchitecte"
                    value="<?= $op->architecte ? $op->architecte->email : "" ?>">
            </div>
            <div class="row mb-1">
                <span class="col-md-4 p-2">Interlocuteur</span>
                <span
                    class="col-md-7 border border-2 p-2"><?= $interlocuteurArchi ? $interlocuteurArchi->fullName : "" ?></span>
                <button onclick="showModalInterlocuteur(<?= $op->architecte->idCompany ?>,'ARCHITECTE')"
                    class="btn btn-danger col-md-1 p-0" <?= $hiddenNInter ?>>
                    <i class="<?= $interlocuteurArchi ? 'fas fa-edit' : "fa fa-plus" ?> " style="color: #ffffff"></i>
                </button>
            </div>
            <div class="row mb-1">
                <span class="col-md-4 p-2">Email interlocuteur</span>
                <span
                    class="col-md-8 border border-2 p-2"><?= $interlocuteurArchi ? $interlocuteurArchi->emailContact : "" ?></span>
            </div>
            <div class="row mb-1">
                <div class="col-md-10">
                    <div class="row">
                        <span class="col-md-5 p-2">Telephone interlocuteur</span>
                        <span class="col-md-7 border border-2 p-2"
                            id="teInterlocuteur"><?= $interlocuteurArchi ? $interlocuteurArchi->telContact : "" ?></span>
                    </div>
                </div>
                <div class="col-md-2">
                    <button
                        <?= $interlocuteurArchi && $interlocuteurArchi->telContact != null && $interlocuteurArchi->telContact != "" ? "" : 'disabled' ?>
                        type="button" rel="tooltip" title="Passer un appel"
                        onclick="callContact('<?= $interlocuteurArchi ? $interlocuteurArchi->telContact : '' ?>')"
                        class="btn btn-sm btn-info btn-simple btn-link">
                        <i class="fas fa-phone-alt" style="color: #ffffff"></i>
                    </button>

                    <a <?= $whatsappInter != null && $whatsappInter != "" ? "" : 'disabled' ?> type="button"
                        rel="tooltip" title="WhtasApp" class="btn btn-sm btn-success btn-simple btn-link mt-1"
                        target="_blank" href="https://api.whatsapp.com/send?phone=<?= $whatsappInter ?>">
                        <i class="fab fa-whatsapp" style="color: #ffffff"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-1 p-2 font-weight-bold text-center text-danger" <?= $op->architecte ? 'hidden' : '' ?>>
            <span class="">Aucune compagnie d'architecte</span>
        </div>

    </div>
</div>