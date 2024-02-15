<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchRegulationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branch_regulations', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('branch_id')->unsigned();
          $table->foreign('branch_id')->references('branch_id')->on('partner_branches');
          $table->integer('week_day_id')->unsigned();
          $table->foreign('week_day_id')->references('week_day_id')->on('week_days');
          $table->integer('work_begin')->nullable();
          $table->integer('work_end')->nullable();
          $table->boolean('around_the_clock');
          $table->boolean('weekend');
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
        Schema::dropIfExists('branch_regulations');
    }
}
