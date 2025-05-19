<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\BookingDto;
use App\Service\BookingService;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/booking', name: 'api_booking')]
final class BookingController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(Request $request, BookingService $bookingService): JsonResponse
    {
        try {
            $bookings = $bookingService->getBookings();
        } catch (Exception $e) {
            return $this->json(['error' => 'falied to get bookings (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(array_map(fn ($b) => $b->toArray(), $bookings), 200);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        BookingService $bookingService,
        ValidatorInterface $validator,
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (!isset($data['houseId'], $data['startDate'], $data['endDate'])) {
            return $this->json(['error' => 'missing required fields'], 400);
        }

        $user = $this->getUser();

        if (!$user || !$user instanceof \App\Entity\User) {
            return $this->json(['error' => 'user not authenticated'], 401);
        }

        $booking = new BookingDto(
            id: null,
            user: $user,
            houseId: $data['houseId'],
            startDate: new DateTime($data['startDate']),
            endDate: new DateTime($data['endDate']),
            comment: $data['comment'] ?? null,
        );

        try {
            $bookingService->saveBooking($validator, $booking);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to save booking (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(['message' => 'booked successfully'], 201);
    }

    #[Route('/change/{bookingId}', name: 'change', methods: ['PUT'])]
    public function change(
        Request $request,
        int $bookingId,
        BookingService $bookingService,
        ValidatorInterface $validator,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['houseId'], $data['startDate'], $data['endDate'])) {
            return $this->json(['error' => 'missing required fields'], 400);
        }

        $user = $this->getUser();

        if (!$user || !$user instanceof \App\Entity\User) {
            return $this->json(['error' => 'user not authenticated'], 401);
        }

        $booking = new BookingDto(
            id: $bookingId,
            user: $user,
            houseId: $data['houseId'],
            startDate: new DateTime($data['startDate']),
            endDate: new DateTime($data['endDate']),
            comment: $data['comment'] ?? null,
        );

        try {
            $bookingService->changeBooking($validator, $booking);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to change booking (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(['message' => 'booking changed successfully'], 200);
    }

    #[Route('/delete/{bookingId}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $bookingId, BookingService $bookingService): JsonResponse
    {
        $user = $this->getUser();

        if (!$user || !$user instanceof \App\Entity\User) {
            return $this->json(['error' => 'user not authenticated'], 401);
        }

        try {
            $bookingService->deleteBooking($bookingId, $user);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to delete booking (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(['message' => 'deleted successfully'], 200);
    }
}
