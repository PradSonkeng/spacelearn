-- =========================================================
--  LMS - Schéma de base de données
--  Encodage UTF8MB4 (support complet des emojis / accents)
-- =========================================================

CREATE DATABASE IF NOT EXISTS lms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lms_db;

-- ---------------------------------------------------------
-- UTILISATEURS (Promoteur / Enseignant / Étudiant)
-- ---------------------------------------------------------
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('promoteur','enseignant','etudiant') NOT NULL DEFAULT 'etudiant',
    avatar      VARCHAR(255) DEFAULT 'default.png',
    bio         TEXT NULL,
    status      ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role (role)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- MODULES (créés par le promoteur)
-- ---------------------------------------------------------
CREATE TABLE modules (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(150) NOT NULL,
    description  TEXT NULL,
    image        VARCHAR(255) DEFAULT NULL,
    promoter_id  INT NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_modules_promoter FOREIGN KEY (promoter_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- COURS (créés par les enseignants, rattachés à un module)
-- ---------------------------------------------------------
CREATE TABLE courses (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    module_id   INT NOT NULL,
    teacher_id  INT NOT NULL,
    title       VARCHAR(150) NOT NULL,
    description TEXT NULL,
    image       VARCHAR(255) DEFAULT NULL,
    status      ENUM('brouillon','publie') NOT NULL DEFAULT 'brouillon',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_courses_module  FOREIGN KEY (module_id)  REFERENCES modules(id) ON DELETE CASCADE,
    CONSTRAINT fk_courses_teacher FOREIGN KEY (teacher_id) REFERENCES users(id)   ON DELETE CASCADE,
    INDEX idx_courses_module (module_id),
    INDEX idx_courses_teacher (teacher_id),
    FULLTEXT INDEX ft_courses_search (title, description)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- LEÇONS (PDF ou Vidéo) - ordonnées dans un cours
-- ---------------------------------------------------------
CREATE TABLE lessons (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    course_id   INT NOT NULL,
    title       VARCHAR(150) NOT NULL,
    description TEXT NULL,
    type        ENUM('pdf','video') NOT NULL,
    file_path   VARCHAR(255) NOT NULL,
    position    INT NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lessons_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_lessons_course (course_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- ÉVALUATIONS (une par leçon, optionnelle)
-- ---------------------------------------------------------
CREATE TABLE evaluations (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id     INT NOT NULL UNIQUE,
    title         VARCHAR(150) NOT NULL,
    passing_score INT NOT NULL DEFAULT 50,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_eval_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- QUESTIONS (QCM ou Vrai/Faux)
-- ---------------------------------------------------------
CREATE TABLE questions (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    evaluation_id INT NOT NULL,
    question_text TEXT NOT NULL,
    points        INT NOT NULL DEFAULT 1,
    position      INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_questions_eval FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
    INDEX idx_questions_eval (evaluation_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- RÉPONSES (plusieurs par question, une ou plusieurs correctes)
-- ---------------------------------------------------------
CREATE TABLE answers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    answer_text VARCHAR(255) NOT NULL,
    is_correct  TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_answers_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_answers_question (question_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- INSCRIPTIONS (étudiant <-> cours) + progression globale
-- ---------------------------------------------------------
CREATE TABLE enrollments (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    student_id       INT NOT NULL,
    course_id        INT NOT NULL,
    progress_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    enrolled_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at     TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uniq_enrollment (student_id, course_id),
    CONSTRAINT fk_enroll_student FOREIGN KEY (student_id) REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_enroll_course  FOREIGN KEY (course_id)  REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_enroll_student (student_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- PROGRESSION PAR LEÇON
-- ---------------------------------------------------------
CREATE TABLE lesson_progress (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    student_id   INT NOT NULL,
    lesson_id    INT NOT NULL,
    status       ENUM('non_commence','en_cours','termine') NOT NULL DEFAULT 'non_commence',
    best_score   DECIMAL(5,2) NULL DEFAULT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uniq_progress (student_id, lesson_id),
    CONSTRAINT fk_progress_student FOREIGN KEY (student_id) REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_progress_lesson  FOREIGN KEY (lesson_id)  REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- TENTATIVES D'ÉVALUATION (historique des notes)
-- ---------------------------------------------------------
CREATE TABLE attempts (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    student_id    INT NOT NULL,
    evaluation_id INT NOT NULL,
    score         DECIMAL(5,2) NOT NULL,
    passed        TINYINT(1) NOT NULL DEFAULT 0,
    attempt_date  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attempts_student FOREIGN KEY (student_id)    REFERENCES users(id)       ON DELETE CASCADE,
    CONSTRAINT fk_attempts_eval    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
    INDEX idx_attempts_student_eval (student_id, evaluation_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- CERTIFICATS (délivrés quand un module est validé)
-- ---------------------------------------------------------
CREATE TABLE certificates (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    module_id  INT NOT NULL,
    code       VARCHAR(50) NOT NULL UNIQUE,
    issued_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_certificate (student_id, module_id),
    CONSTRAINT fk_cert_student FOREIGN KEY (student_id) REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_cert_module  FOREIGN KEY (module_id)  REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- NOTIFICATIONS (cloche en temps réel)
-- ---------------------------------------------------------
CREATE TABLE notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    message    VARCHAR(255) NOT NULL,
    link       VARCHAR(255) DEFAULT NULL,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notif_user_read (user_id, is_read)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- AVIS / NOTES SUR LES COURS
-- ---------------------------------------------------------
CREATE TABLE reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id  INT NOT NULL,
    rating     TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment    TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_review (student_id, course_id),
    CONSTRAINT fk_review_student FOREIGN KEY (student_id) REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_review_course  FOREIGN KEY (course_id)  REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- NOTE : les données de démonstration (utilisateurs, module,
-- cours) sont injectées via le script "database/seed.php"
-- afin que les mots de passe soient hachés correctement
-- avec password_hash() (bcrypt) au moment de l'exécution.
-- =========================================================
