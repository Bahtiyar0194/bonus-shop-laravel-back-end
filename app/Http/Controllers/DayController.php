<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeekDay;
use App\Models\Language;
use Validator;
use App\Traits\ApiResponser;

class DayController extends Controller
{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get_days_of_week(Request $request){
      $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

      $week_days = WeekDay::leftJoin('week_days_lang', 'week_days_lang.week_day_id', '=', 'week_days.week_day_id')
      ->where('week_days_lang.lang_id', '=', $language->lang_id)
      ->select(
        'week_days.week_day_id',
        'week_days.work_begin',
        'week_days.work_end',
        'week_days.around_the_clock',
        'week_days.weekend',
        'week_days_lang.week_day_name'
    )
      ->get();

      return response()->json([
        'week_days' => $week_days,
    ], 200);
  }
}
