<?php

namespace App\Services;

use App\Entity\Order;

class CreateOrderService
{

    public function handle(array $data) : Order
    {
        try {
            $order = new Order();
            $order->setUserId($data['user_id']);
            $order->setAddress($data['address']);
            $order->setQuantity($data['quantity']);
            $order->setProductId($data['product_id']);
            $order->setOrderCode(time().hash('sha256', $data['user_id']));
        } catch (\Throwable $exception){
            throw new \RuntimeException($exception->getMessage());
        }

        return $order;
    }
}