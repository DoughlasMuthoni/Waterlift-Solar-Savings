<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');

require_once __DIR__ . '/db_connect.php';

$rows = $pdo->query(
    "SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order ASC, id ASC"
)->fetchAll();

$out = [];
foreach ($rows as $r) {
    $features = [];
    if ($r['features']) {
        $decoded = json_decode($r['features'], true);
        if (is_array($decoded)) $features = $decoded;
    }

    $out[] = [
        'id'               => (int)$r['id'],
        'name'             => $r['name'],
        'capacity'         => $r['capacity'],
        'capacityNum'      => (float)$r['capacity_num'],
        'icon'             => $r['icon'],
        'tagline'          => $r['tagline'] ?? '',
        'badge'            => $r['badge'],
        'gradient'         => $r['gradient'],
        'accentColor'      => $r['accent_color'],
        'coverageFraction' => (float)$r['coverage_fraction'],
        'features'         => $features,
        'savingsLabel'     => $r['savings_label'],
        'rent'             => ['monthly' => (int)$r['rent_monthly']],
        'rto'              => ['monthly' => (int)$r['rto_monthly'], 'months' => (int)$r['rto_months']],
        'cash'             => ['price' => (int)$r['cash_price'], 'roi' => $r['cash_roi']],
        'sortOrder'        => (int)$r['sort_order'],
    ];
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
