# LiteCRM

Cette branche contient la version **serveur** de LiteCRM, optimisée pour le déploiement sur un environnement de production ou de préproduction.

## 🚀 Installation

1. Clonez le dépôt :
```bash
git clone https://github.com/Josephdeveze/LiteCRM.git
cd litecrm
```

2. Installez les dépendances avec Composer :
```bash
composer install
```

3. Configurez votre base de données MySQL :
```bash
mysql -u root -p < database/litecrm.sql
```

4. Configurez votre serveur web (XAMPP) :
- Placez le projet dans `/Applications/XAMPP/xamppfiles/htdocs/litecrm`
- Assurez-vous que Apache et MySQL sont démarrés

5. Connexion :
- Admin : admin@admin.fr
- Mots de passe : Admin13@

- Utilisateur : joseph.deveze.jd@gmail.com
- Mots de passe : Joseph12@

## 🛠 Structure du Projet

```
litecrm/
├── controllers/    # Contrôleurs de l'application
├── models/        # Modèles de données
├── views/         # Templates Twig
├── database/      # Scripts SQL et configuration
├── tests/         # Tests unitaires
└── public/        # Point d'entrée et assets
```

## 🧪 Tests

Pour exécuter les tests unitaires :

```bash
./vendor/bin/phpunit --testdox --colors=always tests/
```

## 📚 Documentation

### Fonctionnalités principales

- Gestion des utilisateurs (CRUD)
- Gestion des clients
- Gestion des rendez-vous
- Système d'authentification
- Gestion des rôles (admin/user)

### Technologies utilisées

- PHP 8.4
- MySQL
- Twig Template Engine
- AltoRouter
- PHPUnit

## 🔒 Sécurité

- Mots de passe hachés avec `password_hash()`
- Protection contre les injections SQL (PDO)
- Validation des données
- Gestion des sessions sécurisée

## 🤝 Contribution

1. Fork le projet
2. Créez votre branche (`git checkout -b feature/AmazingFeature`)
3. Commit vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## 📝 License

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.