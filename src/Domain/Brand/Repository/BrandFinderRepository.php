<?php

namespace App\Domain\Brand\Repository;

use App\Factory\QueryFactory;

final class BrandFinderRepository
{
    private QueryFactory $queryFactory;

    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    public function findBrands(): array
    {
        $query = $this->queryFactory->newSelect('brands');

        $query->select(
            [
                'id',
                'name',
            ]
        );

        // Add more "use case specific" conditions to the query
        // ...

        return $query->execute()->fetchAll('assoc') ?: [];
    }
}
