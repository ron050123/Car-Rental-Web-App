<?php
header('Content-Type: application/json');

$q      = strtolower(trim($_GET['q'] ?? ''));
$suggest= !empty($_GET['suggest']);
$types  = array_filter(explode(',', $_GET['types']  ?? ''));
$brands = array_filter(explode(',', $_GET['brands'] ?? ''));

$raw       = file_get_contents(__DIR__ . '/../data/cars.json');
$data      = json_decode($raw, true);
$carsArray = $data['cars'] ?? [];

if ($suggest) {
    $sugs = [];
    foreach ($carsArray as $c) {
        foreach (['brand','carModel','carType'] as $field) {
            if (isset($c[$field]) && stripos($c[$field], $q) !== false) {
                $sugs[] = $c[$field];
            }
        }
        if (!empty($c['description']) && stripos($c['description'], $q) !== false) {
            $sugs[] = $c['brand'] . ' ' . $c['carModel'];
        }
    }
    $sugs = array_values(array_unique($sugs));
    echo json_encode(['suggestions' => $sugs]);
    exit;
}

$filtered = array_filter($carsArray, function($c) use($q,$types,$brands) {
    $text    = strtolower(implode(' ', [
        $c['brand'] ?? '',
        $c['carModel'] ?? '',
        $c['carType'] ?? '',
        $c['description'] ?? ''
    ]));
    $matchQ  = $q === '' || strpos($text, $q) !== false;
    $matchT  = empty($types ) || in_array($c['carType'] ?? '', $types );
    $matchB  = empty($brands) || in_array($c['brand']    ?? '', $brands);
    return $matchQ && $matchT && $matchB;
});

echo json_encode(['cars' => array_values($filtered)]);
