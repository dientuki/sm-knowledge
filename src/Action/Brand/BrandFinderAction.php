<?php

namespace App\Action\Brand;

use App\Domain\Brand\Data\BrandFinderResult;
use App\Domain\Brand\Service\BrandFinder;
use App\Renderer\JsonRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class BrandFinderAction
{
    private BrandFinder $brandFinder;

    private JsonRenderer $renderer;

    public function __construct(BrandFinder $brandFinder, JsonRenderer $jsonRenderer)
    {
        $this->brandFinder = $brandFinder;
        $this->renderer = $jsonRenderer;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Optional: Pass parameters from the request to the service method
        // ...

        $brands = $this->brandFinder->findBrands();

        // Transform result and render to json
        return $this->renderer->json($response, $this->transform($brands));
    }

    public function transform(BrandFinderResult $result): array
    {
        $brands = [];

        foreach ($result->brands as $brand) {
            $brands[] = [
                'id' => $brand->id,
                'name' => $brand->name,
            ];
        }

        return [
            'brands' => $brands,
        ];
    }
}
