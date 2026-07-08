<div class="mb-4">
    <h3 class="fw-bold mb-0">Statistiques</h3>
    <p class="text-muted mb-0">Analyse de l'utilisation de la plateforme.</p>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="nl-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-users text-primary me-2"></i>Répartition des utilisateurs</h6>
            <canvas id="usersChart" height="240"></canvas>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="nl-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-layer-group text-primary me-2"></i>Nombre de cours par module</h6>
            <canvas id="modulesChart" height="240"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="nl-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-star text-primary me-2"></i>Notes moyennes par cours</h6>
            <?php if (empty($ratingsByCourse)): ?>
                <p class="text-muted small mb-0">Aucun avis pour le moment.</p>
            <?php else: ?>
            <canvas id="ratingsChart" height="240"></canvas>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="nl-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-simple text-primary me-2"></i>Progression moyenne par cours</h6>
            <?php if (empty($progressByCourse)): ?>
                <p class="text-muted small mb-0">Aucune progression enregistrée.</p>
            <?php else: ?>
            <canvas id="progressChart" height="240"></canvas>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$extraJs = ['https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js'];

$inlineJs = "
new Chart(document.getElementById('usersChart'), {
    type: 'doughnut',
    data: {
        labels: " . json_encode(array_map(fn($r) => role_label($r['role']), $usersByRole)) . ",
        datasets: [{ data: " . json_encode(array_map(fn($r) => (int)$r['total'], $usersByRole)) . ", backgroundColor: ['#f72585','#4361ee','#2dd4bf'] }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('modulesChart'), {
    type: 'bar',
    data: {
        labels: " . json_encode(array_map(fn($r) => $r['title'], $coursesByModule)) . ",
        datasets: [{ label: 'Cours', data: " . json_encode(array_map(fn($r) => (int)$r['total'], $coursesByModule)) . ", backgroundColor: '#4361ee', borderRadius: 6 }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
";

if (!empty($ratingsByCourse)) {
    $inlineJs .= "
new Chart(document.getElementById('ratingsChart'), {
    type: 'bar',
    data: {
        labels: " . json_encode(array_map(fn($r) => $r['title'], $ratingsByCourse)) . ",
        datasets: [{ label: 'Note moyenne /5', data: " . json_encode(array_map(fn($r) => (float)$r['avg_rating'], $ratingsByCourse)) . ", backgroundColor: '#ffb703', borderRadius: 6 }]
    },
    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { min: 0, max: 5 } } }
});";
}

if (!empty($progressByCourse)) {
    $inlineJs .= "
new Chart(document.getElementById('progressChart'), {
    type: 'bar',
    data: {
        labels: " . json_encode(array_map(fn($r) => $r['title'], $progressByCourse)) . ",
        datasets: [{ label: 'Progression moyenne %', data: " . json_encode(array_map(fn($r) => (float)$r['avg_progress'], $progressByCourse)) . ", backgroundColor: '#2dd4bf', borderRadius: 6 }]
    },
    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { min: 0, max: 100 } } }
});";
}
?>
