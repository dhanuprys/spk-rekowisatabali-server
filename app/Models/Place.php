<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;

    public static function getMetrics(array $citiesId) {
        $places = self::query();

        foreach ($citiesId as $cityId) {
            $places->orWhere('city_id', $cityId);
        }

        return $places
            ->join('cities', 'cities.id', '=', 'places.city_id')
            ->select([
                'places.id',
                'cities.name as city_name',
                'places.name',
                'l3_cg1_a',
                'l3_cg1_b',
                'l3_cg2_a',
                'l3_cg2_b',
                'l3_cg2_c',
                'l3_cg3_a',
                'l3_cg3_b',
                'l3_cg3_c',
                'l1_b',
                'l1_c',
            ])
            ->get();
    }
}
