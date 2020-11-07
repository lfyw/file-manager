<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('original_name')->comment('源文件名');
            $table->string('save_name')->comment('保存文件名');
            $table->string('path')->comment('保存地址');
            $table->string('url')->comment('文件url');
            $table->string('extension')->nullable()->comment('文件后缀');
            $table->json('extra')->nullable()->comment('文件额外信息');
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
        Schema::dropIfExists('files');
    }
}
