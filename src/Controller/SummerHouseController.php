<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\SummerHouseDto;
use App\Service\SummerHouseService;
use Exception;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/summerhouse', name: 'api_summerhouse')]
final class SummerHouseController extends AbstractController
{
    public function __construct(
        private SummerHouseService $summerHouseService,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * Retrieves a list of all summer houses.
     */
    #[OA\Tag(name: 'Summer House')]
    #[Security(name: null)]
    #[OA\Response(
        response: 200,
        description: 'The list of summer houses',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: SummerHouseDto::class)))
    )]
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $summerHouses = $this->summerHouseService->getSummerHouses();
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to retrieve summer houses (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json($summerHouses);
    }

    /**
     * Creates a new summer house.
     *
     * Requires admin privileges.
     */
    #[OA\Tag(name: 'Summer House')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: SummerHouseDto::class))
    )]
    #[OA\Response(
        response: 200,
        description: 'Success message',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'message', type: 'string', example: 'success'),
        ])
    )]
    #[Route('/create', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
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
            $this->summerHouseService->saveSummerHouse($summerHouse);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to save summer house (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(['message' => 'success'], 200);
    }

    /**
     * Updates an existing summer house.
     *
     * Requires admin privileges.
     */
    #[OA\Tag(name: 'Summer House')]
    #[OA\Parameter(
        name: 'houseId',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: SummerHouseDto::class))
    )]
    #[OA\Response(
        response: 200,
        description: 'Success message',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'message', type: 'string', example: 'success'),
        ])
    )]
    #[Route('/change/{houseId}', name: 'change', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function change(Request $request, int $houseId): JsonResponse
    {
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
            $this->summerHouseService->changeSummerHouse($summerHouse);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to update summer house (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(['message' => 'success'], 200);
    }

    /**
     * Deletes a summer house.
     *
     * Requires admin privileges.
     */
    #[OA\Tag(name: 'Summer House')]
    #[OA\Parameter(
        name: 'houseId',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Success message',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'message', type: 'string', example: 'success'),
        ])
    )]
    #[Route('/delete/{houseId}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, int $houseId): JsonResponse
    {
        try {
            $this->summerHouseService->deleteSummerHouse($houseId);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to delete summer house (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(['message' => 'success'], 200);
    }
}
