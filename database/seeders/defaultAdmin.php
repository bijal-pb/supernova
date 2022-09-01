<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class defaultAdmin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where("email", "admin@admin.com")->first();

        if (!$user) {
            $user = new User();
            $user->email  = "admin@admin.com";
            $user->name = "Admin";
            $user->password = bcrypt('123456789');
            $user->name = "Supernova Admin";
            $user->save();
        }

        
        $role = Role::create(['name' => 'Admin']);
        $role1 = Role::create(['name' => 'Providers']);
        $role2 = Role::create(['name' => 'Customer']);

        $user->assignRole([$role->id]);
    }
}
