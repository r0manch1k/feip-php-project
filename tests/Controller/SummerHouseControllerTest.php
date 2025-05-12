<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SummerHouseControllerTest extends WebTestCase
{
    public function testGetSummerHouses(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/summerhouse/list');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $this->assertResponseHeaderSame('content-type', 'application/json');

        $this->assertNotFalse($client->getResponse()->getContent());

        /**
         * @psalm-suppress PossiblyFalseArgument
         */
        $responseData = json_decode($client->getResponse()->getContent(), true);

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

        $this->assertNotFalse($payload, 'Failed to encode JSON payload.');

        $client->request(
            'POST',
            '/api/summerhouse/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        $this->assertNotFalse($client->getResponse()->getContent());

        /**
         * @psalm-suppress PossiblyFalseArgument
         */
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertNotEmpty($responseData);
    }
}
