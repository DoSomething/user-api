<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear the database.
        DB::connection('mongodb')
            ->table('users')
            ->delete();

        // Create an example normal & admin user for local development.
        factory(User::class)->create([
            'email' => 'test@dosomething.org',
            'password' => 'secret',
        ]);

        factory(User::class)->states('admin')->create([
            'email' => 'admin@dosomething.org',
            'password' => 'secret',
        ]);

        // Then create some randomly-generated test data!
        factory(User::class, 4)->states('admin')->create();
        factory(User::class, 50)->states('staff')->create();
        factory(User::class, 250)->create();
    }
}
