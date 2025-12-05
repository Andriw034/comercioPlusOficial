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
            'Filtro de aceite para moto', 'Pastillas de freno delanteras', 'Kit de arrastre para moto', 'Neumático trasero para moto',
            'Aceite de motor 4T 10W-40', 'Bujía de iridio para moto', 'Batería de gel para moto', 'Manillar de moto deportivo',
            'Espejos retrovisores para moto', 'Intermitentes LED para moto', 'Faro delantero halógeno', 'Soporte para móvil en moto',
            'Alarma para moto con GPS', 'Candado de disco con alarma', 'Funda para moto exterior', 'Baúl para moto de 48 litros'
        ];

        $productDescriptions = [
            'Casco de moto integral con certificación ECE 22.05, interior desmontable y lavable.',
            'Chaqueta de cuero bovino de alta resistencia, con protecciones en hombros y codos.',
            'Guantes de moto de verano con tejido transpirable y protecciones en los nudillos.',
            'Botas de moto impermeables con membrana Gore-Tex y suela antideslizante.',
            'Filtro de aceite de alto rendimiento para motores de 4 tiempos.',
            'Pastillas de freno delanteras sinterizadas para una frenada potente y progresiva.',
            'Kit de arrastre completo con cadena, corona y piñón de acero de alta calidad.',
            'Neumático trasero deportivo para moto, con excelente agarre en seco y mojado.',
            'Aceite de motor sintético 4T 10W-40 para un rendimiento óptimo del motor.',
            'Bujía de iridio de larga duración para una mejor combustión y arranque.',
            'Batería de gel sin mantenimiento, con mayor potencia de arranque.',
            'Manillar de moto deportivo de aluminio, más ligero y resistente que el original.',
            'Juego de espejos retrovisores homologados para moto, con diseño aerodinámico.',
            'Juego de 4 intermitentes LED para moto, con mayor visibilidad y menor consumo.',
            'Faro delantero halógeno H4 para una mejor iluminación de la carretera.',
            'Soporte universal para móvil en moto, con fijación al manillar o al espejo.',
            'Alarma para moto con localizador GPS y aviso al móvil en caso de robo.',
            'Candado de disco con alarma de 120 dB para disuadir a los ladrones.',
            'Funda para moto de exterior, impermeable y con protección UV.',
            'Baúl para moto con capacidad para dos cascos integrales y respaldo para el pasajero.'
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
