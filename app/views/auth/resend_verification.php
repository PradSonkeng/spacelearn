<div class="nl-auth-wrapper">
    <div class="nl-auth-card">
        <div class="row g-0">
            <div class="col-lg-7 p-5 mx-auto" style="max-width: 500px;">
                <h3 class="fw-bold text-center">Renvoyer le lien de vérification</h3>
                <p class="text-muted text-center">Entrez votre email pour recevoir un nouveau lien.</p>

                <?php display_flash(); ?>

                <form action="<?= url('auth/resendVerification') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="votre@email.com" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Renvoyer le lien</button>
                </form>

                <p class="text-center mt-4">
                    <a href="<?= url('auth/login') ?>">Retour à la connexion</a>
                </p>
            </div>
        </div>
    </div>
</div>
