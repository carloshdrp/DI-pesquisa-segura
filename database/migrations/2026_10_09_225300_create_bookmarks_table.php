<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_bookmark', function (Blueprint $table) {
            $table->id('id_bookmark');
            $table->timestamp('dt_criacao');

            $table->foreignId('id_usuario')
                ->constrained('tb_usuario', 'id_usuario');

            $table->foreignId('id_pagina_indexada')
                ->constrained(
                    'tb_pagina_indexada',
                    'id_pagina_indexada'
                );

            $table->unique([
                'id_usuario',
                'id_pagina_indexada',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_bookmark');
    }
};
