<input name="idOP" id="idOP" hidden value="<?= $op->idOpportunity ?>">
<input name="nameOP" id="nameOP" value="<?= $op->name ?>" hidden>
<input name="numeroOP" id="numeroOP" value="<?= $op->numeroOpportunity ?>" hidden>
<input name="typeDO" id="typeDO" value="<?= $op->typeDO ?>" hidden>
<input name="signatureOP" id="signatureOP" value="<?= $op->signature ?>" hidden>
<input name="gestionnaireAppImm" id="gestionnaireAppImm" value="<?= $op->nomGestionnaireAppImm ?>" hidden>
<input name="nomDO" id="nomDO" value="<?= $op->nomDO ?>" hidden>
<input type='text' id='idUtilisateur' class='form-control' value='<?= $_SESSION['connectedUser']->idUtilisateur ?>'
    hidden>
<input type='text' id='idAuteur' class='form-control' value='<?= $_SESSION['connectedUser']->idContact ?>' hidden>
<input type='text' id='numeroAuteur' class='form-control' value='<?= $_SESSION['connectedUser']->numeroContact ?>'
    hidden>
<input type='text' id='auteur' class='form-control' value='<?= $_SESSION['connectedUser']->fullName ?>' hidden>
<input type='text' id='idRT' class='form-control' value='<?= isset($rt) && $rt  ? $rt->idRT : "0" ?>' hidden>
<input type='text' id='dateRV' class='form-control' value='<?= isset($dateRVRT) ? $dateRVRT : "" ?>' hidden>
<input type='text' id='idRV' class='form-control' value='<?= $op->rv ? $op->rv->idRV : 0 ?>' hidden>
<input name="idExpertWBCC" id="idExpertWBCC" value="<?= isset($rdv) && $rdv ? $rdv->idExpertF : 0 ?>" hidden>
<input type='idDevis' id='idDevis' class='form-control' value='<?= isset($devis) && $devis ? $devis->idDevis : "0" ?>'
    hidden>
<input name="nbOtherOP" id="nbOtherOP" value="<?= isset($otherOpWSameCie) ? sizeof($otherOpWSameCie) : 0 ?>" hidden>
<input type='text' id='idActivity' class='form-control'
    value='<?= isset($activityTE) && ($activityTE) ? $activityTE->idActivity  : (isset($activity) && ($activity) ? $activity->idActivity : 0) ?>'
    hidden>
<input name="typeSinistre" id="typeSinistre" value="<?= $op->typeSinistre ?>" hidden>
<input name="typeLot" id="typeLot" value="<?= $op->typeSinistre == "Partie commune exclusive" ? "PC" : "PP" ?>" hidden>
<input type='text' id='isCleared' class='form-control'
    value='<?= isset($activity) && ($activity) ? $activity->isCleared : 'False' ?>' hidden>
<input name="nbOtherOPRelance" id="nbOtherOPRelance"
    value="<?= isset($otherOpWSameCieForRelance) && sizeof($otherOpWSameCieForRelance) ?>" hidden>
<input name="idExpertWBCC" id="idExpertWBCC" value="<?= isset($rdv) && ($rdv) ? $rdv->idExpertF : 0 ?>" hidden>

<input name="idAuteurRF" id="idAuteurRF"
    value="<?= isset($rf) && $rf ? $rf->idAuteurRF : $_SESSION["connectedUser"]->idUtilisateur  ?>" hidden>
<input name="nomAuteurRF" id="nomAuteurRF"
    value="<?= isset($rf) && $rf ? $rf->auteurRF : $_SESSION["connectedUser"]->fullName ?>" hidden>
<input name="pieceFuyarde" id="pieceFuyarde" value="<?= isset($rf) && $rf ? $rf->pieceFuyarde : ""  ?>" hidden>
<input name="equipementFuyard" id="equipementFuyard" value="<?= isset($rf) && $rf ? $rf->equipementFuyard : "" ?>"
    hidden>
<input name="origineFuiteEquipement" id="origineFuiteEquipement"
    value="<?= isset($rf) && $rf ? $rf->origineFuiteEquipement : ""  ?>" hidden>
<input name="faireRF" id="faireRF" value="<?= isset($rf) && $rf ? $rf->siFaireRF : 0  ?>" hidden>
<input name="siDegatVoisin" id="siDegatVoisin" value="<?= isset($rf) && $rf ? $rf->siDegatVoisin :  0 ?>" hidden>
<input name="siConfierSinistre" id="siConfierSinistre" value="<?= isset($rf) && $rf ? $rf->siConfierSinistre :  0 ?>"
    hidden>
<input name="siJustificatif" id="siJustificatif" value="<?= isset($rf) && $rf ? $rf->siJustificatif : 0 ?>" hidden>
<input name="siSignatureJustificatif" id="siSignatureJustificatif"
    value="<?= isset($rf) && $rf ? $rf->siSignatureJustificatif : 0  ?>" hidden>
<input type="text" name="documentJustificatif" id="documentJustificatif"
    value="<?= isset($rf) && $rf ? $rf->documentJustificatif : ""  ?>" hidden>
<input name="siAccessible" id="siAccessible" value="<?= isset($rf) && $rf ? $rf->siAccessible : 0 ?>" hidden>