<div class="mb-4">
    <h3 class="fw-bold mb-0">Tableau de bord</h3>
    <p class="text-muted">Vue d'ensemble de la plateforme <?= APP_NAME ?>.</p>
</div>

<!-- Cartes statistiques -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="nl-stat-card bg-1">
            <i class="fa-solid fa-user-graduate bg-icon"></i>
            <div class="small opacity-75">Étudiants</div>
            <div class="fs-3 fw-bold"><?= $stats['students'] ?></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="nl-stat-card bg-2">
            <i class="fa-solid fa-chalkboard-user bg-icon"></i>
            <div class="small opacity-75">Enseignants</div>
            <div class="fs-3 fw-bold"><?= $stats['teachers'] ?></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="nl-stat-card bg-3">
            <i class="fa-solid fa-layer-group bg-icon"></i>
            <div class="small opacity-75">Modules</div>
            <div class="fs-3 fw-bold"><?= $stats['modules'] ?></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="nl-stat-card bg-4">
            <i class="fa-solid fa-certificate bg-icon"></i>
            <div class="small opacity-75">Certificats délivrés</div>
            <div class="fs-3 fw-bold"><?= $stats['certificates'] ?></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Graphique inscriptions -->
    <div class="col-lg-7">
        <div class="nl-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-line text-primary me-2"></i>Inscriptions (6 derniers mois)</h6>
            <canvas id="enrollChart" height="220"></canvas>
        </div>
    </div>
    <!-- Top cours -->
    <div class="col-lg-5">
        <div class="nl-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-trophy text-primary me-2"></i>Cours les plus suivis</h6>
            <canvas id="topCoursesChart" height="220"></canvas>
        </div>
    </div>

    <!-- Activité récente -->
    <div class="col-12">
        <div class="nl-card p-4">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Activité récente</h6>
            <?php if (empty($recentActivity)): ?>
                <p class="text-muted small mb-0">Aucune activité récente.</p>
            <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($recentActivity as $a): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <i class="fa-solid fa-user-plus text-success me-2"></i>
                        <strong><?= e($a['full_name']) ?></strong> s'est inscrit(e) au cours <strong><?= e($a['course_title']) ?></strong>
                    </div>
                    <small class="text-muted"><?= time_ago($a['enrolled_at']) ?></small>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$inlineJs = "
const enrollData = " . json_encode($enrollmentsByMonth) . ";
const topCoursesData = " . json_encode($topCourses) . ";

new Chart(document.getElementById('enrollChart'), {
    type: 'line',
    data: {
        labels: enrollData.map(d => d.ym),
        datasets: [{
            label: 'Inscriptions',
            data: enrollData.map(d => d.total),
            borderColor: '#4361ee',
            backgroundColor: 'rgba(67,97,238,.15)',
            tension: .35,
            fill: true,
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});

new Chart(document.getElementById('topCoursesChart'), {
    type: 'bar',
    data: {
        labels: topCoursesData.map(d => d.title),
        datasets: [{
            label: 'Étudiants inscrits',
            data: topCoursesData.map(d => d.nb_students),
            backgroundColor: ['#4361ee','#4cc9f0','#f72585','#2dd4bf','#ffb703'],
            borderRadius: 6,
        }]
    },
    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
});
";
?>
