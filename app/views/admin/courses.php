<div class="mb-4">
    <h3 class="fw-bold mb-0">Tous les cours</h3>
    <p class="text-muted mb-0">Vue d'ensemble des cours créés par les enseignants.</p>
</div>

<div class="nl-card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Cours</th>
                    <th>Module</th>
                    <th>Enseignant</th>
                    <th class="text-center">Leçons</th>
                    <th class="text-center">Étudiants</th>
                    <th>Statut</th>
                    <th>Créé le</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                <tr>
                    <td class="fw-semibold"><?= e($course['title']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= e($course['module_title']) ?></span></td>
                    <td><?= e($course['teacher_name']) ?></td>
                    <td class="text-center"><?= (int)$course['nb_lessons'] ?></td>
                    <td class="text-center"><?= (int)$course['nb_students'] ?></td>
                    <td><?= status_badge($course['status']) ?></td>
                    <td class="text-muted small"><?= format_date($course['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($courses)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Aucun cours pour le moment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
