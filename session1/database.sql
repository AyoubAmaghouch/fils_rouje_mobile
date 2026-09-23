CREATE DATABASE freelance;
USE freelance;

-- 1. Freelance
CREATE TABLE freelance (
    id_freelance INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telephone VARCHAR(30),
    description TEXT,
    image VARCHAR(255),
    facebook VARCHAR(255),
    instagram VARCHAR(255),
    linkedin VARCHAR(255),
    github VARCHAR(255)
);

-- 2. Catégorie de service
CREATE TABLE categorie_service (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT
);

-- 3. Service (Gig)
CREATE TABLE service (
    id_service INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    image_service VARCHAR(255),
    id_freelance INT NOT NULL,
    id_categorie INT NOT NULL,

    CONSTRAINT fk_service_freelance
        FOREIGN KEY (id_freelance)
        REFERENCES freelance(id_freelance)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_service_categorie
        FOREIGN KEY (id_categorie)
        REFERENCES categorie_service(id_categorie)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- 4. Commande
CREATE TABLE commande (
    id_commande INT AUTO_INCREMENT PRIMARY KEY,
    date_commande DATE NOT NULL,
    statut VARCHAR(50) NOT NULL,
    prix_total DECIMAL(10,2) NOT NULL,
    id_service INT NOT NULL,

    CONSTRAINT fk_commande_service
        FOREIGN KEY (id_service)
        REFERENCES service(id_service)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);