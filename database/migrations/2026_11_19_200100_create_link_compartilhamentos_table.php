<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_link_compartilhamento', function (Blueprint $table) {
            $table->id('id_link_compartilhamento');
            $table->char('ds_token', 64)->unique();
            $table->timestamp('dt_criacao');
            $table->timestamp('dt_revogacao')->nullable();

            $table->foreignId('id_bookmark')
                ->nullable()
                ->constrained('tb_bookmark', 'id_bookmark')
                ->cascadeOnDelete();

            $table->foreignId('id_grupo_bookmark')
                ->nullable()
                ->constrained('tb_grupo_bookmark', 'id_grupo_bookmark')
                ->cascadeOnDelete();
        });

        // O link compartilha um favorito OU um grupo, nunca os dois nem nenhum.
        DB::statement(
            'alter table tb_link_compartilhamento add constraint ck_link_compartilhamento_alvo '
            .'check ((id_bookmark is null) <> (id_grupo_bookmark is null))'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_link_compartilhamento');
    }
};
