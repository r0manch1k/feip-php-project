<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function testRegister(): void
    {
        $this->assertNotNull($this->client, 'client is null.');

        $payload = json_encode([
            'phoneNumber' => '+76665554433',
            'password' => 'poE@mTqPY9k4L9fC',
        ]);

        $this->assertNotFalse($payload, 'failed to encode json payload.');

        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        $this->assertNotFalse($this->client->getResponse()->getContent());

        $content = $this->client->getResponse()->getContent();

        $this->assertNotFalse($content);

        $this->assertJson($content);

        $responseData = json_decode($content, true);

        $this->assertArrayHasKey('token', $responseData);

        $this->assertNotEmpty($responseData['token'], 'Token is empty.');
    }

    public function testLogin(): void
    {
        $this->assertNotNull($this->client, 'client is null.');

        $payload = json_encode([
            'phoneNumber' => '+76665554433',
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

        $this->assertNotEmpty($responseData['token'], 'Token is empty.');
    }
}
