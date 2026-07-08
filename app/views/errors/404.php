<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page introuvable — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<div class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height:100vh;">
    <i class="fa-solid fa-circle-exclamation fa-5x text-primary mb-4"></i>
    <h1 class="fw-bold">404</h1>
    <p class="text-muted mb-4">Oups ! La page que vous recherchez n'existe pas.</p>
    <a href="<?= url('') ?>" class="btn btn-primary"><i class="fa-solid fa-house me-2"></i>Retour à l'accueil</a>
</div>
</body>
</html>
