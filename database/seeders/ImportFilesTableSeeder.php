<?php

namespace Database\Seeders;

use App\Models\ImportFile;
use Illuminate\Database\Seeder;

class ImportFilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(ImportFile::class, 60)->create();
    }
}
