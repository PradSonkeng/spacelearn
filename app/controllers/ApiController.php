<?php
require_once APP_PATH . '/models/Notification.php';
require_once APP_PATH . '/models/Course.php';
require_once APP_PATH . '/models/Review.php';
require_once APP_PATH . '/models/Enrollment.php';

class ApiController extends Controller
{
    /** GET /api/notifications — Renvoie les notifications de l'utilisateur connecté */
    public function notifications(): void
    {
        if (!is_logged_in()) {
            $this->json(['count' => 0, 'items' => []]);
        }

        $model = new Notification();
        $items = $model->forUser(current_user_id(), 8);

        foreach ($items as &$item) {
            $item['time_ago'] = time_ago($item['created_at']);
        }

        $this->json([
            'count' => $model->unreadCount(current_user_id()),
            'items' => $items,
        ]);
    }

    /** POST /api/markNotificationsRead */
    public function markNotificationsRead(): void
    {
        if (!is_logged_in()) {
            $this->json(['ok' => false], 401);
        }
        (new Notification())->markAllRead(current_user_id());
        $this->json(['ok' => true]);
    }

    /** GET /api/search?q=... — Recherche AJAX dans le catalogue de cours */
    public function search(): void
    {
        $query = trim($_GET['q'] ?? '');
        if ($query === '' || mb_strlen($query) < 2) {
            $this->json([]);
        }

        $courseModel = new Course();
        $results = $courseModel->catalog($query);

        $output = array_map(function ($c) {
            return [
                'id' => $c['id'],
                'title' => $c['title'],
                'module_title' => $c['module_title'],
            ];
        }, array_slice($results, 0, 8));

        $this->json($output);
    }

    /** POST /api/submitReview — Ajoute/Met à jour un avis sur un cours (étudiant uniquement) */
    public function submitReview(): void
    {
        if (!is_logged_in() || current_role() !== 'etudiant') {
            $this->json(['ok' => false, 'message' => 'Accès refusé.'], 403);
        }
        $this->verifyCsrf();

        $courseId = (int)($_POST['course_id'] ?? 0);
        $rating   = (int)($_POST['rating'] ?? 0);
        $comment  = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $this->json(['ok' => false, 'message' => 'Note invalide (1 à 5).'], 422);
        }

        $enrollmentModel = new Enrollment();
        if (!$enrollmentModel->isEnrolled(current_user_id(), $courseId)) {
            $this->json(['ok' => false, 'message' => 'Vous devez être inscrit à ce cours.'], 403);
        }

        (new Review())->upsert(current_user_id(), $courseId, $rating, $comment);
        $this->json(['ok' => true, 'message' => 'Merci pour votre avis !']);
    }
}
