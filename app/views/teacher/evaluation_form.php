<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= url('teacher/courseManage/' . $lesson['course_id']) ?>" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <h3 class="fw-bold mb-0">Évaluation</h3>
        <p class="text-muted mb-0">Leçon : <?= e($lesson['title']) ?></p>
    </div>
</div>

<div class="alert alert-info">
    <i class="fa-solid fa-circle-info me-2"></i>
    Créez des questions à choix unique ou multiple. Cochez la ou les bonnes réponses pour chaque question.
    L'étudiant devra obtenir au moins la <strong>note de passage</strong> pour que la leçon soit considérée comme terminée.
</div>

<form method="POST" action="<?= url('teacher/evaluationSave') ?>" id="evaluationForm">
    <?= csrf_field() ?>
    <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">

    <div class="nl-card p-4 mb-4">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Titre de l'évaluation</label>
                <input type="text" name="title" class="form-control" value="<?= e($evaluation['title'] ?? 'Évaluation - ' . $lesson['title']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Note de passage (%)</label>
                <input type="number" name="passing_score" class="form-control" min="0" max="100" value="<?= $evaluation['passing_score'] ?? 50 ?>">
            </div>
        </div>
    </div>

    <div id="questionsContainer"></div>

    <button type="button" id="addQuestionBtn" class="btn btn-outline-primary mb-4"><i class="fa-solid fa-plus me-2"></i>Ajouter une question</button>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Enregistrer l'évaluation</button>
        <a href="<?= url('teacher/courseManage/' . $lesson['course_id']) ?>" class="btn btn-light">Annuler</a>
    </div>
</form>

<?php
$existingQuestions = $evaluation['questions'] ?? [];

$inlineJs = "
const existingQuestions = " . json_encode($existingQuestions, JSON_UNESCAPED_UNICODE) . ";
let qIndex = 0;

function answerRow(qIdx, aText = '', aCorrect = false) {
    const aIdx = Date.now() + Math.floor(Math.random()*1000);
    return `
    <div class=\"input-group mb-2 answer-row\">
        <span class=\"input-group-text\">
            <input class=\"form-check-input mt-0\" type=\"checkbox\" name=\"questions[\${qIdx}][answers][\${aIdx}][correct]\" value=\"1\" \${aCorrect ? 'checked' : ''} title=\"Bonne réponse\">
        </span>
        <input type=\"text\" class=\"form-control\" name=\"questions[\${qIdx}][answers][\${aIdx}][text]\" placeholder=\"Texte de la réponse\" value=\"\${aText.replace(/\\\"/g,'&quot;')}\" required>
        <button type=\"button\" class=\"btn btn-outline-danger remove-answer\" title=\"Supprimer\"><i class=\"fa-solid fa-xmark\"></i></button>
    </div>`;
}

function questionBlock(qText = '', qPoints = 1, answers = []) {
    const idx = qIndex++;
    const div = document.createElement('div');
    div.className = 'nl-card p-4 mb-3 question-block';
    div.innerHTML = `
        <div class=\"d-flex justify-content-between align-items-start mb-3\">
            <h6 class=\"fw-bold mb-0\">Question \${idx + 1}</h6>
            <button type=\"button\" class=\"btn btn-sm btn-outline-danger remove-question\"><i class=\"fa-solid fa-trash me-1\"></i>Supprimer</button>
        </div>
        <div class=\"row g-3 mb-3\">
            <div class=\"col-md-9\">
                <label class=\"form-label\">Énoncé de la question</label>
                <input type=\"text\" class=\"form-control\" name=\"questions[\${idx}][text]\" value=\"\${qText.replace(/\\\"/g,'&quot;')}\" placeholder=\"Saisissez votre question...\" required>
            </div>
            <div class=\"col-md-3\">
                <label class=\"form-label\">Points</label>
                <input type=\"number\" class=\"form-control\" name=\"questions[\${idx}][points]\" min=\"1\" value=\"\${qPoints}\">
            </div>
        </div>
        <label class=\"form-label\">Réponses (cochez la/les bonnes réponses)</label>
        <div class=\"answers-container\"></div>
        <button type=\"button\" class=\"btn btn-sm btn-outline-secondary add-answer mt-1\"><i class=\"fa-solid fa-plus me-1\"></i>Ajouter une réponse</button>
    `;

    const answersContainer = div.querySelector('.answers-container');
    if (answers.length > 0) {
        answers.forEach(a => answersContainer.insertAdjacentHTML('beforeend', answerRow(idx, a.answer_text, a.is_correct == 1)));
    } else {
        answersContainer.insertAdjacentHTML('beforeend', answerRow(idx));
        answersContainer.insertAdjacentHTML('beforeend', answerRow(idx));
    }

    div.querySelector('.add-answer').addEventListener('click', () => {
        answersContainer.insertAdjacentHTML('beforeend', answerRow(idx));
    });

    div.addEventListener('click', (e) => {
        if (e.target.closest('.remove-answer')) {
            e.target.closest('.answer-row').remove();
        }
        if (e.target.closest('.remove-question')) {
            div.remove();
        }
    });

    return div;
}

document.getElementById('addQuestionBtn').addEventListener('click', () => {
    document.getElementById('questionsContainer').appendChild(questionBlock());
});

// Pré-remplissage avec les questions existantes
if (existingQuestions.length > 0) {
    existingQuestions.forEach(q => {
        document.getElementById('questionsContainer').appendChild(questionBlock(q.question_text, q.points, q.answers));
    });
} else {
    document.getElementById('questionsContainer').appendChild(questionBlock());
}
";
?>
