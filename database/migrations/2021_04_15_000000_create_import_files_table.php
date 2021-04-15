<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id')->nullable();
            $table->string('import_type')->index();
            $table
                ->string('options')
                ->nullable()
                ->comment(
                    'Parameters passed to the import, like Email Subscription Topic or RTV Report ID.',
                );
            $table->string('filepath');
            $table->integer('row_count');
            $table->integer('import_count');
            $table->integer('skip_count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_files');
    }
}
