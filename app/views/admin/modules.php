<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Modules de formation</h3>
        <p class="text-muted mb-0">Organisez les cours en parcours certifiants.</p>
    </div>
    <a href="<?= url('admin/moduleForm') ?>" class="btn btn-primary"><i class="fa-solid fa-circle-plus me-2"></i>Nouveau module</a>
</div>

<div class="row g-4">
    <?php foreach ($modules as $module): ?>
    <div class="col-md-6 col-lg-4">
        <div class="nl-card h-100 p-4">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                    <i class="fa-solid fa-layer-group fa-lg"></i>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= url('admin/moduleForm/' . $module['id']) ?>"><i class="fa-solid fa-pen me-2"></i>Modifier</a></li>
                        <li>
                            <form method="POST" action="<?= url('admin/moduleDelete/' . $module['id']) ?>" onsubmit="return confirm('Supprimer ce module ainsi que tous ses cours associés ?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-trash me-2"></i>Supprimer</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            <h5 class="fw-bold"><?= e($module['title']) ?></h5>
            <p class="text-muted small"><?= e(truncate($module['description'], 120)) ?></p>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="badge bg-light text-dark border"><?= (int)$module['nb_courses'] ?> cours</span>
                <small class="text-muted">Créé le <?= format_date($module['created_at']) ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($modules)): ?>
        <div class="col-12">
            <div class="nl-card p-5 text-center">
                <i class="fa-solid fa-layer-group fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Aucun module pour le moment. Créez votre premier module pour commencer.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
