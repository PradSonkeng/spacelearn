<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= url('teacher/courseManage/' . $course['id']) ?>" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <h3 class="fw-bold mb-0"><?= $lesson ? 'Modifier la leçon' : 'Nouvelle leçon' ?></h3>
        <p class="text-muted mb-0">Cours : <?= e($course['title']) ?></p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="nl-card p-4">
            <form method="POST" action="<?= url('teacher/lessonSave') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $lesson['id'] ?? 0 ?>">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">

                <div class="mb-3">
                    <label class="form-label">Titre de la leçon <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?= e($lesson['title'] ?? '') ?>" placeholder="Ex: Les balises HTML essentielles" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Résumé du contenu de cette leçon..."><?= e($lesson['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label d-block">Type de contenu <span class="text-danger">*</span></label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="type" id="typePdf" value="pdf" <?= (!isset($lesson['type']) || $lesson['type'] === 'pdf') ? 'checked' : '' ?> onchange="toggleFileHint()">
                            <label class="btn btn-outline-primary w-100" for="typePdf"><i class="fa-solid fa-file-pdf me-2"></i>Document PDF</label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="type" id="typeVideo" value="video" <?= (isset($lesson['type']) && $lesson['type'] === 'video') ? 'checked' : '' ?> onchange="toggleFileHint()">
                            <label class="btn btn-outline-primary w-100" for="typeVideo"><i class="fa-solid fa-circle-play me-2"></i>Vidéo</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Fichier <?= $lesson ? '' : '<span class="text-danger">*</span>' ?></label>
                    <input type="file" name="file" class="form-control" <?= $lesson ? '' : 'required' ?>>
                    <small class="text-muted" id="fileHint">PDF : 50 Mo max.</small>
                    <?php if (!empty($lesson['file_path'])): ?>
                        <div class="mt-2">
                            <span class="text-muted small">Fichier actuel :</span>
                            <a href="<?= upload($lesson['file_path']) ?>" target="_blank"><?= e(basename($lesson['file_path'])) ?></a>
                            <br><small class="text-muted">Laissez ce champ vide pour conserver le fichier actuel.</small>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Enregistrer</button>
                    <a href="<?= url('teacher/courseManage/' . $course['id']) ?>" class="btn btn-light">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$inlineJs = "
function toggleFileHint() {
    const isVideo = document.getElementById('typeVideo').checked;
    document.getElementById('fileHint').textContent = isVideo
        ? 'Vidéo : formats mp4, webm, ogg — 300 Mo max.'
        : 'PDF : 50 Mo max.';
}
toggleFileHint();
";
?>
