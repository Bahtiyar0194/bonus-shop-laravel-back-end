<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypesOfStocksLangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('types_of_stocks_lang', function (Blueprint $table) {
          $table->increments('id');
          $table->string('stock_type_name');
          $table->integer('stock_type_id')->unsigned();
          $table->foreign('stock_type_id')->references('stock_type_id')->on('types_of_stocks');
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
        Schema::dropIfExists('types_of_stocks_lang');
    }
}
