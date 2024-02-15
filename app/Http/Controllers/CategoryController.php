<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Language;
use App\Models\Service;

use Illuminate\Support\Facades\Response;
use File;

class CategoryController extends Controller
{
    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $services = Category::leftJoin('categories_lang', 'categories.category_id', '=', 'categories_lang.category_id')
        ->where('categories_lang.lang_id', '=', $language->lang_id)
        ->where('categories.parent_category_id', '=', 1)
        ->select(
            'categories.category_id as id',
            'categories_lang.category_name as name',
            'categories.image',
            'categories.bg_color'
        )
        ->get();

        foreach ($services as $key => $service) {
            $childs = Category::leftJoin('categories_lang', 'categories.category_id', '=', 'categories_lang.category_id')
            ->where('categories_lang.lang_id', '=', $language->lang_id)
            ->where('categories.parent_category_id', '=', $service->id)
            ->select(
                'categories.category_id as id',
                'categories_lang.category_name as name',
                'categories.image',
                'categories.bg_color'
            )
            ->get();

            $services[$key]->childs = $childs;
        }

        $products = Category::leftJoin('categories_lang', 'categories.category_id', '=', 'categories_lang.category_id')
        ->where('categories_lang.lang_id', '=', $language->lang_id)
        ->where('categories.parent_category_id', '=', 2)
        ->select(
            'categories.category_id as id',
            'categories_lang.category_name as name',
            'categories.image',
            'categories.bg_color'
        )
        ->get();

        foreach ($products as $key => $product) {
            $childs = Category::leftJoin('categories_lang', 'categories.category_id', '=', 'categories_lang.category_id')
            ->where('categories_lang.lang_id', '=', $language->lang_id)
            ->where('categories.parent_category_id', '=', $product->id)
            ->select(
                'categories.category_id as id',
                'categories_lang.category_name as name',
                'categories.image',
                'categories.bg_color'
            )
            ->get();

            $products[$key]->childs = $childs;
        }

        return response()->json([
            'services' => $services,
            'products' => $products
        ], 200);
    }

    public function get_category(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $categories = Category::leftJoin('categories_lang', 'categories.category_id', '=', 'categories_lang.category_id')
        ->where('categories_lang.lang_id', '=', $language->lang_id)
        ->where('categories.parent_category_id', '=', $request->category_id)
        ->select(
            'categories.category_id as id',
            'categories_lang.category_name as name',
            'categories.image',
            'categories.bg_color'
        )
        ->get();

        foreach ($categories as $key => $category) {
            $services = Service::leftJoin('services_coverages', 'services.service_id', '=', 'services_coverages.service_id')
            ->leftJoin('partner_branches', 'services_coverages.branch_id', '=', 'partner_branches.branch_id')
            ->leftJoin('cities', 'partner_branches.city_id', '=', 'cities.city_id')
            ->where('services.category_id', '=', $category->id)
            ->where('cities.city_id', '=', $request->city_id)
            ->select(
                'services.service_id'
            )
            ->get();

            $childs = Category::leftJoin('categories_lang', 'categories.category_id', '=', 'categories_lang.category_id')
            ->where('categories_lang.lang_id', '=', $language->lang_id)
            ->where('categories.parent_category_id', '=', $category->id)
            ->select(
                'categories.category_id as id',
                'categories_lang.category_name as name',
                'categories.image',
                'categories.bg_color'
            )
            ->get();

            $categories[$key]->services = count($services);
            $categories[$key]->childs = $childs;
        }

        return response()->json([
            'categories' => $categories
        ], 200);
    }

    public function get_category_image(Request $request){
        $path = storage_path('/app/public/images/categories/'.$request->file_name);

        if (!File::exists($path)) {
            return response()->json('Image not found', 404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }
}
