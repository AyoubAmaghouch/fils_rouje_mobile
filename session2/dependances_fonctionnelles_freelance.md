# Dépendances fonctionnelles — Projet Freelance

## 1. Table FREELANCE

**Clé primaire :** `id_freelance`

Dépendances fonctionnelles :

- `id_freelance → nom`
- `id_freelance → prenom`
- `id_freelance → email`
- `id_freelance → telephone`
- `id_freelance → description`
- `id_freelance → image`
- `id_freelance → facebook`
- `id_freelance → instagram`
- `id_freelance → linkedin`
- `id_freelance → github`

Donc :

`id_freelance → nom, prenom, email, telephone, description, image, facebook, instagram, linkedin, github`

---

## 2. Table SERVICE

**Clé primaire :** `id_service`

Dépendances fonctionnelles :

- `id_service → titre`
- `id_service → description`
- `id_service → prix`
- `id_service → image_service`
- `id_service → id_freelance`
- `id_service → id_categorie`

Donc :

`id_service → titre, description, prix, image_service, id_freelance, id_categorie`

---

## 3. Table CATEGORIE_SERVICE

**Clé primaire :** `id_categorie`

Dépendances fonctionnelles :

- `id_categorie → nom`
- `id_categorie → description`

Donc :

`id_categorie → nom, description`

---

## 4. Table COMMANDE

**Clé primaire :** `id_commande`

Dépendances fonctionnelles :

- `id_commande → date_commande`
- `id_commande → statut`
- `id_commande → prix_total`
- `id_commande → id_service`

Donc :

`id_commande → date_commande, statut, prix_total, id_service`

---

## Synthèse

| Table | Dépendance fonctionnelle |
|---|---|
| FREELANCE | `id_freelance → nom, prenom, email, telephone, description, image, facebook, instagram, linkedin, github` |
| SERVICE | `id_service → titre, description, prix, image_service, id_freelance, id_categorie` |
| CATEGORIE_SERVICE | `id_categorie → nom, description` |
| COMMANDE | `id_commande → date_commande, statut, prix_total, id_service` |

