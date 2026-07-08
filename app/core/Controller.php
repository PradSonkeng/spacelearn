<?php
/**
 * =========================================================
 *  Controller — Classe de base pour tous les contrôleurs
 * =========================================================
 */

abstract class Controller
{
    /**
     * Charge un modèle et le retourne instancié.
     */
    protected function model(string $name)
    {
        $path = APP_PATH . "/models/{$name}.php";
        if (!file_exists($path)) {
            throw new Exception("Modèle introuvable : {$name}");
        }
        require_once $path;
        return new $name();
    }

    /**
     * Affiche une vue avec son layout.
     *
     * @param string $view   chemin relatif depuis app/views (ex: 'student/dashboard')
     * @param array  $data   données transmises à la vue (extraites en variables)
     * @param string $layout layout à utiliser ('main', 'guest', 'auth')
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);
        $viewFile = APP_PATH . "/views/{$view}.php";
        if (!file_exists($viewFile)) {
            throw new Exception("Vue introuvable : {$view}");
        }

        if ($layout === 'none') {
            require $viewFile;
            return;
        }

        $layoutFile = APP_PATH . "/views/layouts/{$layout}.php";
        require $layoutFile;
    }

    /**
     * Redirige vers une URL interne (relative à BASE_URL).
     */
    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }

    /**
     * Retourne une réponse JSON et termine le script (pour AJAX).
     */
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Stocke un message flash affiché sur la prochaine page.
     */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /** Récupère et efface le message flash courant */
    protected function getFlash(): ?array
    {
        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    /**
     * Vérifie que l'utilisateur est connecté, sinon redirige vers login.
     */
    protected function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->setFlash('warning', 'Veuillez vous connecter pour accéder à cette page.');
            $this->redirect('auth/login');
        }
    }

    /**
     * Vérifie que l'utilisateur a l'un des rôles autorisés.
     *
     * @param string|array $roles
     */
    protected function requireRole($roles): void
    {
        $this->requireAuth();
        $roles = is_array($roles) ? $roles : [$roles];
        if (!in_array($_SESSION['user_role'], $roles, true)) {
            http_response_code(403);
            $this->view('errors/403', [], 'main');
            exit;
        }
    }

    /**
     * Vérifie le jeton CSRF transmis en POST.
     */
    protected function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(419);
            die('Jeton de sécurité invalide. Veuillez rafraîchir la page et réessayer.');
        }
    }
}
