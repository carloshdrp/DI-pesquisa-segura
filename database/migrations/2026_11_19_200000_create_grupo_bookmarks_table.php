<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_grupo_bookmark', function (Blueprint $table) {
            $table->id('id_grupo_bookmark');
            $table->string('nm_grupo', 150);
            $table->timestamp('dt_criacao');

            $table->foreignId('id_usuario')
                ->constrained('tb_usuario', 'id_usuario')
                ->cascadeOnDelete();

            $table->unique(['id_usuario', 'nm_grupo']);
        });

        Schema::table('tb_bookmark', function (Blueprint $table) {
            $table->foreignId('id_grupo_bookmark')
                ->nullable()
                ->constrained('tb_grupo_bookmark', 'id_grupo_bookmark')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_bookmark', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_grupo_bookmark');
        });

        Schema::dropIfExists('tb_grupo_bookmark');
    }
};
