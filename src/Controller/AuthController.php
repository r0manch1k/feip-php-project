<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Auth\LoginDto;
use App\Dto\Auth\TokenDto;
use App\Dto\UserDto;
use App\Entity\User;
use App\Service\AuthService;
use DateTimeImmutable;
use Exception;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AuthController extends AbstractController
{
    public function __construct(
        private readonly RefreshTokenManagerInterface $refreshTokenManager,
        private readonly JWTTokenManagerInterface $JWTManager,
        private readonly AuthService $authService,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * Registers a new user.
     */
    #[OA\Tag(name: 'Auth')]
    #[Security(name: null)]
    #[OA\Response(
        response: 200,
        description: 'Access token and refresh token',
        content: new OA\JsonContent(ref: new Model(type: TokenDto::class))
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            ref: new Model(type: LoginDto::class)
        )
    )]
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['phoneNumber'], $data['password'])) {
            return $this->json(['error' => 'missing required fields'], 400);
        }

        $user = new UserDto(
            id: null,
            phoneNumber: $data['phoneNumber'],
            roles: [],
            password: $data['password'],
        );

        try {
            $user = $this->authService->saveUser($user);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to save user (error: ' . $e->getMessage() . ')'], 500);
        }

        try {
            $refreshToken = new RefreshToken();
            $refreshToken->setRefreshToken(bin2hex(random_bytes(64)));
            $refreshToken->setUsername($user->getUserIdentifier());
            $refreshToken->setValid((new DateTimeImmutable())->modify('+1 month'));
            $this->refreshTokenManager->save($refreshToken);

            return $this->json([
                'token' => $this->JWTManager->create($user),
                'refreshToken' => $refreshToken->getRefreshToken(),
            ], 200);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to create token (error: ' . $e->getMessage() . ')'], 500);
        }
    }

    /**
     * Returns profile.
     */
    #[OA\Tag(name: 'Auth')]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(ref: new Model(type: UserDto::class, groups: ['public']))
    )]
    #[Route('/api/profile', name: 'api_profile', methods: ['GET'])]
    public function profile(): JsonResponse
    {
        /**
         * @var User|null $user
         */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'not authenticated'], 401);
        }

        $response = new UserDto(
            id: $user->getId(),
            phoneNumber: $user->getPhoneNumber() ?? '',
            roles: $user->getRoles(),
            password: '',
        );

        return $this->json($response, 200, [], ['groups' => ['public']]);
    }

    /**
     *  Deletes refresh token.
     */
    #[OA\Tag(name: 'Auth')]
    #[Security(name: null)]
    #[OA\Response(
        response: 200,
        description: 'Success message',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'success'),
            ]
        )
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(ref: new Model(type: TokenDto::class, groups: ['refresh']))
    )]
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['refreshToken'])) {
            return $this->json(['error' => 'missing required fields'], 400);
        }

        try {
            $refreshToken = $this->refreshTokenManager->get($data['refreshToken']);
            if (!$refreshToken) {
                return $this->json(['error' => 'invalid refresh token'], 400);
            }
            $this->refreshTokenManager->delete($refreshToken);
        } catch (Exception $e) {
            return $this->json(['error' => 'failed to delete token (error: ' . $e->getMessage() . ')'], 500);
        }

        return $this->json(['message' => 'success'], 200);
    }
}
