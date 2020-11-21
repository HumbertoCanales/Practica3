<?php
use App\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create(['email' => 'admin@admin.com',
        'name' => 'admin',
        'age' => 19,
        'image' => 'xd',
        'password' => Hash::make('admin')]);
    }
}
