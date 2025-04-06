<div class="col-md-12">
    <div class="card">
        <div class="card-header bg-danger font-weight-bold text-white">
            <div class="row">
                <div class="col-md-11">
                    <?= $numberBloc ?>. INFOS DONNEUR D'ORDRE
                </div>
                <input type="text" id="idDO" value="<?= $do ? $do->idCompany : "" ?>" hidden>
            </div>
        </div>
        <div class="card-body bg-white" style="background-color:whitesmoke;">

            <div class="row mb-1">
                <div class="col-md-4 ">
                    <div class="row">
                        <span class="col-md-2  p-2">Nom</span>
                        <input readonly class="form-control col-md-9" type="text" name="" id=""
                            value="<?= $do ? $do->name : "" ?>">
                    </div>
                </div>

                <div class="col-md-4 ">
                    <div class="row">
                        <span class="col-md-2 p-2">Tél</span>
                        <input readonly class="form-control col-md-6" type="text" name="" id="telDO"
                            value="<?= $do ? $do->businessPhone : "" ?>">
                        <div class="col-md-4">
                            <button type="button" rel="tooltip" title="Passer un appel" onclick="callContact()"
                                class="btn btn-sm btn-info btn-simple btn-link">
                                <i class="fas fa-phone-alt" style="color: #ffffff"></i>
                            </button>

                            <a type="button" rel="tooltip" title="Detail"
                                class="btn btn-sm btn-success btn-simple btn-link"
                                href="https://api.whatsapp.com/send?phone=<?= $do ? $do->businessPhone : "" ?>">
                                <i class="fab fa-whatsapp" style="color: #ffffff"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 ">
                    <div class="row">
                        <span class="col-md-2  p-2">Email</span>
                        <input readonly class="form-control col-md-9" type="text" name="" id=""
                            value="<?= $do ? $do->email : "" ?>">
                    </div>
                </div>
            </div>
            <div class="row mb-1 ">
                <div class="col-md-4 ">
                    <div class="row">
                        <span class="col-md-2 p-2">Statut</span>
                        <input readonly class="form-control col-md-9" type="text" name="" id="statutDO"
                            value="<?= $do ? $do->category : "" ?>">
                    </div>
                </div>
                <div class="col-md-4 px-2">
                    <div class="row">
                        <span class="col-md-2 p-2">Catégorie</span>
                        <input readonly class="form-control col-md-9" type="text" name="" id="categorieD"
                            value="<?= $do ? $do->categorieDO : "" ?>">
                    </div>
                </div>
                <div class="col-md-4 ">
                    <div class="row">
                        <span class="col-md-2 p-2">Sous-catégorie</span>
                        <input readonly class="form-control col-md-9" type="text" name="" id="sousCategorieDO"
                            value="<?= $do ? $do->sousCategorieDO : "" ?>">
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>