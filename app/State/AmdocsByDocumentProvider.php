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

        // Primero intentamos obtener el registro ACTIVO con el menor subscriber_key
        $row = DB::connection('sqlsrv_reniec')
            ->table('dbo.amdocs')
            ->where('document', $document)
            ->where('subscriber_status_key', 'ACTIVO')
            ->orderBy('subscriber_key', 'asc')
            ->first();

        // Si no hay registros ACTIVO, tomamos el registro con el menor subscriber_key sin filtrar por estado
        if (! $row) {
            $row = DB::connection('sqlsrv_reniec')
                ->table('dbo.amdocs')
                ->where('document', $document)
                ->orderBy('subscriber_key', 'asc')
                ->first();
        }

        if (! $row) {
            throw new NotFoundHttpException('No se encontro informacion para el documento proporcionado.');
        }

        return new AmdocsByDocumentResource(
            document: $document,
            attributes: (array) $row,
        );
    }
}
