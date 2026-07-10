<div class="nl-auth-wrapper">
    <div class="nl-auth-card">
        <div class="row g-0">
            <div class="col-lg-5 nl-auth-side d-none d-lg-flex">
                <i class="fa-solid fa-graduation-cap fa-3x mb-4"></i>
                <h2 class="fw-bold">Bienvenue sur <?= APP_NAME ?></h2>
                <p class="opacity-90">La plateforme qui transforme votre apprentissage en réussite concrète : cours, évaluations, suivi de progression et certificats reconnus.</p>
                <ul class="list-unstyled mt-4">
                    <li class="mb-2"><i class="fa-solid fa-circle-check me-2"></i>Cours en PDF & vidéo</li>
                    <li class="mb-2"><i class="fa-solid fa-circle-check me-2"></i>Évaluations automatiques</li>
                    <li class="mb-2"><i class="fa-solid fa-circle-check me-2"></i>Certificats vérifiables</li>
                </ul>
            </div>
            <div class="col-lg-7 p-4 p-md-5">
                <div class="d-lg-none text-center mb-4">
                    <i class="fa-solid fa-graduation-cap fa-2x text-primary"></i>
                    <h4 class="fw-bold mt-2"><?= APP_NAME ?></h4>
                </div>
                <h3 class="fw-bold mb-1">Connexion</h3>
                <p class="text-muted mb-4">Accédez à votre espace personnel.</p>

                <?php display_flash(); ?>

                <form action="<?= url('auth/doLogin') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Adresse email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="vous@exemple.com" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" id="login-password" class="form-control" placeholder="••••••••" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggle-login-password">
                            		<i class="fa-solid fa-eye" id="login-eye"></i>
                            	</button>
                        </div>
                    </div>
                    <div class="text-end mb-3">
    						<a href="<?= url('auth/forgotPassword') ?>" class="text-muted small">Mot de passe oublié ?</a>
					</div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Se connecter
                    </button>
                </form>
                
                <p class="text-center text-muted mt-3">
    					Vous n'avez pas reçu l'email de vérification ? 
    					<a href="<?= url('auth/resendVerification') ?>" class="fw-semibold">Renvoyer le lien</a>
				</p>

                <p class="text-center text-muted mt-4 mb-0">
                    Pas encore de compte ? <a href="<?= url('auth/register') ?>" class="fw-semibold">Créer un compte</a>
                </p>
                <hr>
                <p class="text-center text-muted small mb-0">
                    Comptes de démonstration (mot de passe : <code>password123</code>)<br>
                    Promoteur : admin@lms.test &nbsp;|&nbsp; Enseignant : enseignant@lms.test &nbsp;|&nbsp; Étudiant : etudiant@lms.test
                </p>
            </div>
        </div>
    </div>
    <script>
	document.getElementById('toggle-login-password').addEventListener('click', function () {
    		const pwd = document.getElementById('login-password');
    		const icon = document.getElementById('login-eye');
    		if (pwd.type === 'password') {
        		pwd.type = 'text';
        		icon.classList.replace('fa-eye', 'fa-eye-slash');
    		} else {
        		pwd.type = 'password';
        		icon.classList.replace('fa-eye-slash', 'fa-eye');
    		}
	});
	</script>
</div>
