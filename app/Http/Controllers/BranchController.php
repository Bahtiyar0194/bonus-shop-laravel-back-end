<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PartnerBranch;
use App\Models\BranchRegulation;
use App\Models\BranchImage;
use App\Models\Language;
use Validator;
use App\Traits\ApiResponser;

use Illuminate\Support\Facades\Response;
use File;
use Str;
use Image;
use Storage;

class BranchController extends Controller
{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function my_branches(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $branches = PartnerBranch::leftJoin('cities', 'cities.city_id', '=', 'partner_branches.city_id')
        ->leftJoin('cities_lang', 'cities_lang.city_id', '=', 'cities.city_id')
        ->leftJoin('partners', 'partner_branches.partner_id', '=', 'partners.partner_id')
        ->leftJoin('users_roles', 'partners.partner_id', '=', 'users_roles.organization_id')
        ->leftJoin('types_of_status', 'partner_branches.status_type_id', '=', 'types_of_status.status_type_id')
        ->leftJoin('types_of_status_lang', 'types_of_status.status_type_id', '=', 'types_of_status_lang.status_type_id')
        ->select(
            'partner_branches.branch_id',
            'partner_branches.partner_id',
            'partner_branches.street',
            'partner_branches.house',
            'partner_branches.branch_phone',
            'partner_branches.branch_phone_additional',
            'partner_branches.latitude',
            'partner_branches.longitude',
            'partner_branches.created_at',
            'cities_lang.city_name',
            'types_of_status_lang.status_type_name'
        )
        ->where('users_roles.user_id', '=', auth()->user()->user_id)
        ->where('users_roles.role_type_id', '=', 4)
        ->where('partner_branches.status_type_id', '=', 1)
        ->where('cities_lang.lang_id', '=', $language->lang_id)
        ->where('types_of_status_lang.lang_id', '=', $language->lang_id)
        ->get();

        foreach ($branches as $key => $value) {
            $images = BranchImage::where('branch_id', '=', $value->branch_id)
            ->get();

            $branches[$key]->images = $images;
        }

        $applications = PartnerBranch::leftJoin('cities', 'cities.city_id', '=', 'partner_branches.city_id')
        ->leftJoin('cities_lang', 'cities_lang.city_id', '=', 'cities.city_id')
        ->leftJoin('partners', 'partner_branches.partner_id', '=', 'partners.partner_id')
        ->leftJoin('users_roles', 'partners.partner_id', '=', 'users_roles.organization_id')
        ->leftJoin('types_of_status', 'partner_branches.status_type_id', '=', 'types_of_status.status_type_id')
        ->leftJoin('types_of_status_lang', 'types_of_status.status_type_id', '=', 'types_of_status_lang.status_type_id')
        ->select(
            'partner_branches.branch_id',
            'partner_branches.partner_id',
            'partner_branches.street',
            'partner_branches.house',
            'partner_branches.branch_phone',
            'partner_branches.branch_phone_additional',
            'partner_branches.latitude',
            'partner_branches.longitude',
            'partner_branches.created_at',
            'partners.partner_name',
            'cities_lang.city_name',
            'types_of_status_lang.status_type_name'
        )
        ->where('users_roles.user_id', '=', auth()->user()->user_id)
        ->where('users_roles.role_type_id', '=', 4)
        ->where('partner_branches.status_type_id', '=', 8)
        ->where('cities_lang.lang_id', '=', $language->lang_id)
        ->where('types_of_status_lang.lang_id', '=', $language->lang_id)
        ->get();

        foreach ($applications as $key => $value) {
            $images = BranchImage::where('branch_id', '=', $value->branch_id)
            ->get();

            $applications[$key]->images = $images;
        }

        return response()->json([
            'branches' => $branches,
            'applications' => $applications
        ], 200);
    }

    public function add(Request $request){
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|numeric',
            'street' => 'required|string|between:2,100',
            'house' => 'required|between:1,5',
            'phone' => 'required|size:17|regex:/^((?!_).)*$/s',
            'phone_additional' => 'required|size:17|regex:/^((?!_).)*$/s',
            'city' => 'required|numeric',
            'latitude' => 'required',
            'longitude' => 'required',
            'images' => 'required|min:3'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Add branch error', 422, $validator->errors());
        }

        $new_branch = new PartnerBranch();
        $new_branch->partner_id = $request->organization_id;
        $new_branch->city_id = $request->city;
        $new_branch->street = $request->street;
        $new_branch->house = $request->house;
        $new_branch->branch_phone = $request->phone;
        $new_branch->branch_phone_additional = $request->phone_additional;
        $new_branch->latitude = $request->latitude;
        $new_branch->longitude = $request->longitude;
        $new_branch->save();

        foreach (json_decode($request->regulation) as $key => $value) {
            $new_regulation = new BranchRegulation;
            $new_regulation->branch_id = $new_branch->branch_id;
            $new_regulation->week_day_id = $value->week_day_id;
            $new_regulation->weekend = $value->weekend;
            $new_regulation->around_the_clock = $value->around_the_clock;

            if($value->weekend == 0 && $value->around_the_clock == 0){
                $new_regulation->work_begin = $value->work_begin;
                $new_regulation->work_end = $value->work_end;
            }

            $new_regulation->save();
        }

        if(count(json_decode($request->images)) > 0){
            foreach (json_decode($request->images) as $key => $value) {
                $file_name = Str::random(16);

                $new_image = new BranchImage();
                $new_image->branch_id = $new_branch->branch_id;
                $new_image->file_name = $file_name;
                $new_image->save();

                $imageData = base64_decode($value->base64);

                $resized_image = Image::make($imageData)->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->stream('png', 60);

                Storage::disk('public')->put('images/branches/'.$file_name.'.png', $resized_image);
            }
        }

        return response()->json(['message' => 'success'], 200);
    }

    public function get_branch_image(Request $request){
        $path = storage_path('/app/public/images/branches/'.$request->file_name.'.png');

        if (!File::exists($path)) {
            return response()->json('Image not found', 404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }

    public function get_branches(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $branches = PartnerBranch::leftJoin('partners', 'partner_branches.partner_id', '=', 'partners.partner_id')
        ->leftJoin('cities', 'partner_branches.city_id', '=', 'cities.city_id')
        ->leftJoin('cities_lang', 'cities_lang.city_id', '=', 'cities.city_id')
        ->select(
            'partners.partner_name',
            'partner_branches.branch_id',
            'partner_branches.partner_id',
            'partner_branches.street',
            'partner_branches.house',
            'partner_branches.branch_phone',
            'partner_branches.branch_phone_additional',
            'partner_branches.latitude',
            'partner_branches.longitude',
            'cities_lang.city_name'
        )
        ->where('partner_branches.status_type_id', '=', 1)
        ->where('partner_branches.city_id', '=', $request->city_id)
        ->where('cities_lang.lang_id', '=', $language->lang_id);

        if(isset($request->category_id)){
            $branches->where('partner_branches.category_id', '=', $request->category_id);
        }

        $branches = $branches->get();
        
        foreach ($branches as $key => $value) {
            $images = BranchImage::where('branch_id', '=', $value->branch_id)
            ->get();

            $branches[$key]->images = $images;
        }

        return response()->json($branches, 200);
    }

    public function get_org_branches(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $branches = PartnerBranch::leftJoin('partners', 'partner_branches.partner_id', '=', 'partners.partner_id')
        ->leftJoin('cities', 'partner_branches.city_id', '=', 'cities.city_id')
        ->leftJoin('cities_lang', 'cities_lang.city_id', '=', 'cities.city_id')
        ->select(
            'partner_branches.branch_id',
            'partner_branches.street',
            'partner_branches.house',
            'cities_lang.city_name'
        )
        ->where('partner_branches.status_type_id', '=', 1)
        ->where('partner_branches.partner_id', '=', $request->organization_id)
        ->where('cities_lang.lang_id', '=', $language->lang_id)
        ->orderBy('partner_branches.created_at', 'desc')
        ->get();

        $list = [];

        foreach ($branches as $key => $value) {
            array_push($list, [
                'id' => $value->branch_id,
                'name' => $value->street.' '.$value->house.' ('.$value->city_name.')'
            ]);
        }



        return response()->json($list, 200);
    }
}
