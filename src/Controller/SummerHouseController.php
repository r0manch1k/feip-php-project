<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Dto\SummerHouseDto;
use App\Service\SummerHouseService;

#[Route('/api/summerhouse', name: 'api_summerhouse_')]
final class SummerHouseController extends AbstractController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(Request $request, SummerHouseService $summerHouseService): JsonResponse
    {
        $summerHouses = $summerHouseService->getSummerHouses();

        if ($summerHouses === false) {
            return $this->json(['error' => 'Failed to open file'], 500);
        }

        return $this->json($summerHouses);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request, SummerHouseService $summerHouseService): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        $summerHouse = new SummerHouseDto(
            id: -1,
            address: $data['address'] ? $data['address'] : 'None',
            price: $data['price'] ? $data['price'] : 0,
            bedrooms: $data['bedrooms'] ? $data['bedrooms'] : 0,
            distanceFromSea: $data['distanceFromSea'] ? $data['distanceFromSea'] : 0,
            hasShower: $data['hasShower'] ? $data['hasShower'] : false,
            hasBathroom: $data['hasBathroom'] ? $data['hasBathroom'] : false
        );

        if ($summerHouseService->saveSummerHouses([$summerHouse]) === false) {
            return $this->json(['error' => 'Failed to save summerHouse'], 500);
        }

        return $this->json(['message' => 'Booked successfully'], 201);
    }
}
