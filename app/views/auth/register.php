<div class="nl-auth-wrapper">
    <div class="nl-auth-card">
        <div class="row g-0">
            <div class="col-lg-5 nl-auth-side d-none d-lg-flex">
                <i class="fa-solid fa-graduation-cap fa-3x mb-4"></i>
                <h2 class="fw-bold">Rejoignez <?= APP_NAME ?></h2>
                <p class="opacity-90">Créez votre compte et commencez dès aujourd'hui : suivez des cours en tant qu'étudiant, ou partagez votre savoir en tant qu'enseignant.</p>
            </div>
            <div class="col-lg-7 p-4 p-md-5">
                <div class="d-lg-none text-center mb-4">
                    <i class="fa-solid fa-graduation-cap fa-2x text-primary"></i>
                    <h4 class="fw-bold mt-2"><?= APP_NAME ?></h4>
                </div>
                <h3 class="fw-bold mb-1">Créer un compte</h3>
                <p class="text-muted mb-4">Quelques informations pour commencer.</p>

                <?php display_flash(); ?>

                <form action="<?= url('auth/doRegister') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Nom complet</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <input type="text" name="full_name" class="form-control" placeholder="Votre nom complet" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adresse email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="vous@exemple.com" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="6 caractères min." required minlength="6">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirmer</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password_confirm" class="form-control" placeholder="••••••••" required minlength="6">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block">Je m'inscris en tant que :</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="role" id="roleStudent" value="etudiant" checked>
                                <label class="btn btn-outline-primary w-100" for="roleStudent">
                                    <i class="fa-solid fa-user-graduate me-2"></i>Étudiant
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="role" id="roleTeacher" value="enseignant">
                                <label class="btn btn-outline-primary w-100" for="roleTeacher">
                                    <i class="fa-solid fa-chalkboard-user me-2"></i>Enseignant
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-user-plus me-2"></i>Créer mon compte
                    </button>
                </form>

                <p class="text-center text-muted mt-4 mb-0">
                    Vous avez déjà un compte ? <a href="<?= url('auth/login') ?>" class="fw-semibold">Se connecter</a>
                </p>
            </div>
        </div>
    </div>
</div>
