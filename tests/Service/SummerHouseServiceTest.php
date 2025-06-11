<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Dto\SummerHouseDto;
use App\Service\SummerHouseService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SummerHouseServiceTest extends KernelTestCase
{
    public function testGetSummerHouses(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $container = static::getContainer();

        $container->get('doctrine')->getManager();

        $summerHouseService = $container->get(SummerHouseService::class);

        try {
            $summerHouses = $summerHouseService->getSummerHouses();
            $this->assertNotEmpty($summerHouses);
            $this->assertInstanceOf(SummerHouseDto::class, $summerHouses[0]);
        } catch (Exception $e) {
            $this->fail('failed to get summer houses: ' . $e->getMessage());
        }
    }

    public function testSaveSummerHouse(): void
    {

        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $container = static::getContainer();

        $container->get('doctrine')->getManager();

        $summerHouseService = $container->get(SummerHouseService::class);

        try {
            $summerHouse = new SummerHouseDto(
                id: null,
                address: '123 Main St, Springfield, IL 62704',
                price: 100,
                bedrooms: 2,
                distanceFromSea: 100,
                hasShower: true,
                hasBathroom: true
            );

            $summerHouseService->saveSummerHouse($summerHouse);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail('failed to save summer house: ' . $e->getMessage());
        }
    }
}
