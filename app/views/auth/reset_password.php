<div class="nl-auth-wrapper">
    <div class="nl-auth-card">
        <div class="row g-0">
            <div class="col-lg-7 p-4 p-md-5 mx-auto" style="max-width: 480px;">
                <h3 class="fw-bold mb-1 text-center">Réinitialisation du mot de passe</h3>

                <?php display_flash(); ?>

                <form action="<?= url('auth/doResetPassword') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= e($_GET['token'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label">Nouveau mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" id="register-password" class="form-control" placeholder="6 caractères min." required minlength="6">
                            <button class="btn btn-outline-secondary" type="button" id="toggle-register-password">
                					<i class="fa-solid fa-eye" id="register-eye"></i>
            					</button>
                        </div>
                        <div id="password-strength"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmer le mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password_confirm" id="register-confirm" class="form-control" placeholder="••••••••" required minlength="6">
                            <button class="btn btn-outline-secondary" type="button" id="toggle-register-confirm">
                					<i class="fa-solid fa-eye" id="confirm-eye"></i>
            					</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Changer le mot de passe</button>
                </form>
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
        		<div class="progress mt-1" style="height: 8px;">
            		<div class="progress-bar bg-${color}" style="width: ${score * 20}%"></div>
        		</div>
        		<small class="text-${color} fw-semibold d-block mt-1">${strengthText}</small>
    		`;

    		return isStrong;
	}

	// Application sur le champ mot de passe
	document.addEventListener('DOMContentLoaded', function() {
    		const pwdInput = document.querySelector('input[name="password"]');
    		if (!pwdInput) return;

    		const strengthDiv = document.createElement('div');
    		strengthDiv.id = 'password-strength';
    		strengthDiv.className = 'mt-2';
    		pwdInput.parentElement.appendChild(strengthDiv);

    		pwdInput.addEventListener('input', function() {
        		const isStrong = checkPasswordStrength(this.value);
        		
        		const form = this.form;
        		const submitBtn = form.querySelector('button[type="submit"]');
        		if (submitBtn) {
            		submitBtn.disabled = !isStrong;
        		}
    		});
	});
</script>
	
</div>
