<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::findByName('admin');

        $roles = Role::query()->get();


        for ($i = 1; $i < 10; $i++) {
            $randomRole = $roles[rand(0, count($roles) - 1)];

            $user = User::create([
                'name' => 'user' . $i,
                'email' => 'user' . $i . 'test@example.com',
                'password' => Hash::make('seederuser'),
            ]);

            $user->assignRole($randomRole);
        }
    }
}
