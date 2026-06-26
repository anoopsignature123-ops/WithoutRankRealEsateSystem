<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([ModuleSeeder::class]);
        $this->call(RankDesignationSeeder::class);
        $this->call([AssociateSeeder::class]);
        Role::firstOrCreate(['name' => 'super-admin']);
        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Lucknow@123'),
                'status' => 'active',
            ]
        );
        $user->assignRole(
            'super-admin'
        );
    }
}