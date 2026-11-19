
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_sessao_anonima', function (Blueprint $table) {
            $table->id('id_sessao_anonima');
            $table->char('ds_identificador', 36)->unique();
            $table->integer('nr_buscas_hoje');
            $table->date('dt_referencia_contador');
            $table->timestamp('dt_criacao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_sessao_anonima');
    }
};
