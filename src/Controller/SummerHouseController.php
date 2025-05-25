<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\SummerHouseDto;
use App\Service\SummerHouseService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/summerhouse', name: 'api_summerhouse')]
final class SummerHouseController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(Request $request, SummerHouseService $summerHouseService): JsonResponse
    {
        try {
            $summerHouses = $summerHouseService->getSummerHouses();
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to open file'], 500);
        }

        return $this->json($summerHouses);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(
        Request $request,
        SummerHouseService $summerHouseService,
        ValidatorInterface $validator,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['address'], $data['price'])) {
            return $this->json(['error' => 'missing required fields'], 400);
        }

        $summerHouse = new SummerHouseDto(
            id: null,
            address: $data['address'] ? $data['address'] : 'None',
            price: $data['price'] ? $data['price'] : 0,
            bedrooms: $data['bedrooms'] ?? null,
            distanceFromSea: $data['distanceFromSea'] ?? null,
            hasShower: $data['hasShower'] ?? null,
            hasBathroom: $data['hasBathroom'] ?? null
        );

        try {
            $summerHouseService->saveSummerHouse($validator, $summerHouse);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to save summer house (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(['message' => 'created successfully'], 201);
    }

    #[Route('/change/{houseId}', name: 'change', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function change(
        Request $request,
        int $houseId,
        SummerHouseService $summerHouseService,
        ValidatorInterface $validator,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['address'], $data['price'])) {
            return $this->json(['error' => 'missing required fields'], 400);
        }

        $summerHouse = new SummerHouseDto(
            id: $houseId,
            address: $data['address'] ? $data['address'] : 'None',
            price: $data['price'] ? $data['price'] : 0,
            bedrooms: $data['bedrooms'],
            distanceFromSea: $data['distanceFromSea'],
            hasShower: $data['hasShower'],
            hasBathroom: $data['hasBathroom']
        );

        try {
            $summerHouseService->changeSummerHouse($validator, $summerHouse);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to update summer house (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(['message' => 'updated successfully'], 200);
    }

    #[Route('/delete/{houseId}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Request $request,
        int $houseId,
        SummerHouseService $summerHouseService,
    ): JsonResponse {
        try {
            $summerHouseService->deleteSummerHouse($houseId);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to delete summer house (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(['message' => 'deleted successfully'], 200);
    }
}
