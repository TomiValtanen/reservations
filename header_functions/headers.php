<?php
// Sallittavat HTTP-metodit
$allowedMethods = ['GET', 'POST', 'DELETE'];

// Nykyinen metodi
$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json');

// Tarkistus: sallitaanko metodi
if (!in_array($method, $allowedMethods, true)) {
    
    header('Allow: ' . implode(', ', $allowedMethods));
    respond(['error' => 'Method not allowed'],405);
}