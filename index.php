<?php
require ("headers_functions/headers.php");
require ("functions/helper_functions.php");
require ("http_functions/http_methods.php");

/**
 * Kokoushuoneiden varaus API
 * - In-memory tietokanta (PHP array)
 * - Yksinkertainen REST-tyylinen rajapinta
 */


// Aikavyöhyke lisätty
date_default_timezone_set('Europe/Helsinki');


/**
 * In-memory "tietokanta"
 * Normaalisti tämä olisi esim. Redis tai SQLite :memory:
 */
static $reservations = [
    [
    "id"=> 0,
    "room"=> "B203",
    "start"=> 1769351400,
    "end"=> 1769355000
    ],
    [
    "id"=> 1,
    "room"=> "B204",
    "start"=> 1769351400,
    "end"=> 1769355000
    ],
    [
    "id"=> 2,
    "room"=> "B205",
    "start"=> 1769351400,
    "end"=> 1769355000
    ]
];


/**
 * Reititys
 */
$method = $_SERVER['REQUEST_METHOD'];
$uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

if ($uri[0] !== 'reservations') {
    respond(['error' => 'Not found'], 404); 
}

/**
 * VARAUSTEN LISTAUS
 * GET /reservations?room=A101
 */
if ($method === 'GET') {
    if (!isset($_GET['room'])) {
        respond(['error' => 'room parameter missing'], 400);
    }

    method_get($reservations);
}

/**
 * VARAUKSEN LUONTI
 * POST /reservations
 */
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['room'], $input['start'], $input['end'])) {
        respond(['error' => 'Invalid payload'], 400);
    }

    method_post($input,$reservations);
}

/**
 * VARAUKSEN PERUUTUS
 * DELETE /reservations/{id}
 */
if ($method === 'DELETE' && isset($uri[1])) {
    method_delete($uri[1],$reservations);
}

respond(['error' => 'Method not allowed'], 405);
