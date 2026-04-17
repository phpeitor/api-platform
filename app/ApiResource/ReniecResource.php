<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\ReniecProvider;

#[ApiResource(
    shortName: 'Reniec',
    operations: [
        new Get(
            uriTemplate: '/reniec/{dni}',
            requirements: ['dni' => '\\d{8}'],
            provider: ReniecProvider::class,
        ),
    ],
    paginationEnabled: false,
)]
class ReniecResource
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public string $dni,
        public array $attributes = [],
    ) {}
}
