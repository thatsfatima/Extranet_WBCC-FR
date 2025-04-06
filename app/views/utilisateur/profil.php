<?php
//inputHiddenForUrlRoot();
// $hidden = (isset($etape) && $etape != "") ? "" : "hidden";
// $hiddenFinal = (isset($etape) && $etape != "") ? "hidden" : "";
// $str = file_get_contents(URLROOT .'/public/json/codePostal.json');
// $json = json_decode($str, true);

//var_dump($json[0]);die;
?>
<div class="section-title">
    <h2><span><i class="fas fa-fw fa-user-alt" style="color: #c00000"></i></span> MON PROFIL</h2>
</div>

<div class="row">

    <form id="msform" method="post" action="<?= linkTo("Utilisateur", "changePasse") ?>">
        <div class="col-md-12 text-left ">
            <div class="card ">
                <div class="col-md-12 mx-0">
                    <!-- progressbar -->
                    <div class="row register-form mt-0">
                        <fieldset>
                            <legend class="text-center legend font-weight-bold text-uppercase"><i
                                    class="icofont-info-circle"></i>Informations de connexion (1)</legend>
                            <p style="display: <?= isset($message) && $message != "" ? 'block' : 'none' ?>;"
                                class="alert <?= isset($message) && strstr($message, 'succés') ? 'alert-success' : 'alert-danger' ?>">
                                <?= $message ?> </p>

                            <div class="row">
                                <div class="col-md-3 float-left">
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">login <small class="text-danger">*</small></label>
                                        </div>
                                        <div class="col-md-12">
                                            <input type="text" name="login" value="<?= $user->login ?>"
                                                class="form-control" id="societe" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 float-left">
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Ancien mot de passe<small class="text-danger">*</small>
                                            </label>
                                        </div>
                                        <div class="col-md-12">
                                            <input required type="password" name="oldPassword" class="form-control"
                                                id="oldPassword">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 float-left">
                                    <div class="row mb-2">
                                        <div class="col-md-12">
                                            <label for="">Nouveau mot de passe<small
                                                    class="text-danger">*</small></label>
                                        </div>
                                        <div class="col-md-12">
                                            <input required type="password" name="password1" class="form-control"
                                                id="password1">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 float-left">
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Confirmation de mot de passe<small
                                                    class="text-danger">*</small></label>
                                        </div>
                                        <div class="col-md-12">
                                            <input required type="password" name="password2" class="form-control"
                                                id="password2">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <input type="submit" name="valider" class="action-button" value="Enregistrer" />

                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<form id="msform" method="post" action="<?= linkTo("Utilisateur", "update") ?>">
    <div class="row mt-3 mb-1">
        <div class="col-md-4 text-left ">
            <div class="card ">
                <div class="col-md-12 mx-0">
                    <!-- progressbar -->
                    <div class="row register-form mt-0">
                        <fieldset>
                            <legend class="text-center legend font-weight-bold text-uppercase"><i
                                    class="icofont-info-circle"></i> Carte de visite (1)</legend>
                            <input type="hidden" name="URLROOT" id="URLROOT" value="<?= URLROOT ?>">
                            <div class="row">
                                <div class="col-md-12 float-left">
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Civilité </label>
                                        </div>
                                        <div class="col-md-12">
                                            <select required="required" name="civilite" id="civilite"
                                                class="form-control" value="<?= $user->civiliteContact ?>">
                                                <option value="">-- Choisir --</option>
                                                <option value="M"
                                                    <?= ($user->civiliteContact == "M") ? "selected" : "" ?>>
                                                    Monsieur</option>
                                                <option value="Mme"
                                                    <?= ($user->civiliteContact == "Mme" || $user->civiliteContact == "Mlle") ? "selected" : "" ?>>
                                                    Madame</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Prénom <small class="text-danger">*</small></label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->prenomContact ?>" type="text" name="prenom"
                                                class="form-control" id="prenom">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Nom<small class="text-danger">*</small></label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->nomContact ?>" required type="tel" name="nom"
                                                class="form-control" id="nom">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Ligne Directe<small class="text-danger">*</small></label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->telContact ?>" required type="tel" name="tel1"
                                                class="form-control" id="tel1">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Portable</label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->mobilePhone ?>" type="tel" name="tel2"
                                                class="form-control" id="tel2">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Email Collaboratif</label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->emailCollaboratif ?>" type="email"
                                                name="emailCollaboratif" class="form-control" id="emailCollaboratif">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-left   ">
            <div class="card ">
                <div class="col-md-12 mx-0">
                    <!-- progressbar -->
                    <div class="row register-form mt-0">
                        <fieldset>
                            <legend class="text-center legend font-weight-bold text-uppercase"><i
                                    class="icofont-location-pin"></i>Adresse (2)</legend>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Adresse 1</label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->adresseContact ?>" type="text" name="adresse1"
                                                class="form-control" id="adresse1">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Adresse 2</label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->businessLine2 ?>" type="text" name="adresse2"
                                                class="form-control" id="adresse2">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Code Postal</label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->codePostalContact ?>" type="text" maxlength="5"
                                                onchange="changePostalCode()" name="codePostal" class="form-control"
                                                id="codePostal">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Ville</label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->villeContact ?>" type="text" readonly name="ville"
                                                class="form-control" id="ville">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Département</label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->departement ?>" type="text" readonly
                                                name="departement" class="form-control" id="departement">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Région</label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->businessState ?>" type="text" readonly
                                                name="region" class="form-control" id="region">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-left">
            <div class="card ">
                <div class="col-md-12 mx-0">
                    <!-- progressbar -->
                    <div class="row register-form mt-0">
                        <fieldset>
                            <legend class="text-center legend font-weight-bold text-uppercase"><i
                                    class="icofont-law"></i>Autres INFO (3)</legend>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Télèphone Standard</label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->faxPhone ?>" type="tel" name="faxPhone"
                                                class="form-control" id="faxPhone">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Numéro Porte</label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->codePorte ?>" type="text" name="porte"
                                                class="form-control" id="porte">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Numéro Bâtiment</label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->batiment ?>" type="text" name="batiment"
                                                class="form-control" id="batiment">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Numéro Etage</label>
                                        </div>
                                        <div class="col-md-12">
                                            <input value="<?= $user->etage ?>" type="text" name="etage"
                                                class="form-control" id="etage">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Service</label>
                                        </div>
                                        <div class="col-md-12">
                                            <select name="service" id="service" class="form-control">
                                                <option value="">-- Choisir --</option>
                                                <option value="Marketing/Communication"
                                                    <?= ($user->service == "Marketing/Communication") ? "selected" : "" ?>>
                                                    Marketing/Communication</option>
                                                <option value=">Ressources humaines"
                                                    <?= ($user->service == "Ressources humaines") ? "selected" : "" ?>>
                                                    Ressources humaines</option>
                                                <option value="Informatique"
                                                    <?= ($user->service == "Informatique") ? "selected" : "" ?>>
                                                    Informatique</option>
                                                <option value="Finance/Comptabilité"
                                                    <?= ($user->service == "Finance/Comptabilité") ? "selected" : "" ?>>
                                                    Finance/Comptabilité</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <label for="">Titre</label>
                                        </div>
                                        <div class="col-md-12">
                                            <select name="titre" id="titre" class="form-control">
                                                <option value="">-- Choisir --</option>
                                                <option value="Gestionnaire"
                                                    <?= ($user->jobTitle == "Gestionnaire") ? "selected" : "" ?>>
                                                    Gestionnaire</option>
                                                <option value="Chef de projets"
                                                    <?= ($user->jobTitle == "Chef de projets") ? "selected" : "" ?>>Chef
                                                    de projets</option>
                                                <option value="Directeur"
                                                    <?= ($user->jobTitle == "Directeur") ? "selected" : "" ?>>Directeur
                                                </option>
                                                <option value="Analyste"
                                                    <?= ($user->jobTitle == "Analyste") ? "selected" : "" ?>>Analyste
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="submit" name="valider" class="action-button" value="Enregistrer" />
</form>


<script type="text/javascript">
const URLROOT = document.getElementById("URLROOT").value;

function changePostalCode() {
    var code = document.getElementById("codePostal").value;
    if (code.length === 5) {
        readTextFile(`${URLROOT}/public/json/codePostal.json`, function(text) {
            var data = JSON.parse(text);
            var test = false;
            data.forEach(function(val) {
                if (val[2] === Number(code)) {
                    test = true;
                    document.getElementById("ville").value = val[9];
                    document.getElementById("departement").value = val[12];
                    document.getElementById("region").value = val[14];
                    //console.log(val[9],val[12],val[14]);
                }
            });
            if (!test) {
                alert("Ce code postal n'existe Pas");
            }
        });
    } else {
        document.getElementById("codePostal").value = "";
        document.getElementById("ville").value = "";
        document.getElementById("departement").value = "";
        document.getElementById("region").value = "";
        alert("Code postal invalide !");
    }

}


function readTextFile(file, callback) {
    var rawFile = new XMLHttpRequest();
    rawFile.overrideMimeType("application/json");
    rawFile.open("GET", file, true);
    rawFile.onreadystatechange = function() {
        if (rawFile.readyState === 4 && rawFile.status == "200") {
            callback(rawFile.responseText);
        }
    }
    rawFile.send(null);
}
</script>