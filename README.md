# Documentation de l'API Utilisateurs & Commandes

Cette API permet de gérer les utilisateurs et leurs commandes.  
Elle offre les fonctionnalités suivantes :

- **Authentification** d’un utilisateur
- **Gestion des utilisateurs** (création, consultation, modification, suppression)
- **Gestion des commandes** (création, consultation, modification, suppression)

> **Note de Sécurité :**  
> Bien que l'API accepte le token d'authentification dans l'URL (paramètre `token`), il est recommandé en production de le transmettre dans l'en-tête HTTP pour éviter toute exposition du token dans les logs ou l'historique du navigateur.

---

## Table des Matières

- [Présentation générale](#présentation-générale)
- [Endpoints](#endpoints)
  - [Authentification](#authentification)
  - [Utilisateurs](#utilisateurs)
  - [Commandes](#commandes)
- [Spécification Swagger (OpenAPI 3.0)](#spécification-swagger-openapi-30)
- [Exemples d'utilisation](#exemples-dutilisation)
- [Déploiement sur GitHub Pages](#déploiement-sur-github-pages)

---

## Présentation générale

- **Base URL :** `http://localhost/api`  
  (Adaptez selon votre environnement.)
- **Authentification :**  
  Le token doit être passé en tant que paramètre dans l'URL, par exemple :  
