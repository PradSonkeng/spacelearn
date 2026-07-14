<?php
require_once APP_PATH . '/models/Course.php';
require_once APP_PATH . '/models/ModuleModel.php';
require_once APP_PATH . '/models/Lesson.php';
require_once APP_PATH . '/models/Evaluation.php';
require_once APP_PATH . '/models/Enrollment.php';
require_once APP_PATH . '/models/LessonProgress.php';
require_once APP_PATH . '/models/Attempt.php';
require_once APP_PATH . '/models/Certificate.php';
require_once APP_PATH . '/models/Review.php';

class StudentController extends Controller
{
    public function __construct()
    {
        $this->requireRole('etudiant');
    }

    // =====================================================
    // TABLEAU DE BORD
    // =====================================================
    public function dashboard(): void
    {
        $studentId = current_user_id();
        $enrollmentModel = new Enrollment();
        $enrollments = $enrollmentModel->byStudent($studentId);

        $certificates = (new Certificate())->byStudent($studentId);

        // Cours recommandés (publiés, non encore suivis)
        $enrolledIds = array_column($enrollments, 'course_id');
        $courseModel = new Course();
        $allCourses = $courseModel->catalog();
        $recommended = array_filter($allCourses, fn($c) => !in_array($c['id'], $enrolledIds));
        $recommended = array_slice($recommended, 0, 3);

        $avgProgress = count($enrollments) > 0
            ? round(array_sum(array_column($enrollments, 'progress_percent')) / count($enrollments), 1)
            : 0;

        $this->view('student/dashboard', [
            'title' => 'Tableau de bord',
            'enrollments' => $enrollments,
            'certificates' => $certificates,
            'recommended' => $recommended,
            'avgProgress' => $avgProgress,
        ]);
    }

    // =====================================================
    // CATALOGUE
    // =====================================================
    public function catalog(): void
    {
        $search = trim($_GET['q'] ?? '');
        $moduleId = !empty($_GET['module']) ? (int)$_GET['module'] : null;

        $courseModel = new Course();
        $courses = $courseModel->catalog($search, $moduleId);
        $modules = (new ModuleModel())->all('title ASC');

        $enrollmentModel = new Enrollment();
        $enrolledIds = array_column($enrollmentModel->byStudent(current_user_id()), 'course_id');

        $this->view('student/catalog', [
            'title' => 'Catalogue des cours',
            'courses' => $courses,
            'modules' => $modules,
            'enrolledIds' => $enrolledIds,
            'search' => $search,
            'selectedModule' => $moduleId,
        ]);
    }

    // =====================================================
    // DÉTAIL D'UN COURS
    // =====================================================
    public function course(int $id): void
    {
        $courseModel = new Course();
        $course = $courseModel->details($id);

        if (!$course || $course['status'] !== 'publie') {
            $this->view('errors/404', [], 'none');
            return;
        }

        $lessons = (new Lesson())->byCourse($id);
        $enrollmentModel = new Enrollment();
        $isEnrolled = $enrollmentModel->isEnrolled(current_user_id(), $id);
        $progressMap = $isEnrolled ? (new LessonProgress())->mapForCourse(current_user_id(), $id) : [];

        $reviews = (new Review())->byCourse($id);
        $myReview = (new Review())->findWhere(['student_id' => current_user_id(), 'course_id' => $id]);

        $this->view('student/course_detail', [
            'title' => $course['title'],
            'course' => $course,
            'lessons' => $lessons,
            'isEnrolled' => $isEnrolled,
            'progressMap' => $progressMap,
            'reviews' => $reviews,
            'myReview' => $myReview,
        ]);
    }

    /** Inscription à un cours */
    public function enroll(int $id): void
    {
        $this->verifyCsrf();
        $courseModel = new Course();
        $course = $courseModel->find($id);

        if (!$course || $course['status'] !== 'publie') {
            $this->redirect('student/catalog');
        }

        (new Enrollment())->enroll(current_user_id(), $id);
        $this->setFlash('success', 'Inscription réussie ! Bon apprentissage 🎓');
        $this->redirect('student/course/' . $id);
    }

    // =====================================================
    // MES COURS
    // =====================================================
    public function myCourses(): void
    {
        $enrollments = (new Enrollment())->byStudent(current_user_id());
        $this->view('student/my_courses', [
            'title' => 'Mes cours',
            'enrollments' => $enrollments,
        ]);
    }

    // =====================================================
    // LEÇON (lecteur PDF / Vidéo)
    // =====================================================
    public function lesson(int $id): void
    {
        $lessonModel = new Lesson();
        $lesson = $lessonModel->details($id);

        if (!$lesson) {
            $this->view('errors/404', [], 'none');
            return;
        }

        $enrollmentModel = new Enrollment();
        if (!$enrollmentModel->isEnrolled(current_user_id(), $lesson['course_id'])) {
            $this->setFlash('warning', 'Vous devez être inscrit à ce cours pour accéder à cette leçon.');
            $this->redirect('student/course/' . $lesson['course_id']);
        }

        $allLessons = $lessonModel->byCourse($lesson['course_id']);
        $progress = (new LessonProgress())->get(current_user_id(), $id);

        $bestScore = $lesson['evaluation_id']
            ? (new Attempt())->bestScore(current_user_id(), $lesson['evaluation_id'])
            : null;

        // Marque la leçon comme "en cours" dès la première consultation
        if (!$progress) {
            (new LessonProgress())->setStatus(current_user_id(), $id, 'en_cours');
        }

        // Si la leçon n'a pas d'évaluation, la consultation suffit à la valider
        if (!$lesson['evaluation_id'] && (!$progress || $progress['status'] !== 'termine')) {
            (new LessonProgress())->setStatus(current_user_id(), $id, 'termine');
            $enrollmentModel->recalcProgress(current_user_id(), $lesson['course_id']);
            $this->checkModuleCompletion($lesson['module_id'], current_user_id());
        }

        $this->view('student/lesson_view', [
            'title' => $lesson['title'],
            'lesson' => $lesson,
            'allLessons' => $allLessons,
            'progress' => (new LessonProgress())->get(current_user_id(), $id),
            'bestScore' => $bestScore,
            'hasViewedContent' => (new LessonProgress())->hasViewedContent(current_user_id(), $id), //importation
        ]);
    }
    
    public function markContentViewed(int $lessonId): void
    {
    		if (!is_logged_in() || current_role() !== 'etudiant') {
        		$this->json(['ok' => false], 403);
    		}
    		
    		$lessonModel = new Lesson();
    		$lesson = $lessonModel->details($lessonId);
    		
    		if ($lesson && (new Enrollment())->isEnrolled(current_user_id(), $lesson['course_id'])) {
        		(new LessonProgress())->markContentViewed(current_user_id(), $lessonId);
    		}
    		
    		$this->json(['ok' => true]);
    }

    // =====================================================
    // ÉVALUATION
    // =====================================================
    public function evaluation(int $lessonId): void
    {
        $lessonModel = new Lesson();
        $lesson = $lessonModel->details($lessonId);

        if (!$lesson || !$lesson['evaluation_id']) {
            $this->view('errors/404', [], 'none');
            return;
        }

        if (!(new Enrollment())->isEnrolled(current_user_id(), $lesson['course_id'])) {
            $this->redirect('student/course/' . $lesson['course_id']);
        }

        $evaluation = (new Evaluation())->getForLesson($lessonId);
        $history = (new Attempt())->history(current_user_id(), $evaluation['id']);

        $this->view('student/evaluation', [
            'title' => 'Évaluation : ' . $lesson['title'],
            'lesson' => $lesson,
            'evaluation' => $evaluation,
            'history' => $history,
        ]);
    }

    /**
     * Traite la soumission d'une évaluation via AJAX.
     * Met à jour la progression et délivre un certificat si le module est validé.
     */
    public function submitEvaluation(): void
    {
        if (!is_logged_in() || current_role() !== 'etudiant') {
            $this->json(['ok' => false], 403);
        }
        $this->verifyCsrf();

        $lessonId = (int)($_POST['lesson_id'] ?? 0);
        $lessonModel = new Lesson();
        $lesson = $lessonModel->details($lessonId);

        if (!$lesson || !$lesson['evaluation_id']) {
            $this->json(['ok' => false, 'message' => 'Évaluation introuvable.'], 404);
        }

        if (!(new Enrollment())->isEnrolled(current_user_id(), $lesson['course_id'])) {
            $this->json(['ok' => false, 'message' => 'Vous devez être inscrit à ce cours.'], 403);
        }

        $evaluationModel = new Evaluation();
        $submitted = $_POST['answers'] ?? [];
        $result = $evaluationModel->correct((int)$lesson['evaluation_id'], $submitted);

        $passed = $result['total_points'] > 0 && $result['score'] >= (float)$lesson['passing_score'];

        $attemptModel = new Attempt();
        $attemptModel->record(current_user_id(), (int)$lesson['evaluation_id'], $result['score'], $passed);

        $progressModel = new LessonProgress();
        $status = $passed ? 'termine' : 'en_cours';
        $progressModel->setStatus(current_user_id(), $lessonId, $status, $result['score']);

        $enrollmentModel = new Enrollment();
        $courseProgress = $enrollmentModel->recalcProgress(current_user_id(), $lesson['course_id']);

        $certificateIssued = null;
        if ($passed) {
            $certificateIssued = $this->checkCourseCompletion($lesson['course_id'], current_user_id());
        }

        $this->json([
            'ok' => true,
            'score' => $result['score'],
            'passed' => $passed,
            'passing_score' => (int)$lesson['passing_score'],
            'points_obtained' => $result['points_obtained'],
            'total_points' => $result['total_points'],
            'details' => $result['details'],
            'course_progress' => $courseProgress,
            'certificate_issued' => $certificateIssued,
        ]);
    }

    // =====================================================
    // CERTIFICATS
    // =====================================================
    public function certificates(): void
    {
        $certificates = (new Certificate())->byStudent(current_user_id());
        $this->view('student/certificates', [
            'title' => 'Mes certificats',
            'certificates' => $certificates,
        ]);
    }

    // =====================================================
    // OUTIL INTERNE
    // =====================================================

    /**
     * Vérifie si l'étudiant vient de valider entièrement un module.
     * Si oui, délivre le certificat et envoie une notification.
     *
     * @return array|null Détails du certificat délivré (ou null si rien de nouveau)
     */
    private function checkCourseCompletion(int $checkCourseCompletion, int $studentId): ?array
    {
        $enrollmentModel = new Enrollment();
        $enrollment = $enrollmentModel->findWhere(['student_id' => $studentId, 'course_id' => $courseId]);

        if (!$enrollment || $enrollment['progress_percent'] < 100) {
            return null;
        }

        $certificateModel = new Certificate();
        $existing = $certificateModel->findWhere(['student_id' => $studentId, 'course_id' => $courseId]);
        if ($existing) {
            return null; // déjà délivré précédemment
        }

        $courseModel = new Course();
        $course = $courseModel->find($courseId);
        
        $certificate = $certificateModel->issue($studentId, $courseId);

        notify($studentId, 'Félicitations ! Vous avez terminé le cours "' . $course['title'] . '" et reçu votre certificat.', 'student/certificates');

        return [
            'id' => $certificate['id'],
            'code' => $certificate['code'],
            'course_title' => $course['title'],
        ];
    }
}
