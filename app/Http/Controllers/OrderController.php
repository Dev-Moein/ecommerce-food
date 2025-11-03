<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public static function create($cart,$addressId,$coupon,$amounts,$token,$status,$refId = null)
    {
        DB::beginTransaction();

        $order = Order::create([
            'user_id' => Auth::id(),
            'address_id' => $addressId,
            'total_amount' => $amounts['totalAmount'],
            'coupon_amount' => $amounts['couponAmount'],
            'paying_amount' => $amounts['payingAmount'],
            'coupon_id' => $coupon == null ? null : $coupon->id,
            'payment_status' => $status
        ]);

        foreach($cart as $key => $orderItem) {
            $product = Product::findOrfail($key);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'price' => $product->is_sale ? $product->sale_price : $product->price,
                'quantity' => $orderItem['qty'],
                'subtotal' => $product->is_sale ? ($product->sale_price * $orderItem['qty']) : ($product->price * $orderItem['qty'])
            ]);
        }

        Transaction::create([
            'user_id' => Auth::id(),
            'order_id' => $order->id,
            'amount' => $amounts['payingAmount'],
            'token' => $token,
            'ref_number' => $refId,
            'status' => $status
        ]);

        DB::commit();
        return $order;
    }
}
