<?php
use App\Ability;
use Illuminate\Database\Seeder;

class AbilitySeeder extends Seeder
{
    public function run()
    {
        Ability::insert([
            ['name' => 'admin:admin'],
            ['name' => 'user:list'],
            ['name' => 'user:profile'],
            ['name' => 'post:publish'],
            ['name' => 'post:edit'],
            ['name' => 'post:delete'],
            ['name' => 'com:publish'],
            ['name' => 'com:edit'],
            ['name' => 'com:delete']
        ]);
    }
}
