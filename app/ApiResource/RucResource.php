<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\RucProvider;

#[ApiResource(
    shortName: 'Ruc',
    operations: [
        new Get(
            uriTemplate: '/ruc/{ruc}',
            requirements: ['ruc' => '[0-9]+'],
            provider: RucProvider::class,
        ),
    ],
    paginationEnabled: false,
)]
class RucResource
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public string $ruc,
        public array $attributes = [],
    ) {}
}
