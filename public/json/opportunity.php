<?php
header('Access-Control-Allow-Origin: *');
require_once "../../app/config/config.php";
require_once "../../app/libraries/Database.php";
require_once "../../app/libraries/SMTP.php";
require_once "../../app/libraries/PHPMailer.php";
require_once "../../app/libraries/Role.php";
require_once "../../app/libraries/Utils.php";
require_once "../../app/libraries/Model.php";
require_once "../../app/models/Opportunity.php";

if (isset($_GET['action'])) {
    $db = new Database();
    $action = $_GET['action'];

    

    if ($action == 'saveAuditPenalite') {
        extract($_POST);
        $today = date("Y-m-d H:i:s");

        if ($statusDossier == "Won" || ($statusDossier == "Lost" && $motifCloture == "Désistement")) {
            $db->query("UPDATE wbcc_opportunity SET montantAEncaisser=:montantAEncaisser, montantOp=:montantOp, auditReglement=:auditReglement, idAuteurAuditReglement=$idAuteur,  dateAuditReglement=:dateAuditReglement, siConfierTravaux=:siConfierTravaux, siPaiementWBCC=:siPaiementWBCC , status=:status, typeTranche=:typeTranche WHERE idOpportunity = $idOP");
            $db->bind("montantOp", $montantPC, null);
            $db->bind("montantAEncaisser", $montantAEncaisser, null);
            $db->bind("auditReglement",  1, null);
            $db->bind("siConfierTravaux",  $siTravaux != "undefined" && $siTravaux != "" ? $siTravaux : 0, null);
            $db->bind("siPaiementWBCC",  $siCompteWBCC, null);
            $db->bind("dateAuditReglement",  date("Y-m-d H:i:s"), null);
            $db->bind("status",  $statusDossier, null);
            $db->bind("typeTranche",  $statusDossier == "Won" ? 0 : 8, null);
            $db->execute();

            if ($siCompteWBCC != "2") {
                //UPDATE DEVIS repRecepteurImmediat
                $repRecepteurImmediat = $siCompteWBCC == "1" ? "oui" : "non";
                $db->query("SELECT *  FROM wbcc_opportunity o, wbcc_opportunity_devis od, wbcc_devis d WHERE o.idOpportunity = od.idOpportunityF AND od.idDevisF = d.idDevis AND o.idOpportunity=$idOP LIMIT 1");
                $devis =  $db->single();
                if ($devis) {
                    $db->query("UPDATE wbcc_devis SET repRecepteurImmediat='$repRecepteurImmediat' WHERE idDevis = $devis->idDevis");
                    $db->execute();
                } else {
                    //CREATE DEVIS
                    $numero = "Devis_" . date("YmdHis") . "$idOP$idAuteur";
                    $db->query("INSERT INTO `wbcc_devis`(`numeroDevis`, `idRTF`,  `numeroDevisWBCC`, `dateGenerationDevis`, `devisFile`, `commentaireDevis`, `montantTotal`, montantAvantExpertise, montantHT, taux, `editDate`, `createDate`, `idUserF`, artisan, idArtisanF, dureeTravaux, repRecepteurImmediat) VALUES (:numeroDevis,:idRTF,:numeroDevisWBCC,:dateGenerationDevis,:devisFile,:commentaireDevis,:montantTotal, :montantTotal, :montantHT, :taux, :editDate,:createDate,:idUserF,:artisan, :idArtisanF, :dureeTravaux, :repRecepteurImmediat)");
                    $db->bind("createDate", date("Y-m-d H:i:s"), null);
                    $db->bind("numeroDevis", $numero, null);
                    $db->bind("idRTF", null, null);
                    $db->bind("numeroDevisWBCC", "devis$opName", null);
                    $db->bind("dateGenerationDevis", date("Y-m-d"), null);
                    $db->bind("devisFile", "", null);
                    $db->bind("commentaireDevis", "", null);
                    $db->bind("montantTotal", $montantPC, null);
                    $db->bind("montantHT", ($montantPC / (1 + (10 / 100))), null);
                    $db->bind("taux", "10", null);
                    $db->bind("idUserF", $idAuteur, null);
                    $db->bind("editDate", date("Y-m-d H:i:s"), null);
                    $db->bind("artisan", "FRANCE TRAVAUX", null);
                    $db->bind("idArtisanF", "1", null);
                    $db->bind("dureeTravaux", null, null);
                    $db->bind("repRecepteurImmediat", $repRecepteurImmediat, null);
                    if ($db->execute()) {
                        $devis = findItemByColumn("wbcc_devis", "numeroDevis", $numero);
                        $devisOP = findItemByColumn("wbcc_opportunity_devis", "idOpportunityF", $idOP);
                        if ($devisOP) {
                            $db->query("UPDATE `wbcc_opportunity_devis` SET idDevisF = :idDevisF WHERE idOpportunityF=:idOpportunityF");
                            $db->bind("idDevisF", $devis->idDevis, null);
                            $db->bind("idOpportunityF", $idOP, null);
                            if ($db->execute()) {
                            }
                        } else {
                            $db->query("INSERT INTO `wbcc_opportunity_devis`(`idOpportunityF`, `idDevisF`) VALUES (:idOpportunityF,:idDevisF)");
                            $db->bind("idOpportunityF", $idOP, null);
                            $db->bind("idDevisF", $devis->idDevis, null);
                            if ($db->execute()) {
                            }
                        }
                    }
                }
                if ($siTravaux == "1") {
                } else {
                    //PENALITE A APPLIQUER
                    //FACTURE PENALITE A GENERER DE 30%
                    if ($siCompteWBCC == "1") {
                        //Décaissement 70%
                        $activity = findActivityByIdOP($idOP, '48');
                        if ($activity == false) {
                            $op = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                            $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$op->gestionnaire LIMIT 1");
                            $ges = $db->single();
                            createNewActivity($idOP, $op->name, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  "$op->name - Appeler Sinistré pour décaissement des  70%", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", "48");
                        }
                    } else {
                        //CREER TACHE ENCAISSEMENT DES 30% à demander au sinsitré
                        $activity = findActivityByIdOP($idOP, '47');
                        if ($activity == false) {
                            $op = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                            $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$op->gestionnaire LIMIT 1");
                            $ges = $db->single();
                            createNewActivity($idOP, $op->name, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact,  "$op->name - Appeler Sinistré pour encaissement des  30%", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", "47");
                        }
                    }
                }
            }
        } else {
            if ($motifCloture == "Faux Sinistre") {
                $db->query("UPDATE wbcc_opportunity SET status='Lost', typeTranche=9, auditReglement=1, idAuteurAuditReglement=$idAuteur, dateAuditReglement='" . date("Y-m-d H:i:s") . "' WHERE idOpportunity = $idOP");
                $db->execute();
            } else {
                if ($motifCloture == "Tranche 2") {
                    $db->query("UPDATE wbcc_opportunity SET status='Lost', typeTranche=2,  auditReglement=1, idAuteurAuditReglement=$idAuteur, dateAuditReglement='" . date("Y-m-d H:i:s") . "' WHERE idOpportunity = $idOP");
                    $db->execute();
                }
            }
        }
        createHistorique("Audit Pénalité $opName: $statusDossier ", "$auteur", $idAuteur, $idOP);
        echo json_encode(1);
    }

    if ($action == 'saveAuditFranchise') {
        extract($_POST);

        $today = date("Y-m-d H:i:s");

        $db->query("UPDATE wbcc_opportunity SET franchise=:franchise, franchisePaye=:franchisePaye, montantFranchise=:montantFranchise, commentaireFranchise=:commentaireFranchise,idAuteurFranchise=$idAuteur, auditFranchise=:auditFranchise, repEncaisseFranchise=:repEncaisseFranchise, montantEncaisseFranchise=:montantEncaisseFranchise WHERE idOpportunity = $idOP");
        $db->bind("franchisePaye", isset($franchisePaye)  ? $franchisePaye : null, null);
        $db->bind("montantFranchise", isset($montantFranchise) ? $montantFranchise : 0, null);
        $db->bind("commentaireFranchise",  isset($commentaireFranchise) ? $commentaireFranchise : '', null);
        $db->bind("auditFranchise",  isset($repSiFranchise)  ? "1" : null, null);
        $db->bind("franchise",  isset($repSiFranchise)  ? $repSiFranchise : null, null);
        $db->bind("repEncaisseFranchise",  isset($repEncaisseFranchise)  ? $repEncaisseFranchise : null, null);
        $db->bind("montantEncaisseFranchise", isset($montantEncaisse) ? $montantEncaisse : 0, null);

        $db->execute();

        $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=25 AND idOpportunityF = $idOP LIMIT 1");
        $activityFranchise = $db->single();

        if (isset($repSiFranchise)) {
            if ($repSiFranchise == "0") {
                if ($activityFranchise) {
                    //dlerte
                    $db->query("DELETE FROM wbcc_activity WHERE idActivity = $activityFranchise->idActivity");
                    $db->execute();
                }
            } else {
                if ($franchisePaye == "0") {
                    $db->query("DELETE FROM wbcc_activity WHERE idActivity = $activityFranchise->idActivity");
                    $db->execute();
                } else {
                    if ($activityFranchise) {
                        createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Analyse Police d'Assurance", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", "25");
                    } else {
                        createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Analyse Police d'Assurance", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", "25");
                    }
                    if ($repEncaisseFranchise == "0") {
                        createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Encaissement Franchise", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", "26");
                    } else {
                        if ($montantEncaisse == $montantFranchise) {
                            createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Encaissement Franchise", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", "26");
                        } else {
                            if ($montantEncaisse < $montantFranchise) {
                                createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Irrégularité d'encaissement", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", "27");
                            }
                        }
                    }
                }
            }
        }

        if ($activityFranchise) {
            $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activityFranchise->idActivity");
            $db->execute();
        } else {
            createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Faire Compte Rendu RT", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", "26");
        }

        echo json_encode(1);
    }

    //CRON ANOMALIES OP FOR COMMERCIAL
    if ($action == "setAnomaliesOPForCommercial") {
        $date = date("Y-m-d H:i:s");
        //CORRECT ANOMALIES
        $db->query("UPDATE wbcc_opportunity SET incidentSignale = 2  WHERE incidentSignale=1 AND priseRvRT =1 AND delegationSigne=1");
        $db->execute();
        //SET NEW ANOMALIES
        $db->query("UPDATE wbcc_opportunity o SET incidentSignale = 1, raisonIncident = 'Anomalies PAP', idAuteurIncident=518, dateIncident='$date'  where (o.delegationSigne = 0 OR o.priseRvRT = 0) AND o.incidentSignale = 0 AND o.createDate >= '2024-04-25' AND o.origine = 'Extranet-PAP B2C' AND DATEDIFF(NOW(), o.createDate) > 2  ");
        $db->execute();

        echo json_encode("ok");
    }

    if ($action == "setDOOP") {
        $db->query("SELECT o.*, i.nomDO as nomDOImm  FROM wbcc_opportunity o, wbcc_opportunity_papb2c_contact opc, `wbcc_pap_b2c` p, wbcc_immeuble_pap i WHERE o.idOpportunity = opc.idOpportunityF AND opc.idPAPF = p.idPAP AND i.idImmeublePAP = p.idImmeublePAPF AND (o.nomGestionnaireAppImm = '' OR o.nomGestionnaireAppImm IS NULL)");
        $ops =  $db->resultSet();
        foreach ($ops as $key => $op) {
            //SET DO
            if ($op->nomGestionnaireAppImm == "" || $op->nomGestionnaireAppImm == null) {
                if ($op->nomDOImm != null && $op->nomDOImm != "") {
                    //FIND DO
                    $db->query("SELECT * FROM wbcc_company WHERE name =:nomCom LIMIT 1");
                    $db->bind("nomCom", $op->nomDOImm, null);
                    $com =  $db->single();
                    $db->query("UPDATE wbcc_opportunity SET nomGestionnaireAppImm = :nomGestionnaireAppImm, guidGestionnaireAppImm=:guidGestionnaireAppImm WHERE idOpportunity = $op->idOpportunity");
                    $db->bind("nomGestionnaireAppImm", $op->nomDOImm, null);
                    $db->bind("guidGestionnaireAppImm", ($com ? $com->numeroCompany : ""), null);
                    $db->execute();

                    //LINK COM - OP
                    $db->query("INSERT INTO wbcc_company_opportunity (idCompanyF, idOpportunityF) VALUES ( :idCompanyF, :idOpportunityF)");
                    $db->bind("idCompanyF", $com->idCompany, null);
                    $db->bind("idOpportunityF", $op->idOpportunity, null);
                    $db->execute();
                }
            }
            // SET COMMERCIAL

            //FIND COMMERCIAL
            // $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND numeroContact=:numeroContact LIMIT 1");
            // $db->bind("numeroContact", $op->guidCommercial, null);
            // $commercial = $db->single();
            // if ($commercial) {
            //     // $db->query("UPDATE wbcc_opportunity SET commercial = :commercial, guidCommercial=:guidCommercial WHERE idOpportunity = $op->idOpportunity");
            //     // $db->bind("commercial", $commercial->fullName, null);
            //     // $db->bind("guidCommercial", $commercial->numeroContact, null);
            //     // $db->execute();
            //     $db->query("SELECT * FROM wbcc_contact_opportunity WHERE idContactF=$commercial->idContact AND idOpportunityF= $op->idOpportunity LIMIT 1");
            //     $cono =  $db->single();
            //     if ($cono) {
            //     } else {
            //         $db->query("INSERT INTO wbcc_contact_opportunity(idContactF, idOpportunityF) VALUES ($commercial->idContact,$op->idOpportunity)");
            //         $db->execute();
            //     }
            // }
        }
        echo json_encode("1");
    }

    if ($action == "tansfertOPToGestionnaire") {
        $idGes = $_GET['idGestionnaire'];
        $_POST = json_decode(file_get_contents('php://input'), true);
        $db->query("SELECT * FROM wbcc_utilisateur u, wbcc_contact c WHERE u.idContactF= c.idContact AND u.idUtilisateur=$idGes LIMIT 1");
        $ges =  $db->single();
        $ok = 1;
        extract($_POST);
        foreach ($tabIdOP as $key => $value) {
            $idOP = explode(";", $value)[0];
            $idOldGes = explode(";", $value)[1];
            //UPDATE
            $db->query("UPDATE wbcc_opportunity SET editDate = :editDate, gestionnaire=:gestionnaire WHERE idOpportunity= :idOpportunity");
            $db->bind("editDate", date('Y-m-d H:i:s'), null);
            $db->bind("idOpportunity", $idOP, null);
            $db->bind("gestionnaire", $idGes, null);
            if ($db->execute()) {
                $op = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                //GET OLD GESTIONNAIRE
                $db->query("SELECT * FROM wbcc_utilisateur u, wbcc_contact c WHERE u.idContactF= c.idContact AND u.idUtilisateur=$idOldGes LIMIT 1");
                $oldGes =  $db->single();
                //DELETE GESTIONNAIRE
                $db->query("DELETE FROM wbcc_contact_opportunity WHERE idOpportunityF = $idOP AND idContactF = $oldGes->idContact ");
                $db->execute();
                //DELETE GESTIONNAIRE
                $db->query("DELETE FROM wbcc_contact_opportunity WHERE idOpportunityF = $idOP AND idContactF =  $ges->idContact ");
                $db->execute();
                //LINK GESTIONNAIRE OPPORTUNITY
                $db->query("INSERT INTO wbcc_contact_opportunity (idContactF, idOpportunityF) VALUES ( :idContactF, :idOpportunityF)");
                $db->bind("idContactF", $ges->idContact, null);
                $db->bind("idOpportunityF", $idOP, null);
                if ($db->execute()) {
                    $insert = true;
                    createHistorique("Transfert OP effectué à $ges->fullName par $auteur", $auteur, $idAuteur, $idOP);
                    createNote($idOP, $idAuteur, $auteur, "Transfert OP effectué à $ges->fullName par $auteur", "Transfert OP effectué à $ges->fullName par $auteur", 0);
                    //INFORMEZ GESTIONNAIRE PAR MAIL
                    if ($op->status != 'Lost' && $op->status != 'Won') {
                        $body = "Bonjour $ges->civiliteContact $ges->prenomContact $ges->nomContact, <br><br>
                            $auteur vient de vous attribuer ce dossier :  $op->name.
                            <br><br><br>
                            Veuillez ne pas répondre à ce message envoyé automatiquement pour votre information.<br><br><b>WBCC Assistance</b><br><b>" . $auteur . "</b>";
                        $r = new Role();
                        $to = $ges->emailContact;
                        if ($to != "") {
                            if ($r::mailExtranetWithFiles($to, " $op->name - ATTRIBUTION NOUVEAU DOSSIER", $body, [], [], [])) {
                            }
                        }
                    }
                } else {
                    $insert = false;
                }

                $db->query("SELECT * FROM wbcc_opportunity_activity oa, wbcc_activity a WHERE oa.idActivityF = a.idActivity AND oa.idOpportunityF=$idOP");
                $activities = $db->resultSet();
                foreach ($activities as $key => $activity) {
                    if ($activity->isCleared != 'True'  || ($op->origine == "Extranet-PAP B2C" && $activity->codeActivity != "1" && $activity->codeActivity != "3"  && $activity->codeActivity != "1" && $activity->codeActivity != "6" && $activity->codeActivity != "18" && $activity->codeActivity != "30" && $activity->codeActivity != "45")) {
                        //SET ACTIVITY FOR NEW GESTIONNAIRE
                        $db->query("UPDATE wbcc_activity SET idUtilisateurF = :idUtilisateurF, organizer= :organizer, organizerGuid = :organizerGuid WHERE idActivity = $activity->idActivity ");
                        $db->bind("idUtilisateurF", $idGes, null);
                        $db->bind("organizer", $ges->fullName, null);
                        $db->bind("organizerGuid", $ges->numeroContact, null);
                        $db->execute();
                    }
                }
            } else {
                $ok = 0;
            }

            //GET OTHERS OP FOR SAME CONTACT
            //GET CONTACT
            $o = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
            $contact = findItemByColumn("wbcc_contact", "numeroContact", $o->guidContactClient);
            if ($contact == false) {
                $db->query("SELECT * FROM wbcc_contact  WHERE  fullName=:fullName LIMIT 1");
                $db->bind("fullName", $o->contactClient, null);
                $contact = $db->single();
            }
            if ($o->typeSinistre == "Partie commune exclusive" && $o->source != null && $o->source != "") {
                $db->query("SELECT * FROM wbcc_contact  WHERE  numeroContact='$o->source' LIMIT 1");
                $contact = $db->single();
            }
            if ($contact) {
                $db->query("SELECT * FROM wbcc_contact_opportunity co, wbcc_opportunity o WHERE co.idOpportunityF= o.idOpportunity AND co.idContactF=$contact->idContact AND (o.contactClient = :contactClient OR o.guidContactClient = :guidContact OR o.guidDO = :guidContact OR o.nomDO = :contactClient) AND o.status = 'Open' AND o.name NOT LIKE '%X%'   AND o.idOpportunity != $idOP AND o.type='Sinistres'  GROUP BY o.idOpportunity ORDER BY name DESC");
                $db->bind("contactClient", $contact->fullName, null);
                $db->bind("guidContact", $contact->numeroContact, null);
                $othersOP = $db->resultSet();
                foreach ($othersOP as $key2 => $newOP) {
                    $idOP = $newOP->idOpportunity;
                    //UPDATE
                    $db->query("UPDATE wbcc_opportunity SET editDate = :editDate, gestionnaire=:gestionnaire WHERE idOpportunity= :idOpportunity");
                    $db->bind("editDate", date('Y-m-d H:i:s'), null);
                    $db->bind("idOpportunity", $idOP, null);
                    $db->bind("gestionnaire", $idGes, null);
                    if ($db->execute()) {
                        $op = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                        //GET OLD GESTIONNAIRE
                        $db->query("SELECT * FROM wbcc_utilisateur u, wbcc_contact c WHERE u.idContactF= c.idContact AND u.idUtilisateur=$idOldGes LIMIT 1");
                        $oldGes =  $db->single();
                        //DELETE GESTIONNAIRE
                        $db->query("DELETE FROM wbcc_contact_opportunity WHERE idOpportunityF = $idOP AND idContactF = $oldGes->idContact ");
                        $db->execute();
                        //DELETE GESTIONNAIRE
                        $db->query("DELETE FROM wbcc_contact_opportunity WHERE idOpportunityF = $idOP AND idContactF =  $ges->idContact ");
                        $db->execute();
                        //LINK GESTIONNAIRE OPPORTUNITY
                        $db->query("INSERT INTO wbcc_contact_opportunity (idContactF, idOpportunityF) VALUES ( :idContactF, :idOpportunityF)");
                        $db->bind("idContactF", $ges->idContact, null);
                        $db->bind("idOpportunityF", $idOP, null);
                        if ($db->execute()) {
                            $insert = true;
                            createHistorique("Transfert OP effectué à $ges->fullName par $auteur", $auteur, $idAuteur, $idOP);
                            createNote($idOP, $idAuteur, $auteur, "Transfert OP effectué à $ges->fullName par $auteur", "Transfert OP effectué à $ges->fullName par $auteur", 0);
                            //INFORMEZ GESTIONNAIRE
                            if ($op->status != 'Lost' && $op->status != 'Won') {
                                $body = "Bonjour $ges->civiliteContact $ges->prenomContact $ges->nomContact, <br><br>
                                $auteur vient de vous attribuer ce dossier :  $op->name.
                                <br><br><br>
                                Veuillez ne pas répondre à ce message envoyé automatiquement pour votre information.<br><br><b>WBCC Assistance</b><br><b>" . $auteur . "</b>";
                                $r = new Role();
                                $to = $ges->emailContact;
                                if ($to != "") {
                                    if ($r::mailExtranetWithFiles($to, " $op->name - ATTRIBUTION NOUVEAU DOSSIER", $body, [], [], [])) {
                                    }
                                }
                            }
                        } else {
                            $insert = false;
                        }

                        $db->query("SELECT * FROM wbcc_opportunity_activity oa, wbcc_activity a WHERE oa.idActivityF = a.idActivity AND oa.idOpportunityF=$idOP");
                        $actvities = $db->resultSet();
                        foreach ($activities as $key => $activity) {
                            if ($activity->isCleared != 'True'  || ($op->origine == "Extranet-PAP B2C" && $activity->codeActivity != "1" && $activity->codeActivity != "3" && $activity->codeActivity != "1" && $activity->codeActivity != "6" && $activity->codeActivity != "18" && $activity->codeActivity != "30" && $activity->codeActivity != "45")) {
                                //SET ACTIVITY FOR NEW GESTIONNAIRE
                                $db->query("UPDATE wbcc_activity SET idUtilisateurF = :idUtilisateurF, organizer= :organizer, organizerGuid = :organizerGuid WHERE idActivity = $activity->idActivity ");
                                $db->bind("idUtilisateurF", $idGes, null);
                                $db->bind("organizer", $ges->fullName, null);
                                $db->bind("organizerGuid", $ges->numeroContact, null);
                                $db->execute();
                            }
                        }
                    } else {
                        $ok = 0;
                    }
                }
            }
        }
        echo json_encode($ok);
    }

    if ($action == "setLibellePieceRT") {
        $db->query("SELECT * FROM wbcc_releve_technique ");
        $datas = $db->resultSet();
        foreach ($datas as $key => $rt) {
            $nbP = 0;
            $lib = "";
            $db->query("SELECT * FROM  wbcc_rt_piece p WHERE p.idRTF =:id");
            $db->bind("id", $rt->idRT, null);
            $pieces = $db->resultSet();
            foreach ($pieces as $key => $p) {
                $nbMur = 0;
                $nbMurSin = 0;
                $nbMurNonSin = 0;
                $db->query("SELECT * FROM  wbcc_rt_piece_support p WHERE p.idRTPieceF =$p->idRTPiece AND idSupportF = 1");
                $murs = $db->resultSet();
                foreach ($murs as $key => $m) {
                    $nbMur++;
                    if ($m->estSinistre == '1') {
                        $nbMurSin++;
                    } else {
                        $nbMurNonSin++;
                    }
                }
                $db->query("UPDATE wbcc_rt_piece SET nbMurs=:nbMur, nbMursSinistres=:nbMurSin, nbMursNonSinistres=:nbMurNonSin  WHERE idRTPiece = $p->idRTPiece");
                $db->bind("nbMur", $nbMur, null);
                $db->bind("nbMurSin",  $nbMurSin, null);
                $db->bind("nbMurNonSin",  $nbMurNonSin, null);
                $db->execute();
            }
        }

        echo json_encode("0");
    }

    if ($action == "attribuerExpert") {
        extract($_POST);
        $today = date('Y-m-d H:i:s');
        $reponse = "0";

        if ($frtFait == "1") {
            $db->query("UPDATE wbcc_opportunity SET idAuteurFrt = $idExpert WHERE idOpportunity = $idOp");
            $db->execute();
        }

        if ($idRDV == 0) {
            $db->query("UPDATE wbcc_opportunity SET idAuteurFrt = $idExpert, priseRvRT=1, datePriseRvRT='$today', idAuteurPriseRvRT=518
            WHERE idOpportunity = $idOp");
            $db->execute();

            $db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity = $idOp");
            $op = $db->single();

            //CREATE RDV
            $db->query("INSERT INTO wbcc_rendez_vous(`dateRV`, `heureDebut`, `heureFin`,  `idAppConF`,  `idOpportunityF`, `idAppExtra`, `idAppContactExtra`, `idOpGuid`, `expert`, `idExpertF`, `etatRV`, `numeroOP`, `adresseRV`, `nomDO`, `typeRV`, `createDate`, `editDate`, `auteur`, `idAuteur`) VALUES(:dateRV, :heureDebut, :heureFin,  :idAppConF,  :idOpportunityF, :idAppExtra, :idAppContactExtra, :idOpGuid, :expert, :idExpertF, :etatRV, :numeroOP, :adresseRV, :nomDO, :typeRV, :createDate, :editDate, :auteur, :idAuteur)");
            $db->bind("dateRV", $dateRV, null);
            $db->bind("heureDebut", $heureDebRV, null);
            $db->bind("heureFin", $heureFinRV, null);
            $db->bind("idAppConF", $idAppCon, null);
            $db->bind("idAppContactExtra", $idAppCon, null);
            $db->bind("idOpportunityF", $idOp, null);
            $db->bind("idAppExtra", $idApp, null);
            $db->bind("idOpGuid", $op->numeroOpportunity, null);
            $db->bind("expert", $nomExpert, null);
            $db->bind("idExpertF", $idExpert, null);
            $db->bind("etatRV", 1, null);
            $db->bind("numeroOP", $op->name, null);
            $db->bind("adresseRV", $adresse, null);
            $db->bind("nomDO", $op->nomDO, null);
            $db->bind("typeRV", "RTP", null);
            $db->bind("createDate", $today, null);
            $db->bind("editDate", $today, null);
            $db->bind("auteur", "CompteWBCC", null);
            $db->bind("idAuteur", 518, null);
            $db->execute();
            $reponse = "1";
        } else {
            $db->query("UPDATE wbcc_rendez_vous SET idExpertF=:idExpert AND expert=:expert AND editDate=:editDate WHERE idRV = $idRDV");
            $db->bind("expert", $nomExpert, null);
            $db->bind("idExpert", $idExpert, null);
            $db->bind("editDate", $today, null);
            $db->execute();
            $reponse = "1";
        }



        echo json_encode($reponse);
    }

    if ($action == "updateConclusionActivity") {
        $tab = [];
        $db->query("SELECT * FROM wbcc_rendez_vous");
        $result = $db->resultSet();
        if (sizeof($result) != 0) {
            foreach ($result as $key => $rv) {
                //GET NOTE
                if ($rv->idOpportunityF != null && $rv->idOpportunityF != "") {
                    $db->query("SELECT * FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a WHERE o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND a.regarding LIKE '%- RENDEZ-VOUS RELEVES TECHNIQUES%'  AND idOpportunity=$rv->idOpportunityF  GROUP BY idOpportunity ORDER BY a.idActivity DESC LIMIT 1");
                    $activity = $db->single();
                    if ($activity) {
                        $tab[] = $activity;
                        $detail = "";
                        if ($activity->details != "" && $activity->details != null) {
                            $detail = str_replace("<br>", "\n", $activity->details);
                        }
                        $db->query("UPDATE wbcc_activity SET details=:details  WHERE idActivity = $activity->idActivity");
                        $db->bind("details", $detail . "\nCommentaire : \n" . $rv->conclusion, null);
                        $db->execute();
                    }
                }
            }
        }
        echo json_encode($tab);
    }

    if ($action == "updateDate") {
        $_POST = json_decode(file_get_contents('php://input'), true);
        foreach ($_POST as $key => $op) {
            extract($op);
            $search = findItemByColumn("wbcc_opportunity", "numeroOpportunity", $numeroOpportunity);
            if ($search) {
                //UPDATE
                $db->query("UPDATE wbcc_opportunity SET createDate = :createDate WHERE wbcc_opportunity.numeroOpportunity = :numeroOpportunity");
                $db->bind("createDate", $createDate, null);
                $db->bind("numeroOpportunity", $numeroOpportunity, null);
                $db->execute();
            }
        }
        echo json_encode("ok");
    }

    if ($action == 'saveAuditDevis') {
        extract($_POST);
        $req = "";
        $today = date("Y-m-d H:i:s");
        if ($auditFRT == "1") {
            $req = ", frtFait=1, dateFrt='$today', idAuteurFrt=$idUtilisateur ";
        }
        if ($auditDevis == "1") {
            $req .= ", devisFais=1, dateDevisFais='$today', idAuteurDevisFais=$idUtilisateur ";
        }
        if ($auditFRT == "1" && $auditDevis == "1") {
            $req = ", controleFRT=1, dateControleFRT='$today', idAuteurControleFRT=$idUtilisateur ";
        }

        if ($rvEffectue == "1") {
            $req .= ", priseRvRT=1, datePriseRvRT='$today', idAuteurPriseRvRT=$idUtilisateur ";
        } else {
            $auditDevis = "0";
        }

        $db->query("UPDATE wbcc_opportunity SET auditDevis=:auditDevis, idAuteurAuditDevis=:idAuteurAuditDevis $req  WHERE idOpportunity = $idOP");
        $db->bind("auditDevis", $auditDevis == '' ? null : $auditDevis, null);
        $db->bind("idAuteurAuditDevis", $idUtilisateur == '' ? null : $idUtilisateur, null);
        $db->execute();

        //get ACTIVITY 'Programmer RT'
        $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=3 AND idOpportunityF = $idOP LIMIT 1");
        $activityRV = $db->single();
        //get ACTIVITY 'FAIRE FRT'
        $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=6 AND idOpportunityF = $idOP LIMIT 1");
        $activityFRT = $db->single();
        //get ACTIVITY 'FAIRE DEVIS'
        $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=7 AND idOpportunityF = $idOP LIMIT 1");
        $activityDevis = $db->single();

        //get ACTIVITY 'CONTROLE FRT'
        $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=11 AND idOpportunityF = $idOP LIMIT 1");
        $controleFRT = $db->single();

        //get ACTIVITY 'FAIRE RT'
        $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=16 AND idOpportunityF = $idOP LIMIT 1");
        $activityFaireRT = $db->single();

        if ($rvEffectue == "1") {
            if ($activityFaireRT) {
                $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activityFaireRT->idActivity");
                $db->execute();
            } else {
                createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Faire Compte Rendu RT", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", "16");
            }

            //UPDATE OR CREATE RDV
            if ($idRV == "0") {
                $numeroRV = "RV_" . date('dmYHis') . $idOP . $idUtilisateur;
                $db->query("INSERT INTO wbcc_rendez_vous(numero,`dateRV`, `heureDebut`, `heureFin`,  `idAppConF`,  `idOpportunityF`, `idAppExtra`, `idAppContactExtra`, `idOpGuid`, `expert`, `idExpertF`, `etatRV`, `numeroOP`, `adresseRV`, `nomDO`, `typeRV`, `createDate`, `editDate`, `auteur`, `idAuteur`) VALUES(:numero , :dateRV, :heureDebut, :heureFin,  :idAppConF,  :idOpportunityF, :idAppExtra, :idAppContactExtra, :idOpGuid, :expert, :idExpertF, :etatRV, :numeroOP, :adresseRV, :nomDO, :typeRV, :createDate, :editDate, :auteur, :idAuteur)");
                $db->bind("heureDebut", "09:00", null);
                $db->bind("heureFin", "10:00", null);
                $db->bind("numero", $numeroRV, null);
                $db->bind("createDate", $today, null);
                $db->bind("auteur", "Compte WBCC", null);
                $db->bind("idAuteur", $idUtilisateur, null);
            } else {
                $db->query("UPDATE wbcc_rendez_vous SET  dateRV=:dateRV,  idAppConF=:idAppConF,  idOpportunityF=:idOpportunityF, idAppExtra=:idAppExtra, idAppContactExtra=:idAppContactExtra, idOpGuid=:idOpGuid, expert=:expert, idExpertF=:idExpertF, etatRV=:etatRV, numeroOP=:numeroOP, adresseRV=:adresseRV, nomDO=:nomDO, typeRV=:typeRV, editDate=:editDate WHERE idRV = $idRV");
            }
            $db->bind("dateRV", $dateRV, null);
            $db->bind("idAppConF", ($idAppCon == "" || $idAppCon == "0") ? null : $idAppCon, null);
            $db->bind("idAppContactExtra", ($idAppCon == "" || $idAppCon == "0") ? null : $idAppCon, null);
            $db->bind("idOpportunityF", $idOP, null);
            $db->bind("idAppExtra", $idAppExtra == "" || $idAppExtra == 0 ? null : $idAppExtra, null);
            $db->bind("idOpGuid", $numeroOP, null);
            $db->bind("expert", $nomExpert, null);
            $db->bind("idExpertF", $idExpert, null);
            $db->bind("etatRV", 1, null);
            $db->bind("numeroOP", $opName, null);
            $db->bind("adresseRV", $adresseRV, null);
            $db->bind("nomDO", $nomDO, null);
            $db->bind("typeRV", "RTP", null);
            $db->bind("editDate", $today, null);
            $db->execute();

            //CREATE RT
            if ($idRV == "0") {
            }

            //CLOSE RV RT
            if ($activityRV) {
                $db->query("UPDATE wbcc_activity SET isCleared = 'True' WHERE idActivity = $activityRV->idActivity");
                $db->execute();
            } else {
                createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Programmer le RT", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", "3");
            }
            if ($auditDevis != "") {
                if ($auditFRT == "1") {
                    //UPDATE FRT
                    if ($activityFRT) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'True' WHERE idActivity = $activityFRT->idActivity");
                        $db->execute();
                    } else {
                        createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Faire FRT", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", "6");
                    }
                    if ($auditDevis == "1") {
                        if ($controleFRT) {
                            $db->query("UPDATE wbcc_activity SET isCleared = 'True' WHERE idActivity = $controleFRT->idActivity");
                            $db->execute();
                        } else {
                            createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Contrôle FRT", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", "11");
                        }
                        //UPDATE DEVIS
                        if ($activityDevis) {
                            $db->query("UPDATE wbcc_activity SET isCleared = 'True' WHERE idActivity = $activityDevis->idActivity");
                            $db->execute();
                        } else {
                            createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Faire Devis", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", "7");
                        }
                        //CREATE CONTROLE DEVIS
                        $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=8 AND idOpportunityF = $idOP LIMIT 1");
                        $activityControle = $db->single();
                        if ($activityControle) {
                            $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activityControle->idActivity");
                            $db->execute();
                        } else {
                            createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Contrôler Devis", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", "8");
                        }
                    } else {
                        if ($auditDevis == "0") {
                            //CREATE ACTIVITY CONTROLE FRT
                            if ($controleFRT) {
                                $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $controleFRT->idActivity");
                                $db->execute();
                            } else {
                                createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Contrôle FRT", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", "11");
                            }
                        }
                    }
                } else {
                    //CREATE ACTIVITY FRT
                    if ($activityFRT) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activityFRT->idActivity");
                        $db->execute();
                    } else {
                        createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Faire FRT", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", "6");
                    }
                    if ($auditDevis == "1") {
                        //UPDATE DEVIS
                        if ($activityDevis) {
                            $db->query("UPDATE wbcc_activity SET isCleared = 'True' WHERE idActivity = $activityDevis->idActivity");
                            $db->execute();
                        } else {
                            createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Faire Devis", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", "7");
                        }
                    } else {
                    }
                }
            }
        } else {
            if ($activityRV) {
                // $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activityRV->idActivity");
                // $db->execute();
            } else {
                createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Programmer le RT", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", "3");
            }
            if ($activityFRT) {
                $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activityFRT->idActivity");
                $db->execute();
            } else {
                createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Faire FRT", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", "6");
            }
        }
        echo json_encode(1);
    }

    if ($action == 'saveAuditDeclaration') {
        extract($_POST);
        $req = "";
        $today = date("Y-m-d H:i:s");
        if ($auditDeclaration == "1") {
            $req = ", declarationCie=1, dateDeclarationCie='$today', idAuteurDeclarationCie=$idUtilisateur, delegationSigne=1, dateSignatureDelegation='$today', idAuteurSignatureDelegation=$idUtilisateur,	dateDeclarationAssurance='$dateDeclaration',  $typeOP = '$numSinistre'";
            if ($numSinistre != null && $numSinistre != "") {
                $req = $req . ", relanceCieNumSinistre=1, 	dateRelanceCieNumSinistre='$today', idAuteurRelanceCieNumSinistre=$idUtilisateur ";
            }
        }
        $db->query("UPDATE wbcc_opportunity SET auditDeclaration=:auditDeclaration, idAuteurAuditDeclaration=:idAuteurAuditDeclaration $req  WHERE idOpportunity = $idOP");
        $db->bind("auditDeclaration", $auditDeclaration == '' ? null : $auditDeclaration, null);
        $db->bind("idAuteurAuditDeclaration", $idUtilisateur == '' ? null : $idUtilisateur, null);
        $db->execute();
        if ($auditDeclaration != "") {
            //get ACTIVITY 'DECLARATION SINISTRE'
            $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=4 AND idOpportunityF = $idOP LIMIT 1");
            $activity = $db->single();
            //get ACTIVITY 'SIGNATURE DELEGATION'
            $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=1 AND idOpportunityF = $idOP LIMIT 1");
            $activitySign = $db->single();
            if ($auditDeclaration == "1") {
                $dateStart = date($dateDeclaration, strtotime('+3 days'));
                $dateEnd = date($dateDeclaration, strtotime('+4 days'));
                if ($activity) {
                    $db->query("UPDATE wbcc_activity SET isCleared = 'True' WHERE idActivity = $activity->idActivity");
                    $db->execute();
                } else {
                    createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Faire la déclaration de sinistre à la compagnie d’assurance", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", "4");
                }
                //get ACTIVITY 'RELANCE'
                $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=5 AND idOpportunityF = $idOP LIMIT 1");
                $activityRelance = $db->single();
                if ($numSinistre != null && $numSinistre != "") {
                    if ($activityRelance) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'True' WHERE idActivity = $activityRelance->idActivity");
                        $db->execute();
                    } else {
                        createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Relancer la compagnie pour numèro de sinistre", "", $dateStart, $dateEnd, "Tâche à faire", "True", "0", "5");
                    }
                } else {
                    if ($activityRelance) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activityRelance->idActivity");
                        $db->execute();
                    } else {
                        createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Relancer la compagnie pour numèro de sinistre", "", $dateStart, $dateEnd, "Tâche à faire", "False", "0", "5");
                    }
                }
                //DELEGATION
                if ($activitySign) {
                    $db->query("UPDATE wbcc_activity SET isCleared = 'True' WHERE idActivity = $activitySign->idActivity");
                    $db->execute();
                } else {
                    createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Faire signer la délégation de gestion", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", "1");
                }
            } else {
                if ($auditDeclaration == "0") {
                    $cleared = "False";
                    if ($auditCloture == "0") {
                        $cleared = "True";
                    }
                    if ($activity) {
                        $db->query("UPDATE wbcc_activity SET isCleared = '$cleared' WHERE idActivity = $activity->idActivity");
                        $db->execute();
                    } else {
                        createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Faire la déclaration de sinistre à la compagnie d’assurance", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "$cleared", "0", "4");
                    }
                    if ($activitySign) {
                        if ($cleared == "0") {
                            $db->query("UPDATE wbcc_activity SET isCleared = 'True' WHERE idActivity = $activitySign->idActivity");
                            $db->execute();
                        }
                    } else {
                        createNewActivity($idOP, $opName, "518", "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", "$opName - Faire signer la délégation de gestion", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "$cleared", "0", "1");
                    }
                }
            }
        }
        echo json_encode(1);
    }

    if ($action == 'saveAuditRvRt') {
        extract($_POST);
        $req = "";
        $today = date("Y-m-d H:i:s");
        $op = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
        //gestionnaire
        $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$op->gestionnaire LIMIT 1");
        $ges = $db->single();

        if ($auditRvRt == "1") {
            $req = ", priseRvRT=1, datePriseRvRT='$today', idAuteurPriseRvRT=$ges->idUtilisateur, dateRdvRT='$dateRvRT'";
        }

        $db->query("UPDATE wbcc_opportunity SET auditRvRt=:auditRvRt $req  WHERE idOpportunity = $idOP");
        $db->bind("auditRvRt", $auditRvRt == '' ? null : $auditRvRt, null);
        $db->execute();
        if ($auditRvRt != "") {
            //get ACTIVITY 'PROGRAMMER RT'
            $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=3 AND idOpportunityF = $idOP LIMIT 1");
            $activity = $db->single();
            if ($auditRvRt == "1") {
                $dateStart = date($dateRvRT, strtotime('+1 days'));
                $dateEnd = date($dateRvRT, strtotime('+2 days'));
                if ($activity) {
                    $db->query("UPDATE wbcc_activity SET isCleared = 'True', realisedBy='$ges->fullName', idRealisedBy = $ges->idUtilisateur  WHERE idActivity = $activity->idActivity");
                    $db->execute();
                } else {
                    createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact, "$opName - Programmer le RT", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "0", "3");
                }
                //
                createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact, "$opName - Faire FRT", "", $dateStart,  $dateEnd, "Tâche à faire", "False", "0", "6");
                //GET RV
                $db->query("SELECT * FROM wbcc_rendez_vous WHERE idOpportunityF = $idOP LIMIT 1");
                $rv = $db->single();
                if ($rv) {
                    $db->query("UPDATE wbcc_rendez_vous SET etatRV = 1 WHERE idRV = $rv->idRV");
                    $db->execute();
                } else {
                    $numeroRV = "RV" . date('dmYHis')  . $idUtilisateur;
                    $db->query("INSERT INTO wbcc_rendez_vous(numero,dateRV,heureDebut,heureFin,idAppExtra,idAppGuid, expert, idExpertF, numeroOP,adresseRV,nomDO,idRVGuid, moyenTechnique, conclusion, idAppConF, idCampagneF,idContactGuidF,idOpportunityF, typeRV,createDate, editDate, auteur,idAuteur, etatRV) VALUES (:numero,:dateRV,:heureDebut,:heureFin,:idAppExtra,:idAppGuid, :expert, :idExpertF, :numeroOP,:adresseRV,:nomDO,:idRVGuid, :moyenTechnique, :conclusion, :idAppConF, :idCampagneF,:idContactGuidF,:idOpportunityF, :typeRV,:createDate, :editDate, :auteur,:idAuteur, 1)");

                    $db->bind("numero", $numeroRV, null);
                    $db->bind("dateRV",  $dateRvRT, null);
                    $db->bind("heureDebut", "09:00", null);
                    $db->bind("heureFin", "10:00", null);
                    $db->bind("idAppExtra", ($idAppExtra == "") ? null : $idAppExtra, null);
                    $db->bind("idAppGuid", $idAppGuid, null);
                    $db->bind("expert", "Expert", null);
                    $db->bind("idExpertF", null, null);
                    $db->bind("numeroOP", $numeroOP, null);
                    $db->bind("adresseRV", $adresseRV, null);
                    $db->bind("nomDO", $nomDO, null);
                    $db->bind("idRVGuid", "", null);
                    $db->bind("moyenTechnique", "", null);
                    $db->bind("conclusion", "", null);
                    $db->bind("idAppConF", null, null);
                    $db->bind("idCampagneF", null, null);
                    $db->bind("idContactGuidF", null, null);
                    $db->bind("idOpportunityF", $idOP, null);
                    $db->bind("typeRV", "RTP", null);
                    $db->bind("createDate", $today, null);
                    $db->bind("editDate", $today, null);
                    $db->bind("auteur", $auteur, null);
                    $db->bind("idAuteur", $idUtilisateur, null);

                    if ($db->execute()) {
                        $idRV =  findItemByColumn("wbcc_rendez_vous", "numero", $numeroRV)->idRV;
                        $ok = "1";
                        //LINK RV RT
                        if ($idOP != null) {
                            $rt = findItemByColumn("wbcc_releve_technique", "idOpportunityF", $idOP);
                            if ($rt) {
                                $db->query("UPDATE wbcc_releve_technique SET idRVF=:idRV WHERE idRT =:idRT");
                                $db->bind("idRV", $idRV, null);
                                $db->bind("idRT", $rt->idRT, null);
                                $db->execute();
                            }
                        }
                    } else {
                        $ok = "0";
                    }
                }
            } else {
                if ($auditRvRt == "0") {
                    if ($activity) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'False' WHERE idActivity = $activity->idActivity");
                        $db->execute();
                    } else {
                        createNewActivity($idOP, $opName, $ges->idUtilisateur, $ges->fullName, $ges->numeroContact, "$opName-Programmer le RT", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", "0", "3");
                    }
                }
            }
        }
        echo json_encode(1);
    }

    if ($action == "createActivityForNewOP") {
        $res = 1;
        $db->query("SELECT * FROM `wbcc_opportunity` WHERE idOpportunity >= 4230");
        $result = $db->resultSet();
        foreach ($result as $key => $op) {
            $date = new DateTime();
            $dateFin = ($date->modify("+2 days"))->format('Y-m-d H:i:s');
            //DE
            $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=1 AND idOpportunityF = $op->idOpportunity LIMIT 1");
            $activityDE = $db->single();
            if ($activityDE == false) {
                createNewActivity($op->idOpportunity, $op->name, 518, "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", $op->name . "-Faire signer la délégation de gestion", "", date("Y-m-d H:i:s"), $dateFin, "Tâche à faire", "False", 0, 1);
            }
            //TE
            $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=2 AND idOpportunityF = $op->idOpportunity LIMIT 1");
            $activityTE = $db->single();
            if ($activityTE == false) {
                createNewActivity($op->idOpportunity, $op->name, 518, "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", $op->name . "-Faire la Télé-Expertise", "", date("Y-m-d H:i:s"), $dateFin, "Tâche à faire", "False", 0, 2);
            }
            //RV RT
            $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=3 AND idOpportunityF = $op->idOpportunity LIMIT 1");
            $activityRVRT = $db->single();
            if ($activityRVRT == false) {
                createNewActivity($op->idOpportunity, $op->name, 518, "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", $op->name . "-Programmer le RT", "", date("Y-m-d H:i:s"), $dateFin, "Tâche à faire", "False", 0, 3);
            }

            // $date = new DateTime();
            // $dateFin = ($date->modify("+5 days"))->format('Y-m-d H:i:s');
            // createNewActivity($op->idOpportunity, $op->name, 518, "Compte WBCC", "", $op->name . "-Faire la déclaration de sinistre à la compagnie d’assurance", "", date("Y-m-d H:i:s"), $dateFin, "Tâche à faire", "False", 0, 4);
            // $date = new DateTime();
            // $dateFin = ($date->modify("+30 days"))->format('Y-m-d H:i:s');
            // createNewActivity($op->idOpportunity, $op->name, 518, "Compte WBCC", "", $op->name . "-Mettre en Travaux", "", date("Y-m-d H:i:s"), $dateFin, "Tâche à faire", "False", 0, 14);

            //LINK DELEGATION AND OP
            if ($op->genereDelegation == '1') {
                $numeroDocument = "DOC" . date('dmYHis') . $op->idOpportunity . "518";
                $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");
                $db->bind("publie", 0, null);
                $db->bind("source", 'EXTRA', null);
                $db->bind("numeroDocument", $numeroDocument, null);
                $db->bind("nomDocument", $op->rapportDelegation, null);
                $db->bind("urlDocument", $op->rapportDelegation, null);
                $db->bind("commentaire", "", null);
                $db->bind("createDate",  date('Y-m-d H:i:s'), null);
                $db->bind("guidHistory", "", null);
                $db->bind("typeFichier", "Adobe Acrobat Document", null);
                $db->bind("size", "", null);
                $db->bind("guidUser", "5770501a-425d-4f50-b66a-016c2dbb2557", null);
                $db->bind("idUtilisateurF", "518", null);
                $db->bind("auteur", "Compte WBCC", null);
                if ($db->execute()) {
                    $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                    $db->query("INSERT INTO wbcc_opportunity_document ( idDocumentF, idOpportunityF) VALUES (:idDocumentF, :idOpportunityF)");
                    $db->bind("idDocumentF", $document->idDocument, null);
                    $db->bind("idOpportunityF", $op->idOpportunity, null);
                    if ($db->execute()) {
                    }
                }
            }
        }
        echo json_encode($res);
    }

    if ($action == 'findOpByIDS') {
        $_POST = json_decode(file_get_contents('php://input'), true);
        $tab = [];
        if ($_POST != null) {
            foreach ($_POST as $key => $idOP) {
                $search = findOpportunityByID($idOP);
                $tab[] = $search;
            }
        }

        echo json_encode($tab);
    }

    if ($action == "updateOPCDC") {
        // $db->query("SELECT * FROM `wbcc_opportunity` WHERE nomDO like '%cdc%'");
        // $result = $db->resultSet();
        // if (sizeof($result) != 0) {
        //     foreach ($result as $key => $op) {

        //         $db->query("UPDATE wbcc_opportunity SET nomDO = '$op->contactClient', guidDO='$op->guidContactClient', typeDO='Particuliers', editDate=:date  WHERE idOpportunity = $op->idOpportunity");
        //         $db->bind("date", date("Y-m-d H:i:s"), null);
        //         $db->execute();
        //     }
        // }
        // echo json_encode($result);
    }

    if ($action == "updateEtapeField") {
        $tab = [];
        $db->query("SELECT * FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a WHERE a.isCleared='True'  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND a.codeActivity=3 AND o.type='Sinistres' AND o.declarationCie=1 GROUP BY idOpportunity ORDER BY name DESC");
        $result = $db->resultSet();
        if (sizeof($result) != 0) {
            foreach ($result as $key => $op) {
                //GET NOTE
                $db->query("SELECT * FROM wbcc_opportunity_note o, wbcc_note n WHERE plainText like '%OP2024-01-09-0055- déclaration téléphonique effectué le%' AND  idNote =idNoteF AND idOpportunityF=$op->idOpportunity ORDER BY createDate DESC LIMIT 1");
                $note = $db->single();
                if ($note) {
                    $tab[] = $op;
                    $db->query("UPDATE wbcc_opportunity SET relanceCieNumSinistre = 1, dateRelanceCieNumSinistre=:date, idAuteurRelanceCieNumSinistre=:auteur  WHERE idOpportunity = $op->idOpportunity");
                    $db->bind("auteur", $note->idUtilisateurF, null);
                    $db->bind("date", $note->createDate, null);
                    $db->execute();
                }
            }
        }
        echo json_encode($tab);
    }

    if ($action == "getNbrOP") {
        extract($_POST);
        if ($user == "") {
            foreach ($tabUsers as $key => $value) {
                $nbOP = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "opportunity", "idAuteurOpportunity", "dateOpportunity");
                $nbrDC = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "declarationCie", "idAuteurDeclarationCie", "dateDeclarationCie");
                $nbrDE = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "delegationSigne", "idAuteurSignatureDelegation", "dateSignatureDelegation");
                $nbrTE = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "teleExpertiseFaite", "idAuteurTeleExpertise", "dateTeleExpertise");
                $nbrRV = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "priseRvRT", "idAuteurPriseRvRT", "datePriseRvRT");
                $nbrDCM = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "declarationCieMail", "idAuteurDeclarationCieMail", "dateDeclarationCieMail");
                $nbrFD = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "devisFais", "idAuteurDevisFais", "dateDevisFais");
                $nbrED = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "envoiDevis", "idAuteurEnvoiDevis", "dateEnvoiDevis");
                $nbrPC = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "priseEnCharge", "idAuteurPriseEnCharge", "datePriseEnCharge");
                $nbrSDC = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "relanceCieNumSinistre", "idAuteurRelanceCieNumSinistre", "dateRelanceCieNumSinistre");
                $nbrFRT = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "frtFait", "idAuteurFrt", "dateFrt");
                $nbrFRTRe = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "frtRejet", "idAuteurControleFRT", "dateControleFRT");
                $nbrCFRT1 = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "controleFRT", "idAuteurControleFRT", "dateControleFRT");
                $nbrCFRT2 = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "controleFRT2", "idAuteurControleFRT2", "dateControleFRT2");
                $nbrCFRT3 = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "controleFRT3", "idAuteurControleFRT3", "dateControleFRT3");
                $nbrRRT = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "faireRapportRT", "IdAuteurFaireRapportRT", "dateFaireRapportRT");
                $nbrCRT = getNbTacheEffectGest($value["idUtilisateur"], $periode, $date1, $date2, "controleRT", "idAuteurControleRT", "dateControleRT");

                $one["index"] = $key + 1;
                $one["gestionnaire"] = $value["fullName"];
                $one["nbrDC"] = $nbrDC->nbr;
                $one["nbrDE"] = $nbrDE->nbr;
                $one["nbrTE"] = $nbrTE->nbr;
                $one["nbrRV"] = $nbrRV->nbr;
                $one["nbrDCM"] = $nbrDCM->nbr;
                $one["nbrFD"] = $nbrFD->nbr;
                $one["nbrED"] = $nbrED->nbr;
                $one["nbrPC"] = $nbrPC->nbr;
                $one["nbrSDC"] = $nbrSDC->nbr;
                $one["nbrFRT"] = $nbrFRT->nbr;
                $one["nbrFRTRe"] = $nbrFRTRe->nbr;
                $one["nbrCFRT1"] = $nbrCFRT1->nbr;
                $one["nbrCFRT2"] = $nbrCFRT2->nbr;
                $one["nbrCFRT3"] = $nbrCFRT3->nbr;
                $one["nbrRRT"] = $nbrRRT->nbr;
                $one["nbrCRT"] = $nbrCRT->nbr;

                $dataGen[] = $one;
            }

            $data["data"] = $dataGen;
        } else {
            $nbrDC = getNbTacheEffectGest($user, $periode, $date1, $date2, "declarationCie", "idAuteurDeclarationCie", "dateDeclarationCie");
            $nbrDE = getNbTacheEffectGest($user, $periode, $date1, $date2, "delegationSigne", "idAuteurSignatureDelegation", "dateSignatureDelegation");
            $nbrTE = getNbTacheEffectGest($user, $periode, $date1, $date2, "teleExpertiseFaite", "idAuteurTeleExpertise", "dateTeleExpertise");
            $nbrRV = getNbTacheEffectGest($user, $periode, $date1, $date2, "priseRvRT", "idAuteurPriseRvRT", "datePriseRvRT");
            $nbrDCM = getNbTacheEffectGest($user, $periode, $date1, $date2, "declarationCieMail", "idAuteurDeclarationCieMail", "dateDeclarationCieMail");
            $nbrFD = getNbTacheEffectGest($user, $periode, $date1, $date2, "devisFais", "idAuteurDevisFais", "dateDevisFais");
            $nbrED = getNbTacheEffectGest($user, $periode, $date1, $date2, "envoiDevis", "idAuteurEnvoiDevis", "dateEnvoiDevis");
            $nbrPC = getNbTacheEffectGest($user, $periode, $date1, $date2, "priseEnCharge", "idAuteurPriseEnCharge", "datePriseEnCharge");
            $nbrSDC = getNbTacheEffectGest($user, $periode, $date1, $date2, "relanceCieNumSinistre", "idAuteurRelanceCieNumSinistre", "dateRelanceCieNumSinistre");
            $nbrFRT = getNbTacheEffectGest($user, $periode, $date1, $date2, "frtFait", "idAuteurFrt", "dateFrt");
            $nbrFRTRe = getNbTacheEffectGest($user, $periode, $date1, $date2, "frtRejet", "idAuteurControleFRT", "dateControleFRT");
            $nbrCFRT1 = getNbTacheEffectGest($user, $periode, $date1, $date2, "controleFRT", "idAuteurControleFRT", "dateControleFRT");
            $nbrCFRT2 = getNbTacheEffectGest($user, $periode, $date1, $date2, "controleFRT2", "idAuteurControleFRT2", "dateControleFRT2");
            $nbrCFRT3 = getNbTacheEffectGest($user, $periode, $date1, $date2, "controleFRT3", "idAuteurControleFRT3", "dateControleFRT3");
            $nbrRRT = getNbTacheEffectGest($user, $periode, $date1, $date2, "faireRapportRT", "IdAuteurFaireRapportRT", "dateFaireRapportRT");
            $nbrCRT = getNbTacheEffectGest($user, $periode, $date1, $date2, "controleRT", "idAuteurControleRT", "dateControleRT");

            $allDC = getNbTacheEffectGest("", $periode, $date1, $date2, "declarationCie", "idAuteurDeclarationCie", "dateDeclarationCie");
            $allDE = getNbTacheEffectGest("", $periode, $date1, $date2, "delegationSigne", "idAuteurSignatureDelegation", "dateSignatureDelegation");
            $allTE = getNbTacheEffectGest("", $periode, $date1, $date2, "teleExpertiseFaite", "idAuteurTeleExpertise", "dateTeleExpertise");
            $allRV = getNbTacheEffectGest("", $periode, $date1, $date2, "priseRvRT", "idAuteurPriseRvRT", "datePriseRvRT");
            $allDCM = getNbTacheEffectGest("", $periode, $date1, $date2, "declarationCieMail", "idAuteurDeclarationCieMail", "dateDeclarationCieMail");
            $allFD = getNbTacheEffectGest("", $periode, $date1, $date2, "devisFais", "idAuteurDevisFais", "dateDevisFais");
            $allED = getNbTacheEffectGest("", $periode, $date1, $date2, "envoiDevis", "idAuteurEnvoiDevis", "dateEnvoiDevis");
            $allPC = getNbTacheEffectGest("", $periode, $date1, $date2, "priseEnCharge", "idAuteurPriseEnCharge", "datePriseEnCharge");
            $allSDC = getNbTacheEffectGest("", $periode, $date1, $date2, "relanceCieNumSinistre", "idAuteurRelanceCieNumSinistre", "dateRelanceCieNumSinistre");
            $allFRT = getNbTacheEffectGest("", $periode, $date1, $date2,  "frtFait", "idAuteurFrt", "dateFrt");
            $allFRTRe = getNbTacheEffectGest("", $periode, $date1, $date2,  "frtRejet", "idAuteurControleFRT", "dateControleFRT");
            $allCFRT1 = getNbTacheEffectGest("", $periode, $date1, $date2, "controleFRT", "idAuteurControleFRT", "dateControleFRT");
            $allCFRT2 = getNbTacheEffectGest("", $periode, $date1, $date2, "controleFRT2", "idAuteurControleFRT2", "dateControleFRT2");
            $allCFRT3 = getNbTacheEffectGest("", $periode, $date1, $date2, "controleFRT3", "idAuteurControleFRT3", "dateControleFRT3");
            $allRRT = getNbTacheEffectGest("", $periode, $date1, $date2, "faireRapportRT", "IdAuteurFaireRapportRT", "dateFaireRapportRT");
            $allCRT = getNbTacheEffectGest("", $periode, $date1, $date2, "controleRT", "idAuteurControleRT", "dateControleRT");

            $one["index"] = 1;
            $one["gestionnaire"] = $fullName;
            $one["nbrDC"] = $nbrDC->nbr;
            $one["allDC"] = $allDC->nbr;

            $one["nbrDE"] = $nbrDE->nbr;
            $one["allDE"] = $allDE->nbr;

            $one["nbrTE"] = $nbrTE->nbr;
            $one["allTE"] = $allTE->nbr;

            $one["nbrRV"] = $nbrRV->nbr;
            $one["allRV"] = $allRV->nbr;

            $one["nbrDCM"] = $nbrDCM->nbr;
            $one["allDCM"] = $allDCM->nbr;

            $one["nbrFD"] = $nbrFD->nbr;
            $one["allFD"] = $allFD->nbr;

            $one["nbrED"] = $nbrED->nbr;
            $one["allED"] = $allED->nbr;

            $one["nbrPC"] = $nbrPC->nbr;
            $one["allPC"] = $allPC->nbr;

            $one["nbrSDC"] = $nbrSDC->nbr;
            $one["allSDC"] = $allSDC->nbr;

            $one["nbrFRT"] = $nbrFRT->nbr;
            $one["allFRT"] = $allFRT->nbr;

            $one["nbrFRTRe"] = $nbrFRTRe->nbr;
            $one["allFRTRe"] = $allFRTRe->nbr;

            $one["nbrCFRT1"] = $nbrCFRT1->nbr;
            $one["allCFRT1"] = $allCFRT1->nbr;

            $one["nbrCFRT2"] = $nbrCFRT2->nbr;
            $one["allCFRT2"] = $allCFRT2->nbr;

            $one["nbrCFRT3"] = $nbrCFRT3->nbr;
            $one["allCFRT3"] = $allCFRT3->nbr;

            $one["nbrRRT"] = $nbrRRT->nbr;
            $one["allRRT"] = $allRRT->nbr;

            $one["nbrCRT"] = $nbrCRT->nbr;
            $one["allCRT"] = $allCRT->nbr;

            $data["data"][] = $one;
        }
        echo json_encode($data);
    }

    if ($action == "EngNumeroSinistre") {

        extract($_POST);
        if ($typeSinistre == "Partie commune exclusive") {
            $db->query("UPDATE `wbcc_opportunity` SET sinistreMRI = '$numeroSinistre' WHERE idOpportunity = $idOp");
        } else {
            $db->query("UPDATE `wbcc_opportunity` SET sinistreMRH = '$numeroSinistre' WHERE idOpportunity = $idOp");
        }

        $result = $db->execute();

        echo json_encode($result);
    }

    if ($action == "declarerIncident") {
        extract($_POST);
        $subject = "INCIDENT - $opName";
        $OP_Obj = new Opportunity();
        $op = $OP_Obj->findByIdOp($idOp);
        $pap = $OP_Obj->findPap($idOp);

        $tabAdress = EMAIL_CODIR;
        $role = new Role();
        $data = true;
        $body = '
        Bonjour, <br><br>
        Un incident a été signalé le ' . $dateIncident . ' par ' . $auteurIncident . ' <br> 
        <br/>-------------------------------------------------------------------------------------------------- <br/>
             ' . $incidentText . '
        <br/>-------------------------------------------------------------------------------------------------- <br/>
        <table style="border-collapse: collapse;">
            <tr>
                <td colspan="2" style="color: white; text-align: left; background: #e74a3b; padding: 10px; width: 500px;">1. INFOS CONTACT</td>
            </tr>
            <tr>
                <td style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Civilité: </strong></td>
                <td  style="border: 2px solid #d1d3e2; padding: 5px;" > ' . ($op->contact ? $op->contact->civiliteContact : "") . ' </td>
            </tr>
            <tr>
                <td style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Nom : </strong></td>
                <td  style="border: 2px solid #d1d3e2; padding: 5px;" >  ' . (($op->contact ? $op->contact->prenomContact : "") . " " . ($op->contact ? $op->contact->nomContact : "")) . ' </td>
            </tr>
            <tr>
                <td style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Email: </strong></td>
                <td  style="border: 2px solid #d1d3e2; padding: 5px;" >  ' . ($op->contact ? $op->contact->emailContact : "") . ' </td>
            </tr>
            <tr>
                <td style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Tél: </strong></td>
                <td  style="border: 2px solid #d1d3e2; padding: 5px;" >  ' . ($op->contact ? $op->contact->telContact : "") . '</td>
            </tr>
            <tr>
                <td style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Statut Contact: </strong></td>
                <td  style="border: 2px solid #d1d3e2; padding: 5px;" > ' . ($op->contact ? $op->contact->statutContact : "") . '</td>
            </tr>

            <tr>
                <td colspan="2" style="color: white; text-align: left; background: #e74a3b; padding: 10px; width: 500px; max-width: 500px;">2. INFOS OPPORTUNITE ( ' . $op->name . ' )</td>
            </tr>
            <tr>
                <td style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Date Ouverture: </strong></td>
                <td  style="border: 2px solid #d1d3e2; padding: 5px;" > ' . $op->createDate . ' </td>
            </tr>
            <tr>
                <td style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Type: </strong></td>
                <td  style="border: 2px solid #d1d3e2; padding: 5px;" > ' . ($op->type == "Sinistres" ? "Gestion de Sinistres" : "A.M.O.") . '</td>
            </tr>
            <tr>
                <td style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Partie Concernée: </strong></td>
                <td  style="border: 2px solid #d1d3e2; padding: 5px;" > ' . $op->typeSinistre . '</td>
            </tr>
            <tr>
                <td style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Donneur d\'ordre: </strong></td>
                <td  style="border: 2px solid #d1d3e2; padding: 5px;" >' . $op->nomDO . ' </td>
            </tr>
            <tr>
                <td style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Gest Imm/App: </strong></td>
                <td  style="border: 2px solid #d1d3e2; padding: 5px;" > ' . $op->nomGestionnaireAppImm . ' </td>
            </tr>
            <tr>
                <td style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Commentaire OP: </strong></td>
                <td  style="border: 2px solid #d1d3e2; padding: 5px;" >' . $op->commentaire . '</td>
            </tr>


            <tr>
                <td colspan="2" style="color: white; text-align: left; background: #e74a3b; padding: 10px; width: 500px;">3. INFOS IMMEUBLE - APPARTEMENT</td>
            </tr>
            <tr>
                <td style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Adr. : </strong></td>
                <td  style="border: 2px solid #d1d3e2; padding: 5px;" > ' . $op->adresse . ' </td>
            </tr>
            <tr>
                <td style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>CP: </strong></td>
                <td  style="border: 2px solid #d1d3e2; padding: 5px;" > ' . $op->cp . '</td>
            </tr>
            <tr>
                <td style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Ville: </strong></td>
                <td  style="border: 2px solid #d1d3e2; padding: 5px;" > ' . $op->ville . ' </td>
            </tr>


            <tr>
                <td colspan="2" style="color: white; text-align: left; background: #e74a3b; padding: 10px; width: 500px;">4. INFOS PAP</td>
            </tr> ';

        if ($pap) {
            $body .= '
                    <tr>
                        <td colspan="2" style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Date Visite :</strong>  ' . (($pap) ? date('d/m/Y H:i', strtotime($pap->dateVisite))  : "") . '</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Commercial :</strong> ' . ($pap ? $pap->auteur  : "") . '</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Origine :</strong> ' . ($pap ? $pap->degat  : "") . '</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Voir Rapport PAP: </strong> <a href="' . URLROOT . 'public/documents/pap/rapportTerrain/' . $pap->rapportFile . '" target="_blank"> ' . $pap->rapportFile . '</a></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: 2px solid #d1d3e2; padding: 5px;" ><strong>Commenatire PAP: </strong> <br>  ' . ($pap ? $pap->commentaireVisite  : "") . ' </td>
                    </tr>';
        } else {
            $body .= '
                    <tr>
                        <td colspan="2" style="border: 2px solid #d1d3e2; padding: 5px; color: red;" ><strong>PAS DE VISITE PAP</strong></td>
                    </tr> ';
        }

        $body .= '
        </table>

        </table>
        <br/>-------------------------------------------------------------------------------------------------- <br/>
        <b>WBCC ASSISTANCE Extranet</b>';

        $data = $role::mailExtranetWithFiles("gestion@wbcc.fr", $subject, $body, $tabAdress, [], []);
        if ($data) {
            $db->query("UPDATE wbcc_opportunity SET  incidentSignale= 1, raisonIncident=:raisonIncident, idAuteurIncident=:idAuteurIncident , dateIncident=:date  WHERE idOpportunity = $idOp");
            $db->bind("idAuteurIncident", $idAuteurIncident, null);
            $db->bind("date", date("Y-m-d H:i:s"), null);
            $db->bind("raisonIncident", $incidentText, null);
            $response = $db->execute();

            createHistorique("Déclaration d'incident", $auteurIncident, $idAuteurIncident, $idOp);
        }
        echo json_encode($data);
    }

    if ($action == "OpenOP") {
        $idOP = $_GET['id'];
        $statut = $_GET['statut'];
        $idUser = $_GET['idUser'];

        $db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity = $idOP  LIMIT 1");
        $o = $db->single();
        if ($o) {
            $res = "1";
            $db->query("UPDATE wbcc_opportunity SET status = :statut, etapeOp=:etape, editDate=:editDate, auteurCloture=:idUser, dateCloture=:editDate, demandeCloture=0 WHERE idOpportunity = $idOP");
            $db->bind("statut", $statut, null);
            $db->bind("etape", "Réouverture de l'Opportunité", null);
            $db->bind("editDate", null, null);
            $db->bind("idUser", null, null);
            if ($db->execute()) {
                $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE codeActivity IS NOT NULL AND codeActivity != '' AND idActivity=idActivityF AND idOpportunityF = $idOP");
                $activities = $db->resultSet();
                if (sizeof($activities) != 0) {
                    foreach ($activities as $key => $activity) {
                        $isUpdate = true;
                        if ($activity->codeActivity == "1" && $o->delegationSigne == "1") {
                            $isUpdate = false;
                        }
                        if ($activity->codeActivity == "2" && $o->teleExpertiseFaite == "1") {
                            $isUpdate = false;
                        }
                        if ($activity->codeActivity == "3" && $o->priseRvRT == "1") {
                            $isUpdate = false;
                        }
                        if ($activity->codeActivity == "4" && $o->declarationCie == "1") {
                            $isUpdate = false;
                        }
                        if ($activity->codeActivity == "5" && $o->relanceCieNumSinistre == "1") {
                            $isUpdate = false;
                        }
                        if ($activity->codeActivity == "6" && $o->frtFait == "1") {
                            $isUpdate = false;
                        }
                        if ($activity->codeActivity == "11" && $o->controleFRT == "1") {
                            $isUpdate = false;
                        }
                        if ($isUpdate) {
                            $db->query("UPDATE wbcc_activity SET isCleared = 'False', editDate=:editDate WHERE idActivity = $activity->idActivity");
                            $db->bind("editDate", date("Y-m-d H:i:s"), null);
                            if ($db->execute()) {
                            } else {
                                $res = "0";
                            }
                        }
                    }
                }
                //FIND USER
                $db->query("SELECT * FROM wbcc_utilisateur, wbcc_contact WHERE idContact = idContactF AND idUtilisateur=$idUser LIMIT 1");
                $user = $db->single();
                //INSERT ACTIVITY
                createNewActivity($idOP, $o->name, $idUser, $user->fullName,  $user->numeroContact, "Réouverture de l'opportunité $o->name", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", "1", null);
                //CREATE NOTE
                if (isset($commentaire) && $commentaire != "") {
                    createNote($idOP, $idUser, $user->fullName, $commentaire, $commentaire, 0);
                }
                //INSERT HISTORIQUE
                createHistorique("Rouvrir Opportnuité : $o->name",  $user->fullName, $idUser, $idOP);
            } else {
                $res = "0";
            }
            echo json_encode($res);
        } else {
            echo json_encode("0");
        }
    }

    if ($action == "closeOP") {
        $idOP = $_GET['id'];
        $statut = $_GET['statut'];
        $idUser = $_GET['idUser'];

        extract($_POST);
        $db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity = $idOP  LIMIT 1");
        $o = $db->single();
        //FIND USER
        $db->query("SELECT * FROM wbcc_utilisateur, wbcc_contact WHERE idContact = idContactF AND idUtilisateur=$idUser LIMIT 1");
        $user = $db->single();
        if ($o) {
            $res = "1";
            $etape = "Clôture de l'Opportunité";
            $demande = "0";
            $dateDemande = null;
            $publie = "1";
            if ($user->role == "29" || $user->role == "33") {
                $etape = "Demande de Clôture de l'Opportunité";
                $demande = "1";
                $statut = 'Open';
                $dateDemande = date("Y-m-d H:i:s");
                $publie = "0";
            }
            $typeTranche =  $statut == "Tranche 2" ? 2 : 0;
            $db->query("UPDATE wbcc_opportunity SET status = :statut, etapeOp=:etape, editDate=:editDate, auteurCloture=:idUser, dateCloture=:editDate, demandeCloture=:demande,dateDemandeCloture=:dateDemande, typeTranche=:typeTranche WHERE idOpportunity = $idOP");
            $db->bind("statut", $statut == "Tranche 2" ? "Lost" : $statut, null);
            $db->bind("etape", $etape, null);
            $db->bind("editDate", date("Y-m-d H:i:s"), null);
            $db->bind("idUser", $idUser, null);
            $db->bind("demande", $demande, null);
            $db->bind("dateDemande", $dateDemande, null);
            $db->bind("typeTranche", $typeTranche, null);

            if ($db->execute()) {
                //CLOSE ACTIVITY
                if ($statut != 'Open') {
                    $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND idOpportunityF = $idOP");
                    $activities = $db->resultSet();
                    if (sizeof($activities) != 0) {
                        foreach ($activities as $key => $activity) {
                            $db->query("UPDATE wbcc_activity SET isCleared = 'True', editDate=:editDate WHERE idActivity = $activity->idActivity");
                            $db->bind("editDate", date("Y-m-d H:i:s"), null);
                            if ($db->execute()) {
                            } else {
                                $res = "0";
                            }
                        }
                    }
                    $today = date("Y-m-d");
                    //LIBERER AGENDA POUR RV FUTUR
                    $db->query("DELETE FROM wbcc_evenement_agenda WHERE dateEvenement > '$today' AND idOpportunityF = $idOP");
                    $db->execute();
                }

                //INSERT ACTIVITY
                createNewActivity($idOP, $o->name, $idUser, $user->fullName,  $user->numeroContact, ($user->role == "29" || $user->role == "33" ? "Demande de " : "") . "Clôture de l'opportunité $o->name", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "True", $publie, null);
                //INSERT HISTORIQUE
                createHistorique(($user->role == "29" || $user->role == "33" ? "Demande de " : "") . "Clôturer Opportunité : $o->name",  $user->fullName, $idUser, $idOP);
                //CREATE NOTE
                if (isset($commentaire) && $commentaire != "") {

                    createNote($idOP, $idUser, $user->fullName, ($statut == 'CLOTURE OP / Tranche 2' ? " $statut - " : "CLOTURE OP / ") . $commentaire, ($statut == 'CLOTURE OP /Tranche 2' ? " $statut - " : "CLOTURE OP / ") . $commentaire, 0);
                }
                //NOTIFICATION
                $r = new Role();
                $tabFiles = [];
                $fileNames = [];
                $cc = EMAIL_CODIR;
                $body = "<p style='text-align:justify'>Bonjour, 
                    <br /><br />Opportunité N° $o->name : " . ($statut == 'Won' ? "Clôturée Gagnée" : "Clôturée Perdue" . ($statut == 'Tranche 2' ? " :$statut" : "")) . " .
                    <br /><br /><b>Raison</b> : <br/>$commentaire.
                    <br /><br />Veuillez ne pas répondre à ce message envoyé automatiquement pour votre information. <br /> <br />
                    <b>WBCC ASSISTANCE </b><br /> 
                    <b> $user->fullName </b>";
                $subject = "";
                if ($user->role == "29" || $user->role == "33") {
                    $subject = " $o->name - DEMANDE DE CLOTURE";
                } else {
                    $subject = " $o->name - CLOTURE OP";
                }
                if ($r::mailExtranetWithFiles("gestion@wbcc.fr", $subject, $body, $cc, $tabFiles, $fileNames)) {
                }
            } else {
                $res = "0";
            }
            echo json_encode($res);
        } else {
            echo json_encode("0");
        }
    }


    if ($action == "findInfosAssuranceOPByType") {
        $cie = null;
        $idOP = $_GET['idOP'];
        $type = $_GET['type'];
        $idApp = $_GET['idApp'];
        $idImmeuble = $_GET['idImmeuble'];
        $db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity = $idOP  LIMIT 1");
        $o = $db->single();
        if ($o) {
            $guidComp = "";
            if ($type == "MRI") {
                $guidComp =  $o->guidComMRI;
            } else {
                if ($type == "MRH") {
                    $guidComp =  $o->guidComMRH;
                } else {
                    $guidComp =  $o->guidComPNO;
                }
            }
            if ($guidComp != null && $guidComp != "") {
                $db->query("SELECT * FROM wbcc_company  WHERE numeroCompany=:numero LIMIT 1");
                $db->bind("numero", $guidComp, null);
                $com = $db->single();
                if ($com) {
                    $cie = $com;
                    $cie->dateDebutContrat = "";
                    $cie->dateFinContrat = "";
                    $cie->numPolice = "";
                    if ($type == "MRI") {
                        $cie->numPolice = $o->policeMRI;
                        if ($idImmeuble != null && $idImmeuble != "" && $idImmeuble != "0") {
                            $db->query("SELECT * FROM wbcc_immeuble  WHERE idImmeuble=$idImmeuble LIMIT 1");
                            $imm = $db->single();
                            if ($imm) {
                                $cie->dateDebutContrat = $imm->dateEffetContrat;
                                $cie->dateFinContrat = $imm->dateEcheanceContrat;
                                $cie->numPolice = $cie->numPolice != null && $cie->numPolice != "" ?  $cie->numPolice : $imm->numPolice;
                            }
                        }
                    } else {
                        if ($idApp != null && $idApp != "" && $idApp != "0") {
                            $db->query("SELECT * FROM wbcc_appartement  WHERE idApp=$idApp LIMIT 1");
                            $app = $db->single();
                            if ($app) {
                                if ($type == "MRH") {
                                    $cie->numPolice = $o->policeMRH;
                                    $cie->dateFinContrat = $cie->numPolice != null && $cie->numPolice != "" ?  $cie->numPolice :  $app->dateEcheanceOccupant;
                                    $cie->numPolice = $app->numPoliceOccupant;
                                    $cie->dateDebutContrat = $app->dateEffetOccupant;
                                } else {
                                    $cie->numPolice = $o->policePNO;
                                    $cie->numPolice = $cie->numPolice != null && $cie->numPolice != "" ?  $cie->numPolice :  $app->numPoliceProprietaire;
                                    $cie->dateDebutContrat = $app->dateEffetProprietaire;
                                    $cie->dateFinContrat = $app->dateEcheanceProprietaire;
                                }
                            }
                        }
                    }
                    if ($cie->dateDebutContrat != null &&  $cie->dateDebutContrat != "") {
                        $dateNew = date_parse_from_format("d/m/Y H:i",  $cie->dateDebutContrat);
                        if ($dateNew) {
                            $cie->dateDebutContrat = $dateNew['year'] . "-" . str_pad(($dateNew['month']), 2, '0', STR_PAD_LEFT) . "-" .  str_pad(($dateNew['day']), 2, '0', STR_PAD_LEFT);
                        }
                    }
                    if ($cie->dateFinContrat != null &&  $cie->dateFinContrat != "") {
                        $dateNew = date_parse_from_format("d/m/Y H:i",  $cie->dateFinContrat);
                        if ($dateNew) {
                            $cie->dateFinContrat = $dateNew['year'] . "-" . str_pad(($dateNew['month']), 2, '0', STR_PAD_LEFT) . "-" .  str_pad(($dateNew['day']), 2, '0', STR_PAD_LEFT);
                        }
                    }
                }
            }
        }
        echo json_encode($cie);
    }

    if ($action == "updateInfosAssuranceOP2") {
        $source = isset($_GET['source']);
        if (isset($_GET['source']) && $_GET['source'] == "mobile") {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        extract($_POST);
        $editDate = date("Y-m-d H:i:s");
        $ok = 0;
        //UPDATE DATE NAISSANCE
        if ($idContact != "" && $idContact != "0") {
            $db->query("UPDATE wbcc_contact SET dateNaissance=:dateNaissance, editDate='$editDate', emailContact=:email, telContact=:tel, civiliteContact=:civilite, nomContact=:nom, prenomContact=:prenom  WHERE idContact=$idContact");
            $db->bind("dateNaissance", $dateNaissance, null);
            $db->bind("email", $email, null);
            $db->bind("tel", $tel, null);
            $db->bind("civilite", $civilite, null);
            $db->bind("nom", $nom, null);
            $db->bind("prenom", $prenom, null);
            $db->execute();
        }
        $okCie = false;
        if ($idCie != null && $idCie != "" && $idCie != "0") {
            if ($source) {
                //SAVE OP-ASSURANCE
                $db->query("SELECT * FROM wbcc_company_opportunity WHERE idCompanyF=$idCie AND idOpportunityF=$idOP LIMIT 1");
                $companyOpp = $db->single();
                if ($companyOpp == false) {
                    $db->query("INSERT INTO wbcc_company_opportunity(idCompanyF, idOpportunityF) VALUES ($idCie,$idOP)");
                    $db->execute();
                    $okCie = true;
                } else {
                    $okCie = true;
                }
            } else {
                $db->query("UPDATE wbcc_company SET name=:name, businessLine1=:businessLine1, businessPostalCode=:businessPostalCode,businessCity=:businessCity, businessPhone=:businessPhone, email=:email, editDate='$editDate'  WHERE idCompany=:idCompany");
                $db->bind("name", $nomCie, null);
                $db->bind("businessLine1", $adresse, null);
                $db->bind("businessPostalCode", $codePostal, null);
                $db->bind("businessCity", $ville, null);
                $db->bind("businessPhone", $tel, null);
                $db->bind("email", $email, null);
                $db->bind("idCompany", $idCie, null);
                $db->execute();
                $okCie = true;
            }

            if ($okCie) {
                if ($type == "MRI") {
                    $db->query("UPDATE wbcc_opportunity SET denominationComMRI=:nomCie, policeMRI=:numPolice, sinistreMRI=:numSinistre,dateSinistre=:dateSinistre, guidComMRI=:guidCom, idComMRIF=:idCie,  auteurDerniereModification=:auteur, editDate='$editDate' WHERE idOpportunity=:idOP");
                } else {
                    if ($type == "MRH") {
                        $db->query("UPDATE wbcc_opportunity SET denominationComMRH=:nomCie, policeMRH=:numPolice, sinistreMRH=:numSinistre,dateSinistre=:dateSinistre, guidComMRH=:guidCom,idComMRHF=:idCie, auteurDerniereModification=:auteur, editDate='$editDate' WHERE idOpportunity=:idOP");
                    } else {
                        $db->query("UPDATE wbcc_opportunity SET denominationComPNO=:nomCie, policePNO=:numPolice, sinistrePNO=:numSinistre,dateSinistre=:dateSinistre, guidComPNO=:guidCom, idComMRHF=:idCie, auteurDerniereModification=:auteur, editDate='$editDate' WHERE idOpportunity=:idOP");
                    }
                }
                $db->bind("numPolice", $numPolice, null);
                $db->bind("numSinistre", $numSinistre, null);
                $db->bind("dateSinistre", $dateSinistre, null);
                $db->bind("nomCie", $nomCie, null);
                $db->bind("guidCom", $guidCie, null);
                $db->bind("idCie", $idCie, null);
                $db->bind("idOP", $idOP, null);
                $db->bind("auteur", $auteur, null);
                if ($db->execute()) {
                    //UPDATE RT
                    $db->query("UPDATE wbcc_releve_technique SET date='$dateSinistre' WHERE idOpportunityF =  $idOP");
                    $db->execute();
                    if ($type == "MRI") {
                        if ($idImmeuble !=  null && $idImmeuble != "" && $idImmeuble != "0") {
                            $db->query("UPDATE wbcc_immeuble SET nomCompagnieAssurance=:nomCie, numPolice=:numPolice, dateEffetContrat=:dateDebutContrat, dateEcheanceContrat = :dateFinContrat, editDate='$editDate' WHERE idImmeuble=:idImmeuble");
                            $db->bind("idImmeuble", $idImmeuble, null);
                            $db->bind("numPolice", $numPolice, null);
                            $db->bind("nomCie", $nomCie, null);
                            $db->bind("dateDebutContrat", $dateDebutContrat, null);
                            $db->bind("dateFinContrat", $dateFinContrat, null);
                            if ($db->execute()) {
                                $ok = 1;
                            } else {
                                $ok = 0;
                            }
                        }
                    } else {
                        if ($idApp !=  null && $idApp != "" && $idApp != "0") {
                            if ($type == "MRH") {
                                $db->query("UPDATE wbcc_appartement SET compagnieAssuranceOccupant=:nomCie, numPoliceOccupant=:numPolice, dateEffetOccupant=:dateDebutContrat, dateEcheanceOccupant = :dateFinContrat, editDate='$editDate' WHERE idApp=:idApp");
                            } else {
                                $db->query("UPDATE wbcc_appartement SET compagnieAssuranceProprietaire=:nomCie, numPoliceProprietaire=:numPolice, dateEffetProprietaire=:dateDebutContrat, dateEcheanceProprietaire = :dateFinContrat, editDate='$editDate' WHERE idApp=:idApp");
                            }
                            $db->bind("numPolice", $numPolice, null);
                            $db->bind("nomCie", $nomCie, null);
                            $db->bind("dateDebutContrat", $dateDebutContrat, null);
                            $db->bind("dateFinContrat", $dateFinContrat, null);
                            $db->bind("idApp", $idApp, null);
                            if ($db->execute()) {
                                $ok = 1;
                            } else {
                                $ok = 0;
                            }
                        }
                    }
                } else {
                    $ok = 0;
                }
            } else {
                $ok = 0;
            }
        }
        echo json_encode($ok);
    }

    if ($action == "updateInfosAssuranceOP") {
        extract($_POST);
        $editDate = date("Y-m-d H:i:s");
        $ok = 1;
        //UPDATE DATE NAISSANCE
        if ($idContact != "" && $idContact != "0") {
            $db->query("UPDATE wbcc_contact SET dateNaissance=:dateNaissance  WHERE idContact=$idContact");
            $db->bind("dateNaissance", $dateNaissance, null);
            $db->execute();
        }
        if ($idCie != null && $idCie != "" && $idCie != "0") {
            $db->query("UPDATE wbcc_company SET name=:name, businessLine1=:businessLine1, businessPostalCode=:businessPostalCode,businessCity=:businessCity, businessPhone=:businessPhone, email=:email, editDate='$editDate'  WHERE idCompany=:idCompany");
            $db->bind("name", $nomCie, null);
            $db->bind("businessLine1", $adresse, null);
            $db->bind("businessPostalCode", $codePostal, null);
            $db->bind("businessCity", $ville, null);
            $db->bind("businessPhone", $tel, null);
            $db->bind("email", $email, null);
            $db->bind("idCompany", $idCie, null);
            if ($db->execute()) {
                if ($type == "MRI") {
                    $db->query("UPDATE wbcc_opportunity SET denominationComMRI=:nomCie, policeMRI=:numPolice, sinistreMRI=:numSinistre,dateSinistre=:dateSinistre, guidComMRI=:guidCom, auteurDerniereModification=:auteur, editDate='$editDate' WHERE idOpportunity=:idOP");
                } else {
                    if ($type == "MRH") {
                        $db->query("UPDATE wbcc_opportunity SET denominationComMRH=:nomCie, policeMRH=:numPolice, sinistreMRH=:numSinistre,dateSinistre=:dateSinistre, guidComMRH=:guidCom, auteurDerniereModification=:auteur, editDate='$editDate' WHERE idOpportunity=:idOP");
                    } else {
                        $db->query("UPDATE wbcc_opportunity SET denominationComPNO=:nomCie, policePNO=:numPolice, sinistrePNO=:numSinistre,dateSinistre=:dateSinistre, guidComPNO=:guidCom, auteurDerniereModification=:auteur, editDate='$editDate' WHERE idOpportunity=:idOP");
                    }
                }
                $db->bind("numPolice", $numPolice, null);
                $db->bind("numSinistre", $numSinistre, null);
                $db->bind("dateSinistre", $dateSinistre, null);
                $db->bind("nomCie", $nomCie, null);
                $db->bind("guidCom", $guidCie, null);
                $db->bind("idOP", $idOP, null);
                $db->bind("auteur", $auteur, null);
                if ($db->execute()) {
                    //UPDATE RT
                    $db->query("UPDATE wbcc_releve_technique SET date='$dateSinistre' WHERE idOpportunityF =  $idOP");
                    $db->execute();
                    if ($type == "MRI") {
                        if ($idImmeuble !=  null && $idImmeuble != "" && $idImmeuble != "0") {
                            $db->query("UPDATE wbcc_immeuble SET nomCompagnieAssurance=:nomCie, numPolice=:numPolice, dateEffetContrat=:dateDebutContrat, dateEcheanceContrat = :dateFinContrat, editDate='$editDate' WHERE idImmeuble=:idImmeuble");
                            $db->bind("idImmeuble", $idImmeuble, null);
                            $db->bind("numPolice", $numPolice, null);
                            $db->bind("nomCie", $nomCie, null);
                            $db->bind("dateDebutContrat", $dateDebutContrat, null);
                            $db->bind("dateFinContrat", $dateFinContrat, null);
                        }
                    } else {
                        if ($idApp !=  null && $idApp != "" && $idApp != "0") {
                            if ($type == "MRH") {
                                $db->query("UPDATE wbcc_appartement SET compagnieAssuranceOccupant=:nomCie, numPoliceOccupant=:numPolice, dateEffetOccupant=:dateDebutContrat, dateEcheanceOccupant = :dateFinContrat, editDate='$editDate' WHERE idApp=:idApp");
                            } else {
                                $db->query("UPDATE wbcc_appartement SET compagnieAssuranceProprietaire=:nomCie, numPoliceProprietaire=:numPolice, dateEffetProprietaire=:dateDebutContrat, dateEcheanceProprietaire = :dateFinContrat, editDate='$editDate' WHERE idApp=:idApp");
                            }
                            $db->bind("numPolice", $numPolice, null);
                            $db->bind("nomCie", $nomCie, null);
                            $db->bind("dateDebutContrat", $dateDebutContrat, null);
                            $db->bind("dateFinContrat", $dateFinContrat, null);
                            $db->bind("idApp", $idApp, null);
                        }
                    }

                    if ($db->execute()) {
                        $ok = 1;
                    } else {
                        $ok = 0;
                    }
                } else {
                    $ok = 0;
                }
            } else {
                $ok = 0;
            }
        }
        echo json_encode($ok);
    }

    if ($action == "liste") {
        $db->query("SELECT * FROM wbcc_opportunity WHERE etatOP=1");
        $data = $db->resultSet();
        echo json_encode($data);
    }

    if ($action == "getOpportunitiesEnCours") {
        $tab = [];
        $enCours = [];

        $db->query("SELECT * FROM wbcc_opportunity WHERE name NOT LIKE '%X%' AND status = 'open' AND etatOP=1  ORDER BY name DESC");
        $result = $db->resultSet();

        if (!empty($result)) {
            foreach ($result as $row) {
                $db->query("SELECT * FROM wbcc_releve_technique WHERE idOpportunityF =  $row->idOpportunity LIMIT 1");
                $rt = $db->single();

                //GESTIONNAIRE
                $gestionnaire = false;
                if ($row->numGestionnaire != "" && $row->numGestionnaire != null) {
                    $db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$row->numGestionnaire' LIMIT 1");
                    $gestionnaire =  $db->single();
                }
                $row->gestionnaire =  ($gestionnaire) ? $gestionnaire->fullName : "";
                //Referent DO
                $referentDO = false;
                if ($row->source != null && $row->source != "") {
                    $db->query("SELECT * FROM wbcc_contact  WHERE numeroContact =:source LIMIT 1");
                    $db->bind("source", $row->source, null);

                    $referentDO =  $db->single();
                }
                $row->referentDO =  ($referentDO) ? $referentDO->fullName : "";
                $row->emailNotifie = ($referentDO) ? $referentDO->emailContact : "";

                //Contact Client
                $contactClient = false;
                if ($row->contactClient != null && $row->contactClient != "") {
                    $db->query("SELECT * FROM wbcc_contact  WHERE fullName =:full LIMIT 1");
                    $db->bind("full", $row->contactClient, null);
                    $contactClient =  $db->single();
                }
                $row->emailNotifie = ($contactClient) ? $contactClient->emailContact : "";
                $row->civiliteNotifie = ($contactClient) ? $contactClient->civiliteContact : "";
                $row->fullNameNotifie = ($contactClient) ? $contactClient->fullName : "";

                //adresse
                $db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$row->immeuble' LIMIT 1");
                $immeuble = $db->single();
                $db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF AND codeWBCC = '$row->appartement' LIMIT 1");
                $app = $db->single();

                $row->adresseImmeuble = ($immeuble) ? $immeuble->adresse : (($app) ? $app->adresse : "");
                $row->cpImmeuble = ($immeuble) ? $immeuble->codePostal : (($app) ? $app->codePostal : "");
                $row->villeImmeuble = ($immeuble) ? $immeuble->ville : (($app) ? $app->ville : "");
                $dommages = [];
                $murs = [];
                $biens = [];
                $db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement WHERE idApp = idAppartementF AND  idOpportunityF =  $row->idOpportunity LIMIT 1");
                $lots = $result = $db->resultSet();

                $mrh = false;
                $db->query("SELECT * FROM wbcc_company, wbcc_company_opportunity  WHERE  idCompany = idCompanyF AND name =:name LIMIT 1");
                $db->bind("name", $row->denominationComMRH, null);
                $mrh = $db->single();


                $mri = false;
                $db->query("SELECT * FROM wbcc_company, wbcc_company_opportunity  WHERE  idCompany = idCompanyF AND name =:name  LIMIT 1");
                $db->bind("name", $row->denominationComMRI, null);
                $mri = $db->single();


                $pno = false;
                $db->query("SELECT * FROM wbcc_company, wbcc_company_opportunity  WHERE  idCompany = idCompanyF AND name =:name LIMIT 1");
                $db->bind("name", $row->denominationComPNO, null);
                $pno = $db->single();



                $enCours[] = ["op" => $row, "mrh" => $mrh, "mri" => $mri, "pno" => $pno, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots];
            }
        }

        echo json_encode($enCours);
    }

    if ($action == "getOpportunitiesCom") {
        $clotures = [];
        $tab = [];
        $enCours = [];
        $aValider = [];
        $tous = [];
        $db->query("SELECT * FROM wbcc_opportunity, wbcc_contact_opportunity as co, wbcc_contact, wbcc_utilisateur as u  WHERE idOpportunity = idOpportunityF AND co.idContactF = idContact AND u.idContactF=idContact AND u.libelleRole='Commercial' AND etatOP=1 ORDER BY name DESC");
        $result = $db->resultSet();
        if (!empty($result)) {
            foreach ($result as $row) {
                $db->query("SELECT * FROM wbcc_releve_technique WHERE idOpportunityF =  $row->idOpportunity LIMIT 1");
                $rt = $db->single();
                //GESTIONNAIRE
                $gestionnaire = false;
                if ($row->numGestionnaire != "" && $row->numGestionnaire != null) {
                    $db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$row->numGestionnaire' LIMIT 1");
                    $gestionnaire =  $db->single();
                }
                $row->gestionnaire =  ($gestionnaire) ? $gestionnaire->fullName : "";
                //adresse
                $db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$row->immeuble' LIMIT 1");
                $immeuble = $db->single();
                $db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF AND codeWBCC = '$row->appartement' LIMIT 1");
                $app = $db->single();
                $row->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");

                $dommages = [];
                $murs = [];
                $biens = [];
                $db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement WHERE idApp = idAppartementF AND  idOpportunityF =  $row->idOpportunity LIMIT 1");
                $lots = $result = $db->resultSet();
                if (($row->status == 'Open' || $row->status == 'Inactive') && $row->demandeCloture == 0 && $row->demandeValidation == 1) {
                    $enCours[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots, "gestionnaire" => $gestionnaire];
                }

                if (($row->status == 'Lost' || $row->status == 'Won') && $row->demandeCloture == 0 && $row->demandeValidation == 1) {
                    $clotures[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots, "gestionnaire" => $gestionnaire];
                }

                if ($row->demandeValidation == 0) {
                    $aValider[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots, "gestionnaire" => $gestionnaire];
                }
                $tous[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots, "gestionnaire" => $gestionnaire];
            }
        }
        $tab = ["enCours" => $enCours, "aValider" => $aValider, "tous" => $tous, "clotures" => $clotures];
        echo json_encode($tab);
    }

    if ($action == "delete") {
        $_POST = json_decode(file_get_contents('php://input'), true);
        $delete = false;
        foreach ($_POST as $key => $op) {
            extract($op);
            $db->query("DELETE FROM wbcc_opportunity WHERE idOpportunity=:idOP");
            $db->bind("idOP", $idOpportunity, null);
            if ($db->execute()) {
                $delete = true;
            } else {
                $delete = false;
            }
        }
        if ($delete) {
            echo json_encode("Opportunity DELETE");
        } else {
            echo json_encode("Error Opportunity Delete");
        }
    }

    if ($action == 'updateNameOP') {
        $_POST = json_decode(file_get_contents('php://input'), true);
        $insert = false;
        foreach ($_POST as $key => $op) {
            extract($op);

            $search = findItemByColumn("wbcc_opportunity", "numeroOpportunity", $numeroOpportunity);
            if ($search) {
                //UPDATE
                $db->query("UPDATE wbcc_opportunity SET numeroOpportunity = :numeroOpportunity, name = :name WHERE wbcc_opportunity.numeroOpportunity = :numeroOpportunity");
            } else {
                $search = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOpportunity);
                if ($search) {
                    //UPDATE
                    // $db->query("UPDATE wbcc_opportunity SET numeroOpportunity = :numeroOpportunity, name = :name, immeuble = :immeuble, appartement = :appartement, type = :type, commentaire = :commentaire, etape = :etape, numeroEtape = :numeroEtape, gestionnaire = :gestionnaire, typeSinistre = :typeSinistre, nbAppartement = :nbAppartement, typeDO = :typeDO, numGestionnaire = :numGestionnaire, nomDO = :nomDO, contactClient = :contactClient, numeroLot = :numeroLot, denominationComMRI = :denominationComMRI, denominationComMRH = :denominationComMRH, denominationComPNO = :denominationComPNO, denominationCouMRH = :denominationCouMRH, denominationCouMRI = :denominationCouMRI, denominationCouPNO = :denominationCouPNO, policeMRI = :policeMRI, policeMRH = :policeMRH, policePNO = :policePNO, sinistreMRI = :sinistreMRI, sinistreMRH = :sinistreMRH, sinistrePNO = :sinistrePNO, refMRI = :refMRI, refMRH = :refMRH, refPNO = :refPNO, causeMission = :causeMission, typeIntervention = :typeIntervention, dateExpertiseMRH = :dateExpertiseMRH, dateExpertiseMRI = :dateExpertiseMRI, dateExpertisePNO = :dateExpertisePNO, dateLettreAcceptation = :dateLettreAcceptation, dateReglementImmediat = :dateReglementImmediat, dateReglementDiffere = :dateReglementDiffere, dateDebutTravaux = :dateDebutTravaux, dateFinTravaux = :dateFinTravaux, datePaiementPrestataire = :datePaiementPrestataire, dateDeclarationAssurance = :dateDeclarationAssurance,dateRedactionRT =:dateRedactionRT, nomAgenceMRH =:nomAgenceMRH,nomAgencePNO =:nomAgencePNO,dateRdvRT =:dateRdvRT,dateRdvTravaux =:dateRdvTravaux,source =:source,status =:status, editDate =:editDate,createDate =:createDate,  etapeOp = :etapeOp, montantOp= :montantOp, origine = :origine WHERE wbcc_opportunity.idOpportunity = :idOpportunity");
                    $db->query("UPDATE wbcc_opportunity SET numeroOpportunity = :numeroOpportunity, name = :name WHERE wbcc_opportunity.idOpportunity = :idOpportunity");
                    $db->bind("idOpportunity", $idOpportunity, null);
                }
            }
            $db->bind("numeroOpportunity", $numeroOpportunity, null);
            $db->bind("name", $name, null);
            // $db->bind("immeuble", $immeuble, null);
            // $db->bind("appartement", $appartement, null);
            // $db->bind("type", $type, null);
            // $db->bind("commentaire", $commentaire, null);
            // $db->bind("etape", $etape, null);
            // $db->bind("numeroEtape", $numeroEtape, null);
            // $db->bind("gestionnaire", $gestionnaire, null);
            // $db->bind("typeSinistre", $typeSinistre, null);
            // $db->bind("nbAppartement", $nbAppartement, null);
            // $db->bind("typeDO", $typeDO, null);
            // $db->bind("numGestionnaire", $numGestionnaire, null);
            // $db->bind("nomDO", $nomDO, null);
            // $db->bind("contactClient", $contactClient, null);
            // $db->bind("numeroLot", $numeroLot, null);
            // $db->bind("denominationComMRI", $denominationComMRI, null);
            // $db->bind("denominationComMRH", $denominationComMRH, null);
            // $db->bind("denominationComPNO", $denominationComPNO, null);
            // $db->bind("denominationCouMRH", $denominationCouMRH, null);
            // $db->bind("denominationCouMRI", $denominationCouMRI, null);
            // $db->bind("denominationCouPNO", $denominationCouPNO, null);
            // $db->bind("policeMRI", $policeMRI, null);
            // $db->bind("policeMRH", $policeMRH, null);
            // $db->bind("policePNO", $policePNO, null);
            // $db->bind("sinistreMRI", $sinistreMRI, null);
            // $db->bind("sinistreMRH", $sinistreMRH, null);
            // $db->bind("sinistrePNO", $sinistrePNO, null);
            // $db->bind("refMRI", $refMRI, null);
            // $db->bind("refMRH", $refMRH, null);
            // $db->bind("refPNO", $refPNO, null);
            // $db->bind("causeMission", $causeMission, null);
            // $db->bind("typeIntervention", $typeIntervention, null);
            // $db->bind("dateExpertiseMRH", $dateExpertiseMRH, null);
            // $db->bind("dateExpertiseMRI", $dateExpertiseMRI, null);
            // $db->bind("dateExpertisePNO", $dateExpertisePNO, null);
            // $db->bind("dateLettreAcceptation", $dateLettreAcceptation, null);
            // $db->bind("dateReglementImmediat", $dateReglementImmediat, null);
            // $db->bind("dateReglementDiffere", $dateReglementDiffere, null);
            // $db->bind("dateDebutTravaux", $dateDebutTravaux, null);
            // $db->bind("dateFinTravaux", $dateFinTravaux, null);
            // $db->bind("datePaiementPrestataire", $datePaiementPrestataire, null);
            // $db->bind("dateDeclarationAssurance", $dateDeclarationAssurance, null);
            // $db->bind("dateRedactionRT", $dateRedactionRT, null);
            // $db->bind("nomAgenceMRH", $nomAgenceMRH, null);
            // $db->bind("nomAgencePNO", $nomAgencePNO, null);
            // $db->bind("dateRdvRT", $dateRdvRT, null);
            // $db->bind("dateRdvTravaux", $dateRdvTravaux, null);
            // $db->bind("source", $source, null);
            // $db->bind("status", $status, null);
            // $db->bind("editDate", $editDate, null);
            // $db->bind("createDate", $createDate, null);
            // $db->bind("etapeOp", $etapeOp, null);
            // $db->bind("montantOp", $montantOp, null);
            // $db->bind("origine", $origine, null);

            if ($db->execute()) {
                $insert = true;
                //MAIL VALIDATION OP
                if ($search) {
                    if ($search->name != $name) {
                        if ($mailReferent != "") {
                            $dest = $mailReferent;
                            $mailSyndic = $mailDO;
                            $body = "Bonjour $civiliteReferent $prenomReferent $nomReferent,<br><br>
                            Votre demande de création du dossier <b>$search->name</b> est approuvéé.<br><br>
                            Le numéro du dossier est maintenant : <b>$name</b><br><br>
                            Cordialement,<br><br>
                            <b>Extranet WBCC ASSISTANCE</b>
                           ";
                            $r = new Role();
                            $r::mailExtranetWithFiles($dest, $name . "-" . $gestionnaire . " : Validation du dossier " . $search->name, $body,  [$mailSyndic, "gestion@wbcc.fr"], [], []);
                        }
                    }
                }
            } else {
                $insert = false;
            }
        }
        if ($insert) {
            echo json_encode("Opportunity NAME Update");
        } else {
            echo json_encode("Error Opportunity NAME Update");
        }
    }

    if ($action == 'updateOPGuidContact') {
        $_POST = json_decode(file_get_contents('php://input'), true);
        // extract($_POST);
        $insert = false;
        foreach ($_POST as $key => $op) {
            extract($op);
            $search = findItemByColumn("wbcc_opportunity", "numeroOpportunity", $numeroOpportunity);
            if ($search) {
                //UPDATE
                $db->query("UPDATE wbcc_opportunity SET guidContactClient=:guidContactClient WHERE wbcc_opportunity.numeroOpportunity = :numeroOpportunity");
                $db->bind("numeroOpportunity", $numeroOpportunity, null);
                $db->bind("guidContactClient", $guidContactClient, null);
                $db->execute();
            }
        }
        echo json_encode("ok");
    }

    if ($action == 'update') {
        $_POST = json_decode(file_get_contents('php://input'), true);
        // extract($_POST);
        $insert = false;
        foreach ($_POST as $key => $op) {
            extract($op);

            $search = findItemByColumn("wbcc_opportunity", "numeroOpportunity", $numeroOpportunity);
            if ($search) {
                //UPDATE
                $db->query("UPDATE wbcc_opportunity SET numeroOpportunity = :numeroOpportunity, name = :name, immeuble = :immeuble, appartement = :appartement, type = :type, commentaire = :commentaire, etape = :etape, numeroEtape = :numeroEtape, gestionnaire = :gestionnaire, typeSinistre = :typeSinistre, nbAppartement = :nbAppartement, typeDO = :typeDO, numGestionnaire = :numGestionnaire, nomDO = :nomDO, contactClient = :contactClient, numeroLot = :numeroLot, denominationComMRI = :denominationComMRI, denominationComMRH = :denominationComMRH, denominationComPNO = :denominationComPNO, denominationCouMRH = :denominationCouMRH, denominationCouMRI = :denominationCouMRI, denominationCouPNO = :denominationCouPNO, policeMRI = :policeMRI, policeMRH = :policeMRH, policePNO = :policePNO, sinistreMRI = :sinistreMRI, sinistreMRH = :sinistreMRH, sinistrePNO = :sinistrePNO, refMRI = :refMRI, refMRH = :refMRH, refPNO = :refPNO, causeMission = :causeMission, typeIntervention = :typeIntervention, dateExpertiseMRH = :dateExpertiseMRH, dateExpertiseMRI = :dateExpertiseMRI, dateExpertisePNO = :dateExpertisePNO, dateLettreAcceptation = :dateLettreAcceptation, dateReglementImmediat = :dateReglementImmediat, dateReglementDiffere = :dateReglementDiffere, dateDebutTravaux = :dateDebutTravaux, dateFinTravaux = :dateFinTravaux, datePaiementPrestataire = :datePaiementPrestataire,dateRedactionRT =:dateRedactionRT, nomAgenceMRH =:nomAgenceMRH,nomAgencePNO =:nomAgencePNO,dateRdvRT =:dateRdvRT,dateRdvTravaux =:dateRdvTravaux,source =:source,status =:status, editDate =:editDate,createDate =:createDate, etapeOp = :etapeOp, montantOp= :montantOp, origine = :origine, nomGestionnaireAppImm=:nomGestionnaireAppImm,guidGestionnaireAppImm=:guidGestionnaireAppImm,guidDO=:guidDO,guidCommercial=:guidCommercial,guidApporteur=:guidApporteur,guidContactClient=:guidContactClient, commercial=:commercial, apporteur=:apporteur, guidAppartement=:guidAppartement, guidImmeuble=:guidImmeuble, guidComMRH=:guidComMRH,guidComMRI= :guidComMRI, guidComPNO=:guidComPNO, guidCouMRH=:guidCouMRH, guidCouMRI=:guidCouMRI, guidCouPNO=:guidCouPNO WHERE wbcc_opportunity.numeroOpportunity = :numeroOpportunity");
            } else {
                $search = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOpportunity);
                if ($search) {
                    //UPDATE
                    $db->query("UPDATE wbcc_opportunity SET numeroOpportunity = :numeroOpportunity, name = :name, immeuble = :immeuble, appartement = :appartement, type = :type, commentaire = :commentaire, etape = :etape, numeroEtape = :numeroEtape, gestionnaire = :gestionnaire, typeSinistre = :typeSinistre, nbAppartement = :nbAppartement, typeDO = :typeDO, numGestionnaire = :numGestionnaire, nomDO = :nomDO, contactClient = :contactClient, numeroLot = :numeroLot, denominationComMRI = :denominationComMRI, denominationComMRH = :denominationComMRH, denominationComPNO = :denominationComPNO, denominationCouMRH = :denominationCouMRH, denominationCouMRI = :denominationCouMRI, denominationCouPNO = :denominationCouPNO, policeMRI = :policeMRI, policeMRH = :policeMRH, policePNO = :policePNO, sinistreMRI = :sinistreMRI, sinistreMRH = :sinistreMRH, sinistrePNO = :sinistrePNO, refMRI = :refMRI, refMRH = :refMRH, refPNO = :refPNO, causeMission = :causeMission, typeIntervention = :typeIntervention, dateExpertiseMRH = :dateExpertiseMRH, dateExpertiseMRI = :dateExpertiseMRI, dateExpertisePNO = :dateExpertisePNO, dateLettreAcceptation = :dateLettreAcceptation, dateReglementImmediat = :dateReglementImmediat, dateReglementDiffere = :dateReglementDiffere, dateDebutTravaux = :dateDebutTravaux, dateFinTravaux = :dateFinTravaux, datePaiementPrestataire = :datePaiementPrestataire, dateRedactionRT =:dateRedactionRT, nomAgenceMRH =:nomAgenceMRH,nomAgencePNO =:nomAgencePNO,dateRdvRT =:dateRdvRT,dateRdvTravaux =:dateRdvTravaux,source =:source,status =:status, editDate =:editDate,createDate =:createDate,  etapeOp = :etapeOp, montantOp= :montantOp, origine = :origine, nomGestionnaireAppImm=:nomGestionnaireAppImm,guidGestionnaireAppImm=:guidGestionnaireAppImm,guidDO=:guidDO,guidCommercial=:guidCommercial,guidApporteur=:guidApporteur,guidContactClient=:guidContactClient, commercial=:commercial, apporteur=:apporteur, guidAppartement=:guidAppartement, guidImmeuble=:guidImmeuble, guidComMRH=:guidComMRH,guidComMRI= :guidComMRI, guidComPNO=:guidComPNO, guidCouMRH=:guidCouMRH, guidCouMRI=:guidCouMRI, guidCouPNO=:guidCouPNO  WHERE wbcc_opportunity.idOpportunity = :idOpportunity");
                    $db->bind("idOpportunity", $idOpportunity, null);
                } else {
                    //CREATE
                    // $db->bind("montantOp", $montantOp, null);
                    $db->query("INSERT INTO wbcc_opportunity (numeroOpportunity, name, immeuble, appartement, type, commentaire, etape, numeroEtape, gestionnaire, typeSinistre, nbAppartement, typeDO, numGestionnaire, nomDO, contactClient, numeroLot, denominationComMRI, denominationComMRH, denominationComPNO, denominationCouMRH, denominationCouMRI, denominationCouPNO, policeMRI, policeMRH, policePNO, sinistreMRI, sinistreMRH, sinistrePNO, refMRI, refMRH, refPNO, causeMission, typeIntervention, dateExpertiseMRH, dateExpertiseMRI, dateExpertisePNO, dateLettreAcceptation, dateReglementImmediat, dateReglementDiffere, dateDebutTravaux, dateFinTravaux, datePaiementPrestataire, dateRedactionRT, nomAgenceMRH,nomAgencePNO,dateRdvRT,dateRdvTravaux,source,status, editDate,createDate, etapeOp, montantOp, origine,nomGestionnaireAppImm,guidGestionnaireAppImm,guidDO,guidCommercial,guidApporteur,guidContactClient, commercial, apporteur, guidAppartement, guidImmeuble, guidComMRH, guidComMRI, guidComPNO, guidCouMRH, guidCouMRI, guidCouPNO) VALUES (:numeroOpportunity, :name, :immeuble, :appartement, :type, :commentaire, :etape, :numeroEtape, :gestionnaire, :typeSinistre, :nbAppartement, :typeDO, :numGestionnaire, :nomDO, :contactClient, :numeroLot, :denominationComMRI, :denominationComMRH, :denominationComPNO,:denominationCouMRH,:denominationCouMRI,:denominationCouPNO,:policeMRI, :policeMRH, :policePNO, :sinistreMRI, :sinistreMRH, :sinistrePNO, :refMRI, :refMRH, :refPNO, :causeMission, :typeIntervention, :dateExpertiseMRH, :dateExpertiseMRI, :dateExpertisePNO, :dateLettreAcceptation,:dateReglementImmediat, :dateReglementDiffere, :dateDebutTravaux,:dateFinTravaux, :datePaiementPrestataire,:dateRedactionRT,  :nomAgenceMRH, :nomAgencePNO, :dateRdvRT, :dateRdvTravaux, :source, :status,  :editDate, :createDate,  :etapeOp, :montantOp, :origine, :nomGestionnaireAppImm,:guidGestionnaireAppImm,:guidDO,:guidCommercial,:guidApporteur,:guidContactClient, :commercial, :apporteur, :guidAppartement, :guidImmeuble, :guidComMRH, :guidComMRI, :guidComPNO, :guidCouMRH, :guidCouMRI, :guidCouPNO)");
                }
            }

            $db->bind("numeroOpportunity", $numeroOpportunity, null);
            $db->bind("name", $name, null);
            $db->bind("immeuble", $immeuble, null);
            $db->bind("appartement", $appartement, null);
            $db->bind("type", $type, null);
            $db->bind("commentaire", $commentaire, null);
            $db->bind("etape", $etape, null);
            $db->bind("numeroEtape", $numeroEtape, null);
            $db->bind("gestionnaire", $gestionnaire, null);
            $db->bind("typeSinistre", $typeSinistre, null);
            $db->bind("nbAppartement", $nbAppartement, null);
            $db->bind("typeDO", $typeDO, null);
            $db->bind("numGestionnaire", $numGestionnaire, null);
            $db->bind("nomDO", $nomDO, null);
            $db->bind("contactClient", $contactClient, null);
            $db->bind("numeroLot", $numeroLot, null);
            $db->bind("denominationComMRI", $denominationComMRI, null);
            $db->bind("denominationComMRH", $denominationComMRH, null);
            $db->bind("denominationComPNO", $denominationComPNO, null);
            $db->bind("denominationCouMRH", $denominationCouMRH, null);
            $db->bind("denominationCouMRI", $denominationCouMRI, null);
            $db->bind("denominationCouPNO", $denominationCouPNO, null);
            $db->bind("policeMRI", $policeMRI, null);
            $db->bind("policeMRH", $policeMRH, null);
            $db->bind("policePNO", $policePNO, null);
            $db->bind("sinistreMRI", $sinistreMRI, null);
            $db->bind("sinistreMRH", $sinistreMRH, null);
            $db->bind("sinistrePNO", $sinistrePNO, null);
            $db->bind("refMRI", $refMRI, null);
            $db->bind("refMRH", $refMRH, null);
            $db->bind("refPNO", $refPNO, null);
            $db->bind("causeMission", $causeMission, null);
            $db->bind("typeIntervention", $typeIntervention, null);
            $db->bind("dateExpertiseMRH", $dateExpertiseMRH, null);
            $db->bind("dateExpertiseMRI", $dateExpertiseMRI, null);
            $db->bind("dateExpertisePNO", $dateExpertisePNO, null);
            $db->bind("dateLettreAcceptation", $dateLettreAcceptation, null);
            $db->bind("dateReglementImmediat", $dateReglementImmediat, null);
            $db->bind("dateReglementDiffere", $dateReglementDiffere, null);
            $db->bind("dateDebutTravaux", $dateDebutTravaux, null);
            $db->bind("dateFinTravaux", $dateFinTravaux, null);
            $db->bind("datePaiementPrestataire", $datePaiementPrestataire, null);
            $db->bind("dateRedactionRT", $dateRedactionRT, null);
            $db->bind("nomAgenceMRH", $nomAgenceMRH, null);
            $db->bind("nomAgencePNO", $nomAgencePNO, null);
            $db->bind("dateRdvRT", $dateRdvRT, null);
            $db->bind("dateRdvTravaux", $dateRdvTravaux, null);
            $db->bind("source", $source, null);
            $db->bind("status", $status, null);
            $db->bind("editDate", $editDate, null);
            $db->bind("createDate", $createDate, null);
            $db->bind("etapeOp", $etapeOp, null);
            $db->bind("montantOp", $montantOp, null);
            $db->bind("origine", $origine, null);
            $db->bind("nomGestionnaireAppImm", $nomGestionnaireAppImm, null);
            $db->bind("guidGestionnaireAppImm", $guidGestionnaireAppImm, null);
            $db->bind("guidDO", $guidDO, null);
            $db->bind("guidCommercial", $guidCommercial, null);
            $db->bind("guidApporteur", $guidApporteur, null);
            $db->bind("guidContactClient", $guidContactClient, null);
            $db->bind("commercial", $commercial, null);
            $db->bind("apporteur", $apporteur, null);
            $db->bind("guidAppartement", $guidAppartement, null);
            $db->bind("guidImmeuble", $guidImmeuble, null);
            $db->bind("guidComMRH", $guidComMRH, null);
            $db->bind("guidComMRI", $guidComMRI, null);
            $db->bind("guidComPNO", $guidComPNO, null);
            $db->bind("guidCouMRH", $guidCouMRH, null);
            $db->bind("guidCouMRI", $guidCouMRI, null);
            $db->bind("guidCouPNO", $guidCouPNO, null);


            if ($db->execute()) {
                $insert = true;
            } else {
                $insert = false;
            }
        }
        if ($insert) {
            echo json_encode("Opportunity Update");
        } else {
            echo json_encode("Error Opportunity Update");
        }
    }

    if ($action == "findByReferenceDO") {
        $tab = [];
        $ref = $_GET['ref'];
        $db->query("SELECT * FROM wbcc_opportunity WHERE referenceDO = '$ref' LIMIT 1");
        $op = $db->single();
        if (empty($op)) {
            echo json_encode("0");
        } else {
            echo json_encode($op->idOpportunity);
        }
    }

    if ($action == "addContact") {
        $idOpp = $_POST['idOpportunity'];
        $idContacts = $_POST['idContacts'];
        foreach ($idContacts as $id) {
            $db->query("INSERT INTO wbcc_contact_opportunity(idContactF , idOpportunityF) VALUES ($id,$idOpp)");
            if ($db->execute()) {
                $ok = "1";
            } else {
                $ok = "0";
            }
        }
        echo json_encode($ok);
    }

    if ($action == "findOpportunityByID") {
        $result = null;
        $idOpportunity = $_GET['idOpportunity'];
        $db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity = $idOpportunity LIMIT 1");
        $row = $db->single();
        if ($row) {
            $db->query("SELECT * FROM wbcc_releve_technique WHERE idOpportunityF =  $row->idOpportunity LIMIT 1");
            $rt = $db->single();
            //CONTACT
            $db->query("SELECT * FROM wbcc_contact  WHERE  numeroContact='$row->guidContactClient' LIMIT 1");
            $contact = $db->single();
            if ($contact == false) {
                $db->query("SELECT * FROM wbcc_contact  WHERE  fullName=:fullName LIMIT 1");
                $db->bind("fullName", $row->contactClient, null);
                $contact = $db->single();
            }
            if ($row->typeSinistre == "Partie commune exclusive" && $row->source != null && $row->source != "") {
                $db->query("SELECT * FROM wbcc_contact  WHERE  numeroContact='$row->source' LIMIT 1");
                $contact = $db->single();
            }
            //CIE
            $guidComp = $row->typeSinistre == "Partie commune exclusive" ? $row->guidComMRI : $row->guidComMRH;
            $db->query("SELECT * FROM wbcc_company  WHERE numeroCompany='$guidComp' LIMIT 1");
            $cie = $db->single();
            if ($cie == false) {
                $db->query("SELECT * FROM wbcc_company, wbcc_company_opportunity  WHERE idCompany=idCompanyF AND idOpportunityF=$row->idOpportunity AND category LIKE '%COMPAGNIE D\'ASSURANCE%'  LIMIT 1");
                $cie = $db->single();
            }
            //dernier delegation
            $db->query("SELECT * FROM `wbcc_opportunity_document` od, wbcc_document d WHERE d.idDocument = od.idDocumentF AND od.idOpportunityF=$row->idOpportunity AND lower(urlDocument) like '%delegation%' ORDER BY createDate DESC LIMIT 1");
            $doc = $db->single();
            //RT
            $db->query("SELECT * FROM wbcc_releve_technique WHERE idOpportunityF=$row->idOpportunity LIMIT 1");
            $rt = $db->single();
            //PIECE
            $pieces = [];
            if ($rt) {
                $db->query("SELECT  * FROM wbcc_rt_piece WHERE idRTF= $rt->idRT");
                $pieces = $db->resultSet();
                $supports = [];
                if (sizeof($pieces) > 0) {
                    foreach ($pieces as $key => $piece) {
                        $db->query("SELECT  * FROM wbcc_rt_piece_support WHERE idRTPieceF= $piece->idRTPiece ");
                        $supports = $db->resultSet();
                        $piece->listSupports = $supports;
                        foreach ($supports as $j => $support) {
                            $db->query("SELECT * FROM `wbcc_rt_revetement` WHERE `idRtPieceSupportF` = $support->idRTPieceSupport");
                            $dataR = $db->resultSet();
                            $piece->listSupports[$j]->listRevetements = $dataR;
                        }
                    }
                }
            }

            //GESTIONNAIRE
            $gestionnaire = false;
            if ($row->numGestionnaire != "" && $row->numGestionnaire != null) {
                $db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$row->numGestionnaire' LIMIT 1");
                $gestionnaire =  $db->single();
            }
            $row->gestionnaire =  ($gestionnaire) ? $gestionnaire->fullName : "";
            //adresse
            $db->query("SELECT * FROM wbcc_immeuble, wbcc_opportunity_immeuble  WHERE idImmeuble=idImmeubleF AND idOpportunityF=$row->idOpportunity LIMIT 1");
            $immeuble = $db->single();
            if ($immeuble == false && $row->immeuble != null && $row->immeuble != "") {
                $db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$row->immeuble' LIMIT 1");
                $immeuble = $db->single();
            }

            $db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF AND codeWBCC = '$row->appartement' LIMIT 1");
            $app = $db->single();
            $row->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");

            $dommages = [];
            $murs = [];
            $biens = [];
            //LOTS
            $db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement WHERE idApp = idAppartementF AND  idOpportunityF =  $row->idOpportunity LIMIT 1");
            $lots  = $db->resultSet();
            //IMMEUBLES
            $db->query("SELECT * FROM wbcc_immeuble, wbcc_opportunity_immeuble WHERE idImmeuble = idImmeubleF AND  idOpportunityF =  $row->idOpportunity LIMIT 1");
            $immeubles  = $db->resultSet();
            //INTERVENANTS
            $db->query("SELECT * FROM wbcc_contact, wbcc_contact_opportunity WHERE idContact = idContactF AND  idOpportunityF =  $row->idOpportunity");
            $contacts  = $db->resultSet();
            //SOCIETES
            $db->query("SELECT * FROM wbcc_company, wbcc_company_opportunity WHERE idCompany = idCompanyF AND  idOpportunityF =  $row->idOpportunity");
            $societes  = $db->resultSet();
            //NOTES
            $db->query("SELECT *, n.editDate as editDateNote   FROM wbcc_note n, wbcc_opportunity_note, wbcc_utilisateur, wbcc_contact  WHERE idNote = idNoteF  AND idUtilisateurF = idUtilisateur AND idContactF = idContact AND  idOpportunityF =  $row->idOpportunity ORDER BY dateNote DESC");
            $notes  = $db->resultSet();
            //DOCUMENTS
            $db->query("SELECT *, d.editDate as editDateDoc  FROM wbcc_document d, wbcc_opportunity_document, wbcc_utilisateur, wbcc_contact WHERE idDocument = idDocumentF AND idUtilisateurF = idUtilisateur AND idContactF = idContact AND  idOpportunityF =  $row->idOpportunity ORDER BY d.createDate DESC");
            $documents  = $db->resultSet();
            //ACTIVITY
            $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity, wbcc_utilisateur, wbcc_contact  WHERE idActivity = idActivityF  AND idUtilisateurF = idUtilisateur AND idContactF = idContact AND  idOpportunityF =  $row->idOpportunity AND publie=1 ORDER BY startTime DESC");
            $activities  = $db->resultSet();
            //ACTIVITY FUTUR
            $today = new DateTime(date("Y-m-d"));
            $activitiesFutur = [];
            $activitiesPasse = [];
            if (!empty($activities)) {
                foreach ($activities as $activity) {
                    $dateActivity = new DateTime(date("Y-m-d", strtotime(explode(' ', $activity->startTime)[0])));
                    if ($dateActivity > $today) {
                        $activitiesFutur[] = $activity;
                    }
                    if ($dateActivity < $today) {
                        $activitiesPasse[] = $activity;
                    }
                }
            }

            $result = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots, "gestionnaire" => $gestionnaire, "immeubles" => $immeubles, "contacts" => $contacts, "societes" => $societes, "notes" => $notes, "documents" => $documents, "activities" => $activities, "activitiesFutur" => $activitiesFutur, "activitiesPasse" => $activitiesPasse, "cie" => $cie, "contact" => $contact, "derniereDelegation" => $doc, "rt" => $rt, "pieces" => $pieces];
        }
        echo json_encode($result);
    }

    if ($action == "getOpportunitiesByContact") {
        $clotures = [];
        $tab = [];
        $enCours = [];
        $aValider = [];
        $tous = [];
        $idContact = $_GET['idContact'];
        $db->query("SELECT * FROM wbcc_opportunity, wbcc_contact_opportunity WHERE idOpportunity = idOpportunityF AND idContactF = $idContact AND etatOP=1 ORDER BY name DESC");
        $result = $db->resultSet();
        if (!empty($result)) {
            foreach ($result as $row) {
                $db->query("SELECT * FROM wbcc_releve_technique WHERE idOpportunityF =  $row->idOpportunity LIMIT 1");
                $rt = $db->single();
                //GESTIONNAIRE
                $gestionnaire = false;
                if ($row->numGestionnaire != "" && $row->numGestionnaire != null) {
                    $db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$row->numGestionnaire' LIMIT 1");
                    $gestionnaire =  $db->single();
                }
                $row->gestionnaire =  ($gestionnaire) ? $gestionnaire->fullName : "";
                //adresse
                $db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$row->immeuble' LIMIT 1");
                $immeuble = $db->single();
                $db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF AND codeWBCC = '$row->appartement' LIMIT 1");
                $app = $db->single();
                $row->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");

                $dommages = [];
                $murs = [];
                $biens = [];
                $db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement WHERE idApp = idAppartementF AND  idOpportunityF =  $row->idOpportunity LIMIT 1");
                $lots = $result = $db->resultSet();
                if (($row->status == 'Open' || $row->status == 'Inactive') && $row->demandeCloture == 0 && $row->demandeValidation == 1) {
                    $enCours[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots, "gestionnaire" => $gestionnaire];
                }

                if (($row->status == 'Lost' || $row->status == 'Won') && $row->demandeCloture == 0 && $row->demandeValidation == 1) {
                    $clotures[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots, "gestionnaire" => $gestionnaire];
                }

                if ($row->demandeValidation == 0) {
                    $aValider[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots, "gestionnaire" => $gestionnaire];
                }
                $tous[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots, "gestionnaire" => $gestionnaire];
            }
        }
        $tab = ["enCours" => $enCours, "aValider" => $aValider, "tous" => $tous, "clotures" => $clotures];
        echo json_encode($tab);
    }

    if ($action == "getOpportunitiesByCompany") {
        $tab = [];
        $enCours = [];
        $clotures = [];
        $aValider = [];
        $tous = [];
        $idCompany = $_GET['idCompany'];
        $db->query("SELECT * FROM wbcc_opportunity, wbcc_company_opportunity WHERE idOpportunity = idOpportunityF AND idCompanyF = $idCompany AND etatOP=1");
        $result = $db->resultSet();
        if (!empty($result)) {
            foreach ($result as $row) {
                $db->query("SELECT * FROM wbcc_releve_technique WHERE idOpportunityF =  $row->idOpportunity LIMIT 1");
                $rt = $db->single();
                //GESTIONNAIRE
                $gestionnaire = false;
                if ($row->numGestionnaire != "" && $row->numGestionnaire != null) {
                    $db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$row->numGestionnaire' LIMIT 1");
                    $gestionnaire =  $db->single();
                }
                $row->gestionnaire =  ($gestionnaire) ? $gestionnaire->fullName : "";
                //adresse
                $db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$row->immeuble' LIMIT 1");
                $immeuble = $db->single();
                $db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF AND codeWBCC = '$row->appartement' LIMIT 1");
                $app = $db->single();
                $row->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
                $dommages = [];
                $murs = [];
                $biens = [];
                $db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement WHERE idApp = idAppartementF AND  idOpportunityF =  $row->idOpportunity LIMIT 1");
                $lots = $result = $db->resultSet();
                if (($row->status == 'Open' || $row->status == 'Inactive' || $row->status = "" || $row->status == null) && $row->demandeCloture == 0) {
                    $enCours[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots];
                }

                if (($row->status == 'Lost' || $row->status == 'Won')) {
                    $clotures[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots];
                }

                if ($row->demandeValidation == 0) {
                    $aValider[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots];
                }
                $tous[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots];
            }
        }
        $tab = ["enCours" => $enCours, "aValider" => $aValider, "tous" => $tous, "clotures" => $clotures];
        echo json_encode($tab);
    }

    if ($action == "getOpportunitiesByContactCompany") {
        $clotures = [];
        $tab = [];
        $enCours = [];
        $aValider = [];
        $tous = [];
        $idContact = $_GET['idContact'];
        $idCompany = $_GET['idCompany'];
        $db->query("SELECT * FROM wbcc_opportunity, wbcc_contact_opportunity, wbcc_company_opportunity WHERE idOpportunity = wbcc_contact_opportunity.idOpportunityF AND idOpportunity = wbcc_company_opportunity.idOpportunityF AND idContactF = $idContact AND idCompanyF = $idCompany AND etatOP=1 ORDER BY name DESC");
        $result = $db->resultSet();
        if (!empty($result)) {
            foreach ($result as $row) {
                $db->query("SELECT * FROM wbcc_releve_technique WHERE idOpportunityF =  $row->idOpportunity LIMIT 1");
                $rt = $db->single();
                //GESTIONNAIRE
                $gestionnaire = false;
                if ($row->numGestionnaire != "" && $row->numGestionnaire != null) {
                    $db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$row->numGestionnaire' LIMIT 1");
                    $gestionnaire =  $db->single();
                }
                $row->gestionnaire =  ($gestionnaire) ? $gestionnaire->fullName : "";
                //adresse
                $db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$row->immeuble' LIMIT 1");
                $immeuble = $db->single();
                $db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF AND codeWBCC = '$row->appartement' LIMIT 1");
                $app = $db->single();
                $row->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");

                $dommages = [];
                $murs = [];
                $biens = [];
                $db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement WHERE idApp = idAppartementF AND  idOpportunityF =  $row->idOpportunity LIMIT 1");
                $lots = $result = $db->resultSet();
                if (($row->status == 'Open' || $row->status == 'Inactive') && $row->demandeCloture == 0 && $row->demandeValidation == 1) {
                    $enCours[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots, "gestionnaire" => $gestionnaire];
                }

                if (($row->status == 'Lost' || $row->status == 'Won') && $row->demandeCloture == 0 && $row->demandeValidation == 1) {
                    $clotures[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots, "gestionnaire" => $gestionnaire];
                }

                if ($row->demandeValidation == 0) {
                    $aValider[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots, "gestionnaire" => $gestionnaire];
                }
                $tous[] = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots, "gestionnaire" => $gestionnaire];
            }
        }
        $tab = ["enCours" => $enCours, "aValider" => $aValider, "tous" => $tous, "clotures" => $clotures];
        echo json_encode($tab);
    }

    if ($action == "getDocumentsByOpportunity") {
        $idOpportunity = $_GET['idOpportunity'];
        $db->query("SELECT * FROM wbcc_document, wbcc_opportunity_document, wbcc_utilisateur WHERE idDocument = idDocumentF AND idUtilisateur = idUtilisateurF AND idOpportunityF = :idOpportunity");
        $db->bind("idOpportunity", $idOpportunity, null);
        $result = $db->resultSet();
        echo json_encode($result);
    }

    if ($action == "findPAPByIdOP") {
        $idOpportunity = $_GET['idOpp'];
        $db->query("SELECT * FROM wbcc_opportunity_papb2c_contact, wbcc_pap_b2c, wbcc_opportunity WHERE idPAPF= idPAP AND idOpportunityF=idOpportunity AND idOpportunity = $idOpportunity LIMIT 1");
        $row = $db->resultSet();
        echo json_encode($row);
    }

    if ($action == "listeOPPAP") {
        $tab = [];
        $db->query("SELECT  DISTINCT(idPAP) FROM `wbcc_pap_b2c`, wbcc_opportunity, wbcc_opportunity_papb2c_contact, wbcc_utilisateur, wbcc_contact WHERE idPAP = idPAPF AND idOpportunity = idOpportunityF AND  idUserF=idUtilisateur AND wbcc_contact.idContact=wbcc_utilisateur.idContactF");
        $row = $db->resultSet();
        if (sizeof($row) != 0) {
            foreach ($row as $key => $value) {
                $db->query("SELECT * FROM `wbcc_pap_b2c`, wbcc_opportunity, wbcc_opportunity_papb2c_contact, wbcc_utilisateur, wbcc_contact WHERE idPAP = idPAPF AND idOpportunity = idOpportunityF AND  idUserF=idUtilisateur AND wbcc_contact.idContact=wbcc_utilisateur.idContactF AND idPAP = $value->idPAP LIMIT 1 ");
                $tab[] = $db->single();;
            }
        }
        echo json_encode($tab);
    }

    if ($action == "addOrEditCabinetExpert") {
        extract($_POST);
        $newIdCie = "0";
        $newNumeroCie = "";
        if (isset($checkCie)) {
            $newCie = explode(";", $checkCie);
            $newIdCie = $newCie[0];
            $newNumeroCie = $newCie[1];
        }
        $result = false;


        if ($oldIdCie != "0") {
            $db->query("DELETE FROM wbcc_company_opportunity WHERE idOpportunityF = $idOp AND idCompanyF = $oldIdCie");
            $db->execute();
            $result = true;
        }
        if ($newIdCie != "0") {
            $db->query("INSERT INTO wbcc_company_opportunity (`idCompanyF`, `idOpportunityF`) Values ($newIdCie, $idOp)");
            if ($db->execute()) {
                $db->query("UPDATE `wbcc_opportunity` SET idCabinetExpertF=$newIdCie WHERE idOpportunity = $idOp");
                if ($db->execute()) {
                }
            }
            $result = true;
        }

        if ($newIdCie != "0") {
            $db->query("SELECT * FROM `wbcc_company`  WHERE idCompany= $newIdCie LIMIT 1");
            $result = $db->single();
        }
        echo json_encode($result);
    }

    if ($action == "AddOrEditCie") {
        extract($_POST);
        $newIdCie = "0";
        $newNumeroCie = "";
        if (isset($checkCie)) {
            $newCie = explode(";", $checkCie);
            $newIdCie = $newCie[0];
            $newNumeroCie = $newCie[1];
        }
        $result = false;
        $guidComp = $type == "MRI" ? "guidComMRI" : "guidComMRH";
        $refPolice = $type == "MRI" ? "policeMRI" : "policeMRH";

        if ($oldIdCie != "") {
            $db->query("DELETE FROM wbcc_company_opportunity WHERE idOpportunityF = $idOp AND idCompanyF = $oldIdCie");
            $db->execute();
            $result = true;
        }
        if ($newIdCie != "0") {
            $db->query("INSERT INTO wbcc_company_opportunity (`idCompanyF`, `idOpportunityF`) Values ($newIdCie, $idOp)");
            if ($db->execute()) {
                $db->query("UPDATE `wbcc_opportunity` SET $guidComp = '$newNumeroCie', $refPolice = '$police' WHERE idOpportunity = $idOp");
                if ($db->execute()) {
                }
            }
            $result = true;
        }

        if ($newIdCie != "0") {
            $db->query("SELECT * FROM `wbcc_company`  WHERE idCompany= $newIdCie LIMIT 1");
            $result = $db->single();
        }
        echo json_encode($result);
    }

    if ($action == "AddOrEditInterlocuteur") {

        extract($_POST);

        if ($action == "edit") {
            $db->query("SELECT * FROM wbcc_contact_opportunity WHERE idContactF = $idContact and idOpportunityF = $idOp LIMIT 1");
            $contactOP = $db->single();
            if ($contactOP) {
            } else {
                $db->query("INSERT INTO wbcc_contact_opportunity (idContactF, idOpportunityF) VALUES ($idContact,$idOp)");
                $result = $db->execute();
            }
        } else {
            $db->query("SELECT * FROM wbcc_contact_opportunity WHERE idContactF = $idContact and idOpportunityF = $idOp LIMIT 1");
            $contactOP = $db->single();
            if ($contactOP) {
            } else {
                $db->query("INSERT INTO wbcc_contact_opportunity (idContactF, idOpportunityF) VALUES ($idContact,$idOp)");
                $result = $db->execute();
            }
        }

        if (isset($idCompany) && $idCompany != 0) {
            $db->query("SELECT * FROM wbcc_contact_company WHERE idContactF = $idContact and idCompanyF = $idCompany LIMIT 1");
            $contactCompany = $db->single();
            if (! $contactCompany) {
                $db->query("INSERT INTO wbcc_contact_company (idContactF, idCompanyF) VALUES ($idContact,$idCompany)");
                $db->execute();
            }
        }

        if (isset($typeContact)) {
            $contact = findItemByColumn("wbcc_contact", "idContact", $idContact);
            if ($typeContact == 'expert') {
                //UPDATE TABLE OPPORTUNITY
                if ($contact) {
                    $db->query("UPDATE wbcc_contact SET statutContact='EXPERT ASSUREUR' WHERE idContact = $idContact ");
                    $db->execute();

                    $db->query("UPDATE wbcc_opportunity SET idExpertCompanyF=$idContact, nomExpertCompany='$contact->fullName' WHERE idOpportunity = $idOp ");
                    $db->execute();
                }
            }
            echo json_encode($contact);
        } else {
            echo json_encode($result);
        }
    }


    if ($action == "terminerAppel") {

        $idOpportunity = isset($_POST['idOP']) ? $_POST['idOP'] : '';
        $numeroOpportunity = isset($_POST['numeroOP']) ? $_POST['numeroOP'] : '';
        $auteurAppel = isset($_POST['auteurAppel']) ? $_POST['auteurAppel'] : '';
        $detailsAppel = isset($_POST['detailsAppel']) ? $_POST['detailsAppel'] : '';
        $idauteurAppel = isset($_POST['idauteurAppel']) ? $_POST['idauteurAppel'] : '';
        $dateDebAppel = isset($_POST['dateDebAppel']) ? $_POST['dateDebAppel'] : '';
        $objetAppel = isset($_POST['objetAppel']) ? $_POST['objetAppel'] : '';
        $numeroauteurAppel = isset($_POST['numeroauteurAppel']) ? $_POST['numeroauteurAppel'] : '';
        $idUtilisateur = isset($_POST['idUtilisateur']) ? $_POST['idUtilisateur'] : '';

        $fileAppel = isset($_FILES['fileAppel']) ? $_FILES['fileAppel'] : '';


        $numero = date("YmdHis") . "$idOpportunity";
        $numeroActivity = date("YmdHis") . "$idOpportunity";
        $db->query("INSERT INTO wbcc_note (numeroNote,plainText, noteText, source, isPrivate, auteur, idUtilisateurF) VALUES(:numeroNote, :plainText, :noteText, :source, :isPrivate, :auteur, :idUtilisateurF)");
        $db->bind("source", "EXTRA");
        $db->bind("auteur", $auteurAppel);
        $db->bind("idUtilisateurF", $idauteurAppel);
        $db->bind("isPrivate", 0);
        $db->bind("numeroNote", $numero);
        $db->bind("plainText", "Appel Téléphonique " . $detailsAppel);
        $db->bind("noteText", "Appel Téléphonique " . $detailsAppel);
        $db->execute();

        $idNote = findItemByColumn("wbcc_note", "numeroNote", $numero)->idNote;

        $db->query("INSERT INTO wbcc_opportunity_note (idOpportunityF, idNoteF, numeroOpportunityF) VALUES($idOpportunity,$idNote, '$numeroOpportunity')");


        $db->execute();

        //CREATE
        $db->query("INSERT INTO wbcc_activity (numeroActivity, details,regarding, startTime, endTime, createDate, source, organizer, organizerGuid, isDeleted, activityType, isMailSend, idUtilisateurF, isCleared) VALUES (:numeroActivity, :details, :regarding, :startTime, :endTime, :createDate, :source, :organizer, :organizerGuid, :isDeleted, :activityType, :isMailSend, :idUtilisateurF, :isCleared)");

        $db->bind("numeroActivity", $numeroActivity, null);
        $db->bind("details", $detailsAppel, null);
        $db->bind("regarding", $objetAppel, null);
        $db->bind("startTime", $dateDebAppel, null);
        $db->bind("endTime", date("dmYHis"), null);
        $db->bind("createDate", date("YmdHis"), null);
        $db->bind("source", "EXTRA", null);
        $db->bind("organizer", $auteurAppel, null);
        $db->bind("organizerGuid", $numeroauteurAppel, null);
        $db->bind("isDeleted", "0", null);
        $db->bind("activityType", "Appel");
        $db->bind("isMailSend", "0", null);
        $db->bind("idUtilisateurF", $idUtilisateur, null);
        $db->bind("isCleared", "true", null);
        $db->execute();

        $activity = findItemByColumn("wbcc_activity", "numeroActivity", $numeroActivity);

        $db->query("INSERT INTO wbcc_opportunity_activity (numeroOpportunityF, idActivityF, idOpportunityF) VALUES (:numeroOpportunityF, :idActivityF, :idOpportunityF)");

        $db->bind("numeroOpportunityF", $numeroOpportunity, null);
        $db->bind("idActivityF", $activity->idActivity, null);
        $db->bind("idOpportunityF", $idOpportunity, null);
        $result = $db->execute();

        echo json_encode($result);
    }

    if ($action == "AddOrUpdateContact") {
        extract($_POST);
        $fullname = $prenomContact . " " . $nomContact;
        if (isset($contactnew)  && $contactnew != "" && $contactnew != null) {
            $idContactnew = explode(',', $contactnew)[0];
            $numeroContactnew = explode(',', $contactnew)[1];
        } else {
            $idContactnew = $idContact;
        }

        if ($action == "update") {
            $db->query("UPDATE `wbcc_contact` SET civiliteContact=:civiliteContact, nomContact = :nomContact, prenomContact = :prenomContact, fullName = :fullname, telContact = :telContact, emailContact = :emailContact, dateNaissance=:dateNaissance, statutContact=:statutContact WHERE idContact = '$idContact'");
            $db->bind("civiliteContact", $civiliteContact, null);
            $db->bind("nomContact", $nomContact, null);
            $db->bind("prenomContact", $prenomContact, null);
            $db->bind("fullname", $fullname, null);
            $db->bind("telContact", $telContact, null);
            $db->bind("emailContact", $emailContact, null);
            $db->bind("dateNaissance", $dateNaissanceContact, null);
            $db->bind("statutContact", $statutContact, null);
            $result = $db->execute();
            $contact = findItemByColumn("wbcc_contact", "idContact", $idContact);
        } else if ($action == "new") {
            $db->query("INSERT INTO wbcc_contact_opportunity (idContactF, numeroContactF, idOpportunityF, numeroOpportunityF) VALUES ($idContactnew,'$numeroContactnew',$idOp, '$numeroOP')");
            $db->execute();
            $contact = findItemByColumn("wbcc_contact", "idContact", $idContactnew);
        } else {
            $db->query("DELETE FROM wbcc_contact_opportunity WHERE idOpportunityF = $idOp AND idContactF = $idContact");
            $result = $db->execute();

            $db->query("INSERT INTO wbcc_contact_opportunity (idContactF, idOpportunityF) VALUES ($idContactnew,$idOp)");
            $db->execute();
            $contact = findItemByColumn("wbcc_contact", "idContact", $idContactnew);
        }
        $req = "";
        // $contact = findItemByColumn("wbcc_contact", "idContact", $idContactnew);
        $fullname = "$contact->prenomContact $contact->nomContact";
        if (isset($typeContact) && $typeContact == "responsable") {
            if ($typeResponsable == "voisin") {
                $db->query("UPDATE `wbcc_recherche_fuite` SET  idVoisinF=$idContactnew WHERE idOpportunityF=$idOp ");
                $db->execute();
            } else {
                if ($typeResponsable == "pc") {
                    //UPDATE PC
                }
            }
            $db->query("UPDATE `wbcc_opportunity` SET  nomResponsable=:nomResponsable, idResponsableContactF=$idContactnew WHERE idOpportunity=$idOp ");
            $db->bind("nomResponsable", $fullname, null);
            $result = $db->execute();
            $result = $contact;
        } else {
            if (isset($typeOP) &&  str_contains($typeOP, "commune")) {
                $db->query("UPDATE `wbcc_opportunity` SET guidReferentDo = :guidReferentDo, nomReferentDo=:nomReferentDo, idReferentDo= $idContactnew WHERE idOpportunity=$idOp ");
                $db->bind("guidReferentDo", $numeroContactnew, null);
                $db->bind("nomReferentDo", $fullname, null);
                $result = $db->execute();
                //LINK CONTACT-COMPANY
                $db->query("DELETE FROM wbcc_contact_company WHERE idCompanyF = $idCompany AND idContactF = $idContact");
                $result = $db->execute();

                $db->query("INSERT INTO wbcc_contact_company (idContactF, idCompanyF) VALUES ($idContactnew,$idCompany)");
                $db->execute();
            } else {
                if ($typeDO == "Particuliers") {
                    $req = ", nomDO='$fullname'";
                }
                $db->query("UPDATE `wbcc_opportunity` SET guidContactClient = '$numeroContactnew', contactClient='$fullname', idContactClient=$idContactnew $req WHERE idOpportunity=$idOp ");
                $result = $db->execute();
            }
        }

        echo json_encode($result);
    }

    if ($action == "UpadateImmLotOp") {

        $res = null;
        extract($_POST);
        if ($action == "rvResponsable") {
            if ($idApp == "0" || $idApp == "") {
                $numAPP = "APP" . date('dmYHis') .    $idImmeuble;
                $db->query("INSERT INTO `wbcc_appartement`(numeroApp, etage , codePorte, lot, batiment, ville, codePostal , adresse, idImmeubleF, typeLot, libellePartieCommune, cote, idOccupant)   VALUES('$numAPP',:etage, :porte, :lot, :batiment,  :ville, :codePostal , :adresse, $idImmeuble, :typeLot, :libellePartieCommune, :cote, :idOccupant)");
            } else {
                $db->query("UPDATE `wbcc_appartement` SET etage = :etage, codePorte = :porte, lot = :lot, batiment = :batiment, ville = :ville, codePostal = :codePostal , adresse = :adresse, idImmeubleF=$idImmeuble, typeLot=:typeLot, libellePartieCommune=:libellePartieCommune, cote=:cote, idOccupant:idOccupant WHERE idApp = $idApp");
            }
            $db->bind("etage", $etage, null);
            $db->bind("porte", $porte, null);
            $db->bind("lot", $lot, null);
            $db->bind("batiment", $batiment, null);
            $db->bind("ville", $ville, null);
            $db->bind("codePostal", $codePostal, null);
            $db->bind("adresse", $adresse, null);
            $db->bind("typeLot", isset($typeLot) ? $typeLot : "PP", null);
            $db->bind("libellePartieCommune", isset($libellePartieCommune) ? $libellePartieCommune : null, null);
            $db->bind("cote", isset($cote) ? $cote : null, null);
            $db->bind("idOccupant", $idContact, null);
            $result = $db->execute();
            if ($idApp == "0" || $idApp == "") {
                $app = findItemByColumn("wbcc_appartement", "numeroApp", $numAPP);
                $idApp = $app->idApp;
                $db->query("INSERT INTO wbcc_appartement_contact (idContacF, idAppartementF) VALUES ($idContact,$app->idApp)");
                $db->execute();
            }
            $res = findItemByColumn("wbcc_immeuble", "idImmeuble", $idImmeuble);
        } else {
            $idImmeublenew = "";
            $numeroImmeublenew = "";
            $codeImmeuble = "";
            if (isset($immeublenew) && $immeublenew != "undefined" && $immeublenew != null) {
                $idImmeublenew = explode(',', $immeublenew)[0];
                $numeroImmeublenew = isset(explode(',', $immeublenew)[1]) ? explode(',', $immeublenew)[1] : "0";
                $codeImmeuble = isset(explode(',', $immeublenew)[2]) ? explode(',', $immeublenew)[2] : "";
            }
            $idImmeublenew = ($idImmeublenew == "" ? $idImmeuble : $idImmeublenew);
            if ($action == "editImm") {
                $db->query("DELETE FROM wbcc_opportunity_immeuble WHERE idOpportunityF = '$idOp'");
                $db->execute();

                $db->query("INSERT INTO wbcc_opportunity_immeuble (idOpportunityF, idImmeubleF, numeroOpportunityF, numeroImmeubleF) VALUES ($idOp,$idImmeublenew,'$numeroOP','$numeroImmeublenew')");
                $db->execute();

                $db->query("UPDATE `wbcc_opportunity` SET guidImmeuble = '$numeroImmeuble', immeuble = '$codeImmeuble', idImmeuble=$idImmeublenew WHERE idOpportunity = $idOp");
                $result = $db->execute();
                $res = findItemByColumn("wbcc_immeuble", "idImmeuble", $idImmeublenew);
            } else {
                if ($idApp == "0" || $idApp == "") {
                    $numAPP = "APP" . date('dmYHis') .    $idImmeuble;
                    $db->query("INSERT INTO `wbcc_appartement`(numeroApp, etage , codePorte, lot, batiment, ville, codePostal , adresse, idImmeubleF, typeLot, libellePartieCommune, cote)   VALUES('$numAPP',:etage, :porte, :lot, :batiment,  :ville, :codePostal , :adresse, $idImmeuble, :typeLot, :libellePartieCommune, :cote)");
                } else {
                    $db->query("UPDATE `wbcc_appartement` SET etage = :etage, codePorte = :porte, lot = :lot, batiment = :batiment, ville = :ville, codePostal = :codePostal , adresse = :adresse, idImmeubleF=$idImmeuble, typeLot=:typeLot, libellePartieCommune=:libellePartieCommune, cote=:cote WHERE idApp = $idApp");
                }
                $db->bind("etage", $etage, null);
                $db->bind("porte", $porte, null);
                $db->bind("lot", $lot, null);
                $db->bind("batiment", $batiment, null);
                $db->bind("ville", $ville, null);
                $db->bind("codePostal", $codePostal, null);
                $db->bind("adresse", $adresse, null);
                $db->bind("typeLot", isset($typeLot) ? $typeLot : "PP", null);
                $db->bind("libellePartieCommune", isset($libellePartieCommune) ? $libellePartieCommune : null, null);
                $db->bind("cote", isset($cote) ? $cote : null, null);
                $result = $db->execute();
                if ($idApp == "0") {
                    $app = findItemByColumn("wbcc_appartement", "numeroApp", $numAPP);
                    $idApp = $app->idApp;
                    $db->query("INSERT INTO wbcc_opportunity_appartement (idOpportunityF, idAppartementF) VALUES ($idOp,$app->idApp)");
                    $db->execute();
                }
                if ($idApp != "" && $idApp != "0") {
                    $app = findItemByColumn("wbcc_appartement", "idApp", $idApp);
                    $db->query("UPDATE `wbcc_opportunity` SET guidAppartement = '$app->numeroApp', appartement = '$app->codeWBCC', idAppartement=$idApp WHERE idOpportunity = $idOp");
                    $result = $db->execute();
                }
                $res = findItemByColumn("wbcc_immeuble", "idImmeuble", $idImmeuble);
            }
        }

        echo json_encode($res);
    }

    if ($action == "getAllCategorieDo") {
        $db->query("SELECT * FROM `wbcc_categorie_do`");
        $data =  $db->resultSet();
        echo json_encode($data);
    }

    if ($action == "getSCategorieDo") {
        $idCategorie = $_GET["idCategorie"];
        $data = [];
        if ($idCategorie != "" && $idCategorie != null && $idCategorie != "0" && $idCategorie != "undefined") {
            $db->query("SELECT * FROM `wbcc_sous_categorie_do` WHERE idCategorieF = $idCategorie");
            $data =  $db->resultSet();
        }
        echo json_encode($data);
    }

    if ($action == 'newOpportunity') {
        $insert = false;
        extract($_POST);

        $compDO = null;
        $compAssurance = null;
        $compAgence = null;
        $compCourtier = null;

        $nomGestionnaireAppImm = "";
        $idGestionnaireAppImm = null;
        $numeroGestionnaireAppImm = "";

        $denominationComMRI = "";
        $denominationComMRH = "";
        $denominationComPNO = "";

        $denominationCouMRI = "";
        $denominationCouMRH = "";
        $denominationCouPNO = "";

        $idComMRI = null;
        $idComMRH = null;
        $idComPNO = null;

        $idCouMRI = null;
        $idCouMRH = null;
        $idCouPNO = null;

        $guidComMRI = "";
        $guidComMRH = "";
        $guidComPNO = "";

        $guidCouMRI = "";
        $guidCouMRH = "";
        $guidCouPNO = "";

        $policeMRI = "";
        $policeMRH = "";
        $policePNO = "";

        $nomAgenceMRH = "";
        $nomAgencePNO = "";
        $idAgenceMRH = null;
        $idAgencePNO = null;
        $varDeleg = "0";

        $nomClient = "";
        $idClient = null;
        $numeroClient = "";

        $imm = findItemByColumn("wbcc_immeuble", "idImmeuble ", $idImmeuble);

        if ($categorie == "Particuliers") {
            $nomGestionnaireAppImm = $imm->nomDO;
            $idGestionnaireAppImm = $imm->idDO;
            $numeroGestionnaireAppImm = $imm->guidDO;

            $nomClient = $contactClient;
            $idClient = $idContactClient;
            $numeroClient = $numeroClient;

            $compDO = findItemByColumn("wbcc_company", "idCompany",  $imm->idDO);
            if ($compDO == null || $compDO == false) {
                $compDO = findItemByColumn("wbcc_company", "name", $imm->nomDO);
            }
        } else {

            $nomGestionnaireAppImm = $nomDO;
            $idGestionnaireAppImm = $idDO;
            $numeroGestionnaireAppImm = $numeroDO;

            $nomClient = $nomDO;
            $idClient = $idDO;
            $numeroClient = $numeroDO;

            $compDO = findItemByColumn("wbcc_company", "idCompany",  $idDO);
            if ($compDO == null || $compDO == false) {
                $compDO = findItemByColumn("wbcc_company", "name", $nomDO);
            }

            if ($scategorie == "Agence immobilière") {
                $compAgence = findItemByColumn("wbcc_company", "idCompany",  $idDO);
                if ($compAgence == null || $compAgence == false) {
                    $compAgence = findItemByColumn("wbcc_company", "name", $nomDO);
                }
            }
        }


        if (str_contains($typeSinistre, "commune")) {

            $compAssurance = findItemByColumn("wbcc_company", "idCompany",  $imm->idCompagnieAssurance);
            if ($compAssurance == null || $compAssurance == false) {
                $compAssurance = findItemByColumn("wbcc_company", "name", $imm->nomCompagnieAssurance);
            }

            $compCourtier = findItemByColumn("wbcc_company", "idCompany",  $imm->idCourtier);
            if ($compCourtier == null || $compCourtier == false) {
                $compCourtier = findItemByColumn("wbcc_company", "name", $imm->nomCourtier);
            }

            $denominationComMRI =  $compAssurance ? $compAssurance->name : "";
            $denominationCouMRI = $compCourtier ? $compCourtier->name : "";
            $idComMRI =  $compAssurance ? $compAssurance->idCompany : "";
            $idCouMRI = $compCourtier ? $compCourtier->idCompany : "";
            $policeMRI = $imm->numPolice;
            $guidCouMRI = $compCourtier ? $compCourtier->numeroCompany : "";
            $guidComMRI = $compAssurance ? $compAssurance->numeroCompany : "";
            $nomAgenceMRI = $compAgence ? $compAgence->name : "";
            $idAgenceMRI = $compAgence ? $compAgence->idCompany : null;
            $guidAgenceMRI = $compAgence ? $compAgence->numeroCompany : "";
            $varDeleg = "checkedMRI";
        } else {

            $compAssurance = findItemByColumn("wbcc_company", "idCompany",  $idAssuranceClient);
            $compCourtier = findItemByColumn("wbcc_company", "idCompany",  $idcourtierClient);

            if ($statutClient == "Coproprietaire Non Occupant") {
                $denominationComPNO = $compAssurance ? $compAssurance->name : "";
                $denominationCouPNO = $compCourtier ? $compCourtier->name : "";
                $idComPNO = $compAssurance ? $compAssurance->idCompany : "";
                $idCouPNO = $compCourtier ? $compCourtier->idCompany : "";
                $policePNO = $imm->numPolice;
                $guidCouPNO = $compCourtier ? $compCourtier->numeroCompany : "";
                $guidComPNO = $compAssurance ? $compAssurance->numeroCompany : "";
                $nomAgencePNO = $compAgence ? $compAgence->name : "";
                $idAgencePNO = $compAgence ? $compAgence->idCompany : null;
                $guidAgencePNO = $compAgence ? $compAgence->numeroCompany : "";
                $varDeleg = "checkedPNO";
            } else {
                $denominationComMRH = $compAssurance ? $compAssurance->name : "";
                $denominationCouMRH =  $compCourtier ? $compCourtier->name : "";
                $idComMRH = $compAssurance ? $compAssurance->idCompany : null;
                $idCouMRH = $compCourtier ? $compCourtier->idCompany : null;
                $policeMRH = $imm->numPolice;
                $guidCouMRH = $compCourtier ? $compCourtier->numeroCompany : "";
                $guidComMRH = $compAssurance  ? $compAssurance->numeroCompany : "";
                $nomAgenceMRH = $compAgence ? $compAgence->name : "";
                $idAgenceMRH = $compAgence ? $compAgence->idCompany : null;
                $guidAgenceMRH = $compAgence ?  $compAgence->numeroCompany : null;
                $varDeleg = "checkedMRH";
            }
        }



        //for ($i = 0; $i < $nbAppartement; $i++) {
        $db->query("SELECT * FROM wbcc_parametres LIMIT 1");
        $param = $db->single();
        $name = "";
        if ($type == "A.M.O.2") {
            $numero =  str_pad(($param->numeroOPamo + 3), 4, '0', STR_PAD_LEFT);
            $name = "OP" . ($createDateAMO2 != null && $createDateAMO2 != "" ? $createDateAMO2 : date("Y-m-d")) . "-A$numero";
        } else {
            $numero = str_pad(($param->numeroOP + 1), 4, '0', STR_PAD_LEFT);
            $name = "OP" . date("Y-m-d") . "-$numero";
        }

        $numeroOpportunity = "OPP" . date("Ymdis") . $idUtilisateur;
        $db->query("INSERT INTO wbcc_opportunity (numeroOpportunity, name, immeuble, idImmeuble, guidImmeuble, appartement, idAppartement, type, commentaire, etape, gestionnaire, typeSinistre, nbAppartement, typeDO, nomDO, idDOF, guidDO, contactClient, idContactClient, guidContactClient, denominationComMRI, denominationComMRH, denominationComPNO, policeMRI, policeMRH, policePNO, nomAgenceMRH,nomAgencePNO,source,status, editDate,createDate, etapeOp, origine,nomGestionnaireAppImm,idGestionnaireAppImm,guidGestionnaireAppImm,idAgenceMRH,idAgencePNO, guidComMRH, guidComMRI, guidComPNO, guidCouMRH, guidCouMRI, guidCouPNO, numGestionnaire, idCommercial, guidCommercial, commercial,commentaireSituation,idReferentDo,typeIntervention,causeMission,apporteur,guidApporteur,idApporteur,idAuteurCreation, nomReferentDo, guidReferentDo) VALUES (:numeroOpportunity, :name, :immeuble, :idImmeuble, :guidImmeuble, :appartement, :idAppartement ,:type, :commentaire, :etape, :gestionnaire, :typeSinistre,:nbAppartement, :typeDO, :nomDO, :idDO, :guidDO, :contactClient, :idContactClient, :guidContactClient, :denominationComMRI, :denominationComMRH, :denominationComPNO, :policeMRI, :policeMRH, :policePNO, :nomAgenceMRH, :nomAgencePNO, :source, :status,  :editDate, :createDate,  :etapeOp, :origine, :nomGestionnaireAppImm,:idGestionnaireAppImm,:guidGestionnaireAppImm,:idAgenceMRH,:idAgencePNO, :guidComMRH, :guidComMRI, :guidComPNO, :guidCouMRH, :guidCouMRI, :guidCouPNO, :numGestionnaire, :idCommercial, :guidCommercial, :commercial,:commentaireSituation,:idReferentDo,:typeIntervention, :causeMission, :apporteur, :guidApporteur, :idApporteur, :idAuteurCreation, :nomReferentDo, :guidReferentDo)");

        $db->bind("numeroOpportunity", $numeroOpportunity, null);
        $db->bind("name", $name, null);
        $db->bind("immeuble", $immeuble, null);
        $db->bind("idImmeuble", $idImmeuble, null);
        $db->bind("guidImmeuble", $numeroImmeuble, null);
        $db->bind("appartement", $numeroLot, null);
        $db->bind("idAppartement", ($idApp == "0" || $idApp == 0) ? null : $idApp, null);
        $db->bind("type", $type, null);
        $db->bind("commentaire", $commentaire, null);
        $db->bind("etape", $etape, null);
        $db->bind("gestionnaire", $idGestionnaire, null);
        $db->bind("typeSinistre", $typeSinistre, null);
        $db->bind("nbAppartement", "", null);
        $db->bind("typeDO", $typeDO, null);
        $db->bind("nomDO", $nomDO, null);
        $db->bind("idDO", ($idDO == "0" || $idDO == 0 || $idDO == "" || $idDO == null) ? null : $idDO, null);
        $db->bind("guidDO", $numeroDO, null);
        $db->bind("contactClient", $nomClient, null);
        $db->bind("idContactClient", ($idClient == "0" || $idClient == 0) ? null : $idClient, null);
        $db->bind("guidContactClient", $numeroClient, null);
        $db->bind("denominationComMRI", $denominationComMRI, null);
        $db->bind("denominationComMRH", $denominationComMRH, null);
        $db->bind("denominationComPNO", $denominationComPNO, null);
        $db->bind("policeMRI", $policeMRI, null);
        $db->bind("policeMRH", $policeMRH, null);
        $db->bind("policePNO", $policePNO, null);
        $db->bind("nomAgenceMRH", $nomAgenceMRH, null);
        $db->bind("nomAgencePNO", $nomAgencePNO, null);
        $db->bind("source", $idUtilisateur, null);
        $db->bind("status", "Open", null);
        $db->bind("editDate", date("Y-m-d h:i:s"), null);
        $db->bind("createDate", date("Y-m-d h:i:s"), null);
        $db->bind("etapeOp", $type == "Sinistres" ?  $etapeOp : "Gestion des travaux", null);
        $db->bind("origine", $origine, null);
        $db->bind("nomGestionnaireAppImm", $nomGestionnaireAppImm, null);
        $db->bind("idGestionnaireAppImm", ($idGestionnaireAppImm == "0" || $idGestionnaireAppImm == 0) ? null : $idGestionnaireAppImm, null);
        $db->bind("guidGestionnaireAppImm", $numeroGestionnaireAppImm, null);
        $db->bind("idAgenceMRH", ($idAgenceMRH == "0" || $idAgenceMRH == 0) ? null : $idAgenceMRH, null);
        $db->bind("idAgencePNO", ($idAgencePNO == "0" || $idAgencePNO == 0) ? null : $idAgencePNO, null);
        $db->bind("guidComMRH", $guidComMRH, null);
        $db->bind("guidComMRI", $guidComMRI, null);
        $db->bind("guidComPNO", $guidComPNO, null);
        $db->bind("guidCouMRI", $guidCouMRI, null);
        $db->bind("guidCouMRH", $guidCouMRH, null);
        $db->bind("guidCouPNO", $guidCouPNO, null);
        $db->bind("numGestionnaire", $gestionnaire, null);
        $db->bind("idCommercial", ($idCommercial == "0" || $idCommercial == 0 || $idCommercial == null || $idCommercial == "") ? null : $idCommercial, null);
        $db->bind("guidCommercial", $numeroCommercial, null);
        $db->bind("commercial", $nomCommercial, null);
        $db->bind("commentaireSituation", $commentaireSituation, null);
        $db->bind("idReferentDo", ($idReferent == "0" || $idReferent == 0) ? null : $idReferent, null);
        $db->bind("guidReferentDo",  $numeroReferent, null);
        $db->bind("nomReferentDo",  $nomReferent, null);
        $db->bind("typeIntervention",  $intervention, null);
        $db->bind("causeMission",  $intervention, null);
        $db->bind("apporteur",  $nomApporteur, null);
        $db->bind("guidApporteur",  $numeroApporteur, null);
        $db->bind("idApporteur", ($idApporteur == "0" || $idApporteur == 0) ? null : $idApporteur, null);
        $db->bind("idAuteurCreation", $idUtilisateur, null);
        if ($db->execute()) {
            $insert = true;
            if ($type == "A.M.O.2") {
                updateParametre("numeroOPamo", ($param->numeroOPamo + 3));
            } else {
                updateParametre("numeroOp", ($param->numeroOP + 1));
            }

            $opport = findItemByColumn("wbcc_opportunity", "name", $name);

            $db->query("INSERT INTO `wbcc_contact_opportunity`(`idContactF`, `idOpportunityF`) VALUES ('6740',$opport->idOpportunity)");
            $db->execute();

            $db->query("INSERT INTO `wbcc_opportunity_immeuble`(`idOpportunityF`, `idImmeubleF`) VALUES ($opport->idOpportunity,$idImmeuble)");
            $db->execute();



            if ($idContactClient != "0" && $idContactClient != null && $idContactClient != "") {

                if ($idApp != "0" && $idApp != null && $idApp != "") {
                    $db->query("INSERT INTO `wbcc_opportunity_appartement`(`idOpportunityF`, `idAppartementF`) VALUES ($opport->idOpportunity,$idApp)");
                    $db->execute();

                    $db->query("SELECT * FROM wbcc_appartement_contact WHERE idAppartementF = $idApp and idContactF = $idContactClient LIMIT 1");
                    $find = $db->single();
                    if (!$find) {
                        $db->query("INSERT INTO wbcc_appartement_contact (idContactF, idAppartementF) VALUES($idContactClient, $idApp)");
                        $db->execute();
                    }
                }
                $db->query("SELECT * FROM `wbcc_contact_opportunity` WHERE `idContactF` = '$idContactClient' AND `idOpportunityF`= '$opport->idOpportunity'");
                $element = $db->single();
                if (!$element) {
                    $db->query("INSERT INTO `wbcc_contact_opportunity`(`idContactF`, `idOpportunityF`) VALUES ($idContactClient,$opport->idOpportunity)");
                    $db->execute();
                }
            }

            if ($idGestionnaire != "0" && $idGestionnaire != null && $idGestionnaire != "") {
                $db->query("SELECT * FROM `wbcc_contact_opportunity` WHERE `idContactF` = 6740 AND `idOpportunityF`= '$opport->idOpportunity'");
                $element = $db->single();
                if (!$element) {
                    $db->query("INSERT INTO `wbcc_contact_opportunity`(`idContactF`, `idOpportunityF`) VALUES (6740,$opport->idOpportunity)");
                    $db->execute();
                }
            }

            if ($idCommercial != "0" && $idCommercial != null && $idCommercial != "") {
                $db->query("SELECT * FROM `wbcc_contact_opportunity` WHERE `idContactF` = '$idCommercial' AND `idOpportunityF`= '$opport->idOpportunity'");
                $element = $db->single();
                if (!$element) {
                    $db->query("INSERT INTO `wbcc_contact_opportunity`(`idContactF`, `idOpportunityF`) VALUES ($idCommercial,$opport->idOpportunity)");
                    $db->execute();
                }
            }

            if ($idApporteur != "0" && $idApporteur != null && $idApporteur != "") {
                $db->query("SELECT * FROM `wbcc_contact_opportunity` WHERE `idContactF` = '$idApporteur' AND `idOpportunityF`= '$opport->idOpportunity'");
                $element = $db->single();
                if (!$element) {
                    $db->query("INSERT INTO `wbcc_contact_opportunity`(`idContactF`, `idOpportunityF`) VALUES ($idApporteur,$opport->idOpportunity)");
                    $db->execute();
                }
            }

            // $idReferent = "0";

            if ($idReferent != "0" && $idReferent != "" && $idReferent != null) {
                $db->query("SELECT * FROM `wbcc_contact_opportunity` WHERE `idContactF` = '$idReferent' AND `idOpportunityF`= '$opport->idOpportunity'");
                $element = $db->single();
                if (!$element) {
                    $db->query("INSERT INTO `wbcc_contact_opportunity`(`idContactF`, `idOpportunityF`) VALUES ($idReferent,$opport->idOpportunity)");
                    $db->execute();
                }
            }

            if ($idGestionnaireAppImm != null && $idGestionnaireAppImm != "0" && $idGestionnaireAppImm != "" && $idGestionnaireAppImm != null) {
                $db->query("SELECT * FROM  `wbcc_company_opportunity` WHERE `idCompanyF` = $idGestionnaireAppImm AND `idOpportunityF` =$opport->idOpportunity");
                $element = $db->single();
                if (!$element) {
                    $db->query("INSERT INTO `wbcc_company_opportunity`(`idCompanyF`, `idOpportunityF`) VALUES ($idGestionnaireAppImm,$opport->idOpportunity)");
                    $db->execute();
                }
            }

            if ($compAgence) {
                $db->query("SELECT * FROM  `wbcc_company_opportunity` WHERE `idCompanyF` = $compAgence->idCompany AND `idOpportunityF` =$opport->idOpportunity");
                $element = $db->single();
                if (!$element) {
                    $db->query("INSERT INTO `wbcc_company_opportunity`(`idCompanyF`, `idOpportunityF`) VALUES ($compAgence->idCompany,$opport->idOpportunity)");
                    $db->execute();
                }
            }

            if ($compAssurance) {
                $db->query("SELECT * FROM  `wbcc_company_opportunity` WHERE `idCompanyF` = $compAssurance->idCompany AND `idOpportunityF`=$opport->idOpportunity");
                $element = $db->single();
                if (!$element) {
                    $db->query("INSERT INTO `wbcc_company_opportunity`(`idCompanyF`, `idOpportunityF`) VALUES ($compAssurance->idCompany,$opport->idOpportunity)");
                    $db->execute();
                }
            }

            if ($compCourtier) {
                $db->query("SELECT * FROM  `wbcc_company_opportunity` WHERE `idCompanyF` = $compCourtier->idCompany AND `idOpportunityF`=$opport->idOpportunity");
                $element = $db->single();
                if (!$element) {
                    $db->query("INSERT INTO `wbcc_company_opportunity`(`idCompanyF`, `idOpportunityF`) VALUES ($compCourtier->idCompany,$opport->idOpportunity)");
                    $db->execute();
                }
            }

            if ($compDO) {
                $db->query("SELECT * FROM `wbcc_company_opportunity` WHERE `idCompanyF` = '$compDO->idCompany' AND `idOpportunityF`= '$opport->idOpportunity'");
                $element = $db->single();
                if (!$element) {
                    $db->query("INSERT INTO `wbcc_company_opportunity`(`idCompanyF`, `idOpportunityF`) VALUES ($compDO->idCompany,$opport->idOpportunity)");
                    $db->execute();
                }

                if ($idCommercial != "0" || $idCommercial != "" && $categorie != "Particuliers") {
                    $db->query("UPDATE `wbcc_company` SET nomCommercial = :nomCommercial, idCommercial = $idCommercial WHERE idCompany = $compDO->idCompany");
                    $db->bind("nomCommercial", $nomCommercial, null);
                    $db->execute();
                }

                if ($idApporteur != "0" || $idApporteur != "" && $idApporteur != "Particuliers") {
                    $db->query("UPDATE `wbcc_company` SET idApporteurDO = $idApporteur, idGuidAADO = :numeroApporteur WHERE idCompany = $compDO->idCompany");
                    $db->bind("numeroApporteur", $numeroApporteur, null);
                    $db->execute();
                }
            }

            if ($imm) {
                if ($imm->idGardien != null && $imm->idGardien != "" && $imm->idGardien != "0") {
                    $db->query("SELECT * FROM `wbcc_contact_opportunity` WHERE `idContactF` = '$imm->idGardien' AND `idOpportunityF`= '$opport->idOpportunity'");
                    $element = $db->single();
                    if (!$element) {
                        $db->query("INSERT INTO `wbcc_contact_opportunity`(`idContactF`, `idOpportunityF`) VALUES ($imm->idGardien,$opport->idOpportunity)");
                        $db->execute();
                    }
                }

                if ($imm->idChefSecteur != null && $imm->idChefSecteur != "" && $imm->idChefSecteur != "0") {
                    $db->query("SELECT * FROM `wbcc_contact_opportunity` WHERE `idContactF` = '$imm->idChefSecteur' AND `idOpportunityF`= '$opport->idOpportunity'");
                    $element = $db->single();
                    if (!$element) {
                        $db->query("INSERT INTO `wbcc_contact_opportunity`(`idContactF`, `idOpportunityF`) VALUES ($imm->idChefSecteur,$opport->idOpportunity)");
                        $db->execute();
                    }
                }
            }
        } else {
            $insert = false;
        }
        //    }
        if ($insert) {

            $today = date("Y-m-d H:i:s");

            createHistorique("Création de l'opportunité : $name",  $auteur, $idUtilisateur, $opport->idOpportunity);
            if ($type == "Sinistres") {
                // creer les activite
                createNewActivity($opport->idOpportunity, $opport->name, 518, "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", $opport->name . "-Faire signer la délégation de gestion", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", 0, 1);
                createNewActivity($opport->idOpportunity, $opport->name, 518, "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", $opport->name . "-Faire la Télé-Expertise", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", 0, 2);
                createNewActivity($opport->idOpportunity, $opport->name, 518, "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", $opport->name . "-Programmer le RT", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", 0, 3);

                //generer delegation
                $signDeleg =  "signDeleg_" . $opport->idOpportunity . "png";

                $file1 = file_get_contents(URLROOT . "/public/documents/delegations/rapportDelegation.php?idOp=$opport->idOpportunity&$varDeleg=1&modeSignature=sans");
                $file1 = str_replace('"', "", $file1);
                $db->query("UPDATE wbcc_opportunity SET genereDelegation=1, rapportDelegation=:fileD WHERE idOpportunity=$opport->idOpportunity");
                $db->bind("fileD", $file1, null);
                $db->execute();

                //SAVE DOC DELEGATION
                $numeroDocument = "DOC" . date('dmYHis') . $opport->idOpportunity . "518";
                $db->query("INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, commentaire, createDate,  source, guidHistory, typeFichier, size, guidUser, idUtilisateurF, auteur, publie) VALUES (:numeroDocument, :nomDocument, :urlDocument, :commentaire, :createDate,  :source, :guidHistory, :typeFichier, :size, :guidUser, :idUtilisateurF, :auteur, :publie)");
                $db->bind("publie", 0, null);
                $db->bind("source", 'EXTRA', null);
                $db->bind("numeroDocument", $numeroDocument, null);
                $db->bind("nomDocument", $file1, null);
                $db->bind("urlDocument", $file1, null);
                $db->bind("commentaire", "", null);
                $db->bind("createDate",  date('Y-m-d H:i:s'), null);
                $db->bind("guidHistory", "", null);
                $db->bind("typeFichier", "Adobe Acrobat Document", null);
                $db->bind("size", "", null);
                $db->bind("guidUser", "5770501a-425d-4f50-b66a-016c2dbb2557", null);
                $db->bind("idUtilisateurF", $idGestionnaire, null);
                $db->bind("auteur", $gestionnaire, null);

                if ($db->execute()) {
                    $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                    $db->query("INSERT INTO wbcc_opportunity_document ( idDocumentF, idOpportunityF) VALUES (:idDocumentF, :idOpportunityF)");
                    $db->bind("idDocumentF", $document->idDocument, null);
                    $db->bind("idOpportunityF", $opport->idOpportunity, null);
                    if ($db->execute()) {
                    }
                }

                $emailFormail = "";
                $civiliteFromMail = "";
                $nomFormail = "";
                $prenomFormail = "";

                if ($typeSinistre == "Partie commune exclusive" && $idReferent != "0") {
                    $db->query("SELECT * FROM wbcc_contact WHERE idContact = $idReferent");
                    $refDO = $db->single();
                    if ($refDO) {
                        $emailFormail = $refDO->emailContact;
                        $civiliteFromMail = $refDO->civiliteContact;
                        $nomFormail = $refDO->nomContact;
                        $prenomFormail = $refDO->prenomContact;
                    }
                } else if ($typeSinistre == "Partie privative exclusive" && $idClient != "0") {

                    $emailFormail = $emailClient;
                    $civiliteFromMail = $civiliteClient;
                    $nomFormail = $nomClient;
                    $prenomFormail = $prenomClient;
                }
                //Envoi mail Délégation
                $tabFiles[] = "/public/documents/delegations/$file1";
                $fileDelegation = "/public/documents/delegations/$file1";
                $fileDelegationName = $file1;
                $fileNames[] = $file1;
                $cc = [];

                $body = "Bonjour $civiliteFromMail $prenomFormail $nomFormail , <br><br>
                Veuillez recevoir en PJ le document '$file1' non signé <br><br><br>
                Veuillez ne pas répondre à ce message envoyé automatiquement pour votre information.<br><br><b>WBCC Assistance</b><br><b> " . $auteur . " </b>";

                $r = new Role();
                if ($emailFormail != "") {

                    //$send = $r::mailExtranetWithFiles("wbcc023@gmail.com", "SOS SINISTRE : ORIGINAL DOCUMENT NON SIGNE - ' $file1 ' ", $body, $cc, $tabFiles, $fileNames);
                    $send = $r::mailExtranetWithFiles($emailFormail, "SOS SINISTRE : ORIGINAL DOCUMENT NON SIGNE - ' $file1 ' ", $body, $cc, $tabFiles, $fileNames);
                    //echo json_encode($send);
                    if ($send) {
                        //echo json_encode("Mail sent");
                    }
                }
            } else {
                //CODE FOR AMO
                createNewActivity($opport->idOpportunity, $opport->name, 518, "Compte WBCC", "5770501a-425d-4f50-b66a-016c2dbb2557", $opport->name . "-Faire Devis", "", date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), "Tâche à faire", "False", 0, 7);
            }
            echo json_encode($opport);
        } else {
            echo json_encode("Error Opportunity Insert");
        }
    }

    /********************  DEBUT NEW ESPOIR  ***********************/
    // Modification de : saveEtatAncienneOp

    if ($action == 'saveEtatAncienneOp') {

        extract($_POST);

        if ($type == 'a.m.o.') {
            setEtatActivitiesOP($idOP, 'True', '(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,29,24,25,26,27,28,29)');
            $db->query("UPDATE wbcc_opportunity SET auditAncienneOp=1  WHERE idOpportunity = $idOP ");
            echo json_encode($db->execute());
        } elseif ($type == 'sinistres') {

            if ($etape == 1) {

                /******** SAVE Signature délégation  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $nomDocument = '';
                if (isset($_FILES['rapportDelegation'])) {

                    $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;

                    $nomDocument = $opportunity->name . '_DELEGATION_GESTION_PAIEMENT_' . $typeOp . date('dmYHis') . ".pdf";
                    $urlDocument = $opportunity->name . '_DELEGATION_GESTION_PAIEMENT_' . $typeOp . date('dmYHis') . ".pdf";

                    // 1 - suavegarder le fichier dans document OP
                    //if (!file_exists("../documents/opportunite/".$nomDocument)) {
                    move_uploaded_file($_FILES['rapportDelegation']['tmp_name'], "../documents/opportunite/" . $nomDocument);
                    //file_put_contents("../documents/opportunite/".$nomDocument, $_FILES['rapportDelegation']['tmp_name']);
                    //}

                    // 2- enregistrer la référence dans table document et retourner l'id de la ligne inséré
                    $db->query("SELECT * FROM wbcc_document d, wbcc_opportunity_document od WHERE od.idDocumentF= d.idDocument AND urlDocument='$urlDocument' AND od.idOpportunityF=$idOP LIMIT 1");
                    $oldDocument =  $db->single();

                    if ($oldDocument) {
                        $numeroDocument = $oldDocument->numeroDocument;
                        $db->query("UPDATE wbcc_document SET 
                                    nomDocument=:nomDocument, urlDocument=:urlDocument, 
                                    editDate=:editDate, auteur=:auteur, idUtilisateurF=:idUtilisateurF
                                    WHERE idDocument=:idDocument 
                                    ");
                        $db->bind("idDocument", $oldDocument->idDocument, null);
                    } else {
                        $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                        $db->query(" INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, createDate, editDate, auteur, idUtilisateurF,  source, publie) 
                                VALUES (:numeroDocument, :nomDocument, :urlDocument, :createDate, :editDate, :auteur, :idUtilisateurF, :source, :publie) ");

                        $db->bind("numeroDocument", $numeroDocument, null);
                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                        $db->bind("source", 'EXTRANET', null);
                        $db->bind("publie", 1, null);
                    }


                    $db->bind("nomDocument", $nomDocument, null);
                    $db->bind("urlDocument", $urlDocument, null);
                    $db->bind("editDate", date("Y-m-d H:i:s"), null);
                    $db->bind("auteur", $auteur, null);
                    $db->bind("idUtilisateurF", $idUtilisateur, null);
                    $db->execute();

                    // 3- enregistrer dans la table domument OP

                    $db->query("SELECT * FROM wbcc_opportunity_document WHERE numeroOpportunityF =:numeroOpportunityF AND numeroDocumentF =:numeroDocumentF");
                    $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                    $db->bind("numeroDocumentF", $numeroDocument, null);
                    if ($db->single() == false) {
                        $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                        $db->query("SELECT * FROM wbcc_opportunity_document WHERE idOpportunityF =:idOpportunity AND idDocumentF =:idDocument");
                        $db->bind("idOpportunity", $opportunity->idOpportunity, null);
                        $db->bind("idDocument", $document->idDocument, null);
                        if ($db->single() == false) {
                            $db->query("INSERT INTO wbcc_opportunity_document (numeroOpportunityF, numeroDocumentF, idDocumentF, idOpportunityF) VALUES (:numeroOpportunityF, :numeroDocumentF,  :idDocumentF, :idOpportunityF)");
                            $db->bind("numeroDocumentF", $numeroDocument, null);
                            $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                            $db->bind("idDocumentF", $document->idDocument, null);
                            $db->bind("idOpportunityF", $opportunity->idOpportunity, null);
                            $db->execute();
                        }
                    }
                }

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";
                // Délégation
                if ($delegationSigne == "1") {
                    if ($delegationSigne != $opportunity->delegationSigne) {
                        createNote($idOP, $idUtilisateur, $auteur, "Signature de la délégation de gestion fait ", "Signature de la délégation de gestion fait", 1);
                    }
                    $req = ", delegationSigne=1, dateSignatureDelegation='$dateSignatureDelegation', idAuteurSignatureDelegation =$idAuteurSignatureDelegation, signature='$signature' ";
                    manageActivity($idOP, 1, 'True', $opName, "Faire signer la délégation de gestion ");
                    $etapeAuditeAncienneOpWew = "1";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != 1 && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($delegationSigne == "0") {
                    if ($delegationSigne != $opportunity->delegationSigne) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire signer la délégation de gestion ", "Faire signer la délégation de gestion ", 1);
                    }
                    $req = ", delegationSigne=0, dateSignatureDelegation=NULL, idAuteurSignatureDelegation =NULL";
                    manageActivity($idOP, 1, 'False', $opName, "Faire signer la délégation de gestion ");
                    $etapeAuditeAncienneOpWew = "1";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != 1 && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else if ($delegationSigne == "2") {
                    if ($delegationSigne != $opportunity->delegationSigne) {
                        createNote($idOP, $idUtilisateur, $auteur, "La signature de délégation de gestion n'est pas applicable pour ce dossier ", "La signature de délégation de gestion n'est pas applicable pour ce dossier ", 1);
                    }
                    $req = ", delegationSigne=$delegationSigne, dateSignatureDelegation=NULL, idAuteurSignatureDelegation =NULL";
                    manageActivity($idOP, 1, 'True', $opName, "La signature de délégation de gestion n'est pas applicable pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "1";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != 1 && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", delegationSigne=NULL, dateSignatureDelegation=NULL, idAuteurSignatureDelegation =NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(1)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != 1 && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }


                saveCommentaireAsNote($idOP, $numeroOpportunity, 1, $auteur, $idUtilisateur, $delegationSigneCom);

                if ($delegationSigne == "1") {

                    /***** Enregistrement de police **************/
                    $guidComp = $typeOp == "MRI" ? "guidComMRI" : "guidComMRH";
                    $refPolice = $typeOp == "MRI" ? "policeMRI" : "policeMRH";
                    $db->query("UPDATE `wbcc_opportunity` SET $refPolice = '$numPolice' WHERE idOpportunity = $idOP");
                    $db->execute();


                    /************** Mise à jour rapport délégation *********/
                    if ($nomDocument != '') {
                        $db->query("UPDATE `wbcc_opportunity` SET rapportDelegation = '$nomDocument' WHERE idOpportunity = $idOP");
                        $db->execute();
                    }

                    /******** fin SAVE Signature délégation  *********/

                    /******** Date contrat  *********/
                    if ($opportunity->typeSinistre == "Partie commune exclusive") {
                        $db->query("UPDATE `wbcc_immeuble` SET dateEffetContrat = '$dateEffetContrat', dateEcheanceContrat='$dateEcheanceContrat' WHERE idImmeuble = $idImmeuble ");
                        $db->execute();
                    } else {
                        $db->query("UPDATE `wbcc_appartement` SET dateEffetOccupant = '$dateEffetContrat', dateEcheanceOccupant='$dateEcheanceContrat' WHERE idApp = $idApp ");
                        $db->execute();
                    }

                    /******** Date naissance  *********/
                    if ($dateNaissance != "") {
                        $db->query("UPDATE wbcc_contact SET dateNaissance = '$dateNaissance' WHERE idContact = $idContact ");
                        $db->execute();
                    }
                }

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());
            } elseif ($etape == 2) {
                $req = "";
                $today = date("Y-m-d H:i:s");
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";
                // Délégation
                if ($teleExpertiseFaite == "1") {
                    if ($teleExpertiseFaite != $opportunity->teleExpertiseFaite) {
                        createNote($idOP, $idUtilisateur, $auteur, " Télé-Expertise fait ", " Télé-Expertise fait", 1);
                    }
                    $req = ", teleExpertiseFaite=1, dateTeleExpertise='$dateTeleExpertise', idAuteurTeleExpertise =$idAuteurTeleExpertise";
                    manageActivity($idOP, 2, 'True', $opName, " Faire la Télé-Expertise ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($teleExpertiseFaite == "0") {
                    if ($teleExpertiseFaite != $opportunity->teleExpertiseFaite) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire la Télé-Expertise ", "Faire la Télé-Expertise ", 1);
                    }
                    $req = ", teleExpertiseFaite=0, dateTeleExpertise=NULL, idAuteurTeleExpertise =NULL";
                    manageActivity($idOP, 2, 'False', $opName, "Faire la Télé-Expertise ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($teleExpertiseFaite == "2") {
                    if ($teleExpertiseFaite != $opportunity->teleExpertiseFaite) {
                        createNote($idOP, $idUtilisateur, $auteur, "La télé-expertise n'est pas nécéssaire pour ce dossier ", "La télé-expertise n'est pas nécéssaire pour ce dossier ", 1);
                    }
                    $req = ", teleExpertiseFaite=$teleExpertiseFaite, dateTeleExpertise=NULL, idAuteurTeleExpertise =NULL";
                    manageActivity($idOP, 2, 'True', $opName, "La télé-expertise n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = " teleExpertiseFaite=NULL, dateTeleExpertise=NULL, idAuteurTeleExpertise =NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(2)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew', type='Sinistres' $req WHERE idOpportunity = $idOP");
                $db->execute();

                saveCommentaireAsNote($idOP, $numeroOpportunity, 2, $auteur, $idUtilisateur, $teleExpertiseFaiteCom);

                if ($teleExpertiseFaite == "1") {
                    if ($prkwConnaitPasOrigine != '') {
                        createNote($idOP, $idUtilisateur, $auteur, "Ne sait pas origine sinistre: $prkwConnaitPasOrigine", "Ne sait pas origine sinistre: $prkwConnaitPasOrigine", 1);
                    }

                    if ($prkwOrigineIndeterminee != '') {
                        createNote($idOP, $idUtilisateur, $auteur, "Origine sinistre indéterminée: $prkwOrigineIndeterminee", "Origine sinistre indéterminée: $prkwOrigineIndeterminee", 1);
                    }

                    $numeroRT = "RT_" . date("dmYhis") . $idOP;
                    //Update OR insert  Releve Technique
                    $RTExist = false;
                    if ($idRT != "null") {
                        //echo json_encode('exec 1'); die();
                        $db->query(" UPDATE wbcc_releve_technique SET date=:date, heure=:heure,
                                    numeroBatiment=:numeroBatiment,
                                    adresse=:adresse,ville=:ville,codePostal=:codePostal,
                                    cause=:cause,precisionComplementaire=:precisionComplementaire,
                                    idOpportunityF=:idOpp, numeroOP=:numeroOP,
                                    editDate=:edit,reparateurDegat=:reparateurDegat,equipementMVC=:equipementMVC,fonctionnementMVC=:fonctionnementMVC,
                                    dataAppMoisissure=:dataAppMoisissure,appMoisissureHiver=:appMoisissureHiver, nature=:nature
                                    WHERE idRT=:idRT ");
                        $db->bind("idRT", $idRT, null);
                    } else {

                        $RTExist = findItemByColumn("wbcc_releve_technique", "idOpportunityF", $idOP);

                        if ($RTExist) {
                            //echo json_encode('exec 2'); die();
                            $idRT = $RTExist->idRT;
                            $db->query("UPDATE wbcc_releve_technique SET date=:date, heure=:heure,
                                    numeroBatiment=:numeroBatiment,
                                    adresse=:adresse,ville=:ville,codePostal=:codePostal,
                                    cause=:cause,precisionComplementaire=:precisionComplementaire,
                                    idOpportunityF=:idOpp, numeroOP=:numeroOP,
                                    editDate=:edit,reparateurDegat=:reparateurDegat,equipementMVC=:equipementMVC,fonctionnementMVC=:fonctionnementMVC,
                                    dataAppMoisissure=:dataAppMoisissure,appMoisissureHiver=:appMoisissureHiver, nature=:nature
                                    WHERE idRT=:idRT ");
                            $db->bind("idRT", $RTExist->idRT, null);
                        } else {
                            //echo json_encode('exec 3'); die();
                            $db->query("INSERT INTO wbcc_releve_technique 
                                        (date,heure,numeroBatiment,adresse,ville,codePostal,
                                        cause,precisionComplementaire,idOpportunityF,numeroRT,numeroOP,
                                        createDate,editDate,reparateurDegat,equipementMVC,fonctionnementMVC,
                                        dataAppMoisissure,appMoisissureHiver,nature) 
                                        VALUES (:date,:heure,:numeroBatiment,:adresse,:ville,:codePostal,
                                                :cause,:precisionComplementaire,:idOpp,:numeroRT,:numeroOP,
                                                :create,:edit,:reparateurDegat, :equipementMVC,:fonctionnementMVC,
                                                :dataAppMoisissure,:appMoisissureHiver,:nature )");
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

                    $db->bind("edit", date("d-m-Y H:i"), null);
                    $db->bind("date", $dateSinistre, null);
                    $db->bind("heure", isset(explode(' ', $dateSinistre)[1]) ? explode(' ', $dateSinistre)[1] : '', null);
                    $db->bind("numeroBatiment", $batiment, null);
                    $db->bind("adresse", $adresse, null);
                    $db->bind("ville", $ville, null);
                    $db->bind("codePostal", $codePostal, null);
                    $db->bind("cause", $causeCoche, null);
                    $db->bind("precisionComplementaire", $precisionComp, null);
                    $db->bind("idOpp", $idOP, null);
                    $db->bind("numeroOP", $numeroOP, null);
                    $db->bind("reparateurDegat", $reparateurDegat, null);
                    $db->bind("equipementMVC", $equipementMVC, null);
                    $db->bind("appMoisissureHiver", $appMoisissureHiver, null);
                    $db->bind("dataAppMoisissure", $dataAppMoisissure, null);
                    $db->bind("fonctionnementMVC", $fonctionnementMVC, null);
                    $db->bind("nature", $natureSinitre, null);

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
                } else {
                    echo json_encode('ok');
                }
            } elseif ($etape == 3) {
                $req = "";
                $today = date("Y-m-d H:i:s");
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                $db->query("SELECT * FROM wbcc_rendez_vous WHERE typeRV='RTP' AND idOpportunityF=$idOP  LIMIT 1");
                $rv = $db->single();

                if ($priseRvRT == "1") {
                    if ($priseRvRT != $opportunity->priseRvRT) {
                        createNote($idOP, $idUtilisateur, $auteur, " RV RT pris ", " RV RT pris", 1);
                    }
                    $req = ", priseRvRT=1, datePriseRvRT='$datePriseRvRT', idAuteurPriseRvRT =$idAuteurPriseRvRT";
                    manageActivity($idOP, 3, 'True', $opName, " Prendre RV RT ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($priseRvRT == "0") {
                    if ($priseRvRT != $opportunity->priseRvRT) {
                        createNote($idOP, $idUtilisateur, $auteur, "Prendre RV RT ", "Prendre RV RT ", 1);
                    }
                    $req = ", priseRvRT=0, datePriseRvRT=NULL, idAuteurPriseRvRT =NULL";
                    manageActivity($idOP, 3, 'False', $opName, "Prendre RV RT ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($priseRvRT == "2") {
                    if ($priseRvRT != $opportunity->priseRvRT) {
                        createNote($idOP, $idUtilisateur, $auteur, "Prise de RDV RT n'est pas nécéssaire pour ce dossier ", "Prise de RDV RT n'est pas nécéssaire pour ce dossier ", 1);
                    }
                    $req = ", priseRvRT=$priseRvRT, datePriseRvRT=NULL, idAuteurPriseRvRT =NULL";
                    manageActivity($idOP, 3, 'True', $opName, "Prise de RDV RT n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = " priseRvRT=NULL, datePriseRvRT=NULL, idAuteurPriseRvRT =NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(3)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 3, $auteur, $idUtilisateur, $priseRvRTCom);

                if ($priseRvRT == "1") {
                    $ok = "0";
                    $today = date('Y-m-d H:i:s');
                    setlocale(LC_ALL, "fr_FR");

                    $dateRV = str_replace(" ", "", $dateRV);
                    if (str_contains($heureDebut, "-")) {
                        $heureFin = explode("-", $heureDebut)[1];
                        $heureDebut = explode("-", $heureDebut)[0];
                    }

                    if (str_contains($heureDebut, "h")) {
                        $dateRV =  date('Y-m-d');
                        $heureDebut =  date('H:i');
                    }
                    $heureDebut = str_replace(' ', '', $heureDebut);

                    if (substr($dateRV, 4, 1) == "-") {
                    } else {
                        $dateRV = substr($dateRV, 6, 4) . "-"   . substr($dateRV, 3, 2) . "-" . substr($dateRV, 0, 2);
                    }

                    $date = new DateTime($dateRV . ' ' . $heureDebut);
                    if (isset($dateRVFin)) {
                        $dateFin = new DateTime($dateRVFin . ' ' . $heureFin);
                    } else {
                        $dateFin = new DateTime($dateRV . ' ' . $heureDebut);
                        $heureFin = isset($heureFin) ? $heureFin : ($dateFin->modify("+30 minutes"))->format('H:i');
                        $dateFin = new DateTime($dateRV . ' ' . $heureFin);
                    }

                    //TEST IF EXIST IN AGENDA EXPERT
                    $seeInAgenda = isset($seeInAgenda) && $seeInAgenda == "1" ? false : true;
                    $event =  isset($seeInAgenda) && $seeInAgenda == "1" ? "0" : "1";
                    $event =   "0";

                    $db->query("SELECT * FROM wbcc_utilisateur u, wbcc_contact c WHERE u.idContactF= c.idContact AND u.idUtilisateur=$idExpertRvRT LIMIT 1");
                    $tehExpert =  $db->single();
                    $expert = $tehExpert ? $tehExpert->prenomContact . ' ' . $tehExpert->nomContact : '';
                    $idExpert = $idExpertRvRT;

                    if ($event == "1") {
                        echo json_encode("2");
                        die;
                    } else {
                        //FIND IF RV
                        $rv = false;
                        if ($idOP != "") {
                            $db->query("SELECT * FROM wbcc_rendez_vous WHERE typeRV='$typeRV' AND idOpportunityF=$idOP  LIMIT 1");
                            $rv = $db->single();
                        }
                        if ($rv) {
                            $db->query("UPDATE wbcc_rendez_vous SET dateRV=:dateRV,heureDebut=:heureDebut, dateFin=:dateFin, heureFin=:heureFin,idAppExtra=:idAppExtra,idAppGuid=:idAppGuid,expert=:expert,idExpertF=:idExpertF,numeroOP=:numeroOP,adresseRV=:adresseRV,nomDO=:nomDO,idRVGuid=:idRVGuid, moyenTechnique=:moyenTechnique, conclusion=:conclusion, idAppConF=:idAppConF, idCampagneF=:idCampagneF, idContactGuidF=:idContactGuidF,idContactF=:idContactF, typeRV=:typeRV, editDate=:editDate WHERE idRV=$rv->idRV");
                        } else {
                            $numeroRV = "RV" . date('dmYHis') . $idOP . $idUtilisateur;
                            $db->query("INSERT INTO wbcc_rendez_vous(numero,dateRV,heureDebut,dateFin,heureFin,idAppExtra,idAppGuid, expert, idExpertF, numeroOP,adresseRV,nomDO,idRVGuid, moyenTechnique, conclusion, idAppConF, idCampagneF,idContactGuidF, idContactF, idOpportunityF, typeRV,createDate, editDate, auteur,idAuteur ) VALUES (:numero,:dateRV,:heureDebut, :dateFin, :heureFin,:idAppExtra,:idAppGuid, :expert, :idExpertF, :numeroOP,:adresseRV,:nomDO,:idRVGuid, :moyenTechnique, :conclusion, :idAppConF, :idCampagneF,:idContactGuidF, :idContactF, :idOpportunityF, :typeRV,:createDate, :editDate, :auteur,:idAuteur)");
                            $db->bind("numero", $numeroRV, null);
                            $db->bind("createDate", $today, null);
                            $db->bind("auteur", $auteur, null);
                            $db->bind("idAuteur", $idUtilisateur, null);
                            $db->bind("idOpportunityF", ($idOP == "") ? null : $idOP, null);
                        }

                        $db->bind("dateRV",  $date->format('Y-m-d'), null);
                        $db->bind("heureDebut", $heureDebut, null);
                        $db->bind("dateFin", $date->format('Y-m-d'), null);
                        $db->bind("heureFin", $heureFin, null);
                        $db->bind("idAppExtra", ($idAppExtra == "") ? null : $idAppExtra, null);
                        $db->bind("idAppGuid", $idAppGuid, null);
                        $db->bind("expert", $expert, null);
                        $db->bind("idExpertF", $idExpert, null);
                        $db->bind("numeroOP", $opName, null);
                        $db->bind("adresseRV", $adresseRV, null);
                        $db->bind("nomDO", $nomDO, null);
                        $db->bind("idRVGuid", $idRVGuid, null);
                        $db->bind("moyenTechnique", $moyenTechnique, null);
                        $db->bind("conclusion", $conclusion, null);
                        $db->bind("idAppConF", ($idAppConF == "" || $idAppConF == "0") ? null : $idAppConF, null);
                        $db->bind("idCampagneF", ($idCampagneF == "") ? null : $idCampagneF, null);
                        $db->bind("idContactGuidF", $idContactGuidF, null);
                        $db->bind("idContactF", $idContact, null);
                        $db->bind("typeRV", $typeRV, null);
                        $db->bind("editDate", $today, null);
                        if ($db->execute()) {
                            if ($rv) {
                                $idRV = $rv->idRV;
                            } else {
                                $idRV =  findItemByColumn("wbcc_rendez_vous", "numero", $numeroRV)->idRV;
                            }
                            $ok = "1";
                        } else {
                            $ok = "0";
                        }
                        if ($ok == "1") {
                            if (isset($_GET['sourceEnreg'])) {

                                $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$idExpert LIMIT 1");
                                $exp = $db->single();
                                //SEND MAIL CONFIRMATION RV
                                $con = findItemByColumn("wbcc_contact", "idContact", $idContact);

                                if ($con) {

                                    //SAVE EVENEMENT RV ON AGENDA
                                    $typeRV = $typeRV == "RTP" ? "RT" : ($typeRV == "RFP" ? "RF" : "TRAVAUX");
                                    $db->query("SELECT * FROM wbcc_evenement_agenda WHERE type='$typeRV' AND idOpportunityF=$idOP  LIMIT 1");
                                    $event = $db->single();
                                    if ($event) {
                                        $db->query("UPDATE wbcc_evenement_agenda SET  organisateur =:organisateur , idOrganisateur=:idOrganisateur, contact=:contact, lieu=:lieu, typeEvenement=:typeEvenement, dateEvenement=:dateEvenement, heureDebutEvenement=:heureDebutEvenement, dateFinEvenement=:dateFinEvenement, heureFinEvenement=:heureFinEvenement, editDate=:editDate, auteur=:auteur, idAuteur=:idAuteur WHERE idEvenementAgenda=:idEvenementAgenda");
                                        $db->bind("idEvenementAgenda", $event->idEvenementAgenda, null);
                                    } else {
                                        $numeroEvent = "EVENT" . date('dmYHis') . $idUtilisateur;
                                        $db->query("INSERT INTO wbcc_evenement_agenda( numeroEvenementAgenda, organisateur, idOrganisateur, contact, lieu, typeEvenement, dateEvenement, heureDebutEvenement, dateFinEvenement, heureFinEvenement, createDate, editDate, auteur, idAuteur, idOpportunityF, type) VALUES (:numeroEvenementAgenda, :organisateur, :idOrganisateur, :contact, :lieu, :typeEvenement, :dateEvenement, :heureDebutEvenement, :dateFinEvenement, :heureFinEvenement, :createDate, :editDate, :auteur, :idAuteur, :idOpportunityF, '$typeRV')");
                                        $db->bind("numeroEvenementAgenda", $numeroEvent, null);
                                        $db->bind("createDate", $today, null);
                                        $db->bind("idOpportunityF", $idOP, null);
                                    }
                                    $db->bind("organisateur", $expert, null);
                                    $db->bind("idOrganisateur", $idExpert, null);
                                    $db->bind("contact", "$con->prenomContact $con->nomContact", null);
                                    $db->bind("lieu", $adresseRV, null);
                                    $db->bind("typeEvenement", "Rendez-Vous", null);
                                    $db->bind("dateEvenement", $date->format('Y-m-d'), null);
                                    $db->bind("heureDebutEvenement", $heureDebut, null);
                                    $db->bind("dateFinEvenement", $dateFin->format('Y-m-d'), null);
                                    $db->bind("heureFinEvenement", $heureFin, null);
                                    $db->bind("editDate", $today, null);
                                    $db->bind("auteur", $auteur, null);
                                    $db->bind("idAuteur", $idUtilisateur, null);
                                    $db->execute();
                                }
                            }
                            $ok = "1";
                        } else {
                            $ok = "0";
                        }
                    }
                }

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew', type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());
            } elseif ($etape == 4) {
                $req = "";
                $today = date("Y-m-d H:i:s");
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                if ($declarationCie == "1") {

                    $dateDeclarationCie = $dateDeclarationCie . ' ' . $heureDeclarationCie;
                    if ($declarationCie != $opportunity->declarationCie) {
                        createNote($idOP, $idUtilisateur, $auteur, " Déclaration compagnie fait ", " Déclaration compagnie fait", 1);
                    }
                    $req = ", declarationCie=1, dateDeclarationCie='$dateDeclarationCie', idAuteurDeclarationCie=$idAuteurDeclarationCie";
                    manageActivity($idOP, 4, 'True', $opName, " Faire la déclaration compagnie ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($declarationCie == "0") {
                    if ($declarationCie != $opportunity->declarationCie) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire la déclaration compagnie ", "Faire la déclaration compagnie ", 1);
                    }
                    $req = ", declarationCie=0, dateDeclarationCie=NULL, idAuteurDeclarationCie=NULL";
                    manageActivity($idOP, 4, 'False', $opName, "Faire la déclaration compagnie ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($declarationCie == "2") {
                    if ($declarationCie != $opportunity->declarationCie) {
                        createNote($idOP, $idUtilisateur, $auteur, "La déclaration compagnie n'est pas nécéssaire pour ce dossier ", "La déclaration compagnie n'est pas nécéssaire pour ce dossier ", 1);
                    }
                    $req = ", declarationCie=$declarationCie, dateDeclarationCie=NULL, idAuteurDeclarationCie=NULL";
                    manageActivity($idOP, 4, 'True', $opName, "La déclaration compagnie n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = " declarationCie=NULL, dateDeclarationCie=NULL, idAuteurDeclarationCie=NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(4)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 4, $auteur, $idUtilisateur, $declarationCieCom);

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew', type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());
            } elseif ($etape == 5) {
                $req = "";
                $today = date("Y-m-d H:i:s");
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                if ($relanceCieNumSinistre == "1") {
                    if ($relanceCieNumSinistre != $opportunity->relanceCieNumSinistre) {
                        createNote($idOP, $idUtilisateur, $auteur, " Relance Cie pour numéro sinistre fait ", " Relance Cie pour numéro sinistre fait", 1);
                    }
                    $req = ", relanceCieNumSinistre=1, dateRelanceCieNumSinistre='$dateRelanceCieNumSinistre', idAuteurRelanceCieNumSinistre=$idAuteurRelanceCieNumSinistre ";
                    if ($opportunity->typeSinistre == "Partie commune exclusive") {
                        $req .= " , sinistreMRI= '$numeroSinistre' ";
                    } else {
                        $req .= " , sinistreMRH= '$numeroSinistre' ";
                    }
                    manageActivity($idOP, 5, 'True', $opName, " Faire relance cie pour numéro sinistre ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($relanceCieNumSinistre == "0") {
                    if ($relanceCieNumSinistre != $opportunity->relanceCieNumSinistre) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire relance cie pour numéro sinistre ", "Faire relance cie pour numéro sinistre ", 1);
                    }
                    $req = ", relanceCieNumSinistre=0, dateRelanceCieNumSinistre=NULL, idAuteurRelanceCieNumSinistre=NULL";
                    manageActivity($idOP, 5, 'False', $opName, "Faire relance cie pour numéro sinistre ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($relanceCieNumSinistre == "2") {
                    if ($relanceCieNumSinistre != $opportunity->relanceCieNumSinistre) {
                        createNote($idOP, $idUtilisateur, $auteur, "La relance cie pour numéro de sinistre n'est pas nécéssaire ", "La relance cie pour numéro de sinistre n'est pas nécéssaire ", 1);
                    }
                    $req = ", relanceCieNumSinistre=$relanceCieNumSinistre, dateRelanceCieNumSinistre=NULL, idAuteurRelanceCieNumSinistre=NULL";
                    manageActivity($idOP, 5, 'True', $opName, "La relance cie pour numéro de sinistre n'est pas nécéssaire ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", relanceCieNumSinistre=NULL, dateRelanceCieNumSinistre=NULL, idAuteurRelanceCieNumSinistre=NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(5)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 5, $auteur, $idUtilisateur, $relanceCieNumSinistreCom);

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew', type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());
            } elseif ($etape == 6) {

                /******** SAVE Signature délégation  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $nomDocument = '';
                if (isset($_FILES['rapportFRT'])) {

                    $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                    $nomDocument = $opportunity->name . '_rapportFRT.pdf';
                    $urlDocument = $opportunity->name . '_rapportFRT.pdf';

                    // 1 - suavegarder le fichier dans document OP
                    //if (!file_exists("../documents/opportunite/".$nomDocument)) {
                    move_uploaded_file($_FILES['rapportFRT']['tmp_name'], "../documents/opportunite/" . $nomDocument);
                    //}

                    // 2- enregistrer la référence dans table document et retourner l'id de la ligne inséré
                    $db->query("SELECT * FROM wbcc_document d, wbcc_opportunity_document od WHERE od.idDocumentF= d.idDocument AND urlDocument='$urlDocument' AND od.idOpportunityF=$idOP LIMIT 1");
                    $oldDocument =  $db->single();

                    if ($oldDocument) {
                        $numeroDocument = $oldDocument->numeroDocument;
                        $db->query("UPDATE wbcc_document SET 
                                    nomDocument=:nomDocument, urlDocument=:urlDocument, 
                                    editDate=:editDate, auteur=:auteur, idUtilisateurF=:idUtilisateurF
                                    WHERE idDocument=:idDocument 
                                    ");
                        $db->bind("idDocument", $oldDocument->idDocument, null);
                    } else {
                        $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                        $db->query(" INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, createDate, editDate, auteur, idUtilisateurF,  source, publie) 
                                VALUES (:numeroDocument, :nomDocument, :urlDocument, :createDate, :editDate, :auteur, :idUtilisateurF, :source, :publie) ");

                        $db->bind("numeroDocument", $numeroDocument, null);
                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                        $db->bind("source", 'EXTRANET', null);
                        $db->bind("publie", 1, null);
                    }


                    $db->bind("nomDocument", $nomDocument, null);
                    $db->bind("urlDocument", $urlDocument, null);
                    $db->bind("editDate", date("Y-m-d H:i:s"), null);
                    $db->bind("auteur", $auteur, null);
                    $db->bind("idUtilisateurF", $idUtilisateur, null);
                    $db->execute();

                    // 3- enregistrer dans la table domument OP

                    $db->query("SELECT * FROM wbcc_opportunity_document WHERE numeroOpportunityF =:numeroOpportunityF AND numeroDocumentF =:numeroDocumentF");
                    $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                    $db->bind("numeroDocumentF", $numeroDocument, null);
                    if ($db->single() == false) {
                        $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                        $db->query("SELECT * FROM wbcc_opportunity_document WHERE idOpportunityF =:idOpportunity AND idDocumentF =:idDocument");
                        $db->bind("idOpportunity", $opportunity->idOpportunity, null);
                        $db->bind("idDocument", $document->idDocument, null);
                        if ($db->single() == false) {
                            $db->query("INSERT INTO wbcc_opportunity_document (numeroOpportunityF, numeroDocumentF, idDocumentF, idOpportunityF) VALUES (:numeroOpportunityF, :numeroDocumentF,  :idDocumentF, :idOpportunityF)");
                            $db->bind("numeroDocumentF", $numeroDocument, null);
                            $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                            $db->bind("idDocumentF", $document->idDocument, null);
                            $db->bind("idOpportunityF", $opportunity->idOpportunity, null);
                            $db->execute();
                        }
                    }
                }

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";
                // Délégation
                if ($frtFait == "1") {
                    if ($frtFait != $opportunity->frtFait) {
                        createNote($idOP, $idUtilisateur, $auteur, "FRT fait ", "FRT fait", 1);
                    }
                    $req = ", frtFait=1, dateFrt='$dateFrt', idAuteurFrt =$idAuteurFrt";
                    manageActivity($idOP, 6, 'True', $opName, "Faire FRT ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($frtFait == "0") {
                    if ($frtFait != $opportunity->frtFait) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire FRT ", "Faire FRT ", 1);
                    }
                    $req = ", frtFait=0, dateFrt=NULL, idAuteurFrt =NULL";
                    manageActivity($idOP, 6, 'False', $opName, "Faire FRT ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($frtFait == "2") {
                    if ($frtFait != $opportunity->frtFait) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire FRT n'est pas nécéssaire pour ce dossier ", "Faire FRT n'est pas nécéssaire pour ce dossier ", 1);
                    }
                    $req = ", frtFait=$frtFait, dateFrt=NULL, idAuteurFrt =NULL";
                    manageActivity($idOP, 6, 'True', $opName, "Faire FRT n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", frtFait=NULL, dateFrt=NULL, idAuteurFrt =NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(6)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 6, $auteur, $idUtilisateur, $frtFaitCom);

                if ($frtFait == "1") {
                    /************** Mise à jour rapport FRT *********/
                    if ($nomDocument != '') {
                        $db->query("UPDATE `wbcc_opportunity` SET rapportFRT = '$nomDocument' WHERE idOpportunity = $idOP");
                        $db->execute();
                    }

                    /******** fin SAVE Signature délégation  *********/
                }

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());
            } elseif ($etape == 7) {

                /******** SAVE Signature délégation  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";
                // Délégation
                if ($controleFRT == "1") {
                    if ($controleFRT != $opportunity->controleFRT) {
                        createNote($idOP, $idUtilisateur, $auteur, "Contrôle FRT fait ", "Contrôle FRT fait", 1);
                    }
                    $req = ", controleFRT=1, dateControleFRT='$dateControleFRT', idAuteurControleFRT =$idAuteurControleFRT";
                    manageActivity($idOP, 11, 'True', $opName, "Faire contrôle FRT ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($controleFRT == "0") {
                    if ($controleFRT != $opportunity->controleFRT) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire contrôle FRT ", "Faire contrôle FRT ", 1);
                    }
                    $req = ", controleFRT=0, dateControleFRT=NULL, idAuteurControleFRT =NULL";
                    manageActivity($idOP, 11, 'False', $opName, "Faire contrôle FRT ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($controleFRT == "2") {
                    if ($controleFRT != $opportunity->controleFRT) {
                        createNote($idOP, $idUtilisateur, $auteur, "Contrôle FRT n'est pas nécéssaire pour ce dossier", "Contrôle FRT n'est pas nécéssaire pour ce dossier", 1);
                    }
                    $req = ", controleFRT=$controleFRT, dateControleFRT=NULL, idAuteurControleFRT =NULL";
                    manageActivity($idOP, 11, 'True', $opName, "Contrôle FRT n'est pas nécéssaire pour ce dossier");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", controleFRT=NULL, dateControleFRT=NULL, idAuteurControleFRT =NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(11)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 11, $auteur, $idUtilisateur, $controleFRTCom);

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew', type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());

                /******** fin SAVE Contrôle FRT  *********/
            } elseif ($etape == 8) {

                /******** SAVE constat DDE  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $nomDocument = '';
                if (isset($_FILES['documentConstatDDE'])) {

                    $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;

                    $nomDocument = "$opName" . "_CONSTAT_DDE_" . date('dmYHis') . ".pdf";
                    $urlDocument = "$opName" . "_CONSTAT_DDE_" . date('dmYHis') . ".pdf";

                    // 1 - suavegarder le fichier dans document OP
                    //if (!file_exists("../documents/opportunite/".$nomDocument)) {
                    move_uploaded_file($_FILES['documentConstatDDE']['tmp_name'], "../documents/opportunite/" . $nomDocument);
                    //}

                    // 2- enregistrer la référence dans table document et retourner l'id de la ligne inséré
                    $db->query("SELECT * FROM wbcc_document d, wbcc_opportunity_document od WHERE od.idDocumentF= d.idDocument AND urlDocument='$urlDocument' AND od.idOpportunityF=$idOP LIMIT 1");
                    $oldDocument =  $db->single();

                    if ($oldDocument) {
                        $numeroDocument = $oldDocument->numeroDocument;
                        $db->query("UPDATE wbcc_document SET 
                                    nomDocument=:nomDocument, urlDocument=:urlDocument, 
                                    editDate=:editDate, auteur=:auteur, idUtilisateurF=:idUtilisateurF
                                    WHERE idDocument=:idDocument 
                                    ");
                        $db->bind("idDocument", $oldDocument->idDocument, null);
                    } else {
                        $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                        $db->query(" INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, createDate, editDate, auteur, idUtilisateurF,  source, publie) 
                                VALUES (:numeroDocument, :nomDocument, :urlDocument, :createDate, :editDate, :auteur, :idUtilisateurF, :source, :publie) ");

                        $db->bind("numeroDocument", $numeroDocument, null);
                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                        $db->bind("source", 'EXTRANET', null);
                        $db->bind("publie", 1, null);
                    }
                    $db->bind("nomDocument", $nomDocument, null);
                    $db->bind("urlDocument", $urlDocument, null);
                    $db->bind("editDate", date("Y-m-d H:i:s"), null);
                    $db->bind("auteur", $auteur, null);
                    $db->bind("idUtilisateurF", $idUtilisateur, null);
                    $db->execute();

                    // 3- enregistrer dans la table domument OP

                    $db->query("SELECT * FROM wbcc_opportunity_document WHERE numeroOpportunityF =:numeroOpportunityF AND numeroDocumentF =:numeroDocumentF");
                    $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                    $db->bind("numeroDocumentF", $numeroDocument, null);
                    if ($db->single() == false) {
                        $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                        $db->query("SELECT * FROM wbcc_opportunity_document WHERE idOpportunityF =:idOpportunity AND idDocumentF =:idDocument");
                        $db->bind("idOpportunity", $opportunity->idOpportunity, null);
                        $db->bind("idDocument", $document->idDocument, null);
                        if ($db->single() == false) {
                            $db->query("INSERT INTO wbcc_opportunity_document (numeroOpportunityF, numeroDocumentF, idDocumentF, idOpportunityF) VALUES (:numeroOpportunityF, :numeroDocumentF,  :idDocumentF, :idOpportunityF)");
                            $db->bind("numeroDocumentF", $numeroDocument, null);
                            $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                            $db->bind("idDocumentF", $document->idDocument, null);
                            $db->bind("idOpportunityF", $opportunity->idOpportunity, null);
                            $db->execute();
                        }
                    }
                }

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";


                // Délégation
                if ($genererConstatDDE == "1") {
                    if ($genererConstatDDE != $opportunity->genererConstatDDE) {
                        createNote($idOP, $idUtilisateur, $auteur, "Constat DDE Fait ", "Constat DDE Fait", 1);
                    }
                    $req = ", genererConstatDDE=1, dateGenererConstatDDE='$dateGenererConstatDDE', idAuteurGenererConstatDDE =$idAuteurGenererConstatDDE";
                    manageActivity($idOP, 24, 'True', $opName, "Faire constat DDE ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($genererConstatDDE == "0") {
                    if ($genererConstatDDE != $opportunity->genererConstatDDE) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire constat DDE ", "Faire constat DDE ", 1);
                    }
                    $req = ", genererConstatDDE=0, dateGenererConstatDDE=NULL, idAuteurGenererConstatDDE =NULL";
                    manageActivity($idOP, 24, 'False', $opName, "Faire constat DDE ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($genererConstatDDE == "2") {
                    if ($genererConstatDDE != $opportunity->genererConstatDDE) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire constat DDE n'est pas nécéssaire pour ce dossier ", "Faire constat DDE n'est pas nécéssaire pour ce dossier ", 1);
                    }
                    $req = ", genererConstatDDE=$genererConstatDDE, dateGenererConstatDDE=NULL, idAuteurGenererConstatDDE =NULL";
                    manageActivity($idOP, 24, 'True', $opName, "Faire constat DDE n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", genererConstatDDE=NULL, dateGenererConstatDDE=NULL, idAuteurGenererConstatDDE =NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(24)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 24, $auteur, $idUtilisateur, $genererConstatDDECom);

                if ($genererConstatDDE == "1") {
                    if ($nomDocument != '') {
                        $db->query("UPDATE wbcc_opportunity SET documentConstatDDE = '$nomDocument' WHERE idOpportunity = $idOP ");
                        $db->execute();
                    }

                    $rechercheFuite = findItemByColumn("wbcc_recherche_fuite", "idOpportunityF", $idOP);
                    if ($rechercheFuite) {
                        $db->query("UPDATE `wbcc_recherche_fuite` SET isResponsableSigned = :isResponsableSigned, isVictimSigned = :isVictimSigned 
                                    , signatureVoisin = :signatureVoisin , dateSignatureResponsable = :dateSignatureResponsable 
                                    , signatureDO = :signatureDO , dateSignatureVictime = :dateSignatureVictime
                                    WHERE idOpportunityF = $idOP");
                    } else {
                        $numero = "CON" . date('dmYHis') . $idUtilisateur;
                        $db->query("INSERT INTO wbcc_recherche_fuite(numeroRF, idOpportunityF, numeroOP,  editDate,
                                                isResponsableSigned, isVictimSigned, signatureVoisin, dateSignatureResponsable, signatureDO, dateSignatureVictime) 
                                    VALUES (:numeroRF, i:dOpportunityF, :numeroOP, :editDate, :isResponsableSigned,
                                    :isVictimSigned, :signatureVoisin, :dateSignatureResponsable, :signatureDO, :dateSignatureVictime)");
                        $db->bind("numeroRF", $numero, null);
                        $db->bind("idOpportunityF", $idOP, null);
                        $db->bind("numeroOP", $opName, null);
                        $db->bind("editDate", date('Y-m-d H:i:s'), null);
                    }
                    $db->bind("isResponsableSigned", $isResponsableSigned, null);
                    $db->bind("isVictimSigned", $isVictimSigned, null);
                    $db->bind("signatureVoisin", $signatureVoisin, null);
                    $db->bind("dateSignatureResponsable", $dateSignatureResponsable, null);
                    $db->bind("signatureDO", $signatureDO, null);
                    $db->bind("dateSignatureVictime", $dateSignatureVictime, null);
                    $db->execute();
                }

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew', type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());

                /******** fin SAVE Constat DDE  *********/
            } elseif ($etape == 9) {

                /******** SAVE constat DDE  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $nomDocument = '';
                if (isset($_FILES['rapportRechercheFuite'])) {

                    $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                    $nomDocument = "$opName" . "_RAPPORT_RECHERCHE_FUITE" . ".pdf";
                    $urlDocument = "$opName" . "_RAPPORT_RECHERCHE_FUITE" . ".pdf";

                    // 1 - suavegarder le fichier dans document OP
                    //if (!file_exists("../documents/opportunite/".$nomDocument)) {
                    move_uploaded_file($_FILES['rapportRechercheFuite']['tmp_name'], "../documents/opportunite/" . $nomDocument);
                    //}

                    // 2- enregistrer la référence dans table document et retourner l'id de la ligne inséré
                    $db->query("SELECT * FROM wbcc_document d, wbcc_opportunity_document od WHERE od.idDocumentF= d.idDocument AND urlDocument='$urlDocument' AND od.idOpportunityF=$idOP LIMIT 1");
                    $oldDocument =  $db->single();

                    if ($oldDocument) {
                        $numeroDocument = $oldDocument->numeroDocument;
                        $db->query("UPDATE wbcc_document SET 
                                    nomDocument=:nomDocument, urlDocument=:urlDocument, 
                                    editDate=:editDate, auteur=:auteur, idUtilisateurF=:idUtilisateurF
                                    WHERE idDocument=:idDocument 
                                    ");
                        $db->bind("idDocument", $oldDocument->idDocument, null);
                    } else {
                        $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                        $db->query(" INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, createDate, editDate, auteur, idUtilisateurF,  source, publie) 
                                VALUES (:numeroDocument, :nomDocument, :urlDocument, :createDate, :editDate, :auteur, :idUtilisateurF, :source, :publie) ");

                        $db->bind("numeroDocument", $numeroDocument, null);
                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                        $db->bind("source", 'EXTRANET', null);
                        $db->bind("publie", 1, null);
                    }
                    $db->bind("nomDocument", $nomDocument, null);
                    $db->bind("urlDocument", $urlDocument, null);
                    $db->bind("editDate", date("Y-m-d H:i:s"), null);
                    $db->bind("auteur", $auteur, null);
                    $db->bind("idUtilisateurF", $idUtilisateur, null);
                    $db->execute();

                    // 3- enregistrer dans la table domument OP

                    $db->query("SELECT * FROM wbcc_opportunity_document WHERE numeroOpportunityF =:numeroOpportunityF AND numeroDocumentF =:numeroDocumentF");
                    $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                    $db->bind("numeroDocumentF", $numeroDocument, null);
                    if ($db->single() == false) {
                        $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                        $db->query("SELECT * FROM wbcc_opportunity_document WHERE idOpportunityF =:idOpportunity AND idDocumentF =:idDocument");
                        $db->bind("idOpportunity", $opportunity->idOpportunity, null);
                        $db->bind("idDocument", $document->idDocument, null);
                        if ($db->single() == false) {
                            $db->query("INSERT INTO wbcc_opportunity_document (numeroOpportunityF, numeroDocumentF, idDocumentF, idOpportunityF) VALUES (:numeroOpportunityF, :numeroDocumentF,  :idDocumentF, :idOpportunityF)");
                            $db->bind("numeroDocumentF", $numeroDocument, null);
                            $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                            $db->bind("idDocumentF", $document->idDocument, null);
                            $db->bind("idOpportunityF", $opportunity->idOpportunity, null);
                            $db->execute();
                        }
                    }
                }

                /********** Parie globale pour toutes les OP ******* */

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                // Délégation
                if ($faireRechercheFuite == "1") {
                    if ($faireRechercheFuite != $opportunity->faireRechercheFuite) {
                        createNote($idOP, $idUtilisateur, $auteur, "Recherche fuite Fait ", "Recherche fuite Fait", 1);
                    }
                    $req = ", faireRechercheFuite=1, dateRechercheFuite='$dateRechercheFuite', idAuteurRechercheFuite=$idAuteurRechercheFuite";
                    manageActivity($idOP, 30, 'True', $opName, "Faire recherche fuite ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($faireRechercheFuite == "0") {
                    if ($faireRechercheFuite != $opportunity->faireRechercheFuite) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire recherche fuite ", "Faire recherche fuite ", 1);
                    }
                    $req = ", faireRechercheFuite=0, dateRechercheFuite=NULL, idAuteurRechercheFuite=NULL";
                    manageActivity($idOP, 30, 'False', $opName, "Faire recherche fuite ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($faireRechercheFuite == "2") {
                    if ($faireRechercheFuite != $opportunity->faireRechercheFuite) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire recherche fuite n'est pas nécéssaire pour ce dossier ", "Faire recherche fuite n'est pas nécéssaire pour ce dossier ", 1);
                    }
                    $req = ", faireRechercheFuite=$faireRechercheFuite, dateRechercheFuite=NULL, idAuteurRechercheFuite=NULL";
                    manageActivity($idOP, 30, 'False', $opName, "Faire recherche fuite n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", faireRechercheFuite=NULL, dateRechercheFuite=NULL, idAuteurRechercheFuite=NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(30)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 30, $auteur, $idUtilisateur, $faireRechercheFuiteCom);

                /************** Mise à jour rapport recherche fuite *********/
                if ($faireRechercheFuite == "1") {

                    $search = false;
                    $numero = "";
                    $Piece = findItemByColumn("wbcc_piece", "idPiece", $pieceFuyardeId);
                    $pieceFuyarde = $Piece ? $Piece->libellePiece : '';

                    $Equipement = findItemByColumn("wbcc_equipement", "idEquipement", $equipementFuyardId);
                    $equipementFuyard = $Equipement ? $Equipement->libelleEquipement : '';
                    if ($idOP != "" || $idOP != "") {
                        $search = findItemByColumn("wbcc_recherche_fuite", "idOpportunityF", $idOP);
                    }
                    if ($search) {
                        $id = $search->idRF;
                        $db->query("UPDATE  wbcc_recherche_fuite  SET  numeroOP=:numeroOP,
                                    editDate=:editDate, idOpportunityF=:idOpportunityF, pieceFuyarde=:pieceFuyarde,
                                    equipementFuyard=:equipementFuyard, origineFuiteEquipement=:origineFuiteEquipement WHERE idRF = $id");
                    } else {
                        $numero = "CON" . date('dmYHis') . $idUtilisateur;
                        $db->query("INSERT INTO wbcc_recherche_fuite(numeroRF, numeroOP,  editDate, idOpportunityF,
                                                pieceFuyarde, equipementFuyard, origineFuiteEquipement) 
                                    VALUES (:numeroRF, :numeroOP, :editDate, :idOpportunityF, :pieceFuyarde,
                                    :equipementFuyard, :origineFuiteEquipement)");
                        $db->bind("numeroRF", $numero, null);
                    }

                    $db->bind("numeroOP", $opName, null);
                    $db->bind("editDate", date('Y-m-d H:i:s'), null);
                    $db->bind("idOpportunityF", $idOP, null);
                    $db->bind("pieceFuyarde", $pieceFuyarde, null);
                    $db->bind("equipementFuyard", $equipementFuyard, null);
                    $db->bind("origineFuiteEquipement", $origineFuiteEquipement, null);
                    $db->execute();

                    if ($nomDocument != '') {
                        $db->query("UPDATE `wbcc_opportunity` SET rapportRechercheFuite = '$nomDocument' WHERE idOpportunity = $idOP");
                        $db->execute();
                    }
                }

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());

                /******** fin SAVE Constat DDE  *********/
            } elseif ($etape == 10) {
                //echo json_encode($etape);
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                $libelePasNecessaire = "Faire demande Réparation de fuite pas nécéssaire pour ce dossier";
                // Délégation
                if ($faireDemandeReparationFuite == "1") {
                    createNote($idOP, $idUtilisateur, $auteur, "Demande Réparation de fuite Fait ", "Demande Réparation de fuite Fait", 1);
                    $db->query("SELECT * FROM wbcc_utilisateur u, wbcc_contact c WHERE u.idContactF= c.idContact AND u.idUtilisateur=$idAuteurDemandeReparationFuite LIMIT 1");
                    $theAuteur =  $db->single();
                    $auteurDemandeReparationFuite = $theAuteur ? $theAuteur->nomContact . ' ' . $theAuteur->prenomContact : "";
                    $numAuteurDemandeReparationFuite = $theAuteur ? $theAuteur->nomContact . ' ' . $theAuteur->numeroContact : "";

                    $regarding = "Demande Réparation de fuite Fait";
                    $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=33 AND idOpportunityF = $idOP LIMIT 1");
                    $activity = $db->single();
                    if ($activity) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'True',editDate=:editDate, realisedBy=:realisedBy, idRealisedBy =:idRealisedBy WHERE idActivity = $activity->idActivity");
                        $db->bind("editDate", $dateDemandeReparationFuite, null);
                        $db->bind("realisedBy", $auteurDemandeReparationFuite, null);
                        $db->bind("idRealisedBy", $idAuteurDemandeReparationFuite, null);
                        $db->execute();
                    } else {
                        createNewActivity($idOP, $opName, $idAuteurDemandeReparationFuite, $auteurDemandeReparationFuite, $numAuteurDemandeReparationFuite,  $opName . '-' .  $regarding, "", $dateDemandeReparationFuite, $dateDemandeReparationFuite, "Tâche à faire", "True", "0", 33);
                    }

                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                    /*$db->query("DELETE FROM wbcc_note WHERE noteText='$libelePasNecessaire'
                                AND idNote IN (SELECT idNoteF FROM wbcc_opportunity_note WHERE idOpportunityF = $idOP ) ");
                $db->execute();*/
                } elseif ($faireDemandeReparationFuite == "0") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire demande Réparation de fuite ", "Faire demande Réparation de fuite ", 1);
                    manageActivity($idOP, 33, 'False', $opName, "Faire demande Réparation de fuite ");

                    $regarding = "Demande Réparation de fuite Fait";
                    $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=33 AND idOpportunityF = $idOP LIMIT 1");
                    $activity = $db->single();
                    if ($activity) {
                        $db->query("UPDATE wbcc_activity SET isCleared = 'False',editDate=:editDate, organizer=:organizer, idUtilisateurF =:idUtilisateurF WHERE idActivity = $activity->idActivity");
                        $db->bind("editDate", $dateDemandeReparationFuite, null);
                        $db->bind("organizer", $auteur, null);
                        $db->bind("idUtilisateurF", $idUtilisateur, null);
                        $db->execute();
                    } else {
                        createNewActivity($idOP, $opName, $idUtilisateur, $auteur, '',  $opName . '-' .  $regarding, "", $today, $today, "Tâche à faire", "False", "0", 33);
                    }

                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                    /*$db->query("DELETE FROM wbcc_note WHERE noteText='$libelePasNecessaire' AND idNote IN (SELECT idNoteF FROM wbcc_opportunity_note WHERE idOpportunityF = $idOP )");
                $db->execute();*/
                } elseif ($faireDemandeReparationFuite == "2") {
                    $db->query("DELETE FROM wbcc_note WHERE noteText='$libelePasNecessaire'
                                AND idNote IN (SELECT idNoteF FROM wbcc_opportunity_note WHERE idOpportunityF = $idOP ) ");
                    $db->execute();
                    createNote($idOP, $idUtilisateur, $auteur, "$libelePasNecessaire", "$libelePasNecessaire", 1);
                    manageActivity($idOP, 33, 'False', $opName, "$libelePasNecessaire");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$db->query("DELETE FROM wbcc_note WHERE noteText='$libelePasNecessaire'
                                AND idNote IN (SELECT idNoteF FROM wbcc_opportunity_note WHERE idOpportunityF = $idOP )");
                $db->execute();*/
                    //setEtatActivitiesOP($idOP, 'True', '(33)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                    /*$db->query("DELETE FROM wbcc_note WHERE noteText='$libelePasNecessaire'
                                AND idNote IN (SELECT idNoteF FROM wbcc_opportunity_note WHERE idOpportunityF = $idOP ) ");
                $db->execute();*/
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 33, $auteur, $idUtilisateur, $faireDemandeReparationFuiteCom);
                $db->query("UPDATE `wbcc_opportunity` SET type='Sinistres', etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew' WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());

                /******** fin SAVE Constat DDE  *********/
            } elseif ($etape == 11) {

                /******** SAVE constat DDE  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $nomDocument = '';
                if (isset($_FILES['docJustificatifReparation'])) {

                    $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;

                    $nomDocument = "$opName" . "JUSTIFICATIF_REPARATION_" . date('dmYHis') . ".pdf";
                    $urlDocument = "$opName" . "JUSTIFICATIF_REPARATION_" . date('dmYHis') . ".pdf";

                    // 1 - suavegarder le fichier dans document OP
                    //if (!file_exists("../documents/opportunite/".$nomDocument)) {
                    move_uploaded_file($_FILES['docJustificatifReparation']['tmp_name'], "../documents/opportunite/" . $nomDocument);
                    //}

                    // 2- enregistrer la référence dans table document et retourner l'id de la ligne inséré
                    $db->query("SELECT * FROM wbcc_document d, wbcc_opportunity_document od WHERE od.idDocumentF= d.idDocument AND urlDocument='$urlDocument' AND od.idOpportunityF=$idOP LIMIT 1");
                    $oldDocument =  $db->single();

                    if ($oldDocument) {
                        $numeroDocument = $oldDocument->numeroDocument;
                        $db->query("UPDATE wbcc_document SET 
                                    nomDocument=:nomDocument, urlDocument=:urlDocument, 
                                    editDate=:editDate, auteur=:auteur, idUtilisateurF=:idUtilisateurF
                                    WHERE idDocument=:idDocument 
                                    ");
                        $db->bind("idDocument", $oldDocument->idDocument, null);
                    } else {
                        $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                        $db->query(" INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, createDate, editDate, auteur, idUtilisateurF,  source, publie) 
                                VALUES (:numeroDocument, :nomDocument, :urlDocument, :createDate, :editDate, :auteur, :idUtilisateurF, :source, :publie) ");

                        $db->bind("numeroDocument", $numeroDocument, null);
                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                        $db->bind("source", 'EXTRANET', null);
                        $db->bind("publie", 1, null);
                    }
                    $db->bind("nomDocument", $nomDocument, null);
                    $db->bind("urlDocument", $urlDocument, null);
                    $db->bind("editDate", date("Y-m-d H:i:s"), null);
                    $db->bind("auteur", $auteur, null);
                    $db->bind("idUtilisateurF", $idUtilisateur, null);
                    $db->execute();

                    // 3- enregistrer dans la table domument OP

                    $db->query("SELECT * FROM wbcc_opportunity_document WHERE numeroOpportunityF =:numeroOpportunityF AND numeroDocumentF =:numeroDocumentF");
                    $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                    $db->bind("numeroDocumentF", $numeroDocument, null);
                    if ($db->single() == false) {
                        $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                        $db->query("SELECT * FROM wbcc_opportunity_document WHERE idOpportunityF =:idOpportunity AND idDocumentF =:idDocument");
                        $db->bind("idOpportunity", $opportunity->idOpportunity, null);
                        $db->bind("idDocument", $document->idDocument, null);
                        if ($db->single() == false) {
                            $db->query("INSERT INTO wbcc_opportunity_document (numeroOpportunityF, numeroDocumentF, idDocumentF, idOpportunityF) VALUES (:numeroOpportunityF, :numeroDocumentF,  :idDocumentF, :idOpportunityF)");
                            $db->bind("numeroDocumentF", $numeroDocument, null);
                            $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                            $db->bind("idDocumentF", $document->idDocument, null);
                            $db->bind("idOpportunityF", $opportunity->idOpportunity, null);
                            $db->execute();
                        }
                    }
                }

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                // Délégation
                if ($justificatifReparation == "1") {
                    createNote($idOP, $idUtilisateur, $auteur, "Recupérer justificatif réparation fuite Fait ", "Recupérer justificatif réparation fuite Fait", 1);
                    $req = ", justificatifReparation=1, dateJustificatifReparation='$dateJustificatifReparation', idAuteurJustificatifReparation=$idAuteurJustificatifReparation";
                    manageActivity($idOP, 32, 'True', $opName, "Faire Recupérer justificatif réparation fuite ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($justificatifReparation == "0") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire Recupérer justificatif réparation fuite ", "Faire Recupérer justificatif réparation fuite ", 1);
                    $req = ", justificatifReparation=0, dateJustificatifReparation=NULL, idAuteurJustificatifReparation=NULL";
                    manageActivity($idOP, 32, 'False', $opName, "Faire Recupérer justificatif réparation fuite ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($justificatifReparation == "2") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire Recupérer justificatif réparation fuite n'est pas nécéssaire pour ce dossier ", "Faire Recupérer justificatif réparation fuite n'est pas nécéssaire pour ce dossier ", 1);
                    $req = ", justificatifReparation=$justificatifReparation, dateJustificatifReparation=NULL, idAuteurJustificatifReparation=NULL";
                    manageActivity($idOP, 32, 'True', $opName, "Faire Recupérer justificatif réparation fuite n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", justificatifReparation=NULL, dateJustificatifReparation=NULL, idAuteurJustificatifReparation=NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(32)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 32, $auteur, $idUtilisateur, $justificatifReparationCom);

                if ($justificatifReparation == "1" && $nomDocument != '') {
                    $db->query("UPDATE wbcc_opportunity SET docJustificatifReparation = '$nomDocument' WHERE idOpportunity = $idOP ");
                    $db->execute();
                }

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());

                /******** fin SAVE Constat DDE  *********/
            } elseif ($etape == 12) {

                /******** SAVE constat DDE  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                // Délégation
                if ($envoiJustificatifRF == "1") {
                    createNote($idOP, $idUtilisateur, $auteur, "Envoyer justificatif réparation fuite Fait ", "Envoyer justificatif réparation fuite Fait", 1);
                    $req = ", envoiJustificatifRF=1, dateEnvoiJustificatifRF='$dateEnvoiJustificatifRF', idAuteurEnvoiJustificatifRF=$idAuteurEnvoiJustificatifRF";
                    manageActivity($idOP, 42, 'True', $opName, "Faire envoyer justificatif réparation fuite  ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($envoiJustificatifRF == "0") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire envoyer justificatif réparation fuite ", "Faire envoyer justificatif réparation fuite ", 1);
                    $req = ", envoiJustificatifRF=0, dateEnvoiJustificatifRF=NULL, idAuteurEnvoiJustificatifRF=NULL";
                    manageActivity($idOP, 42, 'False', $opName, "Faire envoyer justificatif réparation fuite ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($envoiJustificatifRF == "2") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire envoyer justificatif réparation fuite n'est pas nécéssaire pour ce dossier ", "Faire envoyer justificatif réparation fuite n'est pas nécéssaire pour ce dossier ", 1);
                    $req = ", envoiJustificatifRF=$envoiJustificatifRF, dateEnvoiJustificatifRF=NULL, idAuteurEnvoiJustificatifRF=NULL";
                    manageActivity($idOP, 42, 'True', $opName, "Faire envoyer justificatif réparation fuite n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", envoiJustificatifRF=NULL, dateEnvoiJustificatifRF=NULL, idAuteurJustificatifReparation=NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(42)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 42, $auteur, $idUtilisateur, $envoiJustificatifRFCom);

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());

                /******** fin SAVE Constat DDE  *********/
            } elseif ($etape == 13) {

                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $nomDocument = $opportunity->name . '_CompteRenduRT.pdf';
                $urlDocument = $opportunity->name . '_CompteRenduRT.pdf';
                if (isset($_FILES['compteRenduRT'])) {
                    //if (!file_exists("../documents/opportunite/".$nomDocument)) {
                    move_uploaded_file($_FILES['compteRenduRT']['tmp_name'], "../documents/opportunite/" . $nomDocument);
                    //}
                }

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                // Délégation
                if ($compteRenduRTFait == "1") {

                    $oldCompteRendu = findItemByColumn("wbcc_document", "urlDocument", $urlDocument);
                    $db->query("SELECT * FROM wbcc_utilisateur u, wbcc_contact c WHERE u.idContactF= c.idContact AND u.idUtilisateur=$idAuteurCompteRenduRT LIMIT 1");
                    $theAuteur =  $db->single();

                    if ($oldCompteRendu) {
                        $numeroDocument = $oldCompteRendu->numeroDocument;
                        $db->query("UPDATE wbcc_document SET 
                                    nomDocument=:nomDocument, urlDocument=:urlDocument, 
                                    editDate=:editDate, auteur=:auteur, idUtilisateurF=:idUtilisateurF
                                    WHERE idDocument=:idDocument 
                                    ");
                        $db->bind("idDocument", $oldCompteRendu->idDocument, null);
                    } else {
                        $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                        $db->query(" INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, createDate, editDate, auteur, idUtilisateurF,  source, publie) 
                                VALUES (:numeroDocument, :nomDocument, :urlDocument, :createDate, :editDate, :auteur, :idUtilisateurF, :source, :publie) ");

                        $db->bind("numeroDocument", $numeroDocument, null);
                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                        $db->bind("source", 'EXTRANET', null);
                        $db->bind("publie", 1, null);
                    }


                    $db->bind("nomDocument", $nomDocument, null);
                    $db->bind("urlDocument", $urlDocument, null);
                    $db->bind("editDate", $dateCompteRenduRT, null);
                    $db->bind("auteur", $theAuteur->prenomContact . ' ' . $theAuteur->nomContact, null);
                    $db->bind("idUtilisateurF", $idAuteurCompteRenduRT, null);
                    $db->execute();

                    // 3- enregistrer dans la table domument OP

                    $db->query("SELECT * FROM wbcc_opportunity_document WHERE numeroOpportunityF =:numeroOpportunityF AND numeroDocumentF =:numeroDocumentF");
                    $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                    $db->bind("numeroDocumentF", $numeroDocument, null);
                    if ($db->single() == false) {
                        $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                        $db->query("SELECT * FROM wbcc_opportunity_document WHERE idOpportunityF =:idOpportunity AND idDocumentF =:idDocument");
                        $db->bind("idOpportunity", $opportunity->idOpportunity, null);
                        $db->bind("idDocument", $document->idDocument, null);
                        if ($db->single() == false) {
                            $db->query("INSERT INTO wbcc_opportunity_document (numeroOpportunityF, numeroDocumentF, idDocumentF, idOpportunityF) VALUES (:numeroOpportunityF, :numeroDocumentF,  :idDocumentF, :idOpportunityF)");
                            $db->bind("numeroDocumentF", $numeroDocument, null);
                            $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                            $db->bind("idDocumentF", $document->idDocument, null);
                            $db->bind("idOpportunityF", $opportunity->idOpportunity, null);
                            $db->execute();
                        }
                    }

                    $db->query("UPDATE wbcc_opportunity SET compteRenduRT = '$nomDocument' WHERE idOpportunity = $idOP ");
                    $db->execute();

                    createNote($idOP, $idUtilisateur, $auteur, "Faire Compte rendu Fait ", "Faire Compte rendu Fait", 1);
                    manageActivity($idOP, 16, 'True', $opName, "Faire constat DDE ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($compteRenduRTFait == "0") {

                    $db->query("UPDATE wbcc_opportunity SET compteRenduRT =NULL WHERE idOpportunity = $idOP ");
                    $db->execute();

                    createNote($idOP, $idUtilisateur, $auteur, "Faire compte rendu ", "Faire compte rendu ", 1);
                    manageActivity($idOP, 16, 'False', $opName, "Faire compte rendu ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($compteRenduRTFait == "2") {
                    $db->query("UPDATE wbcc_opportunity SET compteRenduRT =NULL WHERE idOpportunity = $idOP ");
                    $db->execute();

                    createNote($idOP, $idUtilisateur, $auteur, "Faire compte rendu n'est pas nécéssaire pour ce dossier ", "Faire compte rendu n'est pas nécéssaire pour ce dossier ", 1);
                    manageActivity($idOP, 16, 'True', $opName, "Faire compte rendu n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$db->query("UPDATE wbcc_opportunity SET compteRenduRT =NULL WHERE idOpportunity = $idOP ");
                $db->execute();*/

                    //setEtatActivitiesOP($idOP, 'True', '(16)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }


                saveCommentaireAsNote($idOP, $numeroOpportunity, 16, $auteur, $idUtilisateur, $compteRenduRTFaitCom);

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew', type='Sinistres' WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());
            } elseif ($etape == 14) {

                /******** SAVE faire devis *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $nomDocument = '';

                if (isset($_FILES['fichierDevis'])) {

                    $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;

                    $nomDocument = "$opName" . "-DEVIS" . ".pdf";
                    $urlDocument = "$opName" . "-DEVIS" . ".pdf";

                    // 1 - suavegarder le fichier dans document OP
                    //if (!file_exists("../documents/opportunite/".$nomDocument)) {
                    move_uploaded_file($_FILES['fichierDevis']['tmp_name'], "../documents/opportunite/" . $nomDocument);
                    //}

                    // 2- enregistrer la référence dans table document et retourner l'id de la ligne inséré
                    $db->query("SELECT * FROM wbcc_document d, wbcc_opportunity_document od WHERE od.idDocumentF= d.idDocument AND urlDocument='$urlDocument' AND od.idOpportunityF=$idOP LIMIT 1");
                    $oldDocument =  $db->single();

                    if ($oldDocument) {
                        $numeroDocument = $oldDocument->numeroDocument;
                        $db->query("UPDATE wbcc_document SET 
                                    nomDocument=:nomDocument, urlDocument=:urlDocument, 
                                    editDate=:editDate, auteur=:auteur, idUtilisateurF=:idUtilisateurF
                                    WHERE idDocument=:idDocument 
                                    ");
                        $db->bind("idDocument", $oldDocument->idDocument, null);
                    } else {
                        $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                        $db->query(" INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, createDate, editDate, auteur, idUtilisateurF,  source, publie) 
                                VALUES (:numeroDocument, :nomDocument, :urlDocument, :createDate, :editDate, :auteur, :idUtilisateurF, :source, :publie) ");

                        $db->bind("numeroDocument", $numeroDocument, null);
                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                        $db->bind("source", 'EXTRANET', null);
                        $db->bind("publie", 1, null);
                    }
                    $db->bind("nomDocument", $nomDocument, null);
                    $db->bind("urlDocument", $urlDocument, null);
                    $db->bind("editDate", date("Y-m-d H:i:s"), null);
                    $db->bind("auteur", $auteur, null);
                    $db->bind("idUtilisateurF", $idUtilisateur, null);
                    $db->execute();

                    // 3- enregistrer dans la table domument OP

                    $db->query("SELECT * FROM wbcc_opportunity_document WHERE numeroOpportunityF =:numeroOpportunityF AND numeroDocumentF =:numeroDocumentF");
                    $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                    $db->bind("numeroDocumentF", $numeroDocument, null);
                    if ($db->single() == false) {
                        $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                        $db->query("SELECT * FROM wbcc_opportunity_document WHERE idOpportunityF =:idOpportunity AND idDocumentF =:idDocument");
                        $db->bind("idOpportunity", $opportunity->idOpportunity, null);
                        $db->bind("idDocument", $document->idDocument, null);
                        if ($db->single() == false) {
                            $db->query("INSERT INTO wbcc_opportunity_document (numeroOpportunityF, numeroDocumentF, idDocumentF, idOpportunityF) VALUES (:numeroOpportunityF, :numeroDocumentF,  :idDocumentF, :idOpportunityF)");
                            $db->bind("numeroDocumentF", $numeroDocument, null);
                            $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                            $db->bind("idDocumentF", $document->idDocument, null);
                            $db->bind("idOpportunityF", $opportunity->idOpportunity, null);
                            $db->execute();
                        }
                    }
                }

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                // Délégation
                if ($devisFais == "1") {
                    createNote($idOP, $idUtilisateur, $auteur, "Devis Fait ", "Devis Fait", 1);
                    $req = ", devisFais=1, dateDevisFais='$dateDevisFais', idAuteurDevisFais=$idAuteurDevisFais";
                    manageActivity($idOP, 7, 'True', $opName, "Faire constat DDE ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($devisFais == "0") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire Devis ", "Faire Devis ", 1);
                    $req = ", devisFais=0, dateDevisFais=NULL, idAuteurDevisFais=NULL";
                    manageActivity($idOP, 7, 'False', $opName, "Faire Devis ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($devisFais == "2") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire Devis n'est pas nécéssaire pour ce dossier ", "Faire Devis n'est pas nécéssaire pour ce dossier ", 1);
                    $req = ", devisFais=$devisFais, dateDevisFais=NULL, idAuteurDevisFais=NULL";
                    manageActivity($idOP, 7, 'True', $opName, "Faire Devis n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", devisFais=NULL, dateDevisFais=NULL, idAuteurDevisFais=NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(7)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 7, $auteur, $idUtilisateur, $devisFaisCom);

                if ($devisFais == "1") {
                    if ($nomDocument != '') {
                        $db->query("UPDATE wbcc_opportunity SET fichierDevis='$nomDocument' WHERE idOpportunity = $idOP ");
                        $db->execute();
                    }

                    $res = 1;
                    $ok = true;

                    $db->query("UPDATE wbcc_opportunity SET montantOp=:montantOp, dureeTravaux=:dureeTravaux WHERE idOpportunity = :idOpportunity");
                    $db->bind("montantOp", $mntDevis, null);
                    $db->bind("dureeTravaux", $dureeTravaux, null);
                    $db->bind("idOpportunity", $idOP, null);
                    if ($db->execute()) {
                    } else {
                        $ok = false;
                    }
                    if ($ok) {
                        $numero = "Devis_" . date("YmdHis") . "$idOP$idUtilisateur";
                        $lineOp2 = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                        $fileName = $lineOp2 ? $lineOp2->fichierDevis : "";
                        $devis = false;
                        if ($idDevis == "0") {
                            $devis = findItemByColumn("wbcc_devis", "numeroDevisWBCC", $numDevis);
                            if ($devis) {
                                $db->query("UPDATE `wbcc_devis` SET `idRTF`=:idRTF,`idExpertWBCCF`=:idExpertWBCCF,`dateGenerationDevis`=:dateGenerationDevis,`devisFile`=:devisFile,`commentaireDevis`=:commentaireDevis,`montantTotal`=:montantTotal, montantAvantExpertise=:montantTotal,  montantHT=:montantHT, taux=:taux,  `editDate`=:editDate,`idUserF`=:idUserF,`artisan`=:artisan, idArtisanF=:idArtisanF, dureeTravaux=:dureeTravaux WHERE `numeroDevisWBCC` =:numeroDevisWBCC ");
                            } else {
                                $db->query("INSERT INTO `wbcc_devis`(`numeroDevis`, `idRTF`, `idExpertWBCCF`, `numeroDevisWBCC`, `dateGenerationDevis`, `devisFile`, `commentaireDevis`, `montantTotal`, montantAvantExpertise, montantHT, taux, `editDate`, `createDate`, `idUserF`, artisan, idArtisanF, dureeTravaux) VALUES (:numeroDevis,:idRTF,:idExpertWBCCF,:numeroDevisWBCC,:dateGenerationDevis,:devisFile,:commentaireDevis,:montantTotal, :montantTotal, :montantHT, :taux, :editDate,:createDate,:idUserF,:artisan, :idArtisanF, :dureeTravaux)");
                                $db->bind("createDate", date("Y-m-d H:i:s"), null);
                                $db->bind("numeroDevis", $numero, null);
                            }
                        } else {
                            $devis = findItemByColumn("wbcc_devis", "idDevis", $idDevis);
                            $db->query("UPDATE `wbcc_devis` SET `idRTF`=:idRTF,`idExpertWBCCF`=:idExpertWBCCF,`dateGenerationDevis`=:dateGenerationDevis,`devisFile`=:devisFile,`commentaireDevis`=:commentaireDevis,`montantTotal`=:montantTotal, montantAvantExpertise=:montantTotal, montantHT=:montantHT, taux=:taux,  `editDate`=:editDate,`idUserF`=:idUserF,`artisan`=:artisan, idArtisanF=:idArtisanF,`numeroDevisWBCC` =:numeroDevisWBCC, dureeTravaux=:dureeTravaux WHERE `idDevis` = :idDevis");
                            $db->bind("idDevis", $idDevis, null);
                        }

                        $db->bind("idRTF", $idRT == "" || $idRT == "null" ? null : (isset($idRTF) ? $idRTF : null), null);
                        $db->bind("idExpertWBCCF", $idExpertWBCC, null);
                        $db->bind("numeroDevisWBCC", $numDevis, null);
                        $db->bind("dateGenerationDevis", $dateDevis, null);
                        $db->bind("devisFile", $fileName, null);
                        $db->bind("commentaireDevis", $comDevis, null);
                        $db->bind("montantTotal", $mntDevis, null);
                        $db->bind("montantHT", $montantHT, null);
                        $db->bind("taux", $taux, null);
                        $db->bind("idUserF", $idUtilisateur, null);
                        $db->bind("editDate", date("Y-m-d H:i:s"), null);
                        $db->bind("artisan", $artisan, null);
                        $db->bind("idArtisanF", $idArtisanF, null);
                        $db->bind("dureeTravaux", $dureeTravaux, null);
                        if ($db->execute()) {

                            $devisOP = findItemByColumn("wbcc_opportunity_devis", "idOpportunityF", $idOP);
                            if ($idDevis == "0") {
                                if ($devis) {
                                } else {
                                    $devis = findItemByColumn("wbcc_devis", "numeroDevis", $numero);
                                }
                                if ($devis) {
                                    if ($devisOP) {
                                        $db->query("UPDATE `wbcc_opportunity_devis` SET idDevisF = :idDevisF WHERE idOpportunityF=:idOpportunityF");
                                        $db->bind("idDevisF", $devis->idDevis, null);
                                        $db->bind("idOpportunityF", $idOP, null);
                                        if ($db->execute()) {
                                        } else {
                                            $res = 0;
                                        }
                                    } else {
                                        $db->query("INSERT INTO `wbcc_opportunity_devis`(`idOpportunityF`, `idDevisF`) VALUES (:idOpportunityF,:idDevisF)");
                                        $db->bind("idOpportunityF", $idOP, null);
                                        $db->bind("idDevisF", $devis->idDevis, null);
                                        if ($db->execute()) {
                                        } else {
                                            $res = 0;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew', type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());

                /******** fin SAVE faire devis *********/
            } elseif ($etape == 15) {

                /******** SAVE constat DDE  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                // Délégation
                if ($envoiDevis == "1") {
                    createNote($idOP, $idUtilisateur, $auteur, "Envoie devis à la compagnie Fait ", "Envoie devis à la compagnie Fait", 1);
                    $req = ", envoiDevis=1, dateEnvoiDevis='$dateEnvoiDevis', idAuteurEnvoiDevis=$idAuteurEnvoiDevis";
                    manageActivity($idOP, 9, 'True', $opName, "Faire envoie devis à la compagnie ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($envoiDevis == "0") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire envoie devis à la compagnie ", "Faire envoie devis à la compagnie ", 1);
                    $req = ", envoiDevis=0, dateEnvoiDevis=NULL, idAuteurEnvoiDevis=NULL";
                    manageActivity($idOP, 9, 'False', $opName, "Faire envoie devis à la compagnie ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($envoiDevis == "2") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire envoie devis à la compagnie n'est pas nécéssaire pour ce dossier ", "Faire envoie devis à la compagnie n'est pas nécéssaire pour ce dossier ", 1);
                    $req = ", envoiDevis=$envoiDevis, dateEnvoiDevis=NULL, idAuteurEnvoiDevis=NULL";
                    manageActivity($idOP, 9, 'True', $opName, "Faire envoie devis à la compagnie n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", envoiDevis=NULL, dateEnvoiDevis=NULL, idAuteurJustificatifReparation=NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(9)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 9, $auteur, $idUtilisateur, $envoiDevisCom);

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());

                /******** fin SAVE Constat DDE  *********/
            } elseif ($etape == 16) {

                /******** SAVE constat DDE  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                // Délégation
                if ($priseEnCharge == "1") {
                    createNote($idOP, $idUtilisateur, $auteur, "Relance compagnie pour prise en charge Fait ", "Relance compagnie pour prise en charge Fait", 1);
                    $req = ", priseEnCharge=1, datePriseEnCharge='$datePriseEnCharge', idAuteurPriseEnCharge=$idAuteurPriseEnCharge";
                    manageActivity($idOP, 10, 'True', $opName, "Faire Relance compagnie pour prise en charge ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($priseEnCharge == "0") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire Relance compagnie pour prise en charge ", "Faire Relance compagnie pour prise en charge ", 1);
                    $req = ", priseEnCharge=0, datePriseEnCharge=NULL, idAuteurPriseEnCharge=NULL";
                    manageActivity($idOP, 10, 'False', $opName, "Faire Relance compagnie pour prise en charge ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($priseEnCharge == "2") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire Relance compagnie pour prise en charge n'est pas nécéssaire pour ce dossier ", "Faire Relance compagnie pour prise en charge n'est pas nécéssaire pour ce dossier ", 1);
                    $req = ", priseEnCharge=$priseEnCharge, datePriseEnCharge=NULL, idAuteurPriseEnCharge=NULL";
                    manageActivity($idOP, 10, 'True', $opName, "Faire Relance compagnie pour prise en charge n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", priseEnCharge=NULL, datePriseEnCharge=NULL, idAuteurJustificatifReparation=NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(10)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, $auteur, 10, $idUtilisateur, $priseEnChargeCom);

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());

                /******** fin SAVE Constat DDE  *********/
            } elseif ($etape == 17) {

                /******** SAVE constat DDE  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                // Délégation
                if ($priseDispoSinistre == "1") {
                    createNote($idOP, $idUtilisateur, $auteur, "Appeler sinistré pour RDV d'expertise Fait ", "Appeler sinistré pour RDV d'expertise Fait", 1);
                    $req = ", priseDispoSinistre=1, datePriseDispoSinistre='$datePriseDispoSinistre', idAuteurPriseDispoSinistre=$idAuteurPriseDispoSinistre";
                    manageActivity($idOP, 14, 'True', $opName, "Appeler sinistré pour RDV d'expertise ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($priseDispoSinistre == "0") {
                    createNote($idOP, $idUtilisateur, $auteur, "Appeler sinistré pour RDV d'expertise ", "Appeler sinistré pour RDV d'expertise ", 1);
                    $req = ", priseDispoSinistre=0, datePriseDispoSinistre=NULL, idAuteurPriseDispoSinistre=NULL";
                    manageActivity($idOP, 14, 'False', $opName, "Appeler sinistré pour RDV d'expertise ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($priseDispoSinistre == "2") {
                    createNote($idOP, $idUtilisateur, $auteur, "Appeler sinistré pour RDV d'expertise n'est pas nécéssaire pour ce dossier ", "Appeler sinistré pour RDV d'expertise n'est pas nécéssaire pour ce dossier ", 1);
                    $req = ", priseDispoSinistre=$priseDispoSinistre, datePriseDispoSinistre=NULL, idAuteurPriseDispoSinistre=NULL";
                    manageActivity($idOP, 14, 'True', $opName, "Appeler sinistré pour RDV d'expertise n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", priseDispoSinistre=NULL, datePriseDispoSinistre=NULL, idAuteurJustificatifReparation=NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(14)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 14, $auteur, $idUtilisateur, $priseDispoSinistreCom);

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());

                /******** fin SAVE Constat DDE  *********/
            } elseif ($etape == 18) {
                $req = "";
                $today = date("Y-m-d H:i:s");
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                if ($priseRdvExpertise == "1") {
                    if ($priseRdvExpertise != $opportunity->priseRdvExpertise) {
                        createNote($idOP, $idUtilisateur, $auteur, " RV Expertise pris ", " RV Expertise pris", 1);
                    }
                    $req = ", priseRdvExpertise=1, datePriseRdvExpertise='$datePriseRdvExpertise', idAuteurPriseRdvExpertise =$idAuteurPriseRdvExpertise";
                    manageActivity($idOP, 15, 'True', $opName, " Prendre RV Expertise ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($priseRdvExpertise == "0") {
                    if ($priseRdvExpertise != $opportunity->priseRdvExpertise) {
                        createNote($idOP, $idUtilisateur, $auteur, "Prendre RV Expertise ", "Prendre RV Expertise ", 1);
                    }
                    $req = ", priseRdvExpertise=0, datePriseRdvExpertise=NULL, idAuteurPriseRdvExpertise =NULL";
                    manageActivity($idOP, 15, 'False', $opName, "Prendre RV Expertise ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($priseRdvExpertise == "2") {
                    if ($priseRdvExpertise != $opportunity->priseRdvExpertise) {
                        createNote($idOP, $idUtilisateur, $auteur, "Prise de RDV Expertise n'est pas nécéssaire pour ce dossier ", "Prise de RDV Expertise n'est pas nécéssaire pour ce dossier ", 1);
                    }
                    $req = ", priseRdvExpertise=$priseRdvExpertise, datePriseRdvExpertise=NULL, idAuteurPriseRdvExpertise =NULL";
                    manageActivity($idOP, 15, 'True', $opName, "Prise de RDV Expertise n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = " priseRdvExpertise=NULL, datePriseRdvExpertise=NULL, idAuteurPriseRdvExpertise =NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(15)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 15, $auteur, $idUtilisateur, $priseRdvExpertiseCom);

                if ($priseRdvExpertise == "1") {
                    $ok = "0";
                    $today = date('Y-m-d H:i:s');
                    setlocale(LC_ALL, "fr_FR");

                    $dateRV = str_replace(" ", "", $dateRV);
                    if (str_contains($heureDebut, "-")) {
                        $heureFin = explode("-", $heureDebut)[1];
                        $heureDebut = explode("-", $heureDebut)[0];
                    }

                    if (str_contains($heureDebut, "h")) {
                        $dateRV =  date('Y-m-d');
                        $heureDebut =  date('H:i');
                    }
                    $heureDebut = str_replace(' ', '', $heureDebut);

                    if (substr($dateRV, 4, 1) == "-") {
                    } else {
                        $dateRV = substr($dateRV, 6, 4) . "-"   . substr($dateRV, 3, 2) . "-" . substr($dateRV, 0, 2);
                    }

                    $date = new DateTime($dateRV . ' ' . $heureDebut);
                    if (isset($dateRVFin)) {
                        $dateFin = new DateTime($dateRVFin . ' ' . $heureFin);
                    } else {
                        $dateFin = new DateTime($dateRV . ' ' . $heureDebut);
                        $heureFin = isset($heureFin) ? $heureFin : ($dateFin->modify("+30 minutes"))->format('H:i');
                        $dateFin = new DateTime($dateRV . ' ' . $heureFin);
                    }

                    //TEST IF EXIST IN AGENDA EXPERT
                    $seeInAgenda = isset($seeInAgenda) && $seeInAgenda == "1" ? false : true;
                    $event =  isset($seeInAgenda) && $seeInAgenda == "1" ? "0" : "1";
                    $event =   "0";

                    $db->query("SELECT * FROM wbcc_utilisateur u, wbcc_contact c WHERE u.idContactF= c.idContact AND u.idUtilisateur=$idExpertRvEXP LIMIT 1");
                    $tehExpert =  $db->single();
                    $expert = $tehExpert ? $tehExpert->prenomContact . ' ' . $tehExpert->nomContact : '';
                    $idExpert = $idExpertRvEXP;

                    if ($event == "1") {
                        echo json_encode("2");
                        die;
                    } else {
                        //FIND IF RV
                        $rv = false;
                        if ($idOP != "") {
                            $db->query("SELECT * FROM wbcc_rendez_vous WHERE typeRV='$typeRV' AND idOpportunityF=$idOP  LIMIT 1");
                            $rv = $db->single();
                        }
                        if ($rv) {
                            $db->query("UPDATE wbcc_rendez_vous SET dateRV=:dateRV,heureDebut=:heureDebut, dateFin=:dateFin, heureFin=:heureFin,idAppExtra=:idAppExtra,idAppGuid=:idAppGuid,expert=:expert,idExpertF=:idExpertF,numeroOP=:numeroOP,adresseRV=:adresseRV,nomDO=:nomDO,idRVGuid=:idRVGuid, moyenTechnique=:moyenTechnique, conclusion=:conclusion, idAppConF=:idAppConF, idCampagneF=:idCampagneF, idContactGuidF=:idContactGuidF,idContactF=:idContactF, typeRV=:typeRV, editDate=:editDate WHERE idRV=$rv->idRV");
                        } else {
                            $numeroRV = "RV" . date('dmYHis') . $idOP . $idUtilisateur;
                            $db->query("INSERT INTO wbcc_rendez_vous(numero,dateRV,heureDebut,dateFin,heureFin,idAppExtra,idAppGuid, expert, idExpertF, numeroOP,adresseRV,nomDO,idRVGuid, moyenTechnique, conclusion, idAppConF, idCampagneF,idContactGuidF, idContactF, idOpportunityF, typeRV,createDate, editDate, auteur,idAuteur ) VALUES (:numero,:dateRV,:heureDebut, :dateFin, :heureFin,:idAppExtra,:idAppGuid, :expert, :idExpertF, :numeroOP,:adresseRV,:nomDO,:idRVGuid, :moyenTechnique, :conclusion, :idAppConF, :idCampagneF,:idContactGuidF, :idContactF, :idOpportunityF, :typeRV,:createDate, :editDate, :auteur,:idAuteur)");
                            $db->bind("numero", $numeroRV, null);
                            $db->bind("createDate", $today, null);
                            $db->bind("auteur", $auteur, null);
                            $db->bind("idAuteur", $idUtilisateur, null);
                            $db->bind("idOpportunityF", ($idOP == "") ? null : $idOP, null);
                        }

                        $db->bind("dateRV",  $date->format('Y-m-d'), null);
                        $db->bind("heureDebut", $heureDebut, null);
                        $db->bind("dateFin", $date->format('Y-m-d'), null);
                        $db->bind("heureFin", $heureFin, null);
                        $db->bind("idAppExtra", ($idAppExtra == "") ? null : $idAppExtra, null);
                        $db->bind("idAppGuid", $idAppGuid, null);
                        $db->bind("expert", $expert, null);
                        $db->bind("idExpertF", $idExpert, null);
                        $db->bind("numeroOP", $opName, null);
                        $db->bind("adresseRV", $adresseRV, null);
                        $db->bind("nomDO", $nomDO, null);
                        $db->bind("idRVGuid", $idRVGuid, null);
                        $db->bind("moyenTechnique", $moyenTechnique, null);
                        $db->bind("conclusion", $conclusion, null);
                        $db->bind("idAppConF", ($idAppConF == "" || $idAppConF == "0") ? null : $idAppConF, null);
                        $db->bind("idCampagneF", ($idCampagneF == "") ? null : $idCampagneF, null);
                        $db->bind("idContactGuidF", $idContactGuidF, null);
                        $db->bind("idContactF", $idContact, null);
                        $db->bind("typeRV", $typeRV, null);
                        $db->bind("editDate", $today, null);
                        if ($db->execute()) {
                            if ($rv) {
                                $idRV = $rv->idRV;
                            } else {
                                $idRV =  findItemByColumn("wbcc_rendez_vous", "numero", $numeroRV)->idRV;
                            }
                            $ok = "1";
                        } else {
                            $ok = "0";
                        }
                        if ($ok == "1") {
                            if (isset($_GET['sourceEnreg'])) {

                                $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$idExpert LIMIT 1");
                                $exp = $db->single();
                                //SEND MAIL CONFIRMATION RV
                                $con = findItemByColumn("wbcc_contact", "idContact", $idContact);

                                if ($con) {

                                    //SAVE EVENEMENT RV ON AGENDA
                                    $typeRV = $typeRV == "RTP" ? "RT" : ($typeRV == "RFP" ? "RF" : "TRAVAUX");
                                    $db->query("SELECT * FROM wbcc_evenement_agenda WHERE type='$typeRV' AND idOpportunityF=$idOP  LIMIT 1");
                                    $event = $db->single();
                                    if ($event) {
                                        $db->query("UPDATE wbcc_evenement_agenda SET  organisateur =:organisateur , idOrganisateur=:idOrganisateur, contact=:contact, lieu=:lieu, typeEvenement=:typeEvenement, dateEvenement=:dateEvenement, heureDebutEvenement=:heureDebutEvenement, dateFinEvenement=:dateFinEvenement, heureFinEvenement=:heureFinEvenement, editDate=:editDate, auteur=:auteur, idAuteur=:idAuteur WHERE idEvenementAgenda=:idEvenementAgenda");
                                        $db->bind("idEvenementAgenda", $event->idEvenementAgenda, null);
                                    } else {
                                        $numeroEvent = "EVENT" . date('dmYHis') . $idUtilisateur;
                                        $db->query("INSERT INTO wbcc_evenement_agenda( numeroEvenementAgenda, organisateur, idOrganisateur, contact, lieu, typeEvenement, dateEvenement, heureDebutEvenement, dateFinEvenement, heureFinEvenement, createDate, editDate, auteur, idAuteur, idOpportunityF, type) VALUES (:numeroEvenementAgenda, :organisateur, :idOrganisateur, :contact, :lieu, :typeEvenement, :dateEvenement, :heureDebutEvenement, :dateFinEvenement, :heureFinEvenement, :createDate, :editDate, :auteur, :idAuteur, :idOpportunityF, '$typeRV')");
                                        $db->bind("numeroEvenementAgenda", $numeroEvent, null);
                                        $db->bind("createDate", $today, null);
                                        $db->bind("idOpportunityF", $idOP, null);
                                    }
                                    $db->bind("organisateur", $expert, null);
                                    $db->bind("idOrganisateur", $idExpert, null);
                                    $db->bind("contact", "$con->prenomContact $con->nomContact", null);
                                    $db->bind("lieu", $adresseRV, null);
                                    $db->bind("typeEvenement", "Rendez-Vous", null);
                                    $db->bind("dateEvenement", $date->format('Y-m-d'), null);
                                    $db->bind("heureDebutEvenement", $heureDebut, null);
                                    $db->bind("dateFinEvenement", $dateFin->format('Y-m-d'), null);
                                    $db->bind("heureFinEvenement", $heureFin, null);
                                    $db->bind("editDate", $today, null);
                                    $db->bind("auteur", $auteur, null);
                                    $db->bind("idAuteur", $idUtilisateur, null);
                                    $db->execute();
                                }
                            }
                            $ok = "1";
                        } else {
                            $ok = "0";
                        }
                    }
                }

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew', type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());
            } elseif ($etape == 19) {

                /******** SAVE constat DDE  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                // Délégation
                if ($rvExpertiseFait == "1") {
                    createNote($idOP, $idUtilisateur, $auteur, "Expertise Fait ", "Expertise Fait", 1);
                    $req = ", rvExpertiseFait=1, dateExpertise='$dateExpertise', idAuteurExpertise=$idAuteurExpertise";
                    manageActivity($idOP, 18, 'True', $opName, "Faire Expertise contradictoire ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($rvExpertiseFait == "0") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire Expertise contradictoire ", "Faire Expertise contradictoire ", 1);
                    $req = ", rvExpertiseFait=0, dateExpertise=NULL, idAuteurExpertise=NULL";
                    manageActivity($idOP, 18, 'False', $opName, "Faire Expertise contradictoire ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($rvExpertiseFait == "2") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire Expertise contradictoire n'est pas nécéssaire pour ce dossier ", "Faire Expertise contradictoire n'est pas nécéssaire pour ce dossier ", 1);
                    $req = ", rvExpertiseFait=$rvExpertiseFait, dateExpertise=NULL, idAuteurExpertise=NULL";
                    manageActivity($idOP, 18, 'True', $opName, "Faire Expertise contradictoire n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", rvExpertiseFait=NULL, dateExpertise=NULL, idAuteurJustificatifReparation=NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(18)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 18, $auteur, $idUtilisateur, $rvExpertiseFaitCom);

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());

                /******** fin SAVE Constat DDE  *********/
            } elseif ($etape == 20) {

                /******** SAVE constat DDE  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                // Délégation
                if ($retourExpertiseTraite == "1") {
                    createNote($idOP, $idUtilisateur, $auteur,  "Gestion suite Experise contradictoire Fait ", "Gestion suite Experise contradictoire Fait", 1);
                    $req = ", retourExpertiseTraite=1, dateRetourExpertiseTraite='$dateRetourExpertiseTraite', idAuteurRetourExpertiseTraite=$idAuteurRetourExpertiseTraite";
                    manageActivity($idOP, 43, 'True', $opName, "Faire gestion suite Experise contradictoire ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($retourExpertiseTraite == "0") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire gestion suite Experise contradictoire ", "Faire gestion suite Experise contradictoire ", 1);
                    $req = ", retourExpertiseTraite=0, dateRetourExpertiseTraite=NULL, idAuteurRetourExpertiseTraite=NULL";
                    manageActivity($idOP, 43, 'False', $opName, "Faire gestion suite Experise contradictoire ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($retourExpertiseTraite == "2") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire gestion suite Experise contradictoire n'est pas nécéssaire pour ce dossier ", "Faire gestion suite Experise contradictoire n'est pas nécéssaire pour ce dossier ", 1);
                    $req = ", retourExpertiseTraite=$retourExpertiseTraite, dateRetourExpertiseTraite=NULL, idAuteurRetourExpertiseTraite=NULL";
                    manageActivity($idOP, 43, 'True', $opName, "Faire gestion suite Experise contradictoire n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", retourExpertiseTraite=NULL, dateRetourExpertiseTraite=NULL, idAuteurJustificatifReparation=NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(43)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 43, $auteur, $idUtilisateur, $retourExpertiseTraiteCom);

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());

                /******** fin SAVE Constat DDE  *********/
            } elseif ($etape == 21) {

                /******** SAVE Signature délégation  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $nomDocument = '';
                if (isset($_FILES['fichierLettreAcceptation'])) {

                    $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                    $nomDocument = $opportunity->name . '_lettreAcceptation.pdf';
                    $urlDocument = $opportunity->name . '_lettreAcceptation.pdf';

                    // 1 - suavegarder le fichier dans document OP
                    //if (!file_exists("../documents/opportunite/".$nomDocument)) {
                    move_uploaded_file($_FILES['fichierLettreAcceptation']['tmp_name'], "../documents/opportunite/" . $nomDocument);
                    //}

                    // 2- enregistrer la référence dans table document et retourner l'id de la ligne inséré
                    $db->query("SELECT * FROM wbcc_document d, wbcc_opportunity_document od WHERE od.idDocumentF= d.idDocument AND urlDocument='$urlDocument' AND od.idOpportunityF=$idOP LIMIT 1");
                    $oldDocument =  $db->single();

                    if ($oldDocument) {
                        $numeroDocument = $oldDocument->numeroDocument;
                        $db->query("UPDATE wbcc_document SET 
                                    nomDocument=:nomDocument, urlDocument=:urlDocument, 
                                    editDate=:editDate, auteur=:auteur, idUtilisateurF=:idUtilisateurF
                                    WHERE idDocument=:idDocument 
                                    ");
                        $db->bind("idDocument", $oldDocument->idDocument, null);
                    } else {
                        $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                        $db->query(" INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, createDate, editDate, auteur, idUtilisateurF,  source, publie) 
                                VALUES (:numeroDocument, :nomDocument, :urlDocument, :createDate, :editDate, :auteur, :idUtilisateurF, :source, :publie) ");

                        $db->bind("numeroDocument", $numeroDocument, null);
                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                        $db->bind("source", 'EXTRANET', null);
                        $db->bind("publie", 1, null);
                    }


                    $db->bind("nomDocument", $nomDocument, null);
                    $db->bind("urlDocument", $urlDocument, null);
                    $db->bind("editDate", date("Y-m-d H:i:s"), null);
                    $db->bind("auteur", $auteur, null);
                    $db->bind("idUtilisateurF", $idUtilisateur, null);
                    $db->execute();

                    // 3- enregistrer dans la table domument OP

                    $db->query("SELECT * FROM wbcc_opportunity_document WHERE numeroOpportunityF =:numeroOpportunityF AND numeroDocumentF =:numeroDocumentF");
                    $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                    $db->bind("numeroDocumentF", $numeroDocument, null);
                    if ($db->single() == false) {
                        $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                        $db->query("SELECT * FROM wbcc_opportunity_document WHERE idOpportunityF =:idOpportunity AND idDocumentF =:idDocument");
                        $db->bind("idOpportunity", $opportunity->idOpportunity, null);
                        $db->bind("idDocument", $document->idDocument, null);
                        if ($db->single() == false) {
                            $db->query("INSERT INTO wbcc_opportunity_document (numeroOpportunityF, numeroDocumentF, idDocumentF, idOpportunityF) VALUES (:numeroOpportunityF, :numeroDocumentF,  :idDocumentF, :idOpportunityF)");
                            $db->bind("numeroDocumentF", $numeroDocument, null);
                            $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                            $db->bind("idDocumentF", $document->idDocument, null);
                            $db->bind("idOpportunityF", $opportunity->idOpportunity, null);
                            $db->execute();
                        }
                    }
                }

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";
                // Délégation
                if ($relanceLettreAcceptation == "1") {
                    if ($relanceLettreAcceptation != $opportunity->relanceLettreAcceptation) {
                        createNote($idOP, $idUtilisateur, $auteur, "Relance compagnie pour avoir lettre d’acceptation fait ", "Relance compagnie pour avoir lettre d’acceptation fait", 1);
                    }
                    $req = ", relanceLettreAcceptation=1, dateLettreAcceptation='$dateLettreAcceptation', idAuteurLettreAcceptation =$idAuteurLettreAcceptation";
                    manageActivity($idOP, 21, 'True', $opName, "Faire Relance compagnie pour avoir lettre d’acceptation ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($relanceLettreAcceptation == "0") {
                    if ($relanceLettreAcceptation != $opportunity->relanceLettreAcceptation) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire Relance compagnie pour avoir lettre d’acceptation ", "Faire Relance compagnie pour avoir lettre d’acceptation ", 1);
                    }
                    $req = ", relanceLettreAcceptation=0, dateLettreAcceptation=NULL, idAuteurLettreAcceptation =NULL";
                    manageActivity($idOP, 21, 'False', $opName, "Faire Relance compagnie pour avoir lettre d’acceptation ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($relanceLettreAcceptation == "2") {
                    if ($relanceLettreAcceptation != $opportunity->relanceLettreAcceptation) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire Relance compagnie pour avoir lettre d’acceptation n'est pas nécéssaire pour ce dossier ", "Faire Relance compagnie pour avoir lettre d’acceptation n'est pas nécéssaire pour ce dossier ", 1);
                    }
                    $req = ", relanceLettreAcceptation=$relanceLettreAcceptation, dateLettreAcceptation=NULL, idAuteurLettreAcceptation =NULL";
                    manageActivity($idOP, 21, 'True', $opName, "Faire Relance compagnie pour avoir lettre d’acceptation n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", relanceLettreAcceptation=NULL, dateLettreAcceptation=NULL, idAuteurLettreAcceptation =NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(21)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 21, $auteur, $idUtilisateur, $relanceLettreAcceptationCom);

                if ($relanceLettreAcceptation == "1") {
                    /************** Mise à jour rapport FRT *********/
                    if ($nomDocument != '') {
                        $db->query("UPDATE `wbcc_opportunity` SET fichierLettreAcceptation = '$nomDocument' WHERE idOpportunity = $idOP");
                        $db->execute();
                    }

                    /******** fin SAVE Signature délégation  *********/
                }

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());
            } elseif ($etape == 22) {

                /******** SAVE constat DDE  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                // Délégation
                if ($relanceCiePaiementImmediat == "1") {
                    createNote($idOP, $idUtilisateur, $auteur,  "Relance Cie pour paiement immédiat Fait ", "Relance Cie pour paiement immédiat Fait", 1);
                    $req = ", relanceCiePaiementImmediat=1, dateRelanceCiePaiementImmediat='$dateRelanceCiePaiementImmediat', idAuteurRelanceCiePaiementImmediat=$idAuteurRelanceCiePaiementImmediat";
                    manageActivity($idOP, 21, 'True', $opName, "Faire Relance Cie pour paiement immédiat ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($relanceCiePaiementImmediat == "0") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire Relance Cie pour paiement immédiat ", "Faire Relance Cie pour paiement immédiat ", 1);
                    $req = ", relanceCiePaiementImmediat=0, dateRelanceCiePaiementImmediat=NULL, idAuteurRelanceCiePaiementImmediat=NULL";
                    manageActivity($idOP, 21, 'False', $opName, "Faire Relance Cie pour paiement immédiat ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($relanceCiePaiementImmediat == "2") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire Relance Cie pour paiement immédiat n'est pas nécéssaire pour ce dossier ", "Faire Relance Cie pour paiement immédiat n'est pas nécéssaire pour ce dossier ", 1);
                    $req = ", relanceCiePaiementImmediat=$relanceCiePaiementImmediat, dateRelanceCiePaiementImmediat=NULL, idAuteurRelanceCiePaiementImmediat=NULL";
                    manageActivity($idOP, 21, 'True', $opName, "Faire Relance Cie pour paiement immédiat n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", relanceCiePaiementImmediat=NULL, dateRelanceCiePaiementImmediat=NULL, idAuteurJustificatifReparation=NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(21)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 21, $auteur, $idUtilisateur, $relanceCiePaiementImmediatCom);

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());

                /******** fin SAVE Constat DDE  *********/
            } elseif ($etape == 23) {

                /******** SAVE Signature délégation  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $nomDocument = '';
                if (isset($_FILES['fileBonCommande'])) {

                    $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                    $nomDocument = "BON_COMMANDE_" . $opportunity->name . ".pdf";
                    $urlDocument = "BON_COMMANDE_" . $opportunity->name . ".pdf";

                    // 1 - suavegarder le fichier dans document OP
                    //if (!file_exists("../documents/opportunite/".$nomDocument)) {
                    move_uploaded_file($_FILES['fileBonCommande']['tmp_name'], "../documents/opportunite/" . $nomDocument);
                    //}

                    // 2- enregistrer la référence dans table document et retourner l'id de la ligne inséré
                    $db->query("SELECT * FROM wbcc_document d, wbcc_opportunity_document od WHERE od.idDocumentF= d.idDocument AND urlDocument='$urlDocument' AND od.idOpportunityF=$idOP LIMIT 1");
                    $oldDocument =  $db->single();

                    if ($oldDocument) {
                        $numeroDocument = $oldDocument->numeroDocument;
                        $db->query("UPDATE wbcc_document SET 
                                    nomDocument=:nomDocument, urlDocument=:urlDocument, 
                                    editDate=:editDate, auteur=:auteur, idUtilisateurF=:idUtilisateurF
                                    WHERE idDocument=:idDocument 
                                    ");
                        $db->bind("idDocument", $oldDocument->idDocument, null);
                    } else {
                        $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                        $db->query(" INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, createDate, editDate, auteur, idUtilisateurF,  source, publie) 
                                VALUES (:numeroDocument, :nomDocument, :urlDocument, :createDate, :editDate, :auteur, :idUtilisateurF, :source, :publie) ");

                        $db->bind("numeroDocument", $numeroDocument, null);
                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                        $db->bind("source", 'EXTRANET', null);
                        $db->bind("publie", 1, null);
                    }


                    $db->bind("nomDocument", $nomDocument, null);
                    $db->bind("urlDocument", $urlDocument, null);
                    $db->bind("editDate", date("Y-m-d H:i:s"), null);
                    $db->bind("auteur", $auteur, null);
                    $db->bind("idUtilisateurF", $idUtilisateur, null);
                    $db->execute();

                    // 3- enregistrer dans la table domument OP

                    $db->query("SELECT * FROM wbcc_opportunity_document WHERE numeroOpportunityF =:numeroOpportunityF AND numeroDocumentF =:numeroDocumentF");
                    $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                    $db->bind("numeroDocumentF", $numeroDocument, null);
                    if ($db->single() == false) {
                        $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                        $db->query("SELECT * FROM wbcc_opportunity_document WHERE idOpportunityF =:idOpportunity AND idDocumentF =:idDocument");
                        $db->bind("idOpportunity", $opportunity->idOpportunity, null);
                        $db->bind("idDocument", $document->idDocument, null);
                        if ($db->single() == false) {
                            $db->query("INSERT INTO wbcc_opportunity_document (numeroOpportunityF, numeroDocumentF, idDocumentF, idOpportunityF) VALUES (:numeroOpportunityF, :numeroDocumentF,  :idDocumentF, :idOpportunityF)");
                            $db->bind("numeroDocumentF", $numeroDocument, null);
                            $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                            $db->bind("idDocumentF", $document->idDocument, null);
                            $db->bind("idOpportunityF", $opportunity->idOpportunity, null);
                            $db->execute();
                        }
                    }
                }

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";
                // Délégation
                if ($priseRvTravaux == "1") {
                    if ($priseRvTravaux != $opportunity->priseRvTravaux) {
                        createNote($idOP, $idUtilisateur, $auteur, "Appeler le sinistré pour programmer RDV travaux fait ", "Appeler le sinistré pour programmer RDV travaux fait", 1);
                    }
                    $req = ", priseRvTravaux=1, datePriseRvTravaux='$datePriseRvTravaux', idAuteurPriseRvTravaux =$idAuteurPriseRvTravaux";
                    manageActivity($idOP, 23, 'True', $opName, "Faire Appeler le sinistré pour programmer RDV travaux ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($priseRvTravaux == "0") {
                    if ($priseRvTravaux != $opportunity->priseRvTravaux) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire Appeler le sinistré pour programmer RDV travaux ", "Faire Appeler le sinistré pour programmer RDV travaux ", 1);
                    }
                    $req = ", priseRvTravaux=0, datePriseRvTravaux=NULL, idAuteurPriseRvTravaux =NULL";
                    manageActivity($idOP, 23, 'False', $opName, "Faire Appeler le sinistré pour programmer RDV travaux ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($priseRvTravaux == "2") {
                    if ($priseRvTravaux != $opportunity->priseRvTravaux) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire Appeler le sinistré pour programmer RDV travaux n'est pas nécéssaire pour ce dossier ", "Faire Appeler le sinistré pour programmer RDV travaux n'est pas nécéssaire pour ce dossier ", 1);
                    }
                    $req = ", priseRvTravaux=$priseRvTravaux, datePriseRvTravaux=NULL, idAuteurPriseRvTravaux =NULL";
                    manageActivity($idOP, 23, 'True', $opName, "Faire Appeler le sinistré pour programmer RDV travaux n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", priseRvTravaux=NULL, datePriseRvTravaux=NULL, idAuteurPriseRvTravaux =NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(23)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 23, $auteur, $idUtilisateur, $priseRvTravauxCom);

                if ($priseRvTravaux == "1") {
                    $ok = "0";
                    $today = date('Y-m-d H:i:s');
                    setlocale(LC_ALL, "fr_FR");

                    $dateRV = str_replace(" ", "", $dateRV);
                    if (str_contains($heureDebut, "-")) {
                        $heureFin = explode("-", $heureDebut)[1];
                        $heureDebut = explode("-", $heureDebut)[0];
                    }

                    if (str_contains($heureDebut, "h")) {
                        $dateRV =  date('Y-m-d');
                        $heureDebut =  date('H:i');
                    }
                    $heureDebut = str_replace(' ', '', $heureDebut);

                    if (substr($dateRV, 4, 1) == "-") {
                    } else {
                        $dateRV = substr($dateRV, 6, 4) . "-"   . substr($dateRV, 3, 2) . "-" . substr($dateRV, 0, 2);
                    }
                    $isSendMail = false;
                    $date = new DateTime($dateRV . ' ' . $heureDebut);
                    $jour = my_dateEnFrancais($date->format('Y-m-d'), 'd');
                    if (isset($dateRVFin)) {
                        $dateFin = new DateTime($dateRVFin . ' ' . $heureFin);
                    } else {
                        $dateFin = new DateTime($dateRV . ' ' . $heureDebut);
                        $heureFin = isset($heureFin) ? $heureFin : ($dateFin->modify("+30 minutes"))->format('H:i');
                        $dateFin = new DateTime($dateRV . ' ' . $heureFin);
                    }
                    $jourFin = my_dateEnFrancais($dateFin->format('Y-m-d'), 'd');


                    //TEST IF EXIST IN AGENDA EXPERT
                    $seeInAgenda = isset($seeInAgenda) && $seeInAgenda == "1" ? false : true;
                    $event =  isset($seeInAgenda) && $seeInAgenda == "1" ? "0" : "1";
                    $event =   "0";

                    if ($event == "1") {
                        echo json_encode("2");
                        die;
                    } else {
                        //FIND IF RV
                        $rv = false;
                        if ($idOP != "") {
                            $db->query("SELECT * FROM wbcc_rendez_vous WHERE typeRV='$typeRV' AND idOpportunityF=$idOP  LIMIT 1");
                            $rv = $db->single();
                        }
                        if ($rv) {
                            $db->query("UPDATE wbcc_rendez_vous SET dateRV=:dateRV,heureDebut=:heureDebut, dateFin=:dateFin, heureFin=:heureFin,idAppExtra=:idAppExtra,idAppGuid=:idAppGuid,expert=:expert,idExpertF=:idExpertF,numeroOP=:numeroOP,adresseRV=:adresseRV,nomDO=:nomDO,idRVGuid=:idRVGuid, moyenTechnique=:moyenTechnique, conclusion=:conclusion, idAppConF=:idAppConF, idCampagneF=:idCampagneF, idContactGuidF=:idContactGuidF,idContactF=:idContactF, typeRV=:typeRV, editDate=:editDate WHERE idRV=$rv->idRV");
                        } else {
                            $numeroRV = "RV" . date('dmYHis') . $idOP . $idUtilisateur;
                            $db->query("INSERT INTO wbcc_rendez_vous(numero,dateRV,heureDebut,dateFin,heureFin,idAppExtra,idAppGuid, expert, idExpertF, numeroOP,adresseRV,nomDO,idRVGuid, moyenTechnique, conclusion, idAppConF, idCampagneF,idContactGuidF, idContactF, idOpportunityF, typeRV,createDate, editDate, auteur,idAuteur ) VALUES (:numero,:dateRV,:heureDebut, :dateFin, :heureFin,:idAppExtra,:idAppGuid, :expert, :idExpertF, :numeroOP,:adresseRV,:nomDO,:idRVGuid, :moyenTechnique, :conclusion, :idAppConF, :idCampagneF,:idContactGuidF, :idContactF, :idOpportunityF, :typeRV,:createDate, :editDate, :auteur,:idAuteur)");
                            $db->bind("numero", $numeroRV, null);
                            $db->bind("createDate", $today, null);
                            $db->bind("auteur", $auteur, null);
                            $db->bind("idAuteur", $idUtilisateur, null);
                            $db->bind("idOpportunityF", ($idOP == "") ? null : $idOP, null);
                        }

                        $db->bind("dateRV",  $date->format('Y-m-d'), null);
                        $db->bind("heureDebut", $heureDebut, null);
                        $db->bind("dateFin", $date->format('Y-m-d'), null);
                        $db->bind("heureFin", $heureFin, null);
                        $db->bind("idAppExtra", ($idAppExtra == "") ? null : $idAppExtra, null);
                        $db->bind("idAppGuid", $idAppGuid, null);
                        $db->bind("expert", $expert, null);
                        $db->bind("idExpertF", $idExpert, null);
                        $db->bind("numeroOP", $opName, null);
                        $db->bind("adresseRV", $adresseRV, null);
                        $db->bind("nomDO", $nomDO, null);
                        $db->bind("idRVGuid", $idRVGuid, null);
                        $db->bind("moyenTechnique", $moyenTechnique, null);
                        $db->bind("conclusion", $conclusion, null);
                        $db->bind("idAppConF", ($idAppConF == "" || $idAppConF == "0") ? null : $idAppConF, null);
                        $db->bind("idCampagneF", ($idCampagneF == "") ? null : $idCampagneF, null);
                        $db->bind("idContactGuidF", $idContactGuidF, null);
                        $db->bind("idContactF", $idContact, null);
                        $db->bind("typeRV", $typeRV, null);
                        $db->bind("editDate", $today, null);
                        if ($db->execute()) {
                            if ($rv) {
                                $idRV = $rv->idRV;
                            } else {
                                $idRV =  findItemByColumn("wbcc_rendez_vous", "numero", $numeroRV)->idRV;
                            }
                            $ok = "1";
                        } else {
                            $ok = "0";
                        }
                        if ($ok == "1") {
                            if (isset($_GET['sourceEnreg'])) {

                                $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContact= idContactF AND idUtilisateur=$idExpert LIMIT 1");
                                $exp = $db->single();
                                //SEND MAIL CONFIRMATION RV
                                $con = findItemByColumn("wbcc_contact", "idContact", $idContact);

                                if ($con) {

                                    //SAVE EVENEMENT RV ON AGENDA
                                    $typeRV = $typeRV == "RTP" ? "RT" : ($typeRV == "RFP" ? "RF" : "TRAVAUX");
                                    $db->query("SELECT * FROM wbcc_evenement_agenda WHERE type='$typeRV' AND idOpportunityF=$idOP  LIMIT 1");
                                    $event = $db->single();
                                    if ($event) {
                                        $db->query("UPDATE wbcc_evenement_agenda SET  organisateur =:organisateur , idOrganisateur=:idOrganisateur, contact=:contact, lieu=:lieu, typeEvenement=:typeEvenement, dateEvenement=:dateEvenement, heureDebutEvenement=:heureDebutEvenement, dateFinEvenement=:dateFinEvenement, heureFinEvenement=:heureFinEvenement, editDate=:editDate, auteur=:auteur, idAuteur=:idAuteur WHERE idEvenementAgenda=:idEvenementAgenda");
                                        $db->bind("idEvenementAgenda", $event->idEvenementAgenda, null);
                                    } else {
                                        $numeroEvent = "EVENT" . date('dmYHis') . $idUtilisateur;
                                        $db->query("INSERT INTO wbcc_evenement_agenda( numeroEvenementAgenda, organisateur, idOrganisateur, contact, lieu, typeEvenement, dateEvenement, heureDebutEvenement, dateFinEvenement, heureFinEvenement, createDate, editDate, auteur, idAuteur, idOpportunityF, type) VALUES (:numeroEvenementAgenda, :organisateur, :idOrganisateur, :contact, :lieu, :typeEvenement, :dateEvenement, :heureDebutEvenement, :dateFinEvenement, :heureFinEvenement, :createDate, :editDate, :auteur, :idAuteur, :idOpportunityF, '$typeRV')");
                                        $db->bind("numeroEvenementAgenda", $numeroEvent, null);
                                        $db->bind("createDate", $today, null);
                                        $db->bind("idOpportunityF", $idOP, null);
                                    }
                                    $db->bind("organisateur", $expert, null);
                                    $db->bind("idOrganisateur", $idExpert, null);
                                    $db->bind("contact", "$con->prenomContact $con->nomContact", null);
                                    $db->bind("lieu", $adresseRV, null);
                                    $db->bind("typeEvenement", "Rendez-Vous", null);
                                    $db->bind("dateEvenement", $date->format('Y-m-d'), null);
                                    $db->bind("heureDebutEvenement", $heureDebut, null);
                                    $db->bind("dateFinEvenement", $dateFin->format('Y-m-d'), null);
                                    $db->bind("heureFinEvenement", $heureFin, null);
                                    $db->bind("editDate", $today, null);
                                    $db->bind("auteur", $auteur, null);
                                    $db->bind("idAuteur", $idUtilisateur, null);
                                    $db->execute();
                                }
                            }
                            $ok = "1";
                        } else {
                            $ok = "0";
                        }
                    }
                }

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());
            } elseif ($etape == 24) {

                /******** SAVE Signature délégation  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $nomDocument = '';
                if (isset($_FILES['filePV'])) {

                    $numeroDocument = "DOC" . date('dmYHis') . 'PV' . $opportunity->idOpportunity;
                    $nomDocument = $docPV;
                    $urlDocument = $docPV;

                    // 1 - suavegarder le fichier dans document OP
                    //if (!file_exists("../documents/opportunite/".$nomDocument)) {
                    move_uploaded_file($_FILES['filePV']['tmp_name'], "../documents/opportunite/" . $nomDocument);
                    //}

                    // 2- enregistrer la référence dans table document et retourner l'id de la ligne inséré
                    $db->query("SELECT * FROM wbcc_document d, wbcc_opportunity_document od WHERE od.idDocumentF= d.idDocument AND urlDocument='$urlDocument' AND od.idOpportunityF=$idOP LIMIT 1");
                    $oldDocument =  $db->single();

                    if ($oldDocument) {
                        $numeroDocument = $oldDocument->numeroDocument;
                        $db->query("UPDATE wbcc_document SET 
                                    nomDocument=:nomDocument, urlDocument=:urlDocument, 
                                    editDate=:editDate, auteur=:auteur, idUtilisateurF=:idUtilisateurF
                                    WHERE idDocument=:idDocument 
                                    ");
                        $db->bind("idDocument", $oldDocument->idDocument, null);
                    } else {
                        $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                        $db->query(" INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, createDate, editDate, auteur, idUtilisateurF,  source, publie) 
                                VALUES (:numeroDocument, :nomDocument, :urlDocument, :createDate, :editDate, :auteur, :idUtilisateurF, :source, :publie) ");

                        $db->bind("numeroDocument", $numeroDocument, null);
                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                        $db->bind("source", 'EXTRANET', null);
                        $db->bind("publie", 1, null);
                    }


                    $db->bind("nomDocument", $nomDocument, null);
                    $db->bind("urlDocument", $urlDocument, null);
                    $db->bind("editDate", date("Y-m-d H:i:s"), null);
                    $db->bind("auteur", $auteur, null);
                    $db->bind("idUtilisateurF", $idUtilisateur, null);
                    $db->execute();

                    // 3- enregistrer dans la table domument OP

                    $db->query("SELECT * FROM wbcc_opportunity_document WHERE numeroOpportunityF =:numeroOpportunityF AND numeroDocumentF =:numeroDocumentF");
                    $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                    $db->bind("numeroDocumentF", $numeroDocument, null);
                    if ($db->single() == false) {
                        $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                        $db->query("SELECT * FROM wbcc_opportunity_document WHERE idOpportunityF =:idOpportunity AND idDocumentF =:idDocument");
                        $db->bind("idOpportunity", $opportunity->idOpportunity, null);
                        $db->bind("idDocument", $document->idDocument, null);
                        if ($db->single() == false) {
                            $db->query("INSERT INTO wbcc_opportunity_document (numeroOpportunityF, numeroDocumentF, idDocumentF, idOpportunityF) VALUES (:numeroOpportunityF, :numeroDocumentF,  :idDocumentF, :idOpportunityF)");
                            $db->bind("numeroDocumentF", $numeroDocument, null);
                            $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                            $db->bind("idDocumentF", $document->idDocument, null);
                            $db->bind("idOpportunityF", $opportunity->idOpportunity, null);
                            $db->execute();
                        }
                    }
                }

                if (isset($_FILES['fileCERFA'])) {

                    $numeroDocument = "DOC" . date('dmYHis') . 'CERPA' . $opportunity->idOpportunity;
                    $nomDocument = $docCerfa;
                    $urlDocument = $docCerfa;

                    // 1 - suavegarder le fichier dans document OP
                    //if (!file_exists("../documents/opportunite/".$nomDocument)) {
                    move_uploaded_file($_FILES['fileCERFA']['tmp_name'], "../documents/opportunite/" . $nomDocument);
                    //}

                    // 2- enregistrer la référence dans table document et retourner l'id de la ligne inséré
                    $db->query("SELECT * FROM wbcc_document d, wbcc_opportunity_document od WHERE od.idDocumentF= d.idDocument AND urlDocument='$urlDocument' AND od.idOpportunityF=$idOP LIMIT 1");
                    $oldDocument =  $db->single();

                    if ($oldDocument) {
                        $numeroDocument = $oldDocument->numeroDocument;
                        $db->query("UPDATE wbcc_document SET 
                                    nomDocument=:nomDocument, urlDocument=:urlDocument, 
                                    editDate=:editDate, auteur=:auteur, idUtilisateurF=:idUtilisateurF
                                    WHERE idDocument=:idDocument 
                                    ");
                        $db->bind("idDocument", $oldDocument->idDocument, null);
                    } else {
                        $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                        $db->query(" INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, createDate, editDate, auteur, idUtilisateurF,  source, publie) 
                                VALUES (:numeroDocument, :nomDocument, :urlDocument, :createDate, :editDate, :auteur, :idUtilisateurF, :source, :publie) ");

                        $db->bind("numeroDocument", $numeroDocument, null);
                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                        $db->bind("source", 'EXTRANET', null);
                        $db->bind("publie", 1, null);
                    }


                    $db->bind("nomDocument", $nomDocument, null);
                    $db->bind("urlDocument", $urlDocument, null);
                    $db->bind("editDate", date("Y-m-d H:i:s"), null);
                    $db->bind("auteur", $auteur, null);
                    $db->bind("idUtilisateurF", $idUtilisateur, null);
                    $db->execute();

                    // 3- enregistrer dans la table domument OP

                    $db->query("SELECT * FROM wbcc_opportunity_document WHERE numeroOpportunityF =:numeroOpportunityF AND numeroDocumentF =:numeroDocumentF");
                    $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                    $db->bind("numeroDocumentF", $numeroDocument, null);
                    if ($db->single() == false) {
                        $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                        $db->query("SELECT * FROM wbcc_opportunity_document WHERE idOpportunityF =:idOpportunity AND idDocumentF =:idDocument");
                        $db->bind("idOpportunity", $opportunity->idOpportunity, null);
                        $db->bind("idDocument", $document->idDocument, null);
                        if ($db->single() == false) {
                            $db->query("INSERT INTO wbcc_opportunity_document (numeroOpportunityF, numeroDocumentF, idDocumentF, idOpportunityF) VALUES (:numeroOpportunityF, :numeroDocumentF,  :idDocumentF, :idOpportunityF)");
                            $db->bind("numeroDocumentF", $numeroDocument, null);
                            $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                            $db->bind("idDocumentF", $document->idDocument, null);
                            $db->bind("idOpportunityF", $opportunity->idOpportunity, null);
                            $db->execute();
                        }
                    }
                }

                if (isset($_FILES['fileES'])) {

                    $numeroDocument = "DOC" . date('dmYHis') . 'ES' . $opportunity->idOpportunity;
                    $nomDocument = $docEnquete;
                    $urlDocument = $docEnquete;

                    // 1 - suavegarder le fichier dans document OP
                    //if (!file_exists("../documents/opportunite/".$nomDocument)) {
                    move_uploaded_file($_FILES['fileES']['tmp_name'], "../documents/opportunite/" . $nomDocument);
                    //}

                    // 2- enregistrer la référence dans table document et retourner l'id de la ligne inséré
                    $db->query("SELECT * FROM wbcc_document d, wbcc_opportunity_document od WHERE od.idDocumentF= d.idDocument AND urlDocument='$urlDocument' AND od.idOpportunityF=$idOP LIMIT 1");
                    $oldDocument =  $db->single();

                    if ($oldDocument) {
                        $numeroDocument = $oldDocument->numeroDocument;
                        $db->query("UPDATE wbcc_document SET 
                                    nomDocument=:nomDocument, urlDocument=:urlDocument, 
                                    editDate=:editDate, auteur=:auteur, idUtilisateurF=:idUtilisateurF
                                    WHERE idDocument=:idDocument 
                                    ");
                        $db->bind("idDocument", $oldDocument->idDocument, null);
                    } else {
                        $numeroDocument = "DOC" . date('dmYHis') . $opportunity->idOpportunity;
                        $db->query(" INSERT INTO wbcc_document (numeroDocument, nomDocument,urlDocument, createDate, editDate, auteur, idUtilisateurF,  source, publie) 
                                VALUES (:numeroDocument, :nomDocument, :urlDocument, :createDate, :editDate, :auteur, :idUtilisateurF, :source, :publie) ");

                        $db->bind("numeroDocument", $numeroDocument, null);
                        $db->bind("createDate", date("Y-m-d H:i:s"), null);
                        $db->bind("source", 'EXTRANET', null);
                        $db->bind("publie", 1, null);
                    }


                    $db->bind("nomDocument", $nomDocument, null);
                    $db->bind("urlDocument", $urlDocument, null);
                    $db->bind("editDate", date("Y-m-d H:i:s"), null);
                    $db->bind("auteur", $auteur, null);
                    $db->bind("idUtilisateurF", $idUtilisateur, null);
                    $db->execute();

                    // 3- enregistrer dans la table domument OP

                    $db->query("SELECT * FROM wbcc_opportunity_document WHERE numeroOpportunityF =:numeroOpportunityF AND numeroDocumentF =:numeroDocumentF");
                    $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                    $db->bind("numeroDocumentF", $numeroDocument, null);
                    if ($db->single() == false) {
                        $document = findItemByColumn("wbcc_document", "numeroDocument", $numeroDocument);
                        $db->query("SELECT * FROM wbcc_opportunity_document WHERE idOpportunityF =:idOpportunity AND idDocumentF =:idDocument");
                        $db->bind("idOpportunity", $opportunity->idOpportunity, null);
                        $db->bind("idDocument", $document->idDocument, null);
                        if ($db->single() == false) {
                            $db->query("INSERT INTO wbcc_opportunity_document (numeroOpportunityF, numeroDocumentF, idDocumentF, idOpportunityF) VALUES (:numeroOpportunityF, :numeroDocumentF,  :idDocumentF, :idOpportunityF)");
                            $db->bind("numeroDocumentF", $numeroDocument, null);
                            $db->bind("numeroOpportunityF", $opportunity->numeroOpportunity, null);
                            $db->bind("idDocumentF", $document->idDocument, null);
                            $db->bind("idOpportunityF", $opportunity->idOpportunity, null);
                            $db->execute();
                        }
                    }
                }

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";
                // Délégation
                if ($faireRvTravaux == "1") {
                    if ($faireRvTravaux != $opportunity->faireRvTravaux) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire travaux fait ", "Faire travaux fait", 1);
                    }
                    $req = ", faireRvTravaux=1, dateFaireRvTravaux='$dateFaireRvTravaux', idAuteurFaireRvTravaux =$idAuteurFaireRvTravaux";
                    manageActivity($idOP, 45, 'True', $opName, "Faire travaux ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($faireRvTravaux == "0") {
                    if ($faireRvTravaux != $opportunity->faireRvTravaux) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire travaux ", "Faire travaux ", 1);
                    }
                    $req = ", faireRvTravaux=0, dateFaireRvTravaux=NULL, idAuteurFaireRvTravaux =NULL";
                    manageActivity($idOP, 45, 'False', $opName, "Faire travaux ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($faireRvTravaux == "2") {
                    if ($faireRvTravaux != $opportunity->faireRvTravaux) {
                        createNote($idOP, $idUtilisateur, $auteur, "Faire travaux n'est pas nécéssaire pour ce dossier ", "Faire travaux n'est pas nécéssaire pour ce dossier ", 1);
                    }
                    $req = ", faireRvTravaux=$faireRvTravaux, dateFaireRvTravaux=NULL, idAuteurFaireRvTravaux =NULL";
                    manageActivity($idOP, 45, 'True', $opName, "Faire travaux n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", faireRvTravaux=NULL, dateFaireRvTravaux=NULL, idAuteurFaireRvTravaux =NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(45)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 45, $auteur, $idUtilisateur, $faireRvTravauxCom);

                if ($faireRvTravaux == "1") {

                    $travaux = findItemByColumn("wbcc_travaux", "idOpportunityF", $idOP);
                    if ($travaux) {
                        $db->query("UPDATE wbcc_travaux SET libelleTravaux=:libelleTravaux,dateDebutReelle=:dateDebutReelle,
                    dateFinReelle=:dateFinReelle,fichier_pv=:fichier_pv,fichier_cerfa=:fichier_cerfa,fichier_enquete=:fichier_enquete, 
                    fichier_Bon_Commande=:fichier_Bon_Commande, editDate=:editDate, idAuteur=:idAuteur, auteur=:auteur WHERE idTravaux=:idTravaux");
                        $db->bind("idTravaux", "$travaux->idTravaux", null);
                    } else {
                        $numeroTravaux = "TRAVAUX" . date('dmYHis') . $idOP . $idUtilisateur;
                        $db->query("INSERT INTO wbcc_travaux(numeroTravaux, libelleTravaux, dateDebutReelle, dateFinReelle, fichier_pv, 
                    fichier_cerfa, fichier_enquete, fichier_Bon_Commande, idOpportunityF, createDate, editDate, idAuteur, auteur) VALUES 
                    (:numeroTravaux, :libelleTravaux, :dateDebutReelle, :dateFinReelle, :fichier_pv, :fichier_cerfa, 
                    :fichier_enquete, :fichier_Bon_Commande, :idOpportunityF, :createDate, :editDate, :idAuteur, :auteur)");
                        $db->bind("numeroTravaux", "$numeroTravaux", null);
                        $db->bind("createDate", date("Y-m-d H:i"), null);
                        $db->bind("idOpportunityF", $idOP, null);
                    }
                    $db->bind("libelleTravaux", "TRAVAUX $opName", null);
                    $db->bind("dateFinReelle", "$dateFin $heureFin", null);
                    $db->bind("dateDebutReelle", "$dateDebut $heureDebut", null);
                    $db->bind("fichier_enquete", $docEnquete, null);
                    $db->bind("fichier_cerfa", $docCerfa, null);
                    $db->bind("fichier_pv", $docPV, null);
                    $db->bind("fichier_Bon_Commande", $docBonCommande, null);
                    $db->bind("editDate", date("Y-m-d H:i"), null);
                    $db->bind("idAuteur", $idUtilisateur, null);
                    $db->bind("auteur", $auteur, null);
                    $db->execute();
                }

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                echo json_encode($db->execute());
            } elseif ($etape == 25) {

                /******** SAVE constat DDE  *********/
                $opportunity = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);

                $req = "";
                $today = date("Y-m-d H:i:s");
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $etapeAuditeAncienneOpWew = "";

                // Délégation
                if ($relanceCiePaiementDiffere == "1") {
                    createNote($idOP, $idUtilisateur, $auteur,  "Relance compagnie pour paiement différé Fait ", "Relance compagnie pour paiement différé Fait", 1);
                    $req = ", relanceCiePaiementDiffere=1, dateRelanceCiePaiementDiffere='$dateRelanceCiePaiementDiffere', idAuteurRelanceCiePaiementDiffere=$idAuteurRelanceCiePaiementDiffere";
                    manageActivity($idOP, 37, 'True', $opName, "Faire Relance compagnie pour paiement différé ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($relanceCiePaiementDiffere == "0") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire Relance compagnie pour paiement différé ", "Faire Relance compagnie pour paiement différé ", 1);
                    $req = ", relanceCiePaiementDiffere=0, dateRelanceCiePaiementDiffere=NULL, idAuteurRelanceCiePaiementDiffere=NULL";
                    manageActivity($idOP, 37, 'False', $opName, "Faire Relance compagnie pour paiement différé ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } elseif ($relanceCiePaiementDiffere == "2") {
                    createNote($idOP, $idUtilisateur, $auteur, "Faire Relance compagnie pour paiement différé n'est pas nécéssaire pour ce dossier ", "Faire Relance compagnie pour paiement différé n'est pas nécéssaire pour ce dossier ", 1);
                    $req = ", relanceCiePaiementDiffere=$relanceCiePaiementDiffere, dateRelanceCiePaiementDiffere=NULL, idAuteurRelanceCiePaiementDiffere=NULL";
                    manageActivity($idOP, 37, 'True', $opName, "Faire Relance compagnie pour paiement différé n'est pas nécéssaire pour ce dossier ");
                    $etapeAuditeAncienneOpWew = "$etape";
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                } else {
                    /*$req = ", relanceCiePaiementDiffere=NULL, dateRelanceCiePaiementDiffere=NULL, idAuteurJustificatifReparation=NULL";*/
                    //setEtatActivitiesOP($idOP, 'True', '(37)');
                    foreach ($etapeAuditeAncienneOpArray as $oneLine) {
                        if ($oneLine != $etape && $oneLine != '') {
                            $etapeAuditeAncienneOpWew .= ";$oneLine";
                        }
                    }
                }

                saveCommentaireAsNote($idOP, $numeroOpportunity, 37, $auteur, $idUtilisateur, $relanceCiePaiementDiffereCom);

                $db->query("UPDATE `wbcc_opportunity` SET etapeAuditeAncienneOp = '$etapeAuditeAncienneOpWew',type='Sinistres' $req WHERE idOpportunity = $idOP");
                $db->execute();

                /************** Close audit ********** */

                $opClose = findItemByColumn("wbcc_opportunity", "idOpportunity", $idOP);
                $etapeAuditeAncienneOp = $opportunity->etapeAuditeAncienneOp;
                $etapeAuditeAncienneOpArray = explode(';', $etapeAuditeAncienneOp);
                $close = true;
                for ($i = 1; $i <= 25; $i++) {
                    if (! in_array($i, $etapeAuditeAncienneOpArray)) {
                        $close = false;
                        exit;
                    }
                }
                if ($close) {
                    $db->query("UPDATE `wbcc_opportunity` SET auditAncienneOp = 1 WHERE idOpportunity = $idOP");
                    echo json_encode($db->execute());
                } else {
                    echo json_encode('ok');
                }

                /******** fin SAVE Constat DDE  *********/
            }
        }
    }

    if ($action == "AddOrUpdateExpertCompany") {

        extract($_POST);
        if (isset($contactnew)  && $contactnew != "") {
            $idContactnew = explode(',', $contactnew)[0];
            $numeroContactnew = explode(',', $contactnew)[1];
        }

        $fullname = $prenomContact . " " . $nomContact;
        if ($action == "update") {
            $db->query("UPDATE `wbcc_contact` SET civiliteContact='$civiliteContact', nomContact = '$nomContact', prenomContact = '$prenomContact', fullname = '$fullname', telContact = '$telContact', emailContact = '$emailContact', dateNaissance='$dateNaissanceContact' WHERE idContact = '$idContact'");
            $result = $db->execute();
        } else if ($action == "new") {
            $db->query("INSERT INTO wbcc_contact_opportunity (idContactF, numeroContactF, idOpportunityF, numeroOpportunityF) VALUES ($idContactnew,'$numeroContactnew',$idOp, '$numeroOP')");
            $result = $db->execute();

            $db->query("UPDATE `wbcc_opportunity` SET idExpertCompanyF='$idContactnew', nomExpertCompany = '$fullname' WHERE idOpportunity = '$idOp'");
            $result = $db->execute();
        } else {
            $db->query("DELETE FROM wbcc_contact_opportunity WHERE idOpportunityF = $idOp AND idContactF = $idContact");
            $result = $db->execute();

            $db->query("INSERT INTO wbcc_contact_opportunity (idContactF, numeroContactF, idOpportunityF, numeroOpportunityF) VALUES ($idContactnew,'$numeroContactnew',$idOp, '$numeroOP')");
            $result = $db->execute();

            $db->query("UPDATE `wbcc_opportunity` SET idExpertCompanyF='$idContactnew', nomExpertCompany = '$fullname' WHERE idOpportunity = '$idOp'");
            $result = $db->execute();
        }


        /*$req = "";
            if ($typeDO == "Particuliers") {
                $req = ", nomDO='$fullname'";
            }
            $db->query("UPDATE `wbcc_opportunity` SET guidContactClient = '$numeroContactnew', contactClient='$fullname' $req WHERE idOpportunity=$idOp ");
            $result = $db->execute();*/

        echo json_encode($result);
    }

    if ($action == "saveContact") {
        extract($_POST);

        $sql = "  SELECT * FROM wbcc_contact
                    WHERE (telContact = '" . $telContact . "' AND (telContact <> '' OR telContact <> NULL OR telContact <> ' '))
                    OR    (emailContact = '" . $emailContact . "' AND (emailContact <> '' OR emailContact <> NULL OR emailContact <> ' '))
                ";
        $db->query($sql);
        $chekDoublon = $db->resultSet();
        if (empty($chekDoublon)) {
            $numeroContact = 'CON' . date('dmYHis') . $idAuteur;
            $fullName = $prenomContact . " " . $nomContact;
            $sql = " INSERT INTO wbcc_contact(
                    numeroContact, 
                    civiliteContact,
                    nomContact, 
                    prenomContact, 
                    fullName, 
                    telContact, 
                    mobilePhone, 
                    emailContact, 
                    emailCollaboratif, 
                    statutContact, 
                    adresseContact, 
                    codePostalContact, 
                    villeContact, 
                    departement, 
                    codePorte, 
                    batiment, 
                    etage
                    ) 
                VALUES (
                    :numeroContact, 
                    :civiliteContact, 
                    :nomContact, 
                    :prenomContact, 
                    :fullName, 
                    :telContact, 
                    :mobilePhone, 
                    :emailContact, 
                    :emailCollaboratif, 
                    :statutContact, 
                    :adresseContact, 
                    :codePostalContact, 
                    :villeContact, 
                    :departement, 
                    :codePorte, 
                    :batiment, 
                    :etage  
                )";

            $db->query($sql);
            $db->bind('numeroContact', $numeroContact, null);
            $db->bind('civiliteContact', $civiliteContact, null);
            $db->bind('nomContact', $nomContact, null);
            $db->bind('prenomContact', $prenomContact, null);
            $db->bind('fullName', $fullName, null);
            $db->bind('telContact', $telContact, null);
            $db->bind('mobilePhone', $mobilePhone, null);
            $db->bind('emailContact', $emailContact, null);
            $db->bind('emailCollaboratif', $emailCollaboratif, null);
            $db->bind('statutContact', $statutContact, null);
            $db->bind('adresseContact', $adresseContact, null);
            $db->bind('codePostalContact', $codePostalContact, null);
            $db->bind('villeContact', $villeContact, null);
            $db->bind('departement', $departement, null);
            $db->bind('codePorte', $codePorte, null);
            $db->bind('batiment', $batiment, null);
            $db->bind('etage', $etage, null);

            $db->execute();
            $db->query("SELECT * FROM `wbcc_contact` WHERE numeroContact = '$numeroContact' ");
            $theContact = $db->single();
        } else {
            $theContact = $chekDoublon[0];
        }

        if (!isset($_GET['idCompany']) || $_GET['idCompany'] == 0) {
            $db->query("INSERT INTO wbcc_contact_opportunity (idContactF, numeroContactF, idOpportunityF, numeroOpportunityF) VALUES ($theContact->idContact,'$theContact->numeroContact',$idOp, '$numeroOP')");
            $result = $db->execute();

            $db->query("UPDATE `wbcc_opportunity` SET idExpertCompanyF='$theContact->idContact', nomExpertCompany = '$theContact->fullName' WHERE idOpportunity = '$idOp'");
            $result = $db->execute();
        } elseif ($_GET['idCompany'] != 0) {
            $contact = getItemByGuid("wbcc_contact", "numeroContact", $theContact->numeroContact);
            if (isset($_GET['idCompany'])) {
                $idComp = $_GET['idCompany'];
                if ($idComp != "0") {
                    $db->query("INSERT INTO `wbcc_contact_company`(`idContactF`, `idCompanyF`, `numeroContactF`) VALUES ('$contact->idContact','$idComp','$theContact->numeroContact')");
                    $db->execute();
                }
            }
        }

        echo json_encode($theContact);
    }
    /********************  FIN NEW ESPOIR  ***********************/


    if ($action == "updateAppCnt") {
        $db->query("SELECT * FROM wbcc_opportunity WHERE origine = 'Email' OR origine = 'Téléphone'");
        $data = $db->resultSet();

        foreach ($data as $key => $value) {


            if ($value->status == "OPEN") {
                $db->query("UPDATE wbcc_opportunity SET status = 'Open' WHERE idOpportunity=$value->idOpportunity");
                $db->execute();
            }
            if ($value->typeSinistre = "Partie privative exclusive") {
                $db->query("SELECT * FROM wbcc_opportunity_appartement WHERE idOpportunityF = $value->idOpportunity");
                $lot = $db->single();

                if ($lot) {
                    if ($value->guidContactClient != null  && $value->guidContactClient != "") {
                        $db->query("SELECT * FROM wbcc_contact WHERE numeroContact = '$value->guidContactClient' LIMIT 1");
                        $con = $db->single();
                        if ($con) {
                            $db->query("SELECT * FROM wbcc_appartement_contact WHERE idAppartementF=$lot->idAppartementF and idContactF = $con->idContact LIMIT 1");
                            $find = $db->single();
                            if ($find) {
                            } else {
                                $db->query("INSERT INTO wbcc_appartement_contact (idContactF, idAppartementF) VALUES($con->idContact, $lot->idAppartementF)");
                                $db->execute();
                            }
                        }
                    }
                }
            } else if ($value->typeSinistre = "Partie commune exclusive") {
                $db->query("DELETE FROM wbcc_opportunity_appartement WHERE idOpportunityF = $value->idOpportunity");
                $db->execute();
            }
        }
        echo  json_encode("ok");
    }
}

function saveCommentaireAsNote($idOpportunity, $numeroOpportunity, $etapeOP, $auteur, $idUtilisateur, $commentaire)
{
    if ($commentaire != "" && $commentaire != " ") {
        $db = new Database();
        $db->query("SELECT * FROM wbcc_opportunity_note, wbcc_note WHERE idNote=idNoteF AND idOpportunityF=:idOpportunity AND etapeOP=:etapeOP ");
        $db->bind('idOpportunity', $idOpportunity, null);
        $db->bind('etapeOP', $etapeOP, null);
        $note = $db->single();
        if ($note) {
            $db->query("UPDATE wbcc_note SET plainText=:plainText, noteText=:noteText, auteur=:auteur, idUtilisateurF=:idUtilisateurF WHERE idNote=:idNote");
            $db->bind("plainText", $commentaire, null);
            $db->bind("noteText", $commentaire, null);
            $db->bind("auteur", $auteur);
            $db->bind("idUtilisateurF", $idUtilisateur);
            $db->bind("idNote", $note->idNote, null);
            $db->execute();
        } else {
            $numero = date("YmdHis") . "$idOpportunity-$etapeOP-$auteur";
            $db->query("INSERT INTO wbcc_note (numeroNote,plainText, noteText, source, isPrivate, auteur, idUtilisateurF, etapeOP) VALUES(:numeroNote, :plainText, :noteText, :source, :isPrivate, :auteur, :idUtilisateurF, :etapeOP)");
            $db->bind("source", "EXTRA");
            $db->bind("auteur", $auteur);
            $db->bind("idUtilisateurF", $idUtilisateur);
            $db->bind("isPrivate", 0);
            $db->bind("numeroNote", $numero);
            $db->bind("plainText", $commentaire, null);
            $db->bind("noteText", $commentaire, null);
            $db->bind("etapeOP", $etapeOP, null);
            $db->execute();

            $idNote = findItemByColumn("wbcc_note", "numeroNote", $numero)->idNote;
            $db->query("INSERT INTO wbcc_opportunity_note (idOpportunityF, idNoteF, numeroOpportunityF) VALUES($idOpportunity,$idNote, '$numeroOpportunity')");
            $db->execute();
        }
    }
}

function getNbTacheEffectGest($user, $periode, $date1, $date2, $columnType, $columnAut, $columnDate)
{
    $db = new Database();
    $sql = "";


    if ($columnType == "frtRejet") {
        $sql = "SELECT Count(*) as nbr FROM wbcc_opportunity, wbcc_rendez_vous r WHERE idOpportunity = r.idOpportunityF AND r.etatFRT = 0 ";
        if ($periode != "") {
            $now = date("Y-m-d");
            if ($periode == "today") {
                $sql .= "AND r.$columnDate LIKE '%$now%' ";
            } else if ($periode == "jour") {
                $sql .= "AND r.$columnDate LIKE '%$date1%' ";
            } else if ($periode == "semaine") {
                $sql .= "AND WEEK(r.$columnDate) = WEEK('$now') AND MONTH(r.$columnDate) = MONTH('$now') AND YEAR(r.$columnDate) = YEAR('$now') ";
            } else if ($periode == "mois") {
                $sql .= "AND MONTH(r.$columnDate) = MONTH('$now') AND YEAR(r.$columnDate) = YEAR('$now') ";
            } else if ($periode == "trimestre") {
                $sql .= "AND QUARTER(r.$columnDate) = QUARTER('$now') AND YEAR(r.$columnDate) = YEAR('$now') ";
            } else if ($periode == "semestre") {
                $sql .= "AND (QUARTER(r.$columnDate) = QUARTER('$now') OR QUARTER(r.$columnDate) = QUARTER('$now')+1) AND YEAR(r.$columnDate) = YEAR('$now') ";
            } else if ($periode == "annuel") {
                $sql .= "AND YEAR(r.$columnDate) = YEAR('$now') ";
            } else {
                $sql .= " AND r.$columnDate > '$date1' AND  r.$columnDate < '$date2' ";
            }
        }

        if ($user != "") {

            $sql .= " AND r.$columnAut = $user ; ";
        }
    } else {
        $sql = "SELECT Count(*) as nbr FROM wbcc_opportunity WHERE $columnType = 1 ";

        if ($periode != "") {
            $now = date("Y-m-d");
            if ($periode == "today") {
                $sql .= "AND $columnDate LIKE '%$now%' ";
            } else if ($periode == "jour") {
                $sql .= "AND $columnDate LIKE '%$date1%' ";
            } else if ($periode == "semaine") {
                $sql .= "AND WEEK($columnDate) = WEEK('$now') AND MONTH($columnDate) = MONTH('$now') AND YEAR($columnDate) = YEAR('$now') ";
            } else if ($periode == "mois") {
                $sql .= "AND MONTH($columnDate) = MONTH('$now') AND YEAR($columnDate) = YEAR('$now') ";
            } else if ($periode == "trimestre") {
                $sql .= "AND QUARTER($columnDate) = QUARTER('$now') AND YEAR($columnDate) = YEAR('$now') ";
            } else if ($periode == "semestre") {
                $sql .= "AND (QUARTER($columnDate) = QUARTER('$now') OR QUARTER($columnDate) = QUARTER('$now')+1) AND YEAR($columnDate) = YEAR('$now') ";
            } else if ($periode == "annuel") {
                $sql .= "AND YEAR($columnDate) = YEAR('$now') ";
            } else {
                $sql .= " AND $columnDate > '$date1' AND  $columnDate < '$date2' ";
            }
        }

        if ($user != "") {

            $sql .= " AND $columnAut = $user ; ";
        }
    }



    $db->query($sql);
    return $db->single();
}

function findOpportunityByID($idOpportunity)
{
    $result = null;
    $db = new Database();
    $db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity = $idOpportunity LIMIT 1");
    $row = $db->single();
    if ($row) {
        $db->query("SELECT * FROM wbcc_releve_technique WHERE idOpportunityF =  $row->idOpportunity LIMIT 1");
        $rt = $db->single();
        //CONTACT
        $db->query("SELECT * FROM wbcc_contact  WHERE  numeroContact='$row->guidContactClient' LIMIT 1");
        $contact = $db->single();
        if ($contact == false) {
            $db->query("SELECT * FROM wbcc_contact  WHERE  fullName=:fullName LIMIT 1");
            $db->bind("fullName", $row->contactClient, null);
            $contact = $db->single();
        }
        if ($row->typeSinistre == "Partie commune exclusive" && $row->source != null && $row->source != "") {
            $db->query("SELECT * FROM wbcc_contact  WHERE  numeroContact='$row->source' LIMIT 1");
            $contact = $db->single();
        }
        //CIE
        $guidComp = $row->typeSinistre == "Partie commune exclusive" ? $row->guidComMRI : $row->guidComMRH;
        $db->query("SELECT * FROM wbcc_company  WHERE numeroCompany='$guidComp' LIMIT 1");
        $cie = $db->single();
        if ($cie == false) {
            $db->query("SELECT * FROM wbcc_company, wbcc_company_opportunity  WHERE idCompany=idCompanyF AND idOpportunityF=$row->idOpportunity AND category LIKE '%COMPAGNIE D\'ASSURANCE%'  LIMIT 1");
            $cie = $db->single();
        }
        //dernier delegation
        $db->query("SELECT * FROM `wbcc_opportunity_document` od, wbcc_document d WHERE d.idDocument = od.idDocumentF AND od.idOpportunityF=$row->idOpportunity AND lower(urlDocument) like '%delegation%' ORDER BY createDate DESC LIMIT 1");
        $doc = $db->single();
        //RT
        $db->query("SELECT * FROM wbcc_releve_technique WHERE idOpportunityF=$row->idOpportunity LIMIT 1");
        $rt = $db->single();
        //PIECE
        $pieces = [];
        if ($rt) {
            $db->query("SELECT  * FROM wbcc_rt_piece WHERE idRTF= $rt->idRT");
            $pieces = $db->resultSet();
            $supports = [];
            if (sizeof($pieces) > 0) {
                foreach ($pieces as $key => $piece) {
                    $db->query("SELECT  * FROM wbcc_rt_piece_support WHERE idRTPieceF= $piece->idRTPiece ");
                    $supports = $db->resultSet();
                    $piece->listSupports = $supports;
                    foreach ($supports as $j => $support) {
                        $db->query("SELECT * FROM `wbcc_rt_revetement` WHERE `idRtPieceSupportF` = $support->idRTPieceSupport");
                        $dataR = $db->resultSet();
                        $piece->listSupports[$j]->listRevetements = $dataR;
                    }
                }
            }
        }

        //GESTIONNAIRE
        $gestionnaire = false;
        if ($row->numGestionnaire != "" && $row->numGestionnaire != null) {
            $db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$row->numGestionnaire' LIMIT 1");
            $gestionnaire =  $db->single();
        }
        $row->gestionnaire =  ($gestionnaire) ? $gestionnaire->fullName : "";
        //adresse
        $db->query("SELECT * FROM wbcc_immeuble, wbcc_opportunity_immeuble  WHERE idImmeuble=idImmeubleF AND idOpportunityF=$row->idOpportunity LIMIT 1");
        $immeuble = $db->single();
        if ($immeuble == false && $row->immeuble != null && $row->immeuble != "") {
            $db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$row->immeuble' LIMIT 1");
            $immeuble = $db->single();
        }

        $db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF AND codeWBCC = '$row->appartement' LIMIT 1");
        $app = $db->single();
        $row->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");

        $dommages = [];
        $murs = [];
        $biens = [];
        //LOTS
        $db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement WHERE idApp = idAppartementF AND  idOpportunityF =  $row->idOpportunity LIMIT 1");
        $lots  = $db->resultSet();
        //IMMEUBLES
        $db->query("SELECT * FROM wbcc_immeuble, wbcc_opportunity_immeuble WHERE idImmeuble = idImmeubleF AND  idOpportunityF =  $row->idOpportunity LIMIT 1");
        $immeubles  = $db->resultSet();
        //INTERVENANTS
        $db->query("SELECT * FROM wbcc_contact, wbcc_contact_opportunity WHERE idContact = idContactF AND  idOpportunityF =  $row->idOpportunity");
        $contacts  = $db->resultSet();
        //SOCIETES
        $db->query("SELECT * FROM wbcc_company, wbcc_company_opportunity WHERE idCompany = idCompanyF AND  idOpportunityF =  $row->idOpportunity");
        $societes  = $db->resultSet();
        //NOTES
        $db->query("SELECT * FROM wbcc_note, wbcc_opportunity_note, wbcc_utilisateur, wbcc_contact  WHERE idNote = idNoteF  AND idUtilisateurF = idUtilisateur AND idContactF = idContact AND  idOpportunityF =  $row->idOpportunity ORDER BY dateNote DESC");
        $notes  = $db->resultSet();
        //DOCUMENTS
        $db->query("SELECT * FROM wbcc_document, wbcc_opportunity_document, wbcc_utilisateur, wbcc_contact WHERE idDocument = idDocumentF AND idUtilisateurF = idUtilisateur AND idContactF = idContact AND  idOpportunityF =  $row->idOpportunity ORDER BY wbcc_document.createDate DESC");
        $documents  = $db->resultSet();
        //ACTIVITY
        $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity, wbcc_utilisateur, wbcc_contact  WHERE idActivity = idActivityF  AND idUtilisateurF = idUtilisateur AND idContactF = idContact AND  idOpportunityF =  $row->idOpportunity AND publie=1 ORDER BY startTime DESC");
        $activities  = $db->resultSet();
        //ACTIVITY FUTUR
        $today = new DateTime(date("Y-m-d"));
        $activitiesFutur = [];
        $activitiesPasse = [];
        if (!empty($activities)) {
            foreach ($activities as $activity) {
                $dateActivity = new DateTime(date("Y-m-d", strtotime(explode(' ', $activity->startTime)[0])));
                if ($dateActivity > $today) {
                    $activitiesFutur[] = $activity;
                }
                if ($dateActivity < $today) {
                    $activitiesPasse[] = $activity;
                }
            }
        }

        $result = ["op" => $row, "rt" => $rt, "dommages" => $dommages, "murs" => $murs, "biens" => $biens, "lots" => $lots, "gestionnaire" => $gestionnaire, "immeubles" => $immeubles, "contacts" => $contacts, "societes" => $societes, "notes" => $notes, "documents" => $documents, "activities" => $activities, "activitiesFutur" => $activitiesFutur, "activitiesPasse" => $activitiesPasse, "cie" => $cie, "contact" => $contact, "derniereDelegation" => $doc, "rt" => $rt, "pieces" => $pieces];
    }
    return $result;
}


function manageActivity($idOP, $codeActivity, $etatActivity, $opName, $complateActivityName)
{
    $db = new Database();
    $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity=$codeActivity AND idOpportunityF = $idOP LIMIT 1");
    $activiteLine = $db->single();
    if ($activiteLine) {
        $db->query("UPDATE wbcc_activity SET isCleared = '$etatActivity' WHERE idActivity = $activiteLine->idActivity");
        $db->execute();
    } else {
        createNewActivity(
            $idOP,
            $opName,
            "518",
            "Compte WBCC",
            "5770501a-425d-4f50-b66a-016c2dbb2557",
            "$opName - $complateActivityName ",
            "",
            date("Y-m-d H:i:s"),
            date("Y-m-d H:i:s"),
            "Tâche à faire",
            "$etatActivity",
            "0",
            "$codeActivity"
        );
    }
}

function setEtatActivitiesOP($idOP, $etatActivity, $codes)
{
    $db = new Database();
    $db->query("SELECT * FROM wbcc_activity, wbcc_opportunity_activity WHERE idActivity=idActivityF AND codeActivity IN $codes AND idOpportunityF = $idOP");
    $activities = $db->resultSet();
    foreach ($activities as $activity) {
        $db->query("UPDATE wbcc_activity SET isCleared = '$etatActivity' WHERE idActivity = $activity->idActivity");
        $db->execute();
    }
}

function updateParametre($nomColonne, $val)
{
    $db = new Database();

    $db->query("UPDATE wbcc_parametres SET $nomColonne=$val");
    return $db->execute();
}