<?php
$role = current_role();
$requestPath = trim($_GET['url'] ?? '', '/');

function nl_active(string $path, string $requestPath): string
{
    return str_starts_with($requestPath, $path) ? 'active' : '';
}
?>
<aside class="nl-sidebar" id="nlSidebar">
    <div class="brand">
        <i class="fa-solid fa-graduation-cap"></i>
        <span><?= APP_NAME ?></span>
    </div>

    <nav class="nav flex-column py-2">
        <?php if ($role === 'promoteur'): ?>
            <div class="sidebar-section">Promoteur</div>
            <a class="nav-link <?= nl_active('admin/dashboard', $requestPath) ?>" href="<?= url('admin/dashboard') ?>">
                <i class="fa-solid fa-gauge"></i> Tableau de bord
            </a>
            <a class="nav-link <?= nl_active('admin/modules', $requestPath) ?>" href="<?= url('admin/modules') ?>">
                <i class="fa-solid fa-layer-group"></i> Modules
            </a>
            <a class="nav-link <?= nl_active('admin/courses', $requestPath) ?>" href="<?= url('admin/courses') ?>">
                <i class="fa-solid fa-book"></i> Cours
            </a>
            <a class="nav-link <?= nl_active('admin/users', $requestPath) ?>" href="<?= url('admin/users') ?>">
                <i class="fa-solid fa-users"></i> Utilisateurs
            </a>
            <a class="nav-link <?= nl_active('admin/certificates', $requestPath) ?>" href="<?= url('admin/certificates') ?>">
                <i class="fa-solid fa-certificate"></i> Certificats
            </a>
            <a class="nav-link <?= nl_active('admin/statistics', $requestPath) ?>" href="<?= url('admin/statistics') ?>">
                <i class="fa-solid fa-chart-pie"></i> Statistiques
            </a>

        <?php elseif ($role === 'enseignant'): ?>
            <div class="sidebar-section">Enseignant</div>
            <a class="nav-link <?= nl_active('teacher/dashboard', $requestPath) ?>" href="<?= url('teacher/dashboard') ?>">
                <i class="fa-solid fa-gauge"></i> Tableau de bord
            </a>
            <a class="nav-link <?= nl_active('teacher/courses', $requestPath) ?>" href="<?= url('teacher/courses') ?>">
                <i class="fa-solid fa-book-open"></i> Mes cours
            </a>
            <a class="nav-link <?= nl_active('teacher/courseCreate', $requestPath) ?>" href="<?= url('teacher/courseCreate') ?>">
                <i class="fa-solid fa-circle-plus"></i> Nouveau cours
            </a>
            <a class="nav-link <?= nl_active('teacher/students', $requestPath) ?>" href="<?= url('teacher/students') ?>">
                <i class="fa-solid fa-user-graduate"></i> Étudiants
            </a>

        <?php elseif ($role === 'etudiant'): ?>
            <div class="sidebar-section">Étudiant</div>
            <a class="nav-link <?= nl_active('student/dashboard', $requestPath) ?>" href="<?= url('student/dashboard') ?>">
                <i class="fa-solid fa-gauge"></i> Tableau de bord
            </a>
            <a class="nav-link <?= nl_active('student/catalog', $requestPath) ?>" href="<?= url('student/catalog') ?>">
                <i class="fa-solid fa-store"></i> Catalogue des cours
            </a>
            <a class="nav-link <?= nl_active('student/myCourses', $requestPath) ?>" href="<?= url('student/myCourses') ?>">
                <i class="fa-solid fa-book-open"></i> Mes cours
            </a>
            <a class="nav-link <?= nl_active('student/certificates', $requestPath) ?>" href="<?= url('student/certificates') ?>">
                <i class="fa-solid fa-certificate"></i> Mes certificats
            </a>
        <?php endif; ?>

        <div class="sidebar-section">Compte</div>
        <a class="nav-link <?= nl_active('profile', $requestPath) ?>" href="<?= url('profile') ?>">
            <i class="fa-solid fa-user"></i> Mon profil
        </a>
        <a class="nav-link" href="<?= url('auth/logout') ?>">
            <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
        </a>
    </nav>
</aside>
