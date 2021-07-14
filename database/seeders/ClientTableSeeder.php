<?php

namespace Database\Seeders;

use App\Auth\Scope;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mongodb')
            ->table('clients')
            ->delete();

        // For easy testing, we'll seed one client for web authentication:
        factory(Client::class, 'authorization_code')->create([
            'title' => 'Local Development',
            'description' =>
                'This is an example web OAuth client seeded with your local Northstar installation.',
            'allowed_grant' => 'authorization_code',
            'client_id' => 'dev-oauth',
            'client_secret' => 'secret1',
            'scope' => collect(Scope::all())
                ->except('admin')
                ->keys()
                ->toArray(),
            // @NOTE: We're omitting 'redirect_uri' here for easy local dev.
            'redirect_uri' => null,
        ]);

        // ..and one for machine authentication:
        factory(Client::class, 'client_credentials')->create([
            'title' => 'Local Development (Machine)',
            'description' =>
                'This is an example machine OAuth client seeded with your local Northstar installation.',
            'allowed_grant' => 'client_credentials',
            'client_id' => 'dev-machine',
            'client_secret' => 'secret2',
            'scope' => collect(Scope::all())
                ->keys()
                ->toArray(),
        ]);

        // ...and a few other random ones for good measure!
        factory(Client::class, 'authorization_code', 2)->create();
        factory(Client::class, 'client_credentials', 2)->create();
    }
}
