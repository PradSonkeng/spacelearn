<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= url('admin/modules') ?>" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <h3 class="fw-bold mb-0"><?= $module ? 'Modifier le module' : 'Nouveau module' ?></h3>
        <p class="text-muted mb-0">Un module regroupe plusieurs cours et permet la délivrance d'un certificat.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="nl-card p-4">
            <form method="POST" action="<?= url('admin/moduleSave') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $module['id'] ?? 0 ?>">

                <div class="mb-3">
                    <label class="form-label">Titre du module <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?= e($module['title'] ?? '') ?>" placeholder="Ex: Développement Web Fullstack" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Décrivez les objectifs et le contenu de ce module..."><?= e($module['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Image d'illustration</label>
                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    <?php if (!empty($module['image'])): ?>
                        <small class="text-muted">Image actuelle : <?= e($module['image']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Enregistrer</button>
                    <a href="<?= url('admin/modules') ?>" class="btn btn-light">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
