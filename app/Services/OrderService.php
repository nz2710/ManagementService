<?php

namespace App\Services;

use App\Models\Order;

class OrderService
{
    public function deleteOrder($orderId)
    {
        $order = Order::with('partner', 'products')->find($orderId);

        if ($order) {
            $partner = $order->partner;
            $products = $order->products;

            // Cập nhật revenue, number_of_order và commission của partner
            $partner->revenue -= $order->price;
            $partner->number_of_order -= 1;
            $partner->commission -= $order->price * ($partner->discount / 100);
            $partner->save();

            // Cập nhật quantity của các sản phẩm trong đơn hàng
            foreach ($products as $product) {
                $product->quantity += $product->pivot->quantity;
                $product->save();
            }

            // Xoá các bản ghi trong bảng trung gian order_product
            $order->products()->detach();

            // Xoá đơn hàng
            $order->delete();

            return $order;
        }

        return null;
    }
}
