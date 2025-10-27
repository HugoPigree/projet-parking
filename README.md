# Parking Partagé - Squelette Clean Architecture

Ce projet est un squelette prêt pour démarrer le projet Parking Partagé.

Objectifs pédagogiques :
- Respect de la Clean Architecture
- Respect des principes SOLID
- Séparation claire des responsabilités
- MVC pour la couche interface web
- Use cases isolés
- Repositories abstraits (interfaces) pour inversion de dépendance

IMPORTANT :
- Il n'y a volontairement PAS de code métier complet.
- Il n'y a PAS de base de données branchée.
- À vous d'implémenter la logique dans les classes existantes.

## Lancer en local (exemple futur)
php -S localhost:8000 -t public

## Structure des dossiers

src/
  Domain/        -> Règles métier, entités, interfaces de repo
  UseCase/       -> Scénarios d'application (cas d'utilisation)
  Infrastructure/-> Implémentations techniques (ici: InMemory)
  Interface/     -> MVC : contrôleurs + vues + routing
  Config/        -> config globale

public/
  index.php      -> point d'entrée HTTP

tests/
  Unit/          -> tests unitaires de vos services métier plus tard
