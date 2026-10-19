<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat akun admin default jika belum ada
        if (!User::where('name', 'admin123')->exists()) {
            User::create([
                'name' => 'admin123',
                'email' => 'admin@example.com',
                'password' => Hash::make('12345'),
            ]);
        }

        // Tambah Log
        SystemLog::create([
            'event_type' => 'SYSTEM',
            'description' => 'Sistem pertama kali diinisialisasi. Akun admin dibuat.'
        ]);

        $this->call([
            DummyPltsSeeder::class,
        ]);
    }
}
