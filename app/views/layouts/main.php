<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? e($title) . ' — ' : '' ?><?= APP_NAME ?></title>
	
	<!--favicon-->
	<link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
	<link rel="shortcut icon" href="/favicon/favicon.ico" />
	<link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
	<meta name="apple-mobile-web-app-title" content="SPACELearn" />
	<link rel="manifest" href="/favicon/site.webmanifest" />
	<!--favicon-->
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Style personnalisé -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">

    <?php if (!empty($extraCss)) foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="<?= $css ?>">
    <?php endforeach; ?>
</head>
<body data-base-url="<?= BASE_URL ?>">

<div class="nl-wrapper">
    <?php require APP_PATH . '/views/partials/_sidebar.php'; ?>

    <div class="nl-content">
        <?php require APP_PATH . '/views/partials/_topbar.php'; ?>

        <main class="nl-main">
            <?php display_flash(); ?>
            <?php require $viewFile; ?>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($extraJs)) foreach ($extraJs as $js): ?>
    <script src="<?= $js ?>"></script>
<?php endforeach; ?>
<script src="<?= asset('js/main.js') ?>"></script>
<?php if (!empty($inlineJs)): ?>
<script><?= $inlineJs ?></script>
<?php endif; ?>
</body>
</html>
