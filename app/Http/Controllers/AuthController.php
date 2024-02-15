<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRole;
use App\Models\City;
use App\Models\Region;
use App\Models\Language;
use App\Models\Theme;
use App\Models\Bonus;
use App\Models\Partner;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Response;
use Hash;
use Session;
use Config;

class AuthController extends Controller
{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function register(Request $request){

        if (!Session::has('lang')){
            $language = Language::where('lang_tag', '=', Config::get('app.fallback_locale'))->first();
        }
        else{
            $language = Language::where('lang_tag', '=', Session::get('lang'))->first();
        }

        $regions = Region::leftJoin('regions_lang', 'regions.region_id', '=', 'regions_lang.region_id')
        ->where('regions_lang.lang_id', '=', $language->lang_id)
        ->get();

        $cities_result = [];

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

            array_push($cities_result, $result);
        }

        $sponsor = User::where('login', '=', $request->login)
        ->first();

        if(isset($sponsor)){
            return view('register', [
                'sponsor' => $sponsor,
                'cities' => $cities_result,
                'language' => $language
            ]);
        }
        else{
            abort(404);
        }
    }

    public function register_form(Request $request){
        $this->validate($request, [
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
            'login' => 'required|string|regex:/([A-Za-z0-9 ])+/|max:255|unique:users',
            'sponsor_id' => 'required|numeric',
            'iin' => 'required|unique:users|digits:12',
            'email' => 'required|string|email|max:100',
            'phone' => 'required|regex:/^((?!_).)*$/s|unique:users',
            'city' => 'required|numeric',
            'password' => 'min:8',
            'password_confirmation' => 'min:8|required_with:password|same:password'
        ]);

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->login = $request->login;
        $user->parent_id = $request->sponsor_id;
        $user->iin = $request->iin;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->city_id = $request->city;
        $user->password = bcrypt($request->password);
        $user->status_type_id = 1;
        $user->save();

        $user_role = new UserRole();
        $user_role->user_id = $user->user_id;
        $user_role->role_type_id = 1;
        $user_role->save();

        $userdata = array(
            'phone' => $request->phone,
            'password' => $request->password
        );

        Auth::attempt($userdata);
        return view('get_app');
    }

    public function check_phone(Request $request){
        $validator = Validator::make($request->all(), [
            'phone' => 'required|size:17|regex:/^((?!_).)*$/s',
        ]);

        if($validator->fails()) {
            return $this->json('error', 'Login error', 422, $validator->errors());
        }

        $sms_code = rand(100000, 999999);
        $sms_hash = bcrypt($sms_code);

        $getUser = User::where('phone', $request->phone)
        ->first();

        if(!isset($getUser)){
            $user = new User();
            $user->phone = $request->phone;
            $user->current_role_id = 1;
            $user->sms_hash = $sms_hash;
            $user->save();

            $user_role = new UserRole();
            $user_role->user_id = $user->user_id;
            $user_role->role_type_id = 1;
            $user_role->save();

            $sms_message = $user->lang_id == 1 ? 'Сіздің растау кодыңыз - ' : 'Ваш код подтверждения - ';
            new_sms($sms_message.$sms_code, $user->phone);

            return $this->json('success', 'send_sms', 200, [
                'user_id' => $user->user_id
            ]);
        }


        if($getUser->status_type_id == 3){
            $getUser->sms_hash = $sms_hash;
            $getUser->save();

            $sms_message = $getUser->lang_id == 1 ? 'Сіздің растау кодыңыз - ' : 'Ваш код подтверждения - ';
            new_sms($sms_message.$sms_code, $getUser->phone);

            return $this->json('success', 'send_sms', 200, ['user_id' => $getUser->user_id]);
        }


        return $this->json('success', 'password_login', 200, [
            'user_id' => $getUser->user_id,
            'user_name' => $getUser->first_name
        ]);
    }

    public function reset_password(Request $request){
        $sms_code = rand(100000, 999999);
        $sms_hash = bcrypt($sms_code);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
        ]);

        if($validator->fails()) {
            return $this->json('error', 'Reset password_login error', 422, $validator->errors());
        }

        $user = User::find($request->user_id);

        if(isset($user)){
          $user->sms_hash = $sms_hash;
          $user->status_type_id = 5;
          $user->save();

          $sms_message = $user->lang_id == 1 ? 'Сіздің растау кодыңыз - ' : 'Ваш код подтверждения - ';

          new_sms($sms_message.$sms_code, $user->phone);

          return $this->json('success', 'reset_password', 200, ['user_id' => $user->user_id]);
      }
      else{
        return $this->json('error', 'Reset password error', 422, ['sms' => trans('auth.not_found')]);
    }
}

public function activation(Request $request){
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|numeric',
        'sms' => 'required|digits:6',
    ]);

    if($validator->fails()) {
        return $this->json('error', 'Activation error', 422, $validator->errors());
    }

    $user = User::find($request->user_id);

    if(isset($user)){
        if(!(Hash::check($request->sms, $user->sms_hash))){
            return $this->json('error', 'Activation error', 422, ['sms' => trans('auth.wrong_sms')]);
        }

        return $this->json('success', 'set_password', 200, ['user_id' => $user->user_id]);
    }
    else{
        return $this->json('error', 'Activation error', 422, ['sms' => trans('auth.not_found')]);
    }
}

public function set_password(Request $request){
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|numeric',
        'password' => 'min:8',
        'password_confirmation' => 'min:8|required_with:password|same:password'
    ]);

    if($validator->fails()) {
        return $this->json('error', 'Set password error', 422, $validator->errors());
    }

    $user = User::find($request->user_id);

    if(!isset($user)){
        return $this->json('error', 'Login error', 401, ['auth_failed' => trans('auth.not_found')]);
    }

    $user->password = bcrypt($request->password);

    if($user->status_type_id == 3){
        $message = 'register';
    }
    elseif($user->status_type_id == 5){
        $user->status_type_id = 1;
        $message = 'reset_password_success';
    }

    $user->save();

    return $this->json('success', $message, 200, ['token' => $user->createToken('API Token')->plainTextToken]);
}

public function login(Request $request){
    $validator = Validator::make($request->all(), [
        'phone' => 'required|regex:/^((?!_).)*$/s',
        'password' => 'required',
    ]);

    if($validator->fails()){
        return $this->json('error', 'Login error', 422, $validator->errors());
    }

    $userdata = array(
        'phone' => $request->phone,
        'password' => $request->password,
    );

    if (!Auth::attempt($userdata)) {
        return $this->json('error', 'Login error', 401, ['auth_failed' => trans('auth.failed')]);
    }

    if(auth()->user()->user_status_id == 2){
        return $this->json('error', 'Login error', 401, ['auth_failed' => trans('auth.banned')]);
    }

    return $this->json('success', 'Login successful', 200,  ['token' => auth()->user()->createToken('API Token')->plainTextToken]);
}

public function registration(Request $request){
    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|between:2,100',
        'last_name' => 'required|string|between:2,100',
        'iin' => 'required|unique:users|digits:12',
        'email' => 'required|string|email|max:100',
        'city' => 'required|numeric'
    ]);

    if($validator->fails()){
        return $this->json('error', 'Registration error', 422, $validator->errors());
    }

    $user = auth()->user();
    $user->first_name = $request->first_name;
    $user->last_name = $request->last_name;
    $user->iin = $request->iin;
    $user->email = $request->email;
    $user->city_id = $request->city;
    $user->status_type_id = 1;
    $user->save();

    return $this->json('success', 'Registration successful', 200, ['user' => $user]);
}

public function me(Request $request){
    $user = auth()->user();

    $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

    $city = City::leftJoin('cities_lang', 'cities.city_id', '=', 'cities_lang.city_id')
    ->where('cities.city_id', '=', $user->city_id)
    ->where('cities_lang.lang_id', '=', $language->lang_id)
    ->select(
        'cities_lang.city_name'
    )
    ->first();

    $user->city_name = $city->city_name;

    if(isset($user->theme_id)){
        $theme = Theme::find($user->theme_id);
        $user->theme_slug = $theme->theme_slug;
    }

    if(isset($user->lang_id)){
        $language = Language::find($user->lang_id);
        $user->lang_tag = $language->lang_tag;
    }

    $roles = UserRole::leftJoin('types_of_user_roles', 'users_roles.role_type_id', '=', 'types_of_user_roles.role_type_id')
    ->leftJoin('types_of_user_roles_lang', 'types_of_user_roles.role_type_id', '=', 'types_of_user_roles_lang.role_type_id')
    ->where('users_roles.user_id', $user->user_id)
    ->where('users_roles.status_type_id', '=', 1)
    ->where('types_of_user_roles_lang.lang_id', $language->lang_id)
    ->select(
        'users_roles.role_type_id',
        'types_of_user_roles.role_type_slug',
        'types_of_user_roles_lang.user_role_type_name'
    )
    ->orderBy('users_roles.id')
    ->get();

    $roles_id = [];

    foreach ($roles as $key => $role) {
        if($role->role_type_id == $user->current_role_id){
            $user->current_role_name = $role->user_role_type_name;
            break;
        }
    }

    foreach ($roles as $key => $role) {
        array_push($roles_id, $role->role_type_id);
    }

    $user->roles = $roles;
    $user->roles_id = $roles_id;

    $bonuses = Bonus::leftJoin('bonus_levels', 'bonus_levels.level_id', '=', 'bonuses.level_id')
    ->select(
        'bonuses.*',
        'bonus_levels.level_slug'
    )
    ->where('bonuses.recipient_id', '=', $user->user_id)
    ->get();

    $available_bonuses = 0;
    $on_inspection = 0;
    $my_bonuses = 0;
    $superior_client_bonuses = 0;
    $manager_bonuses = 0;
    $developer_bonuses = 0;
    $business_bonuses = 0;
    $unknown_bonuses = 0;


    foreach ($bonuses as $key => $value) {
        if($value->status_type_id == 8){
            $on_inspection += $value->amount;
        }
        elseif($value->status_type_id == 1){
            switch ($value->level_slug) {
                case 'self_client';
                $my_bonuses += $value->amount;
                break;
                case 'superior_client';
                case 'superior_client_1';
                $superior_client_bonuses += $value->amount;
                break;
                case 'manager':
                $manager_bonuses += $value->amount;
                break;
                case 'developers':
                $developer_bonuses += $value->amount;
                break;
                case 'business':
                $business_bonuses += $value->amount;
                break;
                default:
                $unknown_bonuses += $value->amount;
                break;
            }
        }
    }

    $perc90 = ($my_bonuses * 10) - $my_bonuses;

    if($perc90 <= $superior_client_bonuses){
        $all_active_bonuses = $my_bonuses * 10;
        $my_bonuses = 0;
        $superior_client_bonuses = $superior_client_bonuses - $perc90;
    }
    else{
     $diff = (($superior_client_bonuses * 100) / 90) - $superior_client_bonuses;
     $all_active_bonuses = $superior_client_bonuses + $diff;
     $my_bonuses = $my_bonuses - $diff;
     $superior_client_bonuses = 0;
 }


 $user->bonuses = [
    'all_active_bonuses' => round($all_active_bonuses, 2),
    'on_inspection' => round($on_inspection, 2),
    'my_bonuses' => round($my_bonuses, 2),
    'superior_client_bonuses' => round($superior_client_bonuses, 2),
    'manager_bonuses' => round($manager_bonuses, 2),
    'developer_bonuses' => round($developer_bonuses, 2),
    'business_bonuses' => round($business_bonuses, 2),
    'unknown_bonuses' => round($unknown_bonuses, 2)
];

$user_clients_count = 0;
$level = 5;

get_user_clients(auth()->user()->user_id, $user_clients_count, $level + 1);

$user->clients_count = $user_clients_count;

$user->partners_count = count(Partner::where('manager_id', '=', auth()->user()->user_id)
    ->get());

return response()->json(['user' => $user], 200);
}

public function change_mode(Request $request){
 $user = auth()->user();
 $role_found = false;

 $roles = UserRole::where('user_id', $user->user_id)
 ->select('role_type_id')->get();

 foreach ($roles as $key => $value) {
    if($value->role_type_id == $request->role_type_id){
        $role_found = true;
        break;
    }
}

if($role_found === true){
    $change_user = User::find($user->user_id);
    $change_user->current_role_id = $request->role_type_id;
    $change_user->save();

    return response()->json('User mode change successful', 200);
}
else{
 return response()->json('Access denied', 403);
}
}

public function change_language(Request $request){
    $language = Language::where('lang_tag', '=', $request->lang_tag)->first();

    $user = auth()->user();
    $user->lang_id = $language->lang_id;
    $user->save();
    return response()->json('User language change successful', 200);
}

public function change_theme(Request $request){
    $theme = Theme::where('theme_slug', '=', $request->theme_slug)->first();

    $user = auth()->user();
    $user->theme_id = $theme->theme_id;
    $user->save();
    return response()->json('User theme change successful', 200);
}

public function change_location(Request $request){
    $user = auth()->user();
    $user->current_location_id = $request->location_id;
    $user->save();
    return response()->json('User location change successful', 200);
}
}
