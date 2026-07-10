<div class="nl-auth-wrapper">
    <div class="nl-auth-card">
        <div class="row g-0">
            <div class="col-lg-7 p-4 p-md-5 mx-auto" style="max-width: 480px;">
                <h3 class="fw-bold mb-1 text-center">Mot de passe oublié</h3>
                <p class="text-muted text-center mb-4">Entrez votre email pour recevoir un lien de réinitialisation.</p>

                <?php display_flash(); ?>

                <form action="<?= url('auth/sendResetLink') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Adresse email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Envoyer le lien</button>
                </form>

                <p class="text-center mt-4">
                    <a href="<?= url('auth/login') ?>">Retour à la connexion</a>
                </p>
            </div>
        </div>
    </div>
</div>
