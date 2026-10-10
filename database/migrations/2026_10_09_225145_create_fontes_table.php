<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_fonte', function (Blueprint $table) {
            $table->id('id_fonte');
            $table->string('nm_fonte');
            $table->string('ds_url_base')->unique();
            $table->smallInteger('nr_credibilidade');
            $table->boolean('fl_robots_permitido');
            $table->boolean('fl_ativa');
            $table->timestamp('dt_criacao');
        });

        DB::statement(
            'ALTER TABLE tb_fonte ADD CONSTRAINT chk_fonte_credibilidade CHECK (nr_credibilidade >= 0 AND nr_credibilidade <= 10)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_fonte');
    }
};
