<div class="mb-4">
    <h3 class="fw-bold mb-0">Tableau de bord</h3>
    <p class="text-muted mb-0">Bienvenue, <?= e($_SESSION['user_name']) ?> 👋 Continuez votre apprentissage !</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="nl-stat-card bg-1">
            <i class="fa-solid fa-book-open bg-icon"></i>
            <div class="small opacity-75">Cours suivis</div>
            <div class="fs-3 fw-bold"><?= count($enrollments) ?></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="nl-stat-card bg-2">
            <i class="fa-solid fa-chart-line bg-icon"></i>
            <div class="small opacity-75">Progression moyenne</div>
            <div class="fs-3 fw-bold"><?= $avgProgress ?>%</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="nl-stat-card bg-3">
            <i class="fa-solid fa-certificate bg-icon"></i>
            <div class="small opacity-75">Certificats obtenus</div>
            <div class="fs-3 fw-bold"><?= count($certificates) ?></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="nl-stat-card bg-4">
            <i class="fa-solid fa-store bg-icon"></i>
            <div class="small opacity-75">Explorer</div>
            <a href="<?= url('student/catalog') ?>" class="btn btn-sm btn-light mt-1 fw-semibold">Catalogue</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="nl-card p-4">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-book-open text-primary me-2"></i>Continuer un cours</h6>
            <?php if (empty($enrollments)): ?>
                <p class="text-muted small mb-3">Vous n'êtes inscrit à aucun cours pour le moment.</p>
                <a href="<?= url('student/catalog') ?>" class="btn btn-primary btn-sm">Parcourir le catalogue</a>
            <?php else: ?>
                <?php foreach (array_slice($enrollments, 0, 4) as $e): ?>
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div class="flex-grow-1">
                        <a href="<?= url('student/course/' . $e['course_id']) ?>" class="fw-semibold text-decoration-none"><?= e($e['title']) ?></a>
                        <div class="progress mt-1" style="height:6px;">
                            <div class="progress-bar bg-primary" style="width: <?= $e['progress_percent'] ?>%"></div>
                        </div>
                    </div>
                    <span class="fw-bold text-primary"><?= number_format((float)$e['progress_percent'], 0) ?>%</span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="nl-card p-4">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-lightbulb text-primary me-2"></i>Recommandé pour vous</h6>
            <?php if (empty($recommended)): ?>
                <p class="text-muted small mb-0">Vous suivez déjà tous les cours disponibles. Bravo !</p>
            <?php else: ?>
                <?php foreach ($recommended as $c): ?>
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                    <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px;">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div class="flex-grow-1">
                        <a href="<?= url('student/course/' . $c['id']) ?>" class="fw-semibold text-decoration-none small"><?= e($c['title']) ?></a>
                        <div class="text-muted" style="font-size:.78rem;"><?= e($c['module_title']) ?></div>
                    </div>
                    <?= render_stars((float)$c['avg_rating']) ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
