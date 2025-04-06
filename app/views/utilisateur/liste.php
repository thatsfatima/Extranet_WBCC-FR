<?php
//var_dump($typeDepenses);
HtmlProvider::start_page();
addPrivateRoute();
echo HtmlProvider::viewTitleBarWithRole("GESTION DES UTILISATEURS", '', '', 'success', linkTo('', ''), getRouteAccess());

inputHiddenForUrlRoot();
?>

<div class="page-body">

    <div class="row">
        <div class="col-sm-12">

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <span class="col-md-2 ml-5 "><button type="button" class="btn btn-primary btn-round waves-effect btn-sm" data-toggle="modal" data-target="#newRole">Nouvel Utilisateur</button></span>
                        <div class="form-group col-md-6">
                            <input type="text" id="mySearchText" placeholder="Recherche ..." style="width:100%; padding:3px; border-radius:5px">
                        </div>
                    </div>


                    <div class="page-body">
                        <div class="row">
                            <div class="col-sm-12">

                                <div class="card">

                                    <div class="card-block">
                                        <div class="dt-responsive table-responsive">
                                            <table id="example" class="display">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Prénom</th>
                                                        <th>Nom</th>
                                                        <th>Login</th>
                                                        <th>Téléphone</th>
                                                        <th>Email</th>
                                                        <th>Role</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    foreach ($users as $user) {
                                                        $idUser = $user->idUtilisateur;
                                                        $idEmp = $user->idEmp;
                                                        $trHidden = '';
                                                        $changeLink = $idUser . '/' . $user->etatUser;
                                                        if ($user->etatUser == 1) {
                                                            $btn = 'Bloquer';
                                                            $css = 'danger';
                                                        } else {
                                                            $btn = 'Activer &nbsp;';
                                                            $css = 'success';
                                                        }
                                                        $disable = '';
                                                        if (strtolower($user->libelleRole) == 'administrateur') {
                                                            $disable = 'disabled';
                                                        }
                                                        if ($idUser == $_SESSION['connectedUser']->idUtilisateur) {
                                                            $trHidden = 'hidden';
                                                        }
                                                        echo "<tr $trHidden class='hovered-tr' dateDep='q' idDep='q' onclick=trClicked(this)>";
                                                        echo HtmlProvider::td_printer(['prenomEmp', 'nomEmp', 'login', 'telephoneEmp', 'email', 'libelleRole'], $user);
                                                        echo "<td class='text-right'>
                                                                <a href='" . linkTo('Personnel', 'edit', $idEmp, 0) . "' class='btn btn-sm btn-warning'>Editer</a>
                                                                <a href='" . linkTo('Utilisateur', 'changeUserState', $changeLink) . "' class='btn btn-sm btn-$css $disable'>$btn</a>
                                                            </td>";
                                                        echo "</tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="newRole" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h4 class=" mt-0 text-primary font-weight-bold">Nouvel Utilisateur</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="<?= linkTo('Personnel', 'save') ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="" class="active">Civilité</label>
                            <select name="civilite" class="form-control form-control-primary" required>
                            <option value="" selected disabled>-- Choisir la civilité</option>
                                <option value="Mr">Monsieur</option>
                                <option value="Mme">Madame</option>
                                <option value="Mlle">Mademoiselle</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="active">Groupe</label>
                            <select name="groupe" id="role" class="form-control" required>
                                <option value="" selected disabled>-- Choisir un groupe --</option>
                                <?php
                                foreach ($roles as $role) { $hidden = $role->etatRole == 1 ? '' : 'hidden';
                                ?>
                                    <option <?= $hidden ?> value="<?= $role->idRole ?>"><?= $role->libelleRole ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="role" class="active">Prénom</label>
                            <input name="prenom" required type="text" id="role" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="active">Nom</label>
                            <input name="nom" required type="text" id="role" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="role" class="active">Téléphone</label>
                            <input name="tel" required type="text" id="role" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="active">Email</label>
                            <input name="email" required type="text" id="role" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="role" class="active">Nom d'utilisateur</label>
                            <input name="login" required type="text" id="role" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="active">Mot de passe <small>(Par défaut : passer)</small></label>
                            <input type="password" disabled value="passer" id="role" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger waves-effect " data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary waves-effect waves-light ">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>