<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partner;
use App\Models\Language;
use App\Models\UserRole;
use Validator;
use App\Traits\ApiResponser;

class PartnerController extends Controller
{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $partners = Partner::leftJoin('cities', 'cities.city_id', '=', 'partners.city_id')
        ->leftJoin('cities_lang', 'cities_lang.city_id', '=', 'cities.city_id')
        ->leftJoin('users as applicant', 'applicant.user_id', '=', 'partners.applicant_id')
        ->leftJoin('users as operator', 'operator.user_id', '=', 'partners.operator_id')
        ->leftJoin('users as manager', 'manager.user_id', '=', 'partners.manager_id')
        ->leftJoin('types_of_status', 'partners.status_type_id', '=', 'types_of_status.status_type_id')
        ->leftJoin('types_of_status_lang', 'types_of_status.status_type_id', '=', 'types_of_status_lang.status_type_id')
        ->select(
            'partners.partner_id',
            'partners.partner_name',
            'partners.partner_org_name',
            'partners.partner_bin',
            'partners.partner_phone',
            'partners.partner_email',
            'cities_lang.city_name',
            'partners.bonus',
            'partners.logo',
            'partners.applicant_id',
            'partners.operator_id',
            'partners.manager_id',
            'applicant.first_name as applicant_first_name',
            'applicant.last_name as applicant_last_name',
            'operator.first_name as operator_first_name',
            'operator.last_name as operator_last_name',
            'manager.first_name as manager_first_name',
            'manager.last_name as manager_last_name',
            'partners.created_at',
            'types_of_status_lang.status_type_name',
        )
        ->where('partners.status_type_id', '!=', 12)
        ->where('cities_lang.lang_id', '=', $language->lang_id)
        ->where('types_of_status_lang.lang_id', '=', $language->lang_id)
        ->get();

        $applications = Partner::leftJoin('cities', 'cities.city_id', '=', 'partners.city_id')
        ->leftJoin('cities_lang', 'cities_lang.city_id', '=', 'cities.city_id')
        ->leftJoin('users as applicant', 'applicant.user_id', '=', 'partners.applicant_id')
        ->leftJoin('users as operator', 'operator.user_id', '=', 'partners.operator_id')
        ->leftJoin('users as manager', 'manager.user_id', '=', 'partners.manager_id')
        ->select(
            'partners.partner_id',
            'partners.partner_name',
            'partners.partner_org_name',
            'partners.partner_bin',
            'partners.partner_phone',
            'partners.partner_email',
            'cities_lang.city_name',
            'partners.bonus',
            'partners.logo',
            'partners.applicant_id',
            'partners.operator_id',
            'partners.manager_id',
            'applicant.first_name as applicant_first_name',
            'applicant.last_name as applicant_last_name',
            'operator.first_name as operator_first_name',
            'operator.last_name as operator_last_name',
            'manager.first_name as manager_first_name',
            'manager.last_name as manager_last_name',
            'partners.created_at'
        )
        ->where('partners.status_type_id', '=', 12)
        ->where('cities_lang.lang_id', '=', $language->lang_id)
        ->get();

        return response()->json([
            'partners' => $partners,
            'applications' => $applications
        ], 200);
    }

    public function my_organizations(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $organizations = Partner::leftJoin('cities', 'cities.city_id', '=', 'partners.city_id')
        ->leftJoin('cities_lang', 'cities_lang.city_id', '=', 'cities.city_id')
        ->leftJoin('users as applicant', 'applicant.user_id', '=', 'partners.applicant_id')
        ->leftJoin('users as operator', 'operator.user_id', '=', 'partners.operator_id')
        ->leftJoin('users as manager', 'manager.user_id', '=', 'partners.manager_id')
        ->leftJoin('types_of_status', 'partners.status_type_id', '=', 'types_of_status.status_type_id')
        ->leftJoin('types_of_status_lang', 'types_of_status.status_type_id', '=', 'types_of_status_lang.status_type_id')
        ->leftJoin('users_roles', 'partners.partner_id', '=', 'users_roles.organization_id')
        ->select(
            'partners.partner_id as id',
            'partners.partner_name as name',
            'partners.partner_org_name',
            'partners.partner_bin',
            'partners.partner_phone',
            'partners.partner_email',
            'cities_lang.city_name',
            'partners.bonus',
            'partners.logo',
            'partners.applicant_id',
            'partners.operator_id',
            'partners.manager_id',
            'applicant.first_name as applicant_first_name',
            'applicant.last_name as applicant_last_name',
            'operator.first_name as operator_first_name',
            'operator.last_name as operator_last_name',
            'manager.first_name as manager_first_name',
            'manager.last_name as manager_last_name',
            'partners.created_at',
            'types_of_status_lang.status_type_name',
        )
        ->where('users_roles.user_id', '=', auth()->user()->user_id)
        ->where('users_roles.role_type_id', '=', 4)
        ->where('partners.status_type_id', '!=', 12)
        ->where('cities_lang.lang_id', '=', $language->lang_id)
        ->where('types_of_status_lang.lang_id', '=', $language->lang_id)
        ->get();

        return response()->json([
            'organizations' => $organizations,
        ], 200);
    }

    public function submit_application(Request $request){
        $validator = Validator::make($request->all(), [
            'partner_name' => 'required|string|between:2,100',
            'partner_org_name' => 'required|string|between:2,100',
            'partner_bin' => 'required|unique:partners|digits:12',
            'partner_email' => 'required|string|email|max:100',
            'partner_phone' => 'required|size:17|regex:/^((?!_).)*$/s',
            'city' => 'required|numeric'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Registration error', 422, $validator->errors());
        }

        $partner = new Partner();
        $partner->partner_name = $request->partner_name;
        $partner->partner_org_name = $request->partner_org_name;
        $partner->partner_bin = $request->partner_bin;
        $partner->partner_email = $request->partner_email;
        $partner->partner_phone = $request->partner_phone;
        $partner->city_id = $request->city;
        $partner->bonus = 10;
        $partner->applicant_id = auth()->user()->user_id;
        $partner->operator_id = auth()->user()->user_id;
        $partner->save();

        return $this->json('success', 'Request successful', 200, ['partner' => $partner]);
    }

    public function accept_application(Request $request){
        $validator = Validator::make($request->all(), [
            'partner_id' => 'required|numeric',
        ]);

        if($validator->fails()){
            return $this->json('error', 'Accept error', 422, $validator->errors());
        }

        $partner = Partner::find($request->partner_id);

        if(isset($partner) && $partner->status_type_id == 12){
            $partner->status_type_id = 1;
            $partner->save();

            $user_role = new UserRole();
            $user_role->user_id = $partner->applicant_id;
            $user_role->role_type_id = 4;
            $user_role->organization_id = $request->partner_id;
            $user_role->save();

            return response()->json([
                'partner' => $partner
            ],200);
        }
    }
}