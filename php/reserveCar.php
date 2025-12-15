<?php
header('Content-Type: application/json');

$carsFile  = __DIR__ . '/../data/cars.json';
$ordersFile= __DIR__ . '/../data/orders.json';

$carsData = json_decode(file_get_contents($carsFile), true)['cars'] ?? [];
$vin      = $_POST['vin'] ?? '';
$price    = null;
foreach ($carsData as $c) {
    if (($c['vin'] ?? '') === $vin) {
        $price = $c['pricePerDay'];
        $available = $c['available'] ?? false;
        break;
    }
}
if ($price === null) {
    echo json_encode(['success'=>false,'error'=>'Car not found']);
    exit;
}
if (!$available) {
    echo json_encode(['success'=>false,'error'=>'Sorry, this car is no longer available']);
    exit;
}

$days  = max(1, intval($_POST['days'] ?? 1));
$total = $price * $days;
$new   = [
    'customer' => [
        'name'                 => $_POST['customerName']            ?? '',
        'phoneNumber'          => $_POST['phoneNumber']             ?? '',
        'email'                => $_POST['email']                   ?? '',
        'driversLicenseNumber' => $_POST['driversLicenseNumber']    ?? '',
    ],
    'car'    => ['vin' => $vin],
    'rental' => [
        'startDate'    => $_POST['startDate']    ?? '',
        'rentalPeriod' => $days,
        'totalPrice'   => $total,
        'orderDate'    => date('Y-m-d')
    ],
    'status' => 'pending'
];

$ordersData      = json_decode(file_get_contents($ordersFile), true);
$ordersData['orders'][] = $new;
if (!file_put_contents($ordersFile, json_encode($ordersData, JSON_PRETTY_PRINT))) {
    echo json_encode(['success'=>false,'error'=>'Could not save order']);
    exit;
}

$idx = count($ordersData['orders']) - 1;
echo json_encode([
    'success' => true,
    'confirmationLink' => "php/confirmOrder.php?orderIndex={$idx}"
]);
