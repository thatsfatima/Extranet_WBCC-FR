<?php
header('Access-Control-Allow-Origin: *');

require_once "../../app/config/config.php";
require_once "../../app/libraries/Database.php";
require_once "../../app/libraries/SMTP.php";
require_once "../../app/libraries/PHPMailer.php";
require_once "../../app/libraries/Role.php";
require_once "../../app/libraries/Utils.php";

$db = new Database();

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'saveSections') {
        @ob_end_clean();
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
    
        try {
            file_put_contents('C:/xampp/htdocs/Extranet_WBCC-FR/debug.log', print_r([
                'raw_post_data' => file_get_contents('php://input'),
                'decoded_data' => json_decode(file_get_contents('php://input'), true),
                'server_response' => 'Script atteint ici'
            ], true), FILE_APPEND);
    
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée', 405);
            }
    
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Erreur de décodage JSON : ' . json_last_error_msg());
            }
    
            if (empty($data['idSommaire']) || !is_array($data['sections'])) {
                throw new Exception('Données invalides ou manquantes');
            }
    
            $idSommaire = $data['idSommaire'];
            $sections = $data['sections'];
    
            // Appel de la fonction pour enregistrer les sections
            $response = linkTo("GestionOP", "saveSections", $idSommaire, $sections);
            echo json_encode([
                'success' => true,
                'message' => 'Sections enregistrées avec succès',
                'data' => $response
            ]);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    if ($action == 'getRVExp') {
        $tab = [];
        $tabToday = [];
        $tabPasse = [];
        $tabFutur = [];
        $today = new DateTime(date("Y-m-d"));
        $db->query("SELECT * FROM wbcc_rendez_vous r, wbcc_opportunity o, wbcc_contact, wbcc_appartement a, wbcc_immeuble i WHERE o.status = 'Open' AND typeRV='EXPERTISEP' AND r.idOpportunityF=o.idOpportunity AND idContactGuidF=numeroContact  AND a.idImmeubleF=i.idImmeuble AND r.idAppExtra=a.idApp  GROUP BY o.idOpportunity");
        $data = $db->resultSet();
        foreach ($data as $key => $rv) {
            $dateRV = new DateTime(date("Y-m-d", strtotime(explode(' ', $rv->dateRV)[0])));
            if ($dateRV < $today) {
                $tabPasse[] = $rv;
            } else {
                if ($dateRV > $today) {
                    $tabFutur[] = $rv;
                } else {
                    $tabToday[] = $rv;
                }
            }
        }
        $tab = ["today" => $tabToday, "passe" => $tabPasse, "futur" => $tabFutur];

        echo json_encode($tab);
    }

    //CREATE IMMEUBLE AND LINK
    if ($action == "saveImm") {
        $db->query("SELECT * FROM `wbcc_immeuble_pap` i, wbcc_pap_b2c p, wbcc_opportunity_papb2c_contact op WHERE i.idImmeublePAP = p.idImmeublePAPF AND p.idPAP = op.idPAPF AND adresse LIKE '%193%' AND adresse LIKE '%AVENUE JEAN JAURES%'");
        $datas = $db->resultSet();
        if (sizeof($datas) != 0) {
            foreach ($datas as $key => $op) {
                //CREATE NEW IMMEUBLE
                $numeroImmeuble = 'IMM' . date("dmYHis") . $key . $op->idPAP;
                $search = false;
                $db->query("SELECT * FROM wbcc_immeuble WHERE (nomImmeubleSyndic IS NULL OR nomImmeubleSyndic = '' OR nomImmeubleSyndic = :nomImmeuble) AND adresse = :adresse2 AND codePostal = :cp LIMIT 1");
                $db->bind("nomImmeuble", $op->nomImmeubleSyndic, null);
                $db->bind("adresse2", $op->adresse, null);
                $db->bind("cp", $op->codePostal, null);
                $search  = $db->single();

                $immeuble = $search;
                if ($search) {
                } else {
                    $company = findItemByColumn("wbcc_company", "name", $op->nomDO);
                    $idCompanyF = $company->idCompany;
                    $db->query("INSERT INTO wbcc_immeuble (numeroImmeuble, nomImmeubleSyndic, adresse, codePostal, ville,nomDO, createDate, idDO) VALUES (:numeroImmeuble, :nomImmeuble2, :adresse3, :cp2, :ville2, :nomDO2, :createDate, :idDO)");
                    $db->bind("numeroImmeuble", $numeroImmeuble, null);
                    $db->bind("nomImmeuble2", $op->nomImmeubleSyndic, null);
                    $db->bind("adresse3", $op->adresse, null);
                    $db->bind("cp2", $op->codePostal, null);
                    $db->bind("ville2", $op->ville, null);
                    $db->bind("nomDO2", $op->nomDO, null);
                    $db->bind("idDO", $idCompanyF, null);
                    $db->bind("createDate", $op->createDate, null);

                    if ($db->execute()) {

                        //save IMMEUBLE company
                        $immeuble = ($search) ? $search : findItemByColumn("wbcc_immeuble", "numeroImmeuble", $numeroImmeuble);
                        if ($immeuble) {
                            //UPDATE GUID IMMEUBLE 
                            $db->query("UPDATE wbcc_opportunity SET guidImmeuble=:guidImmeuble, idImmeuble=:idImmeuble WHERE idOpportunity=$op->idOpportunityF");
                            $db->bind("guidImmeuble", $immeuble->numeroImmeuble, null);
                            $db->bind("idImmeuble", $immeuble->idImmeuble, null);
                            $db->execute();
                            $search = false;
                            $db->query("SELECT * FROM wbcc_company_immeuble WHERE  idCompanyF = :idCompany1 AND idImmeubleF = :idImmeuble1 LIMIT 1");
                            $db->bind("idCompany1",  $idCompanyF, null);
                            $db->bind("idImmeuble1", $immeuble->idImmeuble, null);
                            $search  = $db->single();
                            if ($search) {
                            } else {
                                $db->query("INSERT INTO wbcc_company_immeuble (idCompanyF, idImmeubleF) VALUES (:idCompany2, :idImmeuble2)");
                                $db->bind("idCompany2",  $idCompanyF, null);
                                $db->bind("idImmeuble2", $immeuble->idImmeuble, null);
                                if ($db->execute()) {
                                    $db->query("SELECT * FROM wbcc_company_immeuble WHERE  idCompanyF = :idCompany3 AND idImmeubleF = :idImmeuble3 LIMIT 1");
                                    $db->bind("idCompany3",  $idCompanyF, null);
                                    $db->bind("idImmeuble3", $immeuble->idImmeuble, null);
                                    $search  = $db->single();
                                }
                            }

                            //SAVE IMMEUBLE OPPORTUNITY
                            // $search = false;
                            // $db->query("SELECT * FROM wbcc_opportunity_immeuble WHERE  idOpportunityF = :idOpportunityImm AND idImmeubleF = :idImmeubleImm LIMIT 1");
                            // $db->bind("idOpportunityImm",  $op->idOpportunityF, null);
                            // $db->bind("idImmeubleImm", $immeuble->idImmeuble, null);
                            // $search  = $db->single();
                            // if ($search) {
                            // } else
                            {
                                $db->query("UPDATE wbcc_opportunity_immeuble  SET idImmeubleF=:idImmeubleImm2 WHERE idOpportunityF=:idOpportunityImm2 ");
                                $db->bind("idOpportunityImm2",   $op->idOpportunityF, null);
                                $db->bind("idImmeubleImm2", $immeuble->idImmeuble, null);
                                $db->execute();
                            }
                        }
                    }
                }
            }
        }
        echo json_encode("1");
    }

    if ($action == "createActivityAuto") {
        $db->query("SELECT * FROM wbcc_opportunity WHERE status='Open' AND priseEncharge = 1 ");
        $datas = $db->resultSet();
        if (sizeof($datas) != 0) {
            foreach ($datas as $key => $op) {
                $idOP = $op->idOpportunity;
                $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$op->gestionnaire LIMIT 1");
                $ges = $db->single();
                $activity = findActivityByIdOP($idOP, 21);
                if ($activity) {
                    $activity2 = findActivityByIdOP($idOP, 27);
                    if ($activity2) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity2->idActivity");
                        $db->execute();
                    } else {
                        createNewActivity($op->idOpportunity, $op->name, $ges->idUtilisateur, $ges->fullName,  $ges->numeroContact, $op->name . "- Règlement de l'immédiat", "",  date("Y-m-d H:i:s"),  date("Y-m-d H:i:s"), "Tâche à faire", 'False', "0", "27");
                    }
                }
                $activity = findActivityByIdOP($idOP, 22);
                if ($activity) {
                    $activity2 = findActivityByIdOP($idOP, 37);
                    if ($activity2) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity2->idActivity");
                        $db->execute();
                    } else {
                        createNewActivity($op->idOpportunity, $op->name,  $ges->idUtilisateur, $ges->fullName,  $ges->numeroContact, $op->name . "- Règlement du différé", "",  date("Y-m-d H:i:s"),  date("Y-m-d H:i:s"), "Tâche à faire", 'False', "0", "37");
                    }
                }
            }
        }
    }

    if ($action == "correctSignaturePAP") {
        $db->query("SELECT *, o.signature as signOP, p.signature as signPAP FROM `wbcc_opportunity` o, wbcc_opportunity_papb2c_contact op, wbcc_pap_b2c p WHERE o.idOpportunity = op.idOpportunityF AND op.idPAPF = p.idPAP AND (o.signature != '' OR o.signature IS NOT NULL)");
        $rvs = $db->resultSet();
        foreach ($rvs as $key => $rv) {
            //COPIE SIGNATURE
            $idOppF = $rv->idOpportunity;
            $sign = $rv->signPAP;
            $signDeleg =  "signDeleg_" . "$idOppF.png";
            if ($sign != null && $sign != "" && file_exists("../documents/pap/manuscrite/$sign") && !file_exists("../documents/delegations/signatures/$signDeleg")) {
                copy("../documents/pap/manuscrite/$sign",  "../documents/delegations/signatures/$signDeleg");
                $db->query("UPDATE wbcc_opportunity SET signature=:signature WHERE idOpportunity=$idOppF");
                $db->bind("signature", $signDeleg, null);
                // $db->bind("dateSignature", $rv->dateVisite, null);
                $db->execute();
            }
        }
        echo json_encode(sizeof($rvs));
    }

    if ($action == "findRVRT") {
        $idUser =  $_GET['idUser'];
        $idOP =  $_GET['idOP'];

        $db->query("SELECT * FROM wbcc_opportunity o, wbcc_rendez_vous r, wbcc_appartement_contact ac, wbcc_contact c, wbcc_appartement a, wbcc_immeuble i WHERE r.idOpportunityF=$idOP AND o.idOpportunity = r.idOpportunityF AND r.idAppConF=ac.idAppCon AND ac.idContactF = c.idContact AND 
        ac.idAppartementF = a.idApp AND a.idImmeubleF = i.idImmeuble AND o.status = 'Open'  AND r.idExpertF=$idUser GROUP BY r.idOpportunityF ORDER BY r.idRV DESC LIMIT 1");

        $rv = $db->single();
        if ($rv) {
            $db->query("SELECT  * FROM wbcc_releve_technique WHERE idOpportunityF=$idOP LIMIT 1");
            $rt = $db->single();
            $rvComplet = [];
            if ($rt) {
                $rvComplet = ["rv" => $rv, "rt" => $rt,  "etatRV" => $rv->etatRV];
                echo json_encode($rvComplet);
            } else {
                echo json_encode("-1");
            }
        } else {
            echo json_encode("0");
        }
    }

    if ($action == 'updateFaienceMurale') {
        $db->query("SELECT * FROM wbcc_rt_piece_support");
        $ops = $db->resultSet();
        foreach ($ops as $key => $o) {
            $db->query("SELECT * FROM wbcc_rt_revetement WHERE idRtPieceSupportF =$o->idRTPieceSupport ");
            $revs = $db->resultSet();
            if (sizeof($revs) == 1) {
                $rev = $revs[0];

                $surfaceRev = $o->largeurSupport != null && $o->largeurSupport != "" && $o->longueurSupport != null && $o->longueurSupport != "" ? round(($o->largeurSupport * $o->longueurSupport), 2)  : 0;
                $surfaceOuv = 0;
                $surfaceATraiter = 0;

                if ($o->siOuverture == "1") {
                    //GET OUVERTURES SUPPORT TO OUVERTURES REV
                    $db->query("SELECT * FROM wbcc_rt_ouverture WHERE idRtPieceSupportF =$o->idRTPieceSupport ");
                    $ouvs = $db->resultSet();
                    foreach ($ouvs as $key2 => $ouverture) {
                        $surfaceOuv += $ouverture->largeurOuverture != null && $ouverture->largeurOuverture != "" && $ouverture->longueurOuverture != null && $ouverture->longueurOuverture != "" ? round(($ouverture->largeurOuverture * $ouverture->longueurOuverture), 2)  : 0;
                        $ouv = false;
                        $ouv = findItemByColumn("wbcc_ouverture_bordereau", "nomOuvertureB", $ouverture->nomOuverture);
                        $numeroRtRevetementOuverture = "RevOuv" . date("YmdHis") . $rev->idRtRevetement . $key . $key2;

                        $db->query("INSERT INTO wbcc_rt_revetement_ouverture( numeroRtRevetementOuverture, idRevetementF, idOuvertureF, libelleRevetementOuverture, nomRevetementOuverture, longueurRevetementOuverture, largeurRevetementOuverture, surfaceRevetementOuverture, commentaireRevetementOuverture,  createDate, editDate, idUserF) VALUES (:numeroRtRevetementOuverture, :idRevetementF, :idOuvertureF, :libelleRevetementOuverture, :nomRevetementOuverture, :longueurRevetementOuverture, :largeurRevetementOuverture, :surfaceRevetementOuverture, :commentaireRevetementOuverture,  :createDate, :editDate, :idUserF)");

                        $db->bind("numeroRtRevetementOuverture", $numeroRtRevetementOuverture, null);
                        $db->bind("idRevetementF", $rev->idRtRevetement, null);
                        $db->bind("idOuvertureF", $ouv ? $ouv->idOuvertureB : null, null);
                        $db->bind("libelleRevetementOuverture", $ouverture->libelleOuverture, null);
                        $db->bind("nomRevetementOuverture", $ouverture->nomOuverture, null);
                        $db->bind("longueurRevetementOuverture", $ouverture->longueurOuverture, null);
                        $db->bind("largeurRevetementOuverture", $ouverture->largeurOuverture, null);
                        $db->bind("surfaceRevetementOuverture", $ouverture->surfaceOuverture, null);
                        $db->bind("commentaireRevetementOuverture", "", null);
                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                        $db->bind("editDate", date("Y-m-d H:i:s"), null);
                        $db->bind("idUserF", "5", null);
                        if ($db->execute()) {
                        }
                    }
                    $surfaceATraiter = $surfaceRev - $surfaceOuv;
                }

                $db->query("UPDATE wbcc_rt_revetement SET largeurRevetement='$o->largeurSupport', longueurRevetement='$o->longueurSupport', siOuvertureRevetement=$o->siOuverture, siDeduireRevetement= $o->siDeduire , surfaceRevetement='$surfaceRev', surfaceOuvertureRevetement='$surfaceOuv', surfaceATraiterRevetement='$surfaceATraiter' WHERE idRtRevetement = $rev->idRtRevetement ");
                $db->execute();
            }
            // foreach ($revs as $key1 => $rev) {
            //     if ($rev->libelleRevetement ==  "FAIENCE") {
            //         $faience = $rev;
            //     }
            // }

            // if ($rev) {
            //     $db->query("UPDATE wbcc_rt_revetement SET largeurRevetement='$o->largeurOuverture', longueurRevetement='$o->longueurOuverture',  surfaceRevetement='$o->surfaceOuverture', surfaceATraiterRevetement='$o->surfaceOuverture', siATraiterRevetement = 0 WHERE idRtRevetement = $rev->idRtRevetement ");
            //     $db->execute();
            // } else {
            //     $numeroRtRevetement = "Revet" . date("YmdHis") . $o->idOpportunityF . $key;
            //     $db->query("INSERT INTO wbcc_rt_revetement(numeroRtRevetement, idRevetementF, idRtPieceSupportF, nomRevetement, libelleRevetement, largeurRevetement, longueurRevetement, surfaceRevetement, commentaireRevetement, surfaceOuvertureRevetement, surfaceATraiterRevetement, siATraiterRevetement, siOuvertureRevetement, siDeduireRevetement,   createDate, editDate, idUserF) VALUES(:numeroRtRevetement, :idRevetementF, :idRtPieceSupportF, :nomRevetement, :libelleRevetement, :largeurRevetement, :longueurRevetement, :surfaceRevetement, :commentaireRevetement, :surfaceOuvertureRevetement, :surfaceATraiterRevetement, :siATraiterRevetement, :siOuvertureRevetement, :siDeduireRevetement, :createDate, :editDate, :idUserF)");
            //     $db->bind("numeroRtRevetement", $numeroRtRevetement, null);
            //     $db->bind("idRevetementF", "8", null);
            //     $db->bind("idRtPieceSupportF", $o->idRTPieceSupport, null);
            //     $db->bind("nomRevetement", "", null);
            //     $db->bind("libelleRevetement", "", null);
            //     $db->bind("largeurRevetement", $o->largeurOuverture, null);
            //     $db->bind("longueurRevetement", $o->longueurOuverture, null);
            //     $db->bind("surfaceRevetement", $o->surfaceOuverture, null);
            //     $db->bind("commentaireRevetement", "", null);
            //     $db->bind("surfaceOuvertureRevetement", "", null);
            //     $db->bind("surfaceATraiterRevetement", $o->surfaceOuverture, null);
            //     $db->bind("siATraiterRevetement",  0, null);
            //     $db->bind("siOuvertureRevetement", 0, null);
            //     $db->bind("siDeduireRevetement", 0, null);
            //     $db->bind("createDate", date("Y-m-d H:i:s"), null);
            //     $db->bind("editDate", date("Y-m-d H:i:s"), null);
            //     $db->bind("idUserF", "5", null);
            //     if ($db->execute()) {
            //     }
            // }
        }

        echo json_encode("1");
    }

    if ($action == "correctRV") {
        $db->query("SELECT * FROM wbcc_opportunity o, wbcc_rendez_vous r WHERE r.idOpportunityF = o.idOpportunity AND r.idAppConF IS NULL AND r.idAppExtra IS NOT NULL");

        $rvs = $db->resultSet();
        foreach ($rvs as $key => $rv) {
            $db->query("SELECT * FROM wbcc_opportunity_appartement WHERE idOpportunityF =:idOpportunity AND idAppartementF =:idAppartement");
            $db->bind("idOpportunity", $rv->idOpportunityF, null);
            $db->bind("idAppartement", $rv->idAppExtra, null);
            if ($db->single() == false) {
                $db->query("INSERT INTO wbcc_opportunity_appartement (  idAppartementF, idOpportunityF) VALUES (  :idAppartementF, :idOpportunityF)");
                $db->bind("idAppartementF", $rv->idAppExtra, null);
                $db->bind("idOpportunityF", $rv->idOpportunityF, null);

                if ($db->execute()) {
                    $insert = true;
                } else {
                    $insert = false;
                }
            }
        }
        echo json_encode(sizeof($rvs));
    }

    if ($action == "correctRT") {
        $db->query("SELECT * FROM `wbcc_rt_piece` WHERE photosPiece like '%;%' AND (commentsPhotosPiece = '' OR commentsPhotosPiece is null)");
        $rts = $db->resultSet();
        foreach ($rts as $key => $rt) {
            // $db->query("SELECT * FROM wbcc_rt_piece WHERE idRTF = $rt->idRT");
            // $pieces = $db->resultSet();
            $lib = "";
            $photos = explode(";", $rt->photosPiece);
            foreach ($photos as $key2 => $p) {
                $lib .= ($key2 == 0) ? "" : "}" . "";
            }
            // $nbPieces = sizeof($pieces);
            $db->query("UPDATE wbcc_rt_piece  SET commentsPhotosPiece = :lib  WHERE idRTPiece = $rt->idRTPiece");
            $db->bind("lib", $lib, null);
            if ($db->execute()) {
            }
        }
        // $_POST = json_decode(file_get_contents('php://input'), true);
        // foreach ($_POST as $key => $rt) {
        //     // $idRT = $rt['idRT'];
        //     $idOP = $rt['idOpportunityF'];
        //     $rt1 = findItemByColumn("wbcc_releve_technique", "idOpportunityF", $idOP);
        //     if ($rt1) {
        //         $idRT = $rt1->idRT;
        //         if ($idOP != "2254") {
        //             $db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity= $idOP LIMIT 1");
        //             $op = $db->single();
        //             $tabPiece = isset($rt['pieces']) && $rt['pieces'] != null ? $rt['pieces'] : [];
        //             $nbPieces = sizeof($tabPiece);
        //             if (sizeof($tabPiece) != 0) {
        //                 /**** DELETE PIECE SUPPORT AND REVETEMENT *****/
        //                 $db->query("SELECT * FROM wbcc_rt_piece WHERE idRTF= $idRT");
        //                 $listToDelete = $db->resultSet();

        //                 foreach ($listToDelete as $key => $pieceDel) {

        //                     $db->query("SELECT * FROM wbcc_rt_piece_support WHERE idRTPieceF=$pieceDel->idRTPiece");
        //                     $listSupToDelete = $db->resultSet();

        //                     foreach ($listSupToDelete as $key2 => $supDel) {

        //                         $db->query("SELECT * FROM wbcc_rt_revetement WHERE idRtPieceSupportF =$supDel->idRTPieceSupport");
        //                         $listRevToDelete = $db->resultSet();

        //                         $db->query("SELECT * FROM wbcc_rt_ouverture WHERE idRtPieceSupportF =$supDel->idRTPieceSupport");
        //                         $listOuvToDelete = $db->resultSet();

        //                         foreach ($listRevToDelete as $key3 => $revDel) {

        //                             $db->query("DELETE FROM wbcc_rt_revetement WHERE idRtRevetement = $revDel->idRtRevetement");
        //                             $db->execute();
        //                         }

        //                         foreach ($listOuvToDelete as $key3 => $ouvDel) {

        //                             $db->query("DELETE FROM wbcc_rt_ouverture WHERE idRtOuverture = $ouvDel->idRtOuverture");
        //                             $db->execute();
        //                         }

        //                         $db->query("DELETE FROM wbcc_rt_piece_support WHERE idRTPieceSupport = $supDel->idRTPieceSupport");
        //                         $db->execute();
        //                     }

        //                     $db->query("DELETE FROM wbcc_rt_piece WHERE idRTPiece= $pieceDel->idRTPiece");
        //                     $db->execute();
        //                 }

        //                 $editDate = date("Y-m-d H:i:s");
        //                 //SAVE PIECE
        //                 $libPieces = "";
        //                 foreach ($tabPiece as $key => $piece) {
        //                     $libPieces = ($libPieces == "") ? $piece['nomPiece'] : ";" . $piece['nomPiece'];
        //                     $idPieceF = $piece['idPieceF'];

        //                     $pieceSearch = false;
        //                     /**** CREATE PIECE *****/
        //                     $Lpiece = isset($piece['longueurPiece']) ?  $piece['longueurPiece'] : "";
        //                     $lpiece = isset($piece['largeurPiece']) ?  $piece['largeurPiece'] : "";
        //                     $numeroRTPiece = 'PIECE' . date("dmYHis") .  $idOP . $key;
        //                     $db->query("INSERT INTO wbcc_rt_piece(numeroRTPiece, idRTF, numeroRTF, numeroPieceF, idPieceF, nomPiece, libellePiece, commentairePiece, photosPiece, commentsPhotosPiece, videosPiece, commentsVideosPiece, nbMurs, nbMursSinistres, nbMursNonSinistres, etatRtPiece, createDate, editDate, idUserF, longueurPiece, largeurPiece, surfacePiece, commentaireSupport, commentaireMetrePiece) VALUES(:numeroRTPiece, :idRTF, :numeroRTF, :numeroPieceF, :idPieceF, :nomPiece, :libellePiece, :commentairePiece, :photosPiece, :commentsPhotosPiece, :videosPiece, :commentsVideosPiece, :nbMurs, :nbMursSinistres, :nbMursNonSinistres, :etatRtPiece, :createDate, :editDate, :idUserF, :longueurPiece, :largeurPiece, :surfacePiece, :commentaireSupport, :commentaireMetrePiece)");
        //                     $db->bind("numeroRTPiece", $numeroRTPiece, null);
        //                     $db->bind("createDate", $editDate, null);
        //                     $db->bind("idUserF", $piece['idUserF'], null);
        //                     $db->bind("idRTF", $idRT, null);
        //                     $db->bind("numeroRTF",  $rt['numeroRTF'], null);
        //                     $db->bind("numeroPieceF", "", null);
        //                     $db->bind("idPieceF", $idPieceF, null);
        //                     $db->bind("nomPiece",  $piece['nomPiece'], null);
        //                     $db->bind("libellePiece", $piece['libellePiece'], null);
        //                     $db->bind("commentairePiece",  $piece['commentairePiece'], null);
        //                     $db->bind("commentaireMetrePiece",  $piece['commentaireMetrePiece'], null);
        //                     $db->bind("commentaireSupport",  $piece['commentaireSupport'], null);
        //                     $db->bind("photosPiece", $piece['photosPiece'], null);
        //                     $db->bind("commentsPhotosPiece",  $piece['commentsPhotosPiece'], null);
        //                     $db->bind("videosPiece",  $piece['videosPiece'], null);
        //                     $db->bind("commentsVideosPiece",  $piece['commentsVideosPiece'], null);
        //                     $db->bind("nbMurs", $piece['nbMurs'], null);
        //                     $db->bind("nbMursSinistres", $piece['nbMursSinistres'], null);
        //                     $db->bind("nbMursNonSinistres", $piece['nbMursNonSinistres'], null);
        //                     $db->bind("etatRtPiece", 1, null);
        //                     $db->bind("editDate", $editDate, null);
        //                     $db->bind("longueurPiece", $Lpiece, null);
        //                     $db->bind("largeurPiece", $lpiece, null);
        //                     $db->bind("surfacePiece", ($Lpiece != "" && $lpiece != "") ? "" : 0, null);

        //                     if ($db->execute()) {
        //                         $pieceSearch = findItemByColumn("wbcc_rt_piece", "numeroRTPiece", $numeroRTPiece);
        //                         $idRTPiece = $pieceSearch->idRTPiece;
        //                         //SAVE SUPPORT
        //                         $supports = $piece['supports'];
        //                         if (sizeof($supports) != 0) {
        //                             foreach ($supports as $key2 => $support) {
        //                                 $idSupportF = $support['idSupportF'];
        //                                 $Lsupport = isset($support['longueurSupport']) ?  $support['longueurSupport'] : "";
        //                                 $lsupport = isset($support['largeurSupport']) ?  $support['largeurSupport'] : "";
        //                                 $Ssupport = ($Lsupport != "" && $lsupport != "") ? "" : "0";
        //                                 $SsupportT = "0"; // A CALCULER SUR OUVERTURE

        //                                 /**** CREATE SUPPORT *****/
        //                                 $numeroRTPieceSupport = 'SUP' . date("dmYHis") . $idOP . $key . $key2;
        //                                 $db->query("INSERT INTO wbcc_rt_piece_support(numeroRTPieceSupport, idRTPieceF, numeroRTPieceF, idSupportF, numeroSupportF, nomSupport, libelleSupport, largeurSupport, longueurSupport, surfaceSupport, photosSupport, commentsPhotosSupport, videosSupport, commentsVideosSupport, commentaireSupport, siOuverture, etatRtPieceSupport, createDate, editDate, idUserF, estSinistre,surfaceSupportAtraiter, siDeduire, commentaireMetreSupport, commentaireOuvertures, tauxHumidite) VALUES(:numeroRTPieceSupport, :idRTPieceF, :numeroRTPieceF, :idSupportF, :numeroSupportF, :nomSupport, :libelleSupport, :largeurSupport, :longueurSupport, :surfaceSupport, :photosSupport, :commentsPhotosSupport, :videosSupport, :commentsVideosSupport, :commentaireSupport, :siOuverture, :etatRtPieceSupport, :createDate, :editDate, :idUserF, :estSinistre,:surfaceSupportAtraiter, :siDeduire, :commentaireMetreSupport, :commentaireOuvertures, :tauxHumidite)");
        //                                 $db->bind("numeroRTPieceSupport", $numeroRTPieceSupport, null);
        //                                 $db->bind("createDate", $editDate, null);
        //                                 $db->bind("idUserF", $support['idUserF'], null);
        //                                 $db->bind("idRTPieceF", $idRTPiece, null);
        //                                 $db->bind("numeroRTPieceF", $pieceSearch->numeroRTPiece, null);
        //                                 $db->bind("idSupportF", $support['idSupportF'], null);
        //                                 $db->bind("numeroSupportF", "", null);
        //                                 $db->bind("nomSupport", $support['nomSupport'], null);
        //                                 $db->bind("libelleSupport", $support['libelleSupport'], null);
        //                                 $db->bind("largeurSupport", $lsupport, null);
        //                                 $db->bind("longueurSupport", $Lsupport, null);
        //                                 $db->bind("surfaceSupport", $Ssupport, null);
        //                                 $db->bind("surfaceSupportAtraiter", $SsupportT, null);
        //                                 $db->bind("photosSupport", $support['photosSupport'], null);
        //                                 $db->bind("commentsPhotosSupport",  $support['commentsPhotosSupport'], null);
        //                                 $db->bind("videosSupport",  $support['videosSupport'], null);
        //                                 $db->bind("commentsVideosSupport",  $support['commentsVideosSupport'], null);
        //                                 $db->bind("commentaireSupport", $support['commentaireSupport'], null);
        //                                 $db->bind("commentaireOuvertures", $support['commentaireOuvertures'], null);
        //                                 $db->bind("commentaireMetreSupport", $support['commentaireMetreSupport'], null);
        //                                 $db->bind("siOuverture", $support['siOuverture'], null);
        //                                 $db->bind("siDeduire", $support['siDeduire'], null);
        //                                 $db->bind("etatRtPieceSupport", $support['etatRtPieceSupport'], null);
        //                                 $db->bind("editDate", $editDate, null);
        //                                 $db->bind("estSinistre",  $support['estSinistre'], null);
        //                                 $db->bind("tauxHumidite",  $support['tauxHumidite'], null);
        //                                 if ($db->execute()) {
        //                                     //ENREGISTREMENT REVETEMENT
        //                                     $RTPieceSupport = findItemByColumn("wbcc_rt_piece_support", "numeroRTPieceSupport", $numeroRTPieceSupport);
        //                                     $idRTPieceSupport = $RTPieceSupport->idRTPieceSupport;
        //                                     $revetements = isset($support['revetements'])  && $support['revetements'] != null ? $support['revetements'] : [];
        //                                     foreach ($revetements as $key3 => $revetement) {
        //                                         // $idRTRevetement = $revetement['idRTRevetement'];
        //                                         $numeroRtRevetement = "Revet" . date("YmdHis") . $idOP . $key . $key2 . $key3;

        //                                         $db->query("INSERT INTO wbcc_rt_revetement(numeroRtRevetement, idRevetementF, idRtPieceSupportF, nomRevetement, libelleRevetement, largeurRevetement, longueurRevetement, commentaireRevetement, createDate, editDate, idUserF) VALUES(:numeroRtRevetement, :idRevetementF, :idRtPieceSupportF, :nomRevetement, :libelleRevetement, :largeurRevetement, :longueurRevetement, :commentaireRevetement, :createDate, :editDate, :idUserF)");
        //                                         $db->bind("numeroRtRevetement", $numeroRtRevetement, null);
        //                                         $db->bind("idRevetementF", $revetement["idRevetementF"], null);
        //                                         $db->bind("idRtPieceSupportF", $idRTPieceSupport, null);
        //                                         $db->bind("nomRevetement", $revetement["nomRevetement"], null);
        //                                         $db->bind("libelleRevetement", $revetement["libelleRevetement"], null);
        //                                         $db->bind("largeurRevetement", $revetement["largeurRevetement"], null);
        //                                         $db->bind("longueurRevetement",  $revetement["longueurRevetement"], null);
        //                                         $db->bind("commentaireRevetement", $revetement["commentaireRevetement"], null);
        //                                         $db->bind("createDate", date("Y-m-d H:i:s"), null);
        //                                         $db->bind("editDate", date("Y-m-d H:i:s"), null);
        //                                         $db->bind("idUserF", $revetement["idUserF"], null);
        //                                         if ($db->execute()) {
        //                                             $ok = 1;
        //                                         } else {
        //                                             $ok = 0;
        //                                         }
        //                                     }
        //                                     //ENREGISTREMENT OUVERTURE
        //                                     $ouvertures = isset($support['listOuvertures']) && $support['listOuvertures'] != null ? $support['listOuvertures'] : [];
        //                                     foreach ($ouvertures as $key3 => $ouverture) {
        //                                         $numeroRtOuverture = "Ouverture" . date("YmdHis") . $idOP . $key . $key2 . $key3;

        //                                         $db->query("INSERT INTO `wbcc_rt_ouverture`(`numeroRtOuverture`, `idRtPieceSupportF`, `numeroRtPieceSupportF`, `nomOuverture`, `libelleOuverture`, `largeurOuverture`, `longueurOuverture`, `surfaceOuverture`, `commentaireOuverture`,  `createDate`, `editDate`, `idUserF`) VALUES(:numeroRtOuverture, :idRtPieceSupportF, :numeroRtPieceSupportF, :nomOuverture, :libelleOuverture, :largeurOuverture,:longueurOuverture, :surfaceOuverture, :commentaireOuverture, :createDate, :editDate, :idUserF)");

        //                                         $db->bind("numeroRtOuverture", $numeroRtOuverture, null);
        //                                         $db->bind("numeroRtPieceSupportF", $numeroRTPieceSupport, null);
        //                                         $db->bind("idRtPieceSupportF", $idRTPieceSupport, null);
        //                                         $db->bind("nomOuverture", $ouverture["nomOuverture"], null);
        //                                         $db->bind("libelleOuverture", $ouverture["libelleOuverture"], null);
        //                                         $db->bind("longueurOuverture", $ouverture["longueurOuverture"], null);
        //                                         $db->bind("largeurOuverture", $ouverture["largeurOuverture"], null);
        //                                         $db->bind("surfaceOuverture", $ouverture["surfaceOuverture"], null);
        //                                         $db->bind("commentaireOuverture", "", null);
        //                                         $db->bind("createDate", date("Y-m-d H:i:s"), null);
        //                                         $db->bind("editDate", date("Y-m-d H:i:s"), null);
        //                                         $db->bind("idUserF",  $ouverture["idUserF"], null);
        //                                         if ($db->execute()) {
        //                                             $ok = 1;
        //                                         } else {
        //                                             $ok = 0;
        //                                         }
        //                                     }
        //                                 } else {
        //                                     $ok = 0;
        //                                 }
        //                             }
        //                         }
        //                     } else {
        //                         $ok = 0;
        //                     }
        //                 }

        //                 //UPDATE RT
        //                 $db->query("UPDATE wbcc_releve_technique SET nombrePiece=:nombrePiece, nombreBiens=:nombreBiens, libelleDommageMateriel=:libelleDommageMateriel, libellePieces=:libellePieces, editDate=:editDate WHERE idRT =:idRT");
        //                 $db->bind("idRT", $idRT, null);
        //                 $db->bind("nombrePiece", $nbPieces, null);
        //                 $db->bind("nombreBiens", "0", null);
        //                 $db->bind("libellePieces", $libPieces, null);
        //                 $db->bind("libelleDommageMateriel",  "", null);
        //                 $db->bind("editDate",  $editDate, null);
        //                 if ($db->execute()) {
        //                 }

        //                 //UPDATE RV
        //                 if (isset($idRV)) {
        //                     $db->query("UPDATE wbcc_rendez_vous SET etatRV=1, editDate=:editDate1 WHERE idRV =:idRV");
        //                     $db->bind("idRV", $idRV, null);
        //                     $db->bind("editDate1",  $editDate, null);
        //                     if ($db->execute()) {
        //                     }
        //                 } else {
        //                     $db->query("UPDATE wbcc_rendez_vous SET etatRV=1, editDate=:editDate1 WHERE idOpportunityF =:idOP");
        //                     $db->bind("idOP", $idOP, null);
        //                     $db->bind("editDate1",  $editDate, null);
        //                     if ($db->execute()) {
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }

        echo json_encode("OK");
    }

    //RECHERCHE DE FUITE
    if ($action == "sendMailDemandeRechercheFuite") {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        //SEND MAIL
        if ($typeAction == "send") {
            $attachments = [];
            $filenames = [];
            $ccs = [];
            $role = new Role();
            if ($to != "") {
                $tabTo = explode(";", $to);
                if ($tabTo[sizeof($tabTo) - 1] == "") {
                    array_pop($tabTo);
                }
                foreach ($tabTo as $key => $value) {
                    $response = $role::mailGestionWithFiles($value, $subject, $bodyMessage, $attachments, $filenames, $ccs);
                }
            }
            //CREATE NOTE
            createNote($idOP, $idAuteur, $auteur, $bodyMessage, $bodyMessage, 1);
        } else {
            //CREATE NOTE
            createNote($idOP, $idAuteur, $auteur, "Recherche de Fuite déjà effectuée par la partie responsable", "Recherche de Fuite déjà effectuée par la partie responsable", 1);
        }

        //CREATE HISTORIQUE
        createHistorique("$subject", $auteur, $idAuteur, $idOP);

        $op = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
        //gestionnaire
        $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$op->gestionnaire LIMIT 1");
        $ges = $db->single();

        //CLOSE ACTIVITY DEMANDE
        $regarding = "Demande de faire recherche de fuite";
        $activity = findActivityByIdOP($idOP, 29);
        if ($activity) {
            $db->query("UPDATE wbcc_activity SET isCleared = 'True', editDate=:editDate, realisedBy=:realisedBy, idRealisedBy =:idRealisedBy, regarding=:regarding  WHERE idActivity = $activity->idActivity");
            $db->bind("editDate", date("Y-m-d H:i:s"), null);
            $db->bind("realisedBy", $auteur, null);
            $db->bind("idRealisedBy", $idAuteur, null);
            $db->bind("regarding", $regarding, null);
            $db->execute();
        } else {
            createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", 29, "", $idAuteur,  $auteur);
        }

        //CREER ACTIVITY FAIRE RECHERCHE DE FUITE
        $regarding = "Faire Recherche de Fuite";
        $activity = findActivityByIdOP($idOP, 30);
        if ($activity) {
            $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
            $db->execute();
        } else {
            createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 30);
        }

        $etape = getEtapeForOP($idOP);
        $db->query("UPDATE wbcc_opportunity SET etapeOp=:etapeOp  WHERE idOpportunity = $idOP");
        $db->bind("etapeOp", $etape, null);
        $db->execute();

        echo json_encode("1");
    }

    if ($action == "sendMailDemanderSignature") {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        //SEND MAIL
        $attachments = [];
        $filenames = [];
        $role = new Role();
        if ($to != "") {
            $tabTo = explode(";", $to);
            if ($tabTo[sizeof($tabTo) - 1] == "") {
                array_pop($tabTo);
            }
            foreach ($tabTo as $key => $value) {
                $response = $role::mailGestionWithFiles($value, $subject, $bodyMessage, $attachments, $filenames);
            }
        }
        //UPDATE RF
        $db->query("UPDATE  wbcc_recherche_fuite  SET demandeSignatureEnvoye=1, dateDemandeSignatureEnvoye=:date WHERE idOpportunityF = $idOpportunity");
        $db->bind("date", date("Y-m-d H:i:s"), null);
        $db->execute();

        //CREATE NOTE
        createNote($idOpportunity, $idAuteur, $auteur, $subject . " \n" . $bodyMessage, $subject . " \n" .  $bodyMessage, 1);

        //CREATE HISTORIQUE
        createHistorique("$subject", $auteur, $idAuteur, $idOpportunity);

        $etape = getEtapeForOP($idOpportunity);
        $db->query("UPDATE wbcc_opportunity SET etapeOp=:etapeOp  WHERE idOpportunity = $idOpportunity");
        $db->bind("etapeOp", $etape, null);
        $db->execute();

        //CREATE COMPTE FOR SALARIE BS
        createCompte($idResponsable, $idGestionnaireAppImm);
        echo json_encode("1");
    }

    if ($action == "saveReparationFuite") {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);

        if ($typeParam == "fin") {
            //UPDATE INFOS JUSTIFICATIF
            $db->query("UPDATE  wbcc_recherche_fuite  SET descriptionTravaux=:descriptionTravaux, dateReparationFuite=:dateReparationFuite, reparateurCause=:auteurReparation, reparationCause=:reparationCause  WHERE idOpportunityF = $idOP");
            $db->bind("reparationCause",  $reparationCause, null);
            $db->bind("auteurReparation",  $auteurReparation, null);
            $db->bind("dateReparationFuite",  $dateReparationFuite, null);
            $db->bind("descriptionTravaux",  $descriptionTravaux, null);
            $db->execute();
        }
        echo json_encode("1");
    }

    if ($action == "sendMailDemandeReparationFuite") {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        //SEND MAIL
        $attachments = [];
        $filenames = [];
        $role = new Role();
        if ($to != "") {
            $tabTo = explode(";", $to);
            if ($tabTo[sizeof($tabTo) - 1] == "") {
                array_pop($tabTo);
            }
            foreach ($tabTo as $key => $value) {
                $response = $role::mailGestionWithFiles($value, $subject, $bodyMessage, $attachments, $filenames);
            }
        }
        //CREATE NOTE
        createNote($idOP, $idAuteur, $auteur, $bodyMessage, $bodyMessage, 1);

        //CREATE HISTORIQUE
        createHistorique("$subject", $auteur, $idAuteur, $idOP);

        $op = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
        //gestionnaire
        $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$op->gestionnaire LIMIT 1");
        $ges = $db->single();

        //REPROGRAMMER ACTIVITY
        $regarding = "Demande de réparation de fuite";
        $activity = findActivityByIdOP($idOP, 33);
        if ($activity) {
            $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
            $db->execute();
        } else {
            createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 33);
        }

        $etape = getEtapeForOP($idOP);
        $db->query("UPDATE wbcc_opportunity SET etapeOp=:etapeOp  WHERE idOpportunity = $idOP");
        $db->bind("etapeOp", $etape, null);
        $db->execute();

        echo json_encode("1");
    }

    if ($action == "sendMailDemandeJustificatif") {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        //SEND MAIL
        $attachments = [];
        $filenames = [];
        $role = new Role();
        if ($to != "") {
            $tabTo = explode(";", $to);
            if ($tabTo[sizeof($tabTo) - 1] == "") {
                array_pop($tabTo);
            }
            foreach ($tabTo as $key => $value) {
                $response = $role::mailGestionWithFiles($value, $subject, $bodyMessage, $attachments, $filenames);
            }
        }
        //UPDATE rf
        $db->query("UPDATE wbcc_recherche_fuite SET  demandeJustificatifEnvoye=:demandeJustificatifEnvoye,dateDemandeJustificatifEnvoye=:dateDemandeJustificatifEnvoye  WHERE idOpportunityF=$idOP");
        $db->bind("demandeJustificatifEnvoye", 1, null);
        $db->bind("dateDemandeJustificatifEnvoye", date("Y-m-d H:i:s"), null);
        $db->execute();

        //CREATE NOTE
        createNote($idOP, $idAuteur, $auteur, $bodyMessage, $bodyMessage, 1);

        //CREATE HISTORIQUE
        createHistorique("$subject", $auteur, $idAuteur, $idOP);

        $op = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
        //gestionnaire
        $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$op->gestionnaire LIMIT 1");
        $ges = $db->single();

        //REPROGRAMMER ACTIVITY
        $regarding = "Recupérer justificatif de réparation de fuite";
        $activity = findActivityByIdOP($idOP, 32);
        if ($activity) {
            $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
            $db->execute();
        } else {
            createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 32);
        }

        $etape = getEtapeForOP($idOP);
        $db->query("UPDATE wbcc_opportunity SET etapeOp=:etapeOp  WHERE idOpportunity = $idOP");
        $db->bind("etapeOp", $etape, null);
        $db->execute();

        //CREATE COMPTE FOR SALARIE BS
        createCompte($idResponsable, $idGestionnaireAppImm);

        echo json_encode("1");
    }

    if ($action == "saveJustificatifReparationFuite") {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);

        // if ($typeParam == "fin") 
        if (isset($repJustificatif) && $repJustificatif == "2") {

            $regarding = "Recupérer Justificatif de Réparation de Fuite";
            //CLOSE OTHER ACTIVITIES
            closeMultipleTachesByOP($idOP, $idAuteur, $auteur,  [32, 24, 29, 30, 34, 35, 36, 44, 33, 42, 46]);

            //create note
            $noteText = "$regarding : Non applicable";
            createNote($idOP, $idAuteur, $auteur, ($noteText), ($noteText), 0, 32);

            //create Historique
            createHistorique("Clôture Activité : $opName - $regarding ", $auteur, $idAuteur, $idOP);
        } else {
            //UPDATE INFOS JUSTIFICATIF
            $db->query("UPDATE  wbcc_recherche_fuite  SET siJustificatif=:siJustificatif, siSignatureJustificatif=:siSignatureJustificatif, documentJustificatif=:documentJustificatif  WHERE idOpportunityF = $idOP");
            $db->bind("siJustificatif",  1, null);
            $db->bind("siSignatureJustificatif",  1, null);
            $db->bind("documentJustificatif",  $nomDoc, null);
            $db->execute();

            //UPDATE OP
            $db->query("UPDATE  wbcc_opportunity  SET 	docJustificatifReparation=:docJustificatifReparation  WHERE idOpportunity = $idOP");
            $db->bind("docJustificatifReparation",  $nomDoc, null);
            $db->execute();

            if ($nomDoc != "") {
                $numeroDoc = 'DOC' . date("dmYHis") . $idAuteur;
                $db->query("SELECT * FROM wbcc_document WHERE urlDocument='$nomDoc' LIMIT 1");
                $document = $db->single();
                if ($document) {
                    $db->query("UPDATE wbcc_document SET editDate = :editDate WHERE idDocument=$document->idDocument ");
                    $db->bind("editDate",  date("Y-m-d H:i:s"), null);
                } else {
                    $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");
                    $db->bind("publie",  1, null);
                    $db->bind("source", "EXTRA", null);
                    $db->bind("numeroDocument", $numeroDoc, null);
                    $db->bind("nomDocument", $nomDoc, null);
                    $db->bind("urlDocument", $nomDoc, null);
                    $db->bind("commentaire", "", null);
                    $db->bind("createDate",  date("Y-m-d H:i:s"), null);
                    $db->bind("guidHistory", null, null);
                    $db->bind("typeFichier", "Adobe Acrobat Document", null);
                    $db->bind("size", null, null);
                    $db->bind("guidUser", $numeroAuteur, null);
                    $db->bind("idUtilisateurF", $idAuteur, null);
                    $db->bind("auteur", $auteur, null);
                }
                if ($db->execute()) {
                    if (!$document) {
                        $db->query("SELECT * FROM wbcc_document WHERE numeroDocument='$numeroDoc' LIMIT 1");
                        $document = $db->single();
                        $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($idOP,$document->idDocument)");
                        $db->execute();
                    }
                }
            }
        }
        echo json_encode("1");
    }

    if ($action == "saveRechercheFuite") {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        $op = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
        //gestionnaire
        $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$op->gestionnaire LIMIT 1");
        $ges = $db->single();

        if (isset($reponse) && $reponse == "3") {
            //CLOSE OTHER ACTIVITIES
            closeMultipleTachesByOP($idOP, $idAuteur, $auteur, [24, 29, 30, 34, 35, 36, 44]);

            //create note
            $noteText = "Générer Constat DDE Non applicable";
            createNote($idOP, $idAuteur, $auteur, ($noteText), ($noteText), 0, 24);

            //create Historique
            createHistorique("Clôture Activité : $op->name - Générer Constat DDE ", $auteur, $idAuteur, $idOP);

            echo json_encode(1);
        } else {
            $causeCoche = "";
            $precisionF = "";
            $precision = "";
            if (in_array("Fuite", $tabCauseCoche)) {
                $precisionF = implode(";", $tabPrecisionCocheF);
                $causeCoche .= ($causeCoche == "" ? "Fuite" : ";Fuite");
                $precision .=  ($precision == "" ? $precisionF : "}$precisionF");
            }

            $precisionD = "";
            if (in_array("Débordement", $tabCauseCoche)) {
                $precisionD = implode(";", $tabPrecisionCocheD);
                $causeCoche .=  ($causeCoche == "" ? "Débordement" : ";Débordement");
                $precision .= ($precision == "" ? $precisionD : "}$precisionD");
            }

            $precisionI = "";
            if (in_array("Infiltration", $tabCauseCoche)) {
                $precisionI = implode(";", $tabPrecisionCocheI);
                $causeCoche .=  ($causeCoche == "" ? "Infiltration" : ";Infiltration");
                $precision .= ($precision == "" ? $precisionI : "}$precisionI");
            }

            $precisionE = "";
            if (in_array("Engorgement", $tabCauseCoche)) {
                $precisionE = implode(";", $tabPrecisionCocheE);
                $causeCoche .=  ($causeCoche == "" ? "Engorgement" : ";Engorgement");
                $precision .= ($precision == "" ? $precisionE : "}$precisionE");
            }
            if ($autreCause != "" && $autreCause != null) {
                $causeCoche .=  $causeCoche != "" ? ";$autreCause" : "$autreCause";
            }

            $db->query("UPDATE wbcc_releve_technique SET  cause=:cause,precisionDegat=:precisionDegat, lieuDegat=:lieuDegat WHERE idOpportunityF=$idOP");
            $db->bind("cause", $causeCoche, null);
            $db->bind("precisionDegat", $precision, null);
            $db->bind("lieuDegat", $origineFuite, null);
            $db->execute();

            $rf = saveRechercheFuite("", $idOP, $opName, (isset($dateRF) && $dateRF != null && $dateRF != "" ? $dateRF : date("Y-m-d H:i:s")), $idAuteurRF, $nomAuteurRF, ($origineFuite == "Chez le voisin" ? "Chez Vous" : $origineFuite), $reparationCause,  $auteurReparation, "en attente", ($etatRF != null && $etatRF != "" ? $etatRF : 0), "EXTERNE", "", $dateReparationFuite, $descriptionTravaux, null, null, ($typeResponsable == "voisin" ? $idResponsable : null), ($typeResponsable == "voisin" ? "1" : "0"), $siAccessible, "attestation", null, $pieceFuyarde, $equipementFuyard, $origineFuiteEquipement, $faireRF, $siDegatVoisin, $siConfierSinistre, "", $siJustificatif, $siSignatureJustificatif, $documentJustificatif);
            //UPDATE RESPONSABLE OP
            if ($idResponsable != "" && $idResponsable != "0" && $idResponsable != null) {
                $contact = findItemByColumn("wbcc_contact", "idContact", $idResponsable);
                $fullname = "$contact->prenomContact $contact->nomContact";
                $db->query("UPDATE `wbcc_opportunity` SET  nomResponsable=:nomResponsable, idResponsableContactF=$idResponsable WHERE idOpportunity=$idOP ");
                $db->bind("nomResponsable", $fullname, null);
                $result = $db->execute();
                $db->query("SELECT * FROM wbcc_contact_opportunity WHERE idOpportunityF = $idOP AND idContactF = $idResponsable LIMIT 1");
                $oppCon = $db->single();
                if (!$oppCon) {
                    $db->query("INSERT INTO wbcc_contact_opportunity (idContactF, idOpportunityF) VALUES ($idResponsable,$idOP)");
                    $db->execute();
                }
            }

            if ($typeParam == "fin") {
                //NOTE FIN GENERATION
                createNote($idOP, $idAuteur, $auteur, "$opName - Document Constat DDE complété", "$opName - Document Constat DDE complété", "1");
                if ($siFichierUpload == "0") {
                    $fileAttestationReparation = file_get_contents(URLROOT . "/public/documents/rechercheFuite/constatDDE.php?idOP=$idOP&modeSignature=sans");
                    $nomDoc = str_replace('"', "", $fileAttestationReparation);
                }
                $db->query("UPDATE wbcc_recherche_fuite SET  isVictimSigned=:isVictimSigned, isResponsableSigned=:isResponsableSigned, isDocumentCompleted=:isDocumentCompleted,signatureDO=:signatureDO, signatureVoisin=:signatureVoisin, idAuteurGenererConstatDDE=:idAuteurGenererConstatDDE,dateGenererConstatDDE=:dateGenererConstatDDE, documentConstatDDE=:documentConstatDDE  WHERE idOpportunityF=$idOP");
                $db->bind("signatureDO", $signatureDO, null);
                $db->bind("signatureVoisin", $signatureVoisin, null);
                $db->bind("isVictimSigned", $isVictimSigned, null);
                $db->bind("isResponsableSigned", $isResponsableSigned, null);
                $db->bind("isDocumentCompleted", 1, null);
                $db->bind("idAuteurGenererConstatDDE", $idAuteur, null);
                $db->bind("dateGenererConstatDDE", date("Y-m-d H:i:s"), null);
                $db->bind("documentConstatDDE", $nomDoc, null);
                $db->execute();
                //UPDATE DDE OP
                $db->query("UPDATE wbcc_opportunity SET  idAuteurGenererConstatDDE=:idAuteurGenererConstatDDE,dateGenererConstatDDE=:dateGenererConstatDDE, documentConstatDDE=:documentConstatDDE  WHERE idOpportunity=$idOP");
                $db->bind("idAuteurGenererConstatDDE", $idAuteur, null);
                $db->bind("dateGenererConstatDDE", date("Y-m-d H:i:s"), null);
                $db->bind("documentConstatDDE", $nomDoc, null);
                $db->execute();
                if ($isResponsableSigned == "1" && $isVictimSigned == "1") {
                    //CLOSE ACTIVITY CONSTAT DDE
                    $res = closeActivityByOPAndRegarding($idOP, $opName, "$opName - Faire Constat DDE", "", $auteur, "", $idAuteur, "", "Faire Constat DDE", "Faire Constat DDE", "", "", "", "24",  '');
                }
                //Close ACTIVITY GARDIEN / VOISIN
                $activity = findActivityByIdOP($idOP, 34);
                if ($activity) {
                    closeActivityByOPAndRegarding($idOP, $opName, "$opName - Récupération Coordonnées Gardien", "", $auteur, "", $idAuteur, "", "Récupération Coordonnées Gardien", "Récupération Coordonnées Gardien", "", "", "", "34",  '');
                }

                $activity = findActivityByIdOP($idOP, 35);
                if ($activity) {
                    closeActivityByOPAndRegarding($idOP, $opName, "$opName - Récupération Coordonnées Voisin", "", $auteur, "", $idAuteur, "", "Récupération Coordonnées Voisin", "Récupération Coordonnées Voisin", "", "", "", "35",  '');
                }


                $etape = "";
                if ($reparationCause == "Oui") {
                    //FAIRE RF TRUE
                    $regarding = "Faire Recherche de Fuite";
                    $activity = findActivityByIdOP($idOP, 30);
                    if ($activity) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'True', editDate=:editDate, realisedBy=:realisedBy, idRealisedBy =:idRealisedBy WHERE idActivity = $activity->idActivity");
                        $db->bind("editDate", date("Y-m-d H:i:s"), null);
                        $db->bind("realisedBy", $auteur, null);
                        $db->bind("idRealisedBy", $idAuteur, null);
                        $db->execute();
                        $db->execute();
                    } else {
                        createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", 30);
                    }

                    //DEMANDE DE REPARATION TRUE
                    $regarding = "Réparation de Fuite";
                    $activity = findActivityByIdOP($idOP, 33);
                    if ($activity) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'True',editDate=:editDate, realisedBy=:realisedBy, idRealisedBy =:idRealisedBy WHERE idActivity = $activity->idActivity");
                        $db->bind("editDate", date("Y-m-d H:i:s"), null);
                        $db->bind("realisedBy", $auteur, null);
                        $db->bind("idRealisedBy", $idAuteur, null);
                        $db->execute();
                        $db->execute();
                    } else {
                        createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", 33);
                    }

                    //JUSTIFICATIF FALSE
                    $regarding = "Demande de Justificatif de Réparation de Fuite";
                    $activity = findActivityByIdOP($idOP, 32);
                    if ($activity) {
                        // $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                        // $db->execute();
                    } else {
                        createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 32);
                    }

                    $etape = "En Attente de Justificatif de Réparation de Fuite";
                } else {
                    if ($etatRF == "1") {
                        //FAIRE RF TRUE
                        $regarding = "Faire Recherche Fuite";
                        $activity = findActivityByIdOP($idOP, 30);
                        if ($activity) {
                            $db->query("UPDATE wbcc_activity SET isCleared = 'True', editDate=:editDate, realisedBy=:realisedBy, idRealisedBy =:idRealisedBy WHERE idActivity = $activity->idActivity");
                            $db->bind("editDate", date("Y-m-d H:i:s"), null);
                            $db->bind("realisedBy", $auteur, null);
                            $db->bind("idRealisedBy", $idAuteur, null);
                            $db->execute();
                            $db->execute();
                        } else {
                            createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", 30);
                        }
                    } else {
                        $etape = "En Attente de Recherche de Fuite";
                        if ($origineFuite == "Chez Vous" || $origineFuite == "Partie Commune Interne" || $origineFuite == "Je ne sais pas") {
                            //PROGRAMMER RF
                            $regarding = "Programmer Recherche Fuite";
                            $activity = findActivityByIdOP($idOP, 29);
                            if ($activity) {
                                //    $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                                //    $db->execute();
                            } else {
                                createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 29);
                            }
                        } else {
                            $regarding = "Faire Recherche Fuite";
                            $activity = findActivityByIdOP($idOP, 30);
                            if ($activity) {
                                // $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                                // $db->execute();
                            } else {
                                createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 30);
                            }
                        }
                    }
                    //DEMANDE DE REPARATION FALSE
                    $regarding = "Réparation de Fuite";
                    $activity = findActivityByIdOP($idOP, 33);
                    if ($activity) {
                        // $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                        // $db->execute();
                    } else {
                        createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 33);
                    }
                }
                //UPDATE ETAPE
                $db->query("UPDATE wbcc_recherche_fuite SET  etape=:etape  WHERE idOpportunityF=$idOP");
                $db->bind("etape", $etape, null);
                $db->execute();

                //CREATE DOCUMENT
                if ($nomDoc != "") {
                    $numeroDoc = 'DOC' . date("dmYHis") . $idAuteur;
                    $db->query("SELECT * FROM wbcc_document WHERE urlDocument='$nomDoc' LIMIT 1");
                    $document = $db->single();
                    if ($document) {
                        $db->query("UPDATE wbcc_document SET editDate = :editDate WHERE idDocument=$document->idDocument ");
                        $db->bind("editDate",  date("Y-m-d H:i:s"), null);
                    } else {
                        $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");
                        $db->bind("publie",  1, null);
                        $db->bind("source", "EXTRA", null);
                        $db->bind("numeroDocument", $numeroDoc, null);
                        $db->bind("nomDocument", $nomDoc, null);
                        $db->bind("urlDocument", $nomDoc, null);
                        $db->bind("commentaire", "", null);
                        $db->bind("createDate",  date("Y-m-d H:i:s"), null);
                        $db->bind("guidHistory", null, null);
                        $db->bind("typeFichier", "Adobe Acrobat Document", null);
                        $db->bind("size", null, null);
                        $db->bind("guidUser", $numeroAuteur, null);
                        $db->bind("idUtilisateurF", $idAuteur, null);
                        $db->bind("auteur", $auteur, null);
                    }
                    if ($db->execute()) {
                        if (!$document) {
                            $db->query("SELECT * FROM wbcc_document WHERE numeroDocument='$numeroDoc' LIMIT 1");
                            $document = $db->single();
                            $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($idOP,$document->idDocument)");
                            $db->execute();
                        }
                    }
                    // //DELETE OLD DELEGATION
                    // $db->query("SELECT * FROM wbcc_document,wbcc_opportunity_document WHERE idDocument=idDocumentF AND idOpportunityF=$idOP AND numeroDocument!='$numeroDoc'");
                    // $docs = $db->resultSet();
                    // if (sizeof($docs) != 0) {
                    //     foreach ($docs as $key => $docu) {
                    //         if (strpos(strtolower($docu->nomDocument), "CONSTAT_DDE") != false && $docu->isDeleted == 0) {
                    //             //INSERT FILIGRANE
                    //             $url = URLROOT . "/public/json/insertFiligrane.php";
                    //             $query_array = array(
                    //                 'idOp' =>  $idOP,
                    //                 'nomDocument' => $docu->urlDocument,
                    //                 'text' => "Constat annulé et remplacé par celui du " . date("d/m/Y H:i"),
                    //                 'format' => 'json'
                    //             );

                    //             $query = http_build_query($query_array);
                    //             $file = file_get_contents($url . '?' . $query);
                    //             // $file = file_get_contents(URLROOT . "/public/json/insertFiligrane.php?idOp=" . $idOp . "&nomDocument=" . $docu->urlDocument . "&text=Délégation annulée et remplacée par celle du " . date("d/m/Y H:i"));
                    //             //UPDATE DOCUMENT
                    //             $db->query("UPDATE wbcc_document SET isDeleted=1 WHERE idDocument=$docu->idDocument");
                    //             $db->execute();
                    //         }
                    //     }
                    // }
                    //SAVE HISTORIQUE
                    createHistorique("Génération de Constat DDE : $nomDoc", $auteur, $idAuteur, $idOP);
                }

                //SEND MAIL TO RESPONSABLE FOR BAILLEUR SOCIAL
                if (str_contains(strtolower($origineFuite), "commune") && (str_contains(strtolower($sousCategorieDO), "bailleur social") || str_contains(strtolower($statutDO), "bailleur social")) && $isVictimSigned == "1") {
                }
            } else {
                if ($typeParam == "3") {
                    $fileAttestationReparation = file_get_contents(URLROOT . "/public/documents/rechercheFuite/constatDDE.php?idOP=$idOP&modeSignature=sans");
                    $fileAttestationReparation = str_replace('"', "", $fileAttestationReparation);
                }
            }
            echo json_encode("1");
        }
    }

    /*** DEBUT MOBILE  ***/
    if ($action == "generateAttestation") {
        //GENERER ATTESTATION SUR LHONNEUR
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        if (isset($source) && $source == "RYM") {
            if (isset($typeDocument) && $typeDocument == "bailleur") {
                $fileAttestationReparation = file_get_contents(URLROOT . "/public/documents/rechercheFuite/attestationReparationFuiteBS.php?idOP=$idOP&modeSignature=sans");
            } else {
                $fileAttestationReparation = file_get_contents(URLROOT . "/public/documents/rechercheFuite/attestationReparationFuite.php?idOP=$idOP&modeSignature=sans");
            }
            $fileAttestationReparation = str_replace('"', "", $fileAttestationReparation);
            $siSignatureJustificatif = str_contains($fileAttestationReparation, "Signed") ? 1 : 0;
            //UPDATE INFOS JUSTIFICATIF
            $db->query("UPDATE  wbcc_recherche_fuite  SET siJustificatif=:siJustificatif, siSignatureJustificatif=:siSignatureJustificatif, documentJustificatif=:documentJustificatif, dateJustificatif=:dateJustificatif, etape=:etape   WHERE idOpportunityF = $idOP");
            $db->bind("siJustificatif",  1, null);
            $db->bind("siSignatureJustificatif",  $siSignatureJustificatif, null);
            $db->bind("documentJustificatif",  $fileAttestationReparation, null);
            $db->bind("etape", "Justificatif de Réparation de Fuite reçu", null);
            $db->bind("dateJustificatif",  date("Y-m-d H:i:s"), null);
            $db->execute();

            //LINK OP DOCUMENT
            $numeroDoc = 'DOC' . date("dmYHis") . $idAuteur;
            $db->query("SELECT * FROM wbcc_document WHERE urlDocument='$fileAttestationReparation' LIMIT 1");
            $document = $db->single();
            if ($document) {
                $db->query("UPDATE wbcc_document SET editDate = :editDate WHERE idDocument=$document->idDocument ");
                $db->bind("editDate",  date("Y-m-d H:i:s"), null);
            } else {
                $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");
                $db->bind("publie",  1, null);
                $db->bind("source", "EXTRA", null);
                $db->bind("numeroDocument", $numeroDoc, null);
                $db->bind("nomDocument", $fileAttestationReparation, null);
                $db->bind("urlDocument", $fileAttestationReparation, null);
                $db->bind("commentaire", "", null);
                $db->bind("createDate",  date("Y-m-d H:i:s"), null);
                $db->bind("guidHistory", null, null);
                $db->bind("typeFichier", "Adobe Acrobat Document", null);
                $db->bind("size", null, null);
                $db->bind("guidUser", $numeroAuteur, null);
                $db->bind("idUtilisateurF", $idAuteur, null);
                $db->bind("auteur", $auteur, null);
            }
            if ($db->execute()) {
                if (!$document) {
                    $db->query("SELECT * FROM wbcc_document WHERE numeroDocument='$numeroDoc' LIMIT 1");
                    $document = $db->single();
                    $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($idOP,$document->idDocument)");
                    $db->execute();
                }
            }

            //SAVE HISTORIQUE
            createHistorique("Génération de Attestation de réparation : $fileAttestationReparation", $auteur, $idAuteur, $idOP);
        } else {
            $rf = saveRechercheFuite("", $idOP, $numeroOP, (isset($dateRF) && $dateRF != null && $dateRF != "" ? $dateRF : date("Y-m-d H:i:s")), $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, $reparationCause,  $reparateurDegat, "Justificatif de Réparation de Fuite reçu", 1, "EXTERNE", $auteurRF, $dateReparationFuite, $descriptionReparation, $idArtisan, null, $idVoisin, $chezVoisin, null, "attestation", null, $pieceFuyarde, $equipementFuyard, $origineFuiteEquipement, $faireRF, $siDegatVoisin, $siConfierSinistre, "", 0, 0, "");
            $fileAttestationReparation = file_get_contents(URLROOT . "/public/documents/rechercheFuite/attestationReparationFuite.php?idOP=$idOP&modeSignature=sans");
            $fileAttestationReparation = str_replace('"', "", $fileAttestationReparation);
        }


        echo json_encode($fileAttestationReparation);
    }

    if ($action == 'saveAnalyseRechercheFuite') {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        //SAVE SIGNATURE
        $signatureVictim =    $signatureDDEDO;
        $signatureResponsable =  $oldSignatureDDE;
        $rf2 = findItemByColumn("wbcc_recherche_fuite", "idOpportunityF", $idOP);
        if ($signatureDDE != null && $signatureDDE != "" && !str_starts_with($signatureDDE, "Sign")) {
            if ($rf2) {
                if ($rf2->chezVoisin == "1") {
                    $signatureResponsable =  $signatureDDE;
                } else {
                    $signatureVictim =    $signatureDDE;
                    if ($provenanceFuite == "Chez Vous") {
                        $signatureResponsable =  $signatureDDE;
                    }
                }
            } else {
                $signatureVictim =    $signatureDDE;
                if ($provenanceFuite == "Chez Vous") {
                    $signatureResponsable =  $signatureDDE;
                }
            }
        } else {
            if ($rf2) {
                if ($rf2->chezVoisin == "1") {
                } else {
                    if ($provenanceFuite != "Chez Vous") {
                        $signatureResponsable =  "";
                    }
                }
            } else {
                if ($provenanceFuite !== "Chez Vous") {
                    $signatureResponsable =  "";
                }
            }
        }

        $idUser = $user['idUtilisateur'];
        $voisin = false;
        if ($rf2 && $rf2->chezVoisin == "1") {
            $voisin = saveInfosVoisin($idVoisin, $civiliteVoisin, $prenomVoisin, $nomVoisin, $telVoisin, $emailVoisin, $adresse, $codePostal, $ville, $idUser, $idOP, $idImmeuble, $idAppVoisin, $lotVoisin, $batimentVoisin, $etageVoisin, $porteVoisin, $idCompagnieAssuranceF, $compagnieAssurance, $numeroPolice);
        }

        $op = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
        $opName = $op->name;
        //update provenance fuite
        $db->query("UPDATE wbcc_opportunity SET provenanceFuite=:provenanceFuite WHERE idOpportunity = $idOP");
        $db->bind("provenanceFuite", $provenanceFuite, null);
        $db->execute();
        //gestionnaire
        $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$op->gestionnaire LIMIT 1");
        $ges = $db->single();
        $rf = false;

        if ($provenanceFuite == "Je ne sais pas") {
            //"Créer Tâche Faire Recherche de fuite VIA REPA";
            $regarding = "Programmer Recherche Fuite";
            $activity = findActivityByIdOP($idOP, 29);
            if ($activity) {
                $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                $db->execute();
            } else {
                createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 29);
            }
            $rf = saveRechercheFuite("", $idOP, $numeroOP, (isset($dateRF) && $dateRF != null && $dateRF != "" ? $dateRF : date("Y-m-d H:i:s")), $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, null,  null, "En attente de progralmmation de RF", 0, "EXTERNE", null, null, null, null, null, $idVoisin, $chezVoisin, null, $signatureVictim, $signatureResponsable, null, null, null, $faireRF, $siDegatVoisin, $siConfierSinistre, "", 0, 0, "");
            echo json_encode($rf);
            die;
        } else {
            if ($provenanceFuite == "Chez le voisin") {
                $regarding = "Récupération Coordonnées Voisin";
                $activity = findActivityByIdOP($idOP, 35);
                $voisin = false;
                if (($prenomVoisin != "" || $nomVoisin != "" || $telVoisin != "" || $emailVoisin != "")) {
                    //CLOSE TACHE RECUPERATION INFO VOISIN
                    if ($activity) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'True' WHERE idActivity = $activity->idActivity");
                        $db->execute();
                    } else {
                        createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", 35);
                    }
                } else {
                    $prenomVoisin = "Voisin $opName";
                    if ($activity) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                        $db->execute();
                    } else {
                        createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 35);
                    }
                }
                $voisin = saveInfosVoisin($idVoisin, $civiliteVoisin, $prenomVoisin, $nomVoisin, $telVoisin, $emailVoisin, $adresse, $codePostal, $ville, $idUser, $idOP, $idImmeuble, $idAppVoisin, $lotVoisin, $batimentVoisin, $etageVoisin, $porteVoisin, $idCompagnieAssuranceF, $compagnieAssurance, $numeroPolice);

                //SAVE VICTIME - RESPONSABLE FUITE
                $nomResponsable = "Voisin";
                $idResponsable = null;
                if ($voisin) {
                    $nomResponsable = $voisin->fullName;
                    $idResponsable = $voisin->idContact;
                }
                $idColumnVictim = "";
                $nomVictime = "";
                $idVictime = "";
                if ($op->typeSinistre == "Partie commune exclusive") {
                    $nomVictime =  $op->nomDO;
                    $idVictime =  $op->idDOF;
                    $idColumnVictim = "idVictimeCompanyF";
                } else {
                    $nomVictime =  $op->contactClient;
                    $idVictime =  $op->idContactClient;
                    $idColumnVictim = "idVictimeContactF";
                }
                $db->query("UPDATE wbcc_opportunity SET nomVictime=:nomVictime, $idColumnVictim=:idVictime,nomResponsable=:nomResponsable, idResponsableContactF=:idResponsable   WHERE idOpportunity = $idOP");
                $db->bind("nomVictime", $nomVictime, null);
                $db->bind("idVictime", $idVictime, null);
                $db->bind("nomResponsable", $nomResponsable, null);
                $db->bind("idResponsable", $idResponsable, null);
                $db->execute();

                //CREER TACHE FAIRE RF
                $regarding = "Programmer Recherche de Fuite";
                $activity = findActivityByIdOP($idOP, 29);
                if ($activity) {
                    $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                    $db->execute();
                } else {
                    createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 29);
                }

                //CREER TACHE DEMANDE DE FAIRE RECHERCHE DE FUITE
                $rf = saveRechercheFuite("", $idOP, $numeroOP, date("Y-m-d H:i:s"), $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, null, null, "En Attente de Recherche de Fuite", 0, "INTERNE", null, null, null, $idArtisan, null, ($voisin ? $voisin->idContact : null), 1, null, $signatureVictim, $signatureResponsable, null, null, null, $faireRF, $siDegatVoisin, $siConfierSinistre, "", 0, 0, "");
                echo json_encode($rf);
                die;
            } else {
                if ($provenanceFuite == "Partie Commune Externe") {
                    //SAVE INFO GARDIEN
                    if ($coordonneeGardien === "Oui" && ($prenomGardien != "" || $nomGardien != "" || $telGardien != "" || $emailGardien != "")) {
                        saveInfosGardien($idGardien, $civiliteGardien, $prenomGardien, $nomGardien, $telGardien, $emailGardien, $adresse, $codePostal, $ville, $idUser, $idOP, $idImmeuble);
                    } else {
                        //CREER TACHE RECUPERATION INFOS GARDIEN
                        $regarding = "Récupération Coordonnées Gardien";
                        $activity = findActivityByIdOP($idOP, 34);
                        if ($activity) {
                            $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                            $db->execute();
                        } else {
                            createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 34);
                        }
                    }
                    //RESPONSABLE ==> (PROPRIETAIRE)
                    $nomResponsable = $op->nomDO;
                    $idResponsable = $op->idDOF;
                    $idColumnVictim = "";
                    $nomVictime = "";
                    $idVictime = "";
                    if ($op->typeSinistre == "Partie commune exclusive") {
                        $nomVictime =  $op->nomDO;
                        $idVictime =  $op->idDOF;
                        $idColumnVictim = "idVictimeCompanyF";
                    } else {
                        $nomVictime =  $op->contactClient;
                        $idVictime =  $op->idContactClient;
                        $idColumnVictim = "idVictimeContactF";
                    }
                    //SAVE VICTIME - RESPONSABLE FUITE
                    $db->query("UPDATE wbcc_opportunity SET nomVictime=:nomVictime, $idColumnVictim=:idVictime,nomResponsable=:nomResponsable, idResponsableCompanyF=:idResponsable   WHERE idOpportunity = $idOP");
                    $db->bind("nomVictime", $nomVictime, null);
                    $db->bind("idVictime", $idVictime, null);
                    $db->bind("nomResponsable", $nomResponsable, null);
                    $db->bind("idResponsable", $idResponsable, null);
                    $db->execute();

                    if ($reparationCause == "Oui") {
                        //CLOSE ACTIVITY RECHERCHE DE FUITE
                        $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Recherche de Fuite Externe", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", "Recherche de Fuite déjà effectuée dans les parties communes externes", "Recherche de Fuite déjà effectuée dans les parties communes externes", "", "", "", "30",  '');

                        //CLOSE ACTIVITY REPARATION
                        $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Réparation de Fuite", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", "Fuite déjà réparée par le propriétaire", "Fuite déjà réparée par le propriétaire", "", "", "", "33",  '');

                        //DEMANDE DE JUSTIFICATIF DE REPARATION
                        if ($siFactureReparation == "Oui") {
                            //CREATE RECHERCHE FUITE
                            $rf = saveRechercheFuite("", $idOP, $numeroOP, date("Y-m-d H:i:s"), $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, $reparationCause,  $reparateurDegat, "Justificatif de Réparation de Fuite reçu", 1, "EXTERNE", null, $dateReparationFuite, $descriptionReparation, $idArtisan, null, $idVoisin, $chezVoisin, null, $signatureVictim, $signatureResponsable, $pieceFuyarde, $equipementFuyard, $origineFuiteEquipement, $faireRF, $siDegatVoisin, $siConfierSinistre, "", 1, 0, "");

                            //save FACTURATION
                            $photos = ConvertTabPhotoToString($fileDatas, "JUSTIFICATIF_",  $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $idOP, $opName);

                            //UPDATE PHOTOS
                            $db->query("UPDATE wbcc_recherche_fuite SET photosReparation=:photosReparation WHERE idOpportunityF=$idOP ");
                            $db->bind("photosReparation", $photos, null);
                            $db->execute();
                            //GENERATE DOCUMENT
                            if ($photos != "") {
                                $doc = generateJustificatif($idOP, $user, $idUser, "", "", "facture", "");
                                //UPDATE INFOS JUSTIFICATIF
                                $db->query("UPDATE  wbcc_recherche_fuite  SET siJustificatif=:siJustificatif, siSignatureJustificatif=:siSignatureJustificatif, documentJustificatif=:documentJustificatif  WHERE idOpportunityF = $idOP");
                                $db->bind("siJustificatif",  1, null);
                                $db->bind("siSignatureJustificatif",  1, null);
                                $db->bind("documentJustificatif",  $doc, null);
                                $db->execute();
                            }

                            //CLOSE ACTIVITY RECUPERATION JUSTIFICATIF
                            $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Recupérer de facture de réparation de fuite", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", " Recupérer de facture de réparation de fuite : effectué le " . date('d/m/Y H:i:s'), "Recupérer la facture de réparation de fuite : effectué le " . date('d/m/Y H:i:s'), "", "", "", "32",  '');
                        } else {
                            //SEND MAIL AU GESTIONNAIRE IMMEUBLE demander REPARATION


                            //CREATE RECHERCHE FUITE
                            $rf = saveRechercheFuite("", $idOP, $numeroOP, date("Y-m-d H:i:s"), $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, $reparationCause, $reparateurDegat, "En Attente de Justificatif de Réparation de Fuite", 1, "EXTERNE", null, $dateReparationFuite, $descriptionReparation, $idArtisan, null, $idVoisin, $chezVoisin, null, $signatureVictim, $signatureResponsable, $pieceFuyarde, $equipementFuyard, $origineFuiteEquipement, $faireRF, $siDegatVoisin, $siConfierSinistre, "", 0, 0, "");
                            //CREER ACTIVITY RECUPERATION JUSTIFICATIF
                            $regarding = "Recupérer Fcature de réparation";
                            $activity = findActivityByIdOP($idOP, 32);
                            if ($activity) {
                                $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                                $db->execute();
                            } else {
                                createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 32);
                            }
                        }
                    } else {
                        $regarding = "Recherche de Fuite Externe";
                        $activity = findActivityByIdOP($idOP, 30);
                        if ($activity) {
                            $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                            $db->execute();
                        } else {
                            createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 30);
                        }

                        //CREATE RECHERCHE FUITE
                        $rf = saveRechercheFuite("", $idOP, $numeroOP, null, $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, $reparationCause, $reparateurDegat, "En Attente de Recherche de Fuite", 0, "EXTERNE", null, null, null, $idArtisan, null, $idVoisin, $chezVoisin, null, $signatureVictim, $signatureResponsable, null, null, null, $faireRF, $siDegatVoisin, $siConfierSinistre, "", 0, 0, "");
                    }
                    echo json_encode($rf);
                    die;
                } else {
                    if ($provenanceFuite == "Partie Commune Interne") {
                        //SAVE INFO GARDIEN
                        if ($coordonneeGardien === "Oui" && ($prenomGardien != "" || $nomGardien != "" || $telGardien != "" || $emailGardien != "")) {
                            saveInfosGardien($idGardien, $civiliteGardien, $prenomGardien, $nomGardien, $telGardien, $emailGardien, $adresse, $codePostal, $ville, $idUser, $idOP, $idImmeuble);
                        } else {
                            //CREER TACHE RECUPERATION INFOS GARDIEN
                            $regarding = "Récupération Coordonnées Gardien";
                            $activity = findActivityByIdOP($idOP, 34);
                            if ($activity) {
                                $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                                $db->execute();
                            } else {
                                createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 34);
                            }
                        }

                        //RESPONSABLE ==> (PROPRIETAIRE)
                        $nomResponsable = $op->nomDO;
                        $idResponsable = $op->idDOF;
                        $idColumnVictim = "";
                        $nomVictime = "";
                        $idVictime = "";
                        if ($op->typeSinistre == "Partie commune exclusive") {
                            $nomVictime =  $op->nomDO;
                            $idVictime =  $op->idDOF;
                            $idColumnVictim = "idVictimeCompanyF";
                        } else {
                            $nomVictime =  $op->contactClient;
                            $idVictime =  $op->idContactClient;
                            $idColumnVictim = "idVictimeContactF";
                        }
                        //SAVE VICTIME - RESPONSABLE FUITE
                        $db->query("UPDATE wbcc_opportunity SET nomVictime=:nomVictime, $idColumnVictim=:idVictime,nomResponsable=:nomResponsable, idResponsableCompanyF=:idResponsable   WHERE idOpportunity = $idOP");
                        $db->bind("nomVictime", $nomVictime, null);
                        $db->bind("idVictime", $idVictime, null);
                        $db->bind("nomResponsable", $nomResponsable, null);
                        $db->bind("idResponsable", $idResponsable, null);
                        $db->execute();

                        if ($reparationCause === "Oui") {
                            //CLOSE ACTIVITY RECHERCHE DE FUITE
                            $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Recherche de Fuite Externe", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", "Recherche de Fuite effectuée dans les parties communes externes", "Recherche de Fuite effectuée dans les parties communes externes", "", "", "", "30",  '');

                            //CLOSE ACTIVITY REPARATION
                            $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Réparation de Fuite", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", "Fuite réparée par le propriétaire", "Fuite réparée par le propriétaire", "", "", "", "33",  '');


                            //DEMANDE DE JUSTIFICATIF DE REPARATION
                            if ($siFactureReparation == "Oui") {
                                //CREATE RECHERCHE FUITE
                                $rf = saveRechercheFuite("", $idOP, $numeroOP, date("Y-m-d H:i:s"), $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, $reparationCause,  $reparateurDegat, "Justificatif de Réparation de Fuite reçu", 1, "EXTERNE", null, $dateReparationFuite, $descriptionReparation, $idArtisan, null, $idVoisin, $chezVoisin, null, $signatureVictim, $signatureResponsable, $pieceFuyarde, $equipementFuyard, $origineFuiteEquipement, $faireRF, $siDegatVoisin, $siConfierSinistre, "", 0, 0, "");

                                //save FACTURATION
                                $photos = ConvertTabPhotoToString($fileDatas, "JUSTIFICATIF_",  $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $idOP, $opName);

                                //UPDATE PHOTOS
                                $db->query("UPDATE wbcc_recherche_fuite SET photosReparation=:photosReparation WHERE idOpportunityF=$idOP ");
                                $db->bind("photosReparation", $photos, null);
                                $db->execute();
                                //GENERATE DOCUMENT
                                if ($photos != "") {
                                    $doc = generateJustificatif($idOP, $user, $idUser, "", "", "facture", "");
                                    //UPDATE INFOS JUSTIFICATIF
                                    $db->query("UPDATE  wbcc_recherche_fuite  SET siJustificatif=:siJustificatif, siSignatureJustificatif=:siSignatureJustificatif, documentJustificatif=:documentJustificatif  WHERE idOpportunityF = $idOP");
                                    $db->bind("siJustificatif",  1, null);
                                    $db->bind("siSignatureJustificatif",  1, null);
                                    $db->bind("documentJustificatif",  $doc, null);
                                    $db->execute();
                                }

                                //CLOSE ACTIVITY RECUPERATION JUSTIFICATIF
                                $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Recupérer de facture de réparation de fuite", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", " Recupérer de facture de réparation de fuite : effectué le " . date('d/m/Y H:i:s'), "Recupérer la facture de réparation de fuite : effectué le " . date('d/m/Y H:i:s'), "", "", "", "32",  '');
                            } else {
                                //SEND MAIL AU GESTIONNAIRE IMMEUBLE DEMANDE REPARATION DE FUITE

                                //CREATE RECHERCHE FUITE
                                $rf = saveRechercheFuite("", $idOP, $numeroOP, date("Y-m-d H:i:s"), $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, $reparationCause, $reparateurDegat, "En Attente de Justificatif de Réparation de Fuite", 1, "EXTERNE", null, $dateReparationFuite, $descriptionReparation, $idArtisan, null, $idVoisin, $chezVoisin, null, $signatureVictim, $signatureResponsable, $pieceFuyarde, $equipementFuyard, $origineFuiteEquipement, $faireRF, $siDegatVoisin, $siConfierSinistre, "", 0, 0, "");
                                //CREER ACTIVITY RECUPERATION JUSTIFICATIF
                                $regarding = "Recupérer Fcature de réparation";
                                $activity = findActivityByIdOP($idOP, 32);
                                if ($activity) {
                                    $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                                    $db->execute();
                                } else {
                                    createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 32);
                                }
                            }
                        } else {
                            //CREATE RECHERCHE FUITE
                            $rf = saveRechercheFuite("", $idOP, $numeroOP, date("Y-m-d H:i:s"), $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, $reparationCause, $reparateurDegat, "En Attente de Recherche de Fuite", 0, "INTERNE", null, null, null, $idArtisan, null, $idVoisin, $chezVoisin, $accessibleFPCI, $signatureVictim, $signatureResponsable, null, null, null, $faireRF, $siDegatVoisin, $siConfierSinistre,  $newIdOp, 0, 0, "");

                            $regarding = "Programmer Recherche Fuite";
                            $activity = findActivityByIdOP($idOP, 29);
                            if ($activity) {
                                $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                                $db->execute();
                            } else {
                                createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 29);
                            }
                            $rf->$newIdOp = $newIdOp;
                        }
                        echo json_encode($rf);
                        die;
                    } else {
                        //CHEZ VOUS
                        $newIdOp = "";
                        if ($rf2 && $rf2->chezVoisin == "1") {
                            if ($siConfierSinistre == "1") {
                                //CREATE OP
                                $newIdOp = createOP($idOP, $user['idContact'], $idUser, $user['numeroContact'], $user['fullName'], $idVoisin, $idCompagnieAssuranceF,  $compagnieAssurance, $numeroPolice, $siDegatVoisin, $idImmeuble, $idAppVoisin);
                            }
                        }

                        //SAVE VICTIME - RESPONSABLE FUITE
                        $db->query("UPDATE wbcc_opportunity SET nomResponsable=:nomResponsable, idResponsableContactF =:idResponsableContactF  WHERE idOpportunity = $idOP");
                        $db->bind("nomResponsable", $op->contactClient, null);
                        $db->bind("idResponsableContactF", $op->idContactClient, null);
                        $db->execute();
                        if ($reparationCause === "Oui") {
                            //CLOSE ACTIVITY RECHERCHE DE FUITE
                            $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Recherche de Fuite Externe", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", "Recherche de Fuite Externe effectué", "Recherche de Fuite externe effectuée", "", "", "", "30",  '');
                            //CLOSE ACTIVITY REPARATION FUITE
                            $artisan = false;
                            if ($reparateurDegat === "Vous-même") {
                                $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Réparation de Fuite", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", "Réparation de Fuite effectuée par le sinistré", "Réparation de Fuite effectuée par le sinistré", "", "", "", "33",  '');
                            } else {
                                //SAVE INFO ARTISAN
                                if ($coordonneeArtisan === "Oui" && ($prenomArtisan != "" || $nomArtisan != "" || $telArtisan != "" || $emailArtisan != "")) {
                                    $artisan = saveInfosArtisan($idArtisan, $civiliteArtisan, $prenomArtisan, $nomArtisan, $telArtisan, $emailArtisan, "", "", "", $idUser, $idOP, $idImmeuble);
                                } else {
                                    //CREER TACHE RECUPERATION INFOS Artisan
                                    $regarding = "Récupération Coordonnées Artisan";
                                    $activity = findActivityByIdOP($idOP, 36);
                                    if ($activity) {
                                        $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                                        $db->execute();
                                    } else {
                                        createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 36);
                                    }
                                }
                                $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Réparation de Fuite", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", "Réparation de Fuite effectuée par '$reparateurDegat'", "Réparation de Fuite effectuée '$reparateurDegat'", "", "", "", "33",  '');
                            }
                            if ($siFactureReparation == "Oui") {
                                //CREATE RECHERCHE FUITE
                                $rf = saveRechercheFuite("", $idOP, $numeroOP, date("Y-m-d H:i:s"), $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, $reparationCause,  $reparateurDegat, "Justificatif de Réparation de Fuite reçu", 1, "EXTERNE", null, $dateReparationFuite, $descriptionReparation, ($artisan ? $artisan->idContact : null), null, $idVoisin, $chezVoisin, null, $signatureVictim, $signatureResponsable, $pieceFuyarde, $equipementFuyard, $origineFuiteEquipement, $faireRF, $siDegatVoisin, $siConfierSinistre, $newIdOp, 0, 0, "");

                                //save FACTURATION
                                $photos = ConvertTabPhotoToString($fileDatas, "JUSTIFICATIF_",  $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $idOP, $opName);

                                //UPDATE PHOTOS
                                $db->query("UPDATE wbcc_recherche_fuite SET photosReparation=:photosReparation WHERE idOpportunityF=$idOP ");
                                $db->bind("photosReparation", $photos, null);
                                $db->execute();
                                //GENERATE DOCUMENT
                                if ($photos != "") {
                                    $doc = generateJustificatif($idOP, $user, $idUser, "", "", "facture", $newIdOp);
                                    //UPDATE INFOS JUSTIFICATIF
                                    $db->query("UPDATE  wbcc_recherche_fuite  SET siJustificatif=:siJustificatif, siSignatureJustificatif=:siSignatureJustificatif, documentJustificatif=:documentJustificatif  WHERE idOpportunityF = $idOP");
                                    $db->bind("siJustificatif",  1, null);
                                    $db->bind("siSignatureJustificatif",  1, null);
                                    $db->bind("documentJustificatif",  $doc, null);
                                    $db->execute();
                                }

                                //close activity justificatif
                                $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Recupérer de facture de réparation de fuite", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", " Recupérer de facture de réparation de fuite : effectué le " . date('d/m/Y H:i:s'), " Recupérer de facture de réparation de fuite : effectué le " . date('d/m/Y H:i:s'), "", "", "", "32",  '');
                            } else {
                                if ($siAttestation === "Oui") {
                                    //CREATE RECHERCHE FUITE
                                    $rf = saveRechercheFuite("", $idOP, $numeroOP, date("Y-m-d H:i:s"), $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, $reparationCause,  $reparateurDegat, "Justificatif de Réparation de Fuite reçu", 1, "EXTERNE", null, $dateReparationFuite, $descriptionReparation, ($artisan ? $artisan->idContact : null), null, $idVoisin, $chezVoisin, null, $signatureVictim, $signatureResponsable, $pieceFuyarde, $equipementFuyard, $origineFuiteEquipement, $faireRF, $siDegatVoisin, $siConfierSinistre, $newIdOp, 0, 0, "");

                                    // SIGNER ATTESTATION
                                    $doc = generateJustificatif($idOP, $user, $idUser, "avec", $signature, "attestation", $newIdOp);
                                    //UPDATE INFOS JUSTIFICATIF
                                    $db->query("UPDATE  wbcc_recherche_fuite  SET siJustificatif=:siJustificatif, siSignatureJustificatif=:siSignatureJustificatif, documentJustificatif=:documentJustificatif  WHERE idOpportunityF = $idOP");
                                    $db->bind("siJustificatif",  1, null);
                                    $db->bind("siSignatureJustificatif",  1, null);
                                    $db->bind("documentJustificatif",  $doc, null);
                                    $db->execute();

                                    //CLOSE ACTIVITY RECUPERATION JUSTIFICATIF
                                    $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Signer Attestation Réparation de Fuite (Justificatif)", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", "Signer Attestation Réparation de Fuite : effectué le " . date('d/m/Y H:i:s'), "Signer Attestation Réparation de Fuite : effectué le " . date('d/m/Y H:i:s'), "", "", "", "32",  '');
                                } else {
                                    //CREATE RECHERCHE FUITE
                                    $rf = saveRechercheFuite("", $idOP, $numeroOP, date("Y-m-d H:i:s"), $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, $reparationCause, $reparateurDegat, "En Attente de Justificatif de Réparation de Fuite", 1, "EXTERNE", null, $dateReparationFuite, $descriptionReparation, ($artisan ? $artisan->idContact : null), null, $idVoisin, $chezVoisin, null, $signatureVictim, $signatureResponsable, $pieceFuyarde, $equipementFuyard, $origineFuiteEquipement, $faireRF, $siDegatVoisin, $siConfierSinistre, $newIdOp, 0, 0, "");

                                    //CREER ACTIVITY RECUPERATION JUSTIFICATIF
                                    $modeSignature =  "sans";
                                    $regarding = "Signer Attestation Réparation de Fuite (Justificatif)";
                                    $activity = findActivityByIdOP($idOP, 32);
                                    if ($activity) {
                                        $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                                        $db->execute();
                                    } else {
                                        createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", "32");
                                    }
                                }
                            }
                            $rf->$newIdOp = $newIdOp;
                            echo json_encode($rf);
                            die;
                        } else {
                            if ($siRechercheFuite === "Oui") {
                                // "Créer Tâche Réparation FUITE";
                                $regarding = "Réparation de Fuite";
                                $activity = findActivityByIdOP($idOP, 33);
                                if ($activity) {
                                    $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                                    $db->execute();
                                } else {
                                    createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 33);
                                }

                                if ($auteurRF === "Vous-même") {
                                    //CLOSE ACTIVITY RECHERCHE DE FUITE
                                    $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Recherche de Fuite Externe", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", "Recherche de Fuite effectuée par le sinistré", "Recherche de Fuite effectuée par le sinistré", "", "", "", "30",  '');

                                    //CREATE RECHERCHE FUITE
                                    $rf = saveRechercheFuite("", $idOP, $numeroOP, date("Y-m-d H:i:s"), $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, $reparationCause,  $reparateurDegat, "En attente de Réparation de Fuite", 1, "EXTERNE", $auteurRF, null, null, $idArtisan, null, $idVoisin, $chezVoisin, null, $signatureVictim, $signatureResponsable, null, null, null, $faireRF, $siDegatVoisin, $siConfierSinistre, $newIdOp, 0, 0, "");
                                } else {
                                    //CLOSE ACTIVITY RECHERCHE DE FUITE
                                    $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Recherche de Fuite Externe", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", "Recherche de Fuite effectuée par '$auteurRF'", "Recherche de Fuite effectuée par '$auteurRF'", "", "", "", "31",  '');

                                    //SAVE INFO ARTISAN
                                    if ($coordonneeArtisan === "Oui" && ($prenomArtisan != "" || $nomArtisan != "" || $telArtisan != "" || $emailArtisan != "")) {
                                        $artisan = saveInfosArtisan($idArtisan, $civiliteArtisan, $prenomArtisan, $nomArtisan, $telArtisan, $emailArtisan, "", "", "", $idUser, $idOP, $idImmeuble);
                                        //CREATE RECHERCHE FUITE
                                        $rf = saveRechercheFuite("", $idOP, $numeroOP, date("Y-m-d H:i:s"), $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, $reparationCause,  $reparateurDegat, "En attente de Réparation de Fuite", 1, "EXTERNE", $auteurRF, null, null, $artisan->idContact, null, $idVoisin, $chezVoisin, null, $signatureVictim, $signatureResponsable, null, null, null, $faireRF, $siDegatVoisin, $siConfierSinistre, $newIdOp, 0, 0, '');
                                    } else {
                                        //CREATE RECHERCHE FUITE
                                        $rf = saveRechercheFuite("", $idOP, $numeroOP, date("Y-m-d H:i:s"), $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, $reparationCause,  $reparateurDegat, "En attente de Réparation de Fuite", 1, "EXTERNE", $auteurRF, null, null, null, null, $idVoisin, $chezVoisin, null, $signatureVictim, $signatureResponsable, null, null, null, $faireRF, $siDegatVoisin, $siConfierSinistre, $newIdOp, 0, 0, "");
                                        //CREER TACHE RECUPERATION INFOS Artisan
                                        $regarding = "Récupération Coordonnées Artisan";
                                        $activity = findActivityByIdOP($idOP, 36);
                                        if ($activity) {
                                            $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                                            $db->execute();
                                        } else {
                                            createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 36);
                                        }
                                    }
                                }
                                $rf->$newIdOp = $newIdOp;
                                echo json_encode($rf);
                                die;
                            } else {
                                //CREATE RECHERCHE FUITE
                                $rf = saveRechercheFuite("", $idOP, $numeroOP, null, $user['idUtilisateur'], $user['prenomContact'] . " " . $user['nomContact'], $provenanceFuite, $reparationCause, $reparateurDegat, "En attente de recherche de fuite", 0, "INTERNE", null, $dateReparationFuite, null, $idArtisan, null, $idVoisin, $chezVoisin, $siAccessibleFuite, $signatureVictim, $signatureResponsable, null, null, null, $faireRF, $siDegatVoisin, $siConfierSinistre, $newIdOp, 0, 0, "");
                                if ($siAccessibleFuite === "Oui") {
                                    //RF
                                    if ($siFaireRF != "Oui") {
                                        // "Créer Tâche Faire Recherche de fuite VIA REPA";
                                        $regarding = "Programmer Recherche Fuite";
                                        $activity = findActivityByIdOP($idOP, 29);
                                        if ($activity) {
                                            $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                                            $db->execute();
                                        } else {
                                            createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 29);
                                        }
                                    }
                                } else {
                                    // "Créer Tâche Faire Recherche de fuite VIA REPA";
                                    $regarding = "Programmer Recherche Fuite";
                                    $activity = findActivityByIdOP($idOP, 29);
                                    if ($activity) {
                                        $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                                        $db->execute();
                                    } else {
                                        createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $opName . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 29);
                                    }
                                }
                                $rf->$newIdOp = $newIdOp;
                                echo json_encode($rf);
                                die;
                            }
                        }
                    }
                }
            }
        }
        echo json_encode("0");
    }

    if ($action == 'updateRFMobile') {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        $ok = 1;
        $editDate = date('Y-m-d H:i:s');
        $idUser = $user['idUtilisateur'];
        $nomUser = $user['prenomContact'] . " " . $user['nomContact'];
        $op = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
        if ($idRF == "0") {
            $rf = findItemByColumn("wbcc_recherche_fuite", "idOpportunityF", $idOP);
            $idRF = $rf->idRF;
        }

        if (sizeof($tabPieceRF) != 0) {
            /**** DELETE PIECE - EQUIPEMENT AND ORIGINE *****/
            $db->query("DELETE FROM wbcc_rf_piece WHERE idRFF= $idRF");
            $db->execute();
            foreach ($tabPieceRF as $key => $piece) {
                $idPieceF = $piece['id'];
                $photosPiece = ConvertTabPhotoToString($piece['fileDatas'], "RF_" . str_replace(' ', '_', $piece['nom']), $idUser, $nomUser, $idOP, ($op ? $op->name : ""));
                $commentsPhotoPiece = ConvertTabCommentaireToString($piece['textInputs']);
                $videosPiece = ConvertTabVideoToString($piece['videoDatas'], str_replace(' ', '_', $piece['nom']), $idUser);
                $commentsVideosPiece =  ConvertTabCommentaireToString($piece['videoInputs']);

                $pieceSearch = false;
                /**** CREATE PIECE *****/
                $numeroRFPiece = 'PIECE' . date("dmYHis") .  $idOP . $key . $idUser;
                $db->query("INSERT INTO wbcc_rf_piece(numeroRFPiece, idRFF,  idPieceF, nomPiece, libellePiece, commentairePiece, photosPiece, commentsPhotosPiece, videosPiece, commentsVideosPiece, createDate, editDate, idUserF) VALUES(:numeroRFPiece, :idRFF, :idPieceF, :nomPiece, :libellePiece, :commentairePiece, :photosPiece, :commentsPhotosPiece, :videosPiece, :commentsVideosPiece, :createDate, :editDate, :idUserF)");
                $db->bind("numeroRFPiece", $numeroRFPiece, null);
                $db->bind("createDate", $editDate, null);
                $db->bind("idUserF", $idUser, null);
                $db->bind("idRFF", $idRF, null);
                $db->bind("idPieceF", $idPieceF, null);
                $db->bind("nomPiece",  $piece['nom'], null);
                $db->bind("libellePiece", $piece['libelle'], null);
                $db->bind("commentairePiece",  isset($piece['commentaire']) ? $piece['commentaire'] : "", null);
                $db->bind("photosPiece", $photosPiece, null);
                $db->bind("commentsPhotosPiece", $commentsPhotoPiece, null);
                $db->bind("videosPiece", $videosPiece, null);
                $db->bind("commentsVideosPiece", $commentsVideosPiece, null);
                $db->bind("editDate", $editDate, null);

                if ($db->execute()) {
                    $pieceSearch = findItemByColumn("wbcc_rf_piece", "numeroRFPiece", $numeroRFPiece);
                    $idRFPiece = $pieceSearch->idRFPiece;
                    //SAVE SUPPORT
                    $equipements = $piece['equipements'];
                    if (sizeof($equipements) != 0) {
                        foreach ($equipements as $key2 => $equipement) {
                            $libEquipement = $equipement['libelle'];
                            $db->query("SELECT * FROM wbcc_rf_equipement WHERE libelleEquipement=:libelleEquipement AND idRFPieceF=$idRFPiece LIMIT 1 ");
                            $db->bind("libelleEquipement", $libEquipement, null);
                            $sup =  $db->single();
                            if ($sup) {
                            } else {
                                $idEquipementF = $equipement['id'];
                                $photosEquipement = ConvertTabPhotoToString($equipement['fileDatas'], "RF_" . str_replace(' ', '_', $equipement['libelle']),  $idUser, $nomUser, $idOP, ($op ? $op->name : ""));
                                $commentsPhotosEquipement = ConvertTabCommentaireToString($equipement['textInputs']);
                                $videosEquipement = ConvertTabVideoToString($equipement['videoDatas'], str_replace(' ', '_', $equipement['libelle']), $idUser);
                                $commentsVideosEquipement =  ConvertTabCommentaireToString($equipement['videoInputs']);
                                /**** CREATE EQUIPEMENT *****/
                                $numeroRFEquipement = 'EQU' . date("dmYHis") . $idOP . $key . $key2 . $idUser;
                                $db->query("INSERT INTO wbcc_rf_equipement(numeroRFEquipement, idRFPieceF, idEquipementF, nomEquipement, libelleEquipement, photosEquipement, commentsPhotosEquipement, videosEquipement, commentsVideosEquipement, commentaireEquipement, fuiteTrouvee, createDate, editDate, idUserF) VALUES(:numeroRFEquipement, :idRFPieceF, :idEquipementF, :nomEquipement, :libelleEquipement, :photosEquipement, :commentsPhotosEquipement, :videosEquipement, :commentsVideosEquipement, :commentaireEquipement, :fuiteTrouvee, :createDate, :editDate, :idUserF)");
                                $db->bind("numeroRFEquipement", $numeroRFEquipement, null);
                                $db->bind("createDate", $editDate, null);
                                $db->bind("idUserF", $idUser, null);
                                $db->bind("idRFPieceF", $idRFPiece, null);
                                $db->bind("idEquipementF", $equipement['id'], null);
                                $db->bind("nomEquipement", $equipement['libelle'], null);
                                $db->bind("libelleEquipement", $equipement['libelle'], null);
                                $db->bind("photosEquipement", $photosEquipement, null);
                                $db->bind("commentsPhotosEquipement", $commentsPhotosEquipement, null);
                                $db->bind("videosEquipement", $videosEquipement, null);
                                $db->bind("commentsVideosEquipement", $commentsVideosEquipement, null);
                                $db->bind("commentaireEquipement", $equipement['commentaire'], null);
                                $db->bind("fuiteTrouvee", $equipement['fuiteTrouvee'], null);
                                $db->bind("editDate", $editDate, null);
                                if ($db->execute()) {
                                    //ENREGISTREMENT ORIGINE
                                    $RFEquipement = findItemByColumn("wbcc_rf_equipement", "numeroRFEquipement", $numeroRFEquipement);
                                    $idRFEquipement = $RFEquipement->idRFEquipement;
                                    $origines = isset($equipement['origines']) ? $equipement['origines'] : [];
                                    foreach ($origines as $key3 => $origine) {
                                        $numeroRFOrigineFuite = "OF" . date("YmdHis") . $idOP . $key . $key2 . $key3 . $idUser;
                                        $db->query("INSERT INTO wbcc_rf_origine_fuite(numeroRFOrigineFuite, idOrigineFuiteF, idRFEquipementF, libelleOrigineFuite, commentaireOrigineFuite, responsableOrigineFuite,   createDate, editDate, idUserF) VALUES(:numeroRFOrigineFuite, :idOrigineFuiteF, :idRFEquipementF, :libelleOrigineFuite, :commentaireOrigineFuite, :responsableOrigineFuite, :createDate, :editDate, :idUserF)");
                                        $db->bind("numeroRFOrigineFuite", $numeroRFOrigineFuite, null);
                                        $db->bind("idOrigineFuiteF", $origine["id"], null);
                                        $db->bind("idRFEquipementF", $idRFEquipement, null);
                                        $db->bind("libelleOrigineFuite", $origine["libelle"], null);
                                        $db->bind("commentaireOrigineFuite", isset($origine["commentaire"]) ? $origine["commentaire"] : "", null);
                                        $db->bind("responsableOrigineFuite", isset($origine["responsable"]) ? $origine["responsable"] : "", null);
                                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                                        $db->bind("editDate", date("Y-m-d H:i:s"), null);
                                        $db->bind("idUserF", $idUser, null);
                                        if ($db->execute()) {
                                            $ok = 1;
                                        } else {
                                            $ok = 0;
                                        }
                                    }
                                } else {
                                    $ok = 0;
                                }
                            }
                        }
                    }
                } else {
                    $ok = 0;
                }
            }
            // if ($rf->etape == "En attente de recherche de fuite") 
            {
                //UPDATE RF
                $db->query("UPDATE wbcc_recherche_fuite SET dateRF=:dateRF, idAuteurRF=:idAuteurRF, auteurRF=:auteurRF, etatRf=1, rechercheFuiteEffectueeInterne =1, etape=:etape WHERE idRF= $idRF");
                $db->bind("dateRF", date('Y-m-d H:i:s'), null);
                $db->bind("auteurRF", $nomUser, null);
                $db->bind("idAuteurRF", $idUser, null);
                $db->bind("etape", "Recherche de Fuite Effectuée", null);
                $db->execute();

                //GENERATE CONSTAT DDE
                $constatDDE = file_get_contents(URLROOT . "/public/documents/rechercheFuite/constatDDE.php?idOP=$idOP");
                $constatDDE = str_replace('"', "", $constatDDE);
                if ($constatDDE != "") {
                    $db = new Database();
                    //SAVE DOCUMENT
                    $db->query("UPDATE wbcc_recherche_fuite SET  documentConstatDDE=:file, idAuteurGenererConstatDDE=:idAuteur, dateGenererConstatDDE=:date WHERE idOpportunityF=$idOP");
                    $db->bind("idAuteur", $idUser, null);
                    $db->bind("date", date("Y-m-d H:i:s"), null);
                    $db->bind("file", $constatDDE, null);
                    $db->execute();

                    $db->query("UPDATE wbcc_opportunity SET  documentConstatDDE=:file, idAuteurGenererConstatDDE=:idAuteur, dateGenererConstatDDE=:date WHERE idOpportunity=$idOP");
                    $db->bind("idAuteur", $idUser, null);
                    $db->bind("date", date("Y-m-d H:i:s"), null);
                    $db->bind("file", $constatDDE, null);
                    $db->execute();

                    $numeroDoc = 'DOC0' . date("dmYHis") . $idUser;
                    $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");
                    $db->bind("publie",  strpos('Complet', $constatDDE) != 0 ? "1" : "0", null);
                    $db->bind("source", "EXTRA", null);
                    $db->bind("numeroDocument", $numeroDoc, null);
                    $db->bind("nomDocument", $constatDDE, null);
                    $db->bind("urlDocument", $constatDDE, null);
                    $db->bind("commentaire", "", null);
                    $db->bind("createDate",  date("Y-m-d H:i:s"), null);
                    $db->bind("guidHistory", null, null);
                    $db->bind("typeFichier", "Adobe Acrobat Document", null);
                    $db->bind("size", null, null);
                    $db->bind("guidUser", "", null);
                    $db->bind("idUtilisateurF", $idUser, null);
                    $db->bind("auteur", $nomUser, null);
                    if ($db->execute()) {
                        $db->query("SELECT * FROM wbcc_document WHERE numeroDocument='$numeroDoc' LIMIT 1");
                        $document = $db->single();
                        $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($idOP,$document->idDocument)");
                        $db->execute();
                    }

                    //SAVE HISTORIQUE
                    createHistorique("Génération de constat DDE : $constatDDE", $nomUser, $idUser, $idOP);
                }

                //GENERATE RAPPORT
                $file = file_get_contents(URLROOT . "/public/documents/rechercheFuite/rapportRF.php?idOP=$idOP");
                $file = str_replace('"', "", $file);
                if ($file  != "") {
                    $db->query("UPDATE wbcc_opportunity SET rapportRechercheFuite = '$file' WHERE idOpportunity = $idOP");
                    $db->execute();

                    //ADD DOCUMENT TO OP
                    $search = findItemByColumn("wbcc_document", "urlDocument", $file);
                    if ($search) {
                        //UPDATE
                        $db->query("UPDATE wbcc_document SET nomDocument = :nomDocument, urlDocument = :urlDocument, commentaire = :commentaire,  guidHistory = :guidHistory, typeFichier=:typeFichier, size=:size, guidUser=:guidUser, idUtilisateurF= :idUtilisateurF, auteur=:auteur, source=:source, editDate=:editDate WHERE idDocument = $search->idDocument");
                    } else {
                        $search = false;
                        //CREATE
                        $numeroDoc = 'DOC' . date("dmYHis") . "$idOP" .  $user['idUtilisateur'];
                        $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie, editDate) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie, :editDate)");
                        $db->bind("publie",  "0", null);
                        $db->bind("numeroDocument", $numeroDoc, null);
                        $db->bind("createDate",  date("Y-m-d H:i:s"), null);
                    }
                    $db->bind("editDate",  date("Y-m-d H:i:s"), null);
                    $db->bind("source", "EXTRA", null);
                    $db->bind("nomDocument", $file, null);
                    $db->bind("urlDocument", $file, null);
                    $db->bind("commentaire", "", null);
                    $db->bind("guidHistory", null, null);
                    $db->bind("typeFichier", "Adobe Acrobat Document", null);
                    $db->bind("size", null, null);
                    $db->bind("guidUser",  $user['numeroContact'], null);
                    $db->bind("idUtilisateurF",  $user['idUtilisateur'], null);
                    $db->bind("auteur", $user['prenomContact'] . " " . $user['nomContact'], null);
                    if ($db->execute()) {
                        if ($search == false) {
                            $document =  findItemByColumn("wbcc_document", "numeroDocument", $numeroDoc);
                            $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($idOP,$document->idDocument)");
                            $db->execute();
                        }
                    }
                    //SEND MAIL TO GESTION
                    $body = "<p style='text-align:justify'>Bonjour, 
                    <br /><br />RAPPORT RECHERCHE DE FUITE : $op->name
                    <br /><br />Veuillez ne pas répondre à ce message envoyé automatiquement pour votre information. <br /> <br />
                    <b>WBCC ASSISTANCE </b><br /> 
                    <b> " . $user['prenomContact'] . " " . $user['nomContact'] . " </b>";
                    $subject = "$op->name - RAPPORT RECHERCHE DE FUITE";
                    $cc = [];
                    $tabFiles = ["/public/documents/opportunite/$file"];
                    $fileNames = [$file];
                    $r = new Role();
                    if ($r::mailGestionWithFiles("gestion@wbcc.fr", $subject, $body,  $tabFiles, $fileNames, $cc)) {
                    }
                }
                //CLOSE TACHE RF
                $res = closeActivityByOPAndRegarding($idOP, $op->name, "$op->name - Faire Recherche de Fuite", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", "Recherche de fuite effectuée le " . date('d/m/Y H:i:s'), "Recherche de fuite effectuée le " . date('d/m/Y H:i:s'), "", "", "", "30",  '');
            }
        }
        echo json_encode("$ok");
    }

    if ($action == "getListePieceRF") {
        $idRF = $_GET['idRF'];
        $db->query("SELECT  * FROM wbcc_rf_piece WHERE idRFF= $idRF");
        $datas = $db->resultSet();
        if (sizeof($datas) > 0) {
            foreach ($datas as $key => $piece) {
                //GET EQUIPEMENTS
                $db->query("SELECT  * FROM wbcc_rf_equipement WHERE idRFPieceF= $piece->idRFPiece ");
                $equipements = $db->resultSet();
                $piece->equipements = $equipements;
                //GET ORIGINES
                foreach ($equipements as $key2 => $equipement) {
                    $db->query("SELECT  * FROM wbcc_rf_origine_fuite WHERE idRFEquipementF= $equipement->idRFEquipement ");
                    $origines = $db->resultSet();
                    $equipement->origines = $origines;
                }
            }
        }
        echo json_encode($datas);
    }

    if ($action == "findRFByOP") {
        $id = $_GET['idOP'];
        $db->query("SELECT * FROM   wbcc_opportunity o, wbcc_recherche_fuite r, wbcc_appartement_contact ac, wbcc_contact c, wbcc_appartement a, wbcc_immeuble i WHERE  o.idOpportunity = r.idOpportunityF AND o.idAppartement=a.idApp AND o.idContactClient = c.idContact  AND a.idImmeubleF = i.idImmeuble  AND r.idOpportunityF = $id GROUP BY r.idOpportunityF ORDER BY r.dateRF LIMIT 1");
        $data = $db->single();
        if (empty($data)) {
            echo json_encode("null");
        } else {
            echo json_encode($data);
        }
    }

    if ($action == "listeRF") {
        $type = $_GET['type'];
        $idUser = isset($_GET['idUser']) ? $_GET['idUser'] : "";
        $user = false;
        if ($idUser != "") {
            $user = findItemByColumn("wbcc_utilisateur", "idUtilisateur", $idUser);
        }
        $data = [];
        $req = "";
        if ($user) {
            // if ($user->role == "3" || $user->role == "28") {
            //     $req = " AND r.idExpertF= $idUser ";
            // }
        }

        $db->query("SELECT * FROM   wbcc_opportunity o, wbcc_recherche_fuite r, wbcc_appartement_contact ac, wbcc_contact c, wbcc_appartement a, wbcc_immeuble i WHERE  o.idOpportunity = r.idOpportunityF AND o.idAppartement=a.idApp AND o.idContactClient = c.idContact  AND a.idImmeubleF = i.idImmeuble AND o.status = 'Open'  $req GROUP BY r.idOpportunityF ORDER BY r.dateRF");

        $data = $db->resultSet();
        $tab = [];
        $tabToday = [];
        $tabPasse = [];
        $tabFutur = [];
        $today = new DateTime(date("Y-m-d"));
        if (empty($data)) {
            echo json_encode("0");
        } else {
            foreach ($data as $key => $rf) {
                $idOP = $rf->idOpportunity;
                $db->query("SELECT  * FROM wbcc_releve_technique WHERE idOpportunityF=$idOP LIMIT 1");
                $rt = $db->single();
                $rfComplet = ["rf" => $rf, "rt" => $rt,  "etatRF" => $rf->etatRF];
                if ($rf->dateRF != null && $rf->dateRF != "") {
                    $dateRF = new DateTime(date("Y-m-d", strtotime(explode(' ', $rf->dateRF)[0])));
                    if ($dateRF < $today) {
                        $tabPasse[] = $rfComplet;
                    } else {
                        if ($dateRF > $today) {
                            $tabFutur[] = $rfComplet;
                        } else {
                            $tabToday[] = $rfComplet;
                        }
                    }
                } else {
                    $tabFutur[] = $rfComplet;
                }
            }

            $tab = ["today" => $tabToday, "passe" => $tabPasse, "futur" => $tabFutur];
            echo json_encode($tab);
        }
    }
    //RECHERCHE DE FUITE

    if ($action == "UPDATEACTVITYPASSED") {
        $db->query("SELECT * FROM wbcc_rendez_vous WHERE etatRV = 1 AND idOpportunityF IN (3113, 4225, 4205, 3963, 3764)");
        $rvs = $db->resultSet();
        foreach ($rvs as $key3 => $rv) {
            // $res = closeActivityByOPAndRegarding($rv->idOpportunityF, $rv->numeroOP, "$rv->numeroOP - Faire FRT", "", $rv->expert, "", $rv->idExpertF, "", "RENDEZ-VOUS RELEVE TECHNIQUE : effectué le " . $rv->dateRV, "RENDEZ-VOUS RELEVE TECHNIQUE : effectué le " . $rv->dateRV, "", "", "", "6");
        }
        echo json_encode("1");
    }

    if ($action == "updateMetreErrone") {
        //METRE REVETEMENT
        // $db->query("SELECT * FROM `wbcc_rt_piece_support` s, wbcc_rt_piece p WHERE p.idRTPiece = s.idRTPieceF AND s.largeurSupport > p.largeurPiece AND p.largeurPiece != ''  AND s.largeurSupport >3  AND s.libelleSupport LIKE '%MUR%' ");
        // $data = $db->resultSet();
        // foreach ($data as $key => $value) {
        //     $db->query("UPDATE wbcc_rt_piece SET largeurPiece=:largeurPiece WHERE idRTPiece=:idRTPiece");
        //     $db->bind("largeurPiece", $value->largeurSupport, null);
        //     $db->bind("idRTPiece", $value->idRTPiece, null);
        //     $db->execute();

        //     $db->query("UPDATE wbcc_rt_piece_support SET largeurSupport=:largeurSupport WHERE idRTPieceSupport=:idRTPieceSupport");
        //     $db->bind("largeurSupport", $value->largeurPiece, null);
        //     $db->bind("idRTPieceSupport", $value->idRTPieceSupport, null);
        //     $db->execute();
        // }

        $db->query("SELECT * FROM `wbcc_rt_piece` ");
        $data = $db->resultSet();
        foreach ($data as $key => $piece) {
            if ($piece->nbMursSinistres != null && $piece->nbMursSinistres != "") {
                $nbMs = $piece->nbMursSinistres;


                $db->query("SELECT * FROM `wbcc_rt_piece_support` WHERE idRTPieceF=$piece->idRTPiece ORDER BY idRTPieceSupport ASC ");
                $supports = $db->resultSet();
                $i = 0;
                foreach ($supports as $key2 => $support) {
                    if (str_contains(strtolower($support->libelleSupport), "mur")) {
                        $i++;
                        $sin = "";
                        $estSin = 0;
                        if ($i <= $nbMs) {
                            $estSin = 1;
                            $sin = "Sinistré";
                        } else {
                            $sin = "Non Sinistré";
                            $estSin = 0;
                        }

                        $db->query("UPDATE wbcc_rt_piece_support SET nomSupport=:nomSupport, libelleSupport=:libelle, estSinistre=:estSinistre WHERE idRTPieceSupport=$support->idRTPieceSupport");
                        $db->bind("nomSupport", "MUR_$i $sin", null);
                        $db->bind("libelle", "MUR_$i $sin", null);
                        $db->bind("estSinistre", $estSin, null);
                        $db->execute();
                    }
                }
            }
        }

        echo json_encode("1");
    }

    //CRON POUR LIER DOCUMENT ET GENERER RAPPORT FRT
    if ($action == "createDocumentRT") {
        $date = date("Y-m-d");
        $db->query("SELECT * FROM wbcc_rendez_vous rv, wbcc_releve_technique rt, wbcc_opportunity o WHERE rv.idOpportunityF = rt.idOpportunityF AND o.idOpportunity = rt.idOpportunityF  AND rv.dateRV >= '2024-04-01' AND o.frtOutlook != 1 AND o.frtFait=1 AND rv.etatRV = 1 AND (rt.editDate LIKE '%$date%' OR rv.editDate LIKE '%$date%')");
        $rvs = $db->resultSet();
        foreach ($rvs as $key3 => $rv) {
            $idOP = $rv->idOpportunityF;
            $rt = findItemByColumn("wbcc_releve_technique", "idOpportunityF", $rv->idOpportunityF);
            $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$rv->idExpertF LIMIT 1");
            $exp = $db->single();
            // $rv = findItemByColumn("wbcc_rendez_vous", "idOpportunityF", $idOP);
            $tabPhotos = [];
            if ($rt) {
                $db->query("SELECT * FROM wbcc_rt_piece WHERE idRTF= $rt->idRT");
                $pieces = $db->resultSet();
                foreach ($pieces as $key => $piece) {
                    $photos = ($piece->photosPiece != null) ? explode(";", $piece->photosPiece) : [];
                    foreach ($photos as $key => $photo) {
                        $tabPhotos[] = $photo;
                    }
                    $db->query("SELECT * FROM wbcc_rt_piece_support WHERE idRTPieceF= $piece->idRTPiece");
                    $supports = $db->resultSet();
                    foreach ($supports as $key => $support) {
                        $photos = ($support->photosSupport != null) ? explode(";", $support->photosSupport) : [];
                        foreach ($photos as $key => $photo) {
                            $tabPhotos[] = $photo;
                        }
                    }
                }
            }

            foreach ($tabPhotos as $key => $photo) {
                $doc =   findItemByColumn("wbcc_document", "urlDocument", $photo);
                if ($doc) {
                } else {
                    $numeroDocument = "DOC" . date('dmYHis') . $idOP . $key . $rv->idExpertF;
                    $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");

                    $db->bind("publie", "1", null);
                    $db->bind("source", "EXTRA", null);
                    $db->bind("numeroDocument", $numeroDocument, null);
                    $db->bind("nomDocument", $photo, null);
                    $db->bind("urlDocument", $photo, null);
                    $db->bind("commentaire", "", null);
                    $db->bind("createDate",  $rv->editDate, null);
                    $db->bind("guidHistory", null, null);
                    $db->bind("typeFichier", "Image", null);
                    $db->bind("size", null, null);
                    $db->bind("guidUser", $exp->numeroContact, null);
                    $db->bind("idUtilisateurF", $rv->idExpertF, null);
                    $db->bind("auteur", $rv->expert, null);
                    if ($db->execute()) {
                        $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                        $db->query("INSERT INTO wbcc_opportunity_document( idDocumentF, idOpportunityF) VALUES (:idDocumentF, :idOpportunityF)");
                        $db->bind("idDocumentF", $document->idDocument, null);
                        $db->bind("idOpportunityF", $idOP, null);
                        $db->execute();
                    }
                }
            }
            //GENERATE RAPPORT RT
            $fileRT = file_get_contents(URLROOT . "/public/documents/opportunite/rapportRT.php?idOp=$idOP");
            $fileRT = str_replace('"', "", $fileRT);
            //GENERATE RAPPORT
            $file = file_get_contents(URLROOT . "/public/documents/opportunite/rapportFRT.php?idOp=$idOP");
            $file = str_replace('"', "", $file);
            if ($file  != "") {
                $db->query("UPDATE wbcc_opportunity SET rapportFRT = '$file', rapportTeleExpertise='$fileRT' WHERE idOpportunity = $idOP");
                $db->execute();

                //ADD DOCUMENT TO OP
                $search = findItemByColumn("wbcc_document", "urlDocument", $file);
                if ($search) {
                    //UPDATE
                    $db->query("UPDATE wbcc_document SET nomDocument = :nomDocument, urlDocument = :urlDocument, commentaire = :commentaire,  guidHistory = :guidHistory, typeFichier=:typeFichier, size=:size, guidUser=:guidUser, idUtilisateurF= :idUtilisateurF, auteur=:auteur, source=:source WHERE idDocument = $search->idDocument");
                } else {
                    $search = false;
                    //CREATE
                    $numeroDoc = 'DOC' . date("dmYHis") . "$idOP" . $exp->idUtilisateur;
                    $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");
                    $db->bind("publie",  "0", null);
                    $db->bind("numeroDocument", $numeroDoc, null);
                    $db->bind("createDate",  date("Y-m-d H:i:s"), null);
                }
                $db->bind("source", "EXTRA", null);
                $db->bind("nomDocument", $file, null);
                $db->bind("urlDocument", $file, null);
                $db->bind("commentaire", "", null);
                $db->bind("guidHistory", null, null);
                $db->bind("typeFichier", "Adobe Acrobat Document", null);
                $db->bind("size", null, null);
                $db->bind("guidUser", $exp->numeroContact, null);
                $db->bind("idUtilisateurF", $exp->idUtilisateur, null);
                $db->bind("auteur", $exp->fullName, null);
                if ($db->execute()) {
                    if ($search == false) {
                        $document =  findItemByColumn("wbcc_document", "numeroDocument", $numeroDoc);
                        $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($idOP,$document->idDocument)");
                        $db->execute();
                    }
                }
            }
        }

        echo json_encode("1");
    }

    if ($action == "findRTByOP") {
        $id = $_GET['idOP'];
        $db->query("SELECT * FROM   wbcc_opportunity o, wbcc_releve_technique r, wbcc_appartement_contact ac, wbcc_contact c, wbcc_appartement a, wbcc_immeuble i WHERE  o.idOpportunity = r.idOpportunityF AND o.idAppartement=a.idApp AND o.idContactClient = c.idContact  AND a.idImmeubleF = i.idImmeuble  AND r.idOpportunityF = $id GROUP BY r.idOpportunityF  LIMIT 1");
        $data = $db->single();
        if (empty($data)) {
            echo json_encode("null");
        } else {
            echo json_encode($data);
        }
    }

    if ($action == 'updateByPieceMobile') {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        $ok = 1;
        $editDate = date('Y-m-d H:i:s');
        $idUser = $user['idUtilisateur'];
        $nomUser = $user['prenomContact'] . " " . $user['nomContact'];


        $db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity= $idOP LIMIT 1");
        $op = $db->single();

        /**** DELETE PIECE SUPPORT AND REVETEMENT *****/
        if ($positionPiece == "debut") {
            $db->query("SELECT * FROM wbcc_rt_piece WHERE idRTF= $idRT");
            $listToDelete = $db->resultSet();
            foreach ($listToDelete as $key => $pieceDel) {
                $db->query("SELECT * FROM wbcc_rt_piece_support WHERE idRTPieceF=$pieceDel->idRTPiece");
                $listSupToDelete = $db->resultSet();

                foreach ($listSupToDelete as $key2 => $supDel) {

                    $db->query("SELECT * FROM wbcc_rt_revetement WHERE idRtPieceSupportF =$supDel->idRTPieceSupport");
                    $listRevToDelete = $db->resultSet();

                    $db->query("SELECT * FROM wbcc_rt_ouverture WHERE idRtPieceSupportF =$supDel->idRTPieceSupport");
                    $listOuvToDelete = $db->resultSet();

                    foreach ($listRevToDelete as $key3 => $revDel) {

                        $db->query("DELETE FROM wbcc_rt_revetement WHERE idRtRevetement = $revDel->idRtRevetement");
                        $db->execute();
                    }

                    foreach ($listOuvToDelete as $key3 => $ouvDel) {

                        $db->query("DELETE FROM wbcc_rt_ouverture WHERE idRtOuverture = $ouvDel->idRtOuverture");
                        $db->execute();
                    }

                    $db->query("DELETE FROM wbcc_rt_piece_support WHERE idRTPieceSupport = $supDel->idRTPieceSupport");
                    $db->execute();
                }

                $db->query("DELETE FROM wbcc_rt_piece WHERE idRTPiece= $pieceDel->idRTPiece");
                $db->execute();
            }
        }

        // SAVE PIECE
        // $libPieces = ($libPieces == "") ? $piece['nom'] : ";" . $piece['nom'];
        $idPieceF = $piece['id'];
        $idRTPiece = $piece['idRTPiece'];
        $photosPiece = ConvertTabPhotoToString($piece['fileDatas'], "RT_" . str_replace(' ', '_', $piece['nom']), $idUser, $nomUser, $idOP, ($op ? $op->name : ""));
        $commentsPhotoPiece = ConvertTabCommentaireToString($piece['textInputs']);
        $videosPiece = ConvertTabVideoToString($piece['videoDatas'], "RT_" . str_replace(' ', '_', $piece['nom']), $idUser);
        $commentsVideosPiece =  ConvertTabCommentaireToString($piece['videoInputs']);

        $pieceSearch = false;
        /**** CREATE PIECE *****/
        $Lpiece = isset($piece['longueur']) ?  $piece['longueur'] : "";
        $lpiece = isset($piece['largeur']) ?  $piece['largeur'] : "";
        $numeroRTPiece = 'PIECE' . date("dmYHis") .  $idOP . $key . $idUser;
        $db->query("INSERT INTO wbcc_rt_piece(numeroRTPiece, idRTF, numeroRTF, numeroPieceF, idPieceF, nomPiece, libellePiece, commentairePiece, photosPiece, commentsPhotosPiece, videosPiece, commentsVideosPiece, nbMurs, nbMursSinistres, nbMursNonSinistres, etatRtPiece, createDate, editDate, idUserF, longueurPiece, largeurPiece, surfacePiece, commentaireSupport, commentaireMetrePiece) VALUES(:numeroRTPiece, :idRTF, :numeroRTF, :numeroPieceF, :idPieceF, :nomPiece, :libellePiece, :commentairePiece, :photosPiece, :commentsPhotosPiece, :videosPiece, :commentsVideosPiece, :nbMurs, :nbMursSinistres, :nbMursNonSinistres, :etatRtPiece, :createDate, :editDate, :idUserF, :longueurPiece, :largeurPiece, :surfacePiece, :commentaireSupport, :commentaireMetrePiece)");
        $db->bind("numeroRTPiece", $numeroRTPiece, null);
        $db->bind("createDate", $editDate, null);
        $db->bind("idUserF", $idUser, null);
        $db->bind("idRTF", $idRT, null);
        $db->bind("numeroRTF",  $rt['numeroRT'], null);
        $db->bind("numeroPieceF", "", null);
        $db->bind("idPieceF", $idPieceF, null);
        $db->bind("nomPiece",  $piece['nom'], null);
        $db->bind("libellePiece", $piece['libelle'], null);
        $db->bind("commentairePiece",  $piece['commentaire'], null);
        $db->bind("commentaireMetrePiece",  $piece['commentaireMetrePiece'], null);
        $db->bind("commentaireSupport",  $piece['commentaireSupport'], null);
        $db->bind("photosPiece", $photosPiece, null);
        $db->bind("commentsPhotosPiece", $commentsPhotoPiece, null);
        $db->bind("videosPiece", $videosPiece, null);
        $db->bind("commentsVideosPiece", $commentsVideosPiece, null);
        $db->bind("nbMurs", $piece['nbMurs'], null);
        $db->bind("nbMursSinistres", $piece['nbMursSinistres'], null);
        $db->bind("nbMursNonSinistres", $piece['nbMursNonSinistres'], null);
        $db->bind("etatRtPiece", 1, null);
        $db->bind("editDate", $editDate, null);
        $db->bind("longueurPiece", $Lpiece, null);
        $db->bind("largeurPiece", $lpiece, null);
        $db->bind("surfacePiece", ($Lpiece != "" && $lpiece != "") ? "" : 0, null);

        if ($db->execute()) {
            $pieceSearch = findItemByColumn("wbcc_rt_piece", "numeroRTPiece", $numeroRTPiece);
            $idRTPiece = $pieceSearch->idRTPiece;
            //SAVE SUPPORT
            $supports = $piece['listSupports'];
            if (sizeof($supports) != 0) {
                foreach ($supports as $key2 => $support) {
                    $idSupportF = $support['id'];
                    $idRTPieceSupport = $support['idRTPieceSupport'];
                    $photosSupport = ConvertTabPhotoToString($support['fileDatas'], str_replace(' ', '_', $support['nom']),  $idUser, $nomUser, $idOP, ($op ? $op->name : ""));
                    $commentsPhotosSupport = ConvertTabCommentaireToString($support['textInputs']);
                    $videosSupport = ConvertTabVideoToString($support['videoDatas'], str_replace(' ', '_', $support['nom']), $idUser);
                    $commentsVideosSupport =  ConvertTabCommentaireToString($support['videoInputs']);
                    $Lsupport = isset($support['longueur']) ?  $support['longueur'] : "";
                    $lsupport = isset($support['largeur']) ?  $support['largeur'] : "";
                    $Ssupport = ($Lsupport != "" && $lsupport != "") ? "" : "0";
                    $SsupportT = "0"; // A CALCULER SUR OUVERTURE

                    /**** CREATE SUPPORT *****/
                    $numeroRTPieceSupport = 'SUP' . date("dmYHis") . $idOP . $key . $key2 . $idUser;
                    $db->query("INSERT INTO wbcc_rt_piece_support(numeroRTPieceSupport, idRTPieceF, numeroRTPieceF, idSupportF, numeroSupportF, nomSupport, libelleSupport, largeurSupport, longueurSupport, surfaceSupport, photosSupport, commentsPhotosSupport, videosSupport, commentsVideosSupport, commentaireSupport, siOuverture, etatRtPieceSupport, createDate, editDate, idUserF, estSinistre,surfaceSupportAtraiter, siDeduire, commentaireMetreSupport, commentaireOuvertures, tauxHumidite) VALUES(:numeroRTPieceSupport, :idRTPieceF, :numeroRTPieceF, :idSupportF, :numeroSupportF, :nomSupport, :libelleSupport, :largeurSupport, :longueurSupport, :surfaceSupport, :photosSupport, :commentsPhotosSupport, :videosSupport, :commentsVideosSupport, :commentaireSupport, :siOuverture, :etatRtPieceSupport, :createDate, :editDate, :idUserF, :estSinistre,:surfaceSupportAtraiter, :siDeduire, :commentaireMetreSupport, :commentaireOuvertures, :tauxHumidite)");
                    $db->bind("numeroRTPieceSupport", $numeroRTPieceSupport, null);
                    $db->bind("createDate", $editDate, null);
                    $db->bind("idUserF", $idUser, null);
                    $db->bind("idRTPieceF", $idRTPiece, null);
                    $db->bind("numeroRTPieceF", $pieceSearch->numeroRTPiece, null);
                    $db->bind("idSupportF", $support['id'], null);
                    $db->bind("numeroSupportF", "", null);
                    $db->bind("nomSupport", $support['nom'], null);
                    $db->bind("libelleSupport", $support['libelle'], null);
                    $db->bind("largeurSupport", $lsupport, null);
                    $db->bind("longueurSupport", $Lsupport, null);
                    $db->bind("surfaceSupport", $Ssupport, null);
                    $db->bind("surfaceSupportAtraiter", $SsupportT, null);
                    $db->bind("photosSupport", $photosSupport, null);
                    $db->bind("commentsPhotosSupport", $commentsPhotosSupport, null);
                    $db->bind("videosSupport", $videosSupport, null);
                    $db->bind("commentsVideosSupport", $commentsVideosSupport, null);
                    $db->bind("commentaireSupport", $support['commentaire'], null);
                    $db->bind("commentaireOuvertures", $support['commentaireOuvertures'], null);
                    $db->bind("commentaireMetreSupport", $support['commentaireMetreSupport'], null);
                    $db->bind("siOuverture", (sizeof($support['listOuvertures']) == 0 ? 0 : 1), null);
                    $db->bind("siDeduire", $support['siDeduire'], null);
                    $db->bind("etatRtPieceSupport", $support['etatSupport'], null);
                    $db->bind("editDate", $editDate, null);
                    $db->bind("estSinistre", isset($support['estSinistre']) ? ($support['estSinistre'] == "" ? 0 : $support['estSinistre']) : 0, null);
                    $db->bind("tauxHumidite", isset($support['tauxHumidite']) ? $support['tauxHumidite'] : "", null);
                    if ($db->execute()) {
                        //ENREGISTREMENT REVETEMENT
                        $RTPieceSupport = findItemByColumn("wbcc_rt_piece_support", "numeroRTPieceSupport", $numeroRTPieceSupport);
                        $idRTPieceSupport = $RTPieceSupport->idRTPieceSupport;
                        $revetements = isset($support['listRevetements']) ? $support['listRevetements'] : [];
                        foreach ($revetements as $key3 => $revetement) {
                            // $idRTRevetement = $revetement['idRTRevetement'];
                            $numeroRtRevetement = "Revet" . date("YmdHis") . $idOP . $key . $key2 . $key3 . $idUser;

                            $db->query("INSERT INTO wbcc_rt_revetement(numeroRtRevetement, idRevetementF, idRtPieceSupportF, nomRevetement, libelleRevetement, largeurRevetement, longueurRevetement, commentaireRevetement, createDate, editDate, idUserF) VALUES(:numeroRtRevetement, :idRevetementF, :idRtPieceSupportF, :nomRevetement, :libelleRevetement, :largeurRevetement, :longueurRevetement, :commentaireRevetement, :createDate, :editDate, :idUserF)");
                            $db->bind("numeroRtRevetement", $numeroRtRevetement, null);
                            $db->bind("idRevetementF", $revetement["id"], null);
                            $db->bind("idRtPieceSupportF", $idRTPieceSupport, null);
                            $db->bind("nomRevetement", $revetement["libelle"], null);
                            $db->bind("libelleRevetement", $revetement["libelle"], null);
                            $db->bind("largeurRevetement", isset($revetement["largeur"]) ? $revetement["largeur"] : "", null);
                            $db->bind("longueurRevetement", isset($revetement["longueur"]) ? $revetement["longueur"] : "", null);
                            $db->bind("commentaireRevetement", isset($revetement["commentaire"]) ? $revetement["commentaire"] : "", null);
                            $db->bind("createDate", date("Y-m-d H:i:s"), null);
                            $db->bind("editDate", date("Y-m-d H:i:s"), null);
                            $db->bind("idUserF", $idUser, null);
                            if ($db->execute()) {
                                $ok = 1;
                            } else {
                                $ok = 0;
                            }
                        }
                        //ENREGISTREMENT OUVERTURE
                        $ouvertures = isset($support['listOuvertures']) ? $support['listOuvertures'] : [];
                        foreach ($ouvertures as $key3 => $ouverture) {
                            $numeroRtOuverture = "Ouverture" . date("YmdHis") . $idOP . $key . $key2 . $key3 . $idUser;

                            $db->query("INSERT INTO `wbcc_rt_ouverture`(`numeroRtOuverture`, `idRtPieceSupportF`, `numeroRtPieceSupportF`, `nomOuverture`, `libelleOuverture`, `largeurOuverture`, `longueurOuverture`, `surfaceOuverture`, `commentaireOuverture`,  `createDate`, `editDate`, `idUserF`) VALUES(:numeroRtOuverture, :idRtPieceSupportF, :numeroRtPieceSupportF, :nomOuverture, :libelleOuverture, :largeurOuverture,:longueurOuverture, :surfaceOuverture, :commentaireOuverture, :createDate, :editDate, :idUserF)");

                            $db->bind("numeroRtOuverture", $numeroRtOuverture, null);
                            $db->bind("numeroRtPieceSupportF", $numeroRTPieceSupport, null);
                            $db->bind("idRtPieceSupportF", $idRTPieceSupport, null);
                            $db->bind("nomOuverture", $ouverture["libelle"], null);
                            $db->bind("libelleOuverture", $ouverture["libelle"], null);
                            $db->bind("longueurOuverture", $ouverture["largeur"], null);
                            $db->bind("largeurOuverture", $ouverture["hauteur"], null);
                            $db->bind("surfaceOuverture", $ouverture["surface"], null);
                            $db->bind("commentaireOuverture", "", null);
                            $db->bind("createDate", date("Y-m-d H:i:s"), null);
                            $db->bind("editDate", date("Y-m-d H:i:s"), null);
                            $db->bind("idUserF", $idUser, null);
                            if ($db->execute()) {
                                $ok = 1;
                            } else {
                                $ok = 0;
                            }
                        }
                    } else {
                        $ok = 0;
                    }
                }
            }
        } else {
            $ok = 0;
        }


        if ($positionPiece == "fin") {
            $nbBiens = 0;
            $libelleDommage = $nbPieces != 0 ? "Des Pièces" : "";
            $libelleDommage = $nbBiens != 0 ? ($libelleDommage == "" ? "Des Biens" : ($libelleDommage . ";Des Biens")) : $libelleDommage;
            $libPieces = "";
            //UPDATE RT
            $db->query("UPDATE wbcc_releve_technique SET nombrePiece=:nombrePiece, nombreBiens=:nombreBiens, libelleDommageMateriel=:libelleDommageMateriel, libellePieces=:libellePieces, editDate=:editDate WHERE idRT =:idRT");
            $db->bind("idRT", $idRT, null);
            $db->bind("nombrePiece", $nbPieces, null);
            $db->bind("nombreBiens", $nbBiens, null);
            $db->bind("libellePieces", $libPieces, null);
            $db->bind("libelleDommageMateriel",  $libelleDommage, null);
            $db->bind("editDate",  $editDate, null);
            if ($db->execute()) {
            }

            //UPDATE RV
            if (isset($idRV)) {
                $db->query("UPDATE wbcc_rendez_vous SET etatRV=1, editDate=:editDate1 WHERE idRV =:idRV");
                $db->bind("idRV", $idRV, null);
                $db->bind("editDate1",  $editDate, null);
                if ($db->execute()) {
                }
            } else {
                $db->query("UPDATE wbcc_rendez_vous SET etatRV=1, editDate=:editDate1 WHERE idOpportunityF =:idOP");
                $db->bind("idOP", $idOP, null);
                $db->bind("editDate1",  $editDate, null);
                if ($db->execute()) {
                }
            }
        }

        echo json_encode("$ok");
    }

    if ($action == "updateCausesMobile") {
        $_POST = json_decode(file_get_contents('php://input'), true);

        extract($_POST);
        $okContact = "";
        $okApp = "";
        $okRT = "";
        $okDommage = "";
        $okBien = "";

        $causeCoche = "";
        $precisionF = "";
        $precision = "";
        if (in_array("Fuite", $tabCauseCoche)) {
            $precisionF = implode(";", $tabPrecisionCocheF);
            $causeCoche .= ($causeCoche == "" ? "Fuite" : ";Fuite");
            $precision .=  ($precision == "" ? $precisionF : "}$precisionF");
        }

        $precisionD = "";
        if (in_array("Débordement", $tabCauseCoche)) {
            $precisionD = implode(";", $tabPrecisionCocheD);
            $causeCoche .=  ($causeCoche == "" ? "Débordement" : ";Débordement");
            $precision .= ($precision == "" ? $precisionD : "}$precisionD");
        }

        $precisionI = "";
        if (in_array("Infiltration", $tabCauseCoche)) {
            $precisionI = implode(";", $tabPrecisionCocheI);
            $causeCoche .=  ($causeCoche == "" ? "Infiltration" : ";Infiltration");
            $precision .= ($precision == "" ? $precisionI : "}$precisionI");
        }

        $precisionE = "";
        if (in_array("Engorgement", $tabCauseCoche)) {
            $precisionE = implode(";", $tabPrecisionCocheE);
            $causeCoche .=  ($causeCoche == "" ? "Engorgement" : ";Engorgement");
            $precision .= ($precision == "" ? $precisionE : "}$precisionE");
        }
        if ($autreCause != "" && $autreCause != null) {
            $causeCoche .=  $causeCoche != "" ? ";$autreCause" : "$autreCause";
        }

        //Update OR insert  Releve Technique
        if ($idRT != "0") {
            $db->query("UPDATE wbcc_releve_technique SET date=:date,anneeSurvenance=:anneeSurvenance,dateConstat=:dateConstat,heure=:heure,numeroBatiment=:numeroBatiment,adresse=:adresse,ville=:ville,codePostal=:codePostal,ageConstruction=:ageConstruction,quitterLieu=:quitterLieu,cause=:cause,precisionDegat=:precisionDegat,lieuDegat=:lieuDegat,precisionComplementaire=:precisionComplementaire,reparerCauseDegat=:reparerCauseDegat,rechercheFuite=:rechercheFuite,niveauEtage=:niveauEtage,dommageCorporel=:dommageCorporel,dommageMateriel=:dommageMateriel,dommageMaterielAutrePersonne=:dommageMaterielAutrePersonne,libelleDommageMateriel=:libelleDommageMateriel,nombreBiens=:nombreBiens,accessibilite=:accessibilite,localisation=:localisation,partieConcernee=:partieConcernee, idOpportunityF=:idOpp, idRVF=:idRV, idRVGuid=:idRVGuid, commentaireReleve=:commentaire, numeroOP=:numeroOP,commentaireDateInconnue=:commentaireDateInconnue, editDate=:edit, dateExpertiseSinistre=:dateExpertise, reparateurDegat=:reparateurDegat,equipementMVC=:equipementMVC,fonctionnementMVC=:fonctionnementMVC,dataAppMoisissure=:dataAppMoisissure,appMoisissureHiver=:appMoisissureHiver, nature=:nature , listDommages=:listDommages, congePreavis=:congePreavis, dateCongePreavis=:dateCongePreavis WHERE idRT=:idRT");
            $db->bind("idRT", $idRT, null);
        } else {

            $RTExist = findItemByColumn("wbcc_releve_technique", "idOpportunityF", $idOP);
            if ($RTExist) {
                $idRT = $RTExist->idRT;
                $db->query("UPDATE wbcc_releve_technique SET date=:date,anneeSurvenance=:anneeSurvenance,dateConstat=:dateConstat,heure=:heure,numeroBatiment=:numeroBatiment,adresse=:adresse,ville=:ville,codePostal=:codePostal,ageConstruction=:ageConstruction,quitterLieu=:quitterLieu,cause=:cause,precisionDegat=:precisionDegat,lieuDegat=:lieuDegat,precisionComplementaire=:precisionComplementaire,reparerCauseDegat=:reparerCauseDegat,rechercheFuite=:rechercheFuite,niveauEtage=:niveauEtage,dommageCorporel=:dommageCorporel,dommageMateriel=:dommageMateriel,dommageMaterielAutrePersonne=:dommageMaterielAutrePersonne,libelleDommageMateriel=:libelleDommageMateriel,nombreBiens=:nombreBiens,accessibilite=:accessibilite,localisation=:localisation,partieConcernee=:partieConcernee, idOpportunityF=:idOpp, idRVF=:idRV, idRVGuid=:idRVGuid, commentaireReleve=:commentaire, numeroOP=:numeroOP,commentaireDateInconnue=:commentaireDateInconnue, editDate=:edit, dateExpertiseSinistre=:dateExpertise, reparateurDegat=:reparateurDegat,equipementMVC=:equipementMVC,fonctionnementMVC=:fonctionnementMVC,dataAppMoisissure=:dataAppMoisissure,appMoisissureHiver=:appMoisissureHiver, nature=:nature , listDommages=:listDommages, congePreavis=:congePreavis, dateCongePreavis=:dateCongePreavis WHERE idRT=:idRT");
                $db->bind("idRT", $RTExist->idRT, null);
            } else {
                $numeroRT = "RT_" . date("dmYhis") . $idOP;
                $db->query("INSERT INTO wbcc_releve_technique (date,anneeSurvenance,dateConstat,heure,numeroBatiment,adresse,ville,codePostal,ageConstruction,quitterLieu,cause,precisionDegat,lieuDegat,precisionComplementaire,reparerCauseDegat,rechercheFuite,niveauEtage,dommageCorporel,dommageMateriel,dommageMaterielAutrePersonne,libelleDommageMateriel,nombreBiens,accessibilite,localisation,partieConcernee,idOpportunityF, idRVF, idRVGuid, numeroRT, commentaireReleve,  numeroOP, commentaireDateInconnue,createDate,editDate,dateExpertiseSinistre,reparateurDegat,equipementMVC,fonctionnementMVC,dataAppMoisissure,appMoisissureHiver,nature,listDommages, congePreavis, dateCongePreavis) VALUES (:date,:anneeSurvenance,:dateConstat,:heure,:numeroBatiment,:adresse,:ville,:codePostal,:ageConstruction,:quitterLieu,:cause,:precisionDegat,:lieuDegat,:precisionComplementaire,:reparerCauseDegat,:rechercheFuite,:niveauEtage,:dommageCorporel,:dommageMateriel,:dommageMaterielAutrePersonne,:libelleDommageMateriel,:nombreBiens,:accessibilite,:localisation,:partieConcernee,:idOpp,:idRV,:idRVGuid,:numeroRT, :commentaire, :numeroOP,:commentaireDateInconnue,:create,:edit,:dateExpertise,:reparateurDegat,:equipementMVC,:fonctionnementMVC,:dataAppMoisissure,:appMoisissureHiver,:nature, :listDommages,:congePreavis, :dateCongePreavis)");
                $db->bind("create", date("d-m-Y H:i"), null);
                $db->bind("numeroRT", $numeroRT, null);
            }
        }
        $reparateurDegat = isset($reparateurDegat) ? $reparateurDegat : "";
        $equipementMVC = isset($equipementMVC) ? $equipementMVC : "";
        $fonctionnementMVC = isset($fonctionnementMVC) ? $fonctionnementMVC : "";
        $dataAppMoisissure = isset($dataAppMoisissure) ? $dataAppMoisissure : "";
        $appMoisissureHiver = isset($appMoisissureHiver) ? $appMoisissureHiver : "";
        $natureSinitre = isset($natureSinitre) ? $natureSinitre : "";
        $libelleTypeDommage = "Des Pieces";

        $db->bind("edit", date("Y-m-d H:i"), null);
        $db->bind("date", $dateSinistre, null);
        $db->bind("anneeSurvenance", $anneeSurvenance, null);
        $db->bind("dateConstat", $dateSinistre != "" ? "" : $dateConstat, null);
        $db->bind("heure", date("h:i:s"), null);
        $db->bind("numeroBatiment", $batiment, null);
        $db->bind("adresse", $adresse, null);
        $db->bind("ville", $ville, null);
        $db->bind("codePostal", $codePostal, null);
        $db->bind("ageConstruction", $etatConstruction, null);
        $db->bind("quitterLieu", $quitterLieu, null);
        $db->bind("cause", $causeCoche, null);
        $db->bind("precisionDegat", $precision, null);
        $db->bind("lieuDegat", $lieuDegat, null);
        $db->bind("precisionComplementaire", $precisionComp, null);
        $db->bind("reparerCauseDegat", $reparationCause, null);
        $db->bind("rechercheFuite", $rechercheFuite, null);
        $db->bind("niveauEtage", $etage, null);
        $db->bind("dommageCorporel", $dommagesCorporels, null);
        $db->bind("dommageMateriel", $dommagesMateriels, null);
        $db->bind("dommageMaterielAutrePersonne", $dommagesMatAutresPers, null);
        $db->bind("libelleDommageMateriel", $libelleTypeDommage, null);
        $db->bind("nombreBiens", $nbBiens, null);
        $db->bind("accessibilite", $accessibilite, null);
        $db->bind("localisation", $localisation, null);
        $db->bind("partieConcernee", $partieConcerne, null);
        $db->bind("idOpp", $idOP, null);
        $db->bind("idRV", $idRV == "0" ? null : $idRV, null);
        $db->bind("idRVGuid", $idRVGuid, null);
        $db->bind("commentaire", $commentaire, null);
        $db->bind("numeroOP", $numeroOP, null);
        $db->bind("commentaireDateInconnue", $precisionDateInconnue, null);
        $db->bind("dateExpertise", date("Y-m-d H:i"), null);
        $db->bind("reparateurDegat", $reparateurDegat, null);
        $db->bind("equipementMVC", $equipementMVC, null);
        $db->bind("appMoisissureHiver", $appMoisissureHiver, null);
        $db->bind("dataAppMoisissure", $dataAppMoisissure, null);
        $db->bind("fonctionnementMVC", $fonctionnementMVC, null);
        $db->bind("nature", $natureSinistre, null);
        $db->bind("listDommages", implode(';', $tabDommagesCoche), null);
        $db->bind("congePreavis", isset($congePreavis) ? $congePreavis : "", null);
        $db->bind("dateCongePreavis", isset($dateCongePreavis) ? $dateCongePreavis : "", null);
        if ($db->execute()) {
            // echo json_encode("1");
            if ($idRT != "0") {
                echo json_encode(findItemByColumn("wbcc_releve_technique", "idRT", $idRT));
            } else {
                echo json_encode(findItemByColumn("wbcc_releve_technique", "numeroRT", $numeroRT));
            }
        } else {
            echo json_encode("0");
        }
    }

    if ($action == 'updateFinRTMobile') {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        //SAVE SIGNATURE
        $frtO = isset($frtOutlook) ? 1 : 0;
        if ($frtO != 1) {
            if ($etatRV == "0") {
                $db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity= $idOP LIMIT 1");
                $op = $db->single();
                $sign = "";
                $prenomSign = "";
                if ($signatureNom != "") {
                    if (!str_starts_with($signatureNom, "prenomNom")) {
                        $bin = base64_decode($signatureNom);
                        $im = imageCreateFromString($bin);
                        if (!$im) {
                            $prenomSign = "";
                        } else {

                            imagetruecolortopalette($im, FALSE, 5); # convert image from TC to palette
                            $bg = imagecolorat($im, 0, 0); # get the bg colour's index in palette
                            imagecolorset($im, $bg, 255, 255, 255);
                            $prenomSign = "nom_" . "$idOP.png";
                            $img_file = "../documents/delegations/signatures/$prenomSign";
                            imagepng($im, $img_file, 0);
                        }
                    } else {
                        $prenomSign = $signatureNom;
                    }
                }

                if ($signatureRV != "") {
                    if (!str_starts_with($signatureRV, "nom")) {
                        $bin = base64_decode($signatureRV);
                        $im = imageCreateFromString($bin);
                        if (!$im) {
                            $sign = "";
                        } else {
                            imagetruecolortopalette($im, FALSE, 5); # convert image from TC to palette
                            $bg = imagecolorat($im, 0, 0); # get the bg colour's index in palette
                            imagecolorset($im, $bg, 255, 255, 255);
                            $sign = "SignDeleg_" . "$idOP.png";
                            $img_file = "../documents/delegations/signatures/$sign";
                            imagepng($im, $img_file, 0);
                        }
                    } else {
                        $sign = $signatureRV;
                    }

                    $db->query("UPDATE wbcc_opportunity SET signature=:signature, dateSignature=:dateSignature WHERE idOpportunity =:idOP");
                    $db->bind("idOP", $idOP, null);
                    $db->bind("signature", $sign, null);
                    $db->bind("dateSignature", date('Y-m-d H:i:s'), null);
                    if ($db->execute()) {
                    }
                }


                //UPDATE RT
                $db->query("UPDATE wbcc_releve_technique SET nomCompletRV=:nomCompletRV, signatureRV=:signatureRV WHERE idRT =:idRT");
                $db->bind("idRT", $idRT, null);
                $db->bind("nomCompletRV", $prenomSign, null);
                $db->bind("signatureRV", $sign, null);
                if ($db->execute()) {
                }
            }

            //UPDATE RT
            $db->query("UPDATE wbcc_releve_technique SET commentaireReleve=:commentaire   WHERE idRT =:idRT");
            $db->bind("idRT", $idRT, null);
            $db->bind("commentaire", $commentaire, null);
            if ($db->execute()) {
            }
        } else {
            $db->query("UPDATE wbcc_opportunity SET frtOutlook=1 WHERE idOpportunity =:idOP");
            $db->bind("idOP", $idOP, null);
            if ($db->execute()) {
            }
        }


        //UPDATE RV
        $db->query("UPDATE wbcc_rendez_vous SET etatRV=1 AND typeRV='RTP' WHERE idRV =:idRV");
        $db->bind("idRV", $idRV, null);
        if ($db->execute()) {
        }
        $op = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
        //CLOSE
        $msg = $etatRV == "0" ? "RENDEZ-VOUS RELEVE TECHNIQUE" : ($frtO != 1 ? "REMPLISSAGE FRT" : "Indication FRT OUTLOOK");
        $type = "fd";
        // if ($op)
        {
            $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Faire FRT", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", "$msg : effectué le " . date('d/m/Y H:i:s'), "$msg : effectué le " . date('d/m/Y H:i:s'), "", "", "", "6", "", "", "", "", "", "", "", "", "", "", $frtO);

            $res = closeActivityByOPAndRegarding($idOP, $numeroOP, "$numeroOP - Faire TéléExpertise", "", $user['prenomContact'] . " " . $user['nomContact'], $user['numeroContact'], $user['idUtilisateur'], "", "Faire TELE-EXPERTISE : effectué le " . date('d/m/Y H:i:s'), "Faire TELE-EXPERTISE : effectué le " . date('d/m/Y H:i:s'), "", "", "", "2",  '');
        }

        //GENERATE RAPPORT RT
        if ($frtO != "1") {
            $fileRT = file_get_contents(URLROOT . "/public/documents/opportunite/rapportRT.php?idOp=$idOP");
            $fileRT = str_replace('"', "", $fileRT);
            //GENERATE RAPPORT
            $file = file_get_contents(URLROOT . "/public/documents/opportunite/rapportFRT.php?idOp=$idOP");
            $file = str_replace('"', "", $file);
            if ($file  != "") {
                $db->query("UPDATE wbcc_opportunity SET rapportFRT = '$file', rapportTeleExpertise='$fileRT' WHERE idOpportunity = $idOP");
                $db->execute();

                //ADD DOCUMENT TO OP
                $search = findItemByColumn("wbcc_document", "urlDocument", $file);
                if ($search) {
                    //UPDATE
                    $db->query("UPDATE wbcc_document SET nomDocument = :nomDocument, urlDocument = :urlDocument, commentaire = :commentaire,  guidHistory = :guidHistory, typeFichier=:typeFichier, size=:size, guidUser=:guidUser, idUtilisateurF= :idUtilisateurF, auteur=:auteur, source=:source, editDate=:editDate WHERE idDocument = $search->idDocument");
                } else {
                    $search = false;
                    //CREATE
                    $numeroDoc = 'DOC' . date("dmYHis") . "$idOP" .  $user['idUtilisateur'];
                    $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie, editDate) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie, :editDate)");
                    $db->bind("publie",  "0", null);
                    $db->bind("numeroDocument", $numeroDoc, null);
                    $db->bind("createDate",  date("Y-m-d H:i:s"), null);
                }
                $db->bind("editDate",  date("Y-m-d H:i:s"), null);
                $db->bind("source", "EXTRA", null);
                $db->bind("nomDocument", $file, null);
                $db->bind("urlDocument", $file, null);
                $db->bind("commentaire", "", null);
                $db->bind("guidHistory", null, null);
                $db->bind("typeFichier", "Adobe Acrobat Document", null);
                $db->bind("size", null, null);
                $db->bind("guidUser",  $user['numeroContact'], null);
                $db->bind("idUtilisateurF",  $user['idUtilisateur'], null);
                $db->bind("auteur", $user['prenomContact'] . " " . $user['nomContact'], null);
                if ($db->execute()) {
                    if ($search == false) {
                        $document =  findItemByColumn("wbcc_document", "numeroDocument", $numeroDoc);
                        $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($idOP,$document->idDocument)");
                        $db->execute();
                    }
                }

                //SEND MAIL TO GESTION
                $body = "<p style='text-align:justify'>Bonjour, 
                <br /><br />FICHE RELEVE TECHNIQUE (FRT) : $op->name
                <br /><br />Veuillez ne pas répondre à ce message envoyé automatiquement pour votre information. <br /> <br />
                <b>WBCC ASSISTANCE </b><br /> 
                <b> " . $user['prenomContact'] . " " . $user['nomContact'] . " </b>";
                $subject = "$op->name - FICHE RELEVE TECHNIQUE (FRT)";
                $cc = [];
                $tabFiles = ["/public/documents/opportunite/$file"];
                $fileNames = [$file];
                $r = new Role();
                if ($r::mailGestionWithFiles("gestion@wbcc.fr", $subject, $body,  $tabFiles, $fileNames, $cc)) {
                }
            }
        }


        echo json_encode("1");
    }

    if ($action == 'saveRecupRT') {
        $_POST = json_decode(file_get_contents('php://input'), true);
        // extract($_POST);
        $db->query("INSERT INTO wbcc_recuperation_rt (dataLong, dataJSON) VALUES (:donnees, :donnees) ");
        $db->bind("donnees", json_encode($_POST), null);
        $db->execute();

        echo json_encode("1");

        // $ok = 1;
        // $editDate = date('Y-m-d H:i:s');
        // $idUser = $user['idUtilisateur'];
        // $nomUser = $user['prenomContact'] . " " . $user['nomContact'];

        // $nbPieces = sizeof($tabPiece);
        // $nbBiens = 0;
        // $libelleDommage = $nbPieces != 0 ? "Des Pièces" : "";
        // $libelleDommage = $nbBiens != 0 ? ($libelleDommage == "" ? "Des Biens" : ($libelleDommage . ";Des Biens")) : $libelleDommage;
        // $libPieces = "";
        // $db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity= $idOP LIMIT 1");
        // $op = $db->single();
        // if (sizeof($tabPiece) != 0) {
        //     /**** DELETE PIECE SUPPORT AND REVETEMENT *****/
        //     $db->query("SELECT * FROM wbcc_rt_piece WHERE idRTF= $idRT");
        //     $listToDelete = $db->resultSet();

        //     foreach ($listToDelete as $key => $pieceDel) {

        //         $db->query("SELECT * FROM wbcc_rt_piece_support WHERE idRTPieceF=$pieceDel->idRTPiece");
        //         $listSupToDelete = $db->resultSet();

        //         foreach ($listSupToDelete as $key2 => $supDel) {

        //             $db->query("SELECT * FROM wbcc_rt_revetement WHERE idRtPieceSupportF =$supDel->idRTPieceSupport");
        //             $listRevToDelete = $db->resultSet();

        //             $db->query("SELECT * FROM wbcc_rt_ouverture WHERE idRtPieceSupportF =$supDel->idRTPieceSupport");
        //             $listOuvToDelete = $db->resultSet();

        //             foreach ($listRevToDelete as $key3 => $revDel) {
        //                 $db->query("DELETE FROM wbcc_rt_revetement WHERE idRtRevetement = $revDel->idRtRevetement");
        //                 $db->execute();
        //             }

        //             foreach ($listOuvToDelete as $key3 => $ouvDel) {
        //                 $db->query("DELETE FROM wbcc_rt_ouverture WHERE idRtOuverture = $ouvDel->idRtOuverture");
        //                 $db->execute();
        //             }

        //             $db->query("DELETE FROM wbcc_rt_piece_support WHERE idRTPieceSupport = $supDel->idRTPieceSupport");
        //             $db->execute();
        //         }

        //         $db->query("DELETE FROM wbcc_rt_piece WHERE idRTPiece= $pieceDel->idRTPiece");
        //         $db->execute();
        //     }

        //     //SAVE PIECE
        //     foreach ($tabPiece as $key => $piece) {
        //         $libPieces = ($libPieces == "") ? $piece['nom'] : ";" . $piece['nom'];
        //         $idPieceF = $piece['id'];
        //         $idRTPiece = $piece['idRTPiece'];
        //         $photosPiece = ConvertTabPhotoToString($piece['fileDatas'], str_replace(' ', '_', $piece['nom']), $idUser, $nomUser, $idOP, ($op ? $op->name : ""));
        //         $commentsPhotoPiece = ConvertTabCommentaireToString($piece['textInputs']);
        //         $videosPiece = ConvertTabVideoToString($piece['videoDatas'], str_replace(' ', '_', $piece['nom']), $idUser);
        //         $commentsVideosPiece =  ConvertTabCommentaireToString($piece['videoInputs']);

        //         $pieceSearch = false;
        //         /**** CREATE PIECE *****/
        //         $Lpiece = isset($piece['longueur']) ?  $piece['longueur'] : "";
        //         $lpiece = isset($piece['largeur']) ?  $piece['largeur'] : "";
        //         $numeroRTPiece = 'PIECE' . date("dmYHis") .  $idOP . $key . $idUser;
        //         $db->query("INSERT INTO wbcc_rt_piece(numeroRTPiece, idRTF, numeroRTF, numeroPieceF, idPieceF, nomPiece, libellePiece, commentairePiece, photosPiece, commentsPhotosPiece, videosPiece, commentsVideosPiece, nbMurs, nbMursSinistres, nbMursNonSinistres, etatRtPiece, createDate, editDate, idUserF, longueurPiece, largeurPiece, surfacePiece, commentaireSupport, commentaireMetrePiece) VALUES(:numeroRTPiece, :idRTF, :numeroRTF, :numeroPieceF, :idPieceF, :nomPiece, :libellePiece, :commentairePiece, :photosPiece, :commentsPhotosPiece, :videosPiece, :commentsVideosPiece, :nbMurs, :nbMursSinistres, :nbMursNonSinistres, :etatRtPiece, :createDate, :editDate, :idUserF, :longueurPiece, :largeurPiece, :surfacePiece, :commentaireSupport, :commentaireMetrePiece)");
        //         $db->bind("numeroRTPiece", $numeroRTPiece, null);
        //         $db->bind("createDate", $editDate, null);
        //         $db->bind("idUserF", $idUser, null);
        //         $db->bind("idRTF", $idRT, null);
        //         $db->bind("numeroRTF",  $rt['numeroRT'], null);
        //         $db->bind("numeroPieceF", "", null);
        //         $db->bind("idPieceF", $idPieceF, null);
        //         $db->bind("nomPiece",  $piece['nom'], null);
        //         $db->bind("libellePiece", $piece['libelle'], null);
        //         $db->bind("commentairePiece",  $piece['commentaire'], null);
        //         $db->bind("commentaireMetrePiece",  $piece['commentaireMetrePiece'], null);
        //         $db->bind("commentaireSupport",  $piece['commentaireSupport'], null);
        //         $db->bind("photosPiece", $photosPiece, null);
        //         $db->bind("commentsPhotosPiece", $commentsPhotoPiece, null);
        //         $db->bind("videosPiece", $videosPiece, null);
        //         $db->bind("commentsVideosPiece", $commentsVideosPiece, null);
        //         $db->bind("nbMurs", $piece['nbMurs'], null);
        //         $db->bind("nbMursSinistres", $piece['nbMursSinistres'], null);
        //         $db->bind("nbMursNonSinistres", $piece['nbMursNonSinistres'], null);
        //         $db->bind("etatRtPiece", 1, null);
        //         $db->bind("editDate", $editDate, null);
        //         $db->bind("longueurPiece", $Lpiece, null);
        //         $db->bind("largeurPiece", $lpiece, null);
        //         $db->bind("surfacePiece", ($Lpiece != "" && $lpiece != "") ? "" : 0, null);

        //         if ($db->execute()) {
        //             $pieceSearch = findItemByColumn("wbcc_rt_piece", "numeroRTPiece", $numeroRTPiece);
        //             $idRTPiece = $pieceSearch->idRTPiece;
        //             //SAVE SUPPORT
        //             $supports = $piece['listSupports'];
        //             if (sizeof($supports) != 0) {
        //                 foreach ($supports as $key2 => $support) {
        //                     $idRTPieceSupport = $support['idRTPieceSupport'];

        //                     $libSupport = $support['libelle'];
        //                     $db->query("SELECT * FROM wbcc_rt_piece_support WHERE libelleSupport='$libSupport' AND idRTPieceF=$idRTPiece LIMIT 1 ");
        //                     $sup =  $db->single();
        //                     if ($sup) {
        //                     } else {
        //                         $idSupportF = $support['id'];
        //                         $photosSupport = ConvertTabPhotoToString($support['fileDatas'], str_replace(' ', '_', $support['nom']),  $idUser, $nomUser, $idOP, ($op ? $op->name : ""));
        //                         $commentsPhotosSupport = ConvertTabCommentaireToString($support['textInputs']);
        //                         $videosSupport = ConvertTabVideoToString($support['videoDatas'], str_replace(' ', '_', $support['nom']), $idUser);
        //                         $commentsVideosSupport =  ConvertTabCommentaireToString($support['videoInputs']);
        //                         $Lsupport = isset($support['longueur']) ?  $support['longueur'] : "";
        //                         $lsupport = isset($support['largeur']) ?  $support['largeur'] : "";
        //                         $Ssupport = ($Lsupport != "" && $lsupport != "") ? "" : "0";
        //                         $SsupportT = "0"; // A CALCULER SUR OUVERTURE

        //                         /**** CREATE SUPPORT *****/
        //                         $numeroRTPieceSupport = 'SUP' . date("dmYHis") . $idOP . $key . $key2 . $idUser;
        //                         $db->query("INSERT INTO wbcc_rt_piece_support(numeroRTPieceSupport, idRTPieceF, numeroRTPieceF, idSupportF, numeroSupportF, nomSupport, libelleSupport, largeurSupport, longueurSupport, surfaceSupport, photosSupport, commentsPhotosSupport, videosSupport, commentsVideosSupport, commentaireSupport, siOuverture, etatRtPieceSupport, createDate, editDate, idUserF, estSinistre,surfaceSupportAtraiter, siDeduire, commentaireMetreSupport, commentaireOuvertures, tauxHumidite) VALUES(:numeroRTPieceSupport, :idRTPieceF, :numeroRTPieceF, :idSupportF, :numeroSupportF, :nomSupport, :libelleSupport, :largeurSupport, :longueurSupport, :surfaceSupport, :photosSupport, :commentsPhotosSupport, :videosSupport, :commentsVideosSupport, :commentaireSupport, :siOuverture, :etatRtPieceSupport, :createDate, :editDate, :idUserF, :estSinistre,:surfaceSupportAtraiter, :siDeduire, :commentaireMetreSupport, :commentaireOuvertures, :tauxHumidite)");
        //                         $db->bind("numeroRTPieceSupport", $numeroRTPieceSupport, null);
        //                         $db->bind("createDate", $editDate, null);
        //                         $db->bind("idUserF", $idUser, null);
        //                         $db->bind("idRTPieceF", $idRTPiece, null);
        //                         $db->bind("numeroRTPieceF", $pieceSearch->numeroRTPiece, null);
        //                         $db->bind("idSupportF", $support['id'], null);
        //                         $db->bind("numeroSupportF", "", null);
        //                         $db->bind("nomSupport", $support['nom'], null);
        //                         $db->bind("libelleSupport", $support['libelle'], null);
        //                         $db->bind("largeurSupport", $lsupport, null);
        //                         $db->bind("longueurSupport", $Lsupport, null);
        //                         $db->bind("surfaceSupport", $Ssupport, null);
        //                         $db->bind("surfaceSupportAtraiter", $SsupportT, null);
        //                         $db->bind("photosSupport", $photosSupport, null);
        //                         $db->bind("commentsPhotosSupport", $commentsPhotosSupport, null);
        //                         $db->bind("videosSupport", $videosSupport, null);
        //                         $db->bind("commentsVideosSupport", $commentsVideosSupport, null);
        //                         $db->bind("commentaireSupport", $support['commentaire'], null);
        //                         $db->bind("commentaireOuvertures", $support['commentaireOuvertures'], null);
        //                         $db->bind("commentaireMetreSupport", $support['commentaireMetreSupport'], null);
        //                         $db->bind("siOuverture", (sizeof($support['listOuvertures']) == 0 ? 0 : 1), null);
        //                         $db->bind("siDeduire", $support['siDeduire'], null);
        //                         $db->bind("etatRtPieceSupport", $support['etatSupport'], null);
        //                         $db->bind("editDate", $editDate, null);
        //                         $db->bind("estSinistre", isset($support['estSinistre']) ? ($support['estSinistre'] == "" ? 0 : $support['estSinistre']) : 0, null);
        //                         $db->bind("tauxHumidite", isset($support['tauxHumidite']) ? $support['tauxHumidite'] : "", null);
        //                         if ($db->execute()) {
        //                             //ENREGISTREMENT REVETEMENT
        //                             $RTPieceSupport = findItemByColumn("wbcc_rt_piece_support", "numeroRTPieceSupport", $numeroRTPieceSupport);
        //                             $idRTPieceSupport = $RTPieceSupport->idRTPieceSupport;
        //                             $revetements = isset($support['listRevetements']) ? $support['listRevetements'] : [];
        //                             foreach ($revetements as $key3 => $revetement) {
        //                                 // $idRTRevetement = $revetement['idRTRevetement'];
        //                                 $numeroRtRevetement = "Revet" . date("YmdHis") . $idOP . $key . $key2 . $key3 . $idUser;

        //                                 $db->query("INSERT INTO wbcc_rt_revetement(numeroRtRevetement, idRevetementF, idRtPieceSupportF, nomRevetement, libelleRevetement, largeurRevetement, longueurRevetement, surfaceRevetement, commentaireRevetement, surfaceOuvertureRevetement, surfaceATraiterRevetement, siATraiterRevetement, siOuvertureRevetement, siDeduireRevetement,   createDate, editDate, idUserF) VALUES(:numeroRtRevetement, :idRevetementF, :idRtPieceSupportF, :nomRevetement, :libelleRevetement, :largeurRevetement, :longueurRevetement, :surfaceRevetement, :commentaireRevetement, :surfaceOuvertureRevetement, :surfaceATraiterRevetement, :siATraiterRevetement, :siOuvertureRevetement, :siDeduireRevetement, :createDate, :editDate, :idUserF)");
        //                                 $db->bind("numeroRtRevetement", $numeroRtRevetement, null);
        //                                 $db->bind("idRevetementF", $revetement["id"], null);
        //                                 $db->bind("idRtPieceSupportF", $idRTPieceSupport, null);
        //                                 $db->bind("nomRevetement", $revetement["libelle"], null);
        //                                 $db->bind("libelleRevetement", $revetement["libelle"], null);
        //                                 $db->bind("largeurRevetement", isset($revetement["largeur"]) ? $revetement["largeur"] : "", null);
        //                                 $db->bind("longueurRevetement", isset($revetement["longueur"]) ? $revetement["longueur"] : "", null);
        //                                 $db->bind("surfaceRevetement", isset($revetement["surface"]) ? $revetement["surface"] : "", null);
        //                                 $db->bind("commentaireRevetement", isset($revetement["commentaire"]) ? $revetement["commentaire"] : "", null);
        //                                 $db->bind("surfaceOuvertureRevetement", isset($revetement["surfaceTotalOuverture"]) ? $revetement["surfaceTotalOuverture"] : "", null);
        //                                 $db->bind("surfaceATraiterRevetement", isset($revetement["surfaceATraiter"]) ? $revetement["surfaceATraiter"] : "", null);
        //                                 $db->bind("siATraiterRevetement", isset($revetement["siATraiter"]) ? $revetement["siATraiter"] : 0, null);
        //                                 $db->bind("siOuvertureRevetement", isset($revetement["siOuvertureRevetement"]) ? $revetement["siOuvertureRevetement"] : 0, null);
        //                                 $db->bind("siDeduireRevetement", isset($revetement["siDeduireRevetement"]) ? $revetement["siDeduireRevetement"] : 0, null);
        //                                 $db->bind("createDate", date("Y-m-d H:i:s"), null);
        //                                 $db->bind("editDate", date("Y-m-d H:i:s"), null);
        //                                 $db->bind("idUserF", $idUser, null);
        //                                 if ($db->execute()) {
        //                                     //save ouverture revetment
        //                                     $RTRevetement = findItemByColumn("wbcc_rt_revetement", "numeroRtRevetement", $numeroRtRevetement);
        //                                     $idRtRevetement = $RTRevetement->idRtRevetement;
        //                                     $ouverturesrRevetement = isset($revetement["listOuverturesRevetements"])  ? $revetement["listOuverturesRevetements"] : [];
        //                                     if (sizeof($ouverturesrRevetement) != 0) {
        //                                         foreach ($ouverturesrRevetement as $key4 => $ouvRev) {
        //                                             $ouv = false;
        //                                             $ouv = findItemByColumn("wbcc_ouverture_bordereau", "nomOuvertureB", $ouvRev["nom"]);
        //                                             $numeroRtRevetementOuverture = "RevOuv" . date("YmdHis") . $idOP . $key . $key2 . $key3 . $key4 . $idUser;

        //                                             $db->query("INSERT INTO wbcc_rt_revetement_ouverture( numeroRtRevetementOuverture, idRevetementF, idOuvertureF, libelleRevetementOuverture, nomRevetementOuverture, longueurRevetementOuverture, largeurRevetementOuverture, surfaceRevetementOuverture, commentaireRevetementOuverture,  createDate, editDate, idUserF) VALUES (:numeroRtRevetementOuverture, :idRevetementF, :idOuvertureF, :libelleRevetementOuverture, :nomRevetementOuverture, :longueurRevetementOuverture, :largeurRevetementOuverture, :surfaceRevetementOuverture, :commentaireRevetementOuverture,  :createDate, :editDate, :idUserF)");

        //                                             $db->bind("numeroRtRevetementOuverture", $numeroRtRevetementOuverture, null);
        //                                             $db->bind("idRevetementF", $idRtRevetement, null);
        //                                             $db->bind("idOuvertureF", $ouv ? $ouv->idOuvertureB : null, null);
        //                                             $db->bind("libelleRevetementOuverture", $ouvRev["libelle"], null);
        //                                             $db->bind("nomRevetementOuverture", $ouvRev["nom"], null);
        //                                             $db->bind("longueurRevetementOuverture", $ouvRev["largeur"], null);
        //                                             $db->bind("largeurRevetementOuverture", $ouvRev["hauteur"], null);
        //                                             $db->bind("surfaceRevetementOuverture", $ouvRev["surface"], null);
        //                                             $db->bind("commentaireRevetementOuverture", "", null);
        //                                             $db->bind("createDate", date("Y-m-d H:i:s"), null);
        //                                             $db->bind("editDate", date("Y-m-d H:i:s"), null);
        //                                             $db->bind("idUserF", $idUser, null);
        //                                             if ($db->execute()) {
        //                                                 $ok = 1;
        //                                             } else {
        //                                                 $ok = 0;
        //                                             }
        //                                         }
        //                                     } else {
        //                                         $ok = 1;
        //                                     }
        //                                 } else {
        //                                     $ok = 0;
        //                                 }
        //                             }
        //                             //ENREGISTREMENT OUVERTURE
        //                             $ouvertures = isset($support['listOuvertures']) ? $support['listOuvertures'] : [];
        //                             foreach ($ouvertures as $key3 => $ouverture) {
        //                                 $numeroRtOuverture = "Ouverture" . date("YmdHis") . $idOP . $key . $key2 . $key3 . $idUser;

        //                                 $db->query("INSERT INTO `wbcc_rt_ouverture`(`numeroRtOuverture`, `idRtPieceSupportF`, `numeroRtPieceSupportF`, `nomOuverture`, `libelleOuverture`, `largeurOuverture`, `longueurOuverture`, `surfaceOuverture`, `commentaireOuverture`,  `createDate`, `editDate`, `idUserF`) VALUES(:numeroRtOuverture, :idRtPieceSupportF, :numeroRtPieceSupportF, :nomOuverture, :libelleOuverture, :largeurOuverture,:longueurOuverture, :surfaceOuverture, :commentaireOuverture, :createDate, :editDate, :idUserF)");

        //                                 $db->bind("numeroRtOuverture", $numeroRtOuverture, null);
        //                                 $db->bind("numeroRtPieceSupportF", $numeroRTPieceSupport, null);
        //                                 $db->bind("idRtPieceSupportF", $idRTPieceSupport, null);
        //                                 $db->bind("nomOuverture", isset($ouverture["nom"]) ? $ouverture["nom"] : $ouverture["libelle"], null);
        //                                 $db->bind("libelleOuverture", $ouverture["libelle"], null);
        //                                 $db->bind("longueurOuverture", $ouverture["largeur"], null);
        //                                 $db->bind("largeurOuverture", $ouverture["hauteur"], null);
        //                                 $db->bind("surfaceOuverture", $ouverture["surface"], null);
        //                                 $db->bind("commentaireOuverture", "", null);
        //                                 $db->bind("createDate", date("Y-m-d H:i:s"), null);
        //                                 $db->bind("editDate", date("Y-m-d H:i:s"), null);
        //                                 $db->bind("idUserF", $idUser, null);
        //                                 if ($db->execute()) {
        //                                     $ok = 1;
        //                                 } else {
        //                                     $ok = 0;
        //                                 }
        //                             }
        //                         } else {
        //                             $ok = 0;
        //                         }
        //                     }
        //                 }
        //             }
        //         } else {
        //             $ok = 0;
        //         }
        //     }

        //     //UPDATE RT
        //     $db->query("UPDATE wbcc_releve_technique SET nombrePiece=:nombrePiece, nombreBiens=:nombreBiens, libelleDommageMateriel=:libelleDommageMateriel, libellePieces=:libellePieces, editDate=:editDate WHERE idRT =:idRT");
        //     $db->bind("idRT", $idRT, null);
        //     $db->bind("nombrePiece", $nbPieces, null);
        //     $db->bind("nombreBiens", $nbBiens, null);
        //     $db->bind("libellePieces", $libPieces, null);
        //     $db->bind("libelleDommageMateriel",  $libelleDommage, null);
        //     $db->bind("editDate",  $editDate, null);
        //     if ($db->execute()) {
        //     }

        //     //UPDATE RV
        //     if (isset($idRV)) {
        //         $db->query("UPDATE wbcc_rendez_vous SET etatRV=1, editDate=:editDate1 WHERE idRV =:idRV");
        //         $db->bind("idRV", $idRV, null);
        //         $db->bind("editDate1",  $editDate, null);
        //         if ($db->execute()) {
        //         }
        //     } else {
        //         $db->query("UPDATE wbcc_rendez_vous SET etatRV=1, editDate=:editDate1 WHERE idOpportunityF =:idOP");
        //         $db->bind("idOP", $idOP, null);
        //         $db->bind("editDate1",  $editDate, null);
        //         if ($db->execute()) {
        //         }
        //     }
        // }
        // echo json_encode("$ok");
    }

    if ($action == 'updateRTMobile') {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        $rf = false;
        $ok = 1;
        $editDate = date('Y-m-d H:i:s');
        $idUser = $user['idUtilisateur'];
        $nomUser = $user['prenomContact'] . " " . $user['nomContact'];

        $nbPieces = sizeof($tabPiece);
        $nbBiens = 0;
        $libelleDommage = $nbPieces != 0 ? "Des Pièces" : "";
        $libelleDommage = $nbBiens != 0 ? ($libelleDommage == "" ? "Des Biens" : ($libelleDommage . ";Des Biens")) : $libelleDommage;
        $libPieces = "";
        $db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity= $idOP LIMIT 1");
        $op = $db->single();
        $rt2 = findItemByColumn("wbcc_releve_technique", "idOpportunityF", $idOP);
        if ($rt2 && $rt2->nature != null && $rt2->nature != "" && $rt2->nature == "Dégâts des eaux") {
            $rf = true;
        }
        if (sizeof($tabPiece) != 0) {
            /**** DELETE PIECE SUPPORT AND REVETEMENT *****/
            $db->query("SELECT * FROM wbcc_rt_piece WHERE idRTF= $idRT");
            $listToDelete = $db->resultSet();

            foreach ($listToDelete as $key => $pieceDel) {

                $db->query("SELECT * FROM wbcc_rt_piece_support WHERE idRTPieceF=$pieceDel->idRTPiece");
                $listSupToDelete = $db->resultSet();

                foreach ($listSupToDelete as $key2 => $supDel) {

                    $db->query("SELECT * FROM wbcc_rt_revetement WHERE idRtPieceSupportF =$supDel->idRTPieceSupport");
                    $listRevToDelete = $db->resultSet();

                    $db->query("SELECT * FROM wbcc_rt_ouverture WHERE idRtPieceSupportF =$supDel->idRTPieceSupport");
                    $listOuvToDelete = $db->resultSet();

                    foreach ($listRevToDelete as $key3 => $revDel) {
                        $db->query("DELETE FROM wbcc_rt_revetement WHERE idRtRevetement = $revDel->idRtRevetement");
                        $db->execute();
                    }

                    foreach ($listOuvToDelete as $key3 => $ouvDel) {
                        $db->query("DELETE FROM wbcc_rt_ouverture WHERE idRtOuverture = $ouvDel->idRtOuverture");
                        $db->execute();
                    }

                    $db->query("DELETE FROM wbcc_rt_piece_support WHERE idRTPieceSupport = $supDel->idRTPieceSupport");
                    $db->execute();
                }

                $db->query("DELETE FROM wbcc_rt_piece WHERE idRTPiece= $pieceDel->idRTPiece");
                $db->execute();
            }

            //SAVE PIECE
            foreach ($tabPiece as $key => $piece) {
                $libPieces = ($libPieces == "") ? $piece['nom'] : ";" . $piece['nom'];
                $idPieceF = $piece['id'];
                $idRTPiece = $piece['idRTPiece'];
                $photosPiece = ConvertTabPhotoToString($piece['fileDatas'], "RT_" . str_replace(' ', '_', $piece['nom']), $idUser, $nomUser, $idOP, ($op ? $op->name : ""));
                $commentsPhotoPiece = ConvertTabCommentaireToString($piece['textInputs']);
                $videosPiece = ConvertTabVideoToString($piece['videoDatas'], "RT_" . str_replace(' ', '_', $piece['nom']), $idUser);
                $commentsVideosPiece =  ConvertTabCommentaireToString($piece['videoInputs']);

                $pieceSearch = false;
                /**** CREATE PIECE *****/
                $Lpiece = isset($piece['longueur']) ?  $piece['longueur'] : "";
                $lpiece = isset($piece['largeur']) ?  $piece['largeur'] : "";
                $numeroRTPiece = 'PIECE' . date("dmYHis") .  $idOP . $key . $idUser;
                $db->query("INSERT INTO wbcc_rt_piece(numeroRTPiece, idRTF, numeroRTF, numeroPieceF, idPieceF, nomPiece, libellePiece, commentairePiece, photosPiece, commentsPhotosPiece, videosPiece, commentsVideosPiece, nbMurs, nbMursSinistres, nbMursNonSinistres, etatRtPiece, createDate, editDate, idUserF, longueurPiece, largeurPiece, surfacePiece, commentaireSupport, commentaireMetrePiece) VALUES(:numeroRTPiece, :idRTF, :numeroRTF, :numeroPieceF, :idPieceF, :nomPiece, :libellePiece, :commentairePiece, :photosPiece, :commentsPhotosPiece, :videosPiece, :commentsVideosPiece, :nbMurs, :nbMursSinistres, :nbMursNonSinistres, :etatRtPiece, :createDate, :editDate, :idUserF, :longueurPiece, :largeurPiece, :surfacePiece, :commentaireSupport, :commentaireMetrePiece)");
                $db->bind("numeroRTPiece", $numeroRTPiece, null);
                $db->bind("createDate", $editDate, null);
                $db->bind("idUserF", $idUser, null);
                $db->bind("idRTF", $idRT, null);
                $db->bind("numeroRTF",  $rt['numeroRT'], null);
                $db->bind("numeroPieceF", "", null);
                $db->bind("idPieceF", $idPieceF, null);
                $db->bind("nomPiece",  $piece['nom'], null);
                $db->bind("libellePiece", $piece['libelle'], null);
                $db->bind("commentairePiece",  $piece['commentaire'], null);
                $db->bind("commentaireMetrePiece",  $piece['commentaireMetrePiece'], null);
                $db->bind("commentaireSupport",  $piece['commentaireSupport'], null);
                $db->bind("photosPiece", $photosPiece, null);
                $db->bind("commentsPhotosPiece", $commentsPhotoPiece, null);
                $db->bind("videosPiece", $videosPiece, null);
                $db->bind("commentsVideosPiece", $commentsVideosPiece, null);
                $db->bind("nbMurs", $piece['nbMurs'], null);
                $db->bind("nbMursSinistres", $piece['nbMursSinistres'], null);
                $db->bind("nbMursNonSinistres", $piece['nbMursNonSinistres'], null);
                $db->bind("etatRtPiece", 1, null);
                $db->bind("editDate", $editDate, null);
                $db->bind("longueurPiece", $Lpiece, null);
                $db->bind("largeurPiece", $lpiece, null);
                $db->bind("surfacePiece", ($Lpiece != "" && $lpiece != "") ? "" : 0, null);

                if ($db->execute()) {
                    $pieceSearch = findItemByColumn("wbcc_rt_piece", "numeroRTPiece", $numeroRTPiece);
                    $idRTPiece = $pieceSearch->idRTPiece;
                    //SAVE SUPPORT
                    $supports = $piece['listSupports'];
                    if (sizeof($supports) != 0) {
                        foreach ($supports as $key2 => $support) {
                            if ($support['tauxHumidite'] >= 40) {
                                $rf = true;
                            }
                            $idRTPieceSupport = $support['idRTPieceSupport'];

                            $libSupport = $support['libelle'];
                            $db->query("SELECT * FROM wbcc_rt_piece_support WHERE libelleSupport='$libSupport' AND idRTPieceF=$idRTPiece LIMIT 1 ");
                            $sup =  $db->single();
                            if ($sup) {
                            } else {
                                $idSupportF = $support['id'];
                                $photosSupport = ConvertTabPhotoToString($support['fileDatas'], str_replace(' ', '_', $support['nom']),  $idUser, $nomUser, $idOP, ($op ? $op->name : ""));
                                $commentsPhotosSupport = ConvertTabCommentaireToString($support['textInputs']);
                                $videosSupport = ConvertTabVideoToString($support['videoDatas'], str_replace(' ', '_', $support['nom']), $idUser);
                                $commentsVideosSupport =  ConvertTabCommentaireToString($support['videoInputs']);
                                $Lsupport = isset($support['longueur']) ?  $support['longueur'] : "";
                                $lsupport = isset($support['largeur']) ?  $support['largeur'] : "";
                                $Ssupport = ($Lsupport != "" && $lsupport != "") ? "" : "0";
                                $SsupportT = "0"; // A CALCULER SUR OUVERTURE

                                /**** CREATE SUPPORT *****/
                                $numeroRTPieceSupport = 'SUP' . date("dmYHis") . $idOP . $key . $key2 . $idUser;
                                $db->query("INSERT INTO wbcc_rt_piece_support(numeroRTPieceSupport, idRTPieceF, numeroRTPieceF, idSupportF, numeroSupportF, nomSupport, libelleSupport, largeurSupport, longueurSupport, surfaceSupport, photosSupport, commentsPhotosSupport, videosSupport, commentsVideosSupport, commentaireSupport, siOuverture, etatRtPieceSupport, createDate, editDate, idUserF, estSinistre,surfaceSupportAtraiter, siDeduire, commentaireMetreSupport, commentaireOuvertures, tauxHumidite) VALUES(:numeroRTPieceSupport, :idRTPieceF, :numeroRTPieceF, :idSupportF, :numeroSupportF, :nomSupport, :libelleSupport, :largeurSupport, :longueurSupport, :surfaceSupport, :photosSupport, :commentsPhotosSupport, :videosSupport, :commentsVideosSupport, :commentaireSupport, :siOuverture, :etatRtPieceSupport, :createDate, :editDate, :idUserF, :estSinistre,:surfaceSupportAtraiter, :siDeduire, :commentaireMetreSupport, :commentaireOuvertures, :tauxHumidite)");
                                $db->bind("numeroRTPieceSupport", $numeroRTPieceSupport, null);
                                $db->bind("createDate", $editDate, null);
                                $db->bind("idUserF", $idUser, null);
                                $db->bind("idRTPieceF", $idRTPiece, null);
                                $db->bind("numeroRTPieceF", $pieceSearch->numeroRTPiece, null);
                                $db->bind("idSupportF", $support['id'], null);
                                $db->bind("numeroSupportF", "", null);
                                $db->bind("nomSupport", $support['nom'], null);
                                $db->bind("libelleSupport", $support['libelle'], null);
                                $db->bind("largeurSupport", $lsupport, null);
                                $db->bind("longueurSupport", $Lsupport, null);
                                $db->bind("surfaceSupport", $Ssupport, null);
                                $db->bind("surfaceSupportAtraiter", $SsupportT, null);
                                $db->bind("photosSupport", $photosSupport, null);
                                $db->bind("commentsPhotosSupport", $commentsPhotosSupport, null);
                                $db->bind("videosSupport", $videosSupport, null);
                                $db->bind("commentsVideosSupport", $commentsVideosSupport, null);
                                $db->bind("commentaireSupport", $support['commentaire'], null);
                                $db->bind("commentaireOuvertures", $support['commentaireOuvertures'], null);
                                $db->bind("commentaireMetreSupport", $support['commentaireMetreSupport'], null);
                                $db->bind("siOuverture", (sizeof($support['listOuvertures']) == 0 ? 0 : 1), null);
                                $db->bind("siDeduire", $support['siDeduire'], null);
                                $db->bind("etatRtPieceSupport", $support['etatSupport'], null);
                                $db->bind("editDate", $editDate, null);
                                $db->bind("estSinistre", isset($support['estSinistre']) ? ($support['estSinistre'] == "" ? 0 : $support['estSinistre']) : 0, null);
                                $db->bind("tauxHumidite", isset($support['tauxHumidite']) ? $support['tauxHumidite'] : "", null);
                                if ($db->execute()) {
                                    //ENREGISTREMENT REVETEMENT
                                    $RTPieceSupport = findItemByColumn("wbcc_rt_piece_support", "numeroRTPieceSupport", $numeroRTPieceSupport);
                                    $idRTPieceSupport = $RTPieceSupport->idRTPieceSupport;
                                    $revetements = isset($support['listRevetements']) ? $support['listRevetements'] : [];
                                    foreach ($revetements as $key3 => $revetement) {
                                        // $idRTRevetement = $revetement['idRTRevetement'];
                                        $numeroRtRevetement = "Revet" . date("YmdHis") . $idOP . $key . $key2 . $key3 . $idUser;
                                        $larg = "";
                                        $long = "";
                                        $surfa = 0;
                                        if (isset($revetement["listMesures"]) &&  sizeof($revetement["listMesures"]) != 0) {
                                            $listMesures =  $revetement['listMesures'];
                                            foreach ($listMesures as $key5 => $mesure) {
                                                $l =  $mesure['largeur'] != "" && $mesure['largeur'] != null ? round($mesure['largeur'], 2) : 0;
                                                $L =  $mesure['longueur'] != "" && $mesure['longueur'] != null ? round($mesure['longueur'], 2) : 0;
                                                $larg .= ($larg == "") ? $l : ";" . $l;
                                                $long .= ($long == "") ? $L : ";" . $L;
                                                $surfa += ($l * $L);
                                            }
                                        } else {
                                            $larg = isset($revetement["largeur"]) && $revetement["largeur"] != null && $revetement["largeur"] != "" ? round($revetement["largeur"], 2) : 0;
                                            $long = isset($revetement["longueur"]) && $revetement["longueur"] != null && $revetement["longueur"] != "" ? round($revetement["longueur"], 2) : 0;
                                            $surfa =  $larg != "" && $long != "" ? $long * $larg : 0;
                                        }

                                        $db->query("INSERT INTO wbcc_rt_revetement(numeroRtRevetement, idRevetementF, idRtPieceSupportF, nomRevetement, libelleRevetement, largeurRevetement, longueurRevetement, surfaceRevetement, commentaireRevetement, surfaceOuvertureRevetement, surfaceATraiterRevetement, siATraiterRevetement, siOuvertureRevetement, siDeduireRevetement,   createDate, editDate, idUserF) VALUES(:numeroRtRevetement, :idRevetementF, :idRtPieceSupportF, :nomRevetement, :libelleRevetement, :largeurRevetement, :longueurRevetement, :surfaceRevetement, :commentaireRevetement, :surfaceOuvertureRevetement, :surfaceATraiterRevetement, :siATraiterRevetement, :siOuvertureRevetement, :siDeduireRevetement, :createDate, :editDate, :idUserF)");
                                        $db->bind("numeroRtRevetement", $numeroRtRevetement, null);
                                        $db->bind("idRevetementF", $revetement["id"], null);
                                        $db->bind("idRtPieceSupportF", $idRTPieceSupport, null);
                                        $db->bind("nomRevetement", $revetement["libelle"], null);
                                        $db->bind("libelleRevetement", $revetement["libelle"], null);
                                        $db->bind("largeurRevetement", str_replace(",", ".", $larg), null);
                                        $db->bind("longueurRevetement", str_replace(",", ".", $long), null);
                                        $db->bind("surfaceRevetement", $surfa != "" && $surfa != 0 ? round($surfa, 2) : 0, null);
                                        $db->bind("commentaireRevetement", isset($revetement["commentaire"]) ? $revetement["commentaire"] : "", null);
                                        $db->bind("surfaceOuvertureRevetement", isset($revetement["surfaceTotalOuverture"]) ? $revetement["surfaceTotalOuverture"] : "", null);
                                        $db->bind("surfaceATraiterRevetement", isset($revetement["surfaceATraiter"]) ? $revetement["surfaceATraiter"] : "", null);
                                        $db->bind("siATraiterRevetement", isset($revetement["siATraiter"]) ? $revetement["siATraiter"] : 0, null);
                                        $db->bind("siOuvertureRevetement", isset($revetement["siOuvertureRevetement"]) ? $revetement["siOuvertureRevetement"] : 0, null);
                                        $db->bind("siDeduireRevetement", isset($revetement["siDeduireRevetement"]) ? $revetement["siDeduireRevetement"] : 0, null);
                                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                                        $db->bind("editDate", date("Y-m-d H:i:s"), null);
                                        $db->bind("idUserF", $idUser, null);
                                        if ($db->execute()) {
                                            //save ouverture revetment
                                            $RTRevetement = findItemByColumn("wbcc_rt_revetement", "numeroRtRevetement", $numeroRtRevetement);
                                            $idRtRevetement = $RTRevetement->idRtRevetement;
                                            $ouverturesrRevetement = isset($revetement["listOuverturesRevetements"])  ? $revetement["listOuverturesRevetements"] : [];
                                            if (sizeof($ouverturesrRevetement) != 0) {
                                                foreach ($ouverturesrRevetement as $key4 => $ouvRev) {
                                                    $ouv = false;
                                                    $ouv = findItemByColumn("wbcc_ouverture_bordereau", "nomOuvertureB", $ouvRev["nom"]);
                                                    $numeroRtRevetementOuverture = "RevOuv" . date("YmdHis") . $idOP . $key . $key2 . $key3 . $key4 . $idUser;

                                                    $db->query("INSERT INTO wbcc_rt_revetement_ouverture( numeroRtRevetementOuverture, idRevetementF, idOuvertureF, libelleRevetementOuverture, nomRevetementOuverture, longueurRevetementOuverture, largeurRevetementOuverture, surfaceRevetementOuverture, commentaireRevetementOuverture,  createDate, editDate, idUserF) VALUES (:numeroRtRevetementOuverture, :idRevetementF, :idOuvertureF, :libelleRevetementOuverture, :nomRevetementOuverture, :longueurRevetementOuverture, :largeurRevetementOuverture, :surfaceRevetementOuverture, :commentaireRevetementOuverture,  :createDate, :editDate, :idUserF)");

                                                    $db->bind("numeroRtRevetementOuverture", $numeroRtRevetementOuverture, null);
                                                    $db->bind("idRevetementF", $idRtRevetement, null);
                                                    $db->bind("idOuvertureF", $ouv ? $ouv->idOuvertureB : null, null);
                                                    $db->bind("libelleRevetementOuverture", $ouvRev["libelle"], null);
                                                    $db->bind("nomRevetementOuverture", $ouvRev["nom"], null);
                                                    $db->bind("longueurRevetementOuverture", $ouvRev["largeur"], null);
                                                    $db->bind("largeurRevetementOuverture", $ouvRev["hauteur"], null);
                                                    $db->bind("surfaceRevetementOuverture", $ouvRev["surface"], null);
                                                    $db->bind("commentaireRevetementOuverture", "", null);
                                                    $db->bind("createDate", date("Y-m-d H:i:s"), null);
                                                    $db->bind("editDate", date("Y-m-d H:i:s"), null);
                                                    $db->bind("idUserF", $idUser, null);
                                                    if ($db->execute()) {
                                                        $ok = 1;
                                                    } else {
                                                        $ok = 0;
                                                    }
                                                }
                                            } else {
                                                $ok = 1;
                                            }
                                        } else {
                                            $ok = 0;
                                        }
                                    }
                                    //ENREGISTREMENT OUVERTURE
                                    $ouvertures = isset($support['listOuvertures']) ? $support['listOuvertures'] : [];
                                    foreach ($ouvertures as $key3 => $ouverture) {
                                        $numeroRtOuverture = "Ouverture" . date("YmdHis") . $idOP . $key . $key2 . $key3 . $idUser;

                                        $db->query("INSERT INTO `wbcc_rt_ouverture`(`numeroRtOuverture`, `idRtPieceSupportF`, `numeroRtPieceSupportF`, `nomOuverture`, `libelleOuverture`, `largeurOuverture`, `longueurOuverture`, `surfaceOuverture`, `commentaireOuverture`,  `createDate`, `editDate`, `idUserF`) VALUES(:numeroRtOuverture, :idRtPieceSupportF, :numeroRtPieceSupportF, :nomOuverture, :libelleOuverture, :largeurOuverture,:longueurOuverture, :surfaceOuverture, :commentaireOuverture, :createDate, :editDate, :idUserF)");

                                        $db->bind("numeroRtOuverture", $numeroRtOuverture, null);
                                        $db->bind("numeroRtPieceSupportF", $numeroRTPieceSupport, null);
                                        $db->bind("idRtPieceSupportF", $idRTPieceSupport, null);
                                        $db->bind("nomOuverture", isset($ouverture["nom"]) ? $ouverture["nom"] : $ouverture["libelle"], null);
                                        $db->bind("libelleOuverture", $ouverture["libelle"], null);
                                        $db->bind("longueurOuverture", $ouverture["largeur"], null);
                                        $db->bind("largeurOuverture", $ouverture["hauteur"], null);
                                        $db->bind("surfaceOuverture", $ouverture["surface"], null);
                                        $db->bind("commentaireOuverture", "", null);
                                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                                        $db->bind("editDate", date("Y-m-d H:i:s"), null);
                                        $db->bind("idUserF", $idUser, null);
                                        if ($db->execute()) {
                                            $ok = 1;
                                        } else {
                                            $ok = 0;
                                        }
                                    }
                                } else {
                                    $ok = 0;
                                }
                            }
                        }
                    }
                } else {
                    $ok = 0;
                }
            }

            //UPDATE RT
            $db->query("UPDATE wbcc_releve_technique SET nombrePiece=:nombrePiece, nombreBiens=:nombreBiens, libelleDommageMateriel=:libelleDommageMateriel, libellePieces=:libellePieces, editDate=:editDate WHERE idRT =:idRT");
            $db->bind("idRT", $idRT, null);
            $db->bind("nombrePiece", $nbPieces, null);
            $db->bind("nombreBiens", $nbBiens, null);
            $db->bind("libellePieces", $libPieces, null);
            $db->bind("libelleDommageMateriel",  $libelleDommage, null);
            $db->bind("editDate",  $editDate, null);
            if ($db->execute()) {
            }

            //UPDATE RV
            if (isset($idRV)) {
                $db->query("UPDATE wbcc_rendez_vous SET etatRV=1, editDate=:editDate1 WHERE idRV =:idRV");
                $db->bind("idRV", $idRV, null);
                $db->bind("editDate1",  $editDate, null);
                if ($db->execute()) {
                }
            } else {
                $db->query("UPDATE wbcc_rendez_vous SET etatRV=1, editDate=:editDate1 WHERE idOpportunityF =:idOP");
                $db->bind("idOP", $idOP, null);
                $db->bind("editDate1",  $editDate, null);
                if ($db->execute()) {
                }
            }
        }
        if ($ok != 0 && $rf) {
            if (findItemByColumn("wbcc_recherche_fuite", "idOpportunityF", $idOP)) {
                echo json_encode("3");
            } else {
                if ($op->numeroLot != null && $op->numeroLot != "") {
                    if (findItemByColumn("wbcc_recherche_fuite", "numeroLotOP", $op->numeroLot)) {
                        echo json_encode("3");
                    } else {
                        echo json_encode("2");
                    }
                } else {
                    echo json_encode("2");
                }
            }
        } else {
            echo json_encode("$ok");
        }
    }

    if ($action == "getInfoRT") {
        $idRT = $_GET['idRT'];
        $db->query("SELECT * FROM wbcc_rendez_vous r, wbcc_opportunity o, wbcc_contact, wbcc_appartement a, wbcc_immeuble i, wbcc_releve_technique rt WHERE o.status = 'Open' AND typeRV='$type' AND r.idOpportunityF=o.idOpportunity AND idContactGuidF=numeroContact  AND a.idImmeubleF=i.idImmeuble AND r.idAppExtra=a.idApp AND rt.idOpportunityF = o.idOpportunity  AND rt.idRT = $idRT $req  GROUP BY o.idOpportunity LIMIT 1");
        $data = $db->single();
        if ($data) {
            echo json_encode($data);
        } else {
            echo json_encode("null");
        }
    }

    if ($action == "listeRVRT" && isset($_GET['type'])) {
        $type = $_GET['type'];
        $idUser = isset($_GET['idUser']) ? $_GET['idUser'] : "";
        $user = false;
        if ($idUser != "") {
            $user = findItemByColumn("wbcc_utilisateur", "idUtilisateur", $idUser);
        }
        $data = [];
        $req = "";
        if ($user) {
            if ($user->idUtilisateur != "653" && ($user->role == "4")) {
                $req = " AND r.idExpertF= $idUser ";
            }
        }
        // $db->query("SELECT * FROM  wbcc_opportunity_activity oa, wbcc_activity act, wbcc_opportunity o, wbcc_rendez_vous r, wbcc_appartement_contact ac, wbcc_contact c, wbcc_appartement a, wbcc_immeuble i WHERE o.idOpportunity=oa.idOpportunityF AND oa.idActivityF = act.idActivity AND act.codeActivity=6 AND o.idOpportunity = r.idOpportunityF AND r.id=ac.idAppCon AND ac.idContactF = c.idContact AND ac.idAppartementF = a.idApp AND a.idImmeubleF = i.idImmeuble AND o.status = 'Open'  $req GROUP BY r.idOpportunityF ORDER BY r.dateRV, r.heureDebut, r.expert");

        $db->query("SELECT * FROM  wbcc_opportunity o, wbcc_rendez_vous r, wbcc_contact c, wbcc_appartement a, wbcc_immeuble i WHERE  o.idOpportunity = r.idOpportunityF  AND r.idContactF = c.idContact AND r.idAppExtra = a.idApp AND a.idImmeubleF = i.idImmeuble AND o.status = 'Open'  $req GROUP BY r.idOpportunityF ORDER BY r.dateRV, r.heureDebut, r.expert");

        $data = $db->resultSet();
        $tab = [];
        $tabToday = [];
        $tabPasse = [];
        $tabFutur = [];
        $today = new DateTime(date("Y-m-d"));
        if (empty($data)) {
            echo json_encode("0");
        } else {
            foreach ($data as $key => $rv) {
                $idOP = $rv->idOpportunity;
                $db->query("SELECT  * FROM wbcc_releve_technique WHERE idOpportunityF=$idOP LIMIT 1");
                $rt = $db->single();
                $rvComplet = [];
                if ($rt) {
                    $rvComplet = ["rv" => $rv, "rt" => $rt,  "etatRV" => $rv->etatRV];
                } else {
                    $numeroRT = date('dmYHis') . $rv->idOpportunity . $key;
                    $db->query("INSERT INTO wbcc_releve_technique (date,anneeSurvenance,dateConstat,heure,numeroBatiment,adresse,ville,codePostal,ageConstruction,quitterLieu,cause,precisionDegat,lieuDegat,precisionComplementaire,reparerCauseDegat,rechercheFuite,niveauEtage,nombrePiece,nombreBiens,accessibilite,localisation,partieConcernee,idOpportunityF, idRVF, idRVGuid, numeroRT, commentaireReleve,  numeroOP, commentaireDateInconnue,createDate,editDate,dateExpertiseSinistre,reparateurDegat,equipementMVC,fonctionnementMVC,dataAppMoisissure,appMoisissureHiver,nature,listDommages, libellePieces, congePreavis, dateCongePreavis) VALUES (:date,:anneeSurvenance,:dateConstat,:heure,:numeroBatiment,:adresse,:ville,:codePostal,:ageConstruction,:quitterLieu,:cause,:precisionDegat,:lieuDegat,:precisionComplementaire,:reparerCauseDegat,:rechercheFuite,:niveauEtage,:nombrePiece,:nombreBiens,:accessibilite,:localisation,:partieConcernee,:idOpp,:idRV,:idRVGuid,:numeroRT, :commentaire, :numeroOP,:commentaireDateInconnue,:create,:edit,:dateExpertise,:reparateurDegat,:equipementMVC,:fonctionnementMVC,:dataAppMoisissure,:appMoisissureHiver,:nature, :listDommages,:libellePieces,:congePreavis, :dateCongePreavis)");
                    $db->bind("create", date("d-m-Y H:i"), null);
                    $db->bind("numeroRT", $numeroRT, null);
                    $db->bind("edit", date("d-m-Y H:i"), null);
                    $db->bind("date", "", null);
                    $db->bind("anneeSurvenance", "", null);
                    $db->bind("dateConstat", "", null);
                    $db->bind("heure", "", null, null);
                    $db->bind("numeroBatiment", $rv->batiment, null);
                    $db->bind("adresse", $rv->adresse, null);
                    $db->bind("ville", $rv->ville, null);
                    $db->bind("codePostal", $rv->codePostal, null);
                    $db->bind("ageConstruction", "", null);
                    $db->bind("quitterLieu", "", null);
                    $db->bind("cause", "", null);
                    $db->bind("precisionDegat", "", null);
                    $db->bind("lieuDegat", "", null);
                    $db->bind("precisionComplementaire", "", null);
                    $db->bind("reparerCauseDegat", "", null);
                    $db->bind("rechercheFuite", "", null);
                    $db->bind("niveauEtage", $rv->etage, null);
                    $db->bind("nombrePiece", "0", null);
                    $db->bind("nombreBiens", "0", null);
                    $db->bind("accessibilite", "", null);
                    $db->bind("localisation", "", null);
                    $db->bind("partieConcernee", "", null);
                    $db->bind("idOpp", $rv->idOpportunity, null);
                    $db->bind("idRV", $rv->idRV, null);
                    $db->bind("idRVGuid", $rv->numero, null);
                    $db->bind("commentaire", "", null);
                    $db->bind("numeroOP", $rv->name, null);
                    $db->bind("commentaireDateInconnue", "", null);
                    $db->bind("dateExpertise", date("Y-m-d H:i"), null);
                    $db->bind("reparateurDegat", "", null);
                    $db->bind("equipementMVC", "", null);
                    $db->bind("appMoisissureHiver", "", null);
                    $db->bind("dataAppMoisissure", "", null);
                    $db->bind("fonctionnementMVC", "", null);
                    $db->bind("nature", $rv->typeIntervention, null);
                    $db->bind("listDommages", "", null);
                    $db->bind("libellePieces", "", null);
                    $db->bind("congePreavis", "", null);
                    $db->bind("dateCongePreavis", "", null);
                    $rt = null;
                    if ($db->execute()) {
                        $rt = findItemByColumn("wbcc_releve_technique", "numeroRT", $numeroRT);
                    } else {
                        $rt = null;
                    }
                    $rvComplet = ["rv" => $rv, "rt" => $rt,  "etatRV" => "0"];
                }
                $dateRV = new DateTime(date("Y-m-d", strtotime(explode(' ', $rv->dateRV)[0])));
                if ($dateRV < $today) {
                    $tabPasse[] = $rvComplet;
                } else {
                    if ($dateRV > $today) {
                        $tabFutur[] = $rvComplet;
                    } else {
                        $tabToday[] = $rvComplet;
                    }
                }
            }

            $tab = ["today" => $tabToday, "passe" => $tabPasse, "futur" => $tabFutur];
            echo json_encode($tab);
        }
    }

    if ($action == "updateCirconstances") {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        $okContact = "";
        $okApp = "";

        //Update Contact
        $db->query("UPDATE wbcc_contact SET nomContact=:nom,prenomContact=:prenom,telContact=:telContact,emailContact=:emailContact,civiliteContact=:civiliteContact WHERE idContact=:idContact");
        $db->bind("nom", $nom, null);
        $db->bind("prenom", $prenom, null);
        $db->bind("telContact", $tel, null);
        $db->bind("emailContact", $email, null);
        $db->bind("civiliteContact", $civilite, null);
        $db->bind("idContact", $idContact, null);

        if ($db->execute()) {
            $okContact = "OK";
        } else {
            $okContact = "KO";
        }

        //Update appartement
        $db->query("UPDATE wbcc_appartement SET etage=:etage,codePorte=:codePorte,batiment=:batiment,lot=:lot WHERE idApp=:idApp");
        $db->bind("etage", $etage, null);
        $db->bind("codePorte", $porte, null);
        $db->bind("batiment", $batiment, null);
        $db->bind("lot", $lot, null);
        $db->bind("idApp", $idApp, null);

        if ($db->execute()) {
            $okApp = "OK";
        } else {
            $okApp = "KO";
        }
        if ($okApp == $okContact && $okContact == "OK") {
            echo json_encode("1");
        } else {
            echo json_encode("0");
        }
    }

    if ($action == "getListePieceRT") {
        $idRT = $_GET['idRT'];
        $db->query("SELECT  * FROM wbcc_rt_piece WHERE idRTF= $idRT");
        $datas = $db->resultSet();
        if (sizeof($datas) > 0) {
            //GET SUPPORTS
            foreach ($datas as $key => $piece) {
                $db->query("SELECT  * FROM wbcc_rt_piece_support WHERE idRTPieceF= $piece->idRTPiece ");
                $supports = $db->resultSet();
                $piece->listSupports = $supports;
                //GET REVETEMENTS AND OUVERTURES
                foreach ($supports as $key2 => $support) {
                    //GET OUVERTURES REVETEMENT
                    $db->query("SELECT  * FROM wbcc_rt_ouverture WHERE idRTPieceSupportF= $support->idRTPieceSupport ");
                    $ouvertures = $db->resultSet();
                    $support->listOuvertures = $ouvertures;

                    //GET REVETEMENTS
                    $db->query("SELECT  * FROM wbcc_rt_revetement WHERE idRTPieceSupportF= $support->idRTPieceSupport ");
                    $revetements = $db->resultSet();
                    //GET OUVERTURES REVETEMENT
                    foreach ($revetements as $key3 => $rev) {
                        $db->query("SELECT  * FROM wbcc_rt_revetement_ouverture ro WHERE  ro.idRevetementF= $rev->idRtRevetement ");
                        $ouverturesRevetements = $db->resultSet();
                        $rev->listOuverturesRevetements = $ouverturesRevetements;
                    }
                    $support->listRevetements = $revetements;
                }
            }
        }
        echo json_encode($datas);
    }
    /*** FIN MOBILE  ***/

    if ($action == "getSections") {
        extract($POST);

        if (!$idSommaire) {
            $db->query("SELECT * wbcc_sommaire WHERE idRTF=$idRT LIMIT 1");
            $idSommaire = $db->single()->idSommaire;
        }
        
        $db->query("SELECT * wbcc_section WHERE idSommaireF=$idSommaire");
        $sections = $db->execute();

        if ($sections) {
            $response['data'] = $sections;
            echo json_encode($response);
        }
    }

    if ($action == "saveInfosCompteRenduRT") {
        extract($_POST);

        //UPDATE IMAGE IMMEUBLE
        if ($idImmeuble != "0" && $photoImmeuble != "") {
            $db->query("UPDATE wbcc_immeuble SET photoImmeuble=:photoImmeuble, editDate=:editDate  WHERE idImmeuble =:idImmeuble");
            $db->bind("idImmeuble", $idImmeuble, null);
            $db->bind("photoImmeuble",  $photoImmeuble, null);
            $db->bind("editDate",  date("Y-m-d H:i:s"), null);
            $db->execute();
        }
        $db->query("UPDATE wbcc_releve_technique SET introduction=:introduction, conclusion = :conclusion, contexte = :contexte, descriptionSinistre = :descriptionSinistre, origineSinistre = :origineSinistre1, interventionInitiales = :interventionsInitiales, deroulementSeance = :deroulementSeance, editDate=:editDate, auteurDerniereModification=:auteurDerniereModification, precisionComplementaire =:precisionComplementaire,auteurCompteRenduRT=:auteurCompteRenduRT  WHERE idOpportunityF =:idOP");
        $db->bind("idOP", $idOP, null);
        $db->bind("introduction",  $introduction, null);
        $db->bind("conclusion",  $conclusion, null);
        $db->bind("contexte",  $contexte, null);
        $db->bind("descriptionSinistre",  $descriptionSinistre, null);
        $db->bind("origineSinistre1",  $origineSinistre1, null);
        $db->bind("interventionsInitiales",  $interventionsInitiales, null);
        $db->bind("deroulementSeance",  $deroulementSeance, null);
        $db->bind("editDate",  date("Y-m-d H:i:s"), null);
        $db->bind("auteurDerniereModification",  $auteur, null);
        $db->bind("auteurCompteRenduRT",  $auteur, null);
        $db->bind("precisionComplementaire",  $precisionComplementaire, null);
        if ($db->execute()) {
            if (isset($comsPiece)) {
                //UPDATE COMMENTAIRE PIECE
                foreach ($comsPiece as $key => $piece) {
                    $db->query("UPDATE wbcc_rt_piece SET commentaireMetrePiece=:commentaireMetrePiece, commentairePiece = :commentairePiece, commentaireSupport = :commentaireSupportPiece, editDate=:editDate WHERE idRTPiece =:idRTPiece");
                    $db->bind("idRTPiece", $piece['id'], null);
                    $db->bind("commentaireMetrePiece", $comsMetrePiece[$key]['commentaire'], null);
                    $db->bind("commentaireSupportPiece", $comsSupportPiece[$key]['commentaire'], null);
                    $db->bind("commentairePiece", $piece['commentaire'], null);
                    $db->bind("editDate",  date("Y-m-d H:i:s"), null);
                    $db->execute();
                }

                if (isset($comsSupport)) {
                    //UPDATE COMMENTAIRE SUPPORT
                    foreach ($comsSupport as $key => $support) {
                        $db->query("UPDATE wbcc_rt_piece_support SET commentaireMetreSupport=:commentaireMetreSupport, commentaireSupport = :commentaireSupport, editDate=:editDate WHERE idRTPieceSupport =:idRTPieceSupport");
                        $db->bind("idRTPieceSupport", $support['id'], null);
                        $db->bind("commentaireMetreSupport", isset($comsMetreSupport) ? $comsMetreSupport[$key]['commentaire'] : "", null);
                        $db->bind("commentaireSupport", $support['commentaire'], null);
                        $db->bind("editDate",  date("Y-m-d H:i:s"), null);
                        $db->execute();
                    }
                }
            }

            //GENERATE DOCUMENT RT
            if ($isGenerate == "1" || $isGenerate == "3") {
                $file = file_get_contents(URLROOT . "/public/documents/opportunite/compteRenduRT.php?idOP=$idOP");
                $file = str_replace('"', "", $file);
                echo json_encode($file);
                if ($isGenerate == "3") {
                    echo json_encode("1");
                } else {
                    if ($file != "") {
                        //UPDATE DOC TO OP
                        $db->query("UPDATE wbcc_opportunity SET compteRenduRT = :file WHERE idOpportunity = $idOP");
                        $db->bind("file", $file, null);
                        $db->execute();
                        //ADD DOCUMENT TO OP
                        $search = findItemByColumn("wbcc_document", "urlDocument", $file);
                        if ($search) {
                            //UPDATE
                            $db->query("UPDATE wbcc_document SET nomDocument = :nomDocument, urlDocument = :urlDocument, commentaire = :commentaire,  guidHistory = :guidHistory, typeFichier=:typeFichier, size=:size, guidUser=:guidUser, idUtilisateurF= :idUtilisateurF, auteur=:auteur, source=:source WHERE idDocument = $search->idDocument");
                        } else {
                            $search = false;
                            //CREATE
                            $numeroDoc = 'DOC' . date("dmYHis") . "$idOP" . 518;
                            $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");
                            $db->bind("publie",  "0", null);
                            $db->bind("numeroDocument", $numeroDoc, null);
                            $db->bind("createDate",  date("Y-m-d H:i:s"), null);
                        }
                        $db->bind("source", "EXTRA", null);
                        $db->bind("nomDocument", $file, null);
                        $db->bind("urlDocument", $file, null);
                        $db->bind("commentaire", "", null);
                        $db->bind("guidHistory", null, null);
                        $db->bind("typeFichier", "Adobe Acrobat Document", null);
                        $db->bind("size", null, null);
                        $db->bind("guidUser", $numeroAuteur, null);
                        $db->bind("idUtilisateurF", $idAuteur, null);
                        $db->bind("auteur", "$auteur", null);
                        if ($db->execute()) {
                            if ($search == false) {
                                $document =  findItemByColumn("wbcc_document", "numeroDocument", $numeroDoc);
                                $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($idOP,$document->idDocument)");
                                $db->execute();
                            }
                            echo json_encode("1");
                        }
                    } else {
                        echo json_encode("0");
                    }
                }
            } else {
                echo json_encode("1");
            }
        } else {
            echo json_encode("0");
        }
    }

    if ($action == "saveInfosExpertiseDC") {
        extract($_POST);
        $db->query("UPDATE wbcc_releve_technique SET cause=:causeSinistre, precisionComplementaire = :circonstances, commentaireSinistre = :descriptionSinistre  WHERE idRT =:idRT");
        $db->bind("idRT", $idRT, null);
        $db->bind("causeSinistre",  $causeSinistre, null);
        $db->bind("descriptionSinistre",  $descriptionSinistre, null);
        $db->bind("circonstances",  $circonstances, null);
        $res = $db->execute();
        echo json_encode($res);
    }

    if ($action == "generateRapportRTForAllTE") {
        $db->query("SELECT * FROM wbcc_opportunity WHERE teleExpertiseFaite=1 ");
        $data = $db->resultSet();
        $ok = 1;
        $i = 1;
        foreach ($data as $key => $value) {
            $file = file_get_contents(URLROOT . "/public/documents/opportunite/rapportRT.php?idOp=$value->idOpportunity&opName=$value->name");
            $file = str_replace('"', "", $file);
            $i++;
            $tabFiles = [];

            if ($file != "") {
                $tabFiles[] = "/public/rapportRT/$file";
                $fileNames[] = $file;
            }

            if ($file != "") {
                $db->query("UPDATE wbcc_opportunity SET rapportTeleExpertise = :file WHERE idOpportunity = $value->idOpportunity");
                $db->bind("file", $file, null);
                $db->execute();

                //ADD DOCUMENT TO OP
                $search = findItemByColumn("wbcc_document", "urlDocument", $file);
                if ($search) {
                    //UPDATE
                    // $db->query("UPDATE wbcc_document SET nomDocument = :nomDocument, urlDocument = :urlDocument, commentaire = :commentaire,  guidHistory = :guidHistory, typeFichier=:typeFichier, size=:size, guidUser=:guidUser, idUtilisateurF= :idUtilisateurF, auteur=:auteur, source=:source WHERE idDocument = $search->idDocument");
                } else {

                    //CREATE
                    $numeroDoc = 'DOC' . date("dmYHis") . "$value->idOpportunity" . 518;
                    $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");
                    $db->bind("publie",  "0", null);
                    $db->bind("numeroDocument", $numeroDoc, null);
                    $db->bind("createDate",  date("Y-m-d H:i:s"), null);
                    $db->bind("source", "EXTRA", null);
                    $db->bind("nomDocument", $file, null);
                    $db->bind("urlDocument", $file, null);
                    $db->bind("commentaire", "", null);
                    $db->bind("guidHistory", null, null);
                    $db->bind("typeFichier", "Adobe Acrobat Document", null);
                    $db->bind("size", null, null);
                    $db->bind("guidUser", "", null);
                    $db->bind("idUtilisateurF", 518, null);
                    $db->bind("auteur", "Compte WBCC", null);
                    if ($db->execute()) {
                        if ($search == false) {
                            $document =  findItemByColumn("wbcc_document", "numeroDocument", $numeroDoc);
                            $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($value->idOpportunity,$document->idDocument)");
                            $db->execute();
                        }
                    }
                }
            } else {
                // echo json_encode("0");
            }
        }
        echo json_encode("1");
    }

    if ($action == "generateRapportRT") {

        extract($_POST);
        $file = file_get_contents(URLROOT . "/public/documents/opportunite/rapportRT.php?idOp=$idOp&opName=$opName");
        $file = str_replace('"', "", $file);

        if ($file != "") {
            $db->query("UPDATE wbcc_opportunity SET rapportTeleExpertise = :file WHERE idOpportunity = $idOp");
            $db->bind("file", $file, null);
            $db->execute();

            //ADD DOCUMENT TO OP
            $search = findItemByColumn("wbcc_document", "urlDocument", $file);
            if ($search) {
                //UPDATE
                // $db->query("UPDATE wbcc_document SET nomDocument = :nomDocument, urlDocument = :urlDocument, commentaire = :commentaire,  guidHistory = :guidHistory, typeFichier=:typeFichier, size=:size, guidUser=:guidUser, idUtilisateurF= :idUtilisateurF, auteur=:auteur, source=:source WHERE idDocument = $search->idDocument");
            } else {

                //CREATE
                $numeroDoc = 'DOC' . date("dmYHis") . "$idOp" . 518;
                $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");
                $db->bind("publie",  "0", null);
                $db->bind("numeroDocument", $numeroDoc, null);
                $db->bind("createDate",  date("Y-m-d H:i:s"), null);
                $db->bind("source", "EXTRA", null);
                $db->bind("nomDocument", $file, null);
                $db->bind("urlDocument", $file, null);
                $db->bind("commentaire", "", null);
                $db->bind("guidHistory", null, null);
                $db->bind("typeFichier", "Adobe Acrobat Document", null);
                $db->bind("size", null, null);
                $db->bind("guidUser", "", null);
                $db->bind("idUtilisateurF", 518, null);
                $db->bind("auteur", "Compte WBCC", null);
                if ($db->execute()) {
                    if ($search == false) {
                        $document =  findItemByColumn("wbcc_document", "numeroDocument", $numeroDoc);
                        $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($idOp,$document->idDocument)");
                        $db->execute();
                    }
                }
            }
            echo json_encode("1");
        } else {
            echo json_encode("0");
        }
    }


    if ($action == "generateRapportFRTPassed") {
        $db->query("SELECT * FROM wbcc_rendez_vous r, wbcc_opportunity o WHERE o.idOpportunity= r.idOpportunityF AND etatRV = 1");
        // $db->query("SELECT * FROM wbcc_rendez_vous r, wbcc_opportunity o WHERE o.idOpportunity= r.idOpportunityF AND etatRV = 1  AND o.frtOutlook != 1 AND frtFait=1");
        $rvs = $db->resultSet();
        foreach ($rvs as $key3 => $rv) {
            $idOP = $rv->idOpportunityF;
            if ($rv->idOpportunityF != null && $rv->idExpertF != null) {
                $rt = findItemByColumn("wbcc_releve_technique", "idOpportunityF", $rv->idOpportunityF);
                $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$rv->idExpertF LIMIT 1");
                $exp = $db->single();
                // $rv = findItemByColumn("wbcc_rendez_vous", "idOpportunityF", $idOP);

                $db->query("SELECT * FROM `wbcc_rt_piece` p WHERE (p.createDate like '%2024-05-30%' OR p.createDate like '%2024-05-29%') AND p.idRTF =  $rt->idRT");
                $tab = $db->resultSet();
                $tabPhotos = [];
                if (sizeof($tab) != 0) {

                    if ($rt) {
                        $db->query("SELECT * FROM wbcc_rt_piece WHERE idRTF= $rt->idRT");
                        $pieces = $db->resultSet();
                        foreach ($pieces as $key => $piece) {
                            $photos = ($piece->photosPiece != null) ? explode(";", $piece->photosPiece) : [];
                            foreach ($photos as $key => $photo) {
                                $tabPhotos[] = $photo;
                            }
                            $db->query("SELECT * FROM wbcc_rt_piece_support WHERE idRTPieceF= $piece->idRTPiece");
                            $supports = $db->resultSet();
                            foreach ($supports as $key => $support) {
                                $photos = ($support->photosSupport != null) ? explode(";", $support->photosSupport) : [];
                                foreach ($photos as $key => $photo) {
                                    $tabPhotos[] = $photo;
                                }
                            }
                        }
                    }

                    foreach ($tabPhotos as $key => $photo) {
                        $doc =   findItemByColumn("wbcc_document", "urlDocument", $photo);
                        if ($doc) {
                        } else {
                            $numeroDocument = "DOC" . date('dmYHis') . $idOP . $key . $rv->idExpertF;
                            $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");

                            $db->bind("publie", "1", null);
                            $db->bind("source", "EXTRA", null);
                            $db->bind("numeroDocument", $numeroDocument, null);
                            $db->bind("nomDocument", $photo, null);
                            $db->bind("urlDocument", $photo, null);
                            $db->bind("commentaire", "", null);
                            $db->bind("createDate",  date('Y-m-d H:i:s'), null);
                            $db->bind("guidHistory", null, null);
                            $db->bind("typeFichier", "Image", null);
                            $db->bind("size", null, null);
                            $db->bind("guidUser", $exp->numeroContact, null);
                            $db->bind("idUtilisateurF", $rv->idExpertF, null);
                            $db->bind("auteur", $rv->expert, null);
                            if ($db->execute()) {
                                $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                                $db->query("INSERT INTO wbcc_opportunity_document( idDocumentF, idOpportunityF) VALUES (:idDocumentF, :idOpportunityF)");
                                $db->bind("idDocumentF", $document->idDocument, null);
                                $db->bind("idOpportunityF", $idOP, null);
                                $db->execute();
                            }
                        }
                    }

                    //GENERATE RAPPORT RT
                    if ($rt) {
                        $fileRT = file_get_contents(URLROOT . "/public/documents/opportunite/rapportRT.php?idOp=$idOP");
                        $fileRT = str_replace('"', "", $fileRT);
                    }

                    //GENERATE RAPPORT
                    if ($rt) {
                        $file = file_get_contents(URLROOT . "/public/documents/opportunite/rapportFRT.php?idOp=$idOP");
                        $file = str_replace('"', "", $file);
                        if ($file  != "" && $fileRT != "") {
                            $db->query("UPDATE wbcc_opportunity SET rapportFRT = '$file', rapportTeleExpertise='$fileRT' WHERE idOpportunity = $idOP");
                            $db->execute();

                            //ADD DOCUMENT TO OP
                            $search = findItemByColumn("wbcc_document", "urlDocument", $file);
                            if ($search) {
                                //UPDATE
                                $db->query("UPDATE wbcc_document SET nomDocument = :nomDocument, urlDocument = :urlDocument, commentaire = :commentaire,  guidHistory = :guidHistory, typeFichier=:typeFichier, size=:size, guidUser=:guidUser, idUtilisateurF= :idUtilisateurF, auteur=:auteur, source=:source, editDate=:editDate WHERE idDocument = $search->idDocument");
                            } else {
                                $search = false;
                                //CREATE
                                $numeroDoc = 'DOC' . date("dmYHis") . "$idOP" . $exp->idUtilisateur;
                                $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie, editDate) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie, :editDate)");
                                $db->bind("publie",  "0", null);
                                $db->bind("numeroDocument", $numeroDoc, null);
                                $db->bind("createDate",  date("Y-m-d H:i:s"), null);
                            }
                            $db->bind("source", "EXTRA", null);
                            $db->bind("nomDocument", $file, null);
                            $db->bind("urlDocument", $file, null);
                            $db->bind("commentaire", "", null);
                            $db->bind("guidHistory", null, null);
                            $db->bind("typeFichier", "Adobe Acrobat Document", null);
                            $db->bind("size", null, null);
                            $db->bind("guidUser", $exp->numeroContact, null);
                            $db->bind("idUtilisateurF", $exp->idUtilisateur, null);
                            $db->bind("auteur", $exp->fullName, null);
                            $db->bind("editDate",  date("Y-m-d H:i:s"), null);
                            if ($db->execute()) {
                                if ($search == false) {
                                    $document =  findItemByColumn("wbcc_document", "numeroDocument", $numeroDoc);
                                    $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($idOP,$document->idDocument)");
                                    $db->execute();
                                }
                            }
                        }
                    }
                }
            }
        }
        echo json_encode("1");
    }

    if ($action == "generateRapportFRT") {
        extract($_POST);
        $file = file_get_contents(URLROOT . "/public/documents/opportunite/rapportFRT.php?idOp=$idOp&opName=$opName");
        $file = str_replace('"', "", $file);
        $tabFiles = [];
        if ($file != "") {
            $tabFiles[] = "/public/documents/opportunite/$file";
            $fileNames[] = $file;
        }

        if (sizeof($tabFiles) != 0) {
            $db->query("UPDATE wbcc_opportunity SET rapportFRT = '$file' WHERE idOpportunity = $idOp");
            $db->execute();

            //ADD DOCUMENT TO OP
            $search = findItemByColumn("wbcc_document", "urlDocument", $file);
            if ($search) {
                //UPDATE
                $db->query("UPDATE wbcc_document SET nomDocument = :nomDocument, urlDocument = :urlDocument, commentaire = :commentaire,  guidHistory = :guidHistory, typeFichier=:typeFichier, size=:size, guidUser=:guidUser, idUtilisateurF= :idUtilisateurF, auteur=:auteur, source=:source WHERE idDocument = $search->idDocument");
            } else {
                $search = false;
                //CREATE
                $numeroDoc = 'DOC' . date("dmYHis") . "$idOp" . $user['idUtilisateur'];
                $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");
                $db->bind("publie",  "0", null);
                $db->bind("numeroDocument", $numeroDoc, null);
                $db->bind("createDate",  date("Y-m-d H:i:s"), null);
            }
            $db->bind("source", "EXTRA", null);
            $db->bind("nomDocument", $file, null);
            $db->bind("urlDocument", $file, null);
            $db->bind("commentaire", "", null);
            $db->bind("guidHistory", null, null);
            $db->bind("typeFichier", "Adobe Acrobat Document", null);
            $db->bind("size", null, null);
            $db->bind("guidUser", $user['numeroContact'], null);
            $db->bind("idUtilisateurF", $user['idUtilisateur'], null);
            $db->bind("auteur", $user['prenomContact'], null);
            if ($db->execute()) {
                if ($search == false) {
                    $document =  findItemByColumn("wbcc_document", "numeroDocument", $numeroDoc);
                    $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($idOp,$document->idDocument)");
                    $db->execute();
                }
                echo json_encode("1");
            }
        } else {
            echo json_encode("0");
        }
    }

    if ($action == "updateCauses") {
        if (isset($_GET['sourceEnreg'])) {
        } else {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        extract($_POST);
        $okContact = "";
        $okApp = "";
        $okRT = "";
        $okDommage = "";
        $okBien = "";

        if (!isset($_GET['sourceEnreg'])) {
            $causeCoche = implode(";", $tabCauseCoche);

            $precisionA = "";
            if (in_array("Fuite", $tabCauseCoche)) {
                $precisionA = implode(";", $tabPrecisionCocheA);
            }

            $precisionE = "";
            if (in_array("Engorgement", $tabCauseCoche)) {
                $precisionE = implode(";", $tabPrecisionCocheE);
            }

            $precisionI = "";
            if (in_array("Infiltration", $tabCauseCoche)) {
                $precisionI = implode(";", $tabPrecisionCocheI);
            }

            $precisionD = "";
            if (in_array("Débordement", $tabCauseCoche)) {
                $precisionD = implode(";", $tabPrecisionCocheD);
            }
        }
        $precision = "";


        $precision .= $precisionA . "}";


        $precision .= $precisionI . "}";


        $precision .= $precisionE . "}";


        $precision .= $precisionD . "}";



        $numeroRT = "RT_" . date("dmYhis") . $idOP;

        //Update OR insert  Releve Technique
        $RTExist = false;
        if ($idRT != "null") {
            $db->query("UPDATE wbcc_releve_technique SET date=:date,anneeSurvenance=:anneeSurvenance,dateConstat=:dateConstat,heure=:heure,numeroBatiment=:numeroBatiment,adresse=:adresse,ville=:ville,codePostal=:codePostal,ageConstruction=:ageConstruction,quitterLieu=:quitterLieu,cause=:cause,precisionDegat=:precisionDegat,lieuDegat=:lieuDegat,precisionComplementaire=:precisionComplementaire,reparerCauseDegat=:reparerCauseDegat,rechercheFuite=:rechercheFuite,niveauEtage=:niveauEtage,dommageCorporel=:dommageCorporel,dommageMateriel=:dommageMateriel,dommageMaterielAutrePersonne=:dommageMaterielAutrePersonne,libelleDommageMateriel=:libelleDommageMateriel,nombrePiece=:nombrePiece,nombreBiens=:nombreBiens,accessibilite=:accessibilite,localisation=:localisation,partieConcernee=:partieConcernee, idOpportunityF=:idOpp, idRVF=:idRV, idRVGuid=:idRVGuid, commentaireReleve=:commentaire, numeroOP=:numeroOP,commentaireDateInconnue=:commentaireDateInconnue, editDate=:edit, dateExpertiseSinistre=:dateExpertise, reparateurDegat=:reparateurDegat,equipementMVC=:equipementMVC,fonctionnementMVC=:fonctionnementMVC,dataAppMoisissure=:dataAppMoisissure,appMoisissureHiver=:appMoisissureHiver, nature=:nature , listDommages=:listDommages, libellePieces=:libellePieces, congePreavis=:congePreavis, dateCongePreavis=:dateCongePreavis WHERE idRT=:idRT");
            $db->bind("idRT", $idRT, null);
        } else {

            $RTExist = findItemByColumn("wbcc_releve_technique", "idOpportunityF", $idOP);

            if ($RTExist) {
                $idRT = $RTExist->idRT;
                $db->query("UPDATE wbcc_releve_technique SET date=:date,anneeSurvenance=:anneeSurvenance,dateConstat=:dateConstat,heure=:heure,numeroBatiment=:numeroBatiment,adresse=:adresse,ville=:ville,codePostal=:codePostal,ageConstruction=:ageConstruction,quitterLieu=:quitterLieu,cause=:cause,precisionDegat=:precisionDegat,lieuDegat=:lieuDegat,precisionComplementaire=:precisionComplementaire,reparerCauseDegat=:reparerCauseDegat,rechercheFuite=:rechercheFuite,niveauEtage=:niveauEtage,dommageCorporel=:dommageCorporel,dommageMateriel=:dommageMateriel,dommageMaterielAutrePersonne=:dommageMaterielAutrePersonne,libelleDommageMateriel=:libelleDommageMateriel,nombrePiece=:nombrePiece,nombreBiens=:nombreBiens,accessibilite=:accessibilite,localisation=:localisation,partieConcernee=:partieConcernee, idOpportunityF=:idOpp, idRVF=:idRV, idRVGuid=:idRVGuid, commentaireReleve=:commentaire, numeroOP=:numeroOP,commentaireDateInconnue=:commentaireDateInconnue, editDate=:edit, dateExpertiseSinistre=:dateExpertise, reparateurDegat=:reparateurDegat,equipementMVC=:equipementMVC,fonctionnementMVC=:fonctionnementMVC,dataAppMoisissure=:dataAppMoisissure,appMoisissureHiver=:appMoisissureHiver, nature=:nature , listDommages=:listDommages, libellePieces=:libellePieces, congePreavis=:congePreavis, dateCongePreavis=:dateCongePreavis WHERE idRT=:idRT");
                $db->bind("idRT", $RTExist->idRT, null);
            } else {
                $db->query("INSERT INTO wbcc_releve_technique (date,anneeSurvenance,dateConstat,heure,numeroBatiment,adresse,ville,codePostal,ageConstruction,quitterLieu,cause,precisionDegat,lieuDegat,precisionComplementaire,reparerCauseDegat,rechercheFuite,niveauEtage,dommageCorporel,dommageMateriel,dommageMaterielAutrePersonne,libelleDommageMateriel,nombrePiece,nombreBiens,accessibilite,localisation,partieConcernee,idOpportunityF, idRVF, idRVGuid, numeroRT, commentaireReleve,  numeroOP, commentaireDateInconnue,createDate,editDate,dateExpertiseSinistre,reparateurDegat,equipementMVC,fonctionnementMVC,dataAppMoisissure,appMoisissureHiver,nature,listDommages, libellePieces, congePreavis, dateCongePreavis) VALUES (:date,:anneeSurvenance,:dateConstat,:heure,:numeroBatiment,:adresse,:ville,:codePostal,:ageConstruction,:quitterLieu,:cause,:precisionDegat,:lieuDegat,:precisionComplementaire,:reparerCauseDegat,:rechercheFuite,:niveauEtage,:dommageCorporel,:dommageMateriel,:dommageMaterielAutrePersonne,:libelleDommageMateriel,:nombrePiece,:nombreBiens,:accessibilite,:localisation,:partieConcernee,:idOpp,:idRV,:idRVGuid,:numeroRT, :commentaire, :numeroOP,:commentaireDateInconnue,:create,:edit,:dateExpertise,:reparateurDegat,:equipementMVC,:fonctionnementMVC,:dataAppMoisissure,:appMoisissureHiver,:nature, :listDommages,:libellePieces,:congePreavis, :dateCongePreavis)");
                $db->bind("create", date("d-m-Y H:i"), null);
                $db->bind("numeroRT", $numeroRT, null);
            }
        }


        $reparateurDegat = isset($reparateurDegat) ? $reparateurDegat : "";
        $equipementMVC = isset($equipementMVC) ? $equipementMVC : "";
        $fonctionnementMVC = isset($fonctionnementMVC) ? $fonctionnementMVC : "";
        $dataAppMoisissure = isset($dataAppMoisissure) ? $dataAppMoisissure : "";
        $appMoisissureHiver = isset($appMoisissureHiver) ? $appMoisissureHiver : "";
        $natureSinitre = isset($natureSinitre) ? $natureSinitre : "";
        $libelleTypeDommage = "Des Pieces";

        $db->bind("edit", date("d-m-Y H:i"), null);
        $db->bind("date", $dateSinistre, null);
        $db->bind("anneeSurvenance", $anneeSurvenance, null);

        if (isset($_GET['sourceEnreg'])) {
            $db->bind("dateConstat", $dateConstat, null);
        } else {
            $db->bind("dateConstat", date("d-m-Y"), null);
        }
        $db->bind("heure", date("h:i:s"), null);
        $db->bind("numeroBatiment", $batiment, null);
        $db->bind("adresse", $adresse, null);
        $db->bind("ville", $ville, null);
        $db->bind("codePostal", $codePostal, null);
        $db->bind("ageConstruction", $etatConstruction, null);
        $db->bind("quitterLieu", $quitterLieu, null);
        $db->bind("cause", $causeCoche, null);
        $db->bind("precisionDegat", $precision, null);
        $db->bind("lieuDegat", $lieuDegat, null);
        $db->bind("precisionComplementaire", $precisionComp, null);
        $db->bind("reparerCauseDegat", $reparationCause, null);
        $db->bind("rechercheFuite", $rechercheFuite, null);
        $db->bind("niveauEtage", $etage, null);
        $db->bind("dommageCorporel", $dommagesCorporels, null);
        $db->bind("dommageMateriel", $dommagesMateriels, null);
        $db->bind("dommageMaterielAutrePersonne", $dommagesMatAutresPers, null);
        $db->bind("libelleDommageMateriel", $libelleTypeDommage, null);
        $db->bind("nombrePiece", isset($nbPieces) ? $nbPieces : "", null);
        $db->bind("nombreBiens", $nbBiens, null);
        $db->bind("accessibilite", $accessibilite, null);
        $db->bind("localisation", $localisation, null);
        $db->bind("partieConcernee", $partieConcerne, null);
        $db->bind("idOpp", $idOP, null);
        $db->bind("idRV", $idRV == "0" ? null : $idRV, null);
        $db->bind("idRVGuid", $idRVGuid, null);
        $db->bind("commentaire", $commentaire, null);
        $db->bind("numeroOP", $numeroOP, null);
        $db->bind("commentaireDateInconnue", $precisionDateInconnue, null);
        $db->bind("dateExpertise", date("d-m-Y H:i"), null);
        $db->bind("reparateurDegat", $reparateurDegat, null);
        $db->bind("equipementMVC", $equipementMVC, null);
        $db->bind("appMoisissureHiver", $appMoisissureHiver, null);
        $db->bind("dataAppMoisissure", $dataAppMoisissure, null);
        $db->bind("fonctionnementMVC", $fonctionnementMVC, null);
        $db->bind("nature", $natureSinitre, null);
        $db->bind("listDommages", $listDommages, null);
        $db->bind("libellePieces", isset($libellePieces) ? $libellePieces : "", null);
        $db->bind("congePreavis", isset($congePreavis) ? $congePreavis : "", null);
        $db->bind("dateCongePreavis", isset($dateCongePreavis) ? $dateCongePreavis : "", null);

        if ($db->execute()) {
            $okRT = "OK";
        } else {
            $okRT = "KO";
        }
        if ($okRT == "OK") {
            if ($RTExist || $idRT != "null") {
                $RT = findItemByColumn("wbcc_releve_technique", "idRT", $idRT);
                echo json_encode($RT);
            } else {
                $RT = findItemByColumn("wbcc_releve_technique", "numeroRT", $numeroRT);
                echo json_encode($RT);
            }
        } else {
            echo json_encode("0");
        }
    }

    if ($action == 'updateTeleExpertise') {
        if (isset($_GET['sourceEnreg'])) {
        } else {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        extract($_POST);
        $okContact = "";
        $okApp = "";
        $okRT = "";
        $okDommage = "";
        $okBien = "";

        if ($idRV != "null") {
            $db->query("UPDATE wbcc_rendez_vous SET etatRV=1 WHERE idRV=$idRV ");
            $db->execute();
        }
        $RT = findItemByColumn("wbcc_releve_technique", "numeroRT", $numeroRT);

        $documents = explode(";", $RT->documentComplement);

        if (sizeof($documents) != 0) {
            foreach ($documents as  $docBD) {
                if ($docBD != "") {
                    $ind = 0;
                    $trouve = false;

                    // extract($bienD);
                    while (!($trouve) && $ind < sizeof($fileDatasDOC)) {
                        if ($docBD == $fileDatasDOC[$ind]) {
                            $trouve = true;
                        }
                        $ind++;
                    }
                    if (!($trouve)) {
                        //Supprimer les fichiers
                        if (file_exists("../documents/releveTechnique/$docBD")) {
                            unlink("../documents/releveTechnique/$docBD");
                        }
                    }
                }
            }
        }


        //Photos & Videos Documents complementaires
        $photoDOC = "";
        $commentPhotoDOC = "";
        $photos = [];
        $comments = [];
        $i = 0;
        if (sizeof($fileDatasDOC) != 0) {
            $etat = 1;

            foreach ($fileDatasDOC as $key => $file) {
                if ($file != "") {
                    if (substr($file, 0, 8) == "DOC_CMP_") {
                        $photos[$i] = $file;
                    } else {
                        $name = date("dmYHis");
                        $bin = base64_decode($file);
                        $im = imageCreateFromString($bin);
                        if (!$im) {
                            echo json_encode("OKDOC");
                        }
                        $nom = "DOC_CMP_$i" . "$name.jpg";
                        $img_file = "../documents/releveTechnique/$nom";
                        $photos[$i] = $nom;
                        imagejpeg($im, $img_file, 50);
                    }
                    $i++;
                }
            }
        }

        $i = 0;
        if (sizeof($fileInputsDOC) != 0) {
            foreach ($fileInputsDOC as $key => $file) {
                $comments[$i] = $fileInputsDOC[$key];
                $i++;
            }
        }

        $comments = sizeof($comments) != 0 ? implode("}", $comments) : '';
        $photoDOC = sizeof($photos) != 0 ? implode(";", $photos) : '';

        $db->query("UPDATE wbcc_releve_technique SET commentaireReleve=:commentaire,documentComplement=:document,commentaireDocument=:commentDoc WHERE idRT=$idRT ");
        $db->bind("commentaire", $commentaire, null);
        $db->bind("document", $photoDOC, null);
        $db->bind("commentDoc", $comments, null);

        if ($db->execute()) {
        }

        if ($RT) {
            $idRT = $RT->idRT;
            $idRTGuid = $RT->numeroRT;

            //Suppression des dommages
            $db->query("SELECT * FROM wbcc_dommage WHERE idRTF = $idRT");
            $dommagesBD = $db->resultSet();

            if (sizeof($dommagesBD) != 0) {
                foreach ($dommagesBD as $keyBD => $dommageDB) {
                    $ind = 0;
                    $trouve = false;
                    // extract($bienD);
                    $idDommage = $dommageDB->idDommage;
                    while (!($trouve) && $ind < sizeof($tabDegat)) {
                        extract($tabDegat[$ind]);
                        if ($id != "null" && $id == $idDommage) {
                            $trouve = true;
                        }
                        $ind++;
                    }
                    if (!($trouve)) {
                        //Supprimer les fichiers
                        $files = explode(";", $dommageDB->photoSupportP);
                        foreach ($files as $f) {
                            if (file_exists("../documents/releveTechnique/$f")) {
                                unlink("../documents/releveTechnique/$f");
                            }
                        }
                        $files = explode(";", $dommageDB->photoSupportS);
                        foreach ($files as $f) {
                            if (file_exists("../documents/releveTechnique/$f")) {
                                unlink("../documents/releveTechnique/$f");
                            }
                        }

                        $mursPhotos = explode("}", $dommageDB->photoSupportM);
                        foreach ($mursPhotos as $murPhoto) {
                            $files = explode(";", $murPhoto);
                            foreach ($files as $f) {
                                if (file_exists("../documents/releveTechnique/$f")) {
                                    unlink("../documents/releveTechnique/$f");
                                }
                            }
                        }

                        $db->query("DELETE FROM wbcc_dommage WHERE idDommage = $idDommage");
                        $db->execute();
                    }
                }
            }

            //Insertion des dommages
            foreach ($tabDegat as $key => $degat) {
                //   $degat = $tabDegat[0];
                extract($degat);
                $okDG = "";

                $support = implode(";", $surfaceCoche);
                $mesureP = $longueurP . ";" . $largeurP;
                $mesureS = $longueurS . ";" . $largeurS;
                $photoSupportP = "";
                $photoSupportS = "";
                $videoSupportP = "";
                $videoSupportS = "";
                $photoSupportM = "";
                $videoSupportM = "";
                $commentPhotoSupportS = "";
                $commentPhotoSupportP = "";
                $commentVideoSupportS = "";
                $commentPhotoSupportM = "";
                $commentVideoSupportM = "";
                $commentVideoSupportP = "";

                $photos = [];
                $i = 0;

                if (sizeof($fileDatasP) != 0) {
                    $etat = 1;
                    foreach ($fileDatasP as $keyPl => $file) {
                        if ($file != "") {
                            if (substr($file, 0, 13) == "RTSP_Plafond_") {
                                $photos[$i] = $file;
                            } else {
                                $name = date("dmYHis");
                                $bin = base64_decode($file);
                                $im = imageCreateFromString($bin);
                                if (!$im) {
                                    echo json_encode("OKP");
                                }
                                $nom = "RTSP_Plafond_$keykeyPl$i" . "$name.jpg";
                                $img_file = "../documents/releveTechnique/$nom";
                                $photos[$i] = $nom;
                                imagejpeg($im, $img_file, 50);
                            }
                            $i++;
                        }
                    }
                }

                $i = 0;
                $videos = [];
                if (sizeof($videoDatasP) != 0) {
                    $etat = 1;
                    foreach ($videoDatasP as $keyPL => $file) {
                        if ($file != "") {
                            if (substr($file, 0, 13) == "RTSP_Plafond_") {
                                $videos[$i] = $file;
                            } else {
                                $name = date("dmYHis");
                                header('Content-Type: video/mp4');
                                $bin = base64_decode($file);
                                $nom = "RTSP_Plafond_V$key$keyPL$i" . "$name.mp4";
                                $img_file = "../documents/releveTechnique/$nom";
                                $videos[$i] = $nom;
                                file_put_contents($img_file, $bin);
                            }
                            $i++;
                        }
                    }
                }

                $i = 0;
                $comments = [];
                if (sizeof($fileInputsP) != 0) {
                    foreach ($fileInputsP as $keyI => $file) {
                        $comments[$i] = $fileInputsP[$keyI];
                        $i++;
                    }
                }
                $commentPhotoSupportP = sizeof($comments) != 0 ? implode("}", $comments) : '';

                $i = 0;
                $commentsVideo = [];
                if (sizeof($videoInputsP) != 0) {
                    foreach ($videoInputsP as $keyP => $file) {
                        $commentsVideo[$i] = $videoInputsP[$keyP];
                        $i++;
                    }
                }
                $commentVideoSupportP = sizeof($commentsVideo) != 0 ? implode("}", $commentsVideo) : '';

                $photoSupportP = sizeof($photos) != 0 ? implode(";", $photos) : '';
                $videoSupportP = sizeof($videos) != 0 ? implode(";", $videos) : '';

                $photos = [];
                $i = 0;
                if (sizeof($fileDatasS) != 0) {
                    $etat = 1;
                    foreach ($fileDatasS as $keyS => $file) {
                        if ($file != "") {
                            if (substr($file, 0, 9) == "RTSP_Sol_") {
                                $photos[$i] = $file;
                            } else {
                                $name = date("dmYHis");
                                $bin = base64_decode($file);
                                $im = imageCreateFromString($bin);
                                if (!$im) {
                                    echo json_encode("OKS");
                                }
                                $nom = "RTSP_Sol_$key$i$keyS" . "$name.jpg";
                                $img_file = "../documents/releveTechnique/$nom";
                                $photos[$i] = $nom;
                                imagejpeg($im, $img_file, 50);
                            }
                            $i++;
                        }
                    }
                }

                $i = 0;
                $videos = [];
                if (sizeof($videoDatasS) != 0) {
                    $etat = 1;
                    foreach ($videoDatasS as $keyS => $file) {
                        if ($file != "") {
                            if (substr($file, 0, 9) == "RTSP_Sol_") {
                                $videos[$i] = $file;
                            } else {
                                $name = date("dmYHis");
                                header('Content-Type: video/mp4');
                                $bin = base64_decode($file);
                                $nom = "RTSP_Sol_V$key$i$keyS" . "$name.mp4";
                                $img_file = "../documents/releveTechnique/$nom";
                                $videos[$i] = $nom;
                                file_put_contents($img_file, $bin);
                            }
                            $i++;
                        }
                    }
                }

                $i = 0;
                $comments = [];
                if (sizeof($fileInputsS) != 0) {
                    foreach ($fileInputsS as $keyS => $file) {
                        $comments[$i] = $fileInputsS[$keyS];
                        $i++;
                    }
                }
                $commentPhotoSupportS = sizeof($comments) != 0 ? implode("}", $comments) : '';

                $i = 0;
                $commentsVideo = [];
                if (sizeof($videoInputsS) != 0) {
                    foreach ($videoInputsS as $keyS => $file) {
                        $commentsVideo[$i] = $videoInputsS[$keyS];
                        $i++;
                    }
                }
                $commentVideoSupportS = sizeof($commentsVideo) != 0 ? implode("}", $commentsVideo) : '';

                $photoSupportS = sizeof($photos) != 0 ? implode(";", $photos) : '';
                $videoSupportS = sizeof($videos) != 0 ? implode(";", $videos) : '';
                if ($id == "null") {
                    $db->query("INSERT INTO wbcc_dommage (piece,support,revetementPlafond,revetementSol,revetementMur,mesurePlafond,mesureMur,mesureSol,nbMurs,nbMursS,nbMursNS,repMurSinistre,repOuverture,libelleOuverture,idRTF,idRTGuid, mesureSupportP, mesureSupportS, mesureSupportM,photoSupportP, photoSupportS, photoSupportM,videoSupportP,videoSupportS,videoSupportM,commentSupportP,commentSupportS,commentSupportM, commentPhotoSupportP, commentVideoSupportP, commentPhotoSupportS, commentVideoSupportS, commentPhotoSupportM, commentVideoSupportM,mesureOuverture) VALUES (:piece,:support,:revetementPlafond,:revetementSol,:revetementMur,:mesurePlafond,:mesureMur,:mesureSol,:nbMurs,:nbMursS,:nbMursNS,:repMurSinistre,:repOuverture,:libelleOuverture,:idRTF,:idRTGuid, :mesureSupportP, :mesureSupportS, :mesureSupportM, :photoSupportP, :photoSupportS, :photoSupportM, :videoSupportP, :videoSupportS, :videoSupportM, :commentSupportP, :commentSupportS, :commentSupportM, :commentPhotoSupportP, :commentVideoSupportP, :commentPhotoSupportS, :commentVideoSupportS, :commentPhotoSupportM, :commentVideoSupportM,:mesureOuverture)");
                } else {
                    $db->query("UPDATE wbcc_dommage SET piece=:piece,support=:support,revetementPlafond=:revetementPlafond,revetementSol=:revetementSol,revetementMur=:revetementMur,mesurePlafond=:mesurePlafond,mesureMur=:mesureMur,mesureSol=:mesureSol,nbMurs=:nbMurs,nbMursS=:nbMursS,nbMursNS=:nbMursNS,repMurSinistre=:repMurSinistre,repOuverture=:repOuverture,libelleOuverture=:libelleOuverture,idRTF=:idRTF,idRTGuid=:idRTGuid, mesureSupportP=:mesureSupportP, mesureSupportS=:mesureSupportS, mesureSupportM=:mesureSupportM,photoSupportP=:photoSupportP, photoSupportS=:photoSupportS, photoSupportM=:photoSupportM,videoSupportP=:videoSupportP,videoSupportS=:videoSupportS,videoSupportM=:videoSupportM,commentSupportP=:commentSupportP,commentSupportS=:commentSupportS,commentSupportM=:commentSupportM, commentPhotoSupportP=:commentPhotoSupportP, commentVideoSupportP=:commentVideoSupportP, commentPhotoSupportS=:commentPhotoSupportS, commentVideoSupportS=:commentVideoSupportS, commentPhotoSupportM=:commentPhotoSupportM, commentVideoSupportM=:commentVideoSupportM,mesureOuverture=:mesureOuverture WHERE idDommage=:id");
                    $db->bind("id", $id, null);
                }

                $db->bind("piece", $piece, null);
                $db->bind("support", $support, null);
                $db->bind("mesureSupportP", $mesureP, null);
                $db->bind("mesureSupportS", $mesureS, null);
                $db->bind("revetementPlafond", $typePlafond, null);
                $db->bind("revetementSol", $typeSol, null);
                $db->bind("mesurePlafond", implode(";", $tabLongueurPlafondCoche) . "}" . implode(";", $tabLargeurPlafondCoche), null);
                $db->bind("mesureSol", implode(";", $tabLongueurSolCoche) . "}" . implode(";", $tabLargeurSolCoche), null);
                $db->bind("nbMurs", $nbMurs, null);
                $db->bind("nbMursS", $nbMursSinistres, null);
                $db->bind("nbMursNS", $nbMursNonSinistres, null);
                $db->bind("photoSupportP", $photoSupportP, null);
                $db->bind("photoSupportS", $photoSupportS, null);
                $db->bind("videoSupportP", $videoSupportP, null);
                $db->bind("videoSupportS", $videoSupportS, null);
                $db->bind("commentPhotoSupportS", $commentPhotoSupportS, null);
                $db->bind("commentVideoSupportP", $commentVideoSupportP, null);
                $db->bind("commentVideoSupportS", $commentVideoSupportS, null);
                $db->bind("commentPhotoSupportP", $commentPhotoSupportP, null);
                $db->bind("commentSupportP", $commentaireP, null);
                $db->bind("commentSupportS", $commentaireS, null);


                $mesureM = "";
                $revetementMur = "";
                $repMurS = "";
                $repOuverture = "";
                $libelleO = "";
                $mesureRevMur = "";
                $mesureMur = "";
                $commentSupportM = "";
                $mesureOuverture = "";

                if (sizeof($murs) != 0) {
                    foreach ($murs as $keyM => $mur) {
                        extract($mur);
                        $revetementMur .= $nature . "}";
                        $repMurS .= $repMurSinistre . ";";
                        $repOuverture .= $ouvertures . ";";
                        $libelleO .=  implode(";", explode(";", $ouverturesCoche)) . "}";
                        $mesureRevMur .= implode(";", $tabLongueurMurCoche) . "]" . implode(";", $tabLargeurMurCoche) .  "}";
                        $mesureOuverture .= implode(";", $tabLongueurOuvCoche) . "]" . implode(";", $tabLargeurOuvCoche) .  "}";

                        $mesureMur .= $hauteur . ";" . $largeur . "}";
                        $commentSupportM .= $commentaire .  "}";

                        $photos = [];
                        $i = 0;

                        $photoMur = "";

                        if (sizeof($fileDatas) != 0) {
                            $etat = 1;
                            foreach ($fileDatas as $keyF => $file) {
                                if ($file != "") {
                                    if (substr($file, 0, 9) == "RTSP_Mur_") {
                                        $photos[$i] = $file;
                                    } else {
                                        $name = date("dmYHis");
                                        $bin = base64_decode($file);
                                        $im = imageCreateFromString($bin);
                                        if (!$im) {
                                            echo json_encode("OKM");
                                        }
                                        $nom = "RTSP_Mur_$key$keyM$i" . "$name" . "_" . "$keyF.jpg";
                                        $img_file = "../documents/releveTechnique/$nom";
                                        $photos[$i] = $nom;
                                        imagejpeg($im, $img_file, 50);
                                    }
                                    $i++;
                                }
                            }
                        }

                        $i = 0;
                        $videos = [];

                        if (sizeof($videoDatas) != 0) {
                            $etat = 1;
                            foreach ($videoDatas as $keyF => $file) {
                                if ($file != "") {
                                    if (substr($file, 0, 9) == "RTSP_Mur_") {
                                        $videos[$i] = $file;
                                    } else {
                                        $name = date("dmYHis");
                                        header('Content-Type: video/mp4');
                                        $bin = base64_decode($file);
                                        $nom = "RTSP_Mur_V$keyM$key$i" . "$name" . "_" . "$keyF.mp4";
                                        $img_file = "../documents/releveTechnique/$nom";
                                        $videos[$i] = $nom;
                                        file_put_contents($img_file, $bin);
                                    }
                                    $i++;
                                }
                            }
                        }

                        $i = 0;
                        $comments = [];
                        if (sizeof($fileInputs) != 0) {
                            foreach ($fileInputs as $keyF => $file) {
                                $comments[$i] = $fileInputs[$keyF];
                                $i++;
                            }
                        }


                        $i = 0;
                        $commentsVideo = [];
                        if (sizeof($videoInputs) != 0) {
                            foreach ($videoInputs as $keyF => $file) {
                                $commentsVideo[$i] = $videoInputs[$keyF];
                                $i++;
                            }
                        }


                        $commentPhotoSupportM .= sizeof($comments) != 0 ? implode("}", $comments) . "]" : '' . "]";
                        $commentVideoSupportM .= sizeof($videos) != 0 ? implode("}", $videos) . "]" : '' . "]";

                        $photoSupportM .= sizeof($photos) != 0 ? implode(";", $photos) . "}" : '' . "}";
                        $videoSupportM .= sizeof($videos) != 0 ? implode(";", $videos) . "}" : '' . "}";
                    }
                }

                $mesureSupport = $mesureP . "]" . $mesureS . "]" . $mesureMur;

                $db->bind("mesureSupportM", rtrim($mesureMur, "}"), null);
                $db->bind("mesureOuverture", rtrim($mesureOuverture, "}"), null);
                $db->bind("revetementMur", rtrim($revetementMur, "}"), null);
                $db->bind("repMurSinistre", rtrim($repMurSinistre, ";"), null);
                $db->bind("repOuverture", rtrim($repOuverture, ";"), null);
                $db->bind("libelleOuverture", rtrim($libelleO, "}"), null);
                $db->bind("mesureMur", rtrim($mesureRevMur, "}"), null);
                $db->bind("photoSupportM", rtrim($photoSupportM, "}"), null);
                $db->bind("videoSupportM", rtrim($videoSupportM, "}"), null);
                $db->bind("commentSupportM", rtrim($commentSupportM, "}"), null);
                $db->bind("commentPhotoSupportM", rtrim($commentPhotoSupportM, "]"), null);
                $db->bind("commentVideoSupportM", rtrim($commentVideoSupportM, "]"), null);
                $db->bind("idRTF", $idRT, null);
                $db->bind("idRTGuid", $idRTGuid, null);


                if ($db->execute()) {
                    $okDG = "DG OK";
                } else {
                    $okDG = "DG KO";
                }

                $okDommage .= $okDG;
            }

            //Suppression des biens
            $db->query("SELECT * FROM wbcc_bien WHERE idRTF = $idRT");
            $biensBD = $db->resultSet();

            if (sizeof($biensBD) != 0) {
                foreach ($biensBD as $keyBD => $bienD) {
                    $ind = 0;
                    $trouve = false;
                    // extract($bienD);
                    $idBien = $bienD->idBien;
                    while (!($trouve) && $ind < sizeof($tabBiensChoisis)) {
                        extract($tabBiensChoisis[$ind]);
                        if ($id != "null" && $id == $idBien) {
                            $trouve = true;
                        }
                        $ind++;
                    }
                    if (!($trouve)) {
                        //Supprimer les fichiers
                        $files = explode(";", $bienD->photoBien);
                        foreach ($files as $f) {
                            if (file_exists("../documents/releveTechnique/$f")) {
                                unlink("../documents/releveTechnique/$f");
                            }
                        }
                        $db->query("DELETE FROM wbcc_bien WHERE idBien = $idBien");
                        $db->execute();
                    }
                }
            }

            foreach ($tabBiensChoisis as $keyBien => $bien) {
                //   $degat = $tabDegat[0];
                extract($bien);
                $okB = "";

                $photoSupportB = "";
                $videoSupportB = "";
                $commentPhotoSupportB = "";
                $commentVideoSupportB = "";

                $photos = [];
                $i = 0;

                if (sizeof($fileDatas) != 0) {
                    $etat = 1;
                    foreach ($fileDatas as $key => $file) {
                        if ($file != "") {
                            if (substr($file, 0, 5) == "Bien_") {
                                $photos[$i] = $file;
                            } else {
                                $name = date("dmYHis");
                                $bin = base64_decode($file);
                                $im = imageCreateFromString($bin);
                                if (!$im) {
                                    echo json_encode("OKB");
                                }
                                $nom = "Bien_$keyBien" . "$i" . "$name.jpg";
                                $img_file = "../documents/releveTechnique/$nom";
                                $photos[$i] = $nom;
                                imagejpeg($im, $img_file, 50);
                            }
                            $i++;
                        }
                    }
                }

                $i = 0;
                $videos = [];

                if (sizeof($videoDatas) != 0) {
                    $etat = 1;
                    foreach ($videoDatas as $key => $file) {
                        if ($file != "") {
                            if (substr($file, 0, 5) == "Bien_") {
                                $videos[$i] = $file;
                            } else {
                                $name = date("dmYHis");
                                header('Content-Type: video/mp4');
                                $bin = base64_decode($file);
                                $nom = "Bien_$i" . "$name.mp4";
                                $img_file = "../documents/releveTechnique/$nom";
                                $videos[$i] = $nom;
                                file_put_contents($img_file, $bin);
                            }
                            $i++;
                        }
                    }
                }


                $i = 0;
                $comments = [];
                if (sizeof($fileInputs) != 0) {
                    foreach ($fileInputs as $key => $file) {
                        $comments[$i] = $fileInputs[$key];
                        $i++;
                    }
                }
                $commentPhotoSupportB = sizeof($comments) != 0 ? implode("}", $comments) : '';

                $i = 0;
                $commentsVideo = [];
                if (sizeof($videoInputs) != 0) {
                    foreach ($videoInputs as $key => $file) {
                        $commentsVideo[$i] = $videoInputs[$key];
                        $i++;
                    }
                }
                $commentVideoSupportB = sizeof($commentsVideo) != 0 ? implode("}", $commentsVideo) : '';

                $photoSupportB = sizeof($photos) != 0 ? implode(";", $photos) : '';
                $videoSupportB = sizeof($videos) != 0 ? implode(";", $videos) : '';


                if ($id == "null") {
                    $db->query("INSERT INTO wbcc_bien (libelleBien,photoBien,commentPhoto,videoBien,commentVideo,commentaireBien,idRTF,idRTGuid) VALUES (:libelleBien,:photoBien,:commentPhoto,:videoBien,:commentVideo,:commentaireBien,:idRTF,:idRTGuid)");
                } else {

                    $db->query("UPDATE wbcc_bien SET libelleBien=:libelleBien,photoBien=:photoBien,commentPhoto=:commentPhoto,videoBien=:videoBien,commentVideo=:commentVideo,commentaireBien=:commentaireBien,idRTF=:idRTF,idRTGuid=:idRTGuid WHERE idBien =:idBien");
                    $db->bind("idBien", $id, null);
                }

                $db->bind("libelleBien", $libelle, null);
                $db->bind("photoBien", $photoSupportB, null);
                $db->bind("commentPhoto", $commentPhotoSupportB, null);
                $db->bind("videoBien", $videoSupportB, null);
                $db->bind("commentVideo", $commentVideoSupportB, null);
                $db->bind("commentaireBien", $commentaire, null);
                $db->bind("idRTF", $idRT, null);
                $db->bind("idRTGuid", $idRTGuid, null);

                if ($db->execute()) {
                    $okB = "Bien OK";
                } else {
                    $okB = "Bien KO";
                }

                $okBien .= $okB;
            }
        }
        // $fin = "$idRT RTDGOK";
        echo json_encode($idRT);
    }

    if ($action == 'updateRT') {
        if (isset($_GET['sourceEnreg'])) {
        } else {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        extract($_POST);
        $editDate = date('Y-m-d H:i:s');
        $idRT = isset($rt) && $rt && $rt != null ? $rt['idRT'] : "";
        $idUser = $user['idUtilisateur'];
        $nbPieces = sizeof($tabPiece);
        $nbBiens = 0;
        $libelleDommage = $nbPieces != 0 ? "Des Pièces" : "";
        $libelleDommage = $nbBiens != 0 ? ($libelleDommage == "" ? "Des Biens" : ($libelleDommage . ";Des Biens")) : $libelleDommage;


        $commentaireReleve = isset($commentaireReleve) ? $commentaireReleve : "";
        //UPDATE RT or Insert

        $libellePieces = isset($libellePieces) ? $libellePieces : "";

        $rt2 = findItemByColumn("wbcc_releve_technique", "idOpportunityF", $idOP);
        if ($idRT == "" && !$rt2) {
            $db->query("INSERT INTO wbcc_releve_technique ( nombrePiece, nombreBiens, libelleDommageMateriel, libellePieces, commentaireReleve, idOpportunityF) VALUES (:nombrePiece,:nombreBiens,:libelleDommageMateriel, :libellePieces, :commentaireReleve, :idOP)");
            $db->bind("nombrePiece", $nbPieces, null);
            $db->bind("nombreBiens", $nbBiens, null);
            $db->bind("libellePieces", $libellePieces, null);
            $db->bind("libelleDommageMateriel",  $libelleDommage, null);
            $db->bind("commentaireReleve",  $commentaireReleve, null);
            $db->bind("idOP", $idOP, null);
        } else {
            $db->query("UPDATE wbcc_releve_technique SET nombrePiece=:nombrePiece, nombreBiens=:nombreBiens, libelleDommageMateriel=:libelleDommageMateriel, libellePieces=:libellePieces, commentaireReleve=:commentaireReleve WHERE idOpportunityF =:idOP");
            $db->bind("idOP", $idOP, null);
            $db->bind("nombrePiece", $nbPieces, null);
            $db->bind("nombreBiens", $nbBiens, null);
            $db->bind("libellePieces", $libellePieces, null);
            $db->bind("libelleDommageMateriel",  $libelleDommage, null);
            $db->bind("commentaireReleve",  $commentaireReleve, null);
        }

        if ($db->execute()) {
            $rt2 = findItemByColumn("wbcc_releve_technique", "idOpportunityF", $idOP);
            $idRT = $rt2->idRT;
            if ($nbPieces != 0) {
                /**** DELETE PIECE SUPPORT AND REVETEMENT *****/
                $db->query("SELECT * FROM wbcc_rt_piece WHERE idRTF= $idRT");
                $listToDelete = $db->resultSet();

                // //SAVE PIECE
                if (sizeof($listToDelete) != 0) {
                } else {
                    foreach ($tabPiece as $key => $piece) {
                        $idPieceF = $piece['id'];
                        $idRTPiece = $piece['idRTPiece'];

                        $pieceSearch = false;

                        /**** CREATE PIECE *****/
                        $numeroRTPiece = 'PIECE' . date("dmYHis") . $key . $idUser;
                        $db->query("INSERT INTO wbcc_rt_piece(numeroRTPiece, idRTF, numeroRTF, numeroPieceF, idPieceF, nomPiece, libellePiece, commentairePiece, photosPiece, commentsPhotosPiece, videosPiece, commentsVideosPiece, nbMurs, nbMursSinistres, nbMursNonSinistres, etatRtPiece, createDate, editDate, idUserF, longueurPiece, largeurPiece, surfacePiece) VALUES(:numeroRTPiece, :idRTF, :numeroRTF, :numeroPieceF, :idPieceF, :nomPiece, :libellePiece, :commentairePiece, :photosPiece, :commentsPhotosPiece, :videosPiece, :commentsVideosPiece, :nbMurs, :nbMursSinistres, :nbMursNonSinistres, :etatRtPiece, :createDate, :editDate, :idUserF, :longueurPiece, :largeurPiece, :surfacePiece)");
                        $db->bind("numeroRTPiece", $numeroRTPiece, null);
                        $db->bind("createDate", $editDate, null);
                        $db->bind("idUserF", $idUser, null);
                        $db->bind("idRTF", $idRT, null);
                        $db->bind("numeroRTF",  $rt['numeroRT'], null);
                        $db->bind("numeroPieceF", "", null);
                        $db->bind("idPieceF", $idPieceF, null);
                        $db->bind("nomPiece",  $piece['nom'], null);
                        $db->bind("libellePiece", $piece['libelle'], null);
                        $db->bind("commentairePiece",  $piece['commentaire'], null);
                        $db->bind("photosPiece",  isset($piece['photosPiece']) ? $piece['photosPiece'] : "", null);
                        $db->bind("commentsPhotosPiece", "", null);
                        $db->bind("videosPiece", "", null);
                        $db->bind("commentsVideosPiece", "", null);
                        $db->bind("nbMurs", $piece['nbMurs'], null);
                        $db->bind("nbMursSinistres", $piece['nbMursSinistres'], null);
                        $db->bind("nbMursNonSinistres", $piece['nbMursNonSinistres'], null);
                        $db->bind("etatRtPiece", 1, null);
                        $db->bind("editDate", $editDate, null);

                        $Lpiece = isset($piece['longueur']) ?  $piece['longueur'] : "";
                        $lpiece = isset($piece['largeur']) ?  $piece['largeur'] : "";
                        $Spiece = isset($piece['surface']) ?  $piece['surface'] : "";

                        $db->bind("longueurPiece", $Lpiece, null);
                        $db->bind("largeurPiece", $lpiece, null);
                        $db->bind("surfacePiece", $Spiece, null);
                        if ($db->execute()) {
                            $pieceSearch = findItemByColumn("wbcc_rt_piece", "numeroRTPiece", $numeroRTPiece);
                            $idRTPiece = $pieceSearch->idRTPiece;
                        }

                        //SAVE SUPPORT
                        $supports = $piece['listSupports'];
                        if (sizeof($supports) != 0) {
                            foreach ($supports as $key2 => $support) {
                                $idSupportF = $support['id'];
                                $idRTPieceSupport = $support['idRTPieceSupport'];
                                $supportSearch = false;
                                /**** CREATE SUPPORT *****/
                                $numeroRTPieceSupport = 'SUP' . date("dmYHis") . $key . $key2 . $idUser;
                                /* $db->query("INSERT INTO wbcc_rt_piece_support(numeroRTPieceSupport, idRTPieceF, numeroRTPieceF, idSupportF, numeroSupportF, nomSupport, libelleSupport, largeurSupport, longueurSupport, surfaceSupport, commentaireSupport, siOuverture, etatRtPieceSupport, createDate, editDate, idUserF, estSinistre,surfaceSupportAtraiter) VALUES(:numeroRTPieceSupport, :idRTPieceF, :numeroRTPieceF, :idSupportF, :numeroSupportF, :nomSupport, :libelleSupport, :largeurSupport, :longueurSupport, :surfaceSupport, :photosSupport,  :siOuverture, :etatRtPieceSupport, :createDate, :editDate, :idUserF, :estSinistre,:surfaceSupportAtraiter)"); */
                                $db->query("INSERT INTO wbcc_rt_piece_support(numeroRTPieceSupport, idRTPieceF, numeroRTPieceF, idSupportF, numeroSupportF, nomSupport, libelleSupport, largeurSupport, longueurSupport, surfaceSupport, commentaireSupport, siOuverture, etatRtPieceSupport, createDate, editDate, idUserF, estSinistre,surfaceSupportAtraiter) VALUES(:numeroRTPieceSupport, :idRTPieceF, :numeroRTPieceF, :idSupportF, :numeroSupportF, :nomSupport, :libelleSupport, :largeurSupport, :longueurSupport, :surfaceSupport, :commentaireSupport, :siOuverture, :etatRtPieceSupport, :createDate, :editDate, :idUserF, :estSinistre, :surfaceSupportAtraiter)");
                                $db->bind("numeroRTPieceSupport", $numeroRTPieceSupport, null);
                                $db->bind("createDate", $editDate, null);
                                $db->bind("idUserF", $idUser, null);

                                $Lsupport = isset($support['longueur']) ?  $support['longueur'] : "";
                                $lsupport = isset($support['largeur']) ?  $support['largeur'] : "";
                                $Ssupport = isset($support['surface']) ?  $support['surface'] : "";
                                $SsupportT = isset($support['surfaceATraiter']) ?  $support['surfaceATraiter'] : "";

                                $db->bind("idRTPieceF", $idRTPiece, null);
                                $db->bind("numeroRTPieceF", $pieceSearch->numeroRTPiece, null);
                                $db->bind("idSupportF", $support['id'], null);
                                $db->bind("numeroSupportF", "", null);
                                $db->bind("nomSupport", $support['nom'], null);
                                $db->bind("libelleSupport", $support['libelle'], null);
                                $db->bind("largeurSupport", $lsupport, null);
                                $db->bind("longueurSupport", $Lsupport, null);
                                $db->bind("surfaceSupport", $Ssupport, null);
                                $db->bind("surfaceSupportAtraiter", $SsupportT, null);
                                $db->bind("commentaireSupport", $support['commentaire'], null);
                                $db->bind("siOuverture", (sizeof($support['listOuvertures']) == 0 ? 0 : 1), null);
                                $db->bind("etatRtPieceSupport", $support['etatSupport'], null);
                                $db->bind("editDate", $editDate, null);
                                $db->bind("estSinistre", $support['estSinistre'], null);

                                if ($db->execute()) {
                                    $ok = 1;
                                    //ENREGISTREMENT REVETEMENT
                                    $RTPieceSupport = findItemByColumn("wbcc_rt_piece_support", "numeroRTPieceSupport", $numeroRTPieceSupport);
                                    $idRTPieceSupport = $RTPieceSupport->idRTPieceSupport;
                                    $revetements = isset($support['listRevetements']) ? $support['listRevetements'] : [];
                                    foreach ($revetements as $revetement) {
                                        $idRTRevetement = $revetement['idRTRevetement'];
                                        $numeroRtRevetement = "Revet" . date("YmdHis") . $key . $key2  . $idUser;
                                        $idRevetement = isset($revetement['id']) ? $revetement['id'] : null;
                                        $db->query("INSERT INTO wbcc_rt_revetement(numeroRtRevetement, idRtPieceSupportF, nomRevetement, libelleRevetement, createDate, editDate, idUserF, idRevetementF) VALUES(:numeroRtRevetement, :idRtPieceSupportF, :nomRevetement, :libelleRevetement, :createDate, :editDate, :idUserF, :idRevetementF)");
                                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                                        $db->bind("numeroRtRevetement", $numeroRtRevetement, null);
                                        $db->bind("editDate", date("Y-m-d H:i:s"), null);
                                        $db->bind("idRtPieceSupportF", $idRTPieceSupport, null);
                                        $db->bind("nomRevetement", $revetement["libelle"], null);
                                        $db->bind("libelleRevetement", $revetement["libelle"], null);
                                        $db->bind("idUserF", $idUser, null);
                                        $db->bind("idRevetementF", $idRevetement, null);
                                        $db->execute();
                                    }


                                    //ENREGISTREMENT OUVERTURE


                                    $ouvertures = isset($support['listOuvertures']) ? $support['listOuvertures'] : [];

                                    foreach ($ouvertures as $ouverture) {

                                        $numeroRtOuverture = "Ouverture" . date("YmdHis") . $idUser;

                                        $db->query("INSERT INTO `wbcc_rt_ouverture`(`numeroRtOuverture`, `idRtPieceSupportF`, `numeroRtPieceSupportF`, `nomOuverture`, `libelleOuverture`, `largeurOuverture`, `longueurOuverture`, `surfaceOuverture`, `commentaireOuverture`, `etatRtOuverture`, `createDate`, `editDate`, `idUserF`) VALUES(:numeroRtOuverture, :idRtPieceSupportF, :numeroRtPieceSupportF, :nomOuverture, :libelleOuverture, :largeurOuverture,:longueurOuverture, :surfaceOuverture, :commentaireOuverture, :etatRtOuverture, :createDate, :editDate, :idUserF)");

                                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                                        $db->bind("numeroRtOuverture", $numeroRtOuverture, null);
                                        $db->bind("numeroRtPieceSupportF", $numeroRTPieceSupport, null);
                                        $db->bind("editDate", date("Y-m-d H:i:s"), null);
                                        $db->bind("idRtPieceSupportF", $idRTPieceSupport, null);
                                        $db->bind("nomOuverture", $ouverture["libelle"], null);
                                        $db->bind("libelleOuverture", $ouverture["libelle"], null);
                                        $db->bind("longueurOuverture", $ouverture["longueur"], null);
                                        $db->bind("largeurOuverture", $ouverture["largeur"], null);
                                        $db->bind("surfaceOuverture", $ouverture["surface"], null);
                                        $db->bind("commentaireOuverture", $ouverture["commentaire"], null);
                                        $db->bind("etatRtOuverture", $ouverture["etatOuverture"], null);
                                        $db->bind("idUserF", $idUser, null);

                                        $db->execute();
                                    }
                                }
                            }
                        }
                    }
                }


                $db->query("SELECT * FROM `wbcc_rt_piece` WHERE idRTF= $idRT ");
                $data = $db->resultSet();
                $lib = "";
                foreach ($data as $key => $piece) {
                    $lib .= $lib == "" ? $piece->libellePiece : ";" . $piece->libellePiece;
                }
                $db->query("UPDATE wbcc_releve_technique SET libellePieces=:libellePieces WHERE idRT =:idRT");
                $db->bind("idRT", $idRT, null);
                $db->bind("libellePieces", $lib, null);
                if ($db->execute()) {
                    echo json_encode("1");
                    // return;
                } else {
                    echo json_encode("0");
                }
            } else {
                echo json_encode("0");
            }
        } else {
            echo json_encode("0");
        }
    }

    if ($action == 'updateBien') {
        extract($_POST);

        $db = new Database();
        foreach ($tabBien as $bien) {

            $idBien = $bien['id'];
            $bienSearch = false;

            if ($idBien != "") {
                $db->query("SELECT * FROM `wbcc_bien` WHERE idBien = idBien LIMIT 1");
                $bienSearch = $db->single();
            }
            if ($bienSearch) {

                $db->query("UPDATE `wbcc_bien` SET `libelleBien` =:libelleBien , `commentaireBien` =:commentaireBien, `idRTF` =:idRTF, `idRTGuid` =:idRTGuid, `editDate`= :editDate WHERE idBien = :idBien");
                $db->bind("idBien", $idBien, null);
            } else {
                $db->query("INSERT INTO `wbcc_bien`(`numeroBien`, `libelleBien`, `commentaireBien`, `idRTF`, `idRTGuid`, `createDate`, `editDate`) VALUES (:numeroBien,:libelleBien,:commentaireBien,:idRTF,:idRTGuid,:createDate,:editDate)");
                $db->bind("createDate", date('Y-m-d H:i:s'), null);
                $db->bind("numeroBien", "Bien" . date("YmdHis"), null);
            }

            $db->bind("libelleBien", $bien['libelle'], null);
            $db->bind("commentaireBien", $bien['commentaire'], null);
            $db->bind("idRTF", $rt['idRT'], null);
            $db->bind("idRTGuid", $rt['numeroRT'], null);
            $db->bind("editDate", date('Y-m-d H:i:s'), null);

            $data = $db->execute();
        }

        if ($data) {
            echo json_encode("Bien enregistré !");
        } else {
            echo json_encode("Bien non enregistré !");
        }
    }

    if ($action == 'getPieceSupportRevetement') {
        $idrt = $_GET['id'];

        $db = new Database();
        $dataP = array();

        $db->query("SELECT * FROM `wbcc_rt_piece` WHERE `idRTF` = $idrt");
        $dataP["pieces"] = $db->resultSet();

        foreach ($dataP["pieces"] as $i => $p) {

            $db->query("SELECT * FROM `wbcc_rt_piece_support` WHERE `idRTPieceF` = $p->idRTPiece");
            $dataS = $db->resultSet();

            $dataP["support"][$p->nomPiece] = $dataS;

            foreach ($dataS as $j => $s) {
                $db->query("SELECT * FROM `wbcc_rt_revetement` WHERE `idRtPieceSupportF` = $s->idRTPieceSupport");
                $dataR = $db->resultSet();
                $dataP[$p->nomPiece][$s->nomSupport]["revetement"] = $dataR;

                $db->query("SELECT * FROM `wbcc_rt_ouverture` WHERE `idRtPieceSupportF` = $s->idRTPieceSupport");
                $dataO = $db->resultSet();
                $dataP[$p->nomPiece][$s->nomSupport]["ouverture"] = $dataO;
            }
        }

        echo (json_encode($dataP));
    }

    if ($action == "getRTBien") {
        $idrt = $_GET['id'];

        $db = new Database();
        $db->query("SELECT * FROM `wbcc_bien` WHERE `idRTF` = $idrt");
        $data = $db->resultSet();

        echo (json_encode($data));
    }

    if ($action == "updateDocRT") {
        extract($_POST);

        $db->query("SELECT * FROM `wbcc_releve_technique` WHERE idRT = '$idRT' LIMIT 1");
        $rt = $db->single();

        $docRT = $rt->documentComplement;
        if (!strpos($docRT, $documentComplement)) {
            $newdoc = $docRT . $documentComplement;
        } else {
            $newdoc = $documentComplement;
        }

        $db->query("UPDATE `wbcc_releve_technique` SET  documentComplement = '$newdoc' WHERE idRT = '$idRT' ");

        $data = $db->execute();
        echo json_encode($data);
    }

    if ($action == "updateDocFRT") {
        extract($_POST);


        $db->query("SELECT * FROM `wbcc_releve_technique` WHERE idRT = '$idRT' LIMIT 1");
        $rt = $db->single();

        $docRT = $rt->documentFRT;
        if (!strpos($docRT, $documentFRT)) {
            $newdoc = $docRT . $documentFRT;
        } else {
            $newdoc = $documentFRT;
        }

        $db->query("UPDATE `wbcc_releve_technique` SET  documentFRT = '$newdoc' WHERE idRT = '$idRT' ");

        $data = $db->execute();
        echo json_encode($data);
    }

    if ($action == "deleteDocRT") {
        extract($_POST);


        $db->query("SELECT * FROM `wbcc_releve_technique` WHERE idRT = '$idRT' LIMIT 1");
        $rt = $db->single();

        $docRT = $rt->documentComplement;

        $tabDoc = explode(';', $docRT);
        $tabDoc = array_diff($tabDoc, [$documentComplement]);

        $newdoc = implode(';', $tabDoc);
        $db->query("UPDATE `wbcc_releve_technique` SET  documentComplement = '$newdoc' WHERE idRT = '$idRT' ");

        $data = $db->execute();
        echo json_encode($data);
    }

    if ($action == "deleteDocFRT") {
        extract($_POST);


        $db->query("SELECT * FROM `wbcc_releve_technique` WHERE idRT = '$idRT' LIMIT 1");
        $rt = $db->single();

        $docRT = $rt->documentFRT;

        $tabDoc = explode(';', $docRT);
        $tabDoc = array_diff($tabDoc, [$documment]);

        $newdoc = implode(';', $tabDoc);
        $db->query("UPDATE `wbcc_releve_technique` SET  documentFRT = '$newdoc' WHERE idRT = '$idRT' ");

        $data = $db->execute();
        echo json_encode($data);
    }

    if ($action == "deletePiece") {
        extract($_POST);

        $deleted = false;

        $db->query("DELETE FROM `wbcc_rt_piece_support` WHERE idRTPieceF  = $idRTPiece ");

        if ($db->execute()) {
            $db->query("DELETE FROM `wbcc_rt_piece` WHERE idRTPiece = $idRTPiece ");
            $db->execute();

            if ($db->execute()) {
                $deleted = true;
            }
        }


        echo json_encode($deleted);
    }

    if ($action == "deleteBien") {

        extract($_POST);

        $deleted = false;

        $db->query("DELETE FROM `wbcc_bien` WHERE idBien  = $idBien");

        if ($db->execute()) {
            $deleted = true;
        }


        echo json_encode($deleted);
    }

    if ($action == "findRTById") {

        $idRT = $_GET["idRT"];

        $db->query("SELECT * FROM `wbcc_releve_technique` WHERE idRT  = $idRT");
        $rt = $db->single();

        echo json_encode($rt);
    }

    /** A verif **/
    if ($action == 'updateReleve') {
        $_POST = json_decode(file_get_contents('php://input'), true);
        // extract($_POST);
        $insert = false;
        foreach ($_POST as $key => $op) {
            extract($op);
            $search = findItemByColumn("wbcc_releve_technique", "numeroRT", $numeroRT);
            $opportunity = getOpByName($numeroOP);
            $idOpportunity = $opportunity != false ? $opportunity->idOpportunity : null;
            if ($search) {
                //UPDATE
                $db->query("UPDATE wbcc_releve_technique SET numeroRT=:numeroRT, numeroOP=:numeroOP,nature =:nature,date =:date, anneeSurvenance=:anneeSurvenance,dateConstat =:dateConstat, heure=:heure,commentaireDateInconnue =:commentaireDateInconnue, numeroBatiment=:numeroBatiment, adresse=:adresse,ville =:ville, codePostal=:codePostal, ageConstruction=:ageConstruction, quitterLieu=:quitterLieu, codePorte=:codePorte,numeroAppartement =:numeroAppartement, numeroImmeuble=:numeroImmeuble, cause=:cause,precisionDegat =:precisionDegat, lieuDegat=:lieuDegat,precisionComplementaire =:precisionComplementaire, reparerCauseDegat=:reparerCauseDegat,rechercheFuite =:rechercheFuite,chercheurFuite =:chercheurFuite,commentaireSinistre =:commentaireSinistre, vehiculeCause=:vehiculeCause, intervention=:intervention, interventionPompier=:interventionPompier, depotPlainte=:depotPlainte,temoin =:temoin, personneOrigineSinistre=:personneOrigineSinistre, incendieVolontaire=:incendieVolontaire,degatPompier =:degatPompier, dateInterventionPompier=:dateInterventionPompier, niveauEtage=:niveauEtage, dommageCorporel=:dommageCorporel, dommageMateriel=:dommageMateriel,dommageImmateriel =:dommageImmateriel,dommageMaterielAutrePersonne =:dommageMaterielAutrePersonne,libelleDommageMateriel =:libelleDommageMateriel, nomAutrePersonne=:nomAutrePersonne, nombrePiece=:nombrePiece, nombreBiens=:nombreBiens,typeCanalisation =:typeCanalisation, accessibilite=:accessibilite, localisation=:localisation,partieConcernee =:partieConcernee, natureDommage=:natureDommage, dateExpertiseSinistre=:dateExpertiseSinistre, codeExpertSinistre=:codeExpertSinistre, commentaireReleve=:commentaireReleve, documentComplement=:documentComplement, commentaireDocument=:commentaireDocument, createDate=:createDate, editDate=:editDate, idOpportunityF=:idOpportunityF, idRVF=:idRVF, idRVGuid=:idRVGuid, introduction=:introduction, conclusion=:conclusion WHERE wbcc_releve_technique.numeroRT = :numeroRT");
            } else {
                //CREATE
                $db->query("INSERT INTO wbcc_releve_technique(numeroRT, numeroOP, nature, date, anneeSurvenance, dateConstat, heure, commentaireDateInconnue, numeroBatiment, adresse, ville, codePostal, ageConstruction, quitterLieu, codePorte, numeroAppartement, numeroImmeuble, cause, precisionDegat, lieuDegat, precisionComplementaire, reparerCauseDegat, rechercheFuite, chercheurFuite, commentaireSinistre, vehiculeCause, intervention, interventionPompier, depotPlainte, temoin, personneOrigineSinistre, incendieVolontaire, degatPompier, dateInterventionPompier, niveauEtage, dommageCorporel, dommageMateriel, dommageImmateriel, dommageMaterielAutrePersonne, libelleDommageMateriel, nomAutrePersonne, nombrePiece, nombreBiens, typeCanalisation, accessibilite, localisation, partieConcernee, natureDommage, dateExpertiseSinistre, codeExpertSinistre, commentaireReleve, documentComplement, commentaireDocument, createDate, editDate, idOpportunityF, idRVF, idRVGuid, introduction, conclusion) VALUES (:numeroRT, :numeroOP, :nature, :date, :anneeSurvenance, :dateConstat, :heure, :commentaireDateInconnue, :numeroBatiment, :adresse, :ville, :codePostal, :ageConstruction, :quitterLieu, :codePorte, :numeroAppartement, :numeroImmeuble, :cause, :precisionDegat, :lieuDegat, :precisionComplementaire, :reparerCauseDegat, :rechercheFuite, :chercheurFuite, :commentaireSinistre, :vehiculeCause, :intervention, :interventionPompier, :depotPlainte, :temoin, :personneOrigineSinistre, :incendieVolontaire, :degatPompier, :dateInterventionPompier, :niveauEtage, :dommageCorporel, :dommageMateriel, :dommageImmateriel, :dommageMaterielAutrePersonne, :libelleDommageMateriel, :nomAutrePersonne, :nombrePiece, :nombreBiens, :typeCanalisation, :accessibilite, :localisation, :partieConcernee, :natureDommage, :dateExpertiseSinistre, :codeExpertSinistre, :commentaireReleve, :documentComplement, :commentaireDocument, :createDate, :editDate, :idOpportunityF, :idRVF, :idRVGuid, :introduction, :conclusion)");
            }

            $db->bind("numeroRT", $numeroRT, null);
            $db->bind("numeroOP", $numeroOP, null);
            $db->bind("nature", $nature, null);
            $db->bind("date", $date, null);
            $db->bind("anneeSurvenance", $anneeSurvenance, null);
            $db->bind("dateConstat", $dateConstat, null);
            $db->bind("heure", $heure, null);
            $db->bind("commentaireDateInconnue", $commentaireDateInconnue, null);
            $db->bind("numeroBatiment", $numeroBatiment, null);
            $db->bind("adresse", $adresse, null);
            $db->bind("ville", $ville, null);
            $db->bind("codePostal", $codePostal, null);
            $db->bind("ageConstruction", $ageConstruction, null);
            $db->bind("quitterLieu", $quitterLieu, null);
            $db->bind("codePorte", $codePorte, null);
            $db->bind("numeroAppartement", $numeroAppartement, null);
            $db->bind("numeroImmeuble", $numeroImmeuble, null);
            $db->bind("cause", $cause, null);
            $db->bind("precisionDegat", $precisionDegat, null);
            $db->bind("lieuDegat", $lieuDegat, null);
            $db->bind("precisionComplementaire", $precisionComplementaire, null);
            $db->bind("reparerCauseDegat", $reparerCauseDegat, null);
            $db->bind("rechercheFuite", $rechercheFuite, null);
            $db->bind("chercheurFuite", $chercheurFuite, null);
            $db->bind("commentaireSinistre", $commentaireSinistre, null);
            $db->bind("vehiculeCause", $vehiculeCause, null);
            $db->bind("intervention", $intervention, null);
            $db->bind("interventionPompier", $interventionPompier, null);
            $db->bind("depotPlainte", $depotPlainte, null);
            $db->bind("temoin", $temoin, null);
            $db->bind("personneOrigineSinistre", $personneOrigineSinistre, null);
            $db->bind("incendieVolontaire", $incendieVolontaire, null);
            $db->bind("degatPompier", $degatPompier, null);
            $db->bind("dateInterventionPompier", $dateInterventionPompier, null);
            $db->bind("niveauEtage", $niveauEtage, null);
            $db->bind("dommageCorporel", $dommageCorporel, null);
            $db->bind("dommageMateriel", $dommageMateriel, null);
            $db->bind("dommageImmateriel", $dommageImmateriel, null);
            $db->bind("dommageMaterielAutrePersonne", $dommageMaterielAutrePersonne, null);
            $db->bind("libelleDommageMateriel", $libelleDommageMateriel, null);
            $db->bind("nomAutrePersonne", $nomAutrePersonne, null);
            $db->bind("nombrePiece", $nombrePiece, null);
            $db->bind("nombreBiens", $nombreBiens, null);
            $db->bind("typeCanalisation", $typeCanalisation, null);
            $db->bind("localisation", $localisation, null);
            $db->bind("accessibilite", $accessibilite, null);
            $db->bind("partieConcernee", $partieConcernee, null);
            $db->bind("natureDommage", $natureDommage, null);
            $db->bind("dateExpertiseSinistre", $dateExpertiseSinistre, null);
            $db->bind("codeExpertSinistre", $codeExpertSinistre, null);
            $db->bind("commentaireReleve", $commentaireReleve, null);
            $db->bind("documentComplement", $documentComplement, null);
            $db->bind("commentaireDocument", $commentaireDocument, null);
            $db->bind("createDate", $createDate, null);
            $db->bind("editDate", $editDate, null);
            $db->bind("idOpportunityF", $idOpportunity, null);
            $db->bind("idRVF", $idRVF, null);
            $db->bind("idRVGuid", $idRVGuid, null);
            $db->bind("introduction", $introduction, null);
            $db->bind("conclusion", $conclusion, null);

            if ($db->execute()) {
                $insert = true;
            } else {
                $insert = false;
            }
        }
        if ($insert) {
            echo json_encode("Releve Technique Update");
        } else {
            echo json_encode("Error Releve Technique Update");
        }
    }
}

function createCompte($idResponsable, $idGestionnaireAppImm)
{
    $db = new Database();
    //LINK COMPANY - CONTACT
    if ($idGestionnaireAppImm != null && $idGestionnaireAppImm != "" && $idGestionnaireAppImm != "0") {
        $db->query("DELETE FROM wbcc_contact_company WHERE idContactF = $idResponsable");
        $db->execute();
        $db->query("INSERT INTO wbcc_contact_company(idContactF,idCompanyF) VALUES ($idResponsable, $idGestionnaireAppImm)");
        $db->execute();
    }

    $c = findItemByColumn("wbcc_contact", "idContact", $idResponsable);
    if ($c) {
        //SEARCH IF USER HAS ACCOUNT
        $user = findItemByColumn("wbcc_utilisateur", "idContactF", $c->idContact);
        if ($user) {
        } else {
            //CREATE ACCOUNT
            $token = md5(uniqid(rand(), true));
            $param = $c->emailContact . "~" . $token;
            $pass = "";
            $possible = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $i = 0;
            while ($i < 8) {
                $char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);
                if (!strstr($pass, $char)) {
                    $pass .= $char;
                    $i++;
                }
            }
            $statut = $c->statutContact != null && $c->statutContact != "" ? $c->statutContact : 'GARDIEN';
            $idRole = str_contains(strtolower($statut), "chef") ? 14 : 15;
            $db->query("INSERT INTO wbcc_utilisateur(login,mdp,email,role,etatUser,firstConnection,idContactF, token) 
                                VALUES (:email,:password,:email, :idRoleF, :etat, :firstConnection, :idContactF, :token)");
            $db->bind("email", $c->emailContact, null);
            $db->bind("password", sha1($pass), null);
            $db->bind("idRoleF", $idRole, null);
            $db->bind("idContactF", $c->idContact, null);
            $db->bind("token", $token, null);
            $db->bind("etat", 1, null);
            $db->bind("firstConnection", 0, null);
            if ($db->execute()) {
                $db->query("UPDATE  wbcc_contact SET isUser=:isUser WHERE idContact=:idContact");
                $db->bind("isUser", "1", null);
                $db->bind("idContact", $c->idContact, null);
                if ($db->execute()) {
                    //MAIL inform create
                    $subject = "Mail de confirmation de création de compte";
                    $body = "Bonjour $c->civiliteContact $c->fullName,
                                    <br><br>Votre compte d'utilisateur vient d'être créé sur <b>WBCC ASSISTANCE EXTRANET : www.extranet.wbcc.fr </b><br><br>
                                
                                    Pour terminer votre inscription, veuillez confirmer votre adresse e-mail en suivant ce lien : <br>
                                    <b><a href='" . URLROOT . "/Home/confirm/$param'>TERMINER VOTRE INSCRIPTION</a></b> <br><br>
                                    Merci de confirmer votre adresse e-mail, sans quoi votre inscription sera incomplète. <br><br>

                                    Information de connexion :<br>
                                    <u><b>Login</u> :</b> $c->emailContact<br>
                                    <u><b>Mot de passe</u> :</b> $pass

                                    <br><br><br>
                                    Veuillez ne pas répondre à ce message envoyé automatiquement pour votre information.  <br><br>
                                    Email envoyé depuis l'extranet de WBCC par <br>
                                    <b> WBCC ASSISTANCE </b>
                                    <br><br>Pour toutes question ou demande techniques, merci de contacter :
                                    <br><b>Tel : 09 80 08 44 84 
                                    <br>Email : supportdev@wbcc.fr</b>
                                    ";
                    $r = new Role();
                    if ($r::mailOnServer($c->emailContact, $subject, $body)) {
                        $insert = true;
                    } else {
                        $insert = false;
                    }
                } else {
                    $insert = false;
                }
            } else {
                $insert = false;
            }
        }
    }
}

function getOpByName($numero)
{
    $db = new Database();
    $db->query("SELECT * FROM wbcc_opportunity WHERE name = '$numero' ");
    return $db->single();
}

function ConvertTabPhotoToString($fileDatas, $type = "photo", $idUser, $nomUser, $idOP, $opName)
{
    $db = new Database();
    $photos = [];
    $i = 0;
    if (sizeof($fileDatas) != 0) {
        foreach ($fileDatas as $key => $file) {

            if ($file != "") {
                if (strstr(strtolower($file), ".jpg") || strstr(strtolower($file), ".png") || strstr(strtolower($file), ".jpeg")) {
                    $nom = $file;
                } else {
                    $tab = explode('}', $file);
                    $typePhoto = "";
                    if (isset($tab[0]) && ($tab[0] == "gallery" || $tab[0] == "camera")) {
                        $typePhoto = $tab[0];
                        $file = $tab[1];
                    }
                    $bin = base64_decode($file);
                    if ($bin) {
                        $im = imageCreateFromString($bin);
                        if (!$im) {
                            //echo json_encode("0");
                        }
                        $nom = "$opName" . "_$type" . "_$key$idUser" . date("dmYHis") . ".jpg";
                        $nom = str_replace(' ', '_', $nom);
                        $img_file = "../documents/opportunite/$nom";
                        if ($typePhoto == "gallery" && $im) {
                            imagejpeg($im, $img_file, 60);
                        } else {
                            // $rotateImg = imagerotate($im, -90, 0);
                            // imagejpeg($rotateImg, $img_file, 60);
                            if ($typePhoto == "camera" && $im) {
                                imagejpeg($im, $img_file, 60);
                            }
                        }
                    }
                }
                $photos[$i] = $nom;
                $i++;
                //SAVE DOCUMENT OP
                // if (file_exists("../documents/opportunite/" . $nom)) {
                //     $doc = findItemByColumn("wbcc_document", "urlDocument", $nom);
                //     if ($doc) {
                //         //UPDATE
                //         // $db->query("UPDATE wbcc_document SET numeroDocument = :numeroDocument, nomDocument = :nomDocument, urlDocument = :urlDocument, commentaire = :commentaire, createDate=:createDate, guidHistory = :guidHistory, typeFichier=:typeFichier, size=:size, guidUser=:guidUser, idUtilisateurF= :idUtilisateurF, auteur=:auteur, source=:source, publie=:publie WHERE numeroDocument = :numeroDocument");
                //     } else {
                //         //CREATE
                //         $numeroDocument = "DOC" . date('dmYHis') . $idOP . $key . $idUser;
                //         $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");

                //         $db->bind("publie", "1", null);
                //         $db->bind("source", "EXTRA", null);
                //         $db->bind("numeroDocument", $numeroDocument, null);
                //         $db->bind("nomDocument", $nom, null);
                //         $db->bind("urlDocument", $nom, null);
                //         $db->bind("commentaire", "", null);
                //         $db->bind("createDate",  date('Y-m-d H:i:s'), null);
                //         $db->bind("guidHistory", null, null);
                //         $db->bind("typeFichier", "Image", null);
                //         $db->bind("size", null, null);
                //         $db->bind("guidUser", null, null);
                //         $db->bind("idUtilisateurF", $idUser, null);
                //         $db->bind("auteur", $nomUser, null);
                //         if ($db->execute()) {
                //             $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);

                //             $db->query("INSERT INTO wbcc_opportunity_document( idDocumentF, idOpportunityF) VALUES (:idDocumentF, :idOpportunityF)");
                //             $db->bind("idDocumentF", $document->idDocument, null);
                //             $db->bind("idOpportunityF", $idOP, null);
                //             $db->execute();
                //         }
                //     }
                // }
            }
        }
    }
    return sizeof($photos) != 0 ? implode(";", $photos) : '';
}


function ConvertTabVideoToString($fileDatas, $type = "video", $idUser)
{
    $videos = [];
    $i = 0;
    if (sizeof($fileDatas) != 0) {
        foreach ($fileDatas as $key => $file) {
            if ($file != "") {
                header('Content-Type: video/mp4');
                if (strstr($file, ".mp4")) {
                    $nom = $file;
                } else {
                    $bin = base64_decode($file);
                    $nom = "$type" . "_$key$idUser" . date("dmYHis") . ".mp4";
                    $nom = str_replace(' ', '_', $nom);
                    $img_file = "../documents/releveTechnique/$nom";
                    $videos[$i] = $nom;
                    $i++;
                    file_put_contents($img_file, $bin);
                }
            }
        }
    }
    return sizeof($videos) != 0 ? implode("}", $videos) : '';
}

function ConvertTabCommentaireToString($comments)
{
    $commentaires = [];
    $i = 0;
    if (sizeof($comments) != 0) {
        foreach ($comments as $key => $c) {
            $commentaires[$i] = $c;
            $i++;
        }
    }
    return sizeof($commentaires) != 0 ? implode("}", $commentaires) : '';
}

function saveContact($idContact, $civilite, $prenom, $nom, $email, $tel, $statut, $adresse, $codePostal, $ville, $idUser)
{
    $db = new Database();
    $search = false;
    $numero = "";
    if ($idContact != "" && $idContact != "0" && $idContact != 0 && $idContact != null) {
        //UPDATE
        $db->query("UPDATE wbcc_contact SET  civiliteContact = :civilite, fullName=:fullName, nomContact = :nom, prenomContact = :prenom, telContact = :tel, emailContact = :email, statutContact = :statut, adresseContact = :adresse, codePostalContact = :codePostal, villeContact = :ville, editDate =:editDate WHERE idContact = :idContact");
        $db->bind("idContact", $idContact, null);
    } else {
        //TEST IF APP EXIST
        if ($tel != "" || $email != "") {
            $db->query("SELECT * FROM wbcc_contact WHERE  telContact = :tel1 OR emailContact = :email1 LIMIT 1");
            $db->bind("tel1", $tel, null);
            $db->bind("email1", $email, null);
            $search  = $db->single();
        }
        if ($search) {
            $idContact = $search->idContact;
            $db->query("UPDATE wbcc_contact SET  civiliteContact = :civilite, fullName=:fullName, nomContact = :nom, prenomContact = :prenom, telContact = :tel, emailContact = :email, statutContact = :statut, adresseContact = :adresse, codePostalContact = :codePostal, villeContact = :ville, editDate =:editDate WHERE idContact =  $search->idContact ");
        } else {
            $numero = "CON" . date('dmYHis') . $idUser;
            $db->query("INSERT INTO wbcc_contact (numeroContact, civiliteContact, fullName, nomContact, prenomContact, telContact, emailContact, statutContact, adresseContact, codePostalContact, villeContact, editDate) VALUES (:numeroContact, :civilite, :fullName, :nom, :prenom, :tel, :email, :statut, :adresse, :codePostal, :ville, :editDate)");
            $db->bind("numeroContact", $numero, null);
        }
    }
    $db->bind("civilite", $civilite, null);
    $db->bind("nom", $nom, null);
    $db->bind("prenom", $prenom, null);
    $db->bind("fullName", "$prenom $nom", null);
    $db->bind("tel", $tel, null);
    $db->bind("email", $email, null);
    $db->bind("statut", $statut, null);
    $db->bind("adresse", $adresse, null);
    $db->bind("codePostal", $codePostal, null);
    $db->bind("ville", $ville, null);
    $db->bind("editDate", date('Y-m-d H:i:s'), null);

    if ($db->execute()) {
        if ($idContact != "" && $idContact != "0" && $idContact != 0 && $idContact != null) {
            return (findItemByColumn("wbcc_contact", "idContact", $idContact));
        } else {
            return (findItemByColumn("wbcc_contact", "numeroContact", $numero));
        }
    } else {
        return false;
    }
    return false;
}

function saveInfosGardien($id, $civilite, $prenom, $nom, $tel, $email, $adresse, $codePostal, $ville, $idUser, $idOP, $idImmeuble)
{
    $db = new Database();
    $con = saveContact($id, $civilite, $prenom, $nom, $email, $tel, "GARDIEN", $adresse, $codePostal, $ville, $idUser);
    if ($con) {
        //LINK IMMEUBLE-GARDIEN
        $db->query("UPDATE wbcc_immeuble SET  nomGardien = :nomGardien, idGardien = :idGardien WHERE idImmeuble = :idImmeuble");
        $db->bind("nomGardien", "$prenom $nom", null);
        $db->bind("idGardien", $con->idContact, null);
        $db->bind("idImmeuble", $idImmeuble, null);
        $db->execute();
        //LINK OP GARDIEN
        $db->query("SELECT * FROM wbcc_contact_opportunity WHERE  idContactF = :idContact AND idOpportunityF = :idOpportunity LIMIT 1");
        $db->bind("idContact", $con->idContact, null);
        $db->bind("idOpportunity", $idOP, null);
        $search  = $db->single();
        if ($search) {
        } else {
            $db->query("INSERT INTO wbcc_contact_opportunity (idContactF, idOpportunityF) VALUES ($con->idContact, $idOP)");
            $db->execute();
        }
    }
    return $con;
}

function  saveInfosVoisin($id, $civilite, $prenom, $nom, $tel, $email, $adresse, $codePostal, $ville, $idUser, $idOP, $idImmeuble, $idApp, $lot, $batiment, $etage, $porte, $idCompagnieAssuranceF, $compagnieAssurance, $numeroPolice)
{
    $db = new Database();
    $app = false;
    if ($idApp != null && $idApp != "" && $idApp != "0") {
        $app = findItemByColumn("wbcc_appartement", "idApp", $idApp);
    }

    if (!$app) {
        $db->query("SELECT * FROM wbcc_appartement WHERE  etage = :etage1 AND codePorte = :codePorte1  AND adresse = :adresse1 AND codePostal = :codePostal1 AND ville = :ville1 AND batiment=:batiment1 LIMIT 1");
        $db->bind("etage1", $etage, null);
        $db->bind("codePorte1", $porte, null);
        $db->bind("adresse1",  $adresse, null);
        $db->bind("codePostal1", $codePostal, null);
        $db->bind("ville1", $ville, null);
        $db->bind("batiment1",  $batiment, null);
        $app  = $db->single();
    }


    $con = false;
    if ($app) {
        //FIND CONTACT BY APP
        $db->query("SELECT * FROM wbcc_appartement_contact, wbcc_contact WHERE  idContactF = idContact AND idAppartementF = $app->idApp LIMIT 1");
        $con  = $db->single();
        if ($con) {
            $id = $con->idContact;
            $civilite = $civilite != "" ? $civilite : ($con->civiliteContact != null && $con->civiliteContact != "" ? $con->civiliteContact : $civilite);
            $prenom = $prenom != ""  ? $prenom : ($con->prenomContact != null && $con->prenomContact != "" ? $con->prenomContact : $prenom);
            $nom =  $nom != "" ?  $nom : ($con->nomContact != null && $con->nomContact != "" ? $con->nomContact : $nom);
            $email = $email ? $email : ($con->emailContact != null && $con->emailContact != "" ? $con->emailContact : $email);
            $tel = $tel != "" ? $tel : ($con->telContact != null && $con->telContact != "" ? $con->telContact : $tel);
        }
    } else {
        $numeroApp = "APP" . date("dmYHis") . $idUser;
        $db->query("INSERT INTO wbcc_appartement (numeroApp, etage, codePorte, occupant, typeOccupation, adresse, codePostal, ville, editDate, idImmeubleF, lot, batiment, numPoliceOccupant, compagnieAssuranceOccupant, idCompanyCopro) VALUES (:numeroApp, :etage, :codePorte, :occupant, :typeOccupation,  :adresse, :codePostal, :ville, :editDate, :idImmeubleF, :lot, :batiment, :numPoliceOccupant, :compagnieAssuranceOccupant, :idCompanyCopro)");
        $db->bind("numeroApp", $numeroApp, null);
        $db->bind("etage", $etage, null);
        $db->bind("codePorte", $porte, null);
        $db->bind("occupant", "$prenom $nom", null);
        $db->bind("typeOccupation", "LOCATAIRE", null);
        $db->bind("adresse", $adresse, null);
        $db->bind("codePostal", $codePostal, null);
        $db->bind("ville", $ville, null);
        $db->bind("editDate", date('Y-m-d H:i:s'), null);
        $db->bind("idImmeubleF", $idImmeuble, null);
        $db->bind("lot", $lot, null);
        $db->bind("batiment", $batiment, null);
        $db->bind("numPoliceOccupant", $numeroPolice, null);
        $db->bind("compagnieAssuranceOccupant", $compagnieAssurance, null);
        $db->bind("idCompanyCopro", $idCompagnieAssuranceF, null);

        if ($db->execute()) {
            $app  = findItemByColumn("wbcc_appartement", "numeroApp", $numeroApp);
        }
    }

    $con = saveContact($id, $civilite, $prenom, $nom, $email, $tel, "LOCATAIRE", $adresse, $codePostal, $ville, $idUser);
    if ($con) {
        //UPDATE APP
        $db->query("UPDATE  wbcc_appartement SET occupant=:occupant, idImmeubleF =:idImmeuble, typeOccupation=:typeOccupation, numPoliceOccupant=:numPoliceOccupant, compagnieAssuranceOccupant=:compagnieAssuranceOccupant,idCompanyCopro=:idCompanyCopro  WHERE idApp=$app->idApp");
        $db->bind("idImmeuble", $idImmeuble, null);
        $db->bind("occupant", $con->fullName, null);
        $db->bind("typeOccupation", "LOCATAIRE", null);
        $db->bind("numPoliceOccupant", $numeroPolice, null);
        $db->bind("compagnieAssuranceOccupant", $compagnieAssurance, null);
        $db->bind("idCompanyCopro", $idCompagnieAssuranceF, null);
        $db->execute();

        //LINK APP - CONTACT
        $db->query("SELECT * FROM wbcc_appartement_contact WHERE  idContactF = :idContact1 AND idAppartementF = :idAppartement1 LIMIT 1");
        $db->bind("idContact1", $con->idContact, null);
        $db->bind("idAppartement1", $app->idApp, null);
        $appCon  = $db->single();
        if ($appCon) {
        } else {
            $db->query("INSERT INTO wbcc_appartement_contact (idContactF, idAppartementF) VALUES (:idContact, :idAppartement)");
            $db->bind("idContact", $con->idContact, null);
            $db->bind("idAppartement", $app->idApp, null);
            if ($db->execute()) {
            }
        }

        //LINK OP-VOISIN
        $db->query("SELECT * FROM wbcc_contact_opportunity WHERE  idContactF = :idContact AND idOpportunityF = :idOpportunity LIMIT 1");
        $db->bind("idContact", $con->idContact, null);
        $db->bind("idOpportunity", $idOP, null);
        $search  = $db->single();
        if ($search) {
        } else {
            $db->query("INSERT INTO wbcc_contact_opportunity (idContactF, idOpportunityF) VALUES ($con->idContact, $idOP)");
            $db->execute();
        }
    }
    return $con;
}

function saveInfosArtisan($id, $civilite, $prenom, $nom, $tel, $email, $adresse, $codePostal, $ville, $idUser, $idOP, $idImmeuble)
{
    $db = new Database();
    $con = saveContact($id, $civilite, $prenom, $nom, $email, $tel, "ARTISAN", $adresse, $codePostal, $ville, $idUser);
    if ($con) {
        //LINK OP ARTISAN
        $db->query("SELECT * FROM wbcc_contact_opportunity WHERE  idContactF = :idContact AND idOpportunityF = :idOpportunity LIMIT 1");
        $db->bind("idContact", $con->idContact, null);
        $db->bind("idOpportunity", $idOP, null);
        $search  = $db->single();
        if ($search) {
        } else {
            $db->query("INSERT INTO wbcc_contact_opportunity (idContactF, idOpportunityF) VALUES ($con->idContact, $idOP)");
            $db->execute();
        }
    }
    return $con;
}

function saveRechercheFuite($id, $idOP, $numeroOP, $dateRF, $idAuteur, $auteur, $origine, $reparationCause, $auteurReparation, $etape, $etat, $typeRF, $auteurRF, $dateReparationFuite, $descriptionTravaux, $idArtisan, $idSocieteArtisan, $idVoisin, $chezVoisin, $siAccessible, $signatureVictim, $signatureResponsable, $pieceFuyarde, $equipementFuyard, $origineFuiteEquipement, $faireRF, $siDegatVoisin, $siConfierSinistre, $newIdOp, $siJustificatif, $siSignatureJustificatif, $documentJustificatif)
{
    $db = new Database();
    $op = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
    $search = false;
    $numero = "";
    if ($id == "0" || $id == "") {
        //TEST IF APP EXIST
        if ($idOP != "" || $idOP != "") {
            $search = findItemByColumn("wbcc_recherche_fuite", "idOpportunityF", $idOP);
        }
        if ($search) {
            $id = $search->idRF;
            $db->query("UPDATE  wbcc_recherche_fuite  SET  numeroOP=:numeroOP, dateRF=:dateRF, editDate=:editDate, idOpportunityF=:idOpportunityF, idAuteurRF=:idAuteurRF, auteurRF=:auteurRF, etatRF=:etatRF, origineFuite=:origineFuite, origineFuiteSinistre=:origineFuiteSinistre, reparationCause=:reparationCause, reparateurCause=:reparateurCause, etape=:etape, typeRF=:typeRF, auteurRFExterne=:auteurRFExterne, dateReparationFuite=:dateReparationFuite, descriptionTravaux=:descriptionTravaux,idArtisanF=:idArtisanF,idSocieteArtisanF=:idSocieteArtisanF, idVoisinF=:idVoisinF, chezVoisin=:chezVoisin, siAccessible=:siAccessible, pieceFuyarde=:pieceFuyarde, equipementFuyard=:equipementFuyard, origineFuiteEquipement=:origineFuiteEquipement, siDegatVoisin=:siDegatVoisin, siConfierSinistre=:siConfierSinistre, numeroLotOP=:numeroLotOP,  siJustificatif=:siJustificatif, siSignatureJustificatif=:siSignatureJustificatif, documentJustificatif=:documentJustificatif   WHERE idRF = $id");
        } else {
            $numero = "CON" . date('dmYHis') . $idAuteur;
            $db->query("INSERT INTO wbcc_recherche_fuite(numeroRF, numeroOP, dateRF, editDate, idOpportunityF, idAuteurRF, auteurRF, etatRF, origineFuite, origineFuiteSinistre, reparationCause, reparateurCause, etape, typeRF, auteurRFExterne, dateReparationFuite, descriptionTravaux,idArtisanF,idSocieteArtisanF, idVoisinF, chezVoisin, siAccessible, pieceFuyarde, equipementFuyard, origineFuiteEquipement, siDegatVoisin, siConfierSinistre, numeroLotOP, siJustificatif, siSignatureJustificatif, documentJustificatif ) VALUES (:numeroRF, :numeroOP, :dateRF, :editDate, :idOpportunityF, :idAuteurRF, :auteurRF, :etatRF, :origineFuite, :origineFuiteSinistre, :reparationCause, :reparateurCause, :etape, :typeRF, :auteurRFExterne, :dateReparationFuite, :descriptionTravaux, :idArtisanF, :idSocieteArtisanF, :idVoisinF, :chezVoisin, :siAccessible, :pieceFuyarde, :equipementFuyard, :origineFuiteEquipement, :siDegatVoisin, :siConfierSinistre, :numeroLotOP, :siJustificatif, :siSignatureJustificatif, :documentJustificatif )");
            $db->bind("numeroRF", $numero, null);
        }
    } else {
        //UPDATE
        $db->query("UPDATE  wbcc_recherche_fuite  SET  numeroOP=:numeroOP, dateRF=:dateRF, editDate=:editDate, idOpportunityF=:idOpportunityF, idAuteurRF=:idAuteurRF, auteurRF=:auteurRF, etatRF=:etatRF, origineFuite=:origineFuite, origineFuiteSinistre=:origineFuiteSinistre, reparationCause=:reparationCause, reparateurCause=:reparateurCause, etape=:etape, typeRF=:typeRF, auteurRFExterne=:auteurRFExterne, dateReparationFuite=:dateReparationFuite, descriptionTravaux=:descriptionTravaux,idArtisanF=:idArtisanF,idSocieteArtisanF=:idSocieteArtisanF, idVoisinF=:idVoisinF, chezVoisin=:chezVoisin, siAccessible=:siAccessible, pieceFuyarde=:pieceFuyarde, equipementFuyard=:equipementFuyard, origineFuiteEquipement=:origineFuiteEquipement, siDegatVoisin=:siDegatVoisin, siConfierSinistre=:siConfierSinistre, numeroLotOP=:numeroLotOP, siJustificatif=:siJustificatif, siSignatureJustificatif=:siSignatureJustificatif, documentJustificatif=:documentJustificatif  WHERE idRF = :idRF");
        $db->bind("idRF", $id, null);
    }
    $db->bind("numeroOP", $numeroOP, null);
    $db->bind("dateRF", $dateRF, null);
    $db->bind("idOpportunityF", $idOP, null);
    $db->bind("etatRF", $etat, null);
    $db->bind("origineFuite", ($origine), null);
    $db->bind("origineFuiteSinistre", (($chezVoisin == "1")  ? "Chez le voisin"  : $origine), null);
    $db->bind("reparationCause", $reparationCause, null);
    $db->bind("reparateurCause", $auteurReparation, null);
    $db->bind("siAccessible", $siAccessible, null);
    $db->bind("etape", $etape, null);
    $db->bind("idAuteurRF", $idAuteur, null);
    $db->bind("auteurRF", $auteur, null);
    $db->bind("typeRF", $typeRF, null);
    $db->bind("editDate", date('Y-m-d H:i:s'), null);
    $db->bind("auteurRFExterne", $auteurRF, null);
    $db->bind("dateReparationFuite", $dateReparationFuite != null  && $dateReparationFuite != "" ? convertDateMysqlFormat($dateReparationFuite) : null, null);
    $db->bind("descriptionTravaux", $descriptionTravaux, null);
    $db->bind("idArtisanF", $idArtisan != "" && $idArtisan != "0" ? $idArtisan : null, null);
    $db->bind("idSocieteArtisanF", $idSocieteArtisan, null);
    $db->bind("idVoisinF", $idVoisin != "" && $idVoisin != "0" ? $idVoisin : null, null);
    $db->bind("chezVoisin", $chezVoisin == 1 ? 1 : ($search ? $search->chezVoisin : 0), null);
    $db->bind("pieceFuyarde", $pieceFuyarde, null);
    $db->bind("equipementFuyard", $equipementFuyard, null);
    $db->bind("origineFuiteEquipement", $origineFuiteEquipement, null);
    $db->bind("siDegatVoisin", $siDegatVoisin,  null);
    $db->bind("siConfierSinistre",  $siConfierSinistre, null);
    $db->bind("numeroLotOP",  $op->numeroLot, null);
    $db->bind("siJustificatif",  $siJustificatif, null);
    $db->bind("siSignatureJustificatif",  $siSignatureJustificatif, null);
    $db->bind("documentJustificatif",  $documentJustificatif, null);
    if ($db->execute()) {
        //UPDATE RT
        $db->query("UPDATE wbcc_releve_technique SET  lieuDegat=:lieuDegat, reparerCauseDegat=:reparerCauseDegat, reparateurDegat=:reparateurDegat, rechercheFuite=:rechercheFuite, chercheurFuite=:chercheurFuite  WHERE idOpportunityF = $idOP");
        $db->bind("lieuDegat", (($chezVoisin == "1")  ? "Chez le voisin"  : $origine), null);
        $db->bind("reparerCauseDegat", $reparationCause, null);
        $db->bind("reparateurDegat", $auteurReparation, null);
        $db->bind("rechercheFuite", $etat == 0 ? "Non" : "Oui", null);
        $db->bind("chercheurFuite", $auteurRF, null);
        $db->execute();
        if ($newIdOp != "") {
            $db->query("UPDATE wbcc_releve_technique SET  lieuDegat=:lieuDegat, reparerCauseDegat=:reparerCauseDegat, reparateurDegat=:reparateurDegat, rechercheFuite=:rechercheFuite, chercheurFuite=:chercheurFuite  WHERE idOpportunityF = $newIdOp");
            $db->bind("lieuDegat", "Chez Vous", null);
            $db->bind("reparerCauseDegat", $reparationCause, null);
            $db->bind("reparateurDegat", $auteurReparation, null);
            $db->bind("rechercheFuite", $etat == 0 ? "Non" : "Oui", null);
            $db->bind("chercheurFuite", $auteurRF, null);
            $db->execute();
        }
        //GENERATE CONSTAT DDE
        if ($faireRF == "0") {
            if ($signatureVictim != "attestation") {
                $signV = "";
                $signR = "";
                if ($signatureVictim != "") {
                    if (!str_starts_with($signatureVictim, "SignDDE")) {
                        $bin = base64_decode($signatureVictim);
                        $im = imageCreateFromString($bin);
                        if (!$im) {
                            $signV = "";
                        } else {
                            imagetruecolortopalette($im, FALSE, 5); # convert image from TC to palette
                            $bg = imagecolorat($im, 0, 0); # get the bg colour's index in palette
                            imagecolorset($im, $bg, 240, 128, 128);
                            $signV = "SignDDEdo$idOP.png";
                            $img_file = "../documents/rechercheFuite/signaturesDDE/$signV";
                            imagepng($im, $img_file, 0);
                        }
                    } else {
                        $signV = $signatureVictim;
                    }
                }
                if ($signatureResponsable != "") {
                    if (!str_starts_with($signatureResponsable, "SignDDE")) {
                        $bin = base64_decode($signatureResponsable);
                        $im = imageCreateFromString($bin);
                        if (!$im) {
                            $signR = "";
                        } else {
                            imagetruecolortopalette($im, FALSE, 5); # convert image from TC to palette
                            $bg = imagecolorat($im, 0, 0); # get the bg colour's index in palette

                            imagecolorset($im, $bg, 220, 220, 220);
                            $signR = "SignDDEvoisin$idOP.png";
                            $img_file = "../documents/rechercheFuite/signaturesDDE/$signR";
                            imagepng($im, $img_file, 0);
                        }
                    } else {
                        $signR = $signatureResponsable;
                    }
                }
                $db->query("UPDATE wbcc_recherche_fuite SET  signatureDO=:signatureDO, signatureVoisin=:signatureVoisin, isVictimSigned=:isVictimSigned, isResponsableSigned=:isResponsableSigned, isDocumentCompleted=:isDocumentCompleted, dateSignatureVictime=:dateSignatureVictime,dateSignatureResponsable=:dateSignatureResponsable  WHERE idOpportunityF=$idOP");
                $db->bind("signatureDO", $signV, null);
                $db->bind("signatureVoisin", $signR, null);
                $db->bind("isVictimSigned", $signV != null && $signV != "" ? 1 : 0, null);
                $db->bind("isResponsableSigned",  $signR != null && $signR != "" ? 1 : 0, null);
                $db->bind("dateSignatureVictime",  $signV != null && $signV != "" ?  date("Y-m-d H:i:s") : null, null);
                $db->bind("dateSignatureResponsable",  $signR != null && $signR != "" ? date("Y-m-d H:i:s") : null, null);
                $db->bind("isDocumentCompleted",  $signR != null && $signR != "" && $signV != null && $signV != "" ? 1 : 0, null);
                $db->execute();
                //create activity completer DDE
                $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$op->gestionnaire LIMIT 1");
                $ges = $db->single();
                if ($signR != null && $signR != "" && $signV != null && $signV != "") {
                    $regarding = "Faire Constat DDE";
                    $activity = findActivityByIdOP($idOP, 24);
                    if ($activity) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'True', editDate=:editDate, realisedBy=:realisedBy, idRealisedBy =:idRealisedBy WHERE idActivity = $activity->idActivity");
                        $db->bind("editDate", date("Y-m-d H:i:s"), null);
                        $db->bind("realisedBy", $auteur, null);
                        $db->bind("idRealisedBy", $idAuteur, null);
                        $db->execute();
                        $db->execute();
                    } else {
                        createNewActivity($idOP, $op->name, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $op->name . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", 24, "", $idAuteur, $auteur);
                    }
                    $db->query("UPDATE wbcc_opportunity SET  genererConstatDDE=1   WHERE idOpportunity=$idOP");
                    $db->execute();
                    //CREATE TACHE ENVOI CONSTAT
                    $activity = findActivityByIdOP($idOP, 44);
                    if ($activity) {
                        // $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                        // $db->execute();
                    } else {
                        createNewActivity($idOP,  $op->name, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,   $op->name . '-' .  "Envoyer Constat DDE", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 44);
                    }
                } else {
                    $regarding = "Compléter Constat DDE";
                    $activity = findActivityByIdOP($idOP, 24);
                    if ($activity) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                        $db->execute();
                    } else {
                        createNewActivity($idOP, $op->name, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  $op->name . '-' .  $regarding, "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", 24);
                    }
                }

                $constatDDE = file_get_contents(URLROOT . "/public/documents/rechercheFuite/constatDDE.php?idOP=$idOP");
                $constatDDE = str_replace('"', "", $constatDDE);
                if ($constatDDE != "") {
                    $db = new Database();
                    //SAVE DOCUMENT
                    $db->query("UPDATE wbcc_recherche_fuite SET  documentConstatDDE=:file, idAuteurGenererConstatDDE=:idAuteur, dateGenererConstatDDE=:date WHERE idOpportunityF=$idOP");
                    $db->bind("idAuteur", $idAuteur, null);
                    $db->bind("date", date("Y-m-d H:i:s"), null);
                    $db->bind("file", $constatDDE, null);
                    $db->execute();

                    $db->query("UPDATE wbcc_opportunity SET  documentConstatDDE=:file, idAuteurGenererConstatDDE=:idAuteur, dateGenererConstatDDE=:date WHERE idOpportunity=$idOP");
                    $db->bind("idAuteur", $idAuteur, null);
                    $db->bind("date", date("Y-m-d H:i:s"), null);
                    $db->bind("file", $constatDDE, null);
                    $db->execute();

                    if ($newIdOp != "") {
                        $db->query("UPDATE wbcc_opportunity SET  documentConstatDDE=:file, idAuteurGenererConstatDDE=:idAuteur, dateGenererConstatDDE=:date WHERE idOpportunity=$newIdOp");
                        $db->bind("idAuteur", $idAuteur, null);
                        $db->bind("date", date("Y-m-d H:i:s"), null);
                        $db->bind("file", $constatDDE, null);
                        $db->execute();
                    }

                    $numeroDoc = 'DOC0' . date("dmYHis") . $idAuteur;
                    $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");
                    $db->bind("publie",  strpos('Complet', $constatDDE) != 0 ? "1" : "0", null);
                    $db->bind("source", "EXTRA", null);
                    $db->bind("numeroDocument", $numeroDoc, null);
                    $db->bind("nomDocument", $constatDDE, null);
                    $db->bind("urlDocument", $constatDDE, null);
                    $db->bind("commentaire", "", null);
                    $db->bind("createDate",  date("Y-m-d H:i:s"), null);
                    $db->bind("guidHistory", null, null);
                    $db->bind("typeFichier", "Adobe Acrobat Document", null);
                    $db->bind("size", null, null);
                    $db->bind("guidUser", "", null);
                    $db->bind("idUtilisateurF", $idAuteur, null);
                    $db->bind("auteur", $auteur, null);
                    if ($db->execute()) {
                        $db->query("SELECT * FROM wbcc_document WHERE numeroDocument='$numeroDoc' LIMIT 1");
                        $document = $db->single();
                        $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($idOP,$document->idDocument)");
                        $db->execute();
                        if ($newIdOp != "") {
                            $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($newIdOp,$document->idDocument)");
                            $db->execute();
                        }
                    }

                    //SAVE HISTORIQUE
                    createHistorique("Génération de constat DDE : $constatDDE", $auteur, $idAuteur, $idOP);

                    if ($newIdOp != "") {
                        //SAVE HISTORIQUE
                        createHistorique("Génération de constat DDE : $constatDDE", $auteur, $idAuteur, $newIdOp);
                    }
                }
            }
        }


        if ($id != "" && $id != "0") {
            return (findItemByColumn("wbcc_recherche_fuite", "idRF", $id));
        } else {
            return (findItemByColumn("wbcc_recherche_fuite", "numeroRF", $numero));
        }
    } else {
        return false;
    }
    return false;
}

function generateJustificatif($idOP, $user, $idUser, $modeSignature, $signature, $type,  $newIdOp)
{
    $fileAttestationReparation = "";
    if ($type == "attestation") {
        $sign = "";
        if ($signature != null && $signature != "") {
            if (!str_contains("signatureAttestation_", $signature)) {
                $bin = base64_decode($signature);
                $im = imageCreateFromString($bin);
                if (!$im) {
                    $sign = "";
                } else {
                    imagetruecolortopalette($im, FALSE, 5); # convert image from TC to palette
                    $bg = imagecolorat($im, 0, 0); # get the bg colour's index in palette
                    imagecolorset($im, $bg, 255, 255, 255);
                    $sign = "signatureAttestation_" . "$idOP.png";
                    $img_file = "../documents/rechercheFuite/signaturesAttestation/$sign";
                    imagepng($im, $img_file, 0);
                }
            } else {
                $sign = $signature;
            }
        }

        //GENERER ATTESTATION
        $fileAttestationReparation = file_get_contents(URLROOT . "/public/documents/rechercheFuite/attestationReparationFuite.php?idOP=$idOP&modeSignature=$modeSignature");
    } else {
        $fileAttestationReparation = file_get_contents(URLROOT . "/public/documents/rechercheFuite/factureReparationFuite.php?idOP=$idOP");
    }

    $fileAttestationReparation = str_replace('"', "", $fileAttestationReparation);
    if ($fileAttestationReparation != "") {
        $db = new Database();
        //SAVE DOCUMENT

        $db->query("UPDATE wbcc_recherche_fuite SET siJustificatif=1, documentJustificatif=:documentJustificatif, dateJustificatif=:dateJustificatif WHERE idOpportunityF=$idOP");
        $db->bind("dateJustificatif", date("Y-m-d H:i:s"), null);
        $db->bind("documentJustificatif", $fileAttestationReparation, null);
        $db->execute();

        if ($sign != null && $sign != "") {
            $db->query("UPDATE wbcc_recherche_fuite SET siSignatureJustificatif=1, documentJustificatif=:documentJustificatif, dateJustificatif=:dateJustificatif, signatureJustificatif=:signatureJustificatif WHERE idOpportunityF=$idOP");
            $db->bind("dateJustificatif", date("Y-m-d H:i:s"), null);
            $db->bind("documentJustificatif", $fileAttestationReparation, null);
            $db->bind("signatureJustificatif", $sign, null);
            $db->execute();

            $db->query("UPDATE wbcc_opportunity SET justificatifReparation=1, docJustificatifReparation=:file, dateJustificatifReparation=:dateSignature, idAuteurJustificatifReparation=:idAuteurJustificatifReparation WHERE idOpportunity=$idOP");
            $db->bind("idAuteurJustificatifReparation", $user['fullName'], null);
            $db->bind("dateSignature", date("Y-m-d H:i:s"), null);
            $db->bind("file", $fileAttestationReparation, null);
            $db->execute();

            if ($newIdOp != "") {
                $db->query("UPDATE wbcc_opportunity SET justificatifReparation=1, docJustificatifReparation=:file, dateJustificatifReparation=:dateSignature, idAuteurJustificatifReparation=:idAuteurJustificatifReparation WHERE idOpportunity=$newIdOp");
                $db->bind("idAuteurJustificatifReparation", $user['fullName'], null);
                $db->bind("dateSignature", date("Y-m-d H:i:s"), null);
                $db->bind("file", $fileAttestationReparation, null);
                $db->execute();
            }
        }

        $numeroDoc = 'DOC1' . date("dmYHis") . $idUser;
        $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");
        $db->bind("publie",  strpos('Sign', $fileAttestationReparation) != 0 ? "1" : "0", null);
        $db->bind("source", "EXTRA", null);
        $db->bind("numeroDocument", $numeroDoc, null);
        $db->bind("nomDocument", $fileAttestationReparation, null);
        $db->bind("urlDocument", $fileAttestationReparation, null);
        $db->bind("commentaire", "", null);
        $db->bind("createDate",  date("Y-m-d H:i:s"), null);
        $db->bind("guidHistory", null, null);
        $db->bind("typeFichier", "Adobe Acrobat Document", null);
        $db->bind("size", null, null);
        $db->bind("guidUser", $user['numeroContact'], null);
        $db->bind("idUtilisateurF", $user['idUtilisateur'], null);
        $db->bind("auteur", $user['fullName'], null);
        if ($db->execute()) {
            $db->query("SELECT * FROM wbcc_document WHERE numeroDocument='$numeroDoc' LIMIT 1");
            $document = $db->single();
            $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($idOP,$document->idDocument)");
            $db->execute();
            if ($newIdOp != "") {
                $db->query("INSERT INTO wbcc_opportunity_document( idOpportunityF, idDocumentF) VALUES ($newIdOp,$document->idDocument)");
                $db->execute();
            }
        }

        //SAVE HISTORIQUE
        createHistorique("Génération Attesttation de Réparartion de Fuite : $fileAttestationReparation", $user['fullName'], $user['idUtilisateur'], $idOP);
        if ($newIdOp != "") {
            createHistorique("Génération Attesttation de Réparartion de Fuite : $fileAttestationReparation", $user['fullName'],  $user['idUtilisateur'], $newIdOp);
        }
    }

    return  $fileAttestationReparation;
}

function updateParametre($nomColonne, $val)
{
    $db = new Database();

    $db->query("UPDATE wbcc_parametres SET $nomColonne=$val");
    return $db->execute();
}

function createOP($idOP, $idAuteurContact, $idAuteur, $numeroAuteur, $auteur, $idContact, $idCie, $compagnieAssurance, $numeroPolice, $siDegatVoisin, $idImmeuble, $idApp)
{
    $db = new Database();
    $contact = findItemByColumn("wbcc_contact", "idContact", $idContact);
    $op1 = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
    $searchOP = false;
    $numeroLot = "";
    if ($op1->numeroLot != null && $op1->numeroLot != "") {
        $numeroLot = $op1->numeroLot;
        $db->query("SELECT * FROM wbcc_opportunity WHERE numeroLot= $op1->numeroLot  AND idOpportunity != $idOP LIMIT 1");
        $searchOP = $db->single();
    }

    $name = '';
    $param = false;
    $opInsert = false;
    if ($searchOP) {
        $opInsert = findItemByColumn("wbcc_opportunity", "idOpportunity",  $searchOP->idOpportunity);
        $name  = $opInsert->name;
    } else {
        $db->query("SELECT * FROM wbcc_parametres LIMIT 1");
        $param = $db->single();
        $numero = str_pad(($param->numeroOP + 1), 4, '0', STR_PAD_LEFT);
        $name = "OP" . date("Y-m-d") . "-$numero";
    }

    //IF OP EXIST
    $ifOpUpdate = false;
    if ($searchOP) {
        $idOppF = $searchOP->idOpportunity;
        $db->query("UPDATE wbcc_opportunity SET  type=:type, typeSinistre=:typeSinistre,causeMission=:causeMission,typeIntervention=:typeIntervention, demandeValidation=0, dateDemandeValidation='" . date("Y-m-d H:i:s") . "', nomDO=:nomDOOP, guidContactClient=:guidContactClient, guidDO=:guidContactClient, idDOF=:idDOF, idContactClient=:idDOF, typeDO=:typeDO, source=:source, commentaire=:commentaire, origine=:origine, denominationComMRH=:compagnieAssurance4, idComMRHF=:idComMRHF,  policeMRH=:numeroPolice4, contactClient=:contactClient,  nomGestionnaireAppImm=:nomGestionnaireAppImm, idGestionnaireAppImm=:idGestionnaireAppImm, status=:status, guidGestionnaireAppImm=:guidGestionnaireAppImm, idVictimeContactF=:idVictimeContactF, nomVictime=:nomVictime, nomResponsable=:nomResponsable, idResponsableContactF=:idResponsableContactF WHERE idOpportunity=$idOppF");
        $ifOpUpdate = true;
    } else {
        $db->query("INSERT INTO wbcc_opportunity (numeroOpportunity, name, type, typeSinistre,causeMission,typeIntervention, demandeValidation, dateDemandeValidation,nomDO, guidContactClient, guidDO, idDOF, idContactClient, typeDO, source, commentaire, origine, denominationComMRH, idComMRHF,  policeMRH, contactClient, gestionnaire, numGestionnaire,  nomGestionnaireAppImm, status, commercial, guidGestionnaireAppImm, idGestionnaireAppImm, guidCommercial, idCommercial, idAuteurCreation, idVictimeContactF, nomVictime, nomResponsable, idResponsableContactF) VALUES (:numeroOpportunity, :name, :type, :typeSinistre,:causeMission,:typeIntervention, 0,'" . date("Y-m-d H:i:s") . "',  :nomDOOP, :guidContactClient, :guidContactClient, :idDOF, :idDOF, :typeDO, :source, :commentaire, :origine, :compagnieAssurance4, :idComMRHF, :numeroPolice4, :contactClient, :gestionnaire, :numGestionnaire, :nomGestionnaireAppImm, :status, :commercial, :guidGestionnaireAppImm, :idGestionnaireAppImm, :guidCommercial,:idCommercial, :idCommercial, :idVictimeContactF, :nomVictime, :nomResponsable, :idResponsableContactF)");
        $db->bind("numeroOpportunity", $name, null);
        $db->bind("name", $name, null);
        $db->bind("commercial", $auteur, null);
        $db->bind("guidCommercial", $numeroAuteur, null);
        $db->bind("idCommercial", $idAuteur, null);
        $db->bind("gestionnaire", "518", null);
        $db->bind("numGestionnaire", "WBCC000", null);
    }
    $db->bind("type", "Sinistres", null);
    $db->bind("typeSinistre", "Partie privative exclusive", null);
    $db->bind("causeMission", "Sinistre", null);
    $db->bind("typeIntervention",  "Dégâts des eaux", null);
    $db->bind("commentaire", "OP créée àn partir de l'OP $op1->name", null);
    $db->bind("nomDOOP",  $contact->fullName, null);
    $db->bind("guidContactClient", $contact->numeroContact, null);
    $db->bind("idDOF", $contact->idContact, null);
    $db->bind("typeDO", "Particuliers", null);
    $db->bind("compagnieAssurance4", $compagnieAssurance, null);
    $db->bind("idComMRHF", $idCie != "0" && $idCie != "" ?  $idCie : null, null);
    $db->bind("numeroPolice4", $numeroPolice, null);
    $db->bind("contactClient", $contact->fullName, null);
    $db->bind("source", $idAuteur, null);
    $db->bind("guidGestionnaireAppImm", $op1->guidGestionnaireAppImm, null);
    $db->bind("nomGestionnaireAppImm", $op1->nomGestionnaireAppImm, null);
    $db->bind("idGestionnaireAppImm", $op1->idGestionnaireAppImm, null);
    $db->bind("status", 'Open', null);
    $db->bind("origine", "Recherche de Fuite $op1->name", null);
    $db->bind("idVictimeContactF", $op1->idContactClient, null);
    $db->bind("nomVictime", "$op1->contactClient", null);
    $db->bind("nomResponsable", $contact->fullName, null);
    $db->bind("idResponsableContactF", $contact->idContact, null);

    if ($db->execute()) {
        $opInsert = findItemByColumn("wbcc_opportunity", "name", $name);
        $idOppF = $opInsert->idOpportunity;
        if ($opInsert && $ifOpUpdate == false) {
            if ($param) {
                updateParametre("numeroOP", ($param->numeroOP + 1));
                //UPDATE NUMERO LOT FOR OP
                if ($numeroLot == "") {
                    $numeroLot = ($param->numeroLotOP + 1);
                    updateParametre("numeroLotOP", ($param->numeroLotOP + 1));
                }
            }
            $db->query("UPDATE wbcc_opportunity SET numeroLot='$numeroLot' WHERE idOpportunity=$idOppF OR idOpportunity=$idOP");
            $db->execute();

            $db->query("UPDATE wbcc_recherche_fuite SET numeroLotOP='$numeroLot' WHERE idOpportunityF=$idOppF OR idOpportunityF=$idOP");
            $db->execute();
            //CREATE ACTIVITY
            $dateStartActivity = new DateTime();
            $dateEndActivity = ($dateStartActivity->modify("+2 days"))->format('Y-m-d H:i:s');
            createNewActivity($opInsert->idOpportunity, $opInsert->name, $idAuteur, "$auteur", "", $opInsert->name . "-Faire signer la délégation de gestion", "", date("Y-m-d H:i:s"), $dateEndActivity, "Tâche à faire", "False", 0, 1);
            if ($siDegatVoisin == "1") {
                createNewActivity($opInsert->idOpportunity, $opInsert->name, 518, "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", $opInsert->name . "-Faire la Télé-Expertise", "", date("Y-m-d H:i:s"), $dateEndActivity, "Tâche à faire", "False", 0, 2);
                createNewActivity($opInsert->idOpportunity, $opInsert->name, $idAuteur, "$auteur", "", $opInsert->name . "-Programmer le RT", "", date("Y-m-d H:i:s"), $dateEndActivity, "Tâche à faire", "False", 0, 3);
            }
            createHistorique("Création de l'opportunité : $opInsert->name", $auteur, $idAuteur,  $opInsert->idOpportunity);

            $body = "Bonjour $contact->civiliteContact $contact->prenomContact $contact->nomContact , <br><br>
                    Votre dossier portant le N° <b>$opInsert->name</b> vient d'être ouvert chez WBCC. <br><br><br>
              Veuillez ne pas répondre à ce message envoyé automatiquement pour votre information.<br><br><b>WBCC Assistance</b><br><b>$auteur</b>";
            $r = new Role();
            $r::mailExtranetWithFiles($contact->emailContact, "CREATION DOSSIER : $opInsert->name", $body, ["gestion@wbcc.fr"], [], []);
        }
    }

    //SAVE OP-SYNDIC
    if ($op1->idGestionnaireAppImm != null && $op1->idGestionnaireAppImm != "") {
        $db->query("SELECT * FROM wbcc_company_opportunity WHERE idCompanyF=$op1->idGestionnaireAppImm AND idOpportunityF=$idOppF LIMIT 1");
        $companyOpp = $db->single();
        if (!($companyOpp)) {
            $db->query("INSERT INTO wbcc_company_opportunity(idCompanyF, idOpportunityF) VALUES ($op1->idGestionnaireAppImm,$idOppF)");
            $db->execute();
        }
    }

    //SAVE OP-ASSURANCE
    if ($idCie != "0" && $idCie != null && $idCie != "") {
        $db->query("SELECT * FROM wbcc_company_opportunity WHERE idCompanyF=$idCie AND idOpportunityF=$idOppF");
        $companyOpp = $db->single();
        if (!($companyOpp)) {
            $db->query("INSERT INTO wbcc_company_opportunity(idCompanyF, idOpportunityF) VALUES ($idCie,$idOppF)");
            $db->execute();
        }
        //UPDATE GUID com / COU 
        // $db->query("UPDATE wbcc_opportunity SET guidComMRH=:guidComMRH WHERE idOpportunity=$idOppF");
        // $db->bind("guidComMRH", $compagnieAssurance, null);
        // $db->execute();
    }

    //SAVE LOCATAIRE
    if ($contact) {
        //INSERT OP_CONTACT
        $db->query("SELECT * FROM wbcc_contact_opportunity WHERE idContactF=$contact->idContact AND idOpportunityF=$idOppF");
        $conOpp = $db->single();
        if ($conOpp) {
        } else {
            $db->query("INSERT INTO wbcc_contact_opportunity(idContactF, idOpportunityF) VALUES ($contact->idContact,$idOppF)");
            $db->execute();
        }
    }

    if ($idAuteurContact != null && $idAuteurContact != "") {
        $db->query("SELECT * FROM wbcc_contact_opportunity WHERE idContactF=$idAuteurContact AND idOpportunityF=$idOppF");
        $conOpp = $db->single();
        if ($conOpp) {
        } else {
            $db->query("INSERT INTO wbcc_contact_opportunity(idContactF, idOpportunityF) VALUES ($idAuteurContact,$idOppF)");
            $db->execute();
        }
    }

    if ($op1->idContactClient != null && $op1->idContactClient != "") {
        $db->query("SELECT * FROM wbcc_contact_opportunity WHERE idContactF=$op1->idContactClient AND idOpportunityF=$idOppF");
        $conOpp = $db->single();
        if ($conOpp) {
        } else {
            $db->query("INSERT INTO wbcc_contact_opportunity(idContactF, idOpportunityF) VALUES ($op1->idContactClient,$idOppF)");
            $db->execute();
        }
    }

    $immeuble = false;
    //SAVE IMMEUBLE OPPORTUNITY
    if ($idImmeuble != null && $idImmeuble != "") {
        $immeuble = findItemByColumn("wbcc_immeuble", "idImmeuble", $idImmeuble);
        $db->query("UPDATE wbcc_opportunity SET guidImmeuble=:guidImmeuble, idImmeuble=:idImmeuble WHERE idOpportunity=$idOppF");
        $db->bind("guidImmeuble", ($immeuble ? $immeuble->numeroImmeuble : null), null);
        $db->bind("idImmeuble", ($immeuble ? $immeuble->idImmeuble : null), null);
        $db->execute();

        $db->query("SELECT * FROM wbcc_opportunity_immeuble WHERE  idOpportunityF = :idOpportunityImm AND idImmeubleF = :idImmeubleImm LIMIT 1");
        $db->bind("idOpportunityImm",  $idOppF, null);
        $db->bind("idImmeubleImm", $idImmeuble, null);
        $search  = $db->single();
        if ($search) {
        } else {
            $db->query("INSERT INTO wbcc_opportunity_immeuble (idOpportunityF, idImmeubleF) VALUES (:idOpportunityImm2, :idImmeubleImm2)");
            $db->bind("idOpportunityImm2",  $idOppF, null);
            $db->bind("idImmeubleImm2", $idImmeuble, null);
            $db->execute();
        }
    }

    $appartement = false;
    //SAVE APPARTEMENT OPPORTUNITY
    if ($idApp != null && $idApp != "") {
        $appartement = findItemByColumn("wbcc_appartement", "idApp", $idApp);
        $db->query("UPDATE wbcc_opportunity SET guidAppartement=:guidAppartement, idAppartement=:idAppartement WHERE idOpportunity=$idOppF");
        $db->bind("guidAppartement", $appartement->numeroApp, null);
        $db->bind("idAppartement", $appartement->idApp, null);
        $db->execute();

        $db->query("SELECT * FROM wbcc_opportunity_appartement WHERE  idOpportunityF = :idOpportunity1 AND idAppartementF = :idAppartement2 LIMIT 1");
        $db->bind("idOpportunity1", $idOppF, null);
        $db->bind("idAppartement2", $appartement->idApp, null);
        $search  = $db->single();
        if ($search) {
        } else {
            $db->query("INSERT INTO wbcc_opportunity_appartement (idOpportunityF, idAppartementF) VALUES (:idOpportunity2, :idAppartement3)");
            $db->bind("idOpportunity2", $idOppF, null);
            $db->bind("idAppartement3", $appartement->idApp, null);
            if ($db->execute()) {
                $db->query("SELECT * FROM wbcc_opportunity_appartement WHERE  idOpportunityF = :idOpportunity3 AND idAppartementF = :idAppartement4 LIMIT 1");
                $db->bind("idOpportunity3", $idOppF, null);
                $db->bind("idAppartement4", $appartement->idApp, null);
                $search  = $db->single();
            }
        }
    }

    $idAppCon = null;
    if ($appartement && $contact) {
        $db->query("SELECT * FROM wbcc_appartement_contact WHERE  idAppartementF = $appartement->idApp AND idContactF = $contact->idContact LIMIT 1");
        $appCon = $db->single();
        $idAppCon = $appCon ? $appCon->idAppCon  : null;
    }

    /***SAVE SINISTRE(RELEVE TECHNIQUE)***/
    $db->query("SELECT * FROM wbcc_releve_technique WHERE idOpportunityF=$idOppF");
    $rt = $db->single();
    if ($rt) {
        $db->query("UPDATE wbcc_releve_technique SET numeroOP=:numeroOP, nature=:nature,date=:date1, numeroBatiment=:numeroBatiment, adresse=:adresse, codePostal=:codePostal, ville=:ville, codePorte=:codePorte, lieuDegat=:lieuDegat, autrePrecisionDegat=:precisionDegat, niveauEtage=:niveauEtage, partieConcernee=:partieConcernee, idOpportunityF=:idOpportunityF,idAppContactF=:idAppContactF,editDate=:editDate, repDeclarerSinistre=:repDeclarerSinistre, repDateDeclarationSinistre=:repDateDeclarationSinistre WHERE idOpportunityF=:idOpportunityF");
    } else {
        $numeroRT = 'RT' . date("dmYHis") . $idAuteur;
        $db->query("INSERT INTO wbcc_releve_technique (numeroRT, numeroOP, nature, date, numeroBatiment, adresse, codePostal, ville, codePorte, lieuDegat, autrePrecisionDegat, niveauEtage, partieConcernee, idOpportunityF,idAppContactF,createDate, editDate,repDeclarerSinistre, repDateDeclarationSinistre) VALUES (:numeroRT, :numeroOP, :nature,:date1, :numeroBatiment, :adresse, :codePostal, :ville, :codePorte, :lieuDegat, :precisionDegat, :niveauEtage, :partieConcernee, :idOpportunityF,:idAppContactF,:createDate, :editDate,:repDeclarerSinistre,:repDateDeclarationSinistre)");
        $db->bind("createDate", date("Y-m-d H:i:s"), null);
        $db->bind("numeroRT", $numeroRT, null);
    }

    $db->bind("numeroOP", $opInsert->name, null);
    $db->bind("nature", "Dégâts des eaux", null);
    $db->bind("date1", null, null);
    $db->bind("numeroBatiment", $appartement ? $appartement->batiment : "", null);
    $db->bind("adresse", $appartement ? $appartement->adresse : "", null);
    $db->bind("codePostal", $appartement ? $appartement->codePostal : "", null);
    $db->bind("ville",  $appartement ? $appartement->ville : "", null);
    $db->bind("codePorte", $appartement ? $appartement->codePorte : "", null);
    $db->bind("lieuDegat", "Chez vous", null);
    $db->bind("precisionDegat", "", null);
    $db->bind("niveauEtage", "", null);
    $db->bind("partieConcernee", "Privative", null);
    $db->bind("idOpportunityF", $idOppF, null);
    $db->bind("editDate", date("Y-m-d H:i:s"), null);
    $db->bind("idAppContactF", $idAppCon, null);
    $db->bind("repDeclarerSinistre", "", null);
    $db->bind("repDateDeclarationSinistre", "", null);
    $db->execute();

    //CREATE RV RT
    if ($siDegatVoisin == "1") {
        $dateRV = date("Y-m-d H:i");
        $dateFin = new DateTime($dateRV);
        $heureFin = ($dateFin->modify("+1 hours"))->format('H:i');

        $rv = false;
        $db->query("SELECT * FROM wbcc_rendez_vous WHERE typeRV='RTP' AND idOpportunityF=$idOppF  LIMIT 1");
        $rv = $db->single();

        $db->query("UPDATE wbcc_opportunity SET auditRvRt=1, dateRdvRT='$dateRV' WHERE idOpportunity = $idOppF");
        $db->execute();
        if ($rv) {
            $db->query("UPDATE wbcc_rendez_vous SET idAppExtra=:idAppExtra,idAppGuid=:idAppGuid,numeroOP=:numeroOP,adresseRV=:adresseRV,nomDO=:nomDO,idRVGuid=:idRVGuid, moyenTechnique=:moyenTechnique, conclusion=:conclusion, idAppConF=:idAppConF, idCampagneF=:idCampagneF, idContactGuidF=:idContactGuidF,idContactF=:idContactF, typeRV=:typeRV, editDate=:editDate WHERE idRV=$rv->idRV");
        } else {
            $numeroRV = "RV" . date('dmYHis') . $idOppF . $idAuteur;
            $db->query("INSERT INTO wbcc_rendez_vous(numero,dateRV,heureDebut,heureFin,idAppExtra,idAppGuid, expert, idExpertF, numeroOP,adresseRV,nomDO,idRVGuid, moyenTechnique, conclusion, idAppConF, idCampagneF,idContactGuidF, idContactF, idOpportunityF, typeRV,createDate, editDate, auteur,idAuteur ) VALUES (:numero,:dateRV,:heureDebut,:heureFin,:idAppExtra,:idAppGuid, :expert, :idExpertF, :numeroOP,:adresseRV,:nomDO,:idRVGuid, :moyenTechnique, :conclusion, :idAppConF, :idCampagneF,:idContactGuidF, :idContactF, :idOpportunityF, :typeRV,:createDate, :editDate, :auteur,:idAuteur)");
            $db->bind("numero", $numeroRV, null);
            $db->bind("createDate", $dateRV, null);
            $db->bind("auteur", $auteur, null);
            $db->bind("idAuteur", $idAuteur, null);
            $db->bind("idOpportunityF", $idOppF, null);
            $db->bind("dateRV",  date("Y-m-d"), null);
            $db->bind("heureDebut", date("H:i"), null);
            $db->bind("heureFin", $heureFin, null);
            $db->bind("expert", $auteur, null);
            $db->bind("idExpertF", $idAuteur, null);
        }

        $db->bind("idAppExtra", ($appartement ? $appartement->idApp : null), null);
        $db->bind("idAppGuid", ($appartement ? $appartement->numeroApp : null), null);
        $db->bind("numeroOP", $opInsert->name, null);
        $db->bind("adresseRV", ($immeuble ? $immeuble->adresse : ""), null);
        $db->bind("nomDO", $op1->nomGestionnaireAppImm, null);
        $db->bind("idRVGuid", null, null);
        $db->bind("moyenTechnique", "", null);
        $db->bind("conclusion", "", null);
        $db->bind("idAppConF", $idAppCon, null);
        $db->bind("idCampagneF", null, null);
        $db->bind("idContactGuidF", $contact->numeroContact, null);
        $db->bind("idContactF", $idContact, null);
        $db->bind("typeRV", "RTP", null);
        $db->bind("editDate", $dateRV, null);
        if ($db->execute()) {
            if ($rv) {
                $idRV = $rv->idRV;
            } else {
                $idRV =  findItemByColumn("wbcc_rendez_vous", "numero", $numeroRV)->idRV;
            }
            //LINK RV RT
            $rt = findItemByColumn("wbcc_releve_technique", "idOpportunityF", $idOppF);
            if ($rt) {
                $db->query("UPDATE wbcc_releve_technique SET idRVF=:idRV WHERE idRT =:idRT");
                $db->bind("idRV", $idRV, null);
                $db->bind("idRT", $rt->idRT, null);
                $db->execute();
            }
            //CLOSE ACTIVITY RV RT
            $regarding = "$opInsert->name - Prendre RDV RT";
            closeActivityByOPAndRegarding($idOppF, $opInsert->name, "$regarding", "", $auteur, $numeroAuteur, $idAuteur, "", "$regarding : effectué le " . date('d/m/Y H:i:s'), "$regarding : effectué le " . date('d/m/Y H:i:s'), $idContact,  date("Y-m-d"), "", "3", "", "", "", "", "", "", "", "", ($immeuble ? $immeuble->adresse : ""));
        }
    }
    return $idOppF;
}
