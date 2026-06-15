<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/capteurs.php';
login_required();
$nuit=$_GET['nuit']??null;[$debut,$fin]=get_date_range($nuit);
$data=get_air(100,$debut,$fin);$chart=get_air_chart(200,$debut,$fin);$last=get_last_air();$nuits=get_nuits_air();
$page_title='Air ambiant';include 'includes/header.php';
?>
<div class="night-sel mb"><form method="GET" class="night-form"><label class="night-label">Sélectionner une nuit</label><select name="nuit" class="select-ctrl" onchange="this.form.submit()"><option value="">Données récentes</option><?php foreach($nuits as $n):?><option value="<?=$n['nuit']?>" <?=$nuit===$n['nuit']?'selected':''?>>Nuit du <?=$n['nuit']?></option><?php endforeach;?></select><?php if($nuit):?><a href="/capteur_air.php" class="btn btn-secondary btn-sm">Réinitialiser</a><?php endif;?></form></div>
<div class="grid-3 mb">
  <div class="card card-stat"><p class="sl">CO2 actuel</p><p class="sb <?=($last&&$last['CO2']>1500)?'sc-d':(($last&&$last['CO2']>1000)?'sc-w':'sc-ok')?>"><?=$last?$last['CO2'].' ppm':'—'?></p><p class="sh">Seuil : 1000 ppm | Critique : 1500 ppm</p></div>
  <div class="card card-stat"><p class="sl">CH4 actuel</p><p class="sb <?=($last&&$last['CH4']>500)?'sc-d':'sc-ok'?>"><?=$last?$last['CH4']:'—'?></p><p class="sh">Seuil dangereux : 500</p></div>
  <div class="card card-stat"><p class="sl">VOC actuel</p><p class="sb <?=($last&&$last['VOC']>50)?'sc-w':'sc-ok'?>"><?=$last?$last['VOC']:'—'?></p><p class="sh">Seuil alerte : 50 ppb</p></div>
</div>
<div class="card mb"><div class="card-header"><h2>Évolution CO2/CH4/VOC<?=$nuit?' — nuit du '.$nuit:''?></h2><div class="legend"><span class="ldot" style="background:#f59e0b"></span>CO2 <span class="ldot" style="background:#ef4444"></span>CH4 <span class="ldot" style="background:#8b5cf6"></span>VOC</div></div><div class="chart-wrap chart-lg"><canvas id="chart-air"></canvas></div></div>
<div class="card"><div class="card-header"><h2>Historique</h2></div><div class="tbl-wrap"><table><thead><tr><th>Date / Heure</th><th>CO2 (ppm)</th><th>CH4</th><th>VOC</th></tr></thead><tbody><?php foreach($data as $r):?><tr><td><?=$r['timestamp']?></td><td class="<?=$r['CO2']>1500?'cd':($r['CO2']>1000?'cw':'')?>"><?=$r['CO2']?></td><td class="<?=$r['CH4']>500?'cd':''?>"><?=$r['CH4']?></td><td class="<?=$r['VOC']>50?'cw':''?>"><?=$r['VOC']?></td></tr><?php endforeach;?></tbody></table></div></div>
<?php
$cj=json_encode($chart);
$extra_js=<<<JS
const raw={$cj};
const isDark=document.documentElement.getAttribute('data-theme')==='dark';
const gc=isDark?'rgba(255,255,255,0.08)':'rgba(0,0,0,0.06)';const tc=isDark?'#94a3b8':'#64748b';
function fmt(ts){if(!ts)return'';const d=new Date(ts);return`\${d.getFullYear()}-\${String(d.getMonth()+1).padStart(2,'0')}-\${String(d.getDate()).padStart(2,'0')} \${String(d.getHours()).padStart(2,'0')}:\${String(d.getMinutes()).padStart(2,'0')}`;}
new Chart(document.getElementById('chart-air'),{type:'line',data:{labels:raw.map(r=>fmt(r.timestamp)),datasets:[
  {label:'CO2 (ppm)',data:raw.map(r=>r.CO2),borderColor:'#f59e0b',backgroundColor:'rgba(245,158,11,0.08)',tension:0.4,pointRadius:2,fill:true},
  {label:'CH4',data:raw.map(r=>r.CH4),borderColor:'#ef4444',tension:0.4,pointRadius:2,fill:false},
  {label:'VOC',data:raw.map(r=>r.VOC),borderColor:'#8b5cf6',tension:0.4,pointRadius:2,fill:false}
]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{labels:{color:tc,boxWidth:12}}},scales:{x:{ticks:{color:tc,maxTicksLimit:12,maxRotation:45,font:{size:10}},grid:{color:gc}},y:{ticks:{color:tc},grid:{color:gc}}}}});
JS;
include 'includes/footer.php';
