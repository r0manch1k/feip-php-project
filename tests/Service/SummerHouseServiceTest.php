<?php

namespace App\Tests\Service;

use App\Dto\SummerHouseDto;
use App\Service\SummerHouseService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\ORM\EntityManagerInterface;

class SummerHouseServiceTest extends KernelTestCase
{
    public function testGetSummerHouses(): void
    {
        /**
         * @var KernelInterface $kernel
         */
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        /**
         * @var Container $container
         */
        $container = static::getContainer();

        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get('doctrine')->getManager();

        /**
         * @var SummerHouseService $summerHouseService
         */
        $summerHouseService = $container->get(SummerHouseService::class);

        try {
            $summerHouses = $summerHouseService->getSummerHouses();
            $this->assertIsArray($summerHouses);
            $this->assertNotEmpty($summerHouses);
            $this->assertInstanceOf(SummerHouseDto::class, $summerHouses[0]);
        } catch (\Exception $e) {
            $this->fail('failed to get summer houses: ' . $e->getMessage());
        }
    }

    public function testSaveSummerHouse(): void
    {
        /**
         * @var KernelInterface $kernel
         */
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        /**
         * @var Container $container
         */
        $container = static::getContainer();

        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get('doctrine')->getManager();

        /**
         * @var SummerHouseService $summerHouseService
         */
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

            $summerHouseService->saveSummerHouse($container->get('validator'), $summerHouse);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('failed to save summer house: ' . $e->getMessage());
        }
    }
}
