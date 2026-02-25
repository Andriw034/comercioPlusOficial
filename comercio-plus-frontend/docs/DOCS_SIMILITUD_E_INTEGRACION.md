<!-- DOC_STATUS:START -->
> Estado documental: **ACTIVO**
> Fecha de verificacion: **2026-02-25**
> Fuente canonica frontend: `ComercioPlus_Frontend_Contrato_Tecnico.md`.
<!-- DOC_STATUS:END -->

# DOCS_SIMILITUD_E_INTEGRACION

Fecha: 2026-02-25
Alcance: revision de todos los .md en `comercio-plus-frontend/docs` y consolidacion por similitud tematica.

## 1) Inventario revisado
- Total de documentos en `docs/` (actual): 45
- Documentos activos en `docs/`: 8
- Documentos historicos en `docs/repo-md/`: 37
- Fuente tecnica canonica actual: `ComercioPlus_Frontend_Contrato_Tecnico.md`
- Auditoria ejecutiva actual: `ComercioPlus_Frontend_Auditoria_Estructural.md`

## 2) Metodo usado
1. Deteccion de duplicados exactos por hash SHA256.
2. Deteccion de similitud lexica (interseccion de tokens).
3. Agrupacion funcional por tema.

Resultado:
- Duplicados exactos: ninguno.
- Similitudes fuertes detectadas entre documentos de picking, QA/analisis y guias de deploy/cloudinary.

## 3) Grupos integrados

### Grupo A: Analisis + plan + QA
Fuentes:
- `repo-md/ANALISIS_COMPLETO.md`
- `repo-md/TODO_COMPREHENSIVE_ANALYSIS.md`
- `repo-md/EXECUTION_PLAN_SENIOR.md`
- `repo-md/QA_REPORT_COMERCIOPLUS.md`
- `repo-md/QA_E2E_REPORT.md`
- `repo-md/QA_REPORT_AUTOMATICO_FULLFLOW.md`
- `repo-md/INFORME_GENERAL_PROYECTO.md`
- `repo-md/INFORME_COMPLETO_APLICACION.md`

Integrado en:
- `INTEGRADO_ANALISIS_PLAN_QA.md`

### Grupo B: Testing y pruebas
Fuentes:
- `repo-md/PLAN_PRUEBAS_EXHAUSTIVAS_ESPAÑOL.md`
- `repo-md/PLAN_PRUEBAS_VISTAS_VUE.md`
- `repo-md/TODO_PRUEBAS_EXHAUSTIVAS.md`
- `repo-md/TODO_TESTING_VIEWS.md`
- `repo-md/legacy_docs__e2e-playwright.md`

Integrado en:
- `INTEGRADO_TESTING_QA.md`

### Grupo C: Picking
Fuentes:
- `repo-md/legacy_docs__picking-api-contract.md`
- `repo-md/legacy_docs__picking-state-machine.md`
- `repo-md/legacy_docs__picking-runbook.md`
- `repo-md/legacy_docs__picking-phase-plan-prompt.md`
- `repo-md/legacy_docs__picking-claude-design-handoff.md`

Integrado en:
- `INTEGRADO_PICKING.md`

### Grupo D: Deploy + media/cloudinary
Fuentes:
- `repo-md/legacy_docs__cloudinary-uploads.md`
- `repo-md/legacy_docs__cloudinary-production-checklist.md`
- `repo-md/legacy_docs__deploy-railway-vercel-cloudinary.md`

Integrado en:
- `INTEGRADO_DEPLOY_MEDIA.md`

### Grupo E: Wompi ecommerce base
Fuentes:
- `repo-md/README.md`
- `repo-md/GUIA_LARAVEL_WOMPI.md`

Integrado en:
- `INTEGRADO_WOMPI_ECOMMERCE.md`

## 4) Documentos no fusionados (se mantienen como referencia historica)
- `repo-md/API.md`
- `repo-md/TODO_DESIGN_FRONTEND_ANALYSIS.md`
- `repo-md/TODO_PANEL_COMERCIANTE.md`
- `repo-md/TODO_API_FIXES.md`
- `repo-md/TODO_TRADUCCION_ESPANOL.md`
- `repo-md/MIGRATIONS_NOTES.md`
- `repo-md/legacy_docs__inventory-scan-in-audit.md`
- `repo-md/legacy_docs__postman_user_api_examples.md`
- `repo-md/legacy_docs__stock-policy-options.md`
- `repo-md/legacy_docs__blueprint.md`
- `repo-md/legacy_docs__TODO_COMPREHENSIVE_ANALYSIS.md`
- `repo-md/legacy_docs__DatabaseInsertInstructions.md`

## 5) Convencion de uso recomendada
1. Usar `ComercioPlus_Frontend_Contrato_Tecnico.md` como contrato base obligatorio.
2. Usar `ComercioPlus_Frontend_Auditoria_Estructural.md` como resumen ejecutivo de arquitectura.
3. Usar los archivos `INTEGRADO_*.md` como lectura consolidada por tema.
4. Usar `repo-md/**` solo como historial/fuente (no como verdad actual).

