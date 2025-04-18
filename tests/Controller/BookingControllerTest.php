<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookingControllerTest extends WebTestCase
{
    public function testGetBookings(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/booking/list');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);

        $this->assertNotEmpty($responseData);

        if (!empty($responseData)) {
            $booking = $responseData[0];

            $this->assertArrayHasKey('id', $booking);
            $this->assertArrayHasKey('houseId', $booking);
            $this->assertArrayHasKey('comment', $booking);
        }
    }

    public function testCreateBooking(): void
    {
        // TODO: Implement using test db

        $client = static::createClient();

        $client->request('POST', '/api/booking/create', [], [], [], json_encode([
            'phoneNumber' => '123456789',
            'houseId' => 1,
            'comment' => 'Test comment',
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($responseData);
    }

    public function testChangeComment(): void
    {
        $client = static::createClient();

        $client->request('PUT', '/api/booking/change-comment/5', [], [], [], json_encode([
            'comment' => 'New comment',
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($responseData);
    }
}
