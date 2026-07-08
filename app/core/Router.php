<?php
/**
 * =========================================================
 *  Router — Routeur simple basé sur le segment d'URL
 * =========================================================
 *  Format attendu : /public/index.php?url=controller/action/p1/p2
 *  Réécrit en URL propre via .htaccess :
 *     /public/controller/action/p1/p2
 *
 *  Conventions :
 *   - Contrôleur par défaut : Home   -> HomeController
 *   - Action par défaut     : index
 *   - Les segments suivants sont passés en paramètres
 */

class Router
{
    public function dispatch(): void
    {
        $url = trim($_GET['url'] ?? '', '/');
        $segments = $url === '' ? [] : explode('/', $url);

        $controllerName = !empty($segments[0]) ? $segments[0] : 'home';
        $action          = !empty($segments[1]) ? $segments[1] : 'index';
        $params          = array_slice($segments, 2);

        $controllerClass = ucfirst(strtolower($controllerName)) . 'Controller';
        $controllerFile  = APP_PATH . "/controllers/{$controllerClass}.php";

        if (!file_exists($controllerFile)) {
            $this->notFound();
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerClass)) {
            $this->notFound();
            return;
        }

        $controller = new $controllerClass();

        // Sécurité : seules les méthodes publiques explicitement définies
        // dans le contrôleur (hors méthodes héritées de Controller) sont
        // accessibles via une URL.
        if (!method_exists($controller, $action) || !is_callable([$controller, $action])) {
            $this->notFound();
            return;
        }

        call_user_func_array([$controller, $action], $params);
    }

    private function notFound(): void
    {
        http_response_code(404);
        require APP_PATH . '/views/errors/404.php';
    }
}
