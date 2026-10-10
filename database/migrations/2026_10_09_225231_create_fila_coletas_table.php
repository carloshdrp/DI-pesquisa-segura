<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_fila_coleta', function (Blueprint $table) {
            $table->id('id_fila_coleta');
            $table->string('ds_termo_busca')->nullable();
            $table->char('tp_origem', 1);
            $table->char('tp_status', 1);
            $table->timestamp('dt_agendamento');
            $table->timestamp('dt_conclusao')->nullable();

            $table->foreignId('id_fonte')
                ->nullable()
                ->constrained('tb_fonte', 'id_fonte');
        });

        DB::statement(
            "ALTER TABLE tb_fila_coleta ADD CONSTRAINT chk_fila_origem CHECK (tp_origem IN ('1', '2'))"
        );

        DB::statement(
            "ALTER TABLE tb_fila_coleta ADD CONSTRAINT chk_fila_status CHECK (tp_status IN ('1', '2', '3', '4'))"
        );

        DB::statement(
            "ALTER TABLE tb_fila_coleta ADD CONSTRAINT chk_fila_destino CHECK ((ds_termo_busca IS NOT NULL AND id_fonte IS NULL) OR (ds_termo_busca IS NULL AND id_fonte IS NOT NULL))"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_fila_coleta');
    }
};
