<?php

namespace App\Domain\Brand\Service;

use App\Domain\Brand\Repository\BrandRepository;

final class BrandDeleter
{
    private BrandRepository $repository;

    public function __construct(BrandRepository $repository)
    {
        $this->repository = $repository;
    }

    public function deleteBrand(int $brandId): void
    {
        // Input validation
        // ...

        $this->repository->deleteBrandById($brandId);
    }
}
