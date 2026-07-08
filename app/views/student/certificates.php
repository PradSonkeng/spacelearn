<div class="mb-4">
    <h3 class="fw-bold mb-0">Mes certificats</h3>
    <p class="text-muted mb-0">Téléchargez et partagez vos certificats de validation de modules.</p>
</div>

<div class="row g-4">
    <?php foreach ($certificates as $cert): ?>
    <div class="col-md-6 col-lg-4">
        <div class="nl-card h-100 p-4 text-center">
            <i class="fa-solid fa-certificate fa-3x text-primary mb-3"></i>
            <h6 class="fw-bold"><?= e($cert['module_title']) ?></h6>
            <p class="text-muted small mb-2">Délivré le <?= format_date($cert['issued_at']) ?></p>
            <p class="mb-3"><code class="small"><?= e($cert['code']) ?></code></p>
            <a href="<?= url('certificate/show/' . $cert['id']) ?>" target="_blank" class="btn btn-primary btn-sm w-100">
                <i class="fa-solid fa-eye me-1"></i>Voir / Imprimer le certificat
            </a>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($certificates)): ?>
        <div class="col-12">
            <div class="nl-card p-5 text-center">
                <i class="fa-solid fa-certificate fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-2">Vous n'avez encore obtenu aucun certificat.</p>
                <p class="text-muted small mb-3">Validez 100% de tous les cours d'un module pour obtenir automatiquement votre certificat.</p>
                <a href="<?= url('student/catalog') ?>" class="btn btn-primary">Parcourir les cours</a>
            </div>
        </div>
    <?php endif; ?>
</div>
