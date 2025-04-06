<?php

class Enveloppe extends Model
{
    function getJournalByIdJournal($id)
    {
        $this->db->query("SELECT * FROM wbcc_journal WHERE idJournal=$id");
        $journaux = $this->db->single();

        return $journaux;
    }

    function getAllJournaux()
    {
        $this->db->query("SELECT * FROM wbcc_journal ORDER BY idJournal DESC");
        $journaux = $this->db->resultSet();

        return $journaux;
    }

    function getEncaissementsByIdJournal($idJournal)
    {
        $this->db->query("SELECT * FROM wbcc_encaissement WHERE idJournalF=$idJournal ORDER BY idEncaissement DESC");
        $encaissements = $this->db->resultSet();

        return $encaissements;
    }

    function getAllEncaissementsOP($statut, $type, $gestionnaire, $typeIntervention, $commercial, $periode, $date1, $date2)
    {
        $idUser = $_SESSION['connectedUser']->idUtilisateur;
        $sql = "";

        //FILTRER PAR STATUT
        if ($statut == "enCours") {
            $sql .= " AND ( status !='Won' AND status !='Lost') ";
        } else {
            if ($statut == "clotures") {
                $sql .= " AND ( status='Won' OR status='Lost') ";
            } else {
                // if ($statut == "attenteCloture") {
                //     $sql .= " AND status='Open' AND demandeCloture=1 ";
                // } else {
                //     if ($statut == "attenteValidation") {
                //         $sql .= " AND  (status='Open' OR status IS NULL) AND name LIKE '%X%' ";
                //     }
                // }
            }
        }
        //FILTRER PAR TYPE INTERVENTION
        if ($typeIntervention == "AMO") {
            $sql .= " AND type LIKE '%A.M.O%' ";
        } else {
            if ($typeIntervention == "SINISTRE") {
                $sql .= " AND type LIKE '%sinistre%' ";
            }
        }
        //FILTRER PAR SITE
        if ($type != 'tous') {
            if ($type == 'me') {
                $sql .= " AND o.gestionnaire IN ($idUser) ";
            } else {
                $sql .= " AND o.gestionnaire IN (SELECT idUtilisateur FROM wbcc_utilisateur WHERE idSiteF=$type) ";
            }
        }
        //FILTRER PAR GESTIONNAIRE
        if ($gestionnaire != 'tous') {
            $sql .= " AND o.gestionnaire =$gestionnaire ";
        }
        //FILTRER PAR COMMERCIAL
        if ($commercial != 'tous') {
            $sql .= " AND o.idCommercial =$commercial ";
        }
        //FILTRER PAR PERIODE
        $today = date("Y-m-d");
        if ($periode != "" && $periode != "all") {
            if ($periode == "today") {
                $sql .= " AND dateEncaissement = '$today' ";
            } else {
                if ($periode == "day") {
                    $sql .= " AND dateEncaissement = '$date1' ";
                } else {
                    $sql .= " AND   dateEncaissement >= '$date1' AND dateEncaissement <= '$date2'  ";
                }
            }
        }

        $sql = "SELECT * FROM wbcc_encaissement, wbcc_journal, wbcc_opportunity o, wbcc_utilisateur u, wbcc_contact c, wbcc_site s WHERE s.idSite = u.idSiteF AND u.idUtilisateur=o.gestionnaire AND c.idContact = u.idContactF AND idOpportunity=idOPEncaissement AND idJournalF=idJournal $sql ORDER BY idEncaissement DESC";

        $this->db->query($sql);
        $encaissements = $this->db->resultSet();
        foreach ($encaissements as $key => $enc) {
            $this->db->query("SELECT * FROM wbcc_immeuble WHERE idImmeuble =:idImmeuble LIMIT 1");
            $this->db->bind("idImmeuble", ($enc->idImmeuble != null && $enc->idImmeuble != "" ? $enc->idImmeuble : null), null);
            $im = $this->db->single();
            $enc->immeuble = $im;
        }
        return $encaissements;
    }

    function getAllEncaissements()
    {
        $this->db->query("SELECT * FROM wbcc_encaissement, wbcc_journal WHERE idJournalF=idJournal ORDER BY idEncaissement DESC");
        $encaissements = $this->db->resultSet();

        return $encaissements;
    }

    function getAllCheques()
    {
        $this->db->query("SELECT * FROM wbcc_cheque,wbcc_enveloppe WHERE idEnveloppeF=idEnveloppe ORDER BY idCheque DESC");
        $cheques = $this->db->resultSet();

        return $cheques;
    }

    function findChequeById($id)
    {
        $this->db->query("SELECT * FROM wbcc_cheque, wbcc_enveloppe,wbcc_devis WHERE idEnveloppeF=idEnveloppe AND idCheque =$id AND idDevisF=idDevis");
        $cheque = $this->db->single();
        return $cheque;
    }

    function getCompteBancaire()
    {
        $this->db->query("SELECT * FROM wbcc_compte_bancaire WHERE etatCompteBancaire=1");
        $comptes = $this->db->resultSet();

        return $comptes;
    }

    function findEnveloppeOuvert()
    {
        $this->db->query("SELECT * FROM wbcc_enveloppe WHERE etatEnveloppe=0 ORDER BY idEnveloppe DESC LIMIT 1");
        $env = $this->db->single();
        if ($env) {
            $this->db->query("SELECT * FROM wbcc_cheque WHERE idEnveloppeF=$env->idEnveloppe ORDER BY idCheque DESC");
            $env->cheques = $this->db->resultSet();
        }
        return $env;
    }

    function saveEnveloppe($numero)
    {
        $date = date('Y-m-d H:i');
        $this->db->query("INSERT INTO wbcc_enveloppe(numeroEnveloppe, dateCreationEnveloppe) VALUES (:numero,'$date')");
        $this->db->bind("numero", $numero, null);
        if ($this->db->execute()) {
            echo json_encode("1");
        } else {
            echo json_encode("0");
        }
    }

    function getAllEnveloppes()
    {
        $this->db->query("SELECT * FROM wbcc_enveloppe WHERE etatEnveloppe=1 ORDER BY idEnveloppe DESC");
        $enveloppes = $this->db->resultSet();
        foreach ($enveloppes as $key => $env) {
            $this->db->query("SELECT * FROM wbcc_cheque WHERE idEnveloppeF=$env->idEnveloppe ORDER BY idCheque DESC");
            $env->cheques = $this->db->resultSet();
            $enveloppes[$key] = $env;
        }
        return $enveloppes;
    }

    function findEnveloppeById($idEnveloppe)
    {
        $this->db->query("SELECT * FROM wbcc_enveloppe WHERE idEnveloppe=$idEnveloppe");
        $env = $this->db->single();
        if ($env) {
            $this->db->query("SELECT * FROM wbcc_cheque WHERE idEnveloppeF=$env->idEnveloppe ORDER BY idCheque DESC");
            $env->cheques = $this->db->resultSet();
        }
        return $env;
    }
}
