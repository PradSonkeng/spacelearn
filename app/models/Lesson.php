<?php
require_once APP_PATH . '/core/Model.php';

class Lesson extends Model
{
    protected string $table = 'lessons';

    /** Leçons d'un cours, ordonnées, avec indication de présence d'une évaluation */
    public function byCourse(int $courseId): array
    {
        $sql = "SELECT l.*,
                       ev.id AS evaluation_id, ev.title AS evaluation_title, ev.passing_score
                FROM lessons l
                LEFT JOIN evaluations ev ON ev.lesson_id = l.id
                WHERE l.course_id = :cid
                ORDER BY l.position ASC, l.id ASC";
        return Database::query($sql, ['cid' => $courseId])->fetchAll();
    }

    /** Prochaine position disponible pour une nouvelle leçon */
    public function nextPosition(int $courseId): int
    {
        $row = Database::query(
            "SELECT COALESCE(MAX(position), 0) + 1 AS pos FROM lessons WHERE course_id = :cid",
            ['cid' => $courseId]
        )->fetch();
        return (int)$row['pos'];
    }

    /** Détails d'une leçon avec infos du cours parent */
    public function details(int $lessonId): array|false
    {
        $sql = "SELECT l.*, c.id AS course_id, c.title AS course_title, c.teacher_id, c.module_id,
                       ev.id AS evaluation_id, ev.title AS evaluation_title, ev.passing_score
                FROM lessons l
                JOIN courses c ON c.id = l.course_id
                LEFT JOIN evaluations ev ON ev.lesson_id = l.id
                WHERE l.id = :id";
        return Database::query($sql, ['id' => $lessonId])->fetch();
    }
}
