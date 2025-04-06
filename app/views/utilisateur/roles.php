<?php
    $employe = !$employe ? $_SESSION['connectedUser'] : $employe;
    HtmlProvider::start_page();
    addPrivateRoute();
    echo HtmlProvider::viewTitleBarWithRole($title,'', 'black', 'black', linkTo('Microfinance','listePret'),getRouteAccess());
    
    
    inputHiddenForUrlRoot();
   
?>
<div class="page-body">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="page-body">
                        <div class="card-block">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h4 class="sub-title">Information de l'employé</h4>
                                    <form method="post" action="<?= linkTo('Utilisateur', 'changerProfil',$employe->idEmp) ?>">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Nom</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" readonly name="nom" value="<?= $employe->nomEmp ?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Prénom</label>
                                            <div class="col-sm-10">
                                                <input type="text" readonly class="form-control" value="<?= $employe->prenomEmp ?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Téléphone</label>
                                            <div class="col-sm-10">
                                                <input type="text" readonly class="form-control" name="telephone" value="<?= $employe->telephoneEmp ?>">
                                            </div>
                                        </div>
                                        <hr>
                                        <?php if(!isset($employe->role)) {?>
                                        <p style="display: <?= isset($employe->role) ? 'none' : 'block' ?>;" class=" text-primary">Le mot de passe des nouveaux utilisateurs est " passer ". Ils doivent le modifer à la première connexion.</p>
                                        <div class="form-group row" style="display: <?= isset($employe->role) ? 'none' : 'flex' ?>;">
                                            <label class="col-sm-2 col-form-label">Nom d'utilisateur</label>
                                            <div class="col-sm-10">
                                                <input type="text"  class="form-control" name="userName">
                                            </div>
                                        </div>
                                        <?php }?>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Role</label>
                                            <div class="col-sm-7">
                                                <select name="role" id="role" class="form-control">
                                                    <?php
                                                    foreach ($roles as $role) {
                                                    ?>
                                                        <option <?= isset($employe->role) && $role->idRole == $employe->role ? 'selected' : '' ?> value="<?= $role->idRole ?>"><?= $role->libelleRole ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <button class="btn btn-sm btn-outline-primary" <?= Role::getRole() == 'Administrateur' ? '' : 'disabled' ?> type="submit" name="saveRole">Enregistrer</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-sm-6" style="display: <?= isset($employe->role) ? 'block' : 'none' ?>;">
                                    <h4 class="sub-title">Modifier le mot de passe</h4>
                                    <form method="post" action="<?= linkTo('Utilisateur', 'changePasse') ?>">
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label">Ancien mot de passe</label>
                                            <div class="col-sm-8">
                                                <input type="password" class="form-control" name="password1">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label">Nouveau mot de passe</label>
                                            <div class="col-sm-8">
                                                <input type="password" name="password2" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label">Confirmation de mot de passe</label>
                                            <div class="col-sm-8">
                                                <input type="password" name="password3" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group row float-right">
                                            <input class="btn btn-sm btn-outline-secondary float-right" type="submit" name="savePasse" value="Enregistrer">
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <p style="display: <?= $message ? 'block' : 'none' ?>;" class="alert alert-danger"> Verifiez vos données!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php HtmlProvider::end_page() ?>
