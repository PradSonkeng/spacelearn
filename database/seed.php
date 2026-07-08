<?php
/**
 * =========================================================
 *  SCRIPT D'INITIALISATION DES DONNÉES DE DÉMONSTRATION
 * =========================================================
 *  À exécuter UNE SEULE FOIS après avoir importé schema.sql.
 *
 *  Utilisation :
 *   - Navigateur : http://localhost/lms/database/seed.php
 *   - Ou en CLI  : php seed.php
 *
 *  Crée 3 comptes de démonstration (mot de passe : password123) :
 *   - admin@lms.test      (promoteur)
 *   - enseignant@lms.test (enseignant)
 *   - etudiant@lms.test   (étudiant)
 *  ainsi qu'un module et un cours d'exemple.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = Database::getConnection();

function userExists(PDO $db, string $email): bool
{
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    return (bool)$stmt->fetch();
}

echo "=== Initialisation des données de démonstration ===\n";

$password = password_hash('password123', PASSWORD_BCRYPT);

$demoUsers = [
    ['full_name' => 'Admin Promoteur', 'email' => 'admin@lms.test',      'role' => 'promoteur',  'bio' => 'Promoteur et administrateur de la plateforme.'],
    ['full_name' => 'Jean Mballa',      'email' => 'enseignant@lms.test', 'role' => 'enseignant', 'bio' => 'Enseignant en développement web.'],
    ['full_name' => 'Aline Ngono',      'email' => 'etudiant@lms.test',   'role' => 'etudiant',   'bio' => 'Étudiante passionnée de programmation.'],
];

$ids = [];
foreach ($demoUsers as $u) {
    if (userExists($db, $u['email'])) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $u['email']]);
        $ids[$u['role']] = (int)$stmt->fetch()['id'];
        echo "- Utilisateur déjà existant : {$u['email']}\n";
        continue;
    }

    $stmt = $db->prepare(
        "INSERT INTO users (full_name, email, password, role, bio) VALUES (:full_name, :email, :password, :role, :bio)"
    );
    $stmt->execute([
        'full_name' => $u['full_name'],
        'email'     => $u['email'],
        'password'  => $password,
        'role'      => $u['role'],
        'bio'       => $u['bio'],
    ]);
    $ids[$u['role']] = (int)$db->lastInsertId();
    echo "- Compte créé : {$u['email']} (mot de passe : password123)\n";
}

// --- Module de démonstration ---
$stmt = $db->prepare("SELECT id FROM modules WHERE title = :title");
$stmt->execute(['title' => 'Développement Web Fullstack']);
$moduleRow = $stmt->fetch();

if ($moduleRow) {
    $moduleId = (int)$moduleRow['id'];
    echo "- Module déjà existant : Développement Web Fullstack\n";
} else {
    $stmt = $db->prepare(
        "INSERT INTO modules (title, description, promoter_id) VALUES (:title, :description, :promoter_id)"
    );
    $stmt->execute([
        'title' => 'Développement Web Fullstack',
        'description' => 'Module complet couvrant le frontend et le backend du développement web moderne : HTML, CSS, JavaScript, PHP et bases de données.',
        'promoter_id' => $ids['promoteur'],
    ]);
    $moduleId = (int)$db->lastInsertId();
    echo "- Module créé : Développement Web Fullstack\n";
}

// --- Cours de démonstration ---
$stmt = $db->prepare("SELECT id FROM courses WHERE title = :title");
$stmt->execute(['title' => 'Introduction au HTML & CSS']);
$courseRow = $stmt->fetch();

if (!$courseRow) {
    $stmt = $db->prepare(
        "INSERT INTO courses (module_id, teacher_id, title, description, status) VALUES (:module_id, :teacher_id, :title, :description, :status)"
    );
    $stmt->execute([
        'module_id' => $moduleId,
        'teacher_id' => $ids['enseignant'],
        'title' => 'Introduction au HTML & CSS',
        'description' => "Apprenez les fondamentaux de la structuration et de la mise en forme des pages web. Ce cours couvre les balises HTML essentielles, le modèle de boîte CSS, et la mise en page responsive.",
        'status' => 'publie',
    ]);
    echo "- Cours créé : Introduction au HTML & CSS (sans leçon — à compléter par l'enseignant)\n";
} else {
    echo "- Cours déjà existant : Introduction au HTML & CSS\n";
}

echo "\n=== Terminé ! ===\n";
echo "Vous pouvez maintenant vous connecter avec :\n";
echo "  Promoteur  : admin@lms.test      / password123\n";
echo "  Enseignant : enseignant@lms.test / password123\n";
echo "  Étudiant   : etudiant@lms.test   / password123\n";
