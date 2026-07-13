<?php
require_once APP_PATH . '/models/User.php';

class AuthController extends Controller
{
    /** Formulaire de connexion */
    public function login(): void
    {
        if (is_logged_in()) {
            $this->redirectToDashboard();
        }
        $this->view('auth/login', ['title' => 'Connexion'], 'guest');
    }

    /** Traitement de la connexion */
    public function doLogin(): void
    {
        $this->verifyCsrf();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->setFlash('danger', 'Veuillez renseigner votre email et votre mot de passe.');
            $this->redirect('auth/login');
        }

        $userModel = new User();
        $user = $userModel->attempt($email, $password);

        if (!$user) {
            $this->setFlash('danger', 'Identifiants incorrects ou compte désactivé.');
            $this->redirect('auth/login');
        }
        
        // bloque tant que l'email n'est pas verifier
		if ($user['email_verified'] == 0) {
    			$this->setFlash('warning', 'Vous devez vérifier votre adresse email avant de vous connecter.');
    			$this->redirect('auth/login');
		}

        	$_SESSION['user_id']   = $user['id'];
        	$_SESSION['user_role'] = $user['role'];
        	$_SESSION['user_name'] = $user['full_name'];

        	$this->setFlash('success', 'Bienvenue, ' . $user['full_name'] . ' !');
        	$this->redirectToDashboard();
    	}
    	
    	/** Formulaire mot de passe oublié */
	public function forgotPassword(): void
	{
    		$this->view('auth/forgot_password', ['title' => 'Mot de passe oublié'], 'guest');
	}
	
	/** Envoi du lien de réinitialisation */
	public function sendResetLink(): void
	{
    		$this->verifyCsrf();
    		$email = trim($_POST['email'] ?? '');
    		
    		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        		$this->setFlash('danger', 'Adresse email invalide.');
        		$this->redirect('auth/forgotPassword');
    		}

    		$userModel = new User();
    		if (!$userModel->findByEmail($email)) {
        		$this->setFlash('success', 'Si un compte existe avec cet email, un lien de réinitialisation vous a été envoyé.');
        		$this->redirect('auth/forgotPassword');
    		}

    		$token = bin2hex(random_bytes(32));
    		$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    		Database::query(
        		"INSERT INTO password_resets (email, token, expires_at)
        		VALUES (:email, :token, :expires_at)
         	ON DUPLICATE KEY UPDATE token = :token2, expires_at = :expires_at2",
        		[
        			'email' 			=> $email, 
        			'token' 			=> $token, 
        			'expires_at' 	=> $expires,
        			'token2'      	=> $token,
            		'expires_at2' 	=> $expires
        		]
    		);

		// Envoi de l'email
    		require_once APP_PATH . '/helpers/mail.php';
    		$resetLink = full_url("auth/resetPassword?token=" . $token);
    		$subject = "Réinitialisation de votre mot de passe - " . APP_NAME;
    		$body = "
        		<h2>Réinitialisation de mot de passe</h2>
        		<p>Bonjour,</p>
        		<p>Vous avez demandé une réinitialisation de mot de passe.</p>
        		<p><a href='$resetLink' style='font-size:18px;'>Réinitialiser mon mot de passe</a></p>
        		<p>Ce lien est valide pendant <strong>1 heure</strong>.</p>
        		<hr>
        		<small>Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</small>
		";
    		$sent = sendEmail($email, $user['full_name'] ?? 'Utilisateur', $subject, $body);  // Fonction générique

    		if ($sent) {
        		$this->setFlash('success', 'Un lien de réinitialisation vous a été envoyé par email.');
    		} else {
        		$this->setFlash('warning', 'Le lien n\'a pas pu être envoyé. Veuillez réessayer plus tard.');
    		}
    		$this->redirect('auth/forgotPassword');
	}
	
	/** Formulaire de réinitialisation */
	public function resetPassword(): void
	{
    		$token = $_GET['token'] ?? $_POST['token'] ?? '';
    		$this->view('auth/reset_password', [
        		'title' => 'Réinitialisation du mot de passe',
        		'token' => $token
    		], 'guest');
	}
	
	/** Traitement du changement de mot de passe */
	public function doResetPassword(): void
	{
    		$this->verifyCsrf();

    		$token = $_POST['token'] ?? '';
    		$password = $_POST['password'] ?? '';
    		$confirm = $_POST['password_confirm'] ?? '';
    		
    		// Validation stricte du mot de passe (doit être FORT)
		if (strlen($password) < 8 || 
    			!preg_match('/[a-z]/', $password) || 
    			!preg_match('/[A-Z]/', $password) || 
    			!preg_match('/\d/', $password) || 
    			!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>\/?]/', $password)) {
    
    			$this->setFlash('danger', 'Le mot de passe doit être FORT : minimum 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial (!@#$% etc.).');
    			$this->redirect("auth/resetPassword?token=" . urlencode($token));
    			return;
		}

    		if (empty($token) || $password !== $confirm) {
        		$this->setFlash('danger', 'Les mots de passe ne correspondent ');
        		$this->redirect("auth/resetPassword?token=$token");
        		return;
    		}

    		$row = Database::query(
        		"SELECT email FROM password_resets WHERE token = :token AND expires_at > NOW()",
        		['token' => $token]
    		)->fetch();

    		if (!$row) {
        		$this->setFlash('danger', 'Lien invalide ou expiré.');
        		$this->redirect('auth/forgotPassword');
        		return;
    		}

    		$userModel = new User();
    		$user = $userModel->findByEmail($row['email']);

    		if ($user) {
        		$userModel->changePassword($user['id'], $password);
        		Database::query("DELETE FROM password_resets WHERE token = :token", ['token' => $token]);
        		$this->setFlash('success', 'Votre mot de passe a été modifié avec succès.');
        		$this->redirect('auth/login');
    		} else {
        		$this->setFlash('danger', 'Une erreur est survenue.');
        		$this->redirect('auth/login');
    		}
	}

    /** Formulaire d'inscription */
    public function register(): void
    {
        if (is_logged_in()) {
            $this->redirectToDashboard();
        }
        $this->view('auth/register', ['title' => 'Inscription'], 'guest');
    }

    /** Traitement de l'inscription */
    public function doRegister(): void
    {
        $this->verifyCsrf();

        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';
        $role     = $_POST['role'] ?? 'etudiant';


		// Limitation : max 5 tentatives par IP par heure
		$ip = $_SERVER['REMOTE_ADDR'];

		$attemptCount = Database::query(
    			"SELECT COUNT(*) as cnt FROM registration_attempts 
     		WHERE ip_address = :ip AND attempted_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
    			['ip' => $ip]
		)->fetch()['cnt'];

		if ($attemptCount >= 5) {
    			$this->setFlash('danger', 'Trop de tentatives d\'inscription. Veuillez réessayer dans 1 heure.');
    			$this->redirect('auth/register');
    			return;
	}

	// Enregistrer la tentative
	Database::query(
    		"INSERT INTO registration_attempts (ip_address, email) VALUES (:ip, :email)",
    		['ip' => $ip, 'email' => $email]
	);
        // Seuls ces deux rôles peuvent s'auto-inscrire
        if (!in_array($role, ['etudiant', 'enseignant'], true)) {
            $role = 'etudiant';
        }

        if ($fullName === '' || $email === '' || $password === '') {
            $this->setFlash('danger', 'Tous les champs sont obligatoires.');
            $this->redirect('auth/register');
            return;
        }

        // Validation stricte du mot de passe (doit être FORT)
		if (strlen($password) < 8 || 
    		!preg_match('/[a-z]/', $password) || 
    		!preg_match('/[A-Z]/', $password) || 
    		!preg_match('/\d/', $password) || 
    		!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>\/?]/', $password)) {
    
    		$this->setFlash('danger', 'Le mot de passe doit être FORT : minimum 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial (!@#$% etc.).');
    		$this->redirect('auth/register');
    		return;
	}

        if ($password !== $confirm) {
            $this->setFlash('danger', 'Les mots de passe ne correspondent pas.');
            $this->redirect('auth/register');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('danger', 'Adresse email invalide.');
            $this->redirect('auth/register');
            return;
        }

        $userModel = new User();
        if ($userModel->emailExists($email)) {
            $this->setFlash('danger', 'Cet email est déjà utilisé.');
            $this->redirect('auth/register');
            return;
        }

        $data = $userModel->registerWithVerification($fullName, $email, $password, $role);
        
        // Envoi du mail de confirmation (simulation pour l'instant)
        require_once APP_PATH . '/helpers/mail.php';
        $sent = sendVerificationEmail($email, $fullName, $data['token']);
        
		$verifyLink = full_url("auth/verifyEmail?token=" . $data['token']);
		
		if ($sent) {
    			$this->setFlash('success', 'Compte créé ! Un email de confirmation vous a été envoyé.');
		} else {
    			$this->setFlash('warning', 'Compte créé, mais l\'email de confirmation n\'a pas pu être envoyé. Contactez l\'administrateur.');
		}

		$this->redirect('auth/login');
    }
    
    /** Vérification en temps réel de l'email (AJAX) */
	public function checkEmail(): void
	{
    		$this->verifyCsrf();
    		$email = trim($_POST['email'] ?? '');

		// 1. Format valide
    		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        		$this->json(['valid' => false, 'message' => 'Format d\'email invalide']);
    		}
    		
    		// 2. Vérification MX (domaine existe et accepte les emails)
    		$domain = substr(strrchr($email, "@"), 1);
    		$mxValid = checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
    		
    		if (!$mxValid) {
        		$this->json(['valid' => false, 'message' => 'Domaine email invalide ou inexistant.']);
    		}

		// 3. Unicité dans la base
    		$userModel = new User();
    		$exists = $userModel->emailExists($email);

    		$this->json([
        		'valid' => true,
        		'exists' => $exists,
        		'message' => $exists ? 'Cet email est déjà utilisé.' : 'Email disponible.'
    		]);
	}

	/** Vérification du lien d'activation */
	public function verifyEmail(): void
	{
    		$token = $_GET['token'] ?? '';
    		$userModel = new User();

    		if ($userModel->verifyEmail($token)) {
        		$this->setFlash('success', 'Votre email a été vérifié avec succès. Vous pouvez maintenant vous connecter.');
    		} else {
        		$this->setFlash('danger', 'Lien invalide ou déjà utilisé.');
    		}
    		$this->redirect('auth/login');
	}
	
	// Formulaire renvoi lien
	public function resendVerificationForm(): void
	{
    		$this->view('auth/resend_verification', ['title' => 'Renvoyer le lien de vérification'], 'guest');
	}

	// Traitement du renvoi
	public function resendVerification(): void
	{
    		$this->verifyCsrf();
    		$email = trim($_POST['email'] ?? '');
    		

    		$userModel = new User();
    		$user = $userModel->findByEmail($email);

    		if (!$user || $user['email_verified'] == 1) {
        		$this->setFlash('danger', 'Aucun compte en attente de vérification trouvé.');
        		$this->redirect('auth/resendVerification');
    		}

    		// Générer nouveau token
    		$token = bin2hex(random_bytes(32));
    		Database::query(
        		"UPDATE users SET verification_token = :token WHERE id = :id",
        		['token' => $token, 'id' => $user['id']]
    		);

    		require_once APP_PATH . '/helpers/mail.php';
    		$sent = sendVerificationEmail($email, $user['full_name'], $token);

    		if ($sent) {
        		$this->setFlash('success', 'Un nouveau lien de vérification a été envoyé.');
    		} else {
        		$this->setFlash('warning', 'Erreur lors de l\'envoi de l\'email.');
    		}

    		$this->redirect('auth/resendVerification');
	}

    /** Déconnexion */
    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect('');
    }

    /** Redirige vers le tableau de bord correspondant au rôle */
    private function redirectToDashboard(): void
    {
        match ($_SESSION['user_role']) {
            'promoteur'  => $this->redirect('admin/dashboard'),
            'enseignant' => $this->redirect('teacher/dashboard'),
            default      => $this->redirect('student/dashboard'),
        };
    }
}
