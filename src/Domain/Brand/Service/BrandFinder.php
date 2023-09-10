<?php

namespace App\Domain\Brand\Service;

use App\Domain\Brand\Data\BrandFinderItem;
use App\Domain\Brand\Data\BrandFinderResult;
use App\Domain\Brand\Repository\BrandFinderRepository;

final class BrandFinder
{
    private BrandFinderRepository $repository;

    public function __construct(BrandFinderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function findBrands(): BrandFinderResult
    {
        // Input validation
        // ...

        $brands = $this->repository->findBrands();

        return $this->createResult($brands);
    }

    private function createResult(array $brandRows): BrandFinderResult
    {
        $result = new BrandFinderResult();

        foreach ($brandRows as $brandRow) {
            $brand = new BrandFinderItem();
            $brand->id = $brandRow['id'];
            $brand->name = $brandRow['name'];

            $result->brands[] = $brand;
        }

        return $result;
    }
}
