<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesCoveragesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services_coverages', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('service_id')->unsigned();
          $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');
          $table->integer('branch_id')->unsigned();
          $table->foreign('branch_id')->references('branch_id')->on('partner_branches')->onDelete('cascade');
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
        Schema::dropIfExists('services_coverages');
    }
}
