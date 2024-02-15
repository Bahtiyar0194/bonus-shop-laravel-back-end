<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\UserRole;
use Validator;
use App\Traits\ApiResponser;

use Illuminate\Http\Request;

class ManagerController extends Controller
{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $managers = UserRole::leftJoin('users', 'users_roles.user_id', '=', 'users.user_id')
        ->leftJoin('cities', 'cities.city_id', '=', 'users.city_id')
        ->leftJoin('cities_lang', 'cities_lang.city_id', '=', 'cities.city_id')
        ->where('users_roles.role_type_id', '=', 2)
        ->where('users_roles.status_type_id', '!=', 12)
        ->where('cities_lang.lang_id', '=', $language->lang_id)
        ->select(
            'users.user_id',
            'users.first_name',
            'users.last_name',
            'users.iin',
            'users.email',
            'users.phone',
            'cities_lang.city_name',
            'users_roles.created_at'
        )
        ->get();

        $applications = UserRole::leftJoin('users', 'users_roles.user_id', '=', 'users.user_id')
        ->leftJoin('cities', 'cities.city_id', '=', 'users.city_id')
        ->leftJoin('cities_lang', 'cities_lang.city_id', '=', 'cities.city_id')
        ->where('users_roles.role_type_id', '=', 2)
        ->where('users_roles.status_type_id', '=', 12)
        ->where('cities_lang.lang_id', '=', $language->lang_id)
        ->select(
            'users.user_id',
            'users.first_name',
            'users.last_name',
            'users.iin',
            'users.email',
            'users.phone',
            'cities_lang.city_name',
            'users_roles.created_at'
        )
        ->get();

        return response()->json([
            'managers' => $managers,
            'applications' => $applications
        ], 200);
    }

    public function submit_application(Request $request){
        $user_role = new UserRole();
        $user_role->user_id = auth()->user()->user_id;
        $user_role->role_type_id = 2;
        $user_role->status_type_id = 12;
        $user_role->save();

        return response()->json(['message' => 'success'], 200);
    }

    public function accept_application(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
        ]);

        if($validator->fails()){
            return $this->json('error', 'Accept error', 422, $validator->errors());
        }

        $user_role = UserRole::where('user_id', '=', $request->user_id)
        ->where('role_type_id', '=', 2)
        ->first();

        if(isset($user_role) && $user_role->status_type_id == 12){
            $user_role->status_type_id = 1;
            $user_role->save();

            return response()->json(['message' => 'success'], 200);
        }
    }
}
