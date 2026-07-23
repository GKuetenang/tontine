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
                'email' => 'gustave@figuil.com'
            ],
            [
                'name' => 'Gustave Kuetenang',
                'email' => 'gustave@figuil.com',
                'password' => Hash::make('secret'),
                'username' => 'gkuetenang',
            ]
        );

        User::firstOrCreate(
            [
                'email' => 'debiangtk@figuil.com'
            ],
            [
                'name' => 'Debian GTK',
                'email' => 'debiangtk@figuil.com',
                'password' => Hash::make('secret'),
                'username' => 'debiangtk',
            ]
        );
    }
}
