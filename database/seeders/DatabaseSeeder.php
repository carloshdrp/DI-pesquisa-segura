<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(FonteSeeder::class);

        Usuario::factory()->create([
            'nm_usuario' => 'Test User',
            'ds_email' => 'test@example.com',
        ]);
    }
}
