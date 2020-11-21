<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(AbilitySeeder::class);

        DB::table('ability_user')->insert([
            'ability_id' => 1,
            'user_id' => 1
        ]);
    }
}
