# DOC_GOVERNANCE

Politica oficial de documentacion - ComercioPlus

## 1) Proposito

Esta politica define como se mantiene la documentacion para evitar:

- duplicacion de archivos `.md`
- contradicciones entre documentos
- deuda documental

## 2) Fuente unica de verdad

Documento oficial unico:

- `docs/UNIVERSAL_COMERCIOPLUS.md`

Uso oficial:

- contrato tecnico operativo
- arquitectura real
- rutas frontend y endpoints API
- flujos funcionales
- estado implementado vs plan
- roadmap vigente

## 3) Jerarquia de verdad

Regla de prioridad en caso de conflicto:

1. Codigo real del repositorio
2. `docs/UNIVERSAL_COMERCIOPLUS.md`
3. Documentos archivados en `docs/_archive/`

## 4) Estructura oficial permitida

```text
docs/
|-- UNIVERSAL_COMERCIOPLUS.md    # Fuente unica de verdad
|-- DOC_GOVERNANCE.md            # Esta politica
|-- README.md                    # Indice de docs
|-- UNIVERSAL_COMERCIOPLUS_AI.md # Alias derivado para IA (sin autoridad)
|-- QA_RELEASE_REPORT.md         # Reporte QA de release
|-- EXPO_DOSSIER.md              # Dossier para exposicion
|-- README_VALIDADOR.md          # Doc validador pre-codex
|-- UNIVERSAL_FILE_INDEX.txt     # Indice de distribucion ZIP
`-- _archive/                    # Historico
```

## 5) Creacion de nuevos documentos

Antes de crear un nuevo `.md`, verificar si el cambio cabe en el universal.

Solo se permite un nuevo `.md` activo si:

- es RFC aprobado
- es investigacion temporal
- es runbook temporal de migracion

Todo nuevo `.md` debe incluir:

- fecha
- estado (`Draft`, `Aprobado`, `Obsoleto`)
- enlace al universal

## 6) Politica de archivos legacy

Reglas:

- mover docs historicos a `docs/_archive/`
- no borrar sin revision
- no editar docs archivados como fuente oficial
- marcar docs archivados con advertencia de estado legacy

## 7) Proceso de actualizacion del universal

Cuando cambie cualquiera de estos puntos, se actualiza el universal:

- nuevas features
- cambios de auth
- rutas frontend o API
- variables de entorno
- despliegue/integraciones

Proceso:

1. actualizar codigo
2. validar funcionamiento
3. actualizar `UNIVERSAL_COMERCIOPLUS.md`
4. registrar historial de cambios (fecha + resumen)

## 8) Separacion obligatoria

Toda documentacion debe separar:

- Implementado (con evidencia en codigo)
- PLAN / PROPUESTA (no implementado)

No mezclar ambos estados.

## 9) Prohibiciones

No se permite:

- mantener multiples archivos activos de arquitectura
- documentar como implementado algo que solo existe en plan
- crear variantes duplicadas tipo `PLAN_FINAL_v2.md`

## 10) Regla de oro

La documentacion debe reflejar el estado real del codigo, no aspiraciones.

## 11) Responsabilidad

- Responsable primario: equipo de desarrollo de ComercioPlus.
- Cualquier cambio estructural relevante debe incluir actualizacion del universal.

## 12) Estado actual

Desde esta version:

- `docs/UNIVERSAL_COMERCIOPLUS.md` es la referencia oficial.
- `docs/_archive/` es historico.
- README(s) deben apuntar al universal.
