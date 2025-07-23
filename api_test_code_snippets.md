# Código para probar rutas API con scopes incluidos, filtros, orden y paginación

Este documento contiene ejemplos de código para los métodos `index` de los controladores API, mostrando cómo aplicar los scopes `included()`, `filter()`, `sort()` y paginación para facilitar las pruebas en Postman.

---

## Ejemplo para CartController

```php
public function index()
{
    // Aplicar scopes para incluir relaciones, filtrar, ordenar y paginar
    $cart = Cart::included()  // Incluir relaciones indicadas en query param ?include=
        ->filter()            // Filtrar según parámetros en ?filter[field]=value
        ->sort()              // Ordenar según parámetro ?sort=field o ?sort=-field
        ->getOrPaginate();    // Obtener resultados paginados o todos según query param

    return response()->json([
        'status' => 'ok',
        'message' =>  'Carritos obtenidos correctamente',
        'data' => $cart,
    ]);
}
```

---

## Ejemplo para UserController

```php
public function index()
{
    $query = User::query();

    // Aplicar scopes para incluir relaciones, filtrar, ordenar y paginar
    $query->included();  // ?include=relation1,relation2
    $query->filter();    // ?filter[field]=value
    $query->sort();      // ?sort=field o ?sort=-field

    $users = $query->getOrPaginate();

    return response()->json([
        'status' => 'ok',
        'data' => $users,
    ]);
}
```

---

## Cómo probar en Postman

- Para incluir relaciones:  
  `GET /api/carts?include=user`

- Para filtrar:  
  `GET /api/users?filter[status]=active`

- Para ordenar:  
  `GET /api/carts?sort=-created_at` (orden descendente)  
  `GET /api/users?sort=name` (orden ascendente)

- Para paginar:  
  `GET /api/carts?page=1&per_page=10`

---

## Próximos pasos

Si deseas, puedo generar estos métodos `index` con estos scopes para todos los controladores API, o ayudarte a crear ejemplos para otros métodos (store, show, update, destroy).

Quedo atento a tus indicaciones.
