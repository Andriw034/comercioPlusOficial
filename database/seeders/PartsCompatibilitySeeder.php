<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartsCompatibilitySeeder extends Seeder
{
    public function run(): void
    {
        $parts = [
            // ==================== BANDAS ====================

            ['part_reference' => 'GATES-G-125', 'part_type' => 'banda', 'part_brand' => 'Gates', 'part_description' => 'Banda de transmisión CVT reforzada', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'Viva R Style', 'year_from' => 2015, 'year_to' => 2021, 'notes' => 'Medida: 669x17x28mm. Banda original recomendada'],
            ['part_reference' => 'GATES-G-125', 'part_type' => 'banda', 'part_brand' => 'Gates', 'part_description' => 'Banda de transmisión CVT reforzada', 'motorcycle_brand' => 'Suzuki', 'motorcycle_model' => 'AN 125', 'year_from' => 2016, 'year_to' => 2020, 'notes' => 'Medida: 669x17x28mm. Compatible con Viva R'],
            ['part_reference' => 'GATES-G-125', 'part_type' => 'banda', 'part_brand' => 'Gates', 'part_description' => 'Banda de transmisión CVT reforzada', 'motorcycle_brand' => 'Honda', 'motorcycle_model' => 'PCX 125', 'year_from' => 2017, 'year_to' => 2019, 'notes' => 'Medida: 669x17x28mm. Verificar año exacto'],
            ['part_reference' => 'DID-125X', 'part_type' => 'banda', 'part_brand' => 'DID', 'part_description' => 'Banda CVT económica alta calidad', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'Viva R Style', 'year_from' => 2015, 'year_to' => 2021, 'notes' => 'Alternativa a Gates G-125. Mismo rendimiento'],
            ['part_reference' => 'DID-125X', 'part_type' => 'banda', 'part_brand' => 'DID', 'part_description' => 'Banda CVT económica alta calidad', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'NMAX 155', 'year_from' => 2020, 'year_to' => 2024, 'notes' => 'Verificar con distribuidor Yamaha'],
            ['part_reference' => 'BANDO-125', 'part_type' => 'banda', 'part_brand' => 'Bando', 'part_description' => 'Banda CVT estándar', 'motorcycle_brand' => 'Honda', 'motorcycle_model' => 'PCX 125', 'year_from' => 2017, 'year_to' => 2020, 'notes' => 'Opción económica. Menor duración que Gates'],

            // ==================== PASTILLAS DE FRENO ====================

            ['part_reference' => 'EBC-FA213', 'part_type' => 'pastilla_freno', 'part_brand' => 'EBC', 'part_description' => 'Pastillas freno delanteras orgánicas', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'CR4 150', 'year_from' => 2018, 'year_to' => 2024, 'notes' => 'Delanteras. Excelente frenado en mojado'],
            ['part_reference' => 'EBC-FA213', 'part_type' => 'pastilla_freno', 'part_brand' => 'EBC', 'part_description' => 'Pastillas freno delanteras orgánicas', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'CR5 180', 'year_from' => 2019, 'year_to' => 2024, 'notes' => 'Delanteras. Misma referencia que CR4'],
            ['part_reference' => 'EBC-FA213', 'part_type' => 'pastilla_freno', 'part_brand' => 'EBC', 'part_description' => 'Pastillas freno delanteras orgánicas', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'NKD 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Delanteras. Compatible con versiones antiguas'],
            ['part_reference' => 'EBC-FA442', 'part_type' => 'pastilla_freno', 'part_brand' => 'EBC', 'part_description' => 'Pastillas freno traseras orgánicas', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'CR4 150', 'year_from' => 2018, 'year_to' => 2024, 'notes' => 'Traseras. Cambiar junto con delanteras'],
            ['part_reference' => 'EBC-FA442', 'part_type' => 'pastilla_freno', 'part_brand' => 'EBC', 'part_description' => 'Pastillas freno traseras orgánicas', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'CR5 180', 'year_from' => 2019, 'year_to' => 2024, 'notes' => 'Traseras. Misma referencia que CR4'],
            ['part_reference' => 'VESRAH-VD253', 'part_type' => 'pastilla_freno', 'part_brand' => 'Vesrah', 'part_description' => 'Pastillas semi-metálicas alta calidad', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'YBR 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Delanteras y traseras. Mejor opción YBR'],
            ['part_reference' => 'VESRAH-VD253', 'part_type' => 'pastilla_freno', 'part_brand' => 'Vesrah', 'part_description' => 'Pastillas semi-metálicas alta calidad', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'FZ 16', 'year_from' => 2015, 'year_to' => 2024, 'notes' => 'Delanteras. Excelente frenado deportivo'],

            // ==================== BUJÍAS ====================

            ['part_reference' => 'NGK-CR7HSA', 'part_type' => 'bujia', 'part_brand' => 'NGK', 'part_description' => 'Bujía resistor estándar', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'YBR 125', 'year_from' => 2005, 'year_to' => 2024, 'notes' => 'Bujía original Yamaha. Cambio cada 10,000km'],
            ['part_reference' => 'NGK-CR7HSA', 'part_type' => 'bujia', 'part_brand' => 'NGK', 'part_description' => 'Bujía resistor estándar', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'Viva R Style', 'year_from' => 2015, 'year_to' => 2021, 'notes' => 'Misma bujía que YBR. Universal Yamaha 125'],
            ['part_reference' => 'NGK-CR7HSA', 'part_type' => 'bujia', 'part_brand' => 'NGK', 'part_description' => 'Bujía resistor estándar', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'NKD 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Compatible AKT 125cc. Verificar manual'],
            ['part_reference' => 'NGK-CR7HSA', 'part_type' => 'bujia', 'part_brand' => 'NGK', 'part_description' => 'Bujía resistor estándar', 'motorcycle_brand' => 'Honda', 'motorcycle_model' => 'Wave 110', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Compatible con motores Honda 110-125cc'],
            ['part_reference' => 'CHAMPION-RG4HC', 'part_type' => 'bujia', 'part_brand' => 'Champion', 'part_description' => 'Bujía resistor económica', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'NKD 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Alternativa a NGK CR7HSA. Mismo rendimiento'],
            ['part_reference' => 'CHAMPION-RG4HC', 'part_type' => 'bujia', 'part_brand' => 'Champion', 'part_description' => 'Bujía resistor económica', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'YBR 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Opción económica. NGK tiene mejor chispa'],
            ['part_reference' => 'NGK-CR8E', 'part_type' => 'bujia', 'part_brand' => 'NGK', 'part_description' => 'Bujía estándar motos deportivas', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'FZ 16', 'year_from' => 2015, 'year_to' => 2024, 'notes' => 'Específica para motor FZ. NO usar CR7HSA'],
            ['part_reference' => 'NGK-CR8E', 'part_type' => 'bujia', 'part_brand' => 'NGK', 'part_description' => 'Bujía estándar motos deportivas', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'CR5 180', 'year_from' => 2019, 'year_to' => 2024, 'notes' => 'Motor deportivo requiere CR8E, no CR7'],

            // ==================== CADENAS ====================

            ['part_reference' => 'DID-428H-132', 'part_type' => 'cadena', 'part_brand' => 'DID', 'part_description' => 'Cadena reforzada paso 428H', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'YBR 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Paso 428H, 132 eslabones. La mejor calidad'],
            ['part_reference' => 'DID-428H-132', 'part_type' => 'cadena', 'part_brand' => 'DID', 'part_description' => 'Cadena reforzada paso 428H', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'NKD 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Paso 428H, 132 eslabones. Compatible YBR'],
            ['part_reference' => 'DID-428H-132', 'part_type' => 'cadena', 'part_brand' => 'DID', 'part_description' => 'Cadena reforzada paso 428H', 'motorcycle_brand' => 'Honda', 'motorcycle_model' => 'CB 125F', 'year_from' => 2015, 'year_to' => 2024, 'notes' => 'Paso 428H, 132 eslabones. Universal 125cc'],
            ['part_reference' => 'REGINA-428-132', 'part_type' => 'cadena', 'part_brand' => 'Regina', 'part_description' => 'Cadena estándar paso 428', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'YBR 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Alternativa a DID. Menor duración'],
            ['part_reference' => 'REGINA-428-132', 'part_type' => 'cadena', 'part_brand' => 'Regina', 'part_description' => 'Cadena estándar paso 428', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'NKD 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Opción económica. DID dura más'],
            ['part_reference' => 'DID-520-112', 'part_type' => 'cadena', 'part_brand' => 'DID', 'part_description' => 'Cadena reforzada paso 520', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'CR4 150', 'year_from' => 2018, 'year_to' => 2024, 'notes' => 'Paso 520, 112 eslabones. Motor 150cc'],
            ['part_reference' => 'DID-520-112', 'part_type' => 'cadena', 'part_brand' => 'DID', 'part_description' => 'Cadena reforzada paso 520', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'CR5 180', 'year_from' => 2019, 'year_to' => 2024, 'notes' => 'Paso 520, 112 eslabones. Misma de CR4'],
            ['part_reference' => 'DID-520-112', 'part_type' => 'cadena', 'part_brand' => 'DID', 'part_description' => 'Cadena reforzada paso 520', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'FZ 16', 'year_from' => 2015, 'year_to' => 2024, 'notes' => 'Paso 520, 112 eslabones. Verificar largo'],

            // ==================== FILTROS DE ACEITE ====================

            ['part_reference' => 'HIFLO-HF131', 'part_type' => 'filtro_aceite', 'part_brand' => 'HiFlo', 'part_description' => 'Filtro aceite alta eficiencia', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'YBR 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Cambio cada 3,000km. Mejor que genérico'],
            ['part_reference' => 'HIFLO-HF131', 'part_type' => 'filtro_aceite', 'part_brand' => 'HiFlo', 'part_description' => 'Filtro aceite alta eficiencia', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'FZ 16', 'year_from' => 2015, 'year_to' => 2024, 'notes' => 'Mismo filtro YBR. Universal Yamaha'],
            ['part_reference' => 'HIFLO-HF131', 'part_type' => 'filtro_aceite', 'part_brand' => 'HiFlo', 'part_description' => 'Filtro aceite alta eficiencia', 'motorcycle_brand' => 'Suzuki', 'motorcycle_model' => 'Gixxer 150', 'year_from' => 2018, 'year_to' => 2024, 'notes' => 'Compatible Suzuki 125-150cc'],
            ['part_reference' => 'KN-131', 'part_type' => 'filtro_aceite', 'part_brand' => 'K&N', 'part_description' => 'Filtro aceite alto rendimiento', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'YBR 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Mayor flujo aceite. Para uso deportivo'],
            ['part_reference' => 'KN-131', 'part_type' => 'filtro_aceite', 'part_brand' => 'K&N', 'part_description' => 'Filtro aceite alto rendimiento', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'FZ 16', 'year_from' => 2015, 'year_to' => 2024, 'notes' => 'Recomendado para FZ. Mejor rendimiento'],

            // ==================== KIT DE ARRASTRE ====================

            ['part_reference' => 'CASSARELLA-KIT-AKT125', 'part_type' => 'kit_arrastre', 'part_brand' => 'Cassarella', 'part_description' => 'Kit completo cadena + piñones', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'NKD 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Incluye cadena 428H-132 + piñón motor + catalina. Kit original'],
            ['part_reference' => 'CASSARELLA-KIT-AKT125', 'part_type' => 'kit_arrastre', 'part_brand' => 'Cassarella', 'part_description' => 'Kit completo cadena + piñones', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'Flex 125', 'year_from' => 2015, 'year_to' => 2024, 'notes' => 'Mismo kit NKD. Compatible Flex'],
            ['part_reference' => 'CASSARELLA-KIT-CR150', 'part_type' => 'kit_arrastre', 'part_brand' => 'Cassarella', 'part_description' => 'Kit completo cadena + piñones 150cc', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'CR4 150', 'year_from' => 2018, 'year_to' => 2024, 'notes' => 'Incluye cadena 520-112 reforzada + piñones. Para uso deportivo'],
            ['part_reference' => 'CASSARELLA-KIT-CR150', 'part_type' => 'kit_arrastre', 'part_brand' => 'Cassarella', 'part_description' => 'Kit completo cadena + piñones 150cc', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'CR5 180', 'year_from' => 2019, 'year_to' => 2024, 'notes' => 'Mismo kit CR4. Compatible CR5'],

            // ==================== CAUCHOS/GOMAS ====================

            ['part_reference' => 'YAMAHA-5VL-14984', 'part_type' => 'caucho_carburador', 'part_brand' => 'Yamaha', 'part_description' => 'Juego cauchos admisión carburador', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'YBR 125', 'year_from' => 2005, 'year_to' => 2024, 'notes' => 'Original Yamaha. Cambiar si hay entrada falsa aire'],
            ['part_reference' => 'YAMAHA-5VL-14984', 'part_type' => 'caucho_carburador', 'part_brand' => 'Yamaha', 'part_description' => 'Juego cauchos admisión carburador', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'Crypton 115', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Misma referencia YBR. Compatible Crypton'],

            // ==================== PIÑONES ====================

            ['part_reference' => 'VITRIX-PINON-14T-428', 'part_type' => 'pinon_motor', 'part_brand' => 'Vitrix', 'part_description' => 'Piñón motor 14 dientes paso 428', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'YBR 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Estándar 14T. Para velocidad usar 15T'],
            ['part_reference' => 'VITRIX-PINON-14T-428', 'part_type' => 'pinon_motor', 'part_brand' => 'Vitrix', 'part_description' => 'Piñón motor 14 dientes paso 428', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'NKD 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Estándar 14T. Universal 125cc paso 428'],
            ['part_reference' => 'VITRIX-CATALINA-40T-428', 'part_type' => 'catalina', 'part_brand' => 'Vitrix', 'part_description' => 'Catalina trasera 40 dientes paso 428', 'motorcycle_brand' => 'Yamaha', 'motorcycle_model' => 'YBR 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Estándar 40T. Para fuerza usar 42T'],
            ['part_reference' => 'VITRIX-CATALINA-40T-428', 'part_type' => 'catalina', 'part_brand' => 'Vitrix', 'part_description' => 'Catalina trasera 40 dientes paso 428', 'motorcycle_brand' => 'AKT', 'motorcycle_model' => 'NKD 125', 'year_from' => 2010, 'year_to' => 2024, 'notes' => 'Estándar 40T. Compatible YBR'],
        ];

        foreach ($parts as $part) {
            DB::table('parts_compatibility')->insert(array_merge($part, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Insertadas ' . count($parts) . ' relaciones de compatibilidad');
    }
}
