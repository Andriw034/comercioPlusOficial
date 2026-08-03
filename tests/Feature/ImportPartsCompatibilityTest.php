<?php

namespace Tests\Feature;

use App\Services\PartsAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Importador de compatibilidad (que repuesto le sirve a que moto).
 *
 * Es dato compartido por todas las tiendas, asi que una fila mala no afecta a un
 * comerciante: afecta a los clientes de todos. De ahi que se valide antes de escribir.
 */
class ImportPartsCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    private string $archivo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->archivo = sys_get_temp_dir() . '/compat-test-' . uniqid() . '.csv';
    }

    protected function tearDown(): void
    {
        if (is_file($this->archivo)) {
            unlink($this->archivo);
        }

        parent::tearDown();
    }

    private function csv(string $contenido): string
    {
        file_put_contents($this->archivo, $contenido);

        return $this->archivo;
    }

    private const ENCABEZADO = 'part_reference,part_type,part_brand,part_description,'
        . "motorcycle_brand,motorcycle_model,year_from,year_to,notes\n";

    public function test_carga_las_filas_del_csv(): void
    {
        $archivo = $this->csv(self::ENCABEZADO
            . "RET-30X42X11,retenedor,NOK,Reten barra,Bajaj,Boxer 100,2010,2020,dos por moto\n"
            . "RET-30X42X11,retenedor,NOK,Reten barra,AKT,NKD 125,2010,2024,\n");

        $this->artisan('compatibilidad:importar', ['archivo' => $archivo])
            ->assertExitCode(0);

        $this->assertDatabaseCount('parts_compatibility', 2);
        $this->assertDatabaseHas('parts_compatibility', [
            'part_reference'   => 'RET-30X42X11',
            'motorcycle_model' => 'Boxer 100',
            'year_from'        => 2010,
            'notes'            => 'dos por moto',
        ]);
    }

    public function test_correrlo_dos_veces_no_duplica(): void
    {
        // Un CSV se corrige y se vuelve a cargar varias veces: si duplicara, el
        // asistente listaria la misma moto repetida.
        $archivo = $this->csv(self::ENCABEZADO
            . "RET-1,retenedor,NOK,Reten,Bajaj,Boxer 100,2010,2020,\n");

        $this->artisan('compatibilidad:importar', ['archivo' => $archivo]);
        $this->artisan('compatibilidad:importar', ['archivo' => $archivo]);

        $this->assertDatabaseCount('parts_compatibility', 1);
    }

    public function test_el_modo_prueba_no_escribe_nada(): void
    {
        $archivo = $this->csv(self::ENCABEZADO
            . "RET-1,retenedor,NOK,Reten,Bajaj,Boxer 100,2010,2020,\n");

        $this->artisan('compatibilidad:importar', ['archivo' => $archivo, '--dry-run' => true])
            ->assertExitCode(0);

        $this->assertDatabaseCount('parts_compatibility', 0);
    }

    public function test_avisa_cuando_el_tipo_no_lo_reconoce_el_buscador(): void
    {
        // Cargar un tipo que el buscador no detecta es trabajo perdido: los datos
        // quedan en la tabla pero ninguna pregunta del cliente los alcanza.
        $this->assertNotContains('manubrio', PartsAssistantService::tiposBuscables());

        $archivo = $this->csv(self::ENCABEZADO
            . "MAN-1,manubrio,Generico,Manubrio,Bajaj,Boxer 100,2010,2020,\n");

        $this->artisan('compatibilidad:importar', ['archivo' => $archivo, '--dry-run' => true])
            ->expectsOutputToContain("El tipo 'manubrio' NO lo reconoce el buscador");
    }

    public function test_descarta_filas_incompletas_o_con_anios_al_reves(): void
    {
        $archivo = $this->csv(self::ENCABEZADO
            . ",retenedor,NOK,Sin referencia,Bajaj,Boxer 100,2010,2020,\n"
            . "RET-2,retenedor,NOK,Anios al reves,Bajaj,Boxer 100,2020,2010,\n"
            . "RET-3,retenedor,NOK,Buena,Bajaj,Boxer 100,2010,2020,\n");

        $this->artisan('compatibilidad:importar', ['archivo' => $archivo])
            ->assertExitCode(0);

        // Solo entra la buena; las otras dos se reportan y se descartan.
        $this->assertDatabaseCount('parts_compatibility', 1);
        $this->assertDatabaseHas('parts_compatibility', ['part_reference' => 'RET-3']);
    }

    public function test_falla_si_al_encabezado_le_faltan_columnas(): void
    {
        $archivo = $this->csv("part_reference,part_type\nRET-1,retenedor\n");

        $this->artisan('compatibilidad:importar', ['archivo' => $archivo])
            ->assertExitCode(1);

        $this->assertDatabaseCount('parts_compatibility', 0);
    }

    public function test_tolera_el_bom_que_mete_excel(): void
    {
        // Excel guarda los CSV con un marcador invisible al inicio que rompe el
        // nombre de la primera columna. Sin esto, el error no se entiende.
        $archivo = $this->csv("\xEF\xBB\xBF" . self::ENCABEZADO
            . "RET-1,retenedor,NOK,Reten,Bajaj,Boxer 100,2010,2020,\n");

        $this->artisan('compatibilidad:importar', ['archivo' => $archivo])
            ->assertExitCode(0);

        $this->assertDatabaseCount('parts_compatibility', 1);
    }

    public function test_falla_si_el_archivo_no_existe(): void
    {
        $this->artisan('compatibilidad:importar', ['archivo' => '/no/existe.csv'])
            ->assertExitCode(1);
    }

    public function test_los_tipos_nuevos_son_buscables(): void
    {
        // Sin estos tres, cargar sus datos no sirve de nada.
        $buscables = PartsAssistantService::tiposBuscables();

        $this->assertContains('retenedor', $buscables);
        $this->assertContains('guaya', $buscables);
        $this->assertContains('rodamiento_direccion', $buscables);
    }

    public function test_el_buscador_reconoce_la_palabra_retenedor(): void
    {
        DB::table('parts_compatibility')->insert([
            'part_reference'   => 'RET-30X42X11',
            'part_type'        => 'retenedor',
            'part_brand'       => 'NOK',
            'part_description' => 'Retenedor de barra',
            'motorcycle_brand' => 'Bajaj',
            'motorcycle_model' => 'Boxer 100',
            'year_from'        => 2010,
            'year_to'          => 2020,
        ]);

        $resultado = app(PartsAssistantService::class)
            ->search('que retenedor le sirve a una Boxer 100?');

        $this->assertSame('retenedor', $resultado['interpretacion']['tipo_pieza']);
        $this->assertNotEmpty($resultado['compatibilidades']);
    }
}
