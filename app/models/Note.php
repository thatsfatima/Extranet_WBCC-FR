<?php

class Note extends Model
{
    public function  getNoteByOpportunity($id, $type = "")
    {
        if ($type == "") {
            $this->db->query("SELECT * FROM wbcc_note, wbcc_opportunity_note
            WHERE idNote = idNoteF
            AND idOpportunityF = $id AND publie=1 ORDER BY dateNote DESC");
        } else {
            $this->db->query("SELECT * FROM wbcc_note, wbcc_opportunity_note
            WHERE idNote = idNoteF
            AND idOpportunityF = $id ORDER BY dateNote DESC");
        }

        return $this->db->resultSet();
    }


    public function  deleteNoteToOpportunity($idN, $idOp)
    {
        $this->db->query("DELETE FROM wbcc_note 
        WHERE idNote = $idN");
        $this->db->execute();
    }
}
