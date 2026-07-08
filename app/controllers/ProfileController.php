<?php
require_once APP_PATH . '/models/User.php';

class ProfileController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $userModel = new User();
        $user = $userModel->find(current_user_id());

        $this->view('profile/index', [
            'title' => 'Mon profil',
            'user'  => $user,
        ]);
    }

    /** Mise à jour des informations générales (nom, bio, avatar) */
    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userModel = new User();
        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'bio'       => trim($_POST['bio'] ?? ''),
        ];

        if ($data['full_name'] === '') {
            $this->setFlash('danger', 'Le nom complet est obligatoire.');
            $this->redirect('profile');
        }

        if (!empty($_FILES['avatar']['name'])) {
            $path = handle_upload($_FILES['avatar'], 'avatars', ['jpg', 'jpeg', 'png', 'webp'], MAX_IMAGE_SIZE);
            if ($path === false) {
                $this->setFlash('danger', 'Image invalide (formats acceptés : jpg, png, webp — 5 Mo max).');
                $this->redirect('profile');
            }
            $data['avatar'] = basename($path);
        }

        $userModel->update(current_user_id(), $data);
        $_SESSION['user_name'] = $data['full_name'];

        $this->setFlash('success', 'Profil mis à jour avec succès.');
        $this->redirect('profile');
    }

    /** Changement de mot de passe */
    public function changePassword(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userModel = new User();
        $user = $userModel->find(current_user_id());

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['new_password_confirm'] ?? '';

        if (!password_verify($current, $user['password'])) {
            $this->setFlash('danger', 'Mot de passe actuel incorrect.');
            $this->redirect('profile');
        }

        if (strlen($new) < 6) {
            $this->setFlash('danger', 'Le nouveau mot de passe doit contenir au moins 6 caractères.');
            $this->redirect('profile');
        }

        if ($new !== $confirm) {
            $this->setFlash('danger', 'Les mots de passe ne correspondent pas.');
            $this->redirect('profile');
        }

        $userModel->changePassword(current_user_id(), $new);
        $this->setFlash('success', 'Mot de passe modifié avec succès.');
        $this->redirect('profile');
    }
}
