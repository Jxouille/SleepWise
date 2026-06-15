<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!empty($_SESSION['user_id'])) { header('Location: /dashboard.php'); exit; }

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    if (!$username || !$email || !$password) $error = 'Tous les champs sont obligatoires.';
    elseif ($password !== $confirm) $error = 'Les mots de passe ne correspondent pas.';
    elseif (strlen($password) < 6)  $error = 'Mot de passe trop court (6 caractères minimum).';
    else {
        [$ok, $msg] = register_user($username, $email, $password);
        if ($ok) { $success = $msg; }
        else     { $error = $msg; }
    }
}
$theme = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="<?= $theme ?>">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Créer un compte — SleepWise</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <svg width="52" height="52" viewBox="0 0 32 32"><circle cx="16" cy="16" r="14" fill="var(--color-primary)" opacity="0.15"/><circle cx="16" cy="16" r="10" fill="none" stroke="var(--color-primary)" stroke-width="2"/><line x1="16" y1="8" x2="16" y2="16" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round"/><line x1="16" y1="16" x2="21" y2="19" stroke="var(--color-accent)" stroke-width="2" stroke-linecap="round"/><circle cx="16" cy="16" r="2" fill="var(--color-primary)"/></svg>
      <h1>Créer un compte</h1>
      <p class="auth-sub">SleepWise by DeVolt</p>
    </div>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="/index.php">Se connecter</a></div><?php endif; ?>
    <form method="POST">
      <div class="form-group"><label>Nom d'utilisateur</label><input type="text" name="username" required placeholder="Votre prénom"></div>
      <div class="form-group"><label>Adresse email</label><input type="email" name="email" required placeholder="exemple@isep.fr"></div>
      <div class="form-group"><label>Mot de passe</label><input type="password" name="password" required placeholder="6 caractères minimum" minlength="6"></div>
      <div class="form-group"><label>Confirmer</label><input type="password" name="confirm_password" required placeholder="••••••••"></div>
      <button type="submit" class="btn btn-primary btn-full">Créer le compte</button>
    </form>
    <p class="auth-switch">Déjà un compte ? <a href="/index.php">Se connecter</a></p>
  </div>
</div>
<script src="/js/app.js"></script>
</body>
</html>
