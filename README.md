# ClickBill

ClickBill est notre outil de gestion de devis et factures à destination des photographes et vidéastes indépendants et des TPE/PME du domaine. Générer des devis et factures simplement, programmez des relances de paiement et suivez votre clientèle.

## Membres du projet

KATASI Justin (justinDev91)    
JOUVET Erwann (ErwannJouvet)    
DA SILVA SOUSA Pedro (PedroDSS)    
GODARD Lucie (lucie1704)  

## Fonctionnalitées

### DA SILVA SOUSA Pedro
- Installation et configuration initiale de Tailwind CSS
- Page de Login (V2)
- Page de Register (V2)
- Page de Reset-Password
- Ajout de validateur custom pour vérifier si un mail existe (register).
- Ajout de validateur custom pour vérifier si un mail n'existe pas (reset-password).
- Hiérarchisation des rôles et configuration des accès sécurisés.
- Implémentation du système de mail avec Mailtrap (Local) et un DNS Gmail (production).
- Création de service d'envoie de mail avec et sans pièces jointes.
- Création de maquettes twig pour l'envoi de mail.
- Création du CRUD pour les devis avec envoie de mail.
- Ajout de GUID pour les devis.
- Endpoint pour gérer la réponse des clients via les mails concernant les devis et changer de status.
- Création du CRUD back-office pour la création d'entreprises.
- Création du CRUD back-office pour la création d'utilisateurs.
- Page de paramètres pour l'administration afin de choisir l'entreprise afin de se balader sur toutes les pages et fonctionnalitées.
- Ajout d'un listener pour empêcher les utilisateurs de se connecter si leur compte ou leur entreprise est désactiver (soft delete).
- Correction de features.

### JOUVET Erwann

### GODARD Lucie
- Intégration sidebar
- Création du CRUD factures
- intégration toast message pour les erreurs/succes
- Refacto template avec layouts 
- Refacto sideBar pour entreprise + comptable + backoffice
- custom form
- custom error page
- link fontawesome pour les icones
- link chaque rôle à leur environnement
- dark mode et switch
- page modification de profil

### KATASI Justin

# Symfony Docker (PHP8 / Caddy / Postgresql)

A [Docker](https://www.docker.com/)-based installer and runtime for the [Symfony](https://symfony.com) web framework, with full [HTTP/2](https://symfony.com/doc/current/weblink.html), HTTP/3 and HTTPS support.

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/)
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up` (the logs will be displayed in the current shell) or Run `docker compose up -d` to run in background 
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.
6. Run `docker compose logs -f` to display current logs, `docker compose logs -f [CONTAINER_NAME]` to display specific container's current logs 
