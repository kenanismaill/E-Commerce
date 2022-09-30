<?php

namespace App\Services;

use App\Entity\Order;

class UpdateOrderService
{
    public static function handle(Order $order, array $data): Order
    {
        try {
            $order->setAddress($data['address'])->setQuantity($data['quantity'])
                ->setUserId($data['user_id'])->setProductId($data['product_id']);
            if (isset($data['shipping_date'])) {
                $time = new \DateTime('now');
                $order->setShippingDate($time->add(new \DateInterval('P1D')));
            }
        } catch (\Throwable $exception) {
            throw new \RuntimeException($exception->getMessage());
        }

        return $order;
    }
}