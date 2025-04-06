<div class="card">
    <div class="card-header bg-danger font-weight-bold text-white">
        <div class="row">
            <div class="col-md-9">
                <?= $numberBloc ?>. INFOS COMPAGNIE ASSURANCE
            </div>
            <div class="col-md-3">
                <button <?= $hiddenComp ?> type="button" rel="tooltip"
                    title="Enregistrer les modifications de la compagnie d'assurance" onclick="saveInfosCie()"
                    class="btn btn-sm btn-dark btn-simple btn-link ">
                    <i class="fa fa-save" style="color: #ffffff"></i>
                </button>
                <button type="button" rel="tooltip" title="Modifier la compagnie d'assurance"
                    onclick="onClickChangeCie()" class="btn btn-sm btn-secondary btn-simple btn-link">
                    <i class="fas fa-edit" style="color: #ffffff"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body bg-white" style="background-color:whitesmoke;">
        <input name="oldIdCie" id="oldIdCie" value="<?= $op->cie ? $op->cie->idCompany : "" ?>" hidden>
        <div class="row mb-1" <?= $hiddenComp ?>>
            <span class="col-md-4 p-2">Nom Compagnie</span>
            <span class="col-md-8 border border-2"><?= $op->cie ? $op->cie->name  : "" ?></span>
        </div>

        <div class="row mb-1" <?= $hiddenComp ?>>
            <span class="col-md-4 p-2" $hiddenComp>Adresse</span>
            <span class="col-md-8 border border-2 p-2"><?= $op->cie ? $op->cie->businessLine1 : "" ?></span>
        </div>
        <div class="row mb-1" <?= $hiddenComp ?>>
            <span class="col-md-4 p-2">Code Postal</span>
            <span class="col-md-8 border border-2"><?= $op->cie ? $op->cie->businessPostalCode : "" ?></span>
        </div>
        <div class="row mb-1" <?= $hiddenComp ?>>
            <span class="col-md-4 p-2">Ville</span>
            <span class="col-md-8 border border-2 p-2"><?= $op->cie ? $op->cie->businessCity : "" ?></span>
        </div>

        <div class="row mb-1" <?= $hiddenComp ?>>
            <span class="col-md-4 p-2">Telephone</span>
            <input readonly class="form-control col-md-5" type="tel" name="" id="telCie"
                value="<?= $op->cie ? $op->cie->businessPhone : "" ?>">
            <div class="col-md-3 row">
                <button type="button" rel="tooltip" title="Passer un appel"
                    onclick="callContact('<?= $op->cie ? $op->cie->businessPhone : '' ?>')"
                    class="mr-1 btn btn-sm btn-info btn-simple btn-link <?= $op->cie && $op->cie->businessPhone != "" ?: 'disabled' ?>">
                    <i class="fas fa-phone-alt" style="color: #ffffff"></i>
                </button>

                <a type="button" rel="tooltip" title="WhatsApp" target="_blank"
                    class="btn btn-sm btn-success btn-simple <?= $op->cie && $op->cie->businessPhone != "" ?: 'disabled' ?>"
                    href="https://api.whatsapp.com/send?phone=<?= $op->cie && $op->cie->businessPhone != "" ? "33" .   str_replace(' ', '', $op->cie->businessPhone) : "" ?>">
                    <i class="fab fa-whatsapp" style="color: #ffffff"></i>
                </a>
            </div>
        </div>

        <div class="row mb-1" <?= $hiddenComp ?>>
            <span class="col-md-4 p-2">Email</span>
            <input readonly class="form-control col-md-8" type="mail" name="" id="emailCie"
                value="<?= $op->cie ? $op->cie->email : "" ?>">
        </div>
        <div class="row mb-1" <?= $hiddenComp ?>>
            <span class="col-md-4 p-2">Interlocuteur</span>
            <span class="col-md-7 border border-2 p-2"><?= $interlocuteur ? $interlocuteur->fullName : "" ?></span>
            <button onclick="showModalContactCie('edit')" class="btn btn-danger col-md-1 p-0" <?= $hiddenInter ?>><i
                    class="fas fa-edit" style="color: #ffffff"></i></button>
            <button onclick="showModalContactCie('add')" class="btn btn-danger col-md-1 p-0" <?= $hiddenNInter ?>>
                <i class="fa fa-plus" style="color: #ffffff"></i> </button>
        </div>
        <div class="row mb-1" <?= $hiddenComp ?>>
            <span class="col-md-4 p-2">Email interlocuteur</span>
            <span class="col-md-8 border border-2 p-2"><?= $interlocuteur ? $interlocuteur->emailContact : "" ?></span>
        </div>
        <div class="row mb-1" <?= $hiddenComp ?>>
            <div class="col-md-10">
                <div class="row">
                    <span class="col-md-5 p-2">Telephone interlocuteur</span>
                    <span class="col-md-7 border border-2 p-2"
                        id="teInterlocuteur"><?= $interlocuteur ? $interlocuteur->telContact : "" ?></span>
                </div>
            </div>
            <div class="col-md-2" <?= $hiddenComp ?>>
                <button
                    <?= $interlocuteur && $interlocuteur->telContact != null && $interlocuteur->telContact != "" ? "" : 'disabled' ?>
                    type="button" rel="tooltip" title="Passer un appel"
                    onclick="callContact('<?= $interlocuteur ? $interlocuteur->telContact : '' ?>')"
                    class="btn btn-sm btn-info btn-simple btn-link">
                    <i class="fas fa-phone-alt" style="color: #ffffff"></i>
                </button>

                <a <?= $whatsappInter != null && $whatsappInter != "" ? "" : 'disabled' ?> type="button" rel="tooltip"
                    title="WhtasApp" class="btn btn-sm btn-success btn-simple btn-link mt-1" target="_blank"
                    href="https://api.whatsapp.com/send?phone=<?= $whatsappInter ?>">
                    <i class="fab fa-whatsapp" style="color: #ffffff"></i>
                </a>
            </div>
        </div>

        <div class="row mb-1 p-2 font-weight-bold text-center text-danger" <?= $hiddenNComp ?>>
            <span class="">Aucune compagnie d'assurance</span>
        </div>

    </div>
</div>