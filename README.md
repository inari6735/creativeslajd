docker compose -f compose.yaml -f compose.prod.yaml --env-file .env.production up -d --build

# Slideshow App - Aplikacja do pokazów slajdów

Aplikacja webowa do tworzenia i zarządzania pokazami slajdów ze zdjęciami, zbudowana w Symfony 7.3.

## Funkcjonalności

### Panel Administracyjny

- ✅ Tworzenie i edycja pokazów slajdów
- ✅ Upload wielu zdjęć jednocześnie (JPG, PNG, GIF)
- ✅ Przeciąganie i upuszczanie do zmiany kolejności slajdów
- ✅ Usuwanie pokazów i pojedynczych slajdów
- ✅ Podgląd miniatur w trybie edycji

### Odtwarzacz

- ✅ Pełnoekranowy odtwarzacz pokazów
- ✅ Automatyczne przełączanie slajdów (5 sekund)
- ✅ Pasek postępu z animacją
- ✅ Sterowanie klawiaturą:
    - `←/→` - poprzedni/następny slajd
    - `Spacja` - play/pauza
    - `F` - pełny ekran
    - `ESC` - wyjście

### Design

- 🎨 Kolorystyka w stylu Kudobox
    - Primary Red: `#c8433b`
    - Dark Gray: `#333333`
    - Light Gray: `#f5f5f5`
- 📱 Responsywny design
- ✨ Animacje i efekty przejść

## Wymagania

- PHP 8.2 lub nowszy
- Composer
- SQLite (lub PostgreSQL/MySQL)

## Instalacja

1. Sklonuj repozytorium:

```bash
git clone <repo-url>
cd telewizorek
```

2. Zainstaluj zależności:

```bash
composer install
```

3. Skonfiguruj bazę danych w pliku `.env`:

```env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_%kernel.environment%.db"
```

4. Uruchom migracje:

```bash
php bin/console doctrine:migrations:migrate
```

5. Utwórz katalog na uploady:

```bash
mkdir -p public/uploads/slides
chmod 777 public/uploads/slides
```

6. Uruchom serwer deweloperski:

```bash
symfony server:start
# lub
php -S localhost:8000 -t public/
```

7. Otwórz aplikację w przeglądarce:

```
http://localhost:8000
```

## Struktura projektu

```
src/
├── Controller/
│   ├── HomeController.php         # Redirect na listę pokazów
│   ├── SlideshowController.php    # Zarządzanie pokazami
│   ├── SlideController.php        # Upload i zarządzanie slajdami
│   └── PlayerController.php       # Odtwarzacz pełnoekranowy
├── Entity/
│   ├── Slideshow.php              # Encja pokazu slajdów
│   └── Slide.php                  # Encja pojedynczego slajdu
├── Form/
│   ├── SlideshowType.php          # Formularz pokazu
│   └── SlideType.php              # Formularz uploadu
├── Repository/
│   ├── SlideshowRepository.php
│   └── SlideRepository.php
└── Service/
    └── FileUploader.php           # Serwis do uploadu plików

templates/
├── base.html.twig                 # Layout bazowy z nawigacją
├── slideshow/
│   ├── index.html.twig            # Lista pokazów (grid)
│   ├── form.html.twig             # Tworzenie nowego pokazu
│   └── edit.html.twig             # Edycja pokazu + zarządzanie slajdami
└── player/
    └── show.html.twig             # Pełnoekranowy odtwarzacz

public/
├── styles/
│   └── app.css                    # Style z kolorystyką Kudobox
├── js/
│   └── drag-drop.js               # Drag & drop dla slajdów
└── uploads/
    └── slides/                    # Katalog na uploady
```

## Użytkowanie

### Tworzenie nowego pokazu

1. Kliknij "Utwórz nowy pokaz" na stronie głównej
2. Wypełnij nazwę i opcjonalny opis
3. Kliknij "Utwórz pokaz"

### Dodawanie slajdów

1. Na stronie edycji pokazu kliknij w obszar uploadu lub przeciągnij pliki
2. Wybierz jedno lub więcej zdjęć
3. Kliknij "Dodaj zdjęcia"

### Zmiana kolejności slajdów

Przeciągnij i upuść slajdy w żądanej kolejności. Zmiany są automatycznie zapisywane.

### Odtwarzanie pokazu

1. Kliknij przycisk "▶️ Odtwórz" na karcie pokazu lub w widoku edycji
2. Pokaz otworzy się w nowej karcie w trybie pełnoekranowym
3. Użyj przycisków na dole ekranu lub klawiatury do sterowania

## Konfiguracja

### Zmiana czasu wyświetlania slajdu

W pliku `templates/player/show.html.twig` zmień wartość:

```javascript
const SLIDE_DURATION = 5000; // czas w milisekundach
```

### Zmiana maksymalnego rozmiaru pliku

W pliku `src/Form/SlideType.php`:

```php
'maxSize' => '10M', // zmień na np. '20M'
```

### Obsługiwane formaty obrazów

Domyślnie: JPG, JPEG, PNG, GIF

## Troubleshooting

### Problem z uploadem plików

Sprawdź uprawnienia do katalogu:

```bash
chmod -R 777 public/uploads/slides
```

### Brak stylów CSS

Wyczyść cache:

```bash
php bin/console cache:clear
```

### Błędy bazy danych

Sprawdź połączenie w `.env` i uruchom ponownie migracje:

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

## Licencja

Proprietary

## Autor

Created with ❤️ using Symfony
