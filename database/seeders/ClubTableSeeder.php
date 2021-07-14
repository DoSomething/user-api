<?php

namespace Database\Seeders;

use App\Models\Club;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClubTableSeeder extends Seeder
{
    public function run()
    {
        // Clear the database.
        DB::connection('mysql')
            ->table('clubs')
            ->delete();

        factory(Club::class, 5)->create();
    }
}
