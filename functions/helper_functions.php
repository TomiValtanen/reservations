<?php
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

function check_string($input,$field_name){
    $string_format= is_string($input);

    if(!$string_format){
        respond(["error" => "{$field_name} must be string"],400);
    }

    if (trim($input) === '') {
        respond(
            ['error' => "{$field_name} cannot be empty"],
            400
        );
    }

}

/**
 * Tarkistaa arvon halutulla formaatilla antaen tarvittaessa itse asetetun virheviestin
 * @param string $value tarkistettava arvo
 * @param string $format päivämäärälle Y-m-d / ajalle H:i
 * @param string $errorMessage Haluttu virheviesti
 */
function validate_format(string $value, string $format, string $errorMessage): void
{
    $dt = DateTime::createFromFormat($format, $value);
    $errors = DateTime::getLastErrors();

    if ($dt === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        respond(['error' => $errorMessage], 400);
    }

}