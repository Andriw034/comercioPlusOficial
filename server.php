<?php

/*
|--------------------------------------------------------------------------
| Router del servidor embebido de PHP
|--------------------------------------------------------------------------
|
| Cuando se arranca con `php -S ... -t public public/index.php`, el servidor
| embebido invoca ese script para TODA peticion y nunca entrega un archivo del
| disco: /robots.txt, /favicon.ico y sobre todo las imagenes subidas bajo
| /storage terminaban devolviendo el HTML de Laravel con estado 200.
|
| Este router se interpone: si la ruta corresponde a un archivo real dentro de
| public/, devuelve false para que el servidor lo entregue tal cual; en
| cualquier otro caso delega en Laravel.
|
| Es el mismo patron que trae Laravel para `artisan serve`, con una validacion
| de ruta anadida: solo se sirven archivos que estan realmente bajo public/ (o
| bajo la carpeta a la que apunta el enlace public/storage), para que una URL
| con ../ no pueda alcanzar el .env ni el codigo de la aplicacion.
|
*/

$publicDir = __DIR__ . '/public';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');

if ($uri !== '/') {
    $target = realpath($publicDir . $uri);

    // realpath resuelve el enlace public/storage hacia storage/app/public, asi
    // que esa carpeta tambien cuenta como origen valido.
    $allowedRoots = array_filter([
        realpath($publicDir),
        realpath(__DIR__ . '/storage/app/public'),
    ]);

    if ($target !== false && is_file($target)) {
        foreach ($allowedRoots as $root) {
            if (str_starts_with($target, $root . DIRECTORY_SEPARATOR)) {
                return false;
            }
        }
    }
}

require_once $publicDir . '/index.php';
