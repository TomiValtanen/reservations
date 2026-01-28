<?php

/**
 * GET metodi
 */
function method_get($reservations)
{
    
    $room = $_GET['room'];

    //Hakee kaikki huoneet
    $result = array_values(array_filter($reservations, function ($r) use ($room) {
        return $r['room'] === $room;
    }));

    //Järjestellään saadut huoneet
    usort($result, function ($a, $b) {
    return $a['start'] <=> $b['start'];
    });

    respond($result);

}

/**
 * POST Metodi
 */
function method_post($formatted_input,$reservations){

    $reservations=$reservations;
    $nextId= count($reservations);

    $room = $formatted_input->room;
    $date = $formatted_input->date;
    $start_time=$formatted_input->start_time;
    $end_time= $formatted_input->end_time;

    $start = strtotime($date . " " . $start_time);
    $end = strtotime($date . " " .  $end_time);
    $now = time();

    // Business rules
    if ($start === false || $end === false) {
        respond(['error' => 'Invalid datetime format'], 400);
    }

    // Tarkistaa että aloitus aika on ennen lopetusta
    if ($start >= $end) {
        respond(['error' => 'Start time must be before end time'], 400);
    }

    //Tarkistaa että aloitus aika ei ole menneisyydessä
    if ($start < $now) {
        respond(['error' => 'Reservation cannot be in the past'], 400);
    }

    // Tarkistaa onko annettu aloitus ja lopetus aika oikein
    if($start_time < "08:00" || $end_time>"20:00"){
        respond(['error' => 'The reservation must be made between office opening hours of 08:00 and 20:00'], 400);
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

/**
 * DELETE metodi
 */
function method_delete($id,$reservations){
    $reservations=$reservations;
    $id = (int)$id;

    // Tarkistetaan ettei id ole negatiivinen
    if ($id < 0) {
        respond(['error' => 'Invalid reservation id'], 400);
    }

    global $reservations;

    //Tarkistetaan onko kyseisellä id:llä varausta ja se poistetaan
    foreach ($reservations as $index => $r) {
        if ($r['id'] === $id) {
            $deleted_reservation=$reservations[$index];
            unset($reservations[$index]);
            $reservations = array_values($reservations);

            respond([[
                    "message"=> "Reservation deleted",
                    "reservation"=> 
                    [
                        "id"=> $deleted_reservation["id"],
                        "room"=> $deleted_reservation["room"],
                        "start"=> $deleted_reservation["start"],
                        "end"=> $deleted_reservation["end"]
                    ]
                    ]]);
        }
    }

    respond(['error' => 'Reservation not found'], 404);
}