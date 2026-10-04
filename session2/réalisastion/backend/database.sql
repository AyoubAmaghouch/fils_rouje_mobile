-- =====================================================
-- Script SQL — Plateforme Freelance Admin
-- Base de données : freelance
-- =====================================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS `freelance`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `freelance`;

-- =====================================================
-- TABLE : freelance
-- =====================================================
CREATE TABLE IF NOT EXISTS `freelance` (
  `id_freelance` INT(11) NOT NULL AUTO_INCREMENT,
  `nom`          VARCHAR(100) NOT NULL,
  `prenom`       VARCHAR(100) NOT NULL,
  `email`        VARCHAR(150) NOT NULL,
  `telephone`    VARCHAR(20)  DEFAULT NULL,
  `description`  TEXT         DEFAULT NULL,
  `image`        VARCHAR(255) DEFAULT NULL,
  `facebook`     VARCHAR(255) DEFAULT NULL,
  `instagram`    VARCHAR(255) DEFAULT NULL,
  `linkedin`     VARCHAR(255) DEFAULT NULL,
  `github`       VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_freelance`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : categorie_service
-- =====================================================
CREATE TABLE IF NOT EXISTS `categorie_service` (
  `id_categorie` INT(11) NOT NULL AUTO_INCREMENT,
  `nom`          VARCHAR(100) NOT NULL,
  `description`  TEXT         DEFAULT NULL,
  PRIMARY KEY (`id_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : service
-- =====================================================
CREATE TABLE IF NOT EXISTS `service` (
  `id_service`    INT(11)        NOT NULL AUTO_INCREMENT,
  `titre`         VARCHAR(200)   NOT NULL,
  `description`   TEXT           NOT NULL,
  `prix`          DECIMAL(10,2)  NOT NULL,
  `image_service` VARCHAR(255)   DEFAULT NULL,
  `id_freelance`  INT(11)        NOT NULL,
  `id_categorie`  INT(11)        NOT NULL,
  PRIMARY KEY (`id_service`),
  CONSTRAINT `fk_service_freelance`
    FOREIGN KEY (`id_freelance`) REFERENCES `freelance` (`id_freelance`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_service_categorie`
    FOREIGN KEY (`id_categorie`) REFERENCES `categorie_service` (`id_categorie`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : commande
-- =====================================================
CREATE TABLE IF NOT EXISTS `commande` (
  `id_commande`   INT(11)       NOT NULL AUTO_INCREMENT,
  `date_commande` DATE          NOT NULL,
  `statut`        VARCHAR(50)   NOT NULL DEFAULT 'en attente',
  `prix_total`    DECIMAL(10,2) NOT NULL,
  `id_service`    INT(11)       NOT NULL,
  PRIMARY KEY (`id_commande`),
  CONSTRAINT `fk_commande_service`
    FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DONNÉES DE TEST (optionnel)
-- =====================================================

INSERT INTO `categorie_service` (`nom`, `description`) VALUES
('Développement Web', 'Création de sites et applications web'),
('Design Graphique', 'Logo, charte graphique, UI/UX'),
('Marketing Digital', 'SEO, réseaux sociaux, publicité en ligne'),
('Rédaction & Traduction', 'Contenu web, traduction, copywriting'),
('Vidéo & Animation', 'Montage vidéo, motion design');

INSERT INTO `freelance` (`nom`, `prenom`, `email`, `telephone`, `description`, `facebook`, `linkedin`) VALUES
('Dupont', 'Alice', 'alice.dupont@email.com', '0661234567',
 'Développeuse Full Stack passionnée avec 5 ans d expérience en PHP, React et Node.js.',
 'https://facebook.com/alice.dupont', 'https://linkedin.com/in/alice-dupont'),
('Martin', 'Karim', 'karim.martin@email.com', '0672345678',
 'Designer UX/UI créatif spécialisé dans les interfaces modernes et l expérience utilisateur.',
 NULL, 'https://linkedin.com/in/karim-martin');

INSERT INTO `service` (`titre`, `description`, `prix`, `id_freelance`, `id_categorie`) VALUES
('Création site web vitrine', 'Développement d un site vitrine responsive en HTML/CSS/PHP avec formulaire de contact.', 1500.00, 1, 1),
('Application web sur mesure', 'Développement d une application web complète avec base de données MySQL et tableau de bord admin.', 4500.00, 1, 1),
('Création logo professionnel', 'Conception d un logo unique avec 3 propositions, révisions illimitées et fichiers sources.', 800.00, 2, 2);

INSERT INTO `commande` (`date_commande`, `statut`, `prix_total`, `id_service`) VALUES
(CURDATE(), 'en attente', 1500.00, 1),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'confirmée', 4500.00, 2),
(DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'terminée', 800.00, 3);



INSERT INTO categorie_service (nom, description) VALUES
('Developpement Web', 'Creation et developpement de sites et applications web'),
('Developpement Mobile', 'Creation d applications mobiles Android et iOS'),
('Montage Video', 'Montage et edition de videos professionnelles'),
('Edition', 'Edition et correction de contenus'),
('Design Graphique', 'Creation de visuels et supports graphiques'),
('Design 3D', 'Modelisation et conception 3D'),
('UI UX Design', 'Conception d interfaces et experiences utilisateur'),
('Logo et Branding', 'Creation de logos et identites visuelles'),
('Photographie', 'Services de photographie et retouche photo'),
('Redaction', 'Redaction d articles et contenus'),
('Traduction', 'Traduction de documents et contenus'),
('Marketing Digital', 'Marketing digital et gestion des campagnes');