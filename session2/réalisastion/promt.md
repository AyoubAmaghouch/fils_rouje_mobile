RÔLE
Agis comme un développeur Full Stack senior spécialisé en PHP, MySQL, HTML, CSS et JavaScript, avec une bonne maîtrise de l’architecture CRUD, de la modélisation de bases de données et des interfaces d’administration modernes.

CONTEXTE
Je travaille sur une plateforme Freelance permettant de gérer des freelances et leurs services (Gigs).

Le système comporte actuellement 4 tables principales :

1. FREELANCE
- id_freelance
- nom
- prenom
- email
- telephone
- description
- image
- facebook
- instagram
- linkedin
- github

2. CATEGORIE_SERVICE
- id_categorie
- nom
- description

3. SERVICE (GIG)
- id_service
- titre
- description
- prix
- image_service
- id_freelance
- id_categorie

4. COMMANDE
- id_commande
- date_commande
- statut
- prix_total
- id_service

Relations :
- Un freelance peut proposer plusieurs services.
- Un service appartient à un seul freelance.
- Une catégorie peut contenir plusieurs services.
- Un service appartient à une seule catégorie.
- Un service peut avoir plusieurs commandes.
- Une commande concerne un seul service.

La base de données MySQL s'appelle :
freelance

OBJECTIF
Créer uniquement la partie ADMIN de cette plateforme.

L'administrateur doit pouvoir gérer les données avec un système CRUD complet :

- Create : Ajouter
- Read : Afficher
- Update : Modifier
- Delete : Supprimer

Le système doit permettre à l'administrateur de gérer :
- les freelances
- les catégories de services
- les services (Gigs)
- les commandes

Il ne faut PAS développer pour le moment :
- l'espace client
- l'inscription client
- la connexion client
- le paiement
- le marketplace Front Office
- les fonctionnalités avancées du freelance
- les fonctionnalités qui ne concernent pas l'administration

CONTRAINTES TECHNIQUES

Utiliser :
- PHP 8+
- MySQL
- HTML5
- CSS3
- JavaScript uniquement lorsque nécessaire
- PDO pour la connexion à MySQL
- requêtes préparées pour éviter les injections SQL

Le projet doit être simple, propre et adapté à un projet étudiant.

Ne pas utiliser de framework PHP.

L'interface Admin doit être moderne, claire, responsive et professionnelle.

Prévoir une navigation Admin permettant d'accéder facilement à :

Dashboard
Freelances
Catégories
Services
Commandes

Pour chaque module CRUD, prévoir :

LISTE
- afficher les données dans un tableau
- bouton Ajouter
- bouton Modifier
- bouton Supprimer
- bouton Voir lorsque nécessaire
- confirmation avant suppression

AJOUT
- formulaire propre
- validation des champs
- messages d'erreur
- message de succès après ajout

MODIFICATION
- formulaire prérempli
- validation des champs
- message de succès après modification

SUPPRESSION
- confirmation avant suppression
- suppression sécurisée
- message de succès ou d'erreur

GESTION DES IMAGES

Pour le freelance :
- permettre l'ajout d'une image/photo
- enregistrer le chemin de l'image dans MySQL

Pour le service :
- permettre l'ajout d'une image
- enregistrer le chemin de l'image dans MySQL

GESTION DES RÉSEAUX SOCIAUX

Pour le freelance, permettre d'enregistrer :
- Facebook
- Instagram
- LinkedIn
- GitHub

Ces informations doivent être modifiables depuis le CRUD.

BASE DE DONNÉES

Respecter exactement les relations suivantes :

freelance
    1,N
     │
     ▼
service
     ▲
     │
categorie_service
    1,N

service
    1,N
     │
     ▼
commande

Utiliser les clés primaires et étrangères correctement.

Ne pas créer de tables supplémentaires sans nécessité.

Ne pas modifier les noms des tables ou des champs sans raison.

SÉCURITÉ

Utiliser PDO et les requêtes préparées.

Valider les données envoyées par les formulaires.

Sécuriser l'upload des images :
- vérifier l'extension
- vérifier le type MIME
- limiter la taille
- générer un nom de fichier sécurisé

Protéger les actions de suppression et de modification.

STRUCTURE DU PROJET

Proposer une structure claire, par exemple :

/admin
    index.php
    /freelances
        index.php
        ajouter.php
        modifier.php
        supprimer.php
    /categories
        index.php
        ajouter.php
        modifier.php
        supprimer.php
    /services
        index.php
        ajouter.php
        modifier.php
        supprimer.php
    /commandes
        index.php
        modifier.php
        supprimer.php

/config
    database.php

/assets
    /css
    /js
    /images

RÉSULTAT ATTENDU

Construis le système étape par étape.

IMPORTANT :

Ne donne pas tout le code en une seule fois.

Commence par vérifier la structure de la base de données et proposer l'architecture du projet.

Ensuite, développe le système étape par étape.

À chaque étape :
1. Explique brièvement ce qu'on va faire.
2. Donne les fichiers concernés.
3. Donne le code complet de chaque fichier.
4. Explique où placer chaque fichier.
5. Explique comment tester la fonctionnalité.
6. Attends que l'étape soit validée avant de passer à la suivante.

Commence par le module CRUD FREELANCE.

L'objectif final est d'obtenir un panneau d'administration fonctionnel permettant de gérer les Freelances, Catégories, Services et Commandes.


Corrige uniquement le CSS de cette page Admin Freelance.

Le CSS actuel ne se charge pas correctement. Fais en sorte que l’interface soit moderne, professionnelle, responsive et bien organisée, avec une sidebar, des cards, des boutons, des tableaux et une bonne typographie.

Ne modifie ni le PHP, ni le HTML, ni la base de données, ni le CRUD. Concentre-toi uniquement sur le CSS.




Corrige tous les problèmes actuels de mon projet Admin Freelance.

1. Le CSS ne se charge pas correctement : corrige les chemins et fais fonctionner tout le design.
2. Les boutons et les liens de navigation ne fonctionnent pas correctement : corrige tous les href et les chemins vers les pages Admin.
3. Corrige les erreurs 404 comme celle de /admin/commandes/index.php.
4. Vérifie tous les chemins relatifs entre les dossiers admin, pages CRUD, CSS, JS et images.
5. Fais en sorte que Dashboard, Freelances, Catégories, Services et Commandes soient tous accessibles depuis la navigation.

IMPORTANT :
Ne modifie pas la logique du CRUD ni la base de données. Corrige uniquement les chemins, la navigation, les liens, les assets et le CSS.


Corrige uniquement les chemins de navigation de mon projet Admin Freelance.

Le problème actuel est que tous les liens href redirigent vers des URLs qui retournent "Not Found".

Exemple :
/realisastion/admin/freelances/ajouter.php

Quand je clique sur "Ajouter", la page n'est pas trouvée.

Analyse la structure réelle des dossiers du projet et corrige TOUS les href et chemins relatifs afin que :
- Dashboard fonctionne
- Freelances fonctionne
- Ajouter freelance fonctionne
- Modifier freelance fonctionne
- Supprimer freelance fonctionne
- Catégories fonctionne
- Services fonctionne
- Commandes fonctionne
- Tous les boutons et liens de navigation fonctionnent.

IMPORTANT :
Ne crée pas de fausses URLs.
Utilise uniquement les chemins correspondant aux fichiers qui existent réellement dans le projet.
Ne modifie pas la base de données ni la logique CRUD.
Ne change pas le design.
Corrige uniquement les chemins, href et navigation.



Corrige uniquement les liens de navigation de la sidebar Admin.

Les boutons suivants ne fonctionnent pas :
- Catégories
- Services
- Commandes

Analyse la structure réelle des dossiers et fichiers du projet et corrige leurs href pour pointer vers les fichiers PHP qui existent réellement.

Ne modifie ni le CRUD, ni la base de données, ni le CSS, ni le design.
Ne crée pas de nouveaux fichiers.
Corrige uniquement les chemins href des trois liens et vérifie qu'ils fonctionnent après correction.



Ajoute dans le formulaire Ajouter/Modifier Freelance une nouvelle section intitulée « Compétences et services ».

Dans cette section, ajoute deux sélections dynamiques :

1. Catégories de services
- Charger les catégories depuis la table categorie_service.
- Afficher les catégories sous forme de cases à cocher ou sélection multiple.
- Permettre au freelance de choisir une ou plusieurs catégories.

2. Services (Gigs)
- Charger les services depuis la table service.
- Afficher uniquement les services disponibles.
- Permettre au freelance de sélectionner un ou plusieurs services.

Les données doivent être récupérées dynamiquement depuis MySQL avec PDO.

IMPORTANT :
Ne modifie pas les autres champs du formulaire.
Garde le même design CSS.
Les sélections doivent être sauvegardées correctement et réaffichées lors de la modification du freelance.
Respecte la base de données existante.