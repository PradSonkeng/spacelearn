<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= url('student/lesson/' . $lesson['id']) ?>" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <h4 class="fw-bold mb-0"><?= e($evaluation['title']) ?></h4>
        <p class="text-muted mb-0">Leçon : <?= e($lesson['title']) ?> · Note de passage : <strong><?= (int)$evaluation['passing_score'] ?>%</strong></p>
    </div>
</div>

<?php if (!empty($history)): ?>
<div class="alert alert-light border d-flex align-items-center gap-2 small">
    <i class="fa-solid fa-history text-primary"></i>
    Tentatives précédentes :
    <?php foreach (array_slice($history, 0, 5) as $h): ?>
        <span class="badge <?= $h['passed'] ? 'bg-success' : 'bg-secondary' ?>"><?= number_format((float)$h['score'], 0) ?>%</span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div id="resultBox" class="d-none mb-4"></div>

<form id="quizForm">
    <?= csrf_field() ?>
    <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">

    <?php foreach ($evaluation['questions'] as $qi => $q): ?>
    <div class="nl-card p-4 mb-3 question-card" data-question-id="<?= $q['id'] ?>">
        <h6 class="fw-bold mb-3">Question <?= $qi + 1 ?> <span class="text-muted small">(<?= (int)$q['points'] ?> pt<?= $q['points'] > 1 ? 's' : '' ?>)</span></h6>
        <p class="mb-3"><?= e($q['question_text']) ?></p>
        <div class="d-flex flex-column gap-2">
            <?php foreach ($q['answers'] as $a): ?>
            <label class="quiz-option d-flex align-items-center gap-2 mb-0" data-answer-id="<?= $a['id'] ?>">
                <input type="checkbox" class="form-check-input mt-0" name="answers[<?= $q['id'] ?>][]" value="<?= $a['id'] ?>">
                <?= e($a['answer_text']) ?>
            </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($evaluation['questions'])): ?>
        <div class="nl-card p-5 text-center">
            <i class="fa-solid fa-circle-exclamation fa-2x text-warning mb-2"></i>
            <p class="text-muted mb-0">Cette évaluation ne contient encore aucune question.</p>
        </div>
    <?php else: ?>
        <button type="submit" class="btn btn-primary" id="submitQuizBtn"><i class="fa-solid fa-paper-plane me-2"></i>Soumettre mes réponses</button>
    <?php endif; ?>
</form>

<?php
$inlineJs = "
document.querySelectorAll('.quiz-option').forEach(opt => {
    opt.addEventListener('click', function () {
        this.classList.toggle('selected', this.querySelector('input').checked);
    });
});

const quizForm = document.getElementById('quizForm');
if (quizForm) {
    quizForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(quizForm);
        const submitBtn = document.getElementById('submitQuizBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class=\"fa-solid fa-spinner fa-spin me-2\"></i>Correction en cours...';

        fetch('" . url('student/submitEvaluation') . "', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class=\"fa-solid fa-paper-plane me-2\"></i>Soumettre mes réponses';

                if (!data.ok) {
                    alert(data.message || 'Une erreur est survenue.');
                    return;
                }

                // Mise en évidence des réponses correctes / incorrectes
                data.details.forEach(d => {
                    const card = document.querySelector(`.question-card[data-question-id=\"\${d.question_id}\"]`);
                    if (!card) return;
                    card.querySelectorAll('.quiz-option').forEach(opt => {
                        const aid = parseInt(opt.dataset.answerId);
                        opt.querySelector('input').disabled = true;
                        if (d.correct_answers.includes(aid)) {
                            opt.classList.add('correct');
                        } else if (d.given_answers.includes(aid)) {
                            opt.classList.add('incorrect');
                        }
                    });
                });

                const box = document.getElementById('resultBox');
                box.classList.remove('d-none');

                let html = `
                    <div class=\"nl-card p-4 \${data.passed ? 'border-success' : 'border-danger'}\">
                        <div class=\"d-flex align-items-center gap-3\">
                            <i class=\"fa-solid fa-\${data.passed ? 'circle-check text-success' : 'circle-xmark text-danger'} fa-2x\"></i>
                            <div>
                                <h5 class=\"fw-bold mb-0\">Score obtenu : \${data.score}% (\${data.points_obtained}/\${data.total_points} pts)</h5>
                                <p class=\"mb-0 text-muted\">Note de passage requise : \${data.passing_score}%</p>
                            </div>
                        </div>
                `;

                if (data.passed) {
                    html += `<div class=\"alert alert-success mt-3 mb-0\"><i class=\"fa-solid fa-trophy me-2\"></i>Félicitations, vous avez réussi cette évaluation ! Votre progression dans le cours est mise à jour (\${data.course_progress}%).</div>`;
                } else {
                    html += `<div class=\"alert alert-warning mt-3 mb-0\"><i class=\"fa-solid fa-triangle-exclamation me-2\"></i>Vous n'avez pas atteint la note de passage. Révisez la leçon et retentez votre chance.</div>`;
                }

                if (data.certificate_issued) {
                    html += `<div class=\"alert alert-primary mt-2 mb-0\"><i class=\"fa-solid fa-certificate me-2\"></i>🎉 Vous avez validé l'ensemble du module <strong>\${data.certificate_issued.module_title}</strong> ! Votre certificat est disponible dans <a href=\"" . url('student/certificates') . "\">Mes certificats</a>.</div>`;
                }

                html += `<div class=\"mt-3 d-flex gap-2\">
                    <a href=\"" . url('student/lesson/' . $lesson['id']) . "\" class=\"btn btn-outline-primary btn-sm\">Retour à la leçon</a>
                    \${!data.passed ? '<button onclick=\"location.reload()\" class=\"btn btn-primary btn-sm\">Retenter</button>' : ''}
                </div></div>`;

                box.innerHTML = html;
                box.scrollIntoView({ behavior: 'smooth' });
            })
            .catch(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class=\"fa-solid fa-paper-plane me-2\"></i>Soumettre mes réponses';
                alert('Erreur réseau, veuillez réessayer.');
            });
    });
}
";
?>
