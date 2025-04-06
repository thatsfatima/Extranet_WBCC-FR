<div class="card">
    <div class="card-header bg-danger font-weight-bold text-white">
        <div class="row">
            <div class="col-md-8">
                3. INFOS Gestionnaire Immeuble
            </div>
            <div class="col-md-4">
                <button <?= ($do) ?  "hidden" : "" ?> type="button" rel="tooltip"
                    title="Ajouter un gestionnaire de l'immeuble" onclick="showModalCie('add')"
                    class="btn btn-sm btn-dark btn-simple btn-link ml-2">
                    <i class="fas fa-plus" style="color: #ffffff"></i>
                </button>

                <button type="button" <?= ($do) ?  "" : "hidden" ?> rel="tooltip"
                    title="Modifier le gestionnaire de l'immeuble" onclick="showModalCie('edit')"
                    class="btn btn-sm btn-secondary btn-simple btn-link ml-2">
                    <i class="fas fa-edit" style="color: #ffffff"></i>
                </button>
            </div>

            <input type="text" id="idDO" value="<?= $do ? $do->idCompany : "" ?>" hidden>
        </div>
    </div>
    <div class="card-body bg-white" style="background-color:whitesmoke;">
        <div class="col-md-12">
            <div class="col-md-12">
                <label>Nom</label>
                <input readonly class="form-control" type="text" name="" id="" value="<?= $do ? $do->name : "" ?>">
            </div>
            <div class="col-md-12 row mb-1">
                <div class="col-md-12">
                    <label>Tel</label>
                    <div class="col-md-12">
                        <div class="row ">
                            <input readonly class="form-control col-md-6 col-lg-9 col-sm-6 col-xs-6" type="text" name=""
                                id="telDO" value="<?= $do ? $do->businessPhone : "" ?>">
                            <button type="button" rel="tooltip" title="Passer un appel"
                                onclick="callContact(<?= $do ? $do->businessPhone : '' ?>)"
                                class="btn btn-sm btn-info btn-simple btn-link float-right ml-1">
                                <i class="fas fa-phone-alt" style="color: #ffffff"></i>
                            </button>
                            <a type="button" rel="tooltip" title="Detail"
                                class="btn btn-sm btn-success btn-simple btn-link float-right"
                                href="https://api.whatsapp.com/send?phone=<?= $do ? $do->businessPhone : "" ?>">
                                <i class="fab fa-whatsapp" style="color: #ffffff"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <label>Email</label>
                <input readonly class="form-control" type="text" name="" id="" value="<?= $do ? $do->email : "" ?>">
            </div>

            <div class="col-md-12">
                <label>Statut</label>
                <input readonly class="form-control" type="text" name="" id="statutDO"
                    value="<?= $do ? $do->category : "" ?>">
            </div>
            <div class="col-md-12">
                <label>Catégorie</label>
                <input readonly class="form-control" type="text" name="" id="categorieDO"
                    value="<?= $do ? $do->categorieDO : "" ?>">
            </div>
            <div class="col-md-12">
                <label>Sous-Catégorie</label>
                <input readonly class="form-control" type="text" name="" id="sousCategorieDO"
                    value="<?= $do ? $do->sousCategorieDO : "" ?>">
            </div>
        </div>
    </div>
</div>