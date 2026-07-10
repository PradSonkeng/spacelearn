<?php
require_once APP_PATH . '/core/Model.php';

class LessonProgress extends Model
{
    protected string $table = 'lesson_progress';

    /** Récupère (ou initialise) la progression d'un étudiant sur une leçon */
    public function get(int $studentId, int $lessonId): array|false
    {
        return $this->findWhere(['student_id' => $studentId, 'lesson_id' => $lessonId]);
    }

    /** Met à jour ou crée la progression d'une leçon */
    public function setStatus(int $studentId, int $lessonId, string $status, ?float $score = null): void
    {
        $existing = $this->get($studentId, $lessonId);
        $data = ['status' => $status];
        if ($score !== null) {
            $data['best_score'] = $existing && $existing['best_score'] !== null
                ? max((float)$existing['best_score'], $score)
                : $score;
        }
        if ($status === 'termine') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        if ($existing) {
            $this->update((int)$existing['id'], $data);
        } else {
            $data['student_id'] = $studentId;
            $data['lesson_id']  = $lessonId;
            $this->create($data);
        }
    }

    /** Map des progressions d'un étudiant pour un ensemble de leçons d'un cours */
    public function mapForCourse(int $studentId, int $courseId): array
    {
        $sql = "SELECT lp.* FROM lesson_progress lp
                JOIN lessons l ON l.id = lp.lesson_id
                WHERE l.course_id = :cid AND lp.student_id = :sid";
        $rows = Database::query($sql, ['cid' => $courseId, 'sid' => $studentId])->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['lesson_id']] = $row;
        }
        return $map;
    }
    // gerer le blockage/deblockage de l'evaluation
    public function markContentViewed(int $studentId, int $lessonId): void
    {
    		$existing = $this->get($studentId, $lessonId);
    		$data = ['content_viewed' => 1];
    	
    		if ($existing){
    			$this->update((int)$existing['id'], $data);
    		}else{
    			$data['student_id'] = $studentId;
    			$data['lesson_id'] = $lessonId;
    			$data['status'] = 'en_cours';
    			$this->create($data);
    		}
    }
    
    public function hasViewedContent(int $studentId, int $lessonId): bool
    {
    		$row = $this->get($studentId, $lessonId);
    		return $row && !empty($row['content_viewed']);
    }
}
