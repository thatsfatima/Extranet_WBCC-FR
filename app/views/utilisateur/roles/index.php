<?php
HtmlProvider::pageStart();
inputHiddenForUrlRoot();
addPrivateRoute();
echo HtmlProvider::viewTitleBarWithRole('GESTION DES GROUPES', '', '', 'success', linkTo('Eleve'), getRouteAccess());
// var_dump($roles);
?>


<div class="page-body">
    <div class="row">
        <div class="col-sm-1"></div>
        <div class="col-sm-10 ">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <span class="col-md-2 mr-5 ml-3"><button type="button" class="btn btn-danger btn-round waves-effect md-trigger text-dark btn-sm" data-toggle="modal" data-target="#deletedRole">Groupes supprimés</button></span>
                        <span class="col-md-2 ml-5 "><button type="button" class="btn btn-primary btn-round waves-effect btn-sm" data-toggle="modal" data-target="#newRole">Nouveau Groupe</button></span>
                    </div>
                    <div class="page-body">
                        <div class="form-group col-md-6 mt-2">
                            <input type="text" id="mySearchText" placeholder="Recherche ..." style="width:100%; padding:3px; border-radius:5px">
                        </div>
                        <div class="card-block">
                            <form method="post">
                                <section>
                                    <!-- Gird column -->
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <table id="example" class="display " style="width:100%">
                                                    <thead class="bg-light font-weight-bold">
                                                        <tr>
                                                            <th>NOM</th>
                                                            <th>CONTROLEUR</th>
                                                            <th>ACTIONS</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                        <?php foreach ($roles as $role) {
                                                            if ($role->etatRole) {
                                                        ?>
                                                                <tr>
                                                                    <td><?= $role->libelleRole ?></td>
                                                                    <td><?= $role->link ?></td>
                                                                    <td>
                                                                        <a href="#" type="button" class="btn btn-sm text-warning mr-2" onclick="updateGroupe('<?= $role->libelleRole ?>',<?= $role->idRole ?>,'<?= $role->link ?>')" data-toggle="modal" data-target="#updateGroupe">
                                                                            <i class="fa fa-edit"></i>
                                                                        </a>
                                                                        <a href="#" type="button" class="btn btn-sm text-danger " onclick="deleteRole('<?= $role->libelleRole ?>')" data-toggle="modal" data-target="#deleteRole">
                                                                            <i class="fa fa-trash"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                        <?php }
                                                        } ?>
                                                       
                                                    </tbody>
                                                    <tfoot></tfoot>
                                                </table>

                                            </div>
                                            <div class="row col-sm-4 offset-8">
                                                <!-- <button type="submit" name="save" class="btn btn-outline-primary //count($tabLiens) == 0 ? 'disabled' : '' ">Enregistrer</button> -->
                                            </div>
                                        </div>

                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php HtmlProvider::end_page() ?>


    <div class="modal fade" id="newRole" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h4 class=" mt-0 text-primary font-weight-bold">Nouveau Groupe</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="<?= linkTo('Parametrage', 'saveRoles') ?>">
                    <div class="modal-body">
                        <div class="row col-sm-12">
                            <label for="role" class="active">Saisir le nom du Groupe</label>
                            <input name="role" required type="text" id="role" class="form-control">
                        </div>
                        <div class="row col-sm-12">
                            <label for="role" class="active">Saisir le nom du Controlleur <small class="text-info">(CamelCase)</small></label>
                            <input name="ctrl" required type="text" id="ctrl" class="form-control">
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


    <div class="modal fade" id="deleteRole" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="p-2 bg-danger">
                    <span class="text-white"> Groupe : <span class="h4" id="libelleRole">...</span></span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="<?= linkTo('Parametrage', 'changeRoleState', -1) ?>">
                    <input type="hidden" id="hiddenRole" name="role">
                    <div class="modal-body">
                        <div class="row col-sm-12">
                            <h4 class="text-danger">Voulez-vous supprimer ce groupe ?</h4>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary waves-effect " data-dismiss="modal">NON</button>
                        <button type="submit" class="btn btn-danger waves-effect waves-light ">OUI</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- UPDATE ROLE -->
    <div class="modal fade" id="updateGroupe" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class=" mt-0">Edition d'un groupe</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="<?= linkTo('Parametrage', 'updateRole') ?>">
                    <input type="hidden" id="idGroupe" name="idGroupe">
                    <small class="text-info ml-5">(Le nom du controlleur en CamelCase)</small>
                    <div class="modal-body mt-0">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="">Nom du Groupe</h4>
                                <input type="text" class="form-control" id="nomGroupe" name="nomGroupe">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="">Nom du controlleur</h4>
                                <input type="text" class="form-control" id="nomCtrl" name="nomCtrl">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-warning ">Enregistrer</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deletedRole" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="p-2 bg-danger">
                    <span class="h4 text-white">Les groupes supprimés</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="<?= linkTo('Parametrage', 'changeRoleState', 1) ?>">
                    <div class="modal-body">
                        <div class="red lighten-4 view view-cascade gradient-card-header narrower py-2 mx-4 mb-3 d-flex justify-content-between align-items-center mt-2">
                            <div class="row col-sm-12">
                                <label for="deletedRole" class="active">Groupes</label>
                                <select name="role" id="deletedRole" class="form-control">
                                    <?php foreach ($roles as $role) {
                                        if (!$role->etatRole) {
                                    ?>
                                            <option value="<?= $role->libelleRole ?>"><?= $role->libelleRole ?></option>
                                    <?php }
                                    } ?>
                                </select>
                                <p class="mt-3">Séléctionnez un groupe supprimé pour le rétablir</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary waves-effect btn-sm" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger waves-effect waves-light btn-sm">Rétablir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function deleteRole(role) {
            $('#libelleRole').text(role);
            $('#hiddenRole').val(role);
        }

        function updateGroupe(nomG, idG, nomC) {
            console.log(nomG, idG, nomC);
            $('#nomGroupe').val(nomG);
            $('#idGroupe').val(idG);
            $('#nomCtrl').val(nomC);
        }
    </script>