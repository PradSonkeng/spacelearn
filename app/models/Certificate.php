<?php
require_once APP_PATH . '/core/Model.php';

class Certificate extends Model
{
    protected string $table = 'certificates';

    /** Délivre un certificat (s'il n'existe pas déjà) et retourne son enregistrement */
    public function issue(int $studentId, int $courseId): array
    {
        $existing = $this->findWhere(['student_id' => $studentId, 'course_id' => $courseId]);
        if ($existing) {
            return $existing;
        }

        $code = strtoupper(bin2hex(random_bytes(6))); // ex: 9F3A1C7B2E4D
        $code = 'CERT-' . date('Y') . '-' . $code;

        $id = $this->create([
            'student_id' => $studentId,
            'course_id'  => $courseId,
            'code'       => $code,
        ]);

        return $this->find($id);
    }

    /** Certificats d'un étudiant avec infos du cours */
    public function byStudent(int $studentId): array
    {
        $sql = "SELECT c.*, m.title AS module_title
                FROM certificates c
                JOIN courses co ON co.id = c.course_id
                WHERE c.student_id = :sid
                ORDER BY c.issued_at DESC";
        return Database::query($sql, ['sid' => $studentId])->fetchAll();
    }

    /** Vérifie l'authenticité d'un certificat à partir de son code */
    public function verify(string $code): array|false
    {
        $sql = "SELECT c.*, m.title AS module_title, u.full_name AS student_name
                FROM certificates c
                JOIN courses co ON co.id = c.course_id
                JOIN users u ON u.id = c.student_id
                WHERE c.code = :code";
        return Database::query($sql, ['code' => $code])->fetch();
    }
}
