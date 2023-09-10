<?php

namespace App\Domain\Brand\Service;

use App\Domain\Brand\Data\BrandReaderResult;
use App\Domain\Brand\Repository\BrandRepository;

/**
 * Service.
 */
final class BrandReader
{
    private BrandRepository $repository;

    /**
     * The constructor.
     *
     * @param BrandRepository $repository The repository
     */
    public function __construct(BrandRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Read a brand.
     *
     * @param int $brandId The brand id
     *
     * @return BrandReaderResult The result
     */
    public function getBrand(int $brandId): BrandReaderResult
    {
        // Input validation
        // ...

        // Fetch data from the database
        $brandRow = $this->repository->getBrandById($brandId);

        // Optional: Add or invoke your complex business logic here
        // ...

        // Create domain result
        $result = new BrandReaderResult();
        $result->id = $brandRow['id'];
        $result->name = $brandRow['name'];

        return $result;
    }
}
