# QA Checklist

Cette checklist permet de verifier rapidement que la version locale est stable avant mise a jour de GitHub et d InfinityFree.

## 1. Authentification

- [ ] inscription d un nouveau compte
- [ ] connexion avec compte valide
- [ ] refus de connexion avec mauvais mot de passe
- [ ] deconnexion correcte
- [ ] blocage d acces aux pages privees si non connecte

## 2. Gestion des annonces

- [ ] creation d une annonce
- [ ] ajout de plusieurs images
- [ ] affichage dans `Mes annonces`
- [ ] modification d une annonce
- [ ] changement d image principale
- [ ] suppression d une image
- [ ] suppression d une annonce
- [ ] changement du statut d une annonce

## 3. Recherche et detail

- [ ] recherche simple
- [ ] filtres par ville, quartier, type, budget
- [ ] tri des resultats
- [ ] pagination
- [ ] ouverture correcte de la fiche detail
- [ ] affichage des annonces similaires

## 4. Interactions utilisateur

- [ ] ajout / retrait des favoris
- [ ] ajout / retrait dans la comparaison
- [ ] affichage correct de la page comparaison
- [ ] envoi de message
- [ ] ouverture de la conversation
- [ ] reponse dans la conversation
- [ ] demande de visite
- [ ] suivi du statut de visite
- [ ] signalement d une annonce

## 5. Tableau de bord utilisateur

- [ ] affichage du profil
- [ ] modification des informations du profil
- [ ] changement de mot de passe
- [ ] enregistrement de l historique de recherche
- [ ] affichage des suggestions sur le dashboard

## 6. Administration

- [ ] acces reserve au role admin
- [ ] visualisation des utilisateurs
- [ ] changement de role
- [ ] blocage / reactivation d un compte
- [ ] moderation des annonces
- [ ] traitement des signalements
- [ ] affichage des statistiques

## 7. Cas limites

- [ ] ouverture d une annonce inexistante
- [ ] ouverture d une conversation invalide
- [ ] test visuel des pages d erreur
- [ ] verification locale si MySQL est arrete

## 8. Avant publication

- [ ] `git status` propre ou changements compris
- [ ] verification manuelle locale finale
- [ ] copie des derniers fichiers dans `C:\xampp\htdocs\housing-cm`
- [ ] mise a jour GitHub
- [ ] mise a jour InfinityFree
