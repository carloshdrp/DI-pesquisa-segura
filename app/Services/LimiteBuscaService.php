<?php

namespace App\Services;

use App\Models\SessaoAnonima;
use App\Models\Usuario;
use Illuminate\Support\Facades\Date;

class LimiteBuscaService
{
    /**
     * Contabiliza uma busca para o titular, se ainda houver limite no dia.
     *
     * A verificação e o incremento acontecem em um único UPDATE, então
     * requisições simultâneas não ultrapassam o limite.
     */
    public function consumir(Usuario|SessaoAnonima $titular): bool
    {
        $this->zerarSeNovoDia($titular);

        $afetados = $titular->newQuery()
            ->whereKey($titular->getKey())
            ->where('nr_buscas_hoje', '<', $this->limiteDe($titular))
            ->increment('nr_buscas_hoje');

        $titular->refresh();

        return $afetados > 0;
    }

    public function limiteDe(Usuario|SessaoAnonima $titular): int
    {
        return $titular instanceof Usuario
            ? $titular->nr_limite_busca_dia
            : (int) config('busca.limite_anonimo');
    }

    /**
     * Segundos até o contador diário ser zerado (meia-noite).
     */
    public function segundosAteReset(): int
    {
        return (int) max(1, Date::now()->diffInSeconds(Date::tomorrow(), true));
    }

    private function zerarSeNovoDia(Usuario|SessaoAnonima $titular): void
    {
        $titular->newQuery()
            ->whereKey($titular->getKey())
            ->whereDate('dt_referencia_contador', '<', Date::today())
            ->update([
                'nr_buscas_hoje' => 0,
                'dt_referencia_contador' => Date::today(),
            ]);
    }
}
