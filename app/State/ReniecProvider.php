<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ReniecResource;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReniecProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ReniecResource
    {
        $dni = (string) ($uriVariables['dni'] ?? '');

        if (! preg_match('/^\d{8}$/', $dni)) {
            throw new BadRequestHttpException('El DNI debe tener 8 digitos.');
        }

        $row = DB::connection('sqlsrv_reniec')
            ->table('dbo.reniec')
            ->where('dni', $dni)
            ->first();

        if (! $row) {
            throw new NotFoundHttpException('No se encontro informacion para el DNI proporcionado.');
        }

        return new ReniecResource(
            dni: $dni,
            attributes: (array) $row,
        );
    }
}
