<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Mes cours</h3>
        <p class="text-muted mb-0">Gérez vos cours, leçons et évaluations.</p>
    </div>
    <a href="<?= url('teacher/courseCreate') ?>" class="btn btn-primary"><i class="fa-solid fa-circle-plus me-2"></i>Nouveau cours</a>
</div>

<div class="row g-4">
    <?php foreach ($courses as $course): ?>
    <div class="col-md-6 col-lg-4">
        <div class="nl-card h-100">
            <div class="nl-course-cover position-relative d-flex align-items-center justify-content-center text-white">
                <i class="fa-solid fa-book fa-2x"></i>
                <span class="nl-badge-type"><?= status_badge($course['status']) ?></span>
            </div>
            <div class="p-3">
                <span class="badge bg-primary-subtle text-primary mb-2"><?= e($course['module_title']) ?></span>
                <h6 class="fw-bold"><?= e($course['title']) ?></h6>
                <p class="text-muted small mb-2"><?= e(truncate($course['description'], 90)) ?></p>
                <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                    <span><i class="fa-solid fa-list-check me-1"></i><?= (int)$course['nb_lessons'] ?> leçons</span>
                    <span><i class="fa-solid fa-user-graduate me-1"></i><?= (int)$course['nb_students'] ?> étudiants</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= url('teacher/courseManage/' . $course['id']) ?>" class="btn btn-sm btn-primary flex-grow-1"><i class="fa-solid fa-gear me-1"></i>Gérer</a>
                    <a href="<?= url('teacher/courseEdit/' . $course['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-pen"></i></a>
                    <form method="POST" action="<?= url('teacher/courseDelete/' . $course['id']) ?>" onsubmit="return confirm('Supprimer ce cours et toutes ses leçons ?');">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($courses)): ?>
        <div class="col-12">
            <div class="nl-card p-5 text-center">
                <i class="fa-solid fa-book fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-3">Vous n'avez pas encore créé de cours.</p>
                <a href="<?= url('teacher/courseCreate') ?>" class="btn btn-primary">Créer mon premier cours</a>
            </div>
        </div>
    <?php endif; ?>
</div>
