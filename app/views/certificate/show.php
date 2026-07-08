<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificat — <?= e($certificate['module_title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <style>
        body { background: #eef1f7; font-family: 'Poppins', sans-serif; padding: 2rem 0; }
        .nl-certificate h1 { font-family: 'Playfair Display', serif; }
        .signature-line { border-top: 1px solid #9ca3af; width: 200px; margin: .5rem auto 0; }
    </style>
</head>
<body>
<div class="container">
    <div class="text-center mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="fa-solid fa-print me-2"></i>Imprimer / Enregistrer en PDF</button>
        <a href="javascript:history.back()" class="btn btn-light"><i class="fa-solid fa-arrow-left me-2"></i>Retour</a>
    </div>

    <div class="nl-certificate mx-auto" style="max-width: 900px;">
        <div class="d-flex justify-content-center mb-3">
            <i class="fa-solid fa-graduation-cap seal"></i>
        </div>
        <p class="text-uppercase text-muted mb-1" style="letter-spacing: .2em;">Certificat de réussite</p>
        <h1 class="fw-bold mb-4" style="font-size: 2.5rem;"><?= APP_NAME ?></h1>

        <p class="text-muted mb-1">Ce certificat est décerné à</p>
        <h2 class="fw-bold mb-3" style="color: var(--nl-primary);"><?= e($certificate['student_name']) ?></h2>

        <p class="text-muted mb-1">pour avoir validé avec succès le module</p>
        <h4 class="fw-bold mb-4"><?= e($certificate['module_title']) ?></h4>

        <p class="text-muted small mb-4" style="max-width: 600px; margin: 0 auto;">
            <?= e(truncate($certificate['module_description'], 220)) ?>
        </p>

        <div class="row mt-5 align-items-center">
            <div class="col-4 text-center">
                <div class="fw-bold"><?= format_date($certificate['issued_at']) ?></div>
                <div class="signature-line"></div>
                <small class="text-muted">Date de délivrance</small>
            </div>
            <div class="col-4 text-center">
                <img src="<?= $qrUrl ?>" alt="QR Code de vérification" width="100" height="100">
                <div class="signature-line"></div>
                <small class="text-muted">Scanner pour vérifier</small>
            </div>
            <div class="col-4 text-center">
                <div class="fw-bold" style="font-family: 'Playfair Display', serif;"><?= APP_NAME ?></div>
                <div class="signature-line"></div>
                <small class="text-muted">Plateforme certifiante</small>
            </div>
        </div>

        <p class="text-muted small mt-4 mb-0">
            Code de vérification : <code><?= e($certificate['code']) ?></code><br>
            Vérifiable sur : <?= e($verifyUrl) ?>
        </p>
    </div>
</div>
</body>
</html>
