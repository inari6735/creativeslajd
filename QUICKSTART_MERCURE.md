# Quick Start - Mercure Real-time Updates

## 🚀 Szybki start (5 minut)

### Krok 1: Uruchom aplikację

```bash
# Zbuduj i uruchom kontenery
docker compose up -d --build

# Zaczekaj aż wszystko się uruchomi (~30 sekund)
docker compose logs -f php
```

### Krok 2: Sprawdź czy działa

```bash
# Uruchom skrypt testowy
./test-mercure.sh
```

Wszystkie pozycje powinny być ✓ (zielone).

### Krok 3: Testuj!

1. **Otwórz przeglądarkę**: https://localhost
2. **Zaloguj się** lub zarejestruj konto
3. **Utwórz pokaz** i dodaj kilka slajdów
4. **Kliknij "Odtwórz"** - otworzy się w nowej karcie
5. **W pierwszej karcie** dodaj kolejny slajd
6. **W drugiej karcie** (odtwarzacz) **automatycznie pojawi się nowy slajd!** 🎉

---

## 📹 Test demo

### Wersja 1: Prywatny pokaz (wymaga logowania)

```
Tab 1: https://localhost/slideshow/1/edit  → Dodaj slajd
Tab 2: https://localhost/player/1          → Zobacz aktualizację na żywo
```

### Wersja 2: Publiczny pokaz (bez logowania)

1. W edycji pokazu zaznacz: ☑ **"Udostępnij publicznie"**
2. Zapisz pokaz
3. Skopiuj **"Link publiczny"** (np. `https://localhost/share/abc123...`)
4. Otwórz link w **trybie incognito** lub **innej przeglądarce**
5. Dodaj slajd w edycji
6. **Publiczny odtwarzacz też się zaktualizuje!** 🎉

### Wersja 3: Edycja publiczna

1. W edycji pokazu zaznacz: ☑ **"Pozwól na publiczną edycję"**
2. Zapisz pokaz
3. Skopiuj **"Link publiczny (edycja)"**
4. Otwórz w trybie incognito
5. Dodaj slajd **bez logowania**
6. Wszystkie odtwarzacze tego pokazu się zaktualizują!

---

## 🔍 Debugowanie

### Sprawdź konsolę przeglądarki (F12)

W odtwarzaczu powinieneś zobaczyć:

```
Player script loaded
Connecting to Mercure: https://localhost/.well-known/mercure?topic=slideshow/1
✅ Mercure connection established
```

Po dodaniu slajdu:

```
📨 Mercure update received: {"action":"slide_added",...}
🔄 Handling slideshow update: slide_added
✨ Slideshow rebuilt with 5 slides
```

### Sprawdź logi Docker

```bash
docker compose logs -f php
```

Powinieneś zobaczyć (po dodaniu slajdu):

```
[info] Mercure update published to topic: slideshow/1
```

---

## ⚡ Szybkie rozwiązywanie problemów

### ❌ "Failed to connect to Mercure"

```bash
# Sprawdź czy FrankenPHP działa
docker compose ps

# Zrestartuj kontenery
docker compose restart php
```

### ❌ Updates nie przychodzą

```bash
# Wyczyść cache Symfony
docker compose exec php php bin/console cache:clear

# Sprawdź EventSubscriber
docker compose exec php php bin/console debug:event-dispatcher | grep Slideshow
```

### ❌ "ERR_CERT_AUTHORITY_INVALID" w przeglądarce

To normalne w development z self-signed certyfikatem. Kliknij **"Zaawansowane" → "Przejdź do strony"**.

---

## 📊 Co się dzieje w tle?

```
┌─────────────┐
│  Browser 1  │  Dodaje slajd przez formularz
│   (Edit)    │
└──────┬──────┘
       │ POST /slide/upload/1
       ▼
┌─────────────────┐
│   PHP/Symfony   │  Zapisuje w bazie
└──────┬──────────┘
       │ Doctrine postPersist event
       ▼
┌─────────────────────────┐
│ SlideshowUpdateSubscriber│  Publikuje do Mercure
└──────┬──────────────────┘
       │ POST /.well-known/mercure
       ▼
┌─────────────┐
│ Mercure Hub │  Przekazuje wszystkim subskrybentom
│ (FrankenPHP)│
└──────┬──────┘
       │ Server-Sent Events
       ▼
┌─────────────┐
│  Browser 2  │  Otrzymuje JSON z nowym slajdem
│  (Player)   │  Przebudowuje DOM bez refresh
└─────────────┘
```

---

## 🎯 Kluczowe funkcje

✅ **Automatyczne aktualizacje** - bez potrzeby odświeżania  
✅ **Kontynuacja odtwarzania** - nie przerywa prezentacji  
✅ **Działa publicznie** - także dla gości (shareToken)  
✅ **Synchronizacja kompletna** - zawsze aktualna lista  
✅ **Auto-reconnect** - przywraca połączenie po awarii  
✅ **Zero opóźnień** - aktualizacje w <100ms  

---

## 🔐 Produkcja

Przed wdrożeniem na produkcję:

1. **Zmień JWT secret**:
   ```bash
   openssl rand -base64 32
   ```
   
2. **Ustaw w `.env.production`**:
   ```env
   CADDY_MERCURE_JWT_SECRET=<wygenerowany_secret>
   ```

3. **Użyj HTTPS** (FrankenPHP automatycznie z Let's Encrypt)

4. **Ustaw właściwy domain**:
   ```env
   MERCURE_PUBLIC_URL=https://yourdomain.com/.well-known/mercure
   ```

---

## 📚 Więcej informacji

- [MERCURE_SETUP.md](./MERCURE_SETUP.md) - Pełna dokumentacja
- [CHANGELOG_MERCURE.md](./CHANGELOG_MERCURE.md) - Szczegóły implementacji
- [Mercure Protocol](https://mercure.rocks/) - Oficjalna dokumentacja

---

**Gotowe! Twoje pokazy slajdów aktualizują się na żywo! 🎉**
