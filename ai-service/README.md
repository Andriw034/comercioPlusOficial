# ComercioPlus AI Service

Microservicio Python con Claude AI para consultas de compatibilidad de repuestos.

## Instalación
```bash
# Crear entorno virtual
python -m venv venv

# Activar entorno (Windows)
venv\Scripts\activate

# Instalar dependencias
pip install -r requirements.txt
```

## Configuración

1. Copia `.env.example` a `.env`
2. Agrega tu `ANTHROPIC_API_KEY`
3. Verifica credenciales MySQL

## Ejecutar
```bash
python app.py
```

Servidor: http://localhost:5000
