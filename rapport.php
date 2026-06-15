<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/capteurs.php';
login_required();

$periode = $_GET['periode'] ?? 'nuit';
$nuit    = $_GET['nuit'] ?? null;

[$debut, $fin] = get_date_range($nuit);

$score_nuit  = $nuit ? calcul_score($debut, $fin) : null;
$score_3j    = calcul_score_periode(3);
$score_7j    = calcul_score_periode(7);
$score_30j   = calcul_score_periode(30);
$nuits       = get_nuits_th();

$page_title = 'Rapports';
include 'includes/header.php';
?>

<!-- Sélecteur période -->
<div class="period-tabs mb">
  <a href="?periode=nuit<?= $nuit?'&nuit='.$nuit:'' ?>" class="tab-btn <?= $periode==='nuit'?'active':'' ?>">Par nuit</a>
  <a href="?periode=3j"  class="tab-btn <?= $periode==='3j'?'active':'' ?>">3 jours</a>
  <a href="?periode=7j"  class="tab-btn <?= $periode==='7j'?'active':'' ?>">7 jours</a>
  <a href="?periode=30j" class="tab-btn <?= $periode==='30j'?'active':'' ?>">1 mois</a>
</div>

<?php if ($periode === 'nuit'): ?>
<!-- Sélecteur de nuit -->
<div class="night-sel mb">
  <form method="GET" class="night-form">
    <input type="hidden" name="periode" value="nuit">
    <label class="night-label">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      Sélectionner une nuit
    </label>
    <select name="nuit" class="select-ctrl" onchange="this.form.submit()">
      <option value="">-- Choisir --</option>
      <?php foreach ($nuits as $n): ?>
      <option value="<?= $n['nuit'] ?>" <?= $nuit===$n['nuit']?'selected':'' ?>>Nuit du <?= $n['nuit'] ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<?php if ($score_nuit): ?>
<?php $s = $score_nuit; ?>
<div class="rapport-header mb">
  <div class="rapport-score-big badge-<?= $s['couleur'] ?>"><?= $s['score'] ?>/100</div>
  <div class="rapport-meta">
    <h2>Rapport — Nuit du <?= $nuit ?></h2>
    <span class="badge badge-<?= $s['couleur'] ?>"><?= $s['niveau'] ?></span>
  </div>
</div>

<div class="grid-2 mb">
  <div class="card">
    <div class="card-header"><h2>Analyse détaillée</h2></div>
    <div class="rapport-stats">
      <?php if ($s['avg_temp']): ?>
      <div class="rstat">
        <span class="rstat-label">Température moyenne</span>
        <span class="rstat-val <?= $s['avg_temp']>22?'text-warning':($s['avg_temp']<16?'text-info':'text-success') ?>"><?= $s['avg_temp'] ?>°C</span>
        <span class="rstat-ref">Idéal : 18–20°C</span>
      </div>
      <?php endif; ?>
      <?php if ($s['avg_hum']): ?>
      <div class="rstat">
        <span class="rstat-label">Humidité moyenne</span>
        <span class="rstat-val <?= $s['avg_hum']>70||$s['avg_hum']<30?'text-warning':'text-success' ?>"><?= $s['avg_hum'] ?>%</span>
        <span class="rstat-ref">Idéal : 40–60%</span>
      </div>
      <?php endif; ?>
      <?php if ($s['avg_co2']): ?>
      <div class="rstat">
        <span class="rstat-label">CO2 moyen</span>
        <span class="rstat-val <?= $s['avg_co2']>1000?'text-danger':($s['avg_co2']>800?'text-warning':'text-success') ?>"><?= $s['avg_co2'] ?> ppm</span>
        <span class="rstat-ref">Idéal : &lt; 800 ppm</span>
      </div>
      <?php endif; ?>
      <?php if ($s['avg_son']): ?>
      <div class="rstat">
        <span class="rstat-label">Bruit moyen</span>
        <span class="rstat-val <?= $s['avg_son']>40?'text-warning':'text-success' ?>"><?= $s['avg_son'] ?></span>
        <span class="rstat-ref">Idéal : &lt; 40</span>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h2>Alertes de la nuit</h2></div>
    <ul class="alert-list">
      <?php foreach ($s['alertes'] as $a): ?>
      <li class="alert-item"><span class="dot dot-<?= $a['niveau'] ?>"></span><?= htmlspecialchars($a['msg']) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<div class="card mb">
  <div class="card-header"><h2>Conseils personnalisés pour cette nuit</h2></div>
  <ul class="conseils-list">
    <?php foreach ($s['recommandations'] as $i => $r): ?>
    <li class="conseil-item">
      <span class="conseil-num"><?= $i+1 ?></span>
      <span><?= htmlspecialchars($r) ?></span>
    </li>
    <?php endforeach; ?>
    <?php if ($s['score'] >= 80): ?>
    <li class="conseil-item conseil-bonus">
      <span class="conseil-num">✓</span>
      <span>Excellente nuit ! Maintenez ces conditions pour un sommeil optimal.</span>
    </li>
    <?php endif; ?>
  </ul>
</div>

<!-- Graphiques de la nuit -->
<div class="card mb">
  <div class="card-header"><h2>Évolution sur la nuit</h2></div>
  <div class="chart-wrap chart-lg">
    <canvas id="chart-nuit"></canvas>
  </div>
</div>

<?php else: ?>
<div class="card"><p class="empty-msg">Sélectionnez une nuit pour voir le rapport détaillé.</p></div>
<?php endif; ?>

<?php elseif ($periode === '3j'): ?>
<?php $s = $score_3j; ?>

<?php elseif ($periode === '7j'): ?>
<?php $s = $score_7j; ?>

<?php elseif ($periode === '30j'): ?>
<?php $s = $score_30j; ?>

<?php endif; ?>

<?php if (in_array($periode, ['3j','7j','30j'])): ?>
<?php
$label_periode = $periode === '3j' ? '3 derniers jours' : ($periode === '7j' ? '7 derniers jours' : '30 derniers jours');
$jours_int = intval($periode);
?>

<div class="rapport-header mb">
  <div class="rapport-score-big badge-<?= $s['couleur'] ?>"><?= $s['score'] ?>/100</div>
  <div class="rapport-meta">
    <h2>Rapport — <?= $label_periode ?></h2>
    <span class="badge badge-<?= $s['couleur'] ?>"><?= $s['niveau'] ?></span>
  </div>
</div>

<div class="grid-2 mb">
  <div class="card">
    <div class="card-header"><h2>Moyennes sur la période</h2></div>
    <div class="rapport-stats">
      <?php if ($s['avg_temp']): ?><div class="rstat"><span class="rstat-label">Température moy.</span><span class="rstat-val"><?= $s['avg_temp'] ?>°C</span></div><?php endif; ?>
      <?php if ($s['avg_hum']):  ?><div class="rstat"><span class="rstat-label">Humidité moy.</span><span class="rstat-val"><?= $s['avg_hum'] ?>%</span></div><?php endif; ?>
      <?php if ($s['avg_co2']): ?><div class="rstat"><span class="rstat-label">CO2 moyen</span><span class="rstat-val"><?= $s['avg_co2'] ?> ppm</span></div><?php endif; ?>
      <?php if ($s['avg_son']):  ?><div class="rstat"><span class="rstat-label">Bruit moyen</span><span class="rstat-val"><?= $s['avg_son'] ?></span></div><?php endif; ?>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><h2>Conseils sur la période</h2></div>
    <ul class="conseils-list">
      <?php foreach ($s['recommandations'] as $i => $r): ?>
      <li class="conseil-item"><span class="conseil-num"><?= $i+1 ?></span><span><?= htmlspecialchars($r) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
<?php endif; ?>

<?php
$extra_js = '';
if ($periode === 'nuit' && $nuit && $score_nuit) {
    $extra_js = <<<JS
const isDark=document.documentElement.getAttribute('data-theme')==='dark';
const gc=isDark?'rgba(255,255,255,0.08)':'rgba(0,0,0,0.06)';
const tc=isDark?'#94a3b8':'#64748b';
function fmt(ts){if(!ts)return'';const d=new Date(ts);return`\${String(d.getHours()).padStart(2,'0')}:\${String(d.getMinutes()).padStart(2,'0')}`;}
(async()=>{
  const nuit='{$nuit}';
  const [th,air,son]=await Promise.all([
    fetch(`/api.php?action=chart_th&nuit=\${nuit}`).then(r=>r.json()),
    fetch(`/api.php?action=chart_air&nuit=\${nuit}`).then(r=>r.json()),
    fetch(`/api.php?action=chart_son&nuit=\${nuit}`).then(r=>r.json()),
  ]);
  const allTimes=[...new Set([...th.map(r=>r.horodatage),...air.map(r=>r.timestamp),...son.map(r=>r.timestamp)])].sort();
  function mapD(arr,kt,kv){const m={};arr.forEach(r=>m[r[kt]]=r[kv]);return allTimes.map(t=>m[t]??null);}
  new Chart(document.getElementById('chart-nuit'),{
    type:'line',
    data:{labels:allTimes.map(fmt),datasets:[
      {label:'Température (°C)',data:mapD(th,'horodatage','temperature'),borderColor:'#3b82f6',backgroundColor:'rgba(59,130,246,0.08)',tension:0.4,pointRadius:2,fill:true,spanGaps:true},
      {label:'Humidité (%)',    data:mapD(th,'horodatage','humidite'),   borderColor:'#06b6d4',tension:0.4,pointRadius:2,spanGaps:true,yAxisID:'y2'},
      {label:'CO2 (ppm)',       data:mapD(air,'timestamp','CO2'),        borderColor:'#f59e0b',tension:0.4,pointRadius:2,spanGaps:true,yAxisID:'y3'},
    ]},
    options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},
      plugins:{legend:{labels:{color:tc,boxWidth:12}}},
      scales:{x:{ticks:{color:tc,maxTicksLimit:12,font:{size:10}},grid:{color:gc}},
        y:{ticks:{color:'#3b82f6',font:{size:10}},grid:{color:gc}},
        y2:{position:'right',ticks:{color:'#06b6d4',font:{size:10}},grid:{display:false}},
        y3:{position:'right',ticks:{color:'#f59e0b',font:{size:10}},grid:{display:false}},
      }
    }
  });
})();
JS;
}
include 'includes/footer.php';
?>
