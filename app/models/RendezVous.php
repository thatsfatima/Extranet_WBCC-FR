<?php

class RendezVous extends Model
{
    public function findActivityByFilters($codeTache, $statutRDV, $idUser, $periode, $date1, $date2, $lien)
    {
        $sql = "SELECT * FROM    wbcc_rendez_vous r, wbcc_opportunity o,  wbcc_appartement ap, wbcc_immeuble i, wbcc_utilisateur u , wbcc_contact c  WHERE u.idContactF=c.idContact 
                        AND u.idUtilisateur=r.idExpertF 
                        AND  r.idAppExtra=ap.idApp  
                        AND ap.idImmeubleF = i.idImmeuble 
                        AND r.idOpportunityF = o.idOpportunity 
                        AND o.status = 'Open'  ";

        if ($codeTache != "0") {
            if ($codeTache == 6) {
                $sql .= " AND r.typeRV LIKE '%RT%' ";
            } elseif ($codeTache == 18) {
                $sql .= " AND r.typeRV LIKE '%EXPERTISE%' ";
            } elseif ($codeTache == 30) {
                $sql .= " AND r.typeRV LIKE '%RF%' ";
            } elseif ($codeTache == 45) {
                $sql .= " AND r.typeRV LIKE '%TRAVAUX%' ";
            }
        } else {
            if ($lien == "expert") {
                $sql .= " AND ( r.typeRV LIKE '%RT%' OR  r.typeRV LIKE '%EXPERTISE%' ) ";
            } else {
                $sql .= " AND (r.typeRV LIKE '%RF%' OR r.typeRV LIKE '%TRAVAUX%' ) ";
            }
        }

        $today = date("Y-m-d");
        if ($periode != "") {
            if ($periode == "passed") {
                $sql .= " AND r.dateRV < '$today' ";
            } else {
                if ($periode == "futur") {
                    $sql .= " AND r.dateRV > '$today' ";
                } else {
                    if ($periode == "today") {
                        $sql .= " AND r.dateRV = '$today' ";
                    } else {
                        if ($periode == "day") {
                            $sql .= " AND r.dateRV = '$date1' ";
                        } else {
                            if ($periode == "perso") {
                                $sql .= " AND   r.dateRV >= '$date1' AND r.dateRV <= '$date2' ";
                            }
                        }
                    }
                }
            }
        }

        if ($idUser != "0" && $idUser != "" && $idUser != "596") {
            $sql .= " AND r.idExpertF = $idUser";
        }

        if ($statutRDV != "") {
            if ($statutRDV == "2") {
                //EN ATTENTE
                $sql .= " AND ( r.etatRV=0 )";
            } else {
                if ($statutRDV == "1") {
                    //EFFECTUE
                    $sql .= " AND r.etatRV=1 ";
                } else {
                    if ($statutRDV == '4') {
                        //En attente de Confirmation 
                        $sql .= " AND r.isProvisoire = 1 AND (dateRV != '' OR dateRV IS NOT NULL) ";
                    } else {
                        if ($statutRDV == '5') {
                            //En attente d'appel du sinstré
                            $sql .= " AND r.isProvisoire = 1 AND (dateRV = '' OR dateRV IS NULL) ";
                        } else {
                            if ($statutRDV == "3") {
                                //RETARD
                                $sql .= " AND ( r.dateRV < '$today' AND r.etatRV=0 )";
                            }
                        }
                    }

                    // if ($statutRDV == '4') {
                    //     //NON ATTRIBUE
                    //     $sql .= " AND r.idExpertF = 583 ";
                    // } else {
                    //     if ($statutRDV == '5') {
                    //         //REJETE
                    //         $sql .= " AND r.etatFRT = 0 AND o.frtFait=0 ";
                    //     } else {
                    //         if ($statutRDV == "3") {
                    //             //RETARD
                    //             $sql .= " AND ( r.dateRV < '$today' AND r.etatRV=0 )";
                    //         }
                    //     }
                    // }
                }
            }
        }

        $sql .= " ORDER BY r.dateRV, r.heureDebut, r.expert ";

        $this->db->query($sql);
        $data =  $this->db->resultSet();
        $result = [];
        foreach ($data as $key => $rv) {
            $rv->color = "";
            foreach ($data as $key => $rv2) {
                if ($rv2->idExpertF == $rv->idExpertF && $rv2->dateRV == $rv->dateRV && explode(":", $rv2->heureDebut)[0] == explode(":", $rv->heureDebut)[0] && $rv2->idRV != $rv->idRV) {
                    $rv->color = "yellow";
                    break;
                }
            }
            $result[] = $rv;
        }
        return $result;
    }

    public function findActivityFRTByUser($codeTache, $user, $type, $periode)
    {
        $sql = "SELECT * FROM  wbcc_opportunity_activity oa, wbcc_activity a,  wbcc_rendez_vous r, wbcc_opportunity o,  wbcc_appartement ap, wbcc_immeuble i, wbcc_utilisateur u , wbcc_contact c  WHERE u.idContactF=c.idContact 
                        AND u.idUtilisateur=r.idExpertF 
                        AND  r.idAppExtra=ap.idApp  
                        AND ap.idImmeubleF = i.idImmeuble 
                        AND  a.idActivity=oa.idActivityF 
                        AND o.idOpportunity = oa.idOpportunityF 
                        AND r.idOpportunityF = o.idOpportunity 
                        AND o.status = 'Open' 
                        AND codeActivity = $codeTache ";
        if ($periode != "") {
            $today = date("Y-m-d");
            if ($periode == "passe") {
                $sql .= " AND r.dateRV < '$today' ";
            } else {
                if ($periode == "futur") {
                    $sql .= " AND r.dateRV > '$today' ";
                } else {
                    $sql .= " AND r.dateRV = '$today' ";
                }
            }
        }

        if ($user != "" && $user != "596") {
            $sql .= " AND r.idExpertF = $user";
        }

        if ($type != "") {
            if ($type == "attente") {
                $sql .= " AND (isCleared = 'False' OR o.frtFait=0 )";
            } else {
                if ($type == "termine") {
                    $sql .= " AND isCleared = 'True' AND o.frtFait=1 ";
                } else {
                    if ($type == 'nonAttribue') {
                        $sql .= " AND r.idExpertF = 583 ";
                    } else {
                        if ($type == 'rejete') {
                            $sql .= " AND r.etatFRT = 0 AND o.frtFait=0 ";
                        }
                    }
                }
            }
        }



        $sql .= " GROUP BY r.idOpportunityF ORDER BY r.dateRV, r.heureDebut, r.expert ";

        $this->db->query($sql);
        $data =  $this->db->resultSet();
        $result = [];
        foreach ($data as $key => $rv) {
            $rv->color = "";
            foreach ($data as $key => $rv2) {
                if ($rv2->idExpertF == $rv->idExpertF && $rv2->dateRV == $rv->dateRV && explode(":", $rv2->heureDebut)[0] == explode(":", $rv->heureDebut)[0] && $rv2->idRV != $rv->idRV) {
                    $rv->color = "yellow";
                    break;
                }
            }
            $result[] = $rv;
        }
        return $result;
    }


    public function findActivityRDVExpertiseByUser($codeTache, $dateRDV = "", $dateRDVOne = "", $dateDebutRDV = "", $dateFinRDV = "", $etatRDV = "", $expertId = "")
    {
        $sql = "SELECT * FROM wbcc_rendez_vous r
                JOIN wbcc_opportunity o ON(o.idOpportunity=r.idOpportunityF)
                JOIN wbcc_opportunity_activity oa ON (oa.idOpportunityF=o.idOpportunity)
                JOIN wbcc_activity a ON(a.idActivity=oa.idActivityF)
                JOIN wbcc_appartement ap ON (r.idAppExtra=ap.idApp)
                JOIN wbcc_immeuble i ON(i.idImmeuble=ap.idImmeubleF)
                JOIN wbcc_utilisateur u ON(u.idUtilisateur=r.idExpertF)
                JOIN wbcc_contact c ON(u.idContactF=c.idContact)
                WHERE o.status = 'Open' AND a.codeActivity =$codeTache AND r.typeRV LIKE 'EXPERTISE%' AND r.idExpertF <> 583 ";

        $today = date("Y-m-d");
        /***********Date RDV ********* */
        if ($dateRDV != "") {
            if ($dateRDV == 0) {
                $sql .= " AND r.dateRV = '$today' ";
            } elseif ($dateRDV == 1) {
                $sql .= " AND r.dateRV > '$today' ";
            } elseif ($dateRDV == 2) {
                $sql .= " AND r.dateRV < '$today' ";
            } elseif ($dateRDV == 3) {
                $sql .= " AND r.dateRV = '$dateRDVOne' ";
            } elseif ($dateRDV == 4) {
                $sql .= " AND r.dateRV >= '$dateDebutRDV' AND r.dateRV <= '$dateFinRDV' ";
            }
        }

        /********** Etat RDV ********** */
        $today2 = date('Y-m-d H:i:s');
        if ($etatRDV != "") {
            if ($etatRDV == 0) {
                $sql .= " AND (isCleared = 'False' OR o.rvExpertiseFait=0 OR ISNULL(o.rvExpertiseFait) ) AND CONCAT(r.dateRV, ' ', r.heureDebut, ':00') < '$today2' ";
            } elseif ($etatRDV == 1) {
                $sql .= " AND (isCleared = 'False' OR o.rvExpertiseFait=0 OR ISNULL(o.rvExpertiseFait) ) AND CONCAT(r.dateRV, ' ', r.heureDebut, ':00') >= '$today2' ";
            } elseif ($etatRDV == 2) {
                $sql .= " AND isCleared = 'True' AND o.rvExpertiseFait=1 ";
            }
        }

        /************** EXPERT WBCC ********** */
        if ($expertId != "") {
            $sql .= " AND r.idExpertF = $expertId ";
        }

        $sql .= " GROUP BY r.idOpportunityF ORDER BY r.dateRV, r.heureDebut, r.expert ";


        $this->db->query($sql);
        $data =  $this->db->resultSet();
        return $data;
    }


    public function findActivityAllRDVByUser($dateRDV = "", $dateRDVOne = "", $dateDebutRDV = "", $dateFinRDV = "", $etatRDV = "", $expertId = "", $typeRDV = "")
    {
        $sql = "SELECT * FROM wbcc_rendez_vous r
                JOIN wbcc_opportunity o ON(o.idOpportunity=r.idOpportunityF)
                /*JOIN wbcc_releve_technique rt ON(rt.idRVF =r.idRV)
                JOIN wbcc_opportunity_activity oa ON (oa.idOpportunityF=o.idOpportunity)
                JOIN wbcc_activity a ON(a.idActivity=oa.idActivityF)*/
                JOIN wbcc_appartement ap ON (r.idAppExtra=ap.idApp)
                JOIN wbcc_immeuble i ON(i.idImmeuble=ap.idImmeubleF)
                JOIN wbcc_utilisateur u ON(u.idUtilisateur=r.idExpertF)
                JOIN wbcc_contact c ON(u.idContactF=c.idContact)
                WHERE o.status = 'Open' AND r.idExpertF <> 583 ";

        if ($typeRDV != "") {
            if ($typeRDV == 1) {
                $sql .= " AND r.typeRV LIKE '%RTP%' ";
            } elseif ($typeRDV == 2) {
                $sql .= " AND r.typeRV LIKE 'EXPERTISE%' ";
            }
        } else {
            $sql .= " AND (  r.typeRV LIKE 'RTP%' OR r.typeRV LIKE 'EXPERTISE%' ) ";
        }

        $today = date("Y-m-d");
        /***********Date RDV ********* */
        if ($dateRDV != "") {
            if ($dateRDV == 0) {
                $sql .= " AND r.dateRV = '$today' ";
            } elseif ($dateRDV == 1) {
                $sql .= " AND r.dateRV > '$today' ";
            } elseif ($dateRDV == 2) {
                $sql .= " AND r.dateRV < '$today' ";
            } elseif ($dateRDV == 3) {
                $sql .= " AND r.dateRV = '$dateRDVOne' ";
            } elseif ($dateRDV == 4) {
                $sql .= " AND r.dateRV >= '$dateDebutRDV' AND r.dateRV <= '$dateFinRDV' ";
            }
        }

        /********** Etat RDV ********** */
        $today2 = date('Y-m-d H:i:s');
        if ($etatRDV != "") {
            if ($etatRDV == 0) {
                if ($typeRDV != "") {
                    if ($typeRDV == 1) {
                        $sql .= " AND ( (o.frtFait=0 OR ISNULL(o.frtFait))  AND CONCAT(r.dateRV, ' ', r.heureDebut, ':00') < '$today2' )";
                    } elseif ($typeRDV == 2) {
                        $sql .= " AND ( (o.rvExpertiseFait=0 OR ISNULL(o.rvExpertiseFait))  AND CONCAT(r.dateRV, ' ', r.heureDebut, ':00') < '$today2' ) ";
                    }
                } else {
                    $sql .= " AND (
                                    CASE 
                                        WHEN r.typeRV LIKE '%RTP%' 
                                            THEN ( (o.frtFait = 0 OR ISNULL(o.frtFait)) AND CONCAT(r.dateRV, ' ', r.heureDebut, ':00') < '$today2' )
                                        ELSE 
                                            ( (o.rvExpertiseFait = 0 OR ISNULL(o.rvExpertiseFait)) AND CONCAT(r.dateRV, ' ', r.heureDebut, ':00') < '$today2' )
                                    END
                                )";
                }
            } elseif ($etatRDV == 1) {
                if ($typeRDV != "") {
                    if ($typeRDV == 1) {
                        $sql .= " AND ( (o.frtFait=0 OR ISNULL(o.frtFait))  AND CONCAT(r.dateRV, ' ', r.heureDebut, ':00') >= '$today2' ) ";
                    } elseif ($typeRDV == 2) {
                        $sql .= " AND ( (o.rvExpertiseFait=0 OR ISNULL(o.rvExpertiseFait))  AND CONCAT(r.dateRV, ' ', r.heureDebut, ':00') >= '$today2') ";
                    }
                } else {
                    $sql .= " AND (
                                    CASE 
                                        WHEN r.typeRV LIKE '%RTP%' 
                                            THEN ( (o.frtFait = 0 OR ISNULL(o.frtFait)) AND CONCAT(r.dateRV, ' ', r.heureDebut, ':00') >= '$today2' )
                                        ELSE 
                                            ( (o.rvExpertiseFait = 0 OR ISNULL(o.rvExpertiseFait)) AND CONCAT(r.dateRV, ' ', r.heureDebut, ':00') >= '$today2' )
                                    END
                                )";
                }
            } elseif ($etatRDV == 2) {
                if ($typeRDV != "") {
                    if ($typeRDV == 1) {
                        $sql .= " AND ( o.frtFait=1 )";
                    } elseif ($typeRDV == 2) {
                        $sql .= " AND ( o.rvExpertiseFait=1  ) ";
                    }
                } else {
                    $sql .= " AND (
                                    CASE 
                                        WHEN r.typeRV LIKE '%RTP%' THEN ( o.frtFait=1 )
                                        ELSE (o.rvExpertiseFait=1 )
                                    END )
                             ";
                }
            }
        }

        /************** EXPERT WBCC ********** */
        if ($expertId != "") {
            $sql .= " AND r.idExpertF = $expertId ";
        }

        $sql .= " ORDER BY r.dateRV, r.heureDebut, r.expert ";

        //echo $sql; die();
        $this->db->query($sql);
        $data =  $this->db->resultSet();
        return $data;
    }

    public function getRVByIdOp($idOp, $type = "RTP")
    {
        $this->db->query("SELECT * FROM wbcc_rendez_vous WHERE idOpportunityF = $idOp AND typeRV='$type' LIMIT 1");
        return $this->db->single();
    }
    public function getRVS()
    {
        $this->db->query("SELECT * FROM rendez_vous,contact,appartement WHERE idCoproF = idCopro  AND idAppF = idApp AND lower(statut) NOT LIKE  '%partie commune%'  ORDER BY dateRV ASC");
        return $this->db->resultSet();
    }

    public function getPartieCommunes()
    {
        $this->db->query("SELECT * FROM rendez_vous,contact,appartement WHERE idCoproF = idCopro  AND idAppF = idApp AND lower(statut) LIKE  '%partie commune%' ORDER BY dateRV DESC");
        return $this->db->resultSet();
    }


    public function updateRV($idRV)
    {
        $this->db->query("UPDATE rendez_vous SET etatRV = 0 WHERE idRV = $idRV");
        return $this->db->execute();
    }

    public function updateEtatRV($idRV, $etat)
    {
        $this->db->query("UPDATE rendez_vous SET etatRV = $etat WHERE idRV = $idRV");
        return $this->db->execute();
    }

    public function save($numero, $idContact, $dateRV, $heureDebut)
    {
        $this->db->query("INSERT INTO rendez_vous(numero,idCoproF,dateRV,heureDebut)
         VALUES ('$numero',$idContact,'$dateRV','$heureDebut')");
        if ($this->db->execute()) {
            return $numero;
        }
        return "0";
    }

    public function findRvByNumero($num)
    {
        $this->db->query("SELECT * FROM rendez_vous WHERE numero = '$num'");
        return $this->db->single();
    }

    public function findRVById($id)
    {
        $this->db->query("SELECT * FROM rendez_vous,contact,appartement WHERE idCoproF = idCopro  AND idAppF = idApp AND idRV = $id");
        return $this->db->single();
    }
}
