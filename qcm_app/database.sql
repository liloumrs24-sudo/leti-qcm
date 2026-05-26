
-- =========================================================
-- QCM PLATFORM DATABASE
-- MySQL / MariaDB
-- =========================================================

CREATE DATABASE IF NOT EXISTS qcm_platform
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE qcm_platform;

-- =========================================================
-- TABLE: utilisateur
-- =========================================================
CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    role ENUM('user','admin') DEFAULT 'user',
    is_blocked TINYINT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- TABLE: categorie
-- =========================================================
CREATE TABLE categorie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    icone VARCHAR(255)
) ENGINE=InnoDB;

-- =========================================================
-- TABLE: quiz
-- =========================================================
CREATE TABLE quiz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categorie_id INT,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    temps_limite INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categorie(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- TABLE: question
-- =========================================================
CREATE TABLE question (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT,
    question TEXT NOT NULL,
    reponse1 VARCHAR(255),
    reponse2 VARCHAR(255),
    reponse3 VARCHAR(255),
    reponse4 VARCHAR(255),
    bonne_reponse VARCHAR(255),
    FOREIGN KEY (quiz_id) REFERENCES quiz(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- TABLE: tentative
-- =========================================================
CREATE TABLE tentative (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    quiz_id INT,
    score INT DEFAULT 0,
    total_questions INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id)
    ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quiz(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- TABLE: reponse_utilisateur
-- =========================================================
CREATE TABLE reponse_utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tentative_id INT,
    question_id INT,
    reponse_donnee VARCHAR(255),
    is_correct TINYINT DEFAULT 0,
    FOREIGN KEY (tentative_id) REFERENCES tentative(id)
    ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES question(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- TABLE: examen_session
-- =========================================================
CREATE TABLE examen_session (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    quiz_id INT,
    score_suspicion INT DEFAULT 0,
    statut VARCHAR(50) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id)
    ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quiz(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- TABLE: anticheat_log
-- =========================================================
CREATE TABLE anticheat_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT,
    utilisateur_id INT,
    type_evenement VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES examen_session(id)
    ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;
