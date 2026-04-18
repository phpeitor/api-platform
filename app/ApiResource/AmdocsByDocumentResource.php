<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\AmdocsByDocumentProvider;

#[ApiResource(
    shortName: 'AmdocsByDocument',
    operations: [
        new Get(
            uriTemplate: '/document/{document}',
            requirements: ['document' => '[A-Za-z0-9]+' ],
            provider: AmdocsByDocumentProvider::class,
        ),
    ],
    paginationEnabled: false,
)]
class AmdocsByDocumentResource
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public string $document,
        public array $attributes = [],
    ) {}
}
