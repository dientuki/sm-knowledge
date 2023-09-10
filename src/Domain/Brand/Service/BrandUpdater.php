<?php

namespace App\Domain\Brand\Service;

use App\Domain\Brand\Repository\BrandRepository;
use App\Factory\LoggerFactory;
use Psr\Log\LoggerInterface;

final class BrandUpdater
{
    private BrandRepository $repository;

    private BrandValidator $brandValidator;

    private LoggerInterface $logger;

    public function __construct(
        BrandRepository $repository,
        BrandValidator $brandValidator,
        LoggerFactory $loggerFactory
    ) {
        $this->repository = $repository;
        $this->brandValidator = $brandValidator;
        $this->logger = $loggerFactory
            ->addFileHandler('brand_updater.log')
            ->createLogger();
    }

    public function updateBrand(int $brandId, array $data): void
    {
        // Input validation
        $this->brandValidator->validateBrandUpdate($brandId, $data);

        // Update the row
        $this->repository->updateBrand($brandId, $data);

        // Logging
        $this->logger->info(sprintf('Brand updated successfully: %s', $brandId));
    }
}
