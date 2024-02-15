<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockCoveragesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_coverages', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('stock_id')->unsigned();
          $table->foreign('stock_id')->references('stock_id')->on('stocks')->onDelete('cascade');
          $table->integer('city_id')->unsigned();
          $table->foreign('city_id')->references('city_id')->on('cities')->onDelete('cascade');
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
        Schema::dropIfExists('stock_coverages');
    }
}
