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

                <div class="mb-3">
    					<label class="form-label">Type de contenu</label>
    					<select name="type" class="form-select" id="lessonType" onchange="toggleLessonType()">
        					<option value="pdf">Document PDF (upload)</option>
        					<option value="video">Vidéo (upload)</option>
        					<option value="external">Lien externe (YouTube, PDF en ligne...)</option>
    					</select>
				</div>
				
				<div class="mb-3" id="externalGroup" style="display: <?= !empty($lesson['is_external']) ? 'block' : 'none' ?>;">
                    <label class="form-label">URL externe <span class="text-danger">*</span></label>
                    <input type="url" name="external_url" class="form-control" 
                           value="<?= e($lesson['external_url'] ?? '') ?>" 
                           placeholder="https://www.youtube.com/watch?v=... ou lien PDF public">
                    <small class="text-muted">YouTube, Vimeo, Google Drive, PDF hébergé en ligne, etc.</small>
                </div>
                
                <div class="mb-3" id="fileGroup" style="display: <?= empty($lesson['is_external']) ? 'block' : 'none' ?>;">
                    <label class="form-label">Fichier <?= $lesson ? '' : '<span class="text-danger">*</span>' ?></label>
                    <input type="file" name="file" class="form-control">
                    
                    <?php if (!empty($lesson['file_path'])): ?>
                        <div class="mt-2 small">
                            <span class="text-muted">Fichier actuel :</span> 
                            <a href="<?= upload($lesson['file_path']) ?>" target="_blank" class="text-decoration-underline">
                                <?= e(basename($lesson['file_path'])) ?>
                            </a>
                            <br><small class="text-muted">Laissez vide pour conserver le fichier actuel.</small>
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
    <script>
function toggleLessonType() {
    const type = document.getElementById('lessonType').value;
    const externalGroup = document.getElementById('externalGroup');
    const fileGroup = document.getElementById('fileGroup');

    if (type === 'external') {
        externalGroup.style.display = 'block';
        fileGroup.style.display = 'none';
    } else {
        externalGroup.style.display = 'none';
        fileGroup.style.display = 'block';
    }
}

// Initialisation si on édite une leçon externe
document.addEventListener('DOMContentLoaded', function() {
    toggleLessonType();
});
</script>
</div>

