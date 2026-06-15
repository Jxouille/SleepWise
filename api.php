<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/capteurs.php';

if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'Non autorisé']); exit; }

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$nuit   = $_GET['nuit'] ?? null;
[$debut, $fin] = get_date_range($nuit);

switch ($action) {
    case 'refresh':
        appliquer_regles();
        echo json_encode([
            'last_th'    => get_last_temp_hum(),
            'last_air'   => get_last_air(),
            'last_light' => get_last_light(),
            'last_sound' => get_last_sound(),
            'actionneurs'=> get_actionneurs(),
            'sommeil'    => calcul_score(),
        ]);
        break;
    case 'chart_th':
        echo json_encode(get_temp_hum_chart(200, $debut, $fin));
        break;
    case 'chart_air':
        echo json_encode(get_air_chart(200, $debut, $fin));
        break;
    case 'chart_light':
        echo json_encode(get_light_chart(200, $debut, $fin));
        break;
    case 'chart_son':
        echo json_encode(get_sound_chart(200, $debut, $fin));
        break;
    case 'stop_reveil':
        $db = get_db();
        $db->exec("UPDATE etats_actionneurs SET etat=0, declenche_par='aucun' WHERE composant IN ('buzzer','led_rouge','led_jaune') AND declenche_par='reveil'");
        echo json_encode(['ok' => true]);
        break;
    default:
        echo json_encode(['error' => 'Action inconnue']);
}
