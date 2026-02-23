# CarnetPro

Application web de gestion de colocation développée avec **Laravel (MVC)**.  
CarnetPro permet de gérer les membres, les dépenses partagées, les soldes, les remboursements simplifiés, la réputation financière et l’administration globale.

![Aperçu CarnetPro](Images/CarnetPro.png)

## Sommaire

1. [Présentation](#présentation)
2. [Fonctionnalités](#fonctionnalités)
3. [Rôles utilisateurs](#rôles-utilisateurs)
4. [Architecture et stack technique](#architecture-et-stack-technique)
5. [Règles métier principales](#règles-métier-principales)
6. [Installation locale](#installation-locale)
7. [Structure attendue des livrables](#structure-attendue-des-livrables)
8. [Sécurité et qualité](#sécurité-et-qualité)
9. [Axes d’amélioration (bonus)](#axes-damélioration-bonus)

## Présentation

**CarnetPro** répond au besoin de suivi financier d’une colocation:
- centraliser les dépenses communes;
- calculer automatiquement les soldes individuels;
- afficher une vue simplifiée des remboursements ("qui doit à qui");
- suivre les paiements réalisés;
- intégrer une logique de réputation liée au comportement financier.

## Fonctionnalités

### Déjà implémenté

- Authentification et gestion du profil utilisateur.
- Promotion automatique du **premier inscrit** en **admin global**.
- Gestion des colocations: création, consultation, mise à jour, annulation.
- Gestion des membres: invitation (email + token), acceptation/refus, retrait, départ.
- Contrainte: **une seule colocation active** par utilisateur.
- Gestion des dépenses: titre, montant, date, catégorie, payeur.
- Gestion des catégories de dépenses.
- Calcul automatique des balances et génération des remboursements simplifiés.
- Enregistrement des paiements via action **"Marquer payé"**.
- Système de réputation (`+1 / -1`) selon les cas de sortie/annulation avec ou sans dette.
- Dashboard admin global: statistiques, bannissement / débannissement.
- Filtre des dépenses par mois dans la vue d’une colocation.

### Hors périmètre (bonus)

- Paiement Stripe.
- Notifications temps réel.
- Calendrier.
- Export de données.

## Rôles utilisateurs

- **Member**: participe à une colocation, ajoute des dépenses, consulte soldes et remboursements.
- **Owner**: créateur/gestionnaire de la colocation, invite/retire des membres, annule la colocation.
- **Global Admin**: accès aux statistiques globales et à la modération des utilisateurs.

## Architecture et stack technique

- **Architecture**: Monolithique **Laravel MVC**.
- **Backend**: PHP / Laravel.
- **Base de données**: MySQL ou PostgreSQL via migrations.
- **ORM**: Eloquent (`hasMany`, `belongsToMany`, tables pivot).
- **Authentification**: Laravel Breeze / Jetstream (selon configuration du projet).
- **Frontend**: Blade + Tailwind CSS + JavaScript natif.

## Règles métier principales

- Un utilisateur ne peut pas avoir plusieurs memberships actifs.
- L’acceptation d’invitation vérifie la correspondance email/token.
- Les soldes sont recalculés automatiquement après chaque dépense/paiement.
- Si un membre quitte avec dette: pénalité de réputation.
- Si un owner retire un membre débiteur: dette imputée à l’owner (règle actuelle).
- Utilisateur banni: accès refusé + déconnexion forcée.

## Installation locale

```bash
git clone <URL_DU_REPO>
cd CarnetPro
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
php artisan serve
```

> Prérequis: PHP, Composer, Node.js, npm, MySQL/PostgreSQL.

## Structure attendue des livrables

- Lien du repository GitHub.
- Lien de la présentation.
- Diagrammes UML:
  - Diagramme de cas d’utilisation.
  - Diagramme de classes.

## Sécurité et qualité

- Respect de l’architecture MVC et des conventions Laravel.
- Validation côté serveur (`FormRequest` / `validate()`).
- Validation HTML5 côté client.
- Protection CSRF (`@csrf`).
- Protection XSS via échappement Blade (`{{ }}`).
- Requêtes sûres via Eloquent / Query Builder.
- Gestion d’autorisations par rôles (Admin / Owner / Member).
- Code lisible, maintenable, orienté objet.

## Axes d’amélioration (bonus)

- Intégrer Stripe pour les paiements réels.
- Ajouter des notifications temps réel.
- Ajouter un calendrier des dépenses.
- Proposer l’export CSV/PDF.

---

## Aperçu visuel

### Interface CarnetPro

![Capture CarnetPro](Images/CarnetPro.png)
