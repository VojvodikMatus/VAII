<?php
// Rozloženie stránky – vytvorené s pomocou AI (Claude), upravené autorom
/** @var string $contentHTML */
/** @var \Framework\Auth\AppUser $user */
/** @var \Framework\Support\LinkGenerator $link */
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= App\Configuration::APP_NAME ?></title>
    <link rel="stylesheet" href="<?= $link->asset('css/style.css') ?>">
    <script src="<?= $link->asset('js/script.js') ?>" defer></script>
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= $link->url('home.index') ?>"><span class="logo" aria-hidden="true">LOGO</span>MediTermín</a>
        <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-label="Zobraziť menu">
        <label for="nav-toggle" class="nav-button">Menu</label>
        <nav class="site-nav" aria-label="Hlavná navigácia">
            <ul>
                <li><a href="<?= $link->url('doctor.index') ?>">Lekári</a></li>
                <li><a href="#">Moje termíny</a></li>
                <li><a href="#">Profil</a></li>
                <li><a href="<?= $link->url('specialization.index') ?>">Špecializácie</a></li>
            </ul>
            <div class="nav-auth">
                <a class="btn" href="#">Prihlásiť sa</a>
                <a class="btn btn-primary" href="#">Registrovať sa</a>
            </div>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <?= $contentHTML ?>
    </div>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <p>© 2026 MediTermín</p>
        <ul>
            <li><a href="#">FAQ</a></li>
            <li><a href="#">Kontakt</a></li>
            <li><a href="#">Časté vyhľadávanie</a></li>
        </ul>
    </div>
</footer>
</body>
</html>