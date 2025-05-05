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
            $this->assertArrayHasKey('phoneNumber', $booking);
            $this->assertArrayHasKey('houseId', $booking);
            $this->assertArrayHasKey('startDate', $booking);
            $this->assertArrayHasKey('endDate', $booking);
            $this->assertArrayHasKey('comment', $booking);
        }
    }

    public function testCreateBooking(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/booking/create', [], [], [], json_encode([
            'phoneNumber' => '+37061234567',
            'houseId' => 11,
            'comment' => '',
            'startDate' => '2027-10-01 00:00:00',
            'endDate' => '2028-10-10 00:00:00',
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($responseData);
    }

    public function testChangeBooking(): void
    {
        $client = static::createClient();

        $client->request('PUT', '/api/booking/change/5', [], [], [], json_encode([
            'phoneNumber' => '+37061234567',
            'houseId' => 12,
            'startDate' => '2026-10-01 00:00:00',
            'endDate' => '2026-10-10 00:00:00',
            'comment' => 'Hurry up!',
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($responseData);
    }
}
