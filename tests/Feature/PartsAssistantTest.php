<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PartsAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('motorcycle_models')->insert([
            ['brand' => 'Yamaha', 'model' => 'YBR 125', 'year_from' => 2010, 'year_to' => 2020, 'created_at' => now(), 'updated_at' => now()],
            ['brand' => 'Hero',   'model' => 'Splendor', 'year_from' => 2010, 'year_to' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('parts_compatibility')->insert([
            [
                'part_reference' => 'BANDO-125', 'part_type' => 'banda', 'part_brand' => 'Bando',
                'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'YBR 125',
                'year_from' => 2010, 'year_to' => 2020, 'notes' => 'OEM',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'part_reference' => 'NGK-CR7HSA', 'part_type' => 'bujia', 'part_brand' => 'NGK',
                'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'YBR 125',
                'year_from' => 2010, 'year_to' => 2020, 'notes' => null,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }

    public function test_endpoint_publico_responde_sin_autenticacion(): void
    {
        $this->getJson('/api/assistant/search?q=banda para yamaha ybr 125')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'data' => ['interpretacion', 'compatibilidades', 'alcance'], 'message']);
    }

    public function test_encuentra_la_moto_pese_a_error_de_tipeo(): void
    {
        // "yamahaa" y "vanda" mal escritos deben resolverse igual.
        $response = $this->getJson('/api/assistant/search?q=vanda para yamahaa ybr 125');

        $response->assertStatus(200)
            ->assertJsonPath('data.interpretacion.marca', 'Yamaha')
            ->assertJsonPath('data.interpretacion.tipo_pieza', 'banda');
    }

    public function test_no_confunde_la_pieza_con_una_marca(): void
    {
        // "banda" esta a distancia 2 de "Honda": no debe interpretarse como marca.
        $this->getJson('/api/assistant/search?q=que banda sirve para yamaha ybr 125')
            ->assertStatus(200)
            ->assertJsonPath('data.interpretacion.marca', 'Yamaha')
            ->assertJsonPath('data.interpretacion.tipo_pieza', 'banda');
    }

    public function test_moto_sin_datos_no_inventa_referencias(): void
    {
        $response = $this->getJson('/api/assistant/search?q=banda para hero splendor');

        $response->assertStatus(200);
        $data = $response->json('data');

        // Solo hay datos de la YBR. Si devuelve algo, debe avisar que es de otra moto.
        foreach ($data['compatibilidades'] as $item) {
            $this->assertStringNotContainsString('Splendor', $item['moto']);
        }

        if ($data['compatibilidades'] !== []) {
            $this->assertNotNull($data['aviso'], 'Debe avisar que los resultados son de otra moto');
            $this->assertNotSame('moto_exacta', $data['alcance']);
        }
    }

    public function test_consulta_sin_moto_ni_pieza_reconocida_informa_el_motivo(): void
    {
        $response = $this->getJson('/api/assistant/search?q=hola que tal todo bien');

        $response->assertStatus(200)
            ->assertJsonPath('data.sin_resultados_por', 'moto_desconocida')
            ->assertJsonPath('data.compatibilidades', []);

        // Debe orientar diciendo de que motos si hay datos.
        $this->assertNotEmpty($response->json('data.motos_con_datos'));
    }

    public function test_cruza_con_el_inventario_de_la_tienda(): void
    {
        $user  = \App\Models\User::factory()->create(['role' => 'merchant']);
        $store = \App\Models\Store::factory()->create(['user_id' => $user->id]);

        $product = \App\Models\Product::factory()->create([
            'name'     => 'Banda BANDO-125 original',
            'price'    => 25000,
            'stock'    => 4,
            'store_id' => $store->id,
            'user_id'  => $user->id,
        ]);
        $productId = $product->id;

        $response = $this->getJson("/api/assistant/search?q=banda para yamaha ybr 125&store_id={$store->id}");

        $response->assertStatus(200);
        $banda = collect($response->json('data.compatibilidades'))
            ->firstWhere('referencia', 'BANDO-125');

        $this->assertNotNull($banda);
        $this->assertNotNull($banda['en_inventario'], 'Debe encontrar el producto en el inventario');
        $this->assertSame($productId, $banda['en_inventario']['producto_id']);
        $this->assertSame(4, $banda['en_inventario']['stock']);
        $this->assertTrue($banda['en_inventario']['disponible']);
    }

    public function test_sin_store_id_no_reporta_inventario(): void
    {
        $response = $this->getJson('/api/assistant/search?q=banda para yamaha ybr 125');

        foreach ($response->json('data.compatibilidades') as $item) {
            $this->assertNull($item['en_inventario']);
        }
    }

    public function test_valida_la_consulta(): void
    {
        $this->getJson('/api/assistant/search')->assertStatus(422);
        $this->getJson('/api/assistant/search?q=a')->assertStatus(422);
    }
}
