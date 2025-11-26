To jest projekt napisany w Symfony. Używa bazy danych PostgreSQL. Jest skonfigurowany za pomocą dunglas/symfony-docker. Używa Asset Mappera i Stimulus Bundle do frontowych rzeczy.

## Real-time Updates z Mercure

Projekt wykorzystuje Symfony Mercure Bundle do real-time aktualizacji pokazów slajdów:

- **Mercure Hub**: Wbudowany w FrankenPHP jako moduł Caddy (nie wymaga osobnego serwisu)
- **EventSubscriber**: `SlideshowUpdateSubscriber` publikuje zmiany gdy slajd jest dodany/usunięty/zaktualizowany
- **Topic pattern**: `slideshow/{id}` - każdy pokaz ma swój topic
- **Live updates**: Odtwarzacz automatycznie odbiera i wyświetla nowe/usunięte slajdy bez przeładowania strony
- **Endpoint**: `/.well-known/mercure` (dostępny przez FrankenPHP)

### Jak to działa:
1. Gdy użytkownik doda/usunie slajd, Doctrine wywołuje EventSubscriber
2. EventSubscriber publikuje update przez Mercure Hub
3. Odtwarzacz nasłuchuje przez EventSource API
4. Gdy otrzyma update, przebudowuje slajdy bez przerywania odtwarzania

