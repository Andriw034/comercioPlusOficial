<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# CorrecciÃ³n de Errores en Controladores API

## Controladores a Corregir

- [x] CartController.php - Corregido
- [x] CategoryController.php - Corregido
- [x] ProductController.php - Ya corregido
- [x] OrderController.php - Ya corregido
- [x] OrderProductController.php
- [x] OrderMessageController.php
- [x] UserController.php
- [x] ProfileController.php
- [x] RoleController.php
- [x] SaleController.php
- [x] SettingController.php
- [x] TutorialController.php
- [x] LocationController.php
- [x] ChannelController.php
- [x] ClaimController.php
- [x] CartProductController.php
- [x] RatingController.php
- [x] NotificacionController.php
- [x] PublicStoreController.php
- [x] StoreController.php - Ya corregido
- [x] AuthController.php - Ya corregido
- [x] PruebaController.php - Ya corregido

## MÃ©todo a Corregir
Reemplazar las llamadas a mÃ©todos no estÃ¡ndar:
- `included()` -> `with()`
- `filter()` -> Eliminar o implementar lÃ³gica de filtrado
- `sort()` -> Eliminar o implementar lÃ³gica de ordenamiento
- `getOrPaginate()` -> `get()` o `paginate()`

