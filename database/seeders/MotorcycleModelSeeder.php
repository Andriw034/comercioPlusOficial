<?php

namespace Database\Seeders;

use App\Models\MotorcycleModel;
use Illuminate\Database\Seeder;

class MotorcycleModelSeeder extends Seeder
{
    public function run(): void
    {
        $motos = [
            // Yamaha
            ['brand' => 'Yamaha', 'model' => 'YBR 125',     'year_from' => 2010, 'year_to' => null, 'engine_cc' => '125',  'type' => 'naked'],
            ['brand' => 'Yamaha', 'model' => 'XTZ 125',     'year_from' => 2012, 'year_to' => null, 'engine_cc' => '125',  'type' => 'enduro'],
            ['brand' => 'Yamaha', 'model' => 'XTZ 250',     'year_from' => 2014, 'year_to' => null, 'engine_cc' => '250',  'type' => 'enduro'],
            ['brand' => 'Yamaha', 'model' => 'FZ 16',       'year_from' => 2011, 'year_to' => null, 'engine_cc' => '150',  'type' => 'naked'],
            ['brand' => 'Yamaha', 'model' => 'FZ 25',       'year_from' => 2017, 'year_to' => null, 'engine_cc' => '250',  'type' => 'naked'],
            ['brand' => 'Yamaha', 'model' => 'FZ-S FI',     'year_from' => 2019, 'year_to' => null, 'engine_cc' => '150',  'type' => 'naked'],
            ['brand' => 'Yamaha', 'model' => 'R15 V3',      'year_from' => 2018, 'year_to' => null, 'engine_cc' => '155',  'type' => 'sport'],
            ['brand' => 'Yamaha', 'model' => 'MT-03',       'year_from' => 2020, 'year_to' => null, 'engine_cc' => '321',  'type' => 'naked'],
            ['brand' => 'Yamaha', 'model' => 'NMAX 155',    'year_from' => 2018, 'year_to' => null, 'engine_cc' => '155',  'type' => 'scooter'],
            ['brand' => 'Yamaha', 'model' => 'Crypton 115', 'year_from' => 2008, 'year_to' => null, 'engine_cc' => '115',  'type' => 'other'],
            ['brand' => 'Yamaha', 'model' => 'BWS 125',     'year_from' => 2010, 'year_to' => null, 'engine_cc' => '125',  'type' => 'scooter'],

            // Honda
            ['brand' => 'Honda', 'model' => 'CB 190R',      'year_from' => 2016, 'year_to' => null, 'engine_cc' => '184',  'type' => 'naked'],
            ['brand' => 'Honda', 'model' => 'CB 160F',      'year_from' => 2018, 'year_to' => null, 'engine_cc' => '163',  'type' => 'naked'],
            ['brand' => 'Honda', 'model' => 'CB 125F',      'year_from' => 2015, 'year_to' => null, 'engine_cc' => '125',  'type' => 'naked'],
            ['brand' => 'Honda', 'model' => 'XR 190L',      'year_from' => 2017, 'year_to' => null, 'engine_cc' => '186',  'type' => 'enduro'],
            ['brand' => 'Honda', 'model' => 'XR 150L',      'year_from' => 2012, 'year_to' => null, 'engine_cc' => '150',  'type' => 'enduro'],
            ['brand' => 'Honda', 'model' => 'Wave 110',     'year_from' => 2010, 'year_to' => null, 'engine_cc' => '110',  'type' => 'other'],
            ['brand' => 'Honda', 'model' => 'CBR 250R',     'year_from' => 2011, 'year_to' => 2018, 'engine_cc' => '250',  'type' => 'sport'],
            ['brand' => 'Honda', 'model' => 'CBR 300R',     'year_from' => 2015, 'year_to' => null, 'engine_cc' => '286',  'type' => 'sport'],
            ['brand' => 'Honda', 'model' => 'Navi 110',     'year_from' => 2018, 'year_to' => null, 'engine_cc' => '110',  'type' => 'scooter'],
            ['brand' => 'Honda', 'model' => 'PCX 150',      'year_from' => 2019, 'year_to' => null, 'engine_cc' => '150',  'type' => 'scooter'],

            // Suzuki
            ['brand' => 'Suzuki', 'model' => 'GN 125',      'year_from' => 2005, 'year_to' => null, 'engine_cc' => '125',  'type' => 'cruiser'],
            ['brand' => 'Suzuki', 'model' => 'Gixxer 150',  'year_from' => 2015, 'year_to' => null, 'engine_cc' => '155',  'type' => 'naked'],
            ['brand' => 'Suzuki', 'model' => 'Gixxer SF',   'year_from' => 2016, 'year_to' => null, 'engine_cc' => '155',  'type' => 'sport'],
            ['brand' => 'Suzuki', 'model' => 'Gixxer 250',  'year_from' => 2019, 'year_to' => null, 'engine_cc' => '249',  'type' => 'naked'],
            ['brand' => 'Suzuki', 'model' => 'DR 200',      'year_from' => 2010, 'year_to' => null, 'engine_cc' => '199',  'type' => 'enduro'],
            ['brand' => 'Suzuki', 'model' => 'DR 650',      'year_from' => 2008, 'year_to' => null, 'engine_cc' => '644',  'type' => 'enduro'],
            ['brand' => 'Suzuki', 'model' => 'Intruder 150','year_from' => 2018, 'year_to' => null, 'engine_cc' => '155',  'type' => 'cruiser'],
            ['brand' => 'Suzuki', 'model' => 'GSX-R150',    'year_from' => 2018, 'year_to' => null, 'engine_cc' => '150',  'type' => 'sport'],
            ['brand' => 'Suzuki', 'model' => 'V-Strom 250', 'year_from' => 2019, 'year_to' => null, 'engine_cc' => '248',  'type' => 'touring'],

            // Bajaj
            ['brand' => 'Bajaj', 'model' => 'Boxer CT 100', 'year_from' => 2010, 'year_to' => null, 'engine_cc' => '100',  'type' => 'other'],
            ['brand' => 'Bajaj', 'model' => 'Boxer BM 150', 'year_from' => 2012, 'year_to' => null, 'engine_cc' => '150',  'type' => 'other'],
            ['brand' => 'Bajaj', 'model' => 'Pulsar NS 200','year_from' => 2012, 'year_to' => null, 'engine_cc' => '200',  'type' => 'naked'],
            ['brand' => 'Bajaj', 'model' => 'Pulsar NS 160','year_from' => 2017, 'year_to' => null, 'engine_cc' => '160',  'type' => 'naked'],
            ['brand' => 'Bajaj', 'model' => 'Pulsar 180',   'year_from' => 2008, 'year_to' => null, 'engine_cc' => '180',  'type' => 'naked'],
            ['brand' => 'Bajaj', 'model' => 'Pulsar 220F',  'year_from' => 2009, 'year_to' => null, 'engine_cc' => '220',  'type' => 'sport'],
            ['brand' => 'Bajaj', 'model' => 'Pulsar RS 200','year_from' => 2015, 'year_to' => null, 'engine_cc' => '200',  'type' => 'sport'],
            ['brand' => 'Bajaj', 'model' => 'Discover 125', 'year_from' => 2010, 'year_to' => null, 'engine_cc' => '125',  'type' => 'other'],
            ['brand' => 'Bajaj', 'model' => 'Dominar 250',  'year_from' => 2020, 'year_to' => null, 'engine_cc' => '249',  'type' => 'touring'],
            ['brand' => 'Bajaj', 'model' => 'Dominar 400',  'year_from' => 2017, 'year_to' => null, 'engine_cc' => '373',  'type' => 'touring'],

            // Kawasaki
            ['brand' => 'Kawasaki', 'model' => 'Ninja 400',    'year_from' => 2018, 'year_to' => null, 'engine_cc' => '399',  'type' => 'sport'],
            ['brand' => 'Kawasaki', 'model' => 'Z400',         'year_from' => 2019, 'year_to' => null, 'engine_cc' => '399',  'type' => 'naked'],
            ['brand' => 'Kawasaki', 'model' => 'Versys-X 300', 'year_from' => 2017, 'year_to' => null, 'engine_cc' => '296',  'type' => 'touring'],
            ['brand' => 'Kawasaki', 'model' => 'KLR 650',      'year_from' => 2008, 'year_to' => null, 'engine_cc' => '652',  'type' => 'enduro'],
            ['brand' => 'Kawasaki', 'model' => 'Z250',         'year_from' => 2019, 'year_to' => null, 'engine_cc' => '249',  'type' => 'naked'],

            // AKT
            ['brand' => 'AKT', 'model' => 'NKD 125',    'year_from' => 2010, 'year_to' => null, 'engine_cc' => '125',  'type' => 'naked'],
            ['brand' => 'AKT', 'model' => 'TT 125',     'year_from' => 2012, 'year_to' => null, 'engine_cc' => '125',  'type' => 'enduro'],
            ['brand' => 'AKT', 'model' => 'TT 150',     'year_from' => 2014, 'year_to' => null, 'engine_cc' => '150',  'type' => 'enduro'],
            ['brand' => 'AKT', 'model' => 'TTR 200',    'year_from' => 2016, 'year_to' => null, 'engine_cc' => '200',  'type' => 'enduro'],
            ['brand' => 'AKT', 'model' => 'CR5 200',    'year_from' => 2018, 'year_to' => null, 'engine_cc' => '200',  'type' => 'sport'],
            ['brand' => 'AKT', 'model' => 'Dynamic 125','year_from' => 2008, 'year_to' => null, 'engine_cc' => '125',  'type' => 'other'],

            // TVS
            ['brand' => 'TVS', 'model' => 'Apache RTR 200', 'year_from' => 2016, 'year_to' => null, 'engine_cc' => '200',  'type' => 'naked'],
            ['brand' => 'TVS', 'model' => 'Apache RTR 160', 'year_from' => 2018, 'year_to' => null, 'engine_cc' => '160',  'type' => 'naked'],
            ['brand' => 'TVS', 'model' => 'Sport 100',      'year_from' => 2015, 'year_to' => null, 'engine_cc' => '100',  'type' => 'other'],

            // Hero
            ['brand' => 'Hero', 'model' => 'Eco Deluxe',   'year_from' => 2012, 'year_to' => null, 'engine_cc' => '100',  'type' => 'other'],
            ['brand' => 'Hero', 'model' => 'Splendor',      'year_from' => 2010, 'year_to' => null, 'engine_cc' => '100',  'type' => 'other'],
            ['brand' => 'Hero', 'model' => 'Hunk 160R',     'year_from' => 2019, 'year_to' => null, 'engine_cc' => '163',  'type' => 'naked'],

            // KTM
            ['brand' => 'KTM', 'model' => 'Duke 200',      'year_from' => 2012, 'year_to' => null, 'engine_cc' => '200',  'type' => 'naked'],
            ['brand' => 'KTM', 'model' => 'Duke 390',      'year_from' => 2013, 'year_to' => null, 'engine_cc' => '373',  'type' => 'naked'],
            ['brand' => 'KTM', 'model' => 'RC 200',        'year_from' => 2015, 'year_to' => null, 'engine_cc' => '200',  'type' => 'sport'],
            ['brand' => 'KTM', 'model' => 'Adventure 390', 'year_from' => 2020, 'year_to' => null, 'engine_cc' => '373',  'type' => 'touring'],
        ];

        foreach ($motos as $moto) {
            MotorcycleModel::updateOrCreate(
                ['brand' => $moto['brand'], 'model' => $moto['model'], 'year_from' => $moto['year_from']],
                $moto
            );
        }
    }
}
