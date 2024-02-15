<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\City;
use App\Models\Language;

use Illuminate\Http\Request;

class CityController extends Controller
{
    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $regions = Region::leftJoin('regions_lang', 'regions.region_id', '=', 'regions_lang.region_id')
        ->where('regions_lang.lang_id', '=', $language->lang_id)
        ->get();

        $all_result = [];

        foreach ($regions as $key => $region) {
            $region_cities = [];
            $cities = City::leftJoin('cities_lang', 'cities.city_id', '=', 'cities_lang.city_id')
            ->where('cities.region_id', '=', $region->region_id)
            ->where('cities_lang.lang_id', '=', $language->lang_id)
            ->get();

            foreach ($cities as $key => $city) {
                array_push($region_cities, [
                    'id' => $city->city_id,
                    'name' => $city->city_name
                ]);
            }

            $result = [
                'title' => $region->region_name,
                'data' => $region_cities
            ];

            array_push($all_result, $result);
        }

        return response()->json([
            'cities' => $all_result
        ], 200);
    }

    public function get_city_by_id(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $city = City::leftJoin('cities_lang', 'cities.city_id', '=', 'cities_lang.city_id')
        ->where('cities.city_id', '=', $request->city_id)
        ->where('cities_lang.lang_id', '=', $language->lang_id)
        ->select(
            'cities.city_id as id',
            'cities.latitude',
            'cities.longitude',
            'cities_lang.city_name as name'
        )
        ->first();

        return response()->json([
            'city' => $city
        ], 200);
    }

    public function find_by_coordinates(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $city = City::leftJoin('cities_lang', 'cities.city_id', '=', 'cities_lang.city_id')
        ->where('cities.latitude', 'like', ''.$request->latitude.'%')
        ->where('longitude', 'like', ''.$request->longitude.'%')
        ->where('cities_lang.lang_id', '=', $language->lang_id)
        ->select(
            'cities.city_id as id',
            'cities_lang.city_name as name'
        )
        ->first();

        return response()->json([
            'city' => $city
        ], 200);
    }

    public function find_coordinates_by_city(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $city = City::leftJoin('cities_lang', 'cities.city_id', '=', 'cities_lang.city_id')
        ->where('cities.city_id', '=', $request->city_id)
        ->where('cities_lang.lang_id', '=', $language->lang_id)
        ->select(
            'cities.city_id',
            'cities_lang.city_name',
            'cities.latitude',
            'cities.longitude'
        )
        ->first();

        return response()->json([
            'city' => $city
        ], 200);
    }
}
