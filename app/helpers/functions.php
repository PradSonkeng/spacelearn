<?php
/**
 * =========================================================
 *  Fonctions utilitaires globales
 * =========================================================
 */

/** Échappe une chaîne pour un affichage HTML sûr */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Construit une URL absolue (relative à BASE_URL) */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Construit une URL vers un fichier d'asset (css/js/img) */
function asset(string $path): string
{
    return ASSETS_URL . '/' . ltrim($path, '/');
}

/** Construit une URL vers un fichier uploadé */
function upload(string $path): string
{
    return UPLOADS_URL . '/' . ltrim($path, '/');
}

/** Construit une URL absolue (avec schéma + hôte), utile pour les QR codes */
function full_url(string $path = ''): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . url($path);
}

/** Génère (ou réutilise) un jeton CSRF pour le formulaire courant */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Affiche un champ caché contenant le jeton CSRF */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/** Indique si un utilisateur est connecté */
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

/** Retourne le rôle de l'utilisateur connecté (ou null) */
function current_role(): ?string
{
    return $_SESSION['user_role'] ?? null;
}

/** Retourne l'id de l'utilisateur connecté (ou null) */
function current_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/** Formate une date au format français (ex: 12 juin 2026) */
function format_date(?string $date): string
{
    if (!$date) return '—';
    $months = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    $ts = strtotime($date);
    return (int)date('j', $ts) . ' ' . $months[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

/** Formate une date relative ("il y a X minutes") */
function time_ago(?string $date): string
{
    if (!$date) return '—';
    $diff = time() - strtotime($date);
    if ($diff < 60) return 'à l\'instant';
    if ($diff < 3600) return 'il y a ' . floor($diff / 60) . ' min';
    if ($diff < 86400) return 'il y a ' . floor($diff / 3600) . ' h';
    if ($diff < 2592000) return 'il y a ' . floor($diff / 86400) . ' j';
    return format_date($date);
}

/**
 * Affiche le message flash courant (s'il existe) sous forme
 * d'alerte Bootstrap dismissible.
 */
function display_flash(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    $icons = [
        'success' => 'check-circle',
        'danger'  => 'exclamation-triangle',
        'warning' => 'exclamation-circle',
        'info'    => 'info-circle',
    ];
    $icon = $icons[$flash['type']] ?? 'info-circle';

    echo '<div class="alert alert-' . e($flash['type']) . ' alert-dismissible fade show shadow-sm" role="alert">';
    echo '<i class="fa-solid fa-' . $icon . ' me-2"></i>' . e($flash['message']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>';
    echo '</div>';
}

/**
 * Génère le rendu HTML d'une note en étoiles (lecture seule).
 */
function render_stars(float $rating, int $max = 5): string
{
    $html = '';
    for ($i = 1; $i <= $max; $i++) {
        if ($rating >= $i) {
            $html .= '<i class="fa-solid fa-star text-warning"></i>';
        } elseif ($rating >= $i - 0.5) {
            $html .= '<i class="fa-solid fa-star-half-stroke text-warning"></i>';
        } else {
            $html .= '<i class="fa-regular fa-star text-warning"></i>';
        }
    }
    return $html;
}

/** Renvoie un libellé lisible pour un rôle */
function role_label(string $role): string
{
    return match ($role) {
        'promoteur'  => 'Promoteur',
        'enseignant' => 'Enseignant',
        'etudiant'   => 'Étudiant',
        default      => $role,
    };
}

/** Renvoie un badge couleur Bootstrap pour un statut de cours */
function status_badge(string $status): string
{
    return $status === 'publie'
        ? '<span class="badge bg-success">Publié</span>'
        : '<span class="badge bg-secondary">Brouillon</span>';
}

/** Tronque un texte à une longueur donnée */
function truncate(?string $text, int $length = 100): string
{
    $text = $text ?? '';
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '…';
}

/**
 * Crée une notification pour un utilisateur donné.
 */
function notify(int $userId, string $message, ?string $link = null): void
{
    Database::query(
        "INSERT INTO notifications (user_id, message, link) VALUES (:user_id, :message, :link)",
        ['user_id' => $userId, 'message' => $message, 'link' => $link]
    );
}

/**
 * Valide et déplace un fichier uploadé.
 *
 * @return string|false  Chemin relatif (sous /uploads) en cas de succès, false sinon
 */
function handle_upload(array $file, string $subDir, array $allowedExt, int $maxSize): string|false
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    if ($file['size'] > $maxSize) {
        return false;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return false;
    }

    $targetDir = UPLOAD_PATH . '/' . $subDir;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    $filename = uniqid($subDir . '_', true) . '.' . $ext;
    $targetPath = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return false;
    }

    return $subDir . '/' . $filename;
}
