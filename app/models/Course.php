<?php
require_once APP_PATH . '/core/Model.php';

class Course extends Model
{
    protected string $table = 'courses';

    /** Cours d'un enseignant donné, avec nombre de leçons et d'inscrits */
    public function byTeacher(int $teacherId): array
    {
        $sql = "SELECT c.*, m.title AS module_title,
                       (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS nb_lessons,
                       (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS nb_students
                FROM courses c
                JOIN modules m ON m.id = c.module_id
                WHERE c.teacher_id = :tid
                ORDER BY c.created_at DESC";
        return Database::query($sql, ['tid' => $teacherId])->fetchAll();
    }

    /** Détails complets d'un cours (module, enseignant, note moyenne) */
    public function details(int $courseId): array|false
    {
        $sql = "SELECT c.*, m.title AS module_title, u.full_name AS teacher_name, u.avatar AS teacher_avatar,
                       (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS nb_lessons,
                       (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS nb_students,
                       (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r WHERE r.course_id = c.id) AS avg_rating,
                       (SELECT COUNT(*) FROM reviews r WHERE r.course_id = c.id) AS nb_reviews
                FROM courses c
                JOIN modules m ON m.id = c.module_id
                JOIN users u ON u.id = c.teacher_id
                WHERE c.id = :id";
        return Database::query($sql, ['id' => $courseId])->fetch();
    }

    /** Catalogue public : cours publiés, avec filtres optionnels */
    public function catalog(string $search = '', ?int $moduleId = null): array
    {
        $sql = "SELECT c.*, m.title AS module_title, u.full_name AS teacher_name,
                       (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS nb_lessons,
                       (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS nb_students,
                       (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r WHERE r.course_id = c.id) AS avg_rating
                FROM courses c
                JOIN modules m ON m.id = c.module_id
                JOIN users u ON u.id = c.teacher_id
                WHERE c.status = 'publie'";

        $params = [];
        if ($search !== '') {
            $sql .= " AND (c.title LIKE :search OR c.description LIKE :search OR m.title LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        if ($moduleId !== null) {
            $sql .= " AND c.module_id = :module_id";
            $params['module_id'] = $moduleId;
        }
        $sql .= " ORDER BY c.created_at DESC";

        return Database::query($sql, $params)->fetchAll();
    }

    /** Vérifie si un cours appartient bien à un enseignant donné (sécurité) */
    public function belongsToTeacher(int $courseId, int $teacherId): bool
    {
        return (bool)$this->findWhere(['id' => $courseId, 'teacher_id' => $teacherId]);
    }
}
