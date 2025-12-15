<?php
header('Content-Type: application/json');

$carsFile = __DIR__ . '/../data/cars.json';
if (!file_exists($carsFile)) {
    echo json_encode(['cars' => []]);
    exit;
}

echo file_get_contents($carsFile);
