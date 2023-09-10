<?php

namespace App\Domain\Customer\Service;

use App\Domain\Customer\Repository\CustomerRepository;
use App\Support\Validation\ValidationException;
use Cake\Validation\Validator;
use DomainException;

final class CustomerValidator
{
    private CustomerRepository $repository;

    public function __construct(CustomerRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validateCustomerUpdate(int $brandId, array $data): void
    {
        if (!$this->repository->existsCustomerId($brandId)) {
            throw new DomainException(sprintf('Customer not found: %s', $brandId));
        }

        $this->validateCustomer($data);
    }

    public function validateCustomer(array $data): void
    {
        $validator = new Validator();
        $validator
            ->requirePresence('name', true, 'Input required')
            ->notEmptyString('name', 'Input required')
            ->maxLength('name', 255, 'Too long');

        $errors = $validator->validate($data);

        if ($errors) {
            throw new ValidationException('Please check your input', $errors);
        }
    }
}
