<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            [
                'email' => 'gustaveckt@gmail.com'
            ],
            [
                'name' => 'Gustave Kuetenang',
                'email' => 'gustaveckt@gmail.com',
                'password' => Hash::make('secret'),
                'username' => 'gkuetenang',
            ]
        );

        User::firstOrCreate(
            [
                'email' => 'debiangtk@gmail.com'
            ],
            [
                'name' => 'Debian GTK',
                'email' => 'debiangtk@gmail.com',
                'password' => Hash::make('secret'),
                'username' => 'debiangtk',
            ]
        );
    }
}
