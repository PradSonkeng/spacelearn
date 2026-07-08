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

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'];

        $this->setFlash('success', 'Bienvenue, ' . $user['full_name'] . ' !');
        $this->redirectToDashboard();
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

        // Seuls ces deux rôles peuvent s'auto-inscrire
        if (!in_array($role, ['etudiant', 'enseignant'], true)) {
            $role = 'etudiant';
        }

        if ($fullName === '' || $email === '' || $password === '') {
            $this->setFlash('danger', 'Tous les champs sont obligatoires.');
            $this->redirect('auth/register');
        }

        if (strlen($password) < 6) {
            $this->setFlash('danger', 'Le mot de passe doit contenir au moins 6 caractères.');
            $this->redirect('auth/register');
        }

        if ($password !== $confirm) {
            $this->setFlash('danger', 'Les mots de passe ne correspondent pas.');
            $this->redirect('auth/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('danger', 'Adresse email invalide.');
            $this->redirect('auth/register');
        }

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            $this->setFlash('danger', 'Cet email est déjà utilisé.');
            $this->redirect('auth/register');
        }

        $userModel->register($fullName, $email, $password, $role);

        $this->setFlash('success', 'Votre compte a été créé avec succès. Vous pouvez vous connecter.');
        $this->redirect('auth/login');
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
