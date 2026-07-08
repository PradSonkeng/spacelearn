<?php
require_once APP_PATH . '/core/Model.php';

class Enrollment extends Model
{
    protected string $table = 'enrollments';

    /** Inscrit un étudiant à un cours (si pas déjà inscrit) */
    public function enroll(int $studentId, int $courseId): int
    {
        $existing = $this->findWhere(['student_id' => $studentId, 'course_id' => $courseId]);
        if ($existing) {
            return (int)$existing['id'];
        }
        return $this->create(['student_id' => $studentId, 'course_id' => $courseId]);
    }

    /** Vérifie si un étudiant est inscrit à un cours */
    public function isEnrolled(int $studentId, int $courseId): bool
    {
        return (bool)$this->findWhere(['student_id' => $studentId, 'course_id' => $courseId]);
    }

    /** Cours suivis par un étudiant, avec progression et infos du cours */
    public function byStudent(int $studentId): array
    {
        $sql = "SELECT e.*, c.title, c.image, c.module_id, m.title AS module_title,
                       u.full_name AS teacher_name,
                       (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS nb_lessons
                FROM enrollments e
                JOIN courses c ON c.id = e.course_id
                JOIN modules m ON m.id = c.module_id
                JOIN users u ON u.id = c.teacher_id
                WHERE e.student_id = :sid
                ORDER BY e.enrolled_at DESC";
        return Database::query($sql, ['sid' => $studentId])->fetchAll();
    }

    /**
     * Recalcule et met à jour la progression (%) d'un étudiant sur un cours,
     * en se basant sur les leçons marquées "terminées".
     */
    public function recalcProgress(int $studentId, int $courseId): float
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM lessons WHERE course_id = :cid1) AS total,
                    (SELECT COUNT(*) FROM lesson_progress lp
                        JOIN lessons l ON l.id = lp.lesson_id
                        WHERE l.course_id = :cid2 AND lp.student_id = :sid AND lp.status = 'termine') AS done";
        $row = Database::query($sql, ['cid1' => $courseId, 'cid2' => $courseId, 'sid' => $studentId])->fetch();

        $progress = $row['total'] > 0 ? round(($row['done'] / $row['total']) * 100, 2) : 0;

        $enrollment = $this->findWhere(['student_id' => $studentId, 'course_id' => $courseId]);
        if ($enrollment) {
            $data = ['progress_percent' => $progress];
            if ($progress >= 100 && $enrollment['completed_at'] === null) {
                $data['completed_at'] = date('Y-m-d H:i:s');
            }
            $this->update((int)$enrollment['id'], $data);
        }

        return $progress;
    }
}
