<div class="row g-4">
    <div class="col-lg-8">
        <div class="nl-card p-4 mb-4">
            <span class="badge bg-primary-subtle text-primary mb-2"><?= e($course['module_title']) ?></span>
            <h3 class="fw-bold"><?= e($course['title']) ?></h3>
            <p class="text-muted"><?= nl2br(e($course['description'])) ?></p>
            <div class="d-flex flex-wrap gap-3 align-items-center small text-muted">
                <span><i class="fa-solid fa-chalkboard-user me-1"></i><?= e($course['teacher_name']) ?></span>
                <span><i class="fa-solid fa-list-check me-1"></i><?= (int)$course['nb_lessons'] ?> leçons</span>
                <span><i class="fa-solid fa-user-graduate me-1"></i><?= (int)$course['nb_students'] ?> inscrits</span>
                <span><?= render_stars((float)$course['avg_rating']) ?> (<?= (int)$course['nb_reviews'] ?> avis)</span>
            </div>
        </div>

        <!-- Liste des leçons -->
        <div class="nl-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-ol text-primary me-2"></i>Programme du cours</h5>

            <?php
            $unlocked = true; // la première leçon est toujours accessible
            foreach ($lessons as $i => $lesson):
                $lessonProgress = $progressMap[$lesson['id']] ?? null;
                $status = $lessonProgress['status'] ?? 'non_commence';
                $isLocked = $isEnrolled ? !$unlocked : true;
            ?>
            <div class="d-flex align-items-center gap-3 py-3 <?= $i > 0 ? 'border-top' : '' ?>">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                    <?= $status === 'termine' ? 'bg-success text-white' : ($isLocked ? 'bg-light text-muted' : 'bg-primary bg-opacity-10 text-primary') ?>"
                    style="width:40px;height:40px;">
                    <?php if ($status === 'termine'): ?>
                        <i class="fa-solid fa-check"></i>
                    <?php elseif ($isLocked): ?>
                        <i class="fa-solid fa-lock"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-<?= $lesson['type'] === 'pdf' ? 'file-pdf' : 'circle-play' ?>"></i>
                    <?php endif; ?>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold"><?= e($lesson['title']) ?></div>
                    <div class="text-muted small">
                        <?= $lesson['type'] === 'pdf' ? 'Document PDF' : 'Vidéo' ?>
                        <?php if ($lesson['evaluation_id']): ?> · <i class="fa-solid fa-clipboard-question"></i> Évaluation (<?= (int)$lesson['passing_score'] ?>% requis)<?php endif; ?>
                    </div>
                </div>
                <?php if ($isLocked): ?>
                    <span class="badge bg-light text-muted border">Verrouillé</span>
                <?php elseif ($isEnrolled): ?>
                    <a href="<?= url('student/lesson/' . $lesson['id']) ?>" class="btn btn-sm btn-outline-primary">
                        <?= $status === 'termine' ? 'Revoir' : 'Commencer' ?>
                    </a>
                <?php endif; ?>
            </div>
            <?php
                // La leçon suivante n'est débloquée que si celle-ci est terminée
                if ($status !== 'termine') { $unlocked = false; }
            endforeach;

            if (empty($lessons)): ?>
                <p class="text-muted small mb-0">Aucune leçon n'a encore été ajoutée à ce cours.</p>
            <?php endif; ?>
        </div>

        <!-- Avis -->
        <div class="nl-card p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-star text-primary me-2"></i>Avis des étudiants</h5>

            <?php if ($isEnrolled): ?>
            <div class="mb-4 pb-4 border-bottom">
                <label class="form-label d-block mb-1">Votre note</label>
                <div class="star-rating mb-2" id="starRating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fa-solid fa-star <?= ($myReview && $myReview['rating'] >= $i) ? 'active' : '' ?>" data-value="<?= $i ?>"></i>
                    <?php endfor; ?>
                </div>
                <textarea id="reviewComment" class="form-control mb-2" rows="2" placeholder="Partagez votre expérience sur ce cours..."><?= e($myReview['comment'] ?? '') ?></textarea>
                <button id="submitReviewBtn" class="btn btn-sm btn-primary">Envoyer mon avis</button>
                <span id="reviewMessage" class="small ms-2"></span>
            </div>
            <?php endif; ?>

            <?php if (empty($reviews)): ?>
                <p class="text-muted small mb-0">Aucun avis pour le moment. Soyez le premier à donner votre avis !</p>
            <?php else: ?>
                <?php foreach ($reviews as $r): ?>
                <div class="d-flex gap-3 mb-3">
                    <img src="<?= upload('avatars/' . $r['student_avatar']) ?>" onerror="this.src='<?= asset('img/default-avatar.svg') ?>'" class="rounded-circle" width="40" height="40" alt="">
                    <div>
                        <div class="fw-semibold small"><?= e($r['student_name']) ?> <span class="ms-1"><?= render_stars((float)$r['rating']) ?></span></div>
                        <div class="text-muted small"><?= e($r['comment']) ?></div>
                        <div class="text-muted" style="font-size:.75rem;"><?= time_ago($r['created_at']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="nl-card p-4 sticky-top" style="top: 90px;">
            <div class="text-center mb-3">
                <i class="fa-solid fa-graduation-cap fa-3x text-primary"></i>
            </div>
            <?php if ($isEnrolled): ?>
                <div class="alert alert-success text-center small"><i class="fa-solid fa-circle-check me-1"></i>Vous êtes inscrit à ce cours</div>
                <?php if (!empty($lessons)): ?>
                <a href="<?= url('student/lesson/' . $lessons[0]['id']) ?>" class="btn btn-primary w-100"><i class="fa-solid fa-play me-2"></i>Accéder au cours</a>
                <?php endif; ?>
            <?php else: ?>
                <form method="POST" action="<?= url('student/enroll/' . $course['id']) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary w-100 py-2"><i class="fa-solid fa-circle-plus me-2"></i>S'inscrire gratuitement</button>
                </form>
            <?php endif; ?>

            <hr>
            <ul class="list-unstyled small mb-0">
                <li class="d-flex justify-content-between py-1"><span>Module</span><strong><?= e($course['module_title']) ?></strong></li>
                <li class="d-flex justify-content-between py-1"><span>Leçons</span><strong><?= (int)$course['nb_lessons'] ?></strong></li>
                <li class="d-flex justify-content-between py-1"><span>Inscrits</span><strong><?= (int)$course['nb_students'] ?></strong></li>
                <li class="d-flex justify-content-between py-1"><span>Note</span><strong><?= number_format((float)$course['avg_rating'], 1) ?>/5</strong></li>
            </ul>
        </div>
    </div>
</div>

<?php if ($isEnrolled): ?>
<?php
$inlineJs = "
let selectedRating = " . (int)($myReview['rating'] ?? 0) . ";
document.querySelectorAll('#starRating i').forEach(star => {
    star.addEventListener('click', function () {
        selectedRating = parseInt(this.dataset.value);
        document.querySelectorAll('#starRating i').forEach(s => {
            s.classList.toggle('active', parseInt(s.dataset.value) <= selectedRating);
        });
    });
});

document.getElementById('submitReviewBtn').addEventListener('click', function () {
    if (selectedRating < 1) {
        document.getElementById('reviewMessage').textContent = 'Veuillez sélectionner une note.';
        document.getElementById('reviewMessage').className = 'small ms-2 text-danger';
        return;
    }
    const formData = new FormData();
    formData.append('csrf_token', '" . csrf_token() . "');
    formData.append('course_id', '" . $course['id'] . "');
    formData.append('rating', selectedRating);
    formData.append('comment', document.getElementById('reviewComment').value);

    fetch('" . url('api/submitReview') . "', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            const msg = document.getElementById('reviewMessage');
            msg.textContent = data.message;
            msg.className = 'small ms-2 ' + (data.ok ? 'text-success' : 'text-danger');
            if (data.ok) setTimeout(() => location.reload(), 1200);
        });
});
";
?>
<?php endif; ?>
