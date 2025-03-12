<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/booking', name: 'api_booking_')]
final class BookingController extends AbstractController
{   
    /**
     * @param int $id
     * @param string $phoneNumber
     * @param string $comment
     * @return JsonResponse
     */
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(int $id, string $phoneNumber, string $comment): JsonResponse
    {
        
        // TODO: Save to csv file

        return new JsonResponse(['message' => 'Booked successfully']);
    }

    /**
     * @param int $id
     * @param string $phoneNumber|null
     * @param string $comment|null
     * @return JsonResponse
     */
    #[Route('/change', name: 'change', methods: ['PUT'])]
    public function change(int $id, ?string $phoneNumber, ?string $comment): JsonResponse
    {

        // TODO: Save to csv file

        return new JsonResponse(['message' => 'Booking changed successfully']);
    }


}
