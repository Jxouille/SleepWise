<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: /dashboard.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = login_user($email, $password);
    if ($user) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: /dashboard.php'); exit;
    }
    $error = 'Email ou mot de passe incorrect.';
}
$theme = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="<?= $theme ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion — SleepWise</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <svg width="52" height="52" viewBox="0 0 32 32">
        <circle cx="16" cy="16" r="14" fill="var(--color-primary)" opacity="0.15"/>
        <circle cx="16" cy="16" r="10" fill="none" stroke="var(--color-primary)" stroke-width="2"/>
        <line x1="16" y1="8" x2="16" y2="16" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round"/>
        <line x1="16" y1="16" x2="21" y2="19" stroke="var(--color-accent)" stroke-width="2" stroke-linecap="round"/>
        <circle cx="16" cy="16" r="2" fill="var(--color-primary)"/>
      </svg>
      <h1>SleepWise</h1>
      <p class="auth-sub">by DeVolt</p>
    </div>
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label for="email">Adresse email</label>
        <input type="email" id="email" name="email" required placeholder="exemple@isep.fr">
      </div>
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn btn-primary btn-full">Se connecter</button>
    </form>
    <p class="auth-switch">Pas encore de compte ? <a href="/register.php">Créer un compte</a></p>
    <p class="auth-switch"><a href="/cgu.php">Conditions générales d'utilisation</a></p>
    <div style="text-align:center;margin-top:1rem">
      <button class="theme-btn-small" id="theme-toggle" aria-label="Mode sombre">
        <svg class="icon-sun" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        <svg class="icon-moon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>
    </div>
  </div>
</div>
<script src="/js/app.js"></script>
</body>
</html>
