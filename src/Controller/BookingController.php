<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\BookingDto;
use App\Dto\UserDto;
use App\Service\BookingService;
use DateTime;
use Exception;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/booking', name: 'api_booking')]
final class BookingController extends AbstractController
{
    public function __construct(
        private BookingService $bookingService,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * Retrieves a list of all bookings.
     *
     * Returns a list of bookings for the authenticated user.
     * If the user is an admin, it returns all bookings.
     */
    #[OA\Tag(name: 'Booking')]
    #[OA\Response(
        response: 200,
        description: 'The list of bookings',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: BookingDto::class)))
    )]
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user || !$user instanceof \App\Entity\User) {
            return $this->json(['error' => 'user not authenticated'], 401);
        }

        try {
            if ($this->isGranted('ROLE_ADMIN')) {
                $bookings = $this->bookingService->getBookings();
            } else {
                $userDto = new UserDto(
                    id: $user->getId(),
                    phoneNumber: $user->getPhoneNumber(),
                    password: '',
                    roles: $user->getRoles()
                );
                $bookings = $this->bookingService->getBookings($userDto);
            }
        } catch (Exception $e) {
            return $this->json(['error' => 'falied to get bookings (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(array_map(fn ($b) => $b->toArray(), $bookings), 200);
    }

    /**
     * Creates a new booking.
     */
    #[OA\Tag(name: 'Booking')]
    #[OA\Response(
        response: 200,
        description: 'Success message',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'message', type: 'string', example: 'success'),
        ])
    )]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: new Model(type: BookingDto::class)))]
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {

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
            telegramBotUser: null,
            houseId: $data['houseId'],
            startDate: new DateTime($data['startDate']),
            endDate: new DateTime($data['endDate']),
            comment: $data['comment'] ?? null,
        );

        try {
            $this->bookingService->saveBooking($booking);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to save booking (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(['message' => 'success'], 200);
    }

    /**
     * Changes an existing booking.
     */
    #[OA\Tag(name: 'Booking')]
    #[OA\Parameter(
        name: 'bookingId',
        in: 'path',
        required: true,
        description: 'ID of the booking to change',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Success message',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'message', type: 'string', example: 'success'),
        ])
    )]
    #[OA\RequestBody(content: new OA\JsonContent(ref: new Model(type: BookingDto::class)))]
    #[Route('/change/{bookingId}', name: 'change', methods: ['PUT'])]
    public function change(Request $request, int $bookingId): JsonResponse
    {
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
            telegramBotUser: null,
            houseId: $data['houseId'],
            startDate: new DateTime($data['startDate']),
            endDate: new DateTime($data['endDate']),
            comment: $data['comment'] ?? null,
        );

        try {
            $this->bookingService->changeBooking($booking);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to change booking (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(['message' => 'success'], 200);
    }

    /**
     * Deletes a booking.
     */
    #[OA\Tag(name: 'Booking')]
    #[OA\Parameter(
        name: 'bookingId',
        in: 'path',
        required: true,
        description: 'ID of the booking to delete',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Success message',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'message', type: 'string', example: 'success'),
        ])
    )]
    #[Route('/delete/{bookingId}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $bookingId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user || !$user instanceof \App\Entity\User) {
            return $this->json(['error' => 'user not authenticated'], 401);
        }

        try {
            $this->bookingService->deleteBooking($bookingId, $user);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to delete booking (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(['message' => 'success'], 200);
    }
}
