<?php

use Illuminate\Database\Seeder;
use Northstar\Models\Client;
use Northstar\Auth\Scope;

class ClientTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('clients')->delete();

        // For easy testing, we'll seed one client with all scopes...
        Client::create([
            'title' => 'Trusted Test Client',
            'description' => 'This is an example OAuth client seeded with your local Northstar installation. It was automatically given all scopes that were defined when it was created.',
            'client_id' => 'trusted-test-client',
            'client_secret' => 'secret1',
            'allowed_grants' => ['authorization_code', 'password', 'client_credentials'],
            'scope' => collect(Scope::all())->keys()->toArray(),
        ]);

        // ..and one with limited scopes.
        Client::create([
            'title' => 'Untrusted Test Client',
            'description' => 'This is an example OAuth client seeded with your local Northstar installation. It is only given the user scope, and can be used to simulate untrusted clients (for example, the mobile app).',
            'client_id' => 'untrusted-test-client',
            'client_secret' => 'secret2',
            'allowed_grants' => ['password'],
            'scope' => ['user'],
        ]);
    }
}
