<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Operation;
use App\Models\Language;
use App\Models\Partner;
use App\Models\BonusLevel;
use Validator;
use App\Traits\ApiResponser;
use Str;

class OperationController extends Controller
{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|max:10000000'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Operation create error', 422, $validator->errors());
        }

        $new_operation = new Operation();
        $new_operation->operator_id = auth()->user()->user_id;
        $new_operation->amount = $request->amount;
        $new_operation->operation_hash = Str::random(12);
        $new_operation->save();

        return response()->json($new_operation, 200);
    }

    public function check(Request $request){
        $find_operation = Operation::where('operation_hash', '=', $request->hash)
        ->first();

        if(isset($find_operation)){            
            return response()->json($find_operation, 200);
        }
        else{
            return response()->json('Not found', 404);
        }
    }

    public function scan(Request $request){
        $find_operation = Operation::where('operation_hash', '=', $request->hash)
        ->where('status_type_id', '=', 11)
        ->first();

        if(isset($find_operation)){
            $find_operation->client_id = auth()->user()->user_id;
            $find_operation->status_type_id = 10;
            $find_operation->save();

            $organization = Partner::leftJoin('users_roles', 'users_roles.organization_id', '=', 'partners.partner_id')
            ->where('users_roles.user_id', '=', $find_operation->operator_id)
            ->where('users_roles.organization_id', '>=', 1)
            ->first();

            $organization_percentage = $organization->bonus;

            $summ = ($find_operation->amount / 100) * $organization_percentage;
            $remain = 0;

            $bonus_levels = BonusLevel::get();

            foreach ($bonus_levels as $key => $level) {

                $operation_id = $find_operation->operation_id;
                $level_id = $level->level_id;

                if($level->level_slug == 'self_client'){
                    $recipient_id = auth()->user()->user_id;
                    $amount = ($summ / 100) * $level->percentage;
                    $bonus_type_id = 2;
                    create_bonus($recipient_id, $operation_id, $amount, $level_id, $bonus_type_id);
                }
                elseif($level->level_slug == 'superior_client_1'){
                    $parent_id = auth()->user()->parent_id;
                    $amount = ($summ / 100) * $level->percentage;

                    if(isset($parent_id)){
                        $parent = User::find($parent_id);

                        if(isset($parent)){
                            $recipient_id = $parent->user_id;
                            $bonus_type_id = 2;
                            create_bonus($recipient_id, $operation_id, $amount, $level_id, $bonus_type_id);
                        }
                    }
                    else{
                        $remain += $amount;
                    }
                }
                elseif($level->level_slug == 'superior_client'){
                    $parent_id = $parent->parent_id;
                    $amount = ($summ / 100) * $level->percentage;

                    if(isset($parent_id)){
                        $parent = User::find($parent_id);

                        if(isset($parent)){
                            $recipient_id = $parent->user_id;
                            $bonus_type_id = 2;
                            create_bonus($recipient_id, $operation_id, $amount, $level_id, $bonus_type_id);
                        }
                    }
                    else{
                        $remain += $amount;
                    }
                }
                elseif($level->level_slug == 'manager'){
                    $manager_id = $organization->manager_id;
                    $amount = ($summ / 100) * $level->percentage;

                    if(isset($manager_id)){
                        $manager = User::find($manager_id);

                        if(isset($manager)){
                            $recipient_id = $manager->user_id;
                            $bonus_type_id = 1;
                            create_bonus($recipient_id, $operation_id, $amount, $level_id, $bonus_type_id);
                        }
                    }
                    else{
                        $remain += $amount;
                    }
                }
                elseif($level->level_slug == 'developers'){
                    $developer = User::find(1);
                    $amount = ($summ / 100) * $level->percentage;

                    if(isset($developer)){
                        $recipient_id = $developer->user_id;
                        $bonus_type_id = 1;
                        create_bonus($recipient_id, $operation_id, $amount, $level_id, $bonus_type_id);
                    }
                    else{
                       $remain += $amount;
                   }
               }
               elseif($level->level_slug == 'business'){
                $business_id = 2;
                $business = User::find($business_id);

                if(isset($business)){
                    $recipient_id = $business->user_id;
                    $amount = (($summ / 100) * $level->percentage) + $remain;
                    $bonus_type_id = 1;
                    create_bonus($recipient_id, $operation_id, $amount, $level_id, $bonus_type_id);
                }
            }
        }

        return response()->json($find_operation, 200);
    }
    else{
        return response()->json('Not found', 404);
    }
}
}