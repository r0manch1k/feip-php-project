<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SummerHouseControllerTest extends WebTestCase
{
    private function loginDefaultUser(KernelBrowser $client): string
    {
        /**
         * @see \App\DataFixtures\UserFixtures
         */
        $payload = json_encode([
            'phoneNumber' => '+79990000000',
            'password' => 'poE@mTqPY9k4L9fC',
        ]);

        $this->assertNotFalse($payload, 'failed to encode json payload.');

        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $this->assertNotFalse($client->getResponse()->getContent());

        $content = $client->getResponse()->getContent();

        $this->assertNotFalse($content);

        $this->assertJson($content);

        $responseData = json_decode($content, true);

        $this->assertArrayHasKey('token', $responseData);

        $this->assertNotEmpty($responseData['token'], 'token is empty.');

        /**
         * @var string $jwtToken
         */
        $jwtToken = $responseData['token'];

        return $jwtToken;
    }

    public function testGetSummerHouses(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/summerhouse/list');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $this->assertResponseHeaderSame('content-type', 'application/json');

        $this->assertNotFalse($client->getResponse()->getContent());

        $content = $client->getResponse()->getContent();

        $this->assertNotFalse($content);

        $responseData = json_decode($content, true);

        $this->assertIsArray($responseData);

        $this->assertNotEmpty($responseData);

        $this->assertIsArray($responseData[0]);

        $summerHouse = $responseData[0];

        $this->assertArrayHasKey('id', $summerHouse);
        $this->assertArrayHasKey('address', $summerHouse);
        $this->assertArrayHasKey('price', $summerHouse);
        $this->assertArrayHasKey('bedrooms', $summerHouse);
        $this->assertArrayHasKey('distanceFromSea', $summerHouse);
        $this->assertArrayHasKey('hasShower', $summerHouse);
        $this->assertArrayHasKey('hasBathroom', $summerHouse);
    }

    public function testCreateSummerHouse(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'address' => '123 Main St, Springfield, AI 62704',
            'price' => 100,
            'bedrooms' => 2,
            'distanceFromSea' => 50,
            'hasShower' => true,
            'hasBathroom' => true,
        ]);

        $this->assertNotFalse($payload, 'failed to encode json payload.');

        $jwtToken = $this->loginDefaultUser($client);

        $client->request(
            'POST',
            '/api/summerhouse/create',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_Authorization' => 'Bearer ' . $jwtToken,
            ],
            $payload
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        $this->assertNotFalse($client->getResponse()->getContent());

        $content = $client->getResponse()->getContent();

        $this->assertNotFalse($content);

        $responseData = json_decode($content, true);

        $this->assertNotEmpty($responseData);
    }
}
