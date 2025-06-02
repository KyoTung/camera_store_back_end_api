<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    //show all order
    public function index(Request $request)
    {
      $orders = Order::orderBy('created_at', 'DESC')->get();

      return response()->json([
        'data'=>$orders,
        "message"=>"Get all orders succesfully",
        "status"=>200
        ], 200);
    }

   //show detail one order
    public function show(Request $request, $id)
    {
        $order = Order::with('items', 'items.product')->find($id);

        if($order == null){
            return response()->json([
                'data'=>[],
                "message"=>"Order not found",
                "status"=>404
            ], 404);

        }

        return response()->json([
            'data'=>$order,
            "message"=>"Get all orders succesfully",
            "status"=>200
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $order = Order::with('items', 'items.product')->find($id);

        if($order == null){
            return response()->json([
                'data'=>[],
                "message"=>"Order not found",
                "status"=>404
            ], 404);
        }

        $order->status = $request->status;
        $order->payment_status = $request->payment_status;

        $order->save();

        return response()->json([
            'data'=>$order,
            "message"=>"Update orders succesfully",
            "status"=>200
        ], 200);
    }
}
