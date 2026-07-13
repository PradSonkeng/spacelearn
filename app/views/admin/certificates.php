<div class="mb-4">
    <h3 class="fw-bold mb-0">Certificats délivrés</h3>
    <p class="text-muted mb-0">Historique des certificats attribués automatiquement aux étudiants ayant validé un module.</p>
</div>

<div class="nl-card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Étudiant</th>
                    <th>Module validé</th>
                    <th>Date de délivrance</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($certificates as $cert): ?>
                <tr>
                    <td><code><?= e($cert['code']) ?></code></td>
                    <td>
                        <div class="fw-semibold"><?= e($cert['student_name']) ?></div>
                        <div class="text-muted small"><?= e($cert['student_email']) ?></div>
                    </td>
                    <td><?= e($cert['course_title']) ?></td>
                    <td class="text-muted small"><?= format_date($cert['issued_at']) ?></td>
                    <td class="text-end">
                        <a href="<?= url('certificate/show/' . $cert['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-eye me-1"></i>Voir
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($certificates)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Aucun certificat délivré pour le moment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
