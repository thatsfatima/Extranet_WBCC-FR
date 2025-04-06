<div class="card">
    <div class="card-header bg-danger font-weight-bold text-white">
        <div class="row">
            <div class="col-md-10">
                <?= $numberBloc ?>. DOCUMENTS
            </div>
            <div class="col-md-2" hidden>
                <button type="button" rel="tooltip" title="Rediger une note" onclick="ShowModalDocument('0')"
                    class="btn btn-sm btn-dark btn-simple btn-link ">
                    <i class="fa fa-plus" style="color: #ffffff"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body bg-white">
        <?php

        if ($documents == null) {
        ?>
            <div class="font-weight-bold text-center text-danger">
                <span class="">Aucun document !!</span>
            </div>
        <?php
        } else {
        ?>
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable11" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th></th>
                            <th>Nom</th>
                            <th>Date</th>
                            <th>Auteur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;

                        foreach ($documents as $doc) {

                        ?>
                            <tr>
                                <td>
                                    <?= $i++ ?></td>
                                <td style="text-align : center">
                                    <a type="button" rel="tooltip" title="Voir contenu"
                                        href="<?= URLROOT . '/public/documents/opportunite/' . $doc->urlDocument ?>"
                                        target="_blank" class="btn btn-sm btn-info btn-simple btn-link">
                                        <i class="fas fa-eye" style="color: #ffffff"></i>
                                    </a>
                                </td>
                                <td>
                                    <?= $doc->nomDocument ?></td>
                                <td>
                                    <?= $doc->createDate ?></td>
                                <td>
                                    <?= $doc->auteur ?></td>

                            </tr>
                        <?php    }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php
        }
        ?>
    </div>
</div>