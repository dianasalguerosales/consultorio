<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@caine.com'],
            ['password' => Hash::make('admin123')]
        );

        User::updateOrCreate(
            ['email' => 'juan@example.com'],
            ['password' => Hash::make('terapeuta123')]
        );

        User::updateOrCreate(
            ['email' => 'carlos@example.com'],
            ['password' => Hash::make('encargado123')]
        );

        User::updateOrCreate(
            ['email' => 'ana@example.com'],
            ['password' => Hash::make('encargado456')]
        );

        User::updateOrCreate(
            ['email' => 'maria@example.com'],
            ['password' => Hash::make('coordinador123')]
        );

        User::updateOrCreate(
            ['email' => 'jose@example.com'],
            ['password' => Hash::make('auxiliar123')]
        );
    }
}