<?php

namespace App\Services;

use App\Models\Busca;
use Illuminate\Support\Facades\Cache;

class TopicosPopularesService
{
    public const CHAVE_CACHE = 'busca.topicos_populares';

    public function __construct(private readonly FiltroBuscaService $filtro) {}

    /**
     * Agrega os termos mais buscados nos últimos `$dias` dias (RF12).
     * Termos que só diferem em maiúsculas, acentos ou pontuação contam juntos.
     *
     * @return list<array{termo: string, total: int}>
     */
    public function calcular(int $dias, int $limite): array
    {
        $contagem = [];

        Busca::query()
            ->selectRaw('ds_termo, count(*) as total')
            ->where('dt_busca', '>=', now()->subDays($dias))
            ->groupBy('ds_termo')
            ->toBase()
            ->get()
            ->each(function ($linha) use (&$contagem) {
                $chave = $this->filtro->normalizar((string) $linha->ds_termo);

                if ($chave !== '' && $this->filtro->permitido($chave)) {
                    $contagem[$chave] = ($contagem[$chave] ?? 0) + (int) $linha->total;
                }
            });

        // Desempate pelo termo para a ordem ser estável.
        uksort($contagem, fn (string $a, string $b) => [$contagem[$b], $a] <=> [$contagem[$a], $b]);

        $topicos = [];

        foreach (array_slice($contagem, 0, $limite, true) as $termo => $total) {
            $topicos[] = ['termo' => (string) $termo, 'total' => $total];
        }

        return $topicos;
    }

    /**
     * @param  list<array{termo: string, total: int}>  $topicos
     */
    public function guardar(array $topicos): void
    {
        Cache::forever(self::CHAVE_CACHE, $topicos);
    }

    /**
     * @return list<array{termo: string, total: int}>
     */
    public function obter(): array
    {
        return Cache::get(self::CHAVE_CACHE, []);
    }
}
