<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $productNames = [
            'Casco de moto integral', 'Chaqueta de cuero para moto', 'Guantes de moto de verano', 'Botas de moto impermeables',
            'Filtro de aceite para moto', 'Pastillas de freno delanteras', 'Kit de arrastre para moto', 'NeumÃ¡tico trasero para moto',
            'Aceite de motor 4T 10W-40', 'BujÃ­a de iridio para moto', 'BaterÃ­a de gel para moto', 'Manillar de moto deportivo',
            'Espejos retrovisores para moto', 'Intermitentes LED para moto', 'Faro delantero halÃ³geno', 'Soporte para mÃ³vil en moto',
            'Alarma para moto con GPS', 'Candado de disco con alarma', 'Funda para moto exterior', 'BaÃºl para moto de 48 litros'
        ];

        $productDescriptions = [
            'Casco de moto integral con certificaciÃ³n ECE 22.05, interior desmontable y lavable.',
            'Chaqueta de cuero bovino de alta resistencia, con protecciones en hombros y codos.',
            'Guantes de moto de verano con tejido transpirable y protecciones en los nudillos.',
            'Botas de moto impermeables con membrana Gore-Tex y suela antideslizante.',
            'Filtro de aceite de alto rendimiento para motores de 4 tiempos.',
            'Pastillas de freno delanteras sinterizadas para una frenada potente y progresiva.',
            'Kit de arrastre completo con cadena, corona y piÃ±Ã³n de acero de alta calidad.',
            'NeumÃ¡tico trasero deportivo para moto, con excelente agarre en seco y mojado.',
            'Aceite de motor sintÃ©tico 4T 10W-40 para un rendimiento Ã³ptimo del motor.',
            'BujÃ­a de iridio de larga duraciÃ³n para una mejor combustiÃ³n y arranque.',
            'BaterÃ­a de gel sin mantenimiento, con mayor potencia de arranque.',
            'Manillar de moto deportivo de aluminio, mÃ¡s ligero y resistente que el original.',
            'Juego de espejos retrovisores homologados para moto, con diseÃ±o aerodinÃ¡mico.',
            'Juego de 4 intermitentes LED para moto, con mayor visibilidad y menor consumo.',
            'Faro delantero halÃ³geno H4 para una mejor iluminaciÃ³n de la carretera.',
            'Soporte universal para mÃ³vil en moto, con fijaciÃ³n al manillar o al espejo.',
            'Alarma para moto con localizador GPS y aviso al mÃ³vil en caso de robo.',
            'Candado de disco con alarma de 120 dB para disuadir a los ladrones.',
            'Funda para moto de exterior, impermeable y con protecciÃ³n UV.',
            'BaÃºl para moto con capacidad para dos cascos integrales y respaldo para el pasajero.'
        ];

        return [
            'name' => $this->faker->randomElement($productNames),
            'description' => $this->faker->randomElement($productDescriptions),
            'price' => $this->faker->randomFloat(2, 20, 500),
            'image' => 'https://via.placeholder.com/640x480.png/004488?text=product-image',
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'category_id' => $this->faker->numberBetween(1, 5),
        ];
    }
}
