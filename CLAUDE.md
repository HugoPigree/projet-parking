# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Parking Partagé - A shared parking management system built with Clean Architecture principles, SOLID principles, and MVC pattern. The project implements parking spot management, reservations, subscriptions, and user authentication with JWT.

## Development Commands

### Running the Application
```bash
php -S localhost:8000 -t public
```

### Testing
```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite Unit
vendor/bin/phpunit --testsuite Integration
vendor/bin/phpunit --testsuite Functional

# Run a single test file
vendor/bin/phpunit tests/Unit/CreateSubscriptionTest.php
```

### Dependencies
```bash
# Install dependencies
composer install

# Update dependencies
composer update
```

## Architecture

### Clean Architecture Layers

The project strictly follows Clean Architecture with clear separation of concerns:

1. **Domain Layer** (`src/Domain/`): Core business logic, entities, value objects, and interfaces
   - Entities: User, Parking, Reservation, ParkingSession, Subscription, Invoice, PricingRule
   - Value Objects: Email
   - Enums: UserRole
   - Service Interfaces: JwtServiceInterface, PasswordHasherInterface
   - Repository Interfaces: All repository interfaces are defined in Domain layer

2. **Use Case Layer** (`src/UseCase/`): Application business rules organized by entity
   - User: RegisterUser, LoginUser
   - Parking: CreateParking, UpdateParking, DeleteParking, SearchAvailableParkings, etc.
   - Reservation: CreateReservation, CancelReservation, ListUserReservations, GenerateInvoice
   - Subscription: CreateSubscription, CancelSubscription, ListUserSubscriptions
   - ParkingSession: EnterParking, ExitParking

3. **Infrastructure Layer** (`src/Infrastructure/`): Technical implementations
   - InMemory: In-memory repositories for testing (InMemoryUserRepository, InMemoryParkingRepository, etc.)
   - SQL: PDO-based repositories for production (PDOUserRepository, PDOParkingRepository, etc.)
   - Security: PasswordHasher, JwtService
   - Seed: DataSeeder for initial data

4. **Interface Layer** (`src/Interface/`): MVC presentation layer
   - Controllers: Handle HTTP requests and responses
   - Views: PHP templates for web UI
   - Router: Simple routing system
   - AuthMiddleware: JWT authentication middleware
   - routes.php: Route definitions using dependency container

### Dependency Injection Container

The application uses a custom dependency container in `src/Config/dependencies.php`:

- **Storage Types**:
  - `memory`: InMemory repositories (for unit tests only)
  - `sql`: PDO/SQL repositories (for production, default)

- **Switching Storage**:
  ```php
  Dependencies::setStorageType('memory'); // For tests
  Dependencies::setStorageType('sql');    // For production
  ```

- **Getting Dependencies**:
  ```php
  $controller = Dependencies::get('authController');
  $userRepo = Dependencies::get(UserRepositoryInterface::class);
  ```

### Repository Pattern

All repositories implement interfaces defined in the Domain layer (Dependency Inversion Principle):

- Domain interfaces: `UserRepositoryInterface`, `ParkingRepositoryInterface`, etc.
- InMemory implementations: For unit testing
- PDO implementations: For production with MySQL database

### Authentication & Authorization

- JWT-based authentication using `firebase/php-jwt`
- User roles: USER, OWNER (defined in UserRole enum)
- Password hashing with PHP's password_hash/password_verify
- AuthMiddleware for protecting routes
- Configuration in `src/Config/env.php` with JWT secret and expiration

### Routing System

Routes are defined in `src/Interface/routes.php`:
- Organized by HTTP method (GET, POST)
- Uses dependency container for controller instantiation
- Entry point: `public/index.php`
- Simple array-based routing (no dynamic parameters in current implementation)

## Database

### Configuration

Database config in `src/Config/database.php`:
- Default: MySQL on localhost
- Database: `parkingpartage`
- User: `root`, Password: empty

Can also use `.env` file for configuration (loaded by `src/Config/env.php`).

### Setup Scripts

- `setup_database.php`: Database initialization script
- `check_database.php`: Database verification script

## Testing Strategy

Tests are organized into three suites:

1. **Unit Tests** (`tests/Unit/`): Test individual use cases and domain logic
   - Use InMemory repositories
   - Test business rules in isolation
   - Examples: CreateSubscriptionTest, EnterParkingTest, AvailabilityServiceTest

2. **Integration Tests** (`tests/Integration/`): Test repository implementations
   - Test InMemory repository behavior
   - Example: InMemoryParkingRepositoryTest

3. **Functional Tests** (`tests/Functional/`): Test complete user scenarios
   - Test full workflows across multiple layers
   - Example: OwnerManagesParkingScenarioTest

### Test Configuration

Before running tests, set storage type to `memory`:
```php
Dependencies::setStorageType('memory');
Dependencies::reset(); // Clear container between tests
```

## Key Domain Concepts

### Parking Entity
- Contains GPS coordinates, total spots, opening hours, pricing rules
- Methods: `hasAvailableSpot()`, `isOpenAt()`, `calculateDistance()` (Haversine formula)
- Tracks reservation and stationnement IDs (not full objects - separation of concerns)

### Reservation Lifecycle
- States: PENDING, CONFIRMED, CANCELED, COMPLETED
- Tracks planned vs actual start/end times
- Methods: `confirm()`, `cancel()`, `complete()`

### User Entity
- Uses Email value object for validation
- UserRole enum (USER, OWNER)
- Password hash is never exposed directly, only via `verifyPassword()` with callback
- Converts to array without password for safe serialization

### Subscription System
- Users can subscribe to parkings
- Subscription management via dedicated use cases

## Code Style Notes

- PHP 8.1+ features used (enums, readonly properties where applicable)
- PSR-4 autoloading with `App\` namespace
- Value Objects for domain primitives (e.g., Email)
- Entities enforce invariants in constructors
- Use of DateTimeImmutable for User, DateTime for other entities
- Repository interfaces for all data access (never access database directly from use cases)
