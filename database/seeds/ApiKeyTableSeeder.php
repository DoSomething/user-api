<?php

use Illuminate\Database\Seeder;
use Northstar\Models\ApiKey;

class ApiKeyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('api_keys')->delete();

        // An API key with admin (privileged) scope.
        ApiKey::create([
            'app_id' => '456',
            'api_key' => 'abc4324',
            'scope' => ['admin', 'user'],
        ]);

        // An API key with default user (non-privileged) scope.
        ApiKey::create([
            'app_id' => '123',
            'api_key' => '5464utyrs',
            'scope' => ['user'],
        ]);
    }
}
