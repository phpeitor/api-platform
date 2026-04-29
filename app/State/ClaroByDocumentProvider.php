<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ClaroByDocumentResource;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClaroByDocumentProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ClaroByDocumentResource
    {
        $document = trim((string) ($uriVariables['document'] ?? ''));

        if ($document === '') {
            throw new BadRequestHttpException('El documento es obligatorio.');
        }

        $rows = DB::connection('sqlsrv_main')
            ->table('dbo.claro')
            ->where('DOCUMENTO', $document)
            ->get();

        if ($rows->isEmpty()) {
            throw new NotFoundHttpException('No se encontro informacion para el documento proporcionado.');
        }

        // Normalizamos filas a arrays con claves en lowercase para respuesta
        $items = $rows->map(function ($r) {
            $arr = (array) $r;
            $normalized = [];
            foreach ($arr as $k => $v) {
                $normalized[strtolower($k)] = $v;
            }
            return $normalized;
        })->toArray();

        // Si todas las filas comparten mismo titular y plan_claro y documento,
        // respondemos con esos datos + lista de telefonos
        $titulares = array_unique(array_map(fn($i) => $i['titular'] ?? null, $items));
        $planClaro = array_unique(array_map(fn($i) => $i['plan_claro'] ?? null, $items));

        if (count($titulares) === 1 && count($planClaro) === 1) {
            $telefonos = array_values(array_unique(array_map(fn($i) => $i['telefono'] ?? null, $items)));
            $attributes = [
                'titular' => $items[0]['titular'] ?? null,
                'documento' => $document,
                'plan_claro' => $items[0]['plan_claro'] ?? null,
                'telefonos' => $telefonos,
                'rows_count' => count($items),
            ];
        } else {
            // De lo contrario devolvemos todas las filas encontradas
            $attributes = ['results' => $items, 'rows_count' => count($items)];
        }

        return new ClaroByDocumentResource(
            document: $document,
            attributes: $attributes,
        );
    }
}
