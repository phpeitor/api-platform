<?php

return [
    'title' => 'Metadatape',
    'description' => 'Consulta por DNI & PHONE. Genera tokens de API para acceder a los endpoints protegidos.',
    'show_webby' => false,

    // Priorizamos HTML para que /api/docs abra la interfaz en navegador y en curl sin Accept.
    'docs_formats' => [
        'html' => ['text/html'],
        'jsonopenapi' => ['application/vnd.openapi+json'],
        'jsonld' => ['application/ld+json'],
    ],

    'redoc' => [
        'enabled' => false,
    ],

    'scalar' => [
        'enabled' => false,
    ],

    'swagger_ui' => [
        'enabled' => true,
    ],
];
