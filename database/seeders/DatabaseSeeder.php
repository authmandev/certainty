<?php

namespace Database\Seeders;

use App\Models\User;
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
        $this->call(ReferenceOptionSeeder::class);

        User::factory()->admin()->create([
            'name' => 'مدير النظام',
            'email' => 'admin@example.com',
        ]);

        User::factory()->create([
            'name' => 'عميل تجريبي',
            'email' => 'test@example.com',
        ]);
    }
}
