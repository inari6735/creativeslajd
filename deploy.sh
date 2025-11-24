#!/bin/bash

# CreativeSlajd Deployment Script
set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ENV_FILE=".env.production"

# Funkcja do wyświetlania komunikatów
info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Sprawdź czy plik .env.production istnieje
check_env() {
    if [ ! -f "$ENV_FILE" ]; then
        error "Plik $ENV_FILE nie istnieje!"
        info "Skopiuj .env.production.example jako $ENV_FILE i skonfiguruj go:"
        echo "  cp .env.production.example $ENV_FILE"
        echo "  nano $ENV_FILE"
        exit 1
    fi
}

# Wczytaj zmienne środowiskowe
load_env() {
    export $(cat $ENV_FILE | grep -v '^#' | xargs)
}

# Wygeneruj klucze
generate_keys() {
    info "Generowanie kluczy bezpieczeństwa..."
    echo ""
    echo "APP_SECRET (skopiuj do .env.production):"
    openssl rand -hex 32
    echo ""
    echo "DB_PASSWORD (skopiuj do .env.production):"
    openssl rand -base64 32
    echo ""
}

# Walidacja konfiguracji
validate_config() {
    info "Walidacja konfiguracji..."
    
    load_env
    
    if [ -z "$APP_SECRET" ] || [ "$APP_SECRET" == "your-secret-key-here-generate-with-openssl-rand-hex-32" ]; then
        error "APP_SECRET nie jest skonfigurowane!"
        exit 1
    fi
    
    if [ -z "$DB_PASSWORD" ] || [ "$DB_PASSWORD" == "your-strong-database-password-here" ]; then
        error "DB_PASSWORD nie jest skonfigurowane!"
        exit 1
    fi
    
    if [ -z "$DOMAIN" ] || [ "$DOMAIN" == "example.com" ]; then
        error "DOMAIN nie jest skonfigurowane!"
        exit 1
    fi
    
    info "Konfiguracja jest poprawna!"
}

# Start aplikacji
start() {
    check_env
    validate_config
    
    info "Uruchamianie aplikacji..."
    docker compose --env-file $ENV_FILE up -d --build
    
    info "Aplikacja została uruchomiona!"
    info "Sprawdź status: ./deploy.sh status"
    info "Zobacz logi: ./deploy.sh logs"
}

# Stop aplikacji
stop() {
    info "Zatrzymywanie aplikacji..."
    docker compose down
    info "Aplikacja zatrzymana!"
}

# Restart aplikacji
restart() {
    info "Restartowanie aplikacji..."
    docker compose restart
    info "Aplikacja zrestartowana!"
}

# Status
status() {
    docker compose ps
}

# Logi
logs() {
    if [ -z "$1" ]; then
        docker compose logs -f
    else
        docker compose logs -f "$1"
    fi
}

# Backup bazy danych
backup_db() {
    check_env
    load_env
    
    BACKUP_FILE="backup_$(date +%Y%m%d_%H%M%S).sql.gz"
    info "Tworzenie backupu bazy danych: $BACKUP_FILE"
    
    docker compose exec db pg_dump -U "$DB_USER" "$DB_NAME" | gzip > "$BACKUP_FILE"
    
    info "Backup utworzony: $BACKUP_FILE"
}

# Przywróć bazę danych
restore_db() {
    if [ -z "$1" ]; then
        error "Podaj ścieżkę do pliku backupu!"
        echo "Użycie: ./deploy.sh restore-db backup.sql.gz"
        exit 1
    fi
    
    check_env
    load_env
    
    warn "UWAGA: To nadpisze obecną bazę danych!"
    read -p "Czy na pewno chcesz kontynuować? (tak/nie): " confirm
    
    if [ "$confirm" != "tak" ]; then
        info "Anulowano."
        exit 0
    fi
    
    info "Przywracanie bazy danych z: $1"
    gunzip -c "$1" | docker compose exec -T db psql -U "$DB_USER" "$DB_NAME"
    
    info "Baza danych przywrócona!"
}

# Konsola Symfony
console() {
    docker compose exec app php bin/console "$@"
}

# Aktualizacja
update() {
    info "Aktualizacja aplikacji..."
    
    # Backup przed aktualizacją
    backup_db
    
    # Zatrzymaj
    stop
    
    # Pull
    if [ -d .git ]; then
        info "Pobieranie najnowszego kodu..."
        git pull
    fi
    
    # Start
    start
    
    info "Aktualizacja zakończona!"
}

# Help
show_help() {
    cat << EOF
CreativeSlajd - Deployment Script

Użycie: ./deploy.sh [komenda]

Komendy:
  generate-keys    Generuj klucze bezpieczeństwa (APP_SECRET, DB_PASSWORD)
  validate         Waliduj konfigurację w .env.production
  start            Zbuduj i uruchom aplikację
  stop             Zatrzymaj aplikację
  restart          Zrestartuj aplikację
  status           Pokaż status kontenerów
  logs [service]   Pokaż logi (opcjonalnie tylko dla wybranego serwisu)
  backup-db        Utwórz backup bazy danych
  restore-db FILE  Przywróć bazę danych z backupu
  console [args]   Uruchom konsolę Symfony
  update           Aktualizuj aplikację (backup + pull + rebuild)
  help             Pokaż tę pomoc

Przykłady:
  ./deploy.sh generate-keys
  ./deploy.sh start
  ./deploy.sh logs app
  ./deploy.sh console cache:clear
  ./deploy.sh backup-db
  ./deploy.sh restore-db backup_20240101_120000.sql.gz

EOF
}

# Main
case "$1" in
    generate-keys)
        generate_keys
        ;;
    validate)
        check_env
        validate_config
        ;;
    start)
        start
        ;;
    stop)
        stop
        ;;
    restart)
        restart
        ;;
    status)
        status
        ;;
    logs)
        logs "$2"
        ;;
    backup-db)
        backup_db
        ;;
    restore-db)
        restore_db "$2"
        ;;
    console)
        shift
        console "$@"
        ;;
    update)
        update
        ;;
    help|--help|-h)
        show_help
        ;;
    *)
        error "Nieznana komenda: $1"
        echo ""
        show_help
        exit 1
        ;;
esac
