# 🚗 Parking Partagé - Clean Architecture

Système de gestion de parkings partagés développé avec **Clean Architecture**, **SOLID** et **PHP 8.3**.

## 📋 Table des matières

- [Objectifs pédagogiques](#objectifs-pédagogiques)
- [Fonctionnalités](#fonctionnalités)
- [Architecture](#architecture)
- [Installation avec Docker](#installation-avec-docker)
- [Installation locale](#installation-locale)
- [Tests](#tests)
- [Clean Architecture - Interchangeabilité](#clean-architecture---interchangeabilité)

---

## 🎯 Objectifs pédagogiques

- **Clean Architecture** : Séparation stricte des couches (Domain, UseCase, Infrastructure, Interface)
- **Principes SOLID** : Single Responsibility, Dependency Inversion, etc.
- **Repository Pattern** : Abstraction de la couche de persistance
- **Dependency Injection** : Container de dépendances personnalisé
- **Tests** : Unitaires, d'intégration et fonctionnels avec PHPUnit
- **Interchangeabilité** : Basculer entre SQL et fichiers JSON sans modifier le domaine

---

## ✨ Fonctionnalités

### Pour les utilisateurs
- Créer un compte et s'authentifier (JWT)
- Rechercher des parkings autour d'une position GPS
- Réserver une place de parking
- Souscrire un abonnement mensuel/annuel
- Entrer et sortir d'un parking
- Consulter ses réservations et stationnements
- Générer des factures (HTML/PDF)

### Pour les propriétaires
- Créer et gérer des parkings
- Définir les horaires d'ouverture et tarifs
- Consulter les réservations et stationnements
- Voir le chiffre d'affaires mensuel
- Gérer les types d'abonnements

### Règles métier
- **Pénalités** : +20€ fixe + temps supplémentaire facturé par tranche de 15 min
- **Comptage des places** : Réservations et abonnements occupent des places
- **Horaires d'ouverture** : Validation des créneaux
- **Facturation** : Par tranche de 15 minutes avec tarifs évolutifs

---

## 🏗️ Architecture

### Structure des dossiers

```
projet-parking/
├── src/
│   ├── Domain/           # Règles métier, entités, interfaces
│   │   ├── Entity/       # User, Parking, Reservation, ParkingSession, Subscription, Invoice
│   │   ├── Repository/   # Interfaces des repositories
│   │   ├── Service/      # Services métier (PricingService, AvailabilityService)
│   │   └── ValueObject/  # Email, PricingRule
│   ├── UseCase/          # Cas d'utilisation métier
│   │   ├── User/         # RegisterUser, LoginUser
│   │   ├── Parking/      # CreateParking, SearchAvailableParkings
│   │   ├── Reservation/  # CreateReservation, GenerateInvoice
│   │   ├── Subscription/ # CreateSubscription, ListUserSubscriptions
│   │   └── ParkingSession/ # EnterParking, ExitParking
│   ├── Infrastructure/   # Implémentations techniques
│   │   ├── InMemory/     # Repositories en mémoire (tests)
│   │   ├── Repository/   # Repositories PDO (production)
│   │   ├── SQL/          # Database, schema.sql
│   │   └── Security/     # PasswordHasher, JwtService
│   ├── Interface/        # Couche présentation (MVC)
│   │   ├── Controller/   # Contrôleurs HTTP
│   │   ├── View/         # Templates PHP
│   │   └── routes.php    # Définition des routes
│   └── Config/           # Configuration de l'application
├── public/
│   └── index.php         # Point d'entrée HTTP
├── tests/
│   ├── Unit/             # Tests unitaires
│   ├── Integration/      # Tests d'intégration
│   └── Functional/       # Tests fonctionnels
├── storage/              # Données JSON (mode file)
├── Dockerfile
├── docker-compose.yml
└── .env.example
```

### Layers Clean Architecture

```
┌─────────────────────────────────────────┐
│         Interface (Controllers)         │
│              HTTP / CLI                 │
└────────────────┬────────────────────────┘
                 │
┌────────────────▼────────────────────────┐
│           Use Cases                     │
│    (Business Application Rules)         │
└────────────────┬────────────────────────┘
                 │
┌────────────────▼────────────────────────┐
│            Domain                       │
│     (Enterprise Business Rules)         │
│   Entities • Value Objects • Services   │
└────────────────┬────────────────────────┘
                 │
┌────────────────▼────────────────────────┐
│        Infrastructure                   │
│   DB • APIs • Files • Security          │
└─────────────────────────────────────────┘
```

---

## 🐳 Installation avec Docker (Recommandé)

### Prérequis
- Docker
- Docker Compose

### 1. Cloner le projet
```bash
git clone <url-du-repo>
cd projet-parking
```

### 2. Configurer l'environnement
```bash
cp .env.example .env
```

Éditez `.env` si nécessaire :
```env
# Mode de stockage : 'sql' ou 'file'
STORAGE_MODE=sql

# Configuration MySQL
DB_HOST=mysql
DB_NAME=parking_partage
DB_USER=parking_user
DB_PASSWORD=parking_password

# JWT
JWT_SECRET=your-super-secret-key-change-in-production
```

### 3. Démarrer l'application
```bash
# Construire et démarrer tous les services
docker-compose up -d --build

# Vérifier que tout fonctionne
docker-compose ps
```

### 4. Accéder à l'application

- **Application** : http://localhost:8000
- **PHPMyAdmin** : http://localhost:8080
  - Serveur : `mysql`
  - User : `parking_user`
  - Password : `parking_password`

### 5. Arrêter l'application
```bash
# Arrêter les services
docker-compose down

# Arrêter et supprimer les volumes (⚠️ supprime les données)
docker-compose down -v
```

---

## 💻 Installation locale (sans Docker)

### Prérequis
- PHP 8.1+
- MySQL 8.0+
- Composer

### 1. Installer les dépendances
```bash
composer install
```

### 2. Configurer la base de données
```bash
# Créer la base de données
mysql -u root -p < src/Infrastructure/SQL/schema.sql
```

### 3. Configurer l'environnement
```bash
cp .env.example .env
```

Éditez `.env` :
```env
STORAGE_MODE=sql
DB_HOST=localhost
DB_NAME=parking_partage
DB_USER=root
DB_PASSWORD=votre_password
```

### 4. Lancer le serveur
```bash
php -S localhost:8000 -t public
```

Accédez à http://localhost:8000

---

## 🧪 Tests

### Lancer tous les tests
```bash
# Avec Docker
docker-compose exec app vendor/bin/phpunit

# En local
vendor/bin/phpunit
```

### Lancer une suite spécifique
```bash
# Tests unitaires
vendor/bin/phpunit --testsuite Unit

# Tests d'intégration
vendor/bin/phpunit --testsuite Integration

# Tests fonctionnels
vendor/bin/phpunit --testsuite Functional
```

### Tests avec couverture
```bash
vendor/bin/phpunit --coverage-html coverage
```

### Exemples de tests disponibles
- **PenaltyCalculationTest** : Calcul des pénalités (+20€ + surcoût)
- **InvoiceWithPenaltyTest** : Facturation avec pénalités
- **EnterParkingTest** : Entrée dans un parking
- **ExitParkingTest** : Sortie avec calcul automatique pénalité
- **OwnerManagesParkingScenarioTest** : Scénario complet propriétaire

---

## 🔄 Clean Architecture - Interchangeabilité

L'un des objectifs principaux est de **prouver que l'architecture fonctionne indépendamment de la couche de persistance**.

### Basculer entre SQL et Fichiers JSON

#### Mode SQL (Production)
```bash
# Dans .env
STORAGE_MODE=sql
```
Utilise MySQL avec les repositories PDO :
- `PDOUserRepository`
- `PDOParkingRepository`
- `PDOReservationRepository`
- etc.

#### Mode File (Tests/Développement)
```bash
# Dans .env
STORAGE_MODE=file
```
Utilise des fichiers JSON dans `/storage` :
- `FileBasedUserRepository`
- `FileBasedParkingRepository`
- `FileBasedReservationRepository`
- etc.

### Test d'interchangeabilité

```bash
# 1. Démarrer en mode SQL
STORAGE_MODE=sql docker-compose up -d

# 2. Créer des données (utilisateurs, parkings, réservations)
# via l'interface web ou l'API

# 3. Arrêter l'application
docker-compose down

# 4. Redémarrer en mode fichier
STORAGE_MODE=file docker-compose up -d

# 5. Vérifier que l'application fonctionne toujours
# (les données seront vides mais l'application doit fonctionner)
```

**Le domaine métier ne change PAS**, seule l'implémentation du repository change !

---

## 🔐 Authentification

L'application utilise **JWT** (JSON Web Tokens) pour l'authentification.

### Créer un compte
```bash
POST /api/register
{
  "email": "user@example.com",
  "password": "password123",
  "nom": "Doe",
  "prenom": "John",
  "role": "USER"  # ou "OWNER"
}
```

### Se connecter
```bash
POST /api/login
{
  "email": "user@example.com",
  "password": "password123"
}
```

Réponse :
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": { ... }
}
```

### Utiliser le token
```bash
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

## 📚 Documentation supplémentaire

- **CLAUDE.md** : Guide pour Claude Code
- **RAPPORT_CONFORMITE.md** : Analyse de conformité du projet
- **PLAN_ACTIONS_CORRECTIVES.md** : Actions pour améliorer le projet
- **PROGRESSION_PLAN.md** : Plan de progression et checklist

---

## 🛠️ Commandes utiles Docker

```bash
# Voir les logs
docker-compose logs -f app

# Entrer dans le container PHP
docker-compose exec app bash

# Installer une dépendance
docker-compose exec app composer require package/name

# Réinitialiser la base de données
docker-compose down -v
docker-compose up -d

# Exécuter les tests
docker-compose exec app vendor/bin/phpunit
```

---

## 🤝 Contribution

### Standards de code
- PHP 8.1+
- PSR-4 Autoloading
- PSR-12 Coding Style
- Clean Architecture
- SOLID Principles

### Avant de commit
```bash
# Vérifier les tests
vendor/bin/phpunit

# Vérifier la syntaxe
php -l src/**/*.php
```

---

## 📄 Licence

Projet académique - HETIC

---

## 🆘 Problèmes courants

### MySQL ne démarre pas
```bash
# Supprimer les volumes et redémarrer
docker-compose down -v
docker-compose up -d
```

### Permission denied sur /storage
```bash
docker-compose exec app chmod -R 777 storage
```

### Composer install échoue
```bash
docker-compose exec app composer install --no-cache
```

---

## Team

- Baboye Drame
- Hugo Pigree
- Charles Grossin
- Louis Dondey