<?php
require_once APP_PATH . '/core/Model.php';

class Question extends Model
{
    protected string $table = 'questions';

    public function byEvaluation(int $evaluationId): array
    {
        return $this->where(['evaluation_id' => $evaluationId], 'position ASC, id ASC');
    }

    public function nextPosition(int $evaluationId): int
    {
        $row = Database::query(
            "SELECT COALESCE(MAX(position), 0) + 1 AS pos FROM questions WHERE evaluation_id = :eid",
            ['eid' => $evaluationId]
        )->fetch();
        return (int)$row['pos'];
    }
}
