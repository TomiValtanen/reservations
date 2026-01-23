<?php
// Sallittavat HTTP-metodit
$allowedMethods = ['GET', 'POST', 'DELETE'];

// Nykyinen metodi
$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json');

// Tarkistus: sallitaanko metodi
if (!in_array($method, $allowedMethods, true)) {
    
    header('Allow: ' . implode(', ', $allowedMethods));
    http_response_code(405);

    echo json_encode([
        'error' => 'Method not allowed'
    ]);

    exit;
}