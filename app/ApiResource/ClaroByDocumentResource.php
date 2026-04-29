<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\ClaroByDocumentProvider;

#[ApiResource(
    shortName: 'ClaroByDocument',
    operations: [
        new Get(
            uriTemplate: '/claro/document/{document}',
            requirements: ['document' => '[A-Za-z0-9]+' ],
            provider: ClaroByDocumentProvider::class,
        ),
    ],
    paginationEnabled: false,
)]
class ClaroByDocumentResource
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public string $document,
        public array $attributes = [],
    ) {}
}
