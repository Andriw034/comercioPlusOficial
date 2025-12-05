# Makefile para ComercioPlus
# Proporciona comandos de acceso rápido para tareas comunes.

.PHONY: all setup build start list

# Colores para la salida
GREEN=\033[0;32m
NC=\033[0m # No Color

all: list

# Configura el entorno de desarrollo completo
setup:
	@echo "${GREEN}Ejecutando script de setup completo...${NC}"
	@./scripts/setup-local.sh

# Compila los assets del frontend para producción
build:
	@echo "${GREEN}Compilando assets del frontend (producción)...${NC}"
	@npm run build

# Inicia el servidor de desarrollo de Laravel
start:
	@echo "${GREEN}Iniciando servidor de desarrollo en http://0.0.0.0:8000...${NC}"
	@php artisan serve --host=0.0.0.0 --port=8000

# Muestra los comandos disponibles
list:
	@echo "Comandos disponibles:"
	@echo "  ${GREEN}make setup${NC}  -> Ejecuta el script de configuración inicial (Linux/macOS)."
	@echo "  ${GREEN}make build${NC}  -> Compila los assets del frontend."
	@echo "  ${GREEN}make start${NC}  -> Inicia el servidor de desarrollo de Laravel."

