<?php
// $hidden = (isset($etape) && $etape != "") ? "" : "hidden";
// $hiddenFinal = (isset($etape) && $etape != "") ? "hidden" : "";
$active = "red";
$typeIntervention = ($commercial->codeDepartement != "" && $commercial->codeDepartement != null) ? "departement" : (($commercial->cpZoneRV != "" && $commercial->cpZoneRV != null) ? "zone" : "tous");
$hidden = ($_SESSION["connectedUser"]->isAdmin == '1' || $_SESSION["connectedUser"]->libelleRole == 'Manager' || $_SESSION["connectedUser"]->libelleRole == 'Administrateur') ? "" : "hidden";
?>
<!-- ======= Avantages Section ======= -->
<div class="section-title mb-0">
    <div class="row">
        <div class="col-12 col-md-12 col-sm-12 col-lg-12">
            <h2 class="mb-0"><a onclick="history.back()"><button><i class="fas fa-fw fa-arrow-left"
                            style="color: #c00000"></i></button></a><span><i class="fas fa-fw fa-folder"
                        style="color: #c00000"></i>
                    <?= "CONFIGURATION AGENDA" ?> </h2>
        </div>
    </div>

</div>
<div class="p-0 col-md-12 col-lg-10 col-sm-12 col-xs-12 offset-sm-0 offset-xs-0 offset-md-0 offset-lg-1">
    <form id="msform" method="post" action="<?= linkTo("Utilisateur", "saveConfig") ?>">
        <input type="hidden" name="URLROOT" id="URLROOT" value="<?= URLROOT ?>">
        <div class="modal-body mt-0">
            <div class="row mt-0">
                <div class="col-md-12 col-sm-12 col-xs-12 p-0 col-12  text-left ">
                    <div class="card ">
                        <div class="col-md-12 col-sm-12 col-xs-12 p-0 col-12 p-0 m-0 mx-0">
                            <div class="row register-form mt-3 col-md-12 col-12" id="divConfig">
                                <fieldset>
                                    <legend class="text-center legend font-weight-bold text-uppercase"><i
                                            class="icofont-info-circle"></i>Configuration
                                        <?= ($commercial->role == 18 ? " du " : " de l' ") . $commercial->libelleRole ?>
                                        <small>
                                            <?= $commercial->prenomContact . " " . $commercial->nomContact ?></small>
                                    </legend>
                                    <input type="hidden" name="idUser" id="idUserConfig"
                                        value="<?= $commercial->idUtilisateur ?>">
                                    <div class="row">
                                        <div class="row">
                                            <div class="col-12 mb-1 font-weight-bold text-danger">
                                                <label for="">1. Horaire de travail </label>
                                            </div>
                                            <?php foreach ($tabHoraires as $key => $horaire) {
                                                $index = strstr($commercial->jourTravail, $horaire['jour']) != false ?  array_search($horaire['jour'], $tabJoursCoche) : -1;
                                            ?>

                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <div class="row">
                                                    <div class="col-sm-2 col-xs-4 col-md-4 mb-1">
                                                        <input
                                                            <?= strstr($commercial->jourTravail, $horaire['jour']) != false  ? 'checked' : '' ?>
                                                            type="checkbox" name="tabJourCoche[]"
                                                            value="<?= $horaire['jour']  ?>">
                                                        <?= $horaire['jour'] ?>
                                                    </div>
                                                    <div class="col-sm-5 col-xs-4 col-md-4 mb-1">
                                                        <input type="time" class="form-control" name="tabHD[]"
                                                            value="<?= $index == -1 ? $horaire['heureDebut'] : explode('-', $tabHeureCoche[$index])[0] ?>">
                                                    </div>
                                                    <div class="col-sm-5 col-xs-4 col-md-4 mb-1">
                                                        <input type="time" class="form-control" name="tabHF[]"
                                                            value="<?= $index == -1 ? $horaire['heureFin'] : explode('-', $tabHeureCoche[$index])[1] ?>">
                                                    </div>
                                                </div>

                                            </div>

                                            <?php
                                            } ?>
                                            <div class="col-md-12" <?= $hidden ?>>
                                                <div class="col-md-12 mb-1 font-weight-bold text-danger">
                                                    <label for="">2. Marge <small>(en min)</small></label>
                                                </div>
                                                <div class="col-md-12 mb-1">
                                                    <input type="text" class="form-control" name="marge"
                                                        value="<?= $commercial->margeTravail ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-12" <?= $hidden ?>>
                                                <div class="font-weight-bold mb-0">
                                                    <div class="col-md-12 mb-1 font-weight-bold text-danger">
                                                        <label for="">3. Type d'intervention</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="row p-3 text-center">
                                                        <div class="col-md-4">
                                                            <input <?= $typeIntervention == "zone" ? "checked" : "" ?>
                                                                onclick="onClickTypeIntervention('zone')" type="radio"
                                                                name="typeIntervention" value="zone"><label
                                                                class="ml-2 font-weight-bold" for="">Zone</label>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input
                                                                <?= $typeIntervention == "departement" ? "checked" : "" ?>
                                                                onclick="onClickTypeIntervention('departement')"
                                                                type="radio" name="typeIntervention"
                                                                value="departement"><label class="ml-2 font-weight-bold"
                                                                for="">Département</label>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input <?= $typeIntervention == "tous" ? "checked" : "" ?>
                                                                onclick="onClickTypeIntervention('tous')" type="radio"
                                                                name="typeIntervention" value="tous"><label
                                                                class="ml-2 font-weight-bold" for="">Tous</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div <?= $hidden  ?> class="col-md-12" id="divDep"
                                                <?= $typeIntervention == "departement" ? "" : "hidden" ?>>
                                                <div class="col-md-12 mb-1 font-weight-bold text-danger">
                                                    <label for=""><small>les départements doivent être
                                                            séparés par <b>';'</b> Ex : 93;92;94</small></label>
                                                </div>
                                                <div class="col-md-12 mb-1">
                                                    <input type="text" class="form-control" name="departement"
                                                        value="<?= $commercial->codeDepartement ?>">
                                                </div>
                                            </div>
                                            <div <?= $hidden ?> class="col-md-12" id="divZone"
                                                <?= $typeIntervention == "zone" ? "" : "hidden" ?>>
                                                <div class="col-md-12">
                                                    <div class="row ">
                                                        <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 mb-1">
                                                            <span for=""><em>1.a. Adresse</em> </span>
                                                            <input type="text" id="adresse" name="adresse"
                                                                class="form-control  border border-secondary"
                                                                value="<?= $commercial->adresseZoneRV ?>">
                                                        </div>
                                                    </div>
                                                    <div class="row mt-1">
                                                        <div
                                                            class="col-md-6 col-lg-6 col-sm-6 col-sm-12 col-xs-12 mb-1">
                                                            <span for=""><em>1.b. Code Postal</em> </span>
                                                            <input readonly="text" name="codePostal" id="codePostal"
                                                                class="form-control  border border-secondary"
                                                                value="<?= $commercial->cpZoneRV ?>">
                                                        </div>
                                                        <div class="col-md-6 col-lg-6 col-sm-6 col-xs-12 mb-1">
                                                            <span for=""><em>1.c. Ville</em></span>
                                                            <input readonly type="text" name="ville"
                                                                value="<?= $commercial->villeZoneRV ?>" id="ville"
                                                                class="form-control  border border-secondary">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12"
                                                <?= strtolower($_SESSION["connectedUser"]->typeCompany) == 'artisan' ? "hidden" : "" ?>>
                                                <div class="font-weight-bold mb-0">
                                                    <div class="col-md-12 mb-1 font-weight-bold text-danger">
                                                        <label for=""><?= $hidden == "" ? '4.' : "2." ?> Moyen de
                                                            Transport</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="row p-3 text-center">
                                                        <div class="col-md-6 col-xs-6 col-sm-6">
                                                            <input
                                                                <?= $commercial->moyenTransport == "voiture" ? "checked" : "" ?>
                                                                type="radio" name="moyenTransport"
                                                                value="voiture"><label class="ml-2 font-weight-bold"
                                                                for="">En voiture</label>
                                                        </div>
                                                        <div class="col-md-6 col-xs-6 col-sm-6">
                                                            <input
                                                                <?= $commercial->moyenTransport == "pied" ? "checked" : "" ?>
                                                                type="radio" name="moyenTransport" value="pied"><label
                                                                class="ml-2 font-weight-bold" for="">A pied</label>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12"
                                                <?= strtolower($_SESSION["connectedUser"]->typeCompany) == 'artisan' ? "hidden" : "" ?>>
                                                <div class="col-md-12 mb-1 font-weight-bold text-danger">
                                                    <label for=""><?= $hidden == "" ? '5.' : "3." ?> Commentaire
                                                    </label>
                                                </div>
                                                <div class="col-md-12 mb-1">
                                                    <textarea name="commentaire" class="form-control" id="" cols="30"
                                                        rows="2"><?= $commercial->commentaireConfig ?></textarea>
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
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" type="button" data-dismiss="modal">Annuler</button>
            <button class="btn btn-success" href="submit">Enregistrer</button>
        </div>
    </form>
</div>



<script type="text/javascript">
const URLROOT = document.getElementById("URLROOT").value;

function initialize() {
    var input = document.getElementById('adresse');
    autocomplete = new google.maps.places.Autocomplete(input, {
        componentRestrictions: {
            country: ["fr"]
        },
        fields: ["address_components", "geometry"],
        types: ["address"],
    });
    autocomplete.addListener("place_changed", fillInAddress)
}

function fillInAddress() {
    // Get the place details from the autocomplete object.
    const place = autocomplete.getPlace();
    let address1 = "";
    let postcode = "";

    // Get each component of the address from the place details,
    // and then fill-in the corresponding field on the form.
    // place.address_components are google.maps.GeocoderAddressComponent objects
    // which are documented at http://goo.gle/3l5i5Mr
    for (const component of place.address_components) {
        // @ts-ignore remove once typings fixed
        const componentType = component.types[0];

        switch (componentType) {
            case "street_number": {
                address1 = `${component.long_name} ${address1}`;
                break;
            }

            case "route": {
                address1 += component.short_name;
                break;
            }

            case "postal_code": {
                postcode = `${component.long_name}${postcode}`;
                break;
            }

            case "postal_code_suffix": {
                postcode = `${postcode}-${component.long_name}`;
                break;
            }
            case "locality":
                document.querySelector("#ville").value = component.long_name;
                break;
        }
    }

    adresse.value = address1;
    codePostal.value = postcode;
    document.getElementById('adresse').removeAttribute('disabled');
    // ville.value = postcode;
    // After filling the form with address components from the Autocomplete
    // prediction, set cursor focus on the second address line to encourage
    // entry of subpremise information such as apartment, unit, or floor number.
    // address2Field.focus();
}

google.maps.event.addDomListener(window, 'load', initialize);

function onClickAdd() {
    document.getElementById("email").removeAttribute("readonly");
    document.getElementById("idContact").value = "0";
    document.getElementById("civilite").value = "";
    document.getElementById("prenom").value = "";
    document.getElementById("nom").value = "";
    document.getElementById("tel1").value = "";
    document.getElementById("email").value = "";
    document.getElementById("role").value = "";
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

function onClickTypeIntervention(params) {
    $('#divZone').attr("hidden", "hidden");
    $('#divDep').attr("hidden", "hidden");
    if (params == 'zone') {
        $('#divZone').removeAttr("hidden");
    } else {
        if (params == 'departement') {
            $('#divDep').removeAttr("hidden");
        }
    }
}
</script>