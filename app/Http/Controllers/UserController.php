<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Language;
use App\Models\City;
use App\Models\UserRole;
use DB;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function get_user(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $user = User::leftJoin('cities', 'cities.city_id', '=', 'users.city_id')
        ->leftJoin('cities_lang', 'cities.city_id', '=', 'cities_lang.city_id')
        ->where('cities_lang.lang_id', '=', $language->lang_id)
        ->where('user_id', '=', $request->user_id)
        ->select(
            'users.*',
            'cities_lang.city_name'
        )
        ->first();

        $roles = DB::table('types_of_user_roles')
        ->leftJoin('types_of_user_roles_lang','types_of_user_roles.role_type_id','=','types_of_user_roles_lang.role_type_id')
        ->where('types_of_user_roles_lang.lang_id', $language->lang_id)
        ->select(
            'types_of_user_roles.role_type_id',
            'types_of_user_roles_lang.user_role_type_name'
        )
        ->get();

        foreach ($roles as $key => $role) {
            $find_user_role = UserRole::where('role_type_id', '=', $role->role_type_id)
            ->where('user_id', '=', $user->user_id)
            ->first();

            if(isset($find_user_role)){
                $roles[$key]->selected = true;
            }
            else{
                $roles[$key]->selected = false;
            }
        }

        $user->roles = $roles;

        return response()->json(['user' => $user], 200);
    }
}
