<?php
header('Access-Control-Allow-Origin: *');

require_once "../../app/config/config.php";
require_once "../../app/libraries/Database.php";
require_once "../../app/libraries/SMTP.php";
require_once "../../app/libraries/PHPMailer.php";
require_once "../../app/libraries/Role.php";
require_once "../../app/libraries/Utils.php";

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $db = new Database();

    //Liste des roles pour inscription
    if ($action == "getRoles") {
        $db->query("SELECT * FROM wbcc_roles WHERE etatRole = 1 AND visibleInscription=1");
        $data = $db->resultSet();
        echo json_encode($data);
    }

    if ($action == "getUsersBySite") {
        $idSite = $_GET['idSite'];
        $etat = (isset($_GET['etat'])) ? $_GET['etat'] : '';
        $isPointageInterne = (isset($_GET['isPointageInterne'])) ? $_GET['isPointageInterne'] : '';
        $req = "";
        if ($etat != "") {
            $req =  " AND etatUser = $etat ";
        }
        if ($etat != "") {
            $req .=  " AND isPointageInterne = $isPointageInterne ";
        }
        $db->query("SELECT * FROM wbcc_contact a, wbcc_utilisateur b
                WHERE a.idContact = b.idContactF AND b.idSiteF = $idSite AND isInterne=1 $req GROUP BY idUtilisateur ORDER BY a.prenomContact, a.nomContact  ASC");
        $data = $db->resultSet();
        echo json_encode($data);
    }


    if ($action == "getGestionnaires") {
        $idUser = $_GET['idUser'];
        $user = findItemByColumn("wbcc_utilisateur", "idUtilisateur", $idUser);
        $roleUser = $user->role;
        $db = new Database();
        if ($roleUser == "1" || $roleUser == "2" || $roleUser == "8") {
            $sql = "SELECT * FROM wbcc_contact a, wbcc_utilisateur b
            WHERE a.idContact = b.idContactF AND (b.isGestionnaire = 1  OR  b.role NOT IN (6,10,11,13,14,15,16,17,20,21,22,23,24) ) AND etatUser=1
            ORDER BY a.prenomContact, a.nomContact  ASC";
        } else {
            if ($roleUser == '25') {
                $sql = "SELECT * FROM wbcc_contact a, wbcc_utilisateur b
                WHERE a.idContact = b.idContactF AND b.idSiteF = $user->idSiteF  AND (b.isGestionnaire = 1 OR  b.role = 3 OR  b.role = 25) AND etatUser=1 ORDER BY a.prenomContact, a.nomContact  ASC";
            } else {
                $sql = "SELECT * FROM wbcc_contact a
                JOIN wbcc_utilisateur b ON (a.idContact = b.idContactF)
                WHERE idUtilisateur = $idUser";
            }
        }
        $db->query($sql);
        $data = $db->resultSet();

        echo json_encode($data);
    }

    if ($action == "findByEmail") {
        $tab = [];
        $email = $_GET['email'];
        $db->query("SELECT * FROM wbcc_utilisateur u, wbcc_roles r, wbcc_contact c WHERE LOWER(email)=lower('$email') 
        AND u.role = r.idRole
        AND u.idContactF= c.idContact LIMIT 1");
        $contact = $db->single();

        if (empty($contact)) {
            echo json_encode("0");
        } else {
            echo json_encode($contact->idContact);
        }
    }

    if ($action == "findById") {
        $id = $_GET['id'];
        $db->query("SELECT * FROM wbcc_utilisateur u, wbcc_roles r, wbcc_contact c WHERE idUtilisateur=$id
        AND u.role = r.idRole
        AND u.idContactF= c.idContact LIMIT 1");
        $contact = $db->single();

        if (empty($contact)) {
            echo json_encode("0");
        } else {
            echo json_encode($contact);
        }
    }

    if ($action == "listeUser") {
        $db->query("SELECT * FROM wbcc_contact, wbcc_utilisateur WHERE idContactF = idContact");
        $data = $db->resultSet();
        echo json_encode($data);
    }

    if ($action == 'connexion') {
        if (isset($_GET['action'], $_GET['login'], $_GET['password'])) {
            $login = $_GET['login'];
            $pwd = $_GET['password'];

            $pass = sha1($pwd);
            $db->query("
                SELECT * FROM wbcc_utilisateur u, wbcc_roles r, wbcc_contact e
                WHERE u.login=:login 
                AND u.mdp=:pass
                AND u.role = r.idRole
                AND u.idContactF=e.idContact
                LIMIT 1
            ");
            $db->bind("login", $login, null);
            $db->bind("pass", $pass, null);
            $data = $db->single();
            if ($data) {
                if ($data->etatUser == 1) {
                    echo json_encode($data);
                } else {
                    if ($data->etatUser == 2) {
                        echo json_encode("2");
                    } else {
                        echo json_encode("10");
                    }
                }
            } else {
                echo json_encode("0");
            }
        }
    }

    if ($action == 'update') {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        $db = new Database();
        if (isset($idEmp)) {
            $db->query("
                        UPDATE wbcc_contact set nomContact='$nom', prenomContact='$prenom', adresseContact='$adresse', telContact='$telephone', civiliteContact='$genre', skype = '$skype', whatsapp = '$whatsapp'
                        WHERE idContact=$idEmp
                    ");
            if ($db->execute()) {
                $db->query("
                            SELECT * FROM wbcc_utilisateur u, wbcc_roles r, wbcc_contact e
                            WHERE u.idUtilisateur = $idUser
                            AND u.role = r.idRole
                            AND u.idContactF=e.idContact
                            LIMIT 1
                        ");
                $data = $db->single();
                echo json_encode($data);
            } else {
                echo json_encode("employe not update");
            }
        } else {
            echo json_encode("employe not set");
        }
    }

    if ($action == "forgotPassword") {
        if (isset($_GET['email'])) {
            $email = $_GET['email'];
            $user = findUserByEmail2($email);
            if ($user) {
                $token = md5(uniqid(rand(), true));
                $res = addTokenPwd($email, $token);
                if ($res) {

                    $param = $email . "~" . $token;
                    $to = $email;
                    $subject = "Recupération de mot de passe";

                    $txt = "Bonjour  $user->civiliteContact $user->prenomContact $user->nomContact, <br> <br> vous avez oublié votre mot de passe. <br> 
                    Veuillez cliquer sur ce lien pour le réinitialiser : <br>
                    <b><a href='" . URLROOT . "/Home/reset/$param'>Réinitialiser mot de passe ...</a></b> <br><br>
                    Veuillez ne pas répondre à ce message envoyé automatiquement pour votre information.
                    <br><br>
                    <b>WBCC ASSISTANCE</b><br>
                    Extranet
                <br><br>Pour toutes question ou demande techniques, merci de contacter :
               <br><b>Tel : 09 80 08 44 84 
               <br>Email : supportdev@wbcc.fr</b>
                     ";
                    $r = new Role();
                    if ($r::mailOnServer($to, $subject, $txt)) {
                        echo json_encode("1");
                    } else {
                        echo json_encode("Erreur lors de l'envoi du mail, veuillez réessayer !");
                    }
                } else {
                    echo json_encode("Mot de passe non réinitialisé, veuillez réessayer !");
                }
            } else {
                echo json_encode("L'adresse email renseignée n'existe pas !");
            }
        } else {
            echo json_encode("Vous devez renseigner une adresse email !");
        }
    }

    if ($action == "addUser") {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        if (isset($email)) {
            $user = findUserByEmail2($email);
            if ($user) {
                echo json_encode("L'adresse email renseignée existe déjà, Veuillez renseigner une autre!");
            } else {
                $contact = findContactByEmail($email);
                $idContact = ($contact) ? $contact->idContact : "0";
                $numero = "CON_" . date('ddmmYYYhis');
                $r = new Role();
                if (isset($idRole) && $idRole != "0") {
                    $contact = saveContact($idContact, $numero, $sexe, $prenom, $nom, $tel, $email,  $dateNaissance, $pseudoSkype, $telWhatsapp, $idStatut);
                    if ($contact) {
                        $token = md5(uniqid(rand(), true));
                        $param = $email . "~" . $token;
                        $pass = "";
                        $possible = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
                        $i = 0;
                        while ($i < 8) {
                            $char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);
                            if (!strstr($pass, $char)) {
                                $pass .= $char;
                                $i++;
                            }
                        }
                        $res = addUser($email, $pass, $email, $idRole, $contact->idContact, $token);
                        if ($res) {
                            $subject = "Mail de confirmation de création de compte";
                            $body = "Bonjour $sexe $prenom $nom,
                            <br><br>Votre compte d'utilisateur vient d'être créé sur <b>WBCC ASSISTANCE EXTRANET</b><br><br>
                          
                            Pour terminer votre inscription, veuillez confirmer votre adresse e-mail en suivant ce lien : <br>
                            <b><a href='" . URLROOT . "/Home/confirm/$param'>TERMINER VOTRE INSCRIPTION</a></b> <br><br>
                            Merci de confirmer votre adresse e-mail, sans quoi votre compte est inactif. <br><br>
            
                            Information de connexion :<br> 
                            <u><b>Login</u> :</b> $email <br>
                            <u><b>Mot de passe</u> :</b> $pass <br><br>
                            
                            <b>
                            Ces identifiants peuvent être utilisés pour se connecter sur :<br>
                           <a href='https://www.extranet.wbcc.fr'>Extranet</a><br>
                           <a href='https://apps.apple.com/us/app/wbcc-assistance/id1631513137'>App Store(ios)</a><br>
                           <a href='https://play.google.com/store/apps/details?id=com.wbccassistmobile'>Play Store(android)</a><br>
                            </b>
            
                            <br><br>
                            Email envoyé depuis l'extranet de WBCC par <br>
                            <b> " . $auteur . "</b><br>
                            <b>EXTRANET WBCC ASSISTANCE</b>"
                                .
                                "<br><br>Pour toutes question ou demande techniques, merci de contacter :
                            <br><b>Tel : 09 80 08 44 84 
                            <br>Email : supportdev@wbcc.fr</b>";
                            if ($r::mailOnServer($email, $subject, $body)) {
                                echo json_encode("1");
                            } else {
                                echo json_encode("Erreur lors de l'envoi du mail, veuillez réessayer !");
                            }
                        } else {
                            echo json_encode("Erreur lors de la création, merci de réessayer !");
                        }
                    } else {
                        echo json_encode("Erreur lors de la création, merci de réessayer !");
                    }
                } else {
                    if ($password['value'] !== $confirmPassword['value']) {
                        echo json_encode("Les deux mots de passe ne sont pas conformes");
                    } else {
                        $idStatut = "";
                        $idRoleF = 0;
                        if ($typeCompte == "client") {
                            if ($categorie == "Particulier") {
                                $idStatut = "DONNEUR DORDRE;PARTICULIER";
                                $idRoleF = 16;
                            } else {
                                $idStatut = "SALARIE";
                                $idRoleF = 15;
                            }
                        } else {
                            $idRoleF = $roleSelected;
                        }

                        $contact = saveContact($idContact, $numero, $sexe, $prenom['value'], $nom['value'], $tel['value'], $email,  $dateNaissance['value'], $pseudoSkype['value'], $telWhatsapp['value'], $idStatut);

                        if ($contact) {
                            $token = md5(uniqid(rand(), true));
                            $res = addUser($email, $password['value'], $email, $idRoleF, $contact->idContact, $token);
                            if ($res) {
                                //Envoi mail
                                $to = $email;
                                $subject = "Mail de confirmation de création de compte";
                                $param = $email . "~" . $token;
                                $txt = "<p style='text-align:justify'>Bienvenue " . $sexe . " " . $prenom['value'] . " " . $nom['value'] . " à www.extranet.wbcc.fr, <br> <br> Félicitations, nous vous confirmons la création de votre compte. <br> <br>
                                    Pour terminer votre inscription, veuillez confirmer votre adresse e-mail en suivant ce lien : <br>
                                     <b><a href='" . URLROOT . "/Home/confirm/$param'>TERMINER VOTRE INSCRIPTION</a></b> <br><br>
                                     Merci de confirmer votre adresse e-mail, sans quoi votre inscription sera incomplète. <br><br>
                                     Veuillez ne pas répondre à ce message envoyé automatiquement pour votre information.  <br><br>
                                     <b>WBCC ASSISTANCE</b><br>
                                     </p>
                                     ";
                                $r = new Role();
                                if ($r::mailOnServer($to, $subject, $txt)) {
                                    echo json_encode("1");
                                } else {
                                    echo json_encode("Erreur lors de l'envoi du mail, veuillez réessayer !");
                                }
                            } else {
                                echo json_encode("Erreur lors de la création, merci de réessayer !");
                            }
                        } else {
                            echo json_encode("Erreur lors de la création, merci de réessayer !");
                        }
                    }
                }
            }
        } else {
            echo json_encode("Vous devez renseigner une adresse email !");
        }
    }

    if ($action == 'deleteUser') {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        $db = new Database();
        if (isset($idEmp)) {
            $db->query("DELETE FROM wbcc_utilisateur WHERE idUtilisateur = $idUser");
            if ($db->execute()) {
                $db->query("
                        UPDATE wbcc_contact set etatContact = 0, motifSuppressionCompte = :raison
                        WHERE idContact=:idContact
                    ");
                $db->bind("raison", $raisonDelete, null);
                $db->bind("idContact", $idEmp, null);
                if ($db->execute()) {
                    $to = $email;
                    $subject = "Mail de confirmation de suppression de compte";
                    $txt = "<p style='text-align:justify'>Bonjour " .  $prenom . " " . $nom . ", 
                        <br> <br>Nous vous confirmons la suppression de votre compte '$email' sur l'extranet WBCC. <br> <br>
                         Veuillez ne pas répondre à ce message envoyé automatiquement pour votre information.  <br><br>
                         <b>WBCC ASSISTANCE</b><br>
                         <br><br>Pour toutes question ou demande techniques, merci de contacter :
                    <br><b>Tel : 09 80 08 44 84 
                    <br>Email : supportdev@wbcc.fr</b>
                         </p>
                         ";
                    $r = new Role();
                    if ($r::mailOnServer($to, $subject, $txt)) {
                        echo json_encode("$idEmp");
                    } else {
                        echo json_encode("0");
                    }
                } else {
                    echo json_encode("0");
                }
            } else {
                echo json_encode("0");
            }
        } else {
            echo json_encode("0");
        }
    }

    if ($action == "getUsersByRole") {
        $and = isset($_GET['etatUser']) ? " AND etatUser=1 " : "";
        $data = [];
        $role = $_GET['role'];
        $db->query("SELECT * FROM wbcc_utilisateur u, wbcc_roles r, wbcc_contact c WHERE LOWER(libelleRole)=lower('$role') 
        AND u.role = r.idRole
        AND u.idContactF= c.idContact $and");
        $data = $db->resultSet();
        echo json_encode($data);
    }

    if ($action == "saveConfigurationUser") {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        $jour = "";
        $horaire = "";
        $codeDepartement = sizeof($tabDptsCoche) != 0 ? implode(";", $tabDptsCoche) : "";
        $CPZ = "";
        $villeZ = "";
        foreach ($zones as $key => $zone) {
            extract($zone);
            $CPZ .= $codePostal . ";";
            $villeZ .= $ville . ";";
        }
        foreach ($tabHorairesCoche as $key => $item) {
            $jour  = ($jour == "") ? $item['jour']  : ($jour . ";" . $item['jour']);
            $horaire  = ($horaire == "") ? ($item['heureDebut'] . '-' . $item['heureFin']) : $horaire . ";" . ($item['heureDebut'] . '-' . $item['heureFin']);
        }
        $db->query("UPDATE wbcc_utilisateur set jourTravail=:jour, horaireTravail=:horaire, cpZoneRV = :cpZoneRV, margeTravail=:margeTravail, villeZoneRV=:villeZoneRV , commentaireConfig=:com, typeZoneRV =:typeZone, codeDepartement=:codeDepartement WHERE idUtilisateur=:id ");

        $db->bind("id", $idCommercial, null);
        $db->bind("jour", $jour, null);
        $db->bind("horaire", $horaire, null);
        $db->bind("cpZoneRV", rtrim($CPZ, ";"), null);
        $db->bind("margeTravail", $margeTravail, null);
        $db->bind("villeZoneRV", rtrim($villeZ, ";"), null);
        $db->bind("com", $commentaire, null);
        $db->bind("typeZone", $typeZone, null);
        $db->bind("codeDepartement", $codeDepartement, null);

        if ($db->execute()) {
            if ($idEquipe != "0") {
                $db->query("DELETE FROM  wbcc_utilisateur_equipe WHERE idUtilisateurF = :idCom1");
                $db->bind("idCom1", $idCommercial, null);
                if ($db->execute()) {
                    $db->query("INSERT INTO  wbcc_utilisateur_equipe(idUtilisateurF, idEquipeTerrainF) VALUES (:idCom, :idEquipe) ");
                    $db->bind("idCom", $idCommercial, null);
                    $db->bind("idEquipe", $idEquipe, null);
                    $db->execute();
                }
            }
            echo json_encode("1");
        } else {
            echo json_encode("0");
        }
    }

    if ($action == "getConfigurationUser") {
        $idUser = $_GET['idUser'];
        $db->query("SELECT * FROM wbcc_utilisateur u, wbcc_equipe_terrain e, wbcc_utilisateur_equipe ue WHERE idUtilisateur = idUtilisateurF
        AND idEquipeTerrain=idEquipeTerrainF
        AND idUtilisateur= $idUser LIMIT 1");
        $data = $db->single();
        echo json_encode($data);
    }

    if ($action == "getCommerciauxB2C") {
        $db->query("SELECT * FROM wbcc_utilisateur u, wbcc_roles r, wbcc_contact c WHERE (LOWER(login)='bpa@wbcc.fr' OR LOWER(libelleRole)='commercial' OR LOWER(login)='pmesmin@wbcc.fr') AND u.role = r.idRole AND u.idContactF= c.idContact ORDER BY idUtilisateur DESC");
        $users = $db->resultSet();
        echo json_encode($users);
    }

    if ($action == "saveConfigurationB2C") {
        $_POST = json_decode(file_get_contents('php://input'), true);
        extract($_POST);
        $jour = "";
        $horaire = "";
        $codeDepartement = sizeof($tabDptsCoche) != 0 ? implode(";", $tabDptsCoche) : "";
        $CPZ = "";
        $villeZ = "";
        foreach ($zones as $key => $zone) {
            extract($zone);
            $CPZ .= $codePostal . ";";
            $villeZ .= $ville . ";";
        }
        foreach ($tabHorairesCoche as $key => $item) {
            $jour  = ($jour == "") ? $item['jour']  : ($jour . ";" . $item['jour']);
            $horaire  = ($horaire == "") ? ($item['heureDebut'] . '-' . $item['heureFin']) : $horaire . ";" . ($item['heureDebut'] . '-' . $item['heureFin']);
        }
        $db->query("UPDATE wbcc_utilisateur set jourTravailB2C=:jour, horaireTravailB2C=:horaire, margeTravailB2C=:margeTravail, commentaireConfig=:com, nbOpPrevuB2C=:nbOpPrevu, nbVisitePrevuB2C=:nbVisitePrevu, nbGardienB2C=:nbGardien, cpZoneB2C =:cpZoneB2C, villeZoneB2C=:villeZoneB2C, typeZoneB2C=:typeZoneB2C, codeDepartementB2C=:codeDepartementB2C  WHERE idUtilisateur=:id ");
        $db->bind("id", $idCommercial, null);
        $db->bind("jour", $jour, null);
        $db->bind("horaire", $horaire, null);
        $db->bind("margeTravail", $margeTravail, null);
        $db->bind("nbOpPrevu", $nbOpPrevu, null);
        $db->bind("nbVisitePrevu", $nbVisitePrevu, null);
        $db->bind("nbGardien", $nbGardien, null);
        $db->bind("com", $commentaire, null);
        $db->bind("cpZoneB2C", rtrim($CPZ, ";"), null);
        $db->bind("villeZoneB2C", rtrim($villeZ, ";"), null);
        $db->bind("typeZoneB2C", $typeZone, null);
        $db->bind("codeDepartementB2C", $codeDepartement, null);
        if ($db->execute()) {
            echo json_encode("1");
        } else {
            echo json_encode("0");
        }
    }
}

function findUserByEmail2($email)
{
    $db = new Database();
    $db->query("SELECT * FROM wbcc_utilisateur u,  wbcc_contact c WHERE LOWER(email)=LOWER(:email) 
        AND u.idContactF= c.idContact LIMIT 1");
    $db->bind("email", $email, null);
    $data = $db->single();
    return $data;
}

function findContactByEmail($email)
{
    $db = new Database();
    $db->query("SELECT * FROM   wbcc_contact WHERE LOWER(emailContact)=LOWER(:email)  LIMIT 1");
    $db->bind("email", $email, null);
    $data = $db->single();
    return $data;
}

function addTokenPwd($email, $token)
{
    $db = new Database();
    $db->query("
    UPDATE wbcc_utilisateur SET tokenPwd='$token' WHERE login='$email'");
    if ($db->execute()) {
        return true;
    }
    return false;
}

function saveContact($idContact, $numero, $sexe, $prenom, $nom, $tel, $email,  $dateNaissance, $pseudoSkype, $telWhatsapp, $idStatut)
{
    $db = new Database();
    if ($idContact == "0") {
        $db->query("
        INSERT INTO wbcc_contact(numeroContact, civiliteContact, prenomContact, nomContact, fullName, telContact, emailContact, dateNaissance, skype, whatsapp, statutContact, referredBy, editDate, createDate, isUser, etatContact) VALUES (:numeroContact, :civilite, :firstName, :lastName, :fullName, :businessPhone, :businessEmail, :birthDate, :skype, :whatsapp, :category, :referredBy, :editDate, :createDate, :isUser, :etatContact)");
        $db->bind("numeroContact", $numero, null);
    } else {
        $db->query("
        UPDATE wbcc_contact SET  civiliteContact=:civilite, prenomContact=:firstName, nomContact=:lastName, fullName=:fullName, telContact=:businessPhone, emailContact=:businessEmail, dateNaissance=:birthDate, skype=:skype, whatsapp=:whatsapp, statutContact=:category, referredBy=:referredBy, editDate=:editDate, createDate=:createDate, isUser=:isUser, etatContact=:etatContact WHERE idContact = :idContact");
        $db->bind("idContact", $idContact, null);
    }
    $db->bind("civilite", $sexe, null);
    $db->bind("firstName", $prenom, null);
    $db->bind("lastName", $nom, null);
    $db->bind("fullName", $prenom . ' ' . $nom, null);
    $db->bind("businessPhone", $tel, null);
    $db->bind("businessEmail", $email, null);
    $db->bind("birthDate", date("Y-m-d"), null);
    $db->bind("skype", $pseudoSkype, null);
    $db->bind("whatsapp", $telWhatsapp, null);
    $db->bind("category", "$idStatut", null);
    $db->bind("referredBy", "APP MOBILE", null);
    $db->bind("editDate", date('Y-m-d h:i:s'), null);
    $db->bind("createDate",  date('Y-m-d h:i:s'), null);
    $db->bind("isUser", 1, null);
    $db->bind("etatContact", 1, null);
    if ($db->execute()) {
        if ($idContact == "0") {
            return findItemByColumn("wbcc_contact", "numeroContact", $numero);
        } else {
            return findItemByColumn("wbcc_contact", "idContact", $idContact);
        }
    }
    return 0;
}

function addUser($login, $mdp, $email, $idRoleF, $idContact, $token)
{
    $db = new Database();
    $pass = sha1($mdp);
    $db->query("INSERT INTO wbcc_utilisateur(login,mdp,email,role,etatUser,firstConnection,idContactF, token) 
    VALUES ('$login','$pass','$email', $idRoleF, 0, 0, $idContact, '$token')");
    return $db->execute();
}
