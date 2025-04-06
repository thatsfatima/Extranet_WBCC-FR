<?php

class Opportunity extends Model
{
    public function updateIdOpportunity()
    {
        $this->db->query("SELECT * FROM wbcc_opportunity");
        $opportunities = $this->db->resultSet();
        foreach ($opportunities as $opportunity) {
            $name = $opportunity->name;
            $idOpportunityF = $opportunity->idOpportunity;
            
            $this->db->query("UPDATE wbcc_ticket_email_svg SET  idOpportunityF = :idOpportunityF WHERE objetEmail LIKE '%$name%'");
            $this->db->bind(':idOpportunityF', $idOpportunityF);
            $this->db->execute();
        }
    }

    public function getNbMails($gestionnaire, $site, $typeIntervention, $periode, $date1, $date2, $column = "", $data = "")
    {
        $req = "";
        if ($site != "" && $site != "tous") {
            $req .= " AND s.idSiteF = $site ";
        }
        if ($typeIntervention == "AMO") {
            $req .= " AND ( o.type LIKE '%AMO%' OR o.type LIKE '%A.M.O%' ) ";
        } else {
            if ($typeIntervention == "SINISTRE") {
                $req .= " AND o.type LIKE '%sinistre%' ";
            }
        }
        
        if ($periode != "" && $periode != "all") {
            $now = date("Y-m-d");
            if ($periode == "today") {
                $req .= " AND o.createDate LIKE '%$now%' ";
            } else if ($periode == "day") {
                $req .= " AND o.createDate LIKE '%$date1%' ";
            } else if ($periode == "semaine") {
                $req .= " AND WEEK(o.createDate) = WEEK('$now') AND MONTH(o.createDate) = MONTH('$now') AND YEAR(o.createDate) = YEAR('$now') ";
            } else if ($periode == "mois") {
                $req .= " AND MONTH(o.createDate) = MONTH('$now') AND YEAR(o.createDate) = YEAR('$now') ";
            } else if ($periode == "trimestre") {
                $req .= " AND QUARTER(o.createDate) = QUARTER('$now') AND YEAR(o.createDate) = YEAR('$now') ";
            } else if ($periode == "semestre") {
                $req .= " AND (QUARTER(o.createDate) = QUARTER('$now') OR QUARTER(o.createDate) = QUARTER('$now')+1) AND YEAR(o.createDate) = YEAR('$now') ";
            } else if ($periode == "annuel") {
                $req .= " AND YEAR(o.createDate) = YEAR('$now') ";
            } else {
                $req .= " AND o.createDate > '$date1' AND  o.createDate < '$date2' ";
            }
        }

        if ($gestionnaire != "tous") {
            $req .= " AND idExpediteurInterne = $gestionnaire ";
        }

        if ($column != "" && $data != "") {
            $req .= " AND $column = '$data' ";
        }

        $this->db->query("SELECT COUNT(*) as nb FROM wbcc_ticket_email_svg, wbcc_opportunity o, wbcc_utilisateur s WHERE idOpportunityF = o.idOpportunity AND s.idUtilisateur = idExpediteurInterne $req");
        return $this->db->single();
    }

    public function getOpByFilter($statut, $site, $gestionnaire, $typeIntervention, $commercial, $periode, $date1, $date2, $demandeSignature, $declarationCie)
    {
        $req = "";
        //FILTRER PAR SITE
        if ($site != "" && $site != "tous") {
            $req .= " AND s.idSite= $site ";
        }
        //FILTRER PAR TYPE INTERVENTION
        if ($typeIntervention == "AMO") {
            $req .= " AND o.type LIKE '%A.M.O%' ";
        } else {
            if ($typeIntervention == "SINISTRE") {
                $req .= " AND o.type LIKE '%sinistre%' ";
            }
        }
        //FILTRER PAR GESTIONNAIRE
        if ($gestionnaire != 'tous') {
            $req .= " AND o.gestionnaire =$gestionnaire ";
        }
        //FILTRER PAR COMMERCIAL
        if ($commercial != 'tous') {
            $req .= " AND o.idCommercial =$commercial ";
        }

        $columnDate = "";
        //FILTER BY STATUR OR ETAPE
        $typeReq = "";
        if ($statut == "enCours") {
            $req .= " AND (o.status='Open' OR o.status='Inactive') ";
            $typeReq = 1;
            $columnDate = "o.createDate";
        } else {
            if ($statut == "won" || $statut == "lost") {
                $req .= " AND (o.status='$statut') ";
                $typeReq = 1;
                $columnDate = "o.dateCloture";
            } else {
                if ($statut == "attenteCloture") {
                    $req .= " AND (o.status='Open' AND o.demandeCloture=1 ) ";
                    $typeReq = 1;
                    $columnDate = "o.dateDemandeCloture";
                } else {
                    if ($statut == "tous") {
                        $typeReq = 1;
                        $columnDate = "o.createDate";
                    } else {
                        $typeReq = 2;
                        $columnDate = "a.startTime";
                    }
                }
            }
        }

        //FILTRER PAR PERIODE
        $today = date("Y-m-d");
        // die;
        if ($periode != "" && $periode != "all") {
            if ($periode == "today") {
                $req .= " AND $columnDate LIKE '%$today%' ";
            } else {
                if ($periode == "day") {
                    $req .= " AND $columnDate LIKE '%$date1%' ";
                } else {
                    $req .= " AND   $columnDate >= '$date1' AND $columnDate <= '$date2'  ";
                }
            }
        }

        if ($typeReq == "1") {
            $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_contact c, wbcc_utilisateur u, wbcc_site s WHERE o.gestionnaire = u.idUtilisateur AND u.idContactF = c.idContact AND s.idSite=u.idSiteF AND etatOP = 1 $req ORDER BY name DESC");
            $data =  $this->db->resultSet();
        } else {
            $req2 = '';
            if ($statut == "9" || $statut == "16") {
                $req2 .= "  AND ((o.typeSinistre='Partie privative exclusive' AND o.sinistreMRH != '' AND o.sinistreMRH IS NOT NULL) OR (o.typeSinistre='Partie commune exclusive' AND o.sinistreMRI != '' AND o.sinistreMRi IS NOT NULL)) ";
                // $req .= " AND o.controleFRT=1 AND o.controleRT=1 AND o.declarationCie=1 AND ((o.typeSinistre='Partie privative exclusive' AND o.sinistreMRH != '' AND o.sinistreMRH IS NOT NULL) OR (o.typeSinistre='Partie commune exclusive' AND o.sinistreMRI != '' AND o.sinistreMRi IS NOT NULL)) ";
            }
            if ($statut == "10") {
                // $req .= "  AND o.controleFRT=1 AND o.controleFRT2=1 AND o.controleFRT3=1 AND ((o.typeSinistre='Partie privative exclusive' AND o.sinistreMRH != '' AND o.sinistreMRH IS NOT NULL) OR (o.typeSinistre='Partie commune exclusive' AND o.sinistreMRI != '' AND o.sinistreMRi IS NOT NULL)) AND o.envoiDevis=1 AND o.devisFais=1 ";
            }
            if ($statut == "11") {
                // if ($etape == "1") {
                //     $req .= " AND o.controleFRT=0 AND (o.idAuteurControleFRT2 IS NULL OR o.idAuteurControleFRT2 != $idUser ) AND (o.idAuteurControleFRT3 IS NULL OR o.idAuteurControleFRT3 != $idUser ) ";
                // }
                // if ($etape == "2") {
                //     $req .= " AND o.controleFRT2=0 AND (o.idAuteurControleFRT IS NULL OR o.idAuteurControleFRT != $idUser ) AND (o.idAuteurControleFRT3 IS NULL OR o.idAuteurControleFRT3 != $idUser ) ";
                // }
                // if ($etape == "3") {
                //     $req .= " AND o.controleFRT3=0 AND (o.idAuteurControleFRT IS NULL OR o.idAuteurControleFRT != $idUser ) AND (o.idAuteurControleFRT2 IS NULL OR o.idAuteurControleFRT2 != $idUser ) ";
                // }
            }

            if ($statut == '27') {
                $req2 = ' OR codeActivity=37';
            }

            if ($statut == "15") {
                $req2 = ' AND o.idExpertCompanyF IS NOT NULL  ';
            }

            if ($statut == "19") {
                // $req2 = ' OR  ((codeActivity=14 AND o.idExpertCompanyF IS NULL) OR (codeActivity=15 AND AND o.idExpertCompanyF IS NULL))';
            }
            if ($statut == "24" && ($demandeSignature == "0" || $demandeSignature == "1")) {
                $req .= $demandeSignature == "0" ?  " AND o.idOpportunity NOT IN (SELECT idOpportunityF FROM wbcc_recherche_fuite WHERE demandeSignatureEnvoye=1)" :  "  AND o.idOpportunity IN (SELECT idOpportunityF FROM wbcc_recherche_fuite WHERE demandeSignatureEnvoye=1)";
            }
            if ($statut == "32" && ($demandeSignature == "0" || $demandeSignature == "1")) {
                $req .= $demandeSignature == "0" ?   "  AND o.idOpportunity NOT IN (SELECT idOpportunityF FROM wbcc_recherche_fuite WHERE demandeJustificatifEnvoye=1)" :  "  AND o.idOpportunity IN (SELECT idOpportunityF FROM wbcc_recherche_fuite WHERE demandeJustificatifEnvoye=1)";
            }

            if ($statut == "4") {
                if ($declarationCie == "email") {
                    $req .= " AND o.idOpportunity IN (SELECT idOpportunityF FROM wbcc_releve_technique rt WHERE  rt.cause != '' AND rt.cause IS NOT NULL  AND  rt.precisionComplementaire != '' AND rt.precisionComplementaire IS NOT NULL AND rt.libellePieces != '')  AND  o.delegationSigne=1 AND o.teleExpertiseFaite = 1 AND (SELECT urlDocument FROM `wbcc_opportunity_document` od, wbcc_document d WHERE d.idDocument = od.idDocumentF AND od.idOpportunityF=o.idOpportunity AND lower(urlDocument) like '%delegation%' AND lower(urlDocument) NOT LIKE '%-x%' ORDER BY d.createDate DESC LIMIT 1) LIKE '%sign%' ";
                } else {
                    if ($declarationCie == "tel") {
                        $req .= " AND o.idOpportunity IN (SELECT idOpportunityF FROM wbcc_releve_technique rt WHERE  rt.cause != '' AND rt.cause IS NOT NULL  AND  rt.precisionComplementaire != '' AND rt.precisionComplementaire IS NOT NULL AND rt.libellePieces != '')  AND  o.delegationSigne=1 AND o.teleExpertiseFaite = 1 AND (SELECT urlDocument FROM `wbcc_opportunity_document` od, wbcc_document d WHERE d.idDocument = od.idDocumentF AND od.idOpportunityF=o.idOpportunity AND lower(urlDocument) like '%delegation%' AND lower(urlDocument) NOT LIKE '%-x%' ORDER BY d.createDate DESC LIMIT 1) LIKE '%sign%' ";
                    }
                }
            }

            $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a,  wbcc_contact c, wbcc_utilisateur u, wbcc_site s WHERE o.gestionnaire = u.idUtilisateur AND u.idContactF = c.idContact AND u.idSiteF=s.idSite AND  a.isCleared='False'  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND (codeActivity=$statut $req2) AND o.type='Sinistres' AND etatOP = 1 $req GROUP BY idOpportunity ORDER BY name DESC");
            $this->db->bind("code", $statut, null);
            $data =  $this->db->resultSet();
        }

        return $data;
    }

    public function getOp($statut, $site, $gestionnaire, $typeIntervention, $periode, $date1, $date2)
    {
        $req = "";
        //FILTRER PAR SITE
        if ($site != "" && $site != "tous") {
            $req .= " AND s.idSite= $site ";
        }
        //FILTRER PAR TYPE INTERVENTION
        if ($typeIntervention == "AMO") {
            $req .= " AND o.type LIKE '%A.M.O%' ";
        } else {
            if ($typeIntervention == "SINISTRE") {
                $req .= " AND o.type LIKE '%sinistre%' ";
            }
        }
        //FILTRER PAR GESTIONNAIRE
        if ($gestionnaire != 'tous') {
            $req .= " AND o.gestionnaire =$gestionnaire ";
        }
        $columnDate = "";
        //FILTER BY STATUR OR ETAPE
        $typeReq = "";
        if ($statut == "enCours") {
            $req .= " AND (o.status='Open' OR o.status='Inactive') ";
            $typeReq = 1;
            $columnDate = "o.createDate";
        } else {
            if ($statut == "won" || $statut == "lost") {
                $req .= " AND (o.status='$statut') ";
                $typeReq = 1;
                $columnDate = "o.dateCloture";
            } else {
                if ($statut == "attenteCloture") {
                    $req .= " AND (o.status='Open' AND o.demandeCloture=1 ) ";
                    $typeReq = 1;
                    $columnDate = "o.dateDemandeCloture";
                } else {
                    if ($statut == "tous") {
                        $typeReq = 1;
                        $columnDate = "o.createDate";
                    } else {
                        $typeReq = 2;
                        $columnDate = "a.startTime";
                    }
                }
            }
        }

        //FILTRER PAR PERIODE
        $today = date("Y-m-d");
        // die;
        if ($periode != "" && $periode != "all") {
            if ($periode == "today") {
                $req .= " AND $columnDate LIKE '%$today%' ";
            } else {
                if ($periode == "day") {
                    $req .= " AND $columnDate LIKE '%$date1%' ";
                } else {
                    $req .= " AND   $columnDate >= '$date1' AND $columnDate <= '$date2'  ";
                }
            }
        }

        if ($typeReq == "1") {
            $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_contact c, wbcc_utilisateur u, wbcc_site s WHERE o.gestionnaire = u.idUtilisateur AND u.idContactF = c.idContact AND s.idSite=u.idSiteF AND etatOP = 1 $req ORDER BY name DESC");
            $data =  $this->db->resultSet();
        } else {
            $req2 = '';
            if ($statut == "9" || $statut == "16") {
                $req2 .= "  AND ((o.typeSinistre='Partie privative exclusive' AND o.sinistreMRH != '' AND o.sinistreMRH IS NOT NULL) OR (o.typeSinistre='Partie commune exclusive' AND o.sinistreMRI != '' AND o.sinistreMRi IS NOT NULL)) ";
                // $req .= " AND o.controleFRT=1 AND o.controleRT=1 AND o.declarationCie=1 AND ((o.typeSinistre='Partie privative exclusive' AND o.sinistreMRH != '' AND o.sinistreMRH IS NOT NULL) OR (o.typeSinistre='Partie commune exclusive' AND o.sinistreMRI != '' AND o.sinistreMRi IS NOT NULL)) ";
            }
            if ($statut == "10") {
                // $req .= "  AND o.controleFRT=1 AND o.controleFRT2=1 AND o.controleFRT3=1 AND ((o.typeSinistre='Partie privative exclusive' AND o.sinistreMRH != '' AND o.sinistreMRH IS NOT NULL) OR (o.typeSinistre='Partie commune exclusive' AND o.sinistreMRI != '' AND o.sinistreMRi IS NOT NULL)) AND o.envoiDevis=1 AND o.devisFais=1 ";
            }
            if ($statut == "11") {
                // if ($etape == "1") {
                //     $req .= " AND o.controleFRT=0 AND (o.idAuteurControleFRT2 IS NULL OR o.idAuteurControleFRT2 != $idUser ) AND (o.idAuteurControleFRT3 IS NULL OR o.idAuteurControleFRT3 != $idUser ) ";
                // }
                // if ($etape == "2") {
                //     $req .= " AND o.controleFRT2=0 AND (o.idAuteurControleFRT IS NULL OR o.idAuteurControleFRT != $idUser ) AND (o.idAuteurControleFRT3 IS NULL OR o.idAuteurControleFRT3 != $idUser ) ";
                // }
                // if ($etape == "3") {
                //     $req .= " AND o.controleFRT3=0 AND (o.idAuteurControleFRT IS NULL OR o.idAuteurControleFRT != $idUser ) AND (o.idAuteurControleFRT2 IS NULL OR o.idAuteurControleFRT2 != $idUser ) ";
                // }
            }

            if ($statut == '27') {
                $req2 = ' OR codeActivity=37';
            }

            if ($statut == "15") {
                $req2 = ' AND o.idExpertCompanyF IS NOT NULL  ';
            }

            if ($statut == "19") {
                // $req2 = ' OR  ((codeActivity=14 AND o.idExpertCompanyF IS NULL) OR (codeActivity=15 AND AND o.idExpertCompanyF IS NULL))';
            }
            // if ($statut == "24" && ($demandeSignature == "0" || $demandeSignature == "1")) {
            //     $req .= $demandeSignature == "0" ?  " AND o.idOpportunity NOT IN (SELECT idOpportunityF FROM wbcc_recherche_fuite WHERE demandeSignatureEnvoye=1)" :  "  AND o.idOpportunity IN (SELECT idOpportunityF FROM wbcc_recherche_fuite WHERE demandeSignatureEnvoye=1)";
            // }
            // if ($statut == "32" && ($demandeSignature == "0" || $demandeSignature == "1")) {
            //     $req .= $demandeSignature == "0" ?   "  AND o.idOpportunity NOT IN (SELECT idOpportunityF FROM wbcc_recherche_fuite WHERE demandeJustificatifEnvoye=1)" :  "  AND o.idOpportunity IN (SELECT idOpportunityF FROM wbcc_recherche_fuite WHERE demandeJustificatifEnvoye=1)";
            // }

            if ($statut == "4") {
                if ($declarationCie == "email") {
                    $req .= " AND o.idOpportunity IN (SELECT idOpportunityF FROM wbcc_releve_technique rt WHERE  rt.cause != '' AND rt.cause IS NOT NULL  AND  rt.precisionComplementaire != '' AND rt.precisionComplementaire IS NOT NULL AND rt.libellePieces != '')  AND  o.delegationSigne=1 AND o.teleExpertiseFaite = 1 AND (SELECT urlDocument FROM `wbcc_opportunity_document` od, wbcc_document d WHERE d.idDocument = od.idDocumentF AND od.idOpportunityF=o.idOpportunity AND lower(urlDocument) like '%delegation%' AND lower(urlDocument) NOT LIKE '%-x%' ORDER BY d.createDate DESC LIMIT 1) LIKE '%sign%' ";
                } else {
                    if ($declarationCie == "tel") {
                        $req .= " AND o.idOpportunity IN (SELECT idOpportunityF FROM wbcc_releve_technique rt WHERE  rt.cause != '' AND rt.cause IS NOT NULL  AND  rt.precisionComplementaire != '' AND rt.precisionComplementaire IS NOT NULL AND rt.libellePieces != '')  AND  o.delegationSigne=1 AND o.teleExpertiseFaite = 1 AND (SELECT urlDocument FROM `wbcc_opportunity_document` od, wbcc_document d WHERE d.idDocument = od.idDocumentF AND od.idOpportunityF=o.idOpportunity AND lower(urlDocument) like '%delegation%' AND lower(urlDocument) NOT LIKE '%-x%' ORDER BY d.createDate DESC LIMIT 1) LIKE '%sign%' ";
                    }
                }
            }

            $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a,  wbcc_contact c, wbcc_utilisateur u, wbcc_site s WHERE o.gestionnaire = u.idUtilisateur AND u.idContactF = c.idContact AND u.idSiteF=s.idSite AND  a.isCleared='False'  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND (codeActivity=$statut $req2) AND o.type='Sinistres' AND etatOP = 1 $req GROUP BY idOpportunity ORDER BY name DESC");
            $this->db->bind("code", $statut, null);
            $data =  $this->db->resultSet();
        }

        return $data;
    }

    public function getNbTacheEffectGest($user, $periode, $date1, $date2, $code, $statut, $typeIntervention)
    {
        $codeActivity = ($code == 0) ? 11 : $code;
        $this->db->query("SELECT * FROM wbcc_activity_db WHERE codeActivity=:codeActivity LIMIT 1");
        $this->db->bind("codeActivity", $codeActivity, null);
        $activity = $this->db->single();
        $columnType = (isset($activity->nomVariableOP)) ? 'o.'.$activity->nomVariableOP : "0";
        $columnAut = (isset($activity->nomVariableIdAuteurOP)) ? $activity->nomVariableIdAuteurOP : "gestionnaire";
        $columnDate = (isset($activity->nomVariableDateOP)) ? $activity->nomVariableDateOP : "createDate";
        $req = "";
        
        if ($typeIntervention == "AMO") {
            $req .= " AND ( o.type LIKE '%AMO%' OR o.type LIKE '%A.M.O.%' )";
        } else if ($typeIntervention == "SINISTRE") {
            if ($typeIntervention == "SINISTRE") {
                $req .= " AND o.type LIKE '%sinistre%' ";
            }
        }

        //FILTER BY STATUR OR ETAPE
        $typeReq = "";
        if ($statut == "enCours") {
            $req .= " AND (o.status='Open' OR o.status='Inactive') ";
            $typeReq = "";
            $columnDate = "createDate";
        } else {
            if ($statut == "won" || $statut == "lost") {
                $req .= " AND (o.status='$statut') ";
                $typeReq = "";
                $columnDate = "dateCloture";
            } else {
                if ($statut == "attenteCloture") {
                    $req .= " AND (o.status='Open' AND o.demandeCloture=1 ) ";
                    $typeReq = "";
                    $columnDate = "dateDemandeCloture";
                } else {
                    if ($statut == "tous") {
                        $typeReq = "";
                        $columnDate = $columnDate;
                    } else {
                        $typeReq = "";
                        $columnDate = $columnDate;
                    }
                }
            }
        }

        $sql = "";
        if ($code == 0) {
            $sql = "SELECT Count(*) as nbr FROM wbcc_opportunity o, wbcc_rendez_vous r $typeReq WHERE o.idOpportunity = r.idOpportunityF $req AND r.etatFRT = 0 ";
            $columnDate = "r.$activity->nomVariableDateOP";
            $columnAut = "r.$columnAut";
        } else {
            $sql = "SELECT Count(*) as nbr FROM wbcc_opportunity o $typeReq WHERE o.etatOP = 1 AND $columnType = 1 $req";
            $columnDate = "o.$columnDate";
            $columnAut = "o.$columnAut";
        }

        if ($periode != "") {
            $now = date("Y-m-d");
            if ($periode == "all") {
                $sql .= " ";   
            }
            else if ($periode == "today") {
                $sql .= "AND $columnDate LIKE '%$now%' ";
            } else if ($periode == "day") {
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
            $sql .= " AND $columnAut = $user ";
        }

        $this->db->query("$sql ;");
        return $this->db->single()->nbr;
    }

    public function getListeOPByTypeAuteurPeriode($periode, $columnDate, $columnType, $date1, $date2, $user, $site, $columnAut, $code, $statut)
    {
        if ($code == 0) {
            $sql = "SELECT * FROM wbcc_opportunity, wbcc_rendez_vous r, wbcc_utilisateur s WHERE idOpportunity = r.idOpportunityF AND r.etatFRT = 0 ";
            $columnDate = "r.$columnDate";
            $columnAut = "r.$columnAut";
        } else {
            $sql = "SELECT * FROM wbcc_opportunity, wbcc_utilisateur s WHERE $columnType = 1 AND etatOP = 1";
        }

        if ($statut == "enCours") {
            $sql .= " AND (status='Open' OR status='Inactive') ";
        } else {
            if ($statut == "won" || $statut == "lost") {
                $sql .= " AND (status='$statut') ";
            } else {
                if ($statut == "attenteCloture") {
                    $sql .= " AND (status='Open' AND demandeCloture=1 ) ";
                }
            }
        }

        if ($periode != "") {
            $now = date("Y-m-d");
            if ($periode == "all") {
                $sql .= " ";   
            }
            else if ($periode == "today") {
                $sql .= " AND $columnDate LIKE '%$now%' ";
            } else if ($periode == "day") {
                $sql .= " AND $columnDate LIKE '%$date1%' ";
            } else if ($periode == "semaine") {
                $sql .= " AND WEEK($columnDate) = WEEK('$now') AND MONTH($columnDate) = MONTH('$now') AND YEAR($columnDate) = YEAR('$now') ";
            } else if ($periode == "mois") {
                $sql .= " AND MONTH($columnDate) = MONTH('$now') AND YEAR($columnDate) = YEAR('$now') ";
            } else if ($periode == "trimestre") {
                $sql .= " AND QUARTER($columnDate) = QUARTER('$now') AND YEAR($columnDate) = YEAR('$now') ";
            } else if ($periode == "semestre") {
                $sql .= " AND (QUARTER($columnDate) = QUARTER('$now') OR QUARTER($columnDate) = QUARTER('$now')+1) AND YEAR($columnDate) = YEAR('$now') ";
            } else if ($periode == "annuel") {
                $sql .= " AND YEAR($columnDate) = YEAR('$now') ";
            } else {
                $sql .= " AND $columnDate > '$date1' AND  $columnDate < '$date2' ";
            }
        }

        if ($user != "") {

            $sql .= " AND $columnAut = $user ";
        }
        else {
            $sql .= " AND $columnAut = s.idUtilisateur";
            if ($site != "" && $site != "tous") {
                $sql .= " AND s.idSiteF = $site ";
            }
        }

        $this->db->query("$sql ;");
        return $this->db->resultSet();
    }
    
    public function getOpportunityToSign($ypeDoc = "DDE", $etat = "attente",  $typeUser = "", $idContact, $idDO = null)
    {
        $datas = [];
        $req = "";
        if ($typeUser == "user") {
            $req .= " AND o.idResponsableContactF = $idContact ";
        } else {
            if ($typeUser == "do") {
                $req .= " AND o.idGestionnaireAppImm = $idDO ";
            } else {
                if ($typeUser == "cs") {
                    $req .= " AND i.idChefSecteur = $idContact ";
                }
            }
        }


        if ($ypeDoc == "DDE") {
            $req .= " AND  rf.demandeSignatureEnvoye=1 ";
            if ($etat == "attente") {
                $req .= " AND   rf.isResponsableSigned=0  ";
            } else {
                if ($etat == "signe") {
                    $req .= "  AND  rf.isResponsableSigned=1 AND  rf.refusSignature=0 ";
                } else {
                    if ($etat == "refus") {
                        $req .= " AND  rf.isResponsableSigned=1 AND  rf.refusSignature=1   ";
                    }
                }
            }
        } else {
            $req .= " AND  rf.demandeJustificatifEnvoye=1 ";
            if ($etat == "attente") {
                $req .= " AND   rf.siSignatureJustificatif=0  ";
            } else {
                if ($etat == "signe") {
                    $req .= "  AND  rf.siSignatureJustificatif=1 AND  rf.refusSignatureJustificatif=0 ";
                } else {
                    if ($etat == "refus") {
                        $req .= " AND  rf.siSignatureJustificatif=1 AND  rf.refusSignatureJustificatif=1   ";
                    } else {
                    }
                }
            }
        }
        $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_recherche_fuite rf, wbcc_immeuble i WHERE o.idImmeuble = i.idImmeuble AND  o.idOpportunity=rf.idOpportunityF  $req ORDER BY name DESC");
        $datas =  $this->db->resultSet();
        return $datas;
    }

    public function getOpByStatutAndActivity($statut, $col)
    {
        // $this->db->query("SELECT * FROM wbcc_opportunity  WHERE status='$statut' AND $col=1 ORDER BY idOpportunity DESC");
        $this->db->query("SELECT * FROM wbcc_opportunity  WHERE $col=1 AND etatOP = 1 ORDER BY idOpportunity DESC");
        $data =  $this->db->resultSet();
        return $data;
    }

    public function getListeRT($idUser = '')
    {
        $today = new DateTime(date("Y-m-d"));
        $req = '';
        if ($idUser != '') {
            $req = " AND p.idUserF = $idUser ";
        }
        $tab = ["today" => [], "passe" => [], "futur" => []];
        // $this->db->query("SELECT *, a.idApp as idAppWBCC,o.signature as signOp, rt.date as dateSinistre FROM `wbcc_pap_b2c` p, wbcc_opportunity_papb2c_contact opc, wbcc_opportunity o, wbcc_contact c, wbcc_immeuble i, wbcc_appartement a, wbcc_releve_technique rt  WHERE p.idPAP = opc.idPAPF AND o.idOpportunity = opc.idOpportunityF AND o.idOpportunity = rt.idOpportunityF  AND opc.idContactF = c.idContact AND o.idImmeuble = i.idImmeuble AND o.idAppartement = a.idApp AND resultatVisite = '4' AND dateRappelTelephonique != '' AND o.delegationSigne = 0 AND o.status='Open' $req");
        $this->db->query("SELECT *,o.signature as signOp FROM `wbcc_pap_b2c` p, wbcc_opportunity_papb2c_contact opc, wbcc_opportunity o  WHERE p.idPAP = opc.idPAPF AND o.idOpportunity = opc.idOpportunityF AND resultatVisite = '4' AND dateRappelTelephonique != '' AND o.delegationSigne = 0 AND o.status='Open' AND etatOP = 1 $req");
        $data = $this->db->resultSet();
        $tabPasse = [];
        $tabFutur = [];
        $tabToday = [];
        if (!empty($data)) {
            foreach ($data as $key => $rv) {
                if ($rv->dateRappelTelephonique != "") {
                    // $dateRV = new DateTime(date("Y-m-d", strtotime(explode(' ', $rv->dateRappelTelephonique)[0])));
                    $dateRV = new DateTime(date("Y-m-d", strtotime(explode(' ', $rv->createDate)[0])));
                    if ($dateRV < $today) {
                        $tabPasse[] = $rv;
                    } else {
                        if ($dateRV > $today) {
                            $tabFutur[] = $rv;
                        } else {
                            // if ($rv->syndicPSR == "Oui") {
                            $tabToday[] = $rv;
                            //}
                        }
                    }
                }
            }

            $tab = ["today" => $tabToday, "passe" => $tabPasse, "futur" => $tabFutur];
        }
        return $tab;
    }

    public function getEncaissementsByType($idOp)
    {
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_encaissement WHERE o.idOpportunity=idOPEncaissement AND idOpportunity=$idOp AND etatOP = 1");
        $data =  $this->db->resultSet();
        return $data;
    }

    public function getChequeByOP($idOp)
    {
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_cheque WHERE o.idOpportunity=idOpportunityF AND idOpportunity=$idOp AND etatOP = 1");
        $data =  $this->db->resultSet();
        return $data;
    }

    public function getListeOPFaiteByTypeAuteurPeriode($periode, $columnDate, $columnType, $date1, $date2, $user, $columnAut)
    {
        $sql = "SELECT * FROM wbcc_opportunity WHERE  etatOP = 1 AND $columnType = 1 ";

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

        $this->db->query($sql);

        return  $this->db->resultSet();
    }

    public function getOPwithSameContactAndEtape($idOP, $idContact, $codeActivity = "")
    {
        if ($codeActivity != "") {
            $this->db->query("SELECT * FROM wbcc_contact_opportunity co, wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a WHERE co.idOpportunityF= o.idOpportunity AND co.idContactF=$idContact AND  a.isCleared='false'  AND o.status = 'Open' AND o.name NOT LIKE '%X%'  AND a.codeActivity =$codeActivity AND o.idOpportunity != $idOP AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF  AND o.type='Sinistres' AND etatOP = 1 GROUP BY o.idOpportunity ORDER BY name DESC");
        } else {
            $this->db->query("SELECT * FROM wbcc_contact_opportunity co, wbcc_opportunity o WHERE co.idOpportunityF= o.idOpportunity AND co.idContactF=$idContact AND o.status = 'Open' AND o.name NOT LIKE '%X%'   AND o.idOpportunity != $idOP AND o.type='Sinistres' AND etatOP = 1  GROUP BY o.idOpportunity ORDER BY name DESC");
        }
        $data =  $this->db->resultSet();
        return $data;
    }

    public function getOPwithSameCie($numeroCie, $roleInterne = '', $idOp, $idCompany = '0', $role = "", $idUser = '')
    {
        $req = "";
        if ($role == "Gestionnaire EXTERNE DOSSIER") {
            $req = " AND o.gestionnaire = '$idUser'  ";
        }
        $this->db->query("SELECT *, o.name as nameOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a , wbcc_releve_technique rt, wbcc_company_opportunity co, wbcc_company c  WHERE o.idOpportunity = co.idOpportunityF AND c.idCompany=co.idCompanyF AND c.idCompany = $idCompany AND  rt.idOpportunityF= o.idOpportunity  AND idOpportunity != $idOp $req AND rt.cause != '' AND rt.cause IS NOT NULL  AND  rt.precisionComplementaire != '' AND rt.precisionComplementaire IS NOT NULL AND libellePieces != '' AND o.delegationSigne=1 AND (o.teleExpertiseFaite = 1 AND o.declarationCie = 0) AND a.isCleared='False'  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND a.codeActivity=4  AND o.type='Sinistres' AND (SELECT urlDocument FROM `wbcc_opportunity_document` od, wbcc_document d WHERE d.idDocument = od.idDocumentF AND od.idOpportunityF=o.idOpportunity AND lower(urlDocument) like '%delegation%'  AND lower(urlDocument) NOT LIKE '%-x%' ORDER BY createDate DESC LIMIT 1) LIKE '%sign%' AND etatOP = 1  GROUP BY idOpportunity ORDER BY o.name DESC");

        $data =  $this->db->resultSet();
        return $data;
    }

    public function getOPwithSameCieForRelance($numeroCie, $roleInterne = '', $idOp, $idCompany = '0', $role = "", $idUser = '')
    {
        $req = "";
        if ($role == "Gestionnaire EXTERNE DOSSIER") {
            $req = " AND o.gestionnaire = '$idUser'  ";
        }
        $this->db->query("SELECT *, o.name as nameOP  FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a, wbcc_company_opportunity co, wbcc_company c WHERE o.idOpportunity = co.idOpportunityF AND c.idCompany=co.idCompanyF  AND c.idCompany = $idCompany AND a.isCleared='False' $req  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND codeActivity=5 AND o.type='Sinistres' AND o.idOpportunity != $idOp AND etatOP = 1 GROUP BY idOpportunity ORDER BY o.name DESC");

        $data =  $this->db->resultSet();
        return $data;
    }

    public function getOPwithSameCieForPCDevis($numeroCie, $roleInterne = '', $idOp, $idCompany = '0', $role = "", $idUser = '')
    {
        $req = "";
        if ($role == "Gestionnaire EXTERNE DOSSIER") {
            $req = " AND o.gestionnaire = '$idUser'  ";
        }
        $this->db->query("SELECT *, o.name as nameOP  FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a, wbcc_company_opportunity co, wbcc_company c WHERE o.idOpportunity = co.idOpportunityF AND c.idCompany=co.idCompanyF  AND c.idCompany = $idCompany AND a.isCleared='False' $req  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND codeActivity=10 AND o.type='Sinistres' AND o.idOpportunity != $idOp  AND etatOP = 1 GROUP BY idOpportunity ORDER BY o.name DESC");

        $data =  $this->db->resultSet();
        return $data;
    }

    public function getOpNote($idOp)
    {
        $this->db->query("SELECT * FROM wbcc_note n JOIN wbcc_opportunity_note o on o.idNoteF = n.idNote WHERE o.idOpportunityF = '$idOp'");
        return $this->db->resultSet();
    }

    public function findPap($idOp)
    {
        $this->db->query("SELECT * FROM wbcc_pap_b2c p JOIN wbcc_opportunity_papb2c_contact po on po.idPAPF = p.idPAP WHERE po.idOpportunityF = '$idOp' Limit 1");
        return $this->db->single();
    }

    public function findByIdOp($idOp)
    {
        $this->db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity = $idOp");
        $o = $this->db->single();
        if ($o) {
            $this->db->query("SELECT * FROM wbcc_immeuble, wbcc_opportunity_immeuble  WHERE idImmeuble=idImmeubleF AND idOpportunityF=$o->idOpportunity LIMIT 1");
            $immeuble = $this->db->single();
            if ($immeuble == false && $o->immeuble != null && $o->immeuble != "") {
                $this->db->query("SELECT * FROM wbcc_immeuble  WHERE codeImmeuble='$o->immeuble' LIMIT 1");
                $immeuble = $this->db->single();
            }
            $o->immeuble = $immeuble;
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF  AND idOpportunityF=$o->idOpportunity LIMIT 1");
            $app = $this->db->single();
            $o->app = $app;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->cp = ($immeuble) ? $immeuble->codePostal  : (($app) ?  $app->codePostal  : "");
            $o->ville = ($immeuble) ? $immeuble->ville : (($app) ?  $app->ville : "");
            $this->db->query("SELECT * FROM wbcc_contact  WHERE  numeroContact='$o->guidContactClient' LIMIT 1");
            $contact = $this->db->single();
            if ($contact == false) {
                $this->db->query("SELECT * FROM wbcc_contact  WHERE  fullName=:fullName LIMIT 1");
                $this->db->bind("fullName", $o->contactClient, null);
                $contact = $this->db->single();
            }
            if ($o->typeSinistre == "Partie commune exclusive") {
                if ($o->idReferentDo != null && $o->idReferentDo != "" && $o->idReferentDo != "0") {
                    $this->db->query("SELECT * FROM wbcc_contact WHERE idContact=$o->idReferentDo LIMIT 1");
                    $contact = $this->db->single();
                }
                if ($contact == false) {
                    //GET DIRIGEANT SOCIETE
                    if ($o->idGestionnaireAppImm != null && $o->idGestionnaireAppImm != "") {
                        $this->db->query("SELECT * FROM wbcc_contact c, wbcc_contact_company cc WHERE c.idContact = cc.idContactF AND cc.idCompanyF=$o->idGestionnaireAppImm  ");
                        $contacts = $this->db->resultSet();
                        if (sizeof($contacts) == 1 && $contacts[0]->statutContact != null && !str_contains(strtoupper($contacts[0]->statutContact),  'COMMERCIAL')) {
                            $contact = $contacts[0];
                        } else {
                            $trouve = false;
                            foreach ($contacts as $con) {
                                if ($con->statutContact != null && str_contains(strtoupper($con->statutContact),  'DIRIGEANT')) {
                                    $trouve = true;
                                    $contact = $con;
                                    break;
                                }
                            }
                            if (!$trouve) {
                                foreach ($contacts as $con) {
                                    if ($con->statutContact != null && str_contains(strtoupper($con->statutContact),  'RESPONSABLE')) {
                                        $trouve = true;
                                        $contact = $con;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $o->contact = $contact;
            $appCon = false;
            if ($app && $contact) {
                $this->db->query("SELECT * FROM wbcc_appartement_contact  WHERE  idAppartementF=$app->idApp AND idContactF=$contact->idContact LIMIT 1");
                $appCon = $this->db->single();
            }
            $o->appCon  = $appCon;
            $guidComp = $o->typeSinistre == "Partie commune exclusive" ? $o->guidComMRI : $o->guidComMRH;
            $this->db->query("SELECT * FROM wbcc_company, wbcc_company_opportunity  WHERE idCompany=idCompanyF AND idOpportunityF=$o->idOpportunity AND category LIKE '%ASSURANCE%'  LIMIT 1");
            $cie = $this->db->single();
            $o->cie = $cie;
            $this->db->query("SELECT * FROM wbcc_releve_technique WHERE idOpportunityF=$o->idOpportunity ORDER BY idRT DESC LIMIT 1");
            $rt = $this->db->single();
            $o->rt = $rt;
            //RV RT
            $this->db->query("SELECT * FROM wbcc_rendez_vous WHERE idOpportunityF=$o->idOpportunity AND typeRV = 'RTP' LIMIT 1");
            $o->rv = $this->db->single();
            //RV EXPERTISE
            $this->db->query("SELECT * FROM wbcc_rendez_vous WHERE idOpportunityF=$o->idOpportunity AND typeRV = 'EXPERTISEP' LIMIT 1");
            $o->rvExp = $this->db->single();
            //RF RT
            $this->db->query("SELECT * FROM wbcc_recherche_fuite WHERE idOpportunityF=$o->idOpportunity LIMIT 1");
            $o->rf = $this->db->single();
            //expertise
            $this->db->query("SELECT * FROM wbcc_expertise WHERE idOpportunityF=$o->idOpportunity LIMIT 1");
            $o->expertise = $this->db->single();
            //RV RT
            $this->db->query("SELECT * FROM wbcc_rendez_vous WHERE idOpportunityF=$o->idOpportunity AND typeRV = 'RFP' LIMIT 1");
            $o->rvRF = $this->db->single();
            //RV TRAVAUX
            $this->db->query("SELECT * FROM wbcc_rendez_vous WHERE idOpportunityF=$o->idOpportunity AND typeRV = 'TRAVAUX' LIMIT 1");
            $o->rvTravaux = $this->db->single();
            // Archi Immeuble
            $this->db->query("SELECT *, c.name as nameCompany FROM wbcc_company c, wbcc_opportunity o,wbcc_company_immeuble  WHERE idCompany=idCompanyF AND idImmeubleF=o.idImmeuble AND o.idOpportunity=$o->idOpportunity AND category LIKE '%ARCHITECTE%' AND o.idImmeuble=$o->idImmeuble  LIMIT 1");
            $architecte = $this->db->single();
            $o->architecte = $architecte;
            return $o;
        } else {
            return null;
        }
    }

    public function getOpByStatut($statut,  $roleInterne = '', $role = '', $idUser = '', $typeOP = '', $site = '')
    {
        $tab = [];
        $where = "";
        if ($statut == "Won" || $statut == "Lost" || $statut == "Inactive" ||  $statut == "Open") {
            $where = " AND status='$statut' ";
        } else {
            if ($statut == "demandeCloture") {
                $where = " AND status='Open' AND demandeCloture=1 ";
            }
            if ($statut == "demandeValidation") {
                $where = " AND  (status='Open' OR status IS NULL) AND name LIKE '%X%' ";
            }
        }
        // $req = $roleInterne == "" ? ($where == "" ? "WHERE etatOP = 1" : "AND etatOP = 1") : "";
        $req = "";
        if ($role == "Gestionnaire EXTERNE DOSSIER" || $typeOP == "me") {
            $req .=  " AND gestionnaire = '$idUser' ";
        } else {
            if ($typeOP == "site") {
                $this->db->query("SELECT * FROM wbcc_utilisateur WHERE idUtilisateur = $idUser LIMIT 1");
                $user =  $this->db->single();
                $req .=  " AND s.nomSite = '$site' ";
            }
        }
        $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_contact c, wbcc_utilisateur u, wbcc_site s WHERE o.gestionnaire = u.idUtilisateur AND u.idContactF = c.idContact AND s.idSite=u.idSiteF AND etatOP = 1 $where $req ORDER BY name DESC");
        $data =  $this->db->resultSet();
        // foreach ($data as $o) {
        //     $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
        //     $immeuble = $this->db->single();
        //     $app = false;
        //     if ($immeuble) {
        //     } else {
        //         $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF  AND codeWBCC='$o->appartement' LIMIT 1");
        //         $app = $this->db->single();
        //     }
        //     $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
        //     $tab[] = $o;
        // }
        return $data;
    }

    public function getOpDeclarationComplet($type, $typeTache = "", $idUser = '', $typeOP = "", $site = '')
    {
        $req = "";
        if ($idUser != '' && $idUser != '0' && $idUser != null) {
            if ($typeOP == "site") {
                $this->db->query("SELECT * FROM wbcc_utilisateur WHERE idUtilisateur = $idUser LIMIT 1");
                $user =  $this->db->single();
                $req .=  "  s.nomSite = '$site' AND ";
            } else {
                $req .= " o.gestionnaire = '$idUser' AND ";
            }
        }
        if ($typeTache == "declarationCie") {
            $req .= $type == "tel" ? " o.declarationCie!=1 AND " : " o.declarationCieMail=0 AND ";

            if ($type == "tel") {
                if ($idUser == '' || $idUser == '0' || $idUser == null) {
                    $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a, wbcc_releve_technique rt, wbcc_utilisateur u, wbcc_contact c, wbcc_site s  WHERE o.gestionnaire = u.idUtilisateur AND u.idContactF = c.idContact AND u.idSiteF = s.idSite AND rt.idOpportunityF= o.idOpportunity AND rt.cause != '' AND rt.cause IS NOT NULL  AND  rt.precisionComplementaire != '' AND rt.precisionComplementaire IS NOT NULL AND libellePieces != ''  AND  o.delegationSigne=1 AND  (o.teleExpertiseFaite = 1) AND  $req a.isCleared='False'  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND a.codeActivity=4  AND o.type='Sinistres' AND (SELECT urlDocument FROM `wbcc_opportunity_document` od, wbcc_document d WHERE d.idDocument = od.idDocumentF AND od.idOpportunityF=o.idOpportunity AND lower(urlDocument) like '%delegation%' AND lower(urlDocument) NOT LIKE '%-x%' ORDER BY createDate DESC LIMIT 1) LIKE '%sign%' AND etatOP = 1  GROUP BY idOpportunity ORDER BY name DESC");
                } else {
                    $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a , wbcc_releve_technique rt , wbcc_utilisateur u,  wbcc_contact c, wbcc_site s  WHERE o.gestionnaire = u.idUtilisateur AND u.idContactF = c.idContact AND u.idSiteF = s.idSite AND   rt.idOpportunityF= o.idOpportunity AND rt.cause != '' AND rt.cause IS NOT NULL  AND  rt.precisionComplementaire != '' AND rt.precisionComplementaire IS NOT NULL AND libellePieces != ''   AND o.delegationSigne=1 AND (o.teleExpertiseFaite = 1) AND $req a.isCleared='False'  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND a.codeActivity=4  AND o.type='Sinistres' AND (SELECT urlDocument FROM `wbcc_opportunity_document` od, wbcc_document d WHERE d.idDocument = od.idDocumentF AND od.idOpportunityF=o.idOpportunity AND lower(urlDocument) like '%delegation%'  AND lower(urlDocument) NOT LIKE '%-x%' ORDER BY createDate DESC LIMIT 1) LIKE '%sign%' AND etatOP = 1  GROUP BY idOpportunity ORDER BY name DESC");
                }
            } else {
                if ($idUser == '' || $idUser == '0' || $idUser == null) {
                    $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a , wbcc_utilisateur u,  wbcc_contact c, wbcc_site s  WHERE o.gestionnaire = u.idUtilisateur AND u.idContactF = c.idContact AND u.idSiteF = s.idSite AND  o.delegationSigne=1 AND $req a.isCleared='False'  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND lower(a.regarding) LIKE '%faire la declaration%'  AND o.type='Sinistres' AND (SELECT urlDocument FROM `wbcc_opportunity_document` od, wbcc_document d WHERE d.idDocument = od.idDocumentF AND od.idOpportunityF=o.idOpportunity AND lower(urlDocument) like '%delegation%' AND etatOP = 1  AND lower(urlDocument) NOT LIKE '%-x%' ORDER BY createDate DESC LIMIT 1) LIKE '%sign%'  GROUP BY idOpportunity ORDER BY name DESC");
                } else {
                    $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a , wbcc_utilisateur u,  wbcc_contact c, wbcc_site s  WHERE o.gestionnaire = u.idUtilisateur AND u.idContactF = c.idContact AND u.idSiteF = s.idSite AND  $req o.delegationSigne=1 AND  a.isCleared='False'  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND lower(a.regarding) LIKE '%faire la declaration%'  AND o.type='Sinistres' AND (SELECT urlDocument FROM `wbcc_opportunity_document` od, wbcc_document d WHERE d.idDocument = od.idDocumentF AND od.idOpportunityF=o.idOpportunity AND lower(urlDocument) like '%delegation%'  AND lower(urlDocument) NOT LIKE '%-x%' AND etatOP = 1 ORDER BY createDate DESC LIMIT 1) LIKE '%sign%'  GROUP BY idOpportunity ORDER BY name DESC");
                }
            }
        } else {
            if ($typeTache == "constatDDE") {
                $codeActivity = 24;
                $req .= $type == "attente" ?  "  o.idOpportunity NOT IN (SELECT idOpportunityF FROM wbcc_recherche_fuite WHERE demandeSignatureEnvoye=1)" :  "   o.idOpportunity IN (SELECT idOpportunityF FROM wbcc_recherche_fuite WHERE demandeSignatureEnvoye=1)";
            } else {
                $codeActivity = 32;
                $req .= $type == "attente" ?   "  o.idOpportunity NOT IN (SELECT idOpportunityF FROM wbcc_recherche_fuite WHERE demandeJustificatifEnvoye=1)" :  "   o.idOpportunity IN (SELECT idOpportunityF FROM wbcc_recherche_fuite WHERE demandeJustificatifEnvoye=1)";
            }

            if ($idUser == '' || $idUser == '0' || $idUser == null) {
                $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a, wbcc_releve_technique rt, wbcc_utilisateur u, wbcc_contact c, wbcc_site s  WHERE o.gestionnaire = u.idUtilisateur AND u.idContactF = c.idContact AND u.idSiteF = s.idSite AND rt.idOpportunityF= o.idOpportunity AND  a.isCleared='False'  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND a.codeActivity=$codeActivity  AND o.type='Sinistres'  AND etatOP = 1 AND $req GROUP BY idOpportunity ORDER BY name DESC");
            } else {
                $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a , wbcc_releve_technique rt , wbcc_utilisateur u,  wbcc_contact c, wbcc_site s  WHERE o.gestionnaire = u.idUtilisateur AND u.idContactF = c.idContact AND u.idSiteF = s.idSite AND   rt.idOpportunityF= o.idOpportunity AND  a.isCleared='False'  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND a.codeActivity=$codeActivity  AND o.type='Sinistres'  AND etatOP = 1 AND $req GROUP BY idOpportunity ORDER BY name DESC");
            }
        }
        $data =  $this->db->resultSet();
        return $data;
    }

    public function getOPAnomalies($codeActivity, $type, $periode = '', $guidCommercial = '', $idCommercial = '', $role = '')
    {
        $req = "";
        if ($periode == 'nouveau') {
            $req = " AND o.createDate >= '2024-02-01' ";
        } else {
            $req = " AND o.createDate < '2024-02-01' ";
        }

        if ($role == "Gestionnaire EXTERNE DOSSIER") {
            $req = $req . " AND o.gestionnaire = '$idCommercial' ";
        } else {
            if ($guidCommercial != "") {
                $req = $req . " AND (o.guidCommercial='$guidCommercial' OR o.source=$idCommercial OR idAuteurCreation=$idCommercial ) ";
            }
        }
        $idSite = "";
        if ($role == "Manager de Site" || $role == "Gestionnaire") {
            $idSite = $_SESSION['connectedUser']->idSiteF;
            $req .= " AND o.gestionnaire IN (SELECT idUtilisateur FROM wbcc_utilisateur WHERE idSiteF = $idSite) ";
        }

        if ($type == 'pap') {
            $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a, wbcc_opportunity_papb2c_contact pap WHERE pap.idOpportunityF=o.idOpportunity AND o.idOpportunity < 4123 AND a.isCleared='False' AND o.typeSinistre LIKE '%privative%' AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND codeActivity=$codeActivity AND o.type='Sinistres' AND etatOP = 1 GROUP BY idOpportunity ORDER BY name ASC");
        } else {
            if ($type == "horspap") {
                $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a WHERE o.idOpportunity < 4123 AND a.isCleared='False' AND o.typeSinistre LIKE '%privative%' AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND codeActivity=$codeActivity AND o.type='Sinistres' AND o.idOpportunity NOT IN (SELECT idOpportunity FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a, wbcc_opportunity_papb2c_contact pap WHERE pap.idOpportunityF=o.idOpportunity AND o.idOpportunity < 4123 AND a.isCleared='False' AND o.typeSinistre LIKE '%privative%' AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND codeActivity=$codeActivity AND o.type='Sinistres' GROUP BY idOpportunity) AND etatOP = 1 GROUP BY idOpportunity ORDER BY name ASC ");
            } else {
                $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a WHERE a.isCleared='False' AND o.typeSinistre LIKE '%privative%' AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND (codeActivity=$codeActivity  OR codeActivity=3 ) AND o.type='Sinistres' AND o.incidentSignale=1 AND etatOP = 1 $req GROUP BY idOpportunity ORDER BY name ASC ");
            }
        }
        $data =  $this->db->resultSet();
        return $data;
    }

    public function getOpAudit($column, $role = "", $isGestionnaire = "", $idUtilisatuer = "", $flitres = array())
    {
        $sql = "SELECT * FROM wbcc_opportunity o WHERE o.name NOT LIKE '%X%' ";
        $statut = isset($flitres[0]) ? $flitres[0] : 'clotures';
        $site = isset($flitres[1]) ? $flitres[1] : 'tous';
        $gestionnaire = isset($flitres[2]) ? $flitres[2] : $isGestionnaire;
        $etat = isset($flitres[3]) ? $flitres[3] : 'tous';

        // if (($role == 29 || $role == 33 || $isGestionnaire == 1) && $role != 1) {
        //     $sql .= " AND o.gestionnaire =$idUtilisatuer ";
        // } else
        {
            /********* filtre par gestionnaire ********** */
            if ($gestionnaire != 'tous') {
                $sql .= " AND o.gestionnaire =$gestionnaire ";
            }
            /********* filtre par site ********** */
            if ($site != 'tous') {
                if ($site == 'me') {
                    $sql .= " AND o.gestionnaire IN ($idUtilisatuer) ";
                } else {
                    $sql .= " AND o.gestionnaire IN (SELECT idUtilisateur FROM wbcc_utilisateur WHERE idSiteF=$site) ";
                }
            }
        }
        /********* filtre sur OP par statut ********** */
        if ($statut == "enCours") {
            $sql .= " AND status='Open' ";
        } else {
            if ($statut == "clotures") {
                $sql .= " AND ( status='Won' OR status='Lost') ";
            } else {
                if ($statut == "attenteCloture") {
                    $sql .= " AND status='Open' AND demandeCloture=1 ";
                } else {
                    if ($statut == "attenteValidation") {
                        $sql .= " AND  (status='Open' OR status IS NULL) AND name LIKE '%X%' ";
                    }
                }
            }
        }


        /********* filtre sur OP audité ou pas ********** */
        if ($column == "reglement") {
            if ($etat == '1') {
                $sql .= " AND auditReglement != 1  ";
            } elseif ($etat == '2') {
                $sql .= " AND auditReglement = 1 ";
            }
            $sql .= " AND type LIKE '%Sinistre%' AND ( status='Won' OR status='Lost' OR status='Open') ";
        }
        if ($column == "rvrt") {
            if ($etat == '1') {
                $sql .= " AND auditRvRt IS NULL ";
            } elseif ($etat == '2') {
                $sql .= " AND auditRvRt IS NOT NULL ";
            }
        }
        if ($column == "declaration") {
            if ($etat == '1') {
                $sql .= " AND auditDeclaration IS NULL ";
            } elseif ($etat == '2') {
                $sql .= " AND auditDeclaration IS NOT NULL ";
            }

            $req = " AND createDate >= '2023-08-01' AND auditDeclaration IS NULL ";
        }
        if ($column == "devis") {
            if ($etat == '1') {
                $sql .= " AND auditDevis IS NULL ";
            } elseif ($etat == '2') {
                $sql .= " AND auditDevis IS NOT NULL ";
            }
            $req = " AND createDate >= '2023-08-01' AND auditDevis IS NULL";
        }
        if ($column == "franchise") {
            if ($etat == '1') {
                $sql .= " AND auditFranchise IS NULL ";
            } elseif ($etat == '2') {
                $sql .= " AND auditFranchise IS NOT NULL ";
            }
            $req = " AND auditFranchise IS NULL";
        }
        if ($column == "anciennesOP") {
            if ($etat == '1') {
                $sql .= " AND o.auditAncienneOp <> 1 ";
            } elseif ($etat == '2') {
                $sql .= " AND o.auditAncienneOp = 1 ";
            }
        }

        $sql .= " AND etatOP = 1 ORDER BY o.name DESC";


        $this->db->query($sql);
        $data =  $this->db->resultSet();
        return $data;
    }

    public function getOpTache($tache, $roleInterne = '', $idUser = "", $typeOP = '', $site = "")
    {
        $req = "";
        // $req = $roleInterne == "" ?  "AND etatOP = 1" : "";
        if ($tache == "9" || $tache == "16") {
            $req .= "  AND ((o.typeSinistre='Partie privative exclusive' AND o.sinistreMRH != '' AND o.sinistreMRH IS NOT NULL) OR (o.typeSinistre='Partie commune exclusive' AND o.sinistreMRI != '' AND o.sinistreMRi IS NOT NULL)) ";
            // $req .= " AND o.controleFRT=1 AND o.controleRT=1 AND o.declarationCie=1 AND ((o.typeSinistre='Partie privative exclusive' AND o.sinistreMRH != '' AND o.sinistreMRH IS NOT NULL) OR (o.typeSinistre='Partie commune exclusive' AND o.sinistreMRI != '' AND o.sinistreMRi IS NOT NULL)) ";
        }
        if ($tache == "10") {
            // $req .= "  AND o.controleFRT=1 AND o.controleFRT2=1 AND o.controleFRT3=1 AND ((o.typeSinistre='Partie privative exclusive' AND o.sinistreMRH != '' AND o.sinistreMRH IS NOT NULL) OR (o.typeSinistre='Partie commune exclusive' AND o.sinistreMRI != '' AND o.sinistreMRi IS NOT NULL)) AND o.envoiDevis=1 AND o.devisFais=1 ";
        }
        if ($tache == "11") {
            // if ($etape == "1") {
            //     $req .= " AND o.controleFRT=0 AND (o.idAuteurControleFRT2 IS NULL OR o.idAuteurControleFRT2 != $idUser ) AND (o.idAuteurControleFRT3 IS NULL OR o.idAuteurControleFRT3 != $idUser ) ";
            // }
            // if ($etape == "2") {
            //     $req .= " AND o.controleFRT2=0 AND (o.idAuteurControleFRT IS NULL OR o.idAuteurControleFRT != $idUser ) AND (o.idAuteurControleFRT3 IS NULL OR o.idAuteurControleFRT3 != $idUser ) ";
            // }
            // if ($etape == "3") {
            //     $req .= " AND o.controleFRT3=0 AND (o.idAuteurControleFRT IS NULL OR o.idAuteurControleFRT != $idUser ) AND (o.idAuteurControleFRT2 IS NULL OR o.idAuteurControleFRT2 != $idUser ) ";
            // }
            $idUser = "";
        }
        $req2 = '';
        if ($tache == '27') {
            $req2 = ' OR codeActivity=37';
        }

        if ($tache == "15") {
            $req2 = ' AND  ( NOT ISNULL(o.idExpertCompanyF) AND NOT ISNULL(o.idExpertCompanyF) )';
        }

        if ($tache == "19") {
            $req2 = ' OR  ( (codeActivity=14 AND ISNULL(o.idExpertCompanyF) AND IS NULL(o.idExpertCompanyF)) OR (codeActivity=15 AND IS NULL(o.idExpertCompanyF) AND IS NULL(o.idExpertCompanyF)) )';
        }

        if ($idUser != '' && $idUser != '0' && $idUser != null) {
            if ($typeOP == "site") {
                $this->db->query("SELECT * FROM wbcc_utilisateur WHERE idUtilisateur = $idUser LIMIT 1");
                $user =  $this->db->single();
                $req .=  " AND s.nomSite = '$site'";
            } else {
                $req .= " AND o.gestionnaire = $idUser ";
            }
        }

        if ($idUser != '' && $idUser != '0' && $idUser != null) {
            $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a, wbcc_contact c, wbcc_utilisateur u, wbcc_site s WHERE o.gestionnaire = u.idUtilisateur AND u.idContactF = c.idContact AND u.idSiteF=s.idSite AND  a.isCleared='False'  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND codeActivity=:code AND o.type='Sinistres' AND etatOP = 1 $req  GROUP BY idOpportunity ORDER BY name DESC");
            $this->db->bind("code", $tache, null);
        } else {
            $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a,  wbcc_contact c, wbcc_utilisateur u, wbcc_site s WHERE o.gestionnaire = u.idUtilisateur AND u.idContactF = c.idContact AND u.idSiteF=s.idSite AND  a.isCleared='False'  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND (codeActivity=:code $req2) AND o.type='Sinistres' AND etatOP = 1 $req GROUP BY idOpportunity ORDER BY name DESC");
            $this->db->bind("code", $tache, null);
        }
        $data =  $this->db->resultSet();
        return $data;
    }

    public function getOpTachePAP($tache, $roleInterne = '')
    {
        $req = $roleInterne == "" ?  "AND etatOP = 1" : ""; {
            $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a, wbcc_opportunity_papb2c_contact pap WHERE pap.idOpportunityF=o.idOpportunity AND a.isCleared='False'  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND codeActivity=:code AND o.type='Sinistres' AND etatOP = 1 $req GROUP BY idOpportunity ORDER BY name DESC");
            $this->db->bind("code", $tache, null);
        }
        $data =  $this->db->resultSet();
        return $data;
    }

    public function getOpTacheAndUserPAP($tache, $roleInterne = '', $idUser)
    {
        $req = $roleInterne == "" ?  "AND etatOP = 1" : ""; {
            $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_opportunity_activity oa, wbcc_activity a, wbcc_opportunity_papb2c_contact pap WHERE pap.idOpportunityF=o.idOpportunity AND  a.idUtilisateurF = $idUser AND a.isCleared='False'  AND o.status = 'Open' AND o.name NOT LIKE '%X%' AND o.idOpportunity=oa.idOpportunityF AND a.idActivity=oa.idActivityF AND codeActivity=:code AND o.type='Sinistres' AND etatOP = 1 $req GROUP BY idOpportunity ORDER BY name DESC");
            $this->db->bind("code", $tache, null);
        }
        $data =  $this->db->resultSet();
        return $data;
    }

    public function getActivitesFutures($id, $type = "")
    {
        if ($type == "") {
            $this->db->query("SELECT * FROM wbcc_activity a, wbcc_opportunity_activity co
            WHERE a.publie=1 AND a.idActivity = co.idActivityF AND co.idOpportunityF = $id AND a.isCleared='False' ");
        } else {
            $this->db->query("SELECT * FROM wbcc_activity a, wbcc_opportunity_activity co
            WHERE  a.idActivity = co.idActivityF AND co.idOpportunityF = $id AND a.isCleared='False' ");
        }

        return $this->db->resultSet();
    }

    public function getActivitesPassees($id, $type = "")
    {
        if ($type == "") {
            // $this->db->query("SELECT * FROM wbcc_activity a, wbcc_opportunity_activity co WHERE  a.publie=1 AND a.idActivity = co.idActivityF AND co.idOpportunityF = $id AND startTime < NOW()");
            $this->db->query("SELECT * FROM wbcc_activity a, wbcc_opportunity_activity co WHERE  a.publie=1 AND a.idActivity = co.idActivityF AND co.idOpportunityF = $id AND a.isCleared='True'");
        } else {
            $this->db->query("SELECT * FROM wbcc_activity a, wbcc_opportunity_activity co
            WHERE a.idActivity = co.idActivityF AND co.idOpportunityF = $id AND a.isCleared='True' ");
        }
        return $this->db->resultSet();
    }

    //COMPANY
    public function getOpportunityByCompanyAndStatus($idCompany, $statut)
    {
        $where = "";
        if ($statut == "Won" || $statut == "Lost" || $statut == "Inactive" ||  $statut == "Open") {
            $where = " AND status='$statut' ";
        } else {
            if ($statut == "demandeCloture") {
                $where = " AND status='Open' AND demandeCloture=1 ";
            }
            if ($statut == "demandeValidation") {
                $where = " AND  (status='Open' OR status IS NULL) AND name LIKE '%X%' ";
            }
        }
        if (Role::connectedUser()->idRole == "28") {
            $where .= " AND i.idChefSecteur = " . Role::connectedUser()->idContact . " ";
        }
        $this->db->query("SELECT *, o.createDate as createDateOP FROM wbcc_opportunity o, wbcc_immeuble i WHERE o.idImmeuble = i.idImmeuble AND  o.idGestionnaireAppImm = $idCompany  AND etatOP = 1 $where ORDER BY name DESC");
        $data =  $this->db->resultSet();
        return $data;
    }


    //A SUPPRIMER
    public function  getOpportunityByCompany($id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o
        WHERE o.idGestionnaireAppImm = $id  $req ORDER BY name DESC");
        $data = $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }
            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }


    public function  getOpportunityEnCoursByCompany($id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_company_opportunity co 
        WHERE o.idOpportunity = co.idOpportunityF AND co.idCompanyF = $id AND (status='Open' OR status='Inactive') AND o.name NOT LIKE '%X%' $req ORDER BY name DESC");
        $data =  $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }
            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF  AND codeWBCC='$o->appartement' LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }

    public function  getOpportunityClotureByCompany($id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_company_opportunity co 
        WHERE o.idOpportunity = co.idOpportunityF AND co.idCompanyF = $id AND  (status='Lost' OR status='Won') $req ORDER BY name DESC");
        $data =  $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }
            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }

    public function  getOpportunityAttenteValidationByCompany($id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_company_opportunity co 
        WHERE o.idOpportunity = co.idOpportunityF AND co.idCompanyF = $id AND (status='Open' OR status='Inactive' OR status is NULL) AND o.name LIKE '%X%' $req ORDER BY name DESC");
        $data =  $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }
            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF AND codeWBCC='$o->appartement' LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }

    public function  getOpportunityAttenteClotureByCompany($id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_company_opportunity co 
        WHERE o.idOpportunity = co.idOpportunityF AND demandeCloture=1 AND co.idCompanyF = $id $req ORDER BY name DESC");
        $data =  $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }
            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }

    //CONTACT
    public function  getOpportunityByContact($id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_contact_opportunity co 
        WHERE o.idOpportunity = co.idOpportunityF AND co.idContactF = $id AND etatOP = 1 $req ORDER BY name DESC");
        $data =  $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }

            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }

    public function  getOpportunityEnCoursByContact($id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_contact_opportunity co 
        WHERE o.idOpportunity = co.idOpportunityF AND co.idContactF = $id AND (status='Open' OR status='Inactive')  $req GROUP BY idOpportunity ORDER BY name DESC");
        $data =  $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }
            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }

    public function  getOpportunityClotureByContact($id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_contact_opportunity co 
        WHERE o.idOpportunity = co.idOpportunityF AND co.idContactF = $id AND  (status='Lost' OR status='Won') $req ORDER BY name DESC");
        $data =  $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }
            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }

    public function  getOpportunityAttenteValidationByContact($id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_contact_opportunity co 
        WHERE o.idOpportunity = co.idOpportunityF AND demandeValidation=0 AND co.idContactF = $id $req ORDER BY name DESC");
        $data =  $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }
            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }

    public function  getOpportunityAttenteClotureByContact($id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_contact_opportunity co 
        WHERE o.idOpportunity = co.idOpportunityF AND demandeCloture=1 AND co.idContactF = $id $req ORDER BY name DESC");
        $data =  $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }
            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }

    //CONTACT COMPANY
    public function  getOpByContactCompany($idCompany, $id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_contact_opportunity co, wbcc_company_opportunity cop 
           WHERE o.idOpportunity = co.idOpportunityF AND co.idContactF = $id AND cop.idOpportunityF = o.idOpportunity AND cop.idCompanyF=$idCompany $req ORDER BY name DESC");
        $data =  $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }

            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }

    public function  getOpEnCoursByContactCompany($idCompany, $id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_contact_opportunity co , wbcc_company_opportunity cop 
           WHERE o.idOpportunity = co.idOpportunityF AND co.idContactF = $id AND cop.idOpportunityF = o.idOpportunity AND cop.idCompanyF=$idCompany AND (status='Open' OR status='Inactive') AND demandeCloture=0 AND demandeValidation=1 $req ORDER BY name DESC");
        $data =  $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }
            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }

    public function  getOpClotureByContactCompany($idCompany, $id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_contact_opportunity co  , wbcc_company_opportunity cop 
           WHERE o.idOpportunity = co.idOpportunityF AND co.idContactF = $id AND cop.idOpportunityF = o.idOpportunity AND cop.idCompanyF=$idCompany AND  (status='Lost' OR status='Won') $req ORDER BY name DESC");
        $data =  $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }
            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }

    public function  getOpAttenteValidationByContactCompany($idCompany, $id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_contact_opportunity co  , wbcc_company_opportunity cop 
           WHERE o.idOpportunity = co.idOpportunityF AND cop.idOpportunityF = o.idOpportunity AND cop.idCompanyF=$idCompany AND demandeValidation=0 AND co.idContactF = $id $req ORDER BY name DESC");
        $data =  $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }
            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }

    public function  getOpAttenteClotureByContactCompany($idCompany, $id)
    {
        $req = "AND etatOP = 1";
        $tab = [];
        $this->db->query("SELECT * FROM wbcc_opportunity o, wbcc_contact_opportunity co , wbcc_company_opportunity cop  
           WHERE o.idOpportunity = co.idOpportunityF AND cop.idOpportunityF = o.idOpportunity AND cop.idCompanyF=$idCompany AND demandeCloture=1 AND co.idContactF = $id $req ORDER BY name DESC");
        $data =  $this->db->resultSet();
        foreach ($data as $o) {
            $gestionnaire = false;
            if ($o->numGestionnaire != "" && $o->numGestionnaire != null) {
                $this->db->query("SELECT * FROM wbcc_contact c, wbcc_utilisateur u  WHERE idContact = idContactF AND matricule='$o->numGestionnaire' LIMIT 1");
                $gestionnaire =  $this->db->single();
            }
            $this->db->query("SELECT * FROM wbcc_immeuble  WHERE  codeImmeuble='$o->immeuble' LIMIT 1");
            $immeuble = $this->db->single();
            $this->db->query("SELECT * FROM wbcc_appartement, wbcc_opportunity_appartement, wbcc_opportunity  WHERE idOpportunity = idOpportunityF AND idApp = idAppartementF LIMIT 1");
            $app = $this->db->single();
            $o->numGestionnaire = ($gestionnaire) ? $gestionnaire->prenomContact . " " . $gestionnaire->nomContact : $o->numGestionnaire;
            $o->adresse = ($immeuble) ? $immeuble->adresse . " " . $immeuble->codePostal . " " . $immeuble->ville : (($app) ? $app->adresse . " " . $app->codePostal . " " . $app->ville : "");
            $o->nomImmeubleSyndic = ($immeuble) ? $immeuble->nomImmeubleSyndic : "";
            $tab[] = $o;
        }
        return $tab;
    }

    public function changeOpportunityState($idOpportunity, $oldState, $numero)
    {
        $oldState = ($oldState == 1) ? 0 : 1;
        $this->db->query("
            UPDATE wbcc_opportunity
            SET demandeCloture = $oldState,
            dateDemandeCloture='" . date("Y-m-d H:i:s") . "',
            numeroDemandeCloture = '$numero'
            WHERE idOpportunity = $idOpportunity
        ");
        return $this->db->execute();
    }

    public function findById($id, $idCompany)
    {
        $this->db->query("SELECT * FROM wbcc_opportunity  WHERE idGestionnaireAppImm = $idCompany AND idOpportunity = $id LIMIT 1");
        return $this->db->single();
    }

    public function findOpById($id)
    {
        $this->db->query("SELECT * FROM wbcc_opportunity WHERE idOpportunity = $id LIMIT 1");
        return $this->db->single();
    }

    public function findByIdContact($id, $idContact)
    {
        $this->db->query("SELECT * FROM wbcc_opportunity, wbcc_contact_opportunity  WHERE idOpportunity = idOpportunityF AND idContactF = $idContact AND idOpportunity = $id LIMIT 1");
        return $this->db->single();
    }

    public function updateOpportunity(
        $id,
        $name,
        $referenceDO,
        $type,
        $partieConcernee,
        $pm = "",
        $causeMission,
        $typeIntervention,
        $corpsMetier,
        $commentaireOP,
        $numSinistreMRH = '',
        $referenceExpert = ""
    ) {

        $this->db->query("
            UPDATE wbcc_opportunity
            SET name = :name,
            referenceDO=:referenceDO,
            type = :type,
            typeSinistre = :partieConcernee,
            partieMitoyenne = :pm,
            causeMission = :causeMission,
            typeIntervention = :typeIntervention,
            corpsMetier = :corpsMetier,
            commentaire = :commentaireOP,
            sinistreMRH = :numSinistreMRH,
            referenceExpert =:referenceExpert,
            editDate = :editDate
            WHERE idOpportunity = :id
        ");
        $this->db->bind("name", $name, null);
        $this->db->bind("referenceDO", $referenceDO, null);
        $this->db->bind("type", $type, null);
        $this->db->bind("partieConcernee", $partieConcernee, null);
        $this->db->bind("pm", $pm, null);
        $this->db->bind("causeMission", $causeMission, null);
        $this->db->bind("typeIntervention", $typeIntervention, null);
        $this->db->bind("corpsMetier", $corpsMetier, null);
        $this->db->bind("commentaireOP", $commentaireOP, null);
        $this->db->bind("numSinistreMRH", $numSinistreMRH, null);
        $this->db->bind("referenceExpert", $referenceExpert, null);
        $this->db->bind("id", $id, null);
        $this->db->bind("editDate", date("Y-m-d H:i:s"), null);

        return $this->db->execute();
    }

    public function addOpportunity(
        $numero,
        $name,
        $referenceDO,
        $type,
        $partieConcernee,
        $pm,
        $causeMission,
        $typeIntervention,
        $corpsMetier,
        $commentaireOP,
        $guidReferentDO = ""
    ) {
        $this->db->query("
           INSERT INTO wbcc_opportunity 
           (numeroOpportunity, name, referenceDO, type, typeSinistre, partieMitoyenne,
           causeMission,typeIntervention, corpsMetier, demandeValidation, dateDemandeValidation, commentaire, source ) 
           VALUES ('$numero', '$name', '$referenceDO', '$type', '$partieConcernee', 
           '$pm', '$causeMission','$typeIntervention', '$corpsMetier',0,'" . date("Y-m-d H:i:s") . "', '$commentaireOP', '$guidReferentDO')");
        if ($this->db->execute()) {
            $op = $this->findByNumero($numero);
            return $op->idOpportunity;
        } else {
            return "0";
        }
    }

    public function insertCompanyOpportunity($idCompany, $idOpportunity)
    {
        $this->db->query("
           INSERT INTO wbcc_company_opportunity 
           (idCompanyF, idOpportunityF) 
           VALUES ($idCompany,$idOpportunity)");
        $this->db->execute();
    }

    public function insertContactOpportunity($idContact, $idOpportunity)
    {
        $this->db->query("
           INSERT INTO wbcc_contact_opportunity 
           (idContactF, idOpportunityF) 
           VALUES ($idContact,$idOpportunity)");
        $this->db->execute();
    }

    public function insertLotOpportunity($idApp, $idOpportunity)
    {
        $this->db->query("
           INSERT INTO wbcc_opportunity_appartement 
           (idOpportunityF, idAppartementF) 
           VALUES ($idOpportunity,$idApp)");
        $this->db->execute();
    }

    public function insertImmeubleOpportunity($idImmeuble, $idOpportunity)
    {
        $this->db->query("
           INSERT INTO wbcc_opportunity_immeuble 
           (idOpportunityF, idImmeubleF) 
           VALUES ($idOpportunity,$idImmeuble)");
        $this->db->execute();
    }

    public function findByReferenceDO($ref)
    {
        $req = "AND etatOP = 1";
        $this->db->query("SELECT * FROM wbcc_opportunity WHERE referenceDO = '$ref' $req LIMIT 1");
        return $this->db->single();
    }
    public function findByNumero($numero)
    {
        $this->db->query("SELECT * FROM wbcc_opportunity WHERE numeroOpportunity = $numero");
        if ($this->db->single()) {
            return $this->db->single();
        } else {
            return null;
        }
    }

    /**********  Debut Espoir *****************/

    public function getExpertCompagnie($idOp)
    {
        $this->db->query("SELECT * FROM wbcc_opportunity 
                        JOIN wbcc_company ON (idCompany=idCabinetExpertF)
                        LEFT JOIN wbcc_contact ON (idContact=idExpertCompanyF)
                        WHERE idOpportunity = $idOp ");
        return $this->db->single();
    }

    public function getRDVExpertiseOp($idOp)
    {
        $this->db->query("SELECT rv.*, ag.* FROM wbcc_rendez_vous rv
                            JOIN wbcc_opportunity op ON (rv.idOpportunityF=op.idOpportunity)
                            JOIN wbcc_evenement_agenda ag ON(ag.idOpportunityF=op.idOpportunity)
                            WHERE rv.isProvisoire=1 AND rv.idOpportunityF=$idOp AND rv.typeRV='EXPERTISEP'
                            AND rv.expert <> 'Non Attribué' AND rv.idExpertF <> 583
                            AND ag.organisateur <> 'Non Attribué' AND ag.idOrganisateur <> 583; ");
        return $this->db->single();
    }

    /********** fin  Debut Espoir *****************/
}