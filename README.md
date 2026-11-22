
# 🎓 Gestion des Absences des Stagiaires

Application web de gestion et de suivi des absences des stagiaires dans un établissement de formation professionnelle.

Ce projet vise à digitaliser la gestion des absences en remplaçant les méthodes traditionnelles (papier, fichiers Excel) par une plateforme centralisée, moderne et accessible selon le rôle de chaque utilisateur.

## 📌 Présentation du projet

L'application permet aux formateurs d'enregistrer les absences pendant leurs séances de formation et aux responsables administratifs de suivre, justifier et analyser ces absences.

Elle intègre également la gestion des années scolaires, des utilisateurs, des groupes, des filières, des modules et des affectations des formateurs.

## 🎯 Objectifs

- Digitaliser le processus de gestion des absences.
- Centraliser les informations des stagiaires.
- Faciliter la saisie des absences par les formateurs.
- Simplifier le traitement des justifications.
- Générer des statistiques et des rapports.
- Organiser les données par année scolaire.
- Sécuriser l'accès aux fonctionnalités selon les rôles.

## 👥 Rôles des utilisateurs

| Rôle | Responsabilités |
|------|----------------|
| Administrateur | Gestion des utilisateurs et administration du système |
| Surveillant | Suivi, justification et statistiques des absences |
| Formateur | Saisie des absences et consultation des rapports par module |
| Stagiaire | Consultation de son historique d'absences |

## ⚙️ Fonctionnalités principales

### 🔐 Authentification et autorisation
- Connexion sécurisée.
- Gestion des accès selon les rôles.
- Protection des routes avec des middlewares Laravel.

### 👤 Gestion des utilisateurs
- Ajouter un utilisateur.
- Modifier les informations d'un utilisateur.
- Supprimer un utilisateur.
- Gérer les profils des stagiaires et des formateurs.

### 📚 Gestion des données pédagogiques
- Gestion des filières.
- Gestion des groupes.
- Gestion des modules.
- Affectation des groupes et modules aux formateurs.

### 📥 Importation Excel
- Importation globale des données.
- Importation des stagiaires.
- Importation des groupes.
- Importation des affectations des formateurs.

### 📝 Saisie des absences
- Sélection de la filière.
- Sélection du groupe et du module.
- Sélection de la date.
- Choix des horaires de séance.
- Enregistrement des stagiaires absents.

### ✅ Justification des absences
- Recherche d'un stagiaire par CIN.
- Consultation de ses absences.
- Filtrage par période.
- Sélection et justification de plusieurs absences.

### 📊 Rapports et statistiques
- Statistiques générales des absences.
- Rapport des absences par module.
- Nombre d'absences justifiées et non justifiées.
- Historique des absences des stagiaires.

### 📅 Gestion des années scolaires
- Détermination automatique de l'année scolaire actuelle.
- Changement de l'année scolaire sélectionnée.
- Organisation des données par année scolaire.

## 🛠️ Technologies utilisées

| Catégorie | Technologies |
|-----------|-------------|
| Backend | PHP, Laravel |
| Frontend | React.js (JavaScript) |
| Communication | Inertia.js |
| Base de données | MySQL |
| Interface | Tailwind CSS |
| ORM | Eloquent |
| Importation Excel | Laravel Excel |
| Icônes | Lucide React |
| Build Tool | Vite |
| Gestion des dépendances | Composer, npm |

## 📸 Captures d'écran

### Aperçu 1

![Capture 1](demo/Screenshot%202026-06-07%20220331.png)

### Aperçu 2

![Capture 2](demo/Screenshot%202026-06-07%20220609.png)

### Aperçu 3

![Capture 3](demo/Screenshot%202026-06-07%20221438.png)

### Aperçu 4

![Capture 4](demo/Screenshot%202026-06-07%20222355.png)

## 🚀 Installation du projet

### 1. Prérequis

Avant de commencer, installer :

- PHP
- Composer
- Node.js et npm
- MySQL
- Git

### 2. Cloner le repository

```bash
git clone https://github.com/el-youbi6/gestion_absences.git
cd gestion_absences
```

### 3. Installer les dépendances PHP

```bash
composer install
```

### 4. Installer les dépendances JavaScript

```bash
npm install
```

### 5. Configurer l'environnement

Créer le fichier `.env` à partir de `.env.example`.

```bash
cp .env.example .env
```

Générer la clé Laravel :

```bash
php artisan key:generate
```

### 6. Configurer la base de données

Créer une base de données MySQL, puis modifier les paramètres dans `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_absences
DB_USERNAME=root
DB_PASSWORD=
```

Adapter ces valeurs selon votre environnement.

### 7. Exécuter les migrations

```bash
php artisan migrate
```

### 8. Démarrer le backend

```bash
php artisan serve
```

### 9. Démarrer le frontend

Dans un deuxième terminal :

```bash
npm run dev
```

### 10. Accéder à l'application

Ouvrir dans le navigateur :

http://localhost:8000/login

**Remarque :** des comptes utilisateurs et des données pédagogiques sont nécessaires pour tester les différentes fonctionnalités.

## 📁 Structure du projet

```text
gestion_absences/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   ├── Models/
│   ├── Imports/
│   └── Services/
│
├── database/
│   └── migrations/
│
├── resources/
│   ├── css/
│   └── js/
│       ├── Components/
│       ├── Layouts/
│       └── Pages/
│
├── routes/
│   └── web.php
│
├── demo/
│   └── Captures d'écran
│
├── public/
├── composer.json
├── package.json
└── README.md
```

## 🔄 Fonctionnement général

1. L'administrateur prépare les utilisateurs et les données pédagogiques.
2. Les formateurs sont affectés aux groupes et aux modules.
3. Les formateurs enregistrent les absences des stagiaires.
4. Les surveillants consultent et justifient les absences.
5. Les responsables consultent les statistiques.
6. Les stagiaires peuvent suivre leur historique d'absences.

## 👨‍💻 Auteur

**Développeur :** [el-youbi6](https://github.com/el-youbi6)

**Projet :** Gestion des Absences des Stagiaires

**Technologies principales :** Laravel, React, Inertia.js et MySQL.

