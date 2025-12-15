<?php
header('Content-Type: application/json');

if (!isset($_GET['orderIndex'])) {
    echo json_encode(['success'=>false,'error'=>'Missing order index']);
    exit;
}
$idx = intval($_GET['orderIndex']);

$carsFile   = __DIR__ . '/../data/cars.json';
$ordersFile = __DIR__ . '/../data/orders.json';

$carsData   = json_decode(file_get_contents($carsFile), true)['cars']  ?? [];
$ordersData = json_decode(file_get_contents($ordersFile), true)['orders'] ?? [];

if (!isset($ordersData[$idx])) {
    echo json_encode(['success'=>false,'error'=>'Order not found']);
    exit;
}
if (($ordersData[$idx]['status'] ?? '') === 'confirmed') {
    echo json_encode(['success'=>false,'error'=>'Order already confirmed']);
    exit;
}

$ordersData[$idx]['status'] = 'confirmed';
$vin = $ordersData[$idx]['car']['vin'] ?? '';
foreach ($carsData as &$c) {
    if (($c['vin'] ?? '') === $vin) {
        $c['available'] = false;
        break;
    }
}
unset($c);

$ok1 = file_put_contents($ordersFile, json_encode(['orders'=>$ordersData], JSON_PRETTY_PRINT));
$ok2 = file_put_contents($carsFile,   json_encode(['cars'=>$carsData],     JSON_PRETTY_PRINT));

if ($ok1 && $ok2) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Failed to write data']);
}
