# 🎓 QCM Platform — Quiz en ligne (PHP / MySQL)

![PHP](https://img.shields.io/badge/PHP-8.x-blue)
![MySQL](https://img.shields.io/badge/MySQL-Database-orange)
![Status](https://img.shields.io/badge/Project-Completed-brightgreen)

---

##  Description

Plateforme de QCM en ligne permettant aux utilisateurs de passer des quiz avec correction automatique, historique des résultats et système anti-triche.

---

##  Fonctionnalités

- Authentification utilisateur (inscription / connexion)
- Gestion des quiz par catégories
- Passage de QCM avec réponses multiples
- Calcul automatique du score
- Historique des tentatives
- Dashboard utilisateur
- Système anti-triche (sessions + logs)
- Gestion des rôles (user/admin)

---

##  Structure du projet
qcm_app/
├── accueil.php                 # Page d'accueil
├── dashboard.php               # Tableau de bord utilisateur
├── config.php                  # Configuration BDD
├── anti_triche.php             # API anti-triche
├── api_anticheat.php           # API logs
├── heartbeat.php               # Maintien de session
│
├── auth/
│   ├── login.php               # Connexion
│   ├── register.php            # Inscription
│   └── logout.php              # Déconnexion
│
├── admin/
│   ├── dashboard.php           # Dashboard admin
│   ├── users.php               # Gestion utilisateurs
│   ├── questions.php           # Gestion questions
│   └── anticheat.php           # Logs anti-triche
│
├── qcm/
│   ├── choisir_categories.php  # Sélection catégories
│   ├── start.php               # Initialisation QCM
│   ├── take.php                # Passage du QCM
│   ├── process_answer.php      # Traitement réponses
│   ├── result.php              # Affichage résultats
│   └── quitter.php             # Abandon QCM
│
├── user/
│   └── history.php             # Historique utilisateur
│
├── logs/
│   └── triche.txt              # Logs fichier (secours)


docs/  
- rapport.pdf  
- cahier_des_charges.pdf 
readme 

---

##  Base de données

Tables principales :

- utilisateur  
- categorie  
- quiz  
- question  
- tentative  
- reponse_utilisateur  
- examen_session  
- anticheat_log  

---

## ⚙️ Installation

1. Cloner le projet :
git clone https://github.com/ton-projet/qcm-platform.git

2. Mettre le projet dans htdocs

3. Créer une base de données MySQL

4. Importer :
/sql/database.sql

5. Configurer :
DataBase/DB.php

6. Lancer :
http://localhost/MonProjet-QCM

---

## 🔐 Sécurité

- Mots de passe hashés
- Sessions sécurisées
- Logs anti-triche
- Gestion des rôles

---

## 📂 Documentation

- Cahier des charges : docs/cahier_des_charges.pdf  
- Rapport de projet : docs/rapport.pdf  

---

## 👨‍💻 Technologies

- PHP 8+
- MySQL
- HTML / CSS
- JavaScript (AJAX)
- PDO

---

## 📜 Licence

Projet éducatif — non destiné à la production sans adaptation.
