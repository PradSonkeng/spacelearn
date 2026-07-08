<nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--nl-primary-dark);">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="<?= url('') ?>">
            <i class="fa-solid fa-graduation-cap me-2"></i><?= APP_NAME ?>
        </a>
        <a href="<?= url('') ?>" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Accueil</a>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="text-center mb-4">
                <i class="fa-solid fa-certificate fa-3x text-primary mb-3"></i>
                <h2 class="fw-bold">Vérification de certificat</h2>
                <p class="text-muted">Entrez le code unique présent sur le certificat pour vérifier son authenticité.</p>
            </div>

            <form method="GET" action="<?= url('home/verify') ?>" class="d-flex gap-2 mb-4">
                <input type="text" name="code" value="<?= e($_GET['code'] ?? '') ?>" class="form-control form-control-lg" placeholder="Ex: CERT-2026-9F3A1C7B2E4D" required>
                <button class="btn btn-primary btn-lg px-4"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>

            <?php
            // Le code peut être transmis via le paramètre GET 'code' ou la route
            $codeToCheck = $_GET['code'] ?? $code ?? '';
            if ($codeToCheck !== '' && !$certificate) {
                require_once APP_PATH . '/models/Certificate.php';
                $certModel = new Certificate();
                $certificate = $certModel->verify($codeToCheck);
            }
            ?>

            <?php if ($codeToCheck !== ''): ?>
                <?php if ($certificate): ?>
                    <div class="nl-card p-4 border-success">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="fa-solid fa-circle-check fa-2x text-success"></i>
                            <div>
                                <h5 class="fw-bold mb-0">Certificat authentique</h5>
                                <small class="text-muted">Ce certificat a bien été délivré par <?= APP_NAME ?></small>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><strong>Titulaire :</strong> <?= e($certificate['student_name']) ?></li>
                            <li class="mb-2"><strong>Module validé :</strong> <?= e($certificate['module_title']) ?></li>
                            <li class="mb-2"><strong>Date de délivrance :</strong> <?= format_date($certificate['issued_at']) ?></li>
                            <li><strong>Code :</strong> <code><?= e($certificate['code']) ?></code></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="nl-card p-4 border-danger">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-circle-xmark fa-2x text-danger"></i>
                            <div>
                                <h5 class="fw-bold mb-0">Certificat introuvable</h5>
                                <small class="text-muted">Aucun certificat ne correspond au code <code><?= e($codeToCheck) ?></code>.</small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
