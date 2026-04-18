<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\AmdocsByDocumentResource;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AmdocsByDocumentProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AmdocsByDocumentResource
    {
        $document = trim((string) ($uriVariables['document'] ?? ''));

        if ($document === '') {
            throw new BadRequestHttpException('El documento es obligatorio.');
        }

        $row = DB::connection('sqlsrv_reniec')
            ->table('dbo.amdocs')
            ->where('document', $document)
            ->first();

        if (! $row) {
            throw new NotFoundHttpException('No se encontro informacion para el documento proporcionado.');
        }

        return new AmdocsByDocumentResource(
            document: $document,
            attributes: (array) $row,
        );
    }
}
