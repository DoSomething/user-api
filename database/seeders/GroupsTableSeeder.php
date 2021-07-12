<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupsTableSeeder extends Seeder
{
    public function run()
    {
        // Clear the database.
        DB::connection('mysql')
            ->table('groups')
            ->delete();

        factory(Group::class, 5)->create();

        // Groups with a school specified.
        factory(Group::class, 5)
            ->states('school')
            ->create();
    }
}
