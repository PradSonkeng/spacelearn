<?php
// Détermine la leçon précédente / suivante
$currentIndex = null;
foreach ($allLessons as $i => $l) {
    if ($l['id'] == $lesson['id']) { $currentIndex = $i; break; }
}
$prevLesson = $currentIndex !== null && $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
$nextLesson = $currentIndex !== null && $currentIndex < count($allLessons) - 1 ? $allLessons[$currentIndex + 1] : null;

$isCompleted = $progress && $progress['status'] === 'termine';
?>

<div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= url('student/course/' . $lesson['course_id']) ?>" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i></a>
    <div class="flex-grow-1">
        <h4 class="fw-bold mb-0"><?= e($lesson['title']) ?></h4>
        <p class="text-muted mb-0 small"><?= e($lesson['course_title']) ?></p>
    </div>
    <?php if ($isCompleted): ?>
        <span class="badge bg-success fs-6"><i class="fa-solid fa-circle-check me-1"></i>Terminée</span>
    <?php else: ?>
        <span class="badge bg-warning text-dark fs-6"><i class="fa-solid fa-spinner me-1"></i>En cours</span>
    <?php endif; ?>
</div>

<div class="row g-4">
    <div class="col-lg-9">
        <div class="nl-lesson-viewer mb-3">
            <?php if ($lesson['type'] === 'pdf'): ?>
                <iframe src="<?= upload($lesson['file_path']) ?>" title="<?= e($lesson['title']) ?>"></iframe>
            <?php else: ?>
                <video controls preload="metadata" src="<?= upload($lesson['file_path']) ?>"></video>
            <?php endif; ?>
        </div>

        <?php if (!empty($lesson['description'])): ?>
        <div class="nl-card p-3 mb-3">
            <h6 class="fw-bold">Description</h6>
            <p class="text-muted small mb-0"><?= nl2br(e($lesson['description'])) ?></p>
        </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between">
            <?php if ($prevLesson): ?>
                <a href="<?= url('student/lesson/' . $prevLesson['id']) ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i><?= e(truncate($prevLesson['title'], 25)) ?></a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>

            <?php if ($nextLesson || $lesson['evaluation_id']): ?>
                <?php
                	$btnClass = $hasViewedContent ? 'btn-primary' : 'btn-secondary disabled';
        			$btnText  = $lesson['evaluation_id'] 
            			? 'Passer l\'évaluation' 
            			: e(truncate($nextLesson['title'] ?? '', 25));
        			$btnLink  = $lesson['evaluation_id'] 
            			? url('student/evaluation/' . $lesson['id']) 
            			: url('student/lesson/' . $nextLesson['id']);
                ?>
                
                <a href="<?= $btnLink ?>" 
           			id="evalNextBtn"
           			class="btn <?= $btnClass ?>"
           			<?= $hasViewedContent ? '' : 'onclick="return false;"' ?>>
            			<?= $btnText ?>
            			<i class="fa-solid fa-arrow-right ms-2"></i>
        			</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="nl-card p-3">
            <h6 class="fw-bold mb-3">Programme</h6>
            <ul class="list-unstyled mb-0 small">
                <?php foreach ($allLessons as $l): ?>
                <li class="mb-2">
                    <a href="<?= url('student/lesson/' . $l['id']) ?>" class="text-decoration-none d-flex align-items-center gap-2 <?= $l['id'] == $lesson['id'] ? 'fw-bold text-primary' : 'text-body' ?>">
                        <i class="fa-solid fa-<?= $l['type'] === 'pdf' ? 'file-pdf' : 'circle-play' ?>"></i>
                        <?= e($l['title']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php if ($lesson['evaluation_id']): ?>
        <div class="nl-card p-3 mt-3">
            <h6 class="fw-bold mb-2"><i class="fa-solid fa-clipboard-question text-primary me-2"></i>Évaluation</h6>
            <p class="small text-muted mb-2">Note de passage : <strong><?= (int)$lesson['passing_score'] ?>%</strong></p>
            <?php if ($bestScore !== null): ?>
                <p class="small mb-2">Votre meilleur score : <strong><?= number_format($bestScore, 0) ?>%</strong></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const evalBtn = document.getElementById('evalNextBtn');
    if (!evalBtn) return;

    let contentCompleted = <?= $hasViewedContent ? 'true' : 'false' ?>;

    // === VIDÉO ===
    const video = document.querySelector('video');
    if (video) {
        video.addEventListener('timeupdate', function () {
            if (!contentCompleted && video.duration && (video.currentTime / video.duration) >= 0.95) {
                markContentCompleted();
            }
        });
    }

    // === PDF (iframe) - détection scroll + temps minimum ===
    const iframe = document.querySelector('iframe');
    if (iframe) {
        let scrollTimeout;
        iframe.addEventListener('load', function () {
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                iframeDoc.addEventListener('scroll', function () {
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(() => {
                        if (iframeDoc.documentElement.scrollTop + iframeDoc.documentElement.clientHeight >= 
                            iframeDoc.documentElement.scrollHeight * 0.95) {
                            markContentCompleted();
                        }
                    }, 800);
                });
            } catch(e) {}
        });

        // Fallback : après 45 secondes de visualisation
        setTimeout(() => {
            if (!contentCompleted) markContentCompleted();
        }, 45000);
    }

    function markContentCompleted() {
        if (contentCompleted) return;
        contentCompleted = true;

        fetch('<?= url("student/markContentViewed/" . $lesson['id']) ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ csrf_token: '<?= csrf_token() ?>' })
        });

        // Activer le bouton
        evalBtn.classList.remove('btn-secondary', 'disabled');
        evalBtn.classList.add('btn-primary');
        evalBtn.onclick = null;
    }
});
</script>
