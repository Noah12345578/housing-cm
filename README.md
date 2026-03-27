# Housing CM

Plateforme web de recherche de logement au Cameroun, developpee avec `PHP`, `MySQL`, `HTML`, `CSS`, `JavaScript` et `XAMPP`.

## Description

Housing CM est un projet de site web immobilier concu pour aider :

- les clients a rechercher un logement
- les proprietaires a publier leurs biens
- les agents immobiliers a proposer des annonces
- les administrateurs a superviser la plateforme

Le projet prend en compte plusieurs realites du marche immobilier au Cameroun :

- manque d informations claires
- difficultes de contact
- pertes de temps lors des visites
- annonces trompeuses
- besoin de filtrage par zone geographique et budget

## Fonctionnalites principales

- inscription et connexion
- gestion des roles : client, owner, agent, admin
- publication d annonces immobilieres
- recherche avec filtres
- fiche detaillee d un logement
- upload d image principale
- favoris
- messagerie interne simple
- demande de visite
- signalement d annonces suspectes
- tableau de bord administrateur
- liste des utilisateurs, annonces et signalements

## Technologies utilisees

- PHP
- MySQL
- HTML
- CSS
- JavaScript
- XAMPP

## Structure du projet

```text
housing-cm/
├── actions/
├── admin/
├── assets/
├── auth/
├── config/
├── database/
├── includes/
├── messages/
├── properties/
├── uploads/
├── user/
├── .gitignore
├── index.php
└── README.md
```

## Installation en local

### 1. Prerequis

- XAMPP installe
- Apache et MySQL demarres

### 2. Placement du projet

Placer le projet dans :

```text
C:\xampp\htdocs\housing-cm
```

### 3. Base de donnees

1. Ouvrir `phpMyAdmin`
2. Creer une base nommee `housing_cm`
3. Importer le fichier :

```text
database/schema.sql
```

### 4. Configuration locale

Creer ou adapter le fichier :

```text
config/database.php
```

Avec des valeurs locales de type :

```php
$host = 'localhost';
$dbName = 'housing_cm';
$dbUser = 'root';
$dbPass = '';
```

Verifier aussi :

```php
define('APP_BASE_PATH', '/housing-cm/');
```

dans :

```text
config/app.php
```

### 5. Lancement

Ouvrir dans le navigateur :

```text
http://localhost/housing-cm/
```

## Mise en ligne

Le projet a ete adapte pour un hebergement gratuit compatible `PHP/MySQL`, par exemple **InfinityFree**.

### Configuration en ligne

Dans :

```text
config/app.php
```

mettre :

```php
define('APP_BASE_PATH', '/');
```

Puis adapter :

```text
config/database.php
```

avec les identifiants MySQL de l hebergeur.

## GitHub et securite

Le fichier `config/database.php` est ignore par Git via `.gitignore`.

Un fichier exemple est fourni :

```text
config/database.example.php
```

Cela permet de partager le projet sans exposer les vraies informations de connexion a la base de donnees.

## Comptes et roles

Le systeme gere les roles suivants :

- `client`
- `owner`
- `agent`
- `admin`

Pour tester l espace administrateur, il faut modifier le role d un compte dans la table `users`.

## Etat du projet

Le projet couvre deja une base fonctionnelle importante :

- authentification
- annonces
- recherche
- images
- favoris
- messages
- visites
- signalements
- administration de base

## Evolutions futures possibles

- modification et suppression d annonces
- gestion de plusieurs images par logement
- commentaires et avis
- geolocalisation
- notifications
- tableau de bord plus avance
- verification des profils

## Auteur

Projet realise par **Noah12345578** avec accompagnement pedagogique et technique.
