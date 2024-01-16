<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductsManufacture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $today = Carbon::now()->toDateString();
        $user = Auth::user()->id;
        $totalMoneyToday = Order::where('order_status', '<>', 5)
            ->where('deleted', 0)
            ->where('user_init', $user)
            ->whereDate('created', $today)
            ->sum('total_price');
        $totalOrderToday = Order::where('deleted', 0)->where('user_init', $user)->whereDate('created', now())->count('ID');
        $totalCancelToday = Order::where('order_status', 5)->where('user_init', $user)->whereDate('created', now())->count();
        $totalCustomerToday = Order::where('order_status', '<>', 5)->where('user_init', $user)->whereDate('created', now())->count('customer_id');
        $totalMoneySum = Order::where('order_status', '<>', 5)->where('user_init', $user)->where('deleted', 0)->sum('total_money');
        $totalQuantity = Order::where('order_status', '<>', 5)->where('user_init', $user)->where('deleted', 0)->sum('total_quantity');
        $numberCategories = ProductsManufacture::distinct('prd_manuf_name')->count();
        $inventoryProduct = Product::where(['prd_status' => 1, 'deleted' => 0, 'prd_sls' => 0])->where('user_init', $user)->count('ID');
        $outStockProduct =  Product::where(['prd_status' => 1, 'deleted' => 0, 'prd_sls' => 0])->where('user_init', $user)->count('ID');
        $numberProducts = Product::distinct('prd_name')->where('prd_status', 1)->where('user_init', $user)->where('deleted', 0)->count();
        $nullPrice = Product::whereNull('prd_sell_price')->where('user_init', $user)->count();
        $nullPriceOrigin = Product::whereNull('prd_origin_price')->where('user_init', $user)->count();
        $totalOrder = Order::where('deleted', 0)->where('user_init', $user)->whereOr('order_status', '<>', 5)->count('ID');
        $data = [
            'total_money_sum' => $totalMoneySum,
            'totalQuantity' => $totalQuantity,
            'numberCategories' => $numberCategories,
            'numberProducts' => $numberProducts,
            'nullPrice' => $nullPrice,
            'totalMoneyToday' => $totalMoneyToday,
            'totalOrderToday' => $totalOrderToday,
            'totalCancelToday' => $totalCancelToday,
            'totalCustomerToday' => $totalCustomerToday,
            'inventoryProduct' => $inventoryProduct,
            'totalOrder' => $totalOrder,
            'outStockProduct' => $outStockProduct,
            'nullPriceOrigin' => $nullPriceOrigin,
        ];
        return response()->json($data);
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
