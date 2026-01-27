<?php
require ("header_functions/headers.php");
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
    ],
    [
    "id"=> 3,
    "room"=> "B203",
    "start"=> 1769580000,
    "end"=> 1769587200
    ],
    [
    "id"=> 4,
    "room"=> "B203",
    "start"=> 1769590800,
    "end"=> 1769594400
    ],
    [
    "id"=> 5,
    "room"=> "B203",
    "start"=> 1769619600,
    "end"=> 1769623200
    ],
    [
    "id"=> 6,
    "room"=> "B203",
    "start"=> 1769608800,
    "end"=> 1769616000
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

    if (!isset($input['room'], $input['start_time'], $input['end_time'], $input['date'])) {
        respond(['error' => 'Invalid payload'], 400);
    }

    check_string($input['room'],"room");
    check_string($input['date'],"date");
    check_string($input['start_time'],"start_time");
    check_string($input['end_time'],"end_time");

    validate_format($input["date"],"Y-m-d","Invalid date format, expected Y-m-d");
    validate_format($input["start_time"],"H:i","Invalid time format, expected H:i");
    validate_format($input["end_time"],"H:i","Invalid time format, expected H:i");


    method_post($input,$reservations);
}

/**
 * VARAUKSEN PERUUTUS
 * DELETE /reservations/{id}
 */
if ($method === 'DELETE') {

    $uri = $_SERVER['REQUEST_URI'];

    // Poistetaan query string
    $path = parse_url($uri, PHP_URL_PATH);

    // Jaetaan osiin
    $segments = explode('/', trim($path, '/'));

    // Odotetaan: reservations/index.php/{id}
    $id = end($segments);

    if (!ctype_digit($id)) {
    respond(['error' => 'Invalid reservation id'], 400);
    }

    method_delete($id,$reservations);
}

respond(['error' => 'Method not allowed'], 405);
