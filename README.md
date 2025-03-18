# LiteCRM - Système de Gestion de Relations Clients

## 📋 Description
LiteCRM est une application web de gestion de relations clients (CRM) développée en PHP. Elle permet de gérer les clients, les rendez-vous et les utilisateurs de manière simple et efficace.

## ✨ Fonctionnalités

### 👥 Gestion des Clients
- Création et modification des fiches clients
- Liste complète des clients
- Historique des rendez-vous par client

### 📅 Gestion des Rendez-vous
- Calendrier interactif avec vue journalière/hebdomadaire/mensuelle
- Prise de rendez-vous avec plage horaire 8h-19h
- Statuts de rendez-vous (Prévu, Confirmé, Annulé, Terminé)
- Notifications visuelles selon le statut

### 👤 Gestion des Utilisateurs
- Deux niveaux d'accès : Administrateur et Utilisateur
- Création de comptes par l'administrateur uniquement
- Gestion des profils utilisateurs

## 🛠 Technologies Utilisées
- PHP 7.4+
- MySQL 5.7+
- HTML5/CSS3
- Bootstrap 5
- JavaScript
- FullCalendar
- Template Engine Twig

## ⚙️ Prérequis
- XAMPP (Apache, MySQL, PHP)
- Navigateur web moderne
- Git (pour le déploiement)

## 📥 Installation

1. Cloner le dépôt :
```bash
git clone https://github.com/Josephdeveze/LiteCRM.git
cd litecrm
```

2. Importer la base de données :
```bash
mysql -u root -p < database/litecrm.sql
```

3. Configurer l'accès à la base de données dans `config/database.php`

4. Placer le projet dans le dossier XAMPP :
```bash
mv litecrm /Applications/XAMPP/xamppfiles/htdocs/
```

## 🚀 Démarrage

1. Démarrer XAMPP :
```bash
sudo /Applications/XAMPP/xamppfiles/xampp start
```

2. Accéder à l'application :
```
http://localhost/litecrm
```

## 👤 Compte par défaut
- **Admin**
  - Email : admin@admin.fr
  - Mot de passe : Admin13@
- **Utilisateur**
  - Email : joseph.deveze.jd@gmail.com
  - Mot de passe : Joseph12@

## 📁 Structure du Projet
```
litecrm/
├── config/
├── controllers/
├── models/
├── views/
├── public/
│   ├── css/
│   ├── js/
│   └── img/
├── database/
└── index.php
```

## 🔐 Sécurité
- Protection contre les injections SQL
- Hashage des mots de passe
- Validation des formulaires
- Gestion des sessions
- Contrôle d'accès par rôle

## 📝 Licence
Ce projet est sous licence MIT.

## 👨‍💻 Auteur
[Joseph Deveze]
- GitHub : [@josephdeveze](https://github.com/Josephdeveze)
- Email : joseph.deveze.jd@gmail.com
