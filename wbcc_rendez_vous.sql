-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 06 fév. 2025 à 11:26
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

--
-- Index pour les tables déchargées
--

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
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `wbcc_rendez_vous`
--
ALTER TABLE `wbcc_rendez_vous`
  MODIFY `idRV` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `wbcc_rendez_vous`
--
ALTER TABLE `wbcc_rendez_vous`
  ADD CONSTRAINT `wbcc_rendez_vous_ibfk_1` FOREIGN KEY (`idCampagneF`) REFERENCES `wbcc_campagne` (`idCampagne`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `wbcc_rendez_vous_ibfk_2` FOREIGN KEY (`idOpportunityF`) REFERENCES `wbcc_opportunity` (`idOpportunity`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
