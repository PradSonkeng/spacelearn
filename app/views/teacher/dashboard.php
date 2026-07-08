<div class="mb-4">
    <h3 class="fw-bold mb-0">Tableau de bord</h3>
    <p class="text-muted mb-0">Bienvenue, <?= e($_SESSION['user_name']) ?> 👋</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="nl-stat-card bg-1">
            <i class="fa-solid fa-book bg-icon"></i>
            <div class="small opacity-75">Mes cours</div>
            <div class="fs-3 fw-bold"><?= count($courses) ?></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="nl-stat-card bg-2">
            <i class="fa-solid fa-user-graduate bg-icon"></i>
            <div class="small opacity-75">Étudiants inscrits</div>
            <div class="fs-3 fw-bold"><?= $totalStudents ?></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="nl-stat-card bg-3">
            <i class="fa-solid fa-star bg-icon"></i>
            <div class="small opacity-75">Note moyenne</div>
            <div class="fs-3 fw-bold"><?= $avgRating ?> / 5</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="nl-stat-card bg-4">
            <i class="fa-solid fa-circle-plus bg-icon"></i>
            <div class="small opacity-75">Action rapide</div>
            <a href="<?= url('teacher/courseCreate') ?>" class="btn btn-sm btn-light mt-1 fw-semibold">Nouveau cours</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="nl-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-simple text-primary me-2"></i>Progression moyenne par cours</h6>
            <?php if (empty($progressPerCourse)): ?>
                <p class="text-muted small mb-0">Aucune donnée disponible pour le moment.</p>
            <?php else: ?>
                <canvas id="progressChart" height="220"></canvas>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="nl-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-book-open text-primary me-2"></i>Mes cours récents</h6>
            <?php if (empty($courses)): ?>
                <p class="text-muted small mb-0">Vous n'avez pas encore créé de cours.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach (array_slice($courses, 0, 5) as $c): ?>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <a href="<?= url('teacher/courseManage/' . $c['id']) ?>" class="fw-semibold text-decoration-none"><?= e($c['title']) ?></a>
                            <div class="small text-muted"><?= (int)$c['nb_lessons'] ?> leçon(s) · <?= (int)$c['nb_students'] ?> étudiant(s)</div>
                        </div>
                        <?= status_badge($c['status']) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($progressPerCourse)): ?>
<?php
$inlineJs = "
new Chart(document.getElementById('progressChart'), {
    type: 'bar',
    data: {
        labels: " . json_encode(array_map(fn($r) => $r['title'], $progressPerCourse)) . ",
        datasets: [{ label: 'Progression moyenne %', data: " . json_encode(array_map(fn($r) => (float)$r['avg_progress'], $progressPerCourse)) . ", backgroundColor: '#4361ee', borderRadius: 6 }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { min: 0, max: 100 } } }
});
";
?>
<?php endif; ?>
