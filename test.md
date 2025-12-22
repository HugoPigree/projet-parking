# Plan de Développement — Système de Parking Partagé

## Développeur 1 — Domain (Entities + Services)

**Localisation :** `src/Domain/`

### Entités à compléter/créer (`src/Domain/Entity/`)
- ✅ **User.php** (vide - à compléter)
- ✅ **Parking.php** (vide - à compléter)
- ✅ **Reservation.php** (vide - à compléter)
- ❌ **Subscription.php** (à créer)
- ❌ **ParkingSession.php** (à créer)
- ❌ **Invoice.php** (à créer)
- ❌ **PricingRule.php** (à créer)

### Services à compléter/créer (`src/Domain/Service/`)
- ✅ **AvailabilityService.php** (vide - à compléter)
- ✅ **PricingService.php** (vide - à compléter)
- ❌ **InvoiceService.php** (à créer)

---

## Développeur 2 — Use Cases

**Localisation :** `src/UseCase/`

### À compléter
- ✅ **User/LoginUser.php** (vide)
- ✅ **Parking/SearchAvailableParkings.php** (vide)
- ✅ **Parking/CreateReservation.php** (vide)
- ✅ **Reservation/ListUserReservations.php** (vide)

### À créer
- ❌ **User/RegisterUser.php**
- ❌ **Parking/CreateParking.php**
- ❌ **Parking/UpdateParking.php**
- ❌ **Parking/UpdatePricingRules.php**
- ❌ **Parking/GetParkingRevenue.php**
- ❌ **Parking/GetParkingAvailability.php**
- ❌ **Reservation/CancelReservation.php**
- ❌ **Reservation/GenerateInvoice.php**
- ❌ **ParkingSession/EnterParking.php**
- ❌ **ParkingSession/ExitParking.php**
- ❌ **Subscription/CreateSubscription.php**
- ❌ **Subscription/ListUserSubscriptions.php**
- ❌ **Subscription/CancelSubscription.php**

---

## Développeur 3 — Infrastructure

**Localisation :** `src/Infrastructure/`

### Interfaces Repository à compléter (`src/Infrastructure/Repository/`)
- ✅ **UserRepositoryInterface.php** (vide)
- ✅ **ParkingRepositoryInterface.php** (vide)
- ✅ **ReservationRepositoryInterface.php** (vide)

### Interfaces Repository à créer
- ❌ **SubscriptionRepositoryInterface.php**
- ❌ **ParkingSessionRepositoryInterface.php**
- ❌ **InvoiceRepositoryInterface.php**

### Implémentations InMemory à compléter (`src/Infrastructure/InMemory/`)
- ✅ **InMemoryUserRepository.php** (vide)
- ✅ **InMemoryParkingRepository.php** (vide)
- ✅ **InMemoryReservationRepository.php** (vide)

### Implémentations InMemory à créer
- ❌ **InMemorySubscriptionRepository.php**
- ❌ **InMemoryParkingSessionRepository.php**
- ❌ **InMemoryInvoiceRepository.php**

### Implémentations SQL à créer (`src/Infrastructure/SQL/`)
- ❌ **PDOUserRepository.php**
- ❌ **PDOParkingRepository.php**
- ❌ **PDOReservationRepository.php**
- ❌ **PDOSubscriptionRepository.php**
- ❌ **PDOParkingSessionRepository.php**
- ❌ **PDOInvoiceRepository.php**
- ❌ **Database.php**
- ❌ **schema.sql**

### Sécurité à créer (`src/Infrastructure/Security/`)
- ❌ **JwtService.php**
- ❌ **PasswordHasher.php**

### Seeding à créer (`src/Infrastructure/Seed/`)
- ❌ **DataSeeder.php**

---

## Développeur 4 — Interface / MVC

**Localisation :** `src/Interface/`

### Controllers à compléter (`src/Interface/Controller/`)
- ✅ **AuthController.php** (vide)
- ✅ **HomeController.php** (vide)
- ✅ **ReservationController.php** (vide)

### Controllers à créer
- ❌ **ParkingController.php**
- ❌ **SubscriptionController.php**
- ❌ **ParkingSessionController.php**
- ❌ **OwnerDashboardController.php**

### API Controllers à créer (`src/Interface/Api/`)
- ❌ **ApiAuthController.php**
- ❌ **ApiParkingController.php**
- ❌ **ApiReservationController.php**
- ❌ **ApiParkingSessionController.php**

### Vues à compléter (`src/Interface/View/`)
- ✅ **login.php** (vide)
- ✅ **home.php** (vide)
- ✅ **reservation_form.php** (vide)
- ✅ **reservations.php** (vide)

### Vues à créer
- ❌ **register.php**
- ❌ **parking_search.php**
- ❌ **parking_details.php**
- ❌ **subscription_form.php**
- ❌ **subscriptions.php**
- ❌ **invoice.php**
- ❌ **owner_dashboard.php**
- ❌ **parking_form.php**
- ❌ **parking_stats.php**

### Routing et Middleware à compléter/créer
- ✅ **routes.php** (vide)
- ❌ **Router.php**
- ❌ **AuthMiddleware.php**

### Point d'entrée à compléter
- ✅ **public/index.php** (vide)

### Utilitaires à créer
- ❌ **Session/SessionManager.php**
- ❌ **Middleware/ErrorHandler.php**
- ❌ **Response/JsonResponse.php**
- ❌ **Response/HtmlResponse.php**

---

## Configuration

### À compléter
- ✅ **composer.json** (ajouter dépendances JWT, PDF)
- ✅ **src/Config/env.php** (vide)

### À créer
- ❌ **.env.example**
- ❌ **src/Config/database.php**
- ❌ **src/Config/app.php**
- ❌ **src/Config/dependencies.php**

---

## Tests

### À créer dans `tests/`
- ❌ **Unit/Domain/Entity/** (tests entités)
- ❌ **Unit/Domain/Service/** (tests services)
- ❌ **Unit/UseCase/** (tests use cases)
- ❌ **Functional/** (4 scénarios minimum)
- ❌ **Integration/** (repositories, API)

---

## Récapitulatif

| Développeur | À créer | À compléter | Total |
|-------------|---------|-------------|-------|
| Dev 1 (Domain) | 4 | 6 | 10 |
| Dev 2 (Use Cases) | 13 | 4 | 17 |
| Dev 3 (Infrastructure) | 17 | 6 | 23 |
| Dev 4 (Interface) | 18 | 7 | 25 |

**Total : ~75 fichiers**
