/* =========================================================
   SPACELEARN - SCRIPT PRINCIPAL
   ========================================================= */

document.addEventListener('DOMContentLoaded', function () {

    /* -----------------------------------------------------
       1. MODE SOMBRE / CLAIR
       ----------------------------------------------------- */
    const root = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');
    const savedTheme = localStorage.getItem('nl-theme') || 'light';
    root.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const current = root.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            localStorage.setItem('nl-theme', next);
            updateThemeIcon(next);
        });
    }

    function updateThemeIcon(theme) {
        const icon = document.getElementById('themeIcon');
        if (!icon) return;
        icon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }

    /* -----------------------------------------------------
       2. MENU LATÉRAL (mobile)
       ----------------------------------------------------- */
    const sidebar = document.getElementById('nlSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });
    }
    const sidebarClose = document.getElementById('sidebarClose');
    if (sidebarClose && sidebar) {
        sidebarClose.addEventListener('click', function () {
            sidebar.classList.remove('show');
        });
    }
    document.addEventListener('click', function (e) {
        if (window.innerWidth < 992) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });

    /* -----------------------------------------------------
       3. NOTIFICATIONS (AJAX polling)
       ----------------------------------------------------- */
    const notifBell = document.getElementById('notifBell');
    const notifBadge = document.getElementById('notifBadge');
    const notifList = document.getElementById('notifList');
    const baseUrl = document.body.dataset.baseUrl || '';

    function loadNotifications() {
        if (!notifList) return;
        fetch(baseUrl + '/api/notifications')
            .then(r => r.json())
            .then(data => {
                if (data.count > 0) {
                    notifBadge.textContent = data.count;
                    notifBadge.classList.remove('d-none');
                } else {
                    notifBadge.classList.add('d-none');
                }

                if (data.items.length === 0) {
                    notifList.innerHTML = '<li class="text-center text-muted py-3 small">Aucune notification</li>';
                    return;
                }

                notifList.innerHTML = data.items.map(n => `
                    <li>
                        <a class="dropdown-item nl-notif-item ${n.is_read == 0 ? 'unread' : ''} py-2" href="${n.link ? baseUrl + '/' + n.link : '#'}">
                            <div class="small">${n.message}</div>
                            <div class="text-muted" style="font-size:.75rem;">${n.time_ago}</div>
                        </a>
                    </li>`).join('');
            })
            .catch(() => {});
    }

    if (notifList) {
        loadNotifications();
        setInterval(loadNotifications, 30000);

        if (notifBell) {
            notifBell.addEventListener('click', function () {
                fetch(baseUrl + '/api/markNotificationsRead', { method: 'POST' })
                    .then(() => { notifBadge.classList.add('d-none'); });
            });
        }
    }

    /* -----------------------------------------------------
       4. RECHERCHE AJAX (catalogue de cours)
       ----------------------------------------------------- */
    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('searchResults');

    if (searchInput && searchResults) {
        let debounce;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounce);
            const query = this.value.trim();

            if (query.length < 2) {
                searchResults.classList.add('d-none');
                searchResults.innerHTML = '';
                return;
            }

            debounce = setTimeout(() => {
                fetch(baseUrl + '/api/search?q=' + encodeURIComponent(query))
                    .then(r => r.json())
                    .then(data => {
                        if (data.length === 0) {
                            searchResults.innerHTML = '<div class="list-group-item text-muted small">Aucun résultat trouvé.</div>';
                        } else {
                            searchResults.innerHTML = data.map(c => `
                                <a href="${baseUrl}/student/course/${c.id}" class="list-group-item list-group-item-action">
                                    <div class="fw-semibold small">${c.title}</div>
                                    <div class="text-muted" style="font-size:.78rem;">${c.module_title}</div>
                                </a>`).join('');
                        }
                        searchResults.classList.remove('d-none');
                    });
            }, 300);
        });

        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('d-none');
            }
        });
    }

    /* -----------------------------------------------------
       5. ACTIVATION DU LIEN DE MENU COURANT
       ----------------------------------------------------- */
    document.querySelectorAll('.nl-sidebar .nav-link').forEach(link => {
        if (link.getAttribute('href') === window.location.pathname) {
            link.classList.add('active');
        }
    });
});
