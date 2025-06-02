<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function saveOrder(Request $request)
    {
        try {
            if (empty($request->cart)) {
                return response()->json([
                    "message" => "Giỏ hàng trống",
                    "status" => 400
                ], 400);
            }

            // Bắt đầu transaction
            \Illuminate\Support\Facades\DB::beginTransaction();

            // Tạo order
            $order = new Order();
            $order->user_id = $request->user()->id;
            $order->sub_total = $request->sub_total;
            $order->grand_total = $request->grand_total;
            $order->shipping = $request->shipping;
            $order->discount = $request->discount;
            $order->payment_status = $request->payment_status;
            $order->status = $request->status;
            $order->name = $request->name;
            $order->email = $request->email;
            $order->phone = $request->phone;
            $order->address = $request->address;
            $order->city = $request->city;
            $order->district = $request->district;
            $order->commune = $request->commune;
            $order->payment_method = $request->payment_method;
            $order->save();

            // Lưu các order items
            foreach ($request->cart as $item) {
                $orderItem = new OrderItem();
                $orderItem->price = $item['qty'] * $item['price'];
                $orderItem->unit_price = $item['price'];
                $orderItem->qty = $item['qty'];
                $orderItem->product_id = $item['product_id'];
                $orderItem->order_id = $order->id;
                $orderItem->name = $item['name'];

                $orderItem->save(); // Lưu từng item
            }

            // Commit transaction nếu thành công
            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                "message" => "Thanh toán thành công",
                "order_id" => $order->id,
                "status" => 200
            ], 200);

        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            \Illuminate\Support\Facades\DB::rollBack();

            return response()->json([
                "message" => "Lỗi thanh toán: " . $e->getMessage(),
                "status" => 500
            ], 500);
        }
    }

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
            "message"=>"Get orders succesfully",
            "status"=>200
        ], 200);
    }

    public function getOrderHistory($id)
    {
        try {
            // Sử dụng get() để thực sự lấy dữ liệu từ database
            $orders = Order::where('user_id', $id)
                ->with(['items.product']) // Thêm relation product nếu cần
                ->orderBy('created_at', 'DESC') // Sắp xếp theo thời gian tạo mới nhất
                ->get();

            // Kiểm tra nếu không có đơn hàng
            if ($orders->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'message' => 'No orders found for this user',
                    'status' => 404
                ], 404);
            }

            return response()->json([
                'data' => $orders,
                'message' => 'Get all orders history successfully',
                'status' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'message' => 'Error retrieving order history: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function cancelOrder( $id)
    {
        $order = Order::with('items', 'items.product')->find($id);

        if($order == null){
            return response()->json([
                'data'=>[],
                "message"=>"Order not found",
                "status"=>404
            ], 404);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json([
            'data'=>$order,
            "message"=>"Cancelled orders succesfully",
            "status"=>200
        ], 200);
    }

    public function shippedOrder( $id)
    {
        $order = Order::with('items', 'items.product')->find($id);

        if($order == null){
            return response()->json([
                'data'=>[],
                "message"=>"Order not found",
                "status"=>404
            ], 404);
        }

        $order->status = 'shipped';
        $order->save();

        return response()->json([
            'data'=>$order,
            "message"=>"Shipped orders succesfully",
            "status"=>200
        ], 200);
    }
}
