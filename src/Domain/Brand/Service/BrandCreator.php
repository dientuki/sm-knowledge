<?php

namespace App\Domain\Brand\Service;

use App\Domain\Brand\Repository\BrandRepository;
use App\Factory\LoggerFactory;
use Psr\Log\LoggerInterface;

final class BrandCreator
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
            ->addFileHandler('brand_creator.log')
            ->createLogger();
    }

    public function createBrand(array $data): int
    {
        // Input validation
        $this->brandValidator->validateBrand($data);

        // Insert brand and get new brand ID
        $brandId = $this->repository->insertBrand($data);

        // Logging
        $this->logger->info(sprintf('Brand created successfully: %s', $brandId));

        return $brandId;
    }
}
