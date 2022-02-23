<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('fileables')){
            Schema::create('files', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('original_name');
                $table->string('save_name');
                $table->string('path');
                $table->string('url');
                $table->string('extension')->nullable();
                $table->json('extra')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
