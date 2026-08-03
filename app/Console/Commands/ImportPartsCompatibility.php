<?php

namespace App\Console\Commands;

use App\Services\PartsAssistantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Carga la tabla de compatibilidad (que repuesto le sirve a que moto) desde un CSV.
 *
 * Es un comando de consola y no un endpoint a proposito: parts_compatibility NO
 * tiene store_id, la usan TODAS las tiendas. Un comerciante que cargue mal un dato
 * le arruina la respuesta a los clientes de los demas.
 *
 * La intercambiabilidad sale sola de los datos: si dos motos comparten el mismo
 * part_reference, el asistente ya las cruza. No hay que declararla aparte.
 */
class ImportPartsCompatibility extends Command
{
    protected $signature = 'compatibilidad:importar
        {archivo : Ruta del CSV}
        {--dry-run : Revisa y reporta sin escribir nada}';

    protected $description = 'Carga compatibilidad de repuestos (que le sirve a que moto) desde un CSV';

    private const COLUMNAS = [
        'part_reference', 'part_type', 'part_brand', 'part_description',
        'motorcycle_brand', 'motorcycle_model', 'year_from', 'year_to', 'notes',
    ];

    private const OBLIGATORIAS = [
        'part_reference', 'part_type', 'motorcycle_brand', 'motorcycle_model',
    ];

    public function handle(): int
    {
        $archivo = $this->argument('archivo');

        if (! is_file($archivo)) {
            $this->error("No existe el archivo: {$archivo}");

            return self::FAILURE;
        }

        $handle = fopen($archivo, 'r');
        if ($handle === false) {
            $this->error("No se pudo abrir el archivo: {$archivo}");

            return self::FAILURE;
        }

        $encabezado = $this->leerEncabezado($handle);
        if ($encabezado === null) {
            fclose($handle);

            return self::FAILURE;
        }

        $buscables = PartsAssistantService::tiposBuscables();
        $filas     = [];
        $errores   = [];
        $noBuscables = [];
        $linea     = 1;

        while (($datos = fgetcsv($handle, 0, ',')) !== false) {
            $linea++;

            // Fila vacia: en un CSV editado a mano son inevitables, no son un error.
            if ($datos === [null] || implode('', array_map('strval', $datos)) === '') {
                continue;
            }

            $fila = $this->armarFila($encabezado, $datos);
            $falta = $this->validar($fila);

            if ($falta !== null) {
                $errores[] = "linea {$linea}: {$falta}";
                continue;
            }

            if (! in_array($fila['part_type'], $buscables, true)) {
                $noBuscables[$fila['part_type']] = true;
            }

            $filas[] = $fila;
        }

        fclose($handle);

        foreach ($errores as $error) {
            $this->warn("  {$error}");
        }

        // Cargar un tipo que el buscador no reconoce es trabajo perdido: los datos
        // quedan en la tabla pero ninguna pregunta del cliente los alcanza.
        foreach (array_keys($noBuscables) as $tipo) {
            $this->error("El tipo '{$tipo}' NO lo reconoce el buscador: los clientes nunca lo van a encontrar.");
            $this->line("  Agregalo a PART_TYPE_SYNONYMS en PartsAssistantService antes de cargarlo.");
        }

        if ($filas === []) {
            $this->error('No hay filas validas para cargar.');

            return self::FAILURE;
        }

        $this->line('');
        $this->info(count($filas) . ' filas validas, ' . count($errores) . ' descartadas.');

        if ($this->option('dry-run')) {
            $this->comment('Modo prueba: no se escribio nada.');

            return self::SUCCESS;
        }

        $nuevas = $this->guardar($filas);

        $this->info("Cargadas {$nuevas} filas nuevas (" . (count($filas) - $nuevas) . ' ya existian).');

        return self::SUCCESS;
    }

    /** @return list<string>|null */
    private function leerEncabezado($handle): ?array
    {
        $encabezado = fgetcsv($handle, 0, ',');

        if ($encabezado === false) {
            $this->error('El archivo esta vacio.');

            return null;
        }

        // El BOM que mete Excel al guardar como CSV rompe el nombre de la primera
        // columna y el error resultante ("falta part_reference") no se entiende.
        $encabezado[0] = preg_replace('/^\x{FEFF}/u', '', (string) $encabezado[0]);
        $encabezado    = array_map(fn ($c): string => strtolower(trim((string) $c)), $encabezado);

        $faltan = array_diff(self::OBLIGATORIAS, $encabezado);

        if ($faltan !== []) {
            $this->error('Al encabezado le faltan columnas: ' . implode(', ', $faltan));
            $this->line('Columnas esperadas: ' . implode(', ', self::COLUMNAS));

            return null;
        }

        return $encabezado;
    }

    /**
     * @param  list<string>  $encabezado
     * @param  list<string|null>  $datos
     * @return array<string, mixed>
     */
    private function armarFila(array $encabezado, array $datos): array
    {
        $fila = [];

        foreach (self::COLUMNAS as $columna) {
            $i = array_search($columna, $encabezado, true);
            $fila[$columna] = $i === false ? null : trim((string) ($datos[$i] ?? ''));
        }

        foreach (['year_from', 'year_to'] as $anio) {
            $fila[$anio] = is_numeric($fila[$anio]) ? (int) $fila[$anio] : null;
        }

        foreach (['part_brand', 'part_description', 'notes'] as $opcional) {
            $fila[$opcional] = $fila[$opcional] === '' ? null : $fila[$opcional];
        }

        return $fila;
    }

    /** @param array<string, mixed> $fila */
    private function validar(array $fila): ?string
    {
        foreach (self::OBLIGATORIAS as $columna) {
            if (($fila[$columna] ?? '') === '') {
                return "falta {$columna}";
            }
        }

        $desde = $fila['year_from'];
        $hasta = $fila['year_to'];

        if ($desde !== null && $hasta !== null && $desde > $hasta) {
            return "year_from ({$desde}) es mayor que year_to ({$hasta})";
        }

        return null;
    }

    /**
     * Se salta las filas que ya estan para poder correr el importador de nuevo sin
     * duplicar: un CSV se corrige y se vuelve a cargar varias veces.
     *
     * @param  list<array<string, mixed>>  $filas
     */
    private function guardar(array $filas): int
    {
        $nuevas = 0;

        DB::transaction(function () use ($filas, &$nuevas): void {
            foreach ($filas as $fila) {
                $existe = DB::table('parts_compatibility')
                    ->where('part_reference', $fila['part_reference'])
                    ->where('motorcycle_brand', $fila['motorcycle_brand'])
                    ->where('motorcycle_model', $fila['motorcycle_model'])
                    ->where('year_from', $fila['year_from'])
                    ->where('year_to', $fila['year_to'])
                    ->exists();

                if ($existe) {
                    continue;
                }

                DB::table('parts_compatibility')->insert($fila + [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $nuevas++;
            }
        });

        return $nuevas;
    }
}
