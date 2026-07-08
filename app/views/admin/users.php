<div class="mb-4">
    <h3 class="fw-bold mb-0">Utilisateurs</h3>
    <p class="text-muted mb-0">Gérez les comptes enseignants et étudiants de la plateforme.</p>
</div>

<div class="nl-card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Utilisateur</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Inscrit le</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="d-flex align-items-center gap-2">
                        <img src="<?= upload('avatars/' . $u['avatar']) ?>" onerror="this.src='<?= asset('img/default-avatar.svg') ?>'" class="rounded-circle" width="32" height="32" alt="">
                        <span class="fw-semibold"><?= e($u['full_name']) ?></span>
                    </td>
                    <td><?= e($u['email']) ?></td>
                    <td>
                        <?php
                        $roleColors = ['promoteur' => 'danger', 'enseignant' => 'primary', 'etudiant' => 'success'];
                        ?>
                        <span class="badge bg-<?= $roleColors[$u['role']] ?>-subtle text-<?= $roleColors[$u['role']] ?>"><?= role_label($u['role']) ?></span>
                    </td>
                    <td>
                        <?= $u['status'] === 'actif'
                            ? '<span class="badge bg-success">Actif</span>'
                            : '<span class="badge bg-secondary">Inactif</span>' ?>
                    </td>
                    <td class="text-muted small"><?= format_date($u['created_at']) ?></td>
                    <td class="text-end">
                        <?php if ((int)$u['id'] !== current_user_id()): ?>
                        <form method="POST" action="<?= url('admin/userToggleStatus/' . $u['id']) ?>" onsubmit="return confirm('Confirmer le changement de statut de ce compte ?');">
                            <?= csrf_field() ?>
                            <?php if ($u['status'] === 'actif'): ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-user-slash me-1"></i>Désactiver</button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-success"><i class="fa-solid fa-user-check me-1"></i>Activer</button>
                            <?php endif; ?>
                        </form>
                        <?php else: ?>
                            <span class="text-muted small">Vous</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
