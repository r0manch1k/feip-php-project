<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookingControllerTest extends WebTestCase
{
    private string $jwtToken = '';
    private ?KernelBrowser $client = null;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        /**
         * @see \App\DataFixtures\UserFixtures
         */
        $payload = json_encode([
            'phoneNumber' => '+79990000001',
            'password' => 'poE@mTqPY9k4L9fC',
        ]);

        $this->assertNotFalse($payload, 'failed to encode json payload.');

        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $this->assertNotFalse($this->client->getResponse()->getContent());

        $content = $this->client->getResponse()->getContent();

        $this->assertNotFalse($content);

        $this->assertJson($content);

        $responseData = json_decode($content, true);

        $this->assertArrayHasKey('token', $responseData);

        $this->assertNotEmpty($responseData['token'], 'token is empty.');

        $this->jwtToken = $responseData['token'];
    }

    public function testGetBookings(): void
    {
        $this->assertNotNull($this->client, 'client is null.');

        $this->client->request('GET', '/api/booking/list');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $this->assertResponseHeaderSame('content-type', 'application/json');

        $content = $this->client->getResponse()->getContent();

        $this->assertNotFalse($content);

        $responseData = json_decode($content, true);

        $this->assertIsArray($responseData);

        $this->assertNotEmpty($responseData);

        $this->assertIsArray($responseData[0]);

        $booking = $responseData[0];

        $this->assertArrayHasKey('id', $booking);
        $this->assertArrayHasKey('user', $booking);
        $this->assertArrayHasKey('houseId', $booking);
        $this->assertArrayHasKey('startDate', $booking);
        $this->assertArrayHasKey('endDate', $booking);
        $this->assertArrayHasKey('comment', $booking);
    }

    public function testCreateBooking(): void
    {
        $payload = json_encode([
            'houseId' => 11,
            'comment' => '',
            'startDate' => '2027-10-01 00:00:00',
            'endDate' => '2028-10-10 00:00:00',
        ]);

        $this->assertNotFalse($payload, 'failed to encode json payload.');

        $this->assertNotNull($this->client, 'client is null.');

        $this->client->request(
            'POST',
            '/api/booking/create',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_Authorization' => 'Bearer ' . $this->jwtToken,
            ],
            $payload
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        $this->assertNotFalse($this->client->getResponse()->getContent());

        $content = $this->client->getResponse()->getContent();

        $this->assertNotFalse($content);

        $responseData = json_decode($content, true);

        $this->assertNotEmpty($responseData);
    }

    public function testChangeBooking(): void
    {
        $this->assertNotNull($this->client, 'client is null.');

        $payload = json_encode([
            'houseId' => 12,
            'startDate' => '2026-10-01 00:00:00',
            'endDate' => '2026-10-10 00:00:00',
            'comment' => 'Hurry up!',
        ]);

        $this->assertNotFalse($payload, 'failed to encode json payload.');

        $this->client->request(
            'PUT',
            '/api/booking/change/5',
            [],
            [],
            [
                'HTTP_Authorization' => 'Bearer ' . $this->jwtToken,
            ],
            $payload
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $this->assertNotFalse($this->client->getResponse()->getContent());

        $content = $this->client->getResponse()->getContent();

        $this->assertNotFalse($content);

        $responseData = json_decode($content, true);

        $this->assertNotEmpty($responseData);
    }
}
