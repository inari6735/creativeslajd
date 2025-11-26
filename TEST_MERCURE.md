# Test Mercure Real-time Updates

## Krok po kroku - testowanie

### 1. Otwórz publiczny pokaz

Znajdź swój shareToken w `/slideshow/1/edit` (w sekcji "Link publiczny")

Następnie otwórz:
```
https://localhost/share/{twoj-shareToken}
```

### 2. Otwórz konsolę przeglądarki

Naciśnij **F12** lub **Cmd+Option+I** (Mac)

Przejdź do zakładki **Console**

### 3. Sprawdź logi

Powinieneś zobaczyć:
```
Player script loaded
Connecting to Mercure: https://localhost/.well-known/mercure?topic=slideshow/1
✅ Mercure connection established
```

**JEŚLI** widzisz błąd SSL/certyfikat:
1. Otwórz w nowej karcie: `https://localhost/.well-known/mercure?topic=slideshow/1`
2. Zaakceptuj certyfikat (kliknij "Zaawansowane" → "Przejdź do localhost")
3. Odśwież odtwarzacz

### 4. Dodaj slajd w innej karcie

W nowej karcie otwórz:
```
https://localhost/slideshow/1/edit
```

Dodaj nowe zdjęcie

### 5. Sprawdź konsolę odtwarzacza

Powinieneś zobaczyć:
```
📨 Mercure update received: {"action":"slide_added","slideshowId":1,...}
🔄 Handling slideshow update: slide_added
✨ Slideshow rebuilt with 8 slides
```

I **slajd pojawi się automatycznie** bez odświeżania!

### 6. Sprawdź zakładkę Network

W DevTools przejdź do zakładki **Network**

Znajdź połączenie do `mercure?topic=slideshow/1`

- **Type**: eventsource
- **Status**: 200 lub pending (to normalne - połączenie jest ciągłe)

## Troubleshooting

### Nie widzę logów w konsoli

**Rozwiązanie**: Wyczyść cache przeglądarki (**Cmd+Shift+R** na Mac)

### Błąd: "Failed to connect to EventSource"

**Rozwiązanie**: 
1. Sprawdź czy masz zaakceptowany certyfikat SSL dla `/.well-known/mercure`
2. Otwórz `https://localhost/.well-known/mercure?topic=slideshow/1` w nowej karcie
3. Zaakceptuj certyfikat

### Status 401 Unauthorized w Network

**To normalne!** Subscriber używa anonimowego dostępu, więc 401 na poziomie HTTP jest OK.

Ważne jest aby w konsoli widzieć: `✅ Mercure connection established`

### Widzę "Mercure connection established" ale update nie przychodzi

**Sprawdź**:
1. Czy dodajesz slajd do **tego samego** pokazu (slideshow ID musi się zgadzać)
2. Czy w logach Docker widzisz: `http.handlers.mercure Update published {"topics": ["slideshow/1"]}`

Sprawdź logi:
```bash
docker compose logs -f php | grep mercure
```

### Update przychodzi ale slajdy się nie przebudowują

Otwórz konsolę i sprawdź czy nie ma błędów JavaScript.

Sprawdź czy `rebuildSlideshow` się wywołuje:
```
🔄 Handling slideshow update: slide_added
✨ Slideshow rebuilt with X slides
```

## Potwierdzenie że działa

Gdy wszystko działa poprawnie:

1. ✅ W konsoli widzisz: `✅ Mercure connection established`
2. ✅ Po dodaniu slajdu widzisz: `📨 Mercure update received`
3. ✅ Widzisz: `✨ Slideshow rebuilt with X slides`
4. ✅ **Nowy slajd pojawia się automatycznie w odtwarzaczu!**

---

**Jeśli masz problemy, pokaż mi:**
- Screenshot konsoli przeglądarki (Console)
- Screenshot zakładki Network (filtr: mercure)
- Logi Docker: `docker compose logs php --tail=50 | grep mercure`
