
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nm_usuario', 150);
            $table->string('ds_email')->unique();
            $table->string('ds_senha');
            $table->timestamp('dt_criacao');
            $table->boolean('tp_perfil');
            $table->integer('nr_limite_busca_dia');
            $table->integer('nr_buscas_hoje');
            $table->date('dt_referencia_contador');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_usuario');
    }
};
