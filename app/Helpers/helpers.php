<?php 

use App\Models\User;
use App\Models\Bonus;

if (!function_exists('new_sms')){
	function new_sms($message, $phone){
		include_once "smsc_api.php";

		send_sms(str_replace(array(' ', '(' , ')', '-'), '', $phone), $message);
	}
}

if (!function_exists('create_bonus')){
	function create_bonus($recipient_id, $operation_id, $amount, $level_id, $bonus_type_id){
		$new_bonus = new Bonus();
		$new_bonus->operation_id = $operation_id;
		$new_bonus->recipient_id = $recipient_id;
		$new_bonus->amount = $amount;
		$new_bonus->level_id = $level_id;
		$new_bonus->bonus_type_id = $bonus_type_id;
		$new_bonus->save();
	}
}

// Клиенты пользователя
if (!function_exists('get_user_clients')){
	function get_user_clients($u_id, &$count, $level) {
		$level -= 1;

		if($level > 0){
			$childs = User::where('parent_id', '=', $u_id)
			->where('status_type_id', '=', 1)
			->get();

			if(count($childs) > 0){
				foreach ($childs as $child) {
					$count += 1;
					get_user_clients($child->user_id, $count, $level);
				}
			}
		}
	}
}
?>