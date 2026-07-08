<?php
require_once APP_PATH . '/core/Model.php';

class Answer extends Model
{
    protected string $table = 'answers';

    public function byQuestion(int $questionId): array
    {
        return $this->where(['question_id' => $questionId], 'id ASC');
    }
}
