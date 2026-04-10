# GUÍA DE INSTALACIÓN — Kit de IA para ComercioPlus
# ==================================================

## Qué contiene este ZIP

```
AGENT.md              → Reglas generales (funciona con cualquier agente)
CLAUDE.md             → Versión para Claude Code (mismo contenido)
.cursorrules          → Versión para Cursor IDE (resumida)
.mcp.json             → Configuración de herramientas MCP
.skills/
├── registry.md       → Router que decide qué skill cargar
├── frontend-react/
│   └── SKILL.md      → Experto React/TypeScript/Tailwind
├── backend-laravel/
│   └── SKILL.md      → Experto Laravel/Sanctum/MySQL
├── testing/
│   └── SKILL.md      → Experto PHPUnit/Playwright/ESLint
├── inventario/
│   └── SKILL.md      → Experto en scanner/stock/importación
└── pagos/
    └── SKILL.md      → Experto MercadoPago/checkout/fiado
```

## Cómo instalar

### Paso 1: Copiar archivos a la raíz de tu proyecto
```bash
# Desde la raíz de comercioPlusOficial/
cp AGENT.md ./
cp CLAUDE.md ./
cp .cursorrules ./
cp .mcp.json ./
cp -r .skills/ ./
```

### Paso 2: Configurar MCP (opcional pero recomendado)
Edita .mcp.json y reemplaza:
- TU_TOKEN_AQUI → tu GitHub Personal Access Token
- Si no usas Engram todavía, puedes quitar la sección "memory"

### Paso 3: Verificar
```bash
# Estructura esperada después de copiar:
ls -la AGENT.md CLAUDE.md .cursorrules .mcp.json .skills/
```

### Paso 4: Abrir tu IDE
- Claude Code: abre terminal en el proyecto → ejecuta `claude`
  → Claude Code lee CLAUDE.md automáticamente
- Cursor: abre el proyecto en Cursor
  → Cursor lee .cursorrules automáticamente

## Uso recomendado

### Con Claude Code
```bash
cd ~/proyectos/comercioPlusOficial
claude

# Ya puedes pedir cosas como:
# > "Crea un endpoint para listar productos con stock bajo"
# > "Agrega un filtro por categoría en el dashboard de productos"
# > "Escribe tests para el controlador de inventario"
```

### Con Cursor
Abre el proyecto en Cursor y usa el chat (Ctrl+L):
- "Crea un componente de tabla de pedidos para el dashboard"
- "Refactoriza el checkout para manejar errores de MercadoPago"

## Notas importantes

1. El AGENT.md fue creado basándose en tu UNIVERSAL_COMERCIOPLUS.md
   del 2026-03-15. Si el proyecto cambia significativamente,
   actualiza el AGENT.md también.

2. Los skills son modulares. Si necesitas uno nuevo (ej: para deploy
   o para el frontend Vue legacy), crea una nueva carpeta en .skills/
   con su SKILL.md y agrégalo al registry.md.

3. Para instalar Engram (memoria persistente):
   - https://github.com/Gentleman-Programming/engram
   - Una vez instalado, la sección "memory" del .mcp.json funcionará.
