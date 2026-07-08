<div class="row">
    <div class="col-12 mb-4">
        <h3 class="fw-bold mb-0">Mon profil</h3>
        <p class="text-muted">Gérez vos informations personnelles et votre sécurité.</p>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="nl-card p-4 text-center">
            <img src="<?= upload('avatars/' . $user['avatar']) ?>" onerror="this.src='<?= asset('img/default-avatar.svg') ?>'" class="rounded-circle mb-3" width="110" height="110" style="object-fit:cover;" alt="avatar">
            <h5 class="fw-bold mb-0"><?= e($user['full_name']) ?></h5>
            <span class="badge bg-primary-subtle text-primary"><?= role_label($user['role']) ?></span>
            <p class="text-muted small mt-3 mb-0"><?= e($user['email']) ?></p>
            <p class="text-muted small">Membre depuis le <?= format_date($user['created_at']) ?></p>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="nl-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-id-card me-2 text-primary"></i>Informations générales</h5>
            <form method="POST" action="<?= url('profile/update') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Nom complet</label>
                    <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                    <small class="text-muted">L'adresse email ne peut pas être modifiée.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="3" placeholder="Parlez un peu de vous..."><?= e($user['bio']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Photo de profil</label>
                    <input type="file" name="avatar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Enregistrer</button>
            </form>
        </div>

        <div class="nl-card p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-shield-halved me-2 text-primary"></i>Sécurité</h5>
            <form method="POST" action="<?= url('profile/changePassword') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Mot de passe actuel</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="new_password" class="form-control" minlength="6" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirmer</label>
                        <input type="password" name="new_password_confirm" class="form-control" minlength="6" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-outline-primary"><i class="fa-solid fa-key me-2"></i>Changer le mot de passe</button>
            </form>
        </div>
    </div>
</div>
