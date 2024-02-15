<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Stock;
use App\Models\StockCoverage;
use App\Models\StockView;
use App\Models\SharedStock;
use App\Models\UserRole;
use Validator;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Response;
use Str;
use Image;
use Storage;
use File;

class StockController extends Controller
{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get(Request $request){

        if(isset(auth()->user()->user_id)){
            $find_user_role = UserRole::where('role_type_id', '=', 4)
            ->where('user_id', '=', auth()->user()->user_id)
            ->first();

            if(isset($find_user_role)){
                $my_stock = Stock::leftJoin('stock_coverages', 'stock_coverages.stock_id', '=', 'stocks.stock_id')
                ->leftJoin('partners', 'partners.partner_id', '=', 'stocks.organization_id')
                ->select(
                    'stocks.stock_id',
                    'stocks.file_name',
                    'partners.partner_id',
                    'partners.partner_name'
                )
                ->whereBetween('stocks.created_at', [date('Y-m-d H:i:s', strtotime('-1 day')), date('Y-m-d H:i:s')])
                ->get();
            }
        }

        $stocks = Stock::leftJoin('stock_coverages', 'stock_coverages.stock_id', '=', 'stocks.stock_id')
        ->leftJoin('partners', 'partners.partner_id', '=', 'stocks.organization_id')
        ->select(
            'stocks.stock_id',
            'stocks.file_name',
            'partners.partner_id',
            'partners.partner_name'
        )
        ->where('stock_coverages.city_id', '=', $request->current_location_id)
        ->whereBetween('stocks.created_at', [date('Y-m-d H:i:s', strtotime('-1 day')), date('Y-m-d H:i:s')])
        ->get();

        foreach ($stocks as $key => $value) {
            $stock_views = StockView::where('stock_id', '=', $value->stock_id)
            ->get();

            $stocks[$key]->views = count($stock_views);

            $my_view = StockView::where('stock_id', '=', $value->stock_id)
            ->where('user_id', '=', $request->user_id)
            ->first();

            if(isset($my_view)){
                $stocks[$key]->my_view = 1;
            }
            else{
                $stocks[$key]->my_view = 0;
            }
            
        }

        if(isset($request->user_id)){
            $stocks = $stocks->sortBy([
                ['my_view', 'asc'],
                ['stock_id', 'desc']
            ]);
            $stocks->values()->all();
        }

        return response()->json(['stocks' => $stocks], 200);
    }

    public function get_shared_stock(Request $request){
        $shared_stock = SharedStock::leftJoin('stocks', 'shared_stocks.stock_id', '=', 'stocks.stock_id')
        ->leftJoin('users', 'shared_stocks.user_id', '=', 'users.user_id')
        ->select(
            'stocks.stock_id',
            'shared_stocks.stock_hash',
            'users.login'
        )
        ->where('shared_stocks.stock_hash', '=', $request->shared_stock)
        ->first();

        if(isset($shared_stock)){
            return view('stock', [
                'stock_id' => $shared_stock->stock_id,
                'stock_hash' => $shared_stock->stock_hash,
                'login' => $shared_stock->login
            ]);
        }
        else{
            abort(404);
        }
    }

    public function view(Request $request){
        $stock_view = StockView::where('stock_id', '=', $request->stock_id)
        ->where('user_id', '=', auth()->user()->user_id)
        ->first();

        if(!isset($stock_view)){
            $new_stock_view = new StockView();
            $new_stock_view->stock_id = $request->stock_id;
            $new_stock_view->user_id = auth()->user()->user_id;
            $new_stock_view->save();

            return response()->json(['message' => 'Success'], 200);
        }
        else{
            return response()->json(['message' => 'Stock view error'], 403);
        }
    }

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|numeric',
            'stock_type_id' => 'required',
            'file' => 'required',
            'coverage' => 'required|min:3'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Add stock error', 422, $validator->errors());
        }

        if($request->stock_type_id == 1){
            $file_name = rand(1000, 9999).Str::random(32).rand(1000, 9999).'.png';

            $imageData = base64_decode($request->file);

            $resized_image = Image::make($imageData)->resize(400, null, function ($constraint) {
                $constraint->aspectRatio();
            })->stream('png', 20);

            Storage::disk('public')->put('images/stocks/'.$request->organization_id.'/'.$file_name, $resized_image);
        }

        $new_stock = new Stock();
        $new_stock->organization_id = $request->organization_id;
        $new_stock->operator_id = auth()->user()->user_id;
        $new_stock->stock_type_id = $request->stock_type_id;
        $new_stock->file_name = $file_name;
        $new_stock->save();

        foreach (json_decode($request->coverage) as $key => $value) {
            $new_stock_coverage = new StockCoverage;
            $new_stock_coverage->stock_id = $new_stock->stock_id;
            $new_stock_coverage->city_id = $value->id;
            $new_stock_coverage->save();
        }

        return response()->json(['message' => 'success'], 200);
    }

    public function share(Request $request){

        $stock = Stock::find($request->stock_id);

        if(isset($stock)){

            $new_shared_stock = new SharedStock();
            $new_shared_stock->stock_hash = Str::random(16);
            $new_shared_stock->user_id = auth()->user()->user_id;
            $new_shared_stock->stock_id = $stock->stock_id;
            $new_shared_stock->save();

            return response()->json([
                'message' => 'success',
                'stock_hash' => $new_shared_stock->stock_hash
            ], 200);
        }
        else{
            return response()->json('Stock not found', 404);
        }
    }

    public function get_stock_image(Request $request){

        $stock = Stock::find($request->stock_id);

        if(isset($stock)){
            $path = storage_path('/app/public/images/stocks/'.$stock->organization_id.'/'.$stock->file_name);

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
}
