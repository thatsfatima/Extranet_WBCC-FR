<?php

class Activity extends Model
{

    public function getActivities($type)
    {
        $res = [];
        if ($type == "AFaire") {
            $this->db->query("SELECT * FROM wbcc_activity WHERE isCleared = 'false'  ORDER BY idActivity DESC");
            $res = $this->db->resultSet();
        }
        return $res;
    }

    public function findActivityByOp($codeTache, $idOp)
    {
        $this->db->query("SELECT * FROM  wbcc_opportunity_activity oa, wbcc_activity a WHERE a.idActivity=oa.idActivityF AND codeActivity = $codeTache AND idOpportunityF = $idOp  Limit 1 ");
        $data =  $this->db->single();
        return $data;
    }

    public function getActivitiesByOp($idOp)
    {
        $this->db->query("SELECT * FROM  wbcc_opportunity_activity oa, wbcc_activity a WHERE a.idActivity=oa.idActivityF  AND idOpportunityF = $idOp ORDER BY idActivity DESC");
        $data =  $this->db->resultSet();
        return $data;
    }

    public function findActivityById($idActivity)
    {
        $this->db->query("SELECT * FROM wbcc_activity a WHERE idActivity = $idActivity  Limit 1 ");
        $data =  $this->db->single();
        return $data;
    }

    public function getActivitiesDB($onOP = "", $columnOrder = "priorite", $typeOrder = "DESC")
    {
        $req = "";
        if ($onOP != "") {
            $req .= " WHERE usedByOP= $onOP ";
        }

        $this->db->query("SELECT * FROM wbcc_activity_db  $req   ORDER BY $columnOrder $typeOrder");
        return $this->db->resultSet();
    }

    public function findByCode($code) {
        $this->db->query("SELECT * FROM wbcc_activity_db WHERE codeActivity = $code LIMIT 1");
        return $this->db->single();
    }

}
