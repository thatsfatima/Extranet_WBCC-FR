<?php
$today = date('d/m/Y');
?>
<div class="container register mt-0">
    <div class="row">
        <div class="col-md-2 register-left">
            <img src="<?= URLROOT . '/images/logo_WBCC.png' ?>" alt="">
            <h1>Extranet WBCC</h1>
        </div>
        <div class="col-md-10 register-right">
            <!-- MultiStep Form -->
            <div class="container-fluid ">
                <div class="row justify-content-center">
                    <div class="card-body">

                        <div class="register-form mt-0 mb-0 ml-0 mr-0">
                            <fieldset class="mx-0">
                                <div class="alert alert-danger text-center" <?= $message == '' ? 'hidden' : '' ?>>
                                    <?php
                                    if ($message == "eem") {
                                        echo "L'email renseigné est déjà utilisé !";
                                    }
                                    if ($message == "emp") {
                                        echo "Les mots de passe ne sont pas conformes !";
                                    }
                                    if ($message == "ecc") {
                                        echo "Erreur lors de la création du compte, veuillez réessayer !";
                                    }
                                    if ($message == "ecu") {
                                        echo "Erreur lors de la création de l'utilisateur, veuillez réessayer !";
                                    }
                                    if ($message == "edn") {
                                        echo "Désolé, vous devez avoir au moins 18 ans pour créer un compte !";
                                    }
                                    ?>
                                </div>
                                <h6 class="text-center"><strong>INSCRIPTION</strong></h6>
                                <hr class="mb-0">
                                <div class="row mt-0">
                                    <div class="col-12">
                                        <nav class="nav-menu ">
                                            <ul>
                                                <li onClick="onClickTypeCompte('client')" class="active"><a href="#">Client</a>
                                                </li>
                                                <li onClick="onClickTypeCompte('autre')"><a href="#">Autre</a>
                                                </li>
                                            </ul>
                                        </nav><!-- .nav-menu -->
                                    </div>
                                </div>
                                <form name="client" method="post" action="<?= linkTo('Utilisateur', 'addCompte') ?>">
                                    <input hidden type="text" name="typeCompte" id="typeCompte" value="client">
                                    <input hidden type="text" name="URLROOT" id="URLROOT" value="<?= URLROOT ?>">
                                    <div class="text-center">
                                        <small id="textDescriptif">Ce compte va vous pourmettre de faire vos
                                            déclarations
                                            de
                                            sinistres en ligne chez WBCC !</small>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="row" id="categorie" hidden>
                                                <div class="col-md-4">
                                                    <label for="">Catégorie <small class="text-danger">*</small></label>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="">
                                                        <input onClick="onClickTypeClient('Particulier')" type="radio" name="categorie" value="Particulier" checked>
                                                        <span class="mr-4"> Particulier</span>
                                                    </label>
                                                    <label class="">
                                                        <input onClick="onClickTypeClient('Autre Professionnelle')" type="radio" name="categorie" value="Autre Professionnelle">
                                                        <span class="mr-4">Autre Professionnelle </span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div hidden id="professionnelle">

                                                <div class="row mb-1">
                                                    <div class="col-md-4">
                                                        <label for="">Nom de la société <small class="text-danger">*</small></label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="nomSociete" id="nomSociete" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="row mt-1">
                                                    <div class="col-md-4">
                                                        <label for="">Numero Siret <small class="text-danger">*</small></label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="siret" id="siret" class="form-control">
                                                    </div>
                                                </div>

                                                <div class="row mt-1">
                                                    <div class="col-md-4">
                                                        <label for="">Date de Création <small class="text-danger">*</small></label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="date" data-date-format="DD/MMMM/YYYY" max="<?= $today ?>" name="dateCreation" class="form-control" id="dateCreation">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-1">
                                                <div class="col-md-4">
                                                    <label for="">Civilité <small class="text-danger">*</small></label>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="">
                                                        <input type="radio" name="sexe" value="M" id="sexeHomme" checked>
                                                        <span class="mr-4"> M.</span>
                                                    </label>
                                                    <label class="">
                                                        <input type="radio" name="sexe" value="Mme" id="sexeFemme">
                                                        <span class="mr-4">Mme </span>
                                                    </label>
                                                    <label class="">
                                                        <input type="radio" name="sexe" value="Mlle" id="sexeMlle">
                                                        <span>Mlle</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-md-4">
                                                    <label for="">Prénom <small class="text-danger">*</small></label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="text" required name="prenom" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="">Nom <small class="text-danger">*</small></label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="text" required name="nom" class="form-control">
                                                </div>
                                            </div>

                                            <div class="row mt-1">
                                                <div class="col-md-4">
                                                    <label for="">Date de Naissance <small class="text-danger">*</small></label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="date" required name="dateNaissance" class="form-control" id="dateNaissance" onchange="onChangeDateNaissance()">
                                                </div>
                                            </div>
                                            <div class="row offset-2" id="erreurAge" hidden>
                                                <span> <small class="text-center text-danger font-weight-bold">
                                                        Désolé,
                                                        vous devez avoir au moins 18 ans pour créer un compte
                                                        !</small></span>
                                            </div>


                                            <div class="row mt-1">
                                                <div class="col-md-4">
                                                    <label for="">Téléphone <small class="text-danger">*</small></label>
                                                </div>

                                                <div class="col-md-8">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text" id="indicatif">(+33)</div>
                                                        </div>
                                                        <input required type="text" class="form-control number-input" id="" name="tel" maxlength="9" minlength="9">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mt-1">
                                                <div class="col-md-4">
                                                    <label for="">Whatsapp <small class="text-danger">(indicatif
                                                            obligatoire)*</small> </label>
                                                </div>

                                                <div class="col-md-8">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text text-success" id=""> <i class="icofont-whatsapp"></i></div>
                                                        </div>
                                                        <input required type="text" class="form-control number-input" id="" name="telWhatsapp" minlength="12" maxlength="12" placeholder="Ex : +33980084484">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mt-1">
                                                <div class="col-md-4">
                                                    <label for="">Pseudo Skype</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text text-info" id=""> <i class="icofont-skype"></i></div>
                                                        </div>
                                                        <input type="text" class="form-control" id="" name="pseudoSkype">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mt-1">
                                                <div class="col-md-4">
                                                    <label>Email<small class="text-danger">*</small></label>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text" id=""> <i class="icofont-email"></i></div>
                                                        </div>
                                                        <input type="email" required name="email" id="email" class="form-control" onblur="onChangeEmail()">

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row offset-2" id="erreurEmail" hidden>
                                                <span> <small class="text-center text-danger font-weight-bold"> Désolé,
                                                        l'email renseigné est déjà utilisé !</small></span>
                                            </div>

                                            <div class="row mt-1" id="role">
                                                <div class="col-md-4">
                                                    <label>Profil</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <select name="idRoleF" class="form-control" id="">
                                                        <?php foreach ($roles as $role) {
                                                            if (str_contains(strtolower($role->libelleRole), 'candidat')) {
                                                        ?>
                                                                <option value="<?= $role->idRole ?>" <?= strtolower($role->libelleRole) == 'candidat' ? 'selected' : '' ?>>
                                                                    <?= $role->libelleRole ?></option>
                                                        <?php  }
                                                        } ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mt-1">
                                                <div class="col-md-4">
                                                    <label>Mot de passe<small class="text-danger">*</small></label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="password" id="mdp" required name="mdp" class="form-control" onblur="onChangePassword()">
                                                </div>
                                            </div>
                                            <div class="row mt-1">
                                                <div class="col-md-4">
                                                    <label>Confirmation<small class="text-danger">*</small></label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input required type="password" id="cmdp" name="cmdp" class="form-control" onblur="onChangePassword()">
                                                </div>
                                            </div>

                                            <div class="row offset-2" id="erreurMdp" hidden>
                                                <span> <small class="text-center text-danger font-weight-bold"> Les mots
                                                        de passe doivent être identiques !</small></span>
                                            </div>



                                            <div class="row mt-1" id="description" hidden>
                                                <div class="col-md-4">
                                                    <label for="">Pourquoi client autre? <small class="text-danger">*</small></label>
                                                </div>
                                                <div class="col-md-8">
                                                    <Textarea required name="description" class="form-control">

                                                    </Textarea>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <input type="text" hidden id="age" name="age">
                                    <div class="row float-right mt-1">
                                        <div class="col-md-12 form-group">
                                            <button type="submit" class="btn btn-danger btn-sm font-weight-bold ">Envoyer</button>
                                        </div>
                                    </div>
                                </form>

                            </fieldset>
                            <div class="row float-right">
                                <div class="col-md-12 form-group">
                                    <a href="<?= linkTo('Home', 'connexion', '') ?>" class="btn btn-primary">Se
                                        connecter</a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <a href="https://wbcc.fr/mentions-legales" style="color:blue;font-weight:bold;text-decoration:underline" class="h6">Mentions légales</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let indicatif = document.getElementById("indicatif");
    let pays = document.getElementById("pays");
    let age = document.getElementById("age");
    let dateNaissance = document.getElementById("dateNaissance");
    let erreurAge = document.getElementById("erreurAge");
    let email = document.getElementById("email");
    let erreurEmail = document.getElementById("erreurEmail");
    let erreurMdp = document.getElementById("erreurMdp");
    let mdp = document.getElementById("mdp");
    let cmdp = document.getElementById("cmdp");

    let client = document.getElementById("client");
    let autreProfessionnelle = document.getElementById("professionnelle");
    let nomSociete = document.getElementById("nomSociete");
    let siret = document.getElementById("siret");
    let textDescriptif = document.getElementById("textDescriptif");
    let categorie = document.getElementById("categorie");
    let description = document.getElementById("description");
    let role = document.getElementById("role");
    let typeCompte = document.getElementById("typeCompte");

    function chargerIndicatif() {
        if (pays.value != "") {
            indicatif.innerText = "+" + pays.value.split('-')[1];
        }
    }

    function onChangeDateNaissance() {
        if (dateNaissance.value != "") {
            var date1 = new Date(dateNaissance.value);
            var today = new Date();
            var Diff_temps = today.getTime() - date1.getTime();
            var Diff_jours = Diff_temps / (1000 * 3600 * 24);
            var nbAn = Math.trunc(Diff_jours / 365);
            age.value = nbAn;
            if (nbAn < 18) {
                erreurAge.removeAttribute("hidden");
            } else {
                erreurAge.setAttribute("hidden", "");
            }
        }
    }

    function onChangeEmail() {
        const URLROOT = document.getElementById("URLROOT").value;
        if (email.value.trim() != "") {
            $.ajax({
                type: "GET",
                url: `${URLROOT}/public/json/utilisateur.php?action=findByEmail&email=${email.value}`,
                dataType: "JSON",
                success: function(data) {
                    if (data != "0") {
                        erreurEmail.removeAttribute("hidden");

                    } else {
                        erreurEmail.setAttribute("hidden", "");
                    }
                },
                error: function() {
                    console.log("erreur");
                }
            });
        }
    }

    function onChangePassword() {
        if (mdp.value != "" && cmdp.value != "" && mdp.value != cmdp.value) {
            erreurMdp.removeAttribute("hidden");
        } else {
            erreurMdp.setAttribute("hidden", "");
        }
    }

    function onClickTypeCompte(type) {
        typeCompte.setAttribute("value", type);
        if (type === "client") {
            //categorie.removeAttribute("hidden");
            description.setAttribute("hidden", "");
            role.setAttribute("hidden", "");

            textDescriptif.innerText = "";
            textDescriptif.append(
                "Ce compte va vous pourmettre de faire vos déclarations de sinistres en ligne chez WBCC !");
        } else {
            description.removeAttribute("hidden");
            role.removeAttribute("hidden");

            categorie.setAttribute("hidden", "");
            textDescriptif.innerText = "";
            textDescriptif.append("Ce compte va vous pourmettre de faire des recherches de fuite !");
        }
    }

    function onClickTypeClient(type) {
        if (type === "Particulier") {
            autreProfessionnelle.setAttribute("hidden", "");
            siret.removeAttribute("required");
            nomSociete.removeAttribute("required");
        } else {
            autreProfessionnelle.removeAttribute("hidden");
            nomSociete.setAttribute("required", "");
            siret.setAttribute("required", "");
        }
    }
</script>