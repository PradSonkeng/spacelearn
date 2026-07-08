<?php
require_once APP_PATH . '/core/Model.php';

class ModuleModel extends Model
{
    protected string $table = 'modules';

    /** Tous les modules avec le nombre de cours associés */
    public function allWithStats(): array
    {
        $sql = "SELECT m.*,
                       COUNT(DISTINCT c.id) AS nb_courses,
                       u.full_name AS promoter_name
                FROM modules m
                LEFT JOIN courses c ON c.module_id = m.id
                LEFT JOIN users u ON u.id = m.promoter_id
                GROUP BY m.id
                ORDER BY m.created_at DESC";
        return Database::query($sql)->fetchAll();
    }

    /**
     * Vérifie si un étudiant a validé TOUS les cours publiés d'un module
     * (chaque cours doit être complété à 100%).
     */
    public function isCompletedByStudent(int $moduleId, int $studentId): bool
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM courses WHERE module_id = :mid1 AND status = 'publie') AS total_courses,
                    (SELECT COUNT(*) FROM enrollments e
                        JOIN courses c ON c.id = e.course_id
                        WHERE c.module_id = :mid2 AND c.status = 'publie'
                          AND e.student_id = :sid AND e.progress_percent >= 100) AS completed_courses";
        $row = Database::query($sql, ['mid1' => $moduleId, 'mid2' => $moduleId, 'sid' => $studentId])->fetch();

        return $row['total_courses'] > 0 && $row['total_courses'] == $row['completed_courses'];
    }

    /** Progression globale d'un étudiant sur un module (en %) */
    public function studentProgress(int $moduleId, int $studentId): float
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM courses WHERE module_id = :mid1 AND status = 'publie') AS total_courses,
                    (SELECT COALESCE(AVG(e.progress_percent), 0) FROM enrollments e
                        JOIN courses c ON c.id = e.course_id
                        WHERE c.module_id = :mid2 AND c.status = 'publie'
                          AND e.student_id = :sid) AS avg_progress";
        $row = Database::query($sql, ['mid1' => $moduleId, 'mid2' => $moduleId, 'sid' => $studentId])->fetch();
        if ($row['total_courses'] == 0) return 0;
        return round((float)$row['avg_progress'], 2);
    }
}
