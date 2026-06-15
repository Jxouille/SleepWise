<?php
session_start();require_once 'includes/config.php';require_once 'includes/auth.php';require_once 'includes/capteurs.php';login_required();
$nuit=$_GET['nuit']??null;[$debut,$fin]=get_date_range($nuit);
$data=get_light(100,$debut,$fin);$chart=get_light_chart(200,$debut,$fin);$last=get_last_light();$nuits=get_nuits_light();
$page_title='Luminosité';include 'includes/header.php';
?>
<div class="night-sel mb"><form method="GET" class="night-form"><label class="night-label">Sélectionner une nuit</label><select name="nuit" class="select-ctrl" onchange="this.form.submit()"><option value="">Données récentes</option><?php foreach($nuits as $n):?><option value="<?=$n['nuit']?>" <?=$nuit===$n['nuit']?'selected':''?>>Nuit du <?=$n['nuit']?></option><?php endforeach;?></select><?php if($nuit):?><a href="/capteur_lumiere.php" class="btn btn-secondary btn-sm">Réinitialiser</a><?php endif;?></form></div>
<div class="grid-2 mb">
  <div class="card card-stat"><p class="sl">Valeur actuelle</p><p class="sb sc-light"><?=$last?$last['light_value'].' lux':'—'?></p><p class="sh">Obscurité : 0 | Plein soleil : 1000+</p></div>
  <div class="card card-stat"><p class="sl">Statut</p><p class="sb"><?=$last?'<span class="badge badge-'.($last['day_status']==='DAY'?'warning':'info').'">'.$last['day_status'].'</span>':'—'?></p><p class="sh">Seuil DAY/NIGHT : 500 lux</p></div>
</div>
<div class="card mb"><div class="card-header"><h2>Évolution luminosité<?=$nuit?' — nuit du '.$nuit:''?></h2></div><div class="chart-wrap chart-lg"><canvas id="chart-lum"></canvas></div></div>
<div class="card"><div class="card-header"><h2>Historique</h2></div><div class="tbl-wrap"><table><thead><tr><th>Date / Heure</th><th>Valeur (lux)</th><th>Statut</th></tr></thead><tbody><?php foreach($data as $r):?><tr><td><?=$r['created_at']?></td><td><?=$r['light_value']?></td><td><span class="badge <?=$r['day_status']==='DAY'?'badge-warning':'badge-info'?>"><?=$r['day_status']?></span></td></tr><?php endforeach;?></tbody></table></div></div>
<?php
$cj=json_encode($chart);
$extra_js=<<<JS
const raw={$cj};
const isDark=document.documentElement.getAttribute('data-theme')==='dark';
const gc=isDark?'rgba(255,255,255,0.08)':'rgba(0,0,0,0.06)';const tc=isDark?'#94a3b8':'#64748b';
function fmt(ts){if(!ts)return'';const d=new Date(ts);return`\${d.getFullYear()}-\${String(d.getMonth()+1).padStart(2,'0')}-\${String(d.getDate()).padStart(2,'0')} \${String(d.getHours()).padStart(2,'0')}:\${String(d.getMinutes()).padStart(2,'0')}`;}
new Chart(document.getElementById('chart-lum'),{type:'bar',data:{labels:raw.map(r=>fmt(r.created_at)),datasets:[{label:'Luminosité (lux)',data:raw.map(r=>r.light_value),backgroundColor:raw.map(r=>r.day_status==='DAY'?'rgba(245,158,11,0.5)':'rgba(99,102,241,0.5)'),borderColor:raw.map(r=>r.day_status==='DAY'?'#f59e0b':'#6366f1'),borderWidth:1,borderRadius:3}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:tc}}},scales:{x:{ticks:{color:tc,maxTicksLimit:12,maxRotation:45,font:{size:10}},grid:{color:gc}},y:{ticks:{color:tc},grid:{color:gc}}}}});
JS;
include 'includes/footer.php';
