# Cargar compatibilidad de repuestos

Esto es lo que le permite al asistente responder **"¿qué repuesto de una moto le
sirve a otra?"** — la pregunta del mostrador.

## Cómo funciona (en una frase)

**Si dos motos comparten el mismo `part_reference`, el asistente las cruza solo.**

No hay que declarar la equivalencia por separado. Se escribe una fila por cada moto
que usa esa referencia, y el cruce sale de ahí.

```csv
RET-30X42X11,retenedor,...,Bajaj,Boxer 100,...
RET-30X42X11,retenedor,...,Bajaj,Discover 125,...
RET-30X42X11,retenedor,...,AKT,NKD 125,...
```

Con esas tres filas el asistente ya sabe que el retenedor de la Boxer le sirve a la
Discover y a la NKD.

## Columnas

| Columna | ¿Obligatoria? | Ejemplo |
|---|---|---|
| `part_reference` | **Sí** | `RET-30X42X11` |
| `part_type` | **Sí** | `retenedor` |
| `motorcycle_brand` | **Sí** | `Bajaj` |
| `motorcycle_model` | **Sí** | `Boxer 100` |
| `part_brand` | No | `NOK` |
| `part_description` | No | `Retenedor de barra delantera 30x42x11` |
| `year_from` / `year_to` | No | `2010` / `2020` |
| `notes` | No | `Va de a dos por moto` |

**La referencia es la clave de todo.** Si dos motos llevan la misma pieza, tienen que
llevar exactamente el mismo texto en `part_reference` — si una dice `RET-30X42X11` y
otra `RET 30X42X11`, el asistente las trata como piezas distintas y no las cruza.

## Tipos que el buscador reconoce

Solo estos. Si cargás un tipo que no está en la lista, el importador **te avisa** y
los datos quedan invisibles para los clientes:

```
bujia            pastilla_freno    banda           cadena
filtro_aceite    filtro_aire       kit_arrastre    embrague
rodamiento       pinon_motor       catalina        caucho_carburador
retenedor        guaya             rodamiento_direccion
```

Para agregar un tipo nuevo hay que tocar `PART_TYPE_SYNONYMS` en
`app/Services/PartsAssistantService.php`, donde se listan las palabras que usa la
gente ("retén", "estopera") para nombrar cada tipo.

## Cargar el archivo

Siempre probá primero, que no escribe nada:

```powershell
php artisan compatibilidad:importar mi-archivo.csv --dry-run
```

Cuando el reporte esté limpio:

```powershell
php artisan compatibilidad:importar mi-archivo.csv
```

**Se puede correr las veces que quieras.** Las filas que ya están se saltan, así que
podés corregir el CSV y volver a cargarlo sin duplicar nada.

## Si lo editás en Excel

Guardalo como **CSV UTF-8**. El importador ya tolera el marcador invisible que Excel
mete al principio del archivo, pero si usás otra codificación las tildes salen rotas.

## Por qué es un comando y no una pantalla

La tabla de compatibilidad **no tiene dueño**: la usan todas las tiendas de la
plataforma. Un dato mal cargado no afecta a un comerciante, les responde mal a los
clientes de todos. Por eso se carga desde la consola y no desde el panel.

## Regla de seguridad que ya está puesta

El asistente **nunca** afirma una compatibilidad que no esté en esta tabla. Si le
preguntan por una combinación que no está cargada, responde que no la tiene
verificada y manda a consultar al vendedor.

Eso incluye no recomendar un repuesto que sí está en el inventario si su
compatibilidad no está verificada. Recomendar mal un freno o una suspensión no es un
error de dato: es peligroso.
