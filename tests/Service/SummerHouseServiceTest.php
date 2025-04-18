<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use App\Dto\SummerHouseDto;

use App\Service\SummerHouseService;

class SummerHouseServiceTest extends KernelTestCase
{
    public function testGetSummerHouses(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        /**
         * @var string $testCsvFile
         */
        $testCsvFile =  '/tests/csv/summerhouses_1.csv';

        $summerHouseService = new SummerHouseService($kernel->getProjectDir(), $testCsvFile);

        try {
            /**
             * @var SummerHouseDto[] $summerHouses
             */
            $summerHouses = $summerHouseService->getSummerHouses();
        } catch (\Exception $e) {
            $this->fail('Failed to get summer houses: ' . $e->getMessage());
        }

        $this->assertIsArray($summerHouses);

        for ($i = 0; $i < count($summerHouses); $i++) {
            $this->assertInstanceOf(SummerHouseDto::class, $summerHouses[$i]);
        }
    }

    public function testSaveSummerHouses(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        /**
         * @var string $testCsvFile
         */
        $testCsvFile = '/tests/csv/summerhouses_1.csv';

        $summerHouseService = new SummerHouseService($kernel->getProjectDir(), $testCsvFile);

        /**
         * @var SummerHouseDto[] $newSummerHouses
         */
        $newSummerHouses = [
            new SummerHouseDto(
                id: -1,
                address: 'Test address 1',
                price: 100,
                bedrooms: 2,
                distanceFromSea: 100,
                hasShower: true,
                hasBathroom: true
            ),
            new SummerHouseDto(
                id: -1,
                address: 'Test address 2',
                price: 200,
                bedrooms: 3,
                distanceFromSea: 200,
                hasShower: false,
                hasBathroom: true
            )
        ];
        try {
            $summerHouseService->saveSummerHouses($newSummerHouses, true);
        } catch (\Exception $e) {
            $this->fail('Failed to get summer houses: ' . $e->getMessage());
        }

        try {
            /**
             * @var SummerHouseDto[] $summerHouses
             */
            $summerHouses = $summerHouseService->getSummerHouses();
        } catch (\Exception $e) {
            $this->fail('Failed to get summer houses: ' . $e->getMessage());
        }

        $this->assertCount(count($newSummerHouses), $summerHouses);
    }
}
