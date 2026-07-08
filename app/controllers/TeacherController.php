<?php
require_once APP_PATH . '/models/Course.php';
require_once APP_PATH . '/models/ModuleModel.php';
require_once APP_PATH . '/models/Lesson.php';
require_once APP_PATH . '/models/Evaluation.php';
require_once APP_PATH . '/models/Question.php';
require_once APP_PATH . '/models/Answer.php';

class TeacherController extends Controller
{
    public function __construct()
    {
        $this->requireRole('enseignant');
    }

    // =====================================================
    // TABLEAU DE BORD
    // =====================================================
    public function dashboard(): void
    {
        $teacherId = current_user_id();
        $courseModel = new Course();
        $courses = $courseModel->byTeacher($teacherId);

        $totalStudents = Database::query(
            "SELECT COUNT(DISTINCT e.student_id) AS total
             FROM enrollments e JOIN courses c ON c.id = e.course_id
             WHERE c.teacher_id = :tid",
            ['tid' => $teacherId]
        )->fetch()['total'];

        $avgRating = Database::query(
            "SELECT COALESCE(ROUND(AVG(r.rating), 2), 0) AS avg
             FROM reviews r JOIN courses c ON c.id = r.course_id
             WHERE c.teacher_id = :tid",
            ['tid' => $teacherId]
        )->fetch()['avg'];

        $progressPerCourse = Database::query(
            "SELECT c.title, ROUND(AVG(e.progress_percent), 2) AS avg_progress
             FROM courses c LEFT JOIN enrollments e ON e.course_id = c.id
             WHERE c.teacher_id = :tid GROUP BY c.id",
            ['tid' => $teacherId]
        )->fetchAll();

        $this->view('teacher/dashboard', [
            'title' => 'Tableau de bord',
            'courses' => $courses,
            'totalStudents' => $totalStudents,
            'avgRating' => $avgRating,
            'progressPerCourse' => $progressPerCourse,
            'extraJs' => ['https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js'],
        ]);
    }

    // =====================================================
    // COURS
    // =====================================================
    public function courses(): void
    {
        $courses = (new Course())->byTeacher(current_user_id());
        $this->view('teacher/courses', [
            'title' => 'Mes cours',
            'courses' => $courses,
        ]);
    }

    public function courseCreate(): void
    {
        $modules = (new ModuleModel())->all('title ASC');
        $this->view('teacher/course_form', [
            'title' => 'Nouveau cours',
            'course' => null,
            'modules' => $modules,
        ]);
    }

    public function courseEdit(int $id): void
    {
        $courseModel = new Course();
        if (!$courseModel->belongsToTeacher($id, current_user_id())) {
            $this->view('errors/403', [], 'main'); return;
        }
        $course = $courseModel->find($id);
        $modules = (new ModuleModel())->all('title ASC');

        $this->view('teacher/course_form', [
            'title' => 'Modifier le cours',
            'course' => $course,
            'modules' => $modules,
        ]);
    }

    public function courseSave(): void
    {
        $this->verifyCsrf();
        $courseModel = new Course();

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0 && !$courseModel->belongsToTeacher($id, current_user_id())) {
            $this->view('errors/403', [], 'main'); return;
        }

        $title = trim($_POST['title'] ?? '');
        $moduleId = (int)($_POST['module_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $status = ($_POST['status'] ?? 'brouillon') === 'publie' ? 'publie' : 'brouillon';

        if ($title === '' || $moduleId === 0) {
            $this->setFlash('danger', 'Le titre et le module sont obligatoires.');
            $this->redirect($id > 0 ? "teacher/courseEdit/{$id}" : 'teacher/courseCreate');
        }

        $data = [
            'title' => $title,
            'module_id' => $moduleId,
            'description' => $description,
            'status' => $status,
        ];

        if (!empty($_FILES['image']['name'])) {
            $path = handle_upload($_FILES['image'], 'courses', ['jpg', 'jpeg', 'png', 'webp'], MAX_IMAGE_SIZE);
            if ($path !== false) {
                $data['image'] = basename($path);
            }
        }

        if ($id > 0) {
            $courseModel->update($id, $data);
            $this->setFlash('success', 'Cours mis à jour avec succès.');
            $this->redirect("teacher/courseManage/{$id}");
        } else {
            $data['teacher_id'] = current_user_id();
            $newId = $courseModel->create($data);
            $this->setFlash('success', 'Cours créé. Ajoutez maintenant vos leçons.');
            $this->redirect("teacher/courseManage/{$newId}");
        }
    }

    public function courseDelete(int $id): void
    {
        $this->verifyCsrf();
        $courseModel = new Course();
        if ($courseModel->belongsToTeacher($id, current_user_id())) {
            $courseModel->delete($id);
            $this->setFlash('success', 'Cours supprimé.');
        }
        $this->redirect('teacher/courses');
    }

    /** Page de gestion des leçons d'un cours */
    public function courseManage(int $id): void
    {
        $courseModel = new Course();
        if (!$courseModel->belongsToTeacher($id, current_user_id())) {
            $this->view('errors/403', [], 'main'); return;
        }

        $course = $courseModel->details($id);
        $lessons = (new Lesson())->byCourse($id);

        $this->view('teacher/course_manage', [
            'title' => 'Gestion du cours',
            'course' => $course,
            'lessons' => $lessons,
        ]);
    }

    // =====================================================
    // LEÇONS
    // =====================================================

    public function lessonForm(int $courseId, int $lessonId = 0): void
    {
        $courseModel = new Course();
        if (!$courseModel->belongsToTeacher($courseId, current_user_id())) {
            $this->view('errors/403', [], 'main'); return;
        }

        $course = $courseModel->find($courseId);
        $lesson = $lessonId > 0 ? (new Lesson())->find($lessonId) : null;

        $this->view('teacher/lesson_form', [
            'title' => $lesson ? 'Modifier la leçon' : 'Nouvelle leçon',
            'course' => $course,
            'lesson' => $lesson,
        ]);
    }

    public function lessonSave(): void
    {
        $this->verifyCsrf();

        $courseModel = new Course();
        $lessonModel = new Lesson();

        $id = (int)($_POST['id'] ?? 0);
        $courseId = (int)($_POST['course_id'] ?? 0);

        if (!$courseModel->belongsToTeacher($courseId, current_user_id())) {
            $this->view('errors/403', [], 'main'); return;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = ($_POST['type'] ?? 'pdf') === 'video' ? 'video' : 'pdf';

        if ($title === '') {
            $this->setFlash('danger', 'Le titre de la leçon est obligatoire.');
            $this->redirect("teacher/lessonForm/{$courseId}" . ($id > 0 ? "/{$id}" : ''));
        }

        $data = [
            'course_id' => $courseId,
            'title' => $title,
            'description' => $description,
            'type' => $type,
        ];

        if (!empty($_FILES['file']['name'])) {
            $allowed = $type === 'pdf' ? ['pdf'] : ['mp4', 'webm', 'ogg'];
            $maxSize = $type === 'pdf' ? MAX_PDF_SIZE : MAX_VIDEO_SIZE;
            $path = handle_upload($_FILES['file'], 'lessons', $allowed, $maxSize);

            if ($path === false) {
                $this->setFlash('danger', 'Fichier invalide. ' . ($type === 'pdf'
                    ? 'Formats acceptés : PDF (50 Mo max).'
                    : 'Formats acceptés : mp4, webm, ogg (300 Mo max).'));
                $this->redirect("teacher/lessonForm/{$courseId}" . ($id > 0 ? "/{$id}" : ''));
            }
            $data['file_path'] = $path;
        } elseif ($id === 0) {
            $this->setFlash('danger', 'Veuillez sélectionner un fichier (PDF ou vidéo).');
            $this->redirect("teacher/lessonForm/{$courseId}");
        }

        if ($id > 0) {
            $lessonModel->update($id, $data);
            $this->setFlash('success', 'Leçon mise à jour avec succès.');
        } else {
            $data['position'] = $lessonModel->nextPosition($courseId);
            $lessonModel->create($data);
            $this->setFlash('success', 'Leçon ajoutée avec succès. Vous pouvez maintenant y associer une évaluation.');
        }

        $this->redirect("teacher/courseManage/{$courseId}");
    }

    public function lessonDelete(int $id): void
    {
        $this->verifyCsrf();
        $lessonModel = new Lesson();
        $lesson = $lessonModel->details($id);

        if (!$lesson || !(new Course())->belongsToTeacher($lesson['course_id'], current_user_id())) {
            $this->view('errors/403', [], 'main'); return;
        }

        $lessonModel->delete($id);
        $this->setFlash('success', 'Leçon supprimée.');
        $this->redirect("teacher/courseManage/{$lesson['course_id']}");
    }

    /** Réordonne une leçon (déplacement haut/bas) */
    public function lessonMove(int $id, string $direction): void
    {
        $this->verifyCsrf();
        $lessonModel = new Lesson();
        $lesson = $lessonModel->details($id);

        if (!$lesson || !(new Course())->belongsToTeacher($lesson['course_id'], current_user_id())) {
            $this->view('errors/403', [], 'main'); return;
        }

        $lessons = $lessonModel->byCourse($lesson['course_id']);
        $index = array_search($id, array_column($lessons, 'id'));

        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index !== false && isset($lessons[$swapWith])) {
            $posA = $lessons[$index]['position'];
            $posB = $lessons[$swapWith]['position'];
            $lessonModel->update((int)$lessons[$index]['id'], ['position' => $posB]);
            $lessonModel->update((int)$lessons[$swapWith]['id'], ['position' => $posA]);
        }

        $this->redirect("teacher/courseManage/{$lesson['course_id']}");
    }

    // =====================================================
    // ÉVALUATIONS
    // =====================================================

    public function evaluationForm(int $lessonId): void
    {
        $lessonModel = new Lesson();
        $lesson = $lessonModel->details($lessonId);

        if (!$lesson || !(new Course())->belongsToTeacher($lesson['course_id'], current_user_id())) {
            $this->view('errors/403', [], 'main'); return;
        }

        $evaluation = (new Evaluation())->getForLesson($lessonId);

        $this->view('teacher/evaluation_form', [
            'title' => 'Évaluation de la leçon',
            'lesson' => $lesson,
            'evaluation' => $evaluation,
        ]);
    }

    /**
     * Enregistre l'évaluation entière (titre, note de passage, questions, réponses).
     * Stratégie "tout remplacer" : on supprime les anciennes questions/réponses
     * puis on réinsère l'état soumis, dans une transaction.
     */
    public function evaluationSave(): void
    {
        $this->verifyCsrf();

        $lessonId = (int)($_POST['lesson_id'] ?? 0);
        $lessonModel = new Lesson();
        $lesson = $lessonModel->details($lessonId);

        if (!$lesson || !(new Course())->belongsToTeacher($lesson['course_id'], current_user_id())) {
            $this->view('errors/403', [], 'main'); return;
        }

        $title = trim($_POST['title'] ?? ('Évaluation - ' . $lesson['title']));
        $passingScore = max(0, min(100, (int)($_POST['passing_score'] ?? 50)));
        $questions = $_POST['questions'] ?? [];

        $db = Database::getConnection();
        $evaluationModel = new Evaluation();

        try {
            $db->beginTransaction();

            $existing = $evaluationModel->findWhere(['lesson_id' => $lessonId]);
            if ($existing) {
                $evaluationId = (int)$existing['id'];
                $evaluationModel->update($evaluationId, ['title' => $title, 'passing_score' => $passingScore]);
                Database::query("DELETE FROM questions WHERE evaluation_id = :eid", ['eid' => $evaluationId]);
            } else {
                $evaluationId = $evaluationModel->create([
                    'lesson_id' => $lessonId,
                    'title' => $title,
                    'passing_score' => $passingScore,
                ]);
            }

            $position = 0;
            foreach ($questions as $q) {
                $qText = trim($q['text'] ?? '');
                if ($qText === '') continue;

                $points = max(1, (int)($q['points'] ?? 1));
                $position++;

                $questionId = Database::query(
                    "INSERT INTO questions (evaluation_id, question_text, points, position) VALUES (:eid, :text, :points, :pos)",
                    ['eid' => $evaluationId, 'text' => $qText, 'points' => $points, 'pos' => $position]
                ) ? (int)$db->lastInsertId() : 0;

                $answers = $q['answers'] ?? [];
                foreach ($answers as $a) {
                    $aText = trim($a['text'] ?? '');
                    if ($aText === '') continue;
                    $isCorrect = !empty($a['correct']) ? 1 : 0;

                    Database::query(
                        "INSERT INTO answers (question_id, answer_text, is_correct) VALUES (:qid, :text, :correct)",
                        ['qid' => $questionId, 'text' => $aText, 'correct' => $isCorrect]
                    );
                }
            }

            $db->commit();
            $this->setFlash('success', 'Évaluation enregistrée avec succès.');
        } catch (Exception $e) {
            $db->rollBack();
            error_log('[evaluationSave] ' . $e->getMessage());
            $this->setFlash('danger', "Une erreur est survenue lors de l'enregistrement de l'évaluation.");
        }

        $this->redirect("teacher/courseManage/{$lesson['course_id']}");
    }

    // =====================================================
    // ÉTUDIANTS
    // =====================================================

    public function students(): void
    {
        $sql = "SELECT u.id, u.full_name, u.email, u.avatar, c.title AS course_title,
                       e.progress_percent, e.enrolled_at
                FROM enrollments e
                JOIN users u ON u.id = e.student_id
                JOIN courses c ON c.id = e.course_id
                WHERE c.teacher_id = :tid
                ORDER BY e.enrolled_at DESC";
        $students = Database::query($sql, ['tid' => current_user_id()])->fetchAll();

        $this->view('teacher/students', [
            'title' => 'Étudiants',
            'students' => $students,
        ]);
    }
}
