# CreativeSlajd - Production Deployment

## Wymagania

- Docker Engine 20.10+
- Docker Compose v2.0+
- Domena wskazująca na Twój serwer (dla automatycznego HTTPS)
- Porty 80 i 443 otwarte w firewall

## Szybki start

### 1. Przygotowanie środowiska

```bash
# Skopiuj przykładowy plik konfiguracyjny
cp .env.production.example .env.production

# Edytuj plik .env.production i ustaw swoje wartości
nano .env.production
```

### 2. Generowanie kluczy bezpieczeństwa

```bash
# APP_SECRET (32 znaki hex)
openssl rand -hex 32

# DB_PASSWORD (silne hasło)
openssl rand -base64 32
```

### 3. Konfiguracja domeny

W pliku `.env.production` ustaw:
```env
DOMAIN=twoja-domena.com
CADDY_EMAIL=twoj-email@example.com
```

**WAŻNE:** Domena musi być prawidłowo skonfigurowana w DNS i wskazywać na Twój serwer!

### 4. Uruchomienie aplikacji

```bash
# Zbuduj i uruchom wszystkie kontenery
docker compose --env-file .env.production up -d --build

# Sprawdź status
docker compose ps

# Sprawdź logi
docker compose logs -f
```

### 5. Pierwsze uruchomienie

Po uruchomieniu aplikacja automatycznie:
- Zbuduje obrazy Docker
- Uruchomi bazę danych PostgreSQL
- Wykona migracje bazy danych
- Skonfiguruje Caddy z automatycznym HTTPS
- Uzyska certyfikat SSL od Let's Encrypt

## Konfiguracja

### Zmienne środowiskowe

Wszystkie zmienne konfiguracyjne są w pliku `.env.production`:

#### Aplikacja
- `APP_ENV` - Środowisko (prod)
- `APP_SECRET` - Klucz szyfrowania Symfony
- `DEFAULT_LOCALE` - Domyślny język (pl/en/de)

#### Domena i HTTPS
- `DOMAIN` - Twoja domena (bez http/https)
- `CADDY_EMAIL` - Email dla powiadomień Let's Encrypt
- `HTTP_PORT` - Port HTTP (domyślnie 80)
- `HTTPS_PORT` - Port HTTPS (domyślnie 443)

#### Baza danych
- `DB_NAME` - Nazwa bazy danych
- `DB_USER` - Użytkownik bazy danych
- `DB_PASSWORD` - Hasło do bazy danych
- `DB_VERSION` - Wersja PostgreSQL (domyślnie 16)

#### PHP
- `PHP_MEMORY_LIMIT` - Limit pamięci PHP
- `PHP_UPLOAD_MAX_FILESIZE` - Maksymalny rozmiar pliku
- `PHP_POST_MAX_SIZE` - Maksymalny rozmiar POST

## Zarządzanie

### Podstawowe komendy

```bash
# Start
docker compose --env-file .env.production up -d

# Stop
docker compose down

# Restart
docker compose restart

# Logi wszystkich serwisów
docker compose logs -f

# Logi konkretnego serwisu
docker compose logs -f app
docker compose logs -f caddy
docker compose logs -f db

# Status
docker compose ps

# Statystyki zasobów
docker stats
```

### Aktualizacja aplikacji

```bash
# 1. Zatrzymaj kontenery
docker compose down

# 2. Pobierz najnowszy kod
git pull

# 3. Przebuduj i uruchom
docker compose --env-file .env.production up -d --build

# 4. Sprawdź logi
docker compose logs -f app
```

### Uruchamianie komend Symfony

```bash
# Konsola Symfony
docker compose exec app php bin/console

# Cache clear
docker compose exec app php bin/console cache:clear

# Migracje
docker compose exec app php bin/console doctrine:migrations:migrate

# Lista użytkowników
docker compose exec app php bin/console app:list:users
```

## Backup

### Backup bazy danych

```bash
# Utworzenie backupu
docker compose exec db pg_dump -U ${DB_USER} ${DB_NAME} > backup_$(date +%Y%m%d_%H%M%S).sql

# Lub użyj skryptu
docker compose exec db pg_dump -U creativeslajd_user creativeslajd | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz
```

### Przywracanie z backupu

```bash
# Z pliku SQL
docker compose exec -T db psql -U ${DB_USER} ${DB_NAME} < backup.sql

# Z pliku .gz
gunzip -c backup.sql.gz | docker compose exec -T db psql -U ${DB_USER} ${DB_NAME}
```

### Backup plików (uploads)

```bash
# Backup katalogu uploads
docker run --rm -v creativeslajd_uploads:/data -v $(pwd):/backup alpine tar czf /backup/uploads_backup_$(date +%Y%m%d_%H%M%S).tar.gz /data

# Przywracanie
docker run --rm -v creativeslajd_uploads:/data -v $(pwd):/backup alpine tar xzf /backup/uploads_backup.tar.gz -C /
```

## Monitorowanie

### Healthchecks

Wszystkie serwisy mają skonfigurowane healthchecks:

```bash
# Sprawdź status zdrowia
docker compose ps

# Szczegółowe info
docker inspect creativeslajd-app | grep -A 10 Health
```

### Logi

```bash
# Wszystkie logi
docker compose logs -f

# Tylko błędy
docker compose logs -f | grep -i error

# Ostatnie 100 linii
docker compose logs --tail=100
```

## Bezpieczeństwo

### HTTPS

Caddy automatycznie:
- Uzyskuje certyfikaty SSL od Let's Encrypt
- Odnawia certyfikaty przed wygaśnięciem
- Przekierowuje HTTP → HTTPS
- Wspiera HTTP/2 i HTTP/3

### Security Headers

Aplikacja ma skonfigurowane nagłówki bezpieczeństwa:
- X-Content-Type-Options
- X-Frame-Options
- X-XSS-Protection
- Referrer-Policy
- Content-Security-Policy (opcjonalnie)

### HSTS (opcjonalnie)

Po upewnieniu się, że wszystko działa, możesz włączyć HSTS w `Caddyfile`:

```
Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
```

## Troubleshooting

### Aplikacja nie startuje

```bash
# Sprawdź logi
docker compose logs -f app

# Sprawdź konfigurację
docker compose config

# Sprawdź zmienne środowiskowe
docker compose exec app env | grep APP_
```

### Problemy z certyfikatem SSL

```bash
# Sprawdź logi Caddy
docker compose logs -f caddy

# Sprawdź czy domena wskazuje na serwer
nslookup twoja-domena.com

# Sprawdź czy porty są otwarte
curl -I http://twoja-domena.com
curl -I https://twoja-domena.com
```

### Problemy z bazą danych

```bash
# Sprawdź logi
docker compose logs -f db

# Sprawdź połączenie
docker compose exec app php bin/console doctrine:schema:validate

# Połącz się do bazy
docker compose exec db psql -U ${DB_USER} ${DB_NAME}
```

### Brak miejsca na dysku

```bash
# Usuń nieużywane obrazy
docker image prune -a

# Usuń nieużywane wolumeny
docker volume prune

# Sprawdź rozmiar
docker system df
```

## Performance Tuning

### PostgreSQL

Dostosuj parametry w `docker-compose.yml` w sekcji `db.command` według specyfikacji serwera.

### PHP-FPM

Zwiększ limity w `.env.production`:
```env
PHP_MEMORY_LIMIT=1024M
PHP_UPLOAD_MAX_FILESIZE=50M
PHP_POST_MAX_SIZE=50M
```

### Caddy Cache

Caddy automatycznie cachuje pliki statyczne na 1 rok.

## Wsparcie

W razie problemów:
1. Sprawdź logi: `docker compose logs -f`
2. Sprawdź status: `docker compose ps`
3. Sprawdź konfigurację: `docker compose config`

## Licencja

CreativeSlajd © 2024
