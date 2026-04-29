<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\RucResource;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RucProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): RucResource
    {
        $ruc = trim((string) ($uriVariables['ruc'] ?? ''));

        if (! preg_match('/^\d{11}$/', $ruc)) {
            throw new BadRequestHttpException('El RUC debe tener exactamente 11 dígitos.');
        }

        $row = DB::connection('sqlsrv_main')
            ->table('dbo.ruc')
            ->where('RUC', $ruc)
            ->first();

        if (! $row) {
            throw new NotFoundHttpException('No se encontro informacion para el RUC proporcionado.');
        }

        return new RucResource(
            ruc: $ruc,
            attributes: (array) $row,
        );
    }
}
