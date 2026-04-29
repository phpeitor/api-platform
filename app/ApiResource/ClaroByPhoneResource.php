<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\ClaroByPhoneProvider;

#[ApiResource(
    shortName: 'ClaroByPhone',
    operations: [
        new Get(
            uriTemplate: '/claro/telefono/{telefono}',
            requirements: ['telefono' => '\\d{7,15}'],
            provider: ClaroByPhoneProvider::class,
        ),
    ],
    paginationEnabled: false,
)]
class ClaroByPhoneResource
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public string $telefono,
        public array $attributes = [],
    ) {}
}
