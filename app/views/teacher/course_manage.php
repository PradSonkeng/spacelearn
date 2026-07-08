<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= url('teacher/courses') ?>" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i></a>
    <div class="flex-grow-1">
        <h3 class="fw-bold mb-0"><?= e($course['title']) ?></h3>
        <p class="text-muted mb-0"><?= e($course['module_title']) ?> · <?= status_badge($course['status']) ?></p>
    </div>
    <a href="<?= url('teacher/courseEdit/' . $course['id']) ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-pen me-2"></i>Modifier le cours</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="nl-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-list-ol text-primary me-2"></i>Leçons</h5>
                <a href="<?= url('teacher/lessonForm/' . $course['id']) ?>" class="btn btn-sm btn-primary"><i class="fa-solid fa-plus me-1"></i>Ajouter une leçon</a>
            </div>

            <?php if (empty($lessons)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-file-circle-plus fa-3x mb-3"></i>
                    <p class="mb-0">Aucune leçon pour le moment. Ajoutez votre première leçon (PDF ou vidéo).</p>
                </div>
            <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($lessons as $i => $lesson): ?>
                <li class="list-group-item px-0">
                    <div class="d-flex align-items-start gap-3">
                        <div class="d-flex flex-column align-items-center">
                            <form method="POST" action="<?= url('teacher/lessonMove/' . $lesson['id'] . '/up') ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-light <?= $i === 0 ? 'disabled' : '' ?>" title="Monter"><i class="fa-solid fa-chevron-up"></i></button>
                            </form>
                            <span class="fw-bold text-muted"><?= $i + 1 ?></span>
                            <form method="POST" action="<?= url('teacher/lessonMove/' . $lesson['id'] . '/down') ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-light <?= $i === count($lessons) - 1 ? 'disabled' : '' ?>" title="Descendre"><i class="fa-solid fa-chevron-down"></i></button>
                            </form>
                        </div>

                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:45px;height:45px;">
                            <i class="fa-solid fa-<?= $lesson['type'] === 'pdf' ? 'file-pdf' : 'circle-play' ?> fa-lg"></i>
                        </div>

                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1"><?= e($lesson['title']) ?></h6>
                            <p class="text-muted small mb-1"><?= e(truncate($lesson['description'], 100)) ?></p>
                            <span class="badge bg-light text-dark border me-1"><?= $lesson['type'] === 'pdf' ? 'Document PDF' : 'Vidéo' ?></span>
                            <?php if ($lesson['evaluation_id']): ?>
                                <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-circle-check me-1"></i>Évaluation configurée (<?= (int)$lesson['passing_score'] ?>% requis)</span>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>Pas d'évaluation</span>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex flex-column gap-1">
                            <a href="<?= url('teacher/evaluationForm/' . $lesson['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-clipboard-question me-1"></i>Évaluation</a>
                            <a href="<?= url('teacher/lessonForm/' . $course['id'] . '/' . $lesson['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-pen me-1"></i>Modifier</a>
                            <form method="POST" action="<?= url('teacher/lessonDelete/' . $lesson['id']) ?>" onsubmit="return confirm('Supprimer cette leçon et son évaluation ?');">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger w-100"><i class="fa-solid fa-trash me-1"></i>Supprimer</button>
                            </form>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="nl-card p-4">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-circle-info text-primary me-2"></i>Résumé</h6>
            <ul class="list-unstyled mb-0 small">
                <li class="d-flex justify-content-between border-bottom py-2"><span>Module</span><strong><?= e($course['module_title']) ?></strong></li>
                <li class="d-flex justify-content-between border-bottom py-2"><span>Leçons</span><strong><?= count($lessons) ?></strong></li>
                <li class="d-flex justify-content-between border-bottom py-2"><span>Étudiants inscrits</span><strong><?= (int)$course['nb_students'] ?></strong></li>
                <li class="d-flex justify-content-between border-bottom py-2"><span>Note moyenne</span><strong><?= render_stars((float)$course['avg_rating']) ?></strong></li>
                <li class="d-flex justify-content-between py-2"><span>Statut</span><strong><?= status_badge($course['status']) ?></strong></li>
            </ul>
            <?php if ($course['status'] !== 'publie'): ?>
            <div class="alert alert-warning small mt-3 mb-0">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                Ce cours est en brouillon et n'est pas visible par les étudiants. Publiez-le depuis "Modifier le cours" lorsqu'il est prêt.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
