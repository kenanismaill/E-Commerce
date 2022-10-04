<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Services\CreateOrderService;
use App\Services\UpdateOrderService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;

class OrderController extends AbstractController
{
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/api/orders', name: 'app_order', methods: ['GET'])]
    public function index(): Response
    {
        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->managerRegistry->getRepository(Order::class);
        $orders = $orderRepo->findAll();
        return $this->json($orders);
    }

    #[Route('/api/orders/create', name: 'app_order_create', methods: ['POST'])]
    public function store(Request $request)
    {
        $constraints = new Assert\Collection([
            'user_id' => new Assert\Required([
                new Assert\NotBlank(),
            ]),
            'address' => new Assert\Required([
                new Assert\NotBlank(),
            ]),
            'product_id' => new Assert\Required([
                new Assert\NotBlank(),
            ]),
            'quantity' => new Assert\Required([
                new Assert\NotBlank(),
            ]),
        ]);
        $validator = Validation::createValidator();
        $violations = $validator->validate(json_decode($request->getContent(), true), $constraints);
        if ($violations->count() > 0) {
            /** @var ConstraintViolation $violation */
            foreach ($violations as $violation) {
                return $this->json("error " . $violation->getInvalidValue() . " " . $violation->getMessage());
            }
        }
        $service = new CreateOrderService();

        $order = $service->handle(json_decode($request->getContent(), true));
        $this->managerRegistry->getManager()->persist($order);
        $this->managerRegistry->getManager()->flush();
        return $this->json(['message' => 'Order created successfully'], 201);
    }

    #[Route('/api/orders/{id}', name: 'app_order_show', methods: ['GET'])]
    public function show($id): JsonResponse
    {
        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->managerRegistry->getRepository(Order::class);
        $order = $orderRepo->find($id);
        return $this->json($order);
    }

    #[Route('/api/orders/{id}', name: 'app_order_update', methods: ['PUT'])]
    public function update($id, Request $request): JsonResponse
    {
        $constraints = new Assert\Collection([
            'user_id' => new Assert\Required([
                new Assert\NotBlank(),
            ]),
            'address' => new Assert\Required([
                new Assert\NotBlank(),
            ]),
            'product_id' => new Assert\Required([
                new Assert\NotBlank(),
            ]),
            'quantity' => new Assert\Required([
                new Assert\NotBlank(),
            ]),
        ]);
        $validator = Validation::createValidator();
        $violations = $validator->validate(json_decode($request->getContent(), true), $constraints);
        if ($violations->count() > 0) {
            /** @var ConstraintViolation $violation */
            foreach ($violations as $violation) {
                return $this->json("error " . $violation->getInvalidValue() . " " . $violation->getMessage());
            }
        }

        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->managerRegistry->getRepository(Order::class);
        $order = $orderRepo->find($id);

        if (is_null($order)) {
            return $this->json(['message' => 'Order not found'], 404);
        }

        if ($order->getShippingDate() !== null) {
            return $this->json(['message' => 'Order already shipped'], 400);
        }
        $order = UpdateOrderService::handle($order, json_decode($request->getContent(), true));

        $this->managerRegistry->getManager()->persist($order);
        $this->managerRegistry->getManager()->flush();
        return $this->json(['message' => 'Order updated successfully'], 200);
    }
}
