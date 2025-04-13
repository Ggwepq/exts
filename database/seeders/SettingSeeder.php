<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('themes')->insert([
            [
                'name' => 'light',
            ],
            [
                'name' => 'dark',
            ],
            [
                'name' => 'cupcake',
            ],
            [
                'name' => 'bumblebee',
            ],
            [
                'name' => 'emerald',
            ],
            [
                'name' => 'corporate',
            ],
            [
                'name' => 'synthwave',
            ],
            [
                'name' => 'retro',
            ],
            [
                'name' => 'cyberpunk',
            ],
            [
                'name' => 'valentine',
            ],
            [
                'name' => 'hallooween',
            ],
            [
                'name' => 'garden',
            ],
            [
                'name' => 'forest',
            ],
            [
                'name' => 'aqua',
            ],
            [
                'name' => 'lofi',
            ],
            [
                'name' => 'fantasy',
            ],
            [
                'name' => 'wireframe',
            ],
            [
                'name' => 'black',
            ],
            [
                'name' => 'luxury',
            ],
            [
                'name' => 'dracula',
            ],
            [
                'name' => 'cmyk',
            ],
            [
                'name' => 'autumn',
            ],
            [
                'name' => 'business',
            ],
            [
                'name' => 'acid',
            ],
            [
                'name' => 'lemonade',
            ],
            [
                'name' => 'night',
            ],
            [
                'name' => 'coffee',
            ],
            [
                'name' => 'winter',
            ],
            [
                'name' => 'dim',
            ],
            [
                'name' => 'nord',
            ],
            [
                'name' => 'sunset',
            ],
            [
                'name' => 'caramellatte',
            ],
            [
                'name' => 'abyss',
            ],
            [
                'name' => 'silk',
            ],
        ]);
    }
}
