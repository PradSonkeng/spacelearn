<div class="mb-4">
    <h3 class="fw-bold mb-0">Catalogue des cours</h3>
    <p class="text-muted mb-0">Découvrez tous les cours disponibles et inscrivez-vous gratuitement.</p>
</div>

<form method="GET" action="<?= url('student/catalog') ?>" class="row g-2 mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
            <input type="text" name="q" class="form-control" placeholder="Rechercher un cours..." value="<?= e($search) ?>">
        </div>
    </div>
    <div class="col-md-4">
        <select name="module" class="form-select" onchange="this.form.submit()">
            <option value="">Tous les modules</option>
            <?php foreach ($modules as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $selectedModule == $m['id'] ? 'selected' : '' ?>><?= e($m['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100"><i class="fa-solid fa-filter me-1"></i>Filtrer</button>
    </div>
</form>

<div class="row g-4">
    <?php foreach ($courses as $course): ?>
    <div class="col-md-6 col-lg-4">
        <div class="nl-card h-100">
            <div class="nl-course-cover d-flex align-items-center justify-content-center text-white">
                <i class="fa-solid fa-book fa-2x"></i>
            </div>
            <div class="p-3">
                <span class="badge bg-primary-subtle text-primary mb-2"><?= e($course['module_title']) ?></span>
                <h6 class="fw-bold"><?= e($course['title']) ?></h6>
                <p class="text-muted small mb-2"><?= e(truncate($course['description'], 90)) ?></p>
                <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                    <span><i class="fa-solid fa-list-check me-1"></i><?= (int)$course['nb_lessons'] ?> leçons</span>
                    <span><?= render_stars((float)$course['avg_rating']) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                    <span><i class="fa-solid fa-chalkboard-user me-1"></i><?= e($course['teacher_name']) ?></span>
                    <span><i class="fa-solid fa-user-graduate me-1"></i><?= (int)$course['nb_students'] ?></span>
                </div>
                <a href="<?= url('student/course/' . $course['id']) ?>" class="btn btn-sm w-100 <?= in_array($course['id'], $enrolledIds) ? 'btn-outline-success' : 'btn-primary' ?>">
                    <?php if (in_array($course['id'], $enrolledIds)): ?>
                        <i class="fa-solid fa-circle-check me-1"></i>Continuer
                    <?php else: ?>
                        <i class="fa-solid fa-eye me-1"></i>Voir le cours
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($courses)): ?>
        <div class="col-12">
            <div class="nl-card p-5 text-center">
                <i class="fa-solid fa-magnifying-glass fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Aucun cours ne correspond à votre recherche.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
