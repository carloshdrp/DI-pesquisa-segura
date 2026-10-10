<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_pagina_indexada', function (Blueprint $table) {
            $table->id('id_pagina_indexada');
            $table->string('ds_url')->unique();
            $table->string('ds_titulo');
            $table->text('ds_resumo')->nullable();
            $table->longText('ds_conteudo_texto')->nullable();

            $table->timestamp('dt_coletado');
            $table->timestamp('dt_atualizado');

            $table->foreignId('id_fonte')
                ->constrained('tb_fonte', 'id_fonte');

            $table->fullText([
                'ds_titulo',
                'ds_resumo',
                'ds_conteudo_texto',
            ], 'ft_pagina_indexada_conteudo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_pagina_indexada');
    }
};
