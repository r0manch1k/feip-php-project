<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Booking;
use App\Entity\SummerHouse;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Override;

class BookingDataFixtures extends Fixture implements DependentFixtureInterface
{
    #[Override]
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        /**
         * @var SummerHouse[] $summerHouses
         */
        $summerHouses = [];

        for ($i = 0; $i < 20; ++$i) {
            $summerHouse = new SummerHouse(
                id: $i,
                address: $this->generateValidAddress($faker),
                price: (int) $faker->randomFloat(2, 50, 500),
                bedrooms: $faker->numberBetween(1, 5),
                distanceFromSea: $faker->numberBetween(1, 100),
                hasShower: $faker->boolean,
                hasBathroom: $faker->boolean,
            );

            $summerHouses[] = $summerHouse;
            $manager->persist($summerHouse);
        }

        for ($i = 0; $i < 20; ++$i) {
            /**
             * @var User $user
             */
            $user = $manager->getRepository(User::class)->findOneBy(['phoneNumber' => '+79990000001']);

            $booking = new Booking(
                id: $i,
                user: $user,
                house: $summerHouses[$i],
                startDate: $faker->dateTimeBetween('-1 year', '+1 year'),
                endDate: $faker->dateTimeBetween('+1 year', '+2 years'),
            );

            $manager->persist($booking);
        }

        $manager->flush();
    }

    public function generateValidAddress(Generator $faker): string
    {
        $buildingNumber = $faker->buildingNumber();
        $streetName = $faker->streetName();
        $city = $faker->city();
        /**
         * @psalm-suppress UndefinedMagicMethod
         */
        $stateAbbr = $faker->stateAbbr();
        $postcode = $faker->numerify('#####');

        /**
         * @var string $address
         */
        $address = "$buildingNumber $streetName, $city, $stateAbbr $postcode";

        return $address;
    }

    #[Override]
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
