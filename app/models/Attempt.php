<?php
require_once APP_PATH . '/core/Model.php';

class Attempt extends Model
{
    protected string $table = 'attempts';

    /** Enregistre une nouvelle tentative */
    public function record(int $studentId, int $evaluationId, float $score, bool $passed): int
    {
        return $this->create([
            'student_id'    => $studentId,
            'evaluation_id' => $evaluationId,
            'score'         => $score,
            'passed'        => $passed ? 1 : 0,
        ]);
    }

    /** Meilleure note obtenue par un étudiant sur une évaluation */
    public function bestScore(int $studentId, int $evaluationId): ?float
    {
        $row = Database::query(
            "SELECT MAX(score) AS best FROM attempts WHERE student_id = :sid AND evaluation_id = :eid",
            ['sid' => $studentId, 'eid' => $evaluationId]
        )->fetch();
        return $row['best'] !== null ? (float)$row['best'] : null;
    }

    /** Historique des tentatives d'un étudiant pour une évaluation */
    public function history(int $studentId, int $evaluationId): array
    {
        return Database::query(
            "SELECT * FROM attempts WHERE student_id = :sid AND evaluation_id = :eid ORDER BY attempt_date DESC",
            ['sid' => $studentId, 'eid' => $evaluationId]
        )->fetchAll();
    }
}
