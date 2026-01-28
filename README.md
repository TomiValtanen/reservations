# reservations
Kokoushuone varausrajapinta

## API-endpointit:

### Varauksen luonti:
POST/reservations/

***JSON Request body:***
```
{
  "room": "A101",
  "date":"2026-01-31",
  "start_time": "09:00",
  "end_time": "11:00"
}
```
***JSON Response:***
```
{
    "id": 7,
    "room": "A101",
    "start": 1769842800,
    "end": 1769850000
}
```

### Varauksen katselu:
GET /reservations?room=B203

### Varauksen peruutus:
DELETE /reservations/index.php/1
