# Plan de Développement — Système de Parking Partagé

## Principe d'organisation
- Chaque dev gère 1-2 entités de bout en bout (Domain → Use Cases → Infrastructure → Interface)
- Approche verticale : responsabilité complète sur son périmètre
- 4 développeurs travaillent en parallèle sur des domaines fonctionnels distincts

---

## Développeur 1 — Gestion Utilisateurs & Authentification

### Domain (`src/Domain/Entity/`)
- ✅ **User.php** (à compléter)
  - Properties: id, email, password, role (USER/OWNER), createdAt
  - Methods: getId(), getEmail(), getRole(), isOwner()

### Infrastructure
**Repository** (`src/Infrastructure/Repository/`)
- ✅ **UserRepositoryInterface.php** (à compléter)
  - Methods: findById(), findByEmail(), save(), delete()

**InMemory** (`src/Infrastructure/InMemory/`)
- ✅ **InMemoryUserRepository.php** (à compléter)

**SQL** (`src/Infrastructure/SQL/`)
- ❌ **Database.php** (à créer - connexion PDO)
- ❌ **PDOUserRepository.php** (à créer)

**Security** (`src/Infrastructure/Security/`)
- ❌ **PasswordHasher.php** (à créer)
- ❌ **JwtService.php** (à créer)

### Use Cases (`src/UseCase/User/`)
- ✅ **LoginUser.php** (à compléter)
- ❌ **RegisterUser.php** (à créer)

### Interface (`src/Interface/Controller/`)
- ✅ **AuthController.php** (à compléter)
  - login(), register(), logout()

### Tests
- ❌ **Unit/Domain/Entity/UserTest.php**
- ❌ **Unit/Infrastructure/Security/JwtServiceTest.php**
- ❌ **Integration/InMemoryUserRepositoryTest.php**
- ❌ **Functional/UserAuthenticationScenarioTest.php**

**Total : ~13 fichiers**

---

## Développeur 2 — Gestion Parkings

### Domain (`src/Domain/Entity/`)
- ✅ **Parking.php** (à compléter)
  - Properties: id, ownerId, name, address, latitude, longitude, totalSpots, openingHours, pricingRules
  - Methods: hasAvailableSpot(), isOpenAt(), getAvailableSpots()

- ❌ **PricingRule.php** (à créer - Value Object)
  - Properties: intervalMinutes, pricePerInterval
  - Methods: getPriceForDuration()

### Domain Services (`src/Domain/Service/`)
- ✅ **AvailabilityService.php** (à compléter)
  - Methods: isAvailable(), getAvailableSpots(), canReserve()

### Infrastructure
**Repository** (`src/Infrastructure/Repository/`)
- ✅ **ParkingRepositoryInterface.php** (à compléter)
  - Methods: findById(), save(), delete(), findByOwner(), findNearby()

**InMemory** (`src/Infrastructure/InMemory/`)
- ✅ **InMemoryParkingRepository.php** (à compléter)

**SQL** (`src/Infrastructure/SQL/`)
- ❌ **PDOParkingRepository.php** (à créer)

### Use Cases (`src/UseCase/Parking/`)
- ✅ **SearchAvailableParkings.php** (à compléter)
- ❌ **CreateParking.php** (à créer)
- ❌ **UpdateParking.php** (à créer)
- ❌ **GetParkingAvailability.php** (à créer)
- ❌ **GetParkingRevenue.php** (à créer)

### Interface (`src/Interface/Controller/`)
- ❌ **ParkingController.php** (à créer)
  - search(), create(), update(), show(), availability(), revenue()

### Tests
- ❌ **Unit/Domain/Entity/ParkingTest.php**
- ❌ **Unit/Domain/Service/AvailabilityServiceTest.php**
- ❌ **Integration/InMemoryParkingRepositoryTest.php**
- ❌ **Functional/OwnerManagesParkingScenarioTest.php**

**Total : ~15 fichiers**

---

## Développeur 3 — Gestion Réservations & Facturation

### Domain (`src/Domain/Entity/`)
- ✅ **Reservation.php** (à compléter)
  - Properties: id, userId, parkingId, startTime, endTime, price, status, penalty
  - Methods: cancel(), isActive(), isExpired(), calculateOvertime()

- ❌ **Invoice.php** (à créer)
  - Properties: id, reservationId, amount, penalty, items[], generatedAt
  - Methods: generateHTML(), addItem(), getTotal()

### Domain Services (`src/Domain/Service/`)
- ✅ **PricingService.php** (à compléter)
  - Methods: calculatePrice(), calculatePenalty(), calculate15MinIntervals()

- ❌ **InvoiceService.php** (à créer)
  - Methods: generateInvoice(), generatePDF()

### Infrastructure
**Repository** (`src/Infrastructure/Repository/`)
- ✅ **ReservationRepositoryInterface.php** (à compléter)
- ❌ **InvoiceRepositoryInterface.php** (à créer)

**InMemory** (`src/Infrastructure/InMemory/`)
- ✅ **InMemoryReservationRepository.php** (à compléter)
- ❌ **InMemoryInvoiceRepository.php** (à créer)

**SQL** (`src/Infrastructure/SQL/`)
- ❌ **PDOReservationRepository.php** (à créer)
- ❌ **PDOInvoiceRepository.php** (à créer)

### Use Cases
- ✅ **Parking/CreateReservation.php** (à compléter)
- ✅ **Reservation/ListUserReservations.php** (à compléter)
- ❌ **Reservation/CancelReservation.php** (à créer)
- ❌ **Reservation/GenerateInvoice.php** (à créer)

### Interface (`src/Interface/Controller/`)
- ✅ **ReservationController.php** (à compléter)
  - list(), create(), cancel(), invoice()

### Tests
- ❌ **Unit/Domain/Entity/ReservationTest.php**
- ❌ **Unit/Domain/Service/PricingServiceTest.php**
- ❌ **Integration/InMemoryReservationRepositoryTest.php**
- ❌ **Functional/UserReservationScenarioTest.php**

**Total : ~16 fichiers**

---

## Développeur 4 — Gestion Abonnements & Sessions de Stationnement

### Domain (`src/Domain/Entity/`)
- ❌ **Subscription.php** (à créer)
  - Properties: id, userId, parkingId, startDate, endDate, weeklySchedule, monthsDuration
  - Methods: coversDateTime(), isActiveAt(), getWeeklySchedule()

- ❌ **ParkingSession.php** (à créer)
  - Properties: id, userId, parkingId, entryTime, exitTime, reservationId, subscriptionId
  - Methods: enter(), exit(), isActive(), getDuration()

### Infrastructure
**Repository** (`src/Infrastructure/Repository/`)
- ❌ **SubscriptionRepositoryInterface.php** (à créer)
- ❌ **ParkingSessionRepositoryInterface.php** (à créer)

**InMemory** (`src/Infrastructure/InMemory/`)
- ❌ **InMemorySubscriptionRepository.php** (à créer)
- ❌ **InMemoryParkingSessionRepository.php** (à créer)

**SQL** (`src/Infrastructure/SQL/`)
- ❌ **PDOSubscriptionRepository.php** (à créer)
- ❌ **PDOParkingSessionRepository.php** (à créer)

### Use Cases
**Subscription** (`src/UseCase/Subscription/`)
- ❌ **CreateSubscription.php** (à créer)
- ❌ **ListUserSubscriptions.php** (à créer)
- ❌ **CancelSubscription.php** (à créer)

**ParkingSession** (`src/UseCase/ParkingSession/`)
- ❌ **EnterParking.php** (à créer)
- ❌ **ExitParking.php** (à créer)

### Interface (`src/Interface/Controller/`)
- ❌ **SubscriptionController.php** (à créer)
  - list(), create(), cancel()

**API** (`src/Interface/Api/`)
- ❌ **ApiParkingSessionController.php** (à créer)
  - enter(), exit()

### Tests
- ❌ **Unit/Domain/Entity/SubscriptionTest.php**
- ❌ **Unit/Domain/Entity/ParkingSessionTest.php**
- ❌ **Integration/InMemorySubscriptionRepositoryTest.php**
- ❌ **Functional/OwnerManagesSubscriptionsScenarioTest.php**

**Total : ~16 fichiers**

---

## Infrastructure commune (à se répartir)

### Base de données SQL
- ❌ **src/Infrastructure/SQL/schema.sql** (Dev 1)

### Configuration
- ✅ **composer.json** (Dev 1 - ajouter dépendances JWT, PDF)
- ✅ **src/Config/env.php** (Dev 1)
- ❌ **src/Config/database.php** (Dev 1)
- ❌ **src/Config/dependencies.php** (Dev 1)
- ❌ **.env.example** (Dev 1)

### Routing & Bootstrap
- ✅ **src/Interface/routes.php** (Dev 4)
- ❌ **src/Interface/Router.php** (Dev 4)
- ❌ **src/Interface/AuthMiddleware.php** (Dev 1)
- ✅ **public/index.php** (Dev 4)

### Seeding
- ❌ **src/Infrastructure/Seed/DataSeeder.php** (Dev 2)

### Vues HTML
- ✅ **src/Interface/View/login.php** (Dev 1)
- ✅ **src/Interface/View/home.php** (Dev 2)
- ❌ **src/Interface/View/register.php** (Dev 1)
- ❌ **src/Interface/View/parking_search.php** (Dev 2)
- ❌ **src/Interface/View/parking_form.php** (Dev 2)
- ✅ **src/Interface/View/reservation_form.php** (Dev 3)
- ✅ **src/Interface/View/reservations.php** (Dev 3)
- ❌ **src/Interface/View/invoice.php** (Dev 3)
- ❌ **src/Interface/View/subscription_form.php** (Dev 4)
- ❌ **src/Interface/View/owner_dashboard.php** (Dev 2)

---

## Récapitulatif

| Développeur | Entité(s) | Fichiers | Responsabilité |
|-------------|-----------|----------|----------------|
| Dev 1 | User | ~13 | Auth, JWT, Security, Config, Database |
| Dev 2 | Parking, PricingRule | ~15 | Parkings, Disponibilités, Recherche |
| Dev 3 | Reservation, Invoice | ~16 | Réservations, Facturation, Pricing |
| Dev 4 | Subscription, ParkingSession | ~16 | Abonnements, Entrée/Sortie, Routing |

**Total : ~60 fichiers + vues + config**

---

## Ordre de développement recommandé

1. **Phase 1** : Entités + Repositories InMemory
2. **Phase 2** : Use Cases + Services
3. **Phase 3** : Repositories SQL + Database
4. **Phase 4** : Controllers + Routing + Auth
5. **Phase 5** : Vues + Tests
