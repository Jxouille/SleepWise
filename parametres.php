<?php session_start();require_once 'includes/config.php';require_once 'includes/auth.php';login_required();
$page_title='Paramètres';include 'includes/header.php';?>
<div class="settings-grid">
  <div class="card"><div class="card-header"><h2>Apparence</h2></div>
    <div class="settings-row"><div><p class="settings-label">Mode sombre</p><p class="settings-hint">Réduit la fatigue oculaire la nuit</p></div>
    <button class="theme-btn-toggle" id="theme-toggle"><svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg><svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg><span id="theme-label">Activer</span></button></div>
  </div>
  <div class="card"><div class="card-header"><h2>Compte</h2></div>
    <div class="si-row"><span class="settings-label">Utilisateur</span><span class="badge badge-info"><?=htmlspecialchars($_SESSION['username']??'')?></span></div>
    <div class="si-row"><span class="settings-label">Rôle</span><span>Utilisateur standard</span></div>
    <a href="/logout.php" class="btn btn-danger mt">Se déconnecter</a>
  </div>
  <div class="card"><div class="card-header"><h2>À propos</h2></div>
    <div class="about-block"><svg width="40" height="40" viewBox="0 0 32 32"><circle cx="16" cy="16" r="14" fill="var(--color-primary)" opacity="0.15"/><circle cx="16" cy="16" r="10" fill="none" stroke="var(--color-primary)" stroke-width="2"/><line x1="16" y1="8" x2="16" y2="16" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round"/><line x1="16" y1="16" x2="21" y2="19" stroke="var(--color-accent)" stroke-width="2" stroke-linecap="round"/><circle cx="16" cy="16" r="2" fill="var(--color-primary)"/></svg>
    <div><p class="about-name">SleepWise</p><p class="about-sub">Version 1.0 — ISEP A1 2026</p><p class="about-team">Développé par <strong>DeVolt</strong></p></div></div>
    <a href="/cgu.php" class="btn btn-secondary mt">Consulter les CGU</a>
  </div>
  <div class="card"><div class="card-header"><h2>Données & Capteurs</h2></div>
    <div class="si-row"><span class="settings-label">Serveur BDD</span><span>178.33.122.21:3306</span></div>
    <div class="si-row"><span class="settings-label">Base de données</span><span>hangardb_axst62997</span></div>
    <div class="si-row"><span class="settings-label">Actualisation</span><span>Toutes les 10 secondes</span></div>
    <div class="si-row"><span class="settings-label">Capteurs actifs</span><span>4</span></div>
  </div>
</div>
<?php include 'includes/footer.php';?>
