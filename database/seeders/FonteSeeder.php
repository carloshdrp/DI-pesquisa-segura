<?php

namespace Database\Seeders;

use App\Models\Fonte;
use Illuminate\Database\Seeder;

class FonteSeeder extends Seeder
{
    /**
     * Fontes acadêmicas abertas, com as mesmas APIs usadas pelos jobs de busca.
     * Rodar de novo atualiza as existentes (a chave é a URL base).
     */
    public function run(): void
    {
        $fontes = [
            ['nm_fonte' => 'OpenAlex', 'ds_url_base' => 'https://openalex.org', 'nr_credibilidade' => 9],
            ['nm_fonte' => 'arXiv', 'ds_url_base' => 'https://arxiv.org', 'nr_credibilidade' => 8],
            ['nm_fonte' => 'Crossref', 'ds_url_base' => 'https://www.crossref.org', 'nr_credibilidade' => 8],
        ];

        foreach ($fontes as $fonte) {
            Fonte::updateOrCreate(
                ['ds_url_base' => $fonte['ds_url_base']],
                $fonte + ['fl_robots_permitido' => true, 'fl_ativa' => true, 'dt_criacao' => now()],
            );
        }
    }
}
