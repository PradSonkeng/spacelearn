<?php
require_once APP_PATH . '/core/Model.php';

class Evaluation extends Model
{
    protected string $table = 'evaluations';

    /** Récupère l'évaluation d'une leçon (avec ses questions/réponses) */
    public function getForLesson(int $lessonId): array|false
    {
        $eval = $this->findWhere(['lesson_id' => $lessonId]);
        if (!$eval) return false;

        $questions = Database::query(
            "SELECT * FROM questions WHERE evaluation_id = :eid ORDER BY position ASC, id ASC",
            ['eid' => $eval['id']]
        )->fetchAll();

        foreach ($questions as &$q) {
            $q['answers'] = Database::query(
                "SELECT * FROM answers WHERE question_id = :qid ORDER BY id ASC",
                ['qid' => $q['id']]
            )->fetchAll();
        }
        $eval['questions'] = $questions;
        return $eval;
    }

    /** Total des points possibles pour une évaluation */
    public function totalPoints(int $evaluationId): int
    {
        $row = Database::query(
            "SELECT COALESCE(SUM(points), 0) AS total FROM questions WHERE evaluation_id = :eid",
            ['eid' => $evaluationId]
        )->fetch();
        return (int)$row['total'];
    }

    /**
     * Corrige automatiquement une tentative.
     *
     * @param array $submittedAnswers ['question_id' => [answer_id, ...] ou answer_id]
     * @return array ['score' => float (0-100), 'points_obtained' => int, 'total_points' => int, 'details' => []]
     */
    public function correct(int $evaluationId, array $submittedAnswers): array
    {
        $questions = Database::query(
            "SELECT * FROM questions WHERE evaluation_id = :eid",
            ['eid' => $evaluationId]
        )->fetchAll();

        $totalPoints = 0;
        $obtained = 0;
        $details = [];

        foreach ($questions as $q) {
            $totalPoints += (int)$q['points'];

            $correctAnswers = Database::query(
                "SELECT id FROM answers WHERE question_id = :qid AND is_correct = 1",
                ['qid' => $q['id']]
            )->fetchAll(PDO::FETCH_COLUMN);

            $given = $submittedAnswers[$q['id']] ?? [];
            $given = is_array($given) ? $given : [$given];
            $given = array_map('intval', array_filter($given, 'is_numeric'));

            sort($correctAnswers);
            sort($given);

            $isCorrect = ($correctAnswers === $given) && !empty($correctAnswers);
            if ($isCorrect) {
                $obtained += (int)$q['points'];
            }

            $details[] = [
                'question_id' => $q['id'],
                'correct'     => $isCorrect,
                'correct_answers' => $correctAnswers,
                'given_answers'   => $given,
            ];
        }

        $score = $totalPoints > 0 ? round(($obtained / $totalPoints) * 100, 2) : 0;

        return [
            'score'           => $score,
            'points_obtained' => $obtained,
            'total_points'    => $totalPoints,
            'details'         => $details,
        ];
    }
}
