<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# Ejemplos de peticiones para API de Usuarios en Postman

Base URL: `http://localhost:8000/api/users`

---

## 1. Obtener lista de usuarios (GET)

- MÃ©todo: GET
- URL: `/api/users`
- ParÃ¡metros opcionales:
  - `filter[name]`: Filtrar por nombre (ejemplo: Juan)
  - `sort`: Ordenar por campo (ejemplo: -name para descendente)
  - `perPage`: PaginaciÃ³n (ejemplo: 5)

Ejemplo:
```
GET http://localhost:8000/api/users?filter[name]=Juan&sort=-name&perPage=5
```

---

## 2. Crear un nuevo usuario (POST)

- MÃ©todo: POST
- URL: `/api/users`
- Body (raw JSON):
```json
{
  "name": "Juan Perez",
  "email": "juan.perez@example.com",
  "phone": "123456789",
  "address": "Calle Falsa 123",
  "password": "secret123"
}
```

---

## 3. Obtener un usuario especÃ­fico (GET)

- MÃ©todo: GET
- URL: `/api/users/{id}`

Ejemplo:
```
GET http://localhost:8000/api/users/1
```

---

## 4. Actualizar un usuario (PUT)

- MÃ©todo: PUT
- URL: `/api/users/{id}`
- Body (raw JSON) con campos a actualizar:
```json
{
  "name": "Juan Actualizado",
  "email": "juan.actualizado@example.com",
  "phone": "987654321",
  "address": "Nueva DirecciÃ³n 456",
  "password": "newsecret123"
}
```

---

## 5. Eliminar un usuario (DELETE)

- MÃ©todo: DELETE
- URL: `/api/users/{id}`

Ejemplo:
```
DELETE http://localhost:8000/api/users/1
```

---

## Notas

- AsegÃºrate de que el servidor Laravel estÃ© corriendo (`php artisan serve`).
- Usa la pestaÃ±a "Body" en Postman para enviar JSON en peticiones POST y PUT.
- Para filtros, orden y paginaciÃ³n, usa parÃ¡metros en la URL como se muestra.
- Las respuestas serÃ¡n en formato JSON.

---

Si necesitas ayuda para importar estos ejemplos en Postman o para probar otros endpoints, avÃ­same.

