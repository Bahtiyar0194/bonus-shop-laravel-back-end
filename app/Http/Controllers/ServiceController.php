<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceCoverage;
use App\Models\Language;
use App\Models\BranchImage;
use DB;

use Validator;
use App\Traits\ApiResponser;

class ServiceController extends Controller
{

    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function my_services(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $services = Service::leftJoin('categories', 'services.category_id', '=', 'categories.category_id')
        ->leftJoin('categories_lang', 'categories.category_id', '=', 'categories_lang.category_id')
        ->leftJoin('partners', 'services.organization_id', '=', 'partners.partner_id')
        ->leftJoin('users_roles', 'partners.partner_id', '=', 'users_roles.organization_id')
        ->select(
            'services.service_id',
            'services.service_title',
            'services.service_description',
            'categories_lang.category_name'
        )
        ->where('services.status_type_id', '=', 1)
        ->where('categories_lang.lang_id', '=', $language->lang_id)
        ->where('users_roles.user_id', '=', auth()->user()->user_id)
        ->where('users_roles.role_type_id', '=', 4)
        ->get();


        foreach ($services as $key => $value) {
            $branches = ServiceCoverage::leftJoin('partner_branches', 'services_coverages.branch_id', '=', 'partner_branches.branch_id')
            ->leftJoin('cities', 'cities.city_id', '=', 'partner_branches.city_id')
            ->leftJoin('cities_lang', 'cities_lang.city_id', '=', 'cities.city_id')
            ->where('service_id', '=', $value->service_id)
            ->select(
                'services_coverages.branch_id',
                'partner_branches.street',
                'partner_branches.house',
                'cities_lang.city_name'
            )
            ->where('cities_lang.lang_id', '=', $language->lang_id)
            ->get();

            $services[$key]->branches = $branches;
        }


        $applications = Service::leftJoin('categories', 'services.category_id', '=', 'categories.category_id')
        ->leftJoin('categories_lang', 'categories.category_id', '=', 'categories_lang.category_id')
        ->leftJoin('partners', 'services.organization_id', '=', 'partners.partner_id')
        ->leftJoin('users_roles', 'partners.partner_id', '=', 'users_roles.organization_id')
        ->select(
            'services.service_id',
            'services.service_title',
            'services.service_description',
            'categories_lang.category_name'
        )
        ->where('services.status_type_id', '=', 8)
        ->where('categories_lang.lang_id', '=', $language->lang_id)
        ->where('users_roles.user_id', '=', auth()->user()->user_id)
        ->where('users_roles.role_type_id', '=', 4)
        ->get();


        foreach ($applications as $key => $value) {
            $branches = ServiceCoverage::leftJoin('partner_branches', 'services_coverages.branch_id', '=', 'partner_branches.branch_id')
            ->leftJoin('cities', 'cities.city_id', '=', 'partner_branches.city_id')
            ->leftJoin('cities_lang', 'cities_lang.city_id', '=', 'cities.city_id')
            ->where('service_id', '=', $value->service_id)
            ->select(
                'services_coverages.branch_id',
                'partner_branches.street',
                'partner_branches.house',
                'cities_lang.city_name'
            )
            ->where('cities_lang.lang_id', '=', $language->lang_id)
            ->get();

            $applications[$key]->branches = $branches;
        }


        return response()->json([
            'services' => $services,
            'applications' => $applications
        ], 200);
    }

    public function add(Request $request){
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|numeric',
            'service_title' => 'required|min:3',
            'service_description' => 'required|min:3',
            'category_id' => 'required|numeric',
            'coverage' => 'required|min:3'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Add service error', 422, $validator->errors());
        }

        $new_service = new Service();
        $new_service->organization_id = $request->organization_id;
        $new_service->service_title = $request->service_title;
        $new_service->service_description = $request->service_description;
        $new_service->operator_id = auth()->user()->user_id;
        $new_service->category_id = $request->category_id;
        $new_service->save();

        foreach (json_decode($request->coverage) as $key => $value) {
            $new_service_coverage = new ServiceCoverage;
            $new_service_coverage->service_id = $new_service->service_id;
            $new_service_coverage->branch_id = $value->id;
            $new_service_coverage->save();
        }

        return response()->json(['message' => 'success'], 200);
    }

    public function get(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $services = DB::table('services')
        ->leftJoin('services_coverages', 'services_coverages.service_id', '=', 'services.service_id')
        ->leftJoin('partner_branches', 'partner_branches.branch_id', '=', 'services_coverages.branch_id')
        ->leftJoin('partners', 'partner_branches.partner_id', '=', 'partners.partner_id')
        ->leftJoin('cities', 'partner_branches.city_id', '=', 'cities.city_id')
        ->leftJoin('cities_lang', 'cities.city_id', '=', 'cities_lang.city_id')
        ->leftJoin('categories', 'categories.category_id', '=', 'services.category_id')
        ->leftJoin('categories_lang', 'categories_lang.category_id', '=', 'categories.category_id')
        ->select(
            'partners.partner_name',
            'services.service_id',
            'services.service_title',
            'services.service_description',
            'partner_branches.branch_id',
            'partner_branches.partner_id',
            'partner_branches.street',
            'partner_branches.house',
            'partner_branches.branch_phone',
            'partner_branches.branch_phone_additional',
            'partner_branches.latitude',
            'partner_branches.longitude',
            'cities_lang.city_name',
            'categories.image',
            'categories_lang.category_name',
        )
        ->where('services.status_type_id', '=', 1)
        ->where('cities.city_id', '=', $request->city_id)
        ->where('cities_lang.lang_id', '=', $language->lang_id)
        ->where('categories_lang.lang_id', '=', $language->lang_id);


        if(isset($request->search_query) && $request->search_query != ''){
            $search_query = $request->search_query; 
            $services->where(function ($query) use ($search_query) {
                $query->orWhere('categories_lang.category_name', 'LIKE', '%'.$search_query.'%')
                ->orWhere('partners.partner_name', 'LIKE', '%'.$search_query.'%')
                ->orWhere('services.service_title', 'LIKE', '%'.$search_query.'%');
            });
        }

        $services = $services->get();

        foreach ($services as $key => $value) {
            $images = BranchImage::where('branch_id', '=', $value->branch_id)
            ->get();

            $services[$key]->images = $images;
        }

        return response()->json([
            'services' => $services
        ], 200);
    }
}