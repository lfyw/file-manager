<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateFileModelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_model', function (Blueprint $table) {
            $table->bigInteger('file_id')->index()->comment('文件表');
            $table->bigInteger('model_id')->index()->comment('关联模型id');
            $table->string('model_type')->comment('关联模型');
            $table->string('file_type')->nullable()->comment('关联文件类型。主要用于处理一个模型中有多个字段关联文件时的区分');
        });
        DB::statement("ALTER TABLE file_model comment '文件模型关联表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_model');
    }
}
