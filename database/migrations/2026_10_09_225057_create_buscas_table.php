<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_busca', function (Blueprint $table) {
            $table->id('id_busca');
            $table->string('ds_termo');
            $table->integer('nr_resultados');
            $table->timestamp('dt_busca');

            $table->foreignId('id_usuario')
                ->nullable()
                ->constrained('tb_usuario', 'id_usuario');

            $table->foreignId('id_sessao_anonima')
                ->nullable()
                ->constrained('tb_sessao_anonima', 'id_sessao_anonima');
        });

        DB::statement("
            ALTER TABLE tb_busca
            ADD CONSTRAINT chk_busca_origem
            CHECK (
                id_usuario IS NOT NULL
                OR id_sessao_anonima IS NOT NULL
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_busca');
    }
};
