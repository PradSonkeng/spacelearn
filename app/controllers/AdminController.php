<?php
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/ModuleModel.php';
require_once APP_PATH . '/models/Course.php';
require_once APP_PATH . '/models/Certificate.php';

class AdminController extends Controller
{
    public function __construct()
    {
        $this->requireRole('promoteur');
    }

    /** Tableau de bord avec indicateurs clés */
    public function dashboard(): void
    {
        $userModel = new User();
        $moduleModel = new ModuleModel();
        $courseModel = new Course();

        $stats = [
            'students'     => $userModel->countByRole('etudiant'),
            'teachers'     => $userModel->countByRole('enseignant'),
            'modules'      => $moduleModel->count(),
            'courses'      => $courseModel->count(),
            'certificates' => (new Certificate())->count(),
        ];

        // Top 5 des cours les plus suivis
        $topCourses = Database::query(
            "SELECT c.title, COUNT(e.id) AS nb_students
             FROM courses c
             LEFT JOIN enrollments e ON e.course_id = c.id
             GROUP BY c.id ORDER BY nb_students DESC LIMIT 5"
        )->fetchAll();

        // Inscriptions par mois (6 derniers mois)
        $enrollmentsByMonth = Database::query(
            "SELECT DATE_FORMAT(enrolled_at, '%Y-%m') AS ym, COUNT(*) AS total
             FROM enrollments
             WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY ym ORDER BY ym ASC"
        )->fetchAll();

        // Dernières inscriptions (activité récente)
        $recentActivity = Database::query(
            "SELECT u.full_name, c.title AS course_title, e.enrolled_at
             FROM enrollments e
             JOIN users u ON u.id = e.student_id
             JOIN courses c ON c.id = e.course_id
             ORDER BY e.enrolled_at DESC LIMIT 6"
        )->fetchAll();

        $this->view('admin/dashboard', [
            'title' => 'Tableau de bord',
            'stats' => $stats,
            'topCourses' => $topCourses,
            'enrollmentsByMonth' => $enrollmentsByMonth,
            'recentActivity' => $recentActivity,
            'extraJs' => ['https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js'],
        ]);
    }

    // =====================================================
    // MODULES
    // =====================================================

    public function modules(): void
    {
        $moduleModel = new ModuleModel();
        $this->view('admin/modules', [
            'title' => 'Modules de formation',
            'modules' => $moduleModel->allWithStats(),
        ]);
    }

    public function moduleForm(int $id = 0): void
    {
        $module = null;
        if ($id > 0) {
            $module = (new ModuleModel())->find($id);
            if (!$module) $this->redirect('admin/modules');
        }
        $this->view('admin/module_form', [
            'title' => $id > 0 ? 'Modifier le module' : 'Nouveau module',
            'module' => $module,
        ]);
    }

    public function moduleSave(): void
    {
        $this->verifyCsrf();
        $moduleModel = new ModuleModel();

        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '') {
            $this->setFlash('danger', 'Le titre du module est obligatoire.');
            $this->redirect($id > 0 ? "admin/moduleForm/{$id}" : 'admin/moduleForm');
        }

        $data = ['title' => $title, 'description' => $description];

        if (!empty($_FILES['image']['name'])) {
            $path = handle_upload($_FILES['image'], 'modules', ['jpg', 'jpeg', 'png', 'webp'], MAX_IMAGE_SIZE);
            if ($path !== false) {
                $data['image'] = basename($path);
            }
        }

        if ($id > 0) {
            $moduleModel->update($id, $data);
            $this->setFlash('success', 'Module mis à jour avec succès.');
        } else {
            $data['promoter_id'] = current_user_id();
            $moduleModel->create($data);
            $this->setFlash('success', 'Module créé avec succès.');
        }

        $this->redirect('admin/modules');
    }

    public function moduleDelete(int $id): void
    {
        $this->verifyCsrf();
        (new ModuleModel())->delete($id);
        $this->setFlash('success', 'Module supprimé.');
        $this->redirect('admin/modules');
    }

    // =====================================================
    // COURS (vue d'ensemble globale)
    // =====================================================

    public function courses(): void
    {
        $sql = "SELECT c.*, m.title AS module_title, u.full_name AS teacher_name,
                       (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS nb_lessons,
                       (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS nb_students
                FROM courses c
                JOIN modules m ON m.id = c.module_id
                JOIN users u ON u.id = c.teacher_id
                ORDER BY c.created_at DESC";
        $courses = Database::query($sql)->fetchAll();

        $this->view('admin/courses', [
            'title' => 'Tous les cours',
            'courses' => $courses,
        ]);
    }

    // =====================================================
    // UTILISATEURS
    // =====================================================

    public function users(): void
    {
        $users = Database::query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
        $this->view('admin/users', [
            'title' => 'Utilisateurs',
            'users' => $users,
        ]);
    }

    /** Active/désactive un compte utilisateur */
    public function userToggleStatus(int $id): void
    {
        $this->verifyCsrf();
        $userModel = new User();
        $user = $userModel->find($id);

        if (!$user) $this->redirect('admin/users');

        if ((int)$user['id'] === current_user_id()) {
            $this->setFlash('danger', 'Vous ne pouvez pas désactiver votre propre compte.');
            $this->redirect('admin/users');
        }

        $newStatus = $user['status'] === 'actif' ? 'inactif' : 'actif';
        $userModel->update($id, ['status' => $newStatus]);

        $this->setFlash('success', 'Statut de ' . $user['full_name'] . ' mis à jour.');
        $this->redirect('admin/users');
    }

    // =====================================================
    // CERTIFICATS
    // =====================================================

    public function certificates(): void
    {
        $sql = "SELECT c.*, m.title AS module_title, u.full_name AS student_name, u.email AS student_email
                FROM certificates c
                JOIN modules m ON m.id = c.module_id
                JOIN users u ON u.id = c.student_id
                ORDER BY c.issued_at DESC";
        $certificates = Database::query($sql)->fetchAll();

        $this->view('admin/certificates', [
            'title' => 'Certificats délivrés',
            'certificates' => $certificates,
        ]);
    }

    // =====================================================
    // STATISTIQUES (page avec graphiques Chart.js)
    // =====================================================

    public function statistics(): void
    {
        // Répartition des utilisateurs par rôle
        $usersByRole = Database::query(
            "SELECT role, COUNT(*) AS total FROM users GROUP BY role"
        )->fetchAll();

        // Nombre de cours par module
        $coursesByModule = Database::query(
            "SELECT m.title, COUNT(c.id) AS total
             FROM modules m LEFT JOIN courses c ON c.module_id = m.id
             GROUP BY m.id ORDER BY total DESC"
        )->fetchAll();

        // Note moyenne par cours (top 8)
        $ratingsByCourse = Database::query(
            "SELECT c.title, ROUND(AVG(r.rating),2) AS avg_rating, COUNT(r.id) AS nb_reviews
             FROM courses c JOIN reviews r ON r.course_id = c.id
             GROUP BY c.id ORDER BY nb_reviews DESC LIMIT 8"
        )->fetchAll();

        // Progression moyenne des étudiants par cours (top 8)
        $progressByCourse = Database::query(
            "SELECT c.title, ROUND(AVG(e.progress_percent),2) AS avg_progress
             FROM courses c JOIN enrollments e ON e.course_id = c.id
             GROUP BY c.id ORDER BY avg_progress DESC LIMIT 8"
        )->fetchAll();

        $this->view('admin/statistics', [
            'title' => 'Statistiques',
            'usersByRole' => $usersByRole,
            'coursesByModule' => $coursesByModule,
            'ratingsByCourse' => $ratingsByCourse,
            'progressByCourse' => $progressByCourse,
        ]);
    }
}
