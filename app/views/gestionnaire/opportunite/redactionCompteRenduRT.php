<div class="card mt-0 position-relative">
    <div class="modal-content">
        <div class="card-header bg-secondary text-center">
            <div class="row">
                <div class="col-md-12 pull-left row">
                    <div class="col-lg-10 col-md-10 col-sm-8">
                        <h4 class="ml-5 text-white text-center text-uppercase font-weight-bold">
                            <?= (($activityFRT && $activityFRT->isCleared == "False") || $op->faireRapportRT == "0") ? "Faire Compte Rendu RT" : "Contrôler Compte Rendu RT"
                            ?>
                        </h4>
                    </div>
                    <div class="col-md-2 col-lg-2 col-sm-4"
                        <?= (($activityFRT && $activityFRT->isCleared == "False") || $op->faireRapportRT == "0") ? "" : "" ?>>
                        <button type="button" rel="tooltip" title="Enregistrer les sections"
                            onclick="saveSections()"
                            class="btn btn-sm btn-info btn-simple btn-link hidden">
                            <i class="fa fa-save" style="color: #ffffff"></i>
                        </button>
                        <button type="button" rel="tooltip" title="Enregistrer les modifications"
                            onclick="saveInfosCompteRenduRT(0)"
                            class="btn btn-sm btn-danger btn-simple btn-link ">
                            <i class="fa fa-save" style="color: #ffffff"></i>
                        </button>
                        <button type="button" rel="tooltip" title="Voir les modifications"
                            onclick="saveInfosCompteRenduRT(3)" class="btn btn-sm btn-info">
                            <i class="fa fa-eye" style="color: #ffffff"></i> Visualiser
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body bg-white row mx-0 h-auto">
            <div class="col-md-3 border-right border-secondary" style="height: 100%;">
                <!-- <div class="row col-md-12">
                    <label class="font-weight-bold"><?= $sommaire ? htmlspecialchars($sommaire->titreSommaire) : "" ?></label>
                </div> -->
                <div class="row col-md-12">
                    <h5 class="col-md-12 font-weight-bold text-uppercase text-center">Sommaire</h5>
                </div>
                <div class="row col-md-12 mb-3 mx-0" id="showSection">
                </div>
            </div>
            <?php
                $i = 0;
            ?>
            <div class="col-md-9">
                <div class="row col-md-12 mx-0 px-0">
                    <div class="col-md-12 px-0">
                        <button class="col-md-3 row mx-0 space-x-1 btn btn-primary float-right" type="button" onclick="saveOneSection()"><i class="fa fa-save"></i> Enregistrer</button>
                    </div>
                </div>
                <div class='form'
                    <?= (($activityFRT && $activityFRT->isCleared == "False") || $op->faireRapportRT == "0") ? "" : "" ?>>
                    <div class="row">
                        <div class="col-md-12 mb-4 sectionSection section1-" data-parent="true" data-idparent="" data-numero="1-" data-titre="Introduction" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                            <label class="font-weight-bold">Introduction</label>
                            <textarea style="height:45vh" class="form-control tinymce-editor"
                                id="1-"><?= $introduction ?></textarea>
                        </div>
                        <!-- IMAGE IMMEUBLE -->
                        <div class="col-md-12 mb-4 sectionSection hidden section2-" data-parent="true" data-idparent="" data-numero="2-" data-titre="Photo Immeuble" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $idParent = $sections[$i]->idSection ?? $i; $i++; ?>>
                            <label class="font-weight-bold">2- Photo Immeuble : <?= $op->adresse ?> <a
                                    class="btn btn-danger fs-5" target="_blank"
                                    href='https://www.google.com/maps/place/<?= $op->adresse ?>'><i
                                        class="fas fa-street-view"></i></a></label>
                            <div class='form-group col-md-12 row'>
    
                                <div style=" width: 400px;
                                /*Sort of important*/
                                height: 300px;
                                position:absolute;
                                left:50%;
                                /* top:100px; */
                                margin-bottom : 20px;
                                margin-left:-150px;
                                border: 2px dashed rgba(0,0,0,.3);
                                border-radius: 20px;
                                font-family: Arial;
                                text-align: center;
                                position: relative;
                                line-height: 180px;
                                font-size: 20px;
                                color: rgba(0,0,0,.3);" id="dropContainer"
                                    style="border:1px solid black;height:100px;">
                                    <?= $photoImm ?>

                                </div>
                                <input type='file' id="file" class='col-md-11 form-control'
                                    accept='.jpg, .png, .jpeg, .JPG, .PNG, .JPEG'>
                                <button type="button" rel="tooltip" title="Effacer la pièce jointe"
                                    onclick="deleteFileImage()" class="col-md-1 btn btn-danger ">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-12 mb-4 sectionSection hidden section3-" data-parent="true" data-idparent="" data-numero="3-" data-titre="Contexte" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $idParent = "3-"; $i++; ?>>
                            <label class="font-weight-bold">3- Contexte</label>
                            <textarea style="height:45vh" class="form-control tinymce-editor" id="3-"><?= $contexte ?></textarea>
                        </div>
                        <?php
                            $n = "3-1";
                        ?>
                        <div class="col-md-12 sectionSection hidden section<?= $n ?> " data-parent="false" data-idparent="<?= $idParent ?>" data-numero="<?= $n ?>" data-titre="<?= "Description du sinistre" ?>" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                            <label class="font-weight-bold text-danger">
                                <?= $n . " Description du Sinistre" ?>
                            </label>
                            <textarea style="height:45vh" class="form-control tinymce-editor" name="descriptionSinistre"
                                id="<?= $n ?>"><?= $descriptionSinistre ?></textarea>
                        </div>
                        <?php
                            $n = "3-2";
                        ?>
                        <div class="col-md-12 sectionSection hidden section<?= $n ?> " data-parent="false" data-idparent="<?= $idParent ?>" data-numero="<?= $n ?>" data-titre="<?= "Origine du sinistre" ?>" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                            <label class="font-weight-bold text-danger">
                                <?= $n . " Origine du Sinistre" ?>
                            </label>
                            <textarea style="height:45vh" class="form-control tinymce-editor" name="origineSinistre"
                                id="<?= $n ?>"><?= $origineSinistre1 ?></textarea>
                        </div>
                        <?php
                            $n = "3-3";
                        ?>
                        <div class="col-md-12 sectionSection hidden section<?= $n ?> " data-parent="false" data-idparent="<?= $idParent ?>" data-numero="<?= $n ?>" data-titre="<?= "Interventions initiales" ?>" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                            <label class="font-weight-bold text-danger">
                                <?= $n . " Interventions initiales" ?>
                            </label>
                            <textarea style="height:45vh" class="form-control tinymce-editor" name="interventionsInitiales"
                                id="<?= $n ?>"><?= $interventionsInitiales ?></textarea>
                        </div>
                        <div class="col-md-12 mb-4 sectionSection hidden section4-" data-parent="true" data-idparent="" data-numero="4-" data-titre="Deroulement de la séance" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $idParent = "4-"; $i++; ?>>
                            <label class="font-weight-bold">4- Déroulement de la séance</label>
                            <textarea style="height:45vh" class="form-control tinymce-editor"
                                id="4-"><?= $deroulementSeance ?></textarea>
                        </div>
                        <?php
                            $n = "4-1";
                        ?>
                        <div class="col-md-12 mb-4 sectionSection hidden section<?= $n ?>" data-parent="false" data-idparent="<?= $idParent ?>" data-numero="<?= $n ?>" data-titre="Inspection de l'appartement" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                            <label class="font-weight-bold"> <?= $n ?> Inspection de l'appartement </label>
                            <textarea style="height:45vh" class="form-control tinymce-editor"
                                id="<?= $n ?>"><?= $inspectionAppartement ?></textarea>
                        </div>
                        <?php
                            $n = "4-2";
                        ?>
                        <div class="col-md-12 mb-4 sectionSection hidden section<?= $n ?>" data-parent="false" data-idparent="<?= $idParent ?>" data-numero="<?= $n ?>" data-titre="Confirmation de la nature du sinistre" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                            <label class="font-weight-bold"> <?= $n ?> Confirmation de la nature du sinistre </label>
                            <textarea style="height:45vh" class="form-control tinymce-editor"
                                id="<?= $n ?>"><?= $confirmationNSinistre ?></textarea>
                        </div>
                        <?php
                            $n = "4-3";
                        ?>
                        <div class="col-md-12 mb-4 sectionSection hidden section<?= $n ?>" data-parent="false" data-idparent="<?= $idParent ?>" data-numero="<?= $n ?>" data-titre="Identification de l’origine du sinistre" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                            <label class="font-weight-bold"> <?= $n ?> Identification de l’origine du sinistre </label>
                            <textarea style="height:45vh" class="form-control tinymce-editor"
                                id="<?= $n ?>"><?= $identificationOSnistre ?></textarea>
                        </div>
                        <?php
                            $n = "4-4";
                        ?>
                        <div class="col-md-12 mb-4 sectionSection hidden section<?= $n ?>" data-parent="false" data-idparent="<?= $idParent ?>" data-numero="<?= $n ?>" data-titre="Identification des responsabilités" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                            <label class="font-weight-bold"> <?= $n ?> Identification des responsabilités </label>
                            <textarea style="height:45vh" class="form-control tinymce-editor"
                                id="<?= $n ?>"><?= $identificationResponsabilites ?></textarea>
                        </div>
                        <?php
                            $n = "4-5";
                        ?>
                        <div class="col-md-12 mb-4 sectionSection hidden section<?= $n ?>" data-parent="false" data-idparent="<?= $idParent ?>" data-numero="<?= $n ?>" data-titre="Description des dégâts constatés" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                            <label class="font-weight-bold"> <?= $n ?> Description des dégâts constatés </label>
                            <textarea style="height:45vh" class="form-control tinymce-editor"
                                id="<?= $n ?>"><?= $descriptionDegats ?></textarea>
                        </div>
                        <?php
                            $n = "4-6";
                        ?>
                        <div class="col-md-12 mb-4 sectionSection hidden section<?= $n ?>" data-parent="false" data-idparent="<?= $idParent ?>" data-numero="<?= $n ?>" data-titre="Evaluation des dommages matériels et structurels" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                            <label class="font-weight-bold"> <?= $n ?> Evaluation des dommages matériels et structurels </label>
                            <textarea style="height:45vh" class="form-control tinymce-editor"
                                id="<?= $n ?>"><?= $evaluationDommages ?></textarea>
                        </div>
                        <?php
                            $n = "4-7";
                        ?>
                        <div class="col-md-12 mb-4 sectionSection hidden section<?= $n ?>" data-parent="false" data-idparent="<?= $idParent ?>" data-numero="<?= $n ?>" data-titre="Proposition de solutions pour la réparation et la prévention" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                            <label class="font-weight-bold"> <?= $n ?> Proposition de solutions pour la réparation et la prévention </label>
                            <textarea style="height:45vh" class="form-control tinymce-editor"
                                id="<?= $n ?>"><?= $solutions ?></textarea>
                        </div>
                        <div class="col-md-12 mb-4 hidden sectionSection section5-" data-parent="true" data-idparent="" data-numero="5-" data-titre="Conclusion" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                            <label class="font-weight-bold">Conclusion</label>
                            <textarea style="height:45vh" class="form-control tinymce-editor"
                                id="5-"><?= $conclusion ?></textarea>
                        </div>
                        <div class="col-md-12 sectionSection hidden section6-" data-parent="false" data-idparent="" data-numero="6-" data-titre="Detail des dégradations" id="section<?= $sections[$i]->numeroSection ?? $i ?>-">
                            <label class="font-weight-bold">6- Détail des dégradations</label>
                            <?php
                            $idParent = "6-";
                            $i++;
                            foreach ($pieces as $key => $piece) {
                                $pellicules =  $piece->photosPiece == "" || $piece->photosPiece == null ? [] : explode(";", $piece->photosPiece);
                                $libSupport = $piece->commentaireSupport != null && $piece->commentaireSupport != "" ? $piece->commentaireSupport : "";
                                if ($libSupport == "") {
                                    $nbMursSinistres = 0;
                                    $nbMursNonSinistres = 0;
                                    $libSansMur = "";
                                    $surfaceMursSinistres = 0;
                                    $surfaceMursNonSinistres = 0;
                                    foreach ($piece->listSupports as $key2 => $support) {
                                        if (str_contains($support->nomSupport, "MUR")) {
                                            $sTotal =  ($support->longueurSupport != "" &&  $support->longueurSupport != null  &&  $support->longueurSupport != "NULL" && $support->largeurSupport != "" &&  $support->largeurSupport != null && $support->largeurSupport != "NULL" ? round(($support->longueurSupport * $support->largeurSupport), 2) : 0);
                                            //Calcul OUV
                                            $sOuverture = 0;
                                            foreach ($support->listOuvertures as $key3 => $ouverture) {
                                                $sOuverture +=  ($ouverture->longueurOuverture != "" &&  $ouverture->longueurOuverture != null &&  $ouverture->longueurOuverture != "NULL" && $ouverture->largeurOuverture != "" &&  $ouverture->largeurOuverture != null &&  $ouverture->largeurOuverture != "NULL" ? round(($ouverture->longueurOuverture * $ouverture->largeurOuverture), 2) : 0);
                                            }
                                            if ($support->estSinistre == "1") {
                                                $nbMursSinistres++;
                                                if ($support->siDeduire == "1") {
                                                    $surfaceMursSinistres +=  ($sTotal - $sOuverture);
                                                } else {
                                                    $surfaceMursSinistres +=  ($sTotal);
                                                }
                                            } else {
                                                $nbMursNonSinistres++;
                                                if ($support->siDeduire == "1") {
                                                    $surfaceMursNonSinistres +=  ($sTotal - $sOuverture);
                                                } else {
                                                    $surfaceMursNonSinistres +=  ($sTotal);
                                                }
                                            }
                                        } else {
                                            $libSupport .= (($libSupport == "") ? $support->nomSupport : (", " . $support->nomSupport));
                                        }
                                    }
    
                                    if ($nbMursNonSinistres != 0 || $nbMursSinistres != 0) {
                                        $libSupport = $libSupport == "" ? "" : $libSupport . ", ";
                                        $libSupport .= ($nbMursSinistres == 1 ? "$nbMursSinistres Mur Sinistré" : "$nbMursSinistres Murs Sinistrés") . " et " .  ($nbMursNonSinistres == 1 ? "$nbMursNonSinistres Mur Non Sinistré" : "$nbMursNonSinistres Murs Non Sinistrés");
                                    }
                                }
                                $n = "6-" . ($key + 1) .  "-1";
                            ?>
                                <div class="col-md-12 sectionSection hidden section6- section<?= $n ?> <?= $key != 0 ? "mt-5" : "" ?> " data-parent="true" data-idparent="<?= $idParent ?>" data-numero="<?= $n ?>" data-titre="<?= "Piéce " . $key + 1 . " : " . $piece->nomPiece ?>" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $idParentP = $n; $i++; ?>>
                                    <label class="font-weight-bold text-danger">
                                        <?= $n . " Piéce " . $key + 1 . " : " . $piece->nomPiece ?>
                                    </label>
                                </div>
                                <div <?= sizeof($pellicules) != 0 ? "" : "hidden" ?> style="height:45vh"
                                    class="col-md-12 sectionSection hidden section6- section<?= $n ?> my-2">
                                    <div class="col text-center pb-2 d-flex align-items-center">
                                        <div id="carouselExampleIndicators<?= $key ?>" class="col carousel slide"
                                            data-ride="carousel">
    
                                            <div class="carousel-inner">
                                                <?php
                                                foreach ($pellicules as $key5 => $value) {
                                                    if ($value != "") {
                                                ?>
                                                        <?php $n2 = "6-" . ($key + 1) .  "-1-".($key5 + 1); ?>
                                                        
                                                        <div class="carousel-item <?= $key5 == 0 ? "active" : "" ?>">
                                                            <img class="d-block"
                                                                src="<?= URLROOT . '/public/documents/opportunite/' . $value ?>"
                                                                alt="First slide" width="70%" height="300px"
                                                                style="margin-left:15%"><?= $commentairePhoto ?>
                                                        </div>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </div>
    
    
                                            <a class="carousel-control-prev"
                                                href="#carouselExampleIndicators<?= $key ?>" role="button"
                                                data-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="sr-only">Previous</span>
                                            </a>
                                            <a class="carousel-control-next"
                                                href="#carouselExampleIndicators<?= $key ?>" role="button"
                                                data-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="sr-only">Next</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <!-- TABLEAU DIMENSIONS -->
                                <table class="table table-striped table-bordered text-center sectionSection hidden section6- section<?= $n ?> ">
                                    <tr>
                                        <th> Longueur (m) </th>
                                        <th> Largeur (m) </th>
                                        <th> Périmétre (m) </th>
                                        <th> Surface Totale (m2) </th>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold"><?= $piece->longueurPiece ?></td>
                                        <td class="font-weight-bold"><?= $piece->largeurPiece ?></td>
                                        <td class="font-weight-bold">
                                            <?= $piece->longueurPiece != "" && $piece->longueurPiece != null && $piece->largeurPiece != null && $piece->largeurPiece != "" ? (($piece->longueurPiece +  $piece->largeurPiece) * 2) : "" ?>
                                        </td>
                                        <td class="font-weight-bold">
                                            <?= $piece->longueurPiece != "" && $piece->longueurPiece != null && $piece->largeurPiece != null && $piece->largeurPiece != "" ? round(($piece->longueurPiece *  $piece->largeurPiece), 2) : "" ?>
                                        </td>
                                    </tr>
                                </table>
                                <!-- COMMENTAIRE SUR LES PIECES -->
                                <?php
                                    $n = "6-" . ($key + 1) . "-2";
                                ?>
                                <div class="col-md-12 sectionSection hidden section6- section<?= $n ?> " data-parent="true" data-idparent="<?= $idParent ?>" data-numero="<?= $n ?>" data-titre="<?= "Commentaire de la piece" ?>" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                                    <label class="font-weight-bold text-danger">
                                        <?= $n . " Commentaire de la piece" ?>
                                    </label>
                                    <textarea style="height:45vh" class="form-control tinymce-editor" name="commentairePiece"
                                        id="<?= $n ?>" data-idpiece="<?= $piece->idRTPiece ?>"><?= $piece->commentairePiece ?></textarea>
                                </div>
                                <!-- COMMENTAIRE SUR LES SUPPORTS -->
                                <?php
                                    $n = "6-" . ($key + 1) . "-3";
                                ?>
                                <div class="col-md-12 sectionSection hidden section6- section<?= $n ?> " data-parent="true" data-idparent="<?= $idParent ?>" data-numero="<?= $n ?>" data-titre="Commentaire sur les supports de la piéce" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                                    <label class="font-weight-bold text-danger">
                                        <?= $n . " Commentaire sur les supports de la piéce" ?>
                                    </label>
                                    <textarea style="height:45vh" class="form-control tinymce-editor" name="commentaireSupportPiece"
                                        id="<?= $n ?>" data-idpiece="<?= $piece->idRTPiece ?>"><?= $libSupport ?></textarea>
                                </div>
                                <!-- COMMENTAIRE DIMENSION -->
                                <?php
                                    $n = "6-" . ($key + 1) . "-4";
                                ?>
                                <div class="col-md-12 sectionSection hidden section6- section<?= $n ?> " data-parent="true" data-idparent="<?= $idParent ?>" data-numero="<?= $n ?>" data-titre="Commentaire sur les métrés de la piéce" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                                    <label class="font-weight-bold text-danger">
                                        <?= $n . " Commentaire sur les métrés de la piéce" ?>
                                    </label>
                                    <textarea style="height:45vh" class="form-control tinymce-editor" name="commentaireMetrePiece"
                                        id="<?= $n ?>" data-idpiece="<?= $piece->idRTPiece ?>"><?= $piece->commentaireMetrePiece ?></textarea>
                                </div>
    
                                <?php
                                foreach ($piece->listSupports as $key2 => $support) {
                                    $pellicules =  $support->photosSupport == "" || $support->photosSupport == null ? [] : explode(";", $support->photosSupport);
                                    $comLibelleSupport = $support->commentaireSupport != null && $support->commentaireSupport != "" ? $support->commentaireSupport : "";
                                    $surfaceOuv = 0;
                                    $revs = "";
                                    $ouvs = "";
                                    foreach ($support->listOuvertures as $key4 => $ouverture) {
                                        $surfaceOuv +=  ($ouverture->longueurOuverture != "" &&  $ouverture->longueurOuverture != null &&  $ouverture->longueurOuverture != "NULL" && $ouverture->largeurOuverture != "" &&  $ouverture->largeurOuverture != null &&  $ouverture->largeurOuverture != "NULL" ? round(($ouverture->longueurOuverture * $ouverture->largeurOuverture), 2) : 0);
                                        $ouvs .= $ouvs == "" ? $ouverture->libelleOuverture : ", " . $ouverture->libelleOuverture;
                                    }
                                    foreach ($support->listRevetements as $key4 => $rev) {
                                        $revs .= $revs == "" ? $rev->libelleRevetement : ", " . $rev->libelleRevetement;
                                    }
                                    $n2 = "6-" . ($key + 1) . '-'  . ($key2 + 2) . "-1";
                                ?>
                                    <div class="col-md-12 mt-3 sectionSection hidden section6- section<?= $n ?> section<?= $n2 ?>" data-parent="true" data-idparent="<?= $idParentP ?>" data-numero="<?= $n2 ?>" data-titre="<?= "$support->libelleSupport" ?>" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $idParentPP = $n2; $i++; ?>>
                                        <label class="font-weight-bold text-info">
                                            <?= $n2 . " $support->libelleSupport" ?></label>
                                    </div>
                                    <div <?= sizeof($pellicules) != 0 ? "" : "hidden" ?> style="height:45vh"
                                        class="col-md-12 my-2 sectionSection hidden section6- section<?= $n ?> section<?= $n2 ?>">
                                        <div class="col text-center pb-2 d-flex align-items-center">
                                            <div id="carouselExampleIndicators<?= $key . $key2 ?>"
                                                class="col carousel slide" data-ride="carousel">
    
                                                <div class="carousel-inner">
                                                    <?php
                                                    foreach ($pellicules as $key5 => $value) {
                                                        if ($value != "") {
                                                    ?>
                                                            <?php $n3 = "6-" . ($key + 1) . '-'  . ($key2 + 2) . "-1-" . ($key5 + 1); ?>
                                                            <?php $commentaireSupport = isset(explode("}", $support->commentsPhotosSupport)[$key5]) ? explode("}", $support->commentsPhotosSupport)[$key5] : ""; ?>
                                                            
                                                            <div class="carousel-item <?= $key5 == 0 ? "active" : "" ?>">
                                                                <img class="d-block"
                                                                    src="<?= URLROOT . '/public/documents/opportunite/' . $value ?>"
                                                                    alt="First slide" width="70%" height="300px"
                                                                    style="margin-left:15%"><?= $commentaireSupport ?>
                                                            </div>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
    
    
                                                <a class="carousel-control-prev"
                                                    href="#carouselExampleIndicators<?= $key . $key2 ?>" role="button"
                                                    data-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="sr-only">Previous</span>
                                                </a>
                                                <a class="carousel-control-next"
                                                    href="#carouselExampleIndicators<?= $key . $key2 ?>" role="button"
                                                    data-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="sr-only">Next</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- TABLEAU DIMENSIONS -->
                                    <table class="table table-striped table-bordered text-center sectionSection hidden section6- section<?= $n ?> section<?= $n2 ?>" style="width: 90%;">
                                        <tr>
                                            <th> Longueur (m) </th>
                                            <th> <?= ($support->nomSupport == "PLAFOND" || $support->nomSupport == "SOL" ? "Largeur" : "Hauteur") . " (m)" ?>
                                            </th>
                                            <th> Périmétre (m) </th>
                                            <th> Surface Totale (m2) </th>
                                            <th> Surface Totale Ouverture (m2) </th>
                                            <th> A Déduire </th>
                                            <th> Surface à Traiter </th>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold"><?= $support->longueurSupport ?></td>
                                            <td class="font-weight-bold"><?= $support->largeurSupport ?></td>
                                            <td class="font-weight-bold">
                                                <?= ($support->longueurSupport != "" &&  $support->longueurSupport != null  &&  $support->longueurSupport != "NULL" && $support->largeurSupport != "" &&  $support->largeurSupport != null && $support->largeurSupport != "NULL") ? ($support->longueurSupport +  $support->largeurSupport) * 2 : "" ?>
                                            </td>
                                            <td class="font-weight-bold">
                                                <?= ($support->longueurSupport != "" &&  $support->longueurSupport != null  &&  $support->longueurSupport != "NULL" && $support->largeurSupport != "" &&  $support->largeurSupport != null && $support->largeurSupport != "NULL") ?  round(($support->longueurSupport *  $support->largeurSupport), 2) : "" ?>
                                            </td>
                                            <td class="font-weight-bold">
                                                <?= round($surfaceOuv, 2) ?></td>
                                            <td class="font-weight-bold">
                                                <?= ($support->siDeduire == 1 ? "Oui" : "Non") ?></td>
                                            <td class="font-weight-bold">
                                                <?= ($support->longueurSupport != "" &&  $support->longueurSupport != null  &&  $support->longueurSupport != "NULL" && $support->largeurSupport != "" &&  $support->largeurSupport != null && $support->largeurSupport != "NULL") ?  ($support->siDeduire == 1 ? round(($support->longueurSupport *  $support->largeurSupport) - $surfaceOuv, 2) : round(($support->longueurSupport *  $support->largeurSupport), 2)) : "" ?>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- COMMENTAIRE DIMENSION -->
                                    <?php
                                        $n2 = "6-" . ($key + 1) . '-'  . ($key2 + 2) . "-2";
                                    ?>
                                    <div class="col-md-12 sectionSection hidden section6- section<?= $n ?> section<?= $n2 ?>" data-parent="true" data-idparent="<?= $idParentP ?>" data-numero="<?= $n2 ?>" data-titre="<?= "Commentaire sur les métrés du $support->libelleSupport" ?>" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                                        <label class="font-weight-bold  text-info"><?= $n2 . "  Commentaire sur les métrés du $support->libelleSupport" ?></label>
                                        <textarea style="height:45vh" class="form-control tinymce-editor" name="commentaireMetreSupport"
                                            id="<?= $n2 ?>" data-idpiece="<?= $support->idRTPieceSupport ?>"><?= $support->commentaireMetreSupport ?></textarea>
                                    </div>
                                    <!-- COMMENTAIRE SUR LES SUPPORTS -->
                                    <?php
                                        $n2 = "6-" . ($key + 1) . '-'  . ($key2 + 2) . "-3";
                                    ?>
                                    <div class="col-md-12 sectionSection hidden section6- section<?= $n ?> section<?= $n2 ?>" data-parent="true" data-idparent="<?= $idParentP ?>" data-numero="<?= $n2 ?>" data-titre="<?= "Commentaire sur le $support->libelleSupport" ?>" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $i++; ?>>
                                        <label class="font-weight-bold  text-info">
                                            <?= $n2 . "  Commentaire sur le $support->libelleSupport" ?>
                                        </label>
                                        <div class="col-md-12">
                                            <label class="font-weight-bold"> - <?= $revs ?>
                                            </label>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="font-weight-bold"> -
                                                <?= $ouvs == "" ? "Pas d'ouvertures" : $ouvs ?>
                                            </label>
                                        </div>
                                        <textarea rows="4" class="form-control tinymce-editor" name="commentaireSupport"
                                            id="<?= $n2 ?>" data-idpiece="<?= $support->idRTPieceSupport ?>"><?= $comLibelleSupport ?></textarea>
                                    </div>
                            <?php
                                    $k = $key + 1;
                                }
                            }
                            ?>
                        </div>
    
                        <div <?= sizeof($pelliculesRT) != 0 ? "" : "hidden" ?> style="height:45vh"
                            class="col-md-12  my-2 mt-3  sectionSection hidden section6-">
                            <div class="col text-center pb-2 d-flex align-items-center">
                                <div id="carouselExampleIndicators" class="col carousel slide" data-ride="carousel">
    
                                    <div class="carousel-inner">
                                        <?php
                                        foreach ($pelliculesRT as $key5 => $value) {
                                            if ($value != "") {
                                        ?>
                                                <?php $n = "6-" .  ($k) . "-".($key5 + 1); ?>
                                                <?php $commentairePhoto = isset(explode("}", $piece->commentsPhotosPiece)[$key5]) ? explode("}", $piece->commentsPhotosPiece)[$key5] : ""; ?>
                                                
                                                <div class="carousel-item <?= $key5 == 0 ? "active" : "" ?>">
                                                    <img class="d-block"
                                                        src="<?= URLROOT . '/public/documents/opportunite/' . $value ?>"
                                                        alt="First slide" width="70%" height="300px"
                                                        style="margin-left:15%"><?= $commentairePhoto ?>
                                                </div>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </div>
    
    
                                    <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button"
                                        data-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Previous</span>
                                    </a>
                                    <a class="carousel-control-next" href="#carouselExampleIndicators" role="button"
                                        data-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Next</span>
                                    </a>
                                </div>
                            </div>
                        </div>
    
                        <div class="col-md-12 mb-2 mt-2 sectionSection hidden section7-" data-parent="true" data-idparent="" data-numero="7-" data-titre="Commentaire et Circonstances du Sinistre" id="section<?= $sections[$i]->numeroSection ?? $i ?>-" <?php $idParentP = (count($sections) != 0) ? "7-" : $i;; ?>>
                            <label class="font-weight-bold">7- Commentaire et Circonstances du Sinistre</label>
                            <textarea style="height:45vh" class="form-control tinymce-editor"
                                id="7-"><?= $circonstances ?></textarea>
                        </div>
                    </div>
                    <div <?= (($op->devisFais == "1" && $op->delegationSigne == "1" && $op->rapportDelegation != null &&  str_contains(strtolower($op->rapportDelegation), "sign")) ?  "hidden" : "hidden") ?>
                        class="row mt-3 text-center">
                        <label class="font-weight-bold  text-danger">
                            Impossible de valider le compte rendu, assurez-vous que le devis soit fait et la
                            dernière délégation de gestion signée
                        </label>
                    </div>
                </div>
                <div class='form'
                    <?= (($activityFRT && $activityFRT->isCleared == "False") || $op->faireRapportRT == "0")  ? "hidden" : ((($activityCRT && $activityCRT->isCleared == "False") || $op->controleRT == "0") ? "" : "hidden") ?>>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-danger font-weight-bold text-white">
                                    <div class="row">
                                        <div class="col-md-8">
                                            Compte Rendu RT
                                        </div>
                                        <div class="col-md-4"
                                            <?= ($op->compteRenduRT != null && $op->compteRenduRT != "")  ? "" : "hidden" ?>>
                                            <button class="btn btn-secondary"
                                                onclick="popitup('<?= $op->compteRenduRT ?>', 'RAPPORT FRT')">voir
                                                CR
                                                RT</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body text-center border">
                                    <embed
                                        src="<?= ($op->compteRenduRT == null || $op->compteRenduRT == "") ? "" :  URLROOT . '/public/documents/opportunite/' . ($op->compteRenduRT) ?>"
                                        width="100%" height="700vh" />
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <div class="row mt-3 text-center">
                        <div class="offset-2 col-md-4">
                            <button type="button" rel="tooltip" title="Ajouter un contact"
                                onclick="onClickControlerRT(0)" class="btn btn-danger">
                                Rejeter
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" rel="tooltip" title="Ajouter un contact"
                                onclick="onClickControlerRT(1)" class="btn btn-success ">
                                Valider
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="row mx-0 mt-3">
    <div <?= (($op->devisFais == "1" && $op->delegationSigne == "1" && $op->rapportDelegation != null &&  str_contains(strtolower($op->rapportDelegation), "sign")) ?  "" : "") ?>
        class="col-md-1 col-lg-1 col-sm-2 m-auto">
        <button type="button" rel="tooltip" title="Ajouter un contact"
            onclick="onClickTerminerRT()" class="btn btn-success btn-sm offset-5 w-100">
            Terminer
        </button>
    </div>
</div>
    
