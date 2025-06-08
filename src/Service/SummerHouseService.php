<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\SummerHouseDto;
use App\Entity\SummerHouse;
use App\Repository\SummerHouseRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SummerHouseService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SummerHouseRepository $summerHouseRepository,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @return SummerHouseDto[]
     */
    public function getSummerHouses(): array
    {
        /**
         * @var SummerHouse[] $summerHouses
         */
        $summerHouses = $this->summerHouseRepository->findAll();

        $summerHouses = array_map(
            fn (SummerHouse $summerHouse) => new SummerHouseDto(
                id: $summerHouse->getId(),
                address: $summerHouse->getAddress(),
                price: $summerHouse->getPrice(),
                bedrooms: $summerHouse->getBedrooms(),
                distanceFromSea: $summerHouse->getDistanceFromSea(),
                hasShower: $summerHouse->getHasShower(),
                hasBathroom: $summerHouse->getHasBathroom()
            ),
            $summerHouses
        );

        return $summerHouses;
    }

    public function getSummerHouseById(int $houseId): SummerHouseDto
    {
        $summerHouse = $this->summerHouseRepository->find($houseId);

        if (!$summerHouse) {
            throw new InvalidArgumentException('house not found (id: ' . $houseId . ')');
        }

        return new SummerHouseDto(
            id: $summerHouse->getId(),
            address: $summerHouse->getAddress(),
            price: $summerHouse->getPrice(),
            bedrooms: $summerHouse->getBedrooms(),
            distanceFromSea: $summerHouse->getDistanceFromSea(),
            hasShower: $summerHouse->getHasShower(),
            hasBathroom: $summerHouse->getHasBathroom()
        );
    }

    public function saveSummerHouse(SummerHouseDto $summerHouse): void
    {
        $existingHouse = $this->summerHouseRepository->findOneBy(['address' => $summerHouse->address]);

        if ($existingHouse) {
            throw new InvalidArgumentException('a house with this address already exists: ' . $summerHouse->address);
        }

        $newSummerhouse = new SummerHouse(
            id: null,
            address: $summerHouse->address,
            price: $summerHouse->price,
            bedrooms: $summerHouse->bedrooms,
            distanceFromSea: $summerHouse->distanceFromSea,
            hasShower: $summerHouse->hasShower,
            hasBathroom: $summerHouse->hasBathroom
        );

        $errors = $this->validator->validate($newSummerhouse);
        if (count($errors) > 0) {
            throw new InvalidArgumentException('validation failed: ' . (string) $errors);
        }

        $this->entityManager->persist($newSummerhouse);

        $this->entityManager->flush();
    }

    public function changeSummerHouse(SummerHouseDto $summerHouse): void
    {
        if (null === $summerHouse->id) {
            throw new InvalidArgumentException('house id is null');
        }

        $existingHouse = $this->summerHouseRepository->find($summerHouse->id);

        if (!$existingHouse) {
            throw new InvalidArgumentException('house not found (id: ' . $summerHouse->id . ')');
        }

        $existingHouse->setAddress($summerHouse->address);
        $existingHouse->setPrice($summerHouse->price);
        $existingHouse->setBedrooms($summerHouse->bedrooms);
        $existingHouse->setDistanceFromSea($summerHouse->distanceFromSea);
        $existingHouse->setHasShower($summerHouse->hasShower);
        $existingHouse->setHasBathroom($summerHouse->hasBathroom);

        $errors = $this->validator->validate($existingHouse);
        if (count($errors) > 0) {
            throw new InvalidArgumentException('validation failed: ' . (string) $errors);
        }

        $this->entityManager->flush();
    }

    public function deleteSummerHouse(int $houseId): void
    {
        // there will be permitions check

        /**
         * @ var SummerHouse|null $existingHouse
         */
        $summerHouse = $this->summerHouseRepository->find($houseId);

        if (!$summerHouse) {
            throw new InvalidArgumentException('house not found (id: ' . $houseId . ')');
        }

        $this->entityManager->remove($summerHouse);

        $this->entityManager->flush();
    }
}
