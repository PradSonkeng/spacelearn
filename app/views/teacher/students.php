<div class="mb-4">
    <h3 class="fw-bold mb-0">Étudiants</h3>
    <p class="text-muted mb-0">Suivi de la progression des étudiants inscrits à vos cours.</p>
</div>

<div class="nl-card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Étudiant</th>
                    <th>Cours</th>
                    <th>Progression</th>
                    <th>Inscrit le</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $s): ?>
                <tr>
                    <td class="d-flex align-items-center gap-2">
                        <img src="<?= upload('avatars/' . $s['avatar']) ?>" onerror="this.src='<?= asset('img/default-avatar.svg') ?>'" class="rounded-circle" width="32" height="32" alt="">
                        <div>
                            <div class="fw-semibold"><?= e($s['full_name']) ?></div>
                            <div class="text-muted small"><?= e($s['email']) ?></div>
                        </div>
                    </td>
                    <td><?= e($s['course_title']) ?></td>
                    <td style="min-width:160px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px;">
                                <div class="progress-bar bg-primary" style="width: <?= $s['progress_percent'] ?>%"></div>
                            </div>
                            <small class="text-muted"><?= number_format((float)$s['progress_percent'], 0) ?>%</small>
                        </div>
                    </td>
                    <td class="text-muted small"><?= format_date($s['enrolled_at']) ?></td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($students)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">Aucun étudiant inscrit pour le moment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
