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
                'first_name' => 'Gustave',
                'name' => 'Kuetenang',
                'email' => 'gustaveckt@gmail.com',
                'password' => Hash::make('secret'),
                'username' => 'gkuetenang',
            ]
        );

        User::firstOrCreate(
            [
                'email' => 'nelsonnoumbon@gmail.com',
            ],
            [
                'first_name' => 'Nelson',
                'name' => 'Noumbo',
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
                'first_name' => 'Donald',
                'name' => 'Zangue',
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
                'first_name' => 'Cyrille',
                'name' => 'Donfack',
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
                'first_name' => 'Ferdinand',
                'name' => 'Yonta',
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
                'first_name' => 'Hygelin Duplex',
                'name' => 'Kana',
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
                'first_name' => 'Roger',
                'name' => 'Sokeng',
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
                'first_name' => 'Gautier',
                'name' => 'Fouejio',
                'email' => 'gautierfouejio@gmail.com',
                'password' => Hash::make('secret'),
                'username' => 'gautierfouejio',
            ]
        );
    }
}
