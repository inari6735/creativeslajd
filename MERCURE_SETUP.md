# Mercure - Instrukcja uruchomienia

## Wymagania

Mercure jest już wbudowany w FrankenPHP jako moduł Caddy, więc **nie musisz** dodawać osobnego serwisu Docker.

## Konfiguracja

### 1. Zmienne środowiskowe

Upewnij się, że masz poprawnie skonfigurowane zmienne w pliku `.env` lub `.env.local`:

```env
# Mercure JWT Secret - użyj silnego losowego klucza
CADDY_MERCURE_JWT_SECRET=!ChangeThisMercureHubJWTSecretKey!

# Wewnętrzny URL dla publishera (backend)
MERCURE_URL=http://php/.well-known/mercure

# Publiczny URL dla subskrybentów (frontend)
MERCURE_PUBLIC_URL=https://localhost/.well-known/mercure
```

**WAŻNE**: W produkcji zmień `CADDY_MERCURE_JWT_SECRET` na silny losowy klucz!

### 2. Uruchomienie

```bash
# Zbuduj i uruchom kontenery
docker compose up -d --build

# Sprawdź logi
docker compose logs -f php
```

### 3. Weryfikacja

#### Sprawdź endpoint Mercure:
```bash
curl -I https://localhost/.well-known/mercure
```

Powinno zwrócić status `200` lub `401` (wymaga autoryzacji).

#### Test w przeglądarce:

1. Otwórz stronę edycji pokazu: `/slideshow/{id}/edit`
2. Otwórz odtwarzacz w nowej karcie: `/player/{id}` lub publiczny link `/share/{token}`
3. W pierwszej karcie dodaj nowy slajd
4. Odtwarzacz powinien automatycznie zaktualizować się bez odświeżania!

## Jak działa

### Backend (Publisher)

Gdy dodajesz/usuwasz/aktualizujesz slajd:

1. **Doctrine Event** → `SlideshowUpdateSubscriber::postPersist/postRemove/postUpdate`
2. **Subscriber publikuje** update do Mercure Hub na topic `slideshow/{id}`
3. **Mercure Hub** przekazuje update wszystkim subskrybentom tego topicu

### Frontend (Subscriber)

W odtwarzaczu (`templates/player/show.html.twig`):

1. **EventSource** łączy się z `/.well-known/mercure?topic=slideshow/{id}`
2. **Nasłuchuje** wiadomości od Mercure Hub
3. Gdy otrzyma update → **przebudowuje slajdy** dynamicznie
4. **Kontynuuje odtwarzanie** od miejsca, w którym był

## Konfiguracja w Caddy

Mercure jest skonfigurowany w `frankenphp/Caddyfile`:

```caddyfile
mercure {
    publisher_jwt {env.MERCURE_PUBLISHER_JWT_KEY}
    subscriber_jwt {env.MERCURE_SUBSCRIBER_JWT_KEY}
    anonymous  # Pozwala na anonimowe subskrypcje
    subscriptions  # Włącza API subskrypcji
}
```

## Debugowanie

### Włącz logi Mercure:

W `compose.yaml` dodaj do środowiska `php`:

```yaml
MERCURE_DEBUG: 1
CADDY_DEBUG: 1
```

### Sprawdź połączenie w konsoli przeglądarki:

```javascript
const url = new URL('/.well-known/mercure', window.location.origin);
url.searchParams.append('topic', 'slideshow/1');

const eventSource = new EventSource(url);
eventSource.onmessage = (e) => console.log('Message:', e.data);
eventSource.onerror = (e) => console.error('Error:', e);
```

## Troubleshooting

### Problem: "Failed to connect to Mercure"

**Rozwiązanie**: Sprawdź czy FrankenPHP działa:
```bash
docker compose ps
docker compose logs php
```

### Problem: "CORS error"

**Rozwiązanie**: Mercure w FrankenPHP automatycznie obsługuje CORS. Upewnij się, że używasz tego samego hostname co aplikacja.

### Problem: "Updates nie przychodzą"

**Rozwiązanie**: 
1. Sprawdź logi: `docker compose logs -f php`
2. Sprawdź czy `SlideshowUpdateSubscriber` jest zarejestrowany: `bin/console debug:event-dispatcher`
3. Sprawdź czy slajdy są faktycznie zapisywane w bazie

### Problem: "401 Unauthorized"

**Rozwiązanie**: To normalne dla publishera. Subskrybenci (frontend) używają anonimowego dostępu dzięki `anonymous` w konfiguracji Caddy.

## Produkcja

W produkcji:

1. **Wygeneruj silny JWT secret**:
   ```bash
   openssl rand -base64 32
   ```

2. **Ustaw w `.env.production`**:
   ```env
   CADDY_MERCURE_JWT_SECRET=<wygenerowany_secret>
   ```

3. **Użyj HTTPS** (FrankenPHP automatycznie używa Let's Encrypt)

4. **Ustaw poprawny publiczny URL**:
   ```env
   MERCURE_PUBLIC_URL=https://yourdomain.com/.well-known/mercure
   ```

## Dodatkowe informacje

- Mercure używa Server-Sent Events (SSE)
- Automatyczne reconnect przy utracie połączenia
- Działa przez firewall/proxy (używa HTTP)
- Nie wymaga WebSocket
- Obsługuje tysiące jednoczesnych połączeń

## Dokumentacja

- [Mercure Protocol](https://mercure.rocks/)
- [Symfony Mercure Bundle](https://symfony.com/doc/current/mercure.html)
- [FrankenPHP](https://frankenphp.dev/)
