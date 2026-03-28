# Housing CM

Plateforme web de recherche de logement au Cameroun, developpee avec `PHP`, `MySQL`, `HTML`, `CSS`, `JavaScript` et `XAMPP`.

## Description

Housing CM aide :

- les clients a rechercher un logement
- les proprietaires a publier leurs biens
- les agents immobiliers a proposer des annonces
- les administrateurs a superviser la plateforme

Le projet repond a plusieurs difficultes reelles du marche immobilier au Cameroun :

- manque d informations claires
- difficultes de contact
- pertes de temps lors des visites
- annonces trompeuses
- besoin de filtrage par zone geographique et budget

## Fonctionnalites principales

- inscription et connexion
- gestion des roles : client, owner, agent, admin
- publication et modification d annonces
- gestion du statut des annonces
- recherche avec filtres, tri et pagination
- fiche detaillee d un logement
- galerie multi-images
- favoris
- comparaison de logements
- messagerie par conversation
- demande de visite
- signalement d annonces suspectes
- historique de recherche et suggestions simples
- tableau de bord administrateur
- moderation des utilisateurs et des annonces
- statistiques simples pour l administration

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
|-- actions/
|-- admin/
|-- assets/
|-- auth/
|-- config/
|-- database/
|-- includes/
|-- messages/
|-- properties/
|-- uploads/
|-- user/
|-- .gitignore
|-- index.php
`-- README.md
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

Le projet detecte automatiquement l environnement local et utilise :

```php
$host = 'localhost';
$dbName = 'housing_cm';
$dbUser = 'root';
$dbPass = '';
```

Il ajuste aussi automatiquement la base d URL locale :

```php
define('APP_BASE_PATH', '/housing-cm/');
```

### 5. Lancement

Ouvrir dans le navigateur :

```text
http://localhost/housing-cm/
```

## Mise en ligne

Le projet a ete adapte pour un hebergement gratuit compatible `PHP/MySQL`, par exemple **InfinityFree**.

En ligne, le projet detecte aussi automatiquement son environnement et bascule sur :

- la bonne base d URL
- les bons identifiants de base de donnees

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

Pour tester l espace administrateur, il faut attribuer le role `admin` a un compte dans la table `users`.

## Recette rapide

Une checklist de verification est disponible dans :

```text
QA-CHECKLIST.md
```

## Etat actuel du projet

Le projet couvre deja une base fonctionnelle solide :

- authentification
- annonces
- recherche
- images multiples
- favoris
- comparaison
- messagerie
- visites
- signalements
- administration active
- statistiques
- historique de recherche

## Evolutions futures possibles

- notifications temps reel
- geolocalisation sur carte
- verification avancee des profils
- commentaires et avis
- interface mobile encore plus fine
- paiement ou reservation plus tard si necessaire

## Auteur

Projet realise par **Noah12345578** avec accompagnement pedagogique et technique.
