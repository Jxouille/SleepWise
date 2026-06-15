<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/capteurs.php';
login_required();

$nuit = $_GET['nuit'] ?? null;
[$debut,$fin] = get_date_range($nuit);
$data  = get_temp_hum(100,$debut,$fin);
$chart = get_temp_hum_chart(200,$debut,$fin);
$last  = get_last_temp_hum();
$nuits = get_nuits_th();

$page_title = 'Température & Humidité';
include 'includes/header.php';
?>

<div class="night-sel mb">
  <form method="GET" class="night-form">
    <label class="night-label"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Sélectionner une nuit</label>
    <select name="nuit" class="select-ctrl" onchange="this.form.submit()">
      <option value="">Données récentes</option>
      <?php foreach ($nuits as $n): ?><option value="<?= $n['nuit'] ?>" <?= $nuit===$n['nuit']?'selected':'' ?>>Nuit du <?= $n['nuit'] ?></option><?php endforeach; ?>
    </select>
    <?php if ($nuit): ?><a href="/capteur_temp.php" class="btn btn-secondary btn-sm">Réinitialiser</a><?php endif; ?>
  </form>
</div>

<div class="grid-2 mb">
  <div class="card card-stat">
    <p class="sl">Température actuelle</p>
    <p class="sb sc-temp"><?= $last ? $last['temperature'].'°C' : '—' ?></p>
    <p class="sh">Idéal pour le sommeil : 18–20°C</p>
    <?php if ($last): ?><div class="bar-w"><div class="bar bar-temp" style="width:<?= min($last['temperature']/40*100,100) ?>%"></div></div><?php endif; ?>
  </div>
  <div class="card card-stat">
    <p class="sl">Humidité actuelle</p>
    <p class="sb sc-hum"><?= $last ? $last['humidite'].'%' : '—' ?></p>
    <p class="sh">Idéal pour le sommeil : 40–60%</p>
    <?php if ($last): ?><div class="bar-w"><div class="bar bar-hum" style="width:<?= min($last['humidite'],100) ?>%"></div></div><?php endif; ?>
  </div>
</div>

<div class="card mb">
  <div class="card-header">
    <h2>Évolution<?= $nuit ? ' — nuit du '.$nuit : '' ?></h2>
    <div class="legend"><span class="ldot" style="background:#3b82f6"></span>Température <span class="ldot" style="background:#06b6d4"></span>Humidité</div>
  </div>
  <div class="chart-wrap chart-lg"><canvas id="chart-th"></canvas></div>
</div>

<div class="card">
  <div class="card-header"><h2>Historique</h2></div>
  <div class="tbl-wrap">
    <table><thead><tr><th>Date / Heure</th><th>Température (°C)</th><th>Humidité (%)</th><th>État</th></tr></thead>
    <tbody>
      <?php foreach ($data as $row): ?>
      <tr>
        <td><?= $row['horodatage'] ?></td>
        <td class="<?= $row['temperature']>26?'cd':($row['temperature']>22?'cw':($row['temperature']<16?'ci':'')) ?>"><?= $row['temperature'] ?></td>
        <td class="<?= $row['humidite']>70?'cd':($row['humidite']<30?'cw':'') ?>"><?= $row['humidite'] ?></td>
        <td><?php if ($row['temperature']>26||$row['humidite']>70): ?><span class="badge badge-danger">Alerte</span><?php elseif ($row['temperature']>22): ?><span class="badge badge-warning">Attention</span><?php else: ?><span class="badge badge-success">OK</span><?php endif; ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody></table>
  </div>
</div>

<?php
$chart_json = json_encode($chart);
$extra_js = <<<JS
const raw={$chart_json};
const isDark=document.documentElement.getAttribute('data-theme')==='dark';
const gc=isDark?'rgba(255,255,255,0.08)':'rgba(0,0,0,0.06)';
const tc=isDark?'#94a3b8':'#64748b';
function fmt(ts){if(!ts)return'';const d=new Date(ts);return`\${d.getFullYear()}-\${String(d.getMonth()+1).padStart(2,'0')}-\${String(d.getDate()).padStart(2,'0')} \${String(d.getHours()).padStart(2,'0')}:\${String(d.getMinutes()).padStart(2,'0')}`;}
new Chart(document.getElementById('chart-th'),{type:'line',data:{labels:raw.map(r=>fmt(r.horodatage)),datasets:[
  {label:'Température (°C)',data:raw.map(r=>r.temperature),borderColor:'#3b82f6',backgroundColor:'rgba(59,130,246,0.08)',tension:0.4,pointRadius:2,fill:true},
  {label:'Humidité (%)',    data:raw.map(r=>r.humidite),   borderColor:'#06b6d4',backgroundColor:'rgba(6,182,212,0.08)', tension:0.4,pointRadius:2,fill:false,yAxisID:'y2'}
]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},
  plugins:{legend:{labels:{color:tc,boxWidth:12}}},
  scales:{x:{ticks:{color:tc,maxTicksLimit:12,maxRotation:45,font:{size:10}},grid:{color:gc}},
    y:{ticks:{color:tc},grid:{color:gc},title:{display:true,text:'°C',color:tc}},
    y2:{position:'right',ticks:{color:'#06b6d4'},grid:{display:false},title:{display:true,text:'%',color:'#06b6d4'}}}
}});
JS;
include 'includes/footer.php';
?>
