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
                        <div id="email-feedback" class="mt-1 small"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" id="register-password" class="form-control" placeholder="8 caractères min." required minlength="8">
                                <button class="btn btn-outline-secondary" type="button" id="toggle-register-password">
                						<i class="fa-solid fa-eye" id="register-eye"></i>
            						</button>
                            </div>
                            <div id="password-strength"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirmer</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password_confirm" id="register-confirm" class="form-control" placeholder="••••••••" required minlength="8">
                                <button class="btn btn-outline-secondary" type="button" id="toggle-register-confirm">
                						<i class="fa-solid fa-eye" id="confirm-eye"></i>
            						</button>
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
    <script>
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
	

	document.getElementById('toggle-register-password').addEventListener('click', () => togglePassword('register-password', 'register-eye'));
	document.getElementById('toggle-register-confirm').addEventListener('click', () => togglePassword('register-confirm', 'confirm-eye'));
	</script>
	<script>
	const emailInput = document.getElementById('email');
	const feedback = document.getElementById('email-feedback');
	
	emailInput.addEventListener('blur', function() {
    		const email = this.value.trim();
    		if (email.length < 5) return;

    		fetch('<?= url("auth/checkEmail") ?>', {
        		method: 'POST',
        		headers: {'Content-Type': 'application/json'},
        		body: JSON.stringify({email: email, csrf_token: '<?= csrf_token() ?>'})
   		})
    		.then(r => r.json())
    		.then(data => {
        		if (!data.valid) {
            		feedback.innerHTML = `<span class="text-danger">${data.message}</span>`;
            		emailInput.classList.add('is-invalid');
        		} else if (data.exists) {
            		feedback.innerHTML = `<span class="text-danger">${data.message}</span>`;
            		emailInput.classList.add('is-invalid');
        		} else {
            		feedback.innerHTML = `<span class="text-success">${data.message}</span>`;
            		emailInput.classList.remove('is-invalid');
        		}
    		})
    		.catch(() => {
        		feedback.innerHTML = '<span class="text-warning">Vérification temporairement indisponible.</span>';
    		});
	});
	</script>
	<script>
	// Force du mot de passe
	function checkPasswordStrength(password) {
    		let score = 0;
    		const feedback = document.getElementById('password-strength');

    		if (password.length >= 8) score++;
    		if (/[a-z]/.test(password)) score++;
    		if (/[A-Z]/.test(password)) score++;
    		if (/\d/.test(password)) score++;
    		if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) score++;

    		let strengthText = '';
    		let color = '';
    		let isStrong = false;

    		if (score === 5) {
        		strengthText = 'Fort ✓';
        		color = 'success';
        		isStrong = true;
    		} else if (score === 4) {
        		strengthText = 'Moyen';
        		color = 'info';
    		} else if (score === 3) {
        		strengthText = 'Faible';
        		color = 'warning';
    		} else {
        		strengthText = 'Très faible';
        		color = 'danger';
    		}

    		feedback.innerHTML = `
        		<div class="progress mt-1" style="height: 6px;">
            		<div class="progress-bar bg-${color}" style="width: ${score * 20}%"></div>
        		</div>
        		<small class="text-${color}">${strengthText}</small>
    		`;

    		return isStrong;
	}

	// Application sur le champ mot de passe
	document.addEventListener('DOMContentLoaded', function() {
   		 const pwdInput = document.querySelector('input[name="password"]');
    		if (!pwdInput) return;

    		const strengthDiv = document.createElement('div');
    		strengthDiv.id = 'password-strength';
    		pwdInput.parentElement.appendChild(strengthDiv);

    		pwdInput.addEventListener('input', function() {
        		checkPasswordStrength(this.value);
        		
        		// Désactive le bouton de soumission si pas assez fort
        		const form = this.form;
        		const submitBtn = form.querySelector('button[type="submit"]');
        		if (submitBtn) {
            		submitBtn.disabled = !isStrong;
        		}
    		});
	});
</script>
</div>
