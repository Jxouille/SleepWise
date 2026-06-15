<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/capteurs.php';
login_required();
appliquer_regles();

$page_title  = 'Dashboard';
$last_th     = get_last_temp_hum();
$last_air    = get_last_air();
$last_light  = get_last_light();
$last_sound  = get_last_sound();
$actionneurs = get_actionneurs();
$sommeil     = calcul_score();
$nuits       = get_nuits_th();

// Vérifier si réveil actif
$db = get_db();
$reveil_actif = $db->query("SELECT * FROM etats_actionneurs WHERE composant='buzzer' AND etat=1 AND declenche_par='reveil'")->fetch();

include 'includes/header.php';
?>

<?php if ($reveil_actif): ?>
<div class="reveil-banner" id="reveil-banner" role="alert">
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
  <span>⏰ Réveil en cours !</span>
  <button class="btn btn-light" onclick="stopReveil()">Arrêter la sonnerie</button>
</div>
<?php endif; ?>

<!-- Score -->
<div class="grid-2 mb">
  <div class="card card-score">
    <div class="card-header"><h2>Score de sommeil</h2><span class="badge badge-<?= $sommeil['couleur'] ?>" id="score-niveau"><?= $sommeil['niveau'] ?></span></div>
    <div class="score-ring-wrap">
      <svg class="score-ring" viewBox="0 0 120 120">
        <circle cx="60" cy="60" r="50" fill="none" stroke="var(--color-border)" stroke-width="10"/>
        <circle cx="60" cy="60" r="50" fill="none" stroke="var(--color-<?= $sommeil['couleur'] ?>)" stroke-width="10"
          stroke-dasharray="<?= round($sommeil['score']/100*314,1) ?> 314"
          stroke-dashoffset="78.5" stroke-linecap="round" id="score-ring-fill"
          style="transition:stroke-dasharray 0.8s ease"/>
        <text x="60" y="54" text-anchor="middle" class="ring-num" id="score-value"><?= $sommeil['score'] ?></text>
        <text x="60" y="71" text-anchor="middle" class="ring-label">/100</text>
      </svg>
    </div>
    <div class="score-stats">
      <?php if ($sommeil['avg_temp']): ?><div class="stat"><span class="stat-l">Temp. moy.</span><span class="stat-v"><?= $sommeil['avg_temp'] ?>°C</span></div><?php endif; ?>
      <?php if ($sommeil['avg_hum']):  ?><div class="stat"><span class="stat-l">Humidité moy.</span><span class="stat-v"><?= $sommeil['avg_hum'] ?>%</span></div><?php endif; ?>
      <?php if ($sommeil['avg_co2']): ?><div class="stat"><span class="stat-l">CO2 moy.</span><span class="stat-v"><?= $sommeil['avg_co2'] ?> ppm</span></div><?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h2>Alertes actives</h2></div>
    <ul class="alert-list" id="alert-list">
      <?php foreach ($sommeil['alertes'] as $a): ?>
      <li class="alert-item"><span class="dot dot-<?= $a['niveau'] ?>"></span><?= htmlspecialchars($a['msg']) ?></li>
      <?php endforeach; ?>
    </ul>
    <div class="card-sep"></div>
    <h3 class="reco-title">Recommandations</h3>
    <ul class="reco-list" id="reco-list">
      <?php foreach ($sommeil['recommandations'] as $r): ?>
      <li><?= htmlspecialchars($r) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<!-- Cards capteurs live -->
<div class="grid-4 mb">
  <a href="/capteur_temp.php" class="card card-capteur">
    <div class="cap-icon cap-temp"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/></svg></div>
    <p class="cap-label">Température</p>
    <p class="cap-val" id="live-temp"><?= $last_th ? $last_th['temperature'].'°C' : '—' ?></p>
    <p class="cap-sub" id="live-hum"><?= $last_th ? 'Humidité : '.$last_th['humidite'].'%' : '' ?></p>
  </a>
  <a href="/capteur_air.php" class="card card-capteur">
    <div class="cap-icon cap-air"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"/></svg></div>
    <p class="cap-label">Air ambiant</p>
    <p class="cap-val" id="live-co2"><?= $last_air ? $last_air['CO2'].' ppm' : '—' ?></p>
    <p class="cap-sub">CO2</p>
  </a>
  <a href="/capteur_lumiere.php" class="card card-capteur">
    <div class="cap-icon cap-light"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg></div>
    <p class="cap-label">Luminosité</p>
    <p class="cap-val" id="live-lum"><?= $last_light ? $last_light['light_value'].' lux' : '—' ?></p>
    <p class="cap-sub" id="live-day"><?= $last_light ? $last_light['day_status'] : '' ?></p>
  </a>
  <a href="/capteur_son.php" class="card card-capteur">
    <div class="cap-icon cap-son"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg></div>
    <p class="cap-label">Son</p>
    <p class="cap-val" id="live-son"><?= $last_sound ? $last_sound['raw'] : '—' ?></p>
    <p class="cap-sub">Niveau brut</p>
  </a>
</div>

<!-- Actionneurs -->
<div class="card mb">
  <div class="card-header"><h2>Actionneurs</h2></div>
  <div class="act-row" id="act-row">
    <?php foreach ($actionneurs as $a): ?>
    <div class="act <?= $a['etat'] ? 'act-on' : 'act-off' ?>">
      <div class="act-led <?= $a['etat'] ? 'led-on' : 'led-off' ?>"></div>
      <span class="act-name"><?= ucwords(str_replace('_',' ',$a['composant'])) ?></span>
      <span class="act-state"><?= $a['etat'] ? 'ON' : 'OFF' ?></span>
      <?php if ($a['declenche_par'] !== 'aucun'): ?><span class="act-reason"><?= str_replace('_',' ',$a['declenche_par']) ?></span><?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Graphique principal -->
<div class="card mb">
  <div class="card-header">
    <h2>Graphique principal</h2>
    <div class="chart-controls">
      <?php if ($nuits): ?>
      <select id="nuit-select" class="select-ctrl">
        <option value="">Temps réel</option>
        <?php foreach ($nuits as $n): ?>
        <option value="<?= $n['nuit'] ?>">Nuit du <?= $n['nuit'] ?></option>
        <?php endforeach; ?>
      </select>
      <?php endif; ?>
    </div>
  </div>
  <div class="ds-toggles">
    <button class="ds-btn active" data-ds="temperature" style="--c:#3b82f6">Température</button>
    <button class="ds-btn active" data-ds="humidite"    style="--c:#06b6d4">Humidité</button>
    <button class="ds-btn active" data-ds="co2"         style="--c:#f59e0b">CO2</button>
    <button class="ds-btn active" data-ds="son"         style="--c:#10b981">Son</button>
  </div>
  <div class="chart-wrap chart-lg">
    <canvas id="main-chart"></canvas>
  </div>
</div>

<p class="refresh-info" id="refresh-info">Actualisation dans 10s</p>

<?php
$extra_js = <<<'JS'
const isDark = document.documentElement.getAttribute('data-theme')==='dark';
const gc = isDark?'rgba(255,255,255,0.08)':'rgba(0,0,0,0.06)';
const tc = isDark?'#94a3b8':'#64748b';
let mainChart=null, activeDS=new Set(['temperature','humidite','co2','son']);

function fmt(ts){if(!ts)return'';const d=new Date(ts);return`${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;}

async function loadMainChart(nuit){
  const qs=nuit?`&nuit=${nuit}`:'';
  const [th,air,son]=await Promise.all([
    fetch(`/api.php?action=chart_th${qs}`).then(r=>r.json()),
    fetch(`/api.php?action=chart_air${qs}`).then(r=>r.json()),
    fetch(`/api.php?action=chart_son${qs}`).then(r=>r.json()),
  ]);
  const allTimes=[...new Set([...th.map(r=>r.horodatage),...air.map(r=>r.timestamp),...son.map(r=>r.timestamp)])].sort();
  function mapD(arr,kt,kv){const m={};arr.forEach(r=>m[r[kt]]=r[kv]);return allTimes.map(t=>m[t]??null);}
  const datasets=[
    {id:'temperature',label:'Température (°C)',data:mapD(th,'horodatage','temperature'),borderColor:'#3b82f6',backgroundColor:'rgba(59,130,246,0.06)',tension:0.4,pointRadius:2,fill:true,spanGaps:true},
    {id:'humidite',   label:'Humidité (%)',     data:mapD(th,'horodatage','humidite'),   borderColor:'#06b6d4',backgroundColor:'rgba(6,182,212,0.06)',  tension:0.4,pointRadius:2,fill:false,spanGaps:true,yAxisID:'y2'},
    {id:'co2',        label:'CO2 (ppm)',         data:mapD(air,'timestamp','CO2'),         borderColor:'#f59e0b',backgroundColor:'rgba(245,158,11,0.06)', tension:0.4,pointRadius:2,fill:false,spanGaps:true,yAxisID:'y3'},
    {id:'son',        label:'Son (brut)',         data:mapD(son,'timestamp','raw'),         borderColor:'#10b981',backgroundColor:'rgba(16,185,129,0.06)',tension:0.4,pointRadius:2,fill:false,spanGaps:true,yAxisID:'y4'},
  ].filter(d=>activeDS.has(d.id));
  if(mainChart)mainChart.destroy();
  mainChart=new Chart(document.getElementById('main-chart'),{
    type:'line',data:{labels:allTimes.map(fmt),datasets},
    options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},
      plugins:{legend:{labels:{color:tc,boxWidth:12,font:{size:11}}}},
      scales:{x:{ticks:{color:tc,maxTicksLimit:14,font:{size:10}},grid:{color:gc}},
        y:{ticks:{color:'#3b82f6',font:{size:10}},grid:{color:gc}},
        y2:{position:'right',ticks:{color:'#06b6d4',font:{size:10}},grid:{display:false},display:activeDS.has('humidite')},
        y3:{position:'right',ticks:{color:'#f59e0b',font:{size:10}},grid:{display:false},display:activeDS.has('co2')},
        y4:{position:'right',ticks:{color:'#10b981',font:{size:10}},grid:{display:false},display:activeDS.has('son')},
      }
    }
  });
}

const nuitSel=document.getElementById('nuit-select');
if(nuitSel)nuitSel.addEventListener('change',()=>loadMainChart(nuitSel.value));
document.querySelectorAll('.ds-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    const ds=btn.dataset.ds;
    if(activeDS.has(ds)){activeDS.delete(ds);btn.classList.remove('active');}
    else{activeDS.add(ds);btn.classList.add('active');}
    loadMainChart(nuitSel?nuitSel.value:'');
  });
});
loadMainChart('');

// Refresh auto
let countdown=10;
const info=document.getElementById('refresh-info');
setInterval(async()=>{
  countdown--;
  if(info)info.textContent=`Actualisation dans ${countdown}s`;
  if(countdown<=0){
    countdown=10;
    try{
      const d=await fetch('/api.php?action=refresh').then(r=>r.json());
      if(d.last_th){document.getElementById('live-temp').textContent=d.last_th.temperature+'°C';document.getElementById('live-hum').textContent='Humidité : '+d.last_th.humidite+'%';}
      if(d.last_air)document.getElementById('live-co2').textContent=d.last_air.CO2+' ppm';
      if(d.last_light){document.getElementById('live-lum').textContent=d.last_light.light_value+' lux';document.getElementById('live-day').textContent=d.last_light.day_status;}
      if(d.last_sound)document.getElementById('live-son').textContent=d.last_sound.raw;
      if(d.sommeil){document.getElementById('score-value').textContent=d.sommeil.score;document.getElementById('score-niveau').textContent=d.sommeil.niveau;}
      if(d.actionneurs){
        document.getElementById('act-row').innerHTML=d.actionneurs.map(a=>`
          <div class="act ${a.etat?'act-on':'act-off'}">
            <div class="act-led ${a.etat?'led-on':'led-off'}"></div>
            <span class="act-name">${a.composant.replace(/_/g,' ')}</span>
            <span class="act-state">${a.etat?'ON':'OFF'}</span>
            ${a.declenche_par!=='aucun'?`<span class="act-reason">${a.declenche_par.replace(/_/g,' ')}</span>`:''}
          </div>`).join('');
      }
      if(info)info.textContent=`Mis à jour à ${new Date().toLocaleTimeString('fr-FR')}`;
    }catch(e){}
  }
},1000);

function stopReveil(){
  fetch('/api.php?action=stop_reveil').then(()=>{
    const b=document.getElementById('reveil-banner');
    if(b)b.remove();
  });
}
JS;
include 'includes/footer.php';
?>
