<?php
header('Content-Type: application/json');

$file = 'students.json';

if (file_exists($file)) {
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        $data = [];
    }
    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    echo json_encode([]);
}
?>
