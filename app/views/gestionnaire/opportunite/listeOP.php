<?php

// $hiddenFinal = (isset($etape) && $etape != "") ? "hidden" : "";
$active = "red";
$role = $_SESSION["connectedUser"]->role;
$login = $_SESSION["connectedUser"]->login;

$dossier = $role == "Gestionnaire EXTERNE" ? "GestionnaireExterne" : "Gestionnaire";


?>


<div class="section-title">
    <div class="row">
        <div class="col-md-6 text-uppercase">
            <h2><span><i class="fas fa-fw fa-folder " style="color: #c00000"></i></span><?= $titre ?>
                (<?= sizeof($opportunities) ?>)</h2>
        </div>

    </div>
</div>

<div class="row mt-0">

    <!-- <hr style="width: 1px; height: 100%; border : 1px solid #808080"> -->
    <div class="card col-md-11 mx-auto">



        <div class="card">
            <div class="modal-content">
                <div class="card-header bg-secondary text-white">
                    <div class="row">

                        <div class="col-md-12">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">

                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th <?= $type == "empty" ? "hidden" : "" ?>> Actions</th>
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
                                foreach ($opportunities as $op) {
                                ?>
                                    <tr>

                                        <td <?= $type == "empty" ? "hidden" : "" ?>>
                                            <a type="button" rel="tooltip" title="Detail"
                                                href="<?= linkto($dossier, $type, $op->idOpportunity) ?>"
                                                class="btn btn-sm btn-info btn-simple btn-link">
                                                <i class="fas fa-folder-open" style="color: #ffffff"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <?= $i++ ?></td>
                                        <td>
                                            <?= $op->name ?></td>
                                        <td>
                                            <?= $op->contactClient ?></td>
                                        <td>
                                            <?= $op->nomGestionnaireAppImm ?></td>
                                        <td>
                                            <?= ($op->status == 'Lost' ? 'Clôtué Perdu' : ($op->status == 'Won' ? 'Clôturé gagné' : ($op->status == 'Inactive' ? 'Inactif' : 'Ouvert'))) ?>
                                        </td>
                                        <td>
                                            <?= $op->commercial ?></td>
                                        <td>
                                            <?= $op->type ?></td>
                                        <td>
                                            <?= $op->typeSinistre ?></td>
                                        <td>
                                            <?= date('d/m/Y', strtotime(str_replace('/', '-', $op->createDate))) ?></td>

                                    </tr>
                                <?php    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="<?= URLROOT ?>/public/assets/vendor/jquery/jquery.min.js"></script>