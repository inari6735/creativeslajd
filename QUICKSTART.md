# 🚀 Quick Start - CreativeSlajd

## Deployment w 5 krokach

### 1. Przygotuj środowisko

```bash
# Sklonuj repozytorium (jeśli jeszcze nie masz)
git clone <repository-url>
cd telewizorek

# Skopiuj przykładową konfigurację
cp .env.production.example .env.production
```

### 2. Wygeneruj klucze bezpieczeństwa

```bash
./deploy.sh generate-keys
```

Skopiuj wygenerowane klucze do `.env.production`:
- `APP_SECRET` - klucz szyfrowania Symfony
- `DB_PASSWORD` - hasło do bazy danych

### 3. Skonfiguruj domenę

Edytuj `.env.production`:

```env
DOMAIN=twoja-domena.com
CADDY_EMAIL=twoj-email@example.com
```

**⚠️ WAŻNE:** Twoja domena **MUSI** wskazywać na serwer, na którym uruchamiasz aplikację!

### 4. Waliduj konfigurację

```bash
./deploy.sh validate
```

### 5. Uruchom aplikację

```bash
./deploy.sh start
```

## ✅ To wszystko!

Aplikacja jest dostępna pod adresem: `https://twoja-domena.com`

Caddy automatycznie:
- ✅ Uzyska certyfikat SSL od Let's Encrypt
- ✅ Skonfiguruje HTTPS
- ✅ Będzie odnawiał certyfikat automatycznie

## 📊 Sprawdź status

```bash
# Status kontenerów
./deploy.sh status

# Logi
./deploy.sh logs

# Logi konkretnego serwisu
./deploy.sh logs app
./deploy.sh logs caddy
./deploy.sh logs db
```

## 🔧 Podstawowe komendy

```bash
./deploy.sh start      # Uruchom
./deploy.sh stop       # Zatrzymaj
./deploy.sh restart    # Restartuj
./deploy.sh status     # Status
./deploy.sh logs       # Logi
./deploy.sh backup-db  # Backup bazy
./deploy.sh update     # Aktualizuj
./deploy.sh help       # Pomoc
```

## 📝 Tworzenie pierwszego użytkownika

Po uruchomieniu aplikacji:

1. Otwórz w przeglądarce: `https://twoja-domena.com`
2. Kliknij "Zarejestruj się"
3. Wypełnij formularz rejestracji
4. Zaloguj się i zacznij tworzyć pokazy slajdów!

## 🐛 Troubleshooting

### Aplikacja nie startuje

```bash
./deploy.sh logs app
```

### Problemy z certyfikatem SSL

Sprawdź czy domena wskazuje na Twój serwer:

```bash
nslookup twoja-domena.com
```

Sprawdź logi Caddy:

```bash
./deploy.sh logs caddy
```

### Baza danych nie działa

```bash
./deploy.sh logs db
```

## 📚 Więcej informacji

- Pełna dokumentacja: [DEPLOYMENT.md](DEPLOYMENT.md)
- Docker Compose: [docker-compose.yml](docker-compose.yml)
- Konfiguracja Caddy: [Caddyfile](Caddyfile)

## 🆘 Potrzebujesz pomocy?

1. Sprawdź logi: `./deploy.sh logs`
2. Sprawdź status: `./deploy.sh status`
3. Przeczytaj pełną dokumentację: [DEPLOYMENT.md](DEPLOYMENT.md)

---

**Sukces!** 🎉 Twoja aplikacja CreativeSlajd działa z automatycznym HTTPS!
