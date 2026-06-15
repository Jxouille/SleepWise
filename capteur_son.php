<?php
session_start();require_once 'includes/config.php';require_once 'includes/auth.php';require_once 'includes/capteurs.php';login_required();
$nuit=$_GET['nuit']??null;[$debut,$fin]=get_date_range($nuit);
$data=get_sound(100,$debut,$fin);$chart=get_sound_chart(200,$debut,$fin);$last=get_last_sound();$nuits=get_nuits_son();
$db=get_db();$breathing = [];
$page_title='Son & Respiration';include 'includes/header.php';
?>
<div class="night-sel mb"><form method="GET" class="night-form"><label class="night-label">Sélectionner une nuit</label><select name="nuit" class="select-ctrl" onchange="this.form.submit()"><option value="">Données récentes</option><?php foreach($nuits as $n):?><option value="<?=$n['nuit']?>" <?=$nuit===$n['nuit']?'selected':''?>>Nuit du <?=$n['nuit']?></option><?php endforeach;?></select><?php if($nuit):?><a href="/capteur_son.php" class="btn btn-secondary btn-sm">Réinitialiser</a><?php endif;?></form></div>
<div class="grid-3 mb">
  <div class="card card-stat"><p class="sl">Niveau sonore</p><p class="sb <?=($last&&$last['raw']>60)?'sc-d':(($last&&$last['raw']>40)?'sc-w':'sc-ok')?>"><?=$last?$last['raw']:'—'?></p><p class="sh">Alerte : 60 | Critique : 80</p></div>
  <div class="card card-stat"><p class="sl">RPM respiratoire</p><p class="sb <?=($last&&$last['rpm']&&($last['rpm']<8||$last['rpm']>20))?'sc-w':'sc-ok'?>"><?=$last&&$last['rpm']?$last['rpm']:'—'?></p><p class="sh">Normal : 8–20 cycles/min</p></div>
  <div class="card card-stat"><p class="sl">Détection apnée</p><p class="sb <?=($last&&$last['is_apnee']&&in_array(strtolower($last['is_apnee']),['true','1']))?'sc-d':'sc-ok'?>"><?=$last&&$last['is_apnee']?$last['is_apnee']:'Non'?></p><p class="sh">Surveillance continue</p></div>
</div>
<div class="card mb"><div class="card-header"><h2>Évolution son & respiration<?=$nuit?' — nuit du '.$nuit:''?></h2><div class="legend"><span class="ldot" style="background:#10b981"></span>Niveau brut <span class="ldot" style="background:#f59e0b"></span>RPM</div></div><div class="chart-wrap chart-lg"><canvas id="chart-son"></canvas></div></div>
<?php if($breathing):?>
<div class="card mb"><div class="card-header"><h2>Analyse respiratoire</h2></div><div class="tbl-wrap"><table><thead><tr><th>Date</th><th>Rythme (rpm)</th><th>Intervalle</th><th>État</th><th>Confiance</th></tr></thead><tbody><?php foreach($breathing as $r):?><tr><td><?=$r['analysed_at']?></td><td><?=$r['respiration_rate']??'—'?></td><td><?=$r['respiration_interval']??'—'?></td><td><span class="badge <?=$r['breathing_state']==='normal'?'badge-success':($r['breathing_state']==='apnea_suspected'?'badge-danger':'badge-warning')?>"><?=$r['breathing_state']??'—'?></span></td><td><?=$r['confidence_score']??'—'?></td></tr><?php endforeach;?></tbody></table></div></div>
<?php endif;?>
<div class="card"><div class="card-header"><h2>Historique son</h2></div><div class="tbl-wrap"><table><thead><tr><th>Date / Heure</th><th>Type</th><th>RPM</th><th>Apnée</th><th>Brut</th></tr></thead><tbody><?php foreach($data as $r):?><tr><td><?=$r['timestamp']?></td><td><?=$r['detailed_type']??$r['type']??'—'?></td><td class="<?=($r['rpm']&&($r['rpm']<8||$r['rpm']>20))?'cw':''?>"><?=$r['rpm']??'—'?></td><td class="<?=($r['is_apnee']&&in_array(strtolower($r['is_apnee']),['true','1']))?'cd':''?>"><?=$r['is_apnee']??'—'?></td><td class="<?=($r['raw']&&$r['raw']>60)?'cw':''?>"><?=$r['raw']??'—'?></td></tr><?php endforeach;?></tbody></table></div></div>
<?php
$cj=json_encode($chart);
$extra_js=<<<JS
const raw={$cj};
const isDark=document.documentElement.getAttribute('data-theme')==='dark';
const gc=isDark?'rgba(255,255,255,0.08)':'rgba(0,0,0,0.06)';const tc=isDark?'#94a3b8':'#64748b';
function fmt(ts){if(!ts)return'';const d=new Date(ts);return`\${d.getFullYear()}-\${String(d.getMonth()+1).padStart(2,'0')}-\${String(d.getDate()).padStart(2,'0')} \${String(d.getHours()).padStart(2,'0')}:\${String(d.getMinutes()).padStart(2,'0')}`;}
new Chart(document.getElementById('chart-son'),{type:'line',data:{labels:raw.map(r=>fmt(r.timestamp)),datasets:[
  {label:'Niveau brut',data:raw.map(r=>r.raw),borderColor:'#10b981',backgroundColor:'rgba(16,185,129,0.08)',tension:0.4,pointRadius:3,fill:true},
  {label:'RPM',data:raw.map(r=>r.rpm),borderColor:'#f59e0b',tension:0.4,pointRadius:3,fill:false,yAxisID:'y2'}
]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{labels:{color:tc,boxWidth:12}}},scales:{x:{ticks:{color:tc,maxTicksLimit:12,maxRotation:45,font:{size:10}},grid:{color:gc}},y:{ticks:{color:tc},grid:{color:gc}},y2:{position:'right',ticks:{color:'#f59e0b'},grid:{display:false}}}}});
JS;
include 'includes/footer.php';
