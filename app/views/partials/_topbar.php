<?php
$userModel = new User();
$currentUser = current_user_id() ? $userModel->find(current_user_id()) : null;
?>
<header class="nl-topbar">
    <button class="btn btn-light d-lg-none" id="sidebarToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <?php if (current_role() === 'etudiant'): ?>
    <div class="position-relative flex-grow-1" style="max-width: 420px;">
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
            <input type="text" id="globalSearch" class="form-control border-start-0" placeholder="Rechercher un cours, un module...">
        </div>
        <div id="searchResults" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1050; top: 100%;"></div>
    </div>
    <?php else: ?>
        <div class="flex-grow-1"></div>
    <?php endif; ?>

    <!-- Mode sombre -->
    <button class="btn btn-light" id="themeToggle" title="Basculer le thème">
        <i class="fa-solid fa-moon" id="themeIcon"></i>
    </button>

    <!-- Notifications -->
    <div class="dropdown">
        <button class="btn btn-light position-relative" id="notifBell" data-bs-toggle="dropdown">
            <i class="fa-solid fa-bell"></i>
            <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size:.65rem;">0</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end nl-notif-dropdown shadow">
            <li class="px-3 py-2 fw-semibold border-bottom">Notifications</li>
            <ul id="notifList" class="list-unstyled mb-0">
                <li class="text-center text-muted py-3 small">Chargement...</li>
            </ul>
        </ul>
    </div>

    <!-- Profil -->
    <div class="dropdown">
        <button class="btn btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown">
            <img src="<?= upload('avatars/' . ($currentUser['avatar'] ?? 'default.png')) ?>" onerror="this.src='<?= asset('img/default-avatar.svg') ?>'" class="rounded-circle" width="32" height="32" alt="avatar">
            <span class="d-none d-md-inline"><?= e($currentUser['full_name'] ?? '') ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow">
            <li><span class="dropdown-item-text small text-muted"><?= role_label(current_role()) ?></span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= url('profile') ?>"><i class="fa-solid fa-user me-2"></i>Mon profil</a></li>
            <li><a class="dropdown-item" href="<?= url('auth/logout') ?>"><i class="fa-solid fa-right-from-bracket me-2"></i>Déconnexion</a></li>
        </ul>
    </div>
</header>
