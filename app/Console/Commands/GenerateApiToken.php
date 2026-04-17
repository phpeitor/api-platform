<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateApiToken extends Command
{
    protected $signature = 'token:generate {--name=MyApp : Nombre del token} {--description= : Descripción del token}';

    protected $description = 'Generar un nuevo token de API';

    public function handle()
    {
        $name = $this->option('name');
        $description = $this->option('description');
        
        // Generar un token único
        $token = 'token_' . Str::random(60);

        // Guardar en la base de datos
        $id = DB::table('api_tokens')->insertGetId([
            'name' => $name,
            'token' => $token,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info('✅ Token creado exitosamente!');
        $this->line('');
        $this->line("<info>ID:</info> $id");
        $this->line("<info>Nombre:</info> $name");
        if ($description) {
            $this->line("<info>Descripción:</info> $description");
        }
        $this->line('');
        $this->line('<comment>Token:</comment>');
        $this->line("<fg=yellow>$token</>");
        $this->line('');
        $this->warn('⚠️  Copia este token en un lugar seguro. No podras verlo nuevamente.');
        $this->line('');
        $this->line('Úsalo en tu requests como:');
        $this->line("<fg=cyan>Authorization: Bearer $token</>");

        return 0;
    }
}
