<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\AmdocsByPhoneProvider;

#[ApiResource(
    shortName: 'AmdocsByPhone',
    operations: [
        new Get(
            uriTemplate: '/telefono/{telefono}',
            requirements: ['telefono' => '\\d{7,15}'],
            provider: AmdocsByPhoneProvider::class,
        ),
    ],
    paginationEnabled: false,
)]
class AmdocsByPhoneResource
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public string $telefono,
        public array $attributes = [],
    ) {}
}
