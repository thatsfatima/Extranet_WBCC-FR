-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 18 mars 2025 à 01:09
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `extranet_wbcc_fr`
--

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_activity_db`
--

CREATE TABLE `wbcc_activity_db` (
  `idActivitydb` int(11) NOT NULL,
  `codeActivity` int(11) DEFAULT NULL,
  `libelleActivity` varchar(255) DEFAULT NULL,
  `lien` varchar(255) DEFAULT NULL,
  `usedByOP` int(11) NOT NULL DEFAULT 1,
  `accessByRole` varchar(100) DEFAULT NULL,
  `priorite` int(11) DEFAULT NULL,
  `etatActivity` int(11) DEFAULT 1,
  `nomVariableOP` varchar(255) DEFAULT NULL,
  `nomVariableIdAuteurOP` varchar(255) DEFAULT NULL,
  `nomVariableDateOP` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_activity_db`
--

INSERT INTO `wbcc_activity_db` (`idActivitydb`, `codeActivity`, `libelleActivity`, `lien`, `usedByOP`, `accessByRole`, `priorite`, `etatActivity`, `nomVariableOP`, `nomVariableIdAuteurOP`, `nomVariableDateOP`) VALUES
(1, 1, 'Signer Délégation de gestion', 'te', 1, NULL, 1, 1, 'delegationSigne', 'idAuteurSignatureDelegation', 'dateSignatureDelegation'),
(2, 2, 'Faire la Télé-Expertise', 'te', 1, NULL, 2, 1, 'teleExpertiseFaite', 'idAuteurTeleExpertise', 'dateTeleExpertise'),
(3, 3, 'Prendre RDV RT', 'te', 1, NULL, 3, 1, 'priseRvRT', 'idAuteurPriseRvRT', 'datePriseRvRT'),
(4, 4, 'Déclarer Sinistre à la Cie', 'dc', 1, NULL, 19, 1, 'declarationCie', 'idAuteurDeclarationCie', 'dateDeclarationCie'),
(5, 5, 'Relancer Cie pour avoir numéro de sinistre', 'dc', 1, NULL, 20, 1, 'relanceCieNumSinistre', 'idAuteurRelanceCieNumSinistre', 'dateRelanceCieNumSinistre'),
(6, 6, 'Faire FRT', 'frt', 1, NULL, 5, 1, 'frtFait', 'idAuteurFrt', 'dateFrt'),
(7, 7, 'Faire Devis', 'fd', 1, NULL, 15, 1, 'devisFais', 'idAuteurDevisFais', 'dateDevisFais'),
(8, 8, 'Contrôler Devis', 'fd', 1, NULL, 16, 1, 'controlDevis', 'idAuteurControlDevis', 'dateControlDevis'),
(9, 9, 'Envoyer Devis', 'fd', 1, NULL, 21, 1, 'envoiDevis', 'idAuteurEnvoiDevis', 'dateEnvoiDevis'),
(10, 10, 'Relancer Cie Pour prise en charge du devis', 'fd', 1, NULL, 22, 1, 'relanceCiePriseEnCharge', 'idAuteurRelanceCiePriseEnCharge', 'dateRelanceCiePriseEnCharge'),
(11, 11, 'Contrôler FRT', 'fd', 1, NULL, 6, 1, 'controleFRT', 'idAuteurControleFRT', 'dateControleFRT'),
(12, 12, 'Contester Refus de prise en charge du devis', 'fd', 1, NULL, 23, 1, 'relanceRepaDevis', 'idAuteurRelanceRepaDevis', 'dateRelanceRepaDevis'),
(13, 13, '', NULL, 0, NULL, NULL, 1, NULL, NULL, NULL),
(14, 14, 'Appeler le sinistré pour prendre RDV d\'expertise', 'prdvs', 1, NULL, 25, 1, 'appelPourDateRdvExpertise', 'idAuteurAppelPourDateRdvExpertise', 'dateAppelPourDateRdvExpertise'),
(15, 15, 'Appeler Cie pour Prendre RDV Expertise', 'prdvExpert', 1, NULL, 26, 1, 'appelCabinetPrendreRDVExpertise', 'IdAuteurAppelCabinetPrendreRDVExpertise', 'dateAppelCabinetPrendreRDVExpertise'),
(16, 16, 'Faire Compte Rendu RT', 'rt', 1, NULL, 17, 1, 'faireRapportRT', 'idAuteurFaireRapportRT', 'dateFaireRapportRT'),
(17, 17, 'Contrôler Compte Rendu RT', 'rt', 1, NULL, 18, 1, 'controleRT', 'idAuteurControleRT', 'dateControleRT'),
(18, 18, 'Faire RDV EXPERTISE CONTRADICTOIRE', 'suiteExpertise', 1, NULL, 27, 1, 'rdvExpertiseFait', 'idAuteurRdvExpertise', 'dateRdvExpertise'),
(19, 19, 'Appeler Cie pour prendre coordonnées Expert', 'prdvExpert', 1, NULL, 24, 1, 'appelPourDateRdvExpertise', 'idAuteurAppelPourDateRdvExpertise', 'dateAppelPourDateRdvExpertise'),
(20, 20, 'Relancer Cie pour avoir Lettre d\'Acceptation', 'la', 1, NULL, 29, 1, 'relanceLettreAcceptation', 'idAuteurLettreAcceptation', 'dateLettreAcceptation'),
(21, 21, 'Relancer Cie pour le paiement de l\'immédiat', 'eci', 1, NULL, 30, 1, 'relanceCiePaiementImmediat', 'idAuteurRelanceCiePaiementImmediat', 'dateRelanceCiePaiementImmediat'),
(22, 22, 'Relancer Cie pour le paiement du différé', 'eci', 1, NULL, 33, 1, 'relanceCiePaiementDiffere', 'idAuteurRelanceCiePaiementDiffere', 'dateRelanceCiePaiementDiffere'),
(23, 23, 'Appeler Sinistre Pour prendre RDV Travaux', 'rvTr', 1, NULL, 31, 1, 'appelSinistrePrendreRDVTravaux', 'idAuteurAppelSinistrePrendreRDVTravaux', 'dateAppelSinistrePrendreRDVTravaux'),
(24, 24, 'Recupérer Constat DDE', 'constatDDE', 1, NULL, 7, 1, 'genererConstatDDE', 'idAuteurGenererConstatDDE', 'dateGenererConstatDDE'),
(25, 25, 'Analyse Police d\'Assurance', 'fse', 1, NULL, 4, 1, NULL, NULL, NULL),
(26, 26, 'Réglement Franchise', NULL, 0, NULL, NULL, 1, '', '', NULL),
(27, 27, 'Règlement Immédiat', NULL, 0, NULL, NULL, 1, NULL, NULL, ''),
(28, 28, 'Irrégularité d\'encaissement', NULL, 0, NULL, NULL, 1, '', NULL, NULL),
(29, 29, 'Programmer Recherche Fuite', 'priseRDVRF', 1, NULL, 9, 1, NULL, NULL, NULL),
(30, 30, 'Faire Recherche de Fuite', 'priseRDVRF', 1, NULL, 10, 1, 'rechercheFuite', 'idAuteurRechercheFuite', 'dateRechercheFuite'),
(31, 31, 'Confirmation Encaissement Chèque', NULL, 0, NULL, NULL, 1, '', NULL, NULL),
(32, 32, 'Demande de Justificatif de Réparation de Fuite', 'justificatifRF', 1, NULL, 13, 1, 'justificatifReparation', 'idAuteurJustificatifReparation	', 'dateJustificatifReparation	'),
(33, 33, 'Demande Réparation de Fuite', 'reparationFuite', 1, NULL, 12, 1, 'demandeReparation', 'idAuteurDemandeReparation', 'dateDemandeReparation'),
(34, 34, 'Récupération Coordonnées Gardien', 'constatDDE', 1, NULL, 8, 1, NULL, NULL, NULL),
(35, 35, 'Récupération Coordonnées Voisin', 'constatDDE', 1, NULL, 8, 1, 'voisin', NULL, NULL),
(36, 36, 'Récupération Coordonnées Artisan', 'constatDDE', 1, NULL, 8, 1, NULL, NULL, NULL),
(37, 37, 'Règlement Différé', NULL, 0, NULL, NULL, 1, '', NULL, 'dateReglementDiffere'),
(38, 38, 'Clôture Enveloppe', NULL, 0, NULL, NULL, 1, NULL, NULL, NULL),
(39, 39, 'Impression Enveloppe', NULL, 0, NULL, NULL, 1, NULL, NULL, NULL),
(40, 40, 'Preuve Dépot à la Poste', NULL, 0, NULL, NULL, 1, NULL, NULL, NULL),
(41, 41, 'Accusé de Réception Enveloppe', NULL, 0, NULL, NULL, 1, NULL, NULL, NULL),
(42, 42, 'Envoi Justificatif de Réparation de fuite', 'envoiJustificatifRF', 1, NULL, 14, 1, 'envoieJusiRepaCauseCie', 'idAuteurEnvoieJusiRepaCauseCie', 'dateEnvoieJusiRepaCauseCie'),
(43, 43, 'Gestion suite expertise', 'suiteExpertise', 1, NULL, 28, 1, NULL, NULL, NULL),
(44, 44, 'Envoi Constat DDE', 'envoiConstatDDE', 1, NULL, 8, 1, NULL, NULL, NULL),
(45, 0, 'TACHE SANS ETAPE', '', 0, NULL, NULL, 0, NULL, NULL, NULL),
(46, 45, 'Faire RDV Travaux', 'rvTr', 1, NULL, 32, 1, 'faireRvTravaux', 'idAuteurFaireRvTravaux', 'dateFaireRvTravaux'),
(47, 46, 'Traitement Retour Recherche Fuite', 'suiteRF', 1, NULL, 11, 1, NULL, NULL, NULL),
(49, 47, 'Appeler sinistré pour encaissement des 30% ', '', 1, NULL, 30, 1, NULL, NULL, NULL),
(50, 48, 'Décaissement des 70% à l\'assuré', '', 1, NULL, 30, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_appartement`
--

CREATE TABLE `wbcc_appartement` (
  `idApp` int(11) NOT NULL,
  `numeroApp` varchar(100) DEFAULT NULL,
  `codeApp` varchar(100) DEFAULT NULL,
  `etage` varchar(100) DEFAULT NULL,
  `codePorte` varchar(100) DEFAULT NULL,
  `escalier` varchar(25) DEFAULT NULL,
  `batiment` varchar(100) DEFAULT NULL,
  `lot` varchar(255) DEFAULT NULL,
  `typeLot` varchar(25) DEFAULT 'PP',
  `libellePartieCommune` varchar(200) DEFAULT NULL,
  `cote` varchar(100) DEFAULT NULL,
  `digicode` varchar(100) DEFAULT NULL,
  `interphone` varchar(50) DEFAULT NULL,
  `idImmeubleF` int(11) DEFAULT NULL,
  `etatApp` int(11) NOT NULL DEFAULT 1,
  `indexCompteur` varchar(255) DEFAULT NULL,
  `numeroCompteur` varchar(100) DEFAULT NULL,
  `photoCompteur` text DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `nbPiece` varchar(255) DEFAULT NULL,
  `surface` varchar(255) DEFAULT NULL,
  `codeWBCC` varchar(255) DEFAULT NULL,
  `codeImmeubleWBCC` varchar(255) DEFAULT NULL,
  `proprietaire` varchar(255) DEFAULT NULL,
  `occupant` varchar(255) DEFAULT NULL,
  `typeOccupation` varchar(255) DEFAULT NULL,
  `typeOccupant` varchar(255) DEFAULT NULL,
  `compagnieAssuranceOccupant` varchar(255) DEFAULT NULL,
  `courtierOccupant` varchar(255) DEFAULT NULL,
  `refOccupant` varchar(255) DEFAULT NULL,
  `numPoliceOccupant` varchar(255) DEFAULT NULL,
  `dateEffetOccupant` varchar(255) DEFAULT NULL,
  `dateEcheanceOccupant` varchar(255) DEFAULT NULL,
  `copieOccupant` varchar(255) DEFAULT NULL,
  `typeProprietaire` varchar(255) DEFAULT NULL,
  `compagnieAssuranceProprietaire` varchar(255) DEFAULT NULL,
  `numPoliceProprietaire` varchar(255) DEFAULT NULL,
  `dateEffetProprietaire` varchar(255) DEFAULT NULL,
  `dateEcheanceProprietaire` varchar(255) DEFAULT NULL,
  `copieProprietaire` varchar(255) DEFAULT NULL,
  `nomImmeubleSyndic` varchar(255) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `codePostal` varchar(255) DEFAULT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `dateDebutContrat` varchar(25) DEFAULT NULL,
  `dateFinContrat` varchar(25) DEFAULT NULL,
  `createDate` varchar(50) DEFAULT current_timestamp(),
  `editDate` varchar(50) DEFAULT current_timestamp(),
  `departement` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `gererApp` varchar(255) DEFAULT NULL,
  `guidOccupant` varchar(50) DEFAULT NULL,
  `guidProprietaire` varchar(50) DEFAULT NULL,
  `idOccupant` int(11) DEFAULT NULL,
  `idProprietaire` int(11) DEFAULT NULL,
  `idAgenceImmobiliere` int(11) DEFAULT NULL,
  `idCompanyCopro` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_appartement`
--

INSERT INTO `wbcc_appartement` (`idApp`, `numeroApp`, `codeApp`, `etage`, `codePorte`, `escalier`, `batiment`, `lot`, `typeLot`, `libellePartieCommune`, `cote`, `digicode`, `interphone`, `idImmeubleF`, `etatApp`, `indexCompteur`, `numeroCompteur`, `photoCompteur`, `commentaire`, `nbPiece`, `surface`, `codeWBCC`, `codeImmeubleWBCC`, `proprietaire`, `occupant`, `typeOccupation`, `typeOccupant`, `compagnieAssuranceOccupant`, `courtierOccupant`, `refOccupant`, `numPoliceOccupant`, `dateEffetOccupant`, `dateEcheanceOccupant`, `copieOccupant`, `typeProprietaire`, `compagnieAssuranceProprietaire`, `numPoliceProprietaire`, `dateEffetProprietaire`, `dateEcheanceProprietaire`, `copieProprietaire`, `nomImmeubleSyndic`, `adresse`, `codePostal`, `ville`, `dateDebutContrat`, `dateFinContrat`, `createDate`, `editDate`, `departement`, `region`, `gererApp`, `guidOccupant`, `guidProprietaire`, `idOccupant`, `idProprietaire`, `idAgenceImmobiliere`, `idCompanyCopro`) VALUES
(1, '11', '111', '1', '1111', NULL, NULL, NULL, 'PP', NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'current_timestamp()', 'current_timestamp()', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_appartement_contact`
--

CREATE TABLE `wbcc_appartement_contact` (
  `idAppCon` int(11) NOT NULL,
  `idAppartementF` int(11) NOT NULL,
  `idContactF` int(11) NOT NULL,
  `numeroAppartementF` varchar(100) DEFAULT NULL,
  `numeroContactF` varchar(100) DEFAULT NULL,
  `etatAppCon` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_company`
--

CREATE TABLE `wbcc_company` (
  `idCompany` int(11) NOT NULL,
  `numeroCompany` varchar(255) DEFAULT NULL,
  `numeroRCS` varchar(255) DEFAULT NULL,
  `villeRCS` varchar(255) DEFAULT NULL,
  `numeroSiret` varchar(255) DEFAULT NULL,
  `codeSociete` varchar(255) DEFAULT NULL,
  `dateCreationJuridique` varchar(25) DEFAULT NULL,
  `etatConvention` int(11) DEFAULT 0,
  `dateEffetConvention` varchar(25) DEFAULT NULL,
  `dateEcheanceConvention` varchar(25) DEFAULT NULL,
  `categorieDO` varchar(255) DEFAULT NULL,
  `sousCategorieDO` varchar(255) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `industry` varchar(255) DEFAULT NULL,
  `secteur` varchar(255) DEFAULT NULL,
  `convention` varchar(255) DEFAULT NULL,
  `kbs` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `nomCommercial` varchar(255) DEFAULT NULL,
  `codeCommercial` varchar(255) DEFAULT NULL,
  `idCommercial` int(11) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `enseigne` varchar(255) DEFAULT NULL,
  `nomGestionnaire` varchar(255) DEFAULT NULL,
  `codeGestionnaire` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `businessPostalCode` varchar(255) DEFAULT NULL,
  `businessLine1` text DEFAULT NULL,
  `businessLine2` text DEFAULT NULL,
  `businessCity` varchar(255) DEFAULT NULL,
  `businessCountryName` varchar(255) DEFAULT NULL,
  `businessState` varchar(255) DEFAULT NULL,
  `businessPhone` varchar(255) DEFAULT NULL,
  `faxPhone` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `webaddress` varchar(255) DEFAULT NULL,
  `siccode` varchar(255) DEFAULT NULL,
  `revenue` varchar(255) DEFAULT NULL,
  `numEmployees` varchar(25) DEFAULT NULL,
  `referredBy` varchar(255) DEFAULT NULL,
  `editDate` varchar(50) DEFAULT current_timestamp(),
  `createDate` varchar(50) DEFAULT current_timestamp(),
  `idTitreF` int(11) DEFAULT NULL,
  `idServiceF` int(11) DEFAULT NULL,
  `idGerantF` int(11) DEFAULT NULL,
  `idGuidAA` varchar(50) DEFAULT NULL,
  `idApporteurDO` int(11) DEFAULT NULL,
  `idGuidAADO` varchar(50) DEFAULT NULL,
  `formeJuridique` varchar(255) DEFAULT NULL,
  `natureJuridique` varchar(255) DEFAULT NULL,
  `idArtisanDevisF` int(11) DEFAULT NULL,
  `registreCommerce` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_company`
--

INSERT INTO `wbcc_company` (`idCompany`, `numeroCompany`, `numeroRCS`, `villeRCS`, `numeroSiret`, `codeSociete`, `dateCreationJuridique`, `etatConvention`, `dateEffetConvention`, `dateEcheanceConvention`, `categorieDO`, `sousCategorieDO`, `commentaire`, `industry`, `secteur`, `convention`, `kbs`, `email`, `nomCommercial`, `codeCommercial`, `idCommercial`, `region`, `enseigne`, `nomGestionnaire`, `codeGestionnaire`, `name`, `businessPostalCode`, `businessLine1`, `businessLine2`, `businessCity`, `businessCountryName`, `businessState`, `businessPhone`, `faxPhone`, `category`, `description`, `webaddress`, `siccode`, `revenue`, `numEmployees`, `referredBy`, `editDate`, `createDate`, `idTitreF`, `idServiceF`, `idGerantF`, `idGuidAA`, `idApporteurDO`, `idGuidAADO`, `formeJuridique`, `natureJuridique`, `idArtisanDevisF`, `registreCommerce`, `logo`) VALUES
(1, 'SGSDGSD', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Test Organisme', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ORGANISME', NULL, NULL, NULL, NULL, NULL, NULL, 'current_timestamp()', 'current_timestamp()', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_condition`
--

CREATE TABLE `wbcc_condition` (
  `idCondition` int(11) NOT NULL,
  `numeroCondition` varchar(50) DEFAULT NULL,
  `idTypeConditionF` int(11) DEFAULT NULL,
  `operateur` varchar(255) DEFAULT NULL,
  `signeOperateur` varchar(255) DEFAULT NULL,
  `valeur` varchar(255) DEFAULT NULL,
  `createDate` varchar(25) DEFAULT current_timestamp(),
  `editDate` varchar(25) DEFAULT current_timestamp(),
  `idAuteur` int(11) DEFAULT NULL,
  `etatCondition` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_condition`
--

INSERT INTO `wbcc_condition` (`idCondition`, `numeroCondition`, `idTypeConditionF`, `operateur`, `signeOperateur`, `valeur`, `createDate`, `editDate`, `idAuteur`, `etatCondition`) VALUES
(1, 'C271220240946241', 1, 'Supérieur ou Egal', '>=', '23', '2024-12-27 09:46:24', '2024-12-27 11:16:18', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_condition_critere`
--

CREATE TABLE `wbcc_condition_critere` (
  `idConditionCritere` int(11) NOT NULL,
  `idConditionF` int(11) DEFAULT NULL,
  `idCritereF` int(11) NOT NULL,
  `createDate` varchar(25) DEFAULT current_timestamp(),
  `editDate` varchar(25) DEFAULT current_timestamp(),
  `idAuteur` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_condition_critere`
--

INSERT INTO `wbcc_condition_critere` (`idConditionCritere`, `idConditionF`, `idCritereF`, `createDate`, `editDate`, `idAuteur`) VALUES
(2, 1, 1, '2024-12-27 11:16:18', '2024-12-27 11:16:18', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_contact`
--

CREATE TABLE `wbcc_contact` (
  `idContact` int(11) NOT NULL,
  `numeroContact` varchar(100) DEFAULT NULL,
  `nomContact` varchar(255) DEFAULT NULL,
  `prenomContact` varchar(255) DEFAULT NULL,
  `fullName` varchar(255) DEFAULT NULL,
  `telContact` varchar(255) DEFAULT NULL,
  `emailContact` varchar(255) DEFAULT NULL,
  `dateNaissance` varchar(25) DEFAULT NULL,
  `adresseContact` text DEFAULT NULL,
  `codePostalContact` varchar(25) DEFAULT NULL,
  `villeContact` varchar(100) DEFAULT NULL,
  `statutContact` varchar(255) DEFAULT NULL,
  `etatContact` int(11) DEFAULT 1,
  `civiliteContact` varchar(25) DEFAULT NULL,
  `copieCNI` text DEFAULT NULL,
  `copieCA` text DEFAULT NULL,
  `copieTP` text DEFAULT NULL,
  `commentaireCNI` text DEFAULT NULL,
  `commentaireCA` text DEFAULT NULL,
  `commentaireTP` text DEFAULT NULL,
  `lienParente` varchar(255) DEFAULT NULL,
  `age` varchar(255) DEFAULT NULL,
  `fiscalementCharge` varchar(255) DEFAULT NULL,
  `fileJustificatifOcc` text DEFAULT NULL,
  `idContactFContact` int(11) DEFAULT NULL,
  `codeFiche` varchar(100) DEFAULT NULL,
  `skype` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(100) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `companyName` varchar(255) DEFAULT NULL,
  `departement` varchar(255) DEFAULT NULL,
  `mobilePhone` varchar(100) DEFAULT NULL,
  `faxPhone` varchar(100) DEFAULT NULL,
  `emailCollaboratif` varchar(255) DEFAULT NULL,
  `businessLine2` varchar(255) DEFAULT NULL,
  `businessState` varchar(255) DEFAULT NULL,
  `businessCountryName` varchar(255) DEFAULT NULL,
  `digicode1` varchar(100) DEFAULT NULL,
  `codePorte` varchar(50) DEFAULT NULL,
  `batiment` varchar(50) DEFAULT NULL,
  `etage` varchar(50) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `referredBy` varchar(255) DEFAULT NULL,
  `createDate` varchar(50) DEFAULT current_timestamp(),
  `editDate` varchar(50) DEFAULT current_timestamp(),
  `jobTitle` varchar(255) DEFAULT NULL,
  `service` varchar(255) DEFAULT NULL,
  `motifSuppressionCompte` text DEFAULT NULL,
  `isUser` int(11) NOT NULL DEFAULT 0,
  `isPersonnel` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_contact`
--

INSERT INTO `wbcc_contact` (`idContact`, `numeroContact`, `nomContact`, `prenomContact`, `fullName`, `telContact`, `emailContact`, `dateNaissance`, `adresseContact`, `codePostalContact`, `villeContact`, `statutContact`, `etatContact`, `civiliteContact`, `copieCNI`, `copieCA`, `copieTP`, `commentaireCNI`, `commentaireCA`, `commentaireTP`, `lienParente`, `age`, `fiscalementCharge`, `fileJustificatifOcc`, `idContactFContact`, `codeFiche`, `skype`, `whatsapp`, `commentaire`, `category`, `companyName`, `departement`, `mobilePhone`, `faxPhone`, `emailCollaboratif`, `businessLine2`, `businessState`, `businessCountryName`, `digicode1`, `codePorte`, `batiment`, `etage`, `source`, `referredBy`, `createDate`, `editDate`, `jobTitle`, `service`, `motifSuppressionCompte`, `isUser`, `isPersonnel`) VALUES
(1, 'vdfgsdfsddfhdffgd', 'BALTI', 'Jawher', 'Jawher BALTI', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'current_timestamp()', 'current_timestamp()', NULL, NULL, NULL, 0, 0),
(2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'current_timestamp()', 'current_timestamp()', NULL, NULL, NULL, 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_contact_company`
--

CREATE TABLE `wbcc_contact_company` (
  `idContactCompany` int(11) NOT NULL,
  `idContactF` int(11) NOT NULL,
  `idCompanyF` int(11) NOT NULL,
  `numeroContactF` varchar(255) DEFAULT NULL,
  `numeroCompanyF` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_critere`
--

CREATE TABLE `wbcc_critere` (
  `idCritere` int(11) NOT NULL,
  `numeroCritere` varchar(50) DEFAULT NULL,
  `valeurCritere` varchar(255) DEFAULT NULL,
  `typeValeurCritere` varchar(255) DEFAULT NULL,
  `createDate` varchar(25) DEFAULT current_timestamp(),
  `editDate` varchar(25) DEFAULT current_timestamp(),
  `idAuteur` int(11) DEFAULT NULL,
  `etatCritere` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_critere`
--

INSERT INTO `wbcc_critere` (`idCritere`, `numeroCritere`, `valeurCritere`, `typeValeurCritere`, `createDate`, `editDate`, `idAuteur`, `etatCritere`) VALUES
(1, 'C181220241050521', '10', 'Pourcentage', '2024-12-18 10:50:52', '2024-12-27 09:47:19', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_critere_subvention`
--

CREATE TABLE `wbcc_critere_subvention` (
  `idCritereSubvention` int(11) NOT NULL,
  `numeroCritereSubvention` varchar(50) DEFAULT NULL,
  `idCritereF` int(11) DEFAULT NULL,
  `idSubventionF` int(11) DEFAULT NULL,
  `createDate` varchar(25) DEFAULT current_timestamp(),
  `editDate` varchar(25) DEFAULT current_timestamp(),
  `idAuteur` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_critere_subvention`
--

INSERT INTO `wbcc_critere_subvention` (`idCritereSubvention`, `numeroCritereSubvention`, `idCritereF`, `idSubventionF`, `createDate`, `editDate`, `idAuteur`) VALUES
(1, NULL, 1, 1, '2024-12-18 10:50:52', '2024-12-18 10:50:52', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_document`
--

CREATE TABLE `wbcc_document` (
  `idDocument` int(11) NOT NULL,
  `numeroDocument` varchar(255) DEFAULT NULL,
  `nomDocument` varchar(255) DEFAULT NULL,
  `urlDocument` varchar(255) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `createDate` datetime DEFAULT NULL,
  `editDate` varchar(25) DEFAULT current_timestamp(),
  `etatDocument` int(11) DEFAULT 1,
  `idUtilisateurF` int(11) DEFAULT NULL,
  `guidNote` varchar(50) DEFAULT NULL,
  `guidActivity` varchar(50) DEFAULT NULL,
  `guidHistory` varchar(50) DEFAULT NULL,
  `typeFichier` varchar(255) DEFAULT NULL,
  `size` varchar(255) DEFAULT NULL,
  `guidUser` varchar(255) DEFAULT NULL,
  `source` varchar(50) DEFAULT NULL,
  `auteur` varchar(255) DEFAULT NULL,
  `publie` int(11) NOT NULL DEFAULT 1,
  `isDeleted` int(11) DEFAULT 0,
  `urlDossier` varchar(255) NOT NULL DEFAULT 'opportunite'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_document`
--

INSERT INTO `wbcc_document` (`idDocument`, `numeroDocument`, `nomDocument`, `urlDocument`, `commentaire`, `createDate`, `editDate`, `etatDocument`, `idUtilisateurF`, `guidNote`, `guidActivity`, `guidHistory`, `typeFichier`, `size`, `guidUser`, `source`, `auteur`, `publie`, `isDeleted`, `urlDossier`) VALUES
(1, 'DOC_6777e855a09e0', 'madougueye.pdf', 'DOC_6777e855a09e0.pdf', NULL, '2025-01-03 14:38:29', '2025-01-03 14:38:29', 1, NULL, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(2, 'DOC_6777e8d0dd873', 'CNI.pdf', 'DOC_6777e8d0dd873.pdf', NULL, '2025-01-03 14:40:32', '2025-01-03 14:40:32', 1, NULL, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(3, 'DOC_6777f0118caa7', 'projet_last.zip', 'DOC_6777f0118caa7.zip', NULL, '2025-01-03 15:11:29', '2025-01-03 15:11:29', 1, NULL, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(4, 'DOC_6777f22cc9702', 'image 58@2x.png', 'DOC_6777f22cc9702.png', NULL, '2025-01-03 15:20:28', '2025-01-03 15:20:28', 1, NULL, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(5, 'DOC_6777f6828d48c', 'Capture001.png', 'DOC_6777f6828d48c.png', NULL, '2025-01-03 15:38:58', '2025-01-03 15:38:58', 1, NULL, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(6, 'DOC_67780169c4b60', 'Classeur1.xlsx', 'DOC_67780169c4b60.xlsx', NULL, '2025-01-03 16:25:29', '2025-01-03 16:25:29', 1, NULL, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(7, 'DOC_6778018014681', 'DECHARGE AMINATA.docx', 'DOC_6778018014681.docx', NULL, '2025-01-03 16:25:52', '2025-01-03 16:25:52', 1, NULL, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(8, 'DOC_6778095c974f7', 'Feedback_Data.xml', 'DOC_6778095c974f7.xml', NULL, '2025-01-03 16:59:24', '2025-01-03 16:59:24', 1, NULL, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(9, 'DOC1_677bc91897f637.71084580', 'DECHARGE AMINATA.docx', 'DOC1_677bc91897f637.71084580.docx', NULL, '2025-01-06 13:14:16', '2025-01-06 13:14:16', 1, NULL, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(10, 'DOC1_677bd02e903c83.59346341', 'SecurityCmdLnApp_LogFile.txt', 'DOC1_677bd02e903c83.59346341.txt', NULL, '2025-01-06 13:44:30', '2025-01-06 13:44:30', 1, NULL, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(11, 'DOC1_677bd934110e42.78130906', 'PRECAU.txt', 'DOC1_677bd934110e42.78130906.txt', NULL, '2025-01-06 14:23:00', '2025-01-06 14:23:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(12, 'DOC1_677bd9e5e131f3.57455054', 'DECHARGE AMINATA.docx', 'DOC1_677bd9e5e131f3.57455054.docx', NULL, '2025-01-06 14:25:57', '2025-01-06 14:25:57', 1, NULL, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(13, 'DOC_1_677c0a485a8e29.49364526', '9-INFORMATION COMPTABLE SPECIFIQUE.pdf', 'DOC_1_677c0a485a8e29.49364526.pdf', NULL, '2025-01-06 17:52:24', '2025-01-06 17:52:24', 1, NULL, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(14, 'DOC_1_677c0aee71aa16.78064108', '9-INFORMATION COMPTABLE SPECIFIQUE.pdf', 'DOC_1_677c0aee71aa16.78064108.pdf', NULL, '2025-01-06 17:55:10', '2025-01-06 17:55:10', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(15, 'DOC_1_677c0af975b070.88379122', 'CodeActivity_TableActivity.xlsx', 'DOC_1_677c0af975b070.88379122.xlsx', NULL, '2025-01-06 17:55:21', '2025-01-06 17:55:21', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(16, 'DOC_1_677c0b1fe6c9a9.19865383', 'MODEL EMAIL DE DECLARATION DE SINISTRE AUX COMPAGNIE D\'ASSURANCE V2 (1).docx', 'DOC_1_677c0b1fe6c9a9.19865383.docx', NULL, '2025-01-06 17:55:59', '2025-01-06 17:55:59', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(17, 'DOC_1_677c0b41eeee88.98095676', 'LISTE DETAILLEE DES INFILTRATIONS EN CAS DE DDE 2024-02-04.docx', 'DOC_1_677c0b41eeee88.98095676.docx', NULL, '2025-01-06 17:56:33', '2025-01-06 17:56:33', 1, NULL, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', '', 0, 0, 'projet/annexe'),
(18, 'DOC_1_677c0b9a092295.35740128', 'WhatsApp Image 2024-01-30 at 08.47.38.jpeg', 'DOC_1_677c0b9a092295.35740128.jpeg', NULL, '2025-01-06 17:58:02', '2025-01-06 17:58:02', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(19, 'DOC_1_677c0c77c35129.29706615', '8-LISTE DU PERSONNEL.pdf', 'DOC_1_677c0c77c35129.29706615.pdf', NULL, '2025-01-06 18:01:43', '2025-01-06 18:01:43', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(20, 'DOC_1_677c0df28cb027.90875627', 'Origines_Degats_des_Eaux_1_15.xlsx', 'DOC_1_677c0df28cb027.90875627.xlsx', NULL, '2025-01-06 18:08:02', '2025-01-06 18:08:02', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(21, 'DOC_1_677cf07bf40893.37452903', '1-Lettre d\'engagement.pdf', 'DOC_1_677cf07bf40893.37452903.pdf', NULL, '2025-01-07 10:14:36', '2025-01-07 10:14:36', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(22, 'DOC_1_677d1e7bacc9b5.71729015', '7-RESPONSABLE TECHNIQUE.pdf', 'DOC_1_677d1e7bacc9b5.71729015.pdf', NULL, '2025-01-07 13:30:51', '2025-01-07 13:30:51', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(23, 'DOC_1_677d1ea48e13f5.41530614', '14-WBCC ASSISTANCE CONTRAT PRESTATAIRES - CONDITIONS GENERALES V3 2020-12-26.docx', 'DOC_1_677d1ea48e13f5.41530614.docx', NULL, '2025-01-07 13:31:32', '2025-01-07 13:31:32', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(24, 'DOC_1_677d23809374e6.94672370', 'REFONTE TELE EXPERTISE INCENDIE 2024-02-04.doc', 'DOC_1_677d23809374e6.94672370.doc', NULL, '2025-01-07 13:52:16', '2025-01-07 13:52:16', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(25, 'DOC_1_677d2fc96afb26.60575558', 'CAHIER DE CHARGE MODULE REPA.docx', 'DOC_1_677d2fc96afb26.60575558.docx', NULL, '2025-01-07 14:44:41', '2025-01-07 14:44:41', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(26, 'DOC_1_677d2fedbf8e97.34321844', '10-Métiers et catégories de travaux couverts par la qualification.pdf', 'DOC_1_677d2fedbf8e97.34321844.pdf', NULL, '2025-01-07 14:45:17', '2025-01-07 14:45:17', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(27, 'DOC_1_677d30aee637f5.09402869', 'WhatsApp Image 2024-01-30 at 08.47.38.jpeg', 'DOC_1_677d30aee637f5.09402869.jpeg', NULL, '2025-01-07 14:48:30', '2025-01-07 14:48:30', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(28, 'DOC_1_677d32de58ea76.62537685', '00.CHAPITRE 1.pdf', 'DOC_1_677d32de58ea76.62537685.pdf', NULL, '2025-01-07 14:57:50', '2025-01-07 14:57:50', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(29, 'DOC_1_677d333c452484.69139888', 'IDEES PELE MELE V2.docx', 'DOC_1_677d333c452484.69139888.docx', NULL, '2025-01-07 14:59:24', '2025-01-07 14:59:24', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(30, 'DOC_1_677e59d483a332.51125557', 'Origines_Degats_des_Eaux_16_30 V2.xlsx', 'DOC_1_677e59d483a332.51125557.xlsx', NULL, '2025-01-08 11:56:20', '2025-01-08 11:56:20', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(31, 'DOC_1_677e63ad269dd5.50336787', 'Origines_Degats_des_Eaux_suite 7.xlsx', 'DOC_1_677e63ad269dd5.50336787.xlsx', NULL, '2025-01-08 12:38:21', '2025-01-08 12:38:21', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(32, 'DOC_1_677eab074a6616.92097460', 'WhatsApp Image 2024-01-30 at 08.47.38.jpeg', 'DOC_1_677eab074a6616.92097460.jpeg', NULL, '2025-01-08 17:42:47', '2025-01-08 17:42:47', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(33, 'DOC_1_677eab6840bc70.43187556', 'CNI.pdf', 'DOC_1_677eab6840bc70.43187556.pdf', NULL, '2025-01-08 17:44:24', '2025-01-08 17:44:24', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(34, 'DOC_1_677f905a01d6e8.08618346', 'image4.jpeg', 'DOC_1_677f905a01d6e8.08618346.jpeg', NULL, '2025-01-09 10:01:14', '2025-01-09 10:01:14', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(35, 'DOC_1_677f920e7f8984.60453351', 'image 58.jpg', 'DOC_1_677f920e7f8984.60453351.jpg', NULL, '2025-01-09 10:08:30', '2025-01-09 10:08:30', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(36, 'DOC_1_677fd3e78f3622.34032679', 'image4.jpeg', 'DOC_1_677fd3e78f3622.34032679.jpeg', NULL, '2025-01-09 14:49:27', '2025-01-09 14:49:27', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(37, 'DOC_1_67864234dd7697.41358439', 'CNI.pdf', 'DOC_1_67864234dd7697.41358439.pdf', NULL, '2025-01-14 11:53:40', '2025-01-14 11:53:40', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(39, 'DOC_1_678642cd56e082.50547409', 'image 58.jpg', 'DOC_1_678642cd56e082.50547409.jpg', NULL, '2025-01-14 11:56:13', '2025-01-14 11:56:13', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(40, 'DOC_1_678643317d2033.35727610', 'CNI.pdf', 'DOC_1_678643317d2033.35727610.pdf', NULL, '2025-01-14 11:57:53', '2025-01-14 11:57:53', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(41, 'DOC_1_678643688a9730.63080052', 'WhatsApp Image 2024-12-31 at 12.53.44.jpeg', 'DOC_1_678643688a9730.63080052.jpeg', NULL, '2025-01-14 11:58:48', '2025-01-14 11:58:48', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(49, 'DOC_1_6787a9741a3d57.05013796', 'Cmds.docx', 'DOC_1_6787a9741a3d57.05013796.docx', NULL, '2025-01-15 13:26:28', '2025-01-15 13:26:28', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(50, 'DOC_1_6787aa9ae00620.46173455', 'Cmds.docx', 'DOC_1_6787aa9ae00620.46173455.docx', NULL, '2025-01-15 13:31:22', '2025-01-15 13:31:22', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(51, 'DOC_1_6787ab33f2f961.13120993', '001.jpg', 'DOC_1_6787ab33f2f961.13120993.jpg', NULL, '2025-01-15 13:33:55', '2025-01-15 13:33:55', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(52, 'DOC_1_6787ab5b67b5c5.91116778', 'LOGO.png', 'DOC_1_6787ab5b67b5c5.91116778.png', NULL, '2025-01-15 13:34:35', '2025-01-15 13:34:35', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(53, 'DOC_1_6787ac78d1a3d8.48732509', 'Capture d\'écran 2024-03-25 104326.png', 'DOC_1_6787ac78d1a3d8.48732509.png', NULL, '2025-01-15 13:39:20', '2025-01-15 13:39:20', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(54, 'DOC_1_6787ac823373f3.53492997', 'Capture d\'écran 2024-04-16 112210.png', 'DOC_1_6787ac823373f3.53492997.png', NULL, '2025-01-15 13:39:30', '2025-01-15 13:39:30', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(55, 'DOC_1_6787ac9c642622.45507067', 'PT20240112.pdf', 'DOC_1_6787ac9c642622.45507067.pdf', NULL, '2025-01-15 13:39:56', '2025-01-15 13:39:56', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(56, 'DOC_1_6787b9d6eb15a1.60666755', 'Capture d’écran keyyo 2024-07-04 175633.png', 'DOC_1_6787b9d6eb15a1.60666755.png', NULL, '2025-01-15 14:36:22', '2025-01-15 14:36:22', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(57, 'DOC_1_6787b9edab9287.45267667', 'Image.jpg', 'DOC_1_6787b9edab9287.45267667.jpg', NULL, '2025-01-15 14:36:45', '2025-01-15 14:36:45', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe'),
(58, 'DOC_1_6788fa3450ba04.54180416', 'Fichier Scan_15052024120441.pdf', 'DOC_1_6788fa3450ba04.54180416.pdf', NULL, '2025-01-16 13:23:16', '2025-01-16 13:23:16', 1, 1, NULL, NULL, NULL, NULL, NULL, '', 'EXTRA', 'BALTI Jawher', 0, 0, 'projet/annexe');

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_document_requis`
--

CREATE TABLE `wbcc_document_requis` (
  `idDocumentRequis` int(11) NOT NULL,
  `libelleDocumentRequis` varchar(255) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `createDate` varchar(25) DEFAULT current_timestamp(),
  `editDate` varchar(25) DEFAULT current_timestamp(),
  `idAuteur` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_document_requis_subvention`
--

CREATE TABLE `wbcc_document_requis_subvention` (
  `idDocumentRequisSubvention` int(11) NOT NULL,
  `idDocumentRequisF` int(11) DEFAULT NULL,
  `idSubventionF` int(11) DEFAULT NULL,
  `etatDocumentRequisSubvention` int(11) DEFAULT NULL COMMENT 'Obligatoire ou Facultatif',
  `editDate` varchar(25) DEFAULT current_timestamp(),
  `createDate` varchar(25) DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_historique`
--

CREATE TABLE `wbcc_historique` (
  `idHistorique` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `nomComplet` varchar(255) NOT NULL,
  `dateAction` timestamp NOT NULL DEFAULT current_timestamp(),
  `heureAction` datetime DEFAULT NULL,
  `idUtilisateurF` int(11) NOT NULL,
  `idOpportunityF` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_historique`
--

INSERT INTO `wbcc_historique` (`idHistorique`, `action`, `nomComplet`, `dateAction`, `heureAction`, `idUtilisateurF`, `idOpportunityF`) VALUES
(1, 'Connexion', 'Jawher BALTI', '2024-12-18 09:17:48', NULL, 1, NULL),
(2, 'Connexion', 'Jawher BALTI', '2024-12-23 09:56:46', NULL, 1, NULL),
(3, 'Connexion', 'Jawher BALTI', '2025-01-06 16:21:33', NULL, 1, NULL),
(4, 'Connexion', 'Jawher BALTI', '2025-01-07 10:45:44', NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_immeuble`
--

CREATE TABLE `wbcc_immeuble` (
  `idImmeuble` int(11) NOT NULL,
  `numeroImmeuble` varchar(255) DEFAULT NULL,
  `codeImmeuble` varchar(200) DEFAULT NULL,
  `typeImmeuble` varchar(100) DEFAULT 'HLM',
  `adresse` text DEFAULT NULL,
  `codePostal` varchar(25) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `nomDO` varchar(255) DEFAULT NULL,
  `idDO` int(11) DEFAULT NULL,
  `guidDO` varchar(100) DEFAULT NULL,
  `idProprietaire` int(11) DEFAULT NULL,
  `nomProprietaire` varchar(200) DEFAULT NULL,
  `guidProprietaire` varchar(100) DEFAULT NULL,
  `typeProprietaire` varchar(50) DEFAULT NULL,
  `createDate` varchar(50) DEFAULT current_timestamp(),
  `idUserF` int(11) DEFAULT NULL,
  `editDate` varchar(50) DEFAULT current_timestamp(),
  `photoImmeuble` varchar(255) DEFAULT NULL,
  `etatImmeuble` int(11) NOT NULL DEFAULT 1,
  `codeWBCC` varchar(255) DEFAULT NULL,
  `codeImmeubleDO` varchar(255) DEFAULT NULL,
  `nomImmeubleSyndic` varchar(255) DEFAULT NULL,
  `idSyndic` int(11) DEFAULT NULL,
  `guidSyndic` varchar(100) DEFAULT NULL,
  `adresse2` text DEFAULT NULL,
  `departement` varchar(255) DEFAULT NULL,
  `digicode1` varchar(100) DEFAULT NULL,
  `digicode2` varchar(100) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `pays` varchar(255) DEFAULT NULL,
  `nomInterphone` varchar(100) DEFAULT NULL,
  `codeDO` varchar(100) DEFAULT NULL,
  `refCourtier` varchar(100) DEFAULT NULL,
  `numPolice` varchar(100) DEFAULT NULL,
  `dateEffetContrat` varchar(100) DEFAULT NULL,
  `dateEcheanceContrat` varchar(50) DEFAULT NULL,
  `copieContrat` varchar(255) DEFAULT NULL,
  `codeFiche` varchar(100) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `nbreBatiment` varchar(100) DEFAULT NULL,
  `libelleBatiment` varchar(100) DEFAULT NULL,
  `nomPCS` varchar(255) DEFAULT NULL,
  `nomGardien` varchar(255) DEFAULT NULL,
  `nomCourtier` varchar(255) DEFAULT NULL,
  `nomCompagnieAssurance` varchar(255) DEFAULT NULL,
  `idChefSecteur` int(11) DEFAULT NULL,
  `nomChefSecteur` varchar(200) DEFAULT NULL,
  `idGardien` int(11) DEFAULT NULL,
  `idCourtier` int(11) DEFAULT NULL,
  `idCompagnieAssurance` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_immeuble`
--

INSERT INTO `wbcc_immeuble` (`idImmeuble`, `numeroImmeuble`, `codeImmeuble`, `typeImmeuble`, `adresse`, `codePostal`, `ville`, `nomDO`, `idDO`, `guidDO`, `idProprietaire`, `nomProprietaire`, `guidProprietaire`, `typeProprietaire`, `createDate`, `idUserF`, `editDate`, `photoImmeuble`, `etatImmeuble`, `codeWBCC`, `codeImmeubleDO`, `nomImmeubleSyndic`, `idSyndic`, `guidSyndic`, `adresse2`, `departement`, `digicode1`, `digicode2`, `region`, `pays`, `nomInterphone`, `codeDO`, `refCourtier`, `numPolice`, `dateEffetContrat`, `dateEcheanceContrat`, `copieContrat`, `codeFiche`, `commentaire`, `nbreBatiment`, `libelleBatiment`, `nomPCS`, `nomGardien`, `nomCourtier`, `nomCompagnieAssurance`, `idChefSecteur`, `nomChefSecteur`, `idGardien`, `idCourtier`, `idCompagnieAssurance`) VALUES
(1, '1', '111', 'HLM', 'addr1', '1111', 'ville1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'current_timestamp()', NULL, 'current_timestamp()', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2456, '2', '222', 'HLM', 'addr2', '2222', 'ville2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'current_timestamp()', NULL, 'current_timestamp()', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_immeuble_cb`
--

CREATE TABLE `wbcc_immeuble_cb` (
  `idImmeuble` int(11) NOT NULL,
  `numeroImmeuble` varchar(255) DEFAULT NULL,
  `codeImmeuble` varchar(200) DEFAULT NULL,
  `typeImmeuble` varchar(100) DEFAULT 'HLM',
  `adresse` text DEFAULT NULL,
  `codePostal` varchar(25) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `nomDO` varchar(255) DEFAULT NULL,
  `idDO` int(11) DEFAULT NULL,
  `guidDO` varchar(100) DEFAULT NULL,
  `idProprietaire` int(11) DEFAULT NULL,
  `nomProprietaire` varchar(200) DEFAULT NULL,
  `guidProprietaire` varchar(100) DEFAULT NULL,
  `typeProprietaire` varchar(50) DEFAULT NULL,
  `createDate` varchar(50) DEFAULT current_timestamp(),
  `idUserF` int(11) DEFAULT NULL,
  `editDate` varchar(50) DEFAULT current_timestamp(),
  `photoImmeuble` varchar(255) DEFAULT NULL,
  `etatImmeuble` int(11) NOT NULL DEFAULT 1,
  `codeWBCC` varchar(255) DEFAULT NULL,
  `codeImmeubleDO` varchar(255) DEFAULT NULL,
  `nomImmeubleSyndic` varchar(255) DEFAULT NULL,
  `idSyndic` int(11) DEFAULT NULL,
  `guidSyndic` varchar(100) DEFAULT NULL,
  `adresse2` text DEFAULT NULL,
  `departement` varchar(255) DEFAULT NULL,
  `digicode1` varchar(100) DEFAULT NULL,
  `digicode2` varchar(100) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `pays` varchar(255) DEFAULT NULL,
  `nomInterphone` varchar(100) DEFAULT NULL,
  `codeDO` varchar(100) DEFAULT NULL,
  `refCourtier` varchar(100) DEFAULT NULL,
  `numPolice` varchar(100) DEFAULT NULL,
  `dateEffetContrat` varchar(100) DEFAULT NULL,
  `dateEcheanceContrat` varchar(50) DEFAULT NULL,
  `copieContrat` varchar(255) DEFAULT NULL,
  `codeFiche` varchar(100) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `nbreBatiment` varchar(100) DEFAULT NULL,
  `libelleBatiment` varchar(100) DEFAULT NULL,
  `nomPCS` varchar(255) DEFAULT NULL,
  `nomGardien` varchar(255) DEFAULT NULL,
  `nomCourtier` varchar(255) DEFAULT NULL,
  `nomCompagnieAssurance` varchar(255) DEFAULT NULL,
  `idChefSecteur` int(11) DEFAULT NULL,
  `nomChefSecteur` varchar(200) DEFAULT NULL,
  `idGardien` int(11) DEFAULT NULL,
  `idCourtier` int(11) DEFAULT NULL,
  `idCompagnieAssurance` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_immeuble_cb`
--

INSERT INTO `wbcc_immeuble_cb` (`idImmeuble`, `numeroImmeuble`, `codeImmeuble`, `typeImmeuble`, `adresse`, `codePostal`, `ville`, `nomDO`, `idDO`, `guidDO`, `idProprietaire`, `nomProprietaire`, `guidProprietaire`, `typeProprietaire`, `createDate`, `idUserF`, `editDate`, `photoImmeuble`, `etatImmeuble`, `codeWBCC`, `codeImmeubleDO`, `nomImmeubleSyndic`, `idSyndic`, `guidSyndic`, `adresse2`, `departement`, `digicode1`, `digicode2`, `region`, `pays`, `nomInterphone`, `codeDO`, `refCourtier`, `numPolice`, `dateEffetContrat`, `dateEcheanceContrat`, `copieContrat`, `codeFiche`, `commentaire`, `nbreBatiment`, `libelleBatiment`, `nomPCS`, `nomGardien`, `nomCourtier`, `nomCompagnieAssurance`, `idChefSecteur`, `nomChefSecteur`, `idGardien`, `idCourtier`, `idCompagnieAssurance`) VALUES
(1, '1', '111', 'HLM', 'addr1', '1111', 'ville1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'current_timestamp()', NULL, 'current_timestamp()', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2456, '2', '222', 'HLM', 'addr2', '2222', 'ville2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'current_timestamp()', NULL, 'current_timestamp()', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_jour_ferie`
--

CREATE TABLE `wbcc_jour_ferie` (
  `idJourFerie` int(255) NOT NULL,
  `nomJourFerie` varchar(256) NOT NULL,
  `dateJourFerie` date NOT NULL DEFAULT current_timestamp(),
  `anneeJourFerie` int(11) NOT NULL,
  `idSiteF` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_module`
--

CREATE TABLE `wbcc_module` (
  `idModule` int(11) NOT NULL,
  `nomModule` varchar(255) DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `etatModule` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_module`
--

INSERT INTO `wbcc_module` (`idModule`, `nomModule`, `lieu`, `etatModule`) VALUES
(1, 'Gestion Opportunite', NULL, 1),
(2, 'Gestion Interne', NULL, 1),
(3, 'Expert', NULL, 1),
(4, 'Artisan', NULL, 1),
(5, 'Commercial', NULL, 1),
(6, 'Comptabilite', NULL, 1),
(7, 'Coproprietaire', NULL, 1),
(8, 'Occupant', NULL, 1),
(9, 'Particulier', NULL, 1),
(10, 'Espace DO', NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_opportunity_document`
--

CREATE TABLE `wbcc_opportunity_document` (
  `idOpportunityDocument` int(11) NOT NULL,
  `idOpportunityF` int(11) DEFAULT NULL,
  `idDocumentF` int(11) DEFAULT NULL,
  `numeroOpportunityF` varchar(255) DEFAULT NULL,
  `numeroDocumentF` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_opportunity_document`
--

INSERT INTO `wbcc_opportunity_document` (`idOpportunityDocument`, `idOpportunityF`, `idDocumentF`, `numeroOpportunityF`, `numeroDocumentF`) VALUES
(1, 4616, 1, NULL, NULL),
(2, 4657, 2, NULL, NULL),
(3, 4120, 3, NULL, NULL),
(4, 4120, 4, NULL, NULL),
(5, 4120, 5, NULL, NULL),
(6, 4120, 6, NULL, NULL),
(7, 4120, 7, NULL, NULL),
(8, 4713, 8, 'OPP202405244642', '27062024172855'),
(9, 4713, 9, 'OPP202405244642', '27062024173854'),
(10, 4713, 10, 'OPP202405244642', '27062024164054'),
(11, 4657, 11, NULL, NULL),
(12, 4657, 12, NULL, NULL),
(13, 4713, 13, NULL, NULL),
(14, 4717, 14, NULL, NULL),
(15, 4002, 15, NULL, NULL),
(16, 4713, 16, NULL, NULL),
(33, 4002, 32, NULL, NULL),
(34, 4003, 33, NULL, NULL),
(35, 4003, 34, NULL, NULL),
(36, 4003, 35, NULL, NULL),
(37, 4004, 36, NULL, NULL),
(38, 4004, 37, NULL, NULL),
(39, 4004, 38, NULL, NULL),
(40, 4004, 39, NULL, NULL),
(41, 4004, 40, NULL, NULL),
(42, 4002, 41, NULL, NULL),
(43, 4002, 42, NULL, NULL),
(44, 4002, 43, NULL, NULL),
(45, 4004, 44, NULL, NULL),
(46, 4005, 45, NULL, NULL),
(47, 4002, 46, NULL, NULL),
(48, 4002, 47, NULL, NULL),
(49, 4002, 48, NULL, NULL),
(50, 4002, 49, NULL, NULL),
(51, 4006, 50, NULL, NULL),
(52, 4087, 51, NULL, NULL),
(53, 4087, 52, NULL, NULL),
(54, 4087, 53, NULL, NULL),
(55, 4087, 54, NULL, NULL),
(56, 4087, 55, NULL, NULL),
(57, 4087, 56, NULL, NULL),
(58, 4087, 57, NULL, NULL),
(59, 4087, 58, NULL, NULL),
(60, 4087, 59, NULL, NULL),
(61, 4087, 60, NULL, NULL),
(62, NULL, 61, NULL, NULL),
(63, 2791, 62, NULL, NULL),
(64, 2791, 63, NULL, NULL),
(65, 2791, 64, NULL, NULL),
(66, 1380, 65, NULL, NULL),
(67, 1174, 66, NULL, NULL),
(68, 3986, 67, NULL, NULL),
(69, 4373, 68, NULL, NULL),
(70, 4373, 68, NULL, NULL),
(71, 4684, 70, NULL, NULL),
(72, 1225, 71, NULL, NULL),
(73, 2714, 72, NULL, NULL),
(74, 1225, 73, NULL, NULL),
(75, 1225, 73, NULL, NULL),
(76, 1225, 73, NULL, NULL),
(77, 3111, 76, NULL, NULL),
(78, 4093, 77, NULL, NULL),
(79, 3757, 78, NULL, NULL),
(80, 4387, 79, NULL, NULL),
(81, 3325, 80, NULL, NULL),
(82, 3435, 81, NULL, NULL),
(83, 2737, 82, NULL, NULL),
(84, 1225, 83, NULL, NULL),
(85, 1225, 83, NULL, NULL),
(86, 3076, 85, NULL, NULL),
(87, 1225, 83, NULL, NULL),
(88, 3196, 87, NULL, NULL),
(89, 2774, 88, NULL, NULL),
(90, 1225, 89, NULL, NULL),
(91, 3803, 90, NULL, NULL),
(92, 4534, 91, NULL, NULL),
(93, 3769, 92, NULL, NULL),
(94, 1225, 93, NULL, NULL),
(95, 1225, 93, NULL, NULL),
(96, 1225, 93, NULL, NULL),
(97, 1225, 96, NULL, NULL),
(98, 3098, 97, NULL, NULL),
(99, 1225, 96, NULL, NULL),
(100, 2969, 99, NULL, NULL),
(101, 1225, 96, NULL, NULL),
(102, 4129, 101, NULL, NULL),
(103, 3260, 102, NULL, NULL),
(104, 1225, 103, NULL, NULL),
(105, 3877, 104, NULL, NULL),
(106, 3802, 105, NULL, NULL),
(107, 2691, 106, NULL, NULL),
(108, 4340, 107, NULL, NULL),
(109, 4120, 108, NULL, NULL),
(110, 956, 109, NULL, NULL),
(111, 1141, 110, NULL, NULL),
(112, 1225, 111, NULL, NULL),
(113, 4120, 112, NULL, NULL),
(114, 4044, 113, NULL, NULL),
(115, 3985, 114, NULL, NULL),
(116, 3590, 115, NULL, NULL),
(117, 4120, 116, NULL, NULL),
(118, 2681, 117, NULL, NULL),
(119, 1444, 118, NULL, NULL),
(120, 463, 119, NULL, NULL),
(121, 4120, 120, NULL, NULL),
(122, 1125, 121, NULL, NULL),
(123, 4120, 120, NULL, NULL),
(124, 2285, 123, NULL, NULL),
(125, 2566, 124, NULL, NULL),
(126, 1225, 125, NULL, NULL),
(127, 1225, 125, NULL, NULL),
(128, 4453, 127, NULL, NULL),
(129, 1225, 128, NULL, NULL),
(130, 1347, 129, NULL, NULL),
(131, 2409, 130, NULL, NULL),
(132, 3618, 131, NULL, NULL),
(133, 4350, 132, NULL, NULL),
(134, 2641, 133, NULL, NULL),
(135, 2100, 134, NULL, NULL),
(136, 1125, 135, NULL, NULL),
(137, 4120, 136, NULL, NULL),
(138, 2830, 137, NULL, NULL),
(139, 1225, 138, NULL, NULL),
(140, 2551, 139, NULL, NULL),
(141, 463, 140, NULL, NULL),
(142, 1272, 141, NULL, NULL),
(143, 463, 140, NULL, NULL),
(144, 4120, 143, NULL, NULL),
(145, 1125, 144, NULL, NULL),
(146, 2578, 145, NULL, NULL),
(147, 463, 146, NULL, NULL),
(148, 463, 146, NULL, NULL),
(149, 4335, 148, NULL, NULL),
(150, 1495, 149, NULL, NULL),
(151, 4218, 150, NULL, NULL),
(152, 2755, 151, NULL, NULL),
(153, 4471, 152, NULL, NULL),
(154, 1225, 153, NULL, NULL),
(155, 3310, 154, NULL, NULL),
(156, 2544, 155, NULL, NULL),
(157, 1297, 156, NULL, NULL),
(158, 1225, 153, NULL, NULL),
(159, 1174, 158, NULL, NULL),
(160, 4272, 159, NULL, NULL),
(161, 1225, 160, NULL, NULL),
(162, 1297, 161, NULL, NULL),
(163, 1225, 162, NULL, NULL),
(164, 1225, 162, NULL, NULL),
(165, 3783, 164, NULL, NULL),
(166, 1238, 165, NULL, NULL),
(167, 1174, 166, NULL, NULL),
(168, 463, 167, NULL, NULL),
(169, 1125, 168, NULL, NULL),
(170, 463, 167, NULL, NULL),
(171, 1293, 170, NULL, NULL),
(172, 463, 171, NULL, NULL),
(173, 1951, 172, NULL, NULL),
(174, 1444, 173, NULL, NULL),
(175, 3988, 174, NULL, NULL),
(176, 1225, 175, NULL, NULL),
(177, 309, 176, NULL, NULL),
(178, 4471, 177, NULL, NULL),
(179, 1225, 178, NULL, NULL),
(180, 3783, 179, NULL, NULL),
(181, 1273, 180, NULL, NULL),
(182, 2131, 181, NULL, NULL),
(183, 3991, 182, NULL, NULL),
(184, 2548, 183, NULL, NULL),
(185, 1225, 184, NULL, NULL),
(186, 1225, 185, NULL, NULL),
(187, 786, 186, NULL, NULL),
(188, 1225, 185, NULL, NULL),
(189, 1197, 188, NULL, NULL),
(190, 3985, 189, NULL, NULL),
(191, 1225, 190, NULL, NULL),
(192, 1141, 191, NULL, NULL),
(193, 4120, 192, NULL, NULL),
(194, 2859, 193, NULL, NULL),
(195, 1225, 194, NULL, NULL),
(196, 1858, 195, NULL, NULL),
(197, 2118, 196, NULL, NULL),
(198, 721, 197, NULL, NULL),
(199, 57, 198, NULL, NULL),
(200, 1562, 199, NULL, NULL),
(201, 1225, 200, NULL, NULL),
(202, 1498, 201, NULL, NULL),
(203, 3783, 202, NULL, NULL),
(204, 1556, 203, NULL, NULL),
(205, 2099, 204, NULL, NULL),
(206, 2819, 205, NULL, NULL),
(207, 1239, 206, NULL, NULL),
(208, 1225, 207, NULL, NULL),
(209, 1429, 208, NULL, NULL),
(210, 1225, 209, NULL, NULL),
(211, 2194, 210, NULL, NULL),
(212, 2038, 211, NULL, NULL),
(213, 471, 212, NULL, NULL),
(214, 2610, 213, NULL, NULL),
(215, 1535, 214, NULL, NULL),
(216, 1225, 215, NULL, NULL),
(217, 1297, 216, NULL, NULL),
(218, 883, 217, NULL, NULL),
(219, 1125, 218, NULL, NULL),
(220, 463, 219, NULL, NULL),
(221, 1505, 220, NULL, NULL),
(222, 1484, 221, NULL, NULL),
(223, 1141, 222, NULL, NULL),
(224, 1197, 223, NULL, NULL),
(225, 1225, 224, NULL, NULL),
(226, 57, 225, NULL, NULL),
(227, 1511, 226, NULL, NULL),
(228, 883, 227, NULL, NULL),
(229, 2015, 228, NULL, NULL),
(230, 1179, 229, NULL, NULL),
(231, 1364, 230, NULL, NULL),
(232, 54, 231, NULL, NULL),
(233, 1125, 232, NULL, NULL),
(234, 2247, 233, NULL, NULL),
(235, 2247, 234, NULL, NULL),
(236, 2075, 235, NULL, NULL),
(237, 2002, 236, NULL, NULL),
(238, 1225, 237, NULL, NULL),
(239, 1297, 238, NULL, NULL),
(240, 1521, 239, NULL, NULL),
(241, 1505, 240, NULL, NULL),
(242, 1275, 241, NULL, NULL),
(243, 2194, 242, NULL, NULL),
(244, 1125, 243, NULL, NULL),
(245, 1125, 243, NULL, NULL),
(246, 2246, 245, NULL, NULL),
(247, 1378, 246, NULL, NULL),
(248, 2019, 247, NULL, NULL),
(249, 1953, 248, NULL, NULL),
(250, 1858, 249, NULL, NULL),
(251, 1225, 250, NULL, NULL),
(252, 1347, 251, NULL, NULL),
(253, 1179, 252, NULL, NULL),
(254, 1218, 253, NULL, NULL),
(255, 1225, 254, NULL, NULL),
(256, 436, 255, NULL, NULL),
(257, 1387, 256, NULL, NULL),
(258, 1225, 257, NULL, NULL),
(259, 1436, 258, NULL, NULL),
(260, 1225, 259, NULL, NULL),
(261, 1225, 259, NULL, NULL),
(262, 1225, 259, NULL, NULL),
(263, 1225, 259, NULL, NULL),
(264, 1266, 263, NULL, NULL),
(265, 1854, 264, NULL, NULL),
(266, 1299, 265, NULL, NULL),
(267, 1185, 266, NULL, NULL),
(268, 1268, 267, NULL, NULL),
(269, 1503, 268, NULL, NULL),
(270, 1378, 269, NULL, NULL),
(271, 1425, 270, NULL, NULL),
(272, 1855, 271, NULL, NULL),
(273, 1349, 272, NULL, NULL),
(274, 1266, 273, NULL, NULL),
(275, 1855, 271, NULL, NULL),
(276, 9, 275, NULL, NULL),
(277, 1125, 276, NULL, NULL),
(278, 1225, 277, NULL, NULL),
(279, 1225, 277, NULL, NULL),
(280, 400, 279, NULL, NULL),
(281, 1266, 280, NULL, NULL),
(282, 309, 281, NULL, NULL),
(283, 1542, 282, NULL, NULL),
(284, 1125, 283, NULL, NULL),
(285, 1225, 284, NULL, NULL),
(286, 400, 285, NULL, NULL),
(287, 850, 286, NULL, NULL),
(288, 396, 287, NULL, NULL),
(289, 1364, 288, NULL, NULL),
(290, 1429, 289, NULL, NULL),
(291, 1296, 290, NULL, NULL),
(292, 1500, 291, NULL, NULL),
(293, 400, 292, NULL, NULL),
(294, 1255, 293, NULL, NULL),
(295, 1225, 294, NULL, NULL),
(296, 1125, 295, NULL, NULL),
(297, 1225, 296, NULL, NULL),
(298, 1225, 296, NULL, NULL),
(299, 1529, 298, NULL, NULL),
(300, 1132, 299, NULL, NULL),
(301, 1529, 300, NULL, NULL),
(302, 1225, 301, NULL, NULL),
(303, 54, 302, NULL, NULL),
(304, 1197, 303, NULL, NULL),
(305, 3414, 304, NULL, NULL),
(306, 1506, 305, NULL, NULL),
(307, 1501, 306, NULL, NULL),
(308, 1498, 307, NULL, NULL),
(309, 1264, 308, NULL, NULL),
(310, 1225, 309, NULL, NULL),
(311, 1463, 310, NULL, NULL),
(312, 1266, 311, NULL, NULL),
(313, 940, 312, NULL, NULL),
(314, 940, 312, NULL, NULL),
(315, 940, 312, NULL, NULL),
(316, 568, 315, NULL, NULL),
(317, 568, 315, NULL, NULL),
(318, 1490, 317, NULL, NULL),
(319, 1125, 318, NULL, NULL),
(320, 1225, 319, NULL, NULL),
(321, 1225, 319, NULL, NULL),
(322, 1125, 318, NULL, NULL),
(323, 1141, 322, NULL, NULL),
(324, 1429, 323, NULL, NULL),
(325, 57, 324, NULL, NULL),
(326, 57, 324, NULL, NULL),
(327, 1125, 326, NULL, NULL),
(328, 1225, 327, NULL, NULL),
(329, 1324, 328, NULL, NULL),
(330, 1324, 328, NULL, NULL),
(331, 1387, 330, NULL, NULL),
(332, 956, 331, NULL, NULL),
(333, 1459, 332, NULL, NULL),
(334, 1225, 333, NULL, NULL),
(335, 1225, 333, NULL, NULL),
(336, 1373, 335, NULL, NULL),
(337, 1353, 336, NULL, NULL),
(338, 1347, 337, NULL, NULL),
(339, 1371, 338, NULL, NULL),
(340, 956, 339, NULL, NULL),
(341, 1063, 340, NULL, NULL),
(342, 1360, 341, NULL, NULL),
(343, 305, 342, NULL, NULL),
(344, 1444, 343, NULL, NULL),
(345, 1299, 344, NULL, NULL),
(346, 1348, 345, NULL, NULL),
(347, 1125, 346, NULL, NULL),
(348, 1417, 347, NULL, NULL),
(349, 932, 348, NULL, NULL),
(350, 656, 349, NULL, NULL),
(351, 1417, 347, NULL, NULL),
(352, 1417, 351, NULL, NULL),
(353, 728, 352, NULL, NULL),
(354, 1370, 353, NULL, NULL),
(355, 1349, 354, NULL, NULL),
(356, 914, 355, NULL, NULL),
(357, 1108, 356, NULL, NULL),
(358, 931, 357, NULL, NULL),
(359, 1141, 358, NULL, NULL),
(360, 1141, 358, NULL, NULL),
(361, 1125, 360, NULL, NULL),
(362, 1225, 361, NULL, NULL),
(363, 1437, 362, NULL, NULL),
(364, 436, 363, NULL, NULL),
(365, 1225, 364, NULL, NULL),
(366, 1348, 365, NULL, NULL),
(367, 1386, 366, NULL, NULL),
(368, 786, 367, NULL, NULL),
(369, 721, 368, NULL, NULL),
(370, 1225, 369, NULL, NULL),
(371, 1225, 369, NULL, NULL),
(372, 9, 371, NULL, NULL),
(373, 1275, 372, NULL, NULL),
(374, 1125, 373, NULL, NULL),
(375, 1268, 374, NULL, NULL),
(376, 786, 375, NULL, NULL),
(377, 877, 376, NULL, NULL),
(378, 1372, 377, NULL, NULL),
(379, 1330, 378, NULL, NULL),
(380, 1125, 379, NULL, NULL),
(381, 1297, 380, NULL, NULL),
(382, 1299, 381, NULL, NULL),
(383, 1324, 382, NULL, NULL),
(384, 1297, 380, NULL, NULL),
(385, 1324, 384, NULL, NULL),
(386, 1324, 384, NULL, NULL),
(387, 258, 386, NULL, NULL),
(388, 1268, 387, NULL, NULL),
(389, 1369, 388, NULL, NULL),
(390, 862, 389, NULL, NULL),
(391, 1292, 390, NULL, NULL),
(392, 1331, 391, NULL, NULL),
(393, 940, 392, NULL, NULL),
(394, 940, 393, NULL, NULL),
(395, 940, 393, NULL, NULL),
(396, 1264, 395, NULL, NULL),
(397, 1125, 396, NULL, NULL),
(398, 940, 397, NULL, NULL),
(399, 769, 398, NULL, NULL),
(400, 940, 399, NULL, NULL),
(401, 1292, 400, NULL, NULL),
(402, 9, 401, NULL, NULL),
(403, 1225, 402, NULL, NULL),
(404, 1197, 403, NULL, NULL),
(405, 977, 404, NULL, NULL),
(406, 1272, 405, NULL, NULL),
(407, 1125, 406, NULL, NULL),
(408, 1125, 406, NULL, NULL),
(409, 1141, 408, NULL, NULL),
(410, 805, 409, NULL, NULL),
(411, 669, 410, NULL, NULL),
(412, 1087, 411, NULL, NULL),
(413, 1125, 412, NULL, NULL),
(414, 729, 413, NULL, NULL),
(415, 1125, 412, NULL, NULL),
(416, 1232, 415, NULL, NULL),
(417, 940, 416, NULL, NULL),
(418, 1125, 417, NULL, NULL),
(419, 1234, 418, NULL, NULL),
(420, 1238, 419, NULL, NULL),
(421, 1238, 419, NULL, NULL),
(422, 656, 421, NULL, NULL),
(423, 656, 422, NULL, NULL),
(424, 1225, 423, NULL, NULL),
(425, 1282, 424, NULL, NULL),
(426, 1076, 425, NULL, NULL),
(427, 258, 426, NULL, NULL),
(428, 11, 427, NULL, NULL),
(429, 871, 428, NULL, NULL),
(430, 46, 429, NULL, NULL),
(431, 931, 430, NULL, NULL),
(432, 615, 431, NULL, NULL),
(433, 907, 432, NULL, NULL),
(434, 871, 433, NULL, NULL),
(435, 356, 434, NULL, NULL),
(436, 1161, 435, NULL, NULL),
(437, 769, 436, NULL, NULL),
(438, 175, 437, NULL, NULL),
(439, 1225, 438, NULL, NULL),
(440, 1225, 438, NULL, NULL),
(441, 94, 440, NULL, NULL),
(442, 939, 441, NULL, NULL),
(443, 1225, 442, NULL, NULL),
(444, 1225, 443, NULL, NULL),
(445, 703, 444, NULL, NULL),
(446, 1005, 445, NULL, NULL),
(447, 1164, 446, NULL, NULL),
(448, 893, 447, NULL, NULL),
(449, 1145, 448, NULL, NULL),
(450, 1225, 449, NULL, NULL),
(451, 1125, 450, NULL, NULL),
(452, 1125, 450, NULL, NULL),
(453, 1225, 452, NULL, NULL),
(454, 1186, 453, NULL, NULL),
(455, 978, 454, NULL, NULL),
(456, 1141, 455, NULL, NULL),
(457, 139, 456, NULL, NULL),
(458, 57, 457, NULL, NULL),
(459, 1225, 458, NULL, NULL),
(460, 1125, 459, NULL, NULL),
(461, 1125, 459, NULL, NULL),
(462, 1225, 461, NULL, NULL),
(463, 1125, 462, NULL, NULL),
(464, 1225, 461, NULL, NULL),
(465, 570, 464, NULL, NULL),
(466, 1201, 465, NULL, NULL),
(467, 862, 466, NULL, NULL),
(468, 647, 467, NULL, NULL),
(469, 322, 468, NULL, NULL),
(470, 850, 469, NULL, NULL),
(471, 1225, 470, NULL, NULL),
(472, 1141, 471, NULL, NULL),
(473, 1141, 471, NULL, NULL),
(474, 1225, 473, NULL, NULL),
(475, 1141, 474, NULL, NULL),
(476, 862, 475, NULL, NULL),
(477, 912, 476, NULL, NULL),
(478, 1006, 477, NULL, NULL),
(479, 330, 478, NULL, NULL),
(480, 1006, 477, NULL, NULL),
(481, 9, 480, NULL, NULL),
(482, 901, 481, NULL, NULL),
(483, 666, 482, NULL, NULL),
(484, 929, 483, NULL, NULL),
(485, 9, 484, NULL, NULL),
(486, 977, 485, NULL, NULL),
(487, 786, 486, NULL, NULL),
(488, 423, 487, NULL, NULL),
(489, 1010, 488, NULL, NULL),
(490, 1033, 489, NULL, NULL),
(491, 870, 490, NULL, NULL),
(492, 15, 491, NULL, NULL),
(493, 414, 492, NULL, NULL),
(494, 9, 493, NULL, NULL),
(495, 9, 493, NULL, NULL),
(496, 1125, 495, NULL, NULL),
(497, 15, 496, NULL, NULL),
(498, 894, 497, NULL, NULL),
(499, 231, 498, NULL, NULL),
(500, 330, 499, NULL, NULL),
(501, 238, 500, NULL, NULL),
(502, 9, 501, NULL, NULL),
(503, 558, 502, NULL, NULL),
(504, 330, 503, NULL, NULL),
(505, 1138, 504, NULL, NULL),
(506, 423, 505, NULL, NULL),
(507, 704, 506, NULL, NULL),
(508, 4002, 507, NULL, NULL),
(509, 4002, 508, NULL, NULL),
(510, 4002, 509, 'fdf83d11-7020-4688-bfbf-b909e5a379fc', '2409202412392350'),
(511, 3998, 510, NULL, NULL),
(512, 3998, 511, NULL, NULL),
(513, 3998, 512, NULL, NULL),
(514, 3998, 513, NULL, NULL),
(515, 3998, 514, NULL, NULL),
(516, 3998, 515, NULL, NULL),
(517, 3998, 516, NULL, NULL),
(518, 3998, 517, NULL, NULL),
(519, 3998, 518, NULL, NULL),
(520, 3998, 519, NULL, NULL),
(521, 3998, 520, NULL, NULL),
(522, 3998, 521, NULL, NULL),
(523, 3998, 522, NULL, NULL),
(524, 3998, 523, NULL, NULL),
(525, 3998, 524, NULL, NULL),
(526, 3998, 525, NULL, NULL),
(527, 3998, 526, NULL, NULL),
(528, 4695, 527, NULL, NULL),
(529, 4719, 528, NULL, NULL),
(530, 4719, 529, NULL, NULL),
(531, 3998, 530, NULL, NULL),
(532, 4695, 531, NULL, NULL),
(533, 4695, 532, NULL, NULL),
(534, 4695, 533, NULL, NULL),
(535, 4695, 534, NULL, NULL),
(536, 4706, 535, NULL, NULL),
(537, 4706, 536, NULL, NULL),
(538, 4706, 537, NULL, NULL),
(539, 4706, 538, NULL, NULL),
(540, 4706, 539, NULL, NULL),
(541, 4706, 540, NULL, NULL),
(542, 4706, 541, NULL, NULL),
(543, 4706, 542, NULL, NULL),
(544, 4706, 543, NULL, NULL),
(545, 4706, 544, NULL, NULL),
(546, 4706, 545, NULL, NULL),
(547, 4706, 546, NULL, NULL),
(548, 4706, 547, NULL, NULL),
(549, 4706, 548, NULL, NULL),
(550, 4706, 549, NULL, NULL),
(551, 4706, 550, NULL, NULL),
(552, 4706, 551, NULL, NULL),
(553, 4706, 552, NULL, NULL),
(554, 4706, 553, NULL, NULL),
(555, 4706, 554, NULL, NULL),
(556, 4706, 555, NULL, NULL),
(557, 4706, 556, NULL, NULL),
(558, 4706, 557, NULL, NULL),
(559, 4706, 558, NULL, NULL),
(560, 4706, 559, NULL, NULL),
(561, 4706, 560, NULL, NULL),
(562, 4706, 561, NULL, NULL),
(563, 4706, 562, NULL, NULL),
(564, 4706, 563, NULL, NULL),
(565, 3998, 564, NULL, NULL),
(566, 3998, 565, NULL, NULL),
(567, 4706, 566, NULL, NULL),
(568, 3998, 567, NULL, NULL),
(569, 4657, 568, NULL, NULL),
(571, 4719, 570, NULL, NULL),
(572, 4719, 571, NULL, NULL),
(573, 4719, 572, NULL, NULL),
(574, 4719, 573, NULL, NULL),
(575, 4719, 574, NULL, NULL),
(576, 4719, 575, NULL, NULL),
(577, 4003, 576, NULL, NULL),
(578, 4003, 577, NULL, NULL),
(579, 4003, 578, NULL, NULL),
(580, 4003, 579, NULL, NULL),
(581, 4003, 580, NULL, NULL),
(583, 4713, 582, NULL, NULL),
(584, 4713, 583, NULL, NULL),
(585, 4713, 584, NULL, NULL),
(587, 4718, 596, NULL, NULL),
(588, 4713, 597, NULL, NULL),
(589, 4713, 598, NULL, NULL),
(590, 4713, 599, NULL, NULL),
(591, 4713, 600, NULL, NULL),
(592, 4713, 601, NULL, NULL),
(593, 4713, 602, NULL, NULL),
(594, 4713, 603, NULL, NULL),
(595, 4713, 604, NULL, NULL),
(596, 4712, 605, NULL, NULL),
(597, 4712, 606, NULL, NULL),
(598, 4713, 607, NULL, NULL),
(599, 4713, 608, NULL, NULL),
(600, 4713, 609, NULL, NULL),
(601, 4713, 610, NULL, NULL),
(602, 4713, 611, NULL, NULL),
(603, 4713, 612, NULL, NULL),
(604, 4712, 613, NULL, NULL),
(605, 4710, 614, NULL, NULL),
(606, 4111, 630, 'cf39712d-5084-4161-b004-58285c8244b7', '2101202514530250'),
(607, 1174, 631, NULL, NULL),
(608, 1174, 632, NULL, NULL),
(609, 1174, 633, NULL, NULL),
(610, 1174, 634, NULL, NULL),
(611, 1174, 635, NULL, NULL),
(612, 1174, 636, NULL, NULL),
(613, 1174, 637, NULL, NULL),
(614, 1174, 638, NULL, NULL),
(615, 1174, 639, NULL, NULL),
(616, 1174, 640, NULL, NULL),
(617, 4713, 642, NULL, NULL),
(618, 4713, 643, NULL, NULL),
(619, 4695, 644, NULL, NULL),
(620, 4695, 645, NULL, NULL),
(621, 4695, 646, NULL, NULL),
(622, 4695, 647, NULL, NULL),
(623, 4041, 653, NULL, NULL),
(624, 4111, 654, NULL, NULL),
(625, 4111, 655, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_opportunity_immeuble`
--

CREATE TABLE `wbcc_opportunity_immeuble` (
  `idOpportunityImmeuble` int(11) NOT NULL,
  `idOpportunityF` int(11) DEFAULT NULL,
  `idImmeubleF` int(11) DEFAULT NULL,
  `numeroOpportunityF` varchar(255) DEFAULT NULL,
  `numeroImmeubleF` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_opportunity_immeuble`
--

INSERT INTO `wbcc_opportunity_immeuble` (`idOpportunityImmeuble`, `idOpportunityF`, `idImmeubleF`, `numeroOpportunityF`, `numeroImmeubleF`) VALUES
(1, 4657, 1985, 'OP2024-05-16-0765', 'IMM2705202409182931153584'),
(4, 4002, 1977, 'fdf83d11-7020-4688-bfbf-b909e5a379fc', 'IMM2305202413012265313514'),
(5, 4706, 1985, 'OP2024-05-23-0809', 'IMM2705202409182931153584'),
(7, 4695, 1972, NULL, NULL),
(13, 4557, 1985, 'OP2024-04-30-0673', 'IMM2705202409182931153584'),
(16, 4719, 1984, 'OP2024-05-27-0821', 'IMM2405202416454884603572'),
(17, 4003, 1984, 'd88e06a8-1726-4afb-8714-c36407ff75f8', 'IMM2405202416454884603572'),
(19, 4713, 1988, NULL, NULL),
(20, 4718, 1989, 'OP2024-05-27-0820', '081124110553'),
(21, 1174, 146, NULL, NULL),
(22, 4111, 112, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_parametres`
--

CREATE TABLE `wbcc_parametres` (
  `id` int(11) NOT NULL,
  `numeroDemandeCloture` int(11) NOT NULL DEFAULT 0,
  `numeroDemandeValidation` int(11) NOT NULL DEFAULT 0,
  `numeroOpProvisoire` int(11) DEFAULT 0,
  `numeroBordereau` int(11) NOT NULL,
  `numeroOP` int(11) NOT NULL DEFAULT 0,
  `numeroOPamo` int(11) DEFAULT NULL,
  `numeroBordereauCheque` int(100) DEFAULT NULL,
  `numeroJournal` int(11) DEFAULT NULL,
  `numeroFacture` int(11) DEFAULT NULL,
  `numeroFactureProvisoire` int(11) DEFAULT NULL,
  `numeroClient` int(11) DEFAULT NULL,
  `numeroLotOP` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_parametres`
--

INSERT INTO `wbcc_parametres` (`id`, `numeroDemandeCloture`, `numeroDemandeValidation`, `numeroOpProvisoire`, `numeroBordereau`, `numeroOP`, `numeroOPamo`, `numeroBordereauCheque`, `numeroJournal`, `numeroFacture`, `numeroFactureProvisoire`, `numeroClient`, `numeroLotOP`) VALUES
(1, 5, 0, 1267, 168, 1618, 591, 67, 1, 1, 1247, 2, 5002);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_piece`
--

CREATE TABLE `wbcc_piece` (
  `idPiece` int(11) NOT NULL,
  `libellePiece` varchar(255) NOT NULL,
  `etatPiece` int(11) NOT NULL DEFAULT 1,
  `nouveauNom` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_piece`
--

INSERT INTO `wbcc_piece` (`idPiece`, `libellePiece`, `etatPiece`, `nouveauNom`) VALUES
(1, 'Salle de bain', 1, NULL),
(2, 'Cuisine', 1, NULL),
(3, 'WC', 1, NULL),
(4, 'Chambre', 1, NULL),
(5, 'Entrée', 1, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_piece_equipement`
--

CREATE TABLE `wbcc_piece_equipement` (
  `idPieceEquipement` int(11) NOT NULL,
  `idPieceF` int(11) DEFAULT NULL,
  `idEquipementF` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_piece_equipement`
--

INSERT INTO `wbcc_piece_equipement` (`idPieceEquipement`, `idPieceF`, `idEquipementF`) VALUES
(8, 1, 1),
(9, 3, 1),
(13, 2, 6);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_projet`
--

CREATE TABLE `wbcc_projet` (
  `idProjet` int(11) NOT NULL,
  `numeroProjet` varchar(255) NOT NULL,
  `nomProjet` varchar(255) DEFAULT NULL,
  `descriptionProjet` text DEFAULT NULL,
  `createDate` varchar(25) DEFAULT NULL,
  `idImmeubleCB` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_projet`
--

INSERT INTO `wbcc_projet` (`idProjet`, `numeroProjet`, `nomProjet`, `descriptionProjet`, `createDate`, `idImmeubleCB`) VALUES
(29, 'PROJ150120251247411', 'PROROTYPE', 'PROROTYPE', '2024-12-24 13:14:11', 1),
(30, 'PROJ241220241324521', 'teste2', 'ceci est un teste ', '2024-12-24 13:24:52', 1),
(49, 'PROJ020120250938331', 'BONNE ET HEUREUSE ANNEE', 'UNE ANNEE DE PROSPERITE DE SANTE DE REUSSITE DE JOIE ET PLEIN DE SUCCES', '2025-01-02 09:38:33', 2456),
(56, 'PROJ020120251201001', 'titreSommaire', 'titreSommaire', '2025-01-02 12:01:00', 2456),
(62, 'PROJ020120251251131', 'REAL', 'REAL', '2025-01-02 12:51:13', 2456),
(63, 'PROJ020120251253151', 'MADRID', 'MADRID', '2025-01-02 12:53:15', 2456),
(64, 'PROJ020120251408491', 'fall', 'fall', '2025-01-02 14:08:49', 2456),
(65, 'PROJ020120251409241', 'GUEYE', 'GUEYE', '2025-01-02 14:09:24', 2456),
(66, 'PROJ150120251330291', 'Projet', '8eme Projet', '2025-01-15 13:29:53', 1);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_recherche_fuite`
--

CREATE TABLE `wbcc_recherche_fuite` (
  `idRF` int(11) NOT NULL,
  `numeroRF` varchar(255) DEFAULT NULL,
  `numeroOP` varchar(255) DEFAULT NULL,
  `dateRF` varchar(50) DEFAULT NULL,
  `createDate` varchar(50) DEFAULT current_timestamp(),
  `editDate` varchar(50) DEFAULT current_timestamp(),
  `idOpportunityF` int(11) DEFAULT NULL,
  `numeroLotOP` varchar(10) DEFAULT NULL,
  `idAuteurRF` int(11) DEFAULT NULL,
  `auteurRF` varchar(255) DEFAULT NULL,
  `etatRF` int(11) DEFAULT 0,
  `origineFuite` varchar(255) DEFAULT NULL,
  `origineFuiteSinistre` varchar(255) DEFAULT NULL,
  `confirmationProvenance` varchar(255) DEFAULT NULL,
  `reparationCause` varchar(25) DEFAULT NULL,
  `reparateurCause` varchar(255) DEFAULT NULL,
  `siAccessible` varchar(10) DEFAULT NULL,
  `etape` varchar(255) DEFAULT NULL,
  `typeRF` varchar(100) DEFAULT 'INTERNE',
  `auteurRFExterne` varchar(255) DEFAULT NULL,
  `dateReparationFuite` varchar(25) DEFAULT NULL,
  `descriptionTravaux` text DEFAULT NULL,
  `idArtisanF` int(11) DEFAULT NULL,
  `idSocieteArtisanF` int(11) DEFAULT NULL,
  `idVoisinF` int(11) DEFAULT NULL,
  `chezVoisin` int(11) DEFAULT 0,
  `siFactureReparation` varchar(10) DEFAULT NULL,
  `photosReparation` text DEFAULT NULL,
  `signatureDO` varchar(255) DEFAULT NULL,
  `signatureVoisin` varchar(255) DEFAULT NULL,
  `idAuteurGenererConstatDDE` int(11) DEFAULT NULL,
  `dateGenererConstatDDE` varchar(25) DEFAULT NULL,
  `documentConstatDDE` varchar(255) DEFAULT NULL,
  `pieceFuyarde` text DEFAULT NULL,
  `equipementFuyard` text DEFAULT NULL,
  `origineFuiteEquipement` text DEFAULT NULL,
  `siDegatVoisin` varchar(5) DEFAULT NULL,
  `siConfierSinistre` varchar(5) DEFAULT '0',
  `siFaireRF` varchar(5) DEFAULT NULL,
  `isVictimSigned` int(11) DEFAULT 0,
  `isResponsableSigned` int(11) DEFAULT 0,
  `isDocumentCompleted` int(11) DEFAULT 0,
  `refusSignature` int(11) DEFAULT 0,
  `commentaireRefus` text DEFAULT NULL,
  `siJustificatif` int(11) DEFAULT 0,
  `siSignatureJustificatif` int(11) DEFAULT 0,
  `documentJustificatif` text DEFAULT NULL,
  `dateJustificatif` varchar(25) DEFAULT NULL,
  `signatureJustificatif` varchar(255) DEFAULT NULL,
  `demandeJustificatifEnvoye` int(11) NOT NULL DEFAULT 0,
  `dateDemandeJustificatifEnvoye` varchar(25) DEFAULT NULL,
  `refusSignatureJustificatif` int(11) NOT NULL DEFAULT 0,
  `demandeSignatureEnvoye` int(11) DEFAULT 0,
  `dateDemandeSignatureEnvoye` varchar(25) DEFAULT NULL,
  `rechercheFuiteEffectueeInterne` int(11) DEFAULT 0,
  `dateSignatureResponsable` varchar(25) DEFAULT NULL,
  `dateSignatureVictime` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_recherche_fuite`
--

INSERT INTO `wbcc_recherche_fuite` (`idRF`, `numeroRF`, `numeroOP`, `dateRF`, `createDate`, `editDate`, `idOpportunityF`, `numeroLotOP`, `idAuteurRF`, `auteurRF`, `etatRF`, `origineFuite`, `origineFuiteSinistre`, `confirmationProvenance`, `reparationCause`, `reparateurCause`, `siAccessible`, `etape`, `typeRF`, `auteurRFExterne`, `dateReparationFuite`, `descriptionTravaux`, `idArtisanF`, `idSocieteArtisanF`, `idVoisinF`, `chezVoisin`, `siFactureReparation`, `photosReparation`, `signatureDO`, `signatureVoisin`, `idAuteurGenererConstatDDE`, `dateGenererConstatDDE`, `documentConstatDDE`, `pieceFuyarde`, `equipementFuyard`, `origineFuiteEquipement`, `siDegatVoisin`, `siConfierSinistre`, `siFaireRF`, `isVictimSigned`, `isResponsableSigned`, `isDocumentCompleted`, `refusSignature`, `commentaireRefus`, `siJustificatif`, `siSignatureJustificatif`, `documentJustificatif`, `dateJustificatif`, `signatureJustificatif`, `demandeJustificatifEnvoye`, `dateDemandeJustificatifEnvoye`, `refusSignatureJustificatif`, `demandeSignatureEnvoye`, `dateDemandeSignatureEnvoye`, `rechercheFuiteEffectueeInterne`, `dateSignatureResponsable`, `dateSignatureVictime`) VALUES
(30, 'CON020820241943275', 'OP2024-07-01-1002', '2024-08-02 19:45:48', '2024-08-02 19:43:27', '2024-08-02 19:45:48', 4939, NULL, 5, 'Ndacke GUEYE', 1, 'Chez Vous', 'Chez le voisin', NULL, 'Non', 'Un artisan de votre bailleur', NULL, 'En attente de Réparation de Fuite', 'EXTERNE', 'Un artisan de votre bailleur', NULL, NULL, NULL, NULL, 9558, 1, NULL, NULL, 'SignDDEdo4939.png', 'SignDDEvoisin4939.png', 5, '2024-08-02 19:45:50', 'OP2024-07-01-1002_CONSTAT_DDE_Complet_02082024074550.pdf', NULL, NULL, NULL, NULL, '0', NULL, 0, 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(31, 'CON030820241348326', 'OP2024-07-04-1058', '2024-08-03 13:49:52', '2024-08-03 13:48:32', '2024-08-03 13:49:52', 4995, NULL, 6, 'Ben PADONOU', 0, '', 'Chez le voisin', NULL, NULL, NULL, NULL, 'En Attente de Recherche de Fuite', 'INTERNE', NULL, NULL, NULL, NULL, NULL, 9560, 1, NULL, NULL, 'SignDDEdo4995.png', '', 6, '2024-08-03 13:49:54', 'OP2024-07-04-1058_CONSTAT_DDE_Complet_03082024014954.pdf', NULL, NULL, NULL, NULL, '0', NULL, 0, 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(32, 'CON050820241000276', 'OP2024-07-09-1114', '2024-08-05 10:06:32', '2024-08-05 10:00:27', '2024-08-05 10:06:32', 5051, NULL, 6, 'Ben PADONOU', 1, 'Chez Vous', 'Chez le voisin', NULL, 'Oui', 'Vous-même', NULL, 'Justificatif de Réparation de Fuite reçu', 'EXTERNE', NULL, '2024-06-05T07:00:00.000Z', 'Jdhdhd', NULL, NULL, 9561, 1, NULL, NULL, 'SignDDEdo5051.png', 'SignDDEvoisin5051.png', 6, '2024-08-05 10:06:34', 'OP2024-07-09-1114_CONSTAT_DDE_Complet_05082024100634.pdf', 'Douche', 'Tuyaux d\'évacuation', 'Obstruction ou rupture', NULL, '0', NULL, 0, 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(33, 'CON050820241012176', 'OP2024-07-04-1061', '2024-08-05 10:23:46', '2024-08-05 10:12:17', '2024-08-05 10:17:11', 4998, NULL, 6, 'Ben PADONOU', 1, 'Chez Vous', 'Chez le voisin', NULL, 'Non', '', 'Oui', 'Recherche de Fuite Effectuée', 'INTERNE', NULL, NULL, NULL, NULL, NULL, 9562, 1, NULL, NULL, 'SignDDEdo4998.png', '', 6, '2024-08-05 10:23:48', 'OP2024-07-04-1061_CONSTAT_DDE_Signed_05082024102348.pdf', NULL, NULL, NULL, NULL, '0', NULL, 0, 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(34, 'CON05082024140548653', 'OP2024-07-02-1033', '2024-08-05 15:52:21', '2024-08-05 14:05:48', '2024-08-05 15:52:21', 4970, NULL, 653, 'Expert Test', 1, 'Chez Vous', 'Chez le voisin', NULL, 'Oui', 'Vous-même', NULL, 'Justificatif de Réparation de Fuite reçu', 'EXTERNE', NULL, '2024-08-05T11:45:48.524Z', 'Hju', NULL, NULL, 9565, 1, NULL, NULL, 'SignDDEdo4970.png', '', 653, '2024-08-05 15:52:22', 'OP2024-07-02-1033_CONSTAT_DDE_Complet_05082024035222.pdf', 'Balcon', 'Fenêtres', 'Mauvaise étanchéité', '1', '1', NULL, 0, 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(35, 'CON05082024173203653', 'OP2024-07-01-0995', '2024-08-05 17:33:13', '2024-08-05 17:32:03', '2024-08-05 17:33:13', 4932, NULL, 653, 'Expert Test', 1, 'Chez Vous', 'Chez le voisin', NULL, 'Oui', 'Un artisan de votre bailleur', NULL, 'Justificatif de Réparation de Fuite reçu', 'EXTERNE', NULL, '2024-08-05T15:31:41.878Z', 'Vhji', NULL, NULL, 9567, 1, NULL, NULL, 'SignDDEdo4932.png', 'SignDDEvoisin4932.png', 653, '2024-08-05 17:33:14', 'OP2024-07-01-0995_CONSTAT_DDE_Complet_05082024053314.pdf', 'Balcon', 'Système de chauffage central (collectif)', 'Robinet radiateur ', '1', '1', NULL, 0, 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(36, 'CON050820241810505', 'OP2024-07-01-0990', '2024-08-05 18:14:45', '2024-08-05 18:10:50', '2024-08-05 18:14:45', 4927, NULL, 5, 'Ndacke GUEYE', 1, 'Chez Vous', 'Chez le voisin', NULL, 'Oui', 'Un artisan que vous avez trouvé vous-même', NULL, 'Justificatif de Réparation de Fuite reçu', 'EXTERNE', NULL, '2024-08-05T16:13:51.965Z', 'Yuytu', NULL, NULL, 9568, 1, NULL, NULL, 'SignDDEdo4927.png', 'SignDDEvoisin4927.png', 5, '2024-08-05 18:14:46', 'OP2024-07-01-0990_CONSTAT_DDE_Complet_05082024061446.pdf', 'Balcon', 'Murs porteurs', 'Infiltration par des fissures', '1', '1', NULL, 0, 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(37, 'CON050820241818075', 'OP2024-07-09-1116', NULL, '2024-08-05 18:18:07', '2024-08-05 19:08:55', 5053, NULL, 5, 'Ndacke GUEYE', 0, 'Chez Vous', 'Chez le voisin', NULL, 'Non', '', 'Oui', 'En attente de recherche de fuite', 'INTERNE', NULL, NULL, NULL, NULL, NULL, 9569, 1, NULL, NULL, 'SignDDEdo5053.png', 'SignDDEvoisin5053.png', 5, '2024-08-05 19:08:57', 'OP2024-07-09-1116_CONSTAT_DDE_Complet_05082024070857.pdf', NULL, NULL, NULL, '1', '1', NULL, 0, 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(40, 'CON05082024185636653', 'OP2024-07-08-1095', '2024-08-05 18:56:36', '2024-08-05 18:56:36', '2024-08-05 18:56:36', 5032, NULL, 653, 'Expert Test', 0, '', 'Chez le voisin', NULL, NULL, NULL, NULL, 'En Attente de Recherche de Fuite', 'INTERNE', NULL, NULL, NULL, NULL, NULL, 9572, 1, NULL, NULL, 'SignDDEdo5032.png', '', 653, '2024-08-05 18:56:37', 'OP2024-07-08-1095_CONSTAT_DDE_Signed_05082024065637.pdf', NULL, NULL, NULL, '0', NULL, NULL, 0, 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(41, 'CON06082024111536581', 'OP2024-08-02-1192', '2024-08-06 11:15:36', '2024-08-06 11:15:36', '2024-08-06 11:15:36', 5130, NULL, 581, 'Eleazar DJOSSINOU', 0, '', 'Chez le voisin', NULL, NULL, NULL, NULL, 'En Attente de Recherche de Fuite', 'INTERNE', NULL, NULL, NULL, NULL, NULL, 9575, 1, NULL, NULL, 'SignDDEdo5130.png', '', 581, '2024-08-06 11:15:37', 'OP2024-08-02-1192_CONSTAT_DDE_Signed_06082024111537.pdf', NULL, NULL, NULL, '0', NULL, NULL, 0, 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(44, 'CON06082024173010653', 'OP2024-01-19-0136', '2024-08-06 17:30:10', '2024-08-06 17:30:10', '2024-08-06 17:30:10', 3997, '5001', 653, 'Expert Test', 0, '', 'Chez le voisin', NULL, NULL, NULL, NULL, 'En Attente de Recherche de Fuite', 'INTERNE', NULL, NULL, NULL, NULL, NULL, 9582, 1, NULL, NULL, 'SignDDEdo3997.png', '', 653, '2024-08-06 17:30:11', 'OP2024-01-19-0136_CONSTAT_DDE_Signed_06082024053011.pdf', NULL, NULL, NULL, '0', NULL, NULL, 0, 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(45, 'CON06082024175217605', 'OP2024-08-01-1183', NULL, '2024-08-06 17:52:17', '2024-08-06 17:52:17', 5121, NULL, 605, 'Jean-Marc DJOSSINOU', 0, 'Partie Commune Externe', 'Partie Commune Externe', NULL, 'Non', '', NULL, 'En Attente de Recherche de Fuite', 'EXTERNE', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '', '', 605, '2024-08-06 17:52:18', 'OP2024-08-01-1183_CONSTAT_DDE_06082024055218.pdf', NULL, NULL, NULL, '0', NULL, NULL, 0, 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(46, 'CON06082024232051581', 'OP2024-08-05-1197', '2024-08-06 23:20:51', '2024-08-06 23:20:51', '2024-08-06 23:20:51', 5135, NULL, 581, 'Eleazar DJOSSINOU', 0, 'Je ne sais pas', 'Je ne sais pas', NULL, NULL, NULL, NULL, 'En attente de progralmmation de RF', 'EXTERNE', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'SignDDEdo5135.png', '', 581, '2024-08-06 23:20:52', 'OP2024-08-05-1197_CONSTAT_DDE_Signed_06082024112052.pdf', NULL, NULL, NULL, '0', NULL, NULL, 0, 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(47, 'CON07082024110903653', 'OP2024-01-19-0137', '2024-08-19 15:05:47', '2024-08-07 11:09:03', '2024-08-19 15:05:47', 4002, '3889', NULL, '', 0, 'Chez Vous', 'Chez le voisin', NULL, '1', 'Un artisan de WBCC ASSISTANCE (Réseau REPA)', NULL, 'Justificatif de Réparation de Fuite reçu', 'EXTERNE', '', '2024-07-31', 'dgd', NULL, NULL, 8869, 1, NULL, NULL, NULL, NULL, 5, '2024-08-19 15:35:24', 'OP2024-01-19-0137_CONSTAT_DDE_complet19082024153524.pdf', '', '', '', '0', '0', NULL, 1, 1, 1, 0, NULL, 1, 1, 'OP2024-01-19-0137_ATTESTATION_REPARATION_signed20082024125054.pdf', '2024-08-20 12:50:54', NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(49, 'CON070820241212135', 'OP2024-07-03-1035', '2024-08-07 14:05:00', '2024-08-07 12:12:13', '2024-08-07 14:05:00', 4972, '5002', 653, 'Expert Test', 1, 'Chez Vous', 'Chez le voisin', NULL, 'Oui', 'Un artisan que vous avez trouvé vous-même', NULL, 'Justificatif de Réparation de Fuite reçu', 'EXTERNE', NULL, '2024-08-07T10:10:41.994Z', 'Fggf', 9588, NULL, 9587, 1, NULL, NULL, 'SignDDEdo4972.png', 'SignDDEvoisin4972.png', 653, '2024-08-07 14:05:01', 'OP2024-07-03-1035_CONSTAT_DDE_Complet_07082024020501.pdf', 'Buanderie', 'Fenêtres', 'Mauvaise étanchéité', '1', '1', NULL, 0, 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(50, 'CON13082024153857', 'OP2024-01-22-0142', '2024-08-13 18:58:48', '2024-08-13 15:38:57', '2024-08-13 18:58:48', 4003, '3890', NULL, '', 1, 'Partie Commune Interne', 'Partie Commune Interne', NULL, 'Oui', 'Autre reparateur', NULL, 'en attente', 'EXTERNE', '', '2024-08-07', 'EDRFSDTGSD', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 5, '2024-08-13 19:06:05', 'OP2024-01-22-0142_CONSTAT_DDE.pdf', '', '', '', '0', '0', NULL, 0, 1, 1, 0, NULL, 1, 1, 'OP2024-01-22-0142_JUSTIFICATIF_REPARATION.pdf', NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(51, 'CON16082024184024', 'OP2024-01-22-0146', '2024-08-19 14:33:34', '2024-08-16 18:40:24', '2024-08-19 14:33:34', 4004, '3891', NULL, '', 1, '', '', NULL, 'Oui', '', NULL, 'en attente', 'EXTERNE', '', '2024-08-07', 'rtuyt', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 5, '2024-08-16 19:11:27', 'OP2024-01-22-0146_CONSTAT_DDE.pdf', '', '', '', '0', '0', NULL, 0, 0, 0, 0, NULL, 1, 1, 'OP2024-01-22-0146_JUSTIFICATIF_REPARATION.pdf', NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(52, 'CON200820241940225', 'OP2024-01-22-0147', '2024-08-21 03:40:36', '2024-08-20 19:40:22', '2024-08-21 03:40:36', 4006, '3893', 5, 'Ndacke GUEYE', 0, 'Chez Vous', 'Chez le voisin', NULL, 'Non', '', '0', 'En Attente de Recherche de Fuite', 'EXTERNE', '', NULL, '', NULL, NULL, 8857, 1, NULL, NULL, NULL, NULL, 5, '2024-08-21 03:40:41', 'OP2024-01-22-0147_CONSTAT_DDE_21082024034041.pdf', '', '', '', '0', '0', NULL, 0, 0, 1, 0, NULL, 0, 0, '', NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(53, 'CON210820240434015', 'OP2024-01-19-0138', '2024-08-21 04:34:04', '2024-08-21 04:34:01', '2024-08-21 04:34:04', 4001, '3888', 5, 'Ndacke GUEYE', 0, 'Chez Vous', 'Chez Vous', NULL, '', '', '0', 'en attente', 'EXTERNE', '', NULL, '', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '0', '0', NULL, 0, 0, 0, 0, NULL, 0, 0, '', NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(54, 'CON250920241822425', 'OP2024-01-19-0135', '2024-12-17 17:28:03', '2024-09-25 18:22:42', '2024-12-17 17:28:03', 3998, '3885', 5, 'Ndacke GUEYE', 0, 'Partie Commune Interne', 'Partie Commune Interne', NULL, 'Non', '', '0', 'Justificatif de Réparation de Fuite reçu', 'EXTERNE', '', '2024-09-21', 'cxfd', NULL, NULL, NULL, 0, NULL, NULL, '', '', 629, '2024-12-17 17:42:06', 'OP2024-01-19-0135_CONSTAT_DDE_signed17122024174206.pdf', '', '', '', '0', '0', NULL, 0, 1, 1, 0, 'sdfdshhsd', 1, 0, 'OP2024-01-19-0135_ATTESTATION_RF_18122024123353.pdf', '2024-12-18 12:33:53', NULL, 0, NULL, 0, 1, '2024-12-17 17:32:22', 0, '2024-12-17 17:42:06', NULL),
(55, 'CON111220241342165', 'OP2024-05-23-0809', '2024-12-11 18:22:10', '2024-12-11 13:42:16', '2024-12-11 18:22:10', 4706, NULL, 5, 'Ndacke GUEYE', 1, 'Partie Commune Interne', 'Partie Commune Interne', NULL, 'Oui', '', '0', 'Justificatif de Réparation de Fuite reçu', 'EXTERNE', '', '2024-12-06', 'dfkgkdfgbd', NULL, NULL, NULL, 0, NULL, NULL, '', '', 629, '2024-12-11 18:27:27', 'OP2024-05-23-0809_CONSTAT_DDE_complet11122024182727.pdf', '', '', '', '0', '0', NULL, 1, 1, 1, 1, 'HJGHKGH', 1, 0, 'OP2024-05-23-0809_ATTESTATION_RF_17122024054555.pdf', '2024-12-17 17:45:55', NULL, 1, '2024-12-11 17:53:32', 0, 1, '2024-12-11 18:25:06', 0, '2024-12-11 18:27:27', '2024-12-11 18:24:05'),
(56, 'CON191220241348565', 'OP2024-05-16-0765', '2024-12-19 13:55:42', '2024-12-19 13:48:56', '2024-12-19 13:55:42', 4657, NULL, 5, 'Ndacke GUEYE', 0, 'Je ne sais pas', 'Je ne sais pas', NULL, 'Non', '', '0', 'en attente', 'EXTERNE', '', NULL, '', NULL, NULL, NULL, 0, NULL, NULL, '', '', 5, '2024-12-19 13:49:42', 'OP2024-05-16-0765_CONSTAT_DDE_19122024014942.pdf', '', '', '', '0', '0', NULL, 0, 0, 1, 0, NULL, 0, 0, '', NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, NULL),
(57, 'CON02012025013735', 'TEST-OP2024-05-24-0816', '2025-01-03 14:46:07', '2025-01-02 01:37:35', '2025-01-03 14:46:07', 4713, '232', 0, '', 0, '', '', NULL, 'Non', '', '', 'en attente', 'EXTERNE', '', NULL, '', NULL, NULL, 8904, 0, NULL, NULL, '', '', 5, '2025-01-02 01:51:51', 'TEST-OP2024-05-24-0816_CONSTAT_DDE_signed02012025015151.pdf', '', '', '', '', '', NULL, 1, 0, 1, 0, NULL, 0, 0, '', NULL, NULL, 0, NULL, 0, 0, NULL, 0, NULL, '2025-01-02 01:51:51');

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_releve_technique`
--

CREATE TABLE `wbcc_releve_technique` (
  `idRT` int(11) NOT NULL,
  `numeroRT` varchar(255) DEFAULT NULL,
  `numeroOP` varchar(255) DEFAULT NULL,
  `dateRT` varchar(50) DEFAULT NULL,
  `nature` varchar(255) DEFAULT NULL,
  `date` varchar(50) DEFAULT NULL,
  `anneeSurvenance` varchar(50) DEFAULT NULL,
  `dateConstat` varchar(50) DEFAULT NULL,
  `heure` varchar(50) DEFAULT NULL,
  `commentaireDateInconnue` text DEFAULT NULL,
  `numeroBatiment` varchar(50) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `codePostal` varchar(25) DEFAULT NULL,
  `ageConstruction` varchar(30) DEFAULT NULL,
  `quitterLieu` varchar(50) DEFAULT NULL,
  `codePorte` varchar(50) DEFAULT NULL,
  `numeroAppartement` varchar(50) DEFAULT NULL,
  `numeroImmeuble` varchar(50) DEFAULT NULL,
  `cause` text DEFAULT NULL,
  `precisionDegat` text DEFAULT NULL,
  `autrePrecisionDegat` text DEFAULT NULL,
  `lieuDegat` varchar(255) DEFAULT NULL,
  `precisionComplementaire` text DEFAULT NULL,
  `reparerCauseDegat` varchar(255) DEFAULT NULL,
  `rechercheFuite` varchar(255) DEFAULT NULL,
  `chercheurFuite` varchar(255) DEFAULT NULL,
  `commentaireSinistre` text DEFAULT NULL,
  `vehiculeCause` varchar(255) DEFAULT NULL,
  `intervention` varchar(255) DEFAULT NULL,
  `interventionPompier` varchar(255) DEFAULT NULL,
  `depotPlainte` varchar(255) DEFAULT NULL,
  `temoin` varchar(255) DEFAULT NULL,
  `personneOrigineSinistre` varchar(255) DEFAULT NULL,
  `incendieVolontaire` varchar(255) DEFAULT NULL,
  `degatPompier` text DEFAULT NULL,
  `dateInterventionPompier` varchar(255) DEFAULT NULL,
  `niveauEtage` varchar(25) DEFAULT NULL,
  `dommageCorporel` text DEFAULT NULL,
  `dommageMateriel` text DEFAULT NULL,
  `dommageImmateriel` text DEFAULT NULL,
  `dommageMaterielAutrePersonne` text DEFAULT NULL,
  `libelleDommageMateriel` text DEFAULT NULL,
  `nomAutrePersonne` varchar(255) DEFAULT NULL,
  `nombrePiece` varchar(25) DEFAULT NULL,
  `libellePieces` text DEFAULT NULL,
  `nombreBiens` varchar(25) DEFAULT NULL,
  `typeCanalisation` text DEFAULT NULL,
  `accessibilite` text DEFAULT NULL,
  `localisation` varchar(255) DEFAULT NULL,
  `partieConcernee` varchar(255) DEFAULT NULL,
  `natureDommage` text DEFAULT NULL,
  `dateExpertiseSinistre` varchar(50) DEFAULT NULL,
  `codeExpertSinistre` varchar(255) DEFAULT NULL,
  `commentaireReleve` longtext DEFAULT NULL,
  `documentComplement` text DEFAULT NULL,
  `commentaireDocument` longtext DEFAULT NULL,
  `createDate` varchar(50) DEFAULT NULL,
  `editDate` varchar(50) DEFAULT NULL,
  `idOpportunityF` int(11) DEFAULT NULL,
  `idRVF` int(11) DEFAULT NULL,
  `idRVGuid` varchar(50) DEFAULT NULL,
  `idAppContactF` int(11) DEFAULT NULL,
  `introduction` longtext DEFAULT NULL,
  `conclusion` longtext DEFAULT NULL,
  `idImmeubleF` int(11) DEFAULT NULL,
  `etatRT` int(11) DEFAULT 0,
  `listDommages` text DEFAULT NULL,
  `reparateurDegat` varchar(255) DEFAULT NULL,
  `equipementMVC` varchar(50) DEFAULT NULL,
  `fonctionnementMVC` varchar(50) DEFAULT NULL,
  `dataAppMoisissure` varchar(50) DEFAULT NULL,
  `appMoisissureHiver` varchar(50) DEFAULT NULL,
  `congePreavis` varchar(50) DEFAULT NULL,
  `dateCongePreavis` varchar(50) DEFAULT NULL,
  `documentFRT` text DEFAULT NULL,
  `nomCompletRV` varchar(255) DEFAULT NULL,
  `signatureRV` varchar(255) DEFAULT NULL,
  `auteurRV` varchar(255) DEFAULT NULL,
  `idAuteurRV` int(11) DEFAULT NULL,
  `contexte` text DEFAULT NULL,
  `deroulementSeance` text DEFAULT NULL,
  `detailRT` longtext DEFAULT NULL,
  `auteurCompteRenduRT` varchar(255) DEFAULT NULL,
  `etatCompteRendu` int(11) DEFAULT NULL,
  `idAuteurControleCompteRendu` int(11) DEFAULT NULL,
  `dateControleCompteRendu` varchar(25) DEFAULT NULL,
  `commentaireControleCompteRendu` text DEFAULT NULL,
  `auteurDerniereModification` varchar(255) DEFAULT NULL,
  `repDeclarerSinistre` varchar(25) DEFAULT NULL,
  `repDateDeclarationSinistre` varchar(25) DEFAULT NULL,
  `dateDeclarationSinistre` varchar(50) DEFAULT NULL,
  `nomResponsable` varchar(150) DEFAULT NULL,
  `idResponsableContactF` int(11) DEFAULT NULL,
  `descriptionSinistre` text DEFAULT NULL,
  `origineSinistre` text DEFAULT NULL,
  `interventionInitiales` text DEFAULT NULL,
  `remarques` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_releve_technique`
--

INSERT INTO `wbcc_releve_technique` (`idRT`, `numeroRT`, `numeroOP`, `dateRT`, `nature`, `date`, `anneeSurvenance`, `dateConstat`, `heure`, `commentaireDateInconnue`, `numeroBatiment`, `adresse`, `ville`, `codePostal`, `ageConstruction`, `quitterLieu`, `codePorte`, `numeroAppartement`, `numeroImmeuble`, `cause`, `precisionDegat`, `autrePrecisionDegat`, `lieuDegat`, `precisionComplementaire`, `reparerCauseDegat`, `rechercheFuite`, `chercheurFuite`, `commentaireSinistre`, `vehiculeCause`, `intervention`, `interventionPompier`, `depotPlainte`, `temoin`, `personneOrigineSinistre`, `incendieVolontaire`, `degatPompier`, `dateInterventionPompier`, `niveauEtage`, `dommageCorporel`, `dommageMateriel`, `dommageImmateriel`, `dommageMaterielAutrePersonne`, `libelleDommageMateriel`, `nomAutrePersonne`, `nombrePiece`, `libellePieces`, `nombreBiens`, `typeCanalisation`, `accessibilite`, `localisation`, `partieConcernee`, `natureDommage`, `dateExpertiseSinistre`, `codeExpertSinistre`, `commentaireReleve`, `documentComplement`, `commentaireDocument`, `createDate`, `editDate`, `idOpportunityF`, `idRVF`, `idRVGuid`, `idAppContactF`, `introduction`, `conclusion`, `idImmeubleF`, `etatRT`, `listDommages`, `reparateurDegat`, `equipementMVC`, `fonctionnementMVC`, `dataAppMoisissure`, `appMoisissureHiver`, `congePreavis`, `dateCongePreavis`, `documentFRT`, `nomCompletRV`, `signatureRV`, `auteurRV`, `idAuteurRV`, `contexte`, `deroulementSeance`, `detailRT`, `auteurCompteRenduRT`, `etatCompteRendu`, `idAuteurControleCompteRendu`, `dateControleCompteRendu`, `commentaireControleCompteRendu`, `auteurDerniereModification`, `repDeclarerSinistre`, `repDateDeclarationSinistre`, `dateDeclarationSinistre`, `nomResponsable`, `idResponsableContactF`, `descriptionSinistre`, `origineSinistre`, `interventionInitiales`, `remarques`) VALUES
(1, 'RT_270620241237244120', 'OP2024-01-19-0137', NULL, 'Dégâts des eaux', '2024-08-30', '', '', '11:24:02', '', 'D', '24 Rue René Boin 93240 Stains', 'Stains', '93240', 'plus10', 'non', NULL, NULL, NULL, 'Fuite,Fuite,Débordement,Infiltration,E', '}}}}', NULL, '', 'ghjgkghj', 'Non', '', '', 'Cuisine-1,', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'D', '', '', NULL, '', 'Des Pièces', NULL, '1', 'Cuisine-1', '0', NULL, '', '', '', NULL, '24-09-2024 11:24', NULL, '', NULL, NULL, '27-06-2024 12:37', '24-09-2024 11:24', 4002, 8, '', NULL, NULL, NULL, NULL, 0, 'Moisissures;', 'Un artisan de WBCC ASSISTANCE (Réseau REPA)', 'non', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, '2024-12-30 02:21:52', 'jhfyh,', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'RT_120720241218554657', 'OP2024-05-16-0765', NULL, 'Dégâts des eaux', '2024-07-10 12:19', '', '', '12:20:05', '', '', '9 Rue Gérard Philipe 94400 Vitry-sur-Seine', 'Vitry-sur-Seine', '94400', 'plus10', 'non', NULL, NULL, NULL, 'Fuite', 'Evacuation', NULL, 'Je ne sais pas', 'wx wx xw', 'Non', 'Non', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', NULL, '', 'Des Pièces', NULL, '1', 'Cuisine-1', '0', NULL, '', '', '', NULL, '12-07-2024 12:20', NULL, '', NULL, NULL, '12-07-2024 12:18', '12-07-2024 12:20', 4657, NULL, '', NULL, NULL, NULL, NULL, 0, 'Cloques;', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'RT_160720240227544713', 'TEST-OP2024-05-24-0816', NULL, 'Dégâts des eaux', '2025-01-03', '', '', '11:40:56', '', 'n', '218 Rue de Bellevue, 92700 Colombes, France. 92700 Colombes', 'Colombes', '92700', 'plus10', 'non', NULL, NULL, NULL, 'Fuite;Débordement;Infiltration;Engorgement', '', NULL, '', 'hrt', 'Non', 'Non', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '', '', NULL, '', 'Des Pieces', NULL, '1', 'Cuisine-1', '0', NULL, '', '', '', NULL, '02-01-2025 11:40', NULL, '', NULL, NULL, '16-07-2024 14:27', '2025-02-05 16:18:10', 4713, 13, '', NULL, 'Nous avons été mandatés par M Test OPH Test aux fins de gérer en son nom et pour son compte le sinistre intervenu à l’adresse citée ci-dessus.\n\nNous vous prions de trouver ci-dessous notre Compte Rendu relatif au Rendez-Vous Relevés Techniques qui s’est tenu le  à  H sur les lieux du sinistre.\n        ', NULL, NULL, 0, 'Cloques;', '', 'non', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, 'M Test OPH Test est locataire de l’appartement situé au 218 Rue de Bellevue, 92700 Colombes, France. 92700 à Colombes au 1 étage à la porte s.\n\nL’appartement subit des Fuite;Débordement;Infiltration;Engorgement depuis le 03/01/2025. Il s’agit des dégâts des eaux qui venaient à priori du ... ', 'Le , le rendez-vous relevés techniques s’est tenu au 218 Rue de Bellevue, 92700 Colombes, France. 92700 à Colombes.\n\nSur place, l’expert de WBCC Assistance (SOS Sinistre) a constaté que... \n\nLes dégâts des eaux se situent :\n     - Cuisine-1 : \n        ', NULL, 'Ndacke GUEYE', 0, 5, '2025-02-05 16:08:17', 'jkjkjk', 'Ndacke GUEYE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'RT_160720240237544717', 'OP2024-05-24-0819', NULL, 'Dégâts des eaux', '2024-07-12 ', '', '', '02:38:25', '', '', '', '', '', 'plus10', 'non', NULL, NULL, NULL, 'Fuite;', 'Canalisation d\'alimentation fuyarde : Oui;}}}}', NULL, 'chez le voisin', 'dfvdf', 'Non', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', NULL, '', 'Des Pièces', NULL, '1', 'Chambre-1', '0', NULL, '', '', '', NULL, '16-07-2024 14:38', NULL, '', NULL, NULL, '16-07-2024 14:37', '16-07-2024 14:38', 4717, NULL, '', NULL, NULL, NULL, NULL, 0, 'Cloques;', '', 'non', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'RT_130820240327134003', 'OP2024-01-22-0142', NULL, 'Dégâts des eaux', '2024-12-12', '', '', '03:28:05', '', '', '23 SQUARE DU NORD 95500 GONESSE', 'GONESSE', '95500', 'plus10', 'non', NULL, NULL, NULL, 'Infiltration;Engorgement;ttttt', 'Infiltrations par les murs extérieurs ou façade}L\'engorgement provient d\'une canalisation privative', NULL, 'Partie Commune Interne', 'dgs', 'Oui', 'Oui', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', NULL, '', '', NULL, '0', '', '0', NULL, '', '', '', NULL, '13-08-2024 15:28', NULL, '', NULL, NULL, '13-08-2024 15:27', '13-08-2024 15:28', 4003, 12, '', NULL, NULL, NULL, NULL, 0, 'Cloques;Tâches d’humidité;', 'Autre reparateur', 'Je ne sais pas', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'RT_270820240221244087', 'OP2024-01-29-0222', NULL, 'Dégâts des eaux', '2024-08-13', '', '', '02:24:08', '', '', 'ARGONAUTES, 15, ALL DE LA TOISON D\'OR 94000 CRETEIL', 'CRETEIL', '94000', 'plus10', 'non', NULL, NULL, NULL, 'Fuite;', 'Canalisation d\'alimentation fuyarde : Oui;}}}}', NULL, 'chez moi', 'test comment et circonstances', 'Oui', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', NULL, '', 'Des Pieces', NULL, '0', '', '0', NULL, '', '', '', NULL, '27-08-2024 02:24', NULL, '', NULL, NULL, '27-08-2024 02:21', '2024-08-27 09:00:40', 4087, NULL, '', NULL, 'Ce rapport d\'expertise a été rédigé suite à la demande de Madame Djamila Ouazir, résident(e) à ARGONAUTES, 15, ALL DE LA TOISON D\'OR, ayant subi un sinistre le 13/08/2024. Notre intervention s\'inscrit dans le cadre de notre mission de gestion de sinistres pour compte de tiers, afin d\'évaluer les dommages subis et de proposer des solutions de réparation.\nConformément aux principes du mandat définis dans le Code civil français, notamment aux articles 1984 et 1985, notre cabinet, SOS SINISTRE by WBCC ASSISTANCE, a été dûment mandaté par votre assuré(e) Madame Djamila Ouazir aux fins de gérer en son nom et pour son compte son sinistre. Madame Djamila Ouazir nous a signé une délégation de gestion et une cession de créance.\nNotre entreprise, WBCC Assistance, sous la marque SOS Sinistre, s\'engage à fournir une expertise complète et impartiale, en collaboration avec toutes les parties concernées. Nous accompagnons nos clients depuis la déclaration de leur sinistre jusqu\'à la remise en état des lieux, en passant par toutes les étapes intermédiaires, y compris l\'expertise et le suivi des travaux. Ce rapport présente les résultats de notre expertise réalisée le , expert interne de notre société, et détaille les observations, évaluations et recommandations faites sur site.\nL\'objectif de ce rapport est de fournir à Madame Djamila Ouazir et à sa compagnie d\'assurance une évaluation précise et professionnelle des dommages subis, afin de faciliter la prise en charge et la résolution du sinistre dans les meilleures conditions possibles. \n.\n    ', NULL, NULL, 0, 'Moisissures;Cloques;', 'Un artisan de WBCC ASSISTANCE (Réseau REPA)', 'oui', 'Oui', '', '', '', '', NULL, NULL, NULL, NULL, NULL, 'Madame Djamila Ouazir est locataire de l’appartement situé au ARGONAUTES, 15, ALL DE LA TOISON D\'OR 94000 à CRETEIL au 4 étage à la porte d.\n\nL’appartement subit des Fuite; depuis le 13/08/2024. Il s’agit des dégâts des eaux qui venaient à priori du ... ', 'Le , le rendez-vous relevés techniques s’est tenu au ARGONAUTES, 15, ALL DE LA TOISON D\'OR 94000 à CRETEIL.\n\nSur place, l’expert de WBCC Assistance (SOS Sinistre) a constaté que... \n\nLes dégâts des eaux se situent :\n    ', NULL, 'Ndacke GUEYE', NULL, NULL, NULL, NULL, 'Ndacke GUEYE', NULL, NULL, NULL, NULL, NULL, 'Le présent rapport d\'expertise concerne un sinistre de Dégats des eaux survenu dans l\'appartement de Madame Djamila Ouazir, situé au ARGONAUTES, 15, ALL DE LA TOISON D\'OR 94000, locataire de l\'organisme bailleur social I3F. Le sinistre a été signalé le 13/08/2024 à ... et notre intervention sur site a eu lieu le  à .', 'L\'origine du sinistre a été identifiée comme Fuite; de l\'appartement de Madame Djamila Ouazir. La fuite a été causée par dégâts des eaux', NULL, NULL),
(7, 'RT_300920241214473998', 'OP2024-01-19-0135', NULL, 'Dégâts des eaux', '2024-09-20 12:16', '', '', '04:02:43', '', 'xc', '4 Rue des Brigades Internationales 93210 ST DENIS', 'ST DENIS', '93210', '', 'non', NULL, NULL, NULL, 'Fuite;', '}}}}', NULL, '', 'dfhfgh', 'Non', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'xw', '', '', NULL, '', '', NULL, '0', '', '0', NULL, '', '', '', NULL, '07-01-2025 16:02', NULL, '', NULL, NULL, '30-09-2024 12:14', '07-01-2025 16:02', 3998, NULL, '', NULL, NULL, NULL, NULL, 0, 'Cloques;', '', 'non', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'RT_141020240248044719', 'OP2024-05-27-0821', NULL, 'Dégâts des eaux', '2024-12-12', '', '', '10:26:34', '', '', '9 Rue Gérard Philipe 94400 Vitry-sur-Seine', 'Vitry-sur-Seine', '94400', 'plus10', 'non', NULL, NULL, NULL, 'Fuite;', 'Canalisation d\'alimentation fuyarde : Oui;}}}}', NULL, 'chez moi', 'rtur', 'Non', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'e', '', '', NULL, '', 'Des Pièces', NULL, '1', 'Chambre-1', '0', NULL, '', '', '', NULL, '29-12-2024 22:26', NULL, '', NULL, NULL, '14-10-2024 14:48', '29-12-2024 22:26', 4719, NULL, '', NULL, NULL, NULL, NULL, 0, 'Moisissures;', '', 'non', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, NULL, NULL, NULL, NULL, '2025-01-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Des Pièces', NULL, '1', 'Cuisine-1', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, 4718, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Des Pièces', NULL, '1', 'Chambre-1', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, 4712, 14, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_rendez_vous`
--

CREATE TABLE `wbcc_rendez_vous` (
  `numero` varchar(200) DEFAULT NULL,
  `idRV` int(11) NOT NULL,
  `dateRV` varchar(50) DEFAULT NULL,
  `heureDebut` varchar(50) DEFAULT NULL,
  `dateFin` varchar(25) DEFAULT NULL,
  `heureFin` varchar(50) DEFAULT NULL,
  `moyenTechnique` text DEFAULT NULL,
  `conclusion` text DEFAULT NULL,
  `idAppConF` int(11) DEFAULT NULL,
  `idCampagneF` int(11) DEFAULT NULL,
  `idOpportunityF` int(11) DEFAULT NULL,
  `idAppExtra` varchar(11) DEFAULT NULL,
  `idAppGuid` varchar(255) DEFAULT NULL,
  `idAppContactExtra` varchar(11) DEFAULT NULL,
  `idOpGuid` varchar(255) DEFAULT NULL,
  `expert` varchar(255) DEFAULT NULL,
  `idExpertF` int(11) DEFAULT NULL,
  `etatRV` int(11) DEFAULT 0,
  `numeroOP` varchar(255) DEFAULT NULL,
  `adresseRV` varchar(255) DEFAULT NULL,
  `nomDO` varchar(255) DEFAULT NULL,
  `idRVExtra` varchar(255) DEFAULT NULL,
  `idRVGuid` varchar(255) DEFAULT NULL,
  `typeRV` varchar(255) DEFAULT NULL,
  `idContactGuidF` varchar(255) DEFAULT NULL,
  `createDate` varchar(50) DEFAULT NULL,
  `editDate` varchar(50) DEFAULT NULL,
  `auteur` varchar(255) DEFAULT NULL,
  `idAuteur` int(11) DEFAULT NULL,
  `isDeleted` int(11) DEFAULT 0,
  `isProvisoire` int(11) DEFAULT 0,
  `etatFRT` int(11) DEFAULT NULL,
  `commentaireControleFRT` text DEFAULT NULL,
  `idAuteurControleFRT` int(11) DEFAULT NULL,
  `dateControleFRT` varchar(25) DEFAULT NULL,
  `idContactF` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_rendez_vous`
--

INSERT INTO `wbcc_rendez_vous` (`numero`, `idRV`, `dateRV`, `heureDebut`, `dateFin`, `heureFin`, `moyenTechnique`, `conclusion`, `idAppConF`, `idCampagneF`, `idOpportunityF`, `idAppExtra`, `idAppGuid`, `idAppContactExtra`, `idOpGuid`, `expert`, `idExpertF`, `etatRV`, `numeroOP`, `adresseRV`, `nomDO`, `idRVExtra`, `idRVGuid`, `typeRV`, `idContactGuidF`, `createDate`, `editDate`, `auteur`, `idAuteur`, `isDeleted`, `isProvisoire`, `etatFRT`, `commentaireControleFRT`, `idAuteurControleFRT`, `dateControleFRT`, `idContactF`) VALUES
('RV27062024152956041205', 1, '2024-10-23', '12:00', NULL, '13:00', '', 'Nom : Mme Laura NDIAYE\nTel : 0140355356\nEmail : wbcc021@gmail.comm\nCommentaire : ghjgkghj ', NULL, NULL, 4120, '4051', 'APP27062024130729167', NULL, NULL, ' Jean-Marc DJOSSINOU ', 605, 0, 'OP2024-02-05-0248', '4 Rue des Brigades Internationales 93210 ST DENIS', 'RELAIS HABITAT SR', NULL, '', 'RTP', '9c2edd0b-63c7-4be7-b6d1-fff5377e6687', '2024-06-27 15:29:56', '2024-06-27 18:18:45', 'Ndacke GUEYE', 5, 0, 0, NULL, NULL, NULL, NULL, 311),
('RV30092024160302039985', 2, '2024-10-19', '08:00', NULL, '09:00', '', 'Nom : Monsieur Test DUPONT\nTel : 0980084484\nEmail : wbcc021@gmal.com\nCommentaire : dfhfgh ', NULL, NULL, 3998, '4058', 'APP250920241822401757', NULL, NULL, 'Non Attribué', 583, 1, 'OP2024-01-19-0135', '4 Rue des Brigades Internationales 93210 ST DENIS', 'Test DUPONT', NULL, '', 'RTP', 'e61f6a44-dacc-4577-9cb9-2ea8906c085f', '2024-09-30 16:03:02', '2024-11-06 11:11:43', 'Ndacke GUEYE', 5, 0, 0, NULL, NULL, NULL, NULL, 8019),
('RV21102024174935040025', 8, '2024-10-23', '14:00', NULL, '16:00', '', 'Nom : Monsieur Test 6\nTel : 0980000000\nEmail : wbcc021@wbcc.fr\nCommentaire : ghjgkghj ', NULL, NULL, 4002, '4052', 'APP080820241411331977', NULL, NULL, ' Jean-Marc DJOSSINOU ', 605, 0, 'OP2024-01-19-0137', '24 Rue René Boin 93240 Stains', 'Test 6', NULL, '', 'RTP', '4d83a593-a267-4aaa-a4c5-fce4598b6a10', '2024-10-21 17:49:35', '2024-10-21 17:49:35', 'Ndacke GUEYE', 5, 0, 0, NULL, NULL, NULL, NULL, 8023),
('RV06112024122642039985', 9, '2024-11-07', '09:30', NULL, '10:30', '', 'Nom : Monsieur Test DUPONT\nTel : 0980084484\nEmail : wbcc021@gmail.com\nCommentaire : dfhfgh ', NULL, NULL, 3998, '4058', 'APP250920241822401757', NULL, NULL, ' dfgdfgdfgfd fsdgdfg ', 625, 0, 'OP2024-01-19-0135', '4 Rue des Brigades Internationales 93210 ST DENIS', 'Test DUPONT', NULL, '', 'RFP', 'e61f6a44-dacc-4577-9cb9-2ea8906c085f', '2024-11-06 12:26:42', '2024-11-06 12:26:42', 'Ndacke GUEYE', 5, 0, 0, NULL, NULL, NULL, NULL, 8019),
('RV07112024180344046955', 10, '2024-11-12', '09:00', '2024-11-12', '18:00', '', 'Nom : Madame Natohkoma Tadian\nTel : 0655505656565\nEmail : xgsdgdsfgsd@fsdfS.df ', NULL, NULL, 4695, '0', '', NULL, NULL, 'TEST ART3', 628, 0, 'OP2024-05-21-0799', '73 Avenue de la République 93800 Épinay-sur-Seine', 'Madame Natohkoma Tadian', NULL, '', 'TRAVAUX', 'CON21052024154639605', '2024-11-07 18:03:43', '2024-11-12 13:52:39', 'Ndacke GUEYE', 5, 0, 0, NULL, NULL, NULL, NULL, 8837),
('RV131220241555005', 11, '2024-12-20', '08:00', '2024-12-20', '09:00', '', '', NULL, NULL, 4657, '', '', NULL, NULL, 'Jean-Marc DJOSSINOU', 605, 0, 'OP2024-05-16-0765', '9 Rue Gérard Philipe 94400 Vitry-sur-Seine', 'Mamadou Wassa', NULL, '', 'EXPERTISEP', 'CON0205202414100771', '2024-12-13 15:55:00', '2024-12-13 15:55:00', 'Ndacke GUEYE', 5, 0, 1, NULL, NULL, NULL, NULL, 8719),
('RV30122024000303040035', 12, '2025-01-03', '09:30', '2025-01-03', '10:00', '', 'Nom : Monsieur Bernard  DUPOND\nTel : 7777777\nEmail : wbcc0000@wbcc.fr\nCommentaire : dgs ', NULL, NULL, 4003, '4053', 'APP130820241541351404', NULL, NULL, ' Jean-Marc DJOSSINOU ', 605, 0, 'OP2024-01-22-0142', '23 SQUARE DU NORD 95500 GONESSE', 'Bernard  DUPOND', NULL, '', 'RTP', 'CON_18180505202420242024044958', '2024-12-30 00:03:03', '2024-12-30 00:03:03', 'Ndacke GUEYE', 5, 0, 0, NULL, NULL, NULL, NULL, 8828),
('RV16012025154321047135', 13, '', '', '', '', '', '', NULL, NULL, 4713, NULL, NULL, NULL, NULL, NULL, 0, 0, 'TEST-OP2024-05-24-0816', '218 Rue de Bellevue, 92700 Colombes, France. 92700 Colombes', 'Test OPH Test', NULL, '', 'TRAVAUX', '', '2025-01-16 15:43:21', '2025-01-16 15:43:21', 'Ndacke GUEYE', 5, 0, 1, NULL, NULL, NULL, NULL, 8857),
('RV16012025155429047125', 14, '', '', '', '', '', '', NULL, NULL, 4712, NULL, NULL, NULL, NULL, NULL, 0, 0, 'OP2024-05-24-0815', '', 'Rochel Henry', NULL, '', 'TRAVAUX', '', '2025-01-16 15:54:28', '2025-01-16 15:54:28', 'Ndacke GUEYE', 5, 0, 1, NULL, NULL, NULL, NULL, 8855),
('RV16012025155751047105', 15, '', '', '', '', '', '', NULL, NULL, 4710, NULL, NULL, NULL, NULL, NULL, 0, 0, 'OP2024-05-23-0813', '', 'Abdel Kader  Boujemaoui', NULL, '', 'TRAVAUX', '', '2025-01-16 15:57:51', '2025-01-16 15:57:51', 'Ndacke GUEYE', 5, 0, 1, NULL, NULL, NULL, NULL, 8854);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_revetement_bordereau`
--

CREATE TABLE `wbcc_revetement_bordereau` (
  `idRevetementB` int(11) NOT NULL,
  `nomRevetementB` varchar(255) DEFAULT NULL,
  `etatRevetementB` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_roles`
--

CREATE TABLE `wbcc_roles` (
  `idRole` int(11) NOT NULL,
  `libelleRole` varchar(200) NOT NULL,
  `etatRole` tinyint(1) NOT NULL DEFAULT 1,
  `accessibilite` text NOT NULL,
  `visibleInscription` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_roles`
--

INSERT INTO `wbcc_roles` (`idRole`, `libelleRole`, `etatRole`, `accessibilite`, `visibleInscription`) VALUES
(1, 'Administrateur', 1, '1;2;', 0),
(2, 'Manager', 1, '', 0),
(3, 'Gestionnaire', 1, '', 0),
(4, 'Expert', 1, '', 0),
(5, 'Commercial', 1, '', 0),
(6, 'Artisan', 1, '', 0),
(7, 'RH', 1, '', 0),
(8, 'Assistant de Direction', 1, '', 0),
(9, 'Comptable', 1, '', 0),
(10, 'Informaticien', 1, '', 0),
(11, 'Test', 1, '', 0),
(12, 'Télé-Opérateur', 1, '', 0),
(13, 'Dirigeant', 1, '', 0),
(14, 'Responsable', 1, '', 0),
(15, 'Salarie', 1, '', 0),
(16, 'Particulier', 1, '', 0),
(17, 'En attente de validation', 1, '', 0),
(18, 'Responsable Technique', 1, '', 0),
(19, 'RHSR', 1, '', 0),
(20, 'Apporteur d\'Affaires', 1, '', 0),
(21, 'Occupant', 1, '', 0),
(22, 'Coproprietaire', 1, '', 0),
(23, 'Candidat Artisan', 1, '', 1),
(24, 'Candidat Commercial', 1, '', 1),
(25, 'Manager de Site', 1, '', 0),
(33, 'PRESENCE', 1, '', 0);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_role_sous_module`
--

CREATE TABLE `wbcc_role_sous_module` (
  `idRoleSousModule` int(11) NOT NULL,
  `numeroRoleSousModule` varchar(50) NOT NULL,
  `idRoleF` int(11) NOT NULL,
  `idSousModuleF` int(11) NOT NULL,
  `etatRoleSousModule` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_role_sous_module`
--

INSERT INTO `wbcc_role_sous_module` (`idRoleSousModule`, `numeroRoleSousModule`, `idRoleF`, `idSousModuleF`, `etatRoleSousModule`) VALUES
(1, '', 1, 1, 1),
(2, '', 1, 2, 1),
(3, '', 1, 3, 1),
(4, '', 1, 4, 1),
(5, '', 1, 5, 1),
(6, '', 1, 6, 1),
(7, '', 1, 7, 1),
(8, '', 1, 8, 1),
(9, '', 1, 9, 1),
(10, '', 1, 10, 1),
(11, '', 1, 11, 1),
(12, '', 1, 12, 1),
(13, '', 1, 13, 1),
(14, '', 1, 14, 1),
(15, '', 1, 15, 1),
(16, '', 1, 16, 1),
(17, '', 1, 17, 1),
(18, '', 1, 18, 1),
(19, '', 1, 19, 1),
(20, '', 1, 20, 1),
(21, '', 1, 21, 1),
(22, '', 1, 22, 1),
(23, '', 1, 23, 1),
(24, '', 1, 24, 1),
(25, '', 1, 25, 1),
(26, '', 1, 26, 1),
(27, '', 1, 27, 0),
(28, '', 1, 28, 0),
(29, '', 1, 29, 0),
(30, '', 1, 30, 0),
(31, '', 1, 31, 0),
(32, '', 1, 32, 0),
(33, '', 1, 33, 0),
(34, '', 1, 34, 0),
(35, '', 1, 35, 0),
(36, '', 1, 36, 0),
(37, '', 1, 37, 0),
(38, '', 1, 38, 1),
(39, '', 1, 39, 0),
(40, '', 1, 40, 0),
(41, '', 2, 1, 1),
(42, '', 2, 2, 1),
(43, '', 2, 3, 1),
(44, '', 2, 4, 1),
(45, '', 2, 5, 1),
(46, '', 2, 6, 1),
(47, '', 2, 7, 1),
(48, '', 2, 8, 1),
(49, '', 2, 9, 1),
(50, '', 2, 10, 1),
(51, '', 2, 11, 1),
(52, '', 2, 12, 1),
(53, '', 2, 13, 1),
(54, '', 2, 14, 1),
(55, '', 2, 15, 1),
(56, '', 2, 16, 1),
(57, '', 2, 17, 1),
(58, '', 2, 18, 1),
(59, '', 2, 19, 1),
(60, '', 2, 20, 1),
(61, '', 2, 21, 1),
(62, '', 2, 22, 1),
(63, '', 2, 23, 1),
(64, '', 2, 24, 1),
(65, '', 2, 25, 1),
(66, '', 2, 26, 1),
(67, '', 2, 27, 0),
(68, '', 2, 28, 0),
(69, '', 2, 29, 0),
(70, '', 2, 30, 0),
(71, '', 2, 31, 0),
(72, '', 2, 32, 0),
(73, '', 2, 33, 0),
(74, '', 2, 34, 0),
(75, '', 2, 35, 0),
(76, '', 2, 36, 0),
(77, '', 2, 37, 0),
(78, '', 2, 38, 1),
(79, '', 2, 39, 0),
(80, '', 2, 40, 0),
(81, '', 3, 1, 1),
(82, '', 3, 2, 1),
(83, '', 3, 3, 1),
(84, '', 3, 4, 1),
(85, '', 3, 5, 1),
(86, '', 3, 6, 1),
(87, '', 3, 7, 1),
(88, '', 3, 8, 1),
(89, '', 3, 9, 1),
(90, '', 3, 10, 1),
(91, '', 3, 11, 0),
(92, '', 3, 12, 0),
(93, '', 3, 13, 0),
(94, '', 3, 14, 0),
(95, '', 3, 15, 0),
(96, '', 3, 16, 0),
(97, '', 3, 17, 1),
(98, '', 3, 18, 1),
(99, '', 3, 19, 0),
(100, '', 3, 20, 0),
(101, '', 3, 21, 0),
(102, '', 3, 22, 0),
(103, '', 3, 23, 1),
(104, '', 3, 24, 0),
(105, '', 3, 25, 0),
(106, '', 3, 26, 0),
(107, '', 3, 27, 0),
(108, '', 3, 28, 0),
(109, '', 3, 29, 0),
(110, '', 3, 30, 0),
(111, '', 3, 31, 0),
(112, '', 3, 32, 0),
(113, '', 3, 33, 0),
(114, '', 3, 34, 0),
(115, '', 3, 35, 0),
(116, '', 3, 36, 0),
(117, '', 3, 37, 0),
(118, '', 3, 38, 0),
(119, '', 3, 39, 0),
(120, '', 3, 40, 0),
(121, '', 4, 1, 1),
(122, '', 4, 2, 1),
(123, '', 4, 3, 1),
(124, '', 4, 4, 1),
(125, '', 4, 5, 0),
(126, '', 4, 6, 0),
(127, '', 4, 7, 0),
(128, '', 4, 8, 0),
(129, '', 4, 9, 0),
(130, '', 4, 10, 0),
(131, '', 4, 11, 0),
(132, '', 4, 12, 0),
(133, '', 4, 13, 0),
(134, '', 4, 14, 0),
(135, '', 4, 15, 0),
(136, '', 4, 16, 0),
(137, '', 4, 17, 1),
(138, '', 4, 18, 0),
(139, '', 4, 19, 0),
(140, '', 4, 20, 0),
(141, '', 4, 21, 0),
(142, '', 4, 22, 0),
(143, '', 4, 23, 0),
(144, '', 4, 24, 0),
(145, '', 4, 25, 0),
(146, '', 4, 26, 0),
(147, '', 4, 27, 0),
(148, '', 4, 28, 0),
(149, '', 4, 29, 0),
(150, '', 4, 30, 0),
(151, '', 4, 31, 0),
(152, '', 4, 32, 0),
(153, '', 4, 33, 0),
(154, '', 4, 34, 0),
(155, '', 4, 35, 0),
(156, '', 4, 36, 0),
(157, '', 4, 37, 0),
(158, '', 4, 38, 0),
(159, '', 4, 39, 0),
(160, '', 4, 40, 0),
(161, '', 5, 1, 1),
(162, '', 5, 2, 1),
(163, '', 5, 3, 1),
(164, '', 5, 4, 1),
(165, '', 5, 5, 0),
(166, '', 5, 6, 0),
(167, '', 5, 7, 1),
(168, '', 5, 8, 1),
(169, '', 5, 9, 1),
(170, '', 5, 10, 1),
(171, '', 5, 11, 0),
(172, '', 5, 12, 0),
(173, '', 5, 13, 0),
(174, '', 5, 14, 0),
(175, '', 5, 15, 0),
(176, '', 5, 16, 0),
(177, '', 5, 17, 1),
(178, '', 5, 18, 1),
(179, '', 5, 19, 1),
(180, '', 5, 20, 0),
(181, '', 5, 21, 0),
(182, '', 5, 22, 0),
(183, '', 5, 23, 0),
(184, '', 5, 24, 0),
(185, '', 5, 25, 0),
(186, '', 5, 26, 0),
(187, '', 5, 27, 0),
(188, '', 5, 28, 0),
(189, '', 5, 29, 0),
(190, '', 5, 30, 0),
(191, '', 5, 31, 0),
(192, '', 5, 32, 0),
(193, '', 5, 33, 0),
(194, '', 5, 34, 0),
(195, '', 5, 35, 0),
(196, '', 5, 36, 0),
(197, '', 5, 37, 0),
(198, '', 5, 38, 0),
(199, '', 5, 39, 0),
(200, '', 5, 40, 0),
(201, '', 6, 1, 0),
(202, '', 6, 2, 0),
(203, '', 6, 3, 0),
(204, '', 6, 4, 0),
(205, '', 6, 5, 0),
(206, '', 6, 6, 0),
(207, '', 6, 7, 0),
(208, '', 6, 8, 0),
(209, '', 6, 9, 0),
(210, '', 6, 10, 0),
(211, '', 6, 11, 0),
(212, '', 6, 12, 0),
(213, '', 6, 13, 0),
(214, '', 6, 14, 0),
(215, '', 6, 15, 0),
(216, '', 6, 16, 0),
(217, '', 6, 17, 0),
(218, '', 6, 18, 1),
(219, '', 6, 19, 0),
(220, '', 6, 20, 0),
(221, '', 6, 21, 0),
(222, '', 6, 22, 0),
(223, '', 6, 23, 0),
(224, '', 6, 24, 0),
(225, '', 6, 25, 0),
(226, '', 6, 26, 0),
(227, '', 6, 27, 0),
(228, '', 6, 28, 0),
(229, '', 6, 29, 0),
(230, '', 6, 30, 0),
(231, '', 6, 31, 0),
(232, '', 6, 32, 0),
(233, '', 6, 33, 0),
(234, '', 6, 34, 0),
(235, '', 6, 35, 0),
(236, '', 6, 36, 0),
(237, '', 6, 37, 0),
(238, '', 6, 38, 0),
(239, '', 6, 39, 1),
(240, '', 6, 40, 0),
(241, '', 7, 1, 0),
(242, '', 7, 2, 0),
(243, '', 7, 3, 0),
(244, '', 7, 4, 0),
(245, '', 7, 5, 0),
(246, '', 7, 6, 0),
(247, '', 7, 7, 0),
(248, '', 7, 8, 0),
(249, '', 7, 9, 0),
(250, '', 7, 10, 0),
(251, '', 7, 11, 0),
(252, '', 7, 12, 0),
(253, '', 7, 13, 0),
(254, '', 7, 14, 0),
(255, '', 7, 15, 1),
(256, '', 7, 16, 1),
(257, '', 7, 17, 0),
(258, '', 7, 18, 0),
(259, '', 7, 19, 0),
(260, '', 7, 20, 0),
(261, '', 7, 21, 0),
(262, '', 7, 22, 0),
(263, '', 7, 23, 0),
(264, '', 7, 24, 0),
(265, '', 7, 25, 0),
(266, '', 7, 26, 0),
(267, '', 7, 27, 0),
(268, '', 7, 28, 0),
(269, '', 7, 29, 0),
(270, '', 7, 30, 0),
(271, '', 7, 31, 0),
(272, '', 7, 32, 0),
(273, '', 7, 33, 0),
(274, '', 7, 34, 0),
(275, '', 7, 35, 0),
(276, '', 7, 36, 0),
(277, '', 7, 37, 0),
(278, '', 7, 38, 0),
(279, '', 7, 39, 0),
(280, '', 7, 40, 0),
(281, '', 8, 1, 1),
(282, '', 8, 2, 1),
(283, '', 8, 3, 1),
(284, '', 8, 4, 1),
(285, '', 8, 5, 1),
(286, '', 8, 6, 1),
(287, '', 8, 7, 1),
(288, '', 8, 8, 1),
(289, '', 8, 9, 1),
(290, '', 8, 10, 1),
(291, '', 8, 11, 1),
(292, '', 8, 12, 0),
(293, '', 8, 13, 1),
(294, '', 8, 14, 0),
(295, '', 8, 15, 1),
(296, '', 8, 16, 0),
(297, '', 8, 17, 1),
(298, '', 8, 18, 1),
(299, '', 8, 19, 1),
(300, '', 8, 20, 1),
(301, '', 8, 21, 1),
(302, '', 8, 22, 1),
(303, '', 8, 23, 1),
(304, '', 8, 24, 1),
(305, '', 8, 25, 1),
(306, '', 8, 26, 1),
(307, '', 8, 27, 0),
(308, '', 8, 28, 0),
(309, '', 8, 29, 0),
(310, '', 8, 30, 0),
(311, '', 8, 31, 0),
(312, '', 8, 32, 0),
(313, '', 8, 33, 0),
(314, '', 8, 34, 0),
(315, '', 8, 35, 1),
(316, '', 8, 36, 0),
(317, '', 8, 37, 0),
(318, '', 8, 38, 0),
(319, '', 8, 39, 0),
(320, '', 8, 40, 0),
(321, '', 9, 1, 1),
(322, '', 9, 2, 0),
(323, '', 9, 3, 0),
(324, '', 9, 4, 0),
(325, '', 9, 5, 0),
(326, '', 9, 6, 0),
(327, '', 9, 7, 0),
(328, '', 9, 8, 0),
(329, '', 9, 9, 0),
(330, '', 9, 10, 0),
(331, '', 9, 11, 0),
(332, '', 9, 12, 0),
(333, '', 9, 13, 0),
(334, '', 9, 14, 0),
(335, '', 9, 15, 0),
(336, '', 9, 16, 0),
(337, '', 9, 17, 0),
(338, '', 9, 18, 0),
(339, '', 9, 19, 0),
(340, '', 9, 20, 0),
(341, '', 9, 21, 0),
(342, '', 9, 22, 1),
(343, '', 9, 23, 1),
(344, '', 9, 24, 1),
(345, '', 9, 25, 1),
(346, '', 9, 26, 1),
(347, '', 9, 27, 0),
(348, '', 9, 28, 0),
(349, '', 9, 29, 0),
(350, '', 9, 30, 0),
(351, '', 9, 31, 0),
(352, '', 9, 32, 0),
(353, '', 9, 33, 0),
(354, '', 9, 34, 0),
(355, '', 9, 35, 0),
(356, '', 9, 36, 0),
(357, '', 9, 37, 0),
(358, '', 9, 38, 0),
(359, '', 9, 39, 0),
(360, '', 9, 40, 0),
(361, '', 10, 1, 0),
(362, '', 10, 2, 0),
(363, '', 10, 3, 0),
(364, '', 10, 4, 0),
(365, '', 10, 5, 0),
(366, '', 10, 6, 0),
(367, '', 10, 7, 0),
(368, '', 10, 8, 0),
(369, '', 10, 9, 0),
(370, '', 10, 10, 0),
(371, '', 10, 11, 0),
(372, '', 10, 12, 0),
(373, '', 10, 13, 0),
(374, '', 10, 14, 0),
(375, '', 10, 15, 0),
(376, '', 10, 16, 0),
(377, '', 10, 17, 0),
(378, '', 10, 18, 0),
(379, '', 10, 19, 0),
(380, '', 10, 20, 0),
(381, '', 10, 21, 0),
(382, '', 10, 22, 0),
(383, '', 10, 23, 0),
(384, '', 10, 24, 0),
(385, '', 10, 25, 0),
(386, '', 10, 26, 0),
(387, '', 10, 27, 0),
(388, '', 10, 28, 0),
(389, '', 10, 29, 0),
(390, '', 10, 30, 0),
(391, '', 10, 31, 0),
(392, '', 10, 32, 0),
(393, '', 10, 33, 0),
(394, '', 10, 34, 0),
(395, '', 10, 35, 0),
(396, '', 10, 36, 0),
(397, '', 10, 37, 0),
(398, '', 10, 38, 0),
(399, '', 10, 39, 0),
(400, '', 10, 40, 0),
(401, '', 11, 1, 0),
(402, '', 11, 2, 0),
(403, '', 11, 3, 0),
(404, '', 11, 4, 0),
(405, '', 11, 5, 0),
(406, '', 11, 6, 0),
(407, '', 11, 7, 0),
(408, '', 11, 8, 0),
(409, '', 11, 9, 0),
(410, '', 11, 10, 0),
(411, '', 11, 11, 0),
(412, '', 11, 12, 0),
(413, '', 11, 13, 0),
(414, '', 11, 14, 0),
(415, '', 11, 15, 0),
(416, '', 11, 16, 0),
(417, '', 11, 17, 0),
(418, '', 11, 18, 0),
(419, '', 11, 19, 0),
(420, '', 11, 20, 0),
(421, '', 11, 21, 0),
(422, '', 11, 22, 0),
(423, '', 11, 23, 0),
(424, '', 11, 24, 0),
(425, '', 11, 25, 0),
(426, '', 11, 26, 0),
(427, '', 11, 27, 0),
(428, '', 11, 28, 0),
(429, '', 11, 29, 0),
(430, '', 11, 30, 0),
(431, '', 11, 31, 0),
(432, '', 11, 32, 0),
(433, '', 11, 33, 0),
(434, '', 11, 34, 0),
(435, '', 11, 35, 0),
(436, '', 11, 36, 0),
(437, '', 11, 37, 0),
(438, '', 11, 38, 0),
(439, '', 11, 39, 0),
(440, '', 11, 40, 0),
(441, '', 12, 1, 0),
(442, '', 12, 2, 0),
(443, '', 12, 3, 0),
(444, '', 12, 4, 0),
(445, '', 12, 5, 0),
(446, '', 12, 6, 0),
(447, '', 12, 7, 0),
(448, '', 12, 8, 0),
(449, '', 12, 9, 0),
(450, '', 12, 10, 0),
(451, '', 12, 11, 0),
(452, '', 12, 12, 0),
(453, '', 12, 13, 0),
(454, '', 12, 14, 0),
(455, '', 12, 15, 0),
(456, '', 12, 16, 0),
(457, '', 12, 17, 0),
(458, '', 12, 18, 0),
(459, '', 12, 19, 0),
(460, '', 12, 20, 0),
(461, '', 12, 21, 0),
(462, '', 12, 22, 0),
(463, '', 12, 23, 0),
(464, '', 12, 24, 0),
(465, '', 12, 25, 0),
(466, '', 12, 26, 0),
(467, '', 12, 27, 0),
(468, '', 12, 28, 0),
(469, '', 12, 29, 0),
(470, '', 12, 30, 0),
(471, '', 12, 31, 0),
(472, '', 12, 32, 0),
(473, '', 12, 33, 0),
(474, '', 12, 34, 0),
(475, '', 12, 35, 0),
(476, '', 12, 36, 0),
(477, '', 12, 37, 0),
(478, '', 12, 38, 0),
(479, '', 12, 39, 0),
(480, '', 12, 40, 0),
(481, '', 13, 1, 0),
(482, '', 13, 2, 0),
(483, '', 13, 3, 0),
(484, '', 13, 4, 0),
(485, '', 13, 5, 0),
(486, '', 13, 6, 0),
(487, '', 13, 7, 0),
(488, '', 13, 8, 0),
(489, '', 13, 9, 0),
(490, '', 13, 10, 0),
(491, '', 13, 11, 0),
(492, '', 13, 12, 0),
(493, '', 13, 13, 0),
(494, '', 13, 14, 0),
(495, '', 13, 15, 0),
(496, '', 13, 16, 0),
(497, '', 13, 17, 0),
(498, '', 13, 18, 1),
(499, '', 13, 19, 0),
(500, '', 13, 20, 0),
(501, '', 13, 21, 0),
(502, '', 13, 22, 0),
(503, '', 13, 23, 0),
(504, '', 13, 24, 0),
(505, '', 13, 25, 0),
(506, '', 13, 26, 0),
(507, '', 13, 27, 0),
(508, '', 13, 28, 0),
(509, '', 13, 29, 0),
(510, '', 13, 30, 0),
(511, '', 13, 31, 0),
(512, '', 13, 32, 1),
(513, '', 13, 33, 1),
(514, '', 13, 34, 1),
(515, '', 13, 35, 0),
(516, '', 13, 36, 1),
(517, '', 13, 37, 1),
(518, '', 13, 38, 0),
(519, '', 13, 39, 1),
(520, '', 13, 40, 1),
(521, '', 14, 1, 0),
(522, '', 14, 2, 0),
(523, '', 14, 3, 0),
(524, '', 14, 4, 0),
(525, '', 14, 5, 0),
(526, '', 14, 6, 0),
(527, '', 14, 7, 0),
(528, '', 14, 8, 0),
(529, '', 14, 9, 0),
(530, '', 14, 10, 0),
(531, '', 14, 11, 0),
(532, '', 14, 12, 0),
(533, '', 14, 13, 0),
(534, '', 14, 14, 0),
(535, '', 14, 15, 0),
(536, '', 14, 16, 0),
(537, '', 14, 17, 0),
(538, '', 14, 18, 1),
(539, '', 14, 19, 0),
(540, '', 14, 20, 0),
(541, '', 14, 21, 0),
(542, '', 14, 22, 0),
(543, '', 14, 23, 0),
(544, '', 14, 24, 0),
(545, '', 14, 25, 0),
(546, '', 14, 26, 0),
(547, '', 14, 27, 0),
(548, '', 14, 28, 0),
(549, '', 14, 29, 0),
(550, '', 14, 30, 0),
(551, '', 14, 31, 0),
(552, '', 14, 32, 1),
(553, '', 14, 33, 1),
(554, '', 14, 34, 1),
(555, '', 14, 35, 0),
(556, '', 14, 36, 1),
(557, '', 14, 37, 1),
(558, '', 14, 38, 0),
(559, '', 14, 39, 1),
(560, '', 14, 40, 1),
(561, '', 15, 1, 0),
(562, '', 15, 2, 0),
(563, '', 15, 3, 0),
(564, '', 15, 4, 0),
(565, '', 15, 5, 0),
(566, '', 15, 6, 0),
(567, '', 15, 7, 0),
(568, '', 15, 8, 0),
(569, '', 15, 9, 0),
(570, '', 15, 10, 0),
(571, '', 15, 11, 0),
(572, '', 15, 12, 0),
(573, '', 15, 13, 0),
(574, '', 15, 14, 0),
(575, '', 15, 15, 0),
(576, '', 15, 16, 0),
(577, '', 15, 17, 0),
(578, '', 15, 18, 1),
(579, '', 15, 19, 0),
(580, '', 15, 20, 0),
(581, '', 15, 21, 0),
(582, '', 15, 22, 0),
(583, '', 15, 23, 0),
(584, '', 15, 24, 0),
(585, '', 15, 25, 0),
(586, '', 15, 26, 0),
(587, '', 15, 27, 0),
(588, '', 15, 28, 0),
(589, '', 15, 29, 0),
(590, '', 15, 30, 0),
(591, '', 15, 31, 0),
(592, '', 15, 32, 1),
(593, '', 15, 33, 1),
(594, '', 15, 34, 1),
(595, '', 15, 35, 0),
(596, '', 15, 36, 1),
(597, '', 15, 37, 0),
(598, '', 15, 38, 0),
(599, '', 15, 39, 1),
(600, '', 15, 40, 0),
(601, '', 16, 1, 0),
(602, '', 16, 2, 0),
(603, '', 16, 3, 0),
(604, '', 16, 4, 0),
(605, '', 16, 5, 0),
(606, '', 16, 6, 0),
(607, '', 16, 7, 0),
(608, '', 16, 8, 0),
(609, '', 16, 9, 0),
(610, '', 16, 10, 0),
(611, '', 16, 11, 0),
(612, '', 16, 12, 0),
(613, '', 16, 13, 0),
(614, '', 16, 14, 0),
(615, '', 16, 15, 0),
(616, '', 16, 16, 0),
(617, '', 16, 17, 0),
(618, '', 16, 18, 0),
(619, '', 16, 19, 0),
(620, '', 16, 20, 0),
(621, '', 16, 21, 0),
(622, '', 16, 22, 0),
(623, '', 16, 23, 0),
(624, '', 16, 24, 0),
(625, '', 16, 25, 0),
(626, '', 16, 26, 0),
(627, '', 16, 27, 0),
(628, '', 16, 28, 0),
(629, '', 16, 29, 0),
(630, '', 16, 30, 1),
(631, '', 16, 31, 1),
(632, '', 16, 32, 0),
(633, '', 16, 33, 0),
(634, '', 16, 34, 0),
(635, '', 16, 35, 0),
(636, '', 16, 36, 0),
(637, '', 16, 37, 0),
(638, '', 16, 38, 0),
(639, '', 16, 39, 0),
(640, '', 16, 40, 0),
(641, '', 17, 1, 0),
(642, '', 17, 2, 0),
(643, '', 17, 3, 0),
(644, '', 17, 4, 0),
(645, '', 17, 5, 0),
(646, '', 17, 6, 0),
(647, '', 17, 7, 0),
(648, '', 17, 8, 0),
(649, '', 17, 9, 0),
(650, '', 17, 10, 0),
(651, '', 17, 11, 0),
(652, '', 17, 12, 0),
(653, '', 17, 13, 0),
(654, '', 17, 14, 0),
(655, '', 17, 15, 0),
(656, '', 17, 16, 0),
(657, '', 17, 17, 0),
(658, '', 17, 18, 0),
(659, '', 17, 19, 0),
(660, '', 17, 20, 0),
(661, '', 17, 21, 0),
(662, '', 17, 22, 0),
(663, '', 17, 23, 0),
(664, '', 17, 24, 0),
(665, '', 17, 25, 0),
(666, '', 17, 26, 0),
(667, '', 17, 27, 0),
(668, '', 17, 28, 0),
(669, '', 17, 29, 0),
(670, '', 17, 30, 0),
(671, '', 17, 31, 0),
(672, '', 17, 32, 0),
(673, '', 17, 33, 0),
(674, '', 17, 34, 0),
(675, '', 17, 35, 0),
(676, '', 17, 36, 0),
(677, '', 17, 37, 0),
(678, '', 17, 38, 0),
(679, '', 17, 39, 0),
(680, '', 17, 40, 0),
(681, '', 18, 1, 1),
(682, '', 18, 2, 1),
(683, '', 18, 3, 1),
(684, '', 18, 4, 1),
(685, '', 18, 5, 1),
(686, '', 18, 6, 1),
(687, '', 18, 7, 1),
(688, '', 18, 8, 1),
(689, '', 18, 9, 1),
(690, '', 18, 10, 1),
(691, '', 18, 11, 0),
(692, '', 18, 12, 0),
(693, '', 18, 13, 1),
(694, '', 18, 14, 0),
(695, '', 18, 15, 0),
(696, '', 18, 16, 0),
(697, '', 18, 17, 0),
(698, '', 18, 18, 0),
(699, '', 18, 19, 0),
(700, '', 18, 20, 0),
(701, '', 18, 21, 0),
(702, '', 18, 22, 0),
(703, '', 18, 23, 0),
(704, '', 18, 24, 0),
(705, '', 18, 25, 0),
(706, '', 18, 26, 0),
(707, '', 18, 27, 0),
(708, '', 18, 28, 0),
(709, '', 18, 29, 0),
(710, '', 18, 30, 0),
(711, '', 18, 31, 0),
(712, '', 18, 32, 0),
(713, '', 18, 33, 0),
(714, '', 18, 34, 0),
(715, '', 18, 35, 0),
(716, '', 18, 36, 0),
(717, '', 18, 37, 0),
(718, '', 18, 38, 0),
(719, '', 18, 39, 0),
(720, '', 18, 40, 0),
(721, '', 19, 1, 0),
(722, '', 19, 2, 0),
(723, '', 19, 3, 0),
(724, '', 19, 4, 0),
(725, '', 19, 5, 0),
(726, '', 19, 6, 0),
(727, '', 19, 7, 0),
(728, '', 19, 8, 0),
(729, '', 19, 9, 0),
(730, '', 19, 10, 0),
(731, '', 19, 11, 0),
(732, '', 19, 12, 0),
(733, '', 19, 13, 0),
(734, '', 19, 14, 0),
(735, '', 19, 15, 0),
(736, '', 19, 16, 0),
(737, '', 19, 17, 0),
(738, '', 19, 18, 0),
(739, '', 19, 19, 0),
(740, '', 19, 20, 0),
(741, '', 19, 21, 0),
(742, '', 19, 22, 0),
(743, '', 19, 23, 0),
(744, '', 19, 24, 0),
(745, '', 19, 25, 0),
(746, '', 19, 26, 0),
(747, '', 19, 27, 0),
(748, '', 19, 28, 0),
(749, '', 19, 29, 0),
(750, '', 19, 30, 0),
(751, '', 19, 31, 0),
(752, '', 19, 32, 0),
(753, '', 19, 33, 0),
(754, '', 19, 34, 0),
(755, '', 19, 35, 0),
(756, '', 19, 36, 0),
(757, '', 19, 37, 0),
(758, '', 19, 38, 0),
(759, '', 19, 39, 0),
(760, '', 19, 40, 0),
(761, '', 20, 1, 0),
(762, '', 20, 2, 0),
(763, '', 20, 3, 0),
(764, '', 20, 4, 0),
(765, '', 20, 5, 0),
(766, '', 20, 6, 0),
(767, '', 20, 7, 0),
(768, '', 20, 8, 0),
(769, '', 20, 9, 0),
(770, '', 20, 10, 0),
(771, '', 20, 11, 0),
(772, '', 20, 12, 0),
(773, '', 20, 13, 0),
(774, '', 20, 14, 0),
(775, '', 20, 15, 0),
(776, '', 20, 16, 0),
(777, '', 20, 17, 0),
(778, '', 20, 18, 0),
(779, '', 20, 19, 0),
(780, '', 20, 20, 0),
(781, '', 20, 21, 0),
(782, '', 20, 22, 0),
(783, '', 20, 23, 0),
(784, '', 20, 24, 0),
(785, '', 20, 25, 0),
(786, '', 20, 26, 0),
(787, '', 20, 27, 0),
(788, '', 20, 28, 0),
(789, '', 20, 29, 0),
(790, '', 20, 30, 0),
(791, '', 20, 31, 0),
(792, '', 20, 32, 0),
(793, '', 20, 33, 0),
(794, '', 20, 34, 0),
(795, '', 20, 35, 0),
(796, '', 20, 36, 0),
(797, '', 20, 37, 0),
(798, '', 20, 38, 0),
(799, '', 20, 39, 0),
(800, '', 20, 40, 0),
(801, '', 21, 1, 0),
(802, '', 21, 2, 0),
(803, '', 21, 3, 0),
(804, '', 21, 4, 0),
(805, '', 21, 5, 0),
(806, '', 21, 6, 0),
(807, '', 21, 7, 0),
(808, '', 21, 8, 0),
(809, '', 21, 9, 0),
(810, '', 21, 10, 0),
(811, '', 21, 11, 0),
(812, '', 21, 12, 0),
(813, '', 21, 13, 0),
(814, '', 21, 14, 0),
(815, '', 21, 15, 0),
(816, '', 21, 16, 0),
(817, '', 21, 17, 0),
(818, '', 21, 18, 0),
(819, '', 21, 19, 0),
(820, '', 21, 20, 0),
(821, '', 21, 21, 0),
(822, '', 21, 22, 0),
(823, '', 21, 23, 0),
(824, '', 21, 24, 0),
(825, '', 21, 25, 0),
(826, '', 21, 26, 0),
(827, '', 21, 27, 0),
(828, '', 21, 28, 0),
(829, '', 21, 29, 1),
(830, '', 21, 30, 0),
(831, '', 21, 31, 0),
(832, '', 21, 32, 0),
(833, '', 21, 33, 0),
(834, '', 21, 34, 0),
(835, '', 21, 35, 0),
(836, '', 21, 36, 0),
(837, '', 21, 37, 0),
(838, '', 21, 38, 0),
(839, '', 21, 39, 0),
(840, '', 21, 40, 0),
(841, '', 22, 1, 0),
(842, '', 22, 2, 0),
(843, '', 22, 3, 0),
(844, '', 22, 4, 0),
(845, '', 22, 5, 0),
(846, '', 22, 6, 0),
(847, '', 22, 7, 0),
(848, '', 22, 8, 0),
(849, '', 22, 9, 0),
(850, '', 22, 10, 0),
(851, '', 22, 11, 0),
(852, '', 22, 12, 0),
(853, '', 22, 13, 0),
(854, '', 22, 14, 0),
(855, '', 22, 15, 0),
(856, '', 22, 16, 0),
(857, '', 22, 17, 0),
(858, '', 22, 18, 0),
(859, '', 22, 19, 0),
(860, '', 22, 20, 0),
(861, '', 22, 21, 0),
(862, '', 22, 22, 0),
(863, '', 22, 23, 0),
(864, '', 22, 24, 0),
(865, '', 22, 25, 0),
(866, '', 22, 26, 0),
(867, '', 22, 27, 1),
(868, '', 22, 28, 1),
(869, '', 22, 29, 0),
(870, '', 22, 30, 0),
(871, '', 22, 31, 0),
(872, '', 22, 32, 0),
(873, '', 22, 33, 0),
(874, '', 22, 34, 0),
(875, '', 22, 35, 0),
(876, '', 22, 36, 0),
(877, '', 22, 37, 0),
(878, '', 22, 38, 0),
(879, '', 22, 39, 0),
(880, '', 22, 40, 0),
(881, '', 23, 1, 0),
(882, '', 23, 2, 0),
(883, '', 23, 3, 0),
(884, '', 23, 4, 0),
(885, '', 23, 5, 0),
(886, '', 23, 6, 0),
(887, '', 23, 7, 0),
(888, '', 23, 8, 0),
(889, '', 23, 9, 0),
(890, '', 23, 10, 0),
(891, '', 23, 11, 0),
(892, '', 23, 12, 0),
(893, '', 23, 13, 0),
(894, '', 23, 14, 0),
(895, '', 23, 15, 0),
(896, '', 23, 16, 0),
(897, '', 23, 17, 0),
(898, '', 23, 18, 0),
(899, '', 23, 19, 0),
(900, '', 23, 20, 0),
(901, '', 23, 21, 0),
(902, '', 23, 22, 0),
(903, '', 23, 23, 0),
(904, '', 23, 24, 0),
(905, '', 23, 25, 0),
(906, '', 23, 26, 0),
(907, '', 23, 27, 0),
(908, '', 23, 28, 0),
(909, '', 23, 29, 0),
(910, '', 23, 30, 0),
(911, '', 23, 31, 0),
(912, '', 23, 32, 0),
(913, '', 23, 33, 0),
(914, '', 23, 34, 0),
(915, '', 23, 35, 0),
(916, '', 23, 36, 0),
(917, '', 23, 37, 0),
(918, '', 23, 38, 0),
(919, '', 23, 39, 0),
(920, '', 23, 40, 0),
(921, '', 24, 1, 0),
(922, '', 24, 2, 0),
(923, '', 24, 3, 0),
(924, '', 24, 4, 0),
(925, '', 24, 5, 0),
(926, '', 24, 6, 0),
(927, '', 24, 7, 0),
(928, '', 24, 8, 0),
(929, '', 24, 9, 0),
(930, '', 24, 10, 0),
(931, '', 24, 11, 0),
(932, '', 24, 12, 0),
(933, '', 24, 13, 0),
(934, '', 24, 14, 0),
(935, '', 24, 15, 0),
(936, '', 24, 16, 0),
(937, '', 24, 17, 0),
(938, '', 24, 18, 0),
(939, '', 24, 19, 0),
(940, '', 24, 20, 0),
(941, '', 24, 21, 0),
(942, '', 24, 22, 0),
(943, '', 24, 23, 0),
(944, '', 24, 24, 0),
(945, '', 24, 25, 0),
(946, '', 24, 26, 0),
(947, '', 24, 27, 0),
(948, '', 24, 28, 0),
(949, '', 24, 29, 0),
(950, '', 24, 30, 0),
(951, '', 24, 31, 0),
(952, '', 24, 32, 0),
(953, '', 24, 33, 0),
(954, '', 24, 34, 0),
(955, '', 24, 35, 0),
(956, '', 24, 36, 0),
(957, '', 24, 37, 0),
(958, '', 24, 38, 0),
(959, '', 24, 39, 0),
(960, '', 24, 40, 0),
(961, '', 25, 1, 1),
(962, '', 25, 2, 1),
(963, '', 25, 3, 1),
(964, '', 25, 4, 1),
(965, '', 25, 5, 1),
(966, '', 25, 6, 1),
(967, '', 25, 7, 1),
(968, '', 25, 8, 1),
(969, '', 25, 9, 1),
(970, '', 25, 10, 1),
(971, '', 25, 11, 0),
(972, '', 25, 12, 0),
(973, '', 25, 13, 0),
(974, '', 25, 14, 0),
(975, '', 25, 15, 0),
(976, '', 25, 16, 0),
(977, '', 25, 17, 1),
(978, '', 25, 18, 1),
(979, '', 25, 19, 0),
(980, '', 25, 20, 1),
(981, '', 25, 21, 1),
(982, '', 25, 22, 0),
(983, '', 25, 23, 1),
(984, '', 25, 24, 0),
(985, '', 25, 25, 0),
(986, '', 25, 26, 0),
(987, '', 25, 27, 0),
(988, '', 25, 28, 0),
(989, '', 25, 29, 0),
(990, '', 25, 30, 0),
(991, '', 25, 31, 0),
(992, '', 25, 32, 0),
(993, '', 25, 33, 0),
(994, '', 25, 34, 0),
(995, '', 25, 35, 0),
(996, '', 25, 36, 0),
(997, '', 25, 37, 0),
(998, '', 25, 38, 0),
(999, '', 25, 39, 0),
(1000, '', 25, 40, 0),
(1001, '', 1, 41, 1),
(1002, '', 2, 41, 1),
(1003, '', 3, 41, 0),
(1004, '', 4, 41, 0),
(1005, '', 5, 41, 0),
(1006, '', 6, 41, 0),
(1007, '', 7, 41, 0),
(1008, '', 8, 41, 0),
(1009, '', 9, 41, 0),
(1010, '', 10, 41, 0),
(1011, '', 11, 41, 0),
(1012, '', 12, 41, 0),
(1013, '', 13, 41, 0),
(1014, '', 14, 41, 0),
(1015, '', 15, 41, 0),
(1016, '', 16, 41, 0),
(1017, '', 17, 41, 0),
(1018, '', 18, 41, 0),
(1019, '', 19, 41, 0),
(1020, '', 20, 41, 0),
(1021, '', 21, 41, 0),
(1022, '', 22, 41, 0),
(1023, '', 23, 41, 0),
(1024, '', 24, 41, 0),
(1025, '', 25, 41, 0),
(1026, '', 1, 42, 1),
(1027, '', 1, 43, 1);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_rt_ouverture`
--

CREATE TABLE `wbcc_rt_ouverture` (
  `idRtOuverture` int(11) NOT NULL,
  `numeroRtOuverture` varchar(50) DEFAULT NULL,
  `idRtPieceSupportF` int(11) DEFAULT NULL,
  `numeroRtPieceSupportF` varchar(50) DEFAULT NULL,
  `nomOuverture` varchar(255) DEFAULT NULL,
  `libelleOuverture` varchar(255) DEFAULT NULL,
  `largeurOuverture` varchar(10) DEFAULT NULL,
  `longueurOuverture` varchar(10) DEFAULT NULL,
  `surfaceOuverture` varchar(50) DEFAULT NULL,
  `commentaireOuverture` text DEFAULT NULL,
  `etatRtOuverture` int(11) DEFAULT 1,
  `createDate` varchar(25) DEFAULT current_timestamp(),
  `editDate` varchar(25) DEFAULT current_timestamp(),
  `idUserF` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_rt_piece`
--

CREATE TABLE `wbcc_rt_piece` (
  `idRTPiece` int(11) NOT NULL,
  `numeroRTPiece` varchar(100) DEFAULT NULL,
  `idRTF` int(11) DEFAULT NULL,
  `numeroRTF` varchar(50) DEFAULT NULL,
  `numeroPieceF` varchar(50) DEFAULT NULL,
  `idPieceF` int(11) DEFAULT NULL,
  `nomPiece` varchar(255) DEFAULT NULL,
  `libellePiece` varchar(255) DEFAULT NULL,
  `commentairePiece` text DEFAULT NULL,
  `photosPiece` text DEFAULT NULL,
  `commentsPhotosPiece` text DEFAULT NULL,
  `videosPiece` text DEFAULT NULL,
  `commentsVideosPiece` text DEFAULT NULL,
  `nbMurs` varchar(11) DEFAULT NULL,
  `nbMursSinistres` varchar(11) DEFAULT NULL,
  `nbMursNonSinistres` varchar(11) DEFAULT NULL,
  `etatRtPiece` int(11) DEFAULT 1,
  `createDate` varchar(25) DEFAULT current_timestamp(),
  `editDate` varchar(25) DEFAULT current_timestamp(),
  `idUserF` int(11) DEFAULT NULL,
  `longueurPiece` varchar(50) DEFAULT '',
  `largeurPiece` varchar(50) DEFAULT '',
  `surfacePiece` varchar(50) DEFAULT NULL,
  `commentaireMetrePiece` text DEFAULT NULL,
  `commentaireSupport` text DEFAULT NULL,
  `tauxHumidite` varchar(10) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_rt_piece`
--

INSERT INTO `wbcc_rt_piece` (`idRTPiece`, `numeroRTPiece`, `idRTF`, `numeroRTF`, `numeroPieceF`, `idPieceF`, `nomPiece`, `libellePiece`, `commentairePiece`, `photosPiece`, `commentsPhotosPiece`, `videosPiece`, `commentsVideosPiece`, `nbMurs`, `nbMursSinistres`, `nbMursNonSinistres`, `etatRtPiece`, `createDate`, `editDate`, `idUserF`, `longueurPiece`, `largeurPiece`, `surfacePiece`, `commentaireMetrePiece`, `commentaireSupport`, `tauxHumidite`) VALUES
(1, 'PIECE2706202412393305', 1, 'RT_270620241237244120', '', 2, 'Cuisine-1', 'Cuisine-1', '', '', '', '', '', '0', '0', '0', 1, '2024-06-27 12:39:33', '2024-06-27 12:39:33', 5, '', '', '', NULL, NULL, '0'),
(2, 'PIECE1207202412200705', 2, 'RT_120720241218554657', '', 2, 'Cuisine-1', 'Cuisine-1', '', '', '', '', '', '0', '0', '0', 1, '2024-07-12 12:20:07', '2024-07-12 12:20:07', 5, '', '', '', NULL, NULL, '0'),
(3, 'PIECE1607202414282905', 3, 'RT_160720240227544713', '', 2, 'Cuisine-1', 'Cuisine-1', '', '', '', '', '', '0', '0', '0', 1, '2024-07-16 14:28:29', '2025-02-05 16:18:10', 5, '', '', '', '', '', '0'),
(4, 'PIECE1607202414382705', 4, 'RT_160720240237544717', '', 4, 'Chambre-1', 'Chambre-1', '', '', '', '', '', '0', '0', '0', 1, '2024-07-16 14:38:27', '2024-07-16 14:38:27', 5, '', '', '', NULL, NULL, '0'),
(5, 'PIECE2912202422263605', 8, 'RT_141020240248044719', '', 4, 'Chambre-1', 'Chambre-1', '', '', '', '', '', '0', '0', '0', 1, '2024-12-29 22:26:36', '2024-12-29 22:26:36', 5, '', '', '', NULL, NULL, '0'),
(6, 'PIECE1001202516085105', 9, NULL, '', 2, 'Cuisine-1', 'Cuisine-1', '', '', '', '', '', '0', '0', '0', 1, '2025-01-10 16:08:51', '2025-01-10 16:08:51', 5, '', '', '', NULL, NULL, '0'),
(7, 'PIECE1601202514001905', 10, NULL, '', 4, 'Chambre-1', 'Chambre-1', '', '', '', '', '', '0', '0', '0', 1, '2025-01-16 14:00:19', '2025-01-16 14:00:19', 5, '', '', '', NULL, NULL, '0');

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_rt_piece_support`
--

CREATE TABLE `wbcc_rt_piece_support` (
  `idRTPieceSupport` int(11) NOT NULL,
  `numeroRTPieceSupport` varchar(50) DEFAULT NULL,
  `idRTPieceF` int(11) DEFAULT NULL,
  `numeroRTPieceF` varchar(50) DEFAULT NULL,
  `idSupportF` int(11) DEFAULT NULL,
  `numeroSupportF` varchar(50) DEFAULT NULL,
  `nomSupport` varchar(255) DEFAULT NULL,
  `libelleSupport` varchar(255) DEFAULT NULL,
  `largeurSupport` varchar(10) DEFAULT NULL,
  `surfaceSupport` varchar(50) DEFAULT NULL,
  `surfaceSupportAtraiter` varchar(50) DEFAULT NULL,
  `longueurSupport` varchar(10) DEFAULT NULL,
  `photosSupport` text DEFAULT NULL,
  `commentsPhotosSupport` text DEFAULT NULL,
  `videosSupport` text DEFAULT NULL,
  `commentsVideosSupport` text DEFAULT NULL,
  `commentaireSupport` text DEFAULT NULL,
  `siOuverture` int(11) DEFAULT 0,
  `nbOuvertures` varchar(10) DEFAULT '0',
  `siDeduire` int(11) DEFAULT 0,
  `etatRtPieceSupport` int(11) DEFAULT 1,
  `createDate` varchar(25) DEFAULT current_timestamp(),
  `editDate` varchar(25) DEFAULT current_timestamp(),
  `idUserF` int(11) DEFAULT NULL,
  `estSinistre` int(11) NOT NULL DEFAULT 0,
  `commentaireMetreSupport` text DEFAULT NULL,
  `commentaireOuvertures` text DEFAULT NULL,
  `tauxHumidite` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_rt_revetement`
--

CREATE TABLE `wbcc_rt_revetement` (
  `idRtRevetement` int(11) NOT NULL,
  `numeroRtRevetement` varchar(50) DEFAULT NULL,
  `idRevetementF` int(11) DEFAULT NULL,
  `idRtPieceSupportF` int(11) DEFAULT NULL,
  `numeroRtPieceSupportF` varchar(50) DEFAULT NULL,
  `idRtOuvertureF` int(11) DEFAULT NULL,
  `numeroOuvertureF` varchar(50) DEFAULT NULL,
  `nomRevetement` varchar(255) DEFAULT NULL,
  `libelleRevetement` varchar(255) DEFAULT NULL,
  `largeurRevetement` varchar(255) DEFAULT NULL,
  `longueurRevetement` varchar(255) DEFAULT NULL,
  `surfaceRevetement` varchar(25) DEFAULT NULL,
  `surfaceOuvertureRevetement` varchar(25) DEFAULT NULL,
  `surfaceATraiterRevetement` varchar(25) DEFAULT NULL,
  `commentaireRevetement` text DEFAULT NULL,
  `siATraiterRevetement` int(11) DEFAULT 1,
  `longueurAChanger` varchar(50) DEFAULT NULL,
  `largeurAChanger` varchar(50) DEFAULT NULL,
  `surfaceAChanger` varchar(50) DEFAULT NULL,
  `siOuvertureRevetement` int(11) DEFAULT 0,
  `siDeduireRevetement` int(11) DEFAULT 0,
  `siPeintureSatineMate` varchar(50) DEFAULT NULL,
  `nbMLCanalisation` int(10) DEFAULT NULL,
  `qualiteRevetement` varchar(50) DEFAULT NULL,
  `typeOssature` varchar(100) DEFAULT NULL,
  `typeLambris` varchar(100) DEFAULT NULL,
  `typePlafond` varchar(100) DEFAULT NULL,
  `siSurfaceADemolir` varchar(10) DEFAULT NULL,
  `siIsolant` varchar(10) DEFAULT NULL,
  `typeIsolant` varchar(100) DEFAULT NULL,
  `siConserveStructurePlaque` varchar(10) DEFAULT NULL,
  `typePeinture` varchar(100) DEFAULT NULL,
  `siCanalisation` varchar(10) DEFAULT NULL,
  `siPlafondAChanger` varchar(10) DEFAULT NULL,
  `typeMateriauPlafond` varchar(100) DEFAULT NULL,
  `siParquetAChanger` varchar(10) DEFAULT NULL,
  `typeParquet` varchar(100) DEFAULT NULL,
  `siParquetMassifColle` varchar(10) DEFAULT NULL,
  `typePoncageParquet` varchar(100) DEFAULT NULL,
  `typeMoquette` varchar(100) DEFAULT NULL,
  `typeRouleau` varchar(100) DEFAULT NULL,
  `longueurDalle` varchar(10) DEFAULT NULL,
  `largeurDalle` varchar(10) DEFAULT NULL,
  `siAncienneMoquette` varchar(10) DEFAULT NULL,
  `siChangerAncienneMoquette` varchar(10) DEFAULT NULL,
  `siChangerParquetSousMoquette` varchar(10) DEFAULT NULL,
  `siPrevoirPlinthe` varchar(10) DEFAULT NULL,
  `nbMLPlinthe` varchar(10) DEFAULT NULL,
  `siBarreSeuil` varchar(10) DEFAULT NULL,
  `nbBarreSeuil` varchar(10) DEFAULT NULL,
  `typeBarreSeuil` varchar(100) DEFAULT NULL,
  `siChape` varchar(10) DEFAULT NULL,
  `longueurChape` varchar(10) DEFAULT NULL,
  `largeurChape` varchar(10) DEFAULT NULL,
  `epaisseurChape` varchar(10) DEFAULT NULL,
  `siReagreage` varchar(10) DEFAULT NULL,
  `longueurReagreage` varchar(10) DEFAULT NULL,
  `largeurReagreage` varchar(10) DEFAULT NULL,
  `epaisseurReagreage` varchar(10) DEFAULT NULL,
  `etatRtRevetement` int(11) DEFAULT 1,
  `createDate` varchar(25) DEFAULT current_timestamp(),
  `editDate` varchar(25) DEFAULT current_timestamp(),
  `idUserF` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_rt_revetement_ouverture`
--

CREATE TABLE `wbcc_rt_revetement_ouverture` (
  `idRtRevetementOuverture` int(11) NOT NULL,
  `numeroRtRevetementOuverture` varchar(50) DEFAULT NULL,
  `idRevetementF` int(11) DEFAULT NULL,
  `idOuvertureF` int(11) DEFAULT NULL,
  `libelleRevetementOuverture` varchar(255) DEFAULT NULL,
  `nomRevetementOuverture` varchar(255) DEFAULT NULL,
  `longueurRevetementOuverture` varchar(25) DEFAULT NULL,
  `largeurRevetementOuverture` varchar(25) DEFAULT NULL,
  `surfaceRevetementOuverture` varchar(25) DEFAULT NULL,
  `commentaireRevetementOuverture` text DEFAULT NULL,
  `etatRevetementOuverture` int(11) DEFAULT 1,
  `createDate` varchar(25) DEFAULT NULL,
  `editDate` varchar(25) DEFAULT NULL,
  `idUserF` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_section`
--

CREATE TABLE `wbcc_section` (
  `idSection` int(11) NOT NULL,
  `titreSection` varchar(100) DEFAULT NULL,
  `numeroSection` varchar(255) DEFAULT NULL,
  `contenuSection` text DEFAULT NULL,
  `idSommaireF` int(11) DEFAULT NULL,
  `idSection_parentF` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `wbcc_section`
--

INSERT INTO `wbcc_section` (`idSection`, `titreSection`, `numeroSection`, `contenuSection`, `idSommaireF`, `idSection_parentF`) VALUES
(89, 'SANTE', '5', '<p>Nous vous souhaitons une sant de fer afin de pouvoir realiser vos <strong>projets</strong>&nbsp;</p>', 18, NULL),
(90, 'PROSPERITE', '3', '', 18, NULL),
(91, 'REUSSITE', '2', '', 18, NULL),
(92, 'JOIE', '4', '', 18, NULL),
(95, 'section2', '2', '', 26, NULL),
(96, 'section3', '2', '', 26, NULL),
(99, 'AVATAR', '2', '<p>PARTIR POUR LES FETES</p>', 36, NULL),
(100, 'fgsdgs', '1', '<p>reste</p>', 36, NULL),
(101, 'WALLY VOGUE', '3', '<p>teste pour WALLLLLYYYYYYYYYYYYYYYYYYYYYYYY</p>', 36, NULL),
(102, 'wbcc', '4', '<p><span style=\"color: #444444; font-family: \'Open Sans\', sans-serif; font-size: 16px; text-align: justify; background-color: #f8f9fc;\">Collaborer avec WBCC ASSISTANCE offre plusieurs <strong>avantages</strong></span></p>', 36, NULL),
(103, 'AVATAR', '1.1', '', 36, 100),
(104, 'Elle chante', '1.1.2', '', 36, 103),
(105, 'SECTION', '2.2', '', 36, 100),
(106, 'testing', '2.2', '', 36, 101),
(107, 'VOGUE', '3.2', '', 36, 101),
(108, 'bruno', '3.2', '<h1 class=\"post-title\" style=\"padding: 0px; margin: 0px 0px 0.5rem; outline: none; list-style: none; border: 0px; box-sizing: border-box; border-radius: 10px; background: rgba(0, 0, 0, 0.05); line-height: 2.5; font-size: 24px; font-family: Cairo, sans-serif; letter-spacing: 0.5px;\">يلا شوت الجديد &ndash; بث مباشر لمباريات اليوم بدون تقطيع</h1>\n<p>ed</p>', 36, 102),
(109, 'cabinet', '4.1.1', '', 36, 108),
(110, 'test', '4.1.1.1', '', 36, 109),
(111, 'retrait', '4.1.1.2', '', 36, 109),
(112, 'go look', '4.1.1.2.1', '', 36, 111),
(113, 'AVATAR', '1', '<p>PARTIR POUR LES FETES</p>', 37, NULL),
(114, 'fgsdgs', '2', '<p>reste</p>', 37, NULL),
(117, 'AVATAR', '2.1', '', 37, 114),
(118, 'Elle chante', '2.1.1', '<h2 id=\"ID0EDH\" class=\"\" style=\"font-size: 3em; color: #1e1e1e; margin-bottom: 20px; padding-bottom: 0px; font-weight: 300; box-sizing: border-box; font-family: \'Segoe UI Light\', wf_segoe-ui_light, Arial, \'Helvetica Neue\', Verdana, Helvetica, sans-serif; line-height: 1.33; margin-top: 48px; background-color: #ffffff;\">M&eacute;thode 1 : utiliser rand()&nbsp;</h2>\n<p style=\"font-size: 1.6em; box-sizing: border-box; color: #1e1e1e; font-family: \'Segoe UI\', \'Segoe UI Web\', wf_segoe-ui_normal, \'Helvetica Neue\', \'BBAlpha Sans\', \'S60 Sans\', Arial, sans-serif; line-height: 1.5; padding: 0px; background-color: #ffffff;\">Pour ins&eacute;rer un exemple de texte localis&eacute; dans Word, tapez&nbsp;<span class=\"ocpUI\" style=\"font-weight: bold; box-sizing: border-box;\">=rand()</span>&nbsp;dans le document o&ugrave; vous souhaitez que le texte apparaisse, puis&nbsp;<span class=\"ocpUI\" style=\"font-weight: bold; box-sizing: border-box;\">appuyez sur Entr&eacute;e</span>.&nbsp;&nbsp;<br style=\"box-sizing: border-box;\" aria-hidden=\"true\" />L&rsquo;exemple de texte ins&eacute;r&eacute; pour la version anglaise de Word ressemble au texte suivant :&nbsp;</p>\n<pre style=\"text-wrap-mode: wrap; box-sizing: border-box;\">Video provides a powerful way to help you prove your point. When you click Online Video, you can paste in the embed code for the video you want to add. You can also type a keyword to search online for the video that best fits your document.\n\nTo make your document look professionally produced, Word provides header, footer, cover page, and text box designs that complement each other. For example, you can add a matching cover page, header, and sidebar. Click Insert and then choose the elements you want from the different galleries.\n\nThemes and styles also help keep your document coordinated. When you click Design and choose a new Theme, the pictures, charts, and SmartArt graphics change to match your new theme. When you apply styles, your headings change to match the new theme.\n</pre>\n<p style=\"font-size: 1.6em; box-sizing: border-box; color: #1e1e1e; font-family: \'Segoe UI\', \'Segoe UI Web\', wf_segoe-ui_normal, \'Helvetica Neue\', \'BBAlpha Sans\', \'S60 Sans\', Arial, sans-serif; line-height: 1.5; padding: 0px; background-color: #ffffff;\">Vous pouvez contr&ocirc;ler le nombre de paragraphes et de lignes qui apparaissent en ajoutant des nombres entre parenth&egrave;ses de la fonction rand(). La fonction =rand() a la syntaxe suivante :&nbsp;</p>\n<p><code class=\"ocpCode\" style=\"box-sizing: border-box; font-family: \'Courier New\', Courier, monospace; font-size: 1.4em; line-height: 1.3em; overflow-wrap: break-word; color: #363636; background-color: #ffffff;\"></code></p>\n<p style=\"font-size: 1.6em; box-sizing: border-box; color: #1e1e1e; font-family: \'Segoe UI\', \'Segoe UI Web\', wf_segoe-ui_normal, \'Helvetica Neue\', \'BBAlpha Sans\', \'S60 Sans\', Arial, sans-serif; line-height: 1.5; padding: 0px; background-color: #ffffff;\">=rand(p,l)<br /><br /></p>\n<section class=\"ocpSection\" style=\"box-sizing: border-box; color: #363636; font-family: \'Segoe UI\', \'Segoe UI Web\', wf_segoe-ui_normal, \'Helvetica Neue\', \'BBAlpha Sans\', \'S60 Sans\', Arial, sans-serif; font-size: 10px; background-color: #ffffff;\" aria-labelledby=\"ID0EDH\">\n<div class=\"ocpAlert\" style=\"box-sizing: border-box; background: #f3f3f3; margin-top: 16px; margin-bottom: 16px; padding: 6px 61.4375px 6px 26.875px;\">\n<p class=\"ocpAlertSection\" style=\"font-size: 14px; box-sizing: border-box; color: #1e1e1e; line-height: 1.5; padding: 0px;\"><span class=\"ocpNote\" style=\"font-weight: bold; box-sizing: border-box; margin: 20px 0px 5px; font-size: 1em; line-height: 1.5;\">Remarque&nbsp;:&nbsp;</span>Dans cette fonction, p est le nombre de paragraphes, et l est le nombre de lignes que vous souhaitez afficher dans chaque paragraphe.&nbsp;</p>\n</div>\n<p style=\"font-size: 1.6em; box-sizing: border-box; color: #1e1e1e; line-height: 1.5; padding: 0px;\">Les param&egrave;tres sont facultatifs. Si vous omettez les param&egrave;tres, le nombre de paragraphes par d&eacute;faut est de trois, et le nombre par d&eacute;faut de lignes par paragraphe est &eacute;galement de trois.&nbsp;</p>\n<p style=\"font-size: 1.6em; box-sizing: border-box; color: #1e1e1e; line-height: 1.5; padding: 0px;\">Voici quelques exemples qui montrent le fonctionnement des param&egrave;tres :&nbsp;</p>\n<p style=\"font-size: 1.6em; box-sizing: border-box; color: #1e1e1e; line-height: 1.5; padding: 0px;\">=rand(1) ins&egrave;re un paragraphe avec trois lignes de texte dans le paragraphe.&nbsp;</p>\n<p style=\"font-size: 1.6em; box-sizing: border-box; color: #1e1e1e; line-height: 1.5; padding: 0px;\">=rand(10,10) ins&egrave;re 10 paragraphes avec 10 lignes de texte dans chaque paragraphe.&nbsp;</p>\n</section>\n<section class=\"ocpSection\" style=\"box-sizing: border-box; color: #363636; font-family: \'Segoe UI\', \'Segoe UI Web\', wf_segoe-ui_normal, \'Helvetica Neue\', \'BBAlpha Sans\', \'S60 Sans\', Arial, sans-serif; font-size: 10px; background-color: #ffffff;\" aria-labelledby=\"ID0EDF\">\n<h2 id=\"ID0EDF\" class=\"\" style=\"font-size: 3em; color: #1e1e1e; margin-bottom: 20px; padding-bottom: 0px; font-weight: 300; box-sizing: border-box; font-family: \'Segoe UI Light\', wf_segoe-ui_light, Arial, \'Helvetica Neue\', Verdana, Helvetica, sans-serif; line-height: 1.33; margin-top: 48px;\">M&eacute;thode 2 : Utiliser lorem()&nbsp;</h2>\n<p style=\"font-size: 1.6em; box-sizing: border-box; color: #1e1e1e; line-height: 1.5; padding: 0px;\">Pour ins&eacute;rer un exemple de texte pseudo-latin non localis&eacute; dans Word, tapez =lorem() dans le document o&ugrave; vous souhaitez que le texte apparaisse, puis&nbsp;<span class=\"ocpUI\" style=\"font-weight: bold; box-sizing: border-box;\">appuyez sur Entr&eacute;e</span>.&nbsp;<br style=\"box-sizing: border-box;\" aria-hidden=\"true\" />&nbsp;<br style=\"box-sizing: border-box;\" aria-hidden=\"true\" />L&rsquo;exemple de texte ins&eacute;r&eacute; ressemble au texte suivant :&nbsp;</p>\n<code class=\"ocpCode\" style=\"box-sizing: border-box; font-family: \'Courier New\', Courier, monospace; font-size: 1.4em; line-height: 1.3em; overflow-wrap: break-word;\"><code class=\"ocpCode\" style=\"box-sizing: border-box; font-family: \'Courier New\', Courier, monospace; font-size: 1.4em; line-height: 1.3em; overflow-wrap: break-word;\"></code></code>\n<pre style=\"text-wrap-mode: wrap; box-sizing: border-box;\">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Maecenas porttitor congue massa. Fusce posuere, magna sed pulvinar ultricies, purus lectus malesuada libero, sit amet commodo magna eros quis urna.&para; \n \nNunc viverra imperdiet enim. Fusce est. Vivamus a tellus.&para; \n \nPellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin pharetra nonummy pede. Mauris et orci.&para; </pre>\n<p style=\"font-size: 1.6em; box-sizing: border-box; color: #1e1e1e; line-height: 1.5; padding: 0px;\">Vous pouvez contr&ocirc;ler le nombre de paragraphes et de lignes affich&eacute;s en ajoutant des nombres entre parenth&egrave;ses de la fonction lorem(). La fonction =lorem() a la syntaxe suivante : lorem(p,l)&nbsp;</p>\n<div class=\"ocpAlert\" style=\"box-sizing: border-box; background: #f3f3f3; margin-top: 16px; margin-bottom: 16px; padding: 6px 61.4375px 6px 26.875px;\">\n<p class=\"ocpAlertSection\" style=\"font-size: 14px; box-sizing: border-box; color: #1e1e1e; line-height: 1.5; padding: 0px;\"><span class=\"ocpNote\" style=\"font-weight: bold; box-sizing: border-box; margin: 20px 0px 5px; font-size: 1em; line-height: 1.5;\">Remarque&nbsp;:&nbsp;</span>Dans cette fonction, p est le nombre de paragraphes, et l est le nombre de lignes que vous souhaitez afficher dans chaque paragraphe.&nbsp;</p>\n</div>\n<p style=\"font-size: 1.6em; box-sizing: border-box; color: #1e1e1e; line-height: 1.5; padding: 0px;\"><br style=\"box-sizing: border-box;\" aria-hidden=\"true\" />Les param&egrave;tres sont facultatifs. Si vous omettez les param&egrave;tres, le nombre de paragraphes par d&eacute;faut est de trois, et le nombre par d&eacute;faut de lignes par paragraphe est &eacute;galement de trois.&nbsp;<br style=\"box-sizing: border-box;\" aria-hidden=\"true\" />&nbsp;<br style=\"box-sizing: border-box;\" aria-hidden=\"true\" />Pour plus d&rsquo;informations sur le texte &laquo; Lorem ipsum &raquo;, consultez&nbsp;<a class=\"ocpArticleLink\" style=\"color: #006cb4; text-decoration-line: none; box-sizing: border-box;\" href=\"https://support.microsoft.com/fr-fr/topic/description-du-texte-lorem-ipsum-dolor-sit-amet-qui-appara%C3%AEt-dans-l-aide-word-bf3b0a9e-8f6b-c2ab-edd9-41c1f9aa2ea0\" data-bi-type=\"anchor\">Description du texte &laquo; Lorem ipsum dolor sit amet &raquo; qui appara&icirc;t dans Word aide.</a></p>\n</section>\n<section class=\"ocpSection\" style=\"box-sizing: border-box; color: #363636; font-family: \'Segoe UI\', \'Segoe UI Web\', wf_segoe-ui_normal, \'Helvetica Neue\', \'BBAlpha Sans\', \'S60 Sans\', Arial, sans-serif; font-size: 10px; background-color: #ffffff;\" aria-labelledby=\"ID0EDD\">\n<h2 id=\"ID0EDD\" class=\"\" style=\"font-size: 3em; color: #1e1e1e; margin-bottom: 20px; padding-bottom: 0px; font-weight: 300; box-sizing: border-box; font-family: \'Segoe UI Light\', wf_segoe-ui_light, Arial, \'Helvetica Neue\', Verdana, Helvetica, sans-serif; line-height: 1.33; margin-top: 48px;\">Vous ne fonctionnez pas ?</h2>\n<p style=\"font-size: 1.6em; box-sizing: border-box; color: #1e1e1e; line-height: 1.5; padding: 0px;\">Par d&eacute;faut, la fonctionnalit&eacute; d&rsquo;insertion de l&rsquo;exemple de texte dans Word est activ&eacute;e. Toutefois, la fonctionnalit&eacute; d&rsquo;insertion de l&rsquo;exemple de texte est d&eacute;sactiv&eacute;e lorsque l&rsquo;option Remplacer le texte en cours de frappe est d&eacute;sactiv&eacute;e.&nbsp;<br style=\"box-sizing: border-box;\" aria-hidden=\"true\" />&nbsp;<br style=\"box-sizing: border-box;\" aria-hidden=\"true\" />Pour activer ou d&eacute;sactiver l&rsquo;option Remplacer le texte &agrave; mesure que vous tapez, proc&eacute;dez comme suit :&nbsp;</p>\n<p style=\"font-size: 1.6em; box-sizing: border-box; color: #1e1e1e; line-height: 1.5; padding: 0px;\">S&eacute;lectionnez&nbsp;<span class=\"ocpUI\" style=\"font-weight: bold; box-sizing: border-box;\">Bouton Office</span>,&nbsp;<span class=\"ocpUI\" style=\"font-weight: bold; box-sizing: border-box;\">Word Options</span>,&nbsp;<span class=\"ocpUI\" style=\"font-weight: bold; box-sizing: border-box;\">V&eacute;rification</span>, puis&nbsp;<span class=\"ocpUI\" style=\"font-weight: bold; box-sizing: border-box;\">Options de correction automatique</span>.&nbsp;</p>\n<p style=\"font-size: 1.6em; box-sizing: border-box; color: #1e1e1e; line-height: 1.5; padding: 0px;\">Cliquez pour s&eacute;lectionner ou effacer la zone&nbsp;<span class=\"ocpUI\" style=\"font-weight: bold; box-sizing: border-box;\">Remplacer le texte &agrave; mesure que vous tapez</span>&nbsp;case activ&eacute;e.</p>\n<p style=\"font-size: 1.6em; box-sizing: border-box; color: #1e1e1e; line-height: 1.5; padding: 0px;\">&nbsp;</p>\n<table style=\"border-collapse: collapse; width: 100%; height: 28px; background-color: #ced4d9; border-color: #3598DB;\" border=\"1\">\n<tbody>\n<tr style=\"height: 14px;\">\n<td style=\"width: 20.0203%; text-align: center; height: 14px;\">TESTE</td>\n<td style=\"width: 20.0203%; text-align: center; height: 14px;\">RESTE</td>\n<td style=\"width: 20.0203%; text-align: center; height: 14px;\">PESTE</td>\n<td style=\"width: 20.0203%; text-align: center; height: 14px;\">GESTE</td>\n<td style=\"width: 20.0203%; text-align: center; height: 14px;\">EST</td>\n</tr>\n<tr style=\"height: 14px;\">\n<td style=\"width: 20.0203%; text-align: center; height: 14px;\">ceci est un teste</td>\n<td style=\"width: 20.0203%; text-align: center; height: 14px;\">voici ce qui reste</td>\n<td style=\"width: 20.0203%; text-align: center; height: 14px;\">la peste est une maladie</td>\n<td style=\"width: 20.0203%; text-align: center; height: 14px;\">ce geste est a saluer</td>\n<td style=\"width: 20.0203%; text-align: center; height: 14px;\">je vais a l\'est du SENEGAL</td>\n</tr>\n</tbody>\n</table>\n</section>', 37, 117),
(186, 'teste', '1', '', 18, NULL),
(209, 'Lunix', '1.4', '', 1, 284),
(211, 'peste', '2', '', 1, 211),
(212, 'WI-FI', '7', '', 1, NULL),
(213, 'l\'est', '2.2', '', 1, 211),
(227, 'ttrtr', '10', '', 41, NULL),
(228, 'teste', '3', '', 41, NULL),
(229, 'test', '4', '', 41, NULL),
(230, 'reste', '5', '', 41, NULL),
(231, 'peste', '6', '', 41, NULL),
(232, 'veste', '7', '', 41, NULL),
(233, 'l\'est', '8', '', 41, NULL),
(234, 'teste', '4', '', 37, NULL),
(235, 'teste 3', '5', '', 37, NULL),
(236, 'reste', '6', '', 37, NULL),
(237, 'peste', '7', '', 37, NULL),
(242, 'teste', '4.1', '', 37, 234),
(243, 'reste', '5.1', '', 37, 235),
(244, 'teste', '8', '', 37, NULL),
(247, 'teste section', '2.2', '', 37, 114),
(250, 'cause', '1.1', '', 37, 113),
(259, 'teste', '1.1', '', 26, 95),
(260, 'teste 1', '1.2', '', 26, 95),
(261, 'teste 3', '1.1.1', '', 26, 259),
(262, 'teste', '2.3', '', 36, 100),
(263, 'veste', '2.4', '', 36, 100),
(264, 'caste', '1.1.2', '<p>100</p>', 36, 103),
(265, 'teste', '2.1.1.1', '', 36, 104),
(266, 'teste', '1.1.2.2', '', 36, NULL),
(267, 'teste', '2.1.2.1.1', '', 36, 266),
(268, 'teste', '2.2.1', '', 36, 105),
(269, 'teste', '2.2.2', '', 36, 105),
(270, 'teste', '2.2.2.1', '', 36, 269),
(271, 'teste', '2.2.2.2', '', 36, 269),
(272, 'teste', '2.2.2.3', '', 36, 269),
(273, 'trt', '2.2.2.1.1', '', 36, 270),
(274, 'test', '2.4.1', '', 36, 263),
(275, 'macOS', '6', '', 1, NULL),
(276, 'Unix', '1.2', '', 1, 284),
(277, 'Réseaux et communication', '3', '', 1, NULL),
(281, 'goooo', '2.1', '', 1, 211),
(282, 'Ios', '1.3', '', 1, 284),
(283, 'LAN/WAN', '9', '', 1, NULL),
(284, 'Systèmes d’exploitation', '1', '', 1, NULL),
(285, 'Windows', '1.1', '', 1, 284),
(287, 'Android', '2', '', 1, NULL),
(288, 'HTTP/HTTPS', '5', '', 1, NULL),
(289, 'DNS', '4', '', 1, NULL),
(290, 'Langage de Programmation', '12', '', 1, NULL),
(291, 'Python', '12.1', '', 1, 290),
(292, 'Java', '12.2', '', 1, 290),
(293, 'Java FX', '12.2.1', '', 1, 292),
(294, 'JEE', '12.2.2', '', 1, 292),
(295, 'LAN', '8.1', '', 1, 283),
(296, 'WAN', '9.2', '', 1, 283),
(321, 'partie', '1', '<p>This</p>', 42, NULL),
(322, 'part', '1.1', '<p>Helvetica</p>', 42, 321);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_section_document`
--

CREATE TABLE `wbcc_section_document` (
  `idSectionDocument` int(11) NOT NULL,
  `idSectionF` int(11) NOT NULL,
  `idDocumentF` int(11) NOT NULL,
  `numeroDocument` varchar(50) DEFAULT NULL,
  `dateCreation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `wbcc_section_document`
--

INSERT INTO `wbcc_section_document` (`idSectionDocument`, `idSectionF`, `idDocumentF`, `numeroDocument`, `dateCreation`) VALUES
(71, 118, 35, 'DOC_1_677f920e7f8984.60453351', '2025-01-09 10:08:30'),
(72, 118, 33, 'DOC_1_677eab6840bc70.43187556', '2025-01-09 13:05:00'),
(73, 113, 31, 'DOC_1_677e63ad269dd5.50336787', '2025-01-09 14:37:40'),
(74, 117, 36, 'DOC_1_677fd3e78f3622.34032679', '2025-01-09 14:49:27'),
(76, 118, 36, 'DOC_1_677fd3e78f3622.34032679', '2025-01-09 17:27:05'),
(78, 103, 36, 'DOC_1_677fd3e78f3622.34032679', '2025-01-13 15:20:36'),
(79, 103, 35, 'DOC_1_677f920e7f8984.60453351', '2025-01-13 15:20:36'),
(80, 211, 36, 'DOC_1_677fd3e78f3622.34032679', '2025-01-13 15:40:30'),
(89, 277, 41, 'DOC_1_678643688a9730.63080052', '2025-01-14 17:50:47'),
(99, 284, 49, 'DOC_1_6787a9741a3d57.05013796', '2025-01-15 13:26:28'),
(100, 321, 50, 'DOC_1_6787aa9ae00620.46173455', '2025-01-15 13:31:22'),
(101, 321, 51, 'DOC_1_6787ab33f2f961.13120993', '2025-01-15 13:33:56'),
(102, 321, 52, 'DOC_1_6787ab5b67b5c5.91116778', '2025-01-15 13:34:35'),
(103, 321, 53, 'DOC_1_6787ac78d1a3d8.48732509', '2025-01-15 13:39:20'),
(104, 321, 54, 'DOC_1_6787ac823373f3.53492997', '2025-01-15 13:39:30'),
(105, 321, 55, 'DOC_1_6787ac9c642622.45507067', '2025-01-15 13:39:56'),
(106, 321, 56, 'DOC_1_6787b9d6eb15a1.60666755', '2025-01-15 14:36:22'),
(107, 321, 57, 'DOC_1_6787b9edab9287.45267667', '2025-01-15 14:36:45'),
(108, 321, 58, 'DOC_1_6788fa3450ba04.54180416', '2025-01-16 13:23:16');

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_site`
--

CREATE TABLE `wbcc_site` (
  `idSite` int(255) NOT NULL,
  `nomSite` varchar(256) NOT NULL,
  `etatSite` int(255) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_sommaire`
--

CREATE TABLE `wbcc_sommaire` (
  `idSommaire` int(11) NOT NULL,
  `numeroSommaire` varchar(255) DEFAULT NULL,
  `titreSommaire` varchar(100) DEFAULT NULL,
  `idProjetF` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `wbcc_sommaire`
--

INSERT INTO `wbcc_sommaire` (`idSommaire`, `numeroSommaire`, `titreSommaire`, `idProjetF`) VALUES
(1, '1', 'testeSommaire', 29),
(2, '1', 'testeSommaire', 30),
(18, '1', 'VOEUX', 49),
(26, '1', 'tetstt', 56),
(34, '1', 'REAL', 62),
(36, '1', 'MADRID', 63),
(37, '1', 'fall', 64),
(41, '1', 'youpi', 65),
(42, '1', 'ProjetSommaire', 66);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_sous_module`
--

CREATE TABLE `wbcc_sous_module` (
  `idSousModule` int(11) NOT NULL,
  `nomSousModule` varchar(255) DEFAULT NULL,
  `numeroSousModule` varchar(50) DEFAULT NULL,
  `controller` varchar(150) DEFAULT NULL,
  `function` varchar(150) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `etatSousModule` int(11) DEFAULT 1,
  `idModuleF` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_sous_module`
--

INSERT INTO `wbcc_sous_module` (`idSousModule`, `nomSousModule`, `numeroSousModule`, `controller`, `function`, `icon`, `etatSousModule`, `idModuleF`) VALUES
(1, 'Opportunités', NULL, 'Gestionnaire', 'indexOpportunite', 'fas fa-fw fa-folder', 1, 1),
(2, 'Gestion Ticket', NULL, 'Ticket', 'index', 'fas fa-fw fa-envelope', 1, 1),
(3, 'Tableau de Bord', NULL, 'GestionnaireExterne', 'tbdOpportunite', 'fas fa-fw fa-chart-line', 1, 1),
(4, 'Anomalies', NULL, 'GestionnaireExterne', 'indexOpportunite', 'fas fa-fw fa-times-circle', 1, 1),
(5, 'Audit', NULL, 'Gestionnaire', 'indexAudit', 'fa fa-solid fa-search', 1, 1),
(6, 'Liste des Tâches', NULL, 'Gestionnaire', 'indexTache', 'fas fa-fw fa-file-alt', 1, 1),
(7, 'Contacts', NULL, 'Gestionnaire', 'indexContact', 'fas fa-fw fa-address-book', 1, 1),
(8, 'Sociétés', NULL, 'Gestionnaire', 'indexSociete', 'fas fa-fw fa-briefcase', 1, 1),
(9, 'Immeuble', NULL, 'Gestionnaire', 'indexImmeuble', 'fas fa-fw fa-building', 1, 1),
(10, 'Appartement', NULL, 'Gestionnaire', 'indexAppartement', 'fas fa-fw fa-warehouse', 1, 1),
(11, 'Gestion Equipement', NULL, 'GestionInterne', 'indexEquipement', 'fa fa-solid fa-warehouse', 1, 2),
(12, 'Gestion Site', NULL, 'GestionInterne', 'indexSite', 'fa fa-solid fa-warehouse', 1, 2),
(13, 'Gestion Artisan', NULL, 'GestionInterne', 'indexArtisan', 'fa fa-solid fa-users', 1, 2),
(14, ' Gestion Subvention', NULL, 'GestionInterne', 'indexSubvention', 'fa fa-solid fa-euro-sign', 1, 2),
(15, 'Recrutement', NULL, 'Recrutement', 'index', 'fa fa-solid fa-users', 1, 2),
(16, 'Gestion Personnel', NULL, 'GestionInterne', 'acceuiladmin', 'fa fa-solid fa-user', 1, 2),
(17, 'Rendez-Vous', NULL, 'RendezVous', 'index/expert', 'fas fa-fw fa-calendar', 1, 3),
(18, 'Rendez-Vous', NULL, 'RendezVous', 'index/artisan', 'fas fa-fw fa-calendar', 1, 4),
(19, 'Proposition Commerciale', NULL, 'VRP', 'index', 'fa fa-solid fa-handshake', 1, 5),
(20, 'Gestion DO', NULL, 'DonneurOrdre', 'index', 'fas fa-fw fa-users', 1, 1),
(21, 'Gestion Copro', NULL, 'Copro', 'index', 'fas fa-fw fa-users', 1, 1),
(22, 'Règlements', NULL, 'Comptable', 'listeReglement', 'fa fa-solid fa-euro-sign', 1, 6),
(23, 'Encaissements', NULL, 'Comptable', 'indexEncaissement', 'fa fa-solid fa-euro-sign', 1, 6),
(24, 'Enveloppes', NULL, 'Comptable', 'indexEnveloppe', 'fa fa-envelope', 1, 6),
(25, 'Chèques', NULL, 'Comptable', 'indexCheque', 'fa fa-solid fa-money-check', 1, 6),
(26, 'Liste des Tâches', NULL, 'Comptable', 'indexTache', 'fa fa-list', 1, 6),
(27, 'Dossier', NULL, 'Copro', 'indexDossier', 'fas fa-fw fa-folder', 1, 7),
(28, 'Lot', NULL, 'Copro', 'indexLot', 'fas fa-fw fa-warehouse', 1, 7),
(29, 'Dossier', NULL, 'Occupant', 'indexOccupant', 'fas fa-fw fa-folder', 1, 8),
(30, 'Déclarer un sinistre', NULL, 'Sinistre', 'declaration', 'fas fa-house-damage', 1, 9),
(31, 'Mes sinistres', NULL, 'Sinistre', 'index', 'fas fa-folder', 1, 9),
(32, 'Espace', NULL, 'Espace', 'index', 'fas fa-fw fa-home', 1, 10),
(33, 'Dossier', NULL, 'Dossier', 'index', 'fas fa-fw fa-folder', 1, 10),
(34, 'Immeuble', NULL, 'Immeuble', 'index', 'fas fa-fw fa-building', 1, 10),
(35, 'Lot', NULL, 'Lot', 'index', 'fas fa-fw fa-warehouse', 1, 10),
(36, 'Signature', NULL, 'Signature', 'index', 'fas fa-fw fa-file-signature', 1, 10),
(37, 'Personnel', NULL, 'Personnel', 'index', 'fas fa-fw fa-users', 1, 10),
(38, 'Personnel', NULL, 'Utilisateur', 'users', 'fas fa-fw fa-user-tie', 1, 2),
(39, 'Espace', NULL, 'Espace', 'index', 'fas fa-fw fa-home', 1, 4),
(40, 'Personnel', NULL, 'Personnel', 'index', 'fas fa-fw fa-users', 1, 4),
(41, 'Gestion des Rôles', NULL, 'GestionInterne', 'indexRole', 'fa fa-solid fa-warehouse', 1, 2),
(42, 'Gestion Projet', NULL, 'GestionInterne', 'indexProjet', 'fa fa-regular fa-folder-open', 1, 2),
(43, 'Gestion JOUR FERIE', NULL, 'GestionInterne', 'indexJourFerie', 'fa fa-solid fa-warehouse', 1, 2);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_subvention`
--

CREATE TABLE `wbcc_subvention` (
  `idSubvention` int(11) NOT NULL,
  `numeroSubvention` varchar(50) DEFAULT NULL,
  `titreSubvention` varchar(255) DEFAULT NULL,
  `montantSubvention` varchar(50) DEFAULT NULL,
  `taux` int(11) DEFAULT NULL,
  `natureTravaux` varchar(255) DEFAULT NULL,
  `natureAide` varchar(255) DEFAULT NULL,
  `idOrganisme` int(11) DEFAULT NULL,
  `createDate` varchar(25) DEFAULT NULL,
  `editDate` varchar(25) DEFAULT NULL,
  `idAuteur` int(11) DEFAULT NULL,
  `etatSubvention` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_subvention`
--

INSERT INTO `wbcc_subvention` (`idSubvention`, `numeroSubvention`, `titreSubvention`, `montantSubvention`, `taux`, `natureTravaux`, `natureAide`, `idOrganisme`, `createDate`, `editDate`, `idAuteur`, `etatSubvention`) VALUES
(1, 'SUB181220241049261', 'tEST', '1000', 10, 'Collectif', 'Collectif', 1, '2024-12-18 10:49:26', '2024-12-18 10:49:26', 1, 1),
(2, 'SUB191220241355581', 'aze', '1000', 1, 'Collectif', 'Collectif', 1, '2024-12-19 13:55:58', '2024-12-19 13:55:58', 1, 1),
(3, 'SUB191220241409281', 'tEST', '1000', 1, '', '', 1, '2024-12-19 14:09:28', '2024-12-19 14:09:28', 1, 1),
(4, 'SUB191220241412231', 'aze', '1000', 1, '', 'Collectif', 1, '2024-12-19 14:12:23', '2024-12-19 14:12:23', 1, 1),
(5, 'SUB191220241413081', 'aze', '1000', 1, 'Privatif', 'Collectif', 1, '2024-12-19 14:13:08', '2024-12-19 14:13:08', 1, 1),
(6, 'SUB191220241414371', 'aze', '1000', 1, 'Privatif', 'Collectif', 1, '2024-12-19 14:14:37', '2024-12-19 14:14:37', 1, 1),
(7, 'SUB191220241423091', 'az', '1000', 0, '', 'Collectif', 1, '2024-12-19 14:23:09', '2024-12-19 14:23:09', 1, 1),
(8, 'SUB191220241423511', 'tEST', '1000', 1, 'Collectif', '', 1, '2024-12-19 14:23:51', '2024-12-19 14:23:51', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_support_revetement`
--

CREATE TABLE `wbcc_support_revetement` (
  `idSupportRevetement` int(11) NOT NULL,
  `idSupportF` int(11) NOT NULL,
  `idRevetementF` int(11) NOT NULL,
  `etatSupportRevetement` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_type_condition`
--

CREATE TABLE `wbcc_type_condition` (
  `idTypeCondition` int(11) NOT NULL,
  `numeroTypeCondition` varchar(50) DEFAULT NULL,
  `libelleTypeCondition` varchar(255) DEFAULT NULL,
  `nomVariable` varchar(255) DEFAULT NULL,
  `createDate` varchar(25) DEFAULT current_timestamp(),
  `editDate` varchar(25) DEFAULT current_timestamp(),
  `idAuteur` int(11) DEFAULT NULL,
  `etatTypeCondition` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_type_condition`
--

INSERT INTO `wbcc_type_condition` (`idTypeCondition`, `numeroTypeCondition`, `libelleTypeCondition`, `nomVariable`, `createDate`, `editDate`, `idAuteur`, `etatTypeCondition`) VALUES
(1, 'TC271220240946241', 'teste', NULL, '2024-12-27 09:46:24', '2024-12-27 09:46:24', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_user_access`
--

CREATE TABLE `wbcc_user_access` (
  `idUserAccess` int(11) NOT NULL,
  `lien` text DEFAULT NULL,
  `idUserF` int(11) DEFAULT NULL,
  `nomUser` varchar(255) DEFAULT NULL,
  `dateAccess` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wbcc_utilisateur`
--

CREATE TABLE `wbcc_utilisateur` (
  `idUtilisateur` int(11) NOT NULL,
  `login` varchar(255) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `matricule` varchar(50) DEFAULT '',
  `role` int(1) NOT NULL,
  `etatUser` int(11) NOT NULL,
  `idContactF` int(11) NOT NULL,
  `firstConnection` int(11) NOT NULL DEFAULT 0,
  `isVerified` int(11) NOT NULL DEFAULT 0,
  `token` varchar(255) DEFAULT NULL,
  `tokenPwd` varchar(255) DEFAULT NULL,
  `valideCompte` int(11) NOT NULL DEFAULT 0,
  `jourTravail` varchar(255) DEFAULT '',
  `horaireTravail` varchar(255) DEFAULT '',
  `margeTravail` varchar(50) DEFAULT '',
  `cpZoneRV` varchar(255) DEFAULT '',
  `villeZoneRV` varchar(255) DEFAULT '',
  `adresseZoneRV` text DEFAULT '',
  `typeZoneRV` varchar(255) DEFAULT NULL,
  `codeDepartement` varchar(255) DEFAULT NULL,
  `commentaireConfig` text DEFAULT '',
  `moyenTransport` varchar(100) DEFAULT 'pied',
  `idGuidWbccGroup` varchar(50) DEFAULT NULL,
  `jourTravailB2C` varchar(255) DEFAULT NULL,
  `horaireTravailB2C` varchar(255) DEFAULT NULL,
  `margeTravailB2C` varchar(100) DEFAULT NULL,
  `commentaireConfigB2C` text DEFAULT NULL,
  `nbOpPrevuB2C` varchar(100) DEFAULT NULL,
  `nbVisitePrevuB2C` varchar(100) DEFAULT NULL,
  `nbGardienB2C` varchar(100) DEFAULT NULL,
  `cpZoneB2C` varchar(255) DEFAULT NULL,
  `villeZoneB2C` varchar(255) DEFAULT NULL,
  `typeZoneB2C` varchar(255) DEFAULT NULL,
  `codeDepartementB2C` varchar(255) DEFAULT NULL,
  `dateDesactivation` varchar(25) DEFAULT NULL,
  `isExpert` int(11) DEFAULT 0,
  `isAdmin` int(11) DEFAULT 0,
  `isCommercial` int(11) DEFAULT 0,
  `isDirecteurCommercial` int(11) DEFAULT 0,
  `isGestionnaire` int(11) DEFAULT 0,
  `isFormateur` int(11) NOT NULL DEFAULT 0,
  `idSiteF` int(11) DEFAULT 0,
  `isInterne` int(11) DEFAULT 0,
  `typeUser` varchar(100) DEFAULT NULL,
  `isServiceTechnique` int(11) DEFAULT 0,
  `isAccessAllOP` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `wbcc_utilisateur`
--

INSERT INTO `wbcc_utilisateur` (`idUtilisateur`, `login`, `mdp`, `email`, `matricule`, `role`, `etatUser`, `idContactF`, `firstConnection`, `isVerified`, `token`, `tokenPwd`, `valideCompte`, `jourTravail`, `horaireTravail`, `margeTravail`, `cpZoneRV`, `villeZoneRV`, `adresseZoneRV`, `typeZoneRV`, `codeDepartement`, `commentaireConfig`, `moyenTransport`, `idGuidWbccGroup`, `jourTravailB2C`, `horaireTravailB2C`, `margeTravailB2C`, `commentaireConfigB2C`, `nbOpPrevuB2C`, `nbVisitePrevuB2C`, `nbGardienB2C`, `cpZoneB2C`, `villeZoneB2C`, `typeZoneB2C`, `codeDepartementB2C`, `dateDesactivation`, `isExpert`, `isAdmin`, `isCommercial`, `isDirecteurCommercial`, `isGestionnaire`, `isFormateur`, `idSiteF`, `isInterne`, `typeUser`, `isServiceTechnique`, `isAccessAllOP`) VALUES
(1, 'jawher@wbcc.fr', '482f7629a2511d23ef4e958b13a5ba54bdba06f2', 'jawher@wbcc.fr', '', 1, 1, 1, 1, 11, NULL, NULL, 1, '', '', '', '', '', '', NULL, NULL, '', 'pied', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `wbcc_activity_db`
--
ALTER TABLE `wbcc_activity_db`
  ADD PRIMARY KEY (`idActivitydb`);

--
-- Index pour la table `wbcc_appartement`
--
ALTER TABLE `wbcc_appartement`
  ADD PRIMARY KEY (`idApp`),
  ADD KEY `idImmeubleF` (`idImmeubleF`),
  ADD KEY `idProprietaire` (`idProprietaire`),
  ADD KEY `idAgenceImmobiliere` (`idAgenceImmobiliere`),
  ADD KEY `idCompanyCopro` (`idCompanyCopro`),
  ADD KEY `idOccupant` (`idOccupant`);

--
-- Index pour la table `wbcc_appartement_contact`
--
ALTER TABLE `wbcc_appartement_contact`
  ADD PRIMARY KEY (`idAppCon`),
  ADD KEY `idAppartementF` (`idAppartementF`),
  ADD KEY `idContactF` (`idContactF`);

--
-- Index pour la table `wbcc_company`
--
ALTER TABLE `wbcc_company`
  ADD PRIMARY KEY (`idCompany`),
  ADD KEY `idTitreF` (`idTitreF`),
  ADD KEY `idServiceF` (`idServiceF`),
  ADD KEY `getCompaniesBySuperArtisan` (`idArtisanDevisF`);

--
-- Index pour la table `wbcc_condition`
--
ALTER TABLE `wbcc_condition`
  ADD PRIMARY KEY (`idCondition`),
  ADD KEY `idAuteur` (`idAuteur`),
  ADD KEY `idTypeConditionF` (`idTypeConditionF`);

--
-- Index pour la table `wbcc_condition_critere`
--
ALTER TABLE `wbcc_condition_critere`
  ADD PRIMARY KEY (`idConditionCritere`),
  ADD KEY `idConditionF` (`idConditionF`),
  ADD KEY `idCritereF` (`idCritereF`),
  ADD KEY `idAuteur` (`idAuteur`);

--
-- Index pour la table `wbcc_contact`
--
ALTER TABLE `wbcc_contact`
  ADD PRIMARY KEY (`idContact`),
  ADD KEY `idContactFContact` (`idContactFContact`);

--
-- Index pour la table `wbcc_contact_company`
--
ALTER TABLE `wbcc_contact_company`
  ADD PRIMARY KEY (`idContactCompany`),
  ADD KEY `idContactF` (`idContactF`),
  ADD KEY `idCompanyF` (`idCompanyF`);

--
-- Index pour la table `wbcc_critere`
--
ALTER TABLE `wbcc_critere`
  ADD PRIMARY KEY (`idCritere`),
  ADD KEY `idAuteur` (`idAuteur`);

--
-- Index pour la table `wbcc_critere_subvention`
--
ALTER TABLE `wbcc_critere_subvention`
  ADD PRIMARY KEY (`idCritereSubvention`),
  ADD KEY `idSubventionF` (`idSubventionF`),
  ADD KEY `idAuteur` (`idAuteur`),
  ADD KEY `idCritereF` (`idCritereF`);

--
-- Index pour la table `wbcc_document`
--
ALTER TABLE `wbcc_document`
  ADD PRIMARY KEY (`idDocument`),
  ADD KEY `idUserF` (`idUtilisateurF`);

--
-- Index pour la table `wbcc_document_requis`
--
ALTER TABLE `wbcc_document_requis`
  ADD PRIMARY KEY (`idDocumentRequis`);

--
-- Index pour la table `wbcc_document_requis_subvention`
--
ALTER TABLE `wbcc_document_requis_subvention`
  ADD PRIMARY KEY (`idDocumentRequisSubvention`),
  ADD KEY `idDocumentRequisF` (`idDocumentRequisF`),
  ADD KEY `idSubventionF` (`idSubventionF`);

--
-- Index pour la table `wbcc_historique`
--
ALTER TABLE `wbcc_historique`
  ADD PRIMARY KEY (`idHistorique`),
  ADD KEY `idUtilsateur` (`idUtilisateurF`);

--
-- Index pour la table `wbcc_immeuble`
--
ALTER TABLE `wbcc_immeuble`
  ADD PRIMARY KEY (`idImmeuble`),
  ADD KEY `idUserF` (`idUserF`);

--
-- Index pour la table `wbcc_immeuble_cb`
--
ALTER TABLE `wbcc_immeuble_cb`
  ADD PRIMARY KEY (`idImmeuble`),
  ADD KEY `idUserF` (`idUserF`);

--
-- Index pour la table `wbcc_jour_ferie`
--
ALTER TABLE `wbcc_jour_ferie`
  ADD PRIMARY KEY (`idJourFerie`),
  ADD KEY `idSitefk` (`idSiteF`);

--
-- Index pour la table `wbcc_module`
--
ALTER TABLE `wbcc_module`
  ADD PRIMARY KEY (`idModule`);

--
-- Index pour la table `wbcc_opportunity_document`
--
ALTER TABLE `wbcc_opportunity_document`
  ADD PRIMARY KEY (`idOpportunityDocument`),
  ADD KEY `idOpportunityF` (`idOpportunityF`),
  ADD KEY `idDocumentF` (`idDocumentF`);

--
-- Index pour la table `wbcc_opportunity_immeuble`
--
ALTER TABLE `wbcc_opportunity_immeuble`
  ADD PRIMARY KEY (`idOpportunityImmeuble`),
  ADD KEY `idOpportunityF` (`idOpportunityF`),
  ADD KEY `idImmeubleF` (`idImmeubleF`);

--
-- Index pour la table `wbcc_parametres`
--
ALTER TABLE `wbcc_parametres`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `wbcc_piece`
--
ALTER TABLE `wbcc_piece`
  ADD PRIMARY KEY (`idPiece`);

--
-- Index pour la table `wbcc_piece_equipement`
--
ALTER TABLE `wbcc_piece_equipement`
  ADD PRIMARY KEY (`idPieceEquipement`);

--
-- Index pour la table `wbcc_projet`
--
ALTER TABLE `wbcc_projet`
  ADD PRIMARY KEY (`idProjet`),
  ADD KEY `wbcc_projet_ibfk_1` (`idImmeubleCB`);

--
-- Index pour la table `wbcc_recherche_fuite`
--
ALTER TABLE `wbcc_recherche_fuite`
  ADD PRIMARY KEY (`idRF`),
  ADD KEY `idOpportunityF` (`idOpportunityF`),
  ADD KEY `idArtisanF` (`idArtisanF`),
  ADD KEY `idSocieteArtisanF` (`idSocieteArtisanF`),
  ADD KEY `idVoisinF` (`idVoisinF`);

--
-- Index pour la table `wbcc_releve_technique`
--
ALTER TABLE `wbcc_releve_technique`
  ADD PRIMARY KEY (`idRT`),
  ADD KEY `idOpportunityF` (`idOpportunityF`),
  ADD KEY `idRVF` (`idRVF`),
  ADD KEY `idAppContactF` (`idAppContactF`),
  ADD KEY `idImmeubleF` (`idImmeubleF`),
  ADD KEY `idAuteurRV` (`idAuteurRV`);

--
-- Index pour la table `wbcc_rendez_vous`
--
ALTER TABLE `wbcc_rendez_vous`
  ADD PRIMARY KEY (`idRV`),
  ADD KEY `idAppConF` (`idAppConF`),
  ADD KEY `idCampagneF` (`idCampagneF`),
  ADD KEY `idOpportunityF` (`idOpportunityF`),
  ADD KEY `idExpertF` (`idExpertF`),
  ADD KEY `idAuteur` (`idAuteur`);

--
-- Index pour la table `wbcc_revetement_bordereau`
--
ALTER TABLE `wbcc_revetement_bordereau`
  ADD PRIMARY KEY (`idRevetementB`);

--
-- Index pour la table `wbcc_roles`
--
ALTER TABLE `wbcc_roles`
  ADD PRIMARY KEY (`idRole`);

--
-- Index pour la table `wbcc_role_sous_module`
--
ALTER TABLE `wbcc_role_sous_module`
  ADD PRIMARY KEY (`idRoleSousModule`),
  ADD KEY `idSousModuleF` (`idSousModuleF`),
  ADD KEY `idRoleF` (`idRoleF`);

--
-- Index pour la table `wbcc_rt_ouverture`
--
ALTER TABLE `wbcc_rt_ouverture`
  ADD PRIMARY KEY (`idRtOuverture`),
  ADD KEY `idRtPieceSupportF` (`idRtPieceSupportF`),
  ADD KEY `idUserF` (`idUserF`);

--
-- Index pour la table `wbcc_rt_piece`
--
ALTER TABLE `wbcc_rt_piece`
  ADD PRIMARY KEY (`idRTPiece`),
  ADD KEY `idRTF` (`idRTF`),
  ADD KEY `idPieceF` (`idPieceF`),
  ADD KEY `idUserF` (`idUserF`);

--
-- Index pour la table `wbcc_rt_piece_support`
--
ALTER TABLE `wbcc_rt_piece_support`
  ADD PRIMARY KEY (`idRTPieceSupport`),
  ADD KEY `idRtPieceF` (`idRTPieceF`),
  ADD KEY `idUserF` (`idUserF`),
  ADD KEY `idSupportF` (`idSupportF`);

--
-- Index pour la table `wbcc_rt_revetement`
--
ALTER TABLE `wbcc_rt_revetement`
  ADD PRIMARY KEY (`idRtRevetement`),
  ADD KEY `idRtPieceSupportF` (`idRtPieceSupportF`),
  ADD KEY `idRtOuvertureF` (`idRtOuvertureF`),
  ADD KEY `idUserF` (`idUserF`),
  ADD KEY `idRevetementF` (`idRevetementF`);

--
-- Index pour la table `wbcc_rt_revetement_ouverture`
--
ALTER TABLE `wbcc_rt_revetement_ouverture`
  ADD PRIMARY KEY (`idRtRevetementOuverture`),
  ADD KEY `idUserF` (`idUserF`),
  ADD KEY `idRevetementF` (`idRevetementF`),
  ADD KEY `idOuvertureF` (`idOuvertureF`);

--
-- Index pour la table `wbcc_section`
--
ALTER TABLE `wbcc_section`
  ADD PRIMARY KEY (`idSection`),
  ADD KEY `idSection_parentF` (`idSection_parentF`),
  ADD KEY `wbcc_section_ibfk_1` (`idSommaireF`);

--
-- Index pour la table `wbcc_section_document`
--
ALTER TABLE `wbcc_section_document`
  ADD PRIMARY KEY (`idSectionDocument`),
  ADD KEY `idDocumentF` (`idDocumentF`),
  ADD KEY `wbcc_section_document_ibfk_1` (`idSectionF`);

--
-- Index pour la table `wbcc_site`
--
ALTER TABLE `wbcc_site`
  ADD PRIMARY KEY (`idSite`);

--
-- Index pour la table `wbcc_sommaire`
--
ALTER TABLE `wbcc_sommaire`
  ADD PRIMARY KEY (`idSommaire`),
  ADD UNIQUE KEY `idProjetF` (`idProjetF`);

--
-- Index pour la table `wbcc_sous_module`
--
ALTER TABLE `wbcc_sous_module`
  ADD PRIMARY KEY (`idSousModule`),
  ADD KEY `idModule` (`idModuleF`);

--
-- Index pour la table `wbcc_subvention`
--
ALTER TABLE `wbcc_subvention`
  ADD PRIMARY KEY (`idSubvention`),
  ADD KEY `idAuteur` (`idAuteur`),
  ADD KEY `idOrganisme` (`idOrganisme`);

--
-- Index pour la table `wbcc_support_revetement`
--
ALTER TABLE `wbcc_support_revetement`
  ADD PRIMARY KEY (`idSupportRevetement`),
  ADD KEY `idSupportF` (`idSupportF`),
  ADD KEY `idRevetementF` (`idRevetementF`);

--
-- Index pour la table `wbcc_type_condition`
--
ALTER TABLE `wbcc_type_condition`
  ADD PRIMARY KEY (`idTypeCondition`),
  ADD KEY `idAuteur` (`idAuteur`);

--
-- Index pour la table `wbcc_user_access`
--
ALTER TABLE `wbcc_user_access`
  ADD PRIMARY KEY (`idUserAccess`),
  ADD KEY `idUserF` (`idUserF`);

--
-- Index pour la table `wbcc_utilisateur`
--
ALTER TABLE `wbcc_utilisateur`
  ADD PRIMARY KEY (`idUtilisateur`),
  ADD KEY `role` (`role`),
  ADD KEY `idEmployeF` (`idContactF`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `wbcc_activity_db`
--
ALTER TABLE `wbcc_activity_db`
  MODIFY `idActivitydb` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT pour la table `wbcc_appartement`
--
ALTER TABLE `wbcc_appartement`
  MODIFY `idApp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `wbcc_appartement_contact`
--
ALTER TABLE `wbcc_appartement_contact`
  MODIFY `idAppCon` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wbcc_company`
--
ALTER TABLE `wbcc_company`
  MODIFY `idCompany` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `wbcc_condition`
--
ALTER TABLE `wbcc_condition`
  MODIFY `idCondition` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `wbcc_condition_critere`
--
ALTER TABLE `wbcc_condition_critere`
  MODIFY `idConditionCritere` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `wbcc_contact`
--
ALTER TABLE `wbcc_contact`
  MODIFY `idContact` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `wbcc_contact_company`
--
ALTER TABLE `wbcc_contact_company`
  MODIFY `idContactCompany` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wbcc_critere`
--
ALTER TABLE `wbcc_critere`
  MODIFY `idCritere` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `wbcc_critere_subvention`
--
ALTER TABLE `wbcc_critere_subvention`
  MODIFY `idCritereSubvention` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `wbcc_document`
--
ALTER TABLE `wbcc_document`
  MODIFY `idDocument` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT pour la table `wbcc_document_requis`
--
ALTER TABLE `wbcc_document_requis`
  MODIFY `idDocumentRequis` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wbcc_document_requis_subvention`
--
ALTER TABLE `wbcc_document_requis_subvention`
  MODIFY `idDocumentRequisSubvention` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wbcc_historique`
--
ALTER TABLE `wbcc_historique`
  MODIFY `idHistorique` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `wbcc_immeuble`
--
ALTER TABLE `wbcc_immeuble`
  MODIFY `idImmeuble` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2457;

--
-- AUTO_INCREMENT pour la table `wbcc_immeuble_cb`
--
ALTER TABLE `wbcc_immeuble_cb`
  MODIFY `idImmeuble` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2457;

--
-- AUTO_INCREMENT pour la table `wbcc_jour_ferie`
--
ALTER TABLE `wbcc_jour_ferie`
  MODIFY `idJourFerie` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wbcc_module`
--
ALTER TABLE `wbcc_module`
  MODIFY `idModule` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `wbcc_opportunity_document`
--
ALTER TABLE `wbcc_opportunity_document`
  MODIFY `idOpportunityDocument` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=626;

--
-- AUTO_INCREMENT pour la table `wbcc_opportunity_immeuble`
--
ALTER TABLE `wbcc_opportunity_immeuble`
  MODIFY `idOpportunityImmeuble` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `wbcc_parametres`
--
ALTER TABLE `wbcc_parametres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `wbcc_piece`
--
ALTER TABLE `wbcc_piece`
  MODIFY `idPiece` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `wbcc_piece_equipement`
--
ALTER TABLE `wbcc_piece_equipement`
  MODIFY `idPieceEquipement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `wbcc_projet`
--
ALTER TABLE `wbcc_projet`
  MODIFY `idProjet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT pour la table `wbcc_recherche_fuite`
--
ALTER TABLE `wbcc_recherche_fuite`
  MODIFY `idRF` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT pour la table `wbcc_releve_technique`
--
ALTER TABLE `wbcc_releve_technique`
  MODIFY `idRT` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `wbcc_rendez_vous`
--
ALTER TABLE `wbcc_rendez_vous`
  MODIFY `idRV` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `wbcc_revetement_bordereau`
--
ALTER TABLE `wbcc_revetement_bordereau`
  MODIFY `idRevetementB` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wbcc_roles`
--
ALTER TABLE `wbcc_roles`
  MODIFY `idRole` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `wbcc_role_sous_module`
--
ALTER TABLE `wbcc_role_sous_module`
  MODIFY `idRoleSousModule` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1028;

--
-- AUTO_INCREMENT pour la table `wbcc_rt_ouverture`
--
ALTER TABLE `wbcc_rt_ouverture`
  MODIFY `idRtOuverture` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wbcc_rt_piece`
--
ALTER TABLE `wbcc_rt_piece`
  MODIFY `idRTPiece` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `wbcc_rt_piece_support`
--
ALTER TABLE `wbcc_rt_piece_support`
  MODIFY `idRTPieceSupport` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wbcc_rt_revetement`
--
ALTER TABLE `wbcc_rt_revetement`
  MODIFY `idRtRevetement` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wbcc_rt_revetement_ouverture`
--
ALTER TABLE `wbcc_rt_revetement_ouverture`
  MODIFY `idRtRevetementOuverture` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wbcc_section`
--
ALTER TABLE `wbcc_section`
  MODIFY `idSection` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=323;

--
-- AUTO_INCREMENT pour la table `wbcc_section_document`
--
ALTER TABLE `wbcc_section_document`
  MODIFY `idSectionDocument` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT pour la table `wbcc_site`
--
ALTER TABLE `wbcc_site`
  MODIFY `idSite` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wbcc_sommaire`
--
ALTER TABLE `wbcc_sommaire`
  MODIFY `idSommaire` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT pour la table `wbcc_sous_module`
--
ALTER TABLE `wbcc_sous_module`
  MODIFY `idSousModule` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pour la table `wbcc_subvention`
--
ALTER TABLE `wbcc_subvention`
  MODIFY `idSubvention` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `wbcc_support_revetement`
--
ALTER TABLE `wbcc_support_revetement`
  MODIFY `idSupportRevetement` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wbcc_type_condition`
--
ALTER TABLE `wbcc_type_condition`
  MODIFY `idTypeCondition` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `wbcc_user_access`
--
ALTER TABLE `wbcc_user_access`
  MODIFY `idUserAccess` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wbcc_utilisateur`
--
ALTER TABLE `wbcc_utilisateur`
  MODIFY `idUtilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `wbcc_appartement`
--
ALTER TABLE `wbcc_appartement`
  ADD CONSTRAINT `wbcc_appartement_ibfk_1` FOREIGN KEY (`idImmeubleF`) REFERENCES `wbcc_immeuble` (`idImmeuble`);

--
-- Contraintes pour la table `wbcc_appartement_contact`
--
ALTER TABLE `wbcc_appartement_contact`
  ADD CONSTRAINT `wbcc_appartement_contact_ibfk_1` FOREIGN KEY (`idAppartementF`) REFERENCES `wbcc_appartement` (`idApp`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wbcc_appartement_contact_ibfk_2` FOREIGN KEY (`idContactF`) REFERENCES `wbcc_contact` (`idContact`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `wbcc_condition`
--
ALTER TABLE `wbcc_condition`
  ADD CONSTRAINT `wbcc_condition_ibfk_1` FOREIGN KEY (`idTypeConditionF`) REFERENCES `wbcc_condition` (`idCondition`);

--
-- Contraintes pour la table `wbcc_condition_critere`
--
ALTER TABLE `wbcc_condition_critere`
  ADD CONSTRAINT `wbcc_condition_critere_ibfk_1` FOREIGN KEY (`idConditionF`) REFERENCES `wbcc_condition` (`idCondition`),
  ADD CONSTRAINT `wbcc_condition_critere_ibfk_2` FOREIGN KEY (`idCritereF`) REFERENCES `wbcc_critere` (`idCritere`);

--
-- Contraintes pour la table `wbcc_contact_company`
--
ALTER TABLE `wbcc_contact_company`
  ADD CONSTRAINT `wbcc_contact_company_ibfk_1` FOREIGN KEY (`idContactF`) REFERENCES `wbcc_contact` (`idContact`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wbcc_contact_company_ibfk_2` FOREIGN KEY (`idCompanyF`) REFERENCES `wbcc_company` (`idCompany`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `wbcc_critere_subvention`
--
ALTER TABLE `wbcc_critere_subvention`
  ADD CONSTRAINT `wbcc_critere_subvention_ibfk_1` FOREIGN KEY (`idCritereF`) REFERENCES `wbcc_critere` (`idCritere`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wbcc_critere_subvention_ibfk_2` FOREIGN KEY (`idSubventionF`) REFERENCES `wbcc_subvention` (`idSubvention`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `wbcc_document`
--
ALTER TABLE `wbcc_document`
  ADD CONSTRAINT `wbcc_document_ibfk_1` FOREIGN KEY (`idUtilisateurF`) REFERENCES `wbcc_utilisateur` (`idUtilisateur`);

--
-- Contraintes pour la table `wbcc_document_requis_subvention`
--
ALTER TABLE `wbcc_document_requis_subvention`
  ADD CONSTRAINT `wbcc_document_requis_subvention_ibfk_1` FOREIGN KEY (`idDocumentRequisF`) REFERENCES `wbcc_document_requis` (`idDocumentRequis`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wbcc_document_requis_subvention_ibfk_2` FOREIGN KEY (`idSubventionF`) REFERENCES `wbcc_subvention` (`idSubvention`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `wbcc_historique`
--
ALTER TABLE `wbcc_historique`
  ADD CONSTRAINT `wbcc_historique_ibfk_1` FOREIGN KEY (`idUtilisateurF`) REFERENCES `wbcc_utilisateur` (`idUtilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `wbcc_jour_ferie`
--
ALTER TABLE `wbcc_jour_ferie`
  ADD CONSTRAINT `idSitefk` FOREIGN KEY (`idSiteF`) REFERENCES `wbcc_site` (`idSite`);

--
-- Contraintes pour la table `wbcc_projet`
--
ALTER TABLE `wbcc_projet`
  ADD CONSTRAINT `wbcc_projet_ibfk_1` FOREIGN KEY (`idImmeubleCB`) REFERENCES `wbcc_immeuble` (`idImmeuble`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `wbcc_role_sous_module`
--
ALTER TABLE `wbcc_role_sous_module`
  ADD CONSTRAINT `wbcc_role_sous_module_ibfk_1` FOREIGN KEY (`idRoleF`) REFERENCES `wbcc_roles` (`idRole`),
  ADD CONSTRAINT `wbcc_role_sous_module_ibfk_2` FOREIGN KEY (`idSousModuleF`) REFERENCES `wbcc_sous_module` (`idSousModule`);

--
-- Contraintes pour la table `wbcc_section`
--
ALTER TABLE `wbcc_section`
  ADD CONSTRAINT `wbcc_section_ibfk_1` FOREIGN KEY (`idSommaireF`) REFERENCES `wbcc_sommaire` (`idSommaire`) ON DELETE CASCADE;

--
-- Contraintes pour la table `wbcc_section_document`
--
ALTER TABLE `wbcc_section_document`
  ADD CONSTRAINT `wbcc_section_document_ibfk_1` FOREIGN KEY (`idSectionF`) REFERENCES `wbcc_section` (`idSection`) ON DELETE CASCADE,
  ADD CONSTRAINT `wbcc_section_document_ibfk_2` FOREIGN KEY (`idDocumentF`) REFERENCES `wbcc_document` (`idDocument`);

--
-- Contraintes pour la table `wbcc_sommaire`
--
ALTER TABLE `wbcc_sommaire`
  ADD CONSTRAINT `wbcc_sommaire_ibfk_1` FOREIGN KEY (`idProjetF`) REFERENCES `wbcc_projet` (`idProjet`);

--
-- Contraintes pour la table `wbcc_sous_module`
--
ALTER TABLE `wbcc_sous_module`
  ADD CONSTRAINT `wbcc_sous_module_ibfk_1` FOREIGN KEY (`idModuleF`) REFERENCES `wbcc_module` (`idModule`);

--
-- Contraintes pour la table `wbcc_user_access`
--
ALTER TABLE `wbcc_user_access`
  ADD CONSTRAINT `wbcc_user_access_ibfk_1` FOREIGN KEY (`idUserF`) REFERENCES `wbcc_utilisateur` (`idUtilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
