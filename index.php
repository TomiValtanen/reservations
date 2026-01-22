<?php
/**
 * Kokoushuoneiden varaus API
 * - In-memory tietokanta (PHP array)
 * - Yksinkertainen REST-tyylinen rajapinta
 */

header('Content-Type: application/json');

// Aikavyöhyke lisätty
date_default_timezone_set('Europe/Helsinki');


/**
 * In-memory "tietokanta"
 * Normaalisti tämä olisi esim. Redis tai SQLite :memory:
 */
static $reservations = [];
static $nextId = 1;

/**
 * Apufunktio: palauta JSON-vastaus ja lopeta suoritus
 */
function respond($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Tarkistaa menevätkö kaksi aikaväliä päällekkäin
 */
function overlaps($start1, $end1, $start2, $end2): bool {
    return $start1 < $end2 && $end1 > $start2;
}

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

    $room = $_GET['room'];
    global $reservations;

    $result = array_values(array_filter($reservations, function ($r) use ($room) {
        return $r['room'] === $room;
    }));

    respond($result);
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

    $room = $input['room'];
    $start = strtotime($input['start']);
    $end = strtotime($input['end']);
    $now = time();

    // Business rules
    if ($start === false || $end === false) {
        respond(['error' => 'Invalid datetime format'], 400);
    }

    if ($start >= $end) {
        respond(['error' => 'Start time must be before end time'], 400);
    }

    if ($start < $now) {
        respond(['error' => 'Reservation cannot be in the past'], 400);
    }

    global $reservations, $nextId;

    // Päällekkäisyyden tarkistus
    foreach ($reservations as $r) {
        if ($r['room'] === $room && overlaps($start, $end, $r['start'], $r['end'])) {
            respond(['error' => 'Time slot already reserved'], 409);
        }
    }

    // Luo varaus
    $reservation = [
        'id' => $nextId++,
        'room' => $room,
        'start' => $start,
        'end' => $end
    ];

    $reservations[] = $reservation;

    respond($reservation, 201);
}

/**
 * VARAUKSEN PERUUTUS
 * DELETE /reservations/{id}
 */
if ($method === 'DELETE' && isset($uri[1])) {
    $id = (int)$uri[1];
    global $reservations;

    foreach ($reservations as $index => $r) {
        if ($r['id'] === $id) {
            unset($reservations[$index]);
            respond(['message' => 'Reservation deleted']);
        }
    }

    respond(['error' => 'Reservation not found'], 404);
}

respond(['error' => 'Method not allowed'], 405);
