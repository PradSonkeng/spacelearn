<?php
/**
 * =========================================================
 *  CONFIGURATION GÉNÉRALE DU LMS
 * =========================================================
 *  Adaptez uniquement les constantes de connexion à la base
 *  de données si nécessaire. Tout le reste est calculé
 *  automatiquement afin que le projet fonctionne quel que
 *  soit le dossier dans lequel il est placé sous htdocs/.
 */

// --- Affichage des erreurs (à mettre à 0 en production) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Fuseau horaire ---
date_default_timezone_set('Africa/Douala');

// --- Démarrage de la session (une seule fois) ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =========================================================
// BASE DE DONNÉES (XAMPP par défaut : root sans mot de passe)
// =========================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_db_name_here');
define('DB_USER', 'your_username_here');
define('DB_PASS', 'your_password_here');
define('DB_CHARSET', 'utf8mb4');

// =========================================================
// CHEMINS DU SYSTÈME DE FICHIERS
// =========================================================
define('ROOT_PATH', __DIR__ . '/..');   // remonte d'un seul niveau, reste dans htdocs
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH );
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// =========================================================
// URL DE BASE (détection automatique)
// Exemple : http://localhost/lms/public
// =========================================================
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$scriptDir = rtrim($scriptDir, '/');
define('BASE_URL', $scriptDir);          // ex: /lms/public
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');

// =========================================================
// LIMITES D'UPLOAD
// =========================================================
define('MAX_PDF_SIZE', 50 * 1024 * 1024);   // 50 Mo
define('MAX_VIDEO_SIZE', 300 * 1024 * 1024); // 300 Mo
define('MAX_IMAGE_SIZE', 10 * 1024 * 1024);   // 10 Mo

// =========================================================
// NOM DE LA PLATEFORME
// =========================================================
define('APP_NAME', 'SPACELearn');
define('APP_VERSION', '1.0.0');

// =========================================================
// CONFIGURATION EMAIL
// =========================================================
define('MAIL_HOST',       'host.mail.com');
define('MAIL_USERNAME',   'tonemail@gmail.com');
define('MAIL_PASSWORD',   'ton-mot-de-passe-app');
define('MAIL_FROM',       'mail@form.com');
define('MAIL_FROM_NAME',  'SPACELearn');
define('MAIL_PORT',       587);
