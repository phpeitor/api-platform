<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\AmdocsByPhoneResource;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AmdocsByPhoneProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AmdocsByPhoneResource
    {
        $telefono = trim((string) ($uriVariables['telefono'] ?? ''));

        if (! preg_match('/^\d{7,15}$/', $telefono)) {
            throw new BadRequestHttpException('El telefono debe tener entre 7 y 15 digitos.');
        }

        $row = DB::connection('sqlsrv_reniec')
            ->table('dbo.amdocs')
            ->where('primary_resource_value', $telefono)
            ->first();

        if (! $row) {
            throw new NotFoundHttpException('No se encontro informacion para el telefono proporcionado.');
        }

        return new AmdocsByPhoneResource(
            telefono: $telefono,
            attributes: (array) $row,
        );
    }
}
