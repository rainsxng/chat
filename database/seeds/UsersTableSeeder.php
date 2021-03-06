<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
           'name' => 'admin',
           'email' => 'admin@gmail.com',
           'role' => 'admin',
           'password' => bcrypt('password'),
           'color' => \App\User::getRandColor(),
           'gravatar_img' =>md5('admin@gmail.com')
        ]);
    }
}
