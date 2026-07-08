<!-- Navbar publique -->
<nav class="navbar navbar-expand-lg navbar-dark bg-transparent position-absolute w-100" style="z-index: 10;">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="<?= url('') ?>">
            <i class="fa-solid fa-graduation-cap me-2"></i><?= APP_NAME ?>
        </a>
        <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navMenu">
            <ul class="navbar-nav align-items-center gap-2">
                <li class="nav-item"><a class="nav-link" href="#courses">Cours</a></li>
                <li class="nav-item"><a class="nav-link" href="#modules">Modules</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('home/verify') ?>">Vérifier un certificat</a></li>
                <?php if (is_logged_in()): ?>
                    <li class="nav-item"><a class="btn btn-light btn-sm fw-semibold" href="<?= url(current_role() === 'promoteur' ? 'admin/dashboard' : (current_role() === 'enseignant' ? 'teacher/dashboard' : 'student/dashboard')) ?>">Mon espace</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url('auth/login') ?>">Connexion</a></li>
                    <li class="nav-item"><a class="btn btn-light btn-sm fw-semibold" href="<?= url('auth/register') ?>">S'inscrire</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero -->
<section class="nl-hero py-5">
    <div class="container py-5 mt-4">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-3">Apprenez à votre rythme. Progressez avec preuves.</h1>
                <p class="lead opacity-90 mb-4">
                    <?= APP_NAME ?> vous propose des cours en PDF et vidéo, des évaluations automatiques
                    pour mesurer votre progression, et des certificats vérifiables pour valoriser vos compétences.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?= url('auth/register') ?>" class="btn btn-light btn-lg fw-semibold"><i class="fa-solid fa-rocket me-2"></i>Commencer gratuitement</a>
                    <a href="#courses" class="btn btn-outline-light btn-lg">Découvrir les cours</a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block text-center">
                <i class="fa-solid fa-laptop-code" style="font-size: 14rem; opacity:.25;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Statistiques rapides -->
<section class="bg-white border-bottom py-4">
    <div class="container">
        <div class="row text-center g-3">
            <div class="col-md-3 col-6">
                <h3 class="fw-bold text-primary mb-0"><?= count($modules) ?></h3>
                <small class="text-muted">Modules de formation</small>
            </div>
            <div class="col-md-3 col-6">
                <h3 class="fw-bold text-primary mb-0"><?= count($featuredCourses) ?>+</h3>
                <small class="text-muted">Cours disponibles</small>
            </div>
            <div class="col-md-3 col-6">
                <h3 class="fw-bold text-primary mb-0">100%</h3>
                <small class="text-muted">Évaluation automatisée</small>
            </div>
            <div class="col-md-3 col-6">
                <h3 class="fw-bold text-primary mb-0"><i class="fa-solid fa-certificate"></i></h3>
                <small class="text-muted">Certificats vérifiables</small>
            </div>
        </div>
    </div>
</section>

<!-- Modules -->
<section id="modules" class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Nos modules de formation</h2>
            <p class="text-muted">Des parcours complets validés par un certificat à la clé.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($modules as $module): ?>
            <div class="col-md-4">
                <div class="nl-card h-100 p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                            <i class="fa-solid fa-layer-group fa-lg"></i>
                        </div>
                        <h5 class="fw-bold mb-0"><?= e($module['title']) ?></h5>
                    </div>
                    <p class="text-muted small"><?= e(truncate($module['description'], 110)) ?></p>
                    <span class="badge bg-light text-dark border"><?= (int)$module['nb_courses'] ?> cours</span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($modules)): ?>
                <p class="text-center text-muted">Aucun module disponible pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Cours -->
<section id="courses" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Cours populaires</h2>
            <p class="text-muted">Inscrivez-vous et commencez à apprendre dès maintenant.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($featuredCourses as $course): ?>
            <div class="col-md-4">
                <div class="nl-card h-100">
                    <div class="nl-course-cover d-flex align-items-center justify-content-center text-white">
                        <i class="fa-solid fa-book fa-2x"></i>
                    </div>
                    <div class="p-3">
                        <span class="badge bg-primary-subtle text-primary mb-2"><?= e($course['module_title']) ?></span>
                        <h6 class="fw-bold"><?= e($course['title']) ?></h6>
                        <p class="text-muted small mb-2"><?= e(truncate($course['description'], 80)) ?></p>
                        <div class="d-flex justify-content-between align-items-center small text-muted">
                            <span><i class="fa-solid fa-list-check me-1"></i><?= (int)$course['nb_lessons'] ?> leçons</span>
                            <span><?= render_stars((float)$course['avg_rating']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($featuredCourses)): ?>
                <p class="text-center text-muted">Aucun cours publié pour le moment.</p>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= url('auth/register') ?>" class="btn btn-primary">Voir tout le catalogue</a>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white py-4">
    <div class="container text-center">
        <p class="mb-1 fw-semibold"><i class="fa-solid fa-graduation-cap me-2"></i><?= APP_NAME ?></p>
        <p class="small opacity-75 mb-0">&copy; <?= date('Y') ?> <?= APP_NAME ?>. Tous droits réservés. Plateforme de gestion de l'apprentissage.</p>
    </div>
</footer>
