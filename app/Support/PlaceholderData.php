<?php

namespace App\Support;

class PlaceholderData
{
    public static function store(): array
    {
        return [
            'id' => 'mock-store-id',
            'userId' => 'mock-user-id',
            'name' => 'Moto Repuestos Pro',
            'slug' => 'moto-repuestos-pro',
            'description' => 'Los mejores repuestos para tu moto. Calidad y servicio garantizados.',
            'address' => 'Av. Principal 123, Ciudad Capital',
            'mainCategory' => 'Repuestos',
            'logo' => 'https://i.pravatar.cc/150?u=a042581f4e29026704d',
            'cover' => 'https://picsum.photos/1600/400',
            'averageRating' => 4.8,
        ];
    }

    public static function categories(): array
    {
        return [
            ['id' => 'cascos',    'name' => 'Cascos',                  'slug' => 'cascos'],
            ['id' => 'llantas',   'name' => 'Llantas',                 'slug' => 'llantas'],
            ['id' => 'aceites',   'name' => 'Aceites y lubricantes',   'slug' => 'aceites'],
            ['id' => 'frenos',    'name' => 'Frenos',                  'slug' => 'frenos'],
            ['id' => 'baterias',  'name' => 'Baterías',                'slug' => 'baterias'],
            ['id' => 'accesorios','name' => 'Accesorios',              'slug' => 'accesorios'],
        ];
    }

    public static function products(): array
    {
        return [
            [
                'id' => '1','name' => 'Casco Integral Pro-X','category' => 'Cascos',
                'price' => 350000,'stock' => 15,'image' => 'https://picsum.photos/400/400?random=1',
                'description' => 'Casco de alta gama con certificación DOT y ECE. Interior desmontable y lavable, visera anti-rayones y sistema de ventilación avanzado.'
            ],
            [
                'id' => '2','name' => 'Llantas Michelin Pilot','category' => 'Llantas',
                'price' => 450000,'stock' => 8,'image' => 'https://picsum.photos/400/400?random=2',
                'description' => 'Juego de llantas deportivas para un agarre excepcional en seco y mojado. Compuesto dual para mayor durabilidad.'
            ],
            [
                'id' => '3','name' => 'Aceite Motul 7100 4T','category' => 'Aceites y lubricantes',
                'price' => 85000,'stock' => 30,'image' => 'https://picsum.photos/400/400?random=3',
                'description' => 'Aceite 100% sintético con tecnología Éster para protección superior del motor y caja. Cumple JASO MA2.'
            ],
            [
                'id' => '4','name' => 'Pastillas de Freno Brembo','category' => 'Frenos',
                'price' => 120000,'stock' => 25,'image' => 'https://picsum.photos/400/400?random=4',
                'description' => 'Pastillas sinterizadas de alto rendimiento para frenada potente y consistente.'
            ],
            [
                'id' => '5','name' => 'Batería Yuasa YTZ10S','category' => 'Baterías',
                'price' => 280000,'stock' => 12,'image' => 'https://picsum.photos/400/400?random=5',
                'description' => 'Batería sellada AGM sin mantenimiento. Alta potencia de arranque y resistencia a vibraciones.'
            ],
            [
                'id' => '6','name' => 'Exploradoras LED','category' => 'Accesorios/Iluminación',
                'price' => 180000,'stock' => 40,'image' => 'https://picsum.photos/400/400?random=6',
                'description' => 'Exploradoras LED de alta intensidad con carcasa de aluminio resistente al agua.'
            ],
            [
                'id' => '7','name' => 'Chaqueta de Protección','category' => 'Accesorios',
                'price' => 650000,'stock' => 10,'image' => 'https://picsum.photos/400/400?random=7',
                'description' => 'Chaqueta textil con protecciones certificadas en hombros, codos y espalda.'
            ],
            [
                'id' => '8','name' => 'Guantes de Cuero','category' => 'Accesorios',
                'price' => 150000,'stock' => 50,'image' => 'https://picsum.photos/400/400?random=8',
                'description' => 'Guantes de cuero con protecciones en nudillos y palma reforzada; compatibles con táctiles.'
            ],
        ];
    }
}
