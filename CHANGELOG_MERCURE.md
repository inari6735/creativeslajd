# Podsumowanie zmian - Real-time Updates z Mercure

## Dodane pliki

### 1. `/src/EventSubscriber/SlideshowUpdateSubscriber.php`
EventSubscriber Doctrine, który nasłuchuje na zmiany w slajdach i publikuje aktualizacje przez Mercure.

**Funkcje:**
- `postPersist()` - po dodaniu nowego slajdu
- `postRemove()` - po usunięciu slajdu  
- `postUpdate()` - po aktualizacji slajdu
- Publikuje na topic: `slideshow/{id}`
- Wysyła kompletną listę wszystkich slajdów dla łatwej synchronizacji

### 2. `/MERCURE_SETUP.md`
Kompletna dokumentacja konfiguracji i użycia Mercure:
- Wymagania
- Konfiguracja zmiennych środowiskowych
- Instrukcja uruchomienia
- Weryfikacja działania
- Debugowanie
- Troubleshooting
- Konfiguracja produkcyjna

### 3. `/.env.mercure.example`
Przykładowy plik z konfiguracją Mercure do skopiowania do `.env.local`

### 4. `/test-mercure.sh`
Skrypt Bash do testowania konfiguracji Mercure:
- Sprawdza kontenery Docker
- Testuje endpoint Mercure
- Weryfikuje konfigurację Symfony
- Sprawdza EventSubscriber
- Waliduje zmienne środowiskowe

## Zmodyfikowane pliki

### 1. `/src/Controller/PlayerController.php`
**Dodano:**
- Przekazanie zmiennej `mercureTopic` do widoku
- Topic w formacie: `slideshow/{id}`

### 2. `/src/Controller/PublicPlayerController.php`
**Dodano:**
- Przekazanie zmiennej `mercureTopic` do widoku dla publicznych pokazów
- Topic w formacie: `slideshow/{id}`

### 3. `/templates/player/show.html.twig`
**Dodano:**
- Zmienne JavaScript: `MERCURE_TOPIC`, `MERCURE_URL`, `SLIDESHOW_ID`
- Funkcja `initializeMercure()` - łączy się z Mercure Hub przez EventSource API
- Funkcja `handleSlideshowUpdate()` - obsługuje przychodzące aktualizacje
- Funkcja `rebuildSlideshow()` - dynamicznie przebudowuje slajdy bez przerywania odtwarzania
- Obsługa błędów połączenia i automatyczne reconnect
- Zamykanie połączenia przy opuszczaniu strony

**Zmieniono:**
- `slides` i `totalSlides` z `const` na `let` (aby można było je aktualizować)

### 4. `/AGENTS.md`
**Dodano:**
- Sekcja "Real-time Updates z Mercure"
- Opis architektury i działania systemu real-time updates

### 5. `/composer.json` (automatycznie)
**Dodano zależności:**
- `symfony/mercure-bundle: ^0.4.2`
- `symfony/mercure: ^0.7.1`
- `lcobucci/jwt: 5.6.0`

### 6. `/config/packages/mercure.yaml` (automatycznie przez recipe)
**Skonfigurowano:**
- Hub domyślny (`default`)
- URL wewnętrzny i publiczny z variables środowiskowych
- JWT secret i uprawnienia do publikowania

## Istniejąca konfiguracja (bez zmian)

### `/compose.yaml`
**Już zawiera:**
- Zmienne środowiskowe Mercure:
  - `MERCURE_PUBLISHER_JWT_KEY`
  - `MERCURE_SUBSCRIBER_JWT_KEY`
  - `MERCURE_URL`
  - `MERCURE_PUBLIC_URL`
  - `MERCURE_JWT_SECRET`

### `/frankenphp/Caddyfile`
**Już zawiera:**
- Konfigurację modułu Mercure w Caddy
- Publisher/Subscriber JWT
- Anonimowy dostęp dla subskrybentów
- API subskrypcji
- Endpoint: `/.well-known/mercure`

## Jak to działa

### Scenariusz: Użytkownik dodaje slajd

1. **Użytkownik** uploaduje zdjęcie przez formularz (`SlideController::upload`)
2. **Doctrine** zapisuje nową encję `Slide` w bazie
3. **Event `postPersist`** → wywołuje `SlideshowUpdateSubscriber::postPersist()`
4. **Subscriber** publikuje JSON przez Mercure Hub:
   ```json
   {
     "action": "slide_added",
     "slideshowId": 123,
     "slide": {...},
     "allSlides": [...],
     "totalSlides": 10,
     "timestamp": 1732618800
   }
   ```
5. **Mercure Hub** przekazuje update wszystkim subskrybentom topic `slideshow/123`
6. **Player JavaScript** (EventSource) otrzymuje event
7. **Funkcja `rebuildSlideshow()`**:
   - Pauzuje odtwarzanie
   - Czyści kontenery slajdów
   - Tworzy nowe elementy DOM z zaktualizowaną listą
   - Wznawia odtwarzanie od tego samego indeksu
8. **Użytkownik widzi** nowy slajd bez odświeżania strony! 🎉

## Zalety implementacji

✅ **Zero konfiguracji Docker** - Mercure wbudowany w FrankenPHP  
✅ **Automatyczne reconnect** - EventSource sam się łączy ponownie  
✅ **Brak przerw w odtwarzaniu** - kontynuuje od tego samego slajdu  
✅ **Działa publicznie** - także dla udostępnionych pokazów (shareToken)  
✅ **Obsługa wszystkich akcji** - dodawanie, usuwanie, aktualizacja  
✅ **Kompletna synchronizacja** - wysyła całą listę slajdów  
✅ **Error handling** - łapie błędy i loguje do konsoli  
✅ **Production ready** - obsługa CORS, HTTPS, JWT  

## Testing

### Test manualny:
1. Otwórz edycję pokazu: `/slideshow/{id}/edit`
2. Otwórz odtwarzacz: `/player/{id}` (nowa karta)
3. Dodaj slajd w edycji
4. Obserwuj aktualizację w odtwarzaczu (bez refresh!)

### Test automatyczny:
```bash
./test-mercure.sh
```

### Debug w konsoli przeglądarki:
Otwórz odtwarzacz i sprawdź:
- `✅ Mercure connection established` - połączenie OK
- `📨 Mercure update received: {...}` - otrzymano update
- `🔄 Handling slideshow update: slide_added` - obsługa update
- `✨ Slideshow rebuilt with X slides` - przebudowano slajdy

## Następne kroki (opcjonalnie)

Możliwe rozszerzenia:
- [ ] Aktualizacja widoku edycji (`/slideshow/{id}/edit`) w real-time
- [ ] Powiadomienia toast przy zmianach (np. "Dodano nowy slajd")
- [ ] Wskaźnik "Live" w UI gdy są aktywni widzowie
- [ ] Synchronizacja pozycji odtwarzania między urządzeniami
- [ ] Obsługa zmiany kolejności slajdów w real-time

## Uwagi końcowe

- Mercure używa **Server-Sent Events (SSE)**, nie WebSocket
- Działa przez **standardowy HTTP/HTTPS** (nie blokowane przez firewalle)
- **Automatycznie reconnect** przy utracie połączenia
- W produkcji: **zmień CADDY_MERCURE_JWT_SECRET**!
