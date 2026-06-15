<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
login_required();

$db = get_db();
$user_id = $_SESSION['user_id'];

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $date  = $_POST['date_reveil'] ?? '';
        $heure = $_POST['heure_reveil'] ?? '';
        if ($date && $heure) {
            $s = $db->prepare("INSERT INTO reveils (user_id, date_reveil, heure_reveil) VALUES (?,?,?)");
            $s->execute([$user_id, $date, $heure]);
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM reveils WHERE id=? AND user_id=?")->execute([$id, $user_id]);
    } elseif ($action === 'toggle') {
        $id = intval($_POST['id'] ?? 0);
        $db->prepare("UPDATE reveils SET actif = NOT actif WHERE id=? AND user_id=?")->execute([$id, $user_id]);
    } elseif ($action === 'stop_all') {
        $db->exec("UPDATE etats_actionneurs SET etat=0, declenche_par='aucun' WHERE declenche_par='reveil'");
        $db->prepare("UPDATE reveils SET declenche=0 WHERE user_id=?")->execute([$user_id]);
    }
    header('Location: /reveils.php'); exit;
}

$reveils = $db->prepare("SELECT * FROM reveils WHERE user_id=? ORDER BY date_reveil DESC, heure_reveil DESC");
$reveils->execute([$user_id]);
$reveils = $reveils->fetchAll();

// Vérifier si un réveil sonne actuellement
$sonne = $db->query("SELECT * FROM etats_actionneurs WHERE composant='buzzer' AND etat=1 AND declenche_par='reveil'")->fetch();

$page_title = 'Réveils';
include 'includes/header.php';
?>

<?php if ($sonne): ?>
<div class="reveil-banner" id="reveil-banner" role="alert">
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
  <span>⏰ Réveil en cours !</span>
  <form method="POST" style="display:inline">
    <input type="hidden" name="action" value="stop_all">
    <button type="submit" class="btn btn-light">Arrêter la sonnerie</button>
  </form>
</div>
<?php endif; ?>

<!-- Ajouter un réveil -->
<div class="card mb">
  <div class="card-header"><h2>Programmer un réveil</h2></div>
  <form method="POST" class="reveil-form">
    <input type="hidden" name="action" value="add">
    <div class="form-group">
      <label for="date_reveil">Date</label>
      <input type="date" id="date_reveil" name="date_reveil" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
    </div>
    <div class="form-group">
      <label for="heure_reveil">Heure</label>
      <input type="time" id="heure_reveil" name="heure_reveil" required value="07:00">
    </div>
    <button type="submit" class="btn btn-primary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Ajouter le réveil
    </button>
  </form>
</div>

<!-- Liste des réveils -->
<div class="card">
  <div class="card-header"><h2>Réveils programmés</h2></div>
  <?php if (empty($reveils)): ?>
    <p class="empty-msg">Aucun réveil programmé.</p>
  <?php else: ?>
  <div class="reveil-list">
    <?php foreach ($reveils as $r): ?>
    <div class="reveil-item <?= $r['actif'] ? 'reveil-on' : 'reveil-off' ?>">
      <div class="reveil-time">
        <span class="reveil-heure"><?= substr($r['heure_reveil'],0,5) ?></span>
        <span class="reveil-date"><?= $r['date_reveil'] ?></span>
      </div>
      <div class="reveil-status">
        <?php if ($r['declenche']): ?>
          <span class="badge badge-success">Déclenché</span>
        <?php elseif ($r['actif']): ?>
          <span class="badge badge-info">Actif</span>
        <?php else: ?>
          <span class="badge badge-secondary">Inactif</span>
        <?php endif; ?>
      </div>
      <div class="reveil-actions">
        <form method="POST" style="display:inline">
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button type="submit" class="btn btn-sm btn-secondary" title="<?= $r['actif'] ? 'Désactiver' : 'Activer' ?>">
            <?= $r['actif'] ? 'Désactiver' : 'Activer' ?>
          </button>
        </form>
        <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer ce réveil ?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
