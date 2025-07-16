# Ejemplos de peticiones para API de Usuarios en Postman

Base URL: `http://localhost:8000/api/users`

---

## 1. Obtener lista de usuarios (GET)

- Método: GET
- URL: `/api/users`
- Parámetros opcionales:
  - `filter[name]`: Filtrar por nombre (ejemplo: Juan)
  - `sort`: Ordenar por campo (ejemplo: -name para descendente)
  - `perPage`: Paginación (ejemplo: 5)

Ejemplo:
```
GET http://localhost:8000/api/users?filter[name]=Juan&sort=-name&perPage=5
```

---

## 2. Crear un nuevo usuario (POST)

- Método: POST
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

## 3. Obtener un usuario específico (GET)

- Método: GET
- URL: `/api/users/{id}`

Ejemplo:
```
GET http://localhost:8000/api/users/1
```

---

## 4. Actualizar un usuario (PUT)

- Método: PUT
- URL: `/api/users/{id}`
- Body (raw JSON) con campos a actualizar:
```json
{
  "name": "Juan Actualizado",
  "email": "juan.actualizado@example.com",
  "phone": "987654321",
  "address": "Nueva Dirección 456",
  "password": "newsecret123"
}
```

---

## 5. Eliminar un usuario (DELETE)

- Método: DELETE
- URL: `/api/users/{id}`

Ejemplo:
```
DELETE http://localhost:8000/api/users/1
```

---

## Notas

- Asegúrate de que el servidor Laravel esté corriendo (`php artisan serve`).
- Usa la pestaña "Body" en Postman para enviar JSON en peticiones POST y PUT.
- Para filtros, orden y paginación, usa parámetros en la URL como se muestra.
- Las respuestas serán en formato JSON.

---

Si necesitas ayuda para importar estos ejemplos en Postman o para probar otros endpoints, avísame.
