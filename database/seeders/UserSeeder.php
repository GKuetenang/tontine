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
                'email' => 'gustaveckt@gmail.com',
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
                'email' => 'debiangtk@gmail.com',
            ],
            [
                'name' => 'Debian GTK',
                'email' => 'debiangtk@gmail.com',
                'password' => Hash::make('secret'),
                'username' => 'debiangtk',
            ]
        );

        User::firstOrCreate(
            [
                'email' => 'nelsonnoumbon@gmail.com',
            ],
            [
                'name' => 'Nelson Noumbo',
                'email' => 'nelsonnoumbon@gmail.com',
                'password' => Hash::make('secret'),
                'username' => 'nelsonnoumbon',
            ]
        );

        User::firstOrCreate(
            [
                'email' => 'donaldzangue@gmail.com',
            ],
            [
                'name' => 'Donald Zangue',
                'email' => 'donaldzangue@gmail.com',
                'password' => Hash::make('secret'),
                'username' => 'donaldzangue',
            ]
        );

        User::firstOrCreate(
            [
                'email' => 'cyrilledonfact@gmail.com',
            ],
            [
                'name' => 'Cyrille Donfack',
                'email' => 'cyrilledonfact@gmail.com',
                'password' => Hash::make('secret'),
                'username' => 'cyrilledonfact',
            ]
        );

        User::firstOrCreate(
            [
                'email' => 'ferdiandyonta@gmail.com',
            ],
            [
                'name' => 'Ferdinand Yonta',
                'email' => 'ferdiandyonta@gmail.com',
                'password' => Hash::make('secret'),
                'username' => 'ferdiandyonta',
            ]
        );

        User::firstOrCreate(
            [
                'email' => 'hygelinkana@gmail.com',
            ],
            [
                'name' => 'Hygelin Duplex Kana',
                'email' => 'hygelinkana@gmail.com',
                'password' => Hash::make('secret'),
                'username' => 'hygelinkana',
            ]
        );

        User::firstOrCreate(
            [
                'email' => 'rogersokeng@gmail.com',
            ],
            [
                'name' => 'Roger Sokeng',
                'email' => 'rogersokeng@gmail.com',
                'password' => Hash::make('secret'),
                'username' => 'rogersokeng',
            ]
        );

        User::firstOrCreate(
            [
                'email' => 'gautierfouejio@gmail.com',
            ],
            [
                'name' => 'Gautier Fouejio',
                'email' => 'gautierfouejio@gmail.com',
                'password' => Hash::make('secret'),
                'username' => 'gautierfouejio',
            ]
        );
    }
}
