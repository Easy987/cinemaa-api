<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * ROLES
         */
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $uploaderRole = Role::firstOrCreate(['name' => 'uploader']);
        $moderatorRole = Role::firstOrCreate(['name' => 'moderator']);
        $administratorRole = Role::firstOrCreate(['name' => 'administrator']);
        $ownerRole = Role::firstOrCreate(['name' => 'owner']);

        $permissions = [];

        /*
         * USER ROLES
         */
        $permissions[] = Permission::firstOrCreate(['name' => 'favourites.index']);
        $permissions[] = Permission::firstOrCreate(['name' => 'to-see.index']);
        $permissions[] = Permission::firstOrCreate(['name' => 'profile.index']);
        $permissions[] = Permission::firstOrCreate(['name' => 'ladder.index']);
        $permissions[] = Permission::firstOrCreate(['name' => 'upload.index']);
        $permissions[] = Permission::firstOrCreate(['name' => 'logout.index']);
        $userRole->syncPermissions($permissions);

        /*
         * UPLOADER ROLES
         */
        $permissions[] = Permission::firstOrCreate(['name' => 'admin.index']);
        $permissions[] = Permission::firstOrCreate(['name' => 'links.submit']);
        $uploaderRole->syncPermissions($permissions);

        /*
         * MODERATOR ROLES
         */
        $permissions[] = Permission::firstOrCreate(['name' => 'comments.delete']);
        $permissions[] = Permission::firstOrCreate(['name' => 'admin.movies.index']);
        $permissions[] = Permission::firstOrCreate(['name' => 'admin.comments.index']);
        $permissions[] = Permission::firstOrCreate(['name' => 'admin.rules.index']);
        $permissions[] = Permission::firstOrCreate(['name' => 'admin.preliminaries.index']);
        $permissions[] = Permission::firstOrCreate(['name' => 'admin.reports.index']);
        $moderatorRole->syncPermissions($permissions);

        /*
         * ADMINISTRATOR ROLES
         */
        $permissions[] = Permission::firstOrCreate(['name' => 'admin.users.index']);
        $permissions[] = Permission::firstOrCreate(['name' => 'admin.sites.index']);
        $administratorRole->syncPermissions($permissions);

        /*
         * OWNER ROLES
         */
        $permissions[] = Permission::firstOrCreate(['name' => 'admin.sites.index']);
        $permissions[] = Permission::firstOrCreate(['name' => 'admin.sites.delete']);
        $ownerRole->syncPermissions($permissions);
    }
}
