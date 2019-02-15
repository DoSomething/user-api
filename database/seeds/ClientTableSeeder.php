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

        // For easy testing, we'll seed one client for web authentication:
        factory(Client::class, 'authorization_code')->create([
            'title' => 'Local Development',
            'description' => 'This is an example web OAuth client seeded with your local Northstar installation.',
            'allowed_grant' => 'authorization_code',
            'client_id' => 'dev-oauth',
            'client_secret' => 'secret1',
            'scope' => collect(Scope::all())->except('admin')->keys()->toArray(),
            'redirect_uri' => ['http://localhost:3000/', 'http://northstar.test/callback'],
        ]);

        // ..and one for machine authentication:
        factory(Client::class, 'client_credentials')->create([
            'title' => 'Local Development (Machine)',
            'description' => 'This is an example machine OAuth client seeded with your local Northstar installation.',
            'allowed_grant' => 'client_credentials',
            'client_id' => 'dev-machine',
            'client_secret' => 'secret2',
            'scope' => collect(Scope::all())->keys()->toArray(),
        ]);

        // ...and a few other random ones for good measure!
        factory(Client::class, 'authorization_code', 2)->create();
        factory(Client::class, 'client_credentials', 2)->create();
    }
}
