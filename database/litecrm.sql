-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : lun. 17 mars 2025 à 14:27
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `litecrm`
--

-- --------------------------------------------------------

--
-- Structure de la table `Client`
--

CREATE TABLE `Client` (
  `id_client` int(11) NOT NULL,
  `Nom` varchar(255) NOT NULL,
  `Prenom` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Telephone` int(11) NOT NULL,
  `Entreprise` varchar(255) NOT NULL,
  `Adresse` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Client`
--

INSERT INTO `Client` (`id_client`, `Nom`, `Prenom`, `Email`, `Telephone`, `Entreprise`, `Adresse`) VALUES
(1, 'Deveze', 'Joseph', 'joseph.deveze.jd@gmail.com', 648800322, 'Test', 'test');

-- --------------------------------------------------------

--
-- Structure de la table `Rendez-vous`
--

CREATE TABLE `Rendez-vous` (
  `id_rdv` int(11) NOT NULL,
  `id_client` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `lieu` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `notes` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Rendez-vous`
--

INSERT INTO `Rendez-vous` (`id_rdv`, `id_client`, `date`, `heure_debut`, `heure_fin`, `lieu`, `status`, `notes`) VALUES
(13, 1, '2025-03-18', '13:00:00', '15:00:00', 'Nice', 'Prévu', 'test'),
(17, 1, '2025-03-20', '16:00:00', '17:00:00', 'Paris', 'Confirmé', '');

-- --------------------------------------------------------

--
-- Structure de la table `Utilisateur`
--

CREATE TABLE `Utilisateur` (
  `id_utilisateur` int(11) NOT NULL,
  `Nom` varchar(255) NOT NULL,
  `Prenom` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Utilisateur`
--

INSERT INTO `Utilisateur` (`id_utilisateur`, `Nom`, `Prenom`, `Email`, `Password`, `Role`) VALUES
(1, 'Deveze', 'Joseph', 'joseph.deveze.jd@gmail.com', '$2y$10$N6pypjie0AVTflz/Jhp6luIfXiR.ltBKbRGx0VPoGksI5GoFsjcS6', 'user'),
(4, 'admin', 'admin', 'admin@admin.fr', '$2y$10$Z7cg9POVymynQ08EQdn7L.3eP8sKzaLfe9LGIdMANiYGBTaiqBXbe', 'admin');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `Client`
--
ALTER TABLE `Client`
  ADD PRIMARY KEY (`id_client`);

--
-- Index pour la table `Rendez-vous`
--
ALTER TABLE `Rendez-vous`
  ADD PRIMARY KEY (`id_rdv`),
  ADD KEY `rendez_vous_ibfk_1` (`id_client`);

--
-- Index pour la table `Utilisateur`
--
ALTER TABLE `Utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `Client`
--
ALTER TABLE `Client`
  MODIFY `id_client` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `Rendez-vous`
--
ALTER TABLE `Rendez-vous`
  MODIFY `id_rdv` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `Utilisateur`
--
ALTER TABLE `Utilisateur`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `Rendez-vous`
--
ALTER TABLE `Rendez-vous`
  ADD CONSTRAINT `rendez_vous_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `Client` (`id_client`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
