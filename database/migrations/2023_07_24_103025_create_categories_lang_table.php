<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesLangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories_lang', function (Blueprint $table) {
          $table->increments('id');
          $table->string('category_name');
          $table->integer('category_id')->unsigned();
          $table->foreign('category_id')->references('category_id')->on('categories');
          $table->integer('lang_id')->unsigned();
          $table->foreign('lang_id')->references('lang_id')->on('languages');
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
        Schema::dropIfExists('categories_lang');
    }
}
