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
            <div class="input-group">
                <input type="password" name="current_password" id="current-password" class="form-control" required>
                <button class="btn btn-outline-secondary" type="button" id="toggle-current">
                    <i class="fa-solid fa-eye" id="eye-current"></i>
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nouveau mot de passe</label>
                <div class="input-group">
                    <input type="password" name="new_password" id="new-password" class="form-control" minlength="8" required>
                    <button class="btn btn-outline-secondary" type="button" id="toggle-new">
                        <i class="fa-solid fa-eye" id="eye-new"></i>
                    </button>
                </div>
                <div id="password-strength" class="mt-2"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Confirmer le nouveau mot de passe</label>
                <div class="input-group">
                    <input type="password" name="new_password_confirm" id="confirm-password" class="form-control" minlength="8" required>
                    <button class="btn btn-outline-secondary" type="button" id="toggle-confirm">
                        <i class="fa-solid fa-eye" id="eye-confirm"></i>
                    </button>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-outline-primary" id="submit-btn">
            <i class="fa-solid fa-key me-2"></i>Changer le mot de passe
        </button>
    </form>
</div>

<script>
// Toggle œil pour les 3 champs
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Force du mot de passe
function checkPasswordStrength(password) {
    let score = 0;
    const feedback = document.getElementById('password-strength');

    if (password.length >= 8) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) score++;

    let text = 'Très faible';
    let color = 'danger';
    let isStrong = false;

    if (score === 5) {
        text = 'Fort ✓';
        color = 'success';
        isStrong = true;
    } else if (score === 4) {
        text = 'Moyen';
        color = 'info';
    } else if (score === 3) {
        text = 'Faible';
        color = 'warning';
    }

    feedback.innerHTML = `
        <div class="progress mt-1" style="height: 8px;">
            <div class="progress-bar bg-${color}" style="width: ${score * 20}%"></div>
        </div>
        <small class="text-${color} fw-semibold d-block mt-1">${text}</small>
    `;

    // Désactiver le bouton si pas assez fort
    document.getElementById('submit-btn').disabled = !isStrong;
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Toggle des 3 champs
    document.getElementById('toggle-current').addEventListener('click', () => togglePassword('current-password', 'eye-current'));
    document.getElementById('toggle-new').addEventListener('click', () => togglePassword('new-password', 'eye-new'));
    document.getElementById('toggle-confirm').addEventListener('click', () => togglePassword('confirm-password', 'eye-confirm'));

    // Jauge sur nouveau mot de passe
    const newPwd = document.getElementById('new-password');
    if (newPwd) {
        newPwd.addEventListener('input', () => checkPasswordStrength(newPwd.value));
    }
});
</script>
</div>
