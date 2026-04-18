<?php

return [
    'title' => 'API RENIEC',
    'description' => 'Consulta RENIEC por DNI',
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
