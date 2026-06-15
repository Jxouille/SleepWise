<?php session_start();require_once 'includes/config.php';require_once 'includes/auth.php';
$page_title='CGU';
if(!empty($_SESSION['user_id'])){include 'includes/header.php';}else{$theme=$_COOKIE['theme']??'light';echo "<!DOCTYPE html><html lang='fr' data-theme='$theme'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1.0'><title>CGU — SleepWise</title><link rel='stylesheet' href='/css/style.css'></head><body><div class='auth-wrapper' style='padding:2rem'>";}
?>
<div class="cgu-container">
  <div class="card">
    <div class="card-header"><h2>Conditions générales d'utilisation</h2><span class="badge badge-info">Version 1.0 — Juin 2026</span></div>
    <div class="cgu-content">
      <section><h3>1. Présentation</h3><p>SleepWise est une application de surveillance de la qualité du sommeil développée dans le cadre d'un projet de fin de première année à l'ISEP par l'équipe <strong>DeVolt</strong>.</p></section>
      <section><h3>2. Accès</h3><p>L'accès est réservé aux personnes disposant d'un compte valide. L'utilisateur est responsable de la confidentialité de ses identifiants.</p></section>
      <section><h3>3. Données collectées</h3><p>Données de compte (nom, email, mot de passe hashé) et données environnementales (température, humidité, CO2, CH4, VOC, luminosité, son, respiration) stockées sur un serveur MySQL.</p></section>
      <section><h3>4. Finalité</h3><p>Ces données sont utilisées exclusivement dans le cadre du projet pédagogique IoT ISEP pour afficher des mesures, calculer un score de sommeil, générer des alertes et contrôler des actionneurs.</p></section>
      <section><h3>5. Sécurité</h3><p>Les mots de passe sont stockés hashés (SHA-256). SleepWise est un projet pédagogique et ne prétend pas offrir un niveau de sécurité professionnel.</p></section>
      <section><h3>6. Limitations</h3><p>Les recommandations sont générées automatiquement et ne constituent en aucun cas un avis médical.</p></section>
      <section><h3>7. Équipe</h3><p>SleepWise est développé par <strong>DeVolt</strong>, étudiants de première année du cycle ingénieur de l'ISEP, promotion 2025–2026.</p></section>
    </div>
    <div class="cgu-footer">
      <p>Dernière mise à jour : Juin 2026</p>
      <?php if(!empty($_SESSION['user_id'])):?><a href="/dashboard.php" class="btn btn-primary">Retour au dashboard</a><?php else:?><a href="/index.php" class="btn btn-primary">Se connecter</a><?php endif;?>
    </div>
  </div>
</div>
<?php
if(!empty($_SESSION['user_id'])){include 'includes/footer.php';}
else{echo "</div><script src='/js/app.js'></script></body></html>";}
