# Reservations API
Kokoushuone varausrajapinta:
- Huoneen varaaminen
- Huonevarausten tarkastelu
- Huonevarausten poistaminen

## Hyväksytyt API pyynnöt:

**POST** Varauksen luonti

**GET** Huoneen varausten katsominen

**DELETE** Varauksen poistaminen

## Headers:

Content-Type: application/json

## Yleiset palvelimen vastaukset:

- 200 **OK** pyyntö on onnistunut
- 201 **Created** pyyntö on onnistunut ja varaus luotu
- 400 **Bad Request** pyyntö ei onnistunut. Puuttuva parametri tai muita puutteita
- 404 **Not Found** varausta ei löydy
- 405 **Method Not Allowed** Ei ollut hyväksytty API pyyntö
- 409 **Conflict** Huone varauksessa on päällekkäisyys

## Atribuutit:

| Atribuutti      | Tyyppi | Selitys |
| ----------- | ----------- |-----------|
| id     | Number      | Numeraalinen tunniste |
| room     | String      | Huoneen numero    |
| date   | String        | Varauksen päivämäärä     |
| start_time   | String  |  Varauksen aloitus kellon aika    |
| end_time   | String    |  Varauksen päättymis kellon aika   |
| start   | Number    |  Varauksen alkamis aika (timestamp)   |
| end   | Number    |  Varauksen päättymis aika   (timestamp)  |


## API-endpointit:

### Varauksen luonti:

***POST*** /reservations/

Luodaan huonevaraus haluttuun huoneeseen annetulle päivämäärälle ja kellon ajalle

***Malli JSON Request body:***
```
{
  "room": "A101",
  "date":"2026-01-31",
  "start_time": "09:00",
  "end_time": "11:00"
}
```

***Malli onnistunut JSON Response:***
```
{
    "id": 7,
    "room": "A101",
    "start": 1769842800,
    "end": 1769850000
}
```

***Virhetilanteet:***

- Puuttuva atribuutti (room, date, start_time , end_time).
- Atribuutti ei ole string muotoinen tai on tyhjä ("").
- Päivämäärä on menneisyydessä.
- Päivämäärä on ilmoitettu virheellisesti.
- Kellon aika ei ole 8 ja 20 välillä vaan niiden ulkopuolella.
- Varaus menee aikaisemman varauksen kanssa päällekkäin
- Aloitus aika on loppumis ajan jälkeen (esim. aloitus 11:00 lopetus 10:00)


---
### Varauksen katselu:
**GET** /reservations?room=B203

Haetaan halutulla huoneen numerolla.

Antaa kaikki varaukset kyseisellä huoneen numerolla, jos sellaisella on tehty varaus. Silloin kun ei löydy annetulla huoneen numerolla yhtään varausta antaa [];

***Malli onnistunut JSON Response:***
```
[
    {
        "id": 0,
        "room": "B203",
        "start": 1769351400,
        "end": 1769355000
    },
    {
        "id": 3,
        "room": "B203",
        "start": 1769580000,
        "end": 1769587200
    },
    {
        "id": 4,
        "room": "B203",
        "start": 1769590800,
        "end": 1769594400
    },
    {
        "id": 6,
        "room": "B203",
        "start": 1769608800,
        "end": 1769616000
    },
    {
        "id": 5,
        "room": "B203",
        "start": 1769619600,
        "end": 1769623200
    }
]
```
Tai 
```
[]
```
---
### Varauksen poistaminen:

**DELETE** /reservations/index.php/{id}

Etsii kyseisellä id:llä olevaa huonevarausta ja poistaa sen. Täytyy olla numeraalinen arvo.

***Malli onnistunut JSON Response:***
```
[
    {
        "message": "Reservation deleted",
        "reservation": {
            "id": 2,
            "room": "B205",
            "start": 1769351400,
            "end": 1769355000
        }
    }
]
```
***Virhetilanteet:***
- Id ei ole numeraalinen
- Id on negatiivinen luku
- Kyseisellä id:llä ei löytynyt varausta

---

## Oletukset:

- Api käyttö on sisäistä / testaamista.
- Ei autentikointia , tokeneita , rooleja, cors tai käyttöoikeuksia
- API tukee vain GET ,POST ja DELETE
- Muistinvarainen tietokanta
- Testaamista varten annettu muutama huonevaraus valmiiksi.
- Toiminnallisuudet:
  - Varauksen luonti: Varaa huone tietylle aikavälille.
  - Varauksen peruutus: Poista varaus.
  - Varausten katselu: Listaa kaikki tietyn huoneen varaukset. 
- Business rule huomioitu:
  - Varaukset eivät saa mennä päällekkäin (kaksi henkilöä ei voi varata samaa huonetta
samaan aikaan).
  - Varaukset eivät voi sijoittua menneisyyteen.
  - Aloitusajan täytyy olla ennen lopetusaikaa.
- Varaus voidaan tehdä milloin halutaan, mutta varauksen pitää olla 8 ja 20 välillä (Ajateltu jotain toimisto aikoja tämän kanssa sekä esim. varaukset eivät sijoittuisi yölliseen aikaan)
