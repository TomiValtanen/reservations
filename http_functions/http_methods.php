<?php

function method_get($reservations)
{
    
    $room = $_GET['room'];

    $result = array_values(array_filter($reservations, function ($r) use ($room) {
        return $r['room'] === $room;
    }));

    respond($result);

}

function method_post($input,$reservations){

    $reservations=$reservations;
    $nextId= count($reservations);

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

function method_delete($uri,$reservations){
    $reservations=$reservations;
    $id = (int)$uri;

    foreach ($reservations as $index => $r) {
        if ($r['id'] === $id) {
            unset($reservations[$index]);
            respond(['message' => 'Reservation deleted'.$id]);
        }
    }

    respond(['error' => 'Reservation not found'], 404);
}