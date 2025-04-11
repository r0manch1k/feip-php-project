<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Dto\BookingDto;
use App\Service\BookingService;
use App\Service\SummerHouseService;

#[Route('/api/booking', name: 'api_booking_')]
final class BookingController extends AbstractController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(Request $request, BookingService $bookingService): JsonResponse
    {
        $bookings = $bookingService->getBookings();

        if ($bookings === false) {
            return $this->json(['error' => 'Failed to open file'], 500);
        }

        return $this->json($bookings, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request, BookingService $bookingService, SummerHouseService $summerHouseService): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        // TODO: I will implement validation when migrate to orm

        if (!isset($data['phoneNumber'])) {
            return $this->json(['error' => 'Missing Phone Number'], 400);
        }

        if (!isset($data['houseId'])) {
            return $this->json(['error' => 'Missing House ID'], 400);
        }

        if (!is_string($data['phoneNumber'])) {
            return $this->json(['error' => 'Phone Number must be a string'], 400);
        }

        if (!is_int($data['houseId'])) {
            return $this->json(['error' => 'House ID must be an integer'], 400);
        }

        $booking = new BookingDto(
            id: -1,
            phoneNumber: $data['phoneNumber'],
            houseId: $data['houseId'],
            comment: $data['comment'] ? $data['comment'] : 'None'
        );

        if ($bookingService->saveBookings($summerHouseService, [$booking]) === false) {
            return $this->json(['error' => 'Failed to save booking'], 500);
        }

        return $this->json(['message' => 'Booked successfully'], 201);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/change-comment/{bookingId}', name: 'change', methods: ['PUT'])]
    public function change(Request $request, int $bookingId, BookingService $bookingService, SummerHouseService $summerHouseService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['comment'])) {
            return $this->json(['error' => 'Missing Comment'], 400);
        }

        if (!is_string($data['comment'])) {
            return $this->json(['error' => 'Comment must be a string'], 400);
        }

        $bookings = $bookingService->getBookings();

        if ($bookings === false) {
            return $this->json(['error' => 'Failed to open file'], 500);
        }

        /**
         * @var bool $updated
         */
        $isIdExists = $bookingService->isIdExists($bookingId);

        if ($isIdExists === false) {
            return $this->json(['error' => 'Booking not found'], 404);
        }

        foreach ($bookings as $key => $booking) {
            if ($booking->id === $bookingId) {
                $bookings[$key] = new BookingDto(
                    id: $booking->id,
                    phoneNumber: $booking->phoneNumber,
                    houseId: $booking->houseId,
                    comment: $data['comment']
                );
                break;
            }
        }

        if ($bookingService->saveBookings($summerHouseService, $bookings, true) === false) {
            return $this->json(['error' => 'Failed to save booking'], 500);
        }

        return $this->json(['message' => 'Booking updated successfully', 'booking' => $bookings], 200);
    }
}
