<?php
require_once __DIR__ . '/config.php';

function get_last_temp_hum() {
    $db = get_db();
    return $db->query("SELECT * FROM groupe_4B ORDER BY horodatage DESC LIMIT 1")->fetch();
}

function get_temp_hum($limit=50, $debut=null, $fin=null) {
    $db = get_db();
    $l = intval($limit);
    if ($debut && $fin) {
        $s = $db->prepare("SELECT * FROM groupe_4B WHERE horodatage BETWEEN ? AND ? ORDER BY horodatage DESC LIMIT $l");
        $s->execute([$debut, $fin]);
    } else {
        $s = $db->query("SELECT * FROM groupe_4B ORDER BY horodatage DESC LIMIT $l");
    }
    return $s->fetchAll();
}

function get_temp_hum_chart($limit=100, $debut=null, $fin=null) {
    $db = get_db();
    $l = intval($limit);
    if ($debut && $fin) {
        $s = $db->prepare("SELECT horodatage, temperature, humidite FROM groupe_4B WHERE horodatage BETWEEN ? AND ? ORDER BY horodatage ASC LIMIT $l");
        $s->execute([$debut, $fin]);
        return $s->fetchAll();
    }
    $s = $db->query("SELECT horodatage, temperature, humidite FROM groupe_4B ORDER BY horodatage DESC LIMIT $l");
    return array_reverse($s->fetchAll());
}

function get_nuits_th() {
    $db = get_db();
    return $db->query("SELECT DISTINCT DATE(horodatage) as nuit FROM groupe_4B ORDER BY nuit DESC LIMIT 30")->fetchAll();
}

function get_last_air() {
    $db = get_db();
    return $db->query("SELECT * FROM ambient_air ORDER BY timestamp DESC LIMIT 1")->fetch();
}

function get_air($limit=50, $debut=null, $fin=null) {
    $db = get_db();
    $l = intval($limit);
    if ($debut && $fin) {
        $s = $db->prepare("SELECT * FROM ambient_air WHERE timestamp BETWEEN ? AND ? ORDER BY timestamp DESC LIMIT $l");
        $s->execute([$debut, $fin]);
    } else {
        $s = $db->query("SELECT * FROM ambient_air ORDER BY timestamp DESC LIMIT $l");
    }
    return $s->fetchAll();
}

function get_air_chart($limit=100, $debut=null, $fin=null) {
    $db = get_db();
    $l = intval($limit);
    if ($debut && $fin) {
        $s = $db->prepare("SELECT timestamp, CO2, CH4, VOC FROM ambient_air WHERE timestamp BETWEEN ? AND ? ORDER BY timestamp ASC LIMIT $l");
        $s->execute([$debut, $fin]);
        return $s->fetchAll();
    }
    $s = $db->query("SELECT timestamp, CO2, CH4, VOC FROM ambient_air ORDER BY timestamp DESC LIMIT $l");
    return array_reverse($s->fetchAll());
}

function get_nuits_air() {
    $db = get_db();
    return $db->query("SELECT DISTINCT DATE(timestamp) as nuit FROM ambient_air ORDER BY nuit DESC LIMIT 30")->fetchAll();
}

function get_last_light() {
    $db = get_db();
    return $db->query("SELECT * FROM light_sensor_data ORDER BY created_at DESC LIMIT 1")->fetch();
}

function get_light($limit=50, $debut=null, $fin=null) {
    $db = get_db();
    $l = intval($limit);
    if ($debut && $fin) {
        $s = $db->prepare("SELECT * FROM light_sensor_data WHERE created_at BETWEEN ? AND ? ORDER BY created_at DESC LIMIT $l");
        $s->execute([$debut, $fin]);
    } else {
        $s = $db->query("SELECT * FROM light_sensor_data ORDER BY created_at DESC LIMIT $l");
    }
    return $s->fetchAll();
}

function get_light_chart($limit=100, $debut=null, $fin=null) {
    $db = get_db();
    $l = intval($limit);
    if ($debut && $fin) {
        $s = $db->prepare("SELECT created_at, light_value, day_status FROM light_sensor_data WHERE created_at BETWEEN ? AND ? ORDER BY created_at ASC LIMIT $l");
        $s->execute([$debut, $fin]);
        return $s->fetchAll();
    }
    $s = $db->query("SELECT created_at, light_value, day_status FROM light_sensor_data ORDER BY created_at DESC LIMIT $l");
    return array_reverse($s->fetchAll());
}

function get_nuits_light() {
    $db = get_db();
    return $db->query("SELECT DISTINCT DATE(created_at) as nuit FROM light_sensor_data ORDER BY nuit DESC LIMIT 30")->fetchAll();
}

function get_last_sound() {
    $db = get_db();
    return $db->query("SELECT * FROM sound_sleep ORDER BY timestamp DESC LIMIT 1")->fetch();
}

function get_sound($limit=50, $debut=null, $fin=null) {
    $db = get_db();
    $l = intval($limit);
    if ($debut && $fin) {
        $s = $db->prepare("SELECT * FROM sound_sleep WHERE timestamp BETWEEN ? AND ? ORDER BY timestamp DESC LIMIT $l");
        $s->execute([$debut, $fin]);
    } else {
        $s = $db->query("SELECT * FROM sound_sleep ORDER BY timestamp DESC LIMIT $l");
    }
    return $s->fetchAll();
}

function get_sound_chart($limit=100, $debut=null, $fin=null) {
    $db = get_db();
    $l = intval($limit);
    if ($debut && $fin) {
        $s = $db->prepare("SELECT timestamp, raw, rpm FROM sound_sleep WHERE timestamp BETWEEN ? AND ? ORDER BY timestamp ASC LIMIT $l");
        $s->execute([$debut, $fin]);
        return $s->fetchAll();
    }
    $s = $db->query("SELECT timestamp, raw, rpm FROM sound_sleep ORDER BY timestamp DESC LIMIT $l");
    return array_reverse($s->fetchAll());
}

function get_nuits_son() {
    $db = get_db();
    return $db->query("SELECT DISTINCT DATE(timestamp) as nuit FROM sound_sleep ORDER BY nuit DESC LIMIT 30")->fetchAll();
}

function get_actionneurs() {
    $db = get_db();
    return $db->query("SELECT * FROM etats_actionneurs ORDER BY id")->fetchAll();
}

function update_actionneur($composant, $etat, $declenche_par) {
    $db = get_db();
    $s = $db->prepare("UPDATE etats_actionneurs SET etat=?, declenche_par=? WHERE composant=?");
    $s->execute([$etat, $declenche_par, $composant]);
}

function appliquer_regles() {
    $th  = get_last_temp_hum();
    $air = get_last_air();
    $lum = get_last_light();
    $son = get_last_sound();

    $etats = [
        'buzzer'    => [0, 'aucun'],
        'led_rouge' => [0, 'aucun'],
        'led_jaune' => [0, 'aucun'],
        'led_bleue' => [0, 'aucun'],
        'led_verte' => [1, 'normal'],
    ];

    if ($th) {
        $t = $th['temperature']; $h = $th['humidite'];
        if ($t > 28 && $h > 70) { $etats['buzzer'] = [1,'inconfort_thermique']; $etats['led_verte'] = [0,'aucun']; }
        elseif ($t > 26) { $etats['led_rouge'] = [1,'temperature_haute']; $etats['led_verte'] = [0,'aucun']; }
        elseif ($t < 16) { $etats['led_bleue'] = [1,'temperature_basse']; $etats['led_verte'] = [0,'aucun']; }
        if ($h > 70) { $etats['led_jaune'] = [1,'humidite_haute']; $etats['led_verte'] = [0,'aucun']; }
        elseif ($h < 30) { $etats['led_jaune'] = [1,'humidite_basse']; $etats['led_verte'] = [0,'aucun']; }
    }
    if ($air) {
        $co2 = $air['CO2']; $ch4 = $air['CH4']; $voc = $air['VOC'];
        if ($co2 > 1500) { $etats['buzzer'] = [1,'co2_critique']; $etats['led_rouge'] = [1,'co2_critique']; $etats['led_verte'] = [0,'aucun']; }
        elseif ($co2 > 1000) { $etats['led_jaune'] = [1,'co2_eleve']; $etats['led_verte'] = [0,'aucun']; }
        if ($ch4 > 500) { $etats['buzzer'] = [1,'ch4_dangereux']; $etats['led_rouge'] = [1,'ch4_dangereux']; $etats['led_verte'] = [0,'aucun']; }
        if ($voc > 50) { $etats['led_jaune'] = [1,'voc_eleve']; $etats['led_verte'] = [0,'aucun']; }
    }
    if ($lum) {
        $lv = $lum['light_value']; $ds = $lum['day_status'];
        if ($ds === 'NIGHT' && $lv > 300) { $etats['led_jaune'] = [1,'lumiere_nuit_anormale']; $etats['led_verte'] = [0,'aucun']; }
        elseif ($ds === 'DAY' && $lv < 100) { $etats['led_bleue'] = [1,'obscurite_anormale']; $etats['led_verte'] = [0,'aucun']; }
    }
    if ($son) {
        $raw = $son['raw'] ?? 0;
        $apnee = strtolower($son['is_apnee'] ?? '');
        if ($raw > 80) { $etats['buzzer'] = [1,'bruit_critique']; $etats['led_rouge'] = [1,'bruit_critique']; $etats['led_verte'] = [0,'aucun']; }
        elseif ($raw > 60) { $etats['led_jaune'] = [1,'bruit_eleve']; $etats['led_verte'] = [0,'aucun']; }
        if (in_array($apnee, ['true','1','oui'])) { $etats['buzzer'] = [1,'apnee_detectee']; $etats['led_verte'] = [0,'aucun']; }
    }

    $db = get_db();
    $s = $db->prepare("SELECT * FROM reveils WHERE actif=1 AND declenche=0 AND date_reveil=? AND heure_reveil <= ?");
    $s->execute([date('Y-m-d'), date('H:i:s')]);
    $reveil = $s->fetch();
    if ($reveil) {
        $etats['buzzer'] = [1, 'reveil'];
        $etats['led_rouge'] = [1, 'reveil'];
        $etats['led_jaune'] = [1, 'reveil'];
        $etats['led_verte'] = [0, 'aucun'];
        $db->prepare("UPDATE reveils SET declenche=1 WHERE id=?")->execute([$reveil['id']]);
    }

    foreach ($etats as $comp => [$etat, $raison]) {
        update_actionneur($comp, $etat, $raison);
    }
}

function calcul_score($debut=null, $fin=null) {
    $db = get_db();
    if ($debut && $fin) {
        $th  = $db->prepare("SELECT AVG(temperature) as t, AVG(humidite) as h FROM groupe_4B WHERE horodatage BETWEEN ? AND ?");
        $th->execute([$debut,$fin]); $th=$th->fetch();
        $air = $db->prepare("SELECT AVG(CO2) as co2 FROM ambient_air WHERE timestamp BETWEEN ? AND ?");
        $air->execute([$debut,$fin]); $air=$air->fetch();
        $son = $db->prepare("SELECT AVG(raw) as son FROM sound_sleep WHERE timestamp BETWEEN ? AND ?");
        $son->execute([$debut,$fin]); $son=$son->fetch();
    } else {
        $th  = $db->query("SELECT AVG(temperature) as t, AVG(humidite) as h FROM groupe_4B WHERE horodatage >= NOW() - INTERVAL 8 HOUR")->fetch();
        $air = $db->query("SELECT AVG(CO2) as co2 FROM ambient_air WHERE timestamp >= NOW() - INTERVAL 8 HOUR")->fetch();
        $son = $db->query("SELECT AVG(raw) as son FROM sound_sleep WHERE timestamp >= NOW() - INTERVAL 8 HOUR")->fetch();
    }

    $score = 100; $recos = []; $alertes = [];
    $t = $th['t'] ?? null; $h = $th['h'] ?? null;
    $co2 = $air['co2'] ?? null; $s = $son['son'] ?? null;

    if ($t) {
        if ($t > 26)         { $score -= 15; $alertes[] = ['niveau'=>'danger', 'msg'=>"Température trop élevée (".round($t,1)."°C)"]; $recos[] = 'Aérez la pièce. Idéal : 18–20°C.'; }
        elseif ($t > 22)     { $score -= 8;  $alertes[] = ['niveau'=>'warning','msg'=>"Température un peu haute (".round($t,1)."°C)"]; }
        elseif ($t < 16)     { $score -= 10; $alertes[] = ['niveau'=>'warning','msg'=>"Température trop basse (".round($t,1)."°C)"]; $recos[] = 'Chauffez légèrement la pièce.'; }
    }
    if ($h) {
        if ($h > 70)         { $score -= 15; $alertes[] = ['niveau'=>'danger', 'msg'=>"Humidité trop élevée (".round($h,1)."%)"]; $recos[] = 'Utilisez un déshumidificateur.'; }
        elseif ($h < 30)     { $score -= 10; $alertes[] = ['niveau'=>'warning','msg'=>"Air trop sec (".round($h,1)."%)"]; $recos[] = 'Un humidificateur améliorerait le confort.'; }
    }
    if ($co2) {
        if ($co2 > 1500)     { $score -= 20; $alertes[] = ['niveau'=>'danger', 'msg'=>"CO2 critique (".round($co2,0)." ppm)"]; $recos[] = 'Aérez immédiatement la pièce.'; }
        elseif ($co2 > 1000) { $score -= 10; $alertes[] = ['niveau'=>'warning','msg'=>"CO2 élevé (".round($co2,0)." ppm)"]; $recos[] = 'Entrouvrez une fenêtre avant de dormir.'; }
    }
    if ($s) {
        if ($s > 60)         { $score -= 20; $alertes[] = ['niveau'=>'danger', 'msg'=>"Bruit excessif (raw: ".round($s,0).")"]; $recos[] = 'Réduisez les sources sonores.'; }
        elseif ($s > 40)     { $score -= 10; $alertes[] = ['niveau'=>'warning','msg'=>"Environnement bruyant (raw: ".round($s,0).")"]; }
    }

    $score = max(0, $score);
    if ($score >= 80)      { $niveau = 'Excellent'; $couleur = 'success'; }
    elseif ($score >= 60)  { $niveau = 'Bon';       $couleur = 'info'; }
    elseif ($score >= 40)  { $niveau = 'Moyen';     $couleur = 'warning'; }
    else                   { $niveau = 'Mauvais';   $couleur = 'danger'; }

    if (empty($recos))   $recos[]   = 'Toutes les conditions sont optimales !';
    if (empty($alertes)) $alertes[] = ['niveau'=>'success','msg'=>'Aucune alerte active'];

    return [
        'score'   => $score,  'niveau'  => $niveau,  'couleur' => $couleur,
        'avg_temp'=> $t   ? round($t,1)   : null,
        'avg_hum' => $h   ? round($h,1)   : null,
        'avg_co2' => $co2 ? round($co2,0) : null,
        'avg_son' => $s   ? round($s,1)   : null,
        'recommandations' => $recos, 'alertes' => $alertes
    ];
}

function get_date_range($nuit) {
    if (!$nuit) return [null, null];
    $jour_suivant = date('Y-m-d', strtotime($nuit . ' +1 day'));
    return [$nuit . ' 20:00:00', $jour_suivant . ' 09:00:00'];
}

function calcul_score_periode($jours) {
    $debut = date('Y-m-d H:i:s', strtotime("-$jours days"));
    $fin   = date('Y-m-d H:i:s');
    return calcul_score($debut, $fin);
}
