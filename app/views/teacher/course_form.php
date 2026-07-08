<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= url('teacher/courses') ?>" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <h3 class="fw-bold mb-0"><?= $course ? 'Modifier le cours' : 'Nouveau cours' ?></h3>
        <p class="text-muted mb-0">Renseignez les informations générales de votre cours.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="nl-card p-4">
            <form method="POST" action="<?= url('teacher/courseSave') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $course['id'] ?? 0 ?>">

                <div class="mb-3">
                    <label class="form-label">Titre du cours <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?= e($course['title'] ?? '') ?>" placeholder="Ex: Introduction au HTML & CSS" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Module associé <span class="text-danger">*</span></label>
                    <select name="module_id" class="form-select" required>
                        <option value="">Sélectionner un module...</option>
                        <?php foreach ($modules as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= (isset($course['module_id']) && $course['module_id'] == $m['id']) ? 'selected' : '' ?>>
                                <?= e($m['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($modules)): ?>
                        <small class="text-danger">Aucun module n'a encore été créé par le promoteur. Contactez-le avant de publier un cours.</small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Décrivez le contenu et les objectifs de ce cours..."><?= e($course['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Image de couverture</label>
                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>

                <div class="mb-4">
                    <label class="form-label d-block">Statut</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="status" id="statusDraft" value="brouillon" <?= (!isset($course['status']) || $course['status'] === 'brouillon') ? 'checked' : '' ?>>
                            <label class="btn btn-outline-secondary w-100" for="statusDraft"><i class="fa-solid fa-pen-to-square me-2"></i>Brouillon</label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="status" id="statusPublished" value="publie" <?= (isset($course['status']) && $course['status'] === 'publie') ? 'checked' : '' ?>>
                            <label class="btn btn-outline-success w-100" for="statusPublished"><i class="fa-solid fa-circle-check me-2"></i>Publié</label>
                        </div>
                    </div>
                    <small class="text-muted">Seuls les cours publiés sont visibles par les étudiants dans le catalogue.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Enregistrer et continuer</button>
                    <a href="<?= url('teacher/courses') ?>" class="btn btn-light">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
