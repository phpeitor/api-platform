<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ClaroByPhoneResource;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClaroByPhoneProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ClaroByPhoneResource
    {
        $telefono = trim((string) ($uriVariables['telefono'] ?? ''));

        if (! preg_match('/^\d{7,15}$/', $telefono)) {
            throw new BadRequestHttpException('El telefono debe tener entre 7 y 15 digitos.');
        }

        $row = DB::connection('sqlsrv_main')
            ->table('dbo.claro')
            ->where('TELEFONO', $telefono)
            ->first();

        if (! $row) {
            throw new NotFoundHttpException('No se encontro informacion para el telefono proporcionado.');
        }

        return new ClaroByPhoneResource(
            telefono: $telefono,
            attributes: (array) $row,
        );
    }
}
