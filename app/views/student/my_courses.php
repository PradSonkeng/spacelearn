<div class="mb-4">
    <h3 class="fw-bold mb-0">Mes cours</h3>
    <p class="text-muted mb-0">Retrouvez tous les cours auxquels vous êtes inscrit.</p>
</div>

<div class="row g-4">
    <?php foreach ($enrollments as $e): ?>
    <div class="col-md-6 col-lg-4">
        <div class="nl-card h-100">
            <div class="nl-course-cover d-flex align-items-center justify-content-center text-white">
                <i class="fa-solid fa-book fa-2x"></i>
            </div>
            <div class="p-3">
                <span class="badge bg-primary-subtle text-primary mb-2"><?= e($e['module_title']) ?></span>
                <h6 class="fw-bold"><?= e($e['title']) ?></h6>
                <div class="text-muted small mb-2"><i class="fa-solid fa-chalkboard-user me-1"></i><?= e($e['teacher_name']) ?></div>

                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="progress flex-grow-1" style="height:8px;">
                        <div class="progress-bar <?= $e['progress_percent'] >= 100 ? 'bg-success' : 'bg-primary' ?>" style="width: <?= $e['progress_percent'] ?>%"></div>
                    </div>
                    <small class="fw-bold text-muted"><?= number_format((float)$e['progress_percent'], 0) ?>%</small>
                </div>

                <a href="<?= url('student/course/' . $e['course_id']) ?>" class="btn btn-sm w-100 <?= $e['progress_percent'] >= 100 ? 'btn-success' : 'btn-primary' ?>">
                    <?php if ($e['progress_percent'] >= 100): ?>
                        <i class="fa-solid fa-circle-check me-1"></i>Cours terminé
                    <?php else: ?>
                        <i class="fa-solid fa-play me-1"></i>Continuer
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($enrollments)): ?>
        <div class="col-12">
            <div class="nl-card p-5 text-center">
                <i class="fa-solid fa-book fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-3">Vous n'êtes inscrit à aucun cours pour le moment.</p>
                <a href="<?= url('student/catalog') ?>" class="btn btn-primary">Parcourir le catalogue</a>
            </div>
        </div>
    <?php endif; ?>
</div>
