<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testRegister(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'phoneNumber' => '+76665554433',
            'password' => 'poE@mTqPY9k4L9fC',
        ]);

        $this->assertNotFalse($payload, 'failed to encode json payload.');

        $client->request('POST', '/api/register', [], [], [
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
    }

    public function testLogin(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'phoneNumber' => '+76665554433',
            'password' => 'poE@mTqPY9k4L9fC',
        ]);

        $this->assertNotFalse($payload, 'failed to encode json payload.');

        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertResponseIsSuccessful();

        $this->assertNotFalse($client->getResponse()->getContent());

        $content = $client->getResponse()->getContent();

        $this->assertNotFalse($content);

        $this->assertJson($content);

        $responseData = json_decode($content, true);

        $this->assertArrayHasKey('token', $responseData);

        $this->assertArrayHasKey('refreshToken', $responseData);

        $this->assertNotEmpty($responseData['token'], 'token is empty.');
    }

    public function testLogout(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'phoneNumber' => '+76665554433',
            'password' => 'poE@mTqPY9k4L9fC',
        ]);

        $this->assertNotFalse($payload, 'failed to encode json payload.');

        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertResponseIsSuccessful();

        $this->assertNotFalse($client->getResponse()->getContent());

        $content = $client->getResponse()->getContent();

        $this->assertNotFalse($content);

        $this->assertJson($content);

        $responseData = json_decode($content, true);

        $payload = json_encode([
            'refreshToken' => $responseData['refreshToken'],
        ]);

        $this->assertNotFalse($payload, 'failed to encode json payload.');

        $client->request('POST', '/api/token/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertResponseIsSuccessful();

        $this->assertNotFalse($client->getResponse()->getContent());

        $content = $client->getResponse()->getContent();

        $this->assertNotFalse($content);

        $this->assertJson($content);

        $responseData = json_decode($content, true);

        $payload = json_encode([
            'refreshToken' => $responseData['refreshToken'],
        ]);

        $this->assertNotFalse($payload, 'failed to encode json payload.');

        $client->request('POST', '/api/logout', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertResponseIsSuccessful();

        $this->assertNotFalse($client->getResponse()->getContent());

        $client->request('POST', '/api/token/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertResponseStatusCodeSame(401);
    }
}
