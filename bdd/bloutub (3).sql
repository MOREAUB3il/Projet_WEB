-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 01 avr. 2026 à 16:50
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
-- Base de données : `bloutub`
--

-- --------------------------------------------------------

--
-- Structure de la table `capsules`
--

CREATE TABLE `capsules` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `chemin_image` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cree_le` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `capsules`
--

INSERT INTO `capsules` (`id`, `utilisateur_id`, `chemin_image`, `description`, `cree_le`) VALUES
(1, 1, '../uploads/69cbedfc4c436-Capture d\'écran 2026-03-31 174531.png', 'teste', '2026-03-31 17:53:32'),
(2, 3, '../uploads/69cc456151738-Capture d\'écran 2026-04-01 000336.png', 'ROARRRRR!!!!!!!', '2026-04-01 00:06:25'),
(7, 4, '../uploads/69cd307551d1b-Capture d\'écran 2026-04-01 112406.png', 'chat', '2026-04-01 11:43:41'),
(9, 4, '../uploads/69cd17897a899-Capture d\'écran 2026-04-01 130213.png', 'voiture', '2026-04-01 15:03:05');

-- --------------------------------------------------------

--
-- Structure de la table `favoris`
--

CREATE TABLE `favoris` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `capsule_id` int(11) NOT NULL,
  `cree_le` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `favoris` (`id`, `utilisateur_id`, `capsule_id`, `cree_le`) VALUES
(1, 3, 1, '2026-04-01 00:02:49'),
(2, 3, 2, '2026-04-01 00:11:47'),
(15, 4, 7, '2026-04-01 15:01:07'),
(16, 4, 1, '2026-04-01 16:46:40');


CREATE TABLE `commentaires` (
  `id` int(11) NOT NULL,
  `capsule_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `cree_le` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom_utilisateur` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('utilisateur','admin') DEFAULT 'utilisateur',
  `cree_le` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom_utilisateur`, `email`, `mot_de_passe`, `role`, `cree_le`) VALUES
(1, 'mouniapt', 'mouniapt@3il.fr', '$2y$10$gYaofC5IdBZbymFzfqY8eO74jx3cSvPdUxdawOtoVF2PGdByjv.n6', 'utilisateur', '2026-03-31 17:41:11'),
(2, 'ismael', 'Ismaeldiallo953@gmail.com', '$2y$10$ICgv9HPkET/DM7rLl1viy.HA1nqVoOk5uf5WLclFPxG907brEbrfW', 'admin', '2026-03-31 18:19:37'),
(3, 'Lignières', 'maevalignieres@gmail.com', '$2y$10$bzUW26wajmKxJZ/ueIVW6e7V9.5w9oGuiP1o.1Q2hV.OeAGfEFu6O', 'utilisateur', '2026-04-01 00:02:41'),
(4, 'theo', 'theo.mouniapin@gmail.com', '$2y$10$hiek9HZEZ0cXY6IAu5IsdOujcz57peh8.MYtKE.7wE7vRD00kK3fy', 'admin', '2026-04-01 11:34:53');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `capsules`
--
ALTER TABLE `capsules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `commentaires`
--
ALTER TABLE `commentaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `capsule_id` (`capsule_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favori` (`utilisateur_id`,`capsule_id`),
  ADD KEY `capsule_id` (`capsule_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom_utilisateur` (`nom_utilisateur`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `capsules`
--
ALTER TABLE `capsules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `commentaires`
--
ALTER TABLE `commentaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `favoris`
--
ALTER TABLE `favoris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `capsules`
--
ALTER TABLE `capsules`
  ADD CONSTRAINT `capsules_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `commentaires`
--
ALTER TABLE `commentaires`
  ADD CONSTRAINT `commentaires_ibfk_1` FOREIGN KEY (`capsule_id`) REFERENCES `capsules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commentaires_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD CONSTRAINT `favoris_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favoris_ibfk_2` FOREIGN KEY (`capsule_id`) REFERENCES `capsules` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
