<?php
require_once APP_PATH . '/core/Model.php';

class Review extends Model
{
    protected string $table = 'reviews';

    /** Avis d'un cours avec le nom de l'étudiant */
    public function byCourse(int $courseId): array
    {
        $sql = "SELECT r.*, u.full_name AS student_name, u.avatar AS student_avatar
                FROM reviews r
                JOIN users u ON u.id = r.student_id
                WHERE r.course_id = :cid
                ORDER BY r.created_at DESC";
        return Database::query($sql, ['cid' => $courseId])->fetchAll();
    }

    /** Ajoute ou met à jour l'avis d'un étudiant pour un cours */
    public function upsert(int $studentId, int $courseId, int $rating, string $comment): void
    {
        $existing = $this->findWhere(['student_id' => $studentId, 'course_id' => $courseId]);
        if ($existing) {
            $this->update((int)$existing['id'], ['rating' => $rating, 'comment' => $comment]);
        } else {
            $this->create([
                'student_id' => $studentId,
                'course_id'  => $courseId,
                'rating'     => $rating,
                'comment'    => $comment,
            ]);
        }
    }
}
