<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Dto\BookingDto;
use App\Service\BookingService;

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

        return $this->json($bookings);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request, BookingService $bookingService): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        // TODO: I don't know how to validate the data

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

        if ($bookingService->saveBookings([$booking]) === false) {
            return $this->json(['error' => 'Failed to save booking'], 500);
        }

        return $this->json(['message' => 'Booked successfully'], 201);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/change-comment/{bookingId}', name: 'change', methods: ['PUT'])]
    public function change(Request $request, int $bookingId, BookingService $bookingService): JsonResponse
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

        $updated = false;

        foreach ($bookings as $key => $booking) {
            if ($booking->id === $bookingId) {
                $bookings[$key] = new BookingDto(
                    id: $booking->id,
                    phoneNumber: $booking->phoneNumber,
                    houseId: $booking->houseId,
                    comment: $data['comment']
                );
                $updated = true;
                break;
            }
        }

        if ($updated == false) {
            return $this->json(['error' => 'Booking not found'], 404);
        }

        if ($bookingService->saveBookings($bookings, true) === false) {
            return $this->json(['error' => 'Failed to save booking'], 500);
        }

        return $this->json(['message' => 'Booking updated successfully', 'booking' => $bookings], 200);
    }
}
