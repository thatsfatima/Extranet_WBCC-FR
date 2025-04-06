<?php

class Piece extends Model
{
    public function save($libelle)
    {
        $piece = $this->findBy("libellePiece", $libelle);
        if ($piece == null) {
            $this->db->query("INSERT INTO piece(libellePiece) VALUES ('$libelle')");
            if ($this->db->execute()) {
                $piece = $this->findBy("libellePiece", $libelle);
                return $piece->idPiece;
            }
            return 0;
        }
        return $piece->idPiece;
    }

    public function getAll($orderBy = '')
    {
        $this->db->query("SELECT * FROM wbcc_piece  ORDER BY libellePiece ASC");
        return $this->db->resultSet();
    }

    public function getAllWithoutAutre($orderBy = '')
    {
        $this->db->query("SELECT * FROM wbcc_piece  WHERE libellePiece NOT LIKE '%autre%' ORDER BY libellePiece ASC");
        return $this->db->resultSet();
    }


    public function getPiecesByidEquipement($idEquipement)
    {
        $this->db->query("SELECT * FROM wbcc_piece p, wbcc_equipement e, wbcc_piece_equipement pe WHERE p.idPiece = pe.idPieceF AND e.idEquipement = pe.idEquipementF AND pe.idEquipementF = $idEquipement");
        return $this->db->resultSet();
    }

    public function getPiecesByRT($idRT)
    {
        $this->db->query("SELECT  * FROM wbcc_rt_piece WHERE idRTF= $idRT");
        $datas = $this->db->resultSet();
        $supports = [];
        if (sizeof($datas) > 0) {
            foreach ($datas as $key => $piece) {
                $this->db->query("SELECT  * FROM wbcc_rt_piece_support WHERE idRTPieceF= $piece->idRTPiece ");
                $supports = $this->db->resultSet();
                $piece->listSupports = $supports;
                foreach ($supports as $j => $support) {
                    $this->db->query("SELECT * FROM `wbcc_rt_revetement` WHERE `idRtPieceSupportF` = $support->idRTPieceSupport");
                    $dataR = $this->db->resultSet();
                    $piece->listSupports[$j]->listRevetements = $dataR;

                    $this->db->query("SELECT * FROM `wbcc_rt_ouverture` WHERE `idRtPieceSupportF` = $support->idRTPieceSupport");
                    $dataR = $this->db->resultSet();
                    $piece->listSupports[$j]->listOuvertures = $dataR;
                }
            }
        }
        return $datas;
    }
}