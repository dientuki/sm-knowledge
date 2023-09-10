<?php

namespace App\Domain\Brand\Repository;

use App\Factory\QueryFactory;
use DomainException;

final class BrandRepository
{
    private QueryFactory $queryFactory;

    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    public function insertBrand(array $brand): int
    {
        return (int)$this->queryFactory->newInsert('brands', $this->toRow($brand))
            ->execute()
            ->lastInsertId();
    }

    public function getBrandById(int $brandId): array
    {
        $query = $this->queryFactory->newSelect('brands');
        $query->select(
            [
                'id',
                'name',
            ]
        );

        $query->where(['id' => $brandId]);

        $row = $query->execute()->fetch('assoc');

        if (!$row) {
            throw new DomainException(sprintf('Brand not found: %s', $brandId));
        }

        return $row;
    }

    public function updateBrand(int $brandId, array $brand): void
    {
        $row = $this->toRow($brand);

        $this->queryFactory->newUpdate('brands', $row)
            ->where(['id' => $brandId])
            ->execute();
    }

    public function existsBrandId(int $brandId): bool
    {
        $query = $this->queryFactory->newSelect('brands');
        $query->select('id')->where(['id' => $brandId]);

        return (bool)$query->execute()->fetch('assoc');
    }

    public function deleteBrandById(int $brandId): void
    {
        $this->queryFactory->newDelete('brands')
            ->where(['id' => $brandId])
            ->execute();
    }

    private function toRow(array $brand): array
    {
        return [
            'name' => $brand['name'],
        ];
    }
}
